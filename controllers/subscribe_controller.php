<?php
//订阅页面
class SubscribeController extends AppController {

	var $name = 'Subscribe';

	//消息列表，第一次进入配置页面
	function index(){

		$this->set('title', '最新特卖通知');

		$device_id = @$_GET['device_id'];
		$platform = @$_GET['platform'];
		$push_token = @$_GET['push_token'];
		if(!$device_id || !valid($device_id, 'device_id') || !valid($push_token, 'push_token') || !in_array($platform, array('ios','android'))){
			$this->set('error', '<div class="notice">请在<font class="purple">手机设置-通知中心</font> 打开通知<br />强制退出应用后，重新打开</div>');
		}else{

			//首次指向订阅设置
			$setting = D('subscribe')->getSetting($device_id, $platform);
			if(!$setting){
					$this->redirect('/subscribe/setting?platform='.$platform.'&device_id='.$device_id.'&push_token='.$push_token.'&first_time=1');
			}
			$this->set('device_id', $device_id);
			$this->set('platform', $platform);

			//读取订阅消息
			$messages = D('subscribe')->getMessageList($device_id, $platform, '', C('comm', 'subscribe_display_num_limit_app_cell'));
			if($messages){
				$ids = array();
				foreach($messages as $message){
					$ids[] = $message['id'];
				}
				D('subscribe')->markMessageOpened($device_id, $platform, join(',', $ids));
			}
			$this->set('messages', $messages);
		}
	}

	//订阅设置
	function setting(){

		$this->set('title', '特卖订阅设置');

		$device_id = @$_GET['device_id'];
		$platform = @$_GET['platform'];
		$push_token = @$_GET['push_token'];
		if(!$device_id || !valid($device_id, 'device_id') || !valid($push_token, 'push_token') || !in_array($platform, array('ios','android'))){
			$this->set('error', '<div class="notice">请在手机 <font class="purple">设置-通知中心</font> 打开通知<br />强制退出应用后，重新打开</div>');
		}else{
			$this->set('device_id', $device_id);
			$this->set('platform', $platform);
			$this->set('push_token', $push_token);
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

		$default_midcat = array();

		if(@$_GET['default_cat']){
			$default_midcat = D('promotion')->midcat($_GET['default_cat']);
		}

		if(@$_GET['default_midcat']){
			$default_midcat = array($_GET['default_midcat']);
		}

		$this->set('default_midcat', $default_midcat);

		//新订阅或者订阅禁止状态，允许直接提交
		if( !$setting || $setting['status'] == \DB\Subscribe::STATUS_STOP){
			$this->set('enable_submit', true);
		}
	}
}
?>