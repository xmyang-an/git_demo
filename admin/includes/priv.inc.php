<?php

/**
 * 网站后台管理左侧菜单数据
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}

$menu_data = array
(
    'mall_setting' => array
    (
        'default'     => 'default|all',//后台登录
        'setting'     => 'setting|all',//网站设置
        'region'       => 'region|all',//地区设置
        'payment'    => 'payment|all',//支付方式
        'theme'     => 'theme|all',//主题设置
        'mailtemplate'   => 'mailtemplate|all',//邮件模板
        'template'  => 'template|all',//模板编辑
		'channel_manage' => 'channel|all', // 频道页管理
    ),
    'goods_admin' => array
    (
        'gcategory'    => 'gcategory|all',//分类管理
        'brand' => 'brand|all',//品牌管理
        'goods'    => 'goods|all',//商品管理
        'recommend'    => 'recommend|all',//推荐类型
		'props_manage'  => 'props|all', // 属性管理
		'report_manage' => 'report|all',
		'delivery_manage' => 'delivery|all',
    ),
    'store_admin' => array
    (
        'sgrade'    => 'sgrade|all',//店铺等级
        'scategory'     => 'scategory|all',//店铺分类
		'ultimate_store' => 'ultimate_store|all', // 旗舰店管理
        'store'   => 'store|all',//店铺管理
    ),
    'member' => array
    (
        'user'  => 'user|all',//会员管理
        'admin' => 'admin|all',//管理员管理
        'notice' => 'notice|all',//会员通知
		'deposit_manage' => 'deposit|all', //预存款管理
		'integral_manage' => 'integral|all', // 积分管理
    ),
    'order' => array
    (
        'order'   => 'order|all',//订单管理
		'order_stat'   => 'stat|all',
		'refund'  => 'refund|all', //退款管理
		'evaluation_manage'  => 'evaluation|all', //退款管理
    ),
    'website' => array
    (
        'acategory'    => 'acategory|all',//文章分类
        'article'      => array('article' => 'article|all', 'upload' => array('comupload' => 'comupload|all', 'swfupload' => 'swfupload|all')),//文章管理
        'partner'      => 'partner|all',//合作伙伴
        'navigation'   => 'navigation|all',//页面导航
        'db'           => 'db|all',//数据库
        /*'coupon'     => 'coupon|all',*/
        'consulting'   => 'consulting|all',//咨询
        'share_link'   => 'share|all',//分享管理
		'msg'	   	   => 'msg|all', // 短信管理
		'material_manage'	   => 'material|all', // 应用市场
		'wx_mini_manage'    => 'wx_mini|all',
		'appmarket'	   => 'appmarket|all', // 应用市场
		'webim'		   => 'webim|all', // 在线客服管理

    ),
    'weixinmall' => array
    (
        'wxsetting' => 'wxsetting|all',
        'wxreply'   => 'wxreply|all',
        'wxmenu'   => 'wxmenu|all',
    ),

    'external' => array
    (
        'plugin' => 'plugin|all',//插件管理
        'module'   => 'module|all',//模块管理
        'widget'   => 'widget|all',//挂件管理
		'merchant_manage' => 'merchant|all',// 商户管理
    ),
    'clear_cache' =>array
    (
        'clear_cache' => 'clear_cache|all',//清空缓存
    )
);
?>