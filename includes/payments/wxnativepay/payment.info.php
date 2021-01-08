<?php

return array(
    'code'      => 'wxnativepay',
    'name'      => Lang::get('wxnativepay'),
    'desc'      => Lang::get('wxnativepay_desc'),
    'is_online' => '1',
    'author'    => 'MiMall',
    'website'   => '#',
    'version'   => '1.0',
    'currency'  => Lang::get('wxnativepay_currency'),
    'config'    => array(
        'AppID'   => array(        // 公众号开发者应用ID
            'text'  => Lang::get('appid'),
            'desc'  => Lang::get('appid_desc'),
            'type'  => 'text',
        ),
		'AppSecret'  => array(         // 公众号开发者应用密钥
            'text'      => Lang::get('appsecret'),
            'desc'  => Lang::get('appsecret_desc'),
            'type'      => 'text',
        ),
        'MchID'       => array(        //商户号
            'text'  => Lang::get('mchid'),
            'desc'  => Lang::get('mchid_desc'),
            'type'  => 'text',
        ),
        'KEY'   => array(        //商户密钥
            'text'  => Lang::get('key'),
			'desc'  => Lang::get('key_desc'),
            'type'  => 'text',
        ),
        
    ),
);

?>