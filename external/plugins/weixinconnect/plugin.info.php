<?php

return array(
    'id' => 'weixinconnect',
    'hook' => 'on_weixin_login',
    'name' => '微信登录',
    'desc' => '微信登录',
    'author' => 'MiMall',
    'version' => '2.0',
    'config' => array(
        'AppId' => array(
            'type' => 'text',
            'text' => 'AppId'
        ),
        'AppSecret' => array(
            'type' => 'text',
            'text' => 'AppSecret'
        )
    )
);

?>