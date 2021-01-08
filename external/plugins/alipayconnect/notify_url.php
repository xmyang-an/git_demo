<?php

define('ROOT_PATH', dirname(dirname(dirname(dirname(__FILE__)))));

include(ROOT_PATH . '/eccore/ecmall.php');
ecm_define(ROOT_PATH . '/data/config.inc.php');
require(ROOT_PATH . '/includes/ecapp.base.php');

// 此参数在ECAPP中定义，因没有引入，所以在此定义，如果不用到模型（model），可以不定义
if(!defined('CHARSET')) {
	define('CHARSET', substr(LANG, 3));
}

//require(ROOT_PATH . '/eccore/model/model.base.php');   //模型基础类

$auth_code = $_GET['auth_code'];
$state 	   = $_GET['state'];

$url = SITE_URL . "/index.php?app=alipayconnect&act=callback&auth_code=$auth_code&state=$state";
if(check_view_device('mobile', FALSE)) {
	$url = str_replace(SITE_URL, SITE_URL . "/mobile", $url);
}

header("location:{$url}");
		
?>