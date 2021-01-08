<?php

/**
 *    Desc
 *
 *    @author   mimall
 *    @usage    none
 */
class BindApp extends MemberbaseApp
{
	var $_member_mod;
	var $_bind_mod;
	
    function __construct()
    {
        $this->BindApp();
    }
    function BindApp()
    {
        parent::__construct();
		$this->_member_mod = &m('member');
		$this->_bind_mod = &m('member_bind');
    }
    
	/* 用户绑定 */
	function index()
	{
		$token = json_decode(base64_decode($_GET['token']),true);
		if(!isset($token['unionid']) || empty($token['unionid']))
		{
			$this->show_warning('session_expire');
			return;
		}
		
		/* 进入绑定界面，10分钟有效期 */
		if(!isset($token['bind_expire_time']) || ($token['bind_expire_time'] < gmtime()))
		{
			$this->show_warning('session_expire');
			return;
		}
		
		// 绑定当前登录的账户
		if ($this->visitor->has_login) 
		{
			$this->_bind1($token);	
		}
		// 绑定指定的账户
		else
		{
			$this->_bind2($token);
		}
	}
	
	/* 绑定列表 */
	function search()
	{
		$allow = array('qqconnect','weixinconnect','alipayconnect','xwbconnect');
		$bindlist = array();
		foreach($allow as $val)
		{
			$enabled = $this->_get_enabled_plugins('on_'.str_replace('connect', '',$val).'_login', $val) ? 1 : 0;
			if($enabled)
			{
				$plugin_info = $this->_get_plugin_info($val);
				if($plugin_info) {
					$bind = $this->_bind_mod->get("user_id=".$this->visitor->get('user_id')." AND app='".str_replace('connect', '',$val)."'");
					$bindlist[] = array(
						'key' 		=> $val,
						'name'		=> str_replace('connect', '', $val),
						'label' 	=> $plugin_info['name'],
						'enabled' 	=> (empty($bind) || !$bind['enabled']) ? 0 : 1,
					);
				}
			}
		}
		$this->assign('bindlist', $bindlist);
		
		$this->import_resource(array('script' => 'mobile/jquery.plugins/jquery.form.min.js'));
		
		/* 当前位置 */
        $this->_curlocal(LANG::get('member_bind'));

    	/* 当前用户中心菜单 */
   		$this->_curitem('bind');

    	/* 当前所处子菜单 */
  		$this->_curmenu('bindlist');
		
        $this->_config_seo(array('title' => Lang::get('bindlist') . ' - ' . Conf::get('site_title')));
		$this->display('bind.search.html');
	}
	
	/* 解除登录绑定 */
	function relieve()
	{
		$appid = trim($_GET['appid']);
		if(in_array($appid, array('qqconnect','weixinconnect','alipayconnect','xwbconnect'))) 
		{
			if($this->_bind_mod->edit("user_id=".$this->visitor->get('user_id')." AND app='".str_replace('connect', '',$appid)."'", array('enabled' => 0))) {
				$this->json_result('', 'unbind_ok');
				return;
			}
		}
		$this->json_error('unbind_fail');
	}
	
	/* 
	* 绑定当前登录的账户
	*
	* 必须是在登录的状态下进行绑定操作
	*	
	*/
	function _bind1($token)
	{
		$data = array();
			
		$unionid 	= $token['unionid'];
		$openid 	= $token['openid']; // 只有微信才有openid
		$nickname   = htmlspecialchars($token['nickname']);
		$app		= $token['app'];
			
		// 将绑定信息插入数据库
		$bindData = array(
			'unionid' 	=> $unionid, 
			'openid' 	=> $openid,
			'token' 	=> $token, 
			'user_id' 	=> $this->visitor->get('user_id'),
			'nickname'  => $nickname,
			'app' 		=> $app, 
			'enabled' 	=> 1
		);
				
		// 如果存在有绑定，则修改
		if($bind = $this->_bind_mod->get("unionid='{$unionid}' AND app='{$app}'")) {
			$this->_bind_mod->edit($bind['id'], $bindData);
		} 
		// APP中微信登录兼容处理
		elseif($bind = $this->_bind_mod->get("openid='{$openid}' AND app='{$app}'")) {
			$this->_bind_mod->edit($bind['id'], $bindData);
		}
		else{
			$this->_bind_mod->add($bindData);
		}
				
		//header("Location:". site_url() .'/index.php?app=bind&act=search');
		echo '<script>parent.layer.closeAll();</script>';
	}
	
	/*
	* 绑定指定的账户
    *
   	* 不再建议采用邮箱验证，代码暂时预留
	* 
	*/ 
	function _bind2($token)
	{
		if(!IS_POST)
		{
			// QQ登录审核时需要在页面显示用户昵称
			$this->assign('bind', $token);
			
			// 通过此来限制机器恶意批量发送短信
			$_SESSION['_sendcode_ing'] = TRUE;
		
			$bindType = trim($_GET['type']);
			if(in_array($bindType, array('email'))) {
				$title = Lang::get('email_verify');// 此种情况已不推荐
			} else $title = Lang::get('phone_verify');
			
        	$this->_config_seo(array('title' => $title . ' - ' . Conf::get('site_title')));
			$this->display('bind.index.html');
		}
		else
		{	
			$data = array();
			
			$unionid 	= $token['unionid'];
			$openid 	= $token['openid']; // 只有微信才有openid
			$nickname   = htmlspecialchars($token['nickname']);
			$app		= $token['app'];
			
			$phone_mob 	= trim($_POST['phone_mob']);
			$email    	= trim($_POST['email']);
			$password  	= trim($_POST['password']);
				
			$codeType 	= (!empty($_GET['codeType']) && in_array($_GET['codeType'], array('phone', 'email'))) ? trim($_GET['codeType']) : 'phone';
			$code      	= trim($_POST['code']);
			
			$ms =& ms();
			if($codeType == 'email')
			{
				if(!is_email($email)) {
					$this->show_warning('email_invalid');
					return;
				}
					
				/*  检查Email是否被注册过 */
				if($member = $this->_member_mod->get(array('conditions' => 'email="'.$email.'"', 'fields' => 'user_id, user_name, password'))){
					if(!$ms->user->auth($member['user_name'], $password)) {
						$this->show_warning('err_orig_password');
						return;
					}
					$user_id = $member['user_id'];
				}
				else
				{
					if(($_SESSION['email_code'] != md5($email.$code)) || ($_SESSION['last_send_time_email_code'] + 120 < gmtime())) {
						$this->show_warning('email_code_check_failed');
						return;
					}	
				}
				$data['email'] = $email;
			}
			elseif($codeType == 'phone')
			{
				if(!is_mobile($phone_mob)) {
					$this->show_warning('phone_mob_invalid');
					return;
				}
					
				/* 检查手机号是否被注册过 */
				if($member = $this->_member_mod->get(array('conditions' => 'phone_mob="'.$phone_mob.'"', 'fields' => 'user_id, user_name, password'))) {
					if(!$ms->user->auth($member['user_name'], $password)) {
						$this->show_warning('err_orig_password');
						return;
					}
					$user_id = $member['user_id'];
				}
				else
				{
					if(($_SESSION['phone_code'] != md5($phone_mob.$code)) || ($_SESSION['last_send_time_phone_code'] + 120 < gmtime())) {
						$this->show_warning('phone_code_check_failed');
						return;
					}
					$data['phone_mob'] 	= $phone_mob;
				}
			} else {
				exit(0);
			}
				
			/* 如果是绑定新用户，则执行注册 */
			if(!$user_id) 
			{
				$bind = $this->_bind_mod->get("(unionid='{$openid}' OR openid='{$openid}') AND app='{$app}'");
				if(!empty($bind) && $member = $this->_member_mod->get($bind['user_id'])){
					$this->_member_mod->edit($bind['user_id'],$data);
					$user_id = $bind['user_id'];
				}else{
					do {	 
						if(isset($token['nickname']) && !empty($token['nickname'])) {
							$user_name  = $ms->user->check_username($token['nickname']) ? $token['nickname'] : $token['nickname'].mt_rand(10,99);
						} else $user_name  = gmtime() . mt_rand(10,99);
						$password  = mt_rand(1000, 9999);
						if(!$data['email']) $data['email'] = gmtime() . mt_rand(10,99).'@'.$app.'.com';
						if($token['real_name']) $data['real_name'] = $token['real_name'];
						if($token['portrait']) $data['portrait']   = $token['portrait'];
						$user_id = $ms->user->register($user_name, $password, $data['email'], $data);
					} while (!$user_id);
				}
			}
		
			// 将绑定信息插入数据库
			$bindData = array(
				'unionid' 	=> $unionid, 
				'openid' 	=> $openid,
				'token' 	=> $token, 
				'user_id' 	=> $user_id, 
				'nickname'  => $nickname,
				'app' 		=> $app, 
				'enabled' 	=> 1
			);
				
			// 如果存在有绑定，则修改
			if($bind = $this->_bind_mod->get("unionid='{$unionid}' AND app='{$app}'")) {
				$this->_bind_mod->edit($bind['id'], $bindData);
			} 
			// APP中微信登录兼容处理
			elseif($bind = $this->_bind_mod->get("openid='{$openid}' AND app='{$app}'")) {
				$this->_bind_mod->edit($bind['id'], $bindData);
			}
			else{
				$this->_bind_mod->add($bindData);
			}
				
			/* 退出绑定模式 */
			unset($_SESSION['phone_code'], $_SESSION['last_send_time_phone_code'], $_SESSION['email_code'], $_SESSION['last_send_time_email_code']);
			
			// 登录
			$this->_do_login($user_id);
					
			/* 同步登陆外部系统 */
			$synlogin = $ms->user->synlogin($user_id);
				
			header("Location:".htmlspecialchars_decode($this->getRetUrl(TRUE)));
		}
	}
}

?>