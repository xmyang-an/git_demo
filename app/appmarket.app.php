<?php

/**
 *    应用市场管理控制器
 *
 *    @author   Mimall
 *    @usage    none
 */
class AppmarketApp extends MemberbaseApp
{
	var $_appmarket_mod;
	var $_appbuylog_mod;
	var $_apprenewal_mod;

    /* 构造函数 */
    function __construct()
    {
         $this->AppmarketApp();
    }

    function AppmarketApp()
    {
        parent::__construct();
		$this->_appmarket_mod = &m('appmarket');
		$this->_appbuylog_mod = &m('appbuylog');
		$this->_apprenewal_mod= &m('apprenewal');
    }
	
	function index()
	{
		$sort_order = '';
		$sort = trim($_GET['sort']);
		$order= trim($_GET['order']);
		if(in_array($sort, array('sales', 'views', 'add_time'))){
			if(!in_array($order, array('desc', 'asc'))) {
				$order = 'desc';
			}
			$sort_order = $sort . ' ' . $order;
		}
		else
		{
			$sort_order = 'add_time DESC';
		}
		
		$page = $this->_get_page(10);
		
		$appmarket = $this->_appmarket_mod->find(array(
			'conditions' 	=> 'status = 1',
			'fields' 		=> 'title, summary, category, config, logo, views, sales, appid, status',
			'limit' 		=> $page['limit'],
			'order'			=> $sort_order,
			'count' 		=> true,	
		));
		
		foreach($appmarket as $key=>$app)
		{
			$app['config'] && $appmarket[$key]['config'] = unserialize($app['config']);
			if(!$app['logo']) $appmarket[$key]['logo'] = Conf::get('default_goods_image');
			
			if($this->_apprenewal_mod->checkIsRenewal($app['appid'], $this->visitor->get('user_id'))){
				$appmarket[$key]['checkIsRenewal'] = TRUE;
			}
		}
		
		$page['item_count'] = $this->_appmarket_mod->getCount();
		$this->_format_page($page);
		$this->assign('page_info', $page);
		
		$this->assign('appmarket', $appmarket);
		
				
		/* 当前位置 */
		$this->_curlocal(LANG::get('appmarket'), 'index.php?app=appmarket', LANG::get('applist'));
		
		/* 当前用户中心菜单 */
		$this->_curitem('appmarket');
		
		/* 当前所处子菜单 */
		$this->_curmenu('applist');	
       	
		$this->_config_seo('title', Lang::get('applist') . ' - ' . Lang::get('appmarket') . ' - ' . Lang::get('member_center'));
		$this->display('appmarket.index.html');
		
	}
	
	function my()
	{
		$page = $this->_get_page(10);
		
		$apprenewal = $this->_apprenewal_mod ->find(array(
			'conditions' 	=> 'user_id='.$this->visitor->get('user_id'),
			'limit' 		=> $page['limit'],
			'order'			=> 'rid DESC',
			'count' 		=> true,	
		));
		
		foreach($apprenewal as $key=>$renewal)
		{
			$appmarket = $this->_appmarket_mod->get(
				array('conditions'=>'appid="'.$renewal['appid'].'"', 'fields' => 'title,summary,category,logo'));
			
			
			/* 如果没到期，则获取剩余时间文本及数组 */
			if($timediff = $this->_getExpiredDetail($renewal['expired'])){
				$appmarket['timediff'] = $timediff;
				$appmarket['checkIsRenewal'] = TRUE; 
			}
			
			if($appmarket) {
				!$appmarket['logo'] && $appmarket['logo'] = Conf::get('default_goods_image');
				$apprenewal[$key] = array_merge($apprenewal[$key], $appmarket);
			}
			
		}
				
		$page['item_count'] = $this->_apprenewal_mod->getCount();
		$this->_format_page($page);
		$this->assign('page_info', $page);
		
		$this->assign('apprenewal', $apprenewal);
		
		/* 当前位置 */
		$this->_curlocal(LANG::get('appmarket'), 'index.php?app=appmarket', LANG::get('myapp'));
								
		/* 当前用户中心菜单 */
		$this->_curitem('appmarket');;
		
		/* 当前所处子菜单 */
		$this->_curmenu('myapp');	
		
		$this->_config_seo('title', Lang::get('myapp') . ' - ' . ' - ' . Lang::get('appmarket') . ' - ' . Lang::get('member_center'));
		
		$this->display('appmarket.my.html');
	}
	
	function cashier()
	{
		$bid = intval($_GET['id']);
		
		/* 取出购买信息 */
		$appbuylog = $this->_appbuylog_mod->get($bid);
		if($appbuylog['user_id'] != $this->visitor->get('user_id')){
			$this->show_warning('can_not_pay_app');
			return;
		}
		if($appbuylog['status'] != ORDER_PENDING) {
			$this->show_warning('can_not_pay_app_for_status');
			return;
		}
		
		if($appbuylog['amount'] > 0)
		{
			$payform = array(
				'gateway'		=>	site_url() . '/index.php',
				'method'		=>  'GET',
				'params'		=>  array(
					'app' 			=> 'depopay', 
					'act' 			=> 'gateway', 
					'merchantId' 	=> MERCHANTID, 
					'bizOrderId' 	=> $appbuylog['orderId'],
					//'amount'		=> $appbuylog['amount'],
					'bizIdentity'	=> TRADE_BUYAPP,	
				),
			);
			
			$this->assign('payform', $payform);
			header('Content-Type:text/html;charset=' . CHARSET);
			$this->display('cashier.payform.html');			
		}
		//  如果应用是免费的，则直接提示购买成功
		else
		{
			$time = gmtime();
			
			$depopay_type    =&  dpt('outlay', 'buyapp');
			
			/* 修改购买应用状态为交易完成 */
			if($depopay_type->_update_order_status($bid, array('status'=> ORDER_FINISHED, 'pay_time' => $time, 'end_time' => $time))) {
				
				/* 更新所购买的应用的过期时间 */
				if($depopay_type->_update_order_period(array('user_id' => $this->visitor->get('user_id')), $appbuylog)){
					$this->show_message('buy_ok', '', 'index.php?app=appmarket&act=buylog');
					return;
				}
			}
			$this->show_warning('buy_fail');
		}
	}
	
	function buy()
	{
		$aid = intval($_GET['id']);
		if(!$aid)
		{
			$this->json_error('Hacking_Attempt');
			return;
		}
		if(!$appmarket = $this->_appmarket_mod->get(array('conditions'=>'aid='.$aid, 'fields'=>'appid, status, config'))){
			$this->json_error('app_not_existed');
			return;
		}
		
		if(!$appmarket['status'])
		{
			$this->json_error('app_NOT_openBuy');
			return;
		}
		
		/* 如果该应用必须是卖家才能购买，则先判断是不是卖家（目前所有应用都是卖家才能购买） */
		$buyerMustSeller = TRUE;
		if($buyerMustSeller && !$this->visitor->get('has_store')) {
			$this->json_error('buyer_must_seller');
			return;
		}
			
		$period = intval($_GET['period']);
			
		if($appmarket['config']){
			$appmarket['config'] = unserialize($appmarket['config']);
		} else $appmarket['config'] = array();
			
		/* 检查所提交的购买周期是否在允许购买的范围内 */
		if(!in_array($period, $appmarket['config']['period'])){
			$this->json_error('period_fail');
			return;
		}
		
		/* 关闭掉没有付款成功且超过1天的订单 */
		$this->_appbuylog_mod->edit('user_id='.$this->visitor->get('user_id'). ' AND status='.ORDER_PENDING.' AND add_time <=' . (gmtime() - 3600 * 24), array('status' => ORDER_CANCELED));
			
		/* 计算需要支付的金额 */
		$amount = $appmarket['config']['charge'] * $period;
		
		// 为避免用户无数次的购买免费的应用，在这里做个控制：如果上次购买的应用是免费的，且离到期时间大于一个月，那么不允许购买
		if($amount == 0)
		{
			$appbuylog = $this->_appbuylog_mod->get(array(
				'conditions'=>" user_id=".$this->visitor->get('user_id'). " AND status=40 AND appid='".$appmarket['appid']."'",
				'order'		=> 'bid DESC',
				'fields' 	=>' amount '
			));
			if($appbuylog && ($appbuylog['amount'] == 0)) {
				
				$apprenewal = $this->_apprenewal_mod->get("user_id=".$this->visitor->get('user_id'). " AND appid='".$appmarket['appid']."'");
				
				// 如果到期时间大于一个月（一个月以30天计算），则不允许再购买免费应用了
				if($apprenewal && ($apprenewal['expired'] - gmtime() > 30 * 24 * 3600)) {
					$this->json_error('not_allow_buy_for_often');
					return;
				}
			}
		}
			
		/* 如果没有加入到购物车，则先加入 */
		$conditions = "appid='".$appmarket['appid']."' AND status=".ORDER_PENDING." AND user_id=".$this->visitor->get('user_id')." AND period={$period} AND amount='".$amount."'";
		$item_info  = $this->_appbuylog_mod->get($conditions);
        if (!empty($item_info))
        {
			$this->json_result(array('bid' => $item_info['bid']));

            return;
        }
		
		$data = array(
			'orderId'   =>  $this->_appbuylog_mod->genOrderId(),
			'appid'		=>	$appmarket['appid'],
			'user_id'	=>	$this->visitor->get('user_id'),
			'period'	=>	$period,
			'amount'	=>	$amount,
			'status'	=>	ORDER_PENDING,
			'add_time'	=>	gmtime()
		);
			
		if(!$bid = $this->_appbuylog_mod->add($data))
		{
			$this->json_error('add_cart_fail');
			return;
		}
		
		$this->json_result(array('bid' => $bid), 'add_ok');	
	}
	
	function buylog()
	{
		$page = $this->_get_page(10);
		
		$appbuylog = $this->_appbuylog_mod ->find(array(
			'conditions' 	=> 'user_id='.$this->visitor->get('user_id'),
			'limit' 		=> $page['limit'],
			'order'			=> 'bid DESC',
			'count' 		=> true,	
		));
		
		foreach($appbuylog as $key=>$buylog)
		{
			$appmarket = $this->_appmarket_mod->get(
				array('conditions'=>'appid="'.$buylog['appid'].'"', 'fields' => 'title,summary,category,logo'));
		
			if($appmarket) {
				!$appmarket['logo'] && $appmarket['logo'] = Conf::get('default_goods_image');
				$appbuylog[$key] = array_merge($appbuylog[$key], $appmarket);
			}
			
		}
				
		$page['item_count'] = $this->_appbuylog_mod->getCount();
		$this->_format_page($page);
		$this->assign('page_info', $page);
		
		$this->assign('appbuylog', $appbuylog);
		
		/* 当前位置 */
		$this->_curlocal(LANG::get('appmarket'), 'index.php?app=appmarket', LANG::get('buylog'));
								
		/* 当前用户中心菜单 */
		$this->_curitem('appmarket');;
		
		/* 当前所处子菜单 */
		$this->_curmenu('buylog');	
		
		$this->_config_seo('title', Lang::get('buylog') . ' - ' . Lang::get('appmarket') . ' - ' . Lang::get('member_center'));
		
		$this->display('appmarket.buylog.html');
	}
	
	function view()
	{
		$aid = empty($_GET['id']) ? 0 : intval($_GET['id']);
		$appid = empty($_GET['appid']) ? '' : trim($_GET['appid']);
		
		if($aid) {
			$conditions = $aid;
		}
		if($appid && in_array($appid, array('distribution', 'limitbuy', 'meal', 'fullfree', 'fullprefer', 'fullgift', 'growbuy', 'exclusive'))) {
			$conditions = 'appid="'.$appid.'"';
		}
		
		if(!$conditions) {
			$this->show_warning('app_not_existed');
			return;
		}
		
		$appmarket = $this->_appmarket_mod->get($conditions);
		
		if(!$appmarket)
		{
			$this->show_warning('app_not_existed');
			return;
		}
		
		$appmarket['config'] && $appmarket['config'] = unserialize($appmarket['config']);
		!$appmarket['logo'] && $appmarket['logo'] = Conf::get('default_goods_image');
		
		$periodlist = array();
		$period_inc = include_once(ROOT_PATH . '/data/period.inc.php');
		if($period_inc)
		{	
			foreach($appmarket['config']['period'] as $val) {
				$periodlist[] = array('key' => $val, 'value' => $period_inc[$val]);
			}
		}
		$appmarket['config']['period'] = $periodlist; 
		
		/* 更新访问次数 */
		$this->_appmarket_mod->edit($aid, "views = views + 1");
		
		/* 当前位置 */
		$this->_curlocal(LANG::get('appmarket'), 'index.php?app=appmarket', LANG::get('appview'));
								
		/* 当前用户中心菜单 */
		$this->_curitem('appmarket');;
		
		/* 当前所处子菜单 */
		$this->_curmenu('appview');	
		
		$this->_config_seo('title', $appmarket['title'] . ' - ' . Lang::get('appmarket') . ' - ' . Lang::get('member_center'));
		
		if($this->_apprenewal_mod->checkIsRenewal($appmarket['appid'], $this->visitor->get('user_id'))){
			$appmarket['checkIsRenewal'] = TRUE;
		}
		$this->assign('appmarket', $appmarket);
		$this->display('appmarket.view.html');
	}
	
	function _get_member_submenu()
    {
		$menus = array(
			array(
				'name' => 'applist',
				'url'  => 'index.php?app=appmarket',
			 ),
			 array(
				'name' => 'myapp',
				'url'  => 'index.php?app=appmarket&act=my',
			 ),
			 array(
				'name' => 'buylog',
				'url'  => 'index.php?app=appmarket&act=buylog',
			 ),
		); 
        
        if (ACT == 'view')
        {
            $menus[] = array(
                'name' => 'appview',
                'url'  => '',
            );
        }
        
        return $menus;
    }
	
	function _getExpiredDetail($expired = 0)
	{
		$timediff = 0;
		
		if($expired > gmtime())
		{
			$timediff = timediff($expired, gmtime());
			if($timediff['day'] < 1) {
				$text = sprintf(Lang::get('timediff_format_hour'), $timediff['hour'], $timediff['min']);
			}elseif($timediff['day'] < 7) {
				$text = sprintf(Lang::get('timediff_format_week'), $timediff['day'], $timediff['hour'], $timediff['min']);
			}elseif($timediff['day'] < 1115) {
				$text = sprintf(Lang::get('timediff_format_day'), $timediff['day'], $timediff['hour'], $timediff['min']);
			}
			
			$text && $timediff['format'] = $text;
			
		}
		
		return $timediff;
	}

   
	
	/* 价格过滤，返回非负浮点数 */
    function _filter_price($price)
    {
        return abs(floatval($price));
    }
}


?>