<?php

include_once ("sdk/acp_service.php");

/**
 *    中国银联支付方式插件（兼容PC，手机）
 *
 *    @author    MiMall
 *    @usage    none
 */

class UnionpayPayment extends BasePayment
{
	/* 网关地址 */
	var $_gateway 	= 	'https://gateway.95516.com/gateway/api/frontTransReq.do';
    var $_code      =   'unionpay';

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
		
		$channelType = '07';
		if(defined('IN_MOBILE') && (defined('IN_MOBILE') == true)) {
			$channelType = '08';
		}
		 
        $params = array(
			'version' 			=> '5.0.0',                 //版本号
			'encoding' 			=> CHARSET,//'utf-8',		//编码方式
			'txnType'			=> '01',				      //交易类型
			'txnSubType' 		=> '01',				  //交易子类
			'bizType' 			=> '000201',				  //业务类型 000201：B2C网关支付
			'frontUrl' 			=> $this->_create_return_url($payTradeNo),  //前台通知地址
			'backUrl' 			=> $this->_create_notify_url($payTradeNo),	  //后台通知地址
			'signMethod' 		=> '01',	              //签名方法
			'channelType' 		=> $channelType,	              //渠道类型，07-PC，08-手机
			'accessType' 		=> '0',		          //接入类型
			'currencyCode' 		=> '156',	          //交易币种，境内商户固定156
			
			//TODO 以下信息需要填写
			'merId' 			=> trim($this->_config["merId"]),		//商户代码，请改自己的测试商户号。
			'orderId' 			=> $payTradeNo,	//商户订单号，8-32位数字字母，不能含“-”或“_”。
			'txnTime' 			=> local_date("YmdHis", gmtime()),	//订单发送时间，格式为YYYYMMDDhhmmss，取北京时间。
			'txnAmt' 			=> $orderInfo['amount'] * 100,	//交易金额，单位分
		);
		
		
		AcpService::sign ( $params );
		//$uri = SDK_FRONT_TRANS_URL;
		//$html_form = AcpService::createAutoFormHtml( $params, $uri );
		//echo $html_form;

        return $this->_create_payform('POST', $params);
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

        /*----------本地验证开始----------*/
        /* 验证与本地信息是否匹配 */
        if ($orderInfo['payTradeNo'] != $notify['orderId'])
        {
            /* 通知中的订单与欲改变的订单不一致 */
            $this->_error('order_inconsistent');

            return false;
        }
        if ($orderInfo['amount'] != $notify['txnAmt']/100)
        {
            /* 支付的金额与实际金额不一致 */
            $this->_error('price_inconsistent');

            return false;
        }
        //至此，说明通知是可信的，订单也是对应的，可信的


        /* 按通知结果返回相应的结果 */
        if(in_array($notify['respCode'], array('00', 'A6')))
        {
			$order_status = ORDER_ACCEPTED;
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
    function _verify_sign($notify)
    {
		if (isset ( $notify['signature'] )) {
        	return AcpService::validate ( $notify );
		} else return FALSE;
    }
	
	function _getNotifySpecificData()
	{
		$notify = $this->_get_notify();
		
		return array($notify['txnAmt']/100, $notify['queryId']);
	}
}

?>