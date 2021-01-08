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
		$_GET['real_name'] && $conditions = " AND real_name like '%".$_GET['real_name']."%'";
		$page = $this->_get_page(10);
	
		$teams = $this->_distribution_mod->find(array(
			'conditions' =>"store_id=".$this->_store_id.$conditions,
			'limit' => $page['limit'],
			'count' => true,
			'fields'=> 'real_name, logo, store_id, user_id, phone_mob, did, add_time'
		));
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
		$page['item_count'] = $this->_distribution_mod->getCount();
		$this->_format_page($page);
		$this->assign('page_info',$page);
		$this->assign('teams',$teams);
        $this->_curlocal(LANG::get('distribution_manage'),'index.php?app=my_distribution', LANG::get('my_distributions'));
        $this->_curitem('distribution_manage');
        $this->_curmenu('my_distributions');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('my_distributions'));
		
        $this->display('my_distribution.index.html');
    }
	
	function order()
    {
		$conditions = $this->_get_query_conditions(array(
            array(      //按买家名称搜索
                'field' => 'buyer_name',
                'equal' => 'LIKE',
            ),
            array(      //按下单时间搜索,起始时间
                'field' => 'add_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),
            array(      //按下单时间搜索,结束时间
                'field' => 'add_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'=> 'gmstr2time_end',
            ),
            array(      //按订单号
                'field' => 'order_sn',
				'equal' => 'LIKE',
            ),
	    
				array(
					'field' => 'did',
					'name'  => 'did',
					'handler'=> 'intval'
				)
        ));
		$page = $this->_get_page(10);
        $model_order =& m('order');
		$member_mod =& m('member');
		$deposit_trade_mod = &m('deposit_trade');
		$refund_mod =& m('refund');
		$ordergift_mod = &m('ordergift');
			
		$orders = $model_order->findAll(array(
            'conditions'    => 'did > 0 AND seller_id='.$this->_store_id.$conditions,
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
			
			$orders[$key1]['goods_quantities'] = count($order['order_goods']);
			$orders[$key1]['buyer_info'] = $member_mod->get(array('conditions'=>'user_id='.$order['buyer_id'],'fields'=>'real_name,im_qq,im_aliww'));
			
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
			$orders[$key1]['order_gift'] = $ordergift_mod->find('order_id='.$order['order_id']);
				
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
        }
		
        $page['item_count'] = $model_order->getCount();
		$this->_format_page($page);
		$this->assign('page_info',$page);
		$this->assign('orders',$orders);
        $this->_curlocal(LANG::get('distribution_manage'),'index.php?app=my_distribution', LANG::get('distribution_order'));
        $this->_curitem('distribution_manage');
        $this->_curmenu('distribution_order');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('distribution_order'));
		$this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.ui/i18n/' . i18n_code() . '.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/smoothness/jquery.ui.css',
        ));
        $this->display('my_distribution.order.html'); 
    }
	
	function setting()
    {
        if (!IS_POST)
        {
			$store = $this->_store_mod->get(array(
				'conditions' => $this->_store_id,
				'fields' => 'enable_distribution,distribution_1,distribution_2,distribution_3',
			));
            $this->_curlocal(LANG::get('distribution_manage'),'index.php?app=my_distribution',LANG::get('distribution_setting'));
            $this->_curitem('distribution_manage');
            $this->_curmenu('distribution_setting');
            $this->assign('store', $store);
            $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('distribution_manage'));
			$this->assign('appAvailable', $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id));
			$this->import_resource(array(
				'script' => array(
					array(
						'path' => 'jquery.plugins/jquery.validate.js',
						'attr' => 'charset="utf-8"',
					),
				),
			));
            $this->display('my_distribution.setting.html');
        }
        else
        {
			if(($appAvailable = $this->_appmarket_mod->getCheckAvailableInfo($this->_appid, $this->_store_id)) !== TRUE) {
				$this->show_warning($appAvailable['msg']);
				return;
			}
			
            $data = array(
                'enable_distribution' => intval($_POST['enable_distribution']),
                'distribution_1'  => abs(floatval($_POST['distribution_1'])),
				'distribution_2'  => abs(floatval($_POST['distribution_2'])),
				'distribution_3'  => abs(floatval($_POST['distribution_3'])),
            );
	    	if($data['distribution_1'] + $data['distribution_2'] + $data['distribution_3'] >= 100) {
				$this->show_warning('rate_gt100');
				return;
			}
            $this->_store_mod->edit($this->_store_id, $data);
            $this->show_message('setting_ok');
        }
    }
	
    function _get_member_submenu()
    {
        $menus = array(
			array(
                'name' => 'my_distributions',
                'url'  => 'index.php?app=my_distribution',
            ),
			array(
                'name' => 'distribution_order',
                'url'  => 'index.php?app=my_distribution&act=order',
            ),
            array(
                'name' => 'distribution_setting',
                'url'  => 'index.php?app=my_distribution&act=setting',
            )
        );
		return $menus;
    }
	
	/* 取得本店所有商品分类 */
    function _get_sgcategory_options()
    {
        $mod =& bm('gcategory', array('_store_id' => $this->_store_id));
        $gcategories = $mod->get_list();
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');
        return $tree->getOptions();
    }
}

?>
