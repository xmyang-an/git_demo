<?php

/* 订单 order */
class OrderModel extends BaseModel
{
    var $table  = 'order';
    var $alias  = 'order_alias';
    var $prikey = 'order_id';
    var $_name  = 'order';
    var $_relation  = array(
        // 一个订单有一个实物商品订单扩展
        'has_orderextm' => array(
            'model'         => 'orderextm',
            'type'          => HAS_ONE,
            'foreign_key'   => 'order_id',
            'dependent'     => true
        ),
		'has_integral_log' => array(
            'model'         => 'integral_log',
            'type'          => HAS_ONE,
            'foreign_key'   => 'order_id',
            'dependent'     => true
        ),
        // 一个订单有多个订单商品
        'has_ordergoods' => array(
            'model'         => 'ordergoods',
            'type'          => HAS_MANY,
            'foreign_key'   => 'order_id',
            'dependent'     => true
        ),
        // 一个订单有多个订单日志
        'has_orderlog' => array(
            'model'         => 'orderlog',
            'type'          => HAS_MANY,
            'foreign_key'   => 'order_id',
            'dependent'     => true
        ),
        'belongs_to_store'  => array(
            'type'          => BELONGS_TO,
            'reverse'       => 'has_order',
            'model'         => 'store',
        ),
        'belongs_to_user'  => array(
            'type'          => BELONGS_TO,
            'reverse'       => 'has_order',
            'model'         => 'member',
        ),
    );

    /**
     *    修改订单中商品的库存，可以是减少也可以是加回
     *
     *    @author    MiMall
     *    @param     string $action     [+:加回， -:减少]
     *    @param     int    $order_id   订单ID
     *    @return    bool
     */
    function change_stock($action, $order_id)
    {
        if (!in_array($action, array('+', '-')))
        {
            $this->_error('undefined_action');

            return false;
        }
        if (!$order_id)
        {
            $this->_error('no_such_order');

            return false;
        }

        /* 获取订单商品列表 */
        $model_ordergoods =& m('ordergoods');
        $order_goods = $model_ordergoods->find("order_id={$order_id}");
        if (empty($order_goods))
        {
            $this->_error('goods_empty');

            return false;
        }

        $model_goodsspec =& m('goodsspec');
        $model_goods =& m('goods');

        /* 依次改变库存 */
        foreach ($order_goods as $rec_id => $goods)
        {
            $model_goodsspec->edit($goods['spec_id'], "stock=stock {$action} {$goods['quantity']}");
            $model_goods->clear_cache($goods['goods_id']);
        }

        /* 操作成功 */
        return true;
    }
	
	// 获取订单的支付标题
	function getOrderSubjectByOrder($order_id)
	{
		$ordergoods_model =& m('ordergoods');
		$ordergoods = $ordergoods_model->find(array('conditions' => 'order_id='.$order_id, 'fields' => 'goods_name', 'order' => 'rec_id ASC'));
		
		$first = current($ordergoods);
		
		$subject = Lang::get('defray') . ' - ' . $first['goods_name'];
		
		if(count($ordergoods) > 1) {
			$subject = $subject . Lang::get('and_more');
		}
		
		return addslashes($subject);
	}
	
	/*  合并付款情况下，检查每个订单是否都支持货到付款 */
	function _checkMergePayCodPaymentEnable($orderList = array())
	{
		$status 		= TRUE;
		$cod_payments   = array();
		$payment_model =& m('payment');
		
		foreach($orderList as $order_id => $order_info)
		{
			if($this->_checkCodPaymentEnable($order_info) === FALSE) {
				$status = FALSE;
				break;
			}
			$cod_payments[$order_id] = $payment_model->get("payment_code='cod' AND store_id={$order_info['seller_id']} AND enabled=1");
		}
		
		// 必须所有的合并中的订单都支持货到付款，才显示货到付款的支付方式
		if($status === FALSE) $cod_payments = array();
		
		return $cod_payments;
	}
	
	// 检查单个订单是否支持货到付款
	function _checkCodPaymentEnable($order_info)
	{
		$result = FALSE;
		
		$payment_model =& m('payment');
				
		if(in_array('cod', $payment_model->get_white_list())) 
		{
			$payment  = $payment_model->get("payment_code='cod' AND store_id={$order_info['seller_id']} AND enabled=1");
			
			/* 找出收货人地址信息 */
			$model_extm =& m('orderextm');
			$consignee_info = $model_extm->get($order_info['order_id']);
				
			$cod_regions   = unserialize($payment['cod_regions']);
			
			if (is_array($cod_regions) && !empty($cod_regions))
			{
				 /* 取得支持货到付款地区的所有下级地区 */
				 $all_regions = array();
				 $model_region =& m('region');
				 foreach ($cod_regions as $region_id => $region_name)
				 {
					  $all_regions = array_merge($all_regions, $model_region->get_descendant($region_id));
				 }
							
				 /* 查看订单中指定的地区是否在可货到付款的地区列表中，如果在，则显示货到付款的付款方式 */
				 if (in_array($consignee_info['region_id'], $all_regions))
				 {
					$result = TRUE;
				 }
			}
		}
		
		return $result;
			
	}
	
	/* 更新每笔订单的支付方式 */
	function _updateOrderPayment(&$orderInfo = array(), $payments = array(), $isCod = TRUE)
	{
		foreach($orderInfo['orderList'] as $key => $order_info)
		{
			if($isCod === TRUE) {
				$payment_info = $payments[$order_info['order_id']];
			} else $payment_info = $payments;
			
			// 保存支付方式
			$edit_data = array(
				'payment_id'    =>  $payment_info['payment_id'],
				'payment_code'  =>  $payment_info['payment_code'],
				'payment_name'  =>  $payment_info['payment_name'],
			);
			
			// 如果支付方式表更了
			if($order_info['payment_code'] != $payment_info['payment_code']) {
				$edit_data['pay_alter'] = 1;
			} else $edit_data['pay_alter'] = 0;
			
			parent::edit($order_info['order_id'], $edit_data);
			
			// 更新引用数值
			$orderInfo['orderList'][$key] = array_merge($orderInfo['orderList'][$key], $edit_data);
		}
	}
	
	/* 获取每笔订单，订单总额，商品总额等各项实际的金额（或调价后分摊的金额，考虑折扣，运费，改价等情况） */
	function _getRealAmount($order_id = 0)
	{
		$orderextm_mod = &m('orderextm');
		$order_info = parent::get(array('conditions' => 'order_id='.$order_id, 'fields' => 'goods_amount, discount, order_amount, adjust_amount'));
		$orderextm = $orderextm_mod->get(array('conditions' => 'order_id='.$order_id, 'fields' => 'shipping_fee'));
		
		$realGoodsAmount = $realShippingFee = $realOrderAmount = 0;
		if($order_info && $orderextm)
		{
			$realOrderAmount = $order_info['order_amount'];
			
			// 如果实际支付的金额还不过运费的总额，那么先扣完商品总价后，剩余为运费分摊的金额
			$realShippingFee = ($orderextm['shipping_fee'] >= $order_info['order_amount']) ? $order_info['order_amount'] : $orderextm['shipping_fee'];
			
			$realGoodsAmount = $order_info['order_amount'] - $realShippingFee;
		}
		
		return array($realGoodsAmount, $realShippingFee, $realOrderAmount);
	}
}

?>