<?php

class DefaultApp extends MallbaseApp
{
    function index()
    {
        $this->assign('index', 1); // 标识当前页面是首页，用于设置导航状态
		
		// 微信分享
		import('jssdk.lib');
		$jssdk = new JSSDK(Conf::get('weixinkey.AppID'), Conf::get('weixinkey.AppSecret')); // 微信公众号的
		$signPackage = $jssdk->GetSignPackage();
		$this->assign('signPackage', json_encode($signPackage));
		$this->import_resource(array('script' => 'mobile/weixin/jweixin-1.0.0.js,mobile/weixin/share.js, mobile/jquery.plugins/jquery.infinite.js'));
		
		$this->_config_seo(array('title' => Conf::get('site_title')));
		$this->_get_curlocal_title(conf::get('site_title'));
        $this->display('index.html');
    }
	
	// 首页猜你喜欢
	function getInfinite()
	{
		$cate_id = intval($_GET['cate_id']) ? intval($_GET['cate_id']) : '';
		$recom_id = intval($_GET['recom_id']) ? intval($_GET['recom_id']) : -100;
		$sort_by = html_script($_GET['order']);
		
		if(empty($sort_by)){
			$order = ' g.add_time DESC ';
		}elseif(in_array($sort_by,array('views','collects','comments','sales'))){
			$order = ' goodsstatistics.'.$sort_by.' DESC,g.add_time DESC ';
		}elseif($sort_by=='add_time'){
			$order = ' g.add_time DESC ';
		}
		
		$recom_mod = &m('recommend');
		$goods_mod = &m('goods');
		
		$page = $this->_get_page(intval($_GET['pageper']) ? intval($_GET['pageper']) : 10);
		
		$conditions = "g.if_show = 1 AND g.closed = 0 AND s.state = 1 ";
		$goodsList = array();
		
		if($recom_id == -100)
		{
            if ($cate_id > 0)
            {
                $gcategory_mod =& m('gcategory');
                $conditions .= " AND g.cate_id " . db_create_in($gcategory_mod->get_descendant($cate_id));
            }
		}
		else
		{
			if ($recom_id > 0)
            {
                $conditions .= " AND recommended_goods.recom_id= ".$recom_id;
            }
		}
		
		$goodsList = $goods_mod->find(array(
			'conditions'=> $conditions,
			'fields' 	=> 'goods_name, g.goods_id, price, default_image',
			'join'      => 'has_goodsstatistics,be_recommend,belongs_to_store',
			'limit'   	=> $page['limit'],
			'order'		=> $order,
			'count'   	=> true			
		));	
			
		$page['item_count'] = $goods_mod->getCount();
		$this->_format_page($page);
		
		// 必须加 array_values() js遍历顺序才对
		$data = array('result' => array_values($goodsList), 'totalPage' => $page['page_count']);
		echo json_encode($data);
	}
	
	// 显示微分销分享二维码
	function code()
	{
		$did = isset($_GET['did']) ? intval($_GET['did']) : 0;
		$distribution_mod = &m('distribution');
		$distribution = $distribution_mod->get('did='.$did);
		if(!$did || !$distribution)
		{
			$this->show_warning('error');
            return;
		}
		$store_mod = &m('store');
		$store = $store_mod->get(array('conditions'=>$distribution['store_id'],'fields'=>'store_name'));
		$distribution['store_name'] = $store['store_name'];
		if(empty($distribution['logo']))
		{
			$member_mod = &m('member');	
			$member = $member_mod->get($distribution['user_id']);
			$distribution['user_name'] = $member['user_name'];
			$distribution['logo'] = portrait($distribution['user_id'], $member['portrait'], 'middle');
		}
		$distribution['code_img'] = parent::gendtbQRCode('dtb_qrcode',array('store_id'=>$distribution['store_id'],'did'=>$did));
		$this->assign('distribution',$distribution);
		$this->_get_curlocal_title('my_code');
		$this->_config_seo('title', Lang::get('distribution_center') . ' - ' . Lang::get('my_code'));
		$this->display('dcenter.code.html');
	}
}

?>