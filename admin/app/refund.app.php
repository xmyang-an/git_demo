<?php

/**
 *    退款维权管理员控制器
 *
 *    @author    MiMall
 *    @usage    none
 */
class RefundApp extends BackendApp
{
	var $_order_mod;
	var $_order_log_mod;
	var $_order_extm_mod;
	var $_goods_mod;
	var $_ordergoods_mod;
	var $_refund_mod;
	var $_store_mod;
	var $_member_mod;
	var $_refund_message_mod;
	var $_deposit_trade_mod;
	
	function __construct()
    {
        $this->RefundApp();
    }
    function RefundApp()
    {
        parent::__construct();
        $this->_order_mod = &m('order');
		$this->_order_log_mod = &m('orderlog');
		$this->_order_extm_mod = &m('orderextm');
        $this->_goods_mod = &m('goods');
		$this->_ordergoods_mod = &m('ordergoods');
		$this->_refund_mod = &m('refund');
		$this->_store_mod = &m('store');
		$this->_member_mod = &m('member');
		$this->_refund_message_mod = &m('refund_message');
		$this->_deposit_trade_mod = &m('deposit_trade');
    }
	
	function index()
    {
		$query = $this->get_query_conditions();
		$this->assign('filtered', $query);
        $this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js',
		));
        $this->display('refund.index.html');
    }
	
	function get_xml()
	{
		$conditions = '1 = 1';
		$conditions .= $this->get_query_conditions();
        $param = array('refund_sn','total_fee','refund_goods_fee','refund_shipping_fee','created','status','ask_customer');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
		$refunds = $this->_refund_mod->find(array(
			'conditions'=> $conditions,
			'limit'     => $page['limit'],
			'order'     => $order,
			'count'   => true
		));
		$page['item_count']=$this->_refund_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($refunds as $k => $v)
		{
			$store = $this->_store_mod->get(array('conditions'=>'store_id='.$v['seller_id'],'fields'=>'store_name,owner_name'));
			$member = $this->_member_mod->get(array('conditions'=>'user_id='.$v['buyer_id'],'fields'=>'user_name'));
			$list = array();
			$operation = "<a class='btn green' href='index.php?app=refund&act=view&refund_id={$k}'><i class='fa fa-search-plus'></i>查看</a>";
			$list['operation'] = $operation;
			$list['refund_sn'] = $v['refund_sn'];
			$list['buyer_name'] = $member['user_name'];
			$list['store_name'] = "<a href='".SITE_URL."/index.php?app=store&id={$v['seller_id']}' target='_blank'>{$store['store_name']}</a>";
			$list['total_fee'] = $v['total_fee'];
			$list['refund_goods_fee'] = $v['refund_goods_fee'];
			$list['refund_shipping_fee'] = $v['refund_shipping_fee'];
			$list['created'] = local_date('Y-m-d H:i:s',$v['created']);
			$list['status'] = Lang::get('REFUND_'.strtoupper($v['status']));
			$list['ask_customer'] = $v['ask_customer']? '<em class="yes"><i class="fa fa-check-circle"></i>是</em>':'<em class="no"><i class="fa fa-ban"></i>否</em>' ;
			$data['list'][$k] = $list;
		}
		$this->flexigridXML($data);
	}

	function get_query_conditions(){
		$conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'refund_sn',
                'equal' => 'like',
            ),
        ));
		if(!empty($_GET['buyer_name']))
		{
			$users = $this->_member_mod->find(array(
				'conditions'=>"user_name like '%".trim($_GET['buyer_name'])."%'",
				'fields' => 'user_id',
			));
			$conditions .= " AND buyer_id ".db_create_in(array_keys($users));
		}
		if(!empty($_GET['store_name']))
		{
			$stores = $this->_store_mod->find(array(
				'conditions'=>"store_name like '%".trim($_GET['store_name'])."%'",
				'fields' => 'store_id',
			));
			$conditions .= " AND seller_id ".db_create_in(array_keys($stores));
		}
		return $conditions;
	}
		
	function export_csv()
	{
		$conditions = '1 = 1';
        $conditions .= $this->get_query_conditions();
		if ($_GET['id'] != '') {
            $ids = explode(',', $_GET['id']);
			$conditions .= ' AND refund_id' . db_create_in($ids);
        }
		$refunds = $this->_refund_mod->find(array(
			'conditions'=> $conditions,
			'order'     => 'created desc',
		));
		
		if(!$refunds) {
			$this->show_warning('no_such_item');
            return;
		}
		
		/* xls文件数组 */
		$record_xls = array();		
		$record_title = array(
			'refund_sn' 		=> 	'退款单编号',
    		'buyer_name' 		=> 	'买家',
    		'store_name' 		=> 	'卖家',
			'total_fee' => '交易金额',
			'refund_goods_fee' => '退款金额',
    		'refund_shipping_fee' => 	'退运费',
			'created' 		=> 	'申请时间',
			'status' => 	'退款状态',
			'ask_customer' 		=> 	'客服介入',
		);
		$folder = 'refund_'.local_date('Ymdhis', gmtime());
		$record_xls[] = $record_title;
		$record_value = array();
		foreach($record_title as $key=>$val)
		{
			$record_value[$key] = '';
		}
		foreach($refunds as $key=>$val)
    	{
			$store = $this->_store_mod->get(array('conditions'=>'store_id='.$val['seller_id'],'fields'=>'store_name,owner_name'));
			$member = $this->_member_mod->get(array('conditions'=>'user_id='.$val['buyer_id'],'fields'=>'user_name'));
			$record_value['refund_sn']	=	$val['refund_sn'];
			$record_value['buyer_name']	=	$member['user_name'];
			$record_value['store_name']	=	$store['store_name'];
			$record_value['total_fee']	=	$val['total_fee'];
			$record_value['refund_goods_fee']	=	$val['refund_goods_fee'];
			$record_value['refund_shipping_fee']	=	$val['refund_shipping_fee'];
			$record_value['created']	=	local_date('Y/m/d H:i:s',$val['created']);
			$record_value['status']	=	Lang::get('REFUND_'.strtoupper($val['status']));
			$record_value['ask_customer']	=	$val['ask_customer']? '是':'否' ;
        	$record_xls[] = $record_value;
    	}
		import('excelwriter.lib');
		$ExcelWriter = new ExcelWriter(CHARSET, $folder);
		$ExcelWriter->add_array($record_xls);
		$ExcelWriter->output();
	}
	
	function view()
	{
		$refund_id = empty($_GET['refund_id'])? 0 : intval($_GET['refund_id']);
		if(!$refund_id){
			$this->show_warning('refund_id_miss', 'back_list', 'index.php?app=refund&act=view&refund_id='.$refund_id);
			return;
		}
		
		//  读取退款信息
		$refund = $this->_refund_mod->get($refund_id);
		
		if(!$refund){
			$this->show_warning('refund_not_find', 'back_list', 'index.php?app=refund');
			return;
		}
		
		$tradeInfo = $this->_deposit_trade_mod->get(array('conditions' => "tradeNo='{$refund['tradeNo']}'", "fields" => 'bizOrderId'));
		$order_info = $this->_order_mod->get('order_sn='.$tradeInfo['bizOrderId']);
		
		if(!IS_POST)
		{
			$refund['shipped_text'] = Lang::get('shipped_'.$refund['shipped']);
			$refund['status_label'] = Lang::get('REFUND_'.strtoupper($refund['status']));
			
			$order_info['items'] = $this->_ordergoods_mod->find(array(
				'conditions'=>'order_id='.$order_info['order_id'],
			));
			$order_info['shipping'] = $this->_order_extm_mod->get($order_info['order_id']);
			
			$page   =   $this->_get_page(10);   //获取分页信息
			$refund['message'] = $this->_refund_message_mod->find(array(
				'conditions'	=>'refund_id='.$refund_id,
				'order'			=>'created desc',
				'limit'			=>$page['limit'],
				'count'   		=> true			
			));	
			$page['item_count'] = $this->_refund_message_mod->getCount();
			$this->_format_page($page);
			$this->assign('page_info', $page); 
			$this->assign('refund',$refund);
			$this->assign('order',$order_info);
			$this->display('refund.view.html');	
					
		}
		//  客服处理退款
		else
		{
			/* 订单实际金额信息（考虑折扣，调价的情况）*/
			$realAmount = $this->_order_mod->_getRealAmount($order_info['order_id']);
		
			// 检查提交的数据
			$this->_check_post_data($realAmount, $_POST, $refund);
			
			/*在此处理退款后的费用问题，将该商品的相关货款退还给买家(卖家) */
			
			$refund_goods_fee    = $_POST['refund_goods_fee'] ? $_POST['refund_goods_fee'] : 0;
			$refund_shipping_fee = $_POST['refund_shipping_fee'] ? $_POST['refund_shipping_fee'] : 0;
			$refund_total_fee    = $refund_goods_fee + $refund_shipping_fee;
			
			$amount			= round(floatval($refund_total_fee), 2);
			$chajia			= $refund['total_fee'] - $amount;
			
			/* 转到对应的业务实例，不同的业务实例用不同的文件处理，如购物，卖出商品，充值，提现等，每个业务实例又继承支出或者收入 */
			$depopay_type    =&  dpt('outlay', 'refund');
			$result 		= $depopay_type->submit(array(
				'trade_info' =>  array('user_id' => $order_info['seller_id'], 'party_id' => $order_info['buyer_id'],'amount'=> $amount),
				'extra_info' =>  $order_info + array('tradeNo' => $refund['tradeNo'], 'chajia' => $chajia, 'refund_id' => $refund_id, 'operator' => 'admin'),
				'post'		 =>	 $_POST,
			));
			if(!$result)
			{
				$this->json_error($depopay_type->_get_errors());
				return;
			}
			
			// 如果不是全额退款，则按商品总额减去退款商品总额的差价按佣金比率分配给分销商
			if($chajia > 0)
			{
				$mod_distribution = &m('distribution');
				$d_profit = $mod_distribution->get_profit($order_info['order_id'], $refund_goods_fee);
				$depopay_type    =&  dpt('income', 'distribution');
				$result = $depopay_type->submit(array(
					'trade_info' =>  array('user_id' => $order_info['seller_id'], 'party_id' => $order_info['buyer_id'], 'amount' => $chajia),
					'extra_info' =>  $order_info + array('tradeNo' => $tradeInfo['tradeNo'],'d_profit' => $d_profit),
					'post'		 =>	 $_POST,
				));
				if(!$result)
				{
					$this->json_error($depopay_type->_get_errors());
					return;
				}
			}
			
			/* 退款后的积分处理（积分返还，积分赠送） */
			Psmb_init()->_handle_order_integral_return($order_info, $refund);
			
			/* 短信提醒：告知买家与卖家，客服已处理完毕 */
				$this->sendMailMsgNotify($order_info, array(
					),
					array(
						'phone_mob' => $buyer_info['phone_mob'],
						
						'body' => sprintf(Lang::get('sms_warn_buyer_refund_success'), $order_info['order_sn']),
						'sender' => 0,
					)
				);

				$this->sendMailMsgNotify($order_info, array(
					),
					array(
						'phone_mob' => $seller_info['phone_mob'],
						
						'body' => sprintf(Lang::get('sms_warn_seller_refund_success'), $order_info['order_sn']),
						'sender' => 0,
					)
				);
			
			$this->json_result(array('rel'=>1),'system_handle_refund_ok');
		}
	}
	
	function _check_post_data($realAmount = array(), $post = array(), $refund = array())
	{
		list($realGoodsAmount, $realShippingFee, $realOrderAmount) = $realAmount;

		/* 关闭或者是成功的退款，不能添加留言 */
		if(in_array($refund['status'], array('SUCCESS','CLOSED'))){
			$this->json_error('add_refund_message_not_allow');
			exit;
		}	
		if(empty($post['refund_goods_fee']) || floatval($post['refund_goods_fee'])<0)
		{
			$this->json_error('refund_fee_ge0');
			exit;
			
		} elseif(floatval($post['refund_goods_fee']) > $realGoodsAmount){
			$this->json_error('refund_fee_error');
			exit;
		}
		if($post['refund_shipping_fee'] !='' && floatval($post['refund_shipping_fee'])<0)
		{
			$this->json_error('refund_shipping_fee_ge0');
			exit;
			
		}
		if(floatval($post['refund_shipping_fee']) > $realShippingFee){
			$this->json_error('refund_shipping_fee_error');
			exit;
		}
	}
}

?>
