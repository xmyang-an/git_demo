<?php

class QqconnectApp extends MallbaseApp
{
	const GET_AUTH_CODE_URL = "https://graph.qq.com/oauth2.0/authorize";
    const GET_ACCESS_TOKEN_URL = "https://graph.qq.com/oauth2.0/token";
    const GET_OPENID_URL = "https://graph.qq.com/oauth2.0/me";
	const GET_USER_INFO_URL = "https://graph.qq.com/user/get_user_info";
	
	var $_member_mod;
	var $_bind_mod;
	var $_app;
	var $_config = array();
	var $error;
	
    function __construct()
    {
        $this->QqconnectApp();
		$this->error = new ErrorCase();
    }
    function QqconnectApp()
    {
        parent::__construct();
		$this->_member_mod = &m('member');
		$this->_bind_mod = &m('member_bind');
		$this->_app      = 'qq';
		$this->_config = $this->_get_plugin_conf(array('name'=>'qqconnect','event'=>'on_qq_login'));
    }
	
	function callback()
	{
		extract($this->_config);
        //-------请求参数列表
        $keysArr = array(
            "grant_type" => "authorization_code",
            "client_id" => $appid,
            "redirect_uri" => $callback,
            "client_secret" => $appkey,
            "code" => $_GET['code']
        );
		
        //------构造请求access_token的url
		$token_url = $this->combineUrl(self::GET_ACCESS_TOKEN_URL, $keysArr);
        $response = $this->get_distant_contents($token_url);
        if(strpos($response, "callback") !== false){

            $lpos = strpos($response, "(");
            $rpos = strrpos($response, ")");
            $response  = substr($response, $lpos + 1, $rpos - $lpos -1);
            $msg = json_decode($response);

            if(isset($msg->error)){
                 ErrorCase::showError($msg->error, $msg->error_description);
            }
        }

        $params = array();
        parse_str($response, $params);
		$unionid = $openid = $this->get_openid($params["access_token"]);
		
		if($unionid)
		{
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
				$user_info = $this->get_user_info($params["access_token"], $openid, $this->_config['appid']);
				
				// 进入绑定模式
				$bind = array(

					'unionid'			=> $unionid,
					'openid' 			=> $openid,
					'app' 				=> $this->_app, 
					'bind_expire_time' 	=> gmtime() + 600, 
					'nickname' 			=> $user_info['nickname'], 
					'portrait'			=> $user_info['figureurl_qq_2'],
					'real_name'			=> $user_info['nickname']
				);
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
		$_SESSION['ret_url'] = $this->getRetUrl(TRUE);
		
		extract($this->_config);
		
        //-------构造请求参数列表
        $keysArr = array(
            "response_type" => "code",
            "client_id" => $appid,
            "redirect_uri" => $callback,
            "state" => mt_rand().gmtime(),
            "scope" => $scope
        );
		$_SESSION['Qqconnect'] = true;
		$login_url = $this->combineUrl(self::GET_AUTH_CODE_URL,$keysArr);
        header("Location:$login_url");
   }
   function combineUrl($baseurl,$arr)
   {
	    $combined = $baseurl."?";
        $value= array();
        foreach($arr as $key => $val){
            $value[] = "$key=$val";
        }
        $imstr = implode("&",$value);
        $combined .= ($imstr);
        return $combined;
   }
   function get_distant_contents($url){
        //if (ini_get("allow_url_fopen") == "1") {
           // $response = file_get_contents($url);
        //}else{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_URL, $url);
            $response =  curl_exec($ch);
            curl_close($ch);
        //}
        //-------请求为空
        if(empty($response)){
            $this->error->showError("50001");
        }
        return $response;
    }
	function get_openid($access_token){
		
        //-------请求参数列表
        $keysArr = array(
            "access_token" => $access_token
        );
        $graph_url = $this->combineUrl(self::GET_OPENID_URL, $keysArr);
		
        $response = $this->get_distant_contents($graph_url);

        //--------检测错误是否发生
        if(strpos($response, "callback") !== false){
            $lpos = strpos($response, "(");
            $rpos = strrpos($response, ")");
            $response = substr($response, $lpos + 1, $rpos - $lpos -1);
        }

        $user = json_decode($response);
        if(isset($user->error)){
            $this->error->showError($user->error, $user->error_description);
        }
        return $user->openid;

    }
	function get_user_info($access_token,$openid,$appid)
	{
		$keysArr = array(
               "oauth_consumer_key" => $appid,
               "access_token" => $access_token,
               "openid" =>$openid
         );
		 $url=$this->combineUrl(self::GET_USER_INFO_URL,$keysArr);
		 $response=json_decode($this->get_distant_contents($url));
		 $responseArr = $this->objToArr($response);
        //检查返回ret判断api是否成功调用
        if($responseArr['ret'] == 0){
            return $responseArr;
        }else{
            $this->error->showError($response->ret, $response->msg);
        }
	}
	function objToArr($obj){
        if(!is_object($obj) && !is_array($obj)) {
            return $obj;
        }
        $arr = array();
        foreach($obj as $k => $v){
            $arr[$k] = $this->objToArr($v);
        }
        return $arr;
    }
}
/*
 * @brief ErrorCase类，封闭异常
 * */
class ErrorCase{
    private $errorMsg;

    public function __construct(){
        $this->errorMsg = array(
            "20001" => "<h2>配置文件损坏或无法读取，请重新执行intall</h2>",
            "30001" => "<h2>The state does not match. You may be a victim of CSRF.</h2>",
            "50001" => "<h2>可能是服务器无法请求https协议</h2>可能未开启curl支持,请尝试开启curl支持，重启web服务器，如果问题仍未解决，请联系我们"
            );
    }

    /**
     * showError
     * 显示错误信息
     * @param int $code    错误代码
     * @param string $description 描述信息（可选）
     */
    public function showError($code, $description = '$'){
        echo "<meta charset=\"UTF-8\">";
        if($description == "$"){
            die($this->errorMsg[$code]);
        }else{
            echo "<h3>error:</h3>$code";
            echo "<h3>msg  :</h3>$description";
            exit(); 
        }
    }
    public function showTips($code, $description = '$'){
    }
}

?>
