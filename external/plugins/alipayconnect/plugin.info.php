<?php

return array(
    'id' => 'alipayconnect',
    'hook' => 'on_alipay_login',
    'name' => '支付宝快捷登录',
    'desc' => '支付宝快捷登录',
    'author' => 'MiMall',
    'version' => '2.0',
    'config' => array(
        'appId' => array(
            'type' => 'text',
            'text' => 'APPID'
        ),
        'rsaPublicKey' => array(
            'type' => 'text',
            'text' => '商户公钥'
        ),
		'rsaPrivateKey' => array(
			'type' => 'text',
            'text' => '商户私钥'
		),
		'alipayrsaPublicKey'   => array(
			'type'  => 'text',
            'text'  => '支付宝公钥',
            
        ),
		'signType'  => array(
			'type'      => 'select',
            'text'      => '签名类型',
            'items'     => array(
                'RSA2'   => 'RSA2',
				//'RSA'   => 'RSA',
            ),
        ),
    )
);

?>