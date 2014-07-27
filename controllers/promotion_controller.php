<?php
//特卖页面
class PromotionController extends AppController {

	var $name = 'Promotion';
	var $components = array('Pagination');
	var $cacheAction = array('cat'=> MINUTE);

	//特卖分类商品列表
	function cat($cat, $midcat=''){

		if(!$cat)
			$cat = '服装鞋子';
		else
			$cat = urldecode($cat);

		$all_goods_cat = D('promotion')->getCatConfig(true);
		$cond = array();
		$cond['cat'] = $cat;
		if($midcat){
			$midcat = urldecode($midcat);
			$cond['subcat'] = D('promotion')->midcat2subcat($midcat);
		}

		$lists = D('promotion')->getList($this->Pagination, $cond, C('comm', 'h5_promo_cat_goods_pre_page'), false);

		if($midcat){
			$this->set('title', '今日'.$midcat.'特卖');
		}else{
			$this->set('title', '今日'.$cat.'特卖');
		}

		$this->set('lists', $lists);
		$this->set('cat', $cat);
		$this->set('midcat', $midcat);
		$this->set('all_goods_cat', $all_goods_cat);
		$this->set('stat', D('promotion')->getStat());
	}

	//订户订阅信息列表，第一次进入配置页面
	function subscribe(){

		$this->set('title', '订阅特卖通知');

		$device_id = @$_GET['device_id'];
		$platform = @$_GET['platform'];
		if(!$device_id || !valid($device_id, 'device_id') || !in_array($platform, array('ios','android'))){
			$this->set('error', '系统初始化错误，请升级至最新版本！');
		}else{

			//首次指向订阅设置
			$setting = D('subscribe')->getSetting($device_id, $platform);
			if(!$setting){
					$this->redirect('/subscribe/subscribeSetting?platform='.$platform.'&device_id='.$device_id);
			}
			$this->set('device_id', $device_id);
			$this->set('platform', $platform);

			//读取订阅消息
		}
	}

	//订阅设置
	function subscribeSetting(){

		$this->set('title', '订阅特卖通知');

		$device_id = @$_GET['device_id'];
		$platform = @$_GET['platform'];
		if(!$device_id || !valid($device_id, 'device_id') || !in_array($platform, array('ios','android'))){
			$this->set('error', '系统初始化错误，请升级至最新版本！');
		}else{
			$this->set('device_id', $device_id);
			$this->set('platform', $platform);
		}

		$sess_id = D('subscribe')->sessCreate();
		if(!$sess_id){
			$this->set('error', '发生错误，请返回上一界面，重新进入！');
		}else{
			$this->set('sess_id', $sess_id);
		}

		$setting = D('subscribe')->getSetting($device_id, $platform);
		D('subscribe')->sessInit($sess_id, $setting);

		$this->set('all_goods_cat', $all_goods_cat = D('promotion')->getCatConfig(true));
		$this->set('setting', $setting);
	}

	//搜索结果
	function search(){

		$k = $_GET['k'];
		if(!$k){
			$this->set('error', '请重新输入关键词!');
		}else{
			$promo_goods = array();

			//模板需要用到常量
			D('promotion')->db('promotion.queue_promo');
			D('promotion')->db('promotion.goods');

			$promo_goods = D('search')->promo($k);

			$this->set('promo_goods', $promo_goods);
			$this->set('keyword', $k);

			if(!$promo_goods){
				$this->set('error', '暂无符合条件的特卖！');
			}
		}
	}
}
?>