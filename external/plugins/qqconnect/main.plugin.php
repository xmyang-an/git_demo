<?php

/**
 * qqconnect
 *
 */

class QqconnectPlugin extends BasePlugin
{
    var $_config = array();
    
    function __construct($plugin_info)
    {
        $this->_config = $plugin_info;
    }
	
	function _config_info()
	{
		// 为了兼容PC及WAP端，不能用 site_url()
		$callback = defined('IN_MOBILE') ? urlencode(SITE_URL . "/external/plugins/qqconnect/callback.php?display=mobile") : urlencode(SITE_URL . "/external/plugins/qqconnect/callback.php");
		$data = array(
			'appid' 	=> $this->_config['appid'],
			'appkey'   	=> $this->_config['appkey'],
			'callback' 	=> $callback,
		);
		return $data;	
	}
}

?>