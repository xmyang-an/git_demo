<?php

/**
 * xwbconnect
 *
 */

class XwbconnectPlugin extends BasePlugin
{
    var $_config = array();
    
    function __construct($plugin_info)
    {
        $this->_config = $plugin_info;
    }
	function _config_info()
	{
		$data = array(
			'WB_AKEY'    		=> $this->_config['WB_AKEY'],
			'WB_SKEY'   		=> $this->_config['WB_SKEY'],
			
			// 说明：不要填 site_url() 原因：授权回调页需要填写到微博开放平台的后台配置项中，如果填写 site_url() 则导致PC和手机下显示不同的地址，会报：redirect_uri_mismatch
			// 或者解决方案二： 创建回调文件 callback.php，通过此文件转发
			'WB_CALLBACK_URL' 	=> SITE_URL . "/index.php?app=xwbconnect&act=callback",
		);
		return $data;	
	}	
}

?>