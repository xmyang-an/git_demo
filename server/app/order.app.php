<?php

class OrderApp extends ApibaseApp
{
	function __construct()
	{
		parent::__construct();
	}
	
	function baseinfo()
	{
		if(!$this->PostData['user_id'])
		{
			$this->json_fail('login_pls');
			exit;	
		}
		
		$address_model =& m('address');
		if(!$address_model->get('user_id=' .$this->PostData['user_id'])){
			$this->json_fail(Lang::get('please_add_address'),array('action' => 'add_address'));
            return;
		}

		$goods_info = $this->_get_goods_info();
        if ($goods_info === false)
        {
            /* 购物车是空的 */
            $this->json_fail('goods_empty');
            return;
        }
		
		/* 如果是自己店铺的商品，则不能购买 */
		if (in_array($this->PostData['user_id'], $goods_info['storeIds']))
		{
			$this->json_fail('can_not_buy_yourself');
			return;
		}

        /*  检查库存 */
		$goods_beyond = $this->_check_beyond_stock($goods_info['orderList']);
		if ($goods_beyond)
		{
			$str_tmp = '';
			foreach ($goods_beyond as $goods)
			{
				$str_tmp .= '<br /><br />' . $goods['goods_name'] . '&nbsp;&nbsp;' . 
					$goods['specification'] . '&nbsp;&nbsp;' . Lang::get('stock') . ':' . $goods['stock'];
			}
			$this->json_fail(sprintf(Lang::get('quantity_beyond_stock'), $str_tmp));
			return;
		}

        /* 根据商品类型获取对应订单类型 */
        $goods_type     =&  gt($goods_info['type']);
        $order_type     =&  ot($goods_info['otype'], array('PostData' => $this->PostData));

        /* 显示订单表单 */
        $form = $order_type->get_order_form($goods_info);

        if ($form === false)
        {
            $this->json_fail($order_type->get_error());
            return;
        }
		
		$form['data']['shipping_methods'] = ecm_json_decode($form['data']['shippings'],true);
		foreach($form['data']['shipping_methods'] as $store_id => $items){
			foreach($items as $key=>$val){
				foreach($val as $k=>$v){
					$form['data']['shipping_methods'][$store_id][$key][$k]['name'] = strip_tags($v['name']);
				}
			}
		}
		
		foreach($goods_info['orderList'] as $store_id => $order){
			if($order['fullprefer']['text']){
				$goods_info['orderList'][$store_id]['fullprefer']['text'] = strip_tags($order['fullprefer']['text']);
			}
		}
		
		$this->json_success(array('form' => $form['data'], 'goods_info' => $goods_info));	
	}
	
	function create()
	{
		$this->_checkUserAccess();
		
		$goods_info = $this->_get_goods_info();
        if ($goods_info === false)
        {
            /* 购物车是空的 */
            $this->json_fail('goods_empty');
            return;
        }
		
		/* 如果是自己店铺的商品，则不能购买 */
		if (in_array($this->PostData['user_id'], $goods_info['storeIds']))
		{
			$this->json_fail('can_not_buy_yourself');
			return;
		}

        /*  检查库存 */
		$goods_beyond = $this->_check_beyond_stock($goods_info['orderList']);
		if ($goods_beyond)
		{
			$str_tmp = '';
			foreach ($goods_beyond as $goods)
			{
				$str_tmp .= '<br /><br />' . $goods['goods_name'] . '&nbsp;&nbsp;' . 
					$goods['specification'] . '&nbsp;&nbsp;' . Lang::get('stock') . ':' . $goods['stock'];
			}
			$this->json_fail(sprintf(Lang::get('quantity_beyond_stock'), $str_tmp));
			return;
		}
		
        /* 根据商品类型获取对应订单类型 */
        $goods_type     =&  gt($goods_info['type']);
        $order_type     =&  ot($goods_info['otype'], array('PostData' => $this->PostData));
		
		$post = ecm_json_decode($this->PostData['JSON'],true);
        $result = $order_type->submit_order(array(
            'goods_info'    =>  $goods_info,      //商品信息（包括列表，总价，总量，所属店铺，类型）,可靠的!
            'post'          =>  $post,           //用户填写的订单信息
        ));

       if(empty($result))
       {
		   $error = $order_type->get_error();
		   $error = current($error);
           $this->json_fail($error['msg']);
           return;
       }
			
	   foreach($result as $store_id => $order_id) {
				
		   /* 清理购物车商品等操作 */
		   $this->_afterInsertOrder($order_id,  $store_id, $goods_info['orderList'][$store_id]);
	   }
			
	   /* 支付多个订单 */
	   $order_id = implode(',', $result);
		
	   $this->json_success(array('order_id' => $order_id));	
	}
	
	function _afterInsertOrder($order_id, $store_id, $goods_info)
	{
		/* 下单完成后清理商品，如清空购物车，或将团购拍卖的状态转为已下单之类的 */
		$this->_clear_goods($order_id, $store_id);

		/* 发送邮件 */
		$model_order =& m('order');

		/* 减去商品库存 */
		$model_order->change_stock('-', $order_id);

		/* 获取订单信息 */
 		$order_info = $model_order->get($order_id);

  		/* 更新下单次数 */
      	$model_goodsstatistics =& m('goodsstatistics');
     	$goods_ids = array();
    	foreach ($goods_info['items'] as $goods)
      	{
        	$goods_ids[] = $goods['goods_id'];
     	}
   		$model_goodsstatistics->edit($goods_ids, 'orders=orders+1');

   		/* 邮件提醒： 买家已下单通知买家 */
		$this->sendMailMsgNotify($order_info, array('key' => 'tobuyer_new_order_notify', 'touser'=>$this->PostData['user_id']));
				
		/* 短信和邮件提醒： 买家已下单通知卖家 */
		$this->sendMailMsgNotify($order_info, array(
				'key' => 'toseller_new_order_notify',
			),
			array(
				'key' => 'buy', 
				'body' => sprintf(Lang::get('sms_buy'), $order_info['order_sn'], $order_info['buyer_name']),
			)
		);
    }
	
	function _clear_goods($order_id, $store_id)
    {
		$post = ecm_json_decode($this->PostData['JSON'],true);
        switch ($post['goods'])
        {
            case 'cart':
			
				/* 购物车中的商品 */
                /* 订单下完后清空指定购物车 */
                $model_cart =& m('cart');
                $model_cart->drop("store_id = {$store_id} AND selected = 1");
            break;
        }
    }
	
	function _get_goods_info()
    {
        $return = array();
		
        switch ($this->PostData['goods'])
        {
			case 'groupbuy' : 
			
                /* 从购物车中取商品 */
                $group_id = isset($this->PostData['id']) ? intval($this->PostData['id']) : 0;
				$spec_id = isset($this->PostData['spec_id']) ? intval($this->PostData['spec_id']) : 0;
				$quantity = isset($this->PostData['quantity']) ? intval($this->PostData['quantity']) : 0;
				$team_id = isset($this->PostData['team_id']) ? intval($this->PostData['team_id']) : 0;
				
                $groupbuy = $this->getOrderGoodsInfo($this->PostData['user_id'], array('group_id' => $group_id, 'spec_id' => $spec_id,'team_id' => $team_id,'quantity' => $quantity));

				// 按店铺归类商品
				$cart_items = array();
				foreach ($groupbuy as $rec_id => $goods) {
                    $cart_items[$goods['store_id']][$rec_id] = $goods;
                }

				$amount = 0;
                $store_model =& m('store');
                foreach ($cart_items as $store_id => $items)
                {
					$storeAmount = $storeQuantity = 0;
					foreach($items as $key => $goods) 
					{
                    	$items[$key]['subtotal']    =   $goods['quantity'] * $goods['price'];   //小计
						
                    	empty($goods['goods_image']) && $goods['goods_image'] = Conf::get('default_goods_image');
						if(stripos($goods['goods_image'], '//:') == FALSE) {
							$goods['goods_image'] = SITE_URL . '/' . $goods['goods_image'];
						}
						
						$items[$key]['goods_image'] = $goods['goods_image'];
					
						$storeAmount += floatval($items[$key]['subtotal']);
						$storeQuantity += $goods['quantity'];
					}
					
					$store_info = $store_model->get(array(
						'conditions' => 'store_id='.$store_id,  'fields' => 'store_id, store_name, sgrade as sgrade_id, im_qq'));
					
					$return['orderList'][$store_id] = array_merge(array(
						'items' => $items, 'amount' => $storeAmount, 'quantity' => $storeQuantity), $store_info);
					
					$return['orderList'][$store_id]['allow_coupon'] = TRUE;
					
					$return['storeIds'][] = $store_id;

					$amount += $storeAmount;
                }
				
				$return['type']		=   'material';
				$return['otype']	=   'groupbuy';
				$return['amount']	=	$amount;
				$return['team_id']	=	$team_id;
				$return['group_id']	=	$group_id;
            break;
			
			default: 
                /* 从购物车中取商品 */
				$cart_model = &m('cart');
				$cartList =  $cart_model->find(array(
					'conditions' => "user_id = " .$this->PostData['user_id']. " AND selected=1",
					'join'       => 'belongs_to_goodsspec',
					
					// 不能有 gs.price， 要不读取的不是促销价格，购物车里面才是促销价格
					'fields'     => 'gs.spec_id,gs.spec_1,gs.spec_2,gs.stock,gs.sku,cart.*',
		
				));
				
				if(!$cartList) {
					return false;
				}

				// 按店铺归类商品
				$cart_items = array();
				foreach ($cartList as $rec_id => $goods) {
                    $cart_items[$goods['store_id']][$rec_id] = $goods;
                }
				
				$amount = 0;
                $store_model =& m('store');
                foreach ($cart_items as $store_id => $items)
                {
					$storeAmount = $storeQuantity = 0;
					foreach($items as $key => $goods) 
					{
                    	$items[$key]['subtotal']    =   $goods['quantity'] * $goods['price'];   //小计
                    	empty($goods['goods_image']) && $goods['goods_image'] = Conf::get('default_goods_image');
						if(stripos($goods['goods_image'], '//:') == FALSE) {
							$goods['goods_image'] = SITE_URL . '/' . $goods['goods_image'];
						}
						
						$items[$key]['goods_image'] = $goods['goods_image'];
					
						$storeAmount += floatval($items[$key]['subtotal']);
						$storeQuantity += $goods['quantity'];
					}
					
					$store_info = $store_model->get(array(
						'conditions' => 'store_id='.$store_id,  'fields' => 'store_id, store_name, sgrade as sgrade_id, im_qq'));
					
					$return['orderList'][$store_id] = array_merge(array(
						'items' => $items, 'amount' => $storeAmount, 'quantity' => $storeQuantity), $store_info);
					
					// 是否允许使用优惠券
					$return['orderList'][$store_id]['allow_coupon'] = TRUE;
					
					// 记录本次订单有多少个店铺的商品，以便其他地方使用
					$return['storeIds'][] = $store_id;
					
					// 统计各个订单的总额（商品的原价之和，并非订单最终的优惠价格，此值仅作为后续计算各个订单所占总合并订单金额的分摊比例用）
					$amount += $storeAmount;
                }
				
				$return['type']		=   'material';
				$return['otype']	=   'normal';
				$return['amount']	=	$amount;
				
				// 是否允许使用积分抵扣
				$integral_mod = &m('integral');
				if($allow_integral 	= $integral_mod->_get_sys_setting('integral_enabled')) {
					$return['allow_integral'] 	= $allow_integral;
					$return['integralExchange']	= $integral_mod->getIntegralByOrders($this->PostData['user_id'], $cartList);
				}
						
            break;
        }
		
        return $return;
    }
	
	 function _check_beyond_stock($orderList = array())
    {
        $goods_beyond_stock = array();
		foreach($orderList as $store_id => $order)
		{
			foreach ($order['items'] as $rec_id => $goods)
			{
				if ($goods['quantity'] > $goods['stock'])
				{
					$goods_beyond_stock[$goods['spec_id']] = $goods;
				}
			}
		}
        return $goods_beyond_stock;
    }
	
	function getOrderGoodsInfo($user_id = 0, $extraParams = array())
	{
		extract($extraParams);

		if(!$group_id || !$user_id || !$spec_id || !$quantity)
		{
			return $this->json_fail('该团已成团或者已过期！');
		}
		
		$model_goodsspec = &m('goodsspec');
		$groupbuy_mod = &m('groupbuy');
		$group = $groupbuy_mod->get(array(
            'conditions' => 'group_id=' . $group_id . ' AND gb.state =' . GROUP_ON
        ));
		
		if(!$group)
		{
			return $this->json_fail('该团已成团或者已过期！');
		}
		
		if($team_id > 0){
			$team_mod = &m('team');
			$team = $team_mod->get('team_id='.$team_id.' AND status is NULL');
			if(empty($team)){
				return $this->json_fail('该团已成团或者已过期！');
			}
		}
		
		$spec_price = unserialize($group['spec_price']);
		if(!isset($spec_price[$spec_id])){
			return $this->json_fail('该团已成团或者已过期！');
		}
		
		$goods = $model_goodsspec->get(array(
			'conditions' => 'spec_id='.intval($spec_id),
			'join'		 => 'belongs_to_goods', 
			'fields'	 => 'this.*,store_id,goods_name,default_image as goods_image,spec_name_1,spec_name_2'
		));
		
		if($goods)
		{
			$goods['quantity'] = 1;//  套餐商品默认都是购买一件
			!empty($goods['spec_1']) && $goods['specification'] = $goods['spec_name_1'] . ':' . $goods['spec_1'];	
			!empty($goods['spec_2']) && $goods['specification'] .= ' ' . $goods['spec_name_2'] . ':' . $goods['spec_2']; 
				
			// 兼容规格图片功能
			if(isset($goods['spec_image']) && $goods['spec_image']) {
				$goods['goods_image'] = $goods['spec_image'];
				unset($goods['spec_image']);
			}
			
			$goods['price'] = $spec_price[$spec_id]['price'];
			$groupbuy[] = $goods;
		}

		return $groupbuy;
	}
	
	function order_static()
	{
		$this->_checkUserAccess();
		
		$user_id = intval($this->PostData['user_id']);
		
		$order_mod = &m('order');
		$sql1 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE buyer_id = '{$user_id}' AND status = '" . ORDER_PENDING . "'";
		$sql2 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE buyer_id = '{$user_id}' AND status = '" . ORDER_ACCEPTED . "'";
        $sql3 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE buyer_id = '{$user_id}' AND status = '" . ORDER_SHIPPED . "'";
        $sql4 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE buyer_id = '{$user_id}' AND status = '" . ORDER_FINISHED . "' AND evaluation_status = 0";
		
		$buyer_stat = array(
            'pending'  => $order_mod->getOne($sql1),
			'accepted'  => $order_mod->getOne($sql2),
            'shipped'  => $order_mod->getOne($sql3),
            'finished' => $order_mod->getOne($sql4),
			'refund'   => $this->_count_refund()
        );

        $this->json_success($buyer_stat);
	}
	
	function _count_refund()
	{
		$refund_mod = &m('refund');
		$refunds = $refund_mod->find(array(
			'conditions'	=> "buyer_id=".$this->PostData['user_id']." AND status NOT IN ('SUCCESS','CLOSED')",
			'fields'        => 'refund_id'
		));
		
		return count($refunds);
	}
	
	function listing()
    {
		$_GET = $this->PostData;
        $page = $this->_get_page((isset($this->PostData['perpage']) && $this->PostData['perpage'] > 0) ? $this->PostData['perpage'] : 10);
		
        $model_order =& m('order');
        
		$conditions = '1=1';
		
		!$_GET['type'] && $_GET['type'] = 'all_orders';
		$conditions .= $this->_get_query_conditions(array(
            array(      //按订单状态搜索
                'field' => 'status',
                'name'  => 'type',
                'handler' => 'order_status_translator',
            ),
            array(      //按店铺名称搜索
                'field' => 'seller_name',
                //'equal' => 'LIKE',
            ),
            array(      //按下单时间搜索,起始时间
                'field' => 'add_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),
            array(      //按下单时间搜索,结束时间
                'field' => 'add_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'=> 'gmstr2time_end',
            ),
            array(      //按订单号
                'field' => 'order_sn',
            ),
			array(
				'field' => 'evaluation_status',
			),
        ));
		
		if($this->PostData['role'] == 'seller'){
			$conditions .= ' AND seller_id = '.$this->PostData['user_id'];
		}
		else{
			$conditions .= ' AND buyer_id = '.$this->PostData['user_id'];
		}

        /* 查找订单 */
        $orders = $model_order->findAll(array(
            'conditions'    => $conditions,
            'fields'        => 'buyer_id, buyer_name, seller_id, seller_name, status, evaluation_status, order_sn, payment_code, order_amount, shipping_fee,extension,group_id,team_id',
            'count'         => true,
			'join'          => 'has_orderextm',
            'limit'         => $page['limit'],
            'order'         => 'order_id DESC, add_time DESC',
            'include'       =>  array(
                'has_ordergoods',       //取出商品
            ),
        ));
		
		$deposit_trade_mod = &m('deposit_trade');
		$refund_mod = &m('refund');
		$team_mod = &m('team');
		$ordergift_mod = &m('ordergift');
        foreach ($orders as $key1 => $order)
        {
			if(!$order['order_goods']) {
				continue;
			}
			$orders[$key1]['status_label'] = order_status($order['status']);
			
			$total_quantity = 0;
            foreach ($order['order_goods'] as $key2 => $goods)
            {
                empty($goods['goods_image']) && $orders[$key1]['order_goods'][$key2]['goods_image'] = Conf::get('default_goods_image');
				
				$total_quantity += $goods['quantity'];
            }
			
			$orders[$key1]['total_quantity'] = $total_quantity;
				
			/* 是否申请过退款 */
			$tradeInfo = $deposit_trade_mod->get(array(
				'conditions' => 'merchantId="'.MERCHANTID.'" AND bizIdentity="'.TRADE_ORDER.'" AND bizOrderId="'.$order['order_sn'].'"', 'fields' => 'tradeNo'));
			if($tradeInfo) {
				if( $refund = $refund_mod->get(array('conditions'=>'tradeNo="'.$tradeInfo['tradeNo'].'"','fields'=>'status'))) {
					$orders[$key1]['refund_status'] = $refund['status'];
					$orders[$key1]['refund_id'] = $refund['refund_id'];
            	}
			}
			
			/* 读取订单的赠品（如果有）*/
			$orders[$key1]['order_gift'] = array_values($ordergift_mod->find('order_id='.$order['order_id']));
			
			// JS Need
			$orders[$key1]['order_goods'] = array_values($order['order_goods']);
			
			$orders[$key1]['can_refund'] = true;
			$orders[$key1]['can_ship'] = true;
			if($order['extension'] == 'groupbuy')//团购的订单不允许退款
			{
				$orders[$key1]['can_refund'] = false;
				if($order['team_id'] > 0){
					$team = $team_mod->get($order['team_id']);
					if($team['status'] == 1){
						$orders[$key1]['can_refund'] = true;
					}
				}
			}
        }

        $page['item_count'] = $model_order->getCount();
		
		$this->json_success(array_values($orders));	
    }
}

?>
