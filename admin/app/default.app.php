<?php

/**
 *    默认控制器
 *
 *    @usage    none
 */
class DefaultApp extends BackendApp
{
    /**
     *    后台首页
     *

     *    @return    void
     */
    function index()
    {
        $back_nav = $menu = $this->_get_menu();
        unset($back_nav['dashboard']);
        $this->_hook('on_load_adminmenu', array('menu' => &$menu));
        $this->assign('menu', $menu);
        $this->assign('back_nav', $back_nav);
        $this->assign('menu_json', ecm_json_encode($menu));
        $this->display('index.html');
    }

    /**
     *    后台欢迎页
     *

     *    @param    none
     *    @return    void
     */
    function welcome()
    {
		$this->import_resource(array(
			'script' => 'echarts-all.js,jquery.plugins/SuperSlide/jquery.SuperSlide.2.1.2.js'
		));
		
        $this->assign('admin', $this->visitor->get());

        $ms =& ms();
        //$this->assign('new', $ms->pm->check_new($this->visitor->get('user_id')));

        // 一周动态
        $this->assign('news_in_a_week', $this->_get_news_in_a_week());

        // 统计信息
        $stats = $this->_get_stats();
        $this->assign('stats', $stats);

        // 系统信息
        $sys_info = $this->_get_sys_info();
        $this->assign('sys_info', $sys_info);

        // 提示信息
        $remind_info = $this->_get_remind_info();
        $this->assign('remind_info', $remind_info);
        $dangerous_apps  = false;
        if (is_file(ROOT_PATH . '/initdata/index.php'))
        {
            $dangerous_apps[] = Lang::get('dangerous_initdata');
        }
        if (is_file(ROOT_PATH . '/integrate/index.php'))
        {
            $dangerous_apps[] = Lang::get('dangerous_integrate');
        }

        $this->assign('dangerous_apps', $dangerous_apps);
		
		$log_mod = &m('loginlog');
		$logs = $log_mod->find(array(
			'conditions' => 'user_id=0',
			'limit'      => 5,
			'order' => 'add_time desc'
		));
		
		$this->assign('loginLogs', $logs);

        // 当前语言
        $this->assign('cur_lang', LANG);

        $this->_update_store_state();
        $this->_update_site_information($stats, $sys_info);
        $this->display('welcome.html');
    }
	
	function getIpInfo(){
		$loginlog_mod = &m('loginlog');
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		if(empty($id) || !$log = $loginlog_mod->get($id)){
			$this->json_error('no_such_item');
			return;
		}
		$region_mod = &m('region');
		$result = $region_mod->get_address_from_ip($log['ip']);
		$result = json_decode($result,true);
		if($result['code'] == 0)
		{
			$str_array = array_unique(array($result['data']['country'],$result['data']['region'],$result['data']['city']));//把中国北京北京处理成中国北京
			$location = str_replace('XX','',implode('',$str_array));
			$loginlog_mod->edit($id,array('region_name'=>$location));
			$this->json_result($location);
			return;
		}else{
			$this->json_error('获取地区失败！');
			return;
		}
	}

    /**
     *    关于我们页面
     *

     *    @param    none
     *    @return    void
     */
    function aboutus()
    {
        $this->headtag('<base target="_blank" />');
        $this->display('aboutus.html');
    }

    function _get_menu()
    {
        $menu = include(APP_ROOT . '/includes/menu.inc.php');

        return $menu;
    }

    function _get_news_in_a_week()
    {
        $a_week_ago = gmtime() - 7 * 24 * 3600;
        $user_mod =& m('member');
        return array(
            'new_user_qty'  => $user_mod->getOne("SELECT COUNT(*) FROM " . DB_PREFIX . "member WHERE reg_time > '$a_week_ago'"),
            'new_store_qty' => $user_mod->getOne("SELECT COUNT(*) FROM " . DB_PREFIX . "store WHERE add_time > '$a_week_ago' AND state = 1"),
            'new_apply_qty' => $user_mod->getOne("SELECT COUNT(*) FROM " . DB_PREFIX . "store WHERE add_time > '$a_week_ago' AND state = 0"),
            'new_goods_qty' => $user_mod->getOne("SELECT COUNT(*) FROM " . DB_PREFIX . "goods WHERE add_time > '$a_week_ago' AND if_show = 1 AND closed = 0"),
            'new_order_qty' => $user_mod->getOne("SELECT COUNT(*) FROM " . DB_PREFIX . "order WHERE add_time > '$a_week_ago' AND status <> 0"),
			'new_pinglun' => $user_mod->getOne("SELECT COUNT(*) FROM " . DB_PREFIX . "order o LEFT JOIN " . DB_PREFIX . "order_goods og on o.order_id = og.order_id WHERE evaluation_time > '$a_week_ago' AND evaluation_status = 1 "),
        );
    }

    function _get_stats()
    {
        $user_mod =& m('member');
        return array(
            'user_qty'  => $user_mod->getOne("SELECT COUNT(*) FROM " . DB_PREFIX . "member"),
            'store_qty' => $user_mod->getOne("SELECT COUNT(*) FROM " . DB_PREFIX . "store WHERE state = 1"),
            'apply_qty' => $user_mod->getOne("SELECT COUNT(*) FROM " . DB_PREFIX . "store WHERE state = 0"),
            'goods_qty' => $user_mod->getOne("SELECT COUNT(*) FROM " . DB_PREFIX . "goods WHERE if_show = 1 AND closed = 0"),
            'order_qty' => $user_mod->getOne("SELECT COUNT(*) FROM " . DB_PREFIX . "order WHERE status <> 0 "),
            'order_amount' => $user_mod->getOne("SELECT SUM(order_amount) FROM " . DB_PREFIX . "order WHERE status <> 0 "),
            'admin_email' => $user_mod->getOne("SELECT email FROM " . DB_PREFIX . "member WHERE user_id = '1'"),
        );
    }

    function _get_sys_info()
    {
        $user_mod =& m('member');
        $filename = ROOT_PATH . '/data/install.lock';
        return array(
            'server_os'     => PHP_OS,
            'web_server'    => $_SERVER['SERVER_SOFTWARE'],
            'php_version'   => PHP_VERSION,
            'mysql_version' => $user_mod->db->version(),
			'version'=> VERSION . ' ' . RELEASE,
            'install_date'  => file_exists($filename) ? date('Y-m-d', fileatime($filename)) : date('Y-m-d'),
        );
    }

    function _update_site_information($stats, $sys_info)
    {
        $update = array(
            'uniqueid'  => MALL_SITE_ID,
            'version'   => VERSION,
            'release'   => RELEASE,
            'php'       => PHP_VERSION,
            'mysql'     => $sys_info['mysql_version'],
            'charset'   => CHARSET,
            'url'       => SITE_URL,
        );
        $this->assign('uniqueid',base64_encode(MALL_SITE_ID));
        $update_time = 0;
        $update_file = ROOT_PATH . '/data/update_time.lock';
        if (file_exists($update_file))
        {
            $update_time = filemtime($update_file);
        }

        $timestamp = time();
        if(empty($update_time) || ($timestamp - $update_time > 3600 * 4))
        {
            touch($update_file);
            $stat_info = array();
            $stat_info['page_view']    = 1; // todo: no data
            $stat_info['order_amount'] = $stats['order_amount'];
            $stat_info['order_count']  = $stats['order_qty'];
            $stat_info['store_count']  = $stats['store_qty'];
            $stat_info['member_count'] = $stats['user_qty']; // differ from 1.1
            $stat_info['goods_count']  = $stats['goods_qty']; // differ from 1.1
            $stat_info['admin_last_login_time'] = local_date('Y-m-d H:i:s');
            $stat_info['admin_email'] = $stats['admin_email'];
            foreach($stat_info AS $key => $value)
            {
                $update[$key] = $value;
            }
        }

        $data = '';
        foreach($update as $key => $value)
        {
            $data .= $key.'='.rawurlencode($value).'&';
        }

        $this->assign('spt', 'ht'. 'tp:/' . '/e' .'cmal' . 'l.sho' . 'pe' . 'x.c' . 'n/sy' . 'stem'. '/ecm' . 'all' . '_in' . 'stal' . 'l.p' . 'hp?'.'update='.rawurlencode(base64_encode($data)).'&md5hash='.substr(md5($_SERVER['HTTP_USER_AGENT'].implode('', $update).$timestamp), 8, 8).'&timestamp='.$timestamp);
    }

    function clear_cache()
    {
        $cache_server =& cache_server();
        $cache_server->clear();
        $this->json_result('', Lang::get('clear_cache_ok'));
    }

    /* 更新店铺状态：过期的关闭 */
    function _update_store_state()
    {
        $store_mod =& m('store');
        $stores = $store_mod->find(array(
            'conditions' => "state = '" . STORE_OPEN . "' AND end_time > 0 AND end_time < '" . gmtime() . "'",
            'join'       => 'belongs_to_user',
            'fields'     => 'store_id, user_id, user_name, email',
        ));
        foreach ($stores as $store)
        {
            $subject = Lang::get('close_store_notice');
            $content = get_msg('toseller_store_closed_notify', array('reason' => Lang::get('close_reason')));
            /* 连接用户系统 */
            $ms =& ms();
            $ms->pm->send(MSG_SYSTEM, $store['user_id'], '', $content);

            $this->_mailto($store['email'], $subject, $content);
            $store_mod->edit($store['store_id'], array('state' => STORE_CLOSED, 'close_reason' => Lang::get('close_reason')));
        }
    }

    /* 取得提醒信息 */
    function _get_remind_info()
    {
        $remind_info = array();
        $mod =& m('store');

        // 地区
        $region_count = $mod->getOne("SELECT COUNT(*) FROM " . DB_PREFIX . "region WHERE parent_id = 0");
        $region_count == 0 && $remind_info[] = Lang::get('reminds.region');

        // 支付方式
        $filename = ROOT_PATH . '/data/payments.inc.php';
        $payments = array();
        if (file_exists($filename))
        {
            $payments = include_once $filename;
        }
        empty($payments) && $remind_info[] = Lang::get('reminds.payment');

        // 商品分类
        $gcate_count = $mod->getOne("SELECT COUNT(*) FROM " . DB_PREFIX . "gcategory WHERE store_id = 0 AND parent_id = 0 AND if_show = 1");
        $gcate_count == 0 && $remind_info[] = Lang::get('reminds.gcategory');

        // 店铺分类
        $scate_count = $mod->getOne("SELECT COUNT(*) FROM " . DB_PREFIX . "scategory WHERE parent_id = 0");
        $scate_count == 0 && $remind_info[] = Lang::get('reminds.scategory');
		
		//待处理的举报
		$report_count = $mod->getOne("SELECT count(*) FROM " . DB_PREFIX . "report WHERE status is null");
        $report_count > 0 && $remind_info[] = sprintf(Lang::get('reminds.report'),$report_count);
		
		//要求平台接入的退款申请
		$refund_count = $mod->getOne("SELECT count(*) FROM " . DB_PREFIX . "refund WHERE ask_customer=1 AND status='WAIT_SELLER_AGREE'");
        $refund_count > 0 && $remind_info[] = sprintf(Lang::get('reminds.refund'),$refund_count);
		
		//待审核的店铺
		$apply_count = $mod->getOne("SELECT COUNT(*) FROM " . DB_PREFIX . "store WHERE state = 0");
		$apply_count > 0 && $remind_info[] = sprintf(Lang::get('reminds.apply'),$apply_count);

        return $remind_info;
    }
}
?>
