<?php
class My_referApp extends MemberbaseApp {

    var $user_id;
    var $_member_mod;

    function __construct() 
	{
        $this->My_referApp();
    }

    function My_referApp() 
	{

        parent::__construct();
		
        $this->user_id = $this->visitor->get('user_id');
        $this->_member_mod = & m('member');
    }

    function index() 
	{

        //获取当前用户的信息
        $member_info = $this->_member_mod->get($this->user_id);
        $this->assign('member_info', $member_info);

        if (!empty($member_info['referid']))
		{
            //获取当前用户的推荐人
            $parent_refers = $this->_member_mod->get($member_info['referid']);
            $this->assign('parent_refers', $parent_refers);
        }
		
		if(isset($_SESSION['in_wxmp']) || (isset($_GET['s']) && ($_GET['s'] == 'wxmp')))
		{
			$file = ROOT_PATH.'/data/files/mall/phpqrcode/wxmp_'.$this->visitor->get('user_id').'.jpg';
			$this->assign('refer_qrcode', $this->GetWxMPQRCode($file));
		}
		else{
		    $this->assign('refer_qrcode', $this->generateQRCode('refer_qrcode', array('user_id' => $this->user_id)));
		}
		
		$this->headtag('<script type="text/javascript" src="{lib file=layer/layer.js}"></script><script type="text/javascript" src="{lib file=clipboard.min.js}"></script>');
		
		$this->assign('my_refer_count', count($this->_member_mod->getUserRefer($this->user_id)));
		
		$deposit_trade_mod = &m('deposit_trade');
		$my_refer_amount = $deposit_trade_mod->getOne("select sum(amount) from {$deposit_trade_mod->table} where buyer_id=".$this->user_id.' AND bizIdentity="'.TRADE_FX.'"');
		$this->assign('my_refer_amount', $my_refer_amount);
		
		$this->assign('refer_count_1', count($this->_caculateLayerPeople($this->user_id,1)));
		$this->assign('refer_count_2', count($this->_caculateLayerPeople($this->user_id,2)));
		$this->assign('refer_count_3', count($this->_caculateLayerPeople($this->user_id,3)));
		
		$this->_get_curlocal_title('my_refer');

        $this->display('my_refer.index.html');
    }
	
	function GetWxMPQRCode($file)
	{
		if(!file_exists($file)){
			$params['appid'] = Conf::get('weixinminkey.AppID');// 小程序的appID
			$params['secret'] = Conf::get('weixinminkey.AppSecret');//小程序的appsecret
			
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$params['appid']."&secret=".$params['secret'];
			$result = ecm_curl($url);
			$data = json_decode($result,true);
			
			if(!$data['access_token']){
				return false;
			}
			
			$url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.$data['access_token'];
			$result = ecm_curl($url,'POST',json_encode(array('scene' => $this->visitor->get('user_id'))),'','',TRUE);
			
			$qrData = json_decode($result,true);
			if(!empty($qrData)){
				if($qrData['errcode']){
					return false;
				}
			}
			
			file_put_contents($file, $result);
		}
		
		return str_replace(ROOT_PATH, SITE_URL,$file);
	}
	
	function _caculateLayerPeople($user_ids,$max=1,$layer=1)
	{
		if(!is_array($user_ids)) $user_ids = array($user_ids);
		
		$member_mod = &m('member');
		$users = $member_mod->find(array(
			'conditions' => 'referid '.db_create_in($user_ids),
			'fields'     => 'user_id'
		));
		
		$result = array();
		if(!empty($users))
		{
			$ids = array_keys($users);
			if($layer <> $max)
			{
				$layer++;
				$result = $this->_caculateLayerPeople($ids,$max,$layer);
			}
			else{
				$result = $ids;
			}
		}
		
		return $result;
	}

    function all_refer() {
        //获取相关子孙推荐人
        $all_refers = $this->_get_all_refers($this->user_id);
        $this->assign('all_refers', $all_refers);
		
		$this->_get_curlocal_title('all_refer');

        $this->display('my_refer.all.html');
    }

    function _get_all_refers($user_id) {

        //获取所有用户 包含子孙
        $members = $this->_member_mod->find();

        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($members, 'user_id', 'referid', 'user_name');


        return $tree->getOptions(0, $user_id, NULL, '<img src="' . site_url() . '/themes/mall/default/styles/default/images/treetable/tv-item-last.gif" class="ttimage">');
    }
	
	function _format_refer_string($data)
	{
		foreach($data as $key=>$val)
		{
			$val['phone_mob'] && $data[$key]['phone_mob'] = cut_str($val['phone_mob'],3,3);
			$val['real_name'] && $data[$key]['real_name'] = cut_str($val['real_name'],1,0,2);
			$val['user_name'] && $data[$key]['user_name'] = cut_str($val['user_name'],3,3);
			$data[$key]['portrait'] = portrait($val['user_id'],$val['portrait'] );
			$val['im_qq'] && $data[$key]['im_qq'] = cut_str($val['im_qq'],3,2);
		}

		return $data;
	}


    /**
     * 一级推荐人
     */
    function refer_user1() {
		$this->_get_curlocal_title('refer_user1');

        $page = $this->_get_page();
        $refers1 = $this->_member_mod->findAll(
                array(
                    'conditions' => 'referid=' . $this->user_id,
                    'count' => true,
                    'limit' => $page['limit'],
                )
        );
        $page['item_count'] = $this->_member_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('refers', $refers1);
        $this->display('my_refer.refer.html');
    }

    function refer_user2() {
        $this->_get_curlocal_title('refer_user2');

        //首先获得一级的推荐人列表
        $refers1 = $this->_member_mod->findAll(
                array(
                    'fields' => 'referid',
                    'conditions' => 'referid=' . $this->user_id,
                )
        );
		
		

        //如果有推荐人
        if (!empty($refers1)) {
            $ids = array();
            foreach ($refers1 as $key => $refer) {
                $ids[] = $refer['user_id'];
            }
            $page = $this->_get_page();
            $refers2 = $this->_member_mod->findAll(
                    array(
                        'conditions' => 'referid ' . db_create_in($ids),
                        'count' => true,
                        'limit' => $page['limit'],
                    )
            );
			
			$refers2 = $this->_format_refer_string($refers2);
			
            $page['item_count'] = $this->_member_mod->getCount();
            $this->_format_page($page);
            $this->assign('page_info', $page);
            $this->assign('refers', $refers2);
        }
        $this->display('my_refer.refer.html');
    }

    function refer_user3() {
        $this->_get_curlocal_title('refer_user3');

        //首先获得一级的推荐人列表
        $refers1 = $this->_member_mod->findAll(
                array(
                    'fields' => 'referid',
                    'conditions' => 'referid=' . $this->user_id,
                )
        );

        //如果有推荐人
        if (!empty($refers1)) {
            $ids = array();
            foreach ($refers1 as $key => $refer) {
                $ids[] = $refer['user_id'];
            }

            $refers2 = $this->_member_mod->findAll(
                    array(
                        'conditions' => 'referid ' . db_create_in($ids),
                    )
            );

            if (!empty($refers2)) {
                $ids = array();
                foreach ($refers2 as $key => $refer) {
                    $ids[] = $refer['user_id'];
                }

                $page = $this->_get_page(10);
                $refers3 = $this->_member_mod->findAll(
                        array(
                            'conditions' => 'referid ' . db_create_in($ids),
                            'count' => true,
                            'limit' => $page['limit'],
                        )
                );
				$refers3 = $this->_format_refer_string($refers3);
                $page['item_count'] = $this->_member_mod->getCount();
                $this->_format_page($page);
                $this->assign('page_info', $page);
                $this->assign('refers', $refers3);
            }
        }
        $this->display('my_refer.refer.html');
    }

	function refer_order()
	{
		$this->_get_curlocal_title('refer_order');
		
		$page = $this->_get_page(10);
        $model_order = & m('order');
        !$_GET['type'] && $_GET['type'] = 'all_orders';
		
        $con = array(
            array(//按订单状态搜索
                'field' => 'status',
                'name' => 'type',
                'handler' => 'order_status_translator',
            ),
            array(//按店铺名称搜索
                'field' => 'seller_name',
                'equal' => 'LIKE',
            ),
            array(//按下单时间搜索,起始时间
                'field' => 'add_time',
                'name' => 'add_time_from',
                'equal' => '>=',
                'handler' => 'gmstr2time',
            ),
            array(//按下单时间搜索,结束时间
                'field' => 'add_time',
                'name' => 'add_time_to',
                'equal' => '<=',
                'handler' => 'gmstr2time_end',
            ),
            array(//按订单号
                'field' => 'order_sn',
            ),
        );
		
		$buyer_ids = $this->_member_mod->getUserRefer($this->user_id);
		$conditions .= !empty($buyer_ids) ? ' 	AND buyer_id '.db_create_in($buyer_ids) : ' AND buyer_id = -1';
		
        $conditions .= $this->_get_query_conditions($con);
        /* 查找订单 */
        $orders = $model_order->findAll(array(
            'conditions' => "status > 0 AND FIND_IN_SET(".$this->user_id.",referid) {$conditions}",
            'fields' => 'this.*',
            'count' => true,
            'limit' => $page['limit'],
            'order' => 'add_time DESC',
            'include' => array(
                'has_ordergoods', //取出商品
            ),
        ));
			
		if(!empty($orders))
		{
			foreach($orders as $key=>$val)
			{
				$rewards = unserialize($val['refer_reward']);
				foreach($rewards[$this->user_id]['total'] as $k=>$v)
				{
					$orders[$key]['reward'] += $v;
				}
				
				foreach($val['order_goods'] as $rec_id=>$goods){
					if(isset($rewards[$this->user_id]['total'][$goods['spec_id']])){
						$orders[$key]['order_goods'][$rec_id]['reward'] = $rewards[$this->user_id]['total'][$goods['spec_id']];
					}
				}
			}
		}

		$page['item_count'] = $model_order->getCount();
        $this->assign('types', array('all' => '所有订单',
            'pending' => Lang::get('pending_orders'),
            'accepted' => Lang::get('accepted_orders'),
            'shipped' => Lang::get('shipped_orders'),
            'finished' => Lang::get('finished_orders'),
        ));
		
        $this->assign('type', $_GET['type']);
        $this->assign('orders', $orders);
        $this->_format_page($page);
        $this->assign('page_info', $page);
		
		$this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'dialog/dialog.js',
                    'attr' => 'id="dialog_js"',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.ui/i18n/' . i18n_code() . '.js',
                    'attr' => '',
                )
            ),
            'style' => 'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
		
		$this->display('my_refer_order.html');
	}	
}
?>