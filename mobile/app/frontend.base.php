<?php

/**
 *    前台控制器基础类
 *
 *    @author    MiMall
 *    @usage    none
 */
class FrontendApp extends ECBaseApp
{
    function __construct()
    {
        $this->FrontendApp();
    }
    function FrontendApp()
    {
		Lang::load(lang_file('common'));
        Lang::load(lang_file(APP));
		
		/* Rewrite Lang for the Mobile client */
        Lang::load(lang_file('mobile/common'));
        Lang::load(lang_file('mobile/' . APP));
        parent::__construct();
		
		$this->_check_wxmp_login();

        // 判断商城是否关闭
        if (!Conf::get('site_status'))
        {
            $this->show_warning(Conf::get('closed_reason'));
            exit;
        }
        # 在运行action之前，无法访问到visitor对象
    }
    function _config_view()
    {
        parent::_config_view();
        $this->_view->template_dir  = ROOT_PATH . '/mobile/themes';
        $this->_view->compile_dir   = ROOT_PATH . '/temp/compiled/mobile/mall';
        $this->_view->res_base      = SITE_URL . '/mobile/themes';
        $this->_config_seo(array(
            'title' => Conf::get('site_title'),
            'description' => Conf::get('site_description'),
            'keywords' => Conf::get('site_keywords')
        ));
    }
    function display($tpl)
    {
        $cart =& m('cart');
        $this->assign('cart_goods_kinds', $cart->get_kinds(SESS_ID, $this->visitor->get('user_id')));
		
		/* 用于前台判断快递跟踪插件是否安装 */
		$this->assign('enable_express', Psmb_init()->_check_express_plugin());
		
		/* 用户判断是否在微信端 */
		$this->assign('isWeixin', isWeixin());
		
        /* 新消息 */
        $this->assign('new_message', isset($this->visitor) ? $this->_get_new_message() : '');
				
		$this->assign('baidukey',Conf::get('baidukey'));
        $this->assign('site_title', Conf::get('site_title'));
        $this->assign('site_logo', Conf::get('site_logo'));
        $this->assign('statistics_code', Conf::get('statistics_code')); // 统计代码
        $current_url = explode('/', $_SERVER['REQUEST_URI']);
        $count = count($current_url);
        $this->assign('current_url',  $count > 1 ? $current_url[$count-1] : $_SERVER['REQUEST_URI']);// 用于设置导航状态(以后可能会有问题)
		
		//兼容小程序处理
		if((isset($_GET['s']) && ($_GET['s'] == 'wxmp')) || isset($_SESSION['in_wxmp'])){
			$this->assign('in_wxmp', 1);
			
			if(!isset($_SESSION['in_wxmp'])){
				$_SESSION['in_wxmp'] = 1;
			}
		}
		
        parent::display($tpl);
	}
	
	function _check_wxmp_login()
    {
		$post = $this->_getPostData();
		if(($this->visitor->has_login && $this->visitor->get('user_id') <> $post['uid']) || !$this->visitor->has_login){
			if((($post['time_stamp']+100200) < time()) || ($post['time_stamp'] > time()+100200)){
				//TO DO
			}
			else{
				if(isset($post['appid']) && $post['uid'] > 0)
				{
					$merchant_mod = &m('merchant');
					$merchant = $merchant_mod->get('appId="'.$post['appid'].'"');
					if(!empty($merchant))
					{
						$post['key'] = $merchant['appKey'];
						//请求验证通过
						if($this->_VerifySign($post) == true)
						{
							$member_mod = &m('member');
							$user = $member_mod->get(array(
								'conditions' => $post['uid'],
								'fields'     => 'user_id'
							));
							
							if(!empty($user)){
								$this->_do_login($post['uid']);
								$login = true;
							}
						}
					}
					
					if(!isset($_SESSION['reload'])){
						$_SESSION['reload'] = 1;
						header('Location:'.SITE_URL.$_SERVER['REQUEST_URI'].'&smtp='.gmtime());
						exit;
					}
				}
			}
		}
	}
	
	/* 取得/设置浏览历史 */
    function _get_goods_history($id = 0, $num = 9, $total = 50)
    {
		$goods_mod =& m('goods');
        $goods_list = array();
        $goods_ids  = ecm_getcookie('goodsBrowseHistory');
        $goods_ids  = $goods_ids ? explode(',', $goods_ids) : array();
        if ($goods_ids)
        {
            $rows = $goods_mod->find(array(
                'conditions' => $goods_ids,
                'fields'     => 'goods_name,default_image, price',
            ));
			
			// 确保读的是最新的浏览
			for($i = count($goods_ids) - 1; $i >= 0; $i--)
			{
				$goods_id = $goods_ids[$i];
				if (!isset($rows[$goods_id])) {
					unset($goods_ids[$i]);
					continue;
				}
           		empty($rows[$goods_id]['default_image']) && $rows[$goods_id]['default_image'] = Conf::get('default_goods_image');
             	$goods_list[] = $rows[$goods_id];
			
				if(count($goods_list) >= $num) break;
			}
        }
        if($id) $goods_ids[] = $id;
		$goods_ids = array_values(array_unique($goods_ids));
        if (count($goods_ids) > $total)
        {
            unset($goods_ids[0]);
        }
        ecm_setcookie('goodsBrowseHistory', join(',', $goods_ids));

        return $goods_list;
    }
	
    function login()
    { 
        if (!IS_POST)
        {
			if ($this->visitor->has_login)
        	{
				$this->show_message('has_login', '', url('app=member'));
            	return;
        	}
			
            $ret_url = $this->getRetUrl(TRUE);
			
            /* 防止登陆成功后跳转到登陆、退出的页面 */
            // 真实的跳转地址不能转成小写，因为URL地址区别大小写           
            if (str_replace(array('act=login', 'act=logout'), '', strtolower($ret_url)) != strtolower($ret_url))
            {
                $ret_url = SITE_URL . '/index.php';
            }
			
			if (str_replace(array('act=setting'), '', strtolower($ret_url)) != strtolower($ret_url))
            {
                $ret_url = SITE_URL . '/' . url('app=member');
            }
			$this->assign('ret_url', rawurlencode($ret_url));// H5端必须加 rawurlencode，如果不加，讲获取不到&后面的参数
			
			if (Conf::get('captcha_status.login'))
            {
                $this->assign('captcha', 1);
            }
			
            $this->_config_seo('title', Lang::get('user_login') . ' - ' . Conf::get('site_title'));
			$this->_get_curlocal_title('user_login');
            $this->display('login.html');
			
            /* 同步退出外部系统 */
            if (!empty($_GET['synlogout']))
            {
                $ms =& ms();
                echo $synlogout = $ms->user->synlogout();
            }
        }
        else
        {
			if ($this->visitor->has_login)
        	{
				$this->json_error('has_login');
            	return;
       	 	}
			
			$ms =& ms();
			
			//  手机短信登录
			if($_GET['type'] == 'phone') 
			{
				$phone_mob = trim($_POST['phone_mob']);
				$code 	   = trim($_POST['code']);
				
				if(!is_mobile($phone_mob)) {
					$this->json_error('phone_mob_invalid');
					return;
				}
				
				if(($_SESSION['phone_code'] != md5($phone_mob.$code)) || ($_SESSION['last_send_time_phone_code'] + 120 < gmtime())) {
					$this->json_error('phone_code_check_failed');
					return;
				}
				
				if($member = $this->_member_mod->get(array('conditions' => 'phone_mob="'.$phone_mob.'"', 'fields' => 'user_id'))) {
					$user_id = $member['user_id'];
				}
			}
			// 账号密码登录
			else
			{
				$user_name = trim($_POST['user_name']);
				$password  = trim($_POST['password']);
				
				if(!$user_name) {
					$this->json_error('user_name_required');
					return;
				}
				if(!$password) {
					$this->json_error('password_required');
					return;
				}
				if (Conf::get('captcha_status.login') && base64_decode($_SESSION['captcha']) != strtolower($_POST['captcha']))
				{
					$this->json_error('captcha_failed');
					return;
				}
				$user_id = $ms->user->auth($user_name, $password);
			}
			
            if (!$user_id)
            {
				if($ms->user->get_error()) {
					$error = current($ms->user->get_error());
					$msg = $error['msg'];
				} else $msg = Lang::get('login_fail');
                
				/* 未通过验证，提示错误信息 */
				$this->json_error($msg);
				return;
            }
            else
            {
				/* 记住密码，保存cookie*/
				if($_POST['AutoLogin'] && intval($_POST['AutoLogin']) == 1) {
					$this->setAutoLoginCookie($user_id, $user_name, $password);
				}
				
                /* 通过验证，执行登陆操作 */
                $this->_do_login($user_id);

                /* 同步登陆外部系统 */
                $synlogin = $ms->user->synlogin($user_id);
				
				$this->json_result('', 'login_successed');
            }
        }
    }
	function pop_warning ($msg, $dialog_id = '',$url = '')
    {
        if($msg == 'ok')
        {
            if(empty($dialog_id))
            {
                $dialog_id = APP . '_' . ACT;
            }
            if (!empty($url))
            {
                echo "<script type='text/javascript'>window.parent.location.href='".$url."';</script>";
            }
			else {
            	echo "<script type='text/javascript'>window.parent.js_success('" . $dialog_id ."');</script>";
			}
        }
        else
        {
            header("Content-Type:text/html;charset=".CHARSET);
            $msg = is_array($msg) ? $msg : array(array('msg' => $msg));
            $errors = '';
            foreach ($msg as $k => $v)
            {
                $error = $v[obj] ? Lang::get($v[msg]) . " [" . Lang::get($v[obj]) . "]" : Lang::get($v[msg]);
                $errors .= $errors ? "<br />" . $error : $error;
            }
            echo "<script type='text/javascript'>window.parent.js_fail('" . $errors . "');</script>";
        }
    }
    function logout()
    {
		// 必须放在logout前
		$this->clearAutoLoginCookie();
        $this->visitor->logout();
		
        /* 跳转到登录页，执行同步退出操作 */
        header("Location: index.php?app=member&act=login&synlogout=1");
        return;
    }

    /* 执行登录动作 */
    function _do_login($user_id)
    {
        $mod_user =& m('member');

        $user_info = $mod_user->get(array(
            'conditions'    => "user_id = '{$user_id}'",
            'join'          => 'has_store',                 //关联查找看看是否有店铺
            'fields'        => 'user_id, user_name, reg_time, last_login, last_ip, store_id, locked, portrait, store_name, store_logo',
        ));
		
		// 如果用户被锁定，则不能登陆（前台不能加 privs 限制，因无此参数）
		if(isset($user_info['locked']) && $user_info['locked'])
		{
			$this->json_error('your_account_has_locked');
			exit;
		}

        /* 店铺ID */
        $my_store = empty($user_info['store_id']) ? 0 : $user_info['store_id'];

        /* 保证基础数据整洁 */
        //unset($user_info['store_id']);
		
		// username avatar字段给在线客服功能做头像用
		if($user_info['store_id'])
		{
			$user_info['username'] = $user_info['store_name'];
			if(empty($user_info['store_logo'])) {
				$user_info['avatar'] =  Conf::get('default_store_logo');
			} else $user_info['avatar'] = $user_info['store_logo'];
		}
		else
		{
			$user_info['username'] = $user_info['user_name'];
			if(empty($user_info['portrait'])) {
				$user_info['avatar'] = Conf::get('default_user_portrait');
			} else $user_info['avatar'] = $user_info['portrait'];
		}

        /* 分派身份 */
        $this->visitor->assign($user_info);

        /* 更新用户登录信息 */
        $mod_user->edit("user_id = '{$user_id}'", "last_login = '" . gmtime()  . "', last_ip = '" . real_ip() . "', logins = logins + 1");
		
		$mod_user->insertLoginLog(array($user_info['user_name'],$user_id));
		
		/* 如果还没有创建预存款账户，且系统启动了自动创建，则自动创建 */
		$deposit_account_mod = &m('deposit_account');
		$deposit_account_mod->_create_deposit_account($user_id);

        /* 更新购物车中的数据 */
        $mod_cart =& m('cart');
        $mod_cart->edit("(user_id = '{$user_id}' OR session_id = '" . SESS_ID . "') AND store_id <> '{$my_store}'", array(
            'user_id'    => $user_id,
            'session_id' => SESS_ID,
        ));

        /* 去掉重复的项 */
        $cart_items = $mod_cart->find(array(
            'conditions'    => "user_id='{$user_id}' GROUP BY spec_id",
            'fields'        => 'COUNT(spec_id) as spec_count, spec_id, rec_id',
        ));
        if (!empty($cart_items))
        {
            foreach ($cart_items as $rec_id => $cart_item)
            {
                if ($cart_item['spec_count'] > 1)
                {
                    $mod_cart->drop("user_id='{$user_id}' AND spec_id='{$cart_item['spec_id']}' AND rec_id <> {$cart_item['rec_id']}");
                }
            }
        }
    }
	
	function setCookieDid($did,$store_id,$expire=0)
	{
		if(!$expire)  $expire = time() + 3600;
		setcookie('CookieDid', json_encode(array('did' => $did, 'store_id' => $store_id)), $expire,  "/");
	}
	
	function getCookieDid()
	{
		if(isset($_COOKIE['CookieDid']) && trim($_COOKIE['CookieDid']) <> '')
		{
			return json_decode(stripslashes($_COOKIE['CookieDid']), true);			
		}
	}

    /* 取得导航 */
    function _get_navs()
    {
        $cache_server =& cache_server();
        $key = 'common.navigation';
        $data = $cache_server->get($key);
        if($data === false)
        {
            $data = array(
                'header' => array(),
                'middle' => array(),
                'footer' => array(),
            );
            $nav_mod =& m('navigation');
            $rows = $nav_mod->find(array(
                'order' => 'type, sort_order',
            ));
            foreach ($rows as $row)
            {
                $data[$row['type']][] = $row;
            }
            $cache_server->set($key, $data, 86400);
        }

        return $data;
    }

    /**
     *    获取JS语言项
     *
     *    @author    MiMall
     *    @param    none
     *    @return    void
     */
    function jslang($lang = '')
    {
        $lang = array_merge(Lang::fetch(lang_file('jslang')), Lang::fetch(lang_file('mobile/jslang')));
        parent::jslang($lang);
    }

    /**
     *    视图回调函数[显示小挂件]
     *
     *    @author    MiMall
     *    @param     array $options
     *    @return    void
     */
    function display_widgets($options)
    {
        $area = isset($options['area']) ? $options['area'] : '';
        $page = isset($options['page']) ? $options['page'] : '';
        if (!$area || !$page)
        {
            return;
        }
        include_once(ROOT_PATH . '/includes/widget.base.php');

        /* 获取该页面的挂件配置信息 */
        $widgets = get_widget_config($this->_get_template_name(), $page, 'm');

        /* 如果没有该区域 */
        if (!isset($widgets['config'][$area]))
        {
            return;
        }

        /* 将该区域内的挂件依次显示出来 */
        foreach ($widgets['config'][$area] as $widget_id)
        {
            $widget_info = $widgets['widgets'][$widget_id];
            $wn     =   $widget_info['name'];
            $options=   $widget_info['options'];

            $widget =& widget($widget_id, $wn, $options);
            $widget->display();
        }
    }

    /**
     *    获取当前使用的模板名称
     *
     *    @author    MiMall
     *    @return    string
     */
    function _get_template_name()
    {
        return 'default';
    }

    /**
     *    获取当前使用的风格名称
     *
     *    @author    MiMall
     *    @return    string
     */
    function _get_style_name()
    {
        return 'default';
    }

    /**
     *    当前位置
     *
     *    @author    MiMall
     *    @param    none
     *    @return    void
     */
    function _curlocal($arr)
    {
        /*$curlocal = array(array(
            'text'  => Lang::get('index'),
            'url'   => SITE_URL . '/index.php',
        ));*/
		$curlocal = array();
		
        if (is_array($arr))
        {
            $curlocal = array_merge($curlocal, $arr);
        }
        else
        {
            $args = func_get_args();
            if (!empty($args))
            {
                $len = count($args);
                for ($i = 0; $i < $len; $i += 2)
                {
                    $curlocal[] = array(
                        'text'  =>  $args[$i],
                        'url'   =>  $args[$i+1],
                    );
                }
            }
        }

        $this->assign('_curlocal', $curlocal);
    }
	
	
	function _getPostData()
	{
		$post = file_get_contents("php://input");
		$post = json_decode($post, TRUE);
		
		if(!$post) $post = $_POST;
				
		$post = array_merge($_GET, $post);
		foreach($post as $key => $val)
		{
			if(is_string($val)) {
				$post[$key] = trim($val);
			}
		}
	
		return $post;
	}
	
	function _MakeSign($param) 
	{
		$key = $param['key'];
		$sign = $param['sign'];
		unset($param['sign']);unset($param['app']);unset($param['act']);unset($param['key']);
		
		// 对数组的值按key排序
		ksort($param);
		// 生成url的形式
		$params = http_build_query($param);
		// 生成sign
		$sign = md5($params.'&key='.$key);

		return sha1($sign);
	}
	
	/*验证请求的合法性*/
	function _VerifySign($param) 
	{
		$result = false;
		
		$sign = $this->_MakeSign($param);
		if($sign == $param['sign']){
			$result = true;
		}

		return $result;
	}
	
    function _init_visitor()
    {
        $this->visitor = &env('visitor', new UserVisitor());
    }
	function _get_curlocal_title($title)
	{
		$this->assign('curlocal_title',Lang::get($title));
	}
}
/**
 *    前台访问者
 *
 *    @author    MiMall
 *    @usage    none
 */
class UserVisitor extends BaseVisitor
{
    var $_info_key = 'user_info';

    /**
     *    退出登录
     *
     *    @author    MiMall
     *    @param    none
     *    @return    void
     */
    function logout()
    {
        /* 将购物车中的相关项的session_id置为空 */
        $mod_cart =& m('cart');
        $mod_cart->edit("user_id = '" . $this->get('user_id') . "'", array(
            'session_id' => '',
        ));
		
        /* 退出登录 */
        parent::logout();
    }
}
/**
 *    商城控制器基类
 *
 *    @author    MiMall
 *    @usage    none
 */
class MallbaseApp extends FrontendApp
{
    function _run_action()
    {
        /* 只有登录的用户才可访问 */
        if (!$this->visitor->has_login && in_array(APP, array('apply')))
        {
            header('Location:index.php?app=member&act=login&ret_url=' . rawurlencode(get_domain() . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']));

            return;
        }

        parent::_run_action();
    }

    function _config_view()
    {
        parent::_config_view();

        $template_name = $this->_get_template_name();
        $style_name    = $this->_get_style_name();
		
        $this->_view->template_dir = APP_ROOT . "/themes/mall/{$template_name}";
        $this->_view->compile_dir  = ROOT_PATH . "/temp/compiled/mobile/mall/{$template_name}";
        $this->_view->res_base     = site_url() . "/themes/mall/{$template_name}/styles/{$style_name}";
		$this->_view->lib_base     = dirname(site_url()) . '/includes/libraries/javascript';
    }

    /* 取得支付方式实例 */
    function _get_payment($code, $payment_info)
    {
        include_once(ROOT_PATH . '/includes/payment.base.php');
        include(ROOT_PATH . '/includes/payments/' . $code . '/' . $code . '.payment.php');
        $class_name = ucfirst($code) . 'Payment';

        return new $class_name($payment_info);
    }

    /**
     *   获取当前所使用的模板名称
     *
     *    @author    MiMall
     *    @return    string
     */
    function _get_template_name()
    {
        $template_name = Conf::get('wap_template_name');
        if (!$template_name)
        {
            $template_name = 'default';
        }

        return $template_name;
    }

    /**
     *    获取当前模板中所使用的风格名称
     *
     *    @author    MiMall
     *    @return    string
     */
    function _get_style_name()
    {
        $style_name = Conf::get('wap_style_name');
        if (!$style_name)
        {
            $style_name = 'default';
        }

        return $style_name;
    }
}

/**
 *    购物流程子系统基础类
 *
 *    @author    MiMall
 *    @usage    none
 */
class ShoppingbaseApp extends MallbaseApp
{
    function _run_action()
    {
        /* 只有登录的用户才可访问 */
        if (!$this->visitor->has_login && !in_array(ACT, array('login', 'register', 'check_user')))
        {
            if (!IS_AJAX)
            {
				// 加 get_domain() 是针对 www.abc.com/mall这样的站点， 如果没有/mall 则不用加也可以
                header('Location:index.php?app=member&act=login&ret_url=' . rawurlencode(get_domain() . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']));

                return;
            }
            else
            {
                $this->json_error('login_please');
                return;
            }
        }

        parent::_run_action();
    }
}

/**
 *    用户中心子系统基础类
 *
 *    @author    MiMall
 *    @usage    none
 */
class MemberbaseApp extends MallbaseApp
{
    function _run_action()
    {
        /* 只有登录的用户才可访问 */
		if (!(APP == 'member' && ACT == 'index') && !$this->visitor->has_login && !in_array(ACT, array('login', 'register', 'check_user', 'check_email_info','check_phone_mob')) && !in_array(APP, array('bind')))
		{
			if (!IS_AJAX)
			{
				header('Location:index.php?app=member&act=login&ret_url=' . rawurlencode(get_domain() . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']));
	
				return;
			}
			else
			{
				$this->json_error('login_please');
				return;
			}
		}
		
        parent::_run_action();
    }
	
	
    /**
     *    当前选中的菜单项
     *
     *    @author    MiMall
     *    @param     string $item
     *    @return    void
     */
    function _curitem($item)
    {
        $this->assign('has_store', $this->visitor->get('has_store'));
		
        $member_menu = $this->_get_member_menu();
		if(!$this->visitor->get('has_store')){
			unset($member_menu['im_seller']);
			$this->assign('member_role', 'buyer_admin');
		} else {
			if($_SESSION['member_role'] == 'buyer_admin') {
				unset($member_menu['im_seller']);
				$this->assign('member_role', 'buyer_admin');
			} else {
				unset($member_menu['im_buyer']);
				$this->assign('member_role', 'seller_admin');
			}
		}
        $this->assign('_member_menu', $member_menu);
        $this->assign('_curitem', $item);
    }
    /**
     *    当前选中的子菜单
     *
     *    @author    MiMall
     *    @param     string $item
     *    @return    void
     */
    function _curmenu($item)
    {
        $_member_submenu = $this->_get_member_submenu();
        foreach ($_member_submenu as $key => $value)
        {
            $_member_submenu[$key]['text'] = $value['text'] ? $value['text'] : Lang::get($value['name']);
        }
        $this->assign('_member_submenu', $_member_submenu);
        $this->assign('_curmenu', $item);
    }
    /**
     *    获取子菜单列表
     *
     *    @author    MiMall
     *    @param    none
     *    @return    void
     */
    function _get_member_submenu()
    {
        return array();
    }
    /**
     *    获取用户中心全局菜单列表
     *
     *    @author    MiMall
     *    @param    none
     *    @return    void
     */
    function _get_member_menu()
    {
        $menu = array();
		$integral_mod = &m('integral');
		
        /* 我是买家 */
        $menu['im_buyer'] = array(
            'name'  => 'im_buyer',
            'text'  => Lang::get('im_buyer'),
            'submenu'   => array(
                'my_order'  => array(
                    'text'  => Lang::get('my_order'),
					'sub_text'  => Lang::get('view_my_order'),
                    'url'   => url('app=buyer_order'),
                    'name'  => 'my_order',
                ),
				'my_capital' => array(
					'text' => Lang::get('my_capital'),
					'sub_text' => Lang::get('view_my_deposit'),
					'url'     => url('app=deposit'),
					'name'   => 'my_capital',
				),
			)
		);

		$menu['im_buyer']['submenu'] += array(	

                'my_question' =>array(
                    'text'  => Lang::get('my_question'),
                    'url'   => url('app=my_question'),
                    'name'  => 'my_question',
                ),
				'my_address'  => array(
                    'text'  => Lang::get('my_address'),
                    'url'   => url('app=my_address'),
                    'name'  => 'my_address',
                ),
				'my_report'  => array(
                    'text'  => Lang::get('my_report'),
                    'url'   => url('app=my_report'),
                    'name'  => 'my_report',
                ),
				'member_bind' => array(
					'text'  => Lang::get('member_bind'),
					'url'   => url('app=bind&act=search'),
					'name'  => 'member_bind',
				),
				'my_message'  => array(
                    'text'  => Lang::get('my_message'),
                    'url'   => url('app=message&act=newpm'),
                    'name'  => 'my_message',
                ),
				'refund' => array(
					'text' => Lang::get('refund_apply'),
					'url'  => url('app=refund'),
					'name' => 'refund_apply',
				),
				'my_refer' => array(
					'text' => Lang::get('my_refer'),
					'url'  => url('app=my_refer'),
					'name' => 'dcenter',
				),
            );
        
		
        if (!$this->visitor->get('has_store') && Conf::get('store_allow'))
        {
            /* 没有拥有店铺，且开放申请，则显示申请开店链接 */
			$menu['overview'] = array(
                'text' => Lang::get('apply_store'),
                'url'  => url('app=apply'),
				'margin'  => 'mb10',
				'name'  => 'apply_store'
            );
        }
        if ($this->visitor->get('manage_store'))
        {
            /* 指定了要管理的店铺 */
            $menu['im_seller'] = array(
                'name'  => 'im_seller',
                'text'  => Lang::get('im_seller'),
                'submenu'   => array(),
            );
			$menu['im_seller']['submenu']['order_manage'] = array(
                    'text'  => Lang::get('order_manage'),
					'sub_text' => Lang::get('view_all_order'),
                    'url'   => url('app=seller_order'),
                    'name'  => 'order_manage',
            );
			$menu['im_seller']['submenu']['my_capital'] = array(
					'text' => Lang::get('my_capital'),
					'sub_text' => Lang::get('view_my_deposit'),
					'url'     => url('app=deposit'),
					'name'   => 'my_capital',
			);
$menu['im_seller']['submenu']['my_goods'] = array(
                    'text'  => Lang::get('my_goods'),
                    'url'   => url('app=my_goods'),
                    'name'  => 'my_goods',
            ); 
			$menu['im_seller']['submenu']['my_category'] = array(
                    'text'  => Lang::get('my_category'),
                    'url'   => url('app=my_category'),
                    'name'  => 'my_category',
            ); 
	    			$menu['im_seller']['submenu']['my_delivery'] = array(
                    'text'  => Lang::get('my_delivery'),
                    'url'   => url('app=my_delivery'),
                    'name'  => 'my_delivery'
            );
            
            $menu['im_seller']['submenu']['my_qa'] = array(
                    'text'  => Lang::get('my_qa'),
                    'url'   => url('app=my_qa'),
                    'name'  => 'my_qa',
            );     
			$menu['im_seller']['submenu']['my_comment'] = array(
                    'text'  => Lang::get('my_comment'),
                    'url'   => url('app=my_comment'),
                    'name'  => 'my_comment'
            ); 
			$menu['im_seller']['submenu']['refund_manage'] = array(
                    'text'  => Lang::get('refund_manage'),
                    'url'   => url('app=refund&act=receive'),
                    'name'  => 'refund_manage',
            );
			$menu['im_seller']['submenu']['seller_coupon'] = array(
                    'text'  => Lang::get('seller_coupon'),
                    'url'   => url('app=seller_coupon'),
                    'name'  => 'seller_coupon'
            );

		   	$menu['im_seller']['submenu']['my_store'] = array(
                    'text'  => Lang::get('my_store'),
                    'url'   => url('app=my_store'),
                    'name'  => 'my_store',
            );
			$menu['im_seller']['submenu']['map'] = array(
                    'text'  => Lang::get('store_map'),
                    'url'   => url('app=my_store&act=map'),
                    'name'  => 'map',
            );
			$menu['im_seller']['submenu']['view_store'] = array(
                    'text'  => Lang::get('view_store'),
                    'url'   => url('app=store&id='.$this->visitor->get('manage_store')),
                    'name'  => 'view_store',
            );
			$menu['im_seller']['submenu']['buyer_admin'] = array(
                    'text'  => Lang::get('buyer_admin'),
                    'url'   => url('app=buyer_admin'),
                    'name'  => 'buyer_admin',
            );
			
			/* 营销中心 */
			$menu['im_seller']['submenu']['promotool'] = array(
                    'text'  => Lang::get('promotool'),
					'sub_text' => Lang::get('view_allapp'),
                    'url'   => url('app=appmarket'),
                    'name'  => 'promotool',
					'submenu' => array(
						/*'distribution_manage' => array(
                    		'text'  => Lang::get('distribution_manage'),
                    		'url'   => url('app=my_distribution'),
                    		'name'  => 'distribution_manage'
            			),*/
						'limitbuy_manage' => array(
			        		'text'  => Lang::get('limitbuy_manage'),
							'url'   => url('app=seller_limitbuy'),
							'name'  => 'limitbuy_manage',
						),
						'seller_meal' => array(
							'text'  => Lang::get('seller_meal'),
							'url'   => url('app=seller_meal'),
							'name'  => 'seller_meal',
						),
						'seller_fullfree' => array(
							'text'  => Lang::get('fullfree'),
							'url'   => url('app=seller_fullfree'),
							'name'  => 'fullfree',
						),
						'seller_fullprefer' => array(
							'text'  => Lang::get('fullprefer'),
							'url'   => url('app=seller_fullprefer'),
							'name'  => 'fullprefer',
						),
						'seller_fullgift' => array(
							'text'  => Lang::get('fullgift'),
							'url'   => url('app=seller_fullgift'),
							'name'  => 'fullgift',
						),
						'seller_growbuy' => array(
							'text'  => Lang::get('growbuy'),
							'url'   => url('app=seller_growbuy'),
							'name'  => 'growbuy',
						),
						'seller_exclusive' => array(
							'text'  => Lang::get('exclusive'),
							'url'   => url('app=seller_exclusive'),
							'name'  => 'exclusive',
						)
					),
            );

        }

        return $menu;
    }
}

/**
 *    店铺管理子系统基础类
 *
 *    @author    MiMall
 *    @usage    none
 */
class StoreadminbaseApp extends MemberbaseApp
{
    function _run_action()
    {
        /* 只有登录的用户才可访问 */
        if (!$this->visitor->has_login && !in_array(ACT, array('login', 'register', 'check_user')))
        {
            if (!IS_AJAX)
            {
                header('Location:index.php?app=member&act=login&ret_url=' . rawurlencode(get_domain() . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']));

                return;
            }
            else
            {
                $this->json_error('login_please');
                return;
            }
        }
        $referer = $_SERVER['HTTP_REFERER'];
        if (strpos($referer, 'act=login') === false)
        {
            $ret_url = $_SERVER['HTTP_REFERER'];
            $ret_text = 'go_back';
        }
        else
        {
            $ret_url = SITE_URL . '/index.php';
            $ret_text = 'back_index';
        }

        /* 检查是否是店铺管理员 */
        if (!$this->visitor->get('manage_store'))
        {
            /* 您不是店铺管理员 */
            $this->show_warning(
                'not_storeadmin',
                'apply_now', 'index.php?app=apply',
                $ret_text, $ret_url
            );

            return;
        }

        /* 检查是否被授权 */
        $privileges = $this->_get_privileges();
        if (!$this->visitor->i_can('do_action', $privileges))
        {
            $this->show_warning('no_permission', $ret_text, $ret_url);

            return;
        }

        /* 检查店铺开启状态 */
        $state = $this->visitor->get('state');
        if ($state == 0)
        {
            $this->show_warning('apply_not_agree', $ret_text, $ret_url);

            return;
        }
        elseif ($state == 2)
        {
            $this->show_warning('store_is_closed', $ret_text, $ret_url);

            return;
        }

        /* 检查附加功能 */
        if (!$this->_check_add_functions())
        {
            $this->show_warning('not_support_function', $ret_text, $ret_url);
            return;
        }

        parent::_run_action();
    }
    function _get_privileges()
    {
        $store_id = $this->visitor->get('manage_store');
        $privs = $this->visitor->get('s');

        if (empty($privs))
        {
            return '';
        }

        foreach ($privs as $key => $admin_store)
        {
            if ($admin_store['store_id'] == $store_id)
            {
                return $admin_store['privs'];
            }
        }
    }
    
    /* 获取当前店铺所使用的主题 */
    function _get_theme()
    {
        $model_store =& m('store');
        $store_info  = $model_store->get($this->visitor->get('manage_store'));
        $theme = !empty($store_info['theme']) ? $store_info['theme'] : 'default|default';
        list($curr_template_name, $curr_style_name) = explode('|', $theme);
        return array(
            'template_name' => $curr_template_name,
            'style_name'    => $curr_style_name,
        );
    }

    function _check_add_functions()
    {
        $apps_functions = array( // app与function对应关系
            'coupon' => 'coupon',
        );
		
        if (isset($apps_functions[APP]))
        {
            $store_mod =& m('store');
            $settings = $store_mod->get_settings($this->_store_id);
            $add_functions = isset($settings['functions']) ? $settings['functions'] : ''; // 附加功能
            if (!in_array($apps_functions[APP], explode(',', $add_functions)))
            {
                return false;
            }
        }
		
        return true;
    }
}

/**
 *    虚拟币管理子系统基础类
 *
 *    @author   Mimall
 *    @usage    none
 */
class DepositbaseApp extends MemberbaseApp
{
	function _run_action()
	{
		$this->assign('has_account', $this->_has_account());
		
		parent::_run_action();
	}
	
	/* 检查用户是否配置过预存款账户 */
	function _has_account()
	{
		$deposit_account_mod = &m('deposit_account');
		$deposit_account = $deposit_account_mod->get(array('conditions'=>'user_id='.$this->visitor->get('user_id')));
		if($deposit_account) {
			return 1;
		}
		return 0;
	}   
}


/**
 *    店铺控制器基础类
 *
 *    @author    MiMall
 *    @usage    none
 */
class StorebaseApp extends FrontendApp
{
    var $_store_id;

    /**
     * 设置店铺id
     *
     * @param int $store_id
     */
    function set_store($store_id)
    {
        $this->_store_id = intval($store_id);

        /* 有了store id后对视图进行二次配置 */
        $this->_init_view();
        $this->_config_view();
    }

    function _config_view()
    {
        parent::_config_view();
        $template_name = $this->_get_template_name();
        $style_name    = $this->_get_style_name();

		$this->_view->template_dir = APP_ROOT . "/themes/store/{$template_name}";
        $this->_view->compile_dir  = ROOT_PATH . "/temp/compiled/mobile/store/{$template_name}";
        $this->_view->res_base     = site_url() . "/themes/store/{$template_name}/styles/{$style_name}";
		$this->_view->lib_base     = dirname(site_url()) . '/includes/libraries/javascript';
		
		$wap_template_name = Conf::get('wap_template_name') ? Conf::get('wap_template_name'):'default';
		$wap_style_name = Conf::get('wap_style_name') ? Conf::get('wap_style_name'):'default';
		// 该赋值便于在店铺模板中调用商城模板的CSS,JS路径
		$this->assign('mall_theme_root',  site_url() . '/themes/mall/' . $wap_template_name . '/styles/'. $wap_style_name);
    }

    /**
     * 取得店铺信息
     */
    function get_store_data()
    {
        $cache_server =& cache_server();
        $key = 'function_get_store_data_' . $this->_store_id;
        $store = $cache_server->get($key);
        if ($store === false)
        {
            $store = $this->_get_store_info();
            if (empty($store))
            {
                $this->show_warning('the_store_not_exist');
                exit;
            }
            if ($store['state'] == 2)
            {
                $this->show_warning('the_store_is_closed');
                exit;
            }
            $step = intval(Conf::get('upgrade_required'));
            $step < 1 && $step = 5;
            $store_mod =& m('store');
            $store['credit_image'] = $this->_view->res_base . '/images/' . $store_mod->compute_credit($store['credit_value'], $step);

            empty($store['store_logo']) && $store['store_logo'] = Conf::get('default_store_logo');
            $store['store_owner'] = $this->_get_store_owner();
            $goods_mod =& m('goods');
            $store['goods_count'] = $goods_mod->get_count_of_store($this->_store_id);
			
            $store['grade_name'] = $this->_get_store_grade('grade_name');
            $functions = $this->_get_store_grade('functions');
            $store['functions'] = array();
            if ($functions)
            {
                $functions = explode(',', $functions);
                foreach ($functions as $k => $v)
                {
                    $store['functions'][$v] = $v;
                }
            }
			
			$store['industy_compare'] = Psmb_init()->get_industry_avg_evaluation($this->_store_id);
			
			// 粉丝
			$be_collect = db()->getOne("SELECT count(*) FROM ".DB_PREFIX."collect c WHERE type='store' AND item_id=".$this->_store_id);
			if($be_collect >= 10000) {
				$be_collect = ($be_collect/10000).'万';
			}
			$store['be_collect'] = $be_collect;
			
			// 是否收藏了该店
			$member_mod = &m('member');
			if($this->visitor->get('user_id') > 0)
			{
				$collect = $member_mod->get(array(
					'join' => 'collect_store',
					'conditions' => 'member.user_id='.$this->visitor->get('user_id').' AND type="store" AND item_id='.$this->_store_id,
					'fields' => 'item_id'
				));
				
				if($collect['item_id'] > 0)
				{
					$store['collected'] = 1;
				}
			}
			
            $cache_server->set($key, $store, 1800);
        }

        return $store;
    }
    /* 取得店铺信息 */
    function _get_store_info()
    {
        if (!$this->_store_id)
        {
            /* 未设置前返回空 */
            return array();
        }
        static $store_info = null;
        if ($store_info === null)
        {
            $store_mod  =& m('store');
            $store_info = $store_mod->get_info($this->_store_id);
        }

        return $store_info;
    }

    /* 取得店主信息 */
    function _get_store_owner()
    {
        $user_mod =& m('member');
        $user = $user_mod->get($this->_store_id);

        return $user;
    }

    /* 取得店铺导航 */
    function _get_store_nav()
    {
        $article_mod =& m('article');
        return $article_mod->find(array(
            'conditions' => "store_id = '{$this->_store_id}' AND cate_id = '" . STORE_NAV . "' AND if_show = 1",
            'order' => 'sort_order',
            'fields' => 'title',
        ));
    }
	
    /*  取的店铺等级   */
    function _get_store_grade($field)
    {
        $store_info = $store_info = $this->_get_store_info();
        $sgrade_mod =& m('sgrade');
        $result = $sgrade_mod->get_info($store_info['sgrade']);
        return $result[$field];
    }
    /* 取得店铺分类 */
    function _get_store_gcategory()
    {
        $gcategory_mod =& bm('gcategory', array('_store_id' => $this->_store_id));
        $gcategories = $gcategory_mod->get_list(-1, true);
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');
        return $tree->getArrayList(0);
    }

    /**
     *    获取当前店铺所设定的模板名称
     *
     *    @author    MiMall
     *    @return    string
     */
    function _get_template_name()
    {
        $store_info = $this->_get_store_info();
        $theme = !empty($store_info['wap_theme']) ? $store_info['wap_theme'] : 'default|default';
        list($template_name, $style_name) = explode('|', $theme);
        return $template_name;
    }

    /**
     *    获取当前店铺所设定的风格名称
     *
     *    @author    MiMall
     *    @return    string
     */
    function _get_style_name()
    {
        $store_info = $this->_get_store_info();
        $theme = !empty($store_info['wap_theme']) ? $store_info['wap_theme'] : 'default|default';
        list($template_name, $style_name) = explode('|', $theme);

        return $style_name;
    }
}

/* 实现消息基础类接口 */
class MessageBase extends MallbaseApp {};

/* 实现模块基础类接口 */
class BaseModule  extends FrontendApp {};

/* 消息处理器 */
require(ROOT_PATH . '/eccore/controller/message.base.php');

?>
