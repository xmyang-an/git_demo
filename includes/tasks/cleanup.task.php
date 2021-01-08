<?php

/**
 *    自动交易
 *
 *    @author    MiMall
 *    @usage    none
 */
class CleanupTask extends BaseTask
{
    function run()
    {
        /* 自动确认收货 */
        $this->_auto_confirm();

        /* 自动好评 */
        $this->_auto_evaluate();

        /* 关闭过期店铺 */
        $this->_close_expired_store();

		/* 关闭过期订单 */
		$this->_close_expired_trade();
    }
	
	function _close_expired_trade()
	{
		$now = gmtime();
		
        /* 默认48小时 */
        $interval = 2 * 24 * 3600;
		
		$order_mod  		= &m('order');
		$order_log 			= &m('orderlog');
		$deposit_trade_mod 	= &m('deposit_trade');
		$integral_mod 		= &m('integral');
		
		$tradeList = $deposit_trade_mod->find(array(
			'conditions'	=> "merchantId='".MERCHANTID."' AND (bizIdentity = '".TRADE_ORDER."' OR bizIdentity = '".TRADE_RECHARGE."' OR bizIdentity = '".TRADE_BUYAPP."') AND add_time + {$interval} < {$now} AND status = 'PENDING'", 
			'fields' 		=> 'tradeNo, bizOrderId, bizIdentity'
		));
		
		foreach($tradeList as $tradeInfo)
		{
			if($deposit_trade_mod->edit("tradeNo='{$tradeInfo['tradeNo']}'", array('status' => 'CLOSED', 'end_time' => $now))) 
			{
				// 如果是普通购物订单
				if(in_array($tradeInfo['bizIdentity'], array(TRADE_ORDER))) 
				{
					$order_info = $order_mod->get(array(
						'conditions' => 'order_sn='.$tradeInfo['bizOrderId']. ' AND status=' . ORDER_PENDING, 'fields' => 'order_id, order_sn, buyer_id'));
					
					if($order_info) 
					{
						$order_mod->edit($order_info['order_id'], array('status' => ORDER_CANCELED));
					
						/* 加回订单商品库存 */
						$order_mod->change_stock('+', $order_info['order_id']);
						
						/* 记录订单操作日志 */
						$order_log->add(array(
							'order_id'  		=> $order_info['order_id'],
							'operator' 			=> '0',
							'order_status' 		=> order_status(ORDER_PENDING),
							'changed_status' 	=> order_status(ORDER_CANCELED),
							'remark'   			=> '',
							'log_time' 			=> $now,
						));
						
						// 订单取消后，归还买家之前被预扣积分 
						$integral_mod->return_integral($order_info);
					}
				}
			}
		}
	}

    /**
     *    自动确认指定时间后未确认收货的订单
     *
     *    @author    MiMall
     *    @param    none
     *    @return    void
     */
    function _auto_confirm()
    {
        $now = gmtime();
        /* 默认15天 */
        $interval = empty($this->_config['confirm_interval']) ? 15 * 24 * 3600 : intval($this->_config['confirm_interval']);
		
		
        $model_order 	= &m('order');
		$integral_mod 	= &m('integral');
		$ordergoods_mod = &m('ordergoods');
		$order_log 		= &m('orderlog');
		$refund_mod 	= &m('refund');  // 有退款功能
		$model_goodsstatistics =& m('goodsstatistics');
		$deposit_trade_mod = &m('deposit_trade');

        /* 确认收货 */
        /* 款到发货的订单 */
        $orders = $model_order->find(array(
            //'fields'    => 'order_id', by psmb
            'conditions'=> "ship_time + {$interval} < {$now} AND status = " . ORDER_SHIPPED,
        ));
        /* 货到付款的订单 */
        $cod_orders = $model_order->find(array(
            //'fields'    => 'order_id', by psmb
            'conditions'=> "ship_time + {$interval} < {$now} AND status =" . ORDER_SHIPPED . ' AND payment_code=\'cod\'',
        ));
		

        if (empty($orders) && empty($cod_orders))
        {
            return;
        }

        /* 操作日志 */
        $order_logs = array();
        $order_shipped = order_status(ORDER_SHIPPED);
        $order_finished= order_status(ORDER_FINISHED);

        /* 款到发货的订单 */
        if (!empty($orders))
        {
			foreach($orders as $order_id => $order_info)
			{
				/* 交易信息 */
				$tradeInfo = $deposit_trade_mod->get(array('conditions' => "merchantId='".MERCHANTID."' AND bizIdentity='".TRADE_ORDER."' AND bizOrderId='".$order_info['order_sn']."' AND buyer_id=".$order_info['buyer_id']));
		
				if (empty($tradeInfo))
       	 		{
            		continue;
       			}			
				
				/* 有退款功能： 如果该订单有退款商品（退款关闭的除外），则不允许确认收货 */
				$refund_mod 	= &m('refund');
				$refund = $refund_mod->get(array('conditions' => "tradeNo='".$tradeInfo['tradeNo']."'", 'fields' => 'refund_id, status'));

				if($refund && !in_array($refund['status'], array('CLOSED', 'SUCCESS'))) {
				
					continue;
				}
			
				/* 如果订单中的商品为空，则认为订单信息不完整，不执行 */
				$ordergoods_mod =& m('ordergoods');
            	$order_goods = $ordergoods_mod->find("order_id={$order_id}");
			
				if(empty($order_goods)) {

					continue;
				}	
				
				/* 更新订单状态 */
            	$model_order->edit($order_id, array('status' => ORDER_FINISHED, 'finished_time' => gmtime()));

            	if ($model_order->has_error()){
					continue;
            	}
				
				/* 转到对应的业务实例，不同的业务实例用不同的文件处理，如购物，卖出商品，充值，提现等，每个业务实例又继承支出或者收入 */
				$depopay_type    =&  dpt('income', 'sellgoods');
				$result  		 = $depopay_type->submit(array(
					'trade_info' =>  array('user_id' => $order_info['seller_id'], 'party_id' => $order_info['buyer_id'], 'amount' => $order_info['order_amount']),
					'extra_info' =>  $order_info + array('tradeNo' => $tradeInfo['tradeNo']),
					'post'		 =>	 $_POST,
				));
				
			
				if(!$result)
				{
					continue;
				}
				
				$model_distribution = &m('distribution');
				$d_profit = $model_distribution->get_profit($order_info['order_id']);
				$depopay_type    =&  dpt('income', 'distribution');
				$depopay_type->submit(array(
					'trade_info' =>  array('user_id'=>$order_info['seller_id'], 'party_id'=>$order_info['buyer_id'], 'amount'=>$order_info['order_amount']),
					'extra_info' =>  $order_info + array('tradeNo' => $tradeInfo['tradeNo'],'d_profit' => $d_profit),
					'post'		 =>	 $_POST,
				));
				
				$depopay_type    =&  dpt('income', 'refer');
				$result = $depopay_type->submit(array(
					'trade_info' =>  array('user_id'=>$order_info['seller_id'], 'party_id'=>$order_info['buyer_id']),
					'extra_info' =>  $order_info + array('tradeNo' => $tradeInfo['tradeNo']),
					'post'		 =>	 $_POST,
				));
				
				/* 买家确认收货后，即交易完成，将订单积分表中的积分进行派发 */
				$integral_mod->distribute_integral($order_info);
				
				/* 更新累计销售件数 以及将本次确认的商品 状态值修改为 交易成功 */
            	foreach ($order_goods as $key => $goods){
					$model_goodsstatistics->edit($goods['goods_id'], "sales=sales+{$goods['quantity']}");
					$ordergoods_mod->edit($goods['rec_id'], array('status'=>'SUCCESS'));
            	}
				
				/* 记录订单操作日志 */
           	 	$order_log->add(array(
					'order_id'  => $order_id,
                    'operator'  => '0',
                    'order_status' => $order_shipped,
                    'changed_status' => $order_finished,
                    'remark'    => '',
                    'log_time'  => $now,
            	));

            	$new_data = array(
                	'status'    => Lang::get('order_finished'),
                	'actions'   => array('evaluate'),
            	);
			}
        }
		
		/* 货到付款的订单 */
        if (!empty($cod_orders))
        {
			// TODO
        }
    }

    function _auto_evaluate()
    {
        $now = gmtime();

        /* 默认30天未评价自动好评 */
        $interval = empty($this->_config['evaluate_interval']) ? 30 * 24 * 3600 : intval($this->_config['evaluate_interval']);
        $goods_evaluation = array(
            'evaluation'    => 3,
            'comment'       => '',
            'credit_value'  => 1,
			'goods_evaluation'=>5,
			'service_evaluation'=>5,
			'shipped_evaluation'=>5,
        );

        /* 获取满足条件的订单 */
        $model_order =& m('order');

        /* 指定时间后已确认收货的未评价的 */
        $orders = $model_order->find(array(
            'conditions'    => "finished_time + {$interval} < {$now} AND evaluation_status = 0 AND status = " . ORDER_FINISHED,
            'fields'        => 'order_id, seller_id',
        ));

        /* 没有满足条件的订单 */
        if (empty($orders))
        {
            return;
        }

        $order_ids = array_keys($orders);

        /* 获取待评价的商品列表 */
        $model_ordergoods =& m('ordergoods');
        $order_goods = $model_ordergoods->find(array(
            'conditions'    => 'order_id ' . db_create_in($order_ids),
            'fields'        => 'rec_id, goods_id',
        ));

        /* 自动好评 */
        $model_ordergoods->edit(array_keys($order_goods), $goods_evaluation);
        $model_order->edit($order_ids, array(
                'evaluation_status' => 1,
                'evaluation_time'   => gmtime()
        ));

        $model_store =& m('store');

        /* 因为店铺ID有可能重复，因此 */
        $sellers = array();
        foreach ($orders as $order_id => $order)
        {
            $sellers[$order['seller_id']] = $order['seller_id'];
        }
        foreach ($sellers as $seller_id)
        {
            $model_store->edit($seller_id, array(
                'credit_value'  =>  $model_store->recount_credit_value($seller_id),
                'praise_rate'   =>  $model_store->recount_praise_rate($seller_id)
            ));
        }

        /* 因为商品ID有可能重复，因此 */
        $comments = array();
        foreach ($order_goods as $rec_id => $og)
        {
			!isset($comments[$og['goods_id']]) && $comments[$og['goods_id']] = 0;
            $comments[$og['goods_id']]++;
        }
        $edit_comments = array();
        foreach ($comments as $og_id => $t)
        {
            $edit_comments[$t][] = $og_id;
        }

        $model_goodsstatistics =& m('goodsstatistics');
        foreach ($edit_comments as $times => $goods_ids)
        {
            $model_goodsstatistics->edit($goods_ids, 'comments=comments+' . $times);
        }
    }

    function _close_expired_store()
    {
        $store_mod =& m('store');
        $stores = $store_mod->find(array(
            'conditions' => "state = '" . STORE_OPEN . "' AND end_time > 0 AND end_time < '" . gmtime() . "'",
            'join'       => 'belongs_to_user',
            'fields'     => 'store_id, user_id, user_name, email',
        ));

        /* 无过期店铺 */
        if (empty($stores))
        {
            return;
        }

        $ms =& ms();
        $store_ids = $store_emails = array();

        /* 消息内容 */
        $content = get_msg('toseller_store_expired_closed_notify');

        foreach ($stores as $store)
        {
            $store_ids[] = $store['store_id'];
            $store_emails[] = $store['email'];
        }

        
        $ms->pm->send(MSG_SYSTEM, $store_ids, '', $content);
        
        
        
        $store_mod->edit($store_ids, array('state' => STORE_CLOSED, 'close_reason' => Lang::get('toseller_store_expired_closed_notify')));
    }
}

?>
