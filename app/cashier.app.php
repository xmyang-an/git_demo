<?php

/**
 *    收银台控制器，其扮演的是收银员的角色，你只需要将你的订单交给收银员，收银员按订单来收银，她专注于这个过程
 *
 *    @author    MiMall
 */
class CashierApp extends ShoppingbaseApp
{
    /**
     *    根据提供的订单信息进行支付
     *
     *    @author    MiMall
     *    @param    none
     *    @return    void
     */
    function index()
    {
        /* 外部提供订单号 */
        $order_id = isset($_GET['order_id']) ? html_script($_GET['order_id']) : 0;
        if (!$order_id)
        {
            $this->show_warning('no_such_order');

            return;
        }
		
		$orderIds = explode(',', $order_id);
		if(!is_array($orderIds))
		{
			$this->show_warning('no_such_order');

            return;
		}
		
		$order_model =& m('order');
		$orderList  = $order_model->find("order_id " . db_create_in($orderIds) . " AND buyer_id=" . $this->visitor->get('user_id'));
        if (empty($orderList))
        {
            $this->show_warning('no_such_order');

            return;
		}
		
		$bizOrderId = array();
		foreach($orderList as $order_info)
		{
			$bizOrderId[] = $order_info['order_sn'];
		}
		$payform = array(
			'gateway'		=>	site_url() . '/index.php',
			'method'		=>  'GET',
			'params'		=>  array(
				'app' 			=> 'depopay', 
				'act' 			=> 'gateway', 
				'merchantId' 	=> MERCHANTID, 
				'bizOrderId' 	=> implode(',', $bizOrderId),
				'bizIdentity'	=> TRADE_ORDER,
			),
		);
		
		$this->assign('payform', $payform);
		header('Content-Type:text/html;charset=' . CHARSET);
		$this->display('cashier.payform.html');
	}
}

?>