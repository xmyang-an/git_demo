<?php

/* 预存款控制器 */
class DepositApp extends BackendApp
{
	var $_deposit_trade_mod;
	var $_deposit_withdraw_mod;
	var $_deposit_record_mod;
	var $_deposit_account_mod;
	var $_deposit_recharge_mod;
	var $_deposit_setting_mod;
	var $_member_mod;
	
    function __construct()
    {
        $this->DepositApp();
    }

    function DepositApp()
    {
        parent::__construct();
		$this->_deposit_trade_mod    = &m('deposit_trade');
		$this->_deposit_withdraw_mod = &m('deposit_withdraw');
		$this->_deposit_record_mod   = &m('deposit_record');
		$this->_deposit_account_mod  = &m('deposit_account');
		$this->_deposit_recharge_mod = &m('deposit_recharge');
		$this->_deposit_setting_mod  = &m('deposit_setting');
		$this->_member_mod			 = &m('member');
    }
	
	function index()
    {
		
		$query = $this->get_query_conditions();
		$this->assign('filtered', $query);
		$search_options = array(
            'account'   => Lang::get('account'),
            'real_name' => Lang::get('real_name'),
			'user_name' => Lang::get('user_name'),
        );
		$this->assign('search_options', $search_options);
		$this->assign('pay_status_list', array(
			'ON' => Lang::get('yes'),
            'OFF'=> Lang::get('no'),
        ));
		$this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'jquery.plugins/flexigrid.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),

                array(
                    'path' => 'jquery.ui/i18n/' . i18n_code() . '.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'mlselection.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));

        $this->display('deposit.index.html');
    }
	
	function get_account_xml()
	{
		$conditions = '1 = 1';
		$conditions .= $this->get_query_conditions();
        $param = array('account','real_name','money','frozen','pay_status','add_time');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
		$pre_page = $_POST['rp'] ? intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
		$accountlist = $this->_deposit_account_mod->find(array(
			'conditions'	=>	$conditions,
			'limit' 		=>  $page['limit'],
			'order'			=>	$order,
			'count'			=>  true
		));
		$page['item_count'] = $this->_deposit_account_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($accountlist as $k => $v)
		{
			$member = $this->_member_mod->get(array('conditions'=>'user_id='.$v['user_id'],'fields'=>'user_name'));
			$list = array();
			$operation = "<a class='btn red' onclick=\"fg_delete({$k},'deposit')\"><i class='fa fa-trash-o'></i>删除</a>";
			$operation .= "<span class='btn'><em><i class='fa fa-cog'></i>设置 <i class='arrow'></i></em><ul>";
			$operation .= "<li><a href='index.php?app=deposit&act=edit&id={$k}'>编辑</a></li>";
			$operation .= "<li><a href='index.php?app=deposit&act=recharge&id={$k}'>充值</a></li>";
			$operation .= "<li><a href='index.php?app=deposit&act=monthbill&user_id={$v['user_id']}'>月账单</a></li>";
			$operation .= "</ul>";
			$list['operation'] 	= $operation;
			$list['account'] 	= $v['account'];
			$list['real_name'] 	= $v['real_name'];
			$list['user_name'] 	= $member['user_name'];
			$list['money'] 		= $v['money'];
			$list['frozen'] 	= $v['frozen'];
			$list['pay_status'] = $v['pay_status'] == 'OFF' ? '<em class="no"><i class="fa fa-ban"></i>否</em>' : '<em class="yes"><i class="fa fa-check-circle"></i>是</em>';
			$list['add_time'] 	= local_date('Y-m-d H:i:s',$v['add_time']);
			$data['list'][$k] 	= $list;
		}
		$this->flexigridXML($data);
	}

	function get_query_conditions(){
		$conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'add_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'add_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time_end',
            ),array(
                'field' => 'money',
                'name'  => 'money_from',
                'equal' => '>=',
                'type'  => 'numeric',
            ),array(
                'field' => 'money',
                'name'  => 'money_to',
                'equal' => '<=',
                'type'  => 'numeric',
            ),
        ));
		
		$search_name = trim($_GET['search_name']);
		if(!empty($search_name))
		{
			$field = trim($_GET['field']);
			if($field == 'user_name') {
				$member = $this->_member_mod->find(array('conditions'=>"user_name LIKE '%".$search_name."%' ",'fields'=>'user_id'));
				if($member){
					$conditions .= ' AND user_id '. db_create_in(array_keys($member));
				}else{
					$conditions .= ' AND user_id=0 ';
				} 
			}else{
				$conditions .= " AND " . $field . " LIKE '%".$search_name."%' ";
			}
		}
		if($_GET['pay_status'] || in_array(trim($_GET['pay_stauts']), array('ON', 'OFF'))){
			$conditions .= " AND pay_status='".trim($_GET['pay_status'])."' ";
		}
		
		return $conditions;
	}
		
	function export_csv()
	{
		$conditions = '1 = 1';
		$conditions .= $this->get_query_conditions();
		if ($_GET['id'] != '') {
            $ids = explode(',', $_GET['id']);
			$conditions .= ' AND account_id' . db_create_in($ids);
        }
		$accountlist = $this->_deposit_account_mod->find(array(
			'conditions'	=>	$conditions,
			'order'			=>	'account_id'
		));
		
		if(!$accountlist) {
			$this->show_warning('no_such_account');
            return;
		}
		
		foreach($accountlist as $key=>$account)
		{
			$member = $this->_member_mod->get(array('conditions'=>'user_id='.$account['user_id'],'fields'=>'user_name'));
			$accountlist[$key]['user_name'] = $member['user_name'];
		}
		/* xls文件数组 */
		$record_xls = array();		
		$record_title = array(
			'user_name' 	=> 	'会员名',
    		'real_name'		=> 	'真实姓名',
    		'account' 		=> 	'账户名',
			'money' 		=> 	'金钱',
			'frozen' 		=> 	'冻结',
    		'pay_status' 	=> 	'开启余额支付',
			'add_time' 		=> 	'创建时间',
		);
		$folder = 'accountlist_'.local_date('Ymdhis', gmtime());
		$record_xls[] = $record_title;
		foreach($accountlist as $key=>$val)
    	{
			$record_value['user_name']		=	$val['user_name'];
			$record_value['real_name']		=	$val['real_name'];
			$record_value['account']		=	$val['account'];
			$record_value['money']			=	$val['money'];
			$record_value['frozen']			=	$val['frozen'];
			$record_value['pay_status']		=	$val['pay_status'];
			$record_value['add_time']		=	local_date('Y/m/d H:i:s',$val['add_time']);
        	$record_xls[] = $record_value;
    	}
		import('excelwriter.lib');
		$ExcelWriter = new ExcelWriter(CHARSET, $folder);
		$ExcelWriter->add_array($record_xls);
		$ExcelWriter->output();
	}
	
	/* 交易记录 */
	function tradelist()
	{
		$query = $this->get_tradelist_query_conditions();
		$this->assign('filtered', $query);
        $this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js',
		));
		
		$this->assign('trade_type_list', array(
            TRADE_ORDER => '购物订单',
            TRADE_RECHARGE => '充值订单',
			TRADE_DRAW => '提现订单',
			TRADE_CHARGE => '系统扣费',
			TRADE_BUYAPP => '应用订单',
			TRADE_TRANS => '转账订单',
			TRADE_FX => '分销订单',
			TRADE_FX_JINTIE => '津贴订单',
        ));
		
        $this->display('deposit.tradelist.html');
    }
	
	function get_trade_xml()
	{
		$conditions = "merchantId = '".MERCHANTID."' ";
		$conditions .= $this->get_tradelist_query_conditions();
        $param = array('bizOrderId','add_time','tradeNo','title','amount');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
		$recordlist = $this->_deposit_trade_mod->find(array(
			'conditions'	=>	$conditions,
			'order'			=>	$order,
			'limit' 		=>  $page['limit'],
			'count'			=>  true,
		));
		$page['item_count'] = $this->_deposit_trade_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($recordlist as $k => $v)
		{
			// 交易方
			$account = $this->_deposit_account_mod->get(array('conditions'=>'user_id='.$v['buyer_id'],'fields'=>'real_name,account'));
			// 交易的对方
			$partyInfo = $this->_deposit_trade_mod->getPartyInfoByRecord($v['buyer_id'], $v);
			$list = array();
			$operation = "<a class='btn red' onclick=\"fg_delete('{$v['tradeNo']}','deposit','drop_trade')\"><i class='fa fa-trash-o'></i>删除</a>";
			$list['operation'] 	= $operation;
			$list['add_time'] 	= local_date('Y-m-d H:i:s',$v['add_time']);
			$list['bizOrderId'] = $v['bizOrderId'];
			$list['tradeNo'] 	= $v['tradeNo'];
			$list['title'] 		= $v['title'];
			$list['buyer_name'] = $account['real_name'] ? $account['real_name'] : $account['account'];
			$list['party'] 		= $partyInfo['name'];
			$list['amount'] 	= $v['flow'] == 'income' ? '<span style="color:#C00"><strong>+'.$v['amount'].'</strong></span>' : '<span style="color:#03C"><strong>-'.$v['amount'].'</strong></span>';
			$list['status'] 	= Lang::get(strtolower($v['status']));
			$data['list'][$v['tradeNo']] = $list;
		}
		$this->flexigridXML($data);
	}

	function get_tradelist_query_conditions(){
		$conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'bizOrderId',
                'equal' => 'like',
            ),
            array(
                'field' => 'tradeNo',
                'equal' => 'like',
            )
        ));
		
		$member_mod = &m('member');
		if($_GET['buyer_name']){
			$buyer_info = $member_mod->find(array(
				'conditions' => 'user_name LIKE "%'.html_script($_GET['buyer_name']).'%"',
				'fields'     => 'user_id'
			));
	
			if(empty($buyer_info)){
				$buyer_ids = array(-1);
			}
			else{
				$buyer_ids = array_keys($buyer_info);
			}
			
			$conditions .= ' AND buyer_id '.db_create_in($buyer_ids);
		}
		
		if($_GET['seller_name']){
			$seller_info = $member_mod->find(array(
				'conditions' => 'user_name LIKE "%'.html_script($_GET['seller_name']).'%"',
				'fields'     => 'user_id'
			));
			
			if(empty($seller_info)){
				$seller_ids = array(-1);
			}
			else{
				$seller_ids = array_keys($seller_info);
			}
			
			$conditions .= ' AND seller_id '.db_create_in($seller_ids);
		}
		
		if($_GET['bizIdentity'])
		{
			$conditions .= ' AND bizIdentity="'.html_script($_GET['bizIdentity']).'"';
		}
		
		return $conditions;
	}
		
	function drop_trade()
    {
		// 删除记录将导致交易数据不完整，请您在知晓后果后注销以下两行代码，即可执行删除
		$this->json_error('drop_notice');
		return;
		
        $id = (isset($_GET['id']) && $_GET['id'] !='') ? trim($_GET['id']) : '';

        if (!$id){
            $this->json_error('choose_record');
            return;
        }
		
		$ids = explode(',',$id);
		
		/* 不是交易完成或交易关闭的记录不能删除*/
		foreach($ids as $k=>$id) {
			if(!$this->_deposit_trade_mod->get(array('conditions'=>'tradeNo="'.html_script($id).'" AND (status="SUCCESS" OR status="CLOSED") ','fields'=>'trade_id'))) {
				unset($ids[$k]);	
			}
		}
		
        $conditions = " tradeNo " . db_create_in($ids);
		
        if (!$res = $this->_deposit_trade_mod->drop($conditions))
        {
            $this->json_error('drop_failed');
            return;
        }
		
		// 删除相应的收支记录
        $this->_deposit_record_mod->drop($conditions);
		
        $this->json_result('','drop_ok');
    }
	
	function export_trade_csv()
	{
		$conditions = '';
		$conditions .= $this->get_tradelist_query_conditions();
		if ($_GET['id'] != '') {
            $ids = explode(',', $_GET['id']);
			$conditions .= ' AND tradesn' . db_create_in($ids);
        }
		$recordlist = $this->_deposit_trade_mod->find(array(
			'conditions'	=>	"merchantId = '".MERCHANTID."' " . $conditions,
			'order'			=>	'trade_id desc',
		));
		
		/* xls文件数组 */
		$record_xls = array();		
		$record_title = array(
			'add_time' 		=> 	'创建时间',
    		'bizOrderId' 		=> 	'商户订单号',
    		'tradeNo' 		=> 	'交易号',
			'title' => '交易标题',
			'buyer_name' => '交易方',
			'party' => '对方',
			'amount' 		=> 	'金额（元）',
			'status' 		=> 	'状态',
		);
		$folder = 'tradelist_'.local_date('Ymdhis', gmtime());
		$record_xls[] = $record_title;
		foreach($recordlist as $key=>$val)
    	{
			// 交易方
			$account = $this->_deposit_account_mod->get(array('conditions'=>'user_id='.$val['buyer_id'],'fields'=>'real_name,account'));
			// 交易的对方
			$partyInfo = $this->_deposit_trade_mod->getPartyInfoByRecord($val['buyer_id'], $val);
			$record_value['add_time']	=	local_date('Y/m/d H:i:s',$val['add_time']);
			$record_value['bizOrderId']	=	$val['bizOrderId'];
			$record_value['tradeNo']	=	$val['tradeNo'];
			$record_value['title']		=	$val['title'];
			$record_value['buyer_name']	=	$account['real_name'] ? $account['real_name'] : $account['account'];
			$record_value['party']		=	$partyInfo['name'];
			$record_value['amount']		=	$val['flow'] == 'income' ? '+'.$val['amount'] : '-'.$val['amount']; 
			$record_value['status']		=	Lang::get(strtolower($val['status']));
        	$record_xls[] = $record_value;
    	}
		import('excelwriter.lib');
		$ExcelWriter = new ExcelWriter(CHARSET, $folder);
		$ExcelWriter->add_array($record_xls);
		$ExcelWriter->output();
	}
	
	function setting()
	{
		$sys_setting = $this->_deposit_setting_mod->_get_system_setting();
		
		if(!IS_POST)
		{
			foreach($sys_setting as $key=>$val)
			{
				if($val=='0.000') $sys_setting[$key] = 0;
			}
			
			$this->assign('setting', $sys_setting);
			$this->display('deposit.setting.html');
		}
		else
		{
			
			$trade_rate = trim($_POST['trade_rate']);
			$transfer_rate = trim($_POST['transfer_rate']);
			$withdraw_rate = trim($_POST['withdraw_rate']);
			$auto_create_account = intval($_POST['auto_create_account']);
			
			/* 检查比率是否在0-1之间 */
			if(!$this->_check_rate_number(array($trade_rate, $transfer_rate,$withdraw_rate)))
			{
				$this->json_error('number_error');
				return;
			}
			
			$data_setting = array(
				'user_id'		=>	'0', // 系统配置
				'trade_rate'	=>	$trade_rate,
				'transfer_rate' =>	$transfer_rate,
				'withdraw_rate' =>	$withdraw_rate,
				'auto_create_account' => $auto_create_account,
			);
			
			if($sys_setting)
			{
				$this->_deposit_setting_mod->edit($sys_setting['setting_id'], $data_setting);
			}
			else
			{
				$this->_deposit_setting_mod->add($data_setting);
			}
			
			$this->json_result('','edit_ok');			
		}
		
	}
	
	/* 管理员手动给账户充值 */
	function recharge()
	{
		$id = intval($_GET['id']);
		
		if(!$id)
		{
			$this->show_warning('choose_account');
            return;
		}
		
		$account = $this->_deposit_account_mod->get($id);
	
		if(!$account)
		{
			$this->show_warning('account_error');
			return;
		}
		
		if(!IS_POST)
		{
			$this->assign('account', $account);
			$this->display('deposit.recharge.html');
		}
		else
		{
			$money_change = trim($_POST['money_change']);
			$amount 	  = trim($_POST['money']);
			$remark		  = trim($_POST['remark']);
			
			if($amount <= 0)
			{
				$this->json_error('money_error');
				return;
			}

			if(empty($money_change)) {
				$this->json_error('data_no_change');
				return;
			}
			
			if(!in_array($money_change, array('increase','reduce'))){
				$this->json_error('recharge_error');
				return;
			}
			
			if($money_change=='increase') 
			{
				$balance = $account['money'] + $amount;
				empty($remark) && $remark = LANG::get('system_recharge');
			}
			else
			{
				$balance = $account['money'] - $amount;
				empty($remark) && $remark = LANG::get('system_chargeback');
			}
			
			if($balance < 0) {
				$this->json_error('money_error');
				return;
			}

			/* 插入交易记录 */
			$time = gmtime();
			$tradeNo	= $this->_deposit_trade_mod->genTradeNo();
			
			$data_trade = array(
				'tradeNo'		=> $tradeNo,
				'merchantId'	=> MERCHANTID,
				'bizOrderId'	=> $this->_deposit_trade_mod->genTradeNo(12),
				'bizIdentity'	=> TRADE_RECHARGE,
				'buyer_id'		=> $account['user_id'],
				'seller_id'		=> 0,
				'amount'		=> $amount,
				'status'		=> 'SUCCESS',
				'payment_code'  => 'deposit',
				'tradeCat'		=> $money_change=='increase' ? 'RECHARGE' : 'CHARGE',
				'payType'		=> 'INSTANT',
				'flow'			=> $money_change=='increase' ? 'income' : 'outlay',
				'fundchannel'   => Lang::get('deposit'),
				'title'			=> $money_change=='increase' ? LANG::get('recharge') : LANG::get('chargeback'),
				'add_time'		=> $time,
				'pay_time'		=> $time,
				'end_time'		=> $time,
			);
			
			if($this->_deposit_trade_mod->add($data_trade))
			{
				$recharge_result = TRUE;
				
				/* 增加表示充值，则插入充值记录，减少表示扣费，只插入收支记录 */
				if($money_change == 'increase')
				{
					$data_recharge = array(
						'orderId'		=> $data_trade['bizOrderId'],
						'user_id'		=> $account['user_id'],
						'examine'		=> $this->visitor->get('user_name'),
						'is_online'		=> 1,
					);
					
					$recharge_result = $this->_deposit_recharge_mod->add($data_recharge);
				}
	
				if($recharge_result)
				{
					/* 充值成功，插入收支记录，变更用户余额账户 */
					$data_record = array(
						'tradeNo'		=>	$tradeNo,
						'user_id'		=>	$account['user_id'],
						'amount'		=>  $amount,
						'balance'		=>	$balance,
						'tradeType' 	=>  $money_change == 'increase' ? 'RECHARGE' : 'CHARGE',
						'tradeTypeName' => 	$money_change == 'increase' ? LANG::get('recharge') : LANG::get('chargeback'),
						'flow'	    	=>  $money_change == 'increase' ? 'income' : 'outlay',
						'remark'		=>  $remark
					);
					if($this->_deposit_record_mod->add($data_record))
					{
						/* 修改当前用户的账户余额 */
						$this->_deposit_account_mod->edit('user_id='.$account['user_id'], array('money' => $balance));
						
						$this->json_result('','edit_ok');
						return;
					}
				}
				$this->json_error('edit_error');
			}
		}		
	}
	
	function drop()
    {
		// 删除记录将导致交易数据不完整，请您在知晓后果后注销以下两行代码，即可执行删除
		$this->json_error('drop_notice');
		return;	
	
        $id = (isset($_GET['id']) && $_GET['id'] !='') ? trim($_GET['id']) : '';
        
        $ids = explode(',',$id);
        if (!$id)
        {
            $this->json_error('choose_record');
            return;
        }
        
        $conditions = " account_id " . db_create_in($ids);
        if (!$res = $this->_deposit_account_mod->drop($conditions))
        {
            $this->json_error('drop_failed');
            return;
        }
        $this->json_result('','drop_ok');
    }
	
    function edit()
    {
        $id = (isset($_GET['id']) && $_GET['id'] !='') ? intval($_GET['id']) : '';
        
        if (!$id)
        {
            $this->json_error('choose_record');
            return;
        }

        if (!IS_POST)
        {
            $account = $this->_deposit_account_mod->get($id);
            $this->assign('account', $account);
            $this->display('deposit.account.html');
        }
        else
        {
			$password = trim($_POST['password']);
			
            $account = array(
				'account'	=>	trim($_POST['account']),
				'password'	=>	md5(trim($_POST['password'])),
				'real_name'	=>	trim($_POST['real_name']),
				'pay_status'=>	strtoupper($_POST['pay_status'])=='ON' ? 'ON' : 'OFF',
			);
			if(empty($password)) {
				unset($account['password']);
			}
			
			if(empty($account['real_name'])) {
				$this->json_error('real_name_not_empty');
				return;
			}
			$this->_deposit_account_mod->edit($id, $account);
			if($this->_deposit_account_mod->has_error())
			{
				$error = current($this->_deposit_account_mod->get_error());
				$this->json_error($error['msg']);
				return;
			}
			$this->json_result('','edit_ok');
        }
    }
	
	/* 提现记录 */
	function drawlist()
	{
		$this->assign('status_list', array(
			'WAIT_ADMIN_VERIFY'	=>	LANG::get('wait_admin_verify'),
			'SUCCESS' => Lang::get('success'),
        ));	
		$query = $this->get_drawlist_query_conditions();
		$this->assign('filtered', $query);
		$search_options = array(
            'tradeNo'   => Lang::get('tradeNo'),
            'user_name' => Lang::get('user_name'),
        );
		$this->assign('search_options', $search_options);
		$this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'jquery.plugins/flexigrid.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),

                array(
                    'path' => 'jquery.ui/i18n/' . i18n_code() . '.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'mlselection.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
        $this->display('deposit.drawlist.html');
    }
	
	function get_drawlist_xml()
	{
		$conditions = '';
		$conditions .= $this->get_drawlist_query_conditions();
        $param = array('orderId','tradeNo','add_time','amount','status');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
		$recordlist = $this->_deposit_withdraw_mod->find(array(
			'conditions' 	=>  " merchantId = '".MERCHANTID."' " . $conditions,
			'order'			=>	$order,
			'join'			=>  'has_trade',
			'limit' 		=>  $page['limit'],
			'count'			=>  true
		));
		$page['item_count'] = $this->_deposit_withdraw_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($recordlist as $k => $v)
		{
			$chargeTrade = $this->_deposit_trade_mod->get('bizOrderId='.$v['tradeNo']);
			if(!empty($chargeTrade)){
				$charge = $chargeTrade['amount'];
			}
			else{
				$charge = 0;
			}
			$member = $this->_member_mod->get(array('conditions'=>'user_id='.$v['user_id'],'fields'=>'user_name'));
			if($v['status']=='WAIT_ADMIN_VERIFY'){
				$status_label = '<span style="color:#f60">'.Lang::get(strtolower($v['status'])).'</span>';
			}elseif($v['status']=='CLOSED'){
				$status_label = '<span style="color:#999">'.Lang::get(strtolower($v['status'])).'</span>';
			}else{
				$status_label = '<span style="color:#2F792E">'.Lang::get(strtolower($v['status'])).'</span>';
			}
			$card_info = unserialize($v['card_info']);
			$list = array();
			$operation = "<a class='btn red' onclick=\"fg_delete('{$k}','deposit','drop_draw')\"><i class='fa fa-trash-o'></i>删除</a>";
			if($v['status']=='WAIT_ADMIN_VERIFY'){
				$info = $card_info['bank_name'].','.LANG::get($card_info['type']).','.$card_info['account_name'].','.$card_info['num'].','.$card_info['open_bank'];
				$operation .= "<a class='btn orange' onclick=\"fg_withdraw_verify('{$v['tradeNo']}','{$info}')\"><i class='fa fa-check-square'></i>审核</a>";
			}
			$list['operation'] 	= $operation;
			$list['add_time'] 	= local_date('Y-m-d H:i:s',$v['add_time']);
			$list['orderId'] 	= $v['orderId'];
			$list['tradeNo'] 	= $v['tradeNo'];
			$list['user_name'] 	= $member['user_name'];	
			$list['name'] 		= Lang::get('withdraw');
			$list['amount'] 	= $v['amount'];
			$list['charge'] 	= $charge;
			$list['card_info'] 	= $card_info['bank_name'].'<span class="gray">( '.LANG::get($card_info['type']).','.$card_info['account_name'].','.$card_info['num'].','.$card_info['open_bank'].' )</span>';
			$list['status'] 	= $status_label;
			$data['list'][$v['tradeNo']] = $list;
         }
		$this->flexigridXML($data);
	}


	function get_drawlist_query_conditions(){
		$conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'orderId',
                'equal' => 'like',
            ),
            array(
                'field' => 'tradeNo',
                'equal' => 'like',
            ),
			array(
                'field' => 'add_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'add_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time_end',
            ),array(
                'field' => 'amount',
                'name'  => 'amount_from',
                'equal' => '>=',
                'type'  => 'numeric',
            ),array(
                'field' => 'amount',
                'name'  => 'amount_to',
                'equal' => '<=',
                'type'  => 'numeric',
            ),
        ));
		$search_name = trim($_GET['search_name']);
		if(!empty($search_name))
		{
			$field = trim($_GET['field']);
			if($field == 'user_name') {
				$member = $this->_member_mod->find(array('conditions'=>"user_name LIKE '%".$search_name."%' ",'fields'=>'user_id'));
				if($member){
					$conditions .= ' AND user_id '. db_create_in(array_keys($member));
				}else{
					$conditions .= ' AND user_id=0 ';
				} 
			}elseif($field == 'tradeNo') {
				$conditions .= " AND tradeNo LIKE '%".$search_name."%' ";
			}
		}
		if($_GET['status']){
			$conditions .= " AND status='".trim($_GET['status'])."' ";
		}
		return $conditions;
	}
		
	/* 提现审核 */
	function withdraw_verify()
	{
		$tradeNo = trim($_GET['tradeNo']);
		
		if(empty($tradeNo)) {
			$this->json_error('verify_error');
            return;
		}
		
		$draw = $this->_deposit_withdraw_mod->get(array(
			'conditions'	=>	" tradeNo='".$tradeNo."' ",
			'join'			=>  ' has_trade',
			'fields'		=>	' tradeNo, user_id, status, amount',
		));
		
		
		/* 不是等待审核的提现，不允许审核 */
		if(!$draw || ($draw['status'] != 'WAIT_ADMIN_VERIFY'))
		{
			$this->json_error('verify_error');
            return;
		}
		$chargeTrade = $this->_deposit_trade_mod->get('bizOrderId='.$tradeNo);
		if(!empty($chargeTrade)){
			$charge = $chargeTrade['amount'];
		}
		else{
			$charge = 0;
		}
		// 变更交易状态
		if($this->_deposit_trade_mod->edit("tradeNo='{$tradeNo}' OR bizOrderId='{$tradeNo}'", array('status' => 'SUCCESS', 'end_time' =>  gmtime())))
		{
			/* 扣减当前用户的冻结金额 */
			if($this->_deposit_account_mod->_update_deposit_frozen($draw['user_id'], $draw['amount']+$charge, 'reduce')){
				$this->json_result('', 'verify_ok');
            	return;
			}
			else
			{
				/* 在此可以考虑做回滚操作，目前暂不考虑 */	
				$this->json_error('verify_error');
            	return;
			}
		}
		$this->json_error('verify_error');
        return;
	}
	
	// 管理员拒绝提现
	function withdraw_refuse()
	{
		$tradeNo = trim($_GET['tradeNo']);
		
		if(empty($tradeNo)) {
			$this->json_error('verify_error');
            return;
		}
		
		$draw = $this->_deposit_withdraw_mod->get(array(
			'conditions'	=>	" tradeNo='".$tradeNo."' ",
			'join'			=>  ' has_trade',
			'fields'		=>	' tradeNo,user_id,status,amount',
		));
		
		
		/* 不是等待审核的提现，不允许审核 */
		if(!$draw || ($draw['status'] != 'WAIT_ADMIN_VERIFY'))
		{
			$this->json_error('verify_error');
            return;
		}
		
		$remark = trim($_GET['remark']);
		
		if(empty($remark)) {
			$this->json_error('refuse_remark_empty');
			return;
		}
		
		$chargeTrade = $this->_deposit_trade_mod->get('bizOrderId='.$tradeNo);
		if(!empty($chargeTrade)){
			$charge = $chargeTrade['amount'];
		}
		else{
			$charge = 0;
		}
		
		$time = gmtime();
		
		// 变更交易状态为交易关闭
		if($this->_deposit_trade_mod->edit("tradeNo='{$tradeNo}' OR bizOrderId='{$tradeNo}'", array('status'=>'CLOSED', 'end_time' => $time)))
		{
			/* 管理员拒绝增加备注 */
			$this->_deposit_record_mod->edit("tradeNo='{$tradeNo}' AND tradeType='WITHDRAW' AND user_id=" . $draw['user_id'], array('remark'=>$remark));
		
			$return  = $draw['amount']+$charge;
			/* 扣减当前用户的冻结金额 */
			if($this->_deposit_account_mod->_update_deposit_frozen($draw['user_id'], $return, 'reduce')){
				
				/* 将冻结金额退回到账户余额（变更账户余额）*/ 
				$data_record = array(
					'tradeNo'		=>	$tradeNo,
					'user_id'		=>	$draw['user_id'],
					'amount'		=>	$return,
					'balance'		=>  $this->_deposit_account_mod->_get_deposit_balance($draw['user_id']) + $draw['amount'],
					'tradeType'		=>  'TRANSFER',
					'tradeTypeName'	=>	sprintf('%s(包含手续费)',Lang::get('draw_return')),
					'flow'			=>  'income',
					'remark'		=>	$remark,
				);
				if($this->_deposit_record_mod->add($data_record)) {
					$this->_deposit_account_mod->_update_deposit_money($draw['user_id'], $return, 'add');
				}
				
				$this->json_result('','refuse_draw_ok');
				return;
			}
			else
			{
				/* 在此可以考虑做回滚操作，目前暂不考虑 */
				$this->json_error('verify_error');
				return;
			}
		}
		else 
		{
			$this->json_error('verify_error');
		}
	}
	
	function export_draw_csv()
	{
		$conditions = '';
		$conditions .= $this->get_drawlist_query_conditions();
		if ($_GET['id'] != '') {
            $ids = explode(',', $_GET['id']);
			$conditions .= ' AND tradeNo' . db_create_in($ids);
        }
		
		$recordlist = $this->_deposit_withdraw_mod->find(array(
			'conditions' 	=>  ' 1=1 ' . $conditions,
			'join'			=>  ' has_trade',
			'order'			=>	' withdraw_id desc',
		));
		
		$lang_title = array(
			'add_time' 			=> '申请时间',
			'tradeNo'			=> '交易号',
			'account_name'		=> '收款人姓名',
			'num' 				=> '收款人银行账号',
			'bank_name' 		=> '开户行',
			'amount'			=> '金额',
			'status'			=> '状态',
			'remark'			=> '提现备注',
		);
		
		/* xls文件数组 */
		$record_xls = $record_value = array();

		$folder = 'drawlist_'.local_date('YmdHis', gmtime());
		
		$record_xls[]  = $lang_title;
		foreach($recordlist as $key=>$record)
		{
			$record_value['add_time']     	= local_date('Y-m-d H:i:s', $record['add_time']);
			$record_value['tradeNo']	    = $record['tradeNo'];
			$card_info 						= unserialize($record['card_info']);
			$record_value['account_name'] 	= $card_info['account_name'];
			$record_value['num']			= $card_info['num'];
			$record_value['bank_name']		= $card_info['bank_name'] . $card_info['open_bank'];
			$record_value['amount']			= $record['amount'];
			$record_value['status']  		= Lang::get(strtolower($record['status']));
			$record_value['remark']  		= $record['buyer_remark'];
			$record_xls[] = $record_value;
		}
		import('excelwriter.lib');
		$ExcelWriter = new ExcelWriter(CHARSET, $folder);
		$ExcelWriter->add_array($record_xls);
		$ExcelWriter->output();
	}
	
	function drop_draw()
    {
		$this->json_error('drop_notice');
		return;
		
        $id = (isset($_GET['id']) && $_GET['id'] !='') ? trim($_GET['id']) : '';

        if (!$id){
            $this->json_error('choose_record');
            return;
        }
		
		$ids = explode(',',$id);
		
		/* 不是交易完成的提现不能删除 */
		foreach($ids as $k=>$id) {
			if(!$this->_deposit_withdraw_mod->get(array('conditions'=>'withdraw_id='.$id,'fields'=>'withdraw_id'))) {
				unset($ids[$k]);	
			}
		}
        
        $conditions = " withdraw_id " . db_create_in($ids);
        if (!$res = $this->_deposit_withdraw_mod->drop($conditions))
        {
            $this->json_error('drop_failed');
            return;
        }
        $this->json_result('','drop_ok');
    }
	
	/* 充值记录 */
	function rechargelist()
	{
		$query = $this->get_rechargelist_query_conditions();
		$this->assign('filtered', $query);
		$search_options = array(
            'tradeNo'   => Lang::get('tradeNo'),
            'user_name' => Lang::get('user_name'),
        );
		$this->assign('search_options', $search_options);
		$this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'jquery.plugins/flexigrid.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),

                array(
                    'path' => 'jquery.ui/i18n/' . i18n_code() . '.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'mlselection.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
		$this->assign('status_list', array(
			'WAIT_ADMIN_VERIFY'	=>	LANG::get('wait_admin_verify'),
			'SUCCESS' => Lang::get('success'),
			'PENDING' => Lang::get('pending'),
			'CLOSED'  => Lang::get('closed'),
        ));
		$this->assign('recharge_method', array(
			0 => Lang::get('offline'),
			1 => Lang::get('online')
		));
		
        $this->display('deposit.rechargelist.html');
    }
	
	function get_rechargelist_xml()
	{
		$conditions = '1 = 1';
		$conditions .= $this->get_rechargelist_query_conditions();
        $param = array('tradeNo','add_time','name','card_info','amount','status');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
		$pre_page = $_POST['rp'] ? intval($_POST['rp']) : 10;
		$page   =   $this->_get_page($pre_page);
		$recordlist = $this->_deposit_recharge_mod->find(array(
			'conditions'	=>	$conditions,
			'order'			=>	$order,
			'join'			=>  'has_trade',
			'limit' 		=>  $page['limit'],
			'count'			=>  true
		));
		$page['item_count'] = $this->_deposit_recharge_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach($recordlist as $k => $v){
			$member = $this->_member_mod->get(array('conditions'=>'user_id='.$v['user_id'],'fields'=>'user_name'));
			$list = array();
			$operation = "<a class='btn red' onclick=\"fg_delete({$k},'deposit','drop_recharge')\"><i class='fa fa-trash-o'></i>删除</a>";
			$list['operation'] 	= $operation;
			$list['add_time'] 	= local_date('Y-m-d H:i:s',$v['add_time']);
			$list['orderId'] 	= $v['orderId'];
			$list['tradeNo'] 	= $v['tradeNo'];
			$list['user_name'] 	= $member['user_name'];
			$list['name'] 		= Lang::get('recharge');
			$list['amount'] 	= $v['amount'];
			$list['is_online'] 	= $v['is_online'] ? Lang::get('online'):Lang::get('offline');
			$list['status'] 	= Lang::get(strtolower($v['status']));
			$list['examine'] 	= $v['examine'];
			$data['list'][$k] 	= $list;
		}
		$this->flexigridXML($data);
	}

	function get_rechargelist_query_conditions()
	{
        $conditions = $this->_get_query_conditions(array(
			array(
                'field' => 'add_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'add_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time_end',
            ),array(
                'field' => 'amount',
                'name'  => 'amount_from',
                'equal' => '>=',
                'type'  => 'numeric',
            ),array(
                'field' => 'amount',
                'name'  => 'amount_to',
                'equal' => '<=',
                'type'  => 'numeric',
            ),
			array(
				'field' => 'is_online',
				'equal' => '=',
				'type'  => 'numeric',
			),
        ));
		$search_name = trim($_GET['search_name']);
		if(!empty($search_name))
		{
			$field = trim($_GET['field']);
			if($field == 'user_name') {
				$member = $this->_member_mod->find(array('conditions'=>"user_name LIKE '%".$search_name."%' ",'fields'=>'user_id'));
				if($member){
					$conditions .= ' AND user_id '. db_create_in(array_keys($member));
				}else{
					$conditions .= ' AND user_id=0 ';
				} 
			}elseif($field == 'tradeNo') {
				$conditions .= " AND tradeNo LIKE '%".$search_name."%' ";
			}
		}
		if($_GET['status']){
			$conditions .= " AND status='".trim($_GET['status'])."' ";
		}
		return $conditions;
	}
		
	function drop_recharge()
    {
		$this->json_error('drop_notice');
		return;
		
        $id = (isset($_GET['id']) && $_GET['id'] !='') ? trim($_GET['id']) : '';

        if (!$id){
            $this->json_error('choose_record');
            return;
        }
		
		$ids = explode(',',$id);
		
		/* 不是交易完成或者待付款的充值不能删除 */
		foreach($ids as $k=>$id) {
			if(!$this->_deposit_recharge_mod->get(array('conditions'=>'recharge_id='.$id,'fields'=>'recharge_id'))) {
				unset($ids[$k]);
			}
		}
        
        $conditions = " recharge_id " . db_create_in($ids);
        if (!$res = $this->_deposit_recharge_mod->drop($conditions))
        {
            $this->json_error('drop_failed');
            return;
        }
        $this->json_result('','drop_ok');
    }
	
	function export_recharge_csv()
	{
		$conditions = '1 = 1';
		$conditions .= $this->get_rechargelist_query_conditions();
		if ($_GET['id'] != '') {
            $ids = explode(',', $_GET['id']);
			$conditions .= ' AND recharge_id' . db_create_in($ids);
        }
		$recordlist = $this->_deposit_recharge_mod->find(array(
			'conditions'	=>	$conditions,
			'order'			=>	'add_time desc',
			'join'			=>  'has_trade'
		));
		
		/* xls文件数组 */
		$record_xls = array();		
		$record_title = array(
			'add_time' 		=> 	'创建时间',
    		'orderId' 		=> 	'商户订单号',
    		'tradeNo' 		=> 	'用户名',
			'user_name' 	=> 	'用户名',
			'name' 			=> '名称',
			'amount' 		=> 	'金额',
			'is_online' 	=> '充值方式',
			'status' 		=> 	'状态',
			'examine' 		=> '审批员',
		);
		$folder = 'rechargelist_'.local_date('Ymdhis', gmtime());
		$record_xls[] = $record_title;
		foreach($recordlist as $key=>$val)
    	{
			$member = $this->_member_mod->get(array('conditions'=>'user_id='.$val['user_id'],'fields'=>'user_name'));
			$record_value['add_time']	=	local_date('Y/m/d H:i:s',$val['add_time']);
			$record_value['orderId']	=	$val['orderId'];
			$record_value['tradeNo']	=	$val['tradeNo']; 
			$record_value['user_name']	=	$member['user_name'];
			$record_value['name']		=	Lang::get('recharge');
			$record_value['amount']		=	$val['amount']; 
			$record_value['is_online']	=	$val['is_online'] ? Lang::get('online') : Lang::get('offline');
			$record_value['status']		=	Lang::get(strtolower($val['status']));
			$record_value['examine']	=	$val['examine'];
        	$record_xls[] = $record_value;
    	}
		import('excelwriter.lib');
		$ExcelWriter = new ExcelWriter(CHARSET, $folder);
		$ExcelWriter->add_array($record_xls);
		$ExcelWriter->output();
	}
	
	/* 月账单下载 */
	function monthbill()
	{
		$user_id = !empty($_GET['user_id']) ? intval($_GET['user_id']) : 0;
		
		if(!$user_id) {
			return;
		}
		$member = $this->_member_mod->get(array('conditions'=>'user_id='.$user_id,'fields'=>'user_id,user_name'));
		$this->assign('member', $member);
	 	$this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js',
		));
        $this->display('deposit.monthbill.html');
    }
	
	function get_monthbill_xml()
	{
		$user_id = intval($_GET['user_id']);
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
		$monthbill = $this->_deposit_record_mod->find(array(
			'conditions'	=>	"status = 'SUCCESS' AND (end_time > 0) AND user_id = ".$user_id,
			'order'			=>	'record_id DESC',
			'join'			=>  'has_trade',
			'fields'		=>	'deposit_trade.end_time, this.*',
		));
		$bill_list = array();
		/* 按月进行归类 */
		if($monthbill)
		{
			foreach($monthbill as $key => $bill)
			{
				$year_month = local_date('Y-m', $bill['end_time']);
				$bill_list[$year_month][$bill['flow'].'_money'] += $bill['amount'];
				$bill_list[$year_month][$bill['flow'].'_count'] += 1;
				
				/* 如果是支出，判断是否是服务费 */
				if($bill['flow'] == 'outlay' && ($bill['tradeType'] == 'SERVICE'))
				{
					$bill_list[$year_month][$bill['tradeType'].'_money'] += $bill['amount'];
					$bill_list[$year_month][$bill['tradeType'].'_count'] += 1;
				}
			}
		}
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = count($bill_list);
		foreach ($bill_list as $k => $v)
		{
			$list = array();
			$operation = "<a class='btn green' href='index.php?app=deposit&act=downloadbill&user_id={$user_id}&month={$k}'><i class='fa fa-download'></i>下载</a>";
			$list['operation']    = $operation;
			$list['month'] 		  = $k;
			$list['income_count'] = $v['income_count'] ? $v['income_count'] : 0;
			$list['income_money'] = $v['income_money'] ? $v['income_money'] : 0;
			$list['outlay_count'] = $v['outlay_count'] ? $v['outlay_count'] : 0;
			$list['outlay_money'] = $v['outlay_money'] ? $v['outlay_money'] : 0;
			$list['charge_count'] = $v['SERVICE_count'] ? $v['SERVICE_count'] : 0;
			$list['charge_money'] = $v['SERVICE_money'] ? $v['SERVICE_money'] : 0;
			$data['list'][$k] = $list;
		}
		$this->flexigridXML($data);
	}
	
	/* 下载某个用户某个月的对账单 */
	function downloadbill()
	{
		$month = trim($_GET['month']);
		$user_id = !empty($_GET['user_id']) ? intval($_GET['user_id']) : 0;
		
		if(empty($month) || !$user_id) return;
		
		$this->_deposit_account_mod->downloadbill($user_id, $month);
	}	
	
	/*检查账户的唯一性*/
    function check_account()
    {
          $account = empty($_GET['account']) ? null : trim($_GET['account']);
		  $id 	   = intval($_GET['id']);
		  
		  
          if (!$account || !$id)
          {
              echo ecm_json_encode(false);
              return ;
          }
		  
		  if(!is_email($account) && !is_mobile($account)) {
			  echo ecm_json_encode(Lang::get('note_account'));
              return ;
		  }
		  
		  $deposit_account = $this->_deposit_account_mod->get(array('conditions'=>'account_id !='.$id." AND account='".$account."' ", 'fields'=>'account_id'));
		  
		  if(!$deposit_account) {
			  echo ecm_json_encode(true);
			  return;
		  }
		  echo ecm_json_encode(false);
    }
	
	function _check_rate_number($data)
	{
		if(!is_array($data)) $data = array($data);
		
		foreach($data as $rate)
		{
			if($rate !='' && (!is_numeric($rate) || $rate>1 || $rate<0))
			{
				return false;
			}
		}
		return true;
	}
}

?>