<?php
//H5促销商品处理后端
class ajaxPromotionController extends AppController {

	var $name = 'ajaxPromotion';
	var $components = array('Pagination');
	var $layout = 'ajax';

	//特卖列表
	function cat(){

	}

	//9.9列表
	function cat9(){

		D('promotion')->db('promotion.queue_promo');

		$cond = array();
		$cond['type'] = \DB\QueuePromo::TYPE_9;

		if(@$_GET['category']){

			$config = C('comm', 'category_9');
			$config_cat = $config[urldecode($_GET['category'])];
			if($config_cat){
				if(is_array($config_cat)){
					$cond['subcat'] = $config_cat;
				}else{
					$cond['cat'] = explode(',', $config_cat);
				}
			}
		}

		$lists = D('promotion')->getList($this->Pagination, $cond, 8, false);

		$this->layout = 'ajax';

		if($lists){
			$this->set('lists', $lists);
		}else{
			echo 'empty';die();
		}
	}
}
?>