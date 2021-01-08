<?php

return array(
    'code'      => 'alipay_mobile',
    'name'      => Lang::get('alipay_mobile'),
    'desc'      => Lang::get('alipay_desc'),
    'is_online' => '1',
    'author'    => 'MiMall',
    'website'   => 'http://www.mimall.com',
    'version'   => '1.0',
    'currency'  => Lang::get('alipay_currency'),
    'config'    => array(
        'appId'   => array(        //APPID
            'text'  => Lang::get('appId'),
            'desc'  => Lang::get('appId_desc'),
            'type'  => 'text',
        ),
		'rsaPublicKey'       => array(        // 应用公钥
            'text'  => Lang::get('rsaPublicKey'),
            'desc'  => Lang::get('rsaPublicKey_desc'),
            'type'  => 'text',
        ),
        'rsaPrivateKey'       => array(        // 应用私钥
            'text'  => Lang::get('rsaPrivateKey'),
            'desc'  => Lang::get('rsaPrivateKey_desc'),
            'type'  => 'text',
        ),
        'alipayrsaPublicKey'   => array(        // 支付宝公钥
            'text'  => Lang::get('alipayrsaPublicKey'),
			'desc'  => Lang::get('alipayrsaPublicKey_desc'),
            'type'  => 'text',
        ),
		'signType'  => array(         // 签名类型
            'text'      => Lang::get('signType'),
            'type'      => 'select',
            'items'     => array(
                'RSA2'   => Lang::get('signType_RSA2'),
				'RSA'   => Lang::get('signType_RSA'),
            ),
        ),
    ),
);

?>