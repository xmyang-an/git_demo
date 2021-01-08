<?php

define('ROOT_PATH', dirname(dirname(dirname(dirname(__FILE__)))));

include(ROOT_PATH . '/eccore/mimall.php');
ecm_define(ROOT_PATH . '/data/config.inc.php');

// 外部交易号
$payTradeNo = html_script($_GET['out_trade_no']);

$url = SITE_URL . '/index.php?app=paynotify&payTradeNo='.$payTradeNo;
header('Location:'.$url);

?>