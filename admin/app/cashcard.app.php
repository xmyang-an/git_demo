<?php

class CashcardApp extends BackendApp {
	
	var $_cashcard_mod;
	
	function __construct()
	{
		parent::__construct();
		$this->_cashcard_mod = &m('cashcard');
	}
	
	function index()
    {
		$query = $this->get_query_conditions();
		$this->assign('filtered', $query);
        $this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
            'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'
		));
        $this->display('cashcard.index.html');
    }
	
	function get_xml()
	{
        $conditions = '1=1';		 
		$conditions .= $this->get_query_conditions();
		$order = 'id DESC';
        $param = array('cardNo','money','user_name','add_time','printed','active_time','expire_time');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
		$pre_page = $_POST['rp'] ? intval($_POST['rp']) : 10;
		$page   =   $this->_get_page($pre_page);
		$users = $this->_cashcard_mod->find(array(
            'conditions' 	=> $conditions,
            'limit' 		=> $page['limit'],
            'order' => $order,
            'count' => true,
        ));
		$page['item_count'] = $this->_cashcard_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($users as $k => $v){
			$list = array();
			$operation = "<a class='btn red' onclick=\"fg_delete({$k},'cashcard')\"><i class='fa fa-trash-o'></i>删除</a>";
			$operation .= "<span class='btn'><em><i class='fa fa-cog'></i>设置 <i class='arrow'></i></em><ul>";
			$operation .= "<li><a onclick=\"goConfirm('".LANG::get('print_confirm')."','index.php?app=cashcard&act=printed&value=1&id={$k}',true)\">制卡</a></li>";
			$operation .= "<li><a onclick=\"goConfirm('".LANG::get('print_cancel_confirm')."','index.php?app=cashcard&act=printed&value=0&id={$k}',true)\">取消制卡</a></li>";
			$operation .= "</ul>";
			$list['operation'] = $operation;
			$list['name'] = $v['name'];
			$list['cardNo'] = $v['cardNo'];
			$list['password'] = $v['password'];
			$list['money'] = $v['money'];
			$list['user_name'] = $v['user_name'];
			$list['add_time'] = local_date('Y-m-d H:i:s',$v['add_time']);
			$list['printed'] = $v['printed'] == 0 ? '<em class="no"><i class="fa fa-ban"></i>未制卡</em>' : '<em class="yes"><i class="fa fa-check-circle"></i>已制卡</em>';
			$list['active_time'] = local_date('Y-m-d H:i:s',$v['active_time']);
			$list['expire_time'] = local_date('Y-m-d H:i:s',$v['expire_time']);
			$data['list'][$k] = $list;
		}
		$this->flexigridXML($data);
	}

	function get_query_conditions(){
		$conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'cardNo',
                'equal' => '=',
            ),
			array(
                'field' => 'name',
                'equal' => '=',
            ),
			array(
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
				'field' => 'add_time',
            ),array(
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time_end',
				'field' => 'add_time'
            )
        ));
	    if(intval($_GET['active_time'])) {
			(intval($_GET['active_time']) == 1) && $conditions .= ' AND active_time=0 ';
			(intval($_GET['active_time']) == 2) && $conditions .= ' AND active_time>0 ';
		}
		if(intval($_GET['printed'])) {
			(intval($_GET['printed']) == 1) && $conditions .= ' AND printed=0 ';
			(intval($_GET['printed']) == 2) && $conditions .= ' AND printed=1 ';
		}
		return $conditions;
	}
	
	function add()
	{
		if(!IS_POST)
		{
			$this->import_resource(array(
				'script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
           		'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'
			));
			$this->display('cashcard.form.html');
		}
		else
		{
			$name = trim($_POST['name']);
			$password = trim($_POST['password']);
			$quantity = intval($_POST['quantity']);
			$money = floatval(trim($_POST['money']));
			if(($money <= 0) || ($money > 10000))
			{
				$this->json_error('money_error');
				return;
			}
			
			if($quantity <= 0) $quantity = 1;
			if($quantity > 1000) {
				$this->json_error('quantity_limit');
				return;
			}
			
			if($password && strlen($password) < 6 || strlen($password) > 30) {
				$this->json_error('password_len_error');
				return;
			}

			for($i = 1; $i <= $quantity; $i++)
			{
				$data = array(
					'name'			=> $name,
					'cardNo' 		=> $this->_cashcard_mod->genCardNo(),
					'money'			=> $money,
					'password' 		=> $password ? $password : mt_rand(100000,999999),
					'expire_time' 	=> trim($_POST['expire_time']) ? gmstr2time(trim($_POST['expire_time'])) : 0,
					'active_time'	=> 0,
					'add_time'  	=> gmtime(),
					'useId'			=> 0,
					
				);
			
				$this->_cashcard_mod->add($data);
			}		
			$this->json_result('','add_ok');
		}
	}
    
	function drop()
	{ 
		$id = isset($_GET['id']) ? trim($_GET['id']) : '';
		if(!$id)
		{
			$this->json_error('no_such_item');
			exit;
		}
		$ids = explode(',', $id);		
		$this->_cashcard_mod->drop($ids);
		if($this->_cashcard_mod->has_error())
		{
			$this->json_error($this->_cashcard_mod->get_error());
			exit;
		}		
		$this->json_result('','drop_ok');
	}
	
	function printed()
	{ 
		$id = isset($_GET['id']) ? trim($_GET['id']) : '';
		if(!$id)
		{
			$this->json_error('no_such_item');
			exit;
		}
		
		$ids = explode(',', $id);		
		$this->_cashcard_mod->edit('id '.db_create_in($ids), array('printed' => intval($_GET['value'])));
		if($this->_cashcard_mod->has_error())
		{
			$error = current($this->_cashcard_mod->get_error());
			$this->json_error($error['msg']);
			exit;
		}		
		$this->json_result('','set_ok');
	}
	
	function export_csv() 
	{	
		$conditions = '1=1';
		if ($_GET['id'] != '') {
            $ids = explode(',', $_GET['id']);
			$conditions .= ' AND id' . db_create_in($ids);
        }
		$conditions .= $this->get_query_conditions();
		$cashcards = $this->_cashcard_mod->find(array(
			'conditions' 	=> $conditions,
			'order'			=> 'id DESC',
		));
		$member_mod = &m('member');
		foreach($cashcards as $key => $val)
		{
			if($val['useId']) {
				$member = $member_mod->get(array('conditions' => 'user_id='.$val['useId'], 'fields' => 'user_name'));
				$member && $cashcards[$key]['user_name'] = $member['user_name'];
			}
		}
		
		import('excelwriter.lib');
        $excel = new ExcelWriter('utf8', 'cardsexcel');
        if (!$cashcards) {
            $this->show_warning('no_such_item');
            return;
        }

        $cols = array();
        $cols_item = array();
        $cols_item[] = LANG::get('cardNo');
        $cols_item[] = LANG::get('password');
        $cols_item[] = LANG::get('money');
		$cols_item[] = LANG::get('print_status');
        $cols_item[] = LANG::get('expire_time');

        $cols[] = $cols_item;

        if (is_array($cashcards) && count($cashcards) > 0) {
            foreach ($cashcards as $k => $v) {

                $tmp_col = array();
                $tmp_col[] = $v['cardNo'];
                $tmp_col[] = $v['password'];
                $tmp_col[] = $v['money'];
				 $tmp_col[] = $v['printed'] ? LANG::get('printed') : LANG::get('no_print');
                $tmp_col[] = $v['expire_time'] ? local_date('Y-m-d H:i:s', $v['expire_time']) : LANG::get('no_limit');
                $cols[] = $tmp_col;
            }
        }
        $excel->add_array($cols);
        $excel->output();
    }
}
?>