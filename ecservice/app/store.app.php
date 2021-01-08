<?php

class StoreApp extends MallbaseApp
{
	function index()
    {
		list($check, $loginResult) = TRUE;//parent::_checkLogin();
		if($check === FALSE)
		{
			$result = array(
				'status' => 'FAILED',
				'errorMsg' => $loginResult['errorMsg']
			);
		}
		else
		{
			$post = parent::_getPostData();
			
			$store = array();
			if($post['id'])
			{
				$store_mod = &m('store');
				
				$store = $store_mod->get(array(
					'conditions' => 'store_id=' . $post['id'],
					'fields' => 'store_name, store_logo, store_banner, store_id',
				));
				empty($store['store_logo']) && $store['store_logo'] = Conf::get('default_store_logo');
				
				$store['store_logo'] = SITE_URL . '/' . $store['store_logo'];
				$store['store_banner'] = SITE_URL . '/' . $store['store_banner'];

				$collects = db()->getOne("SELECT count(*) FROM ".DB_PREFIX."collect c WHERE type='store' AND item_id=".$post['id']);

				if($collects >= 10000) {
					$collects = ($collects/10000).'万';
				}
				
				$store['collects'] = $collects;
				
				$store['slides'] = $this->_getStoreSlides($post['id']);
			}

			$result = array(
				'status'=> 'SUCCESS',
				'title' => "店铺首页",
				'retval'=> $store
			);
		}
		echo json_encode($result);
    }
	
	function _getStoreSlides($store_id)
	{
		$slides = array(
			array(
				'image_url' => SITE_URL.'/mobile/static/images/201703101150531370.jpeg',
				'link_url'  => ''
			),
			array(
				'image_url' => SITE_URL.'/mobile/static/images/201703101150535137.jpeg',
				'link_url'  => ''
			),
			array(
				'image_url' => SITE_URL.'/mobile/static/images/201703101150539284.jpeg',
				'link_url'  => ''
			)
		);
		
		return $slides;	
	}
	
	function goods()
	{
		list($check, $loginResult) = TRUE;//parent::_checkLogin();
		if($check === FALSE)
		{
			$result = array(
				'status' => 'FAILED',
				'errorMsg' => $loginResult['errorMsg']
			);
		}
		else
		{
			$post = parent::_getPostData();
			
			$goods_list = array();
			if($post['id'])
			{
				$conditions = 'store_id='.$post['id'];
				if($_POST['keyword']) {
					$conditions .= ' AND goods_name LIKE "'.$_POST['keyword'].'%"';
				}
				
				$order = 'g.goods_id DESC';
				if($_POST['sort'] && in_array($_POST['sort'], array('add_time', 'sales', 'recommended'))) {
					$order = $_POST['sort'] . ' DESC';
				}
				
				$goods_mod = &m('goods');

				$page = $this->_get_page(10);
				$goodsList = $goods_mod->find(array(
					'conditions' => $conditions,
					'join'      => 'has_goodsstatistics',
					'limit'     =>$page['limit'],
					'fields' => 'goods_name, default_image, g.goods_id, price, cate_id,sales',
					'order' => $order,
					'count'     =>true,
				));
				foreach($goodsList as  $key => $goods) {
					empty($goods['default_image']) && $goods['default_image'] = Conf::get('default_goods_image');
					$goodsList[$key]['default_image'] = SITE_URL . '/' . $goods['default_image'];
				}
				
				$goodsList = array_values($goodsList);
				
				$result = array(
					'status'=> 'SUCCESS',
					'title' => "店铺首页推荐商品",
					'retval'=> $goodsList
				);
			}
		}
		echo json_encode($result);
	}
	
	function category()
	{
		list($check, $loginResult) = TRUE;//parent::_checkLogin();
		if($check === FALSE)
		{
			$result = array(
				'status' => 'FAILED',
				'errorMsg' => $loginResult['errorMsg']
			);
		}
		else
		{
			$post = parent::_getPostData();
			
			$goods_list = array();
			if($post['id'])
			{
				$gcategory_mod =& bm('gcategory', array('_store_id' => $post['id']));
				$gcategories = $gcategory_mod->get_list(-1, true);
				import('tree.lib');
				$tree = new Tree();
				$tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');
				
				$result = array(
					'status'=> 'SUCCESS',
					'title' => "店铺分类",
					'retval'=> $tree->getArrayList(0)
				);
			}
		}
		
		echo json_encode($result);
	}
}
?>
