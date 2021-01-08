<?php

class OrderApp extends MemberbaseApp
{
    function index()
    {
	}
	
	// 创建订单
	function createOrder()
	{
		list($check, $loginResult) = parent::_checkLogin(TRUE);
		if($check === FALSE)
		{
			$result = array(
				'isSuccess' => FALSE,
				'returnMsg' => $loginResult['returnMsg'],
				'errorCode' => 'ORDER014',
			);
		}
		else
		{
			$post = parent::_getPostData();
			$post = $post['data'];
			
			$buyer_id = $loginResult['resultInfo']['user_id'];
			$buyer_name = $loginResult['resultInfo']['user_name'];
			$buyer_email = $loginResult['resultInfo']['email'];
						
			// 对数据进行简单过滤
			foreach($post as $k => $v)
			{
				if(is_int($v)) $post[$k] = intval($v);
				elseif(is_string($v) || is_float($v)) $post[$k] = trim($v);
			}
			
			/**********************订单数据验证开始**************************************/
			$checkPostErrorMsg = FALSE;
			
			$order_mod = &m('order');
			if($order = $order_mod->find(array('conditions' => "out_trade_sn='{$post['tradeNo']}'", 'fields' => 'order_id'))) {
				$checkPostErrorMsg = '订单号（交易流水号）已存在，请不要重复提交';
				$errorCode = 'ORDER013';
			}
			
			else
			{
				// 订单商品数据有误
				if(!isset($post['sku']) || empty($post['sku']) || !is_array($post['sku'])) {
					$checkPostErrorMsg = '缺少sku参数';
				}
				else
				{
					$post['goods_amount'] = 0;
					$goods_mod = &m('goods');
					$goodsspec_mod = &m('goodsspec');
					$store_mod = &m('store');
					foreach($post['sku'] as $key => $val) 
					{				
						if(!$goodsspec = $goodsspec_mod->get($val['skuId'])) {
							$checkPostErrorMsg = sprintf('订单商品[%s]不存在，请核实后再下单', $val['skuId']);
							$errorCode = 'ORDER007';
							break;
						}
						else {
							
							$goods = $goods_mod->get(array(
								'conditions' => 'goods_id='.$goodsspec['goods_id'], 
								'fields' =>'goods_id, goods_name, default_image, store_id, if_show, closed, spec_name_1, spec_name_2'
							));
							if(!$goods || !$goods['if_show'] || $goods['closed']) {
								$checkPostErrorMsg = sprintf('订单商品[SKUID:%s]不存在，请核实后再下单', $val['skuId']);
								$errorCode = 'ORDER004';
								break;
							}
							else
							{
								// 以下数据给插入订单商品使用(BEGIN)
								$post['sku'][$key]['goods_id'] = $goods['goods_id'];
								$post['sku'][$key]['goods_name'] = $goods['goods_name'];
								$post['sku'][$key]['goods_image'] = $goods['default_image'];
								$post['sku'][$key]['specification'] = $this->_getSpecification($goods, $goodsspec); 
								// END
								
								$store = $store_mod->get(array('conditions' => 'store_id=' . $goods['store_id'], 'fields' => 'store_id, store_name, state'));
								if(!$store || ($store['state'] != 1)) {
									$checkPostErrorMsg = '该店铺已经被关闭，请核实后再下单';
									break;
								}
								else
								{
									if(isset($post['seller_id']) && !empty($post['seller_id'])) {
										if($post['seller_id'] != $goods['seller_id']) {
											$checkPostErrorMsg = '不能同时购买多家（两家及以上）店铺的商品，请核实后再下单';
											break;
										}
									}
									else 
									{
										$post['seller_id'] 		= $store['store_id'];
										$post['seller_name'] 	= $store['store_name'];
									}								
									if($goods['store_id'] == $buyer_id) {
										$checkPostErrorMsg = sprintf('订单商品[SKUID:%s]为自己店内商品，不能购买', $val['skuId']);
										break;
									}
								}
							}
							
							if($goodsspec['stock'] < $val['num']) {
								$checkPostErrorMsg = sprintf('订单商品[SKUID:%s]库存不足', $val['skuId']);
								break;
							}
						}
						
						if($val['num'] <= 0) {
							$checkPostErrorMsg = sprintf('订单商品[SKUID:%s]购买数量有误', $val['skuId']);
							break;
						}
						if($val['price'] < 0) {
							$checkPostErrorMsg = sprintf('订单商品[SKUID:%s]购买单价非法', $val['skuId']);
							break;
						}
						
						$post['goods_amount'] += floatval($val['price'] * $val['num']);
					}
				}
				
				if($checkPostErrorMsg == FALSE)
				{
					// 收件人
					if(!$post['name']) {
						$checkPostErrorMsg = '收件人不能为空';
					}
					// 收件人手机号
					elseif(!$post['mobile']) {
						$checkPostErrorMsg = '收件人手机号不能为空';
					}
					// 收件人邮箱
					elseif(!$post['email'] || !is_email($post['email'])) {
						$checkPostErrorMsg = '收件人邮箱不能为空、或收件人邮箱格式不对';
					}
					// 收货地址（省）
					elseif(!trim($post['provinceId'])) {
						$checkPostErrorMsg = '收货地址（省）不能为空';
						$errorCode = 'ORDER001_02';
					}
					// 收货地址（市）
					elseif(!$post['cityId']) {
						$checkPostErrorMsg = '收货地址（市）不能为空';
						$errorCode = 'ORDER001_01';
					}
					// 收货地址（区/县）
					elseif(!$post['countyId']) {
						$checkPostErrorMsg = '收货地址（区/县）不能为空';
						$errorCode = 'ORDER001_02';
					}
					// 收货地址（街道/镇）
					//elseif(!$post['townId']) {
						//$checkPostErrorMsg = '收货地址（街道/镇）不能为空';
					//}
					// 收货地址（详细地址）
					elseif(!$post['address']) {
						$checkPostErrorMsg = '收货地址（详细地址）不能为空';
						$errorCode = 'ORDER001_02';
					}
					// 运费
					elseif(!$post['freight']) {
						$checkPostErrorMsg = '运费不能为空';
						$errorCode = 'ORDER016';
					}
					
					if($post['invoiceState'] == 1)
					{
						// 发票类型
						if(!in_array($post['invoiceType'], array(0,1,2))) { // 测试工具有传0，故加上
							$checkPostErrorMsg = '发票类型非法';
							$errorCode = 'ORDER003_01';
						}
						// 发票抬头
						elseif(!$post['companyName']) {
							$checkPostErrorMsg = '发票抬头不能为空';
							$errorCode = 'ORDER003_02';
						}
						elseif(!$post['invoiceContent']) {
							$checkPostErrorMsg = '发票内容不能为空';
							$errorCode = 'ORDER003_02';
						}
						
						// 纳税人识别号
						//elseif(!$post['taxNo']) {
							//$checkPostErrorMsg = '纳税人识别号不能为空';
						//}
					}
				}
			}
			/**********************订单数据验证结束**************************************/
			
			if($checkPostErrorMsg !== FALSE)
			{
				$result = array(
					'isSuccess' => FALSE,
					'returnMsg' => $checkPostErrorMsg,
					'errorCode' => $errorCode,
				);
			}
			else
			{
				/******************** 插入订单操作 ***************************/
				
				// 1） 订单数据
				$order_type =& ot('normal');
				$base_info = array(
					'order_sn' 		=> 	$order_type->_gen_order_sn($post['seller_id']),
					'out_trade_sn'	=>	$post['tradeNo'],
					'type'			=>	'material',
					'extension'		=>	'normal',
					'seller_id'		=>	$post['seller_id'],
					'seller_name'	=>  $post['seller_name'],
					'buyer_id'		=>	$buyer_id,
					'buyer_name'	=>	$buyer_name,
					'buyer_email'	=>	$buyer_email,
					'status'		=>  ORDER_PENDING,
					'add_time'		=>	gmtime(),
					'postscript'	=>	$post['remark'],
					//'payment_id'	=>	'',
					'payment_name'	=>	$post['payment'],
					//'payment_code'	=>	'',
					
					'goods_amount'	=>	$post['goods_amount'],
					'order_amount'	=>	$post['goods_amount'] + $post['freight'],
				);
				$order_model =& m('order');
        		$order_id    = $order_model->add($base_info);

        		if (!$order_id)
        		{
            		/* 插入基本信息失败 */
            		$result = array(
						'isSuccess' => FALSE,
						'returnMsg' => '订单信息插入失败',
					);
        		}
				else
				{
					// 2）订单配送数据
					$consignee_info = array(
						'order_id'		=>	$order_id,
						'consignee'		=>	$post['name'],
						'region_id'		=>	1,// 暂不考虑该值，因为接口要求传的地址数据为字符串，无法跟系统地区数据匹配，所以该值无法获取
						'region_name'	=>	$post['provinceId'].' '.$post['cityId'].' '.$post['countyId'].' '.$post['townId'],
						'address'		=>	$post['address'],
						'zipcode'		=>	'',//$post['zip'],
						'phone_tel'		=>	$post['phone'],
						'phone_mob'		=>	$post['mobile'],
						'shipping_id'	=>	0,
						'shipping_name'	=>	'快递',
						'shipping_fee'	=>	floatval($post['freight']),
					);
					$order_extm_model =& m('orderextm');
					$order_extm_model->add($consignee_info);
					
					// 3）插入订单商品
					$goods_items = $this->_getOrderGoods($order_id, $post);
					$order_goods_model =& m('ordergoods');
        			$order_goods_model->add(addslashes_deep($goods_items)); //防止二次注入
					
					// 4）插入发票信息
					if($post['invoiceState']) {
						
						$orderinvoice_mod = &m('orderinvoice');
						
						/*$invoiceContent = '';
						if($post['invoiceContent'] == 1) {
							$invoiceContent = '明细';
						}
						elseif($post['invoiceContent'] == 3) {
							$invoiceContent = '电脑配件';
						}
						elseif($post['invoiceContent'] == 19) {
							$invoiceContent = '耗材';
						}
						elseif($post['invoiceContent'] == 22) {
							$invoiceContent = '办公用品';
						}*/
						
						// 兼容处理
						$invoiceInfo = array(
							'invoiceType'	=> $post['invoiceType'],
							'invoiceTitle' => $post['companyName'],
							'invoiceContent' => $post['invoiceContent'],
							
							//'consigneeName' => $post['name'],
							//'consigneeAddress' => $post['provinceId'].$post['cityId'].$post['countyId'].$post['townId'].$post['address'],
							//'consigneeMobileNum' => $post['mobile'],
							//'taxNo'				=> $post['taxNo'],
						);
						
						// 保存订单发票数据
						if($orderinvoice_mod->createOrderInvoice($invoiceInfo, $order_id)) {
							$order_model->edit($order_id, array('invoiceStatus' => 1));
						}
					}
					
					$createOrder = array(
						'orderId'	=>	$base_info['order_sn'],
						'sku'		=>	$this->_getOrderGoodsLine($order_id),
						'amount'	=>	$base_info['order_amount'],
						'freight'	=>	$consignee_info['shipping_fee'],
						'errorCode' =>  '',
					);
					$result = array_merge(array(
							'isSuccess' => TRUE,
							'returnMsg' => "创建订单完成",
    					), $createOrder
					);
				}
			}
		}
		
		echo json_encode($result);
	}
	
	function _getOrderGoods($order_id = 0, $post = array())
	{
		$result = array();
		
		if(isset($post['sku']) && is_array($post['sku']))
		{
			foreach($post['sku'] as $key => $val)
			{
				$result[] = array(
					'order_id'		=>	$order_id,
					'goods_id'		=>	$val['goods_id'],
					'goods_name'	=>	$val['goods_name'],
					'spec_id'		=>	$val['skuId'],
					'specification'	=>	$val['specification'],
					'price'			=>	$val['unitPrice'],
					'quantity'		=>	$val['num'],
					'goods_image'	=>	$val['goods_image'],
				);
			}
		}
		return $result;
	}
	
	// 获取订单商品行记录（兼容插入订单，和查询单个订单明细接口）
	function _getOrderGoodsLine($order_id = 0, $flag = '')
	{
		$result = array();
		
		if($order_id) {
			$ordergoods_mod = &m('ordergoods');
			$ordergoods = $ordergoods_mod->find(array(
				'conditions'	=>	'order_id='.$order_id,
				'fields'		=>	'rec_id, order_id, spec_id, quantity, price, goods_id, goods_name',
				'order'			=>	'rec_id ASC'
			));
			
			if($ordergoods)
			{
				// 查询单个订单明细接口
				if(in_array($flag, array('getOrderDetail')))
				{
					$order_mod = &m('order');
					$goods_mod = &m('goods');
					$orderExtm_mod = &m('orderextm');
					$order = $order_mod->get(array('conditions' => 'order_id='.$order_id, 'fields' => 'order_sn, add_time'));
					$consignee_info = $orderExtm_mod->get(array('conditions' => 'order_id='.$order['order_id'], 'fields' => 'shipping_fee'));
					
					foreach($ordergoods as $key => $val)
					{
						$goods = $goods_mod->get(array('conditions' => 'goods_id='.$val['goods_id'], 'fields' => 'brand'));
						
						// 不含运费分摊的情况
						$skuAmt = $val['price'] * $val['quantity'];
						
						// 含运费分摊的情况（接口文档要求：商品总金额=商品数量*商品单价（含运费分摊），但本人认为不太合理）
						$originalAmount = $this->_getOrderItemAmount($ordergoods);
						if(($originalAmount > 0) && ($consignee_info['shipping_fee'] > 0)) {
							$skuAmt = $skuAmt + ($skuAmt/$originalAmount) * $consignee_info['shipping_fee'];
						}
						
						$result[] = array(
							'orderItemId'	=>	$val['rec_id'],
							'orderId'		=>	$order['order_sn'],
							'commdtyName'	=>	$val['goods_name'],
							'commdtyCode'	=> 	$val['spec_id'],
							'skuNum'		=>	$val['quantity'],
							'unitPrice'		=>	$val['price'],
							'skuAmt'		=>	$skuAmt,
							'brandName'		=>	$goods['brand'],
							'hopeArriveTime'=>	local_date('Y-m-d H:i:s', $order['add_time'] + 5 * 24 * 3600), // 固定5日后（同arriveData）
						);
					}
				}
				// 获取订单状态接口
				elseif(in_array($flag, array('getOrderStatus')))
				{
					$order_mod = &m('order');
					$order = $order_mod->get(array('conditions' => 'order_id='.$order_id, 'fields' => 'status'));
					foreach($ordergoods as $key => $val)
					{
						$result[] = array(
							'orderItemId'	=>	$val['rec_id'],
							'skuId'			=>	$val['spec_id'],
							'statusName'	=>	$order['status'],
						);
					}
				}
				// 插入订单接口
				else
				{
					foreach($ordergoods as $key => $val)
					{
						$result[] = array(
							//'orderItemId'	=>	$val['rec_id'],
							'skuId'			=>	$val['spec_id'],
							'num'			=>	$val['quantity'],
							//'price'			=>	$val['price'],
							'arriveData'	=>	local_date('Y-m-d H:i:s', gmtime() + 5 * 24 * 3600), // 固定5日后
						);
					}
				}
			}
		}
		
		return $result;
	}
	
	// 获取原始订单商品的价格总额
	function _getOrderItemAmount($ordergoods = array())
	{
		$result = 0;
		
		foreach($ordergoods as $goods) {
			$result += $goods['price'] * $goods['quantity'];
		}
		return $result;
	}
	
	// 获取规格数据并格式化
	function _getSpecification($goods, $goodsspec)
	{
		$specification = '';
		
		if($goods['spec_name_1']) {
			$specification .= $goods['spec_name_1'].':'.$goodsspec['spec_1'];
		}
		if($goods['spec_name_2']) {
			if($specification) $specification .= ' '.$goods['spec_name_2'].':'.$goodsspec['spec_1'];
			else $specification .= $goods['spec_name_2'].':'.$goodsspec['spec_2'];// 此种情况应该不存在
		}
		return $specification;
	}
	
	// 获取单个订单信息
	function getOrderDetail()
	{
		list($check, $loginResult) = parent::_checkLogin(TRUE);
		if($check === FALSE)
		{
			$result = array(
				'getOrderDetail' => array(
					'success' => false,
					'errorMsg' => $loginResult['errorMsg']
				)
			);
		}
		else
		{
			$post = file_get_contents("php://input");
			$post = json_decode($post, true);
			
			$buyer_id = $loginResult['resultInfo']['user_id'];
			$companyName = $loginResult['resultInfo']['companyName'];
			$orderId  = $post['orderId'];
			
			$order_mod = &m('order');
			$order = $order_mod->get("order_sn='{$orderId}'");
			
			if(!$order) {
				$result = array(
					'getOrderDetail' => array(
						'success' => false,
						'errorMsg' => '订单不存在，请返回检查'
					)
				);
			}
			elseif($order['buyer_id'] != $buyer_id) {
				$result = array(
					'getOrderDetail' => array(
						'success' => false,
						'errorMsg' => '对不起，您无权查看此订单信息'
					)
				);
			}
			else
			{
				$orderExtm_mod = &m('orderextm');
				$consignee_info = $orderExtm_mod->get(array('conditions' => 'order_id='.$order['order_id'], 'fields' => 'phone_mob,region_name, address'));
				
				$getOrderDetail = array(
					'orderId'			=>	$order['order_sn'],
					'companyName'		=>	$companyName,
					'orderAmt'			=>	$order['order_amount'],
					'createTime'		=>	local_date('Y-m-d H:i:s', $order['add_time']),
					'accountName'		=>	$order['buyer_name'],
					'receiverTel'		=>	$consignee_info['phone_mob'],
					'receiverAddress'	=>	$consignee_info['region_name'].$consignee_info['address'],
					'orderItemList'		=> 	$this->_getOrderGoodsLine($order['order_id'], 'getOrderDetail'),
				);
				
				$result = array(
					//'resultCode' => 'SUCCESS',
					//'resultInfo' => $getOrderDetail
					'getOrderDetail' => array(
						'success' => $getOrderDetail
					)
				);
			}
		}
		
		echo json_encode($result);
	}
	// 获取订单状态接口
	/*
	function getOrderStatus()
	{
		list($check, $loginResult) = parent::_checkLogin(TRUE);
		if($check === FALSE)
		{
			$result = array(
				'getOrderStatus' => array(
					'success' => false,
					'errorMsg' => $loginResult['errorMsg']
				)
			);
		}
		else
		{
			$post = file_get_contents("php://input");
			$post = json_decode($post, true);
			
			$buyer_id = $loginResult['resultInfo']['user_id'];
			$orderId  = $post['orderId'];
			 
			$order_mod = &m('order');
			$order = $order_mod->get("order_sn='{$orderId}'");
			
			if(!$order) {
				$result = array(
					'getOrderStatus' => array(
						'success' => false,
						'errorMsg' => '订单不存在，请返回检查'
					)
				);
			}
			elseif($order['buyer_id'] != $buyer_id) {
				$result = array(
					'getOrderStatus' => array(
						'success' => false,
						'errorMsg' => '对不起，您无权查看此订单信息'
					)
				);
			}
			else
			{
				$getOrderStatus = array(
					'orderId'			=>	$order['order_sn'],
					'orderStatus'		=>	$order['status'],
					'orderItemInfoList'	=>	$this->_getOrderGoodsLine($order['order_id'], 'getOrderStatus'),
					//'errorCode'			=>	'', // 成功时为空
				);
				
				$result = array(
					//'resultCode' => 'SUCCESS',
					//'resultInfo' => $getOrderStatus
					'getOrderStatus' => array(
						'success' => $getOrderStatus
					)
				);
			}
		}
		
		echo json_encode($result);
	}
	*/
	
	
	// 取消订单
	function deleteRejectOrder()
	{
		list($check, $loginResult) = parent::_checkLogin(TRUE);
		if($check === FALSE)
		{
			$result = array(
				'deleteRejectOrder' => array(
					'success' => false,
					'errorMsg' => $loginResult['errorMsg']
				)
			);
		}
		else
		{
			$post = file_get_contents("php://input");
			$post = json_decode($post, true);
			
			$buyer_id = $loginResult['resultInfo']['user_id'];
			$orderId  = $post['orderId']; 
			
			$deleteRejectOrder = array();
			
			$order_mod = &m('order');
			
			$order_info = $order_mod->get(array(
				//'conditions' => "order_id={$order_id} AND buyer_id={$buyer_id} AND status " . db_create_in(array(ORDER_PENDING, ORDER_SUBMITTED)),
				'conditions' => "order_sn='{$orderId}' AND buyer_id={$buyer_id} AND status " . db_create_in(array(ORDER_PENDING, ORDER_SUBMITTED)),
				'fields' 	 => "order_id"
			));
			if($order_info)
			{
				$order_id = $order_info['order_id'];
				$order_mod->edit($order_id, array('status' => ORDER_CANCELED));
            	if($order_mod->has_error()) {
                	$deleteRejectOrder['unCampSuccess'] = 'N';
            	}
				else
				{
					$deleteRejectOrder['unCampSuccess'] = 'Y';
					
            		/* 加回商品库存 */
            		$order_mod->change_stock('+', $order_id);
				}
			}
			else
			{
				$deleteRejectOrder['unCampSuccess'] = 'N';
			}
			
			$result = array(
				//'resultCode' => 'SUCCESS',
				//'resultInfo' => $deleteRejectOrder
				'deleteRejectOrder' => array(
					'success' => $deleteRejectOrder
				)
			);
		}
		
		echo json_encode($result);
	}
	
	// 获取订单物流详情接口
	function getOrderLogist()
	{
		list($check, $loginResult) = parent::_checkLogin(TRUE);
		if($check === FALSE)
		{
			$result = array(
				'getOrderLogist' => array(
					'success' => false,
					'errorMsg' => $loginResult['errorMsg']
				)
			);
		}
		else
		{
			$post = file_get_contents("php://input");
			$post = json_decode($post, true);
			
			$buyer_id = $loginResult['resultInfo']['user_id'];
			$orderId  = trim($post['orderId']);
			 
			$order_mod = &m('order');
			$order = $order_mod->get("order_sn='{$orderId}'");
			
			if(!$order) {
				$result = array(
					'getOrderLogist' => array(
						'success' => false,
						'errorMsg' => '订单不存在，请返回检查'
					)
				);
			}
			elseif($order['buyer_id'] != $buyer_id) {
				$result = array(
					'getOrderLogist' => array(
						'success' => false,
						'errorMsg' => '对不起，您无权查看此订单信息'
					)
				);
			}
			else
			{
				$getOrderLogist = array(
					'orderId'				=>	$order['order_sn'],
					'orderItemId'			=>	'',// 订单行号 非必填
					'skuId'					=>	$post['skuId'],
					'orderLogisticStatus'	=>	$this->_orderLogisticStatus($order['order_id'], $order),
					'shippingTime'			=>	local_date('Y-m-d H:i:s', $order['ship_time']),
					'receiveTime'			=>	local_date('Y-m-d H:i:s', $order['finished_time'])
				);
				
				$result = array(
					//'resultCode' => 'SUCCESS',
					//'resultInfo' => $getOrderLogist
					'getOrderLogist' => array(
						'success' => $getOrderLogist
					)
				);
			}
		}
		
		echo json_encode($result);
	}
	
	// 使用快递接口（快递100）读取数据
	function _orderLogisticStatus($order_id = 0, $order_info = array())
	{
		$result = array();
		
		if(!$order_info) {
			
			// 通过order_id 获取
			// TODO
		}
		else
		{
			// 如果快递公司或者快递单号为空，则返回空
			if(!empty($order_info['invoice_no']) && !empty($order_info['express_company'])){
				
				// 从订单ID查询快递公司和快递单号
				$kuaiInfo =  $this->_hook('on_query_express', array('com' => $order_info['express_company'], 'nu' => $order_info['invoice_no']));
		
				if($kuaiInfo && $kuaiInfo['status'] == 1) {
				
					foreach($kuaiInfo['data'] as $key => $val)
					{
						$result[] = array(
							'operateState' 	=> $val['context'],
							'operatorTime'	=> $val['time'],
						);
					}
				}
			}
		}
		
		return $result;
	}
	
	// 获取物流运费接口（暂时不考虑不同地区运费的情况，因为本身系统没有这个功能）
	function getShipCarriage()
	{
		list($check, $loginResult) = parent::_checkLogin();
		if($check === FALSE)
		{
			$result = array(
				'getShipCarriage' => array(
					'success' => false,
					'errorMsg' => $loginResult['errorMsg']
				)
			);
		}
		else
		{
			$post = file_get_contents("php://input");
			$post = json_decode($post, true);
			
			// 对数据进行检查
			$store_id = 0; 
			$quantity = 0; // 统计购买件数
			$shippingInfo = array(); // 保存店铺配送费用
			$checkPostErrorMsg = FALSE;
			if(!isset($post['skuIds']) || empty($post['skuIds']) || !is_array($post['skuIds'])) {
				$result = array(
					'getShipCarriage' => array(
						'success' => false,
						'errorMsg' => '缺少skuIds参数'
					)
				);
			}
			else
			{
				$goods_mod = &m('goods');
				$goodsspec_mod = &m('goodsspec');
				foreach($post['skuIds'] as $key => $val)
				{				
					if(!$goodsspec = $goodsspec_mod->get($val['skuId'])) {
						$checkPostErrorMsg = sprintf('商品[SKUID:%s]不存在，请返回检查', $val['skuId']);
						break;
					}
					$goods = $goods_mod->get(array('conditions' => 'goods_id='.$goodsspec['goods_id'], 'fields' => 'store_id'));
					if(!$store_id) {
						$store_id = $goods['store_id'];
					}
					elseif($goods['store_id'] != $store_id) {
						$checkPostErrorMsg = '不能同时获取多家店铺的商品的物流费用';
						break;
					}
					
					$quantity += $val['piece'];
				}
			}
			
			if($checkPostErrorMsg != FALSE)
			{
				$result = array(
					'getShipCarriage' => array(
						'success' => false,
						'errorMsg' => $checkPostErrorMsg
					)
				);
			}
			else
			{
				$shipping_mod = &m('shipping');
				$shipping = $shipping_mod->get(array('conditions' => 'store_id='.$store_id.' AND enabled=1', 'order' => 'sort_order ASC'));
				
				$shipping_fee = 0;
				if($shipping) {
					$shipping_fee = $shipping['first_price'] + ($quantity - 1) * $shipping['step_price'];
				}
				
				$result = array(
					'getShipCarriage' => array(
						'success' => array('freightFare' => $shipping_fee)
					)
				);
			}
			
		}
		
		echo json_encode($result);
	}
	
	// 补开发票接口
	function confirmInvoice()
	{
		list($check, $loginResult) = parent::_checkLogin(TRUE);
		if($check === FALSE)
		{
			$result = array(
				'confirmInvoice' => array(
					'success' => false,
					'errorMsg' => $loginResult['errorMsg']
				)
			);
		}
		else
		{
			$post = file_get_contents("php://input");
			$post = json_decode($post, true);
			
			// 字段兼容处理
			if(isset($post['applyForInvoiceReqDTO']['title'])){
				$post['applyForInvoiceReqDTO']['invoiceTitle'] = $post['applyForInvoiceReqDTO']['title'];
				unset($post['applyForInvoiceReqDTO']['title']);
			}
			if(isset($post['applyForInvoiceReqDTO']['address'])){
				$post['applyForInvoiceReqDTO']['consigneeAddress'] = $post['applyForInvoiceReqDTO']['address'];
				unset($post['applyForInvoiceReqDTO']['address']);
			}
			
			$buyer_id = $loginResult['resultInfo']['user_id'];
			
			$checkPostErrorMsg = FALSE;
			if(!isset($post['orderInfoDTO']) || empty($post['orderInfoDTO']) || !is_array($post['orderInfoDTO'])) {
				$result = array(
					'confirmInvoice' => array(
						'success' => false,
						'errorMsg' => '缺少orderInfoDTO参数'
					)
				);
			}
			else
			{
				$order_mod = &m('order');
				$orderinvoice_mod = &m('orderinvoice');
				foreach($post['orderInfoDTO'] as $key => $val)
				{				
					if(!$order = $order_mod->get(array('conditions' => "order_sn='{$val['gcOrderNo']}'", 'fields' => 'order_id, buyer_id, status, seller_id'))) {
						$checkPostErrorMsg = sprintf('订单gcOrderNo:%s]不存在，请返回检查', $val['gcOrderNo']);
						break;
					}
					elseif($order['buyer_id'] != $buyer_id) {
						$checkPostErrorMsg = sprintf('无法对该订单[订单号：%s]补开发票（没有权限）', $val['gcOrderNo']);
						break;
					}
					elseif(!in_array($order['status'], array(ORDER_ACCEPTED, ORDER_SHIPPED, ORDER_FINISHED))) {
						$checkPostErrorMsg = sprintf('无法对该订单[订单号：%s]申请发票（订单尚未付款）', $val['gcOrderNo']);
						break;
					}
					// 如果已经开过发票，则不允许在开
					elseif($orderinvoice_mod->get(array('conditions' => 'order_id='.$order['order_id'].' AND status != "CLOSED"'))) {
						$checkPostErrorMsg = sprintf('该订单[订单号：%s]已经开过发票，不能重复提交', $val['gcOrderNo']);
						break;
					}
				}
				
				// 对POST的其他数据进行判断
				if(!in_array($post['applyForInvoiceReqDTO']['invoiceType'], array(2,6))) {
					$checkPostErrorMsg = '开票类型非法';
				}
				elseif(empty($post['applyForInvoiceReqDTO']['consigneeName'])) {
					$checkPostErrorMsg = '发票收件人信息不能为空';
				}
				elseif(empty($post['applyForInvoiceReqDTO']['address'])) {
					$checkPostErrorMsg = '发票收件人详细地址不能为空';
				}
				elseif(empty($post['applyForInvoiceReqDTO']['consigneeMobileNum'])) {
					$checkPostErrorMsg = '发票收件人手机号码不能为空';
				}
				elseif(empty($post['applyForInvoiceReqDTO']['taxNo'])) {
					$checkPostErrorMsg = '纳税人识别号不能为空';
				}
				
				if(in_array($post['applyForInvoiceReqDTO']['invoiceType'], array(2)) && empty($post['applyForInvoiceReqDTO']['title'])) {
					$checkPostErrorMsg = '开普通发票时，发票抬头必填';
				}
			}
			
			if($checkPostErrorMsg != FALSE)
			{
				$result = array(
					'confirmInvoice' => array(
						'success' => false,
						'errorMsg' => $checkPostErrorMsg
					)
				);
			}
			else
			{
				$order_mod = &m('order');
				$orderinvoice_mod = &m('orderinvoice');
				
				foreach($post['orderInfoDTO'] as $key => $val)
				{		
					$order = $order_mod->get(array('conditions' => "order_sn='{$val['gcOrderNo']}'"));
					
					// 处理订单发票保存事宜（如果有订单有开票记录，且订单发票状态是关闭的， 则删除之，在添加新的开票信息）
					$orderinvoice = $orderinvoice_mod->get('order_id='.$val['gcOrderNo']);
					if($orderinvoice['status'] == 'CLOSED') {
						$orderinvoice_mod->drop($orderinvoice['order_invoice_id']);
					}
					if($orderinvoice_mod->createOrderInvoice($post['applyForInvoiceReqDTO'], $order))
					{
						$order_mod->edit($order['order_id'], array('invoiceStatus' => 1));
						
						$orderItem = array(
							'gcOrderNo'		=> 	$order['order_sn'],
							'orderNo'		=>  $order['out_trade_sn'],
							'status'		=>	order_status($order['status']),
							'skuId'			=>	'',
							'skuName'		=>	'',
						);
						
						$orderinvoice = $orderinvoice_mod->get('order_id='.$order['order_id']);
						$orderItem = array_merge($orderItem, array(
							'invoiceAmount' =>  $orderinvoice['invoiceAmount'],
							'invoiceType'   =>  $orderinvoice['invoiceType'] == 2 ? '增值税普通发票' : '增值税专用发票',
							'invoiceStatus' =>  $orderinvoice['status'] == 'APPLYING' ? 1 : 0,
						));					
						$allOrderItemList[] = $orderItem;
					}
					
					$result = array(
						'confirmInvoice' => array(
							'success' => array('allOrderItemList' => $allOrderItemList)
						)
					);
				}
			}
			
		}
		
		echo json_encode($result);
	}
	
}

?>
