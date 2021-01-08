<?php

/**
 * alipayconnect
 *
 */

class AlipayconnectPlugin extends BasePlugin
{
	var $_config = array();
    
    function __construct($plugin_info)
    {
        $this->_config = $plugin_info;
    }
	function _config_info()
	{
		$data = array(
			'appId'					=> $this->_config['appId'],
			'rsaPublicKey'			=> $this->_config['rsaPublicKey'],
			'rsaPrivateKey' 		=> $this->_config['rsaPrivateKey'],
			'alipayrsaPublicKey'	=> $this->_config['alipayrsaPublicKey'],
			'signType'				=> $this->_config['signType'],
			'scope'					=> 'auth_user', //auth_base',
			'redirect_uri'			=> urlencode(site_url() . "/index.php?app=alipayconnect&act=callback"),//urlencode(site_url() . "/external/plugins/alipayconnect/notify_url.php"),
			'gatewayUrl'			=> 'https://openapi.alipay.com/gateway.do',
		);
		return $data;	
	}	
}

?>