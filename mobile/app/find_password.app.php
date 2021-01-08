<?php
/**
 * 找回密码控制器
 * @author andcpp
 */
class Find_passwordApp extends MallbaseApp
{
    var $_member_mod;
    function __construct()
    {
        $this->Find_passwordApp();
    }

    function Find_passwordApp()
    {
        parent::FrontendApp();
        $this->_member_mod = &m("member");
    }

    /**
     * 显示文本框及处理提交的用户信息
     *
     */
    function index()
    {
       if(!IS_POST)
       {
		   $this->import_resource(array(
            	'script' => array(
					array(
                    	'path' => 'mobile/dialog/dialog.js',
                    	'attr' => 'id="dialog_js"',
                	),
                	array(
                    	'path' => 'mobile/jquery.ui/jquery.ui.js',
                    	'attr' => '',
					),
				),
            	'style' =>  'mobile/jquery.ui/themes/ui-lightness/jquery.ui.css',
        	));
			
			/* 配置seo信息 */
        	$this->_config_seo(array('title' => Lang::get('find_password') . ' - ' . Conf::get('site_title')));
			$this->_get_curlocal_title('find_password');
			$this->display("find_password.html");
       }
       else
       {
			$user_name	= trim($_POST['user_name']);
		   	$codeType 	= trim($_POST['codeType']);
			$code     	= trim($_POST['code']);
			
			// code must  but captcha not must
			if (empty($user_name) || (isset($_POST['captcha']) && empty($_POST['captcha'])) || empty($codeType) || empty($code))
           	{
               	$this->json_error("unsettled_required");
               	return;
           	}
           	if (isset($_POST['captcha']) && (base64_decode($_SESSION['captcha']) != strtolower($_POST['captcha'])))
           	{
               	$this->json_error("captcha_faild");
               	return;
           	}
		   
			$member = $this->_member_mod->get(array('conditions'=>'phone_mob = "'.$user_name.'" OR user_name="'.$user_name.'"', 'fields'=>'user_id, email, phone_mob'));
			if($codeType == 'email')
			{
				if(($_SESSION['email_code'] != md5($member['email'].$code)) || ($_SESSION['last_send_time_email_code'] + 120 < gmtime())) {
					$this->json_error('email_code_check_failed');
					return;	
				}
			}
			elseif($codeType == 'phone')
			{
				if(($_SESSION['phone_code'] != md5($member['phone_mob'].$code)) || ($_SESSION['last_send_time_phone_code'] + 120 < gmtime())) {
					$this->json_error('phone_code_check_failed');
					return;
				}
			}

           /* 至此，验证通过，为该用户重置密码 */
		   $activation = ($codeType == 'email') ? $_SESSION['email_code'] : $_SESSION['phone_code'];
		   $this->_member_mod->edit($member['user_id'], array('activation' => $activation));
		   $this->json_result(array('ret_url' => url('app=find_password&act=set&id='.$member['user_id'].'&key='.$activation)), '验证通过');
       }
    }

    /**
     * 显示设置密码及处理提交的新密码信息
     *
     */
    function set()
    {
		$id  = intval($_GET['id']);
		$key = trim($_GET['key']);
		
		
		
		
        if (!IS_POST)
        {
			if(!$id || empty($key))
			{
				$this->show_warning("request_error");
				return;
			}
				
			if(($_SESSION['email_code'] && ($_SESSION['email_code'] != $key)) || ($_SESSION['phone_code'] && ($_SESSION['phone_code'] != $key)))
			{
				$this->show_warning("session_expire");
				return;
			}
		
            $this->import_resource('mobile/jquery.plugins/jquery.form.min.js');
			
            /* 配置seo信息 */
        	$this->_config_seo(array('title' => Lang::get('set_password') . ' - ' . Conf::get('site_title')));
			$this->_get_curlocal_title('set_password');
			$this->display("find_password.step2.html");
        }
        else
        {
			if(!$id || empty($key))
			{
				$this->json_error("request_error");
				return;
			}
				
			if(($_SESSION['email_code'] && ($_SESSION['email_code'] != $key)) || ($_SESSION['phone_code'] && ($_SESSION['phone_code'] != $key)))
			{
				$this->json_error("session_expire");
				return;
			}
			
			$member = $this->_member_mod->get(array('conditions' => 'user_id='.$id, 'fields'=>'activation'));
			if(!$member || ($member['activation'] != $key))
			{
				$this->json_error("request_error");
				return;
			}
			
			$password 		= trim($_POST['password']);
			$confirm_password 	= trim($_POST['confirm_password']);
			
            if (empty($password) || empty($confirm_password))
            {
                $this->json_error("unsettled_required");
                return ;
            }
            if ($password != trim($confirm_password))
            {
                $this->json_error("password_not_equal");
                return ;
            }
			
            $passlen = strlen($password);
            if ($passlen < 6 || $passlen > 20)
            {
                $this->json_error('password_length_error');
                return;
            }
			
            $ms =& ms();        //连接用户系统
            $ms->user->edit($id, '', array('password' => $password), true); //强制修改
            if ($ms->user->has_error())
            {
				$error = current($ms->user->get_error());
                $this->show_warning($error['msg']);
                return;
            }
			$this->_member_mod->edit($id, array('activation' => ''));
			unset($_SESSION['phone_code'], $_SESSION['last_send_time_phone_code'], $_SESSION['email_code'],$_SESSION['last_send_time_email_code']);
			$this->json_result(array('ret_url' => url('app=find_password&act=success')), 'setPassword_successed');
        }
    }

	function success()
	{
		$this->assign('ret_url', url('app=member'));
		
		 /* 配置seo信息 */
        $this->_config_seo(array('title' => Lang::get('setPassword_successed') . ' - ' . Conf::get('site_title')));
		$this->_get_curlocal_title('setPassword_successed');
		$this->display("find_password.step3.html");
	}
	
}
?>
