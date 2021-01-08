<?php

class deposit_tradeModel extends BaseModel
{
    var $table  = 'deposit_trade';
    var $prikey = 'trade_id';
    var $_name  = 'deposit_trade';
	
	/**
     *    生成交易号
     *
     *    @author   Mimall
     *    @return    string
     */
    function genTradeNo( $length = 0)
    {
        /* 选择一个随机的方案 */
        mt_srand((double) microtime() * 1000000);
		
		if($length > 0) 
		{
			$tradeNo = $this->make_char( $length );
		}
		else
		{
			/* 交易号20位 */
        	$tradeNo = local_date('YmdHis', gmtime()) . str_pad(mt_rand(1, 99), 2, '0', STR_PAD_LEFT).mt_rand(1000, 9999);
		}

        $trade = parent::get("tradeNo='{$tradeNo}'");
        if (!$trade)
        {
            /* 否则就使用这个交易号 */
            return $tradeNo;
        }

        /* 如果有重复的，则重新生成 */
        return $this->genTradeNo( $length );
    }

	/* 生成对应支付接口的商户交易号，也即跟系统内的外部交易号对应 */
	function genPayTradeNo($orderInfo = array(), $length = 0)
	{
		$payTradeNo = NULL;
		
		if(empty($orderInfo)) {
			
			$payTradeNo = $this->genTradeNo( $length );
		}
		else
		{
			// 如果本次所有交易中，都有商户交易号且一样，并且支付方式没有变更，也不存在未在本次支付的交易有相同的商户交易号，则取现在的商户交易号，其他情况下都重新生成
			$genPayTradeNo = FALSE;
			$tempPayTradeNo= array();
			foreach($orderInfo['tradeList'] as $tradeInfo)
			{
				// 商户交易号为空或者支付已变更
				if(!$tradeInfo['payTradeNo'] || $tradeInfo['pay_alter']) {
					$genPayTradeNo = TRUE;
					break;
				}
				$tempPayTradeNo[] = $tradeInfo['payTradeNo'];
				
				// 去掉重复值
				$tempPayTradeNo = array_unique($tempPayTradeNo);
				
				// 说明有多个不同商户交易号
				if(count($tempPayTradeNo) > 1) {
					$genPayTradeNo = TRUE;
					break;
				}
			}
			
			// 系统中还存在相同的商户交易号，但未在本次付款中
			$samePayTradeNo = parent::find(array('conditions' => "payTradeNo='".current($tempPayTradeNo)."'"));
			if(count($samePayTradeNo) != count($orderInfo['tradeList'])) {
				$genPayTradeNo = TRUE;
				
				// 为避免交易影响，置空商户交易号（这样做的目的是，如果不置空，那么当买家又发起单交易支付时， 会继续使用原商户交易号，导致支付的金额不匹配
				$diff = array_diff(array_keys($samePayTradeNo), array_keys($orderInfo['tradeList']));
				if(parent::edit($diff, array('payTradeNo' => '')))
				{
					// 创建一个该笔商户订单号的副本，以便支付通知返回后找不到交易交易记录，无法处理已支付的资金退回问题
					$tempTradeInfo = json_encode($samePayTradeNo);
					$path = ROOT_PATH . '/data/files/mall/tradelog';
					@mkdir($path, 0777, true);
					file_put_contents($path . '/' . md5(current($tempPayTradeNo)).'.log', $tempTradeInfo, LOCK_EX);
				}
			}
			
			if($genPayTradeNo === FALSE) {
				$payTradeNo = current($tempPayTradeNo);
			}
			else {
				$payTradeNo = $this->genTradeNo( $length );
			}
		}
		
		return $payTradeNo;
	}
	
	function getPaymentDetail($paymethod = '')
	{
		//  包含有网银代号的字符串，如 Alipay|ICBC 代表支付宝网银的工行支付， 还可以拓展 Chinapay|ICBC 代表银联在线的工行支付
		if(strpos($paymethod, '|') !== FALSE)
		{
			$paymethod = explode('|', $paymethod);
			$payment_code = $paymethod[0];
			$payment_bank = $paymethod[1];
		}
		else
		{
			$payment_code = $paymethod;
			$payment_bank = '';
		}
		
		return array($payment_code, $payment_bank);
	}
	
	/* 更新每笔交易的付款方式 */
	function _updateTradePayment(&$orderInfo = array(), $paymethod = '')
	{		
		list($payment_code, $payment_bank) = $this->getPaymentDetail($paymethod);
		
		$edit_data = array('payment_code' => $payment_code, 'payment_bank' => $payment_bank);
		
		foreach($orderInfo['tradeList'] as $key => $tradeInfo)
		{
			// 如果支付方式表更了
			if($tradeInfo['payment_code'] != $payment_code) {
				$edit_data['pay_alter'] = 1;
			} else $edit_data['pay_alter'] = 0;
		
			// 如果付款方式是货到付款，则变更交易类型
			if(in_array($orderInfo['bizIdentity'], array(TRADE_ORDER)) && in_array(strtoupper($payment_code), array('COD'))) {
				$edit_data['payType'] = 'COD';
			}
			
			// 资金渠道
			$edit_data['fundchannel'] = $payment_bank ? Lang::get($payment_bank) : Lang::get($payment_code);
			
			parent::edit($tradeInfo['trade_id'], $edit_data);
			
			// 更新引用数值
			$orderInfo['tradeList'][$key] = array_merge($orderInfo['tradeList'][$key], $edit_data);
		}
				
		// 返回拆分后的数据
		return array($payment_code, $payment_bank);
	}
	
	/* 获取交易数据给网关支付后的业务处理 */
	function _getTradeInfoForNotify($payTradeNo = 0)
	{
		$result			= array();
		
		// 当支付变更后，置空受影响的商户交易号后，这里获取到的tradeList，要么就是空，要么就是全部待付款的交易记录
		$tradeList 	= parent::find("payTradeNo='{$payTradeNo}'");
		
		if(empty($tradeList)) 
		{
			/* 如果没找到交易记录，那么说明交易变更了，从交易日志中获取交易数据，待异步通知验证通过后，充值支付的金额到余额账户
			 * 情况一: 还有待付款的交易，继续完成交易流程
			 * 情况二: 没有待付款的交易，仅做充值就完结
			 */
			$tradelog = ROOT_PATH . '/data/files/mall/tradelog/'.md5($payTradeNo).'.log';
			if(file_exists($tradelog))
			{
				$tradeList = json_decode(file_get_contents($tradelog), true);
				if(!empty($tradeList)) {
					$returnMoney = TRUE; // 资金退回标记
					foreach($tradeList as $tradeInfo) {
						if($tradeInfo['status'] == 'PENDING') 
							$returnMoney = FALSE;
							break;
					}
					$result['RETURN_MONEY'] = $returnMoney;
					$result['tradelogfile'] = $tradelog;
				}
			}	
		}
		
		if($tradeList)
		{
			$firstTradeInfo 	= current($tradeList);
			
			// 获取基本参数，给网关通知验证调用（必须放循环前面以便能正确获取到值）
			$result['buyer_id']		= $firstTradeInfo['buyer_id'];
			$result['payTradeNo']	= $firstTradeInfo['payTradeNo'];
			$result['bizIdentity'] 	= $firstTradeInfo['bizIdentity'];
			$result['payment_code']	= $firstTradeInfo['payment_code'];
			$result['payment_bank']	= $firstTradeInfo['payment_bank'];
			$result['title'] 		= addslashes($firstTradeInfo['title']);
			
			$result['amount'] 	= 0;
			$order_mod 			= &m('order');
			$appbuylog_mod 		= &m('appbuylog');
			foreach($tradeList as $tradeInfo)
			{
				if($tradeInfo['status'] == 'PENDING')
				{
					if(in_array($tradeInfo['bizIdentity'], array(TRADE_ORDER))) 
					{
						$order_info = $order_mod->get('order_sn="'.$tradeInfo['bizOrderId'].'" AND status=' . ORDER_PENDING);
					}
					
					/* 如果是购买APP订单 */
					elseif(in_array($tradeInfo['bizIdentity'], array(TRADE_BUYAPP))) 
					{
						$order_info = $appbuylog_mod->get("orderId='{$tradeInfo['bizOrderId']}' AND status=" . ORDER_PENDING);
					}
					
					// 每笔交易对应的订单信息
					if($order_info){
						$tradeInfo['seller'] 		= $order_info['seller_name'];
						$tradeInfo['order_info']	= $order_info;
					}
						
					$result['tradeList'][$tradeInfo['trade_id']]	= $tradeInfo;
				}
				
				$result['amount'] += $tradeInfo['amount'];	
			}
			// 说明是合并付款
			if(count($result['tradeList']) > 1)
			{
				$result['title'] = sprintf(Lang::get('mergepay_num_order'), count($result['tradeList']));
				$result['mergePay'] = TRUE;
			}
		}
		return $result;
	}
	
	/* 验证合并付款中交易信息有效性，正确则返回交易数组等数据 */
	function _checkAndGetTradeInfo($orderId = 0, $userId = 0)
	{
		$result 	= array();
		$errorMsg 	= FALSE;
			
		if(!$orderId)
		{
			$errorMsg = Lang::get('no_such_order');
		}
		else
		{
			// 如果为多个TradeNo， 则说明是合并付款
			$listTradeNo = explode(',', $orderId);
				
			$tradeList = parent::find(array('conditions' => 'tradeNo '.db_create_in($listTradeNo)));
			
			if(empty($tradeList)) {
				$errorMsg = Lang::get('no_such_order');
			}
			else
			{
				$order_mod = &m('order');
				foreach($tradeList as $tradeInfo)
				{
					if(($tradeInfo['buyer_id'] != $userId)){
						$errorMsg = sprintf(Lang::get('not_pay_for_not_yourself'), $tradeInfo['tradeNo']);
						break;
					}
					elseif($tradeInfo['status'] != 'PENDING')
					{
						$errorMsg = sprintf(Lang::get('not_pay_for_not_pending_of_trade'), $tradeInfo['tradeNo']);
						break;
					}
						
					/* 如果是普通购物订单 */
					if(in_array($tradeInfo['bizIdentity'], array(TRADE_ORDER))) 
					{
						$order_info = $order_mod->get('order_sn="'.$tradeInfo['bizOrderId'].'"');
						if(!$order_info) 
						{
							$errorMsg = sprintf(Lang::get('not_pay_for_no_such_order'), $tradeInfo['bizOrderId']);
							break;
						}
						elseif($order_info['buyer_id'] != $userId) {
							$errorMsg = sprintf(Lang::get('not_pay_for_not_yourself'), $tradeInfo['bizOrderId']);
							break;
						}
						elseif($order_info['status'] != ORDER_PENDING)
						{
							$errorMsg = sprintf(Lang::get('not_pay_for_not_pending_of_order'), $tradeInfo['bizOrderId']);
							break;
						}
						elseif($tradeInfo['amount'] != $order_info['order_amount'])
						{
							$errorMsg = sprintf(Lang::get('not_pay_for_order_amount_invalid'), $tradeInfo['bizOrderId']);
							break;
						}
						$tradeInfo['seller'] = $order_info['seller_name'];
						$result['orderList'][$order_info['order_id']] = $order_info;
					}
					
					/* 如果是购买APP应用服务的订单（暂不考虑合并付款的的情况） */
					elseif(in_array($tradeInfo['bizIdentity'], array(TRADE_BUYAPP))) {
						$tradeInfo['seller'] = Lang::get('platform_appmarket');
					}
					
					$tradeInfo['name'] = substr($tradeInfo['title'], 9);
					
					$result['tradeList'][$tradeInfo['trade_id']]	= $tradeInfo;
						
					// 计算合并付款的总金额
					if(!isset($result['amount'])) $result['amount'] = 0;
					$result['amount'] += $tradeInfo['amount'];
					
					$result['payType'] = $tradeInfo['payType'];
				}
					
				if($errorMsg === FALSE)
				{
					// 说明是合并付款
					if(count($result['tradeList']) > 1)
					{
						$result['title'] = sprintf(Lang::get('mergepay_num_order'), count($result['tradeList']));
						$result['mergePay'] = TRUE;
					}
					else
					{
						$firstTradeInfo 	= current($result['tradeList']);
						$result['title'] 	= addslashes($firstTradeInfo['title']);
					}
				}
				
				// 获取商务业务代码
				$result['bizIdentity'] = $tradeInfo['bizIdentity'];
			}
		}
		
		return array($errorMsg, $result);
	}
	
	/* 生成指定长度的随机字符串 */
	function make_char( $length = 8 )
	{  
		// 密码字符集，可任意添加你需要的字符  
		$chars = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');  

		// 在 $chars 中随机取 $length 个数组元素键名  
		$str = '';  

		for($i = 0; $i < $length; $i++){  

   			// 将 $length 个数组元素连接成字符串  

   			$str .= $chars[array_rand($chars)];
		}
		
		if(substr( $str, 0, 1 ) == '0') {
			
			$str = $this->make_char( $length );
		}

		return $str;
	}
	
	/* 获取交易的对方信息（这里获取的是资金账户的信息） */
	function getPartyInfoByRecord($user_id, $record)
	{
		$partyInfo = array();
		$deposit_account_mod = &m('deposit_account');
		
		// 交易的对方
		$party_id = ($record['buyer_id'] == $user_id) ? $record['seller_id'] : $record['buyer_id'];
			
		/* 找出对方信息 */
		if($party_id) {
			$partyInfo = $deposit_account_mod->get(array(
				'conditions' => 'user_id=' . $party_id, 'fields' => 'real_name as name, account'));
			//$partyInfo['account'] = cut_str($partyInfo['account'], 8);
			empty($partyInfo['name']) && $partyInfo['name'] = $partyInfo['account'];
			
			$member_mod = &m('member');
			$member = $member_mod->get(array('conditions' => 'user_id='.$party_id, 'fields' => 'portrait'));
			if($member) $partyInfo['portrait'] = portrait($party_id, $member['portrait']);
		}
		else {
			if(in_array($record['tradeCat'], array('WITHDRAW', 'RECHARGE')) && $record['fundchannel']) {
				$partyInfo = array('name' => $record['fundchannel']);
			}
			elseif(in_array($record['bizIdentity'], array(TRADE_BUYAPP))) { // 此处使用商户业务类型来判断并不合适，以后再优化
				$partyInfo = array('name' => Lang::get('platform_appmarket'));
			}
			else $partyInfo = array('name' => Lang::get('platform'));
		}
		return $partyInfo;
	}	
}

?>