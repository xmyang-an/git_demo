<?php

/**
 * docs: 手机网站支付 https://docs.open.alipay.com/203/107090/
 * docs: 电脑网站支付 https://docs.open.alipay.com/270/105900/
 *
 *
 */
require_once("lib/AopClient.php");

/**
 *    支付宝支付方式插件
 *
 *    @author    MiMall
 *    @usage    none
 */

class AlipayPayment extends BasePayment
{
	/* 支付宝手机支付网关地址 */
	var $_gateway 	= 	'https://openapi.alipay.com/gateway.do';
    var $_code      =   'alipay';
	
	function get_payform(&$orderInfo = array())
    {
		// 支付网关商户订单号
		$payTradeNo = $this->_get_trade_sn($orderInfo);
		// 给其他页面使用
		foreach($orderInfo['tradeList'] as $key => $val) {
			$orderInfo['tradeList'][$key]['payTradeNo'] = $payTradeNo;
		}

		$biz_content	= array(
			'subject'         	=> $orderInfo['title'],
			'out_trade_no'    	=> $payTradeNo,
			'total_amount'  	=> $orderInfo['amount'],   //应付总价
			//'product_code'		=> 'FAST_INSTANT_TRADE_PAY',
			//'passback_params' => '', // 原样返回参数。 UrlEncode之后才可以发送给支付宝
			//'quit_url'		=> '',  // 添加该参数后在h5支付收银台会出现返回按钮，可用于用户付款中途退出并返回到该参数指定的商户网站地址。注：该参数对支付宝钱包标准收银台下的跳转不生效。
		);
 
		// 电脑网站支付和手机网站支付代码差异仅在此，为了考虑历史原因，还是分开为2个支付方式来设计（但不是必须的）		
		if(in_array($this->_code, array('alipay_mobile'))) 
		{
			require_once("lib/request/AlipayTradeWapPayRequest.php");
			$biz_content['product_code'] = 'QUICK_WAP_WAY';
			$request = new AlipayTradeWapPayRequest();
		}
		else
		{
			require_once("lib/request/AlipayTradePagePayRequest.php");
			$biz_content['product_code'] = 'FAST_INSTANT_TRADE_PAY';
			$request = new AlipayTradePagePayRequest();
		}
		
		$aop = new AopClient();
		$aop->appId 				= $this->_config['appId'];
		$aop->rsaPrivateKey 		= $this->_config['rsaPrivateKey'];
		$aop->alipayrsaPublicKey 	= $this->_config['alipayrsaPublicKey'];
		$aop->postCharset 			= CHARSET;
		$aop->signType 				= $this->_config['signType'];
		
		$request->setBizContent(json_encode($biz_content));
		$request->setReturnUrl($this->_create_return_url($payTradeNo));
		$request->setNotifyUrl($this->_create_notify_url($payTradeNo));
		$result = $aop->pageExecute($request, 'POST', FALSE);

		$params = array_merge($result, array('payment_code' => $this->_code));
        return $this->_create_payform('POST', $params);
    }

	/**
     *    获取通知地址
     *
     *    @author    MiMall
     *    @param     int $store_id
     *    @param     int $tradeNo
     *    @return    string
     */
    function _create_notify_url($payTradeNo)
    {
        return SITE_URL .'/includes/payments/'.$this->_code.'/notify_url.php';
    }

    /**
     *    获取返回地址
     *
     *    @author    MiMall
     *    @param     int $store_id
     *    @param     int $tradeNo
     *    @return    string
     */
    function _create_return_url($payTradeNo)
    {
        return SITE_URL .'/includes/payments/'.$this->_code.'/return_url.php';
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
		$notify['fund_bill_list'] && $notify['fund_bill_list'] = stripslashes($notify['fund_bill_list']);
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
        if ($orderInfo['amount'] != $notify['total_amount'])
        {
            /* 支付的金额与实际金额不一致 */
            $this->_error('price_inconsistent');

            return false;
        }
        //至此，说明通知是可信的，订单也是对应的，可信的
		
        /* 按通知结果返回相应的结果 */
        switch ($notify['trade_status'])
        {
            case 'TRADE_FINISHED':              //交易结束
			case 'TRADE_SUCCESS':               // 交易成功
                
				$order_status = ORDER_ACCEPTED;
            break;
            case 'TRADE_CLOSED':                //交易关闭
                $order_status = ORDER_CANCLED;
            break;

            default:
                $this->_error('undefined_status');
                return false;
            break;
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
		// 页面通知不验证签名
		if($strict == TRUE) 
		{
			$aop = new AopClient();
			//$aop->appId 				= $this->_config['appId'];
			//$aop->rsaPrivateKey 		= $this->_config['rsaPrivateKey'];
			$aop->alipayrsaPublicKey 	= $this->_config['alipayrsaPublicKey'];
			//$aop->postCharset 			= CHARSET;
			//$aop->signType 				= $this->_config['signType'];
			$verify_result = $aop->rsaCheckV1($notify, $this->_config['alipayrsaPublicKey'], $this->_config['signType']);
			
			return $verify_result;
		}
		else return TRUE;
    }
	
	function _getNotifySpecificData()
	{
		$notify = $this->_get_notify();
		
		return array($notify['total_amount'], $notify['trade_no']);
	}
}

?>