<?php

class GoodsApp extends MallbaseApp
{
	function index()
	{
		list($check, $loginResult) = TRUE; //parent::_checkLogin();
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

			$goods_id = intval($post['id']);
			
			$result = array();
			
			$goods_mod = &m('goods');
			//$goodsspec_mod = &m('goodsspec');
			
			$goods = $goods_mod->get(array('conditions' => "goods_id={$goods_id}", 'fields' => 'goods_id, goods_name, description, price, default_image, store_id, if_show, closed, add_time,brand, cate_id, cate_name, default_spec, delivery_template_id'));
				
			if($goods)
			{
				if($goods['closed'] == 0 && $goods['if_show'] == 1) 
				{
					$goods['default_image'] = $goods['default_image'] ? $goods['default_image'] : Conf::get('default_goods_image');
					if(stripos($goods['default_image'], '//:') == FALSE) {
						$goods['default_image'] = SITE_URL . '/' . $goods['default_image'];
					}
					
					$goodsimage_mod = &m('goodsimage');
					$goodsimage = $goodsimage_mod->find(array(
						'conditions'=> 'goods_id='.$goods_id,
						'fields'	=> 'thumbnail,sort_order',
						'order'		=> 'sort_order ASC'
					));
					
					$swiper = array();
					foreach($goodsimage as $key => $val)
					{
						if(stripos($val['thumbnail'], '//:') == FALSE) {
							$val['thumbnail'] = SITE_URL . '/' . $val['thumbnail'];
						}
						$swiper[] = array('url' => $val['thumbnail']);
					}
					
					$goods['swiper'] = $swiper;
					
					$result = array(
						'status'=> 'SUCCESS',
						'title' => '商品详情',
						'retval'=> $goods,
					);
				}
				else
				{
					
				}
			}
		}
		
		echo json_encode($result);
	}
	
	// 获取商品图片
	function getProductImage()
	{
		list($check, $loginResult) = parent::_checkLogin();
		if($check === FALSE)
		{
			$result = array(
				'isSuccess' => FALSE,
				'returnMsg' => $loginResult['returnMsg']
			);
		}
		else
		{
			$post = parent::_getPostData();
			$post = $post['data'];
			
			$skuIds = $post['sku'];
			
			$productImage = array();
			if($skuIds)
			{ 
				$goodsspec_mod = &m('goodsspec');
				$goodsimage_mod = &m('goodsimage');
				foreach($skuIds as $v)
				{
					$goodsspec = $goodsspec_mod->get('spec_id='.$v['skuId']);
					if($goodsspec)
					{
						$urls = array();
						$goodsimage = $goodsimage_mod->find(array(
							'conditions' 	=> 'goods_id='.$goodsspec['goods_id'],
							'fields'		=> 'thumbnail,sort_order',
							'order'			=> 'sort_order ASC'
						));
						
						$urls[] = array('skuId' => $v['skuId']);
						$i = 0;
						foreach($goodsimage as $image)
						{
							$i++;
							
							if($image['sort_order'] == 1) {
								$primary = 1;
							} elseif($i == 1) {
								$primary = 1;
							} else $primary = 0;
							
							$urls[] = array('primary' => $primary, 'path' => SITE_URL . '/' . $image['thumbnail']);
						}
						
						$productImage[] = array(
							'urls' => $urls
						);
					}
			
					$result = array(
						'isSuccess' => TRUE,
						'returnMsg' => "商品图片信息",
						'result' 	=> $productImage
					);
				}
			}
			
		}
		
		echo json_encode($result);
	}
	
	// 获取商品上下架状态
	function getProductOnShelvesInfo()
	{
		list($check, $loginResult) = parent::_checkLogin();
		if($check === FALSE)
		{
			$result = array(
				'isSuccess' => FALSE,
				'returnMsg' => $loginResult['returnMsg']
			);
		}
		else
		{
			$post = parent::_getPostData();
			$post = $post['data'];
			
			$skuIds = $post['sku'];
			
			$onShelvesList = array();
			if($skuIds)
			{ 
				$goods_mod = &m('goods');
				$goodsspec_mod = &m('goodsspec');
				$store_mod = &m('store');
				foreach($skuIds as $v)
				{
					$goodsspec = $goodsspec_mod->get('spec_id='.$v['skuId']);
					if($goodsspec)
					{
						$goods = $goods_mod->get(array(
							'conditions' 	=> 'goods_id='.$goodsspec['goods_id'],
							'fields'		=> 'goods_id, if_show, closed, store_id',
						));
						if($goods) 
						{
							$store = $store_mod->get(array('conditions' => 'store_id='.$goods['store_id'], 'fields' => 'state'));
							
							$onShelvesList[] = array(
								'skuId' => $v['skuId'],
								'listState' => ($store['state'] && $goods['if_show'] && !$goods['closed']) ? 1 : 0
							);
						}
					}
			
					$result = array(
						'isSuccess' 	=> TRUE,
						'returnMsg' 	=> "商品上下架状态信息",
						'onShelvesList' => $onShelvesList
					);
				}
			}
			
		}
		
		echo json_encode($result);
	}
	
	// 批量查询商品价格
	function queryCountPrice()
	{
		list($check, $loginResult) = parent::_checkLogin();
		if($check === FALSE)
		{
			$result = array(
				'isSuccess' => FALSE,
				'returnMsg' => $loginResult['returnMsg']
			);
		}
		else
		{
			$post = parent::_getPostData();
			$post = $post['data'];
			
			$skuIds = $post['sku'];
			$cityId = $post['cityId'];
			
			$priceList = array();
			if($skuIds)
			{ 
				$goods_mod = &m('goods');
				$goodsspec_mod = &m('goodsspec');
				$store_mod = &m('store');
				foreach($skuIds as $v)
				{
					$goodsspec = $goodsspec_mod->get('spec_id='.$v['skuId']);
					if($goodsspec)
					{
						$goods = $goods_mod->get(array(
							'conditions' 	=> 'goods_id='.$goodsspec['goods_id'],
							'fields'		=> 'goods_id, if_show, closed, store_id, rfPrice',
						));
						if($goods)
						{
							$store = $store_mod->get(array('conditions' => 'store_id='.$goods['store_id'], 'fields' => 'state'));
							
							if($store['state'] && $goods['if_show'] && !$goods['closed'])
							{
								// 如果参考价大于等于单价，则计算折扣
								$discount = '';
								if(($goods['rfPrice'] >= $goodsspec['price']) && ($goods['rfPrice'] > 0)) {
									$discount = round(($goods['rfPrice']-$goodsspec['price'])/$goods['rfPrice']*100, 2) . '%';
								}
								$priceList[] = array(
									'skuId' 	=> $v['skuId'],
									'price' 	=> $goodsspec['price'],
									'discount' 	=> $discount,
								);
							}
							else
							{
								$priceList[] = array(
									'skuId' 	=> $v['skuId'],
									'price' 	=> '',
									'discount' 	=> '',
								);
							}
						}
					}
			
					$result = array(
						'isSuccess' 	=> TRUE,
						'returnMsg' 	=> "商品折扣价格",
						'priceList' 	=> $priceList
					);
				}
			}
			
		}
		
		echo json_encode($result);
	}
	
	// 商品库存查询接口
	function getProductInventory()
	{
		list($check, $loginResult) = parent::_checkLogin();
		if($check === FALSE)
		{
			$result = array(
				'isSuccess' => FALSE,
				'returnMsg' => $loginResult['returnMsg']
			);
		}
		else
		{
			$post = parent::_getPostData();
			$post = $post['data'];
			
			$skuId 		= $post['sku'];
			$num   		= $post['num'];
			$cityId		= $post['cityId'];
			$countyId	= $post['countyId'];
			
			$queryStock = array();
			if($skuId)
			{ 
				$goods_mod = &m('goods');
				$goodsspec_mod = &m('goodsspec');
				$store_mod = &m('store');
				
				$goodsspec = $goodsspec_mod->get('spec_id='.$skuId);
				if($goodsspec)
				{
					$goods = $goods_mod->get(array(
						'conditions' 	=> 'goods_id='.$goodsspec['goods_id'],
						'fields'		=> 'goods_id, if_show, closed, store_id',
					));
					if($goods)
					{
						$store = $store_mod->get(array('conditions' => 'store_id='.$goods['store_id'], 'fields' => 'state'));
							
						$state = '01';
						if($store['state'] && $goods['if_show'] && !$goods['closed']) {
							if($goodsspec['stock'] > 0) 
							{
								if($goodsspec['stock'] >= intval($v['num'])) {
									$state = '00';
								}
								else 
								{
									$state = '03';
								}
							}
							else
							{
								$state = '02';
							}
						}
							
						$queryStock = array(
							'skuId' 	=> $skuId,
							'state'		=> $state
						);
					}
				}
			
				$result = array_merge($queryStock, array(
					'isSuccess' => TRUE,
					'returnMsg' => "商品库存信息",
				));
			}
		}
		
		echo json_encode($result);
	}
	
	// 商品库存状态批量查询接口（一期不接入）
	function bathQueryInventoryStatus()
	{
		list($check, $loginResult) = parent::_checkLogin();
		if($check === FALSE)
		{
			$result = array(
				'isSuccess' => FALSE,
				'returnMsg' => $loginResult['returnMsg']
			);
		}
		else
		{
			$post = parent::_getPostData();
			$post = $post['data'];
			
			$skuIds = $post['skus'];
			$cityId = $post['cityId'];
			
			$queryMpStock = array();
			if($skuIds)
			{ 
				$goods_mod = &m('goods');
				$goodsspec_mod = &m('goodsspec');
				$store_mod = &m('store');
				foreach($skuIds as $v)
				{
					$goodsspec = $goodsspec_mod->get('spec_id='.$v['skuId']);
					if($goodsspec)
					{
						$goods = $goods_mod->get(array(
							'conditions' 	=> 'goods_id='.$goodsspec['goods_id'],
							'fields'		=> 'goods_id, if_show, closed, store_id',
						));
						if($goods)
						{
							$store = $store_mod->get(array('conditions' => 'store_id='.$goods['store_id'], 'fields' => 'state'));
							
							$state = '01';
							if($store['state'] && $goods['if_show'] && !$goods['closed']) {
								if($goodsspec['stock'] > 0) 
								{
									if($goodsspec['stock'] >= intval($v['num'])) {
										$state = '00';
									}
									else 
									{
										$state = '03';
									}
								}
								else
								{
									$state = '02';
								}
							}
							
							$queryMpStock[] = array(
								'skuId' 	=> $v['skuId'],
								'state'		=> $state
							);
						}
					}
			
					$result = array(
						'isSuccess' => TRUE,
						'returnMsg' => "商品库存状态批量查询",
						'result' 	=> $queryMpStock
					);
				}
			}
			
		}
		
		echo json_encode($result);
	}
}

?>
