<?php

/**
 *    默认Rewrite引擎
 *
 *    @author    MiMall
 *    @usage    none
 */

class DefaultRewrite extends BaseRewrite
{
    /* Rewrite规则地图，记录参数对应的rule名称 */
    var $_rewrite_maps  = array(
        /* '{app名称}_{参数列表，按升序排序，"_"连接}' => '重写规则名称', */

        /* 店铺首页 */
        'store_id'  => 'store_index',

        /* 商品详情 */
        'goods_id'  => 'goods_detail',

        /* 商品分类 */
        'category'  => 'goods_cate',
		
		/* 商品搜索 */
		'search_cate_id'   => 'search_goods',
		'search_cate_id_page' => 'search_goods_page',
		
		/* 积分商城 */
		'integral'  => 'integral',
		'integral_list_page'  => 'integral_list_page',
		
		'limitbuy'  => 'limitbuy',
		'limitbuy_cate_id' => 'limitbuy_cate_id',
		'limitbuy_cate_id_page' => 'limitbuy_cate_id_page',

        /* 品牌列表 */
        'brand'     => 'brand_list',

        /* 店铺分类 */
        'category_act' => 'store_cate',

        /* 文章详情 */
        'article_act_id' => 'article_detail',
        'article_act_article_id' => 'article_detail',

        /* 店铺文章 */
        'store_act_id'  => REWRITE_RULE_FN,
        'store_act_id_page' => REWRITE_RULE_FN,
        'store_act_eval_id' => 'store_credit_eval',
        'store_act_eval_id_page'    => 'store_credit_eval_page',
        'store_act_cate_id_id'  => 'store_goodscate',
        'store_act_cate_id_id_page' => 'store_goodscate_page',
        'goods_act_id'      => 'goods_extra_info',
        'goods_act_id_page' => 'goods_extra_info_page',
		
		'seller_admin'  => 'seller_admin',
    );

    /* Rewrite rules，记录各规则信息 */
    var $_rewrite_rules = array(
        'store_index'   => array(
            'rewrite'   => 'store/%id%',
        ),
        'goods_detail'  => array(
            'rewrite'   => 'goods/%id%',
        ),
        'goods_cate'    => array(
            'rewrite'   => 'category/goods',
        ),
		'search_goods' => array(
			'rewrite'  => 'category-%cate_id%.html',
		),
		'search_goods_page' => array(
			'rewrite'  => 'category-%cate_id%-%page%.html',
		),
		
		/* 积分商城 */
		'integral'  => array(
			'rewrite' => 'integral.html',
		),
		'integral_list_page'  => array(
			'rewrite' => 'integral-list-%page%.html',
		),
		
		'limitbuy'  => array(
			'rewrite' => 'limitbuy.html',
		),
		'limitbuy_cate_id'  => array(
			'rewrite' => 'limitbuy-%cate_id%.html',
		),
		'limitbuy_cate_id_page'  => array(
			'rewrite' => 'limitbuy-%cate_id%-%page%.html',
		),
		
        'brand_list'    => array(
            'rewrite'   => 'brand',
        ),
        'store_cate'    => array(
            'rewrite'   => 'category/%act%',
        ),
        'article_detail'    => array(
            'rewrite'   => 'article/%article_id%.html',
        ),
        'store_article' => array(
            'rewrite'   => 'store/article/%id%.html',
        ),
        'store_credit'  => array(
            'rewrite'   => 'store/%id%/credit',
        ),
        'store_credit_page'  => array(
            'rewrite'   => 'store/%id%/credit/page_%page%',
        ),
        'store_credit_eval'  => array(
            'rewrite'   => 'store/%id%/credit/%eval%',
        ),
        'store_credit_eval_page'    => array(
            'rewrite'   => 'store/%id%/credit/%eval%/page_%page%',
        ),
		'store_categorylist'   => array(
            'rewrite'   => 'store/%id%/category/goods',
        ),
        'store_goodslist'   => array(
            'rewrite'   => 'store/%id%/goods',
        ),
        'store_goodslist_page'   => array(
            'rewrite'   => 'store/%id%/goods/page_%page%',
        ),
        'store_goodscate'   => array(
            'rewrite'   => 'store/%id%/category/%cate_id%',
        ),
        'store_goodscate_page'   => array(
            'rewrite'   => 'store/%id%/category/%cate_id%/page_%page%',
        ),
        'goods_extra_info' => array(
            'rewrite'   => 'goods/%id%/%act%',
        ),
        'goods_extra_info_page' => array(
            'rewrite'   => 'goods/%id%/%act%/page_%page%',
        ),
        'groupbuy_detail'   =>  array(
            'rewrite'   => 'groupbuy/%id%',
        ),
        'store_groupbuy'   =>  array(
            'rewrite'   => 'store/%id%/groupbuy',
        ),
        'store_groupbuy_page'   =>  array(
            'rewrite'   => 'store/%id%/groupbuy/page_%page%',
        ),
		
		'seller_admin'   =>  array(
            'rewrite'   => 'seller',
        ),
    );


    function rule_store_act_id($params)
    {
        $rule_name = '';
        switch ($params['act'])
        {
            case 'article':
                $rule_name = 'store_article';
            break;
            case 'credit':
                $rule_name = 'store_credit';
            break;
            case 'search':
                $rule_name = 'store_goodslist';
            break;
            case 'groupbuy':
                $rule_name = 'store_groupbuy';
            break;
        }

        return $rule_name;
    }

    function rule_store_act_id_page($params)
    {
        $rule_name = '';
        switch ($params['act'])
        {
            case 'credit':
                $rule_name = 'store_credit_page';
            break;
            case 'search':
                $rule_name = 'store_goodslist_page';
            break;
            case 'groupbuy':
                $rule_name = 'store_groupbuy_page';
            break;
        }

        return $rule_name;
    }
}

?>
