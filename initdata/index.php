<?php

/* 应用根目录 */
define('APP_ROOT', dirname(__FILE__));
define('ROOT_PATH', dirname(APP_ROOT));
include(ROOT_PATH . '/eccore/mimall.php');

/* 定义配置信息 */
ecm_define(ROOT_PATH . '/data/config.inc.php');

MiMall::startup(array(
    'default_app'   =>  'default',
    'default_act'   =>  'index',
    'app_root'      =>  APP_ROOT . '/app',
    'external_libs' =>  array(
        ROOT_PATH . '/includes/global.lib.php',
        ROOT_PATH . '/includes/libraries/time.lib.php',
    ),
));

?>