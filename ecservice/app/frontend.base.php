<?php

/**
 *    前台控制器基础类
 *
 *    @author    Garbin
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
		
        parent::__construct();
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
        parent::display($tpl);
	}
	
	/**
     *    获取当前使用的模板名称
     *
     *    @author    Garbin
     *    @return    string
     */
    function _get_template_name()
    {
        return 'default';
    }

    /**
     *    获取当前使用的风格名称
     *
     *    @author    Garbin
     *    @return    string
     */
    function _get_style_name()
    {
        return 'default';
	}
    function _init_visitor()
    {
        $this->visitor =& env('visitor', new UserVisitor());
    }
}

/**
 *    前台访问者
 *
 *    @author    Garbin
 *    @usage    none
 */
class UserVisitor extends BaseVisitor
{
    var $_info_key = 'user_info';

    /**
     *    退出登录
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function logout()
    {
        /* 退出登录 */
        parent::logout();
    }
}

/**
 *    商城控制器基类
 *
 *    @author    Garbin
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
		
        $this->_view->template_dir = ROOT_PATH . "/mobile/themes/mall/{$template_name}";
        $this->_view->compile_dir  = ROOT_PATH . "/temp/compiled/mobile/mall/{$template_name}";
        $this->_view->res_base     = dirname(site_url()) . "/mobile/themes/mall/{$template_name}/styles/{$style_name}";
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
     *    @author    Garbin
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
     *    @author    Garbin
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
}

class MemberbaseApp extends MallbaseApp
{
	function _run_action()
	{
		parent::_run_action(); 
	}
	
	function _checkLogin($getInfo = FALSE)
	{
		$post = parent::_getPostData();
		
		$result = array();
		
		// 未授权
		if(!isset($post['accessToken']) || empty($post['accessToken'])) {
			$result = array(
				'status' => FALSE,
				'errorMsg' => '未授权',
			);
		}
		else
		{
			// 检查是否过期
			$appKey = trim($post['appKey']);
			$token = trim($post['accessToken']);
			
			$merchantLog_mod = &m('merchantLog');
			
			if($merchantLog = $merchantLog_mod->get("token='{$token}' AND appId='{$appKey}'")) {
				
				// 已经过期
				if($merchantLog['expired'] <= gmtime()) {
					$result = array(
						'status' => FALSE,
						'errorMsg' => 'accessToken已过期，请重新授权',
					);
				}
				else
				{
					// 需要返回部分用户信息
					if($getInfo) {
						
						$merchant_mod	 = &m('merchant');
						$merchant = $merchant_mod->get(array('conditions' => "appId='{$merchantLog['appId']}'", 'fields' => 'user_id,appId,name'));
						
						$member_mod = &m('member');
						$member = $member_mod->get(array('conditions' =>'user_id='.$merchant['user_id'],'fields' =>'user_id, user_name, email'));
						$result = array(
							'status' => TRUE,
							'retval' => array_merge(array('companyCustNo' => $merchant['appId'], 'companyName' => $merchant['name']), $member)
						);
					}
				}
			}
			// 非法token
			else
			{
				$result = array(
					'status' => FALSE,
					'errorMsg' => '非法访问(accessToken错误)',
				);
			}
		}
		
		return array($result['status'], $result);
	}
}

?>
