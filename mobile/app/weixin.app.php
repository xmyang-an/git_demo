<?php

class WeixinApp extends MallbaseApp
{
	var $weixin;
	var $member_mod;
	var $bind_mod;
	var $weixin_reply_mod;
	
    function __construct()
    {
        $this-> WeixinApp();
    }
    function  WeixinApp()
    {
        parent::__construct();
		$this->bind_mod = &m('member_bind');
		$this->member_mod = &m('member');
		$this->weixin_reply_mod = &m('weixin_reply');
		
		import('weixin.lib');
		$this->weixin = new weixin();
    }
	
    function index()
    {
		$this->weixin->valid(); // 验证
		$postData = $this->weixin->getPostData();
		if($postData['MsgType'] == 'event') //接收事件推送
		{
			switch($postData['Event'])
			{
				case 'subscribe'://关注事件
					if($this->weixin->_config['auto_login']){
						$this->autoRegister($postData['FromUserName']);
					}
					$reply = $this->weixin_reply_mod->get("user_id=0 AND action='beadded'");
					if($reply)
					{
						if($reply['type']){
							$content[] = $reply;
						}else{
							$content = $reply['content'];
						}
						
					}
					else
					{
						$wxInfo = $this->weixin->getUserInfo($postData['FromUserName']);
						$content = $wxInfo['nickname'].' 您好，欢迎您关注' . $this->weixin->_config['name'] .'! <a href="' . site_url() . '/index.php?app=weixin&act=doLogin&openid='.$postData['FromUserName'].'">【会员中心】</a>';
					}
				break;
				
				case 'CLICK'://点击菜单拉取消息时的事件推送,后台设定为图文消息
					$reply = $this->weixin_reply_mod->get(intval($postData['EventKey']));
					if(empty($reply)){
						return;
					}
					$content[] = $reply;
				break;
				default:
				break;
			}
		}else{
			//先执行回复命令，再找关键字，再自动回复
			$getContent = $postData['Content'];
			//关键词命令
			$getContent && $reply = $this->checkKeywords($getContent);
			
			if($reply){//关键字回复
				if($reply['type'])//图文消息
				{
					$content[] = $reply;
				}
				else
				{
					$content = $reply['content']; 
				}
			}else{//自动回复
				$autoreply = $this->weixin_reply_mod->get("user_id=0 AND action='autoreply'");
				if($autoreply){
					if($autoreply['type'])//图文消息
					{
						$content[] = $autoreply;
					}
					else
					{
						$content = $autoreply['content']; 
					}
				}
			}
		}
		$resultStr = $this->weixin->getMsgXML($postData['FromUserName'], $postData['ToUserName'], $content);
		if($resultStr){
			 echo $resultStr;
			 exit;
		}
	}
	
	
	
	function checkKeywords($word = '')
	{
		$replys = $this->weixin_reply_mod->find("user_id=0 AND keywords LIKE '%".$word."%'");
		foreach($replys as $key => $val){
			$keywords = explode(',',str_replace('，',',',$val['keywords']));
			if(in_array($word,$keywords)){
				return $val;//找到匹配即返回
			}
		}
		return false;
	}
	
	function getCode($redirect_uri)
	{
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->weixin->_config['appid']."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_base&state=".mt_rand()."#wechat_redirect";
		header("location:$url");
	}
	
	function getOpenid($code)
	{
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->weixin->_config['appid']."&secret=".$this->weixin->_config['appsecret']."&code=".$code."&grant_type=authorization_code";
		$result = ecm_curl($url);
		if(!$result)
		{
			return false;
		}
		$result = json_decode($result, true);
		return $result['openid'];
	}
	
	function login()
	{
		$redirect_uri = urlencode(site_url() . '/index.php?app=weixinconnect&act=callback');
		$this->getCode($redirect_uri);
	}
	
	/**
     *  自动注册并登陆
     *  @author mimall
     *  @param  string $FromUserName
     */
	function autoRegister($openid)
	{
		if(!$openid)
		{
			return;
		}
		$wx_info = $this->weixin->getUserInfo($openid);
		if(!$wx_info)
		{
			return;
		}
		$ms =& ms();
		$bind = $this->bind_mod->get(array('conditions'=>"openid='".$openid."' OR unionid='".$openid."' AND app='weixin'"));
		if($bind && $this->member_mod->get($bind['user_id']))
		{
			return;
		}
		else
		{
			if(strlen($wx_info['nickname']) <=30 && $ms->user->check_username($wx_info['nickname'])){
				$user_name = $wx_info['nickname'];
			}
			else{
				$user_name = $wx_info['nickname'].'_'.mt_rand(1,9);
			}
			if(empty($user_name)){
				$user_name  = 'wx_'.gmtime() . mt_rand(10,99);
			}
			$password  = mt_rand(1000, 9999);  //随机密码
			$other_data = array('portrait'=>$wx_info['headimgurl'],'real_name'=>$wx_info['nickname'],'gender'=>$wx_info['sex']);
			$user_id = $ms->user->register($user_name, $password, gmtime() . mt_rand(10,99).'@weixin.com',$other_data);
			if($user_id){
				$bind_data = array(
					'unionid' 	=> $$wx_info['unionid'],
					'openid'  	=> $openid,
					'user_id'	=> $user_id,
					'app'   	=> 'weixin',
					'nickname'	=> $wx_info['nickname'],
					'enabled' 	=> 1
				);
				if($bind){
					$this->bind_mod->edit($bind['id'],$bind_data);
				}else{
					$this->bind_mod->add($bind_data);
				}
				return $user_id;
			}
		}
	}
	
	function doLogin(){
		if($this->visitor->has_login){
			header('Location: index.php?app=member');
			exit;
		}
		$openid = isset($_GET['openid']) ? trim($_GET['openid']) : '';
		if(empty($openid)){
			header('Location: index.php?app=weixinconnect&act=login');
			exit;
		}
		$bind = $this->bind_mod->get(array('conditions'=>"openid='".$openid."' OR unionid='".$openid."' AND app='weixin'"));
		if($bind){
			if($bind['locked']){ //非第一次登陆
				header('Location: index.php?app=weixinconnect&act=login');
				exit;
			}else{
				$this->_do_login($bind['user_id']);
				$ms =& ms();
                $synlogin = $ms->user->synlogin($user_id);
				$this->bind_mod->edit($bind['id'],array('locked'=>1));
				header('Location: index.php?app=member');
				exit;
			}
		}else{
			header('Location: index.php?app=weixinconnect&act=login');
			exit;
		}
	}
	
	
}
?>