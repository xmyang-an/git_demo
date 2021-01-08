<?php

include('../../../eccore/mimall.php');

if($_GET['display'] == 'mobile') {
	$url = get_domain() . '/mobile/index.php?app=qqconnect&act=callback&code='.$_GET['code'].'&state='.$_GET['state'];
} else $url = get_domain() . '/index.php?app=qqconnect&act=callback&code='.$_GET['code'].'&state='.$_GET['state'];

header('Location:'.$url);

?>