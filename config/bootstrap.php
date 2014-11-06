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

		if($sp == 'tmall' || $sp == 'taobao' || $sp == 'ju'){
			//淘宝移动端hack跳转跟单
			if($goods_id){
				//$url = MY_WWW_URL .'/item-'.$sp.'-'.$goods_id.'?tc='.$tc;
				$url = MY_WWW_URL . "/go/{$sp}?tc={$tc}&t=".urlencode($url);
			}else{
				//sclick模式|无跟单模式
				$url = MY_WWW_URL . "/go/{$sp}?tc={$tc}&t=".urlencode($url);
			}
		}else{
			$url = MY_WWW_URL . "/go/{$sp}?tc={$tc}&t=".urlencode($url);
		}
	}

	return $url;
}

//获取APP版本
function getVersion(){

	$ver = intval(@$_GET['app_ver']);
	return $ver;
}
?>