<?php

return array(
    'code'      => 'tenpay',
    'name'      => Lang::get('tenpay'),
    'desc'      => Lang::get('tenpay_desc'),
    'is_online' => '1',
    'author'    => 'MiMall TEAM',
    'website'   => 'http://www.tenpay.com',
    'version'   => '1.0',
    'currency'  => Lang::get('tenpay_currency'),
    'config'    => array(
        'partner'   => array(        //账号
            'text'  => Lang::get('partner'),
            'desc'  => Lang::get('partner_desc'),
            'type'  => 'text',
        ),
        'key'       => array(        //密钥
            'text'  => Lang::get('key'),
            'type'  => 'text',
        ),
    ),
);

?>