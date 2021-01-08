<?php

class BankApp extends MemberbaseApp
{
	var $_bank_mod;
	
	/* 构造函数 */
    function __construct()
    {
         $this->BankApp();
    }

    function BankApp()
    {
        parent::__construct();
		$this->_bank_mod = &m('bank');
    }
    function index()
    {
		if(!IS_AJAX)
		{
			$this->import_resource(array(
				'script' => array(
					array(
						'path' => 'mobile/jquery.plugins/jquery.form.min.js',
						'attr' => ''
					),
					array(
						'path' => 'mobile/jquery.plugins/weui/js/jquery-weui.min.js',
						'attr' => ''
					),
					array(
						'path' => 'mobile/jquery.plugins/jquery.infinite.js',
						'attr' => ''
					)
				),
				'style' =>  'mobile/jquery.plugins/weui/lib/weui.min.css,mobile/jquery.plugins/weui/css/jquery-weui.min.css',
			));
			$this->assign('infiniteParams', json_encode($_GET));
			
			$this->_config_seo('title', Lang::get('bank_list') . ' - ' . Conf::get('site_title'));
			$this->_get_curlocal_title('bank_list');
			$this->display('bank.index.html');
		}
		else
		{
			$page = $this->_get_page(intval($_GET['pageper']));
			$bank_list = $this->_bank_mod->find(array(
				'conditions'=>'user_id='.$this->visitor->get('user_id'),
            	'limit'         => $page['limit'],
				'count'         => true
			));
			$page['item_count'] = $this->_bank_mod->getCount();
        	$this->_format_page($page);
			
			if(!empty($bank_list)){
				foreach($bank_list as $key=>$val)
				{
					$bank_list[$key]['num'] = formatBankNumber($val['num']);
					$bank_list[$key]['type_label'] = Lang::get($val['type']);
				}
			}
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($bank_list), 'totalPage' => $page['page_count']);
			echo json_encode($data);
		}
    }
	
	function add()
	{
		if(!IS_POST)
		{
			$this->assign('bank_list', $this->_get_bank_inc());
		
			$this->import_resource('mobile/jquery.plugins/jquery.form.min.js');
			
			$this->_config_seo('title', Lang::get('bank_add') . ' - ' . Conf::get('site_title'));
			$this->_get_curlocal_title('bank_add');
			$this->display('bank.form.html');
		}
		else
		{
			$short_name = trim($_POST['short_name']);
			$account_name = trim($_POST['account_name']);
			$type	= trim($_POST['type']);
			$num 	= trim($_POST['num']);
			
			if(empty($short_name)) {
				$this->json_error('short_name_error');
				return;
			}
			if(empty($num)) {
				$this->json_error('num_empty');
				return;
			}
			if(empty($account_name) || strlen($account_name)<6 || strlen($account_name)>30) {
				$this->json_error('account_name_error');
				return;
			}
			/*if(!in_array($type, array('debit','credit'))){
				$this->json_error('type_error');
				return;
			}*/
			$bank_name = $this->_get_bank_name($short_name);
			if(empty($bank_name))
			{
				$this->json_error('bank_name_error');
				return;
			}
		
			$data = array(
				'user_id'		=>	$this->visitor->get('user_id'),
				'bank_name'		=>	$bank_name,
				'short_name'	=>	strtoupper($short_name),
				'account_name'	=>	$account_name,
				'open_bank'		=>  trim($_POST['open_bank']),
				'type'			=> 	$type,
				'num'			=>	$num,
			);
			
			if(!$this->_bank_mod->add($data)){
				$this->json_error('add_error');
				return;
			}
			$this->json_result('', 'add_ok');
		}
	}
	
	function edit()
	{
		$bid = intval($_GET['bid']);
		
		if(!IS_POST)
		{
			if($bid) 
			{
				$card = $this->_bank_mod->get($bid);
			}
			
			if(!$card) {
				$this->show_warning('edit_error');
				return;
			}
		
			$this->assign('bank_list', $this->_get_bank_inc());
			$this->assign('card', $card);
			
			$this->import_resource('mobile/jquery.plugins/jquery.form.min.js');

			$this->_config_seo('title', Lang::get('bank_edit') . ' - ' . Conf::get('site_title'));
			$this->_get_curlocal_title('bank_edit');
			$this->display('bank.form.html');
		}
		else
		{
			$short_name = trim($_POST['short_name']);
			$account_name = trim($_POST['account_name']);
			$type	= trim($_POST['type']);
			$num 	= trim($_POST['num']);
			
			if(empty($short_name)) {
				$this->json_error('short_name_empty');
				return;
			}
			if(empty($num)) {
				$this->json_error('num_empty');
				return;
			}
			if(empty($account_name) || strlen($account_name)<6 || strlen($account_name)>90) {
				$this->json_error('account_name_error');
				return;
			}
			/*if(!in_array($type, array('debit','credit'))){
				$this->json_error('type_error');
				return;
			}*/

			$bank_name = $this->_get_bank_name($short_name);
			if(empty($bank_name))
			{
				$this->json_error('bank_name_error');
				return;
			}
			
			$data = array(
				'user_id'		=>	$this->visitor->get('user_id'),
				'bank_name'		=>	$bank_name,
				'short_name'	=>	strtoupper($short_name),
				'account_name'	=>	$account_name,
				'open_bank'		=>  trim($_POST['open_bank']),
				'type'			=> 	$type,
				'num'			=>	$num,
			);

			if(!$this->_bank_mod->edit($bid, $data)){
				$this->json_error('edit_error');
				return;
			}
			$this->json_result('', 'edit_ok');
		}
	}
	
	function drop()
	{
		$bid = intval($_GET['bid']);
		if(!$bid)
		{
			$this->json_error('no_such_bank');
			return;
		}
		
		if(!$this->_bank_mod->drop("user_id=".$this->visitor->get('user_id').' AND bid='.$bid))
		{
			$this->json_error('drop_bank_error');
			return;
		}
		$this->json_result('', 'drop_ok');
	}
	
	function _check_short_name($short_name)
	{
		$bank_list = $this->_get_bank_inc();
		
		if(!is_array($bank_list) || count($bank_list)<1){
			return false;
		}
		
		foreach($bank_list as $key=>$bank)
		{
			if(strtoupper($short_name)==strtoupper($key)) {
				return true;
			}
		}
		return false;
	}
	
	function _get_bank_name($short_name)
	{
		if(!$this->_check_short_name($short_name)) return '';
		$bank_list = $this->_get_bank_inc();
		return $bank_list[$short_name];
	}
}

?>
