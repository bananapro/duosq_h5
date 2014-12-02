<?php
//特卖页面
class PromotionController extends AppController {

	var $name = 'Promotion';
	var $components = array('Pagination');
	var $cacheAction = array('cat'=> MINUTE);

	//发现首页
	function index(){

		//特卖分类各出一副图
		$config = C('comm', 'category');

		D()->db('promotion.queue_promo');
		$first_temai = array();
		foreach($config as $category_name => $subcat_config){
			$cond = array();
			if(is_array($subcat_config)){
				foreach ($subcat_config as $subcat_name => $subcat_condition) {
					$cond['subcat'] = array_merge(@(array)$cond['subcat'], $subcat_condition);
				}
			}else{
				$cond['cat'] = explode(',', $subcat_config);
			}

			$cond['type'] = \DB\QueuePromo::TYPE_DISCOUNT;
			$lists = D('promotion')->getList($this->Pagination, $cond, 1, false);
			$first_temai[$category_name] = $lists[0];
		}

		$this->set('first_temai', $first_temai);
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
	function cat($category='', $category_sub=''){

		$category = urldecode($category);
		$category_sub = urldecode($category_sub);

		//map跳转APP旧版分类到新分类
		$cat2jiu = array('服装鞋子'=>'女装', '家居日用'=>'居家', '箱包配饰'=>'鞋包饰', '美妆个护'=>'美妆', '母婴用品'=>'母婴', '美食生鲜'=>'美食', '家用电器'=>'数码', '手机数码'=>'数码');
		if(isset($cat2jiu[$category])){
			$category = $cat2jiu[$category];
		}

		if($category_sub){
			$this->set('title', '今日'.$category_sub.'特卖');
		}elseif($category){
			$this->set('title', '今日'.$category.'特卖');
		}else{
			$this->set('title', '今日精选特卖');
		}
		$this->set('category', $category);
		$this->set('category_sub', $category_sub);
	}

	//9.9分类商品列表
	function cat9($category=''){

		if($category){
			$category = urldecode($category);
			$this->set('title', '9.9包邮 - '.$category);
		}else{
			$this->set('title', '9.9包邮特惠');
		}
		$this->set('category', $category);
	}

	//女人街分类导航
	function catNvRenNav($nv_category=''){

		if($nv_category){
			$nv_category = urldecode($nv_category);
			$this->set('title', '女人街 - '.$nv_category);
		}else{
			$this->set('title', '女人街分类导航');
		}
		$this->set('nv_category', $nv_category);
	}

	//女人街分类商品列表
	function catNvRen($subcat, $tag=''){

		if(!$subcat)$this->flash('参数错误，系统自动返回首页', '/', 2);

		$subcat = urldecode($subcat);
		$tag = urldecode($tag);

		$this->set('subcat', $subcat);
		$this->set('tag', $tag);
		$conf = D('promotion')->nvRenSubcat2conf($subcat, $tag);

		if($conf){
			if($subcat && !$tag){
				$this->set('title', '女人街·'.$conf['nv_category'].'分类');
				$this->set('nv_category', $conf['nv_category']);
			}else{
				$this->set('title', $conf['nv_category'].'·'.$conf['nv_cat']);
				$this->set('nv_cat', $conf['nv_cat']);
				$this->set('nv_category', $conf['nv_category']);
			}
		}else{
			if($subcat && !$tag){
				$this->set('title', $subcat.'分类');
			}else{
				$this->set('title', $subcat.'·'.$tag);
			}
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

		if(!$goods['shop_id']){
			$succ = D('promotion')->updateGoodsDeepInfo($sp, $goods_id, $goods['url_id']);
			if($succ)$goods = array_merge($goods, $succ);
		}

		$this->set('promo', $promo);
		$this->set('goods', $goods);
		$this->set('title', $goods['name']);
	}
}
?>