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
?>