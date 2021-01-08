<?php

!defined('ROOT_PATH') && exit('Forbidden');

/**
 *    支付方式基础类
 *
 *    @author    MiMall
 *    @usage    none
 */
class BasePayment extends Object
{
    /* 外部处理网关 */
    var $_gateway   = '';
    /* 支付方式唯一标识 */
    var $_code      = '';


    function __construct($payment_info = array())
    {
        $this->BasePayment($payment_info);
    }
    function BasePayment($payment_info = array())
    {
        $this->_info   = $payment_info;
        $this->_config = unserialize($payment_info['config']);
    }

    /**
     *    获取支付表单
     *
     *    @author    MiMall
     *    @param     array $order_info
     *    @return    array
     */
    function get_payform()
    {
        return $this->_create_payform('POST');
    }

    /**
     *    获取规范的支付表单数据
     *
     *    @author    MiMall
     *    @param     string $method
     *    @param     array  $params
     *    @return    void
     */
    function _create_payform($method = '', $params = array())
    {
        return array(
            'online'    =>  $this->_info['is_online'],
            'desc'      =>  $this->_info['payment_desc'],
            'method'    =>  $method,
            'gateway'   =>  $this->_gateway,
            'params'    =>  $params,
        );
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
        return SITE_URL . "/index.php?app=paynotify&act=notify&payTradeNo={$payTradeNo}";
    }

    /**
     *    获取返回地址
     *
     *    @author    MiMall
     *    @param     int $store_id
     *    @param     int $order_id
     *    @return    string
     */
    function _create_return_url($payTradeNo)
    {
        return SITE_URL . "/index.php?app=paynotify&payTradeNo={$payTradeNo}";
    }

    /**
     *    获取外部交易号
     *
     *    @author    MiMall
     *    @param     array $orderInfo
     *    @return    string
     */
    function _get_trade_sn($orderInfo, $length = 0)
    {
		$deposit_trade_mod = &m('deposit_trade');
		$payTradeNo = $deposit_trade_mod->genPayTradeNo($orderInfo, $length);
		
		$deposit_trade_mod->edit(array_keys($orderInfo['tradeList']), array('payTradeNo' => $payTradeNo));
					
        return $payTradeNo;
    }

    /**
     *    获取商品简介
     *
     *    @author    MiMall
     *    @param     array $order_info
     *    @return    string
     */
    function _get_subject($order_info)
    {
        return 'MiMall Order:' . $order_info['order_sn'];
    }

    /**
     *    获取通知信息
     *
     *    @author    MiMall
     *    @return    array
     */
    function _get_notify()
    {
		/* POST JSON first */
        $post = file_get_contents("php://input");
		if($post) 
		{
			return json_decode($post, true);
		}
        /* 如果有POST的数据，则认为POST的数据是通知内容 */
        elseif (!empty($_POST))
        {
            return $_POST;
        }

        /* 否则就认为是GET的 */
        else return $_GET;
    }

    /**
     *    验证支付结果
     *
     *    @author    MiMall
     *    @return    void
     */
    function verify_notify()
    {
        #TODO
    }

    /**
     *    将验证结果反馈给网关
     *
     *    @author    MiMall
     *    @param     bool   $result
     *    @return    void
     */
    function verify_result($result)
    {
        if ($result)
        {
            echo 'success';
        }
        else
        {
            echo 'fail';
        }
    }
}

?>