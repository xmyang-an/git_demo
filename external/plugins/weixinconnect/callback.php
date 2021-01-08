<?php

include('../../../eccore/mimall.php');

if(isWeixin()){
	$url = get_domain()  . '/mobile/index.php?app=weixinconnect&act=callback&code='.$_GET['code'].'&state='.$_GET['state'];
} else $url = get_domain() . '/index.php?app=weixinconnect&act=callback&code='.$_GET['code'].'&state='.$_GET['state'];

header('Location:'.$url);

?>