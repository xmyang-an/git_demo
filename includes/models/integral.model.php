<?php

/* 积分数据模型 */
class IntegralModel extends BaseModel
{
    var $table  = 'integral';
    var $prikey = 'user_id';
    var $alias  = 'ig';
    var $_name  = 'integral';

	function update_integral($data = array())
	{
		extract($data);
		
		// 当积分开关未启用的情况下， 在数组中的积分变动类型继续执行
		$allow = array('return_integral','buying_has_integral','selling_has_integral');
		if(!in_array($type, $allow) && !$this->_get_sys_setting('integral_enabled')) {
			return;
		}
		
		if(empty($amount) || empty($user_id)) {
			return;
		}
		
		$balance = ($flow == 'minus') ? -$amount: $amount;
		
		$result = array(
			'user_id'=> $user_id,
			'amount' => $balance
		);
				
		$integral = parent::get($user_id);
		if(empty($integral))
		{
			parent::add($result);
		}
		else
		{
			$balance += $integral['amount'];
			
			if($balance < 0)
			{
				echo "余额不足";
				exit;
			}
			parent::edit($user_id, array('amount' => $balance));
		}
		
		$data['balance'] = $balance;// 积分余额	
		$integral_log_mod = &m('integral_log');
		$integral_log_mod->add_log($data);
	}
	
	// 订单取消后，归还买家之前被预扣积分
	function return_integral($order_info = array())
	{
		if(!empty($order_info))
		{
			$order_integral_mod = &m('order_integral');
			$order_integral = $order_integral_mod->get($order_info['order_id']);
			
			$integral_log_mod = &m('integral_log');
			$log = $integral_log_mod->get('order_id='.$order_info['order_id']);
			if($order_integral)
			{
				$data=array(
					'user_id' => $order_info['buyer_id'],
					'type'    => 'return_integral',
					'order_id'=> $order_info['order_id'],
					'order_sn'=> $order_info['order_sn'],
					'amount'  => $order_integral['frozen_integral'],
					'flag'    => Lang::get('return_integral_for_cancel_order')	
				);
				
				$this->update_integral($data);
				$integral_log_mod->edit($log['log_id'],array('state'=>'cancel'));
				$order_integral_mod->drop($order_info['order_id']);
			}
		}
	}
	
	//订单完成后分发积分。该操作可以不受是否开启积分的影响
	function distribute_integral($order_info = array())
	{
		if(!empty($order_info))
		{
			$store_mod = &m('store');
			$store = $store_mod->get(array(
				'conditions' => 'store_id='.$order_info['seller_id'],
				'fields'     => 'sgrade'
			));
			
			/* 订单实际金额信息（考虑折扣，调价的情况）*/
			$order_mod = &m('order');
			list($realGoodsAmount) = $order_mod->_getRealAmount($order_info['order_id']);
			
			//订单完成给买家赠送积分（按实际支付的商品总额）
			$buy_has_integral = round($realGoodsAmount * $this->_get_sys_setting(array('buying_integral',$store['sgrade'])),2);
			if($buy_has_integral > 0)
			{
				$this->update_integral(array(
					'user_id' => $order_info['buyer_id'],
					'type'    => 'buying_has_integral',
					'amount'  => $buy_has_integral,
					'order_id'=> $order_info['order_id'],
					'order_sn'=> $order_info['order_sn'],
					'flag'	  => sprintf('买家使用积分抵扣货款，订单号[%s]', $order_info['order_sn']),
				));
			}
			
			// 买家使用抵扣的积分，给卖家
			$order_integral_mod = &m('order_integral');
			$frozen_integral = $order_integral_mod->get($order_info['order_id']);
			if($frozen_integral)
			{
				if($frozen_integral['frozen_integral'] > 0)
				{
					//把冻结的积分分发给商家
					$this->update_integral(array(
						'user_id' => $order_info['seller_id'],
						'type'    => 'selling_has_integral',
						'amount'  => $frozen_integral['frozen_integral'],
						'order_id'=> $order_info['order_id'],
						'order_sn'=> $order_info['order_sn'],
						'flag'	  => sprintf('买家购买商品抵扣积分，订单号[%s]', $order_info['order_sn']),
					));	
				}
				
				$order_integral_mod->drop($order_info['order_id']);
				
				//把冻结的记录状态改为完成
				$integral_log_mod = &m('integral_log');
				$log = $integral_log_mod->get('order_id='.$order_info['order_id']);
				$integral_log_mod->edit($log['log_id'],array('state'=>'finished'));
			}
		}
	}
	
	
	//积分变动的状态，完成，取消，冻结
	
	function status($string)
	{
		$status = array(
			'finished' => 'integral_finished',
			'frozen'   => 'integral_frozen',
			'cancel'   => 'integral_cancel'
		);
		
		return Lang::get($status[$string]);
	}
	
	/*
	// 获取系统的积分配置数据，参数为字符串或者数组
	*/
	function _get_sys_setting($param)
	{
		if(!$param)
		{
			return 0;
		}
		$setting = Conf::get('integral_manage');
		if(empty($setting))
		{
			return 0;
		}
		else
		{
			if(is_array($param))
			{
				list($val1,$val2) = $param;//只要前两个值
				return $setting[$val1][$val2];
			}
			else
			{
				return($setting[$param]);
			}
		}
	}
	
	// 订单页，获取积分信息，以便做验证
	function getIntegralByOrders($user_id = 0, $goodsList = array())
	{
		$maxPoints = $getPoints = $exchange_rate = 0;
		$orderIntegral = array();
		
		if($this->_get_sys_setting('integral_enabled'))
		{
			// 积分兑换比率
			if(!$exchange_rate = $this->_get_sys_setting('exchange_rate')){
				$exchange_rate = 0;
			}
						
			$store_mod  =& m('store');
			$goods_integral_mod =& m('goods_integral');

			$integralRate = array();
			foreach($goodsList as $goods)
			{
				// 获取店铺等级对应的积分比率
				if(!isset($integralRate[$goods['store_id']])) {
					$store = $store_mod->get(array('conditions' => 'store_id='.$goods['store_id'], 'fields' => 'sgrade'));
					$integralRate[$goods['store_id']] = $this->_get_sys_setting(array('buying_integral', $store['sgrade']));
				}
				
				// （计算获得赠送的积分）如果店铺所处的等级的购物赠送积分比率大于零
				if($integralRate[$goods['store_id']] > 0)
				{
					$sgrade_integral = $integralRate[$goods['store_id']];
					$getPoints += $goods['price'] * $goods['quantity'] * $sgrade_integral;
				}
				
				// （计算可最多使用多少积分抵扣） 如果积分兑换比率大于零
				if($exchange_rate > 0)
				{
					$goods_integral = $goods_integral_mod->get($goods['goods_id']);
						
					// 如果单个商品的最大积分抵扣小于或等于单个商品的价格，则是合理的，否则，仅取能抵扣完商品价格的积分值
					$max_exchange_price = round($goods_integral['max_exchange'] * $exchange_rate, 2);
					if($max_exchange_price > $goods['price']) {
						$max_exchange_price = $goods['price'];	
					}
					
					// 购物车中每个商品可使用的最大抵扣积分值
					$goodsMaxPoints = ($max_exchange_price / $exchange_rate) * $goods['quantity'];
					
					// 每个订单最多可使用的最大抵扣积分值
					if(!isset($orderIntegral[$goods['store_id']])) $orderIntegral[$goods['store_id']] = 0;
					$orderIntegral[$goods['store_id']] += $goodsMaxPoints;
					
					$maxPoints += $goodsMaxPoints;	
					
					
				}
			}
		}
		
		/* 获取用户拥有的积分 */
		if($integral = parent::get($user_id)) {
			$userIntegral = $integral['amount'];
		} else{
			$userIntegral = 0;
		}
		
		$maxPoints = round($maxPoints, 2);
		$getPoints = round($getPoints, 2);
		
		if($maxPoints > $userIntegral) {
			
			foreach($orderIntegral as $key => $val)
			{
				$orderIntegral[$key] = round($val * ($userIntegral / $maxPoints), 2);
			}
			$maxPoints = $userIntegral;
		}
		
		$result = array(
			'maxPoints' => $maxPoints, 'userIntegral' => $userIntegral, 'getPoints' => $getPoints, 'rate' => $exchange_rate,
			'orderIntegral' => array('totalPoints' => $maxPoints, 'items' => $orderIntegral));
		
		return $result;	
	}
	
}

?>