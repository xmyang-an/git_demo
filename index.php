<?php

define('ROOT_PATH', dirname(__FILE__));

/**
 * 安装判断
 */
if (!file_exists(ROOT_PATH . "/data/install.lock") && is_dir(ROOT_PATH . "/install")){
	@header("location: install");
	exit;
}

include_once(ROOT_PATH . '/eccore/mimall.php');

/* 定义配置信息 */
ecm_define(ROOT_PATH . '/data/config.inc.php');

/* 客户端判断 */
Psmb_init()->check_view_device();
/*if(Psmb_init()->check_view_device('',FALSE) == TRUE){
	$query_string = '';
	if(!empty($_SERVER['QUERY_STRING']))
	{
		$queryArray = explode('&', $_SERVER['QUERY_STRING']);
		foreach($queryArray as $key => $val)
		{
			if(in_array(strtolower($val), array('device=pc', 'device=wap'))) {
				unset($queryArray[$key]);
			}
		}
			$queryArray && $query_string = '?'. implode('&', $queryArray);
	}
			
	$redirect_uri = SITE_URL . "/mobile" ;
	if($query_string){
		$redirect_uri .= "/index.php" . $query_string;
	}
	
	header('Location:'.$redirect_uri);
	exit;
}
else{
	//跳转到其他的页面
}*/


MiMall::startup(array(
    'default_app'   =>  'default',
    'default_act'   =>  'index',
    'app_root'      =>  ROOT_PATH . '/app',
    'external_libs' =>  array(
        ROOT_PATH . '/includes/global.lib.php',
        ROOT_PATH . '/includes/libraries/time.lib.php',
        ROOT_PATH . '/includes/ecapp.base.php',
        ROOT_PATH . '/includes/plugin.base.php',
        ROOT_PATH . '/app/frontend.base.php',
        ROOT_PATH . '/includes/subdomain.inc.php',
    ),
));
?>