<?php
//H5订阅信息处理后端
class ajaxSubscribeController extends AppController {

	var $name = 'ajaxSubscribe';
	var $components = array('Pagination');

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
	function getUpAblum(){

		$device_id = $_GET['device_id'];
		$platform = @$_GET['platform'];
		if(!valid($device_id, 'device_id') || !in_array($platform, array('ios','android'))){
			$this->_error('请安装最新版本应用程序！');
		}

		$lists = D('ablum')->getNewAblum($device_id, $platform, true);
		$this->_rendAblumList($lists);
	}

	//向下查询旧专辑，算法改良，应该是上次看过的
	function getDownAblum(){

		$device_id = $_GET['device_id'];
		$platform = $_GET['platform'];
		if(!valid($device_id, 'device_id') || !in_array($platform, array('ios','android'))){
			$this->_error('请安装最新版本应用程序！');
		}

		$lists = D('ablum')->getOldAblum($device_id, $platform);
		$this->_rendAblumList($lists);
	}

	//添加或删除专辑收藏
	function cangToggle(){

		$ablum_id = intval($_GET['ablum_id']);
		$device_id = $_GET['device_id'];
		$platform = $_GET['platform'];
		$action = $_GET['action'];
		if(!$ablum_id || !valid($device_id, 'device_id') || !in_array($platform, array('ios','android'))){
			$this->_error('请安装最新版本应用程序！');
		}

		$detail = D('subscribe')->detail($device_id, $platform);
		if(!$detail){
			$this->_error('请重启应用重试！');
		}

		if($action == 'add'){
			$ret = D('cang')->add($device_id, $platform, $ablum_id);
		}else{
			$ret = D('cang')->del($device_id, $platform, $ablum_id);
		}

		if($ret)
			$this->_success();
		else
			$this->_error('系统错误，请重试！');
	}

	//读取收藏专辑列表
	function cangList(){

		$device_id = $_GET['device_id'];
		$platform = $_GET['platform'];
		if(!valid($device_id, 'device_id') || !in_array($platform, array('ios','android'))){
			$this->_error('请安装最新版本应用程序！');
		}

		$lists = D('cang')->getList($this->Pagination, array('account'=>$device_id, 'channel'=>$platform, 'status'=>1), 4);

		if($lists){
			$this->_rendAblumList($lists, 'cang');
		}else{
			if($_GET['page']>1){
				$this->_error('无更多收藏！');
			}else{
				$this->_error('您暂无收藏，点击 <img src="'.MY_STATIC_URL.'/img/app/p-cang.png" width="20" height="20" align="absmiddle" /> 即可收藏!');
			}
		}
	}

	private function _rendAblumList($lists=array(), $mode='index'){

		$data = array();
		$ablum_ids = array();
		if(isset($_GET['width']) && intval($_GET['width'])>0){
			$height = intval((intval($_GET['width'])-10)*0.625);
			$height = 'height:'.$height.'px';
		}else{
			$height = '';
		}

		foreach($lists as $list){

			if(@$list['more']){
				$more = '<a class="more" ref="ablum_'.$list['id'].'" href="javascript:void(0)">more</a>';
			}else{
				$more = '';
			}

			if($mode == 'cang'){
				$selected = ' cang-selected';
			}else{
				$selected = false;
				if(D('cang')->has($_GET['device_id'], $_GET['platform'], $list['id'])){
					$selected = ' cang-selected';
				}
			}

			$cang = '<a class="cang'.$selected.'" href="javascript:void(0)" onclick="cang(this, '.$list['id'].')">cang</a>';

			$data[] = array(
				'html'=>'<li>
				<a href="jump:'.promoUrl($list['sp'], 0, $list['link']).'">
					<div class="cover" id="ablum_'.$list['id'].'" style="'.$height.'"><img id="ablum_'.$list['id'].'_img" src="'.uploadImageUrl($list['cover_1']).'" width="100%"/></div>
					<dl><dd class="title"><span>'.tagLogo($list['sp'], 'width="100%"').'</span>'.$list['title'].'</dd>
					<dd class="brand"></dd></dl></a>'.$more.$cang.'</li>',
				'ablum_id'=>$list['id']
			);

		}

		$this->_success($data);
	}
}
?>