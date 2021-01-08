<?php

return array(
    'dashboard' => array(
        'text'      => Lang::get('dashboard'),
        'subtext'   => Lang::get('offen_used'),
        'default'   => 'welcome',
        'children'  => array(
            'welcome'   => array(
                'text'  => Lang::get('welcome_page'),
                'url'   => 'index.php?act=welcome',
				'ico'   => 'icon-huanyingye'
            ),
            'base_setting'  => array(
                'parent'=> 'setting',
                'text'  => Lang::get('base_setting'),
                'url'   => 'index.php?app=setting&act=base_setting',
				'ico'   => 'icon-shezhi2'
            ),
            'user_manage' => array(
                'text'  => Lang::get('user_manage'),
                'parent'=> 'user',
                'url'   => 'index.php?app=user',
				'ico'   => 'icon-kehuhuiyuanguanli'
            ),
            'store_manage'  => array(
                'text'  => Lang::get('store_manage'),
                'parent'=> 'store',
                'url'   => 'index.php?app=store',
				'ico'   => 'icon-dianpuguanli1'
            ),
            'goods_manage'  => array(
                'text'  => Lang::get('goods_manage'),
                'parent'=> 'goods',
                'url'   => 'index.php?app=goods',
				'ico'   => 'icon-shangpinguanli1'
            ),
            'order_manage' => array(
                'text'  => Lang::get('order_manage'),
                'parent'=> 'trade',
                'url'   => 'index.php?app=order',
				'ico'   => 'icon-icon-test'
            ),
        ),
    ),
    // 设置
    'setting'   => array(
        'text'      => Lang::get('setting'),
        'default'   => 'base_setting',
        'children'  => array(
            'base_setting'  => array(
                'text'  => Lang::get('base_setting'),
                'url'   => 'index.php?app=setting&act=base_setting',
				'ico'   => 'icon-wangluo_l'
            ),
            'region' => array(
                'text'  => Lang::get('region'),
                'url'   => 'index.php?app=region',
				'ico'   => 'icon-diqu'
            ),
            'payment' => array(
                'text'  => Lang::get('payment'),
                'url'   => 'index.php?app=payment',
				'ico'   => 'icon-zhifufangshi'
            ),
            'theme' => array(
                'text'  => Lang::get('theme'),
                'url'   => 'index.php?app=theme',
				'ico'   => 'icon-zhuti1'
            ),
            'template' => array(
                'text'  => Lang::get('template'),
                'url'   => 'index.php?app=template',
				'ico'   => 'icon-svgmoban'
            ),
            'mailtemplate' => array(
                'text'  => Lang::get('noticetemplate'),
                'url'   => 'index.php?app=mailtemplate',
				'ico'   => 'icon-tongzhi'
            ),
        ),
    ),
    // 商品
    'goods' => array(
        'text'      => Lang::get('goods'),
        'default'   => 'goods_manage',
        'children'  => array(
            'gcategory' => array(
                'text'  => Lang::get('gcategory'),
                'url'   => 'index.php?app=gcategory',
				'ico'   => 'icon-zhuti'
            ),
            'brand' => array(
                'text'  => Lang::get('brand'),
                'url'   => 'index.php?app=brand',
				'ico'   => 'icon-pinpaiguanli'
            ),
            'goods_manage' => array(
                'text'  => Lang::get('goods_manage'),
                'url'   => 'index.php?app=goods',
				'ico'   => 'icon-shangpin1'
            ),
			// psmb 
			'props_manage' => array(
			   'text' => Lang::get('props_manage'),
			   'url'  => 'index.php?app=props',
			   'ico'   => 'icon-shangpinshuxing'
			),
			// end			
            'recommend_type' => array(
                'text'  => LANG::get('recommend_type'),
                'url'   => 'index.php?app=recommend',
				'ico'   => 'icon-tuijian' 
            ),
			'report_manage' => array(
                'text'  => LANG::get('report_manage'),
                'url'   => 'index.php?app=report',
				'ico'   => 'icon-jubao' 
            ),
			'delivery_manage' => array(
                'text'  => LANG::get('delivery_manage'),
                'url'   => 'index.php?app=delivery&act=config',
				'ico'   => 'icon-wuliu1' 
            ),
        ),
    ),
    // 店铺
    'store'     => array(
        'text'      => Lang::get('store'),
        'default'   => 'store_manage',
        'children'  => array(
            'sgrade' => array(
                'text'  => Lang::get('sgrade'),
                'url'   => 'index.php?app=sgrade',
				'ico'   => 'icon-dengji'
            ),
            'scategory' => array(
                'text'  => Lang::get('scategory'),
                'url'   => 'index.php?app=scategory',
				'ico'   => 'icon-dianpu'
            ),
			//by cengnlaeng
			'ultimate_store'     =>array(
				'text'  => Lang::get('ultimate_store'),
                'url'   => 'index.php?app=ultimate_store',
				'ico'   => 'icon-tuijianshangjia'
			),
			//end
            'store_manage'  => array(
                'text'  => Lang::get('store_manage'),
                'url'   => 'index.php?app=store',
				'ico'   => 'icon-dianpushezhi'
            ),
			'store_statistic' => array(
                'text'  => Lang::get('store_statistic'),
                'url'   => 'index.php?app=store&act=statistic',
				'ico'   => 'icon-tongji2'
            ),
        ),
    ),
    // 会员
    'user' => array(
        'text'      => Lang::get('user'),
        'default'   => 'user_manage',
        'children'  => array(
            'user_manage' => array(
                'text'  => Lang::get('user_manage'),
                'url'   => 'index.php?app=user',
				'ico'   => 'icon-kehuhuiyuanguanli'
            ),
            'admin_manage' => array(
                'text' => Lang::get('admin_manage'),
                 'url'   => 'index.php?app=admin',
				 'ico'   => 'icon-guanliyuanguanli'
             ),
			 'user_statistic' => array(
                'text'  => Lang::get('user_statistic'),
                'url'   => 'index.php?app=user&act=statistic',
				'ico'   => 'icon-tongji2'
            ),
             'user_notice' => array(
                'text' => Lang::get('user_notice'),
                'url'  => 'index.php?app=notice',
				'ico'   => 'icon-tongzhi'
             ),
			 'deposit_manage' => array(
			 	'text' => Lang::get('deposit_manage'),
				'url'  => 'index.php?app=deposit',
				'ico'   => 'icon-qian'
			 ),
			 'user_integral'=> array(
			    'text' => Lang::get('integral_manage'),
				'url'  => 'index.php?app=integral',
				'ico'   => 'icon-jfdh'
			 ),
			 'cashcard' => array(
				'text' => Lang::get('cashcard_manage'),
                'url' => 'index.php?app=cashcard',
				'ico'   => 'icon-qia'
			),
        ),
    ),
    // 交易
    'trade' => array(
        'text'      => Lang::get('trade'),
        'default'   => 'order_manage',
        'children'  => array(
            'order_manage' => array(
                'text'  => Lang::get('order_manage'),
                'url'   => 'index.php?app=order',
				'ico'   => 'icon-icon-test'
            ),
	    	'order_stat' => array(
                'text'  => Lang::get('order_stat'),
                'url'   => 'index.php?app=stat',
				'ico'   => 'icon-tongji2'
            ),
			'refund_manage' => array(
				'text' => Lang::get('refund_manage'),
				'url'  => 'index.php?app=refund',
				'ico'   => 'icon-list_tuihuoshenqingshenhe'
			),
			'evaluation_manage' => array(
                'text'  => Lang::get('evaluation_manage'),
                'url'   => 'index.php?app=evaluation',
				'ico'   => 'icon-weibiaoti527'
            ),
            'xunjia_manage' => array(
                'text'  => Lang::get('xj_manage'),
                'url'   => 'index.php?app=xj',
				'ico'   => 'icon-zhuti1'
            ),
			
        ),
    ),
    // 网站
    'website' => array(
        'text'      => Lang::get('website'),
        'default'   => 'acategory',
        'children'  => array(
            'acategory' => array(
                'text'  => Lang::get('acategory'),
                'url'   => 'index.php?app=acategory',
				'ico'   => 'icon-fabuzhuanjiawenzhang'
            ),
            'article' => array(
                'text'  => Lang::get('article'),
                'url'   => 'index.php?app=article',
				'ico'   => 'icon-guanlizhuanjiawenzhang'
            ),
            'partner' => array(
                'text'  => Lang::get('partner'),
                'url'   => 'index.php?app=partner',
				'ico'   => 'icon-webicon306'
            ),
            'navigation' => array(
                'text'  => Lang::get('navigation'),
                'url'   => 'index.php?app=navigation',
				'ico'   => 'icon-daohang01'
            ),
            'db' => array(
                'text'  => Lang::get('db'),
                'url'   => 'index.php?app=db&amp;act=backup',
				'ico'   => 'icon-yidongyunkongzhitaiicon42'
            ),
           /* 'coupon' => array(
                'text' => Lang::get('coupon'),
                'url'  => 'index.php?app=coupon',
				'ico'  => 'icon-UY-youhuiquan'
            ),*/
            'consulting' => array(
                'text'  =>  LANG::get('consulting'),
                'url'   => 'index.php?app=consulting',
				'ico'   => 'icon-shouye'
            ),
            'msg' => array(
                'text'  =>  LANG::get('msg'),
                'url'   => 'index.php?app=msg',
				'ico'   => 'icon-shoujiduanxintixing'
            ),
			'appmarket' => array(
				'text'	=> LANG::get('appmarket'),
				'url'	=> 'index.php?app=appmarket',
				'ico'   => 'icon-yingyongshichangyouhuiquanyi'
			),
			'webim'   => array(
				'text'  => Lang::get('webim'),
				'url' => 'index.php?app=webim',
				'ico'   => 'icon-kefu1'
			),
			'material' => array(
				'text'	=> LANG::get('material_manage'),
				'url'	=> 'index.php?app=material',
				'ico'   => 'icon-yingyongshichangyouhuiquanyi'
			),
			'wx_mini' => array(
				'text'	=> LANG::get('wx_mini_manage'),
				'url'	=> 'index.php?app=wx_mini',
				'ico'   => 'icon-yingyongshichangyouhuiquanyi'
			),
        ),
    ),    
    // 微商城
    'weixinmall' => array(
        'text'      => Lang::get('weixinmall'),
        'default'   => 'wxsetting',
        'children'  => array(
			'theme' => array(
                'text'  => Lang::get('theme'),
                'url'   => 'index.php?app=theme&type=mobile',
				'ico'   => 'icon-zhuti1'
            ),
            'template' => array(
                'text'  => Lang::get('template'),
                'url'   => 'index.php?app=template&client=m',
				'ico'   => 'icon-svgmoban'
            ),
            'wxsetting' => array(
                'text'  => Lang::get('wxsetting'),
                'url'   => 'index.php?app=wxsetting',
				'ico'   => 'icon-weixin'
            ),
			'wxreply' => array(
                'text'  => Lang::get('wxreply'),
                'url'   => 'index.php?app=wxreply',
				'ico'   => 'icon-weibiaoti527'
            ),
            'wxmenu' => array(
                'text'  => Lang::get('wxmenu'),
                'url'   => 'index.php?app=wxmenu',
				'ico'   => 'icon-zidingyibaobiao'
            ),
        ),
    ),
    // 扩展
    'extend' => array(
        'text'      => Lang::get('extend'),
        'default'   => 'plugin',
        'children'  => array(
            'plugin' => array(
                'text'  => Lang::get('plugin'),
                'url'   => 'index.php?app=plugin',
				'ico'   => 'icon-charuxing'
            ),
            'module' => array(
                'text'  => Lang::get('module'),
                'url'   => 'index.php?app=module&act=manage',
				'ico'   => 'icon-svgmoban'
            ),
            'widget' => array(
                'text'  => Lang::get('widget'),
                'url'   => 'index.php?app=widget',
				'ico'   => 'icon-mokuai'
            ),
			'merchant' => array(
				'text' => Lang::get('merchant_manage'),
				'url' => 'index.php?app=merchant',
				'ico'   => 'icon-dianpushezhi'
			),
        ),
    ),
    
);

?>
