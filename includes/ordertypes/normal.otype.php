<?php

/**
 *    普通订单类型
 *
 *    @author    MiMall
 *    @usage    none
 */
class NormalOrder extends BaseOrder
{
    var $_name = 'normal';
	
	function __construct($params)
    {
        parent::__construct($params);
    }

    /**
     *    查看订单
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

        /* 获取商品列表 */
        $data['goods_list'] =   $this->_get_goods_list($order_id);

        /* 配关信息 */
        $data['order_extm'] =   $this->_get_order_extm($order_id);

        /* 支付方式信息 */
        if ($order_info['payment_id'])
        {
            $payment_model      	=	& m('payment');
            $payment_info       	=  	$payment_model->get("payment_id={$order_info['payment_id']}");
            $data['payment_info']   =   $payment_info;
        }

        /* 订单操作日志 */
        $data['order_logs'] =   $this->_get_order_logs($order_id);

        return array('data' => $data);
    }

    /* 显示订单表单 */
    function get_order_form(&$goods_info = array())
    {
        $data = array();
        $template = 'order.form.html';

        $visitor =& env('visitor');

        /* 获取我的收货地址 */
        $data['my_address']         = $this->_get_my_address($this->PostData['user_id'] ? $this->PostData['user_id'] : $visitor->get('user_id'));
        $data['addresses']          = ecm_json_encode($data['my_address']);
        $data['regions']            = $this->_get_regions();
		
		if(!$shipping_method =  $this->_get_order_shippings($goods_info)) {
 			return false;
		}
		$data['shipping_methods']   = $shipping_method;
		$data['shippings'] = ecm_json_encode($data['shipping_methods']);
		
		
		// 取默认（第一条地区对应的运费），此作用为：取每个店铺的第一个收货地址各个运送方式的资费，以便第一次加载时显示
		foreach($shipping_method as $key => $val) {
			$data['shipping_methods'][$key] = current($val);
		}
		
		// 获取店铺可用优惠券
		$this->getStoreCouponList($goods_info, $this->PostData['user_id'] ? $this->PostData['user_id'] : $visitor->get('user_id'));
		
		/* 获取订单提交页面显示该订单所有营销工具信息 */
		import('promotool.lib');
		foreach($goods_info['storeIds'] as $store_id) {
			$promotool = new Promotool(array('_store_id' => $store_id));
			$promotool->getOrderAllPromotoolInfo($goods_info);
		}
		
        return array('data' => $data, 'template' => $template);
    }

    /**
     *    提交生成订单，外部告诉我要下的单的商品类型及用户填写的表单数据以及商品数据，我生成好订单后返回订单ID
     *
     *    @author    MiMall
     *    @param     array $data
     *    @return    int
     */
    function submit_order($data)
    {
        /* 释放goods_info和post两个变量 */
        extract($data);
        /* 处理订单基本信息 */
        $base_info = $this->_handle_order_info($goods_info, $post);
        if (!$base_info)
        {
            /* 基本信息验证不通过 */

            return 0;
        }

        /* 处理订单收货人信息 */
        $consignee_info = $this->_handle_consignee_info($goods_info, $post);
        if (!$consignee_info)
        {
            /* 收货人信息验证不通过 */
            return 0;
        }
		
		/* 获取订单折扣信息 */
		$discount_info = $this->getAllDiscountByPost($goods_info, $post);
		if($discount_info === FALSE)
		{
			return 0;
		}
		
		/* 检验折扣信息和订单总价的合理性 */
		if(!$this->checkAllDiscountForOrderAmount($base_info, $discount_info, $consignee_info, $goods_info['integralExchange']['rate']))
		{
			return 0;
		}

        /* 至此说明订单的信息都是可靠的，可以开始入库了 */

		$insertFail = 0;
		$result = array();
		foreach($base_info as $store_id => $store)
		{
			$order_id = $this->insert_order($base_info[$store_id], $consignee_info[$store_id], $goods_info['orderList'][$store_id]);
			
			if(!$order_id) {
				$insertFail++;
				
				// 如果合并付款提交订单中，有一个订单插入失败，为保证数据完成，删除本次合并订单已经插入成功的订单
				$this->drop_order($result);
				break;
			}
			$result[$store_id] = $order_id;
		}
		if($insertFail)
		{
			$this->_error('create_order_failed');
			return;
		}
		
		/* 保存订单加价购，满赠数据 */
		$this->saveOrderExtraInfoByOrder($result, $goods_info, $post);
		
		/* 更新优惠券的使用次数 */
		$this->updateCouponRemainTimes($result, $discount_info['coupon']);
		
		/* 保存每个订单使用的积分数额（处理合并付款订单的积分分摊）*/
		$this->saveIntegralInfoByOrder($result, $discount_info['integral']);
		
		return $result;
	}
	
	function getTridistributeInfo($order_info,$goods_info)
	{
		$member_mod = &m('member');
		$user_ids = $member_mod->getPrevRefer($order_info['buyer_id']);

		$data = array();
		$goods_mod = &m('goods');
		foreach($goods_info['items'] as $rec_id=>$goods)
		{
			$item = $goods_mod->get($goods['goods_id']);
			for($i=0;$i < count($user_ids);$i++)
			{
				if($user_ids[$i] <> $order_info['seller_id'])
				{
					$radio = $item['refer_reward_'.($i+1)];
					if($radio > 0)
					{
						$amount = round($goods['quantity']*$goods['price']*$radio,2);
						if($amount > 0)
						{
							$data[$user_ids[$i]]['total'][$goods['spec_id']] = $amount;
							$data[$user_ids[$i]]['layer'] = $i+1;
						}
					}
				}
			}
		}

		return $data;
	}
	
	/* 插入订单信息 */
	function insert_order($base_info, $consignee_info, $goods_info)
	{	
		$referInfo = $this->getTridistributeInfo($base_info, $goods_info);
		if(!empty($referInfo))
		{
			$base_info['referid'] = implode(',', array_keys($referInfo));
			$base_info['refer_reward'] = serialize($referInfo);
		}
		
        $order_model =& m('order');
        $order_id    = $order_model->add($base_info);

        if (!$order_id)
        {
            /* 插入基本信息失败 */
            $this->_error('create_order_failed');

            return 0;
        }

        /* 插入收货人信息 */
        $consignee_info['order_id'] = $order_id;
        $order_extm_model =& m('orderextm');
        $order_extm_model->add(addslashes_deep($consignee_info));

        /* 插入商品信息 */
        $goods_items = array();
        foreach ($goods_info['items'] as $key => $value)
        {
            $goods_items[] = array(
                'order_id'      =>  $order_id,
                'goods_id'      =>  $value['goods_id'],
                'goods_name'    =>  $value['goods_name'],
                'spec_id'       =>  $value['spec_id'],
                'specification' =>  $value['specification'],
                'price'         =>  $value['price'],
                'quantity'      =>  $value['quantity'],
                'goods_image'   =>  $value['goods_image'],
            );
        }
		
        $order_goods_model =& m('ordergoods');
        $order_goods_model->add(addslashes_deep($goods_items)); //防止二次注入

        return $order_id;
    }
    
    /* 插入合并付款提交订单过程中，如有插入失败的订单，则删除其他订单 */
	function drop_order($result = array())
	{
		foreach($result as $order_id)
		{
			$order_model 		=& m('order');
			$order_goods_model	=& m('ordergoods');
			$order_extm_model 	=& m('orderextm');
			
			$order_model->drop($order_id);
			$order_goods_model->drop("order_id=".$order_id);
			$order_extm_model->drop("order_id=".$order_id);
		}
	}
	
	/* 保存订单加价购，满赠数据 */
	function saveOrderExtraInfoByOrder($result = array(), $goods_info = array(), $post = array())
	{
		import('promotool.lib');
		foreach($result as $store_id => $order_id)
		{
			$promotool = new Promotool(array('_store_id' => $store_id));
			
			/* 保存用户选择的加价购商品加入到订单商品表 */
			if($userGrowbuyList = $promotool->getOrderGrowbuyInfoByUserChecked($goods_info['orderList'][$store_id], $post['growbuy'][$store_id])) {
				$promotool->saveOrderGrowbuyItem($order_id, $userGrowbuyList['items']);
			}
			
			/* 保存满赠商品到订单赠品表 */
			if($fullgift = $promotool->getOrderFullgiftInfo($goods_info['orderList'][$store_id])) {
				$promotool->saveOrderFullgiftItem($order_id, $fullgift);
			}
		}
	}
	
	/* 更新优惠券的使用次数 */
	function updateCouponRemainTimes($result = array(), $coupon = array())
	{
		$couponsn_mod =& m('couponsn');
		foreach($result as $store_id => $order_id)
		{
			if(isset($coupon[$store_id]['coupon_sn']))
			{
				$sn = $coupon[$store_id]['coupon_sn'];
				$couponsn = $couponsn_mod->get("coupon_sn = '{$sn}'");
 				if ($couponsn['remain_times'] > 0)
 				{
   					$couponsn_mod->edit("coupon_sn = '{$sn}'", "remain_times= remain_times - 1");
  				}
			}
		}
	}
	
	/* 保存订单使用的积分数额 */
	function saveIntegralInfoByOrder($result = array(), $integral = array())
	{
		if(!empty($result) && isset($integral['points']) && ($integral['points'] > 0))
		{
			$order_mod 			= &m('order');
			$integral_mod 		= &m('integral');
			$order_integral_mod = &m('order_integral');
			
			foreach($result as $store_id => $order_id)
			{
				if($integral['shareIntegral'][$store_id]['points'] >　0)
				{
					$order_info = $order_mod->get(array('conditions' => 'order_id='.$order_id, 'fields' => 'order_sn, buyer_id'));
									
					/*扣减操作*/
					$result = array(
						'user_id' 	=> $order_info['buyer_id'],
						'type'    	=> 'buying_pay_integral',
						'order_id'	=> $order_id,
						'order_sn'	=> $order_info['order_sn'],
						'amount'  	=> $integral['shareIntegral'][$store_id]['points'],
						'state'   	=> 'frozen',
						'flow'    	=> 'minus'
					);
					$integral_mod->update_integral($result);
								
					/* 积分扣减 */
					$order_integral = array(
						'order_id'			=>	$order_id,
						'buyer_id'			=>	$order_info['buyer_id'],
						'frozen_integral'	=> 	$integral['shareIntegral'][$store_id]['points'], // 买家抵价的积分，该积分会在交易完成后付给卖家，从买家账户中扣除
					);
					$order_integral_mod->add($order_integral);
				}
			}
		}
	}
	
	// 获取购物车中的商品数据，用来计算订单可使用的最大积分值
	function getOrderGoodsInfo($user_id = 0, $extraParams = array())
	{
		extract($extraParams);

		$cart_model =& m('cart');
		$cartList      =  $cart_model->find(array(
 			'conditions' => "user_id = " . $user_id . " AND selected=1 AND session_id='" . SESS_ID . "'",
			'join'       => 'belongs_to_goodsspec',
			
			// 不能有 gs.price， 要不读取的不是促销价格，购物车里面才是促销价格
			'fields'     => 'gs.spec_id,gs.spec_1,gs.spec_2,gs.stock,gs.sku,cart.*',

  		));
		
		return array($cartList);
	}
}

?>