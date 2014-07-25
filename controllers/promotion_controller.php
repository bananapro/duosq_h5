<?php
//移动特卖分类页面
class PromotionController extends AppController {

	var $name = 'Promotion';
	var $components = array('Pagination');
	var $cacheAction = array('cat'=> MINUTE);

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
}
?>