<?php

!defined('ROOT_PATH') && exit('Forbidden');

/**
 *    预付款支付类型基类
 *
 *    @author   Mimall
 *    @usage    none
 */
class BaseDepopay extends Object
{
	var $_errorCode= array();
	
	var $_deposit_trade_mod;
	var $_deposit_account_mod;
	var $_deposit_record_mod;
	var $_deposit_withdraw_mod;
	var $_deposit_setting_mod;
	
    function __construct($params)
    {
        $this->BaseDepopay($params);
    }
    function BaseDepopay($params)
    {
        if (!empty($params))
        {
            foreach ($params as $key => $value)
            {
                $this->$key = $value;
            }
        }
		
		$this->_deposit_trade_mod       = &m('deposit_trade');
		$this->_deposit_account_mod		= &m('deposit_account');
		$this->_deposit_record_mod 		= &m('deposit_record');
		$this->_deposit_withdraw_mod 	= &m('deposit_withdraw');
		$this->_deposit_setting_mod		= &m('deposit_setting');
    }
	
	/* 验证账户余额是否足够 */
	function _check_enough_money($money, $user_id)
	{
		return $this->_deposit_account_mod->_check_enough_money($money, $user_id);
	}
	
	/* 获取当前账户余额，或者冻结金额 */
	function _get_deposit_balance($user_id, $fields = 'money')
	{
		return $this->_deposit_account_mod->_get_deposit_balance($user_id, $fields);
	}
	
	/* 插入账户收支记录，并变更账户余额 */
	function _insert_deposit_record($data, $change_balance = TRUE)
	{
		if($record_id = $this->_deposit_record_mod->add($data))
		{
			/* 变更该用户的预存款账户余额 */
			if($change_balance)
			{
				$this->_deposit_account_mod->edit('user_id='.$data['user_id'], array('money' => $data['balance']));
				if(!$this->_deposit_account_mod->get_error()) {
					return $record_id;
				}
				else
				{
					/* 发生错误，回滚操作 */
					$this->_deposit_record_mod->drop($record_id);
				
					return FALSE;
				}
			}
			else return TRUE;
		}
		else return FALSE;
	}
	
	/* 系统扣费（交易，提现，转账等） */
	function _sys_chargeback($tradeNo, $trade_info, $rate, $type = 'trade_fee')
	{
		/* 费率不合理，不进行扣点 */
		if(!$rate || $rate <=0 || $rate >1) return TRUE;
		
		$fee  = round($trade_info['amount'] * $rate, 2);
		
		if($fee <= 0) {
			return TRUE;
		}
		
		$time = gmtime();
		
		if(is_array($type) || empty($type)) {
			$remark	= LANG::get('trade_fee').'['.$tradeNo.']';
		} else $remark = LANG::get($type).'['.$tradeNo.']';
		
		$data_trade = array(
			'tradeNo'		=>	$this->_genTradeNo(),
			'merchantId'	=>	MERCHANTID,
			'bizOrderId'	=>  $this->_genTradeNo(12),
			'bizIdentity'	=>  TRADE_CHARGE,
			'buyer_id'		=>	$trade_info['user_id'],
			'seller_id'		=>	0,
			'amount'		=>	$fee,
			'status'		=>	'SUCCESS',
			'payment_code' 	=>  'deposit',
			'tradeCat'		=>	'SERVICE',// 服务费
			'payType'		=>	'INSTANT', 
			'flow'			=>	'outlay',
			'fundchannel'   =>  Lang::get('deposit'),
			'title'			=>	LANG::get('chargeback'),
			'add_time'		=>	$time,
			'pay_time'		=>	$time,
			'end_time'		=>	$time,
		);
		
		if($this->_deposit_trade_mod->add($data_trade))
		{
			$data_record = array(
				'tradeNo'		=>	$data_trade['tradeNo'],
				'user_id'		=>	$trade_info['user_id'],
				'amount'		=>  $fee,
				'balance'		=>	$this->_get_deposit_balance($trade_info['user_id']) - $fee,
				'tradeType'		=>  'SERVICE',
				'tradeTypeName' => 	Lang::get('SERVICE'),
				'flow'			=>	'outlay',
				'name'			=>	LANG::get('chargeback'),
				'remark'		=>  $remark,
			);
			
			return $this->_insert_deposit_record($data_record);
		}
	}
	
	/* 更新冻结金额，增加（如提现）或减少 */
	function _update_deposit_frozen($user_id, $amount, $change = 'add')
	{
		if(!$user_id || $amount < 0) return false;
		
		$deposit_account_mod = &m('deposit_account');
		
		return $this->_deposit_account_mod->_update_deposit_frozen($user_id, $amount, $change);
	}
	
	/*  更新交易状态 */
	function _update_trade_status($tradeNo, $data)
	{
		if(!$tradeNo) return;
		
		$deposit_trade_mod = &m('deposit_trade');
		return $deposit_trade_mod->edit('tradeNo="'.$tradeNo.'"', $data);
	}
	
	/*  更新订单状态 */
	function _update_order_status($order_id, $data)
	{
		if(!$order_id) return;
		
		$order_mod = &m('order');
		$order = $order_mod->get('order_id='.$order_id.' AND extension="groupbuy"');
		if(!empty($order)){
			$team_mod = &m('team');
			$team_mod->handleAfterPayment($order);
		}
		
		return $order_mod->edit($order_id, $data);
	}
	
	function _get_record_info($record_id)
	{
		if(!$record_id) return false;
		
		return $this->_deposit_record_mod->get($record_id);
	}
	
	function _get_trade_info($tradeNo)
	{
		if(!$tradeNo) return FALSE;
		
		return $this->_deposit_trade_mod->get("tradeNo='{$tradeNo}'");
	}
	
	function _get_bank_info($bid)
	{
		if(!$bid)  return;
		
		$bank_mod = &m('bank');
		
		return $bank_mod->get($bid);
		
	}
	
	function _get_deposit_setting($user_id=0, $fields='')
	{
		$result = $this->_deposit_setting_mod->_get_deposit_setting($user_id,$fields);
		
		if(empty($fields)) return $result;
		
		if($result <0 || $result>1) return 0;
		
		return $result;
	}
	
	function _get_intro_by_order($order_id)
	{
		$intro = '';
		if(!$order_id) return $intro;
		
		$ordergoods_mod = &m('ordergoods');
		$order_goods = $ordergoods_mod->find(array('conditions'=>"order_id={$order_id}",'fields'=>'goods_name'));
			
		$first_goods = current($order_goods);
		if(count($order_goods) > 1) {
			$intro = $first_goods['goods_name'] . LANG::get('and_more');
		} else {
			$intro = $first_goods['goods_name'];
		}
		return addslashes($intro);
	}

    /**
     *    生成交易号
     *
     *    @author   Mimall
     *    @return    string
     */
    function _genTradeNo( $length = 0)
    {
        return $this->_deposit_trade_mod->genTradeNo( $length );
    }
	
	function _get_errors()
	{
		$ex = new DepopayException();
		if(is_array($this->_errorCode) && count($this->_errorCode) > 1){
			$error = '';
			foreach($this->_errorCode as $k=>$code)
			{
				$error .= ($k+1) . '. '. $ex->errorMsg[$code].'<br>';
			}
			return $error;
		}
		return $ex->errorMsg[$this->_errorCode[0]];
	}
}

class DepopayException extends Object
{
	var $errorMsg;
	
	function __construct() {
		
		$this->errorMsg = array(
			"10001" => "交易金额不能小于零！",
            "50001" => "交易异常！扣除的交易服务费小于零元，或者所扣除的交易服务费大于交易金额。",
            "50002" => "交易异常！交易金额小于零元。",
            "50003" => "订单异常！找不到商户订单号。",
			"50004" => "充值异常！找不到指定的银行卡信息。",
			"50005" => "交易异常！无法正确添加充值记录。",
			"50006" => "对不起！在退款给买家过程中，插入收支记录失败。",
			"50007" => "对不起！订单退款记录添加失败。",
			"50008" => "对不起！卖家收支记录添加过程中出现异常。",
			"50009" => "平台扣除卖家手续费出现异常！",
			"50010" => "对不起！无法通过商户订单号查询到该订单的交易号。",
			"50011" => "交易异常！转账过程中，无法正确添加转出记录。",
			"50012" => "交易异常！转账过程中，无法正确添加转入记录。",
			"50013" => "退款异常！无法正确修改订单状态。",
			"50014" => "对不起！订单日志插入失败。",
			"50015" => "平台扣除转账手续费出现异常！",
			"50016" => "对不起！提现过程中插入收支记录出现异常。",
			"50017" => "对不起！提现过程中冻结资金更新出现异常。",
			"50018" => "对不起！在插入提现银行卡信息过程中出现错误。",
			"50019" => "对不起！您的账户余额不足。",
			"50020" => "交易异常！插入收支记录过程中出现问题。",
			"50021" => "交易异常！无法正确修改订单状态。",
			"50022" => "操作异常！买家确认收货后无法正常修改交易状态。",
			"50023" => "交易异常！取消订单中退回给买家款项时出现插入错误。",
			"50024" => "交易异常！无法正确修改交易状态为已付款。",
			
			"60001" => "交易异常！购买应用中无法正常支付",
			"60002" => "更新所购买应用的过期时间出现异常！",
			"60003" => "无法正常变更购买应用记录中的状态",
        );
    }  
}


?>