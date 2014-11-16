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

		//自动新建账号
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

		$lists = D('album')->getNewAlbum($this->device_id, $this->platform);
		$this->set('lists', $lists);
		$this->set('title', '最新特卖');
	}

	//新版订阅设置
	function setting(){

		$setting = D('subscribe')->getSetting($this->device_id, $this->platform);
		if(!$setting){
			$this->set('error', '请下载最新应用');
		}

		//新用户自动创建setting配置
		$this->set('setting', $setting);
		$this->set('title', '订阅设置');
	}

	//我的收藏列表
	function cang(){

		$push_token = @$_GET['push_token'];
		if(!valid($push_token, 'push_token')){
			$push_token = '';
			$push_token = D('subscribe')->detail($this->device_id, $this->platform, 'push_token');
		}

		if(!$push_token){
			$this->set('warning', '请在“设置-通知”开启通知，以免错过特卖通知！');
		}

		$this->set('title', '我的收藏');
	}
}
?>