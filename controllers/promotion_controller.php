<?php
//特卖页面
class PromotionController extends AppController {

	var $name = 'Promotion';
	var $components = array('Pagination');
	var $cacheAction = array('cat'=> MINUTE);

	//特卖分类商品列表
	function cat($cat, $midcat=''){

		if(!$cat)
			$cat = '服装鞋子';
		else
			$cat = urldecode($cat);

		$all_goods_cat = D('promotion')->getCatConfig(true);
		$cond = array();
		$cond['cat'] = $cat;
		if($midcat){
			$midcat = urldecode($midcat);
			$cond['subcat'] = D('promotion')->midcat2subcat($midcat);

			//临时屏蔽成人内容
			if($key = array_search('女睡衣内衣', $cond['subcat'])){
				unset($cond['subcat'][$key]);
			}
			if($key = array_search('男睡衣内衣', $cond['subcat'])){
				unset($cond['subcat'][$key]);
			}
			if($key = array_search('女裤子', $cond['subcat'])){
				unset($cond['subcat'][$key]);
			}
			if($key = array_search('成人用品', $cond['subcat'])){
				unset($cond['subcat'][$key]);
			}
		}else{
			//临时屏蔽成人内容
			//$cond['subcat'] = "not in ('女睡衣内衣', '女裤子', '男裤子', '男睡衣内衣')";
		}

		$lists = D('promotion')->getList($this->Pagination, $cond, C('comm', 'h5_promo_cat_goods_pre_page'), false);

		if($midcat){
			$this->set('title', '今日'.$midcat.'特卖');
		}else{
			$this->set('title', '今日'.$cat.'特卖');
		}

		$this->set('lists', $lists);
		$this->set('cat', $cat);
		$this->set('midcat', $midcat);
		$this->set('all_goods_cat', $all_goods_cat);
		$this->set('stat', D('promotion')->getStat());
	}

	//搜索结果
	function search(){

		$this->set('title', '特卖搜索结果');

		$k = $_GET['k'];
		//屏蔽成人用户搜索关键词
		if(strpos($k,'成人用品')!==false||strpos($k,'情趣')!==false||strpos($k,'神油')!==false||strpos($k,'自慰')!==false||strpos($k,'性用品')!==false){
			$k = false;
		}
		if(!$k){
			$this->set('error', '关键词无效，请重新输入关键词!');
		}else{
			$promo_goods = array();

			//模板需要用到常量
			D('promotion')->db('promotion.queue_promo');
			D('promotion')->db('promotion.goods');

			$promo_goods = D('search')->promo($k);

			$this->set('promo_goods', $promo_goods);
			$this->set('keyword', $k);

			if(!$promo_goods){
				$this->set('error', '暂无符合条件的特卖！');
			}
		}
	}

	//制造h5 referer头部进行淘宝hack跳转
	function jump(){

		if(!$_GET['t'])die('参数错误');

		$this->layout = 'hint';
		$this->set('title', '商品跳转中');
		$this->set('tlink', $_GET['t']);
	}
}
?>