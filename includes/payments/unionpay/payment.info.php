<?php

return array(
    'code'      => 'unionpay',
    'name'      => Lang::get('unionpay'),
    'desc'      => Lang::get('unionpay_desc'),
    'is_online' => '1',
    'author'    => 'MiMall',
    'website'   => 'http://www.unionpay.com',
    'version'   => '1.0',
    'currency'  => Lang::get('unionpay_currency'),
    'config'    => array(
        'merId'   => array(
            'text'  => Lang::get('merId'),
            'desc'  => Lang::get('merId_desc'),
            'type'  => 'text',
        ), 
    ),
);

?>