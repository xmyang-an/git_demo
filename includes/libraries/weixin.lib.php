<?php

class Weixin extends Object
{
	var $_config;
	var $_config_mod;
	var $weixin_reply_mod;
	
	function __construct()
	{
		 $this->Weixin();
	}
	
    function Weixin()
    {
		$this->weixin_reply_mod = & m('weixin_reply');
		$this->_config_mod = & m('weixin_config');
		$this->_config = $this->_config_mod->get(array(
			'conditions' => 'user_id=0'
		));
    }
	
	/**
     *  微信配置验证
     *  @author mimall
     *  @return void
     */
	function valid()
    {
		if(!$this->_config['if_valid']){
			$echoStr = $_GET["echostr"];
			if($this->checkSignature()){
				$this->_config_mod->edit($this->_config['id'],array('if_valid'=>1));
				echo $echoStr;
				exit;
			}
		}
		return true;
    }
	
	function checkSignature()
	{
		$token = $this->_config['token'];
        if (!$token) {
            throw new Exception('TOKEN is not defined!');
        }
        
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
	
	/**
     *  生成自定义菜单
     *  @author mimall
	 *  @param  array $data
     *  @return obj
     */
	function createMenus($data)
	{
		$api = $this->apiList('weixinMenus');
		$param = array('access_token' => $this->getAccessToken());
		$url = $this->combineUrl($api,$param);
		$result = ecm_curl($url,'POST',$data);
		return json_decode($result);	
	}
	
	/**
     *  获取access_token
     *  @author mimall
     *  @return string
     */
	function getAccessToken()
	{
		
		$api = $this->apiList('AccessToken');
		$param = array('appid' => $this->_config['appid'], 'secret' => $this->_config['appsecret']);

		$url = $this->combineUrl($api,$param);
		$getData = json_decode(ecm_curl($url));

		return $getData->access_token;

	}
	
	function apiList($api)
	{
		$list = array(
			'AccessToken' 	=> 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential',
			'weixinMenus' 	=> 'https://api.weixin.qq.com/cgi-bin/menu/create?',
			'userInfo'	  	=> 'https://api.weixin.qq.com/cgi-bin/user/info?',
			'createQrcode' 	=> 'https://api.weixin.qq.com/cgi-bin/qrcode/create?',
			'showQrcode' 	=> 'https://mp.weixin.qq.com/cgi-bin/showqrcode?',
		);
		
		return $list[$api];
	}
	
	function combineUrl($url,$param)
	{
		$newParam = array('url'=>$url);
		
		if(!empty($param))
		{
			foreach($param as $key=>$val)
			{
				$newParam[] = $key.'='.$val;
			}
		}

		return  implode('&',$newParam);
	}
	
	/**
     *  获取用户向公众平台发送的信息
     *  @author mimall
     *  @return array
     */
	function getPostData()
	{
		$xml = $GLOBALS["HTTP_RAW_POST_DATA"];
		libxml_disable_entity_loader(true);	
		$data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
		return $data;
	}
	
	/**
     *  获取文本消息和图文消息XML模板
     *  @author mimall
	 *  @param  string $ToUserName
	 *  @param  string $FromUserName
     *  @param  string or array $param
     *  @return xml
     */
    function getMsgXML($ToUserName, $FromUserName, $param) 
	{
		if(empty($param)){
			return false;
		}
		// $param 必须设定为二维数组
		if(is_array($param)){
			$resultStr = "<xml>
						 <ToUserName><![CDATA[" . $ToUserName . "]]></ToUserName>
						 <FromUserName><![CDATA[" . $FromUserName . "]]></FromUserName>
						 <CreateTime>" . gmtime() . "</CreateTime>
						 <MsgType><![CDATA[news]]></MsgType>
						 <ArticleCount>" . count($param) . "</ArticleCount>
						 <Articles>";
			foreach ($param as $key => $val) 
			{
				$resultStr .= "<item>
							   <Title><![CDATA[" . $val['title'] . "]]></Title> 
							   <Description><![CDATA[" . $val['content'] . "]]></Description>
							   <PicUrl><![CDATA[" . SITE_URL . '/' . $val['image'] . "]]></PicUrl>
							   <Url><![CDATA[" . $val['link'] . "]]></Url>
							   </item>";
			}
			$resultStr .= "</Articles></xml>";
		}else{
			$tpl = "<xml>
			  <ToUserName><![CDATA[%s]]></ToUserName>
			  <FromUserName><![CDATA[%s]]></FromUserName>
			  <CreateTime>%s</CreateTime>
			  <MsgType><![CDATA[text]]></MsgType>
			  <Content><![CDATA[%s]]></Content>
			  </xml>"; 
			$resultStr = sprintf($tpl, $ToUserName, $FromUserName, gmtime(), $param);
		}
		
        return $resultStr;
    }
	
	/**
     *  获取生成的二维码 带scene_id
     *  @author mimall
     *  @param  int $scene_id
     */
    function getQrcode($scene_id) 
	{
        $refer_qrcode = $QR = ROOT_PATH . '/data/files/mall/phpqrcode/qrcode_' . $scene_id . '.jpg';
        if (!file_exists($QR)) {
            $access_token = $this->getAccessToken();
            $post_data = json_encode(array('action_name' => 'QR_LIMIT_SCENE', 'action_info' => array('scene' => array('scene_id' => $scene_id))));
            $api = $this->apiList('createQrcode');
			$param = array('access_token' => $access_token);
			$url = $this->combineUrl($api,$param);
            $res = ecm_curl($url, 'post', $post_data);
			$res = json_decode($res,true);
            if ($res['ticket']) {
				$showApi = $this->apiList('showQrcode');
				$ticket = array('ticket' => urlencode($res['ticket'])); 
				$geturl = $this->combineUrl($showApi,$ticket);
				$imageinfo = ecm_curl($geturl);
				$local_file = fopen($QR, 'a');
                if (false !== $local_file) {
                    if (false !== fwrite($local_file, $imageinfo)) {
                        fclose($local_file);
                    }
                }
            } else {
                return '获取二维码失败';
            }
        }
        return str_replace(ROOT_PATH, SITE_URL, $refer_qrcode);
    }
	
	/**
     *  获取微信用户信息
     *  @author mimall
     *  @param  string $FromUserName
     *  @return array
     */
	function getUserInfo($FromUserName)
	{
		if(empty($FromUserName))
		{
			return false;
		}
		$api = $this->apiList('userInfo');
		$param = array('access_token' => $this->getAccessToken(),'openid'=>$FromUserName,'lang'=>'zh_CN');
		$url = $this->combineUrl($api,$param);
		$result = ecm_curl($url);
		return json_decode($result, true);
	}
}

?>