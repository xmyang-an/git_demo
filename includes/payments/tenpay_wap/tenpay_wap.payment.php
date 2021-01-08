<?php

require_once ("classes/RequestHandler.class.php");
require ("classes/client/ClientResponseHandler.class.php");
require ("classes/client/TenpayHttpClient.class.php");



/**
 *    财付通WAP支付方式插件
 *
 *    @author   MiMall
 *    @usage    none
 */

class Tenpay_wapPayment extends BasePayment
{
	/* 财付通WAP支付网关地址 */
	var $_gateway 	= 	'http://wap.tenpay.com/cgi-bin/wappayv2.0/wappay_gate.cgi';
    var $_code      =   'tenpay_wap';
	
	
	
    /**
     *    获取支付表单
     *
     *    @author    MiMall
     *    @param     array $orderInfo  待支付的订单信息，必须包含总费用及唯一外部交易号
     *    @return    array
     */
    function get_payform(&$orderInfo = array())
    {
		// 支付网关商户订单号
		$payTradeNo = $this->_get_trade_sn($orderInfo);
		// 给其他页面使用
		foreach($orderInfo['tradeList'] as $key => $val) {
			$orderInfo['tradeList'][$key]['payTradeNo'] = $payTradeNo;
		}
		
		/* 创建支付请求对象 */
		$reqHandler = new RequestHandler();
		$reqHandler->init();
		$reqHandler->setKey($this->_config['key']);
		//设置初始化请求接口，以获得token_id
		$reqHandler->setGateUrl("http://wap.tenpay.com/cgi-bin/wappayv2.0/wappay_init.cgi");
		
		
		$httpClient = new TenpayHttpClient();
		//应答对象
		$resHandler = new ClientResponseHandler();
		//----------------------------------------
		//设置支付参数 
		//----------------------------------------
		$reqHandler->setParameter("total_fee", $orderInfo['amount'] * 100);  //总金额
		//用户ip
		$reqHandler->setParameter("spbill_create_ip", $_SERVER['REMOTE_ADDR']);//客户端IP
		$reqHandler->setParameter("ver", "2.0");//版本类型
		$reqHandler->setParameter("bank_type", "0"); //银行类型，财付通填写0
		$reqHandler->setParameter("callback_url", $this->_create_return_url($payTradeNo));//交易完成后跳转的URL
		$reqHandler->setParameter("bargainor_id", $this->_config['partner']); //商户号
		$reqHandler->setParameter("sp_billno", $payTradeNo); //商户订单号
		$reqHandler->setParameter("notify_url", $this->_create_notify_url($payTradeNo));//接收财付通通知的URL，需绝对路径
		$reqHandler->setParameter("desc", $orderInfo['title']);
		$reqHandler->setParameter("attach", $orderInfo['payTradeNo']);
		
		
		$httpClient->setReqContent($reqHandler->getRequestURL());
		
		$params = array();
		
		//后台调用
		if($httpClient->call()) {
		
			$resHandler->setContent($httpClient->getResContent());
			//获得的token_id，用于支付请求
			$token_id = $resHandler->getParameter('token_id');
			$reqHandler->setParameter("token_id", $token_id);
			
			//请求的URL
			//$reqHandler->setGateUrl("https://wap.tenpay.com/cgi-bin/wappayv2.0/wappay_gate.cgi");
			//此次请求只需带上参数token_id就可以了，$reqUrl和$reqUrl2效果是一样的
			//$reqUrl = $reqHandler->getRequestURL(); 
			//$reqUrl = "http://wap.tenpay.com/cgi-bin/wappayv2.0/wappay_gate.cgi?token_id=".$token_id;
			
			if(!$token_id) exit($httpClient->getResContent());
			else $params['token_id'] = $token_id;
				
		}
		
		return $this->_create_payform('GET', $params);
    }
	
	/**
     *    获取通知地址
     *
     *    @author    MiMall
     *    @return    string
     */
    function _create_notify_url($payTradeNo)
    {
        return SITE_URL .'/includes/payments/tenpay_wap/notify_url.php';
    }

    /**
     *    获取返回地址
     *
     *    @author    MiMall
     *    @return    string
     */
	 /*
    function _create_return_url($payTradeNo)
    {
        return SITE_URL .'/includes/payments/tenpay_wap/return_url.php';
    }

    /**
     *    返回通知结果
     *
     *    @author    MiMall
     *    @param     array $orderInfo
     *    @param     bool  $strict
     *    @return    array
     */
    function verify_notify($orderInfo, $strict = false)
    {
        if (empty($orderInfo))
        {
            $this->_error('order_info_empty');

            return false;
        }

        /* 初始化所需数据 */
        $notify =   $this->_get_notify();
		
        /* 验证通知是否可信 */
        $sign_result = $this->_verify_sign($notify);
        if (!$sign_result)
        {
            /* 若本地签名与网关签名不一致，说明签名不可信 */
            $this->_error('sign_inconsistent');

            return false;
        }
        /*----------通知验证结束----------*/
		
		/* 如果是页面返回的通知，那么到此说明签名可信了，直接返回true */
		if(!$strict) {
			return true;
		}
		
		
		
        /*----------本地验证开始----------*/
        /* 验证与本地信息是否匹配 */
        /* 这里不只是付款通知，有可能是发货通知，确认收货通知 */

        if ($orderInfo['payTradeNo'] != $notify['sp_billno'])
        {
            /* 通知中的订单与欲改变的订单不一致 */
            $this->_error('order_inconsistent');

            return false;
        }
        if ($orderInfo['amount'] != $notify['total_fee']/100)
        {
            /* 支付的金额与实际金额不一致 */
            $this->_error('price_inconsistent');

            return false;
        }
        //至此，说明通知是可信的，订单也是对应的，可信的

		if($notify['pay_result'] == "0") 
		{
			$order_status = ORDER_ACCEPTED;
        	
		}	
		
		return array(
			'target'    =>  $order_status,
		);
        
    }
	
	/**
     *    获取签名字符串
     *
     *    @author    MiMall
     *    @param     array $params
     *    @return    string
     */
    function _get_sign($params)
    {
        /* 去除不参与签名的数据 */
        unset($params['sign']);

        /* 排序 */
        ksort($params);
        reset($params);

        $sign  = '';
        foreach ($params AS $key => $value)
        {
			if($value) {
            	$sign  .= "{$key}={$value}&";
			}
        }

        return strtoupper(md5(substr($sign, 0, -1) . '&key='.$this->_config['key']));
    }

    /**
     *    验证签名是否可信
     *
     *    @author    MiMall
     *    @param     array $notify
     *    @return    bool
     */
    function _verify_sign($notify)
    {
		unset($notify['app'], $notify['act'], $notify['tradeNo']);
		
		require ("classes/ResponseHandler.class.php");
		require ("classes/WapNotifyResponseHandler.class.php");

		/* 创建支付应答对象 */
		$resHandler = new WapNotifyResponseHandler();
		$resHandler->parameters = $notify;
		$resHandler->setKey($this->_config['key']);
		
		return $resHandler->isTenpaySign();
	}
	
	function _getNotifySpecificData()
	{
		$notify = $this->_get_notify();
		
		return array($notify['total_fee']/100, $notify['transaction_id']);
	}
}

?>