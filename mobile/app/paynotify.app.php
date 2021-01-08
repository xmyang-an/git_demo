<?php

/**
 *    支付网关通知接口
 *
 *    @author   MiMall
 *    @usage    none
 */
class PaynotifyApp extends MallbaseApp
{
    /**
     *    支付完成后返回的URL，在此只进行提示，不对订单进行任何修改操作,这里不严格验证，不改变订单状态
     *
     *    @author    MiMall
     *    @return    void
     */
    function index()
    {
        //这里是支付宝，财付通等当订单状态改变时的通知地址
		$payTradeNo    = isset($_GET['payTradeNo']) ? trim($_GET['payTradeNo']) : 0;
		
		// 为了在用户页面不显示支付网关GET过来的隐私数据，把隐私数据过滤点后执行一次跳转
		if(array_diff(array_keys($_GET), array('app', 'payTradeNo'))){
		 	header('Location:index.php?app=paynotify&payTradeNo='.$payTradeNo);
			exit;
		}
		
        if (!$payTradeNo)
        {
            /* 无效的通知请求 */
            $this->show_warning('forbidden');

            return;
        }

		$deposit_trade_mod 	= &m('deposit_trade');
		
		// 检索出最后支付的单纯充值或购物（或购买应用）订单，如果最后一笔是支付成功的，那么认为都是支付成功了
		$tradeInfo = $deposit_trade_mod->get(array(
			'conditions' => 'buyer_id='.$this->visitor->get('user_id')." AND payTradeNo='{$payTradeNo}'", 'order' => 'trade_id DESC'));
	
		if(empty($tradeInfo))
		{
			// 由于支付变更，通过商户交易号找不到对应的交易记录后，插入的资金退回记录
			$tradeInfo = $deposit_trade_mod->get(array(
				'conditions' => 'buyer_id='.$this->visitor->get('user_id')." AND tradeNo='{$payTradeNo}'", 'order' => 'trade_id DESC'));
				
			// 资金退回标记
			if($tradeInfo) $tradeInfo['RETURN_MONEY']	= TRUE;
		}
		
		if(empty($tradeInfo))
		{
			$this->show_warning('trade_info_empty');
			return;
		}
		
		$bizIdentity 	= $tradeInfo['bizIdentity'];
		$status 		= $tradeInfo['status'];
		$status_label 	= in_array($status, array('PENDING', 'CLOSED')) ? Lang::get('pay_failed') : Lang::get('pay_success');
		$payInfo 		= array('payment_code' => $tradeInfo['payment_code'], 'status' => $status, 'status_label' => $status_label, 'Links'  => array(
			array('text' => Lang::get('view_trade'), 'link' => url('app=deposit&act=record&tradeNo='.$tradeInfo['tradeNo']))));
			
		/* 获取订单信息（以显示不同的连接） */
		if(in_array($bizIdentity, array(TRADE_ORDER)))
		{
			$order_mod = &m('order');
			$order_info = $order_mod->get(array('conditions' => 'order_sn='.$tradeInfo['bizOrderId'], 'fields' => 'order_id'));
			
			!in_array($status, array('PENDING', 'CLOSED')) && $payInfo['status_label'] = Lang::get('pay_successed_for_shipped');
			
			// 货到付款的订单
			if(in_array($tradeInfo['payment_code'], array('cod'))) {
				$payInfo['status_label'] = Lang::get('pay_result_for_cod');
			}
			
			$payInfo['Links'] = array_merge($payInfo['Links'], array(
				array('text' => Lang::get('view_order'), 'link' => url('app=buyer_order&act=view&order_id='.$order_info['order_id'])),
				//array('text' => Lang::get('shopping_continue'), 'link' => site_url())
			));
		} 
		elseif(in_array($bizIdentity, array(TRADE_RECHARGE)))
		{
			if(!in_array($status, array('PENDING', 'CLOSED'))) {
				$payInfo['status_label'] = $tradeInfo['RETURN_MONEY'] ? Lang::get('return_successed') : Lang::get('recharge_successed');
			} else $payInfo['status_label'] = $tradeInfo['RETURN_MONEY'] ? Lang::get('return_failed') : Lang::get('recharge_failed');
			
			if($tradeInfo['RETURN_MONEY']) {
				$payInfo['Links'] = array_merge($payInfo['Links'], array(
					array('text' => Lang::get('view_balance'), 'link' => url('app=deposit')),
					//array('text' => Lang::get('view_return_record'), 'link' => url('app=deposit&act=record&tradeNo='.$tradeInfo['tradeNo'])),
					array('text' => Lang::get('view_order'), 'link' => url('app=buyer_order'))
				));
			}
			else
			{
				$payInfo['Links'] = array_merge($payInfo['Links'], array(
					array('text' => Lang::get('view_balance'), 'link' => url('app=deposit')),
					array('text' => Lang::get('recharge_continue'), 'link' => url('app=deposit&act=recharge'))
				));
			}
		}
		elseif(in_array($bizIdentity, array(TRADE_BUYAPP))) 
		{
			$payInfo['Links'] = array_merge($payInfo['Links'], array(
				array('text' => Lang::get('view_balance'), 'link' => url('app=deposit')),
				//array('text' => Lang::get('view_buyapp'), 'link' => url('app=appmarket&act=my'))
			));
		}
		
		$this->assign('back_url', url('app=buyer_order'));
		
		$this->assign('payInfo', $payInfo);
		$this->_get_curlocal_title('paynotify_status');
		$this->_config_seo('title', Lang::get('paynotify_status') . ' - ' . Conf::get('site_title'));
        $this->display('paynotify.index.html');
    }
	
	function wxWaitPay()
	{
		$payTradeNo    = isset($_GET['payTradeNo']) ? trim($_GET['payTradeNo']) : 0;
		
        if (!$payTradeNo)
        {
            /* 无效的通知请求 */
            $this->show_warning('forbidden');

            return;
        }
		
		$deposit_trade_mod 	= &m('deposit_trade');
		// 检索出最后支付的单纯充值或购物（或购买应用）订单，如果最后一笔是支付成功的，那么认为都是支付成功了
		$tradeInfo = $deposit_trade_mod->get(array(
			'conditions' => 'buyer_id='.$this->visitor->get('user_id')." AND payTradeNo='{$payTradeNo}'", 'order' => 'trade_id DESC'));

		if (empty($tradeInfo))
        {
            /* 无效的通知请求 */
            $this->show_warning('forbidden');
            return;
        }
		
		$bizIdentity 	= $tradeInfo['bizIdentity'];
		$link = array();
		if(in_array($bizIdentity, array(TRADE_ORDER)))
		{
			$order_mod = &m('order');
			$order_info = $order_mod->get(array('conditions' => 'order_sn='.$tradeInfo['bizOrderId'], 'fields' => 'order_id'));
				
			// 货到付款的订单
			if(in_array($tradeInfo['payment_code'], array('cod'))) {
				$this->show_warning('forbidden');
            	return;
			}
			
			$link = array(
				'repayLink' => 'index.php?app=cashier&order_id='.$order_info['order_id'],
				'backLink'  => 'index.php?app=buyer_order&order_id='.$order_info['order_id']
			);
		} 
		elseif(in_array($bizIdentity, array(TRADE_RECHARGE)))
		{
			$link = array(
				'repayLink' => url('app=deposit&act=recharge'),
				'backLink'  => url('app=deposit')
			);
		}
		elseif(in_array($bizIdentity, array(TRADE_BUYAPP))) 
		{
			$link = array(
				'repayLink' => url('app=appmarket'),
				'backLink'  => url('app=appmarket')
			);
		}
		
		$this->assign('tradeInfo', $tradeInfo);
		$this->assign('link', $link);
		$this->_get_curlocal_title('paynotify_status');
		$this->_config_seo('title', Lang::get('paynotify_status') . ' - ' . Conf::get('site_title'));
		$this->display('paynotify.wxwaitpay.html');
	}

    /**
     *    支付完成后，外部网关的通知地址，在此会进行订单状态的改变，这里严格验证，改变订单状态
     *
     *    @author    MiMall
     *    @return    void
     */
    function notify()
    {
		// 暂时先保留数据抓取, 避免支付成功后业务处理有误,可以修复
		logResult1('post', $_POST);
		logResult1('get', $_GET);
		
        //这里是支付宝，财付通等当订单状态改变时的通知地址
		$payTradeNo    = isset($_GET['payTradeNo']) ? html_script(trim($_GET['payTradeNo'])) : 0;
		
        if (!$payTradeNo)
        {
            /* 无效的通知请求 */
            $this->show_warning('order_info_empty');

            return;
        }
	
		$deposit_trade_mod 	= &m('deposit_trade');
		
		$orderInfo = $deposit_trade_mod->_getTradeInfoForNotify($payTradeNo);
		
		if (!$orderInfo)
        {
            /* 无效的通知请求 */
            $this->show_warning('order_info_empty');

            return;
        }
		
		$payment_code = $orderInfo['payment_code'];
		
		/* 货到付款的订单不进入此通知页面 */
        if(in_array(strtoupper($payment_code), array('COD'))) {
			
			$this->show_warning('forbidden');
			return;
		}

        $model_payment =& m('payment');
		$payment_info  = $model_payment->get("payment_code='{$payment_code}'");
		
        if (empty($payment_info))
        {
            /* 没有指定的支付方式 */
            $this->show_warning('no_such_payment');

            return;
        }
		
        /* 调用相应的支付方式 */
        $payment = $this->_get_payment($payment_code, $payment_info);
		
        /* 获取验证结果 */
        $notify_result = $payment->verify_notify($orderInfo, true);
        if ($notify_result === false)
        {
            /* 支付失败 */
            $payment->verify_result(false);
            return;
        }
		
		/* 当支付结果通知验证成功后，说明买家已经实际支付了款项，那么处理业务逻辑 */ 
		list($notifyMoney, $outTradeNo) = $payment->_getNotifySpecificData();
		
		/* 将买家在支付网关支付的钱（兼容处理充值的订单），充值到余额账户里（增加收支记录，变更账户余额） */
		if(!$result = $this->_handleRechargeAfterNotify($orderInfo, $notify_result, $outTradeNo)) {
			
			/* 充值失败 */
			$this->show_warning('recharge_error');
			
			return;
		}	
			
		// 购物订单（处理购物逻辑）
		if(in_array($orderInfo['bizIdentity'], array(TRADE_ORDER)))
		{
			$this->_handleOrderAfterNotify($orderInfo, $notify_result);
		}	
		
		// 购买应用订单（处理购买应用逻辑）
		if(in_array($orderInfo['bizIdentity'], array(TRADE_BUYAPP)))
		{
			$this->_handleBuyappAfterNotify($orderInfo, $notify_result);
		}
			
		$payment->verify_result(true);
    }
	
	/* 异步通知后的充值订单处理 */
	function _handleRechargeAfterNotify($orderInfo, $notify_result, $outTradeNo)
	{
		if(!in_array($notify_result['target'], array(ORDER_ACCEPTED, ORDER_FINISHED))) {
			
			/* 订单状态不合适 */
			$this->show_warning('trade_info_error');
			
			return;
		}
			
		/* 转到对应的业务实例，不同的业务实例用不同的文件处理，如购物，卖出商品，充值，提现等，每个业务实例又继承支出或者收入 */
		$depopay_type    =&  dpt('income', 'recharge');
				
		/* 线上充值响应通知 */
		$result 	= $depopay_type->respond_notify($orderInfo, $notify_result, $outTradeNo);
		
		return $result;
	}
	
	/* 异步通知后的购物订单处理 */
	function _handleOrderAfterNotify($orderInfo, $notify_result)
	{
		if(!in_array($notify_result['target'], array(ORDER_ACCEPTED, ORDER_FINISHED))) {
			
			/* 订单状态不合适 */
			$this->show_warning('trade_info_error');
			
			return;
		}
		
		if(empty($orderInfo['tradeList'])) {
			
			$this->show_warning('trade_info_error');
			
			return;
		}
			
		/* 转到对应的业务实例，不同的业务实例用不同的文件处理，如购物，卖出商品，充值，提现等，每个业务实例又继承支出或者收入 */
		$depopay_type    =&  dpt('outlay', 'buygoods');
				
		foreach($orderInfo['tradeList'] as $tradeInfo)
		{
			$order_info		= $tradeInfo['order_info'];
			$result 		= $depopay_type->respond_notify(array(
				'trade_info' => array('user_id' => $tradeInfo['buyer_id'], 'party_id' => $tradeInfo['seller_id'], 'amount' => $tradeInfo['amount']),
				'extra_info' => $order_info + array('tradeNo' => $tradeInfo['tradeNo'], 'status' => $tradeInfo['status']),
				'post'		 =>	$_POST,
			));
					
			if(!$result) {
				//$this->show_warning($depopay_type->_get_errors());
				//return;
				continue;
			}
	
			/* 短信和邮件提醒： 买家已付款通知卖家 */
			$this->sendMailMsgNotify($order_info, array(
					'key' => 'toseller_online_pay_success_notify'
				),
				array(
					'key' => 'pay', 
					'body' => sprintf(Lang::get('sms_pay'), $order_info['order_sn'], $order_info['buyer_name'])
				)
			);
		}
	}

	/* 异步通知后的购买应用订单处理 */
	function _handleBuyappAfterNotify($orderInfo, $notify_result)
	{
		if(!in_array($notify_result['target'], array(ORDER_ACCEPTED, ORDER_FINISHED))) {
			
			/* 订单状态不合适 */
			$this->show_warning('trade_info_error');
			
			return;
		}
		
		if(empty($orderInfo['tradeList'])) {
			
			$this->show_warning('trade_info_error');
			
			return;
		}
		
		/* 转到对应的业务实例，不同的业务实例用不同的文件处理，如购物，卖出商品，充值，提现等，每个业务实例又继承支出或者收入 */
		$depopay_type    =&  dpt('outlay', 'buyapp');
		
		/* 目前暂不考虑同时支付多个购买APP的交易，所以循环只有一次 */
		foreach($orderInfo['tradeList'] as $tradeInfo)
		{
			$order_info		= $tradeInfo['order_info'];
			
			$result 		= $depopay_type->respond_notify(array(
				'trade_info' => array('user_id' => $tradeInfo['buyer_id'], 'party_id' => 0, 'amount' => $tradeInfo['amount']),
				'extra_info' => $order_info + array('tradeNo' => $tradeInfo['tradeNo']),
				//'post'		 =>	$_POST,
			));

			if(!$result) {
				//$this->show_warning($depopay_type->_get_errors());
				//return;
				continue;
			}
		}
	}
	
	function wxAjaxParameters()
	{
		$payTradeNo    	= isset($_GET['payTradeNo']) ? html_script(trim($_GET['payTradeNo'])) : 0;
		$code       	= html_script($_GET['code']);
		
        if (!$payTradeNo)
        {
            /* 无效的通知请求 */
            $this->json_error('order_info_empty');
            return;
        }
	
		$deposit_trade_mod 	= &m('deposit_trade');
		
		$orderInfo = $deposit_trade_mod->_getTradeInfoForNotify($payTradeNo);
		
		if (!$orderInfo)
        {
            /* 无效的通知请求 */
            $this->json_error('order_info_empty');
            return;
        }
		
		if(!in_array($orderInfo['payment_code'], array('wxminiprogram'))){
			$this->json_error('Hacking Attempt');
			return;
		}
		
		$payment_code = $orderInfo['payment_code'];

		$payment_model =& m('payment');
		$payment_info  = $payment_model->get("payment_code = '{$payment_code}'");
			
		$payment    = $this->_get_payment($payment_code, $payment_info);
		$jsApiParameters = $payment->getParameters($code, $orderInfo, $payTradeNo);	

		echo $jsApiParameters;
	}
	
	function wxOauthCallBack()
	{
		$payTradeNo    	= isset($_GET['payTradeNo']) ? html_script(trim($_GET['payTradeNo'])) : 0;
		$code       	= html_script($_GET['code']);
		
        if (!$payTradeNo)
        {
            /* 无效的通知请求 */
            $this->show_warning('order_info_empty');

            return;
        }
	
		$deposit_trade_mod 	= &m('deposit_trade');
		
		$orderInfo = $deposit_trade_mod->_getTradeInfoForNotify($payTradeNo);
		
		if (!$orderInfo)
        {
            /* 无效的通知请求 */
            $this->show_warning('order_info_empty');

            return;
        }
		
		if(!in_array($orderInfo['payment_code'], array('wxpay'))){
			$this->show_warning('Hacking Attempt');
			return;
		}
		
		$payment_code = $orderInfo['payment_code'];

		$payment_model =& m('payment');
		$payment_info  = $payment_model->get("payment_code = '{$payment_code}'");
			
		$payment    = $this->_get_payment($payment_code, $payment_info);
		$jsApiParameters = $payment->getParameters($code, $orderInfo, $payTradeNo);	
			
		if($payment->has_error()){
			//$this->show_warning($payment->get_error());
			//return;
			$payment_form = $payment->get_payform($orderInfo);
			exit;
		}
	
		$params = array('orderInfo' => $orderInfo, 'jsApiParameters' => $jsApiParameters);
	
		$redirect_uri = site_url() . "/index.php?app=paynotify&payTradeNo={$payTradeNo}";
	
		$this->assign('redirect_uri', $redirect_uri);
		$this->assign('payform', $params);
		$this->_get_curlocal_title('cashier');
		$this->display('cashier.wxpay.html');
	}
}

?>