<?php

require_once ("classes/ResponseHandler.class.php");
require ("classes/RequestHandler.class.php");
require ("classes/client/ClientResponseHandler.class.php");
require ("classes/client/TenpayHttpClient.class.php");

/**
 *    财付通支付方式插件
 *
 *    @author   MiMall
 *    @usage    none
 */

class TenpayPayment extends BasePayment
{
    /* 财付通即时到帐网关 */
    var $_gateway   =   'https://gw.tenpay.com/gateway/pay.htm';
    var $_code      =   'tenpay';

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
		$reqHandler->setGateUrl($this->_gateway);
		
		//----------------------------------------
		//设置支付参数 
		//----------------------------------------
		$reqHandler->setParameter("partner", $this->_config['partner']);
		$reqHandler->setParameter("out_trade_no", $payTradeNo);
		$reqHandler->setParameter("total_fee", $orderInfo['amount'] * 100);  //总金额
		$reqHandler->setParameter("return_url", $this->_create_return_url($payTradeNo));
		$reqHandler->setParameter("notify_url", $this->_create_notify_url($payTradeNo));
		$reqHandler->setParameter("body", $orderInfo['title']);
		$reqHandler->setParameter("bank_type", "DEFAULT");  	  //银行类型，默认为财付通
		//用户ip
		$reqHandler->setParameter("spbill_create_ip", $_SERVER['REMOTE_ADDR']);//客户端IP
		$reqHandler->setParameter("fee_type", "1");               //币种
		$reqHandler->setParameter("subject", $orderInfo['title']);          //商品名称，（中介交易时必填）
		
		//系统可选参数
		$reqHandler->setParameter("sign_type", "MD5");  	 	  //签名方式，默认为MD5，可选RSA
		$reqHandler->setParameter("service_version", "1.0"); 	  //接口版本号
		$reqHandler->setParameter("input_charset", "utf-8");   	  //字符集
		$reqHandler->setParameter("sign_key_index", "1");    	  //密钥序号
		
		//业务可选参数
		$reqHandler->setParameter("trade_mode", 1);              //交易模式（1.即时到帐模式，2.中介担保模式，3.后台选择（卖家进入支付中心列
		
		/*
		$reqHandler->setParameter("attach", "");             	  //附件数据，原样返回就可以了
		$reqHandler->setParameter("product_fee", "");        	  //商品费用
		$reqHandler->setParameter("transport_fee", "0");      	  //物流费用
		$reqHandler->setParameter("time_start", date("YmdHis"));  //订单生成时间
		$reqHandler->setParameter("time_expire", "");             //订单失效时间
		$reqHandler->setParameter("buyer_id", "");                //买方财付通帐号
		$reqHandler->setParameter("goods_tag", "");               //商品标记
		$reqHandler->setParameter("transport_desc","");              //物流说明
		$reqHandler->setParameter("trans_type","1");              //交易类型
		$reqHandler->setParameter("agentid","");                  //平台ID
		$reqHandler->setParameter("agent_type","");               //代理模式（0.无代理，1.表示卡易售模式，2.表示网店模式）
		$reqHandler->setParameter("seller_id","");                //卖家的商户号
		*/
		
		
		//请求的URL
		//$reqUrl = $reqHandler->getRequestURL();
		$params = $reqHandler->getAllParameters();
		
		$params['sign'] = $this->_get_sign($params);

        return $this->_create_payform('POST', $params);
    }

    /**
     *    返回通知结果
     *
     *    @author    MiMall
     *    @param     array $orderInfo
     *    @param     bool  $strict
     *    @return    array 返回结果
     *               false 失败时返回
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

		/* 验证来路是否可信 */
        if ($strict)
        {
            /* 严格验证 */
            $verify_result = $this->_query_notify($notify);
			
            if(!$verify_result)
            {
                /* 来路不可信 */
                $this->_error('notify_unauthentic');

                return false;
            }
        }
		else
		{
			/* 验证签名 */
 			$sign_result = $this->_verify_sign($notify);
        	if (!$sign_result)
        	{
            	/* 若本地签名与网关签名不一致，说明签名不可信 */
            	$this->_error('sign_inconsistent');

            	return false;
        	}

		}

        /*----------本地验证开始----------*/
        /* 验证与本地信息是否匹配 */
        /* 这里不只是付款通知，有可能是发货通知，确认收货通知 */

        if ($orderInfo['payTradeNo'] != $notify['out_trade_no'])
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
		
		if($notify['trade_mode'] == "1" && $notify['trade_state'] == "0") 
		{
			$order_status = ORDER_ACCEPTED;
        	
		}
		
		return array(
            'target'    =>  $order_status,
        );
    }
	
	/**
     *    查询通知是否有效
     *
     *    @author    MiMall
     *    @param     string $notify_id
     *    @return    string
     */
    function _query_notify($notify)
    {
		unset($notify['app'], $notify['act'], $notify['tradeNo'], $notify['payTradeNo']);
		
		$result = FALSE;
		
		/* 创建支付应答对象 */
		$resHandler = new ResponseHandler();
		$resHandler->parameters = $notify;
		$resHandler->setKey($this->_config['key']);

		//判断签名
		if($resHandler->isTenpaySign()) 
		{
			//通知id
			$notify_id = $resHandler->getParameter("notify_id");
		
			//通过通知ID查询，确保通知来至财付通
			//创建查询请求
			$queryReq = new RequestHandler();
			$queryReq->init();
			$queryReq->setKey($this->_config['key']);
			$queryReq->setGateUrl("https://gw.tenpay.com/gateway/simpleverifynotifyid.xml");
			$queryReq->setParameter("partner", $this->_config['partner']);
			$queryReq->setParameter("notify_id", $notify_id);
			
			//通信对象
			$httpClient = new TenpayHttpClient();
			$httpClient->setTimeOut(5);
			//设置请求内容
			$httpClient->setReqContent($queryReq->getRequestURL());
		
			//后台调用
			if($httpClient->call()) 
			{
				//设置结果参数
				$queryRes = new ClientResponseHandler();
				$queryRes->setContent($httpClient->getResContent());
				$queryRes->setKey($this->_config['key']);
			
				if($resHandler->getParameter("trade_mode") == "1"){ //  即时到帐
					//判断签名及结果（即时到帐）
					//只有签名正确,retcode为0，trade_state为0才是支付成功
					if($queryRes->isTenpaySign() && $queryRes->getParameter("retcode") == "0" && $resHandler->getParameter("trade_state") == "0") {
						$result = TRUE;
					}
				}
			}
		}
		
		return $result;
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
		unset($notify['app'], $notify['act'], $notify['tradeNo'], $notify['payTradeNo']);
		
		/* 创建支付应答对象 */
		$resHandler = new ResponseHandler();
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