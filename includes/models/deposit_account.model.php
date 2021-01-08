<?php

class Deposit_accountModel extends BaseModel
{
    var $table  = 'deposit_account';
    var $prikey = 'account_id';
    var $_name  = 'deposit_account';
	
	function _check_account($account, $user_id)
	{
		if(empty($account)) {
			return false;
		}
		if($this->get(array('conditions'=>"account='".$account."' AND user_id !=".$user_id,'fields'=>'account_id'))){
			return false;
		}
		return true;
	}
	
	function _create_deposit_account($user_id = 0)
	{
		if(!$user_id) return;
		
		$deposit_setting_mod = &m('deposit_setting');
		$deposit_setting = $deposit_setting_mod->_get_system_setting();
		
		// 如果系统设置不自动创建账户，则直接返回
		/*if(!$deposit_setting['auto_create_account']) {
			return;
		}*/
		
		if(!$this->_get_account_info($user_id))
		{
			$member_mod = &m('member');
			$user_info = $member_mod->get(array('conditions'=> 'user_id='.$user_id, 'fields' => 'user_id, email, phone_mob, user_name'));
			$time = gmtime();
			$data = array(
				'user_id'		=>	$user_id,
				'account'		=>	$this->_gen_deposit_account($user_info),
				'password'		=>  md5('123456'),//$user_info['password'], // 如果整合了UC，那么支付密码默认等于登陆密码会存在验证不通过的情况，所以支付密码改为默认为空
				'real_name'		=>	$user_info['user_name'],
				'pay_status'	=>	"ON",
				'add_time'		=>	$time,
				'last_update'	=> 	$time,
			);
			return parent::add($data);
		}
	}
	
	function _gen_deposit_account($user_info = array(), $random = false)
	{
		if($random == false)
		{
			if($user_info['user_name']) {
				$account = $user_info['user_name'];	
			} elseif($user_info['phone_mob']) {
				$account = $user_info['phone_mob'];
			}
			if(!$account) return $this->_gen_deposit_account($user_info, true);
		}
		else {
			$account = gmtime();
		}
		if(!parent::get('account="'.$account.'"')) {
			return $account;	
		} else return $this->_gen_deposit_account($user_info, true);
	}
	
	
	function _get_account_info($user_id)
	{
		if(!$user_id) return;
		
		return $this->get('user_id='.$user_id);
	}
	
	function _check_enough_money($money, $user_id=0)
	{
		if(empty($money) || !$user_id) return false;
		
		$deposit_account = $this->get(array('conditions'=>'user_id='.$user_id,'fields'=>'money'));
		if(!$deposit_account) return false;
		
		$total_money = $deposit_account['money'];
		
		return $total_money >= $money;
	}
	
	/* 验证账户密码，$ext 可以是 user_id 或 预存款账户 */
	function _check_account_password($password, $ext = 0, $is_ajax = false)
	{
		$result = false;

		if($ext && (is_numeric($ext) || is_email($ext))) 
		{
			if(is_numeric($ext)) {
				$conditions = 'user_id='.intval($ext);
			} else {
				$conditions = "account='".trim($ext)."'";
			}
			if($this->get(array('conditions'=>$conditions." AND password='".md5($password)."'",'fields'=>'account_id'))){
				$result = true;
			}
		}
		if(!$is_ajax) return $result;
		echo ecm_json_encode($result);
	}
	
	/* 可获取可用余额或者冻结金额 */
	function _get_deposit_balance($user_id, $fields = 'money')
	{
		if(!$user_id) return;
		
		if(!in_array($fields, array('money','frozen'))) $fields = 'money';
				
		if(!$deposit_account = parent::get(array('conditions'=>'user_id='.$user_id,'fields'=>'money,frozen')))
		{
			/* 如果还没有预存款账户，则新增 */
			$this->_create_deposit_account($user_id);
		}
		
		return $deposit_account[$fields];
	}
	
	/* 更新账户余额，增加（如卖出商品）或者减少 */
	function _update_deposit_money($user_id, $amount, $change='add')
	{
		if(!$user_id || $amount < 0) return false;
		
		$money = $this->_get_deposit_balance($user_id);
		
		if($change=='add') {
			$money += $amount;
		}
		else 
		{
			if($money < $amount) return false;
			
			$money = $money - $amount;
		}
		
		return parent::edit('user_id='.$user_id, array('money'=>$money));
	}
	
	/* (使用事务)更新账户余额，增加（如卖出商品）或者减少，并同时返回最新的余额 */
	function _update_deposit_money_transaction($user_id, $amount, $change='add')
	{
		$money = FALSE;
		
		if($user_id && $amount >=0)
		{
			db()->query("LOCK TABLES {$this->table} WRITE");
			
			//$money = $balance = $this->_get_deposit_balance($user_id);
			$money = db()->getOne("SELECT money FROM {$this->table} WHERE user_id={$user_id}");
			
			if($change=='add') {
				$money += $amount;
			}
			else 
			{
				if($money >= $amount) {
					$money = $money - $amount;
				}
			}
			
			if($money !== FALSE)
			{
				//if(!parent::edit('user_id='.$user_id, array('money' => $money))) {
				if(!db()->query("UPDATE {$this->table} SET money={$money} WHERE user_id={$user_id}")) {
					$money = $balance;
				}
			}
			
			db()->query("UNLOCK TABLES");
		}
		
		return $money;
	}
	
	/* 更新冻结金额，增加（如提现）或减少 */
	function _update_deposit_frozen($user_id, $amount, $change='add')
	{
		if(!$user_id || $amount < 0) return false;
		
		$frozen = $this->_get_deposit_balance($user_id, 'frozen');
		if($change=='add') {
			$frozen += $amount;
		} else $frozen = $frozen - $amount;
		
		return parent::edit('user_id='.$user_id, array('frozen'=>$frozen));
	}
	
	/* 查看当前用户余额支付是否开启 */
	function _check_pay_status($user_id = 0)
	{
		if(!$user_id) return false;
		
		$deposit_account = parent::get(array('conditions'=>'user_id='.$user_id,'fields'=>'pay_status'));
		if($deposit_account && strtoupper($deposit_account['pay_status']) == 'ON') {
			return true;
		}
		return false;
	}
	
	/* 下载某个月的对账单 */
	function downloadbill($user_id = 0, $month = '')
	{
		if(empty($month) || !$user_id) return;
		
		$deposit_trade_mod  = &m('deposit_trade');
		$deposit_record_mod = &m('deposit_record');
		
		$result = Psmb_init()->DepositApp_downloadbill($month);
		list($begin_month, $end_month) = $result;
		
		$monthbill = $deposit_record_mod->find(array(
			'conditions'=>	"user_id=".$user_id." AND status = 'SUCCESS' AND end_time >= '".$begin_month."' AND end_time <= '".$end_month."' ",
			'order'		=>	'record_id ASC',
			'join'		=>  'has_trade',
			'fields'	=>	'deposit_trade.bizOrderId, deposit_trade.title, deposit_trade.buyer_id, deposit_trade.seller_id, deposit_trade.payment_code,deposit_trade.end_time, this.*',
		));
	
		if(!$monthbill) {
			return;
		}
		
		/* xls文件数组 */
		$record_xls = array();
				
		$lang_bill = array(
			'end_time'		=>  '日期',
			'tradeTypeName'	=>	'交易类型',
			'tradeNo' 		=> 	'交易号',
    		'bizOrderId'	=> 	'商户订单号',
    		'other_account' => 	'对方账号',
    		'income_money' 	=> 	'收入金额（+元）',
    		'outlay_money' 	=> 	'支出金额（-元）',
			'balance'		=>	'账户余额（元）',
			'payment_code'  =>  '支付方式',
			'title' 		=> 	'交易标题',
			'remark'		=>	'备注',
		);
		
		$deposit_account = $this->_get_account_info($user_id);
		
		$folder = 'bill_'.local_date('Ym', $begin_month).'_'.$deposit_account['account'];
		//$file = 'bill_'.local_date('YmdHis', $begin_month).'_'.local_date('YmdHis', $end_month);
		
		$record_xls[] = $lang_bill;
		
		$bill_value = array();
		foreach($lang_bill as $key => $val)
		{
			$bill_value[$key] = '';
		}
		foreach($monthbill as $key => $bill)
    	{
			$bill_value['end_time']		=	local_date('Y-m-d H:i:s',$bill['end_time']);
			$bill_value['tradeTypeName']=	$bill['tradeTypeName'];
			$bill_value['tradeNo']		=	$bill['tradeNo'];
			$bill_value['bizOrderId']	=	$bill['bizOrderId'];
			$bill_value['balance']		=	$bill['balance'];
			$bill_value['payment_code'] =   Lang::get($bill['payment_code']);
			$bill_value['fundchannel']	=	$bill['fundchannel'];
			$bill_value['title']		=	$bill['title'];
			$bill_value['remark']   	=   $bill['remark'];
			
			if($bill['flow'] == 'income'){
				$bill_value['outlay_money'] = 0;
				$bill_value['income_money']	= $bill['amount'];
			} else {
				$bill_value['income_money'] = 0;
				$bill_value['outlay_money'] = $bill['amount'];
			}
			
			// 交易的对方信息
			$partyInfo = $deposit_trade_mod->getPartyInfoByRecord($user_id, $bill);
			$bill_value['other_account'] = $partyInfo['name'];
			if($partyInfo['account']) $bill_value['other_account'] .= '('.$partyInfo['account'].')';
			
        	$record_xls[] = $bill_value;
    	}
		
		import('excelwriter.lib');
		$ExcelWriter = new ExcelWriter(CHARSET, $folder);
		$ExcelWriter->add_array($record_xls);
		$ExcelWriter->output();
	}
} 

?>