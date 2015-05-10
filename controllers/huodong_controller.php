<?php
//活动页面
class HuodongController extends AppController {

	var $name = 'Huodong';

	function beforeFilter(){

		$device_id = device_id();
		$platform = platform();

		if(!$device_id || !$platform){
			die('请下载最新应用');
		}
	}

	//显示抽奖界面
	function index(){

		die('活动下线！');

		//中奖配置
		$config = array(
			1=>0,
			2=>30,
			3=>20,
			4=>1,
			5=>100,
			6=>1000,
		);

		$prize = 4;

		//提交出现错误，仍然记住上次集分宝
		if(isset($_GET['hint']) && $_GET['hint']){
			//出现错误提示，不进行重复抽奖
		}else{
			//设置奖金
			D('myuser')->newgift($config[$prize], false);
		}

		$this->set('prize', $prize);
		$this->set('jfb', $config[$prize]);
		if(D('vcode')->need()){
			$this->set('need_vcode', true);
		}

		$this->set('title', '高倍奖励全面开启！！');
		$this->set('meta_keywords', '集分宝,集分宝签到,淘宝集分宝,支付宝集分宝,集分宝怎么用');
		$this->set('meta_description', '每日赠送1w集分宝，大量赠送集分宝，永久有效，希望免费领集分宝的朋友赶紧来吧');
	}

	//领取奖励
	function getPrize(){

		die('活动下线！');

		$this->layout = 'hint';

		$need = D('vcode')->need();

		if($need){
			$code = $_GET['vcode'];
			if(!$code || !D('vcode')->verify($code)){
				$this->redirect(urlWithParam($_GET+array('hint'=>'请在下面填入验证码'), '/huodong'));
			}
		}

		$alipay = D('subscribe')->getAlipay(device_id(), platform());

		if(!$alipay){
			$alipay = @$_GET['alipay'];
			$no_relative_alipay = true;
		}else{
			$no_relative_alipay = false;
		}


		if($alipay && !isset($_GET['ch_alipay'])){

			if(!valid($alipay, 'email') && !valid($alipay, 'mobile')){
				$this->redirect(urlWithParam($_GET+array('hint'=>'支付宝错误，是手机号或邮箱才对哟'), '/huodong'));
			}

			//进行登录
			$ret = D('myuser')->saveAlipay($alipay, $err);
			if(!$ret)$this->redirect(urlWithParam($_GET+array('hint'=>$err), '/huodong'));

			$exist = $ret['exist'];
			$ret = D('myuser')->login($ret['user_id']);

			if(!$ret)$this->redirect(urlWithParam($_GET+array('hint'=>'系统登录错误，请重试'), '/huodong'));

			if($no_relative_alipay)D('subscribe')->saveAlipay(device_id(), platform(), $alipay);

			//判断是否恶意注册
			if(!$exist)D('protect')->attackReg();
			D('vcode')->record();

			//如果支付宝无效，则不进行增加资产
			if(D('myuser')->getAlipayValid() == \DAL\User::ALIPAY_VALID_ERROR)$ch_alipay = true;

		}elseif($alipay && isset($_GET['ch_alipay']) && isset($_GET['alipay']) && D('myuser')->islogined()){

			//如果支付宝无效，则不进行增加资产
			if(D('myuser')->getAlipayValid() == \DAL\User::ALIPAY_VALID_ERROR)$ch_alipay = true;

			$alipay = $_GET['alipay'];

			$ret = D('myuser')->changeAlipay($alipay, $err);
			if(!$ret)$this->redirect(urlWithParam($_GET+array('hint'=>$err), '/huodong'));

			D('subscribe')->saveAlipay(device_id(), platform(), $alipay);

		}else{
			die('您尚未输入支付宝!');
		}

		if(!D('myuser')->canGetCashgift()){
			$this->flash('您已经抽过奖，请将机会让给更多的人！', urlWithParam($_GET, '/huodong'), 3);
		}

		//判断是否已有积分
		$amount_exist = D('myuser')->newgift(0, true);

		if(!$amount_exist || $amount_exist> 10000){
			$this->flash('抽奖无效，请重新抽奖！', urlWithParam($_GET, '/huodong'), 3);
		}

		//进行打款，捕获支付状态，显示提示，并提醒手机APP可多次抽奖，并提示状态
		$ret = D('order')->redis('lock')->getlock(\Redis\Lock::LOCK_LOTTERY_ADD, D('myuser')->getId().':app');
		if($ret){

			$amount = D('myuser')->newgift(0, true);

			//防止上次因为支付宝账号无效，进行解锁
			if(!@$ch_alipay){

				//插入新人抽奖集分宝红包订单
				D('order')->db('order_cashgift');
				$ret = D('order')->addCashgift(D('myuser')->getId(), \DB\OrderCashgift::GIFTTYPE_LOTTERY, $amount);
				if(!$ret){
					D('order')->redis('lock')->unlock(\Redis\Lock::LOCK_LOTTERY_ADD, D('myuser')->getId().':app');
					$this->flash('抽奖无效，请返回重试！', urlWithParam($_GET, '/huodong'), 3);
				}
				D('log')->action(1220, 1, array('data1'=>$ret['o_id'], 'data2'=>$ret['amount'], 'data3'=>'app'));
			}

			//走接口支付
			D()->api('interal')->pay(D('myuser')->getId());
			D()->db('order_reduce');
			$hit = D('order')->getSubList('reduce', array('user_id'=>D('myuser')->getId(), 'createdate'=>date('Y-m-d'), 'status'=>\DB\OrderReduce::STATUS_PAY_DONE), 'o_id DESC');

			if($hit)$this->set('message', '已支付，请登录支付宝查收~');

			if(D('myuser')->getAlipayValid() == \DAL\User::ALIPAY_VALID_ERROR){
				D('order')->redis('lock')->unlock(\Redis\Lock::LOCK_LOTTERY_ADD, D('myuser')->getId().':app');
				//防止携带了想更改成的目标alipay，由于验证码去除了ch_alipay标志，变为了初次领取，
				unset($_GET['alipay']);
				$this->redirect(urlWithParam($_GET+array('hint'=>'alipay_not_exist'), urlWithParam($_GET, '/huodong')));
			}

			if(!$hit)$this->set('message', '3小时内到账支付宝，注意查收~');

			//清除中奖信息
			D('myuser')->newgift();

		}else{
			$this->set('message', '今日已抽过奖了，请明天再来~');
		}
	}
}
?>