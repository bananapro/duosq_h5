<?php
//特卖页面
class PromotionController extends AppController {

	var $name = 'Promotion';
	var $components = array('Pagination');
	var $cacheAction = array('cat'=> MINUTE);

	//发现首页
	function index(){

		$this->set('title', '发现特卖');
	}

	//专辑特卖列表页
	function album($album_id){

		if($album_id){
			$album = D('album')->detail($album_id);
			//判断过期
			if($album['status']){
				$lists = D('album')->getGoods($album_id);
				$this->set('lists', $lists);
				$this->set('album', $album);
			}
		}

		$this->set('title', $album['brand_names'] . $album['title']);
	}

	//特卖分类商品列表
	function cat($cat, $midcat=''){

		if(!$cat)
			$cat = '服装鞋子';
		else
			$cat = urldecode($cat);

		//map跳转到9.9分页
		$cat2jiu = array('服装鞋子'=>'女装', '家居日用'=>'居家', '箱包配饰'=>'包包配饰', '美妆个护'=>'美妆', '母婴用品'=>'母婴', '美食生鲜'=>'美食', '家用电器'=>'数码家电', '手机数码'=>'数码家电');
		$jiu = $cat2jiu[$cat];
		$this->redirect(urlWithParam($_GET, '/promotion/cat9?category='.urlencode($jiu)));

		$all_goods_cat = D('promotion')->getCatConfig(true);
		$cond = array();
		$cond['cat'] = $cat;
		if($midcat){
			$midcat = urldecode($midcat);
			$cond['subcat'] = D('promotion')->midcat2subcat($midcat);

			//临时屏蔽成人内容
			if($key = array_search('成人用品', $cond['subcat'])){
				unset($cond['subcat'][$key]);
			}
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
	}

	//9.9分类列表
	function cat9(){
		if($_GET['category']){
			$this->set('title', '9.9包邮 - '.urldecode($_GET['category']));
		}else{
			$this->set('title', '9.9包邮特惠');
		}
	}

	//手机快速充值
	function charge(){
		$this->set('title', '自动匹配最优惠手机充值');
	}

	//搜索结果
	function search(){

		//直接跳转S8
		$this->set('title', '特卖搜索结果');

		$k = $_GET['k'];
		//屏蔽成人用户搜索关键词
		if(strpos($k,'成人用品')!==false||strpos($k,'情趣')!==false||strpos($k,'神油')!==false||strpos($k,'自慰')!==false||strpos($k,'性用品')!==false){
			$k = false;
		}
		if(!$k){
			$this->set('error', '关键词无效，请重新输入关键词!');
		}else{

			$this->redirect('http://s8.taobao.com/search?pid=mm_36614165_4544181_15182721&unid=0&q='.urlencode($k).'&taoke_type=1');
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
	//已废除
	function jump(){

		if(!$_GET['t'])die('参数错误');

		$this->layout = 'hint';
		$this->set('title', '商品跳转中');
		$this->set('tlink', $_GET['t']);
	}

	//商品详情页
	function detail($sp, $goods_id){

		if(!$sp || !$goods_id)$this->flash('参数错误，系统自动返回首页', '/', 2);

		$promo = D('promotion')->promoDetail($sp, $goods_id);
		$goods = D('promotion')->goodsDetail($sp, $goods_id);
		if(!$goods)$this->redirect('/', 301);

		if(!$goods['name_long']){
			$succ = D('promotion')->updateGoodsDeepInfo($sp, $goods_id, $goods['url_id']);
			if($succ)$goods = array_merge($goods, $succ);
		}

		$this->set('promo', $promo);
		$this->set('goods', $goods);
		$this->set('title', $goods['name']);
	}
}
?>