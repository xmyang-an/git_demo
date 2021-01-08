<?php

define('ROOT_PATH', dirname(dirname(dirname(dirname(__FILE__)))));

include(ROOT_PATH . '/eccore/mimall.php');
ecm_define(ROOT_PATH . '/data/config.inc.php');
//require(ROOT_PATH . '/includes/ecapp.base.php');

// 此参数在ECAPP中定义，因没有引入，所以在此定义，如果不用到模型（model），可以不定义
if(!defined('CHARSET')) {
	define('CHARSET', substr(LANG, 3));
}

require(ROOT_PATH . '/eccore/model/model.base.php');   //模型基础类

//存储微信的回调
$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
	
$params = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

// 外部交易号
$payTradeNo = trim($params['out_trade_no']);

if($payTradeNo) 
{
	$sendNotify = FALSE;
	$deposit_trade_mod = &m('deposit_trade');
	
	// 检索出最后支付的单纯充值或购物（或购买应用）订单，如果最后一笔是支付成功的，那么认为都是支付成功了
	$tradeInfo = $deposit_trade_mod->get(array(
		'conditions' => "payTradeNo='{$payTradeNo}'", 'fields' => 'status', 'order' => 'trade_id DESC'));
	
	if(empty($tradeInfo))
	{
		// 由于支付变更，通过商户交易号找不到对应的交易记录后，插入的资金退回记录
		$tradeInfo = $deposit_trade_mod->get(array(
			'conditions' => "tradeNo='{$payTradeNo}' AND status='SUCCESS' ", 'fields' => 'trade_id', 'order' => 'trade_id DESC'));
			
		if(empty($tradeInfo)) {
			$sendNotify = TRUE;
		}
	}
	elseif(in_array($tradeInfo['status'], array('PENDING', 'SUBMITTED'))) {
		$sendNotify = TRUE;
	}
	
	if($sendNotify === TRUE)
	{
		$url = SITE_URL . "/mobile/index.php?app=paynotify&act=notify&payTradeNo={$payTradeNo}";
		//$cacert_url = getcwd().'\\cacert.pem';
		
		// 输出处理结果给支付网关
		echo ecm_curl($url, 'POST', $params, $cacert_url);
	}
	else
	{
		// 可能不需要了
		echo 'SUCCESS';
	}
}
?>