<?php

/**
 * 短信公用类库
 * 第三方短信接口：中国网建
 *
 */

class SMS extends Object
{
	var $_gateway = 'http://utf8.sms.webchinese.cn/?';
	var $_setting = null;
	
	function __construct($options = null)
    {
        $this->SMS($options);
    }
    function SMS($options = null)
    {
		$msg_setting_mod = &m('msg_setting');
		$setting = $msg_setting_mod->get('');
		$this->_setting = $setting;
    }
	
	function getNumGetWay()
	{
		if(strtoupper(CHARSET) == 'GBK') {
			return 'http://sms.webchinese.cn/web_api/SMS/GBK';
		} else return 'http://sms.webchinese.cn/web_api/SMS';
	}
	function getGetWay()
	{
		if(strtoupper(CHARSET) == 'GBK') {
			$this->_gateway = str_replace('utf8', 'gbk', $this->_gateway);
		}
		return $this->_gateway;
	}
	function getStatus()
	{
		if(!empty($this->_setting['msg_pid']) && !empty($this->_setting['msg_key'])) 
		{
			return true;
		} else {
			return false;	
		}
	}
	function getNum()
	{
		if($this->getStatus() === false) {
			return;
		}
		else
		{
			$url = $this->getNumGetWay() . '/?Action=SMS_Num&Uid=' . $this->_setting['msg_pid'].'&Key='.$this->_setting['msg_key'];

			return ecm_curl($url);
		}
	}
	function send($params = array())
	{
		if($this->getStatus() === false) {
			return false;
		}
		else
		{
			$sendResult = false; // 短信发送结果
			
			$mod_msglog = &m('msglog');
			
			$params = $this->checkData($params);
			extract($params);
			
			/* 此处是插入本站验证的错误日志 */
			if($errorMsg) 
			{
				$data = array(
					'user_id' 	=> $sender,
					'to_mobile' => $phone_mob,
					'content' 	=> $text,
					'quantity' 	=> 0,
					'state' 	=> 0,
					'result' 	=> $errorMsg,
					'time' 		=> gmtime(),
				);
				$mod_msglog->add($data);
			}
			else
			{
				$url =  $this->getGetWay() . 'Uid='.$this->_setting['msg_pid'].'&Key='.$this->_setting['msg_key'].'&smsMob='.$phone_mob.'&smsText='.urlencode($text);
				$result = ecm_curl($url);
				
				$data = array();
				if($result >0)
				{
					$sendResult = true;
					
					 /* 已经使用短信数，写入统计 */
					$mod_msgstatistics = &m('msgstatistics');
					$mod_msgstatistics->edit(0,"used = used +".$result);
					
					/* 如果是用户发送减少用户的短信数 */
					if($sender >0) {
						$mod_msg = &m('msg');
						$mod_msg->edit("user_id='{$sender}'","num = num - ".$result);
					}
					
					$data = array(
						'user_id' 	=> $sender,
						'to_mobile' => $phone_mob,
						'content' 	=> $text,
						'quantity' 	=> $result,
						'state' 	=> 1,
						'result' 	=> $this->getMessage($result),
						'time' 		=> gmtime(),
					);
				}
				
				/* 本次是插入短信平台发送失败的错误日志 */
				else
				{
					$data = array(
						'user_id' 	=> $sender,
						'to_mobile' => $phone_mob,
						'content' 	=> $text,
						'quantity' 	=> 0,
						'state' 	=> 0,
						'result' 	=> $this->getMessage($result),
						'time' 		=> gmtime(),
					);
					
				}
				$mod_msglog->add($data);
			}
		
			return $sendResult;
		}
		
	}
	
	function checkData($params = array())
	{
		$result = array();
		
		if(!isset($params['phone_mob']) || empty($params['phone_mob'])){
			
			/* 如果手机号为空，则通过发送者的用户ID获取手机号 */
			if(isset($params['sender']) && $params['sender'] > 0) {
				$member_mod = &m('member');
				$member = $member_mod->get(array('conditions' => 'user_id='.$params['sender'], 'fields' => 'phone_mob'));
				if($member && $member['phone_mob'] && is_mobile($member['phone_mob'])){
					$params['phone_mob'] = $member['phone_mob'];
				} else $result = array('errorMsg' => $this->getMessage(-4));
			} else {
				$result = array('errorMsg' => $this->getMessage(-41));
			}
		}
		if(!isset($params['text']) || empty($params['text'])){
			$result = array('errorMsg' => $this->getMessage(-42));
		}
		
		/* 如果是用户发送短信，则检查用户是否有发送短信的权限 */
		if(isset($params['sender']) && $params['sender'] > 0)
		{
			$mod_msg = &m('msg');
			$msg = $mod_msg->get('user_id='.$params['sender']);
			
			/* 短信功能被关闭 */
			if(!$msg['state']) {
				$result = array('errorMsg' => $this->getMessage(-12));
			}
			
			/* 没有短信了 */
			if($msg['num'] <= 0)
			{
				$result = array('errorMsg' => $this->getMessage(-3));
			}
			
			/* 没有发送短信的权限 */
			if(empty($msg['functions'])) {
				$result = array('errorMsg' => $this->getMessage(-13));
			}
			else
			{
				$fun = explode(',', $msg['functions']);
				
				if(!in_array($params['fun'], $fun) || !in_array($params['fun'], $this->getFunctions())) {
					$result = array('errorMsg' => $this->getMessage(-13));
				}
			}
		}
		$result = array_merge($params, $result);

		return $result;
	}
	
	function getFunctions()
    {
        $arr = array();        
        $arr[] = 'buy';  // 买家已下单通知卖家
		$arr[] = 'pay';  // 买家已付款通知卖家   
        $arr[] = 'send'; // 卖家已发货通知买家   
		$arr[] = 'check';// 买家已确认通知卖家
		$arr[] = 'refund_apply_warn_seller'; //买家退款申请，提醒卖家
		$arr[] = 'seller_agree_refund_warn_buyer'; //卖家同意退款成功，提醒买家
			
        return $arr;
    }
	
	function checkSendMsg($phone_mob, $oneTime = 120, $fiveTime = 1800, $dayTimes = 10)
	{
		if(!$phone_mob)
		{
			$this->_error('phone_empty');
			return false;
		}
		$msglog_mod = &m('msglog');
		
		//当天开始时间戳
		$dayBegin = strtotime(local_date("Y-m-d 00:00:00", gmtime()));
		
		$msgs = $msglog_mod->find(array("conditions" => "state = 1 AND to_mobile='".$phone_mob."' AND time >= {$dayBegin}", 'fields'=> 'time', 'order' => 'time DESC'));
		
		if(empty($msgs)){
			return true;
		}
		
		$count = count($msgs);
		$msg   = current($msgs);
		
		// 每天只允许发送10次短信
		if($count >= $dayTimes)
		{
			$this->_error(sprintf(Lang::get('send_limit_frequency_daytimes'), $dayTimes));
			return false;
		}
		elseif($count > 0)
		{
			// 每半小时只允许发送5次短信
			if($count % 5 == 0)
			{
				if(gmtime() < $msg['time'] + $fiveTime) {
					$this->_error(sprintf(Lang::get('send_limit_frequency_five_time'), round($fiveTime/60)));
					return false;
				}
			}
			else
			{
				// 每2分钟只能发送一次短信
				if(gmtime() < $msg['time'] + $oneTime) {
					$this->_error(sprintf(Lang::get('send_limit_frequency_one_time'), $oneTime));
					return false;
				}
			}
		}
		return true;		
	}
	
	function getMessage($code)
	{
		if($code > 0) {
			return '发送成功';
		}
		elseif($code == -1) {
			return '没有该用户账户';
		}
		elseif($code == -2) {
			return '接口密钥不正确';
		}
		elseif($code == -3) {
			return '短信数量不足';
		}
		elseif($code == -4){
			return '手机号格式不正确';
		}
		elseif($code == -6) {
			return 'IP限制';
		}
		elseif($code == -11) {
			return '该用户被禁用';
		}
		/* 本站的短信开关CODE（非网建）*/
		elseif($code == -12) {
			return '短信功能已关闭';
		}
		/* 本站的短信权限CODE（非网建）*/
		elseif($code == -13) {
			return '无法发送该短信，请检查权限';
		}
		elseif($code == -14) {
			return '短信内容出现非法字符';
		}
		elseif($code == -21) {
			return 'MD5接口密钥加密不正确';
		}
		elseif($code == -41) {
			return '手机号码为空';
		}
		elseif($code == -42) {
			return '短信内容为空';
		}
		elseif($code == -51) {
			return '短信签名格式不正确';
		}
		else {
			return '未知错误';
		}
	} 
}

?>