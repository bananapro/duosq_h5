<?php
//订阅页面
class SubscribeController extends AppController {

	var $name = 'Subscribe';
	var $setting = array();
	var $device_id = '';
	var $platform = '';

	function beforeFilter(){

		$device_id = @$_GET['device_id'];
		$platform = @$_GET['platform'];

		if(!$device_id || !valid($device_id, 'device_id') || !in_array($platform, array('ios','android'))){
			die('请下载最新应用');
		}

		$setting = D('subscribe')->getSetting($device_id, $platform);
		if(!$setting){
			D('subscribe')->settingAutoCreated($device_id, $platform);
			$setting = D('subscribe')->getSetting($device_id, $platform);
		}

		if(!$setting){
			die('系统错误，请重启APP');
		}else{
			$this->setting = $setting;
		}

		$this->device_id = $device_id;
		$this->platform = $platform;
		$this->set('device_id', $device_id);
		$this->set('platform', $platform);
	}

	//消息列表，第一次进入配置页面
	function index(){

		$this->set('title', '最新特卖通知');

		$push_token = @$_GET['push_token'];
		if(!valid($push_token, 'push_token')){
			$push_token = '';
			$push_token = D('subscribe')->detail($device_id, $platform, 'push_token');
		}

		if(!$push_token){
			$this->set('warning', '请在“设置-通知”开启通知，以免错过特卖通知！');
		}

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

		if($this->device_id == '71E47D29-ED86-4569-9916-FFF589E114F0' || $this->device_id == 'CEC0F585-C16E-4B9F-B1C8-8316F1251EA8' || $this->device_id == '9ABA5F5F-AEBB-4B00-ADCF-9A287160C509'){
			$this->action = 'index_new';
			$this->indexNew();
		}
	}

	//新版订阅中心首页
	function indexNew(){

		$lists = D('ablum')->getNewAblum($this->device_id, $this->platform);
		$this->set('lists', $lists);
		$this->set('title', '最新特卖');
	}

	//订阅设置
	function setting(){

		$this->set('title', '特卖订阅设置');

		$push_token = @$_GET['push_token'];

		if(!valid($push_token, 'push_token')){
			$push_token = '';
			$push_token = D('subscribe')->detail($device_id, $platform, 'push_token');
		}

		if(!$push_token){
			$this->set('warning', '请在“设置-通知”开启通知，以免错过特卖通知！');
		}

		$this->set('push_token', $push_token);

		$sess_id = D('subscribe')->sessCreate();
		if(!$sess_id){
			$this->set('error', '发生错误，请返回上一界面，重新进入！');
		}else{
			$this->set('sess_id', $sess_id);
		}

		$setting = D('subscribe')->getSetting($this->device_id, $this->platform);
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

		if($this->device_id == '71E47D29-ED86-4569-9916-FFF589E114F0' || $this->device_id == 'CEC0F585-C16E-4B9F-B1C8-8316F1251EA8' || $this->device_id == '9ABA5F5F-AEBB-4B00-ADCF-9A287160C509'){
			$this->action = 'setting_new';
			$this->settingNew();
		}
	}

	//新版订阅设置
	function settingNew(){

		$push_token = @$_GET['push_token'];

		if(!valid($push_token, 'push_token')){
			$push_token = '';
			$push_token = D('subscribe')->detail($this->device_id, $this->platform, 'push_token');
		}

		if(!$push_token){
			$this->set('warning', '请在“设置-通知”开启通知，以免错过特卖通知！');
		}

		$setting = D('subscribe')->getSetting($this->device_id, $this->platform);
		if(!$setting){
			$this->set('error', '请下载最新应用');
		}

		//新用户自动创建setting配置
		$this->set('setting', $setting);
		$this->set('push_token', $push_token);
		$this->set('title', '订阅设置');
	}

	//我的收藏列表
	function cang(){

		$this->set('title', '我的收藏');
	}
}
?>