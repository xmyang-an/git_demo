<?php

/**
 *    退款维权管理控制器
 *
 *    @author    MiMall
 *    @usage    none
 */
class RefundApp extends MemberbaseApp
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
		if(!IS_AJAX)
		{
			$this->import_resource('mobile/jquery.plugins/jquery.infinite.js');
			$this->assign('infiniteParams', json_encode($_GET));
			
        	$this->_config_seo('title', Lang::get('refund_apply') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('refund_apply');
			$this->display('refund.index.html');
		}
		else
		{
			$page   =   $this->_get_page(intval($_GET['pageper']));   //获取分页信息
			$refunds = $this->_refund_mod->find(array(
				'conditions'	=>	'buyer_id='.$this->visitor->get('user_id'),
				'limit'			=>	$page['limit'],
				'order'     	=>  'created desc',
				'count'   		=>  true
			));
			$page['item_count'] = $this->_refund_mod->getCount();
			$ordergoods_mod = &m('ordergoods');
			foreach($refunds as $key => $refund)
			{	
				if($tradeInfo = $this->_deposit_trade_mod->get(array('conditions' => "tradeNo='{$refund['tradeNo']}'", "fields" => 'bizOrderId'))) 
				{
					$order = $this->_order_mod->get(array(
						'conditions'=>'order_sn='.$tradeInfo['bizOrderId'], 
						'fields'=>'order_id,order_sn, buyer_name, seller_name',
					));
					
					$order['goods_list'] = $ordergoods_mod->find('order_id='.$order['order_id']);
					
					$refunds[$key] = $refunds[$key] + $order;
				}
				$refunds[$key]['status_label'] = Lang::get('REFUND_'.strtoupper($refund['status']));
			}
			$this->_format_page($page);
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($refunds), 'totalPage' => $page['page_count']);
			echo json_encode($data);
		}
	}
	function view()
	{
		$refund_id = empty($_GET['refund_id'])? 0 : intval($_GET['refund_id']);
		
		if(!IS_POST)
		{
			if(!$refund_id){
				$this->show_warning('refund_not_exist');
				return;
			}
		
			/* 同时验证该退款是否可以查看 */
			$refund = $this->_refund_mod->get(array(
				'conditions'=>'refund_id='.$refund_id.' AND (buyer_id='.$this->visitor->get('user_id').' OR seller_id='.$this->visitor->get('user_id').')'));
		
			if(!$refund){
				$this->show_warning('refund_not_exist');
				return;
			}
			
			$refund['shipped_text'] = Lang::get('shipped_'.$refund['shipped']);
			$refund['status_label'] = Lang::get('REFUND_'.strtoupper($refund['status']));
			
			// 找order_id
			$tradeInfo = $this->_deposit_trade_mod->get(array(
				'conditions' => 'merchantId="'.MERCHANTID.'" AND tradeNo="'.$refund["tradeNo"].'"', 'fields' => 'bizOrderId'));
			$orderInfo = $this->_order_mod->get(array(
				'conditions'	=> "order_sn='".$tradeInfo['bizOrderId']."'",
				'fields'		=> "order_id"
			));
			if($orderInfo) $refund['order_id'] = $orderInfo['order_id'];
			
			$title = 'refund_view';
			if(in_array(trim($_GET['type']), array('logs'))) 
			{
				if(!IS_AJAX)
				{
					//$this->import_resource('mobile/jquery.plugins/jquery.infinite.js');
					$this->assign('infiniteParams', json_encode($_GET));
			
					$title = 'refund_logs';
				}
				else
				{
					$page   =   $this->_get_page(intval($_GET['pageper']));   //获取分页信息
					$messageList = $this->_refund_message_mod->find(array(
						'conditions'=>'refund_id='.$refund_id,
						'order'=>'created desc',
						'limit'=>$page['limit'],
						'count'=> true				
					));	
					$page['item_count']=$this->_refund_message_mod->getCount();
					$this->_format_page($page);
					
					foreach($messageList as $key => $val)
					{
						if($val['owner_id'] == $this->visitor->get('user_id')) {
							$sender = '自己';
						} elseif($val['owner_role'] == 'buyer') {
							$sender = '买家';
						} elseif($val['owner_role'] == 'seller') {
							$sender = '卖家';
						} elseif($val['owner_role'] == 'admin') {
							$sender = '平台客服';
						}
						
						$messageList[$key]['sender'] = $sender;
						$messageList[$key]['created'] = local_date('Y-m-d H:i:s', $val['created']);
					}
					$refund['message'] = array_values($messageList);
					
					// 必须加 array_values() js遍历顺序才对(由于refund是一维数组，此处不用加)
					$data = array('result' => $refund, 'totalPage' => $page['page_count']);
					echo json_encode($data);
					exit;
				}
			}
			elseif(in_array(trim($_GET['type']), array('send'))) 
			{
				$title = 'refund_send';
			}
			
			$this->assign('refund',$refund);
			
			$this->import_resource('mobile/jquery.plugins/jquery.form.min.js, mobile/jquery.plugins/jquery.infinite.js');
			
			
        	$this->_config_seo('title', Lang::get($title) . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title($title);
			$this->display('refund.view.html');			
		}
		//  新增退款留言
		else
		{
			if(!$refund_id){
				$this->json_error('refund_not_exist');
				return;
			}
		
			/* 同时验证该退款是否可以查看 */
			$refund = $this->_refund_mod->get(array(
				'conditions'=>'refund_id='.$refund_id.' AND (buyer_id='.$this->visitor->get('user_id').' OR seller_id='.$this->visitor->get('user_id').')'));
		
			if(!$refund){
				$this->json_error('refund_not_exist');
				return;
			}
			
			/* 关闭或者是成功的退款，不能添加留言 */
			if(in_array($refund['status'], array('SUCCESS', 'CLOSED'))){
				$this->json_error('add_refund_message_not_allow');
				return;
			}
			
			$content = trim($_POST['content']);
			if(!$content) {
				$this->json_error('refund_message_empty');
                return;
			}
			
			$refund_image = $this->_upload_files();
            if ($refund_image === false){
				$this->json_error('refund_message_image_upload_error');
                return;
            }
			$data = array(
				'owner_id'	=> $this->visitor->get('user_id'),
				'owner_role'=> $refund['buyer_id']==$this->visitor->get('user_id') ? 'buyer' : ($refund['seller_id']==$this->visitor->get('user_id') ? 'seller' : 'admin'),
				'refund_id'	=> $refund_id,
				'content'	=> htmlspecialchars(trim($_POST['content'])),
				'pic_url'	=> $refund_image['refund_cert'],
				'created'	=> gmtime()				
			);
			$this->_refund_message_mod->add($data);
			$this->json_result('', 'add_ok');
		}
	}
	
	/* 申请退款 */
	function add()
	{
		$order_id = intval($_GET['order_id']);
		
		$order = $this->_order_mod->get($order_id);
		if($order['extension'] == 'groupbuy')//团购的订单不允许退款
		{
			if($order['team_id'] > 0){
				$team_mod = &m('team');
				$team = $team_mod->get($order['team_id']);
				if($team['status'] <> 1){
					$this->show_warning('该订单无法操作退款');
					return;
				}
			}
		}
		
		$tradeInfo = $this->_deposit_trade_mod->get('merchantId="'.MERCHANTID.'" AND bizIdentity="'.TRADE_ORDER.'" AND bizOrderId="'.$order['order_sn'].'"');
		if(!$tradeInfo)
		{
			$this->show_warning('such_order_not_trade');
			return;
		}
		
		/* 如果是货到付款的订单，不允许退款 */
		if(in_array($tradeInfo['payment_code'], array('cod')))
		{
			$this->show_warning('cod_order_refund_disabled');
			return;
		}
		
		/* 如果已存在退款记录，则直接访问 */
		if($refund = $this->_refund_mod->get("tradeNo='{$tradeInfo['tradeNo']}'")) {
			header('Location:index.php?app=refund&act=view&refund_id='.$refund['refund_id']);
			exit;
		}

		/* 验证该订单是否可以申请退款 */
		$this->_available_refund($tradeInfo);
		
		/* 物流信息 */
		$shipping_info = $this->_order_extm_mod->get($order_id);
		
		/* 订单实际金额信息（考虑折扣，调价的情况）*/
		$realAmount = $this->_order_mod->_getRealAmount($order_id);
		
		if(!IS_POST)
		{
			$refund = array();
			
			list($realGoodsAmount, $realShippingFee, $realOrderAmount) = $realAmount;
			
			$refund['goods_fee'] = $realGoodsAmount;
			$refund['total_fee'] =  $realOrderAmount;
			$refund['shipping_fee'] = $realShippingFee;
			
			$refund['shippeds'] = $this->_getShippedOptions();
			$refund['reasons'] = $this->_getRefundReasonOptions();
			$this->assign('refund', $refund);
			
			$this->_get_curlocal_title('refund_add');
        	$this->_config_seo('title', Lang::get('refund_add') . ' - ' . Lang::get('member_center'));
			$this->display('refund.form.html');
			
		}
		else 
		{
			// 检查提交的数据
			$this->_check_post_data($realAmount, $_POST);
			list($realGoodsAmount, $realShippingFee, $realOrderAmount) = $realAmount;

			$refund_goods_fee    = $_POST['refund_goods_fee'] ? $_POST['refund_goods_fee'] : 0;
			$refund_shipping_fee = $_POST['refund_shipping_fee'] ? $_POST['refund_shipping_fee'] : 0;

			$data =  array(
				'tradeNo'				=>$tradeInfo['tradeNo'],
				'refund_sn'				=>$this->_refund_mod->gen_refund_sn(),
				'title'					=>$this->_getRefundTitle($order_id),
				'refund_reason'			=>htmlspecialchars(trim($_POST['refund_reason'])),
				'refund_desc'   		=>htmlspecialchars(trim($_POST['refund_desc'])),
				'total_fee'				=>$realOrderAmount,
				'goods_fee'	    		=>$realGoodsAmount,
				'shipping_fee'			=>$realShippingFee,
				'refund_total_fee'		=>$refund_goods_fee + $refund_shipping_fee,
				'refund_goods_fee'		=>$refund_goods_fee,
				'refund_shipping_fee'	=>$refund_shipping_fee,
				'shipped'				=>$_POST['shipped'] ? intval(trim($_POST['shipped'])) : 0,
				'buyer_id'				=>$order['buyer_id'],
				'seller_id'				=>$order['seller_id'],
				'status'				=>'WAIT_SELLER_AGREE', // 买家已经申请退款，等待卖家同意
				'created'				=>gmtime(),
			);
			if(!$refund_id = $this->_refund_mod->add($data)) {
				$this->show_warning('refund_submit_fail');
				return;
			}
			else
			{				
				$this->_refund_message_mod->add(array(
					'owner_id'	=> $this->visitor->get('user_id'),
					'owner_role'=> 'buyer',
					'refund_id'	=> $refund_id,
					'content'	=> sprintf(LANG::get('apply_refund_content_change'), $_POST['refund_goods_fee'], $refund_shipping_fee, LANG::get('shipped_'.$_POST['shipped']), $_POST['refund_reason'], $_POST['refund_desc']),
					'created'	=> gmtime(),
				));
				
				/* 短信提醒： 买家已申请退款通知卖家 */
				$this->sendMailMsgNotify($order, 
					array(
					),
					array(
						
						'key' => 'refund_apply_warn_seller',
						'body' => sprintf(Lang::get('sms_warn_seller_refund_apply'), $order['order_sn']),
					
					)
				);
				
				$this->show_message('refund_submit_success','back_list', url('app=refund&act=view&refund_id='.$refund_id));
			}
		}
	}
	function edit()
	{
		$refund_id = empty($_GET['refund_id'])? 0 : intval($_GET['refund_id']);
		if(!$refund_id){
			$this->show_warning('no_such_refund');
			return;
		}
		
		/* 验证当前用户对该退款是否有编辑的权限 */
		$refund = $this->_refund_mod->get(array(
			'conditions'=>'(status != "SUCCESS" AND status != "CLOSED" AND status != "WAIT_ADMIN_AGREE") and refund_id='.$refund_id.' and buyer_id='.$this->visitor->get('user_id')));
			
		if(!$refund){
			$this->show_warning('refund_not_allow_edit');
			return;
		}
		
		if(!IS_POST)
		{
			$refund['shippeds'] = $this->_getShippedOptions();
			$refund['reasons'] = $this->_getRefundReasonOptions();
			
			$this->assign('refund', $refund);
			
			$this->_get_curlocal_title('refund_edit');
        	$this->_config_seo('title', Lang::get('refund_edit') . ' - ' . Lang::get('member_center'));
			$this->display('refund.form.html');			
		}
		else
		{	
			$tradeInfo = $this->_deposit_trade_mod->get(array('conditions' => "tradeNo='{$refund['tradeNo']}'", 'fields' => 'bizOrderId'));
			
			/* 订单实际金额信息（考虑折扣，调价的情况）*/
			$order_info = $this->_order_mod->get(array('conditions' => 'order_sn="'.$tradeInfo['bizOrderId'].'"', 'fields' => 'order_id'));
			$realAmount = $this->_order_mod->_getRealAmount($order_info['order_id']);
		
			// 检查提交的数据
			$this->_check_post_data($realAmount, $_POST);
			$refund_goods_fee    = $_POST['refund_goods_fee'] ? $_POST['refund_goods_fee'] : 0;
			$refund_shipping_fee = $_POST['refund_shipping_fee'] ? $_POST['refund_shipping_fee'] : 0;

			$data =  array(
				'status'				=> 'WAIT_SELLER_CONFIRM',// 退款申请等待卖家确认中(买家修改后)
				'refund_reason'			=> htmlspecialchars(trim($_POST['refund_reason'])),
				'refund_desc'   		=> htmlspecialchars(trim($_POST['refund_desc'])),
				'refund_total_fee'		=> $refund_goods_fee + $refund_shipping_fee,
				'refund_goods_fee'		=> $refund_goods_fee,
				'refund_shipping_fee'	=> $refund_shipping_fee,
				'shipped'				=> $_POST['shipped'] ? intval(trim($_POST['shipped'])) : 0,
			);
			$this->_refund_mod->edit($refund_id,$data);
			
			$this->_refund_message_mod->add(array(
				'owner_id'	=> $this->visitor->get('user_id'),
				'owner_role'=> 'buyer',
				'refund_id'	=> $refund_id,
				'content'	=> sprintf(LANG::get('refund_content_change'), $_POST['refund_goods_fee'], $_POST['refund_shipping_fee'], LANG::get('shipped_'.$_POST['shipped']), $_POST['refund_reason'], $_POST['refund_desc']),
				'created'	=> gmtime(),
			));
					
			$this->show_message('edit_ok','back_list', url('app=refund&act=view&refund_id='.$refund_id));
		}
	}
	/* 要求客服介入处理 */
	function ask_customer()
	{
		$refund_id = empty($_GET['refund_id'])? 0 : intval($_GET['refund_id']);
		if(!$refund_id){
			$this->json_error('handle_fail');
			return;
		}
		/* 验证当前用户是否有对该退款有要求客服介入的权限*/
		$refund = $this->_refund_mod->get(array('conditions'=>'ask_customer !=1 and (status!="SUCCESS" AND status!="CLOSED") and refund_id='.$refund_id.' and (buyer_id='.$this->visitor->get('user_id').' or seller_id='.$this->visitor->get('user_id').')','fields'=>'refund_id, refund_sn, buyer_id,seller_id'));
		if(!$refund){
			$this->json_error('ask_customer_not_allow');
			return;
		}
		$this->_refund_mod->edit($refund_id, array('ask_customer' => 1));
		
		/* 同时插入退款处理日志 */
		if($refund['buyer_id'] == $this->visitor->get('user_id')){
			$owner_role = 'buyer';
		} else {
			$owner_role = 'seller';
		}
		$data = array(
			'owner_id'	=> $this->visitor->get('user_id'),
			'owner_role'=> $owner_role,
			'refund_id'	=> $refund_id,
			'content'	=> sprintf(Lang::get('ask_customer_content_change'), Lang::get($owner_role)),
			'created'	=> gmtime(),		
		);
		if(!$this->_refund_message_mod->add($data)) {
			$this->json_error('handle_fail');
			return;
		}
		
		$this->json_result('', 'ask_customer_ok');
	}
	/* 卖家同意退款 */
	function agree()
	{
		$refund_id = empty($_GET['refund_id']) ? 0 : intval($_GET['refund_id']);
		if(!$refund_id){
			$this->json_error('handle_fail');
			return;
		}
		
		/* 验证当前用户对该退款是否有同意权限 */
		$refund = $this->_refund_mod->get(array('conditions'=>'(status !="SUCCESS" AND status !="CLOSED" AND status !="WAIT_ADMIN_AGREE") and refund_id='.$refund_id.' and seller_id='.$this->visitor->get('user_id')));
		
		if(!$refund){
			$this->json_error('agree_no_allow');
			return;
		}
		
		$amount			= $refund['refund_total_fee'];
		$chajia			= number_format($refund['total_fee'] - $amount, 2);
		
		$tradeInfo 		= $this->_deposit_trade_mod->get(array("conditions" => "tradeNo='{$refund['tradeNo']}'", "fields" => 'bizOrderId')); 
		$order_info		= $this->_order_mod->get("order_sn='{$tradeInfo['bizOrderId']}'");
		
		/* 转到对应的业务实例，不同的业务实例用不同的文件处理，如购物，卖出商品，充值，提现等，每个业务实例又继承支出或者收入 */
		$depopay_type    =&  dpt('outlay', 'refund');
		$result 		= $depopay_type->submit(array(
			'trade_info' =>  array('user_id' => $order_info['seller_id'], 'party_id' => $order_info['buyer_id'], 'amount' => $amount),
			'extra_info' =>  $order_info + array('tradeNo' => $refund['tradeNo'], 'chajia' => $chajia, 'refund_id' => $refund_id,'operator' => 'seller'),
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
			$d_profit = $mod_distribution->get_profit($order_info['order_id'], $refund['refund_goods_fee']);
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
		
		/* 短信提醒：卖家同意退款，通知买家 */
		$this->sendMailMsgNotify($order_info, array(),
			array(
				'phone_mob' => $buyer_info['phone_mob'],
				'key' => 'seller_agree_refund_warn_buyer', 
				'body' => sprintf(Lang::get('sms_warn_buyer_agree_refund'), $order_info['order_sn']),
			)
		);
			
		$this->json_result('', 'seller_agree_refund_ok');
	}
	
	/* 拒绝退款 */
	function refuse()
	{
		$refund_id = empty($_GET['refund_id']) ? 0 : intval($_GET['refund_id']);
		
		/* 验证当前用户对该退款是否有拒绝权限 */
		if($refund_id) {
			$refund = $this->_refund_mod->get(array('conditions'=>'(status!="SUCCESS" AND status!="CLOSED" AND status!="WAIT_ADMIN_AGREE") and refund_id='.$refund_id.' and seller_id='.$this->visitor->get('user_id')));
		}
		
		if(!IS_POST)
		{
			if(!$refund){
				$this->show_warning('refuse_not_allow');
				return;
			}
		
			$this->assign('refund', $refund);
			
			$this->import_resource('mobile/jquery.plugins/jquery.form.min.js');
			
			$this->_get_curlocal_title('refund_refuse');
        	$this->_config_seo('title', Lang::get('member_center'));
			$this->display('refund.refuse.html');			
		}
		else
		{
			if(!$refund){
				$this->json_error('refuse_not_allow');
				return;
			}
			
			$content = trim($_POST['content']);
			if(!$content) {
				$this->json_error('refund_message_empty');
                return;
			}
			
			$refund_image = $this->_upload_files();
            if ($refund_image === false)
            {
				$this->json_error('refund_message_image_upload_error');
                return;
            }
			
			$this->_refund_mod->edit($refund_id, array('status'=>'SELLER_REFUSE_BUYER'));
			
			$data = array(
				'owner_id'	=> $this->visitor->get('user_id'),
				'owner_role'=> 'seller',
				'refund_id'	=> $refund_id,
				'content'	=> sprintf(Lang::get('refuse_content_change'), htmlspecialchars(trim($_POST['content']))),
				'pic_url'	=> $refund_image['refund_cert'],
				'created'	=> gmtime()				
			);
			$this->_refund_message_mod->add($data);
			$this->json_result(array('ret_url' => url('app=refund&act=view&refund_id='.$refund_id)), 'seller_refuse_refund_ok');	
		}
		
	}
	/* 取消退款 */
	function cancel()
	{
		$refund_id = empty($_GET['refund_id']) ? 0 : intval($_GET['refund_id']);
		if(!$refund_id){
			$this->json_error('handle_fail');
			return;
		}
		/* 验证是否具有取消该退款的权限 */
		$refund = $this->_refund_mod->get(array('conditions'=>'(status!="SUCCESS" AND status!="CLOSED" AND status!="WAIT_ADMIN_AGREE") and refund_id='.$refund_id.' and buyer_id='.$this->visitor->get('user_id'),'fields'=>'refund_id,refund_sn'));
		if(!$refund)
		{
			$this->json_error('cancel_not_allow');
			return;
		}
		/* 取消退款由修改状态变成删除记录（V3.0) - 考虑到取消退款后，如果再次申请退款的情况，删除会更利于再次申请退款的处理 */
		/*$this->_refund_mod->edit($refund_id, array('status'=>'CLOSED', 'end_time' => gmtime()));
		
		$data = array(
			'owner_id'	=> $this->visitor->get('user_id'),
			'owner_role'=> 'buyer',
			'refund_id'	=> $refund_id,
			'content'	=> sprintf(Lang::get('cancel_content_change'), $refund['refund_sn']),
			'created'	=> gmtime(),
		);
		if(!$this->_refund_message_mod->add($data)) {
			$this->json_error('handle_fail');
			return;
		}
		*/
		$this->_refund_mod->drop($refund_id);
		$this->_refund_message_mod->drop('refund_id='.$refund_id);
		$this->json_result(array('ret_url' => url('app=refund')), 'cancel_ok');
	}
	
	// 卖家收到的退款
	function receive()
	{
		if(!IS_AJAX)
		{
			$this->import_resource('mobile/jquery.plugins/jquery.infinite.js');
			$this->assign('infiniteParams', json_encode($_GET));
			
        	$this->_config_seo('title', Lang::get('refund_receive')  . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('refund_receive');
			$this->display('refund.receive.html');
		}
		else
		{
			$page    =   $this->_get_page(intval($_GET['pageper']));   //获取分页信息
			$refunds = $this->_refund_mod->find(array(
				'conditions'	=> 'seller_id='.$this->visitor->get('user_id'),
				'order' 		=> 'created desc',
				'limit'			=> $page['limit'],
				'count'   		=> true
			));
			$page['item_count']=$this->_refund_mod->getCount();
			
			$ordergoods_mod = &m('ordergoods');
			foreach($refunds as $key=>$refund)
			{
				if($tradeInfo = $this->_deposit_trade_mod->get(array('conditions' => "tradeNo='{$refund['tradeNo']}'", "fields" => 'bizOrderId'))) 
				{
					$order = $this->_order_mod->get(array(
						'conditions'=>'order_sn='.$tradeInfo['bizOrderId'],'fields'=>'order_id, order_sn, buyer_name, seller_name'));
					
					$order['goods_list'] = $ordergoods_mod->find('order_id='.$order['order_id']);
					
					$refunds[$key]['status_label'] = Lang::get('REFUND_'.strtoupper($refund['status']));
					
					$refunds[$key] = $refunds[$key] + $order;
				}
			}
			
			$this->_format_page($page);
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($refunds), 'totalPage' => $page['page_count']);
			echo json_encode($data);
		}
	}
	
	// 验证是否可以申请退款
	function _available_refund($tradeInfo)
	{		
		/* 验证是否已经添加过退款申请 */
		if($this->_refund_mod->get(array('conditions'=>"tradeNo='{$tradeInfo['tradeNo']}' ",'fields'=>'refund_id'))){

			$this->show_warning('order_applied_refund');
			exit;

		}
		
		/* 验证当前订单是否是当前用户的 */
		$order = $this->_order_mod->get(array(
			'conditions'=>'order_sn='.$tradeInfo['bizOrderId'].' and buyer_id='.$this->visitor->get('user_id'), 'fields'=>'order_id,status,payment_code'));

		if(empty($order)){
			$this->show_warning('refund_order_not_exist');
			exit;

		} 
		/* 如果订单状态是已完成的,或者是已取消的等不能申请退款，只有订单状态是已发货的或者是已付款代发货的，才能申请退款 */
		elseif(!in_array($order['status'], array(ORDER_ACCEPTED, ORDER_SHIPPED))){ 
			$this->show_warning('order_not_apply_refund');
			exit;

		}
	}	

	function _check_post_data($realAmount = array(), $post = array())
	{
		list($realGoodsAmount, $realShippingFee, $realOrderAmount) = $realAmount;

		if((empty($post['refund_goods_fee']) && empty($post['refund_shipping_fee'])) || (floatval($post['refund_goods_fee']) + floatval($post['refund_shipping_fee']))<0)
		{
			$this->show_warning('refund_fee_ge0');
			exit;

		} elseif(floatval($post['refund_goods_fee']) > $realGoodsAmount){

			$this->show_warning('refund_fee_error');
			exit;
		}
		if($post['refund_shipping_fee'] !='' && floatval($post['refund_shipping_fee'])<0)
		{
			$this->show_warning('refund_shipping_fee_ge0');
			exit;
		}

		if(floatval($post['refund_shipping_fee']) > $realShippingFee){

			$this->show_warning('refund_shipping_fee_error');
			exit;
		}
		if(!in_array(trim($post['shipped']), array(0,1,2))) {
			$this->show_warning('select_refund_shipped');
			exit;
		}
		if(empty($post['refund_reason'])){
			$this->show_warning('select_refund_reason');
			exit;
		}
	}
	
	function _getRefundTitle($order_id)
	{
		$ordergoods = $this->_ordergoods_mod->find(array('conditions'=>'order_id='.$order_id,'fields'=>'goods_name'));
		if(count($ordergoods) > 1){
			$ext = Lang::get('and_more');
		} else $ext = '';
		
		$goods = current($ordergoods);
			
		$title = $goods['goods_name'] . $ext;
		
		return $title;
	}
	
	function _getShippedOptions()
	{
		return array(Lang::get('shipped_0'), Lang::get('shipped_1'), Lang::get('shipped_2'));	
	}
	
	function _getRefundReasonOptions()
	{
		return array('缺货', '未按约定时间发货', '收到假货', '商品有质量问题', '商品错发/漏发', '收到的商品破损', '收到的商品描述不符', '其他');
	}
	
	
	/**
     * 上传凭证
     *
     */
    function _upload_files()
    {
        import('uploader.lib');
        $data      = array();
        $file = $_FILES['refund_cert'];
        if ($file['error'] == UPLOAD_ERR_OK && $file !='')
        {
            $uploader = new Uploader();
            $uploader->allowed_type(IMAGE_FILE_TYPE);
            $uploader->addFile($file);
            $uploader->root_dir(ROOT_PATH);

			$refund_cert = $uploader->save('data/files/mall/refund/member_'.$this->visitor->get('user_id'), $uploader->random_filename());
			
			if($refund_cert)
			{
				// 图片压缩处理（如：手机拍照上传图片）
			 	if($file['size'] >= 1024 * 1024) // 1M才压缩
				{
					import('image.func');
					$thumbnail = dirname($refund_cert) . '/' . basename($refund_cert);
					make_thumb(ROOT_PATH . '/' . $refund_cert, ROOT_PATH .'/' . $thumbnail, 500, 500, 85);
				
					$refund_cert = $thumbnail;
				}
				$data['refund_cert'] = $refund_cert;
			}
        }
	
        return $data;
    }

}

?>