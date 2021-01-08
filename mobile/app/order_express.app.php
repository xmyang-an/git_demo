<?php

/* 快递查询控制器
 * 
 * author psmb
 * site   MiMall（#）
 *
 */
 
class Order_expressApp extends MemberbaseApp
{
	var $_order_mod;

    /* 构造函数 */
    function __construct()
    {
         $this->Order_expressApp();
    }

    function Order_expressApp()
    {
        parent::__construct();
		$this->_order_mod = &m('order');
    }

    function index()
    {
		if(!Psmb_init()->_check_express_plugin())
		{
			$this->show_warning('no_such_express_plugin');
			return;
		}
		$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
		if(!$order_id)
		{
			$this->show_warning('no_such_order');
			return;
		}
		
		// 查询的订单必须是已发货（<del>不是货到付款</del>）或者是已完成的交易
        $order_info  = $this->_order_mod->get(array(
            'conditions' => "(status=40 OR status=30) AND order_id={$order_id} AND (seller_id=" . $this->visitor->get('user_id')." OR buyer_id=".$this->visitor->get('user_id').")",
			'fields'=>'invoice_no,express_company,buyer_id,seller_id',
        ));
        if (!$order_info)
        {
			$this->show_warning('no_such_order');
            return;
        }
		
		// 如果快递公司或者快递单号为空，则返回空
		if(empty($order_info['invoice_no']) || empty($order_info['express_company'])){
			$this->show_warning('invoice_or_ecompany_empty');
			return;
		}
		
		// 从订单ID查询快递公司和快递单号
		$data =  $this->_hook('on_query_express',array('com'=>$order_info['express_company'],'nu'=>$order_info['invoice_no']));
		
		//if(!$data['status']) {
		//	$data['url'] = 'http://m.kuaidi100.com/index_all.html?type='.$order_info['express_company'].'&postid='.$order_info['invoice_no'].'&callbackurl='.urlencode(site_url().'/index.php?app=order_express&order_id='.$order_info['order_id']);
			
		//	header("location:".$data['url']);
		//	exit;
		//}
		
		$this->assign('kuaidi_info', $data);
	
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('view_delivery_track'));
	   	$this->_get_curlocal_title('view_delivery_track');
		$this->display('order.express.html');
		
	}

}

?>
