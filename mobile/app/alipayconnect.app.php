<?php

class AlipayconnectApp extends MallbaseApp
{
	var $_member_mod;
	var $_bind_mod;
	var $_app;
	var $_config = array();
	
    function __construct()
    {
        $this->AlipayconnectApp();
    }
    function AlipayconnectApp()
    {
        parent::__construct();
		$this->_member_mod  = &m('member');
		$this->_bind_mod 	= &m('member_bind');
		$this->_app      	= 'alipay';
		
		$this->_config = $this->_get_plugin_conf(array('name'=>'alipayconnect','event'=>'on_alipay_login'));
    }
	
	function callback()
	{
		$auth_code = $_GET['auth_code'];
		$state 	   = $_GET['state'];
		
		// 有可能是CSRF攻击（此规则无法兼容pc/h5登录）
		if($state != $_SESSION['alipayconnectstate']) {
			//$this->show_warning('CSRF攻击');
			//return;
		}
		
		/* alipay.system.oauth.token(换取授权访问令牌) */
		if(($response = $this->getAccessToken($auth_code)) == FALSE) {
			$this->show_warning('授权令牌获取失败');
			return;			
		}
		if($response->access_token)
		{
			//支付宝用户Id
			$unionid = trim($response->user_id);

			$bind = $this->_bind_mod->get(array(
				'conditions'=>"unionid='".$unionid."' AND app='".$this->_app."'", 'fields'=>'user_id,enabled'));
			
			// 包含登录状态绑定的情况，如果当前登录用户与原有绑定用户不一致，则修改为新绑定
			if($bind && $bind['user_id'] && $bind['enabled'] && (!$this->visitor->has_login || ($this->visitor->get('user_id') == $bind['user_id'])))
			{
				$user_id = $bind['user_id'];
				
				/* 如果该unionid已经绑定， 则检查该用户是否存在 */
				if(!$member = $this->_member_mod->get(array('conditions'=>'user_id='.$user_id, 'fields'=>'phone_mob, email'))) {
					/* 如果没有此用户，则说明绑定数据过时，删除绑定 */
					$this->_bind_mod->drop('user_id='.$user_id);
					$this->show_message('bind_data_error');
					return;
				}
				
				// 执行登录
				$this->_do_login($user_id);
				
				/* 同步登陆外部系统 */
				$ms =& ms();
				$synlogin = $ms->user->synlogin($user_id);
				//$this->show_message(Lang::get('login_successed') . $synlogin, 'back_index', site_url());
				header("Location:".htmlspecialchars_decode($this->getRetUrl(TRUE)));
			}
			else
			{
				// 进入绑定模式
				$bind = array('unionid' => $unionid, 'app' => $this->_app, 'bind_expire_time' => gmtime() + 600, 'token' => $response->access_token);
				
				/* alipay.user.info.share(支付宝会员授权信息查询接口) */
				if(($alipayUserInfo = $this->getUserInfo($response->access_token)) != FALSE) {
					$bind['portrait'] 	= $alipayUserInfo->avatar;
					$bind['real_name'] 	= $alipayUserInfo->nick_name;
				}
				$url = SITE_URL . '/' . url('app=bind&token='.base64_encode(json_encode($bind)));
				header("Location:".htmlspecialchars_decode($url));
			}
		}
		else
		{
    		$this->show_warning('verify_fail');
			return;
		}

	}
	
	function login()
	{
		// state防止CSRF
		$state = mt_rand();
		$_SESSION['alipayconnectstate'] = $state;
		
		$_SESSION['ret_url'] = $this->getRetUrl(TRUE);
		
		// 跳转授权页面
		$url = "https://openauth.alipay.com/oauth2/publicAppAuthorize.htm?app_id={$this->_config['appId']}&scope={$this->_config['scope']}&redirect_uri={$this->_config['redirect_uri']}&state={$state}";
		header("location:$url");
	}
	
	/* alipay.system.oauth.token(换取授权访问令牌) */
	function getAccessToken($auth_code = '')
	{
		$response = FALSE;
		
		if($auth_code) 
		{
			require_once(ROOT_PATH . "/external/plugins/alipayconnect/lib/AopClient.php");
			require_once(ROOT_PATH . "/external/plugins/alipayconnect/lib/SignData.php");
			require_once(ROOT_PATH . "/external/plugins/alipayconnect/lib/request/AlipaySystemOauthTokenRequest.php");
			
			$aop = new AopClient ();
			$aop->gatewayUrl = $this->_config['gatewayUrl'];
			$aop->appId = $this->_config['appId'];
			$aop->rsaPrivateKey = $this->_config['rsaPrivateKey'];
			$aop->alipayrsaPublicKey = $this->_config['alipayrsaPublicKey'];
			$aop->apiVersion = '1.0';
			$aop->signType = $this->_config['signType'];
			$aop->postCharset = CHARSET;
			$aop->format = 'json';
			$request = new AlipaySystemOauthTokenRequest ();
			$request->setGrantType("authorization_code");
			$request->setCode($auth_code);
			//$request->setRefreshToken("上一次的access_token"); // 可选
			$result = $aop->execute ( $request); 
			
			/* responseNode （字段可能有变化，请参照具体的返回值）
			 * user_id: "2088102150477652",
			 * access_token: "20120823ac6ffaa4d2d84e7384bf983531473993",
			 * expires_in: "3600",
			 * refresh_token: "20120823ac6ffdsdf2d84e7384bf983531473993",
			 * re_expires_in: "3600"
			 * @link: https://docs.open.alipay.com/api_9/alipay.system.oauth.token
			*/
			$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
			//$resultCode = $result->$responseNode->code;
			//if(!empty($resultCode) && $resultCode == 10000){
			if($result->$responseNode->access_token) {
				$response = $result->$responseNode;
			} else {
				//$response = FALSE;
			}
		}
		return $response;
	}
	
	/* alipay.user.info.share(支付宝会员授权信息查询接口) */
	function getUserInfo($accessToken = '')
	{
		$response = FALSE;
		
		if($accessToken) 
		{
			require_once(ROOT_PATH . "/external/plugins/alipayconnect/lib/AopClient.php");
			require_once(ROOT_PATH . "/external/plugins/alipayconnect/lib/SignData.php");
			require_once(ROOT_PATH . "/external/plugins/alipayconnect/lib/request/AlipayUserInfoShareRequest.php");
			
			$aop = new AopClient ();
			$aop->gatewayUrl = $this->_config['gatewayUrl'];
			$aop->appId = $this->_config['appId'];
			$aop->rsaPrivateKey = $this->_config['rsaPrivateKey'];
			$aop->alipayrsaPublicKey = $this->_config['alipayrsaPublicKey'];
			$aop->apiVersion = '1.0';
			$aop->signType = $this->_config['signType'];
			$aop->postCharset = CHARSET;
			$aop->format = 'json';
			$request = new AlipayUserInfoShareRequest ();
			$result = $aop->execute ( $request , $accessToken ); 
			
			/* responseNode （字段可能有变化，请参照具体的返回值）
			 * user_id: "2088102104794936",
			 * avatar: "http://tfsimg.alipay.com/images/partner/T1uIxXXbpXXXXXXXX",
			 * nick_name: "支付宝小二",
			 * gender: "F"
			 * @link: https://docs.open.alipay.com/api_2/alipay.user.info.share
			*/
			$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
			$resultCode = $result->$responseNode->code;
			if(!empty($resultCode) && $resultCode == 10000){
				$response = $result->$responseNode;
			}
			else {
				//$response = FALSE;
			}
		}
		return $response;
	}
	
	function object2array(&$object) {
 		$object =  json_decode( json_encode( $object),true);
		return  $object;
    }
}

?>