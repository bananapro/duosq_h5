<?php

//在移动端导航栏调用自定义按钮
function navButton($value, $url){

	if(!$value || !$url)return;
	if(getBrowser() == 'android'){
		return "<script type='text/javascript'>WebDetailActivity.setTitleAndButton('', '{$value}', '{$url}')</script>";
	}else{
		return "<input type='hidden' id='setting_title' value='{$value}'><input type='hidden' id='setting_url' value='{$url}'/>";
	}
}

//转换外链
function promoUrl($sp, $goods_id, $url, $tc='app'){

	//IOS跳转loading图标有BUG，修正后去除此处
	if(getBrowser() == 'ios' && getVersion()<2)
		return $url;

	if(!$sp || (!$goods_id && !$url)){
		return "javascript:alert('链接错误，请稍后尝试！');\" target='_self'";
	}

	//if(isset($_GET['tc']))$tc = $_GET['tc'];

	//获取跳转驱动
	if(D('go')->getDriver($sp)){

		//sclick模式|taobao无跟单模式|B2C跟单跳转
		$url = MY_WWW_URL . "/go/{$sp}?tc={$tc}&t=".urlencode($url);
	}

	return $url;
}

//专辑列表链接
function albumUrl($album_id){

	if(!$album_id)return;
	return MY_HOMEPAGE_URL . '/promotion/album/'.$album_id;
}

//商品详情页
function goodsUrl($sp, $goods_id){

	if(!$sp || !$goods_id)return;
	return MY_HOMEPAGE_URL . '/promotion/detail/'.$sp.'/'.$goods_id;
}

//获取APP版本
function getVersion(){

	$ver = intval(@$_GET['app_ver']);
	return $ver;
}

//获取device_id
function device_id(){

	$device_id = @$_GET['device_id']?$_GET['device_id']:$_COOKIE['device_id'];
	if(!valid($device_id, 'device_id'))return false;
	return $device_id;
}

//获取platform
function platform(){

	$platform = @$_GET['platform']?$_GET['platform']:$_COOKIE['platform'];
	if(!in_array($platform, array('ios','android')))return false;
	return $platform;
}

//获取push_token
function pushToken(){

	$push_token = @$_GET['push_token']?$_GET['push_token']:$_COOKIE['push_token'];
	if(!in_array($push_token, array('ios','android')))return false;
	return $push_token;
}
?>