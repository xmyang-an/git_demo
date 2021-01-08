<?php

/**
 *    售货员控制器，其扮演实际交易中柜台售货员的角色，你可以这么理解她：你告诉我（售货员）要买什么东西，我会询问你你要的收货地址是什么之类的问题
 *        并根据你的回答来生成一张单子，这张单子就是“订单”
 *
 *    @author    MiMall
 *    @param    none
 *    @return    void
 */
class OrderApp extends ShoppingbaseApp
{
	var $_integral_mod ;
	
	function __construct()
	{
		parent::__construct();
		$this->_integral_mod = &m('integral');
	}
    /**
     *    填写收货人信息，选择配送，支付方式。
     *
     *    @author    MiMall
     *    @param    none
     *    @return    void
     */
    function index()
    {
		/* 检查买家的收货地址，因为用到了运费模板，如果没有收货地址，无法读取运费 */
		$current_url = site_url().'/index.php?app=order&goods='.html_script($_GET['goods']);
		$address_model =& m('address');
		if(!$address_model->get('user_id=' . $this->visitor->get('user_id'))){
			$this->show_warning(Lang::get('please_add_address'), Lang::get('add_address'), site_url()  . '/index.php?app=my_address&act=add&ret_url='.urlencode($current_url));
            return;
		}
		
        $goods_info = $this->_get_goods_info();
		
        if ($goods_info === false)
        {
            /* 购物车是空的 */
            //$this->show_warning('goods_empty');
			header('Location:index.php?app=buyer_order');
            return;
        }
		
		/* 如果是自己店铺的商品，则不能购买 */
		if ($this->visitor->get('manage_store'))
		{
			if (in_array($this->visitor->get('manage_store'), $goods_info['storeIds']))
			{
				$this->show_warning('can_not_buy_yourself');
	
				return;
			}
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
			$this->show_warning(sprintf(Lang::get('quantity_beyond_stock'), $str_tmp));
			return;
		}
		
		if (!IS_POST)
        {
            /* 根据商品类型获取对应订单类型 */
            $goods_type     =&  gt($goods_info['type']);
            $order_type     =&  ot($goods_info['otype']);

            /* 显示订单表单 */
            $form = $order_type->get_order_form($goods_info);
			
            if ($form === false)
            {
                $this->show_warning($order_type->get_error());

                return;
            }
            $this->_curlocal(
                LANG::get('create_order')
            );
            $this->_config_seo('title', Lang::get('confirm_order') . ' - ' . Conf::get('site_title'));
	    	$this->_get_curlocal_title('confirm_order');			
            $this->assign('goods_info', $goods_info);
			$this->assign('ret_url', $current_url);
            $this->assign($form['data']);
            $this->display($form['template']);
        }
        else
        {
            /* 根据商品类型获取对应的订单类型 */
            $goods_type =& gt($goods_info['type']);
            $order_type =& ot($goods_info['otype']);
			
			$goods_info = $this->_add_dids($goods_info);

            /* 将这些信息传递给订单类型处理类生成订单(你根据我提供的信息生成一张订单) */
            $result = $order_type->submit_order(array(
                'goods_info'    =>  $goods_info,      //商品信息（包括列表，总价，总量，所属店铺，类型）,可靠的!
                'post'          =>  $_POST,           //用户填写的订单信息
            ));

            if(empty($result))
            {
                $this->show_warning($order_type->get_error());

                return;
            }
			
			foreach($result as $store_id => $order_id) {
				
				/* 清理购物车商品等操作 */
				$this->_afterInsertOrder($order_id,  $store_id, $goods_info['orderList'][$store_id]);
			}
			
			/* 支付多个订单 */
			$order_id = implode(',', $result);
			
			/* 到收银台付款 */
            header('Location:index.php?app=cashier&order_id=' . $order_id);
		}
	}
	
	function _afterInsertOrder($order_id, $store_id, $goods_info)
	{
		/* 下单完成后清理商品，如清空购物车，或将拼团拍卖的状态转为已下单之类的 */
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
			
		/* 发送事件 */
    	$feed_images = array();
     	foreach ($goods_info['items'] as $_gi)
    	{
			$feed_images[] = array(
				'url'   => SITE_URL . '/' . $_gi['goods_image'],
      			'link'  => SITE_URL . '/' . url('app=goods&id=' . $_gi['goods_id']),
         	);
       	}
  		$this->send_feed('order_created', array(
     		'user_id'   => $this->visitor->get('user_id'),
        	'user_name' => addslashes($this->visitor->get('user_name')),
        	'seller_id' => $order_info['seller_id'],
       		'seller_name' => $order_info['seller_name'],
         	'store_url' => SITE_URL . '/' . url('app=store&id=' . $order_info['seller_id']),
       		'images'    => $feed_images,
    	));

   		/* 邮件提醒： 买家已下单通知买家 */
		$this->sendMailMsgNotify($order_info, array('key' => 'tobuyer_new_order_notify', 'touser'=>$this->visitor->get('user_id')));
				
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

    /**
     *    获取外部传递过来的商品
     *
     *    @author    MiMall
     *    @param    none
     *    @return    void
     */
    function _get_goods_info()
    {
        $return = array();
		
        switch ($_GET['goods'])
        {
			case 'meal':
				$meal_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
				$specs = isset($_GET['specs']) ? explode('-', html_script($_GET['specs'])) : array();
				
				$order_type     =&  ot('meal');
                list($mealList, $meal) = $order_type->getOrderGoodsInfo($this->visitor->get('user_id'), array('meal_id' => $meal_id, 'specs' => $specs));
				if(!$mealList) {
					return false;
				}
				
				// 按店铺归类商品
				$meal_items = array();
				foreach ($mealList as $rec_id => $goods) {
                    $meal_items[$goods['store_id']][$rec_id] = $goods;
                }
				
				
				$amount = 0;
				$store_model =& m('store');
				foreach ($meal_items as $store_id => $items)
                {
					$storeAmount = $storeQuantity = 0;
					foreach($items as $key => $goods) 
					{
                    	$items[$key]['subtotal']    =   $goods['quantity'] * $goods['price'];   //小计
                    	empty($goods['goods_image']) && $items[$key]['goods_image'] = Conf::get('default_goods_image');
					
						$storeAmount += floatval($items[$key]['subtotal']);
						$storeQuantity += $goods['quantity'];
					}
					
					$store_info = $store_model->get(array(
						'conditions' => 'store_id='.$store_id,  'fields' => 'store_id, store_name, sgrade as sgrade_id, im_qq'));
					
					$return['orderList'][$store_id] = array_merge(array(
						'items' => $items, 'oldAmount' => $storeAmount, 'amount' => $meal['price'], 'quantity' => $storeQuantity), $store_info);
					
					// 是否允许使用优惠券
					$return['orderList'][$store_id]['allow_coupon'] = TRUE;
					
					// 记录本次订单有多少个店铺的商品，以便其他地方使用
					$return['storeIds'][] = $store_id;
					
					// 统计各个订单的总额（商品的原价之和，并非订单最终的优惠价格，此值仅作为后续计算各个订单所占总合并订单金额的分摊比例用）
					//$amount += $storeAmount;
					$amount += $meal['price'];
                }
				
				$return['extId']			=	$meal_id;
				$return['type']         	=   'material';
                $return['otype']        	=   'meal';
				$return['amount']       	=   $amount;
				
				// 是否允许使用积分抵扣
				if($allow_integral 	= $this->_integral_mod->_get_sys_setting('integral_enabled')) {
					$return['allow_integral'] 	= $allow_integral;
					$return['integralExchange']	= $this->_integral_mod->getIntegralByOrders($this->visitor->get('user_id'), $mealList);
				}
			
			break;
			case 'groupbuy' : 
			
				if(!isset($_SESSION['groupbuy'])){
					header('Location:index.php?app=search&act=groupbuy');
					exit;
				}
			
                /* 从购物车中取商品 */
                $group_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
				$spec_id = isset($_GET['spec_id']) ? intval($_GET['spec_id']) : 0;
				$quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 0;
				$team_id = isset($_GET['team_id']) ? intval($_GET['team_id']) : 0;
				
				$order_type     =&  ot('groupbuy');
                $groupbuy = $this->getOrderGoodsInfo($this->visitor->get('user_id'), array('group_id' => $group_id, 'spec_id' => $spec_id,'team_id' => $team_id,'quantity' => $quantity));
				if(!$groupbuy) {
					$this->show_warning($order_type->get_error());
					exit;
				}

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
                    	empty($goods['goods_image']) && $items[$key]['goods_image'] = Conf::get('default_goods_image');
					
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
				$return['otype']	=   'groupbuy';
				$return['amount']	=	$amount;
				$return['team_id']	=	$team_id;
				$return['group_id']	=	$group_id;
            break;
			
			default: 
                /* 从购物车中取商品 */
				$order_type     =&  ot('normal');
                list($cartList) = $order_type->getOrderGoodsInfo($this->visitor->get('user_id'));
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
                    	empty($goods['goods_image']) && $items[$key]['goods_image'] = Conf::get('default_goods_image');
					
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
				if($allow_integral 	= $this->_integral_mod->_get_sys_setting('integral_enabled')) {
					$return['allow_integral'] 	= $allow_integral;
					$return['integralExchange']	= $this->_integral_mod->getIntegralByOrders($this->visitor->get('user_id'), $cartList);
				}
						
            break;
        }
		
        return $return;
    }

    /**
     *    下单完成后清理商品
     *
     *    @author    MiMall
     *    @return    void
     */
    function _clear_goods($order_id, $store_id)
    {
        switch ($_GET['goods'])
        {
            case 'cart':
			
				/* 购物车中的商品 */
                /* 订单下完后清空指定购物车 */
                $model_cart =& m('cart');
                $model_cart->drop("store_id = {$store_id} AND session_id='" . SESS_ID . "' AND selected = 1");
            break;
			
			case 'groupbuy' :
			unset($_SESSION['groupbuy']);
			break;
        }
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
	
	function _add_dids($goods_info)
    {
		$appmarket_mod = &m('appmarket');
		$distribution_mod = &m('distribution');
		foreach($goods_info['orderList'] as $store_id => $order)
		{
			// 如果供货商购买了微分销功能，并且没有到期
			if(($appAvailable = $appmarket_mod->getCheckAvailableInfo('distribution', $order['store_id'])) === TRUE)
			{
				/*记录分销信息*/
				$cookie = $this->getCookieDid();
				//记录下单时的分销比率,避免下单后店铺修改分销比率使得计算错误
				$distribution_rate = $distribution_mod->get_distribution_rate($order['store_id']);
				if($cookie['did']>0 && $distribution_rate)
				{
					$goods_info['orderList'][$store_id]['did'] = $cookie['did'];
					$goods_info['orderList'][$store_id]['distribution_rate'] = serialize($distribution_rate);
				}
			}
		}
		return $goods_info;		
    }
	
	function getOrderGoodsInfo($user_id = 0, $extraParams = array())
	{
		extract($extraParams);

		if(!$group_id || !$user_id || !$spec_id || !$quantity)
		{
			$this->show_warning('该团已成团或者已过期！');
			exit;
		}
		
		$model_goodsspec = &m('goodsspec');
		$groupbuy_mod = &m('groupbuy');
		$group = $groupbuy_mod->get(array(
            'conditions' => 'group_id=' . $group_id . ' AND gb.state =' . GROUP_ON
        ));
		
		if(!$group)
		{
			$this->show_warning('该团已成团或者已过期！');
			exit;
		}
		
		if($team_id > 0){
			$team_mod = &m('team');
			$team = $team_mod->get('team_id='.$team_id.' AND status is NULL');
			if(empty($team)){
				$this->show_warning('该团已成团或者已过期！');
				exit;
			}
		}
		
		$spec_price = unserialize($group['spec_price']);
		if(!isset($spec_price[$spec_id])){
			$this->show_warning('该团已成团或者已过期！');
			exit;
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
}
?>
