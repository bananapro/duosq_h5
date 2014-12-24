<?php
//H5促销商品处理后端
class ajaxPromotionController extends AppController {

	var $name = 'ajaxPromotion';
	var $components = array('Pagination');
	var $layout = 'ajax';

	//特卖列表
	function cat(){

		D('promotion')->db('promotion.queue_promo');

		$cond = array();
		$cond['type'] = \DB\QueuePromo::TYPE_DISCOUNT;

		if(@$_GET['category']){

			$config = C('comm', 'category');
			$config_category = $config[urldecode($_GET['category'])];
			if($config_category){
				if(is_array($config_category)){
					if(isset($_GET['category_sub']) && $_GET['category_sub']){
						$cond['subcat'] = $config_category[$_GET['category_sub']];
					}else{
						foreach ($config_category as $subcat_name => $subcat_condition) {
							$cond['subcat'] = array_merge(@(array)$cond['subcat'], $subcat_condition);
						}
					}
				}else{
					$cond['cat'] = explode(',', $config_category);
				}
			}
		}

		$lists = D('promotion')->getList($this->Pagination, $cond, 10, false);
		$this->layout = 'ajax';

		if($lists){
			$this->set('lists', $lists);
		}else{
			echo 'empty';die();
		}
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

		$lists = D('promotion')->getList($this->Pagination, $cond, 10, false);

		$this->layout = 'ajax';

		if($lists){
			$this->set('lists', $lists);
		}else{
			echo 'empty';die();
		}
	}

	//女人街列表
	function catJie($subcats, $tags=''){

		if(!$subcats){echo 'empty';die();}

		D('promotion')->db('promotion.queue_promo');

		$cond = array();
		//$cond['type'] = \DB\QueuePromo::TYPE_JIE;

		$subcats = urldecode($subcats);
		$tags = urldecode($tags);

		$cond['subcat'] = explode('|', $subcats);

		if(@$tags && $tags != '_'){
			$cond['tag'] = explode('&', $tags);
		}

		$lists = D('promotion')->getTagList($this->Pagination, $cond, 10);
		//pr($lists);die();
		$this->layout = 'ajax';

		if($lists){
			$this->set('lists', $lists);
			$this->set('subcats', $cond['subcat']);
			$this->set('tags', @(array)$cond['tag']);
		}else{
			echo 'empty';die();
		}
	}
}
?>