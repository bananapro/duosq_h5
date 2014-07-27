<?php
class AppController extends Controller {

	var $helpers = array('Pagination', 'Javascript', 'Global', 'Ajax', 'Cache');
	var $uses = array(); //不使用默认的model，全部数据访问使用数据层DAL()
	var $cacheAction = array(); //$_GET:del_cache 参数清理缓存| 调试模式也会自动清除缓存
	var $loginValide = 0;
	var $filterParam = true; //底层过滤所有入参

	function beforeFilter() {

		parent::beforeFilter();
		$this->set('title', '特卖订阅');
		header('Content-Type: text/html; charset=UTF-8');

		if ($this->loginValide && !D('myuser')->isLogined()) {
			if ($this->action == 'index' && $this->name == 'Default') $this->redirect('/Login');
			else $this->flash('您尚未登陆多省钱，或已经超时，请重新登陆!', '/', 5);
		}
	}

	//自动识别ajax
	function setAjax() {

		if ($this->isAjax()) {
			$this->layout = 'ajax';
			// Add UTF-8 header for IE6 on XPsp2 bug
			header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
			header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
			header("P3P: CP=CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR");
		}
	}

	function isAjax() {

		if (env('HTTP_X_REQUESTED_WITH') != null) {
			return env('HTTP_X_REQUESTED_WITH') == "XMLHttpRequest";
		} else {
			return false;
		}
	}

	//判断是否jsonp请求
	function isJsonp() {

		$jsoncallback = isset($_REQUEST['jsoncallback']) ? $_REQUEST['jsoncallback'] : '';
		return $jsoncallback;
	}

	function setFlash($msg, $status = 0) {

		$_SESSION['Message']['flash'] = $msg;
		$_SESSION['Message']['flash_status'] = $status;
	}

	function checkFlash() {

		if (isset($_SESSION['Message']['flash']) && $_SESSION['Message']['flash']) return true;
		else return false;
	}

	//格式化字符串参数
	function _fStr($string=''){

		if(!$string)return '';
		$string = strip_tags($string);
		if(preg_match('/select|inert|update|delete|\'|\/\*|\*|\.\.\/|\.\/|UNION|into|load_file|outfile/i', $string)){

			D('log')->action(1800, 1, array('data1'=>$string, 'data2'=>$_SERVER['REQUEST_URI'], 'data4'=>json_encode($_GET), 'data5'=>json_encode($_POST)));
			$string = '';
		}
		return $string;
	}

	//格式化数字参数
	function _fNum($num=0){
		return intval($num);
	}

	//接口返回成功信息
	function _success($message = '', $force_api = false) {

		if ($message === '') $message = '操作成功!';

		if (!DEBUG) {
			if ($this->isAjax() || $this->isJsonp() || $force_api) {
				if ($this->isJsonp()) {
					$this->_jsonpReturn($message, 1);
				} else {
					echo json_encode(array('message' => $message, 'status' => 1));
				}
			} else {
				$this->flash($message, '/', 5);
			}
		} else {
			pr(array('message' => $message, 'status' => 1));
		}
		die();
	}

	//接口返回错误信息
	function _error($message = '', $force_api = false) {

		if (!$message) $message = '系统发生错误，请重试!';

		if (!DEBUG) {

			if ($this->isAjax() || $force_api) {
				if ($this->isJsonp()) {
					$this->_jsonpReturn($message, 0);
				} else {
					echo json_encode(array('message' => $message, 'status' => 0));
				}
			} else {
				$this->flash($message, '/', 5);
			}
		} else {
			pr(array('message' => $message, 'status' => 0));
		}
		die();
	}

	/**
	 * jsonp格式返回数据，登录时跨域post提交用
	 * @param array $data 需返回的数据数组
	 * @param string $info 需返回的信息
	 * @param int $status 需返回的状态
	 */
	function _jsonpReturn($message = '', $status = '') {

		header("Content-Type:application/x-javascript; charset=utf-8");
		echo htmlentities($_REQUEST['jsoncallback']) . '(' . json_encode(array('message' => $message, 'status' => $status)) . ')';
		exit;
	}

	function appError($type, $message){

		if(!DEBUG) //调试模式将会抛出错误
			$this->flash('您查找的链接不存在，系统将自动返回首页', '/', 5);
	}
}
?>
