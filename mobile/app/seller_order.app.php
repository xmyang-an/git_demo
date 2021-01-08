<?php

/**
 *    买家的订单管理控制器
 *
 *    @author    MiMall
 *    @usage    none
 */
class Seller_orderApp extends StoreadminbaseApp
{
    function index()
    {
		if(!IS_AJAX)
		{
			$this->import_resource(array('script' => 'mobile/jquery.plugins/jquery.infinite.js'));
			$this->assign('infiniteParams', json_encode($_GET));
			
			$this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('order_manage'));
			$this->_get_curlocal_title(Lang::get('order_manage'));
        	$this->display('seller_order.index.html');
		}
		else
		{
        	/* 获取订单列表 */
        	$this->_get_orders();
		}
    }

    /**
     *    查看订单详情
     *
     *    @author    MiMall
     *    @return    void
     */
    function view()
    {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

        $model_order =& m('order');
        $order_info  = $model_order->findAll(array(
            'conditions'    => "order_alias.order_id={$order_id} AND seller_id=" . $this->visitor->get('manage_store'),
			'fields'        => "buyer_id, buyer_name, seller_id, seller_name, order_amount, discount, order_sn, payment_code, payment_name, pay_time, ship_time, finished_time, postscript, extension, order.add_time as order_add_time, status",
        ));
        $order_info = current($order_info);
        if (!$order_info)
        {
            $this->show_warning('no_such_order');
            return;
        }

        /* 调用相应的订单类型，获取整个订单详情数据 */
        $order_type =& ot($order_info['extension']);
        $order_detail = $order_type->get_order_detail($order_id, $order_info);
        foreach ($order_detail['data']['goods_list'] as $key => $goods)
        {
            empty($goods['goods_image']) && $order_detail['data']['goods_list'][$key]['goods_image'] = Conf::get('default_goods_image');
		}
		
		/* 读取订单的赠品（如果有）*/
		$ordergift_mod = &m('ordergift');
		$order_detail['data']['gift_list'] = $ordergift_mod->find('order_id='.$order_id);
		
		/* 是否申请过退款 */
		$deposit_trade_mod = &m('deposit_trade');
		$refund_mod = &m('refund');
		$tradeInfo = $deposit_trade_mod->get(array('conditions' => 'merchantId="'.MERCHANTID.'" AND bizIdentity="'.TRADE_ORDER.'" AND bizOrderId="'.$order_info['order_sn'].'"', 'fields' => 'tradeNo'));
		if($tradeInfo) {
			if( $refund = $refund_mod->get(array('conditions'=>'tradeNo="'.$tradeInfo['tradeNo'].'"','fields'=>'status'))) {
				if(in_array($refund['status'], array('SUCCESS'))) {
					$order_info['refund_status_label'] = '退款成功';
				} elseif(!in_array($refund['status'], array('CLOSED'))) {
					$order_info['refund_status_label'] = '退款中';
				}
				$order_info['refund_id'] = $refund['refund_id'];
            }
		}

        $this->assign('order', $order_info);
        $this->assign($order_detail['data']);
		
		$this->_config_seo('title', Lang::get('detail') . ' - ' . Lang::get('member_center'));
		$this->_get_curlocal_title('detail');
        $this->display('seller_order.view.html');
    }

    /**
     *    调整费用
     *
     *    @author    MiMall
     *    @return    void
     */
    function adjust_fee()
    {
        list($order_id, $order_info)    = $this->_get_valid_order_info(array(ORDER_SUBMITTED, ORDER_PENDING));
        
        $model_order    =&  m('order');
        $model_orderextm =& m('orderextm');
        $shipping_info   = $model_orderextm->get($order_id);
		
        if (!IS_POST)
        {
			if (!$order_id)
        	{
            	$this->show_warning('no_such_order');

            	return;
        	}
           
            $this->assign('order', $order_info);
            $this->assign('shipping', $shipping_info);
			
			$this->import_resource('mobile/jquery.plugins/jquery.form.min.js');
			
			$this->_config_seo('title', Lang::get('adjust_fee') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('adjust_fee');
            $this->display('seller_order.adjust_fee.html');
        }
        else
        {
			if (!$order_id)
        	{
            	$this->json_error('no_such_order');

            	return;
        	}
			
            /* 订单实际总金额 */
            $order_amount = isset($_POST['order_amount']) ? abs(floatval($_POST['order_amount'])) : $order_info['order_amount'];
            if ($order_amount <= 0)
            {
                /* 如订单总价小于等于0，则不是一个有效的数据 */
                $this->json_error('invalid_fee');

                return;
            }
            $data = array(
                'order_amount'  => $order_amount,     //修改订单实际总金额
				'adjust_amount' => $order_amount - ($order_info['goods_amount'] + $shipping_info['shipping_fee'] - $order_info['discount']), //  调价幅度
                'pay_alter' => 1    //支付变更
            );
					
            $model_order->edit($order_id, $data);

            if ($model_order->has_error())
            {
				$error = current($model_order->get_error());
                $this->json_error($error['msg']);

                return;
            }
			
			// 修改对应的交易记录的交易价格
			$deposit_trade_mod = &m('deposit_trade');
			$deposit_trade_mod->edit("bizOrderId='{$order_info['order_sn']}'", array('amount' => $order_amount, 'pay_alter' => 1));
	
            /* 记录订单操作日志 */
            $order_log =& m('orderlog');
            $order_log->add(array(
                'order_id'  => $order_id,
                'operator'  => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status($order_info['status']),
                'remark'    => Lang::get('adjust_fee'),
                'log_time'  => gmtime(),
            ));

            /* 发送给买家邮件通知，订单金额已改变，等待付款 */
            $model_member =& m('member');
            $buyer_info   = $model_member->get($order_info['buyer_id']);
            $mail = get_mail('tobuyer_adjust_fee_notify', array('order' => $order_info));
            $this->_mailto($buyer_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            $new_data = array(
                'order_amount'  => price_format($order_amount),
            );

            $this->json_result(array('ret_url' => url('app=seller_order&type=pending')), 'handle_ok');
        }
    }

    /**
     *    待发货的订单发货
     *
     *    @author    MiMall
     *    @return    void
     */
    function shipped()
    {
        list($order_id, $order_info)    = $this->_get_valid_order_info(array(ORDER_SUBMITTED, ORDER_ACCEPTED, ORDER_SHIPPED));
        
        $model_order    =&  m('order');
		
        if (!IS_POST)
        {
			if (!$order_id)
        	{
            	$this->show_warning('no_such_order');

            	return;
       		}
		
            $this->assign('order', $order_info);
			
			// 快递公司列表
			if(Psmb_init()->_check_express_plugin()){
				$this->assign('express_company',include(ROOT_PATH . '/data/express_company.inc.php'));
			}
			
			$this->import_resource('mobile/jquery.plugins/jquery.form.min.js');
			
			$this->_config_seo('title', Lang::get('shipped_order') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('shipped_order');
            $this->display('seller_order.shipped.html');
        }
        else
        {
			if (!$order_id)
        	{
            	$this->json_error('no_such_order');

            	return;
       		}
			
			if (!$_POST['invoice_no'])
            {
                $this->json_error('invoice_no_empty');

                return;
            }
            $edit_data = array('status' => ORDER_SHIPPED, 'invoice_no' => $_POST['invoice_no']);
			
			if(Psmb_init()->_check_express_plugin()){
				
				if(!$_POST['express_company']) {
					$this->json_error('express_company_empty');
                	return;
				}
				$edit_data['express_company'] = trim($_POST['express_company']);
			}
			
            $is_edit = true;
            if (empty($order_info['invoice_no']))
            {
                /* 不是修改发货单号 */
                $edit_data['ship_time'] = gmtime();
                $is_edit = false;
            }
            $model_order->edit(intval($order_id), $edit_data);
            if ($model_order->has_error())
            {
				$error = current($model_order->get_error());
                $this->json_error($error['msg']);

                return;
			}
			
			/* 改变交易状态 */
			$deposit_trade_mod = &m('deposit_trade');
			$deposit_trade_mod->edit("merchantId='".MERCHANTID."' AND bizIdentity='".TRADE_ORDER."' AND bizOrderId='".$order_info['order_sn']."' AND seller_id=".$this->visitor->get('user_id'), array('status' =>'SHIPPED'));

            /* 记录订单操作日志 */
            $order_log =& m('orderlog');
            $order_log->add(array(
                'order_id'  => $order_id,
                'operator'  => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status(ORDER_SHIPPED),
                'remark'    => $_POST['remark'],
                'log_time'  => gmtime(),
            ));
			
			$new_data = array(
                'status'    => Lang::get('order_shipped'),
                'actions'   => array(
                    'cancel',
                    'edit_invoice_no'
                ), //可以取消可以发货
            );
            if ($order_info['payment_code'] == 'cod')
            {
                $new_data['actions'][] = 'finish';
            }
			
			
			/* 获取收货人的手机号 */
			$orderextm_mod =& m('orderextm');
			$orderextm = $orderextm_mod->get(array('conditions' => "order_id={$order_id}", 'fields' => 'phone_mob'));
			
			/* 短信和邮件提醒： 卖家已发货通知买家 */
			$this->sendMailMsgNotify(array_merge($order_info, array('invoice_no' => $edit_data['invoice_no'])), array(
					'key' 		=> 'tobuyer_shipped_notify',
					'touser' 	=> $order_info['buyer_id'],
				),
				array(
					'key' 		=> 'send',
					'phone_mob' => $orderextm['phone_mob'],
					'body'		=> sprintf(Lang::get('sms_send'), $order_info['order_sn'], $order_info['seller_name'])
				)
			);
			
            $this->json_result('', 'handle_ok');
        }
    }

    /**
     *    取消订单
     *
     *    @author    MiMall
     *    @return    void
     */
    function cancel_order()
    {
		$order_id = isset($_GET['order_id']) ? html_script(trim($_GET['order_id'])) : '';
        
        if (!IS_POST)
        {
			if (!$order_id)
			{
				$this->show_warning('no_such_order');
				return;
			}
			
			// 只有已提交和待付款的订单才可取消
			$status = array(ORDER_SUBMITTED, ORDER_PENDING);
			$order_ids = explode(',', $order_id);
			if ($ext)
			{
				$ext = ' AND ' . $ext;
			}
	
			$model_order    =&  m('order');
			
			$order_info     = $model_order->find(array(
				'conditions'    => "order_id" . db_create_in($order_ids) . " AND seller_id=" . $this->visitor->get('manage_store') . " AND status " . db_create_in($status) . $ext,
			));
			
			if (!$order_info)
			{
				$this->show_warning('no_such_order');
	
				return;
			}
			$ids = array_keys($order_info);
		
            $this->assign('orders', $order_info);
            $this->assign('order_id', count($ids) == 1 ? current($ids) : implode(',', $ids));
			$this->assign('ret_url',  count($ids) == 1 ? url('app=seller_order&act=view&order_id='.current($ids)) : url('app=seller_order'));
			
			$this->import_resource('mobile/jquery.plugins/jquery.form.min.js');
			
			$this->_config_seo('title', Lang::get('cancel_order') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('cancel_order');
            $this->display('seller_order.cancel.html');
        }
        else
        {
			if (!$order_id)
			{
				$this->json_error('no_such_order');
				return;
			}
			
			// 只有已提交和待付款的订单才可取消
			$status = array(ORDER_SUBMITTED, ORDER_PENDING);
			$order_ids = explode(',', $order_id);
			if ($ext)
			{
				$ext = ' AND ' . $ext;
			}
	
			$model_order    =&  m('order');
			
			$order_info     = $model_order->find(array(
				'conditions'    => "order_id" . db_create_in($order_ids) . " AND seller_id=" . $this->visitor->get('manage_store') . " AND status " . db_create_in($status) . $ext,
			));
			
			if (!$order_info)
			{
				$this->json_error('no_such_order');
	
				return;
			}
			$ids = array_keys($order_info);
			
			$deposit_trade_mod = &m('deposit_trade');
            foreach ($ids as $val)
            {
                $id = intval($val);
				
                $model_order->edit($id, array('status' => ORDER_CANCELED));
                if ($model_order->has_error())
                {
                    //$_erros = $model_order->get_error();
                    //$error = current($_errors);
                    //$this->json_error(Lang::get($error['msg']));
                    //return;
                    continue;
                }
				
				/* 修改交易记录状态为关闭 */
				$deposit_trade_mod->edit("merchantId='" . MERCHANTID . "' AND bizIdentity='".TRADE_ORDER."' AND bizOrderId='" . $order_info[$id]['order_sn']. "' AND seller_id=" . $this->visitor->get('user_id'), array('status' => 'CLOSED'));
				

                /* 加回订单商品库存 */
                $model_order->change_stock('+', $id);
                $cancel_reason = (!empty($_POST['remark'])) ? $_POST['remark'] : $_POST['cancel_reason'];
                /* 记录订单操作日志 */
                $order_log =& m('orderlog');
                $order_log->add(array(
                    'order_id'  => $id,
                    'operator'  => addslashes($this->visitor->get('user_name')),
                    'order_status' => order_status($order_info[$id]['status']),
                    'changed_status' => order_status(ORDER_CANCELED),
                    'remark'    => $cancel_reason,
                    'log_time'  => gmtime(),
                ));
				
				// 订单取消后，归还买家之前被预扣积分 
				$integral_mod = &m('integral');
				$integral_mod ->return_integral($order_info[$id]);

                /* 发送给买家订单取消通知 */
                $model_member =& m('member');
                $buyer_info   = $model_member->get($order_info[$id]['buyer_id']);
                $mail = get_mail('tobuyer_cancel_order_notify', array('order' => $order_info[$id], 'reason' => $_POST['remark']));
                $this->_mailto($buyer_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

                $new_data = array(
                    'status'    => Lang::get('order_canceled'),
                    'actions'   => array(), //取消订单后就不能做任何操作了
                );
            }
			$this->json_result('', 'handle_ok');
        }
    }

    

    /**
     *    获取有效的订单信息
     *
     *    @author    MiMall
     *    @param     array $status
     *    @param     string $ext
     *    @return    array
     */
    function _get_valid_order_info($status, $ext = '')
    {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        if (!$order_id)
        {

            return array();
        }
        if (!is_array($status))
        {
            $status = array($status);
        }

        if ($ext)
        {
            $ext = ' AND ' . $ext;
        }

        $model_order    =&  m('order');
        /* 只有已发货的货到付款订单可以收货 */
        $order_info     = $model_order->get(array(
            'conditions'    => "order_id={$order_id} AND seller_id=" . $this->visitor->get('manage_store') . " AND status " . db_create_in($status) . $ext,
        ));
        if (empty($order_info))
        {

            return array();
        }

        return array($order_id, $order_info);
    }
    /**
     *    获取订单列表
     *
     *    @author    MiMall
     *    @return    void
     */
    function _get_orders()
    {
        $page = $this->_get_page(intval($_GET['pageper']));
        $model_order =& m('order');

        !$_GET['type'] && $_GET['type'] = 'all_orders';

        $conditions = $this->_get_query_conditions(array(
            array(      //按订单状态搜索
                'field' => 'status',
                'name'  => 'type',
                'handler' => 'order_status_translator',
            ),
            array(      //按买家名称搜索
                'field' => 'buyer_name',
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
		
        /* 查找订单 */
        $orders = $model_order->findAll(array(
            'conditions'    => "seller_id=" . $this->visitor->get('manage_store') . "{$conditions}",
			'fields'        => 'buyer_id, buyer_name, seller_id, seller_name, status, order_sn, payment_code, order_amount, shipping_fee,extension,group_id,team_id',
            'count'         => true,
            'join'          => 'has_orderextm',
            'limit'         => $page['limit'],
            'order'         => 'add_time DESC',
            'include'       =>  array(
                'has_ordergoods',       //取出商品
            ),
        ));
		$page['item_count'] = $model_order->getCount();
        $this->_format_page($page);
		
		$deposit_trade_mod = &m('deposit_trade');
		$refund_mod =& m('refund');
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
			
			$orders[$key1]['can_ship'] = true;
			if($order['extension'] == 'groupbuy')//团购的订单不允许退款
			{
				$orders[$key1]['can_ship'] = false;
				if($order['team_id'] > 0){
					$team = $team_mod->get($order['team_id']);
					if($team['status'] == 1){
						$orders[$key1]['can_ship'] = true;
					}
				}
			}
        }
        
        // 必须加 array_values() js遍历顺序才对
		$data = array('result' => array_values($orders), 'totalPage' => $page['page_count']);
		echo json_encode($data);
    }
}

?>
