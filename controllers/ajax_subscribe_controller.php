<?php
//H5订阅信息处理后端
class ajaxSubscribeController extends AppController {

	var $name = 'ajaxSubscribe';
	var $components = array('Pagination');
	var $layout = 'ajax';

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

		if(!valid($device_id, 'device_id') || !in_array($platform, array('ios','android'))){
			$this->_error('请安装最新版本应用程序！');
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

		if(!valid($device_id, 'device_id') || !in_array($platform, array('ios','android'))){
			$this->_error('请安装最新版本应用程序！');
		}

		$ret = D('subscribe')->sessSave($sess_id, $device_id, $platform);
		if(!$ret){
			$this->_error('网络故障202，请退出应用重试！');
		}else{
			D('log')->action(1555, 1, array('data1'=>$platform, 'data2'=>$email, 'data3'=>$sess_id, 'data4'=>'app'));
			$this->_success();
		}
	}

	//直接保存订阅选项
	function saveOptionNew(){

		$device_id = $_GET['device_id'];
		$platform = @$_GET['platform'];
		$option = $_GET['option'];
		$value = $_GET['value'];
		$action = $_GET['action'];

		if(!$option || !$action){
			$this->_error('网络故障300，请返回重试！');
		}

		if(!valid($device_id, 'device_id') || !in_array($platform, array('ios','android'))){
			$this->_error('请安装最新版本应用程序！');
		}

		$ret = D('subscribe')->settingUpdate($device_id, $platform, $option, $value, $action);

		if($ret)
			$this->_success('选项保存成功');
		else
			$this->_error('网络故障301，请重试！');
	}

	//向上查询最新专辑
	function getUpAlbum(){

		$device_id = $_GET['device_id'];
		$platform = @$_GET['platform'];
		if(!valid($device_id, 'device_id') || !in_array($platform, array('ios','android'))){
			$this->_error('请安装最新版本应用程序！');
		}

		$lists = D('album')->getNewAlbum($device_id, $platform, true);
		$this->_rendAlbumList($lists);
	}

	//向下查询旧专辑，算法改良，应该是上次看过的
	function getDownAlbum(){

		$device_id = $_GET['device_id'];
		$platform = $_GET['platform'];
		if(!valid($device_id, 'device_id') || !in_array($platform, array('ios','android'))){
			$this->_error('请安装最新版本应用程序！');
		}

		$lists = D('album')->getOldAlbum($device_id, $platform);
		$this->_rendAlbumList($lists);
	}

	//添加或删除专辑收藏
	function cangToggle(){

		$id = $_GET['id'];
		$type = $_GET['type'];
		$device_id = $_GET['device_id'];
		$platform = $_GET['platform'];
		$action = $_GET['action'];

		if(!$id || !valid($device_id, 'device_id') || !in_array($platform, array('ios','android')) || !in_array($type, array('album','goods'))){
			$this->_error('请安装最新版本应用程序！');
		}

		//加锁防止当天重复提取任务
		$lock = D()->redis('lock')->getlock(\Redis\Lock::LOCK_SUBSCRIBE_CANG, $device_id.':'.$type.':'.$id);
		if(!$lock)die();

		$detail = D('subscribe')->detail($device_id, $platform);
		if(!$detail){
			$this->_error('请重启应用重试！');
		}

		if($action == 'add'){
			$ret = D('cang')->add($device_id, $platform, $type, $id);
		}else{
			$ret = D('cang')->del($device_id, $platform, $type, $id);
		}

		if($ret)
			$this->_success();
		else
			$this->_error('系统错误，请重试！');
	}

	//读取收藏专辑列表
	function cangList($type=''){

		$device_id = $_GET['device_id'];
		$platform = $_GET['platform'];
		if(!valid($device_id, 'device_id') || !in_array($platform, array('ios','android'))|| !in_array($type, array('album','goods'))){
			$this->_error('请安装最新版本应用程序！');
		}

		$lists = D('cang')->getList($this->Pagination, $type, array('account'=>$device_id, 'channel'=>$platform, 'status'=>1), 6);

		if($lists){
			if($type == 'album')
				$this->_rendAlbumList($lists, 'cang');
			else
				$this->_rendGoodsList($lists, 'cang');
		}else{
			if($_GET['page']>1){
				$this->_error('无更多收藏！');
			}else{
				$this->_success(array(array('html'=>'<dl class="no-cang"><dt><img src="http://static.sxedm1.com/assets/m/i/images/wish/activity.png"></dt></dl>')));
			}
		}
	}

	private function _rendAlbumList($lists=array(), $mode='index'){

		$data = array();
		$album_ids = array();
		$view = new View($this);

		foreach($lists as $album){
			$data[] = array('html'=>$view->renderElement('promo_album', array('album'=>$album)), 'album_id'=>$album['id']);
		}

		$this->_success($data);
	}

	private function _rendGoodsList($lists=array()){

		$this->action = '';
		$view = new View($this);

		foreach ($lists as $list) {
			$data[] = array('html'=>$view->renderElement('promo_cat_goods', array('promo'=>$list)));
		}

		$this->_success($data);
	}
}
?>