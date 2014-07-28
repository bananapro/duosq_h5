<?php
//H5订阅信息处理后端
class ajaxSubscribeController extends AppController {

	var $name = 'ajaxSubscribe';

	/**
	 * 实时根据关键词进行品牌匹配
	 * @return [type] [description]
	 */
	function suggestBrand() {

		$keyword = trim(@$_REQUEST['k']);

		if(valid($keyword, 'url')){

			D('log')->action(1510, 0, array('data1'=>'url', 'data4'=>$keyword));
			$this->_success(array('content'=>'<li><div>不支持网址，请输入关键词</div></li>'));
		}else{
			D('log')->action(1510, 1, array('data1'=>'url', 'data4'=>$keyword));
		}

		if(strlen($keyword) > 100){
			$this->_success(array('content'=>'<li><div>关键词过长</div></li>'), true);
		}

		$suggest = D('brand')->search(array('name_search' => "like %{$keyword}%"), 5);

		if($suggest){
			$ret = '';
			foreach($suggest as $n_k){
				$ret .= '<li onclick="selectBrand('.$n_k['id'].',\''.D('brand')->getName($n_k['id']).'\')"> &nbsp; &nbsp;'.D('brand')->getName($n_k['id']).'<i>选择该品牌</i></li>';
			}
			$this->_success(array('content'=>$ret), true);
		}else{
			$this->_success(array('content'=>'<li onclick="suggestClear()"><div><font class="ico ico-warning"></font>找不到相关品牌!</div><i>X</i></li>'), true);
		}
	}

	//保存订阅会话信息
	function saveOption(){

		$sess_id = $_GET['sess_id'];
		$option = $_GET['option'];
		$value = $_GET['value'];
		$action = $_GET['action'];

		if(!$sess_id || !$option || !$action || !D('subscribe')->sessCheck($sess_id)){
			$this->_error('网络故障100，请返回重试！');
		}

		$ret = D('subscribe')->sessUpdate($sess_id, $option, $value, $action);
		if($ret)
			$this->_success('订阅信息保存成功');
		else
			$this->_error('网络故障101，请重试！');
	}

	//提交APP订阅
	function saveSetting(){

		//IP速控
		if(D('speed')->subscribe()){
			$this->_error('您的操作太频繁，请过10分钟再尝试！');
		}

		$sess_id = $_GET['sess_id'];
		$device_id = $_GET['device_id'];
		$platform = @$_GET['platform'];
		$push_token = @$_GET['push_token'];

		if(!D('subscribe')->sessCheck($sess_id)){
			$this->_error('网络故障200，请退出应用重试！');
		}

		if(!valid($device_id, 'device_id') || !valid($push_token, 'push_token')  || !in_array($platform, array('ios','android'))){
			$this->_error('请确认手机设置-通知权限后，请退出应用重试！');
		}

		$ret = D('subscribe')->sessSave($sess_id, $device_id, $platform);
		if(!$ret){
			$this->_error('网络故障202，请退出应用重试！');
		}else{
			$this->_success();
		}
	}
}
?>