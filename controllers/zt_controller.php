<?php
//H5专题页面
class ZtController extends AppController {

	var $name = 'Zt';
	var $components = array('Pagination');
	var $cacheAction = array();

	//新人引导
	function guide(){
		$this->set('title', '特卖知识新人科普篇');
	}

	//首次下单50返10元
	function newOrder(){

		if(@$_POST['post']){
			if(!$_POST['taobao_no'] || !$_POST['alipay'] || !$_POST['email']){
				$this->flash('请填入订单尾号、收款支付宝、联系邮箱！', '/zt/newOrder', 3);
			}

			if(strlen($_POST['taobao_no'])!=6 || !valid($_POST['email'], 'email')){
				$this->flash('请填入正确信息！', '/zt/newOrder', 3);
			}

			if(!platform() || !device_id()){
				$this->flash('提交失败，请下载最新应用', '/zt/newOrder', 3);
			}

			$data = array('taobao_no'=>$_POST['taobao_no'], 'alipay'=>$_POST['alipay'], 'email'=>$_POST['email'], 'channel'=>platform(), 'account'=>device_id());
			$ret = D('huodong')->add($data);
			if($ret){
				$this->flash('信息提交成功，1个工作日内返现', '/zt/newOrder', 3);
			}else{
				$this->flash('请勿重复提交，如有问题，请在下方提交疑问信息', '/zt/newOrder', 3);
			}
		}else{

			D('huodong')->db('huodong');
			$ret = D('huodong')->get(\DB\Huodong::TYPE_NEW, device_id(), platform());
			if($ret){

				if($ret['status'] == \DB\Huodong::STATUS_WAIT){
					$this->set('status', 'wait');
				}

				switch ($ret['status']) {
					case \DB\Huodong::STATUS_WAIT:
						$this->set('status', 'wait');
						break;
					case \DB\Huodong::STATUS_PASS:
						$this->set('status', 'pass');
						break;
					case \DB\Huodong::STATUS_INVALID:
						$this->set('status', 'invalid');
						break;
				}
			}
		}
	}
}
?>