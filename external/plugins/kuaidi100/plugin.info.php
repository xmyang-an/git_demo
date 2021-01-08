<?php

return array(
    'id' => 'kuaidi100',
    'hook' => 'on_query_express',
    'name' => '快递跟踪',
    'desc' => '显示物流快递的配送进程',
    'author' => 'MiMall',
    'version' => '1.0',
    'config' => array(
	
		/* 兼容免费版/企业版 */
		'key' => array(
			'type' => 'text',
			'text' => 'key',
		),
		'customer' => array(
			'type' => 'text',
			'text' => 'customer',
		),
		
    )
);

?>