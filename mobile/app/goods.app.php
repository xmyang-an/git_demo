<?php

/* 商品 */
class GoodsApp extends StorebaseApp
{
    var $_goods_mod;
    function __construct()
    {
        $this->GoodsApp();
    }
    function GoodsApp()
    {
        parent::__construct();
        $this->_goods_mod =& m('goods');
    }

    function index()
    {
        /* 参数 id */
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }

        /* 可缓存数据 */
        $data = $this->_get_common_info($id);
        if ($data === false)
        {
            return;
        }
        else
        {
            $this->_assign_common_info($data);
        }

        /* 更新浏览次数 */
        $this->_update_views($id);
		
		/* 在详情页显示前3条商品评价 */
		$this->assign('goods_comments', $this->_getLastComments($id, 3));
		
		/* 在详情页显示前3条商品问答 */
		$this->assign('goods_qas', $this->_getLastQas($id, 3));
		
		//在详情页显示买家印象
		$this->_get_comment_tips($data['goods']);
		
		$statistics['total_count'] = $total_count = $this->count_eval($id);
		$statistics['bad_count'] = $bad_count = $this->count_eval($id,1);
		$statistics['middle_count'] = $middle_count = $this->count_eval($id,2);
		$statistics['good_count'] = $goods_count = $this->count_eval($id,3);
		$statistics['share_count'] = $this->count_eval($id,4);

		$this->assign('statistics', $statistics);
		
		/*是否开启积分功能*/
		$integral_mod = &m('integral');
		if($integral_mod->_get_sys_setting('integral_enabled'))
		{
			$this->assign('integral_enabled', 1);
			
			// 购买商品可获得多少积分
			$integralRadio = $integral_mod->_get_sys_setting(array('buying_integral', $data['store_data']['sgrade']));
			
			if($integralRadio > 0 && $integralRadio <=1) {
				$this->assign('buyIntegral', array('radio' => $integralRadio, 'price' => round($data['goods']['price'] * $integralRadio, 2)));
			}
		}
		$this->assign('props', $this->_get_goods_props($id));
		
		//是否开启验证码
        if (Conf::get('captcha_status.goodsqa'))
        {
            $this->assign('captcha', 1);
        }
        $this->assign('guest_comment_enable', Conf::get('guest_comment'));
		
		
		// 微信分享
		import('jssdk.lib');
		$jssdk = new JSSDK(Conf::get('weixinkey.AppID'), Conf::get('weixinkey.AppSecret')); // 微信公众号的
		$signPackage = $jssdk->GetSignPackage();
		$this->assign('signPackage', json_encode($signPackage));

		$this->import_resource(array(
				'script' => array(
					array(
						'path' => 'mobile/jquery.plugins/jquery.form.min.js',
						'attr' => '',
					),
					array(
						'path' => 'mobile/dialog/dialog.js',
						'attr' => 'id="dialog_js"',
					),
					array(
						'path' => 'mobile/jquery.ui/jquery.ui.js',
						'attr' => '',
					),
					array(
						'path' => 'mobile/weixin/jweixin-1.0.0.js',
						'attr' => '',
					),
					array(
						'path' => 'mobile/weixin/share.js',
						'attr' => '',
					),
					array(
						'path' => 'mobile/goodsinfo.js',
						'attr' => '',
					),
					array(
						'path' => 'layui/layui.js',
						'attr' => '',
					),
					array(
						'path' => 'layui/webim.mobile.js',
						'attr' => '',
					),

				),
				'style' => 'layui/css/layui.mobile.css',
		));
		
		/* 配置seo信息 */
        $this->_config_seo(array('title' => $data['goods']['goods_name'] . ' - ' . Conf::get('site_title')));
		$this->_get_curlocal_title('goods_detail');
        $this->display('goods.index.html');
    }
	
	function _get_comment_tips($goods_info)
	{
		$gcategory_mod = &bm('gcategory');
		$cate_ids = $gcategory_mod->get_parents($goods_info['cate_id_1']);
		foreach($cate_ids as $val){
			$tpl = $gcategory_mod->get($val);
			if(!empty($tpl['eval_tips'])){
				$eval_tips = explode(',',$tpl['eval_tips']);
				break;
			}
		}
		$ordergoods_mod = &m('ordergoods');
		if(!empty($eval_tips)){
			foreach($eval_tips as $key=>$tip){
				$tips[$key]['tip'] = $tip;
				$tips[$key]['count'] = $ordergoods_mod->getOne("SELECT COUNT(*) AS count FROM {$ordergoods_mod->table} WHERE goods_id=".$goods_info['goods_id']." AND tips like '%{$tip}%'");
			}
		}

		$this->assign('eval_tips',$tips);
	}
	
	function _get_goods_props($id=0)
	{
		$goods_pvs_mod = &m('goods_pvs');
		$props_mod = &m('props');
		$prop_value_mod = &m('prop_value');
		$goods_pvs = $goods_pvs_mod->get($id);// 取出该商品的属性字符串
		$goods_pvs_str = $goods_pvs['pvs'];
		$props = array();
		if(!empty($goods_pvs_str))
		{
			$goods_pvs_arr = explode(';',$goods_pvs_str);//  分割成数组
			foreach($goods_pvs_arr as $pv)
			{
				if($pv)
				{
					$pv_arr = explode(':',$pv);
					$prop = $props_mod->get(array('conditions'=>'pid='.$pv_arr[0].' AND status=1'));
					if($prop)
					{
						$prop_value = $prop_value_mod->get(array('conditions'=>'pid='.$pv_arr[0].' AND vid='.$pv_arr[1].' AND status=1'));
						if($prop_value){
							if(isset($props[$pv_arr[0]])){
								$props[$pv_arr[0]]['value'] .= '，'.$prop_value['prop_value'];
							}
							else {
								$props[$pv_arr[0]] = array('name'=>$prop['name'],'value'=>$prop_value['prop_value']);
							}
						}
					}
				}
			}
		}
		return $props;		
	}
	
    /* 商品评论 */
    function comments()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
		
		if(!IS_AJAX)
		{
			$this->import_resource(array(
				'script' => array(
					array(
						'path' => 'mobile/jquery.plugins/jquery.infinite.js',
						'attr' => '',
					),
					array(
						'path' => 'mobile/search_goods.js',
						'attr' => '',
					),
					array(
						'path' => 'mobile/photoswipe/photoswipe.min.js',
						'attr' => '',
					),
					array(
						'path' => 'mobile/photoswipe/photoswipe-ui-default.min.js',
						'attr' => '',
					),
					array(
						'path' => 'mobile/photoswipe/photoswipe.init.js',
						'attr' => '',
					)
				),
				'style' =>  'mobile/photoswipe/css/photoswipe.css,mobile/photoswipe/css/default-skin/default-skin.css',
			));
		
			$this->assign('infiniteParams', json_encode($_GET));
			
			$this->_get_comment_tips($this->_goods_mod->get($id));
			
			$statistics['total_count'] = $total_count = $this->count_eval($id);
			$statistics['bad_count'] = $bad_count = $this->count_eval($id,1);
			$statistics['middle_count'] = $middle_count = $this->count_eval($id,2);
			$statistics['good_count'] = $goods_count = $this->count_eval($id,3);
			$statistics['share_count'] = $this->count_eval($id,4);

			$this->assign('statistics', $statistics);
			
			$this->_config_seo('title', Lang::get('goods_comment') . ' - ' . Conf::get('site_title'));
			$this->_get_curlocal_title('goods_comment');
        	$this->display('goods.comments.html');
		}
		else
		{
			$data = array();
			if(isset($_GET['eval']) && in_array($_GET['eval'],array(1,2,3)))
			{
				$conditions .= " AND evaluation =".intval($_GET['eval']);
			}
			elseif($_GET['eval'] == 4)
			{
				$conditions .= " AND share_images != ''";
			}
			
			$tip = html_script($_GET['tip']);
			if(!empty($tip))
			{
				$conditions .= ' AND FIND_IN_SET("'.$tip.'", tips)';
			}
		
			$page = $this->_get_page(intval($_GET['pageper']));
			$ordergoods_mod =& m('ordergoods');
			$comments = $ordergoods_mod->find(array(
				'conditions' => "goods_id = '$id' AND evaluation_status = '1'".$conditions,
				'join'  => 'belongs_to_order',
				'fields'=> 'buyer_id, buyer_name, anonymous, evaluation_time, comment, evaluation,share_images,reply_content',
				'count' => true,
				'order' => 'evaluation_time desc',
				'limit' => $page['limit'],
			));
			if($comments)
			{
				$member_mod = &m('member');
				foreach($comments as $key => $comment)
				{
					$share_images = unserialize($comment['share_images']);
					$comments[$key]['images'] = array();
					if(!empty($share_images))
					{
						foreach($share_images as $k=>$image)
						{
							$file = ROOT_PATH.'/'.$image;
							if(file_exists($file))
							{
								$image_info = getimagesize($file);
								$comments[$key]['images'][$k]['url'] = $image;
								$comments[$key]['images'][$k]['data_size'] = implode('x', array($image_info[0],$image_info[1]));
							}
						}
					}
					$member = $member_mod->get(array(
						'conditions'	=> 'user_id='.$comment['buyer_id'],
						'fields'		=> 'portrait',
					));
					empty($member['portrait']) && $member['portrait'] = Conf::get('default_user_portrait');
					
					$comments[$key]['portrait'] = $member['portrait'];
					$comments[$key]['buyer_name'] = cut_str($comment['buyer_name']);
					
					$comments[$key]['evaluation_time'] = local_date('Y-m-d', $comment['evaluation_time']);
				}
			}

			$page['item_count'] = $ordergoods_mod->getCount();
			$this->_format_page($page);
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($comments), 'totalPage' => $page['page_count']);
			echo json_encode($data);
		}
    }
	
	function count_eval($goods_id,$eval = '')
	{
		$order_goods_mod =& m('ordergoods');
		if(in_array($eval,array(1,2,3)) || !$eval)
		{
			if(in_array($eval,array(1,2,3)))
			{
				$condition=" AND evaluation =".$eval;
			}
			$count=count($order_goods_mod->find(array('conditions' => "goods_id = '$goods_id' AND evaluation_status = '1' ".$condition,'join'  => 'belongs_to_order')));
		}
		elseif($eval == 4)
		{
			$count = count($order_goods_mod->find(array('conditions' => "goods_id = '$goods_id' AND share_images != ''")));
		}
		
		return $count;
		
	}
	
	// 取得最近的评论
	function _getLastComments($id = 0, $num = 3)
	{
		$ordergoods_mod =& m('ordergoods');
		$comments = $ordergoods_mod->find(array(
			'conditions'=> "goods_id = '$id' AND evaluation_status = '1' AND comment <> '' ",
			'join'  	=> 'belongs_to_order',
			'fields'	=> 'buyer_id, buyer_name, anonymous, evaluation_time, comment, evaluation',
			'order' 	=> 'evaluation_time desc',
			'limit' 	=> $num,
			'count'		=> true
		));
		if($comments)
		{
			$member_mod = &m('member');
			foreach($comments as $key => $comment)
			{
				$member = $member_mod->get(array(
					'conditions'	=> 'user_id='.$comment['buyer_id'],
					'fields'		=> 'portrait',
				));
				empty($member['portrait']) && $member['portrait'] = Conf::get('default_user_portrait');
				
				$comments[$key]['portrait'] = $member['portrait'];
				$comments[$key]['buyer_name'] = cut_str($comment['buyer_name']);
			}
		}
		return array('list' => $comments, 'total' => $ordergoods_mod->getCount());
	}
	
	// 取得最近的问答
	function _getLastQas($id = 0, $num = 3)
	{
		$goodsqa_mod = & m('goodsqa');
		$qas = $goodsqa_mod->find(array(
            'join' => 'belongs_to_user',
            'fields' => 'member.user_name,question_content,reply_content,time_post,time_reply',
            'conditions' => '1 = 1 AND item_id = '.$id . " AND type = 'goods' AND reply_content <> '' ",
            'limit' => $num,
            'order' =>'time_post desc',
            'count' => true
        ));
		return array('list' => $qas, 'total' => $goodsqa_mod->getCount());
	}

    /* 销售记录 */
    function saleslog()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }

        $data = $this->_get_common_info($id);
        if ($data === false)
        {
            return;
        }
        else
        {
            $this->_assign_common_info($data);
        }

        /* 赋值销售记录 */
        $data = $this->_get_sales_log($id, 10);
        $this->_assign_sales_log($data);
		
		$this->_get_curlocal_title('sales_log');
        $this->display('goods.saleslog.html');
    }
    function qa()
    {        
		$id = intval($_GET['id']);
		$goods_qa =& m('goodsqa');
         
        if(!IS_POST)
        {
			if (!$id)
     		{
         		$this->show_warning('Hacking Attempt');
            	return;
      		}
			 
			if(!IS_AJAX)
			{
				$this->import_resource('mobile/jquery.plugins/jquery.form.min.js,mobile/jquery.plugins/jquery.infinite.js');
				$this->assign('infiniteParams', json_encode($_GET));
			
				//如果登陆，则查出email
        		if (!empty($_SESSION['user_info']))
        		{
            		$user_mod = & m('member');
            		$user_info = $user_mod->get(array(
                		'fields' => 'email',
                		'conditions' => '1=1 AND user_id = '.$_SESSION['user_info']['user_id']
            		));
            		$this->assign('email', $user_info['email']);
        		}
			
            	//是否开启验证码
            	if (Conf::get('captcha_status.goodsqa'))
            	{
                	$this->assign('captcha', 1);
            	}
				$this->assign('guest_comment_enable', Conf::get('guest_comment'));
			
				$this->_config_seo('title', Lang::get('qa') . ' - ' . Conf::get('site_title'));
				$this->_get_curlocal_title('qa');
            	$this->display('goods.qa.html');
			 }
			 else
			 {
				$page = $this->_get_page(intval($_GET['pageper']));
        		$goodsqa_mod = & m('goodsqa');
        		$qa_info = $goodsqa_mod->find(array(
            		'join' => 'belongs_to_user',
            		'fields' => 'member.user_name,question_content,reply_content,time_post,time_reply',
            		'conditions' => '1 = 1 AND item_id = '.$id . " AND type = 'goods'",
            		'limit' => $page['limit'],
            		'order' =>'time_post desc',
            		'count' => true
        		));
        		$page['item_count'] = $goodsqa_mod->getCount();
        		$this->_format_page($page);
				
				foreach($qa_info as $key => $val) {
					$qa_info[$key]['time_post'] = local_date('Y-m-d H:i:s', $val['time_post']);
					$qa_info[$key]['time_reply'] = local_date('Y-m-d H:i:s', $val['time_reply']);
				}
				
				// 必须加 array_values() js遍历顺序才对
				$data = array('result' => array_values($qa_info), 'totalPage' => $page['page_count']);
				echo json_encode($data);
			}	
        }
        else
        {
			if (!$id)
         	{
            	$this->json_error('Hacking Attempt');
            	return;
        	 }
			 
            /* 不允许游客评论 */
            if (!Conf::get('guest_comment') && !$this->visitor->has_login)
            {
                $this->json_error('guest_comment_disabled');

                return;
            }
            $content = (isset($_POST['content'])) ? trim($_POST['content']) : '';
            //$type = (isset($_POST['type'])) ? trim($_POST['type']) : '';
            $email = (isset($_POST['email'])) ? trim($_POST['email']) : '';
            $hide_name = (isset($_POST['hide_name'])) ? trim($_POST['hide_name']) : '';
            if (empty($content))
            {
                $this->json_error('content_not_null');
                return;
            }
            //对验证码和邮件进行判断

            if (Conf::get('captcha_status.goodsqa'))
            {
                if (base64_decode($_SESSION['captcha']) != strtolower($_POST['captcha']))
                {
                    $this->json_error('captcha_failed');
                    return;
                }
            }
            if (!empty($email) && !is_email($email))
            {
                $this->json_error('email_not_correct');
                return;
            }
            $user_id = empty($hide_name) ? $_SESSION['user_info']['user_id'] : 0;
            $conditions = 'g.goods_id ='.$id;
            $goods_mod = & m('goods');
            $ids = $goods_mod->get(array(
                'fields' => 'store_id,goods_name',
                'conditions' => $conditions
            ));
            extract($ids);
            $data = array(
                'question_content' => html_script($content),
                'type' => 'goods',
                'item_id' => $id,
                'item_name' => addslashes($goods_name),
                'store_id' => $store_id,
                'email' => $email,
                'user_id' => $user_id,
                'time_post' => gmtime(),
            );
            if (!$goods_qa->add($data))
            {
                $this->json_error('add_fail');
            	return;
            }
            $this->json_result('', 'add_ok');
        }
    }
	
	function getGoodsProInfo()
	{
		$goods_id 	= intval($_GET['goods_id']);
		$spec_id 	= intval($_GET['spec_id']);
		
		// 在此获取该商品的促销策略，包括促销商品，会员价格，手机专享优惠价格等
		$result = FALSE;
		
		import('promotool.lib');
		$promotool = new Promotool();
		
		$result = $promotool->getItemProInfo($goods_id, $spec_id);
		
		if($result === FALSE) {
			$this->json_error($result);
			exit;
		}
		
		$this->json_result($result);
	}

    /**
     * 取得公共信息
     *
     * @param   int     $id
     * @return  false   失败
     *          array   成功
     */
    function _get_common_info($id)
    {
        $cache_server =& cache_server();
        $key = 'page_of_goods_' . $id;
        $data = $cache_server->get($key);
        $cached = true;
        if ($data === false)
        {
            $cached = false;
            $data = array('id' => $id);

            /* 商品信息 */
            $goods = $this->_goods_mod->get_info($id);
			if ($goods['state'] == 2)
            {
                $this->show_warning('the_store_is_closed');
                exit;
            }
            if (!$goods || $goods['if_show'] == 0 || $goods['closed'] == 1 || $goods['state'] != 1)
            {
                $this->show_warning('goods_not_exist');
                return false;
            }
            $goods['tags'] = $goods['tags'] ? explode(',', trim($goods['tags'], ',')) : array();
			
			import('promotool.lib');
			$promotool = new Promotool(array('_store_id' => $goods['store_id']));
			
			$result = $promotool->getItemProInfo($goods['goods_id'], $goods['default_spec']);
			if($result !== FALSE) {
				if($result['pro_type'] == 'limitbuy') {
					$limitbuy_mod = &m('limitbuy');
					$limitbuy = $limitbuy_mod->get(array('conditions'=>"pro_id=".$result['pro_id'], 'fields' => 'end_time,pro_name'));
					$goods['lefttime'] = Psmb_init()->lefttime($limitbuy['end_time']);
					$goods['pro_name'] = $limitbuy['pro_name'];
				}
				else $goods['pro_name'] = Lang::get($result['pro_type']);
			}
		
            $data['goods'] = $goods;
			
			/* 获取商品详情页显示所有该商品具有的营销工具信息 */
			//import('promotool.lib');
            $data['promotool'] = $promotool->getGoodsAllPromotoolInfo($id);
			
            /* 店铺信息 */
            if (!$goods['store_id'])
            {
                $this->show_warning('store of goods is empty');
                return false;
            }
            $this->set_store($goods['store_id']);
            $data['store_data'] = $this->get_store_data();
			$data['store_data']['goodsAddress'] = end(explode(' ', preg_replace("/\s/"," ",$data['store_data']['region_name'])));

			$integral_mod = &m('integral');
			if($integral_mod->_get_sys_setting('integral_enabled'))
			{
				$data['goods']['integral_enabled'] = true;
				$data['goods']['exchange_price'] = $integral_mod->_get_sys_setting('exchange_rate')*$goods['max_exchange'];
			}
			
			$meal_mod = &m('meal');
			$data['goods']['has_meal'] = $meal_mod->has_meal($id);
			
            $cache_server->set($key, $data, 1800);
        }
        if ($cached)
        {
            $this->set_store($data['goods']['store_id']);
        }
		
		$coupon_mod = &m('coupon');
		$data['coupons'] = $coupon_mod->find(array(
			'conditions' => 'clickreceive = 1 AND if_issue = 1 AND (total = 0 OR (total > 0 && surplus > 0)) AND  coupon.end_time > '.gmtime().' AND store_id='.$data['store_data']['store_id'],
			'limit'      => 1
		));
		
		/* 收藏情况（某个商品是否被收藏，另：$goods['collects'] 代表的是某个商品被收藏的总数） */
		$collected = $this->_goods_mod->get(array(
            'join'  => 'be_collect',
            'fields'=> 'goods_id',
            'conditions' => 'collect.user_id = ' . $this->visitor->get('user_id').' AND goods_id='.$id,
        ));
		$data['goods']['collected'] = $collected ? 1 : 0;

        return $data;
    }
	
	function get_default_logist($delivery_template_id, $store_id)
	{
		$store_mod = &m('store');
		$region_mod= &m('region');
		$delivery_mod = &m('delivery_template');
		
		// 如果没有设置运费模板，则取该店铺默认的运费模板
		if(!$delivery_template_id || !$delivery_mod->get($delivery_template_id))
		{
			$delivery = $delivery_mod->get(array(
				'conditions'=>'store_id='.$store_id,
				'order'=>'template_id',
			));
	
			// 如果店铺也没有默认的运费模板
			if(empty($delivery)) {
				return array();
			}
		}
		else {
			$delivery = $delivery_mod->get($delivery_template_id);
		}
		
		// 通过IP自动获取省和城市id
		$city_id = 1;//$region_mod->get_city_id_by_ip();// 太慢

		$logist_fee = $delivery_mod->get_city_logist($delivery, $city_id);
		
		return !empty($logist_fee) ? current($logist_fee) : array();
	}

    /* 赋值公共信息 */
    function _assign_common_info($data)
    {
		// 手机版要读取默认的运费（PC端不需要）
		$data['goods']['default_logist'] = $this->get_default_logist($data['goods']['delivery_template_id'], $data['goods']['store_id']);
		
        /* 商品信息 */
        $goods = $data['goods'];
        $this->assign('goods', $goods);
        $this->assign('sales_info', sprintf(LANG::get('sales'), $goods['sales'] ? $goods['sales'] : 0));
        $this->assign('comments', sprintf(LANG::get('comments'), $goods['comments'] ? $goods['comments'] : 0));

        /* 店铺信息 */
        $this->assign('store', $data['store_data']);
		
		/* 浏览历史 */
        //$this->assign('goods_history', $this->_get_goods_history($data['id']));
		$this->_get_goods_history($data['id']);// 统计浏览用

        /* 默认图片 */
        $this->assign('default_image', Conf::get('default_goods_image'));
		
		/* 店铺优惠券 */
		$this->assign('coupons', $data['coupons']);
		
		/* 赋值营销工具 */
		$this->assign('promotool', $data['promotool']);
    }

    /* 取得销售记录 */
    function _get_sales_log($goods_id, $num_per_page)
    {
        $data = array();

        $page = $this->_get_page($num_per_page);
        $order_goods_mod =& m('ordergoods');
        $sales_list = $order_goods_mod->find(array(
            'conditions' => "goods_id = '$goods_id' AND order_alias.status = '" . ORDER_FINISHED . "'",
            'join'  => 'belongs_to_order',
            'fields'=> 'buyer_id, buyer_name, add_time, anonymous, goods_id, specification, price, quantity, evaluation',
            'count' => true,
            'order' => 'add_time desc',
            'limit' => $page['limit'],
        ));
		foreach($sales_list as $k=>$v) {
			$sales_list[$k]['buyer_name'] = cut_str($v['buyer_name']);
		}
        $data['sales_list'] = $sales_list;

        $page['item_count'] = $order_goods_mod->getCount();
        $this->_format_page($page);
        $data['page_info'] = $page;
        $data['more_sales'] = $page['item_count'] > $num_per_page;

        return $data;
    }

    /* 赋值销售记录 */
    function _assign_sales_log($data)
    {
        $this->assign('sales_list', $data['sales_list']);
        $this->assign('page_info',  $data['page_info']);
        $this->assign('more_sales', $data['more_sales']);
	}

    /* 更新浏览次数 */
    function _update_views($id)
    {
        $goodsstat_mod =& m('goodsstatistics');
		if($goodsstat_mod->get($id)){
        	$goodsstat_mod->edit($id, "views = views + 1");
		} else {
			$goodsstat_mod->add(array('goods_id' => $id, 'views' => 1));
		}
    }
}

?>
