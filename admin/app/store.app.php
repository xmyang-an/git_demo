<?php

/* 店铺控制器 */
class StoreApp extends BackendApp
{
    var $_store_mod;

    function __construct()
    {
        $this->StoreApp();
    }

    function StoreApp()
    {
        parent::__construct();
        $this->_store_mod =& m('store');
    }

	function index()
    {
		$sgrade_mod =& m('sgrade');
        $grades = $sgrade_mod->get_options();
        $this->assign('sgrades', $grades);
		$query = $this->get_query_conditions();
		$this->assign('filtered', $query);
        $this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js,inline_edit.js',
		));
        $this->display('store.index.html');
    }
	
	function get_xml()
	{
		if($_GET['wait_verify']==3)
		{
			$conditions = empty($_GET['wait_verify']) ? "state <> '" . STORE_APPLYING . "'" : "state = '" . STORE_REJECT . "'";
		}else{
			$conditions = empty($_GET['wait_verify']) ? "state <> '" . STORE_APPLYING . "'" : "state = '" . STORE_APPLYING . "'";
		}
		
		$conditions .= $this->get_query_conditions();
		$order = 'sort_order asc,store_id DESC';
        $param = array('user_name','owner_name','store_name','region_name','cate_name','sgrade','add_time','end_time','state','sort_order','recommended','enable_distribution','distribution_1','distribution_2','distribution_3');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
		
		list($timeConditoins) = $this->getConditions();
		
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
		$stores = $this->_store_mod->find(array(
            'conditions' => $conditions.$timeConditoins,
            'join'  => 'belongs_to_user',
            'fields'=> 'this.*,member.user_name',
            'limit' => $page['limit'],
            'count' => true,
            'order' => $order
        ));
		$page['item_count'] = $this->_store_mod->getCount();
        $sgrade_mod =& m('sgrade');
        $grades = $sgrade_mod->get_options();
        $states = array(
            STORE_APPLYING  => LANG::get('wait_verify'),
            STORE_OPEN      => Lang::get('open'),
            STORE_CLOSED    => Lang::get('close'),
			STORE_REJECT	=> '拒绝',
        );
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($stores as $k => $v){
			$list = array();
			$operation = "<a class='btn red' onclick=\"fg_delete({$k},'store')\"><i class='fa fa-trash-o'></i>删除</a>";
			if(empty($_GET['wait_verify']))
			{
				$operation .= "<span class='btn'><em><i class='fa fa-cog'></i>设置 <i class='arrow'></i></em><ul>";
				$operation .= "<li><a href='" .SITE_URL."/index.php?app=store&id=".$k. "' target=\"_blank\">查看</a></li>";
				$operation .= "<li><a href='index.php?app=store&act=edit&id={$k}'>编辑</a></li>";
				$operation .= "</ul>";
			}
			else
			{
				$operation .= "<a class='btn orange' href='index.php?app=store&act=view&id={$k}'><i class='fa fa-check'></i>审核</a>";
			}
			$list['operation'] = $operation;
			$list['user_name'] = $v['user_name'];
			$list['owner_name'] = $v['owner_name'];
			$list['store_name'] = $v['store_name'];
			$list['region_name'] = $v['region_name'];
			$list['sgrade'] = $grades[$v['sgrade']];
			$list['add_time'] = local_date('Y-m-d',$v['add_time']);

			$list['state'] = $states[$v['state']];
			$list['sort_order'] = '<span ectype="inline_edit" fieldname="sort_order" fieldid="'.$k.'" datatype="pint" class="editable" title="'.Lang::get('editable').'">'.$v['sort_order'].'</span>';
			$list['recommended'] = $v['recommended'] == 0 ? '<em class="no" ectype="inline_edit" fieldname="recommended" fieldid="'.$k.'" fieldvalue="0" title="'.Lang::get('editable').'"><i class="fa fa-ban"></i>否</em>' : '<em class="yes" ectype="inline_edit" fieldname="recommended" fieldid="'.$k.'" fieldvalue="1" title="'.Lang::get('editable').'"><i class="fa fa-check-circle"></i>是</em>';
			$list['enable_distribution'] = $v['enable_distribution'] == 0 ? '<em class="no"><i class="fa fa-ban"></i>否</em>' : '<em class="yes"><i class="fa fa-check-circle"></i>是</em>';
			$data['list'][$k] = $list;
		}
		$this->flexigridXML($data);
	}
	
	function get_query_conditions(){
		$conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'store_name',
                'equal' => 'like',
            ),
            array(
                'field' => 'sgrade',
            ),
        ));
		$owner_name = trim($_GET['owner_name']);
        if ($owner_name)
        {

            $conditions .= " AND (user_name LIKE '%{$owner_name}%' OR owner_name LIKE '%{$owner_name}%') ";
        }
		return $conditions;
	}
	
    function test()
    {
        if (!IS_POST)
        {
            $sgrade_mod =& m('sgrade');
            $grades = $sgrade_mod->find();
            if (!$grades)
            {
                $this->show_warning('set_grade_first');
                return;
            }
            $this->display('store.test.html');
        }
        else
        {
            $user_name = trim($_POST['user_name']);
            $password  = $_POST['password'];

            /* 连接到用户系统 */
            $ms =& ms();
            $user = $ms->user->get($user_name, true);
            if (empty($user))
            {
                $this->json_error('user_not_exist');
                return;
            }
            if ($_POST['need_password'] && !$ms->user->auth($user_name, $password))
            {
                $this->json_error('invalid_password');

                return;
            }

            $store = $this->_store_mod->get_info($user['user_id']);
            if ($store)
            {
                if ($store['state'] == STORE_APPLYING)
                {
                    $this->json_error('user_has_application');
                    return;
                }
                else
                {
                    $this->json_error('user_has_store');
                    return;
                }
            }
			$this->json_result(array('ret_url'=>'index.php?app=store&act=add&user_id='.$user['user_id']),'check_ok_to_next_step');
        }
    }

    function add()
    {
        $user_id = $_GET['user_id'];
        if (!$user_id)
        {
            $this->json_error('Hacking Attempt');
            return;
        }

        if (!IS_POST)
        {
            /* 取得会员信息 */
            $user_mod =& m('member');
            $user = $user_mod->get_info($user_id);
            $this->assign('user', $user);

            $this->assign('store', array('state' => STORE_OPEN, 'recommended' => 0, 'sort_order' => 65535, 'end_time' => 0));

            $sgrade_mod =& m('sgrade');
            $this->assign('sgrades', $sgrade_mod->get_options());

            $this->assign('states', array(
                STORE_OPEN   => Lang::get('open'),
                STORE_CLOSED => Lang::get('close'),
            ));

            $this->assign('recommended_options', array(
                '1' => Lang::get('yes'),
                '0' => Lang::get('no'),
            ));

            $this->assign('scategories', $this->_get_scategory_options());

            $region_mod =& m('region');
            $this->assign('regions', $region_mod->get_options(0));

            /* 导入jQuery的表单验证插件 */
            $this->import_resource(array(
                'script' => 'mlselection.js'
            ));
            $this->assign('enabled_subdomain', ENABLED_SUBDOMAIN);
            $this->display('store.form.html');
        }
        else
        {
            /* 检查名称是否已存在 */
            if (!$this->_store_mod->unique(trim($_POST['store_name'])))
            {
                $this->json_error('name_exist');
                return;
            }
            $domain = empty($_POST['domain']) ? '' : trim($_POST['domain']);
            if (!$this->_store_mod->check_domain($domain, Conf::get('subdomain_reserved'), Conf::get('subdomain_length')))
            {
				$error = current($this->_store_mod->get_error());
                $this->json_error($error['msg']);

                return;
            }
            $data = array(
                'store_id'     => $user_id,
                'store_name'   => $_POST['store_name'],
                'owner_name'   => $_POST['owner_name'],
                'owner_card'   => $_POST['owner_card'],
                'region_id'    => $_POST['region_id'],
                'region_name'  => $_POST['region_name'],
                'address'      => $_POST['address'],
                'zipcode'      => $_POST['zipcode'],
                'tel'          => $_POST['tel'],
                'sgrade'       => $_POST['sgrade'],
                'end_time'     => empty($_POST['end_time']) ? 0 : gmstr2time(trim($_POST['end_time'])),
                'state'        => $_POST['state'],
                'recommended'  => $_POST['recommended'],
                'sort_order'   => $_POST['sort_order'],
                'add_time'     => gmtime(),
                'domain'       => $domain,
            );
            $certs = array();
            isset($_POST['autonym']) && $certs[] = 'autonym';
            isset($_POST['material']) && $certs[] = 'material';
            $data['certification'] = join(',', $certs);

            if ($this->_store_mod->add($data) === false)
            {
                $error = current($this->_store_mod->get_error());
                $this->json_error($error['msg']);
                return false;
            }

			// 给商家赠送积分  by psmb
			$integral_mod=&m('integral');
			$result = array(
				'user_id' => $user_id,
				'type'    => 'open_integral',
				'amount'  => $integral_mod->_get_sys_setting('open_integral')
			);
			$integral_mod->update_integral($result);
			
            $this->_store_mod->unlinkRelation('has_scategory', $user_id);
            $cate_id = intval($_POST['cate_id']);
            if ($cate_id > 0)
            {
                $this->_store_mod->createRelation('has_scategory', $user_id, $cate_id);
            }

            $this->json_result(array('ret_url'=>'index.php?app=store'),'add_ok');
        }
    }

    function edit()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!IS_POST)
        {
            /* 是否存在 */
            $store = $this->_store_mod->get_info($id);
            if (!$store)
            {
                $this->show_warning('store_empty');
                return;
            }
            if ($store['certification'])
            {
                $certs = explode(',', $store['certification']);
                foreach ($certs as $cert)
                {
                    $store['cert_' . $cert] = 1;
                }
            }
            $this->assign('store', $store);

            $sgrade_mod =& m('sgrade');
            $this->assign('sgrades', $sgrade_mod->get_options());

            $this->assign('states', array(
                STORE_OPEN   => Lang::get('open'),
                STORE_CLOSED => Lang::get('close'),
            ));

            $this->assign('recommended_options', array(
                '1' => Lang::get('yes'),
                '0' => Lang::get('no'),
            ));

            $region_mod =& m('region');
            $this->assign('regions', $region_mod->get_options(0));

            $this->assign('scategories', $this->_get_scategory_options());

            $scates = $this->_store_mod->getRelatedData('has_scategory', $id);
            $this->assign('scates', array_values($scates));

            /* 导入jQuery的表单验证插件 */
            $this->import_resource(array(
                'script' => 'mlselection.js'
            ));
            $this->assign('enabled_subdomain', ENABLED_SUBDOMAIN);
            $this->display('store.form.html');
        }
        else
        {
            /* 检查名称是否已存在 */
            if (!$this->_store_mod->unique(trim($_POST['store_name']), $id))
            {
                $this->json_error('name_exist');
                return;
            }
            $store_info = $this->_store_mod->get_info($id);
            $domain = empty($_POST['domain']) ? '' : trim($_POST['domain']);
            if ($domain && $domain != $store_info['domain'])
            {
                if (!$this->_store_mod->check_domain($domain, Conf::get('subdomain_reserved'), Conf::get('subdomain_length')))
                {
                    $error = current($this->_store_mod->get_error());
                	$this->json_error($error['msg']);

                    return;
                }
            }

            $data = array(
                'store_name'   => $_POST['store_name'],
                'owner_name'   => $_POST['owner_name'],
                'owner_card'   => $_POST['owner_card'],
                'region_id'    => $_POST['region_id'],
                'region_name'  => $_POST['region_name'],
                'address'      => $_POST['address'],
                'zipcode'      => $_POST['zipcode'],
                'tel'          => $_POST['tel'],
                'sgrade'       => $_POST['sgrade'],
                'end_time'     => empty($_POST['end_time']) ? 0 : gmstr2time(trim($_POST['end_time'])),
                'state'        => $_POST['state'],
                'sort_order'   => $_POST['sort_order'],
                'recommended'  => $_POST['recommended'],
                'domain'       => $domain,
            );
            $data['state'] == STORE_CLOSED && $data['close_reason'] = $_POST['close_reason'];
            $certs = array();
            isset($_POST['autonym']) && $certs[] = 'autonym';
            isset($_POST['material']) && $certs[] = 'material';
            $data['certification'] = join(',', $certs);

            $old_info = $this->_store_mod->get_info($id); // 修改前的店铺信息
            $this->_store_mod->edit($id, $data);

            $this->_store_mod->unlinkRelation('has_scategory', $id);
            $cate_id = intval($_POST['cate_id']);
            if ($cate_id > 0)
            {
                $this->_store_mod->createRelation('has_scategory', $id, $cate_id);
            }

            /* 如果修改了店铺状态，通知店主 */
            if ($old_info['state'] != $data['state'])
            {
                $ms =& ms();
                if ($data['state'] == STORE_CLOSED)
                {
                    // 关闭店铺
                    $subject = Lang::get('close_store_notice');
                    //$content = sprintf(Lang::get(), $data['close_reason']);
                    $content = get_msg('toseller_store_closed_notify',array('reason' => $data['close_reason']));
                }
                else
                {
                    // 开启店铺
                    $subject = Lang::get('open_store_notice');
                    $content = Lang::get('toseller_store_opened_notify');
                }
                $ms->pm->send(MSG_SYSTEM, $old_info['store_id'], '', $content);
                $this->_mailto($old_info['email'], $subject, $content);
            }

            $ret_page = isset($_GET['ret_page']) ? intval($_GET['ret_page']) : 1;
            $this->json_result('','edit_ok');
        }
    }

    //异步修改数据
   function ajax_col()
   {
       $id     = empty($_GET['id']) ? 0 : intval($_GET['id']);
       $column = empty($_GET['column']) ? '' : trim($_GET['column']);
       $value  = isset($_GET['value']) ? trim($_GET['value']) : '';
       $data   = array();
       if (in_array($column ,array('recommended','sort_order')))
       {
           $data[$column] = $value;
           $this->_store_mod->edit($id, $data);
           if(!$this->_store_mod->has_error())
           {
               echo ecm_json_encode(true);
           }
       }
       else
       {
           return ;
       }
       return ;
   }

    function drop()
    {
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$id)
        {
            $this->json_error('no_store_to_drop');
            return;
        }

        $ids = explode(',', $id);
        foreach ($ids as $id)
        {
            $this->_drop_store_image($id); // 注意这里要先删除图片，再删除店铺，因为删除图片时要查店铺信息
        }
        if (!$this->_store_mod->drop($ids))
        {
            $error = current($this->_store_mod->get_error());
            $this->json_error($error['msg']);
            return;
        }

        /* 通知店主 */
        $user_mod =& m('member');
        $users = $user_mod->find(array(
            'conditions' => "user_id" . db_create_in($ids),
            'fields'     => 'user_id, user_name, email',
        ));
        foreach ($users as $user)
        {
            $ms =& ms();
            $subject = Lang::get('drop_store_notice');
            $content = get_msg('toseller_store_droped_notify');
            $ms->pm->send(MSG_SYSTEM, $user['user_id'], $subject, $content);
            $this->_mailto($user['email'], $subject, $content);
        }

        $this->json_result('','drop_ok');
    }

    /* 查看并处理店铺申请 */
    function view()
    {
        if (!IS_POST)
        {
			$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		
			/* 是否存在 */
			$store = $this->_store_mod->get_info($id);
			if (!$store)
			{
				$this->show_warning('Hacking Attempt');
				return;
			}
		
            $sgrade_mod =& m('sgrade');
            $sgrades = $sgrade_mod->get_options();
            $store['sgrade'] = $sgrades[$store['sgrade']];
            $this->assign('store', $store);

            $scates = $this->_store_mod->getRelatedData('has_scategory', $id);
            $this->assign('scates', $scates);

            $this->display('store.view.html');
        }
        else
        {
			$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		
			/* 是否存在 */
			$store = $this->_store_mod->get_info($id);
			if (!$store)
			{
				$this->json_error('Hacking Attempt');
				return;
			}
			
            /* 批准 */
            if ($_POST['action'] == 'agree')
            {
				// 已经开通的店铺不允许再提交，防止重复插入
				if($store['state'] == STORE_OPEN) {
					$this->json_error('agree_ok');
					return;
				}
                $this->_store_mod->edit($id, array(
                    'state'      => STORE_OPEN,
                    'add_time'   => gmtime(),
                    'sort_order' => 65535,
					'apply_remark' => '',
                ));

                $content = get_msg('toseller_store_passed_notify');
                $ms =& ms();
                $ms->pm->send(MSG_SYSTEM, $id, '', $content);
                
				
				// 给商家赠送积分
				$integral_mod=&m('integral');
				$result = array(
					'user_id' => $id,
					'type'    => 'open_integral',
					'amount'  => $integral_mod->_get_sys_setting('open_integral')
				);
				$integral_mod->update_integral($result);	
				
				
                $this->send_feed('store_created', array(
                    'user_id'   =>  $store['store_id'],
                    'user_name'   => $store['user_name'],
                    'store_url'   => SITE_URL . '/' . url('app=store&id=' . $id),
                    'seller_name'   => $store['store_name'],
                ));
                $this->_hook('after_opening', array('user_id' => $id));
                 $this->json_result(array('ret_url'=>'index.php?app=store'),'agree_ok');
            }
            /* 拒绝 */
            elseif ($_POST['action'] == 'reject')
            {
                $reject_reason = trim($_POST['reject_reason']);
                if (!$reject_reason)
                {
                    $this->json_error('input_reason');
                    return;
                }

                $content = get_msg('toseller_store_refused_notify', array('reason' => $reject_reason));
                $ms =& ms();
                $ms->pm->send(MSG_SYSTEM, $id, '', $content);

                /* 拒绝后不删掉店铺，让卖家修改后再审核 by mimall
				$this->_drop_store_image($id); // 注意这里要先删除图片，再删除店铺，因为删除图片时要查店铺信息
                $this->_store_mod->drop($id);
				*/
				$this->_store_mod->edit($id, array('apply_remark' => $reject_reason));
                $this->json_result(array('ret_url'=>'index.php?app=store'),'reject_ok');
            }
            else
            {
                $this->json_error('Hacking Attempt');
                return;
            }
        }
    }

    function batch_edit()
    {
        if (!IS_POST)
        {
            $sgrade_mod =& m('sgrade');
            $this->assign('sgrades', $sgrade_mod->get_options());

            $region_mod =& m('region');
            $this->assign('regions', $region_mod->get_options(0));

            $this->headtag('<script type="text/javascript" src="{lib file=mlselection.js}"></script>');
            $this->display('store.batch.html');
        }
        else
        {
            $id = isset($_POST['id']) ? trim($_POST['id']) : '';
            if (!$id)
            {
                $this->json_error('Hacking Attempt');
                return;
            }

            $ids = explode(',', $id);
            $data = array();
            if ($_POST['region_id'] > 0)
            {
                $data['region_id'] = $_POST['region_id'];
                $data['region_name'] = $_POST['region_name'];
            }
            if ($_POST['sgrade'] > 0)
            {
                $data['sgrade'] = $_POST['sgrade'];
            }
            if ($_POST['certification'])
            {
                $certs = array();
                if ($_POST['autonym'])
                {
                    $certs[] = 'autonym';
                }
                if ($_POST['material'])
                {
                    $certs[] = 'material';
                }
                $data['certification'] = join(',', $certs);
            }
            if ($_POST['recommended'] > -1)
            {
                $data['recommended'] = $_POST['recommended'];
            }
            if (trim($_POST['sort_order']))
            {
                $data['sort_order'] = intval(trim($_POST['sort_order']));
            }

            if (empty($data))
            {
                $this->json_error('no_change_set');
                return;
            }

            $this->_store_mod->edit($ids, $data);
            $ret_page = isset($_GET['ret_page']) ? intval($_GET['ret_page']) : 1;
            $this->json_result('','edit_ok');
        }
    }
	
	function export_csv()
	{
		list($timeConditoins) = $this->getConditions();
		
		$conditions = empty($_GET['wait_verify']) ? "state <> '" . STORE_APPLYING . "'" : "state = '" . STORE_APPLYING . "'";
		if ($_GET['id'] != '') {
            $ids = explode(',', $_GET['id']);
			$conditions .= ' AND store_id' . db_create_in($ids);
        }
		$conditions .= $this->get_query_conditions();
        $stores = $this->_store_mod->find(array(
            'conditions' => $conditions.$timeConditoins,
            'join'  => 'belongs_to_user',
            'fields'=> 'this.*,member.user_name'
        ));
		if(!$stores) {
			$this->show_warning('no_such_store');
            return;
		}
        $sgrade_mod =& m('sgrade');
        $grades = $sgrade_mod->get_options();
        $states = array(
            STORE_APPLYING  => LANG::get('wait_verify'),
            STORE_OPEN      => Lang::get('open'),
            STORE_CLOSED    => Lang::get('close'),
        );
        foreach ($stores as $key => $store)
        {
            $stores[$key]['sgrade'] = $grades[$store['sgrade']];
            $stores[$key]['state'] = $states[$store['state']];
            $certs = empty($store['certification']) ? array() : explode(',', $store['certification']);
            for ($i = 0; $i < count($certs); $i++)
            {
                $certs[$i] = Lang::get($certs[$i]);
            }
            $stores[$key]['certification'] = join('<br />', $certs);
        }
		/* xls文件数组 */
		$record_xls = array();		
		$record_title = array(
			'store_id' 		=> 	'ID',
			'user_name' 		=> 	'会员名',
    		'owner_name' 		=> 	'店主姓名',
    		'store_name' 		=> 	'店铺名称',
			'region_name' => '所在地',
    		'sgrade' 		=> 	'所属等级',
    		'end_time' => 	'有效期至',
    		'state' 	=> 	'状态',
			'enable_distribution' 		=> 	'开启分销',
			'distribution_1' 		=> 	'一级分销',
			'distribution_2' 		=> 	'二级分销',
			'distribution_3' 		=> 	'三级分销',
		);
		$folder = 'store_'.local_date('Ymdhis', gmtime());
		$record_xls[] = $record_title;
		$amount = 0;
		foreach($stores as $key=>$val)
    	{
			$record_value['store_id']	=	$val['store_id'];
			$record_value['user_name']	=	$val['user_name'];
			$record_value['owner_name']	=	$val['owner_name'];
			$record_value['store_name']	=	$val['store_name'];
			$record_value['region_name']	=	$val['region_name'];
			$record_value['sgrade']	=	$val['sgrade'];
			$record_value['end_time']	=	local_date('Y/m/d H:i:s',$val['end_time']);
			$record_value['state']	=	$val['state'];
			$record_value['enable_distribution']	=	$val['enable_distribution']?'是':'否';
			$record_value['distribution_1']	=	$val['distribution_1'];
			$record_value['distribution_2']	=	$val['distribution_2'];
			$record_value['distribution_3']	=	$val['distribution_3'];
        	$record_xls[] = $record_value;
    	}
		$record_xls[] = array('店铺总数:',count($stores));
		import('excelwriter.lib');
		$ExcelWriter = new ExcelWriter(CHARSET, $folder);
		$ExcelWriter->add_array($record_xls);
		$ExcelWriter->output();
	}
	
	function statistic()
	{
		import('datehelper.lib');
        $search_arr = formatTime($_REQUEST);
		
		$this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js,echarts-all.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
			'style' =>  'jquery.ui/themes/smoothness/jquery.ui.css',
		));
		//获得系统年份
		$year_arr = getSystemYearArr();
		//获得系统月份
		$month_arr = getSystemMonthArr();

		//获得本月的周时间段
		$week_arr = getMonthWeekArr($search_arr['week']['current_year'], $search_arr['week']['current_month']);
		$this->assign('year_arr', $year_arr);
		$this->assign('month_arr', $month_arr);
		$this->assign('week_arr', $week_arr);
		$this->assign('search_arr', $search_arr);
		$this->display('store.statistic.html');
	}
	
	/**
     * 输出平台数据
     */
    function get_plat_sale()
	{
        list($conditions) = $this->getConditions();
        $statcount_arr = $this->_store_mod->get(array(
			'conditions' => '1=1'.$conditions,
			'fields' => 'COUNT(*) as storenum',		
		));
		echo '<dl class="row"><dd class="opt"><ul>';
		echo '<li><h4>店铺数量：</h4><h2 class="timer">'.$statcount_arr['storenum'].'</h2><h6>人</h6></li>';
		echo '</ul></dd><dl>';
        exit();
    }
    
	/**
     * 订单走势
     */
    function increase_trend()
	{
		import('datehelper.lib');
        $search_arr = formatTime($_REQUEST);
		
		$conditions = "1=1";
		
        //默认统计当前数据
        if(!$search_arr['search_type']){
            $search_arr['search_type'] = 'day';
        }

        if($search_arr['search_type'] == 'day'){
    	    $stime = $search_arr['day']['search_time'] - 86400;//昨天0点
    	    $etime = $search_arr['day']['search_time'] + 86400 - 1;//今天24点
            //构造横轴数据
            for($i=0; $i<24; $i++){
                //统计图数据
                $curr_arr[$i] = 0;//今天
                $up_arr[$i] = 0;//昨天
                //统计表数据
                $currlist_arr[$i]['timetext'] = $i;

                $uplist_arr[$i]['val'] = 0;
                $currlist_arr[$i]['val'] = 0;
                //横轴
                $stat_arr['xAxis']['data'][] = "$i";
            }
			
            $today_day = local_date('d', $etime);//今天日期
            $yesterday_day = local_date('d', $stime);//昨天日期
			
			$conditions .= " AND add_time BETWEEN {$stime} AND {$etime} ";
            $field .= " ,DAY(FROM_UNIXTIME(add_time+".date('Z').")) as dayval, HOUR(FROM_UNIXTIME(add_time+".date('Z').")) as hourval ";
            $group = ' GROUP BY dayval,hourval';
			$users = $this->_store_mod->find(array(
				'conditions' => $conditions.$group,
				'fields' => 'count(*) as num'.$field,
			));

            foreach($users as $k => $v){
                if($today_day == $v['dayval']){
                    $curr_arr[$v['hourval']] = floatval($v['num']);
                    $currlist_arr[$v['hourval']]['val'] = $v['num'];
                }
                if($yesterday_day == $v['dayval']){
                    $up_arr[$v['hourval']] = floatval($v['num']);
                    $uplist_arr[$v['hourval']]['val'] = $v['num'];
                }
            }

			$stat_arr['legend']['data'][0] = '昨天';
            $stat_arr['legend']['data'][1] = '今天';
			$stat_arr['series'][0]['data'] = array_values($up_arr);
            $stat_arr['series'][1]['data'] = array_values($curr_arr);
        }

        if($search_arr['search_type'] == 'week'){
			$stime = getYearFirstDay($search_arr['searchweek_year']);
			$etime = getYearLastDay($search_arr['searchweek_year']);

			$current_weekarr = explode('|', $search_arr['week']['current_week']);
			$wstime = gmstr2time($current_weekarr[0]);
			$wetime = gmstr2time_end($current_weekarr[1]) - 1;
			
			// 当年的第几周
            $up_week = local_date('W', $wstime);//上周
            $curr_week = local_date('W', $wetime+1);//本周
			if(($curr_week < $up_week) && ($curr_week == 1)) { // 统计了去年的第几周了，故重置
				$up_week = 0;
			}
			
            //构造横轴数据
            for($i=1; $i<=7; $i++){
                //统计图数据
                $up_arr[$i] = 0;
                $curr_arr[$i] = 0;
                $tmp_weekarr = getSystemWeekArr();
                //统计表数据
                $uplist_arr[$i]['timetext'] = $tmp_weekarr[$i];
                $currlist_arr[$i]['timetext'] = $tmp_weekarr[$i];
                $uplist_arr[$i]['val'] = 0;
                $currlist_arr[$i]['val'] = 0;
                //横轴
                $stat_arr['xAxis']['data'][] = $tmp_weekarr[$i];
                unset($tmp_weekarr);
            }
            $conditions .= " AND add_time BETWEEN {$stime} AND {$etime} ";
            $field .= ",WEEKOFYEAR(FROM_UNIXTIME(add_time))+1 as weekval, WEEKDAY(FROM_UNIXTIME({add_time}))+1 as dayofweekval ";
            $group = ' GROUP BY weekval,dayofweekval';
            $users = $this->_store_mod->find(array(
				'conditions' => $conditions.$group,
				'fields' => 'count(*) as num'.$field,
			));
			
            foreach($users as $k => $v){
                if ($up_week == $v['weekval']){
                    $up_arr[$v['dayofweekval']] = floatval($v['num']);
                    $uplist_arr[$v['dayofweekval']]['val'] = floatval($v['num']);
                }
                if ($curr_week == $v['weekval']){
                    $curr_arr[$v['dayofweekval']] = floatval($v['num']);
                    $currlist_arr[$v['dayofweekval']]['val'] = floatval($v['num']);
                }
            }
			
			$stat_arr['legend']['data'][0] = '上周';
            $stat_arr['legend']['data'][1] = '本周';
			$stat_arr['series'][0]['data'] = array_values($up_arr);
            $stat_arr['series'][1]['data'] = array_values($curr_arr);
        }

        if($search_arr['search_type'] == 'month'){
			$stime = getYearFirstDay($search_arr['month']['current_year']);
			$etime = getYearLastDay($search_arr['month']['current_year']);
			
			$curr_month = $search_arr['month']['current_month'];
            $up_month = date('m', strtotime($search_arr['month']['current_year'].'-'.$curr_month.'-01 -1 month'));

            //计算横轴的最大量（由于每个月的天数不同）
            $up_dayofmonth = local_date('t', $stime-1);
            $curr_dayofmonth = local_date('t',$etime);

            $x_max = $up_dayofmonth > $curr_dayofmonth ? $up_dayofmonth : $curr_dayofmonth;

            //构造横轴数据
            for($i=1; $i<=$x_max; $i++){
                //统计图数据
                $up_arr[$i] = 0;
                $curr_arr[$i] = 0;
                //统计表数据
                $currlist_arr[$i]['timetext'] = $i;
                $uplist_arr[$i]['val'] = 0;
                $currlist_arr[$i]['val'] = 0;
                //横轴
                $stat_arr['xAxis']['data'][] = $i;
            }
            $conditions .= " AND add_time BETWEEN {$stime} AND {$etime} ";
            $field .= ",MONTH(FROM_UNIXTIME(add_time)) as monthval,day(FROM_UNIXTIME(add_time)) as dayval ";
            $group = ' GROUP BY monthval,dayval';
            $users = $this->_store_mod->find(array(
				'conditions' => $conditions.$group,
				'fields' => 'count(*) as num'.$field,
			));
			
            foreach($users as $k => $v){
                if ($up_month == $v['monthval']){
                    $up_arr[$v['dayval']] = floatval($v['num']);
                    $uplist_arr[$v['dayval']]['val'] = floatval($v['num']);
                }
                if ($curr_month == $v['monthval']){
                    $curr_arr[$v['dayval']] = floatval($v['num']);
                    $currlist_arr[$v['dayval']]['val'] = floatval($v['num']);
                }
            }
            $stat_arr['legend']['data'][0] = '上月';
            $stat_arr['legend']['data'][1] = '本月';
			$stat_arr['series'][0]['data'] = array_values($up_arr);
            $stat_arr['series'][1]['data'] = array_values($curr_arr);
        }

        $stat_json = creatChartData($stat_arr);
        $this->assign('stat_json',$stat_json);
        $this->assign('stattype','storenum');
        $this->display('stat.linelabels.html');
    }
	
	function getConditions()
	{
		import('datehelper.lib');
        $search_arr = formatTime($_REQUEST);
		
		if($_GET['search_type'])
		{
        	//计算昨天和今天时间
			if($search_arr['search_type'] == 'day'){
				$stime = $search_arr['day']['search_time'] - 86400;//昨天0点
				$etime = $search_arr['day']['search_time'] + 86400 - 1;//今天24点
				$curr_stime = $search_arr['day']['search_time'];//今天0点
			} elseif ($search_arr['search_type'] == 'week'){
				$current_weekarr = explode('|', $search_arr['week']['current_week']);
				$stime = gmstr2time($current_weekarr[0]);
				$etime = gmstr2time_end($current_weekarr[1]) - 1;
				$curr_stime = $stime;//本周0点
			} elseif ($search_arr['search_type'] == 'month'){
				$stime = getMonthFirstDay($search_arr['month']['current_year'], $search_arr['month']['current_month']);
				$etime = getMonthLastDay($search_arr['month']['current_year'], $search_arr['month']['current_month']);
				$curr_stime = $stime; // 本月0点
			}
			
			$conditions .= " AND add_time BETWEEN ".$curr_stime." AND ".$etime;
		}
		
		return 	array($conditions, $curr_stime, $etime);
	}

    function check_name()
    {
        $id         = empty($_GET['id']) ? 0 : intval($_GET['id']);
        $store_name = empty($_GET['store_name']) ? '' : trim($_GET['store_name']);

        if (!$this->_store_mod->unique($store_name, $id))
        {
            echo ecm_json_encode(false);
            return;
        }
        echo ecm_json_encode(true);
    }

    /* 删除店铺相关图片 */
    function _drop_store_image($store_id)
    {
        $files = array();

        /* 申请店铺时上传的图片 */
        $store = $this->_store_mod->get_info($store_id);
        for ($i = 1; $i <= 3; $i++)
        {
            if ($store['image_' . $i])
            {
                $files[] = $store['image_' . $i];
            }
        }

        /* 店铺设置中的图片 */
        if ($store['store_banner'])
        {
            $files[] = $store['store_banner'];
        }
        if ($store['store_logo'])
        {
            $files[] = $store['store_logo'];
        }

        /* 删除 */
        foreach ($files as $file)
        {
            $filename = ROOT_PATH . '/' . $file;
            if (file_exists($filename))
            {
                @unlink($filename);
            }
        }
    }

    /* 取得店铺分类 */
    function _get_scategory_options()
    {
        $mod =& m('scategory');
        $scategories = $mod->get_list();
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($scategories, 'cate_id', 'parent_id', 'cate_name');

        return $tree->getOptions();
    }
}

?>
