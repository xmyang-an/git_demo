<?php


include_once('WxPayPubHelper/WxPayPubHelper.php');

/**
 *    微信扫码支付插件
 *
 *    @author   mimall
 *    @usage    none
 */

class WxnativepayPayment extends BasePayment
{
	/* 微信统一支付网关地址 */
	var $_gateway 	= 	'https://api.mch.weixin.qq.com/pay/unifiedorder';
    var $_code      =   'wxnativepay';
	
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
		
		$unifiedOrder = new UnifiedOrder_pub($this->_config);
		
		// body max length <= 128
		if(strlen($orderInfo['title']) > 128) {
			$body = mb_substr($orderInfo['title'],0,40, CHARSET);// 代表40个字 120个字符
		} else $body = $orderInfo['title'];
	
		//设置统一支付接口参数
		$unifiedOrder->setParameter("product_id", $payTradeNo);
		$unifiedOrder->setParameter("body", $body);//商品描述
		//自定义订单号
		$unifiedOrder->setParameter("out_trade_no", $payTradeNo);//商户订单号 
		$unifiedOrder->setParameter("total_fee", $orderInfo['amount'] * 100);//总金额
		$unifiedOrder->setParameter("notify_url", $this->_create_notify_url($payTradeNo));//通知地址 
		$unifiedOrder->setParameter("trade_type","NATIVE");//交易类型
		//非必填参数，商户可根据实际情况选填
		//$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号  
		//$unifiedOrder->setParameter("device_info","XXXX");//设备号 
		//$unifiedOrder->setParameter("attach","XXXX");//附加数据 
		//$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
		//$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间 
		//$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记 
		//$unifiedOrder->setParameter("openid","XXXX");//用户标识
		//$unifiedOrder->setParameter("product_id","XXXX");//商品ID
		
		//获取统一支付接口结果
        $unifiedOrderResult = $unifiedOrder->getResult();
		
		//商户根据实际情况设置相应的处理流程
        if ($unifiedOrderResult["return_code"] == "FAIL") 
        {
			$this->_error($unifiedOrderResult['return_msg']);
			return;
        }
        elseif($unifiedOrderResult["result_code"] == "FAIL")
        {
			$this->_error($unifiedOrderResult['err_code'].':'.$unifiedOrderResult['err_code_des']);
			return;
        }
        elseif(!$unifiedOrderResult["code_url"])
        {
            $this->_error('code_url empty');
			return;
       	}
		
		//从统一支付接口获取到code_url
		$code_url = $unifiedOrderResult["code_url"];
		
		$params['code_url'] 	= urlencode($code_url);
		$params['payment_code'] = $this->_code;
		
		return $this->_create_payform('POST', $params);
    }
	
	/**
     *    获取通知地址
     *
     *    @author    MiMall
     *    @param     int $store_id
     *    @param     int $order_id
     *    @return    string
     */
    function _create_notify_url($payTradeNo)
    {
        return SITE_URL .'/includes/payments/wxnativepay/notify_url.php';
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
        $sign_result = $this->_verify_sign($notify, $strict);
		
        if (!$sign_result)
        {
            /* 若本地签名与网关签名不一致，说明签名不可信 */
            $this->_error('sign_inconsistent');

            return false;
        }
        /*----------通知验证结束----------*/

		
        /*----------本地验证开始----------*/
        /* 验证与本地信息是否匹配 */
        /* 这里不只是付款通知，有可能是发货通知，确认收货通知 */

        if ($orderInfo['payTradeNo'] != $notify['out_trade_no'])
        {
            /* 通知中的订单与欲改变的订单不一致 */
            $this->_error('order_inconsistent');

            return false;
        }
        if ($orderInfo['amount'] != round($notify['total_fee']/100,2))
        {
            /* 支付的金额与实际金额不一致 */
            $this->_error('price_inconsistent');

            return false;
        }
        //至此，说明通知是可信的，订单也是对应的，可信的
				
		/* 按通知结果返回相应的结果 */
		if(($notify['return_code'] == 'SUCCESS') && ($notify['result_code'] == 'SUCCESS'))
		{
			$order_status = ORDER_ACCEPTED;
			
		} else {
			$order_status = false;
		}
	
        return array(
            'target'    =>  $order_status,
        );
    }

    /**
     *    验证签名是否可信
     *
     *    @author    MiMall
     *    @param     array $notify
     *    @return    bool
     */
    function _verify_sign($notify, $strict = false)
    {
		$notify_pub = new Notify_pub($this->_config);
		
		unset($notify['app'], $notify['act'], $notify['payTradeNo']);
		$notify_pub->data = $notify;
		
		return $notify_pub->checkSign();
    }
	
	function verify_result($result) 
    {
		$notify= new Notify_pub($this->_config);
		
        if ($result)
        {
			$notify->setReturnParameter("return_code","SUCCESS");//设置返回码	
        }
		else
		{
			$notify->setReturnParameter("return_code","FAIL");//返回状态码
			$notify->setReturnParameter("return_msg","签名失败");//返回信息
		}
		
		//回应微信
		$returnXml = $notify->returnXml();
		echo $returnXml;
    }
	
	function _getNotifySpecificData()
	{
		$notify = $this->_get_notify();
		
		return array(round($notify['total_fee']/100,2), $notify['transaction_id'], 'payment_bank' => $notify['bank_type']);
	}	
}

?>