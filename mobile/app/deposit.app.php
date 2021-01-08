<?php

class DepositApp extends DepositbaseApp
{
	var $_deposit_account_mod;
	var $_deposit_trade_mod;
	var $_deposit_record_mod;
	var $_deposit_recharge_mod;
	var $_deposit_withdraw_mod;
	var $_deposit_setting_mod;
	var $_bank_mod;
	var $_order_mod;
	var $_member_mod;
	var $_refund_mod;
	
	
	/* 构造函数 */
    function __construct()
    {
         $this->DepositApp();
    }

    function DepositApp()
    {
        parent::__construct();
		$this->_deposit_account_mod = &m('deposit_account');
		$this->_deposit_trade_mod   = &m('deposit_trade');
		$this->_deposit_record_mod	= &m('deposit_record');
		$this->_deposit_recharge_mod= &m('deposit_recharge');
		$this->_deposit_withdraw_mod= &m('deposit_withdraw');
		$this->_deposit_setting_mod = &m('deposit_setting');
		$this->_bank_mod = &m('bank');
		$this->_order_mod = &m('order');
		$this->_member_mod = &m('member');
		$this->_refund_mod = &m('refund');
    }
	
    function index()
    {
		$deposit_account = $this->_deposit_account_mod->get(array('conditions'=>'user_id='.$this->visitor->get('user_id')));
		$bank_list = $this->_bank_mod->find(array('conditions'=>'user_id='.$this->visitor->get('user_id'), 'fileds' => 'bid', 'count' => true));

		$this->assign('bank_list', array('count'=> $this->_bank_mod->getCount()));
		$this->assign('deposit_account', $deposit_account);
		$this->assign('portrait', portrait($this->visitor->get('user_id'), $this->visitor->get('portrait')));
		
		$this->_config_seo('title', Lang::get('deposit_index') . ' - ' . Lang::get('member_center'));
		$this->_get_curlocal_title('deposit_index');
		$this->display('deposit.index.html');
    }
	
	/* 配置预存款账户信息 */
	function config()
	{		
		if(!IS_POST)
		{
			$deposit_account = $this->_deposit_account_mod->get(array('conditions'=>'user_id='.$this->visitor->get('user_id')));
			$this->assign('deposit_account', $deposit_account);
			$this->assign('ret_url', url('app=deposit'));
			
			$this->import_resource(array(
            	'script' => array(
					array(
                    	'path' => 'mobile/jquery.plugins/jquery.form.min.js',
                	),
					array(
                    	'path' => 'mobile/dialog/dialog.js',
                    	'attr' => 'id="dialog_js"',
                	),
                	array(
                    	'path' => 'mobile/jquery.ui/jquery.ui.js',
                    	'attr' => '',
					)
				)
        	));
			
			$this->_config_seo('title', Lang::get('deposit_config') .' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('deposit_config');
			$this->display('deposit.config.html');
		}
		else
		{
			$deposit_account = $this->_deposit_account_mod->get(array('conditions'=>'user_id='.$this->visitor->get('user_id')));
			
			$account = trim($_POST['account']);
			$real_name = trim($_POST['real_name']);
			$password = trim($_POST['password']);
			$password_config = trim($_POST['password_confirm']);
			$pay_status = strtoupper(trim($_POST['pay_status'])) == 'ON' ? 'ON' : 'OFF';
			$codeType 	= trim($_POST['codeType']);
			$code     	= trim($_POST['code']);
			
			if(empty($account)) {
				$this->json_error('account_empty');
				return;
			}
			/* 如果不是注册后自动创建的账户，则限制是手机或是邮箱（此处是为了考虑第三方登录后某些特特情况没有验证手机/邮箱的情况，不过此种情况几率不大） */
			if(!$deposit_account) {
				$this->json_error('account_invalid');
				return;
			}
			
			if(!$this->_deposit_account_mod->_check_account($account, $this->visitor->get('user_id'))){
				$this->json_error('account_exist');
				return;
			}
			if(empty($real_name))
			{
				$this->json_error('real_name_empty');
				return;
			}
			if(!$deposit_account && empty($password)) {
				$this->json_error('password_empty');
				return;
			}
			if($password != $password_config) {
				$this->json_error('password_confirm_error');
				return;
			}
			if(!in_array($pay_status,array('ON','OFF')))
			{
				$this->json_error('illegal_param');
				return;
			}
			
			$member = $this->_member_mod->get(array('conditions'=>'user_id='.$this->visitor->get('user_id'), 'fields'=>'email, phone_mob'));
			if($codeType == 'email')
			{
				if(($_SESSION['email_code'] != md5($member['email'].$code)) || ($_SESSION['last_send_time_email_code'] + 120 < gmtime())) {
					$this->json_error('email_code_check_failed');
					return;
						
				}
			}
			elseif($codeType == 'phone')
			{
				if(($_SESSION['phone_code'] != md5($member['phone_mob'].$code)) || ($_SESSION['last_send_time_phone_code'] + 120 < gmtime())) {
					$this->json_error('phone_code_check_failed');
					return;
						
				}
			} else {
				$this->json_error('unknown error');
				return;
			}
			
			$time = gmtime();
			$data = array(
				'user_id'		=>	$this->visitor->get('user_id'),
				'account'		=>	$account,
				'password'		=> 	md5($password),
				'real_name'		=>	$real_name,
				'pay_status'	=>	$pay_status,
				'add_time'		=>	$time,
				'last_update'	=> 	$time,
			);
			
			if($deposit_account)
			{
				unset($data['user_id'], $data['money'], $data['frozen'], $data['add_time']);
				if(empty($password)) unset($data['passowrd']);
				
				if($this->_deposit_account_mod->edit('user_id='.$this->visitor->get('user_id'), $data)){
					$this->json_result(array('ret_url' => url('app=deposit')), 'edit_ok');
					return;
				}	
			}
			else
			{
				if($this->_deposit_account_mod->add($data)){
					$this->json_result(array('ret_url' => url('app=deposit')), 'add_ok');
					return;
				}
			}
		}
	}
	
	/* 查询单笔收支详细 */
	function record()
	{
		$tradeNo = trim($_GET['tradeNo']);
		
		if(empty($tradeNo)) {
			$this->show_warning('error');
			return;
		}
		$record = $this->_deposit_trade_mod->get("tradeNo='".$tradeNo."'");
		
		if(!$record) {
			$this->show_warning('no_record');
			return;
		}
		
		//  这笔交易既不是买家，也不是卖家，则认为当前用户跟这笔交易无关，无法访问交易信息
		if(!in_array($this->visitor->get('user_id'), array($record['buyer_id'], $record['seller_id']))){
			$this->show_warning('no_record');
			return;
		}
		
		// 交易的对方
		$record['partyInfo'] = $this->_deposit_trade_mod->getPartyInfoByRecord($this->visitor->get('user_id'), $record);
		
		if($extraInfo = $this->_deposit_record_mod->get("user_id=".$this->visitor->get('user_id'). " AND tradeNo='".$tradeNo."'")){
			$record = array_merge($record, $extraInfo);
		}
		
		$record['status_label'] = LANG::get('TRADE_'.strtoupper($record['status']));
		
		/* 如果是商品订单 */
		if(in_array($record['bizIdentity'], array(TRADE_ORDER)))
		{
			$order = $this->_order_mod->findAll(array(
				'conditions'	=> "order_sn='".$record['bizOrderId']."'", 
				'fields'		=> 'order.order_id, order.buyer_id, order.seller_id,order_amount,shipping_fee,shipping_name,payment_name,seller_name as store_name',
				'join'			=> 'has_orderextm',
				//'include'		=> array('has_ordergoods') // mobile client no need
			));
			reset($order);
			$order = current($order);
			if($order) 
			{
				// 查询交易是否有退款
				list($refund, $status_label) 		= $this->_checkTradeHasRefund($record);
				if($refund) {
					$record['refundInfo']   = $refund;
					$record['status_label'] = $status_label;
					
					// 扣除退款金额后的交易应付金额
					$record['amount'] 		= number_format($refund['total_fee'] - $refund['refund_total_fee'], 2);
					$record['tradeAmount'] 	= $refund['total_fee'];
				}
			}
			
			$record['orderInfo'] = $order;
		}
		$this->assign('tradeInfo', $record);
	
		$this->_config_seo('title', Lang::get('record_detail') . ' - ' . Lang::get('member_center'));
		$this->_get_curlocal_title('record_detail');
		$this->display('deposit.record.html');
	}
	
	/* 交易记录 */
	function tradelist()
	{
		if(!IS_AJAX)
		{
			$this->import_resource(array('script' => 'mobile/jquery.plugins/jquery.infinite.js'));
			$this->assign('infiniteParams', json_encode($_GET));
			
			$this->_config_seo('title', Lang::get('tradelist') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('tradelist');
			$this->display('deposit.tradelist.html');
		}
		else
		{
			$page = $this->_get_page(intval($_GET['pageper']));
			$recordlist = $this->_deposit_trade_mod->find(array(
				'conditions'=>	"(buyer_id=".$this->visitor->get('user_id'). " OR seller_id=" . $this->visitor->get('user_id') . ")",
				'limit' 	=>  $page['limit'],
				'order'		=>	'trade_id DESC',
				'fields'	=>  'tradeNo, title, flow, amount, buyer_id, seller_id, add_time, tradeCat, bizIdentity, status',
				'count'		=>  true
			));
			$page['item_count'] = $this->_deposit_trade_mod->getCount();
			$this->_format_page($page);
			
			foreach($recordlist as $key => $record)
			{
				$recordlist[$key]['add_time'] = local_date('Y-m-d H:i:s', $record['add_time']);
				
				// 如果当前用户是交易的卖方
				if($record['seller_id'] == $this->visitor->get('user_id')) {
					$recordlist[$key]['flow'] = ($record['flow'] == 'income') ?  'outlay' : 'income';
				}
				
				// 交易的对方
				$recordlist[$key]['partyInfo'] = $this->_deposit_trade_mod->getPartyInfoByRecord($record['buyer_id'], $record);
				
				// 赋值中文状态值（且检查是否退款）
				list($refund, $status_label) 		= $this->_checkTradeHasRefund($record);
				$recordlist[$key]['refund'] 		= $refund;
				$recordlist[$key]['status_label'] 	= $status_label;
			}
			
			/* 统计总交易额（支出也累加）和交易笔数 */
			$list = $this->_deposit_record_mod->find(array(
				'conditions'	=>	"user_id=".$this->visitor->get('user_id'),
				'order'			=>	'record_id DESC',
				'join'			=>  'has_trade',
				'fields'		=>	'this.flow, this.amount',
			));
			
			$total_amount = $total_bill = 0;
			foreach($list as $key=>$val)
			{
				$total_amount += $val['amount'];
				$total_bill++;
			}
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array(
				'retval' => array(
								'list' => array_values($recordlist), 
								'total_amount' => $total_amount, 
								'total_bill' => $total_bill
							)
				), 
				'totalPage' => $page['page_count']
			);
			echo json_encode($data);
		}
	}

	/* 财务明细 */
	function recordlist()
	{
		if(!IS_AJAX)
		{
			$this->import_resource(array('script' => 'mobile/jquery.plugins/jquery.infinite.js'));
			$this->assign('infiniteParams', json_encode($_GET));
			
			$this->_config_seo('title', Lang::get('recordlist') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('recordlist');
			$this->display('deposit.recordlist.html');
		}
		else
		{
			$page = $this->_get_page(intval($_GET['pageper']));
			$recordlist = $this->_deposit_record_mod->find(array(
				'conditions'	=>	"user_id=".$this->visitor->get('user_id'),
				'limit' 		=>  $page['limit'],
				'order'			=>	'record_id DESC',
				'join'			=>  'has_trade',
				'fields'		=>  'this.tradeNo, this.tradeTypeName, this.balance, this.flow, this.amount, deposit_trade.add_time, status',
				'count'			=>  true
			));
			$page['item_count'] = $this->_deposit_record_mod->getCount();
			$this->_format_page($page);
			
			foreach($recordlist as $key => $record)
			{
				$recordlist[$key]['add_time'] = local_date('Y-m-d H:i:s', $record['add_time']);
			}
	
			/* 统计总收入和总支出 */
			$list = $this->_deposit_record_mod->find(array(
				'conditions'	=>	"user_id=".$this->visitor->get('user_id'),
				'order'			=>	'record_id desc',
				'join'			=>  'has_trade',
				'fields'		=>	'this.flow, this.amount',
			));
			
			$total_income = $total_outlay = 0;
			foreach($list as $key=>$val)
			{
				if($val['flow']=='income') $total_income += $val['amount'];
				else $total_outlay += $val['amount'];
			}
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array(
				'retval' => array(
								'list' => array_values($recordlist), 
								'total_income' => $total_income, 
								'total_outlay' => $total_outlay
							)
				), 
				'totalPage' => $page['page_count']
			);
			echo json_encode($data);
		}
	}
	
	/* 冻结明细 */
	function frozenlist()
	{
		if(!IS_AJAX)
		{
			$this->import_resource(array('script' => 'mobile/jquery.plugins/jquery.infinite.js'));
			$this->assign('infiniteParams', json_encode($_GET));

			$this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('frozenlist'));
			$this->_get_curlocal_title('frozenlist');
			$this->display('deposit.frozenlist.html');
		}
		else
		{
			$add_time_from	= trim($_GET['add_time_from']);
			$add_time_to	= trim($_GET['add_time_to']);
			
			$conditions = $conditions_time = $conditions_extra = '';
			if($add_time_from) {
				$conditions_time .= " AND add_time >='".gmstr2time($add_time_from)."'";
			}
			if($add_time_to) {
				$conditions_time .= " AND add_time <='".gmstr2time($add_time_to)."'";
			}
			
			// 待审核的提现（目前冻结的这样待审核的提现，如果还有其他类型的交易，则加到此）
			$conditions_extra .= " AND tradeCat = 'WITHDRAW' AND status='WAIT_ADMIN_VERIFY' ";
			
			$conditions = $conditions_time . $conditions_extra;
			
			$page = $this->_get_page(intval($_GET['pageper']));
			
			$recordlist = $this->_deposit_record_mod->find(array(
				'conditions'	=>	"buyer_id=".$this->visitor->get('user_id'). $conditions,
				'limit' 		=>  $page['limit'],
				'order'			=>	'record_id DESC',
				'join'			=>  'has_trade',
				'count'			=>  true
			));
	
			$total_income = $total_outlay = 0;
			foreach($recordlist as $key => $record)
			{
				// 交易的对方
				$recordlist[$key]['partyInfo'] = $this->_deposit_trade_mod->getPartyInfoByRecord($this->visitor->get('user_id'), $record);
				
				/* 统计某段时间内的总冻结金额 */
				if($record['flow']=='income') $total_income += $record['amount'];
				else $total_outlay += $record['amount'];
				
				$recordlist[$key]['status_label'] = Lang::get(strtolower($record['status']));
				$recordlist[$key]['add_time'] = local_date('Y-m-d H:i:s', $record['add_time']);
			}
	
			$page['item_count'] = $this->_deposit_record_mod->getCount();
			$this->_format_page($page);
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($recordlist), 'totalPage' => $page['page_count']);
			echo json_encode($data);		
		}
	}
	
	
	/* 充值 */
	function recharge()
	{
		if(!$this->_has_account())
		{
			$this->show_warning('has_not_account');
			return;
		}
		
		if(!IS_POST)
		{
			$deposit_account = $this->_deposit_account_mod->get(array(
				'conditions'=>'user_id='.$this->visitor->get('user_id'),'fields'=>'money'));
			$this->assign('deposit_account', $deposit_account);
		
			/* 获取可用于充值的支付方式列表 */
			$all_payments = $this->_getRechargeAvailablePayments();
			$this->assign('payments', $all_payments);
			
			$this->_config_seo('title', Lang::get('deposit_recharge') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('deposit_recharge');
			$this->display('deposit.recharge.html');
		}
		else
		{
			// 此为取得再次付款的充值交易的交易号（之前没有付款成功的充值订单）
			$tradeNo 	    = html_script(trim($_POST['tradeNo']));
			
			if(!$tradeNo)
			{
				// 创建充值交易号
				$tradeNo 			= $this->_deposit_trade_mod->genTradeNo();
				$payment_method 	= html_script(trim($_POST['payment_code']));
				$money 				= html_script(trim($_POST['money']));
			
				/* 买家选择的支付方式数组（考虑使用支付宝直连网银支付的情况） */
				$payment_method = explode('|', $payment_method);
				list($payment_code, $payment_bank) = array($payment_method[0], $payment_method[1]);

			}
			// 如果是待付款的充值，再付款，则不需要再插入记录
			else
			{
				$tradeInfo = $this->_deposit_trade_mod->get("tradeNo='{$tradeNo}' AND buyer_id=" . $this->visitor->get('user_id'));
				if($tradeInfo)
				{
					$payment_code = $tradeInfo['payment_code'];
					$payment_bank = $tradeInfo['payment_bank'];
					$money = $tradeInfo['amount'];
				}
			}
			
			$payment_model =& m('payment');
			
            /* 检查用户所使用的付款方式是否在允许的范围内 */
			$all_payments = $this->_getRechargeAvailablePayments();
			if(!in_array($payment_code, $payment_model->getKeysOfPayments($all_payments)))
			{
				$this->show_warning('payment_not_available');
				return;
			}
			
			$payment_info  = $payment_model->get("payment_code ='{$payment_code}' AND store_id=0");
				
            /* 若卖家没有启用，则不允许使用 */
            if (!$payment_info['enabled'])
            {
                $this->show_warning('payment_disabled');

                return;
            }
			
			if(!$tradeInfo)
			{
				/* 转到对应的业务实例，不同的业务实例用不同的文件处理，如购物，卖出商品，充值，提现等，每个业务实例又继承支出或者收入 */
				$depopay_type    =&  dpt('income', 'recharge');
					
				/* 插入充值记录表，状态为：待付款 */
				$result 	= $depopay_type->submit(array(
					'trade_info' =>  array('user_id' => $this->visitor->get('user_id'), 'party_id' => 0, 'amount' => $money),
					'extra_info' =>  array('tradeNo' => $tradeNo, 'is_online' => 1),
					'post'		 =>	 $_POST + array('payment_code'=> $payment_code, 'payment_bank' => $payment_bank),
				));
				
				if(!$result)
				{
					$this->show_warning($depopay_type->_get_errors());
					return;
				}
			}
			
			// 获得格式化后的交易数据
			list($errorMsg, $orderInfo) = $this->_deposit_trade_mod->_checkAndGetTradeInfo($tradeNo, $this->visitor->get('user_id'));
			
			if($errorMsg !== FALSE) {
				$this->show_warning($errorMsg);
				return;
			}
			
			/* 生成支付URL或表单 */
			$payment    = $this->_get_payment($payment_code, $payment_info);
			$payment_form = $payment->get_payform($orderInfo);
			
			/* 通过其中一笔记录，获取商户交易号 */
			$getTradeInfo = end($orderInfo['tradeList']);
			$payTradeNo = $getTradeInfo['payTradeNo'];
			
			$this->assign('payform', $payment_form);
            $this->assign('payment', $payment_info);
			$this->assign('payTradeNo', $payTradeNo);
			
			/* 跳转到真实收银台 */
            $this->_config_seo('title', Lang::get('cashier'));
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->display('deposit.payform.html');	
		}
	}
	
	/* 充值卡充值 */
	function cardrecharge()
	{
		if(!$this->_has_account())
		{
			$this->show_warning('has_not_account');
			return;
		}
		
		if(!IS_POST)
		{	
			$this->_config_seo('title', Lang::get('deposit_recharge') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('deposit_recharge');
			$this->display('deposit.cardrecharge.html');
		}
		else
		{
			$cardNo = trim($_POST['cardNo']);
			$password = trim($_POST['password']);
			
			// 验证卡号和密码
			$cashcard_mod = &m('cashcard');
			if(!$cashcard = $cashcard_mod->get("cardNo='{$cardNo}' AND password='{$password}'")) {
				$this->show_warning('cashcard_verify_fail');
				return;
			}
			if($cashcard['active_time'] > 0) {
				$this->show_warning('cashcard_already_used');
				return;
			}
			if($cashcard['expire_time'] && ($cashcard['expire_time'] <= gmtime())) {
				$this->show_warning('cashcard_already_expired');
				return;
			}
			
			/* 转到对应的业务实例，不同的业务实例用不同的文件处理，如购物，卖出商品，充值，提现等，每个业务实例又继承支出或者收入 */
			$depopay_type    =&  dpt('income', 'cardrecharge');
					
			/* 插入充值记录表，状态为：待付款 */
			$result 	= $depopay_type->submit(array(
				'trade_info' =>  array('user_id' => $this->visitor->get('user_id'), 'party_id' => 0, 'amount' => $cashcard['money']),
				'extra_info' =>  array('tradeNo' => $this->_deposit_trade_mod->genTradeNo(), 'is_online' => 0),
				'post'		 =>	 array('payment_code' => 'RECHARGECARD', 'payment_bank' => '', 'card_id' => $cashcard['id'], 'remark' => $cashcard['cardNo']),
			));
				
			if(!$result)
			{
				$this->show_warning($depopay_type->_get_errors());
				return;
			}
			$this->show_message(sprintf(Lang::get('cashcard_recharge_ok'), $cashcard['money']), '', url('app=deposit'));
		}
	}
	
	/* 充值记录 */
	function rechargelist()
	{
		if(!IS_AJAX)
		{
			$this->import_resource(array('script' => 'mobile/jquery.plugins/jquery.infinite.js'));
			$this->assign('infiniteParams', json_encode($_GET));
			
			$this->_config_seo('title', Lang::get('rechargelist') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('rechargelist');
			$this->display('deposit.rechargelist.html');		
		}
		else
		{
			$add_time_from	= trim($_GET['add_time_from']);
			$add_time_to	= trim($_GET['add_time_to']);
			$status			= trim($_GET['status']);
			
			$conditions = '';
			if($add_time_from) {
				$conditions .= " AND add_time >='".gmstr2time($add_time_from)."'";
			}
			if($add_time_to) {
				$conditions .= " AND add_time <='".gmstr2time($add_time_to)."'";
			}
			if($status) {
				$status = strtoupper($status) == 'VERIFING' ? 'WAIT_ADMIN_VERIFY' : 'SUCCESS';
				$conditions .= " AND status='".$status."'";
			}
	
			$page = $this->_get_page(intval($_GET['pageper']));
			
			$recordlist = $this->_deposit_trade_mod->find(array(
				'conditions'	=>	"merchantId = '".MERCHANTID."' AND tradeCat = 'RECHARGE' AND buyer_id=".$this->visitor->get('user_id'). $conditions,
				'limit' 		=>  $page['limit'],
				'order' 		=>	'trade_id DESC',
				'count'			=>  true
			));
			
			foreach($recordlist as $key => $record)
			{
				$recordlist[$key]['status_label'] = Lang::get('TRADE_'.$record['status']);
				$recordlist[$key]['add_time'] = local_date('Y-m-d H:i:s', $record['add_time']);
			}
			
			$page['item_count'] = $this->_deposit_trade_mod->getCount();
			$this->_format_page($page);
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($recordlist), 'totalPage' => $page['page_count']);
			echo json_encode($data);
		}
	}
	
	/* 提现申请 */
	function withdraw()
	{
		$deposit_account = $this->_deposit_account_mod->get(array('conditions'=>'user_id='.$this->visitor->get('user_id'),'fields'=>'money'));
		$bank_list = $this->_bank_mod->find(array('conditions'=>'user_id='.$this->visitor->get('user_id')));
		foreach($bank_list as $key => $val) {
			$bank_list[$key]['formatnum'] = '****'.substr($val['num'], -4);
		}
		$this->assign('deposit_account', $deposit_account);
		$this->assign('bank_list', $bank_list);

		$this->_get_curlocal_title('deposit_withdraw');
		$this->_config_seo('title', Lang::get('deposit_withdraw') . ' - ' . Lang::get('member_center'));
		$this->display('deposit.withdraw.html');
	}
	
	/* 提现确认 */
	function withdraw_confirm()
	{
		$bid = intval($_GET['bid']);
		$money = floatval($_GET['money']);
		
		if($rate = $this->_deposit_setting_mod->_get_deposit_setting($this->visitor->get('user_id'),'withdraw_rate')){
			$fee = round($money * $rate, 2);
		}
		else $fee = 0;

		if(!IS_POST)
		{
			if(!$this->_bank_mod->get($bid)) {
				$this->show_warning('select_bank_error');
				return;
			}
		
			/* 验证提现金额 */
			if(empty($money) || $money <=0 )
			{
				$this->show_warning('money_error');
				return;
			}
			
			$bank = $this->_bank_mod->get($bid);
			$deposit_account = $this->_deposit_account_mod->get(array('conditions'=>'user_id='.$this->visitor->get('user_id'),'fields'=>'money'));
			$this->assign('bank', $bank);
			$this->assign('deposit_account', $deposit_account);
			$this->assign('widthdraw', array('money' => $money, 'total' => $money, 'fee' => $fee));
			
			$this->import_resource('mobile/jquery.plugins/jquery.form.min.js');
					
			$this->_config_seo('title', Lang::get('deposit_withdraw') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('deposit_withdraw');
			$this->display('deposit.withdraw_confirm.html');
		}
		else
		{	
			if(!$this->_bank_mod->get($bid)) {
				$this->json_error('select_bank_error');
				return;
			}
		
			/* 验证提现金额 */
			if(empty($money) || $money <=0 )
			{
				$this->json_error('money_error');
				return;
			}
			
			$password = trim($_POST['password']);
			
			if(!$this->_deposit_account_mod->_check_account_password($password, $this->visitor->get('user_id')))
			{
				$this->json_error('password_error');
				return;
			}
			
			if (base64_decode($_SESSION['captcha']) != strtolower($_POST['captcha']))
            {
                $this->json_error('captcha_failed');
                return;
            }
			
			/* 转到对应的业务实例，不同的业务实例用不同的文件处理，如购物，卖出商品，充值，提现等，每个业务实例又继承支出或者收入 */
			$depopay_type    =&  dpt('outlay', 'withdraw');
			
			$result = $depopay_type->submit(array(
				'trade_info' =>  array('user_id' => $this->visitor->get('user_id'), 'party_id' => 0, 'amount' => $money, 'fee' => $fee),
				'extra_info' =>  array('tradeNo' => $this->_deposit_trade_mod->genTradeNo()),
				'post'		 =>	 $_POST,
			));
			
			if(!$result)
			{
				$this->json_error($depopay_type->_get_errors());
				return;
			}
			$this->json_result('', 'add_ok_wait_verify');
		}
	}
	
	/* 提现记录 */
	function drawlist()
	{
		if(!IS_AJAX)
		{
			$this->import_resource(array('script' => 'mobile/jquery.plugins/jquery.infinite.js'));
			$this->assign('infiniteParams', json_encode($_GET));
			
			$this->_config_seo('title', Lang::get('drawlist') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('drawlist');
			$this->display('deposit.drawlist.html');
		}
		else
		{
			$add_time_from	= trim($_GET['add_time_from']);
			$add_time_to	= trim($_GET['add_time_to']);
			$status			= trim($_GET['status']);
			
			$conditions = '';
			if($add_time_from) {
				$conditions .= " AND add_time >='".gmstr2time($add_time_from)."'";
			}
			if($add_time_to) {
				$conditions .= " AND add_time <='".gmstr2time($add_time_to)."'";
			}
			if($status) {
				$status = strtoupper($status) == 'VERIFING' ? 'WAIT_ADMIN_VERIFY' : 'SUCCESS';
				$conditions .= " AND status='".$status."'";
			}
	
			$page = $this->_get_page(intval($_GET['pageper']));
			
			$recordlist = $this->_deposit_trade_mod->find(array(
				'conditions'	=>	"merchantId = '".MERCHANTID."' AND tradeCat = 'WITHDRAW' AND buyer_id=".$this->visitor->get('user_id') . $conditions,
				'limit' 		=>  $page['limit'],
				'order'			=>	'trade_id DESC',
				'count'			=>  true
			));
	
			foreach($recordlist as $key => $record)
			{
				$recordlist[$key]['status_label'] = Lang::get('TRADE_'.$record['status']);
				$recordlist[$key]['add_time']     = local_date('Y-m-d H:i:s', $record['add_time']);
			
				$draw = $this->_deposit_withdraw_mod->get("orderId='{$record['bizOrderId']}'");
				$card_info = unserialize($draw['card_info']);
				$card_info['type_label'] = LANG::get($card_info['type']);
				$recordlist[$key]['card_info']  = $card_info;
			}
	
			$page['item_count'] = $this->_deposit_trade_mod->getCount();
			$this->_format_page($page);
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($recordlist), 'totalPage' => $page['page_count']);
			echo json_encode($data);
		}
	}
	
	/* 转账 */
	function transfer()
	{
		$deposit_account = $this->_deposit_account_mod->get(array('conditions'=>'user_id='.$this->visitor->get('user_id'),'fields'=>'money,account'));
		$this->assign('deposit_account', $deposit_account);
	
		$this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('deposit_transfer'));
		$this->_get_curlocal_title('deposit_transfer');
		$this->display('deposit.transfer.html');	
	}
	
	/* 转账确认 */
	function transfer_confirm()
	{
		$money = floatval($_GET['money']);
		$account = trim($_GET['account']);
		
		$deposit_account = $this->_deposit_account_mod->get(array('conditions'=>'user_id='.$this->visitor->get('user_id')));
		
		if($rate = $this->_deposit_setting_mod->_get_deposit_setting($this->visitor->get('user_id'),'transfer_rate')){
			$fee = round($money * $rate, 2);
		}
		else $fee = 0;
		
		if(!IS_POST)
		{
			$member = $this->_member_mod->get(array('conditions' => 'user_name="'.$account.'"', 'fields' => 'portrait,user_id'));
			
			if(!$party = $this->_deposit_account_mod->get(array('conditions'=>" user_id='".$member['user_id']."' ")))
			{
				$this->show_warning('select_account_not_exist');
				return;
			}
			// 对方头像
			if($member) $party['portrait'] = portrait($member['user_id'], $member['portrait']);
			
			if($deposit_account['account'] == $account)
			{
				$this->show_warning('select_account_yourself');
				return;
			}
			
			if($deposit_account['pay_status'] != 'ON')
			{
				$this->show_warning('pay_status_off');
				return;
			}
			
			/* 验证转账金额 */
			if(empty($money) || $money <=0 )
			{
				$this->show_warning('money_error');
				return;
			}
			$this->assign('party', $party);
			$this->assign('transfer', array('money' => $money, 'fee' => $fee));
			
			$this->import_resource('mobile/jquery.plugins/jquery.form.min.js');
		
			$this->_config_seo('title', Lang::get('deposit_transfer') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('deposit_transfer');
			$this->display('deposit.transfer_confirm.html');
		}
		else
		{
			$member = $this->_member_mod->get(array('conditions' => 'user_name="'.$account.'"', 'fields' => 'portrait,user_id'));
			
			if(!$party = $this->_deposit_account_mod->get(array('conditions'=>" user_id='".$member['user_id']."' ")))
			{
				$this->json_error('select_account_not_exist');
				return;
			}
			
			if($deposit_account['account'] == $party['account'])
			{
				$this->json_error('select_account_yourself');
				return;
			}
			
			if($deposit_account['pay_status'] != 'ON')
			{
				$this->json_error('pay_status_off');
				return;
			}
			
			/* 验证转账金额 */
			if(empty($money) || $money <=0 )
			{
				$this->json_error('money_error');
				return;
			}

			$password = trim($_POST['password']);
			if(!$this->_deposit_account_mod->_check_account_password($password, $this->visitor->get('user_id')))
			{
				$this->json_error('password_error');
				return;
			}
			
			if (base64_decode($_SESSION['captcha']) != strtolower($_POST['captcha']))
            {
                $this->json_error('captcha_failed');
                return;
            }
			
			$party = $this->_deposit_account_mod->get(array('conditions'=>" account='".$party['account']."' ",'fields'=>'user_id'));
			
			/* 转到对应的业务实例，不同的业务实例用不同的文件处理，如购物，卖出商品，充值，提现等，每个业务实例又继承支出或者收入 */
			$depopay_type    =&  dpt('outlay', 'transfer');
			
			$result = $depopay_type->submit(array(
				'trade_info' =>  array('user_id' => $this->visitor->get('user_id'), 'party_id' => $party['user_id'], 'amount' => $money, 'fee' => $fee),
				'extra_info' =>  array('tradeNo' => $this->_deposit_trade_mod->genTradeNo()),
				'post'		 =>	 $_POST,
			));
			
			if(!$result)
			{
				$this->json_error($depopay_type->_get_errors());
				return;
			}
			$this->json_result('', 'transfer_ok');
		}	
	}
	
	/* 月账单下载 */
	function monthbill()
	{
		$monthbill = $this->_deposit_record_mod->find(array(
			'conditions'	=>	"status = 'SUCCESS' AND (end_time > 0) AND user_id = ".$this->visitor->get('user_id'),
			'order'			=>	'record_id DESC',
			'join'			=>  'has_trade',
			'fields'		=>	'deposit_trade.end_time, this.*',
		));

		$bill_list = array();
		
		/* 按月进行归类 */
		if($monthbill)
		{
			foreach($monthbill as $key => $bill)
			{
				$year_month = local_date('Y-m', $bill['end_time']);
				$bill_list[$year_month][$bill['flow'].'_money'] += $bill['amount'];
				$bill_list[$year_month][$bill['flow'].'_count'] += 1;
				
				/* 如果是支出，判断是否是服务费 */
				if($bill['flow'] == 'outlay' && ($bill['tradeType'] == 'SERVICE'))
				{
					$bill_list[$year_month][$bill['tradeType'].'_money'] += $bill['amount'];
					$bill_list[$year_month][$bill['tradeType'].'_count'] += 1;
				}
			}
		}
		$this->assign('monthbill', $bill_list);
			
		$this->_config_seo('title',  Lang::get('monthbill') . ' - ' . Lang::get('member_center'));
        $this->_get_curlocal_title('monthbill');
		$this->display('deposit.monthbill.html');
		
	}
	
	/* 下载某个月的对账单 */
	function downloadbill()
	{
		$month = trim($_GET['month']);
		if(empty($month)) return;
		
		$this->_deposit_account_mod->downloadbill($this->visitor->get('user_id'), $month);
	}
	
	function pay_status()
	{
		$status = strtoupper(trim($_GET['status']));
		if(!in_array($status, array('ON','OFF'))){
			$this->json_error('pay_status_error');
			return;
		}

		$status == 'OFF' ? 'ON' : 'OFF';
		if(!$this->_deposit_account_mod->edit('user_id='.$this->visitor->get('user_id'),array('pay_status'=> $status))){
			$this->json_error('handle_fail');
			return;
		}
		$this->json_result('','handle_ok');
	}
	
	function check_deposit_password_ajax()
	{
		$password = trim($_GET['password']);
		$this->_deposit_account_mod->_check_account_password($password, $this->visitor->get('user_id'), true);
	}
	
	/* 查询该笔交易是否有退款 */
	function _checkTradeHasRefund($tradeInfo)
	{
		$status_label = Lang::get('TRADE_'.strtoupper($tradeInfo['status']));
		
		$refund = $this->_refund_mod->get(array(
			"conditions" => "tradeNo='{$tradeInfo['tradeNo']}'", "fields" => "refund_id, status, total_fee, refund_total_fee"));
		
		if($refund)
		{
			if(!in_array($refund['status'], array('CLOSED', 'SUCCESS'))) {
				$refund['status_label'] = Lang::get('REFUND_'.strtoupper($refund['status']));
				$status_label = ($tradeInfo['buyer_id'] == $this->visitor->get('user_id')) ? Lang::get('has_apply_refund') : Lang::get('party_apply_refund');
			}
			
			// 如果是退款成功，则获取退款的金额
			if(in_array($refund['status'], array('SUCCESS'))) {
				/*if($record['buyer_id'] == $this->visitor->get('user_id')) {
					$refund['amount'] = $refund['total_fee'] - $refund['refund_total_fee'];
				} else $refund['amount'] = $refund['refund_total_fee'];
				*/
				$refund['amount'] = $refund['refund_total_fee'];
			}
		}
		return array($refund, $status_label);
	}
	
	function _getRechargeAvailablePayments()
	{
		$payment_model =& m('payment');
		$payments = $payment_model->get_enabled(0);
		$all_payments = array();
			
		foreach ($payments as $key => $payment)
		{
			if ($payment['is_online'])
			{
				// 充值操作不支持余额付款方式
				if(!in_array($payment['payment_code'], array('deposit'))) {
					$all_payments[] = $payment;
				}
			}
		}
		return $all_payments;
	}
	
	function send_email()
    {
        if (IS_POST)
        {
            //$email_from = Conf::get('site_name');
            //$email_type = Conf::get('email_type');
            //$email_host = Conf::get('email_host');
            //$email_port = Conf::get('email_port');
            //$email_addr = Conf::get('email_addr');
            //$email_id   = Conf::get('email_id');
            //$email_pass = Conf::get('email_pass');
			
			$email_captcha = mt_rand(1000,9999);
			$_SESSION['email_captcha'] = base64_encode($email_captcha);
			$_SESSION['email_captcha_time'] = gmtime() + 60; // 过期时间设置为60秒
			
			$deposit_account = $this->_deposit_account_mod->get('user_id='.$this->visitor->get('user_id'));
			if($deposit_account && $deposit_account['account']) {
				$email = $deposit_account['account'];
			} else {
				$email = trim($_POST['email']);
			}

            $email_subject = Conf::get('site_title') . LANG::get('mail_account_active');
            $email_content = sprintf(LANG::get('mail_captcha'), Conf::get('site_title'), $email_captcha);

            /* 使用mailer类 */
            //import('mailer.lib');
           // $mailer = new Mailer($email_from, $email_addr, $email_type, $email_host, $email_port, $email_id, $email_pass);
            //$mail_result = $mailer->send($email, $email_subject, $email_content, CHARSET, 1);
            
			$mail_result = $this->_mailto($email, addslashes($email_subject), addslashes($email_content), 1);
			if ($mail_result)
            {
                $this->json_result('', sprintf(LANG::get('mail_send_succeed'), $email));
            }
            else
            {
                $this->json_error('mail_send_failure', implode("\n", $mailer->errors));
           }  
        }
        else
        {
           $this->show_warning('Hacking Attempt');
        }
    }
	
	function _mailto($to, $subject, $message, $priority = MAIL_PRIORITY_MID)
	{
		$model_mailqueue =& m('mailqueue');
		$mails = array();
		$to_emails = is_array($to) ? $to : array($to);
		foreach ($to_emails as $_to)
		{
			$mails[] = array(
				'mail_to'       => $_to,
				'mail_encoding' => CHARSET,
				'mail_subject'  => $subject,
				'mail_body'     => $message,
				'priority'      => $priority,
				'add_time'      => gmtime(),
			);
		}
        
		$mq = $model_mailqueue->add($mails);
		$this->_sendmail(true);
			
		return $mq;
     }
}

?>
