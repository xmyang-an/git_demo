<?php

/**
 * weixinconnect
 *
 */

class WeixinconnectPlugin extends BasePlugin
{
    var $_config = array();
    
    function __construct($plugin_info)
    {
        $this->WeixinconnectPlugin($plugin_info);
    }
    function WeixinconnectPlugin($plugin_info)
    {
        $this->_config = $plugin_info;
    }
	
	function _config_info()
    {
		if(defined('IN_BACKEND') && IN_BACKEND === true) // 后台无需执行
		{
			return;
		}
		else
		{
			$data = array(
				'gateway'       => 'https://open.weixin.qq.com/connect/qrconnect?',
				'AppId'			=> $this->_config['AppId'],
				'AppSecret'		=> $this->_config['AppSecret'],
				'redirect_uri'	=> urlencode(SITE_URL . "/external/plugins/weixinconnect/callback.php"),
				'response_type' => 'code',
				'scope' 		=> 'snsapi_login',
				'state' 		=> mt_rand()
			);
			
			if($this->isWeixin())
			{
				$data['gateway'] = 'https://open.weixin.qq.com/connect/oauth2/authorize?';
				$data['AppId']	 = Conf::get('weixinkey.AppID');
				$data['AppSecret'] = Conf::get('weixinkey.AppSecret');
				$data['scope'] 		= 'snsapi_userinfo';
			}
			
			return $data;
		}  
    }
	function isWeixin()
	{
		/* JSMethod
		var ua = navigator.userAgent.toLowerCase();
		if(ua.match(/MicroMessenger/i)=="micromessenger") {
			return true;
 		} else {
			return false;
		}
		*/
		
		if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
			return true;
		}	
		return false;
	}		
}

?>