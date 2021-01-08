<?php

!defined('ROOT_PATH') && exit('Forbidden');

/**
 *    订单类型基类
 *
 *    @author    MiMall
 *    @usage    none
 */
class BaseOrder extends Object
{
    function __construct($params)
    {
        $this->BaseOrder($params);
    }
    function BaseOrder($params)
    {
        if (!empty($params))
        {
            foreach ($params as $key => $value)
            {
                $this->$key = $value;
            }
        }
    }

    /**
     *    获取订单类型名称
     *
     *    @author    MiMall
     *    @return    string
     */
    function get_name()
    {
        return $this->_name;
    }

    /**
     *    获取订单详情
     *
     *    @author    MiMall
     *    @param     int $order_id
     *    @param     array $order_info
     *    @return    array
     */
    function get_order_detail($order_id, $order_info)
    {
        if (!$order_id)
        {
            return array();
        }

        /* 订单基本信息 */
        $data['order_info'] =   $order_info;

        return array('data' => $data, 'template' => 'normalorder.view.html');
    }
    /**
     *    获取该商品类型在购物流程中的表单模板及数据
     *
     *    @author    MiMall
     *    @return    array
     */
    function get_order_form()
    {
        return array();
    }

    /**
     *    处理表单提交上来后的数据，并插入订单表
     *
     *    @author    MiMall
     *    @param     array $data
     *    @return    int
     */
    function submit_order($data)
    {
        return 0;
    }

    /**
     *    响应支付通知
     *
     *    @author    MiMall
     *    @param     int    $order_id
     *    @param     array  $notify_result
     *    @return    bool
     */
    function respond_notify($order_id, $notify_result)
    {
        $model_order =& m('order');
        $where = "order_id = {$order_id}";
        $data = array('status' => $notify_result['target']);
        switch ($notify_result['target'])
        {
            case ORDER_ACCEPTED:
                $where .= ' AND status=' . ORDER_PENDING;   //只有待付款的订单才会被修改为已付款
                $data['pay_time']   =   gmtime();
            break;
            case ORDER_SHIPPED:
                $where .= ' AND status=' . ORDER_ACCEPTED;  //只有等待发货的订单才会被修改为已发货
                $data['ship_time']  =   gmtime();
            break;
            case ORDER_FINISHED:
                $where .= ' AND status=' . ORDER_SHIPPED;   //只有已发货的订单才会被自动修改为交易完成
                $data['finished_time'] = gmtime();
            break;
            case ORDER_CANCLED:                             //任何情况下都可以关闭
                /* 加回商品库存 */
                $model_order->change_stock('+', $order_id);
            break;
        }

        return $model_order->edit($where, $data);
    }

    /**
     *    获取收货人信息
     *
     *    @author    MiMall
     *    @param     int $user_id
     *    @return    array
     */
    function _get_my_address($user_id)
    {
        if (!$user_id)
        {
            return array();
        }
        $address_model =& m('address');

        return $address_model->find(array('conditions'=>'user_id=' . $user_id, 'order' => 'setdefault DESC,addr_id'));
    }
	
	 /**
     *    获取地址信息
     *
     *    @author   Mimall
     *    @param     int $addr_id
     *    @return    array
     */
	function _get_address_info($addr_id)
	{
		if(!$addr_id)
		{
			return array();
		}
		$address_model =& m('address');

        return $address_model->get($addr_id);
	}

    /**
     *    获取配送方式
     *
     *    @author    MiMall
     *    @param     int $store_id
     *    @return    array
     */
    function _get_shipping_methods($store_id)
    {
        if (!$store_id)
        {
            return array();
        }
        $shipping_model =& m('shipping');

        return $shipping_model->find('enabled=1 AND store_id=' . $store_id);
    }

    /**
     *    获取支付方式
     *
     *    @author    MiMall
     *    @param     int $store_id
     *    @return    array
     */
    function _get_payments($store_id)
    {
        if (!$store_id)
        {
            return array();
        }
        $payment_model =& m('payment');

        return $payment_model->get_enabled($store_id);
    }

    /**
     *    生成订单号
     *
     *    @author    MiMall
     *    @return    string
     */
    function _gen_order_sn($ext = '')
    {
        /* 选择一个随机的方案 */
        mt_srand((double) microtime() * 1000000);
        $timestamp = gmtime();
        $y = date('y', $timestamp);
        $z = date('z', $timestamp);
        $order_sn = $y . str_pad($z, 3, '0', STR_PAD_LEFT) . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
		if($ext) $order_sn .= $ext;

        $model_order =& m('order');
        $orders = $model_order->find('order_sn=' . $order_sn);
        if (empty($orders))
        {
            /* 否则就使用这个订单号 */
            return $order_sn;
        }

        /* 如果有重复的，则重新生成 */
        return $this->_gen_order_sn($ext);
    }

    /**
     *    验证收货人信息是否合法
     *
     *    @author    MiMall
     *    @param     array $consignee
     *    @return    void
     */
    function _valid_consignee_info($consignee)
    {
		if($consignee['addr_id']) {
			$consignee = array_merge($consignee, $this->_get_address_info($consignee['addr_id']));
		}
		
        if (!$consignee['consignee'])
        {
            $this->_error('consignee_empty');

            return false;
        }
        if (!$consignee['region_id'])
        {
            $this->_error('region_empty');

            return false;
        }
        if (!$consignee['address'])
        {
            $this->_error('address_empty');

            return false;
        }
        if (!$consignee['phone_tel'] && !$consignee['phone_mob'])
        {
            $this->_error('phone_required');

            return false;
        }
	
		foreach($consignee['delivery_type'] as $val)
		{
			if (!$val || !in_array($val, array('express', 'ems', 'post','group_free_shipping_fee')))
			{
				$this->_error('shipping_required');
	
				return false;
			}
		}

        return $consignee;
    }

    /**
     *    获取商品列表
     *
     *    @author    MiMall
     *    @param     int $order_id
     *    @return    array
     */
    function _get_goods_list($order_id)
    {
        if (!$order_id)
        {
            return array();
        }
        $ordergoods_model =& m('ordergoods');

        return $ordergoods_model->find("order_id={$order_id}");
    }

    /**
     *    获取扩展信息
     *
     *    @author    MiMall
     *    @param     int $order_id
     *    @return    array
     */
    function _get_order_extm($order_id)
    {
        if (!$order_id)
        {
            return array();
        }

        $orderextm_model =& m('orderextm');

        return $orderextm_model->get($order_id);
    }

    /**
     *    获取订单操作日志
     *
     *    @author    MiMall
     *    @param     int $order_id
     *    @return    array
     */
    function _get_order_logs($order_id)
    {
        if (!$order_id)
        {
            return array();
        }

        $model_orderlog =& m('orderlog');

        return $model_orderlog->find("order_id = {$order_id}");
    }

    /**
     *    处理订单基本信息,返回有效的订单信息数组
     *
     *    @author    MiMall
     *    @param     array $goods_info
     *    @param     array $post
     *    @return    array
     */
    function _handle_order_info($goods_info, $post)
    {
        /* 默认都是待付款 */
        $order_status = ORDER_PENDING;

        /* 买家信息 */
		if($this->PostData['user_id']){
			$user_id     =  $this->PostData['user_id'];
			
			$member_mod = &m('member');
			$user = $member_mod->get(array(
				'conditions' => $user_id,
				'fields'     => 'user_name'
			));
			
			$user_name   =  $user['user_name'];
			$email = $user['email'];
		}
		else{
			$visitor     =& env('visitor');
			
			$user_id     =  $visitor->get('user_id');
			$user_name   =  $visitor->get('user_name');
			$email = $visitor->get('email');
		}
		
        /* 返回基本信息 */
		$result = array();
		
		foreach($goods_info['orderList'] as $store_id => $order)
		{
        	$result[$store_id] = array(
				'order_sn'      	=>  $this->_gen_order_sn($store_id),
				'type'          	=>  $goods_info['type'],
				'extension'    		=>  $this->_name,
				'seller_id'    	 	=>  $order['store_id'],
				'seller_name'   	=>  addslashes($order['store_name']),
				'buyer_id'      	=>  $user_id,
				'buyer_name'    	=>  addslashes($user_name),
				'buyer_email'   	=>  $email,
				'status'       	 	=>  $order_status,
				'add_time'      	=>  gmtime(),
				'goods_amount'  	=>  $order['amount'],
				'anonymous'     	=>  intval($post['anonymous'][$store_id]),
				'postscript'   		=>  trim($post['postscript'][$store_id]),
				'did'				=> 	$order['did'],
				'distribution_rate'	=> 	$order['distribution_rate'],
				'group_id'	=> 	$goods_info['group_id'],
				'team_id'	=> 	$goods_info['team_id'],
        
			);
		}

		return $result;
    }

    /**
     *    处理收货人信息，返回有效的收货人信息
     *
     *    @author    MiMall
     *    @param     array $goods_info
     *    @param     array $post
     *    @return    array
     */
    function _handle_consignee_info($goods_info, $post)
    {
		$result = array();
		
        /* 验证收货人信息填写是否完整 */
        $consignee_info = $this->_valid_consignee_info($post);
        if (!$consignee_info)
        {
            return false;
        }
		
        /* 计算配送费用 - 运费模板 */
		$addr_id 		= intval($post['addr_id']);
		$delivery_type 	= $post['delivery_type'];
		
		$shipping_method = $this->_get_order_shippings($goods_info);
		foreach($shipping_method as $store_id => $shipping)
		{
        	$result[$store_id] = array(
				'consignee'     =>  $consignee_info['consignee'],
				'region_id'     =>  $consignee_info['region_id'],
				'region_name'   =>  $consignee_info['region_name'],
				'address'       =>  $consignee_info['address'],
				'zipcode'       =>  $consignee_info['zipcode'],
				'phone_tel'     =>  $consignee_info['phone_tel'],
				'phone_mob'     =>  $consignee_info['phone_mob'],
				'shipping_id'   =>  0, //$post['delivery_type'][$store_id]
				'shipping_name' =>  addslashes(delivery_name($delivery_type[$store_id])),
				'shipping_fee'  =>  $shipping[$addr_id][$delivery_type[$store_id]]['logist_fees'],
			);
		}
		return $result;
    }

    /**
     *    获取一级地区
     *
     *    @author    MiMall
     *    @param    none
     *    @return    void
     */
    function _get_regions()
    {
        $model_region =& m('region');
        $regions = $model_region->get_list(0);
        if ($regions)
        {
            $tmp  = array();
            foreach ($regions as $key => $value)
            {
                $tmp[$key] = $value['region_name'];
            }
            $regions = $tmp;
        }

        return $regions;
    }
	
	/* 获取本次订单的各个店铺的可用优惠券 */
	function getStoreCouponList(&$goods_info = array(), $user_id)
	{
		foreach($goods_info['orderList'] as $store_id => $order)
		{
			if($order['allow_coupon'])
			{
				$goods_info['orderList'][$store_id]['coupon_list'] = Psmb_init()->get_available_coupon($order, $user_id);
			}
		}
	}
	
	/* 取得有效的订单折扣信息，如积分抵扣，店铺优惠券的合理性，返回各个优惠减少的金额 */
	function getAllDiscountByPost($goods_info, $post = array())
	{
		$result 		= $discount_info = array();
		
		$visitor     	=& env('visitor');

		// 验证买家使用多少积分抵扣货款的有效性
		if($goods_info['allow_integral'])
		{
			$result 		= $goods_info['integralExchange'];
			
			if($post['exchange_integral'] > $result['maxPoints'])
			{
				$this->_error(Lang::get('order_can_use_max_integral').$result['maxPoints']);
				return FALSE;
			} 
			elseif($post['exchange_integral'] > 0) {	
										
				$discount_info['integral'] = array(
					'amount' 		=> round($post['exchange_integral'] * $result['rate'], 2), 
					'points' 		=> $post['exchange_integral'],
					'orderIntegral' => $result['orderIntegral']
				);
			}
		}
		// 验证买家使用的优惠券的有效性
		$this->getStoreCouponList($goods_info, $this->PostData['user_id'] ? $this->PostData['user_id'] : $visitor->get('user_id'));
		$result = $goods_info['orderList'];
				
		if(!isset($post['coupon_sn'])) $post['coupon_sn'] = array();
		
		foreach($post['coupon_sn'] as $store_id => $coupon_sn)
		{
			if(isset($result[$store_id]['coupon_list']) && !empty($result[$store_id]['coupon_list']))
			{
				foreach($result[$store_id]['coupon_list'] as $key => $val)
				{
					if($coupon_sn == $val['coupon_sn'])
					{
						$discount_info['coupon'][$store_id] = array('coupon_value' => $val['coupon_value'], 'coupon_sn' => $coupon_sn);
						break;
					}
				}
			}
		}
		
		import('promotool.lib');
		foreach($goods_info['storeIds'] as $store_id) 
		{
			$promotool = new Promotool(array('_store_id' => $store_id));
			
			/* 处理满折满减信息 */
			if($fullprefer = $promotool->getOrderFullPreferInfo($goods_info['orderList'][$store_id])) {
				$discount_info['fullprefer'][$store_id] = array('amount' => $fullprefer['price']);
			}
		
			/* 处理加价够信息 */
			if($userGrowbuyList = $promotool->getOrderGrowbuyInfoByUserChecked($goods_info['orderList'][$store_id], $post['growbuy'][$store_id])) {
				$discount_info['growbuy'][$store_id] = array('amount' => $userGrowbuyList['amount']);
			}
		}
		
		return $discount_info;
	}
	
	/* 检验折扣信息和订单总价的合理性 */
	function checkAllDiscountForOrderAmount(&$base_info, &$discount_info, $consignee_info, $integralExchangeRate = 0)
	{
		$amount = 0;
		foreach($base_info as $store_id => $order_info)
		{
			// 商品总价
			$goodsAmount 	= $order_info['goods_amount'];
			// 包含运费的订单总价
			$storeAmount 	= $order_info['goods_amount'] + $consignee_info[$store_id]['shipping_fee']; 
			
			// 加上加价购商品后的订单总价
			if(isset($discount_info['growbuy'][$store_id]['amount']) && ($discount_info['growbuy'][$store_id]['amount'] > 0)) {
				$storeAmount += $discount_info['growbuy'][$store_id]['amount'];
				$goodsAmount += $discount_info['growbuy'][$store_id]['amount'];
			}
			
			$couponDiscount = $fullpreferDiscount = 0;
			
			// 每个订单的店铺优惠券优惠
			if(isset($discount_info['coupon'][$store_id]['coupon_value']))
			{
				$couponDiscount 	= $discount_info['coupon'][$store_id]['coupon_value'];
				if($couponDiscount > 0)
				{
					// 如果优惠折扣大于订单总价
					if($couponDiscount > $storeAmount)
					{
						$this->_error('discount_gt_storeAmount');
						return FALSE;
					}
					
					$storeAmount -= $couponDiscount;
				}
			}
			// 每个订单的满折满减优惠
			if(isset($discount_info['fullprefer'][$store_id]['amount']))
			{
				$fullpreferDiscount 	= $discount_info['fullprefer'][$store_id]['amount'];
				if($fullpreferDiscount > 0)
				{
					// 如果优惠折扣大于订单总价
					if($fullpreferDiscount > $storeAmount)
					{
						$this->_error('discount_gt_storeAmount');
						return FALSE;
					}
					
					$storeAmount -= $fullpreferDiscount;
				}
			}
			
			// 返回的数据
			$base_info[$store_id]['order_amount'] 	= $storeAmount;
			$base_info[$store_id]['goods_amount']   = $goodsAmount;
			$base_info[$store_id]['discount']		= $couponDiscount + $fullpreferDiscount;
			
			// 所有订单实际支付的金额汇总（未使用积分前）
			$amount			+= $storeAmount;
		}
		
		/*  情况一：所有订单减去折扣之后的总额为零，那么说明已经不能再使用积分来抵扣了 */
		/*  情况二：所有订单减去折扣之后的总额不为零，则判断积分抵扣的金额是否合理（如使用积分抵扣后订单总额为负，则不合理）*/
		if(isset($discount_info['integral']['amount']) && ($discount_info['integral']['amount'] > 0)) 
		{
			if(($amount <= 0) || ($discount_info['integral']['amount'] > $amount))
			{
				$this->_error('integral_gt_amount');
				return FALSE;
			}
		}
		
		/* 至此说明所使用的积分抵扣值是合理的（不大于订单总价了，或者本次订单没有使用积分来抵扣，如果使用了积分抵扣，还要继续判断哪个订单使用了多少积分来抵扣，用分摊来计算） */
		
		if(($amount > 0) && (isset($discount_info['integral']['amount']) && ($discount_info['integral']['amount'] > 0)))
		{
			foreach($base_info as $store_id => $order_info)
			{
				$rate = $discount_info['integral']['orderIntegral']['items'][$store_id] / $discount_info['integral']['orderIntegral']['totalPoints'];
				$sharePoints = round($rate * $discount_info['integral']['points'], 2);
				$shareAmount = round($rate * $discount_info['integral']['points'] * $integralExchangeRate, 2);
				
				// 在这里已经不用判断各个订单分摊的积分，是否抵扣完订单总价甚至抵扣为负值的情况了，因为最多能抵扣完， 不会出现负值的情况
				$discount_info['integral']['shareIntegral'][$store_id] = array('amount' => $shareAmount, 'points' => $sharePoints);
				
				// 返回的数据
				$base_info[$store_id]['order_amount'] 	-= $shareAmount;
				$base_info[$store_id]['discount']		+= $shareAmount;
			}
		}
		
		return TRUE;
	}
	
	/* 获取本次订单的运费资费或获取指定地址的资费（多个店铺） */
	function _get_order_shippings($goods_info = array())
	{
		$data = $shipping_methods = array();
		
		import('promotool.lib');
		$visitor =& env('visitor');

       	 /* 获取我的收货地址 */
        $data['my_address'] = $this->_get_my_address($this->PostData['user_id'] ? $this->PostData['user_id'] : $visitor->get('user_id'));
	
	
		/* 根据 goods_info找出所有店铺每个商品的运费模板id */
		$goods_mod 		= &m('goods');
		$delivery_mod 	= &m('delivery_template');
		$base_deliverys = array();
				
		foreach($goods_info['orderList'] as $store_id => $order)
		{
			foreach($order['items'] as $goods)
			{
				$search_goods = $goods_mod->get(array(
					'conditions'	=>	'goods_id='.$goods['goods_id'],
					'fields'		=>	'delivery_template_id'
				));
				$template_id = $search_goods['delivery_template_id'];
				
				/* 如果商品的运费模板id为0，即未设置运费模板，则获取店铺默认的运费模板（取第一个） */
				if(!$template_id || !$delivery_mod->get($template_id))
				{
					$delivery = $delivery_mod->get(array(
						'conditions'	=>	'store_id='.$store_id,
						'order'			=>	'template_id',
					));
					
					/* 如果店铺也没有默认的运费模板 */
					if(empty($delivery)){
						$this->_error('store_no_delivery');
						return false;
					}
								
				} else {
					$delivery = $delivery_mod->get($template_id);
				}
				
				$base_deliverys[$store_id][$goods['goods_id']] = $delivery;
			}
		}
		
		/* 根据运送目的地，获取运费情况 */
		foreach($data['my_address'] as $addr_id => $my_address)
		{
			if($goods_info['otype'] == 'groupbuy')
			{
				$shipping_methods[$store_id][$addr_id]['ems'] = array(
					'logist_fees' => 0,
					'type' => 'group_free_shipping_fee',
					'name' => '拼团专区',
				);
			}
			else
			{
				$city_id = $my_address['region_id']; // 此处不是 city_id 的话，可能影响也不大。
				foreach($base_deliverys as $store_id => $goods_deliverys)
				{
					$deliverys = array();
					foreach($goods_deliverys as $key => $delivery){
						$deliverys[$key] = $delivery_mod->get_city_logist($delivery, $city_id);
					}
			
				
					/* 一、如果每个商品可用的运送方式都一致，则统一计算；二、 如果有一个商品的运送方式不同，则进行组合计算 */
					/* 注：目前已经强制每个运费模板都必须设置三个运送方式，所以不存在不全等的情况。 */
	
					/* 1. 分别计算每个运送方式的费用：找出首费最大的那个运费方式，作为首费，并且找出作为首费的那个商品id，便于在统计运费总额时，该商品使用首费，其他商品使用续费计算 */
					$merge_info = array(
						'express' => array('start_fees'=>0,'goods_id'=>0),
						'ems'     => array('start_fees'=>0,'goods_id'=>0),
						'post'    => array('start_fees'=>0,'goods_id'=>0),
					);
					foreach($deliverys as $goods_id	=> $delivery)
					{
						foreach($delivery as $template_types)
						{
							if($merge_info[$template_types['type']]['start_fees'] <= $template_types['start_fees']){
								$merge_info[$template_types['type']]['start_fees'] = $template_types['start_fees'];
								$merge_info[$template_types['type']]['goods_id'] = $goods_id;
							}
						}
					}
					
					/* 2. 计算每个订单（店铺）的商品的总件数（包括不同规格）和每个商品的总件数（包括不同规格），以下会用到总件数来计算运费 */
					$total_quantity = 0;
					$quantity = array();
					foreach($goods_info['orderList'][$store_id]['items'] as $goods)
					{
						$quantity[$goods['goods_id']] += $goods['quantity'];
						$total_quantity += $goods['quantity'];
					}
					/* 3. 计算总运费 */
					$logist = array();
					foreach($deliverys as $goods_id => $delivery)
					{
						foreach($delivery as $template_types)
						{
							if($goods_id == $merge_info[$template_types['type']]['goods_id']){
								if($total_quantity > $template_types['start_standards'] && $template_types['add_standards'] > 0){
									if($quantity[$goods_id] > $template_types['start_standards']) {
										$goods_fees = $merge_info[$template_types['type']]['start_fees'] + ($quantity[$goods_id]- $template_types['start_standards'])/$template_types['add_standards'] * $template_types['add_fees'];
									}
									else {
										$goods_fees = $merge_info[$template_types['type']]['start_fees'];
									}
									
								} else {
									$goods_fees = $merge_info[$template_types['type']]['start_fees'];
								}
								//$logist[$template_types['type']]['list_fee'][$goods_id]['logist_fee'] +=  $goods_fees;	
							}
							else
							{
								if($template_types['add_standards']>0){
									$goods_fees = $quantity[$goods_id]/$template_types['add_standards'] * $template_types['add_fees'];
								} else {
									$goods_fees = $template_types['add_fees'];
								}
								//$logist[$template_types['type']]['list_fee'][$goods_id]['logist_fee'] += $goods_fees;
							}
							$logist[$template_types['type']]['logist_fees'] += round($goods_fees, 2);
							$logist[$template_types['type']] += $template_types;
						}
					}
					
					/* 检查是否满足满包邮条件 */
					$promotool = new Promotool(array('_store_id' => $store_id));
					if($result = $promotool->getOrderFullfree($goods_info['orderList'][$store_id])) {
						foreach($logist as $k => $v) {
							$logist[$k]['logist_fees'] = 0;
							$logist[$k]['name'] = $v['name'].'('.$result['title'].')';
						}
					}
					
					$shipping_methods[$store_id][$addr_id] = $logist;
				}
			}
			
		}
		
		/* 返回本次订单所有地址的运费资费 */
		return $shipping_methods;
	}
}

?>