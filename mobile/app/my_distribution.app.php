<?php

class My_distributionApp extends StoreadminbaseApp
{
	var $_appid;
	var $_appmarket_mod;
    var $_store_id;
    var $_store_mod;
	var $_goods_mod;
	var $_distribution_mod;

    function __construct()
    {
        $this->My_distributionApp();
    }
    function My_distributionApp()
    {
        parent::__construct();
		$this->_appid     = 'distribution';
        $this->_store_id  = intval($this->visitor->get('manage_store'));
        $this->_store_mod =& m('store');
		$this->_goods_mod =&m('goods');
		$this->_distribution_mod =&m('distribution');
		$this->_appmarket_mod = &m('appmarket');
    }
	
	function index()
    {
		if(!IS_AJAX)
		{
			$this->assign('appAvailable', $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id));
			
			$this->import_resource('mobile/jquery.plugins/jquery.infinite.js');
			$this->assign('infiniteParams', json_encode($_GET));
			
			$this->_config_seo('title', Lang::get('my_distributions') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('my_distributions');
        	$this->display('my_distribution.index.html');
		}
		else
		{
			$page = $this->_get_page(intval($_GET['pageper']));
			$teams = $this->_distribution_mod->find(array(
				'conditions' =>"store_id=".$this->_store_id,
				'limit' => $page['limit'],
				'count' => true,
				'fields'=> 'real_name, logo, store_id, user_id, phone_mob, did, add_time'
			));
			$page['item_count'] = $this->_distribution_mod->getCount();
			$this->_format_page($page);
			
			$store = $this->_store_mod->get(array('store_id='.$team['store_id'], 'fields' => 'store_logo, enable_distribution'));
			
			$member_mod = &m('member');
			$deposit_trade_mod = &m('deposit_trade');
			foreach($teams as $key=>$team)
			{
				$member = $member_mod->get(array('conditions' => 'user_id='.$team['user_id'], 'fields' => 'user_name'));
				if($member) $teams[$key]['user_name'] = $member['user_name'];
				
				if(!$team['logo']) {
					$teams[$key]['logo'] = $store['store_logo'] ? $store['store_logo'] : Conf::get('default_store_logo');
				}
				
				$amount = $deposit_trade_mod->get(array(
					'conditions' => "flow='income' AND bizIdentity='".TRADE_FX."' AND buyer_id=".$team['user_id'], 
					'fields' => 'sum(amount) as amount'
				));
				$teams[$key]['amount'] = $amount['amount'];
			}
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($teams), 'totalPage' => $page['page_count']);

			echo json_encode($data);
		}
    }
	
	function order()
    {
		if(!IS_AJAX)
		{
			$this->import_resource(array('script' => 'mobile/jquery.plugins/jquery.infinite.js'));
			$this->assign('infiniteParams', json_encode($_GET));
			
        	$this->_config_seo('title',  Lang::get('distribution_order') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('distribution_order');
        	$this->display('my_distribution.order.html'); 
		}
		else
		{
			$conditions = $this->_get_query_conditions(array(
				array(      //按订单状态搜索
					'field' => 'status',
					'name'  => 'type',
					'handler' => 'order_status_translator',
				),
				array(
					'field' => 'did',
					'name'  => 'did',
					'handler'=> 'intval'
				)
			));
			
			$model_order =& m('order');
			$deposit_trade_mod = &m('deposit_trade');
			$refund_mod =& m('refund');
			$ordergift_mod = &m('ordergift');
			
			$page = $this->_get_page(intval($_GET['pageper']));
			
			/* 查找订单 */
			$orders = $model_order->findAll(array(
				'conditions'    => "did > 0 AND seller_id=" . $this->_store_id . "{$conditions}",
				'fields'        => 'buyer_id, seller_id, status, order_sn, payment_code, order_amount, buyer_name, add_time, shipping_fee',
				'count'         => true,
				'join'          => 'has_orderextm',
				'limit'         => $page['limit'],
				'order'         => 'add_time DESC',
				'include'       =>  array(
					'has_ordergoods',       //取出商品
				),
			));
			
			foreach ($orders as $key1 => $order)
			{
				if(!$order['order_goods']) {
					continue;
				}
				$orders[$key1]['status_label'] = order_status($order['status']);
				
				$total_quantity = 0;
				foreach ($order['order_goods'] as $key2 => $goods)
				{
					empty($goods['goods_image']) && $orders[$key1]['order_goods'][$key2]['goods_image'] = Conf::get('default_goods_image');
					
					$total_quantity += $goods['quantity'];
				}
				
				$orders[$key1]['total_quantity'] = $total_quantity;
				
				
				/* 是否申请过退款 */
				$tradeInfo = $deposit_trade_mod->get(array(
					'conditions' => 'merchantId="'.MERCHANTID.'" AND bizIdentity="'.TRADE_ORDER.'" AND bizOrderId="'.$order['order_sn'].'"', 'fields' => 'tradeNo'));
				if($tradeInfo) {
					if( $refund = $refund_mod->get(array('conditions'=>'tradeNo="'.$tradeInfo['tradeNo'].'"', 'fields'=>'status'))) {
						$orders[$key1]['refund_status'] = $refund['status'];
						$orders[$key1]['refund_id'] = $refund['refund_id'];
					}
				}
				/* 读取订单的赠品（如果有）*/
				$orders[$key1]['order_gift'] = array_values($ordergift_mod->find('order_id='.$order['order_id']));
				
				// 分销商
				$distribution = $this->_distribution_mod->get(array(
					'condditions' => 'did='.$order['did'], 'fields' => 'did, real_name as distributioner'));
				$orders[$key1] += $distribution;
				
				$orders[$key1]['profit'] = 0;
				$profits = $this->_distribution_mod->get_profit($order['order_id'], ($refund && $refund['status'] != 'CLOSED') ? $refund['refund_goods_fee'] : 0);
				if($profits) {
					foreach($profits as $profit) {
						$orders[$key1]['profit'] += $profit['amount'];
					}
				}
				
				// JS Need
				$orders[$key1]['order_goods'] = array_values($order['order_goods']);
			}
			
			$page['item_count'] = $model_order->getCount();
			$this->_format_page($page);
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($orders), 'totalPage' => $page['page_count']);
			echo json_encode($data);
		}
    }
	
	function setting()
    {
        if (!IS_POST)
        {
			$store = $this->_store_mod->get(array(
				'conditions' => $this->_store_id,
				'fields' => 'enable_distribution,distribution_1,distribution_2,distribution_3',
			));
            $this->assign('store', $store);
			$this->assign('appAvailable', $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id));
			
			$this->import_resource('mobile/jquery.plugins/jquery.form.min.js');
			
			$this->_config_seo('title', Lang::get('distribution_manage') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('distribution_setting');
            $this->display('my_distribution.setting.html');
        }
        else
        {
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->json_error($appAvailable['msg']);
				return;
			}

            $data = array(
                'enable_distribution' => intval($_POST['enable_distribution']),
                'distribution_1'  => abs(floatval($_POST['distribution_1'])),
				'distribution_2'  => abs(floatval($_POST['distribution_2'])),
				'distribution_3'  => abs(floatval($_POST['distribution_3'])),
            );
			if($data['distribution_1'] + $data['distribution_2'] + $data['distribution_3'] >= 100) {
				$this->json_error('rate_gt100');
				return;
			}
			
            $this->_store_mod->edit($this->_store_id, $data);
			
			$this->json_result('', 'setting_ok');
        }
    }
}

?>
