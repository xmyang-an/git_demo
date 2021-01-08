<?php

/* 会员控制器 */
class UserApp extends BackendApp
{
	var $_admin_mod;
    var $_user_mod;

    function __construct()
    {
        $this->UserApp();
    }

    function UserApp()
    {
        parent::__construct();
        $this->_user_mod =& m('member');
		$this->_admin_mod = & m('userpriv');
    }
	
	function index()
    {
		$query = $this->get_query_conditions();
		$this->assign('filtered', $query);
        $this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js',
		));
        $this->display('user.index.html');
    }
	
	function get_xml()
	{
        $conditions = '1=1';		 
		$conditions .= $this->get_query_conditions();
		
		list($timeConditoins) = $this->getConditions();
		
		$order = 'user_id asc';
        $param = array('user_name','real_name','email','region_name','phone_mob','reg_time','last_login','logins','last_ip');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
		$users = $this->_user_mod->find(array(
            'join' => 'has_store,manage_mall',
            'fields' => 'this.*,store.store_id,userpriv.store_id as priv_store_id,userpriv.privs',
            'conditions' => $conditions.$timeConditoins,
            'limit' => $page['limit'],
            'order' => $order,
            'count' => true,
        ));
		$page['item_count'] = $this->_user_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($users as $k => $v){
			$refer = array();
			if($v['referid'] > 0)
			{
				$refer = $this->_user_mod->get($v['referid']);
			}
			$list = array();
			$operation = "<a class='btn red' onclick=\"fg_delete({$k},'user')\"><i class='fa fa-trash-o'></i>删除</a>";
			$operation .= "<a class='btn blue' href='index.php?app=user&act=edit&id={$k}'><i class='fa fa-pencil-square-o'></i>编辑</a><a class='btn red' href='index.php?app=user&act=loginLog&id={$k}'><i class='fa fa-clock-o'></i>登陆记录</a>";
			$list['operation'] = $operation;
			$list['user_name'] = $v['user_name'];
			$list['real_name'] = $v['real_name'];
			$list['refer_name'] = $refer['user_name'] ? $refer['user_name'] : '自行注册';
			$list['email'] = $v['email'];
			$list['phone_mob'] = $v['phone_mob'];
			$list['reg_time'] = local_date('Y-m-d',$v['reg_time']);
			$list['last_login'] = local_date('Y-m-d H:i:s',$v['last_login']);
			$list['last_ip'] = $v['last_ip'];
			$list['logins'] = $v['logins'];
			$list['if_admin'] = $v['priv_store_id'] == 0 && $v['privs'] != '' ? "<em class='yes'><i class='fa fa-check-circle'></i>是</em>" : "<a href='index.php?app=admin&act=add&id={$k}' onclick=\"parent.openItem('admin_manage', 'user')\">设为管理员</a>";
			$data['list'][$k] = $list;
		}
		$this->flexigridXML($data);
	}

	function get_query_conditions(){
		$conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'user_name',
                'equal' => 'like',
            ),
            array(
                'field' => 'real_name',
                'equal' => 'like',
            ),
            array(
                'field' => 'email',
                'equal' => 'like',
            ),
            array(
                'field' => 'phone_mob',
                'type'  => 'int',
				'equal' => 'like',
            ),
        ));
		return $conditions;
	}
	
    function add()
    {
        if (!IS_POST)
        {
            $this->assign('user', array(
                'gender' => 0,
            ));
            $ms =& ms();
            $this->assign('set_avatar', $ms->user->set_avatar());
            $this->display('user.form.html');
        }
        else
        {
            $user_name = trim($_POST['user_name']);
            $password  = trim($_POST['password']);
            $email     = trim($_POST['email']);
			$phone_mob = trim($_POST['phone_mob']);
            $real_name = trim($_POST['real_name']);
            $gender    = trim($_POST['gender']);
            $im_qq     = trim($_POST['im_qq']);
			$imforbid  = intval($_POST['imforbid']);

            if (strlen($user_name) < 3 || strlen($user_name) > 15)
            {
                $this->json_error('user_length_limit');

                return;
            }

            if (strlen($password) < 6 || strlen($password) > 20)
            {
                $this->json_error('password_length_error');

                return;
            }

            if (!is_email($email))
            {
                $this->json_error('email_error');

                return;
            }
			
			if(!is_mobile($phone_mob)) 
			{
				$this->json_error('phone_mob_error');
				
				return;
			}

            /* 连接用户系统 */
            $ms =& ms();

            /* 检查名称是否已存在 */
            if (!$ms->user->check_username($user_name))
            {
				$error = current($ms->user->get_error());
                $this->json_error($error['msg']);

                return;
            }
			
			
			
			/*  检查Email是否被注册过 */
			if(!$ms->user->check_email($email)){
				$error = current($ms->user->get_error());
                $this->json_error($error['msg']);

                return;
			}
			
			/*  检查手机是否被注册过 */
			if(!$ms->user->check_phone($phone_mob)){
				$error = current($ms->user->get_error());
                $this->json_error($error['msg']);

                return;
			}

            /* 保存本地资料 */
            $data = array(
                'real_name' => $_POST['real_name'],
                'gender'    => $_POST['gender'],
                'phone_mob' => $_POST['phone_mob'],
                'im_qq'     => $_POST['im_qq'],
		'locked'    => intval($_POST['locked']),
		'imforbid'  => $imforbid,
                'im_aliww'  => $_POST['im_aliww'],
                'reg_time'  => gmtime(),
            );

            /* 到用户系统中注册 */
            $user_id = $ms->user->register($user_name, $password, $email, $data);
            if (!$user_id)
            {
                $error = current($ms->user->get_error());
                $this->json_error($error['msg']);

                return;
            }
			if($_POST['refername'])
			{
				$refer = $this->_user_mod->get('user_name="'.$_POST['refername'].'"');
				if(!empty($refer))
				{
					$data['referid'] = $refer['user_id'];
				}
			}
			// 如果开启了积分功能，则给新会员赠送积分
			$integral_mod=&m('integral');
			$data = array(
				'user_id'=> $user_id,
				'type'   => 'register_has_integral',
				'amount' => $integral_mod->_get_sys_setting('register_integral')
			);
			$integral_mod->update_integral($data);
			
            if (!empty($_FILES['portrait']))
            {
                $portrait = $this->_upload_portrait($user_id);
                if ($portrait === false)
                {
                    return;
                }

                $portrait && $this->_user_mod->edit($user_id, array('portrait' => $portrait));
            }
			
            $this->json_result('','add_ok');
        }
    }

    /*检查会员名称的唯一性*/
    function  check_user()
    {
          $user_name = empty($_GET['user_name']) ? null : trim($_GET['user_name']);
          if (!$user_name)
          {
              echo ecm_json_encode(false);
              return ;
          }

          /* 连接到用户系统 */
          $ms =& ms();
          echo ecm_json_encode($ms->user->check_username($user_name));
    }
	
	function loginLog()
	{
		$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		if(!$id)
		{
			$this->show_warning('会员信息不存在');
			exit;
		}
		
		$this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js',
		));
		$this->display('user.log.html');
	}
	
	function getLogxml()
	{
		$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		if(!$id)
		{
			return false;
		}
		
		$conditions = 'user_id='.$id;
		if($this->_admin_mod->check_system_manager($id))//初始管理员只显示后台的登陆记录
		{
			$conditions = 'user_id=0';
		}
		
		$order = 'add_time desc';
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
		$log_mod = &m('loginlog'); 
		$users = $log_mod->find(array(
            'conditions' => $conditions,
            'limit' => $page['limit'],
            'order' => $order,
            'count' => true,
        ));
		$page['item_count'] = $log_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($users as $k => $v){
			$list = array();
			$list['user_name'] = $v['user_name'];
			$list['ip'] = $v['ip'];
			$list['region_name'] = $v['region_name'];
			$list['add_time'] = local_date('Y-m-d H:i:s',$v['add_time']);
			$data['list'][$k] = $list;
		}
		$this->flexigridXML($data);
	}

    function edit()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		
        if (!IS_POST)
        {
			//判断是否是系统初始管理员，如果是系统管理员，必须是自己才能编辑，其他管理员不能编辑系统管理员
        	if ($this->_admin_mod->check_system_manager($id) && !$this->_admin_mod->check_system_manager($this->visitor->get('user_id')))
        	{
            	$this->show_warning('system_admin_edit');
           		return;
       	 	}
		
            /* 是否存在 */
            $user = $this->_user_mod->get_info($id);
            if (!$user)
            {
                $this->show_warning('user_empty');
                return;
            }
			if($user['referid'])
			{
				$refer = $this->_user_mod->get($user['referid']);
				if(!empty($refer))
				{
					$user['refername'] = $refer['user_name'];
				}
			}

            $ms =& ms();
            $this->assign('set_avatar', $ms->user->set_avatar($id));
            $this->assign('user', $user);
            $this->assign('phone_tel', explode('-', $user['phone_tel']));
            $this->display('user.form.html');
        }
        else
        {
			//判断是否是系统初始管理员，如果是系统管理员，必须是自己才能编辑，其他管理员不能编辑系统管理员
        	if ($this->_admin_mod->check_system_manager($id) && !$this->_admin_mod->check_system_manager($this->visitor->get('user_id')))
        	{
            	$this->json_error('system_admin_edit');
            	return;
        	}
		
            $data = array(
                'real_name' => $_POST['real_name'],
                'gender'    => $_POST['gender'],
                'phone_mob' => $_POST['phone_mob'],
                'im_qq'     => $_POST['im_qq'],
				'locked'    => intval($_POST['locked']),
				'imforbid'  => intval($_POST['imforbid']),
                'im_aliww'  => $_POST['im_aliww'],
            );
            if (!empty($_POST['password']))
            {
                $password = trim($_POST['password']);
                if (strlen($password) < 6 || strlen($password) > 20)
                {
                    $this->json_error('password_length_error');

                    return;
                }
            }
            if (!is_email(trim($_POST['email'])))
            {
                $this->json_error('email_error');

                return;
            }
			if(!is_mobile(trim($_POST['phone_mob']))) 
			{
				$this->json_error('phone_mob_error');
				
				return;
			}

            if (!empty($_FILES['portrait']))
            {
                $portrait = $this->_upload_portrait($id);
                $portrait && $data['portrait'] = $portrait;
            }
			if($_POST['refername'])
			{				
				$refer = $this->_user_mod->get('user_name="'.$_POST['refername'].'"');
				if(!empty($refer) && ($refer['user_id'] <> $id))
				{
					$ids = $this->_user_mod->getUserRefer($id, 10000000);
					if(in_array($refer['user_id'], $ids)){
						$this->json_error(sprintf('%s不可设置为的上级,因为其处于当前会员的下级。', $refer['user_name']));
						return;
					}
					else{
						$data['referid'] = $refer['user_id'];
					}
				}
			}
			
			
			$ms =& ms();    //连接用户系统
			
			/*  检查Email是否被注册过 */
			if(!$ms->user->check_email(trim($_POST['email']), $id)){
				$error = current($ms->user->get_error());
                $this->json_error($error['msg']);

                return;
			}
			
			/*  检查手机是否被注册过 */
			if(!$ms->user->check_phone(trim($_POST['phone_mob']), $id)){
				$error = current($ms->user->get_error());
                $this->json_error($error['msg']);

                return;
			}

            /* 修改本地数据 */
            $this->_user_mod->edit($id, $data);

            /* 修改用户系统数据 */
            $user_data = array();
            !empty($_POST['password']) && $user_data['password'] = trim($_POST['password']);
            !empty($_POST['email'])    && $user_data['email']    = trim($_POST['email']);
			!empty($_POST['phone_mob']) && $user_data['phone_mob'] = trim($_POST['phone_mob']);
            if (!empty($user_data))
            {
                $ms =& ms();
                $ms->user->edit($id, '', $user_data, true);
            }

            $this->json_result('','edit_ok');
        }
    }

    function drop()
    {
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$id)
        {
            $this->json_error('no_user_to_drop');
            return;
        }
        $admin_mod =& m('userpriv');
        if(!$admin_mod->check_admin($id))
        {
            $this->json_error('cannot_drop_admin');
            return;
        }

        $ids = explode(',', $id);

        /* 连接用户系统，从用户系统中删除会员 */
        $ms =& ms();
        if (!$ms->user->drop($ids))
        {
            $error = current($ms->user->get_error());
            $this->json_error($error['msg']);

            return;
        }
		
		/* 如果是第三方账号登陆进来的会员，则删掉相应的绑定数据 */
		$member_bind_mod = &m('member_bind');
		$member_bind_mod->drop('user_id '.db_create_in($ids));

        $this->json_result('','drop_ok');
    }
	
	// 分销上下级关系列表
	function disteam()
    {
        $this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js',
		));
        $this->display('user.disteam.html');
    }
	
	function get_disteam_xml()
	{
        $conditions = '';
		$query = $this->get_query_conditions();
		$order = 'user_id asc';
        $param = array('user_name','phone_mob');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
		$distribution_mod = &m('distribution');
		$distribution_statistics_mod = &m('distribution_statistics');
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
		$users = $distribution_mod->find(array(
            'conditions' => '1=1' . $conditions,
			'fields' 	=> 'user_id, parent_id',
            'limit' => $page['limit'],
            'order' => $order,
            'count' => true,
        ));
		$page['item_count'] = $distribution_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($users as $k => $v){
			$list = array();
			$user = $this->_user_mod->get(array('conditions' => 'user_id='.$v['user_id'], 'fields' => 'user_name, phone_mob, reg_time'));
			$list['user_name'] = $user['user_name'];
			$list['phone_mob'] = $user['phone_mob'];
			$list['reg_time'] = local_date('Y-m-d',$user['reg_time']);
			// 累计收益
			$distribution_statistics = $distribution_statistics_mod->get('user_id='.$v['user_id']);
			$list['profit'] = $distribution_statistics['amount'];
			
			// 上级
			$list['parentName'] = '';
			$list['parentPhone'] = '';
			if($v['parent_id']) {
				if($parent = $this->_user_mod->get(array('conditions' => 'user_id='.$v['parent_id'], 'fields' => 'user_name,phone_mob'))) {
					$list['parentName'] = $parent['user_name'];
					$list['parentPhone'] = $parent['phone_mob'];
				}
			}
			$data['list'][$k] = $list;
		}
		$this->flexigridXML($data);
	}
    
    function export_csv()
	{
		$conditions = '1=1';
		if ($_GET['id'] != '') {
            $ids = explode(',', $_GET['id']);
			$conditions .= ' AND user_id' . db_create_in($ids);
        }
		if ($_GET['query'] != '') 
		{
			$conditions .= " AND ".$_GET['qtype']." like '%" . $_GET['query'] . "%'";
		}
		
		list($timeConditoins) = $this->getConditions();
		
		$users = $this->_user_mod->find(array(
            'fields' => 'this.*',
            'conditions' => $conditions.$timeConditoins,
            'order' => "user_id asc"
        ));
		
		if(!$users) {
			$this->show_warning('no_such_user');
            return;
		}
		/* xls文件数组 */
		$record_xls = array();		
		$record_title = array(
			'user_name' 		=> 	'会员名',
    		'real_name' 		=> 	'真实姓名',
    		'email' 		=> 	'电子邮箱',
			'phone_mob' => '手机号码',
    		'im_qq' 		=> 	'QQ',
    		'im_ww' => 	'旺旺',
    		'reg_time' 	=> 	'注册时间',
			'last_login' => '最后登录时间',
			'last_ip' => '最后登录ip',
    		'logins' 	=> 	'登录次数',
		);
		$folder = 'user_'.local_date('Ymdhis', gmtime());
		$record_xls[] = $record_title;
		$amount = 0;
		foreach($users as $key=>$user)
    	{
			$record_value['user_name']	=	$user['user_name'];
			$record_value['real_name']	=	$user['real_name'];
			$record_value['email']	=	$user['email'];
			$record_value['phone_mob']	=	$user['phone_mob'];
			$record_value['im_qq']	=	$user['im_qq'];
			$record_value['im_ww']	=	$user['im_ww'];
			$record_value['reg_time']	=	local_date('Y/m/d H:i:s',$user['reg_time']);
			$record_value['last_login']	=	local_date('Y/m/d H:i:s',$user['last_login']);
			$record_value['last_ip']	=	$user['last_ip'];
			$record_value['logins']   =   $user['logins'];
        	$record_xls[] = $record_value;
    	}
		//$record_xls[] = array('会员总数:',count($users));
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
		$this->display('user.statistic.html');
	}
	
	/**
     * 输出平台数据
     */
    function get_plat_sale()
	{
        list($conditions) = $this->getConditions();
        $statcount_arr = $this->_user_mod->get(array(
			'conditions' => '1=1'.$conditions,
			'fields' => 'COUNT(*) as usernum',		
		));
		echo '<dl class="row"><dd class="opt"><ul>';
		echo '<li><h4>会员人数：</h4><h2 class="timer">'.$statcount_arr['usernum'].'</h2><h6>人</h6></li>';
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
			
			$conditions .= " AND reg_time BETWEEN {$stime} AND {$etime} ";
            $field .= " ,DAY(FROM_UNIXTIME(reg_time+".date('Z').")) as dayval, HOUR(FROM_UNIXTIME(reg_time+".date('Z').")) as hourval ";
            $group = ' GROUP BY dayval,hourval';
			$users = $this->_user_mod->find(array(
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
            $conditions .= " AND reg_time BETWEEN {$stime} AND {$etime} ";
            $field .= ",WEEKOFYEAR(FROM_UNIXTIME(reg_time))+1 as weekval, WEEKDAY(FROM_UNIXTIME({reg_time}))+1 as dayofweekval ";
            $group = ' GROUP BY weekval,dayofweekval';
            $users = $this->_user_mod->find(array(
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
            $conditions .= " AND reg_time BETWEEN {$stime} AND {$etime} ";
            $field .= ",MONTH(FROM_UNIXTIME(reg_time)) as monthval,day(FROM_UNIXTIME(reg_time)) as dayval ";
            $group = ' GROUP BY monthval,dayval';
            $users = $this->_user_mod->find(array(
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
        $this->assign('stattype','usernum');
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
			
			$conditions .= " AND reg_time BETWEEN ".$curr_stime." AND ".$etime;
		}
		
		return 	array($conditions, $curr_stime, $etime);
	}

    /**
     * 上传头像
     *
     * @param int $user_id
     * @return mix false表示上传失败,空串表示没有上传,string表示上传文件地址
     */
    function _upload_portrait($user_id)
    {
        $file = $_FILES['portrait'];
        if ($file['error'] != UPLOAD_ERR_OK)
        {
            return '';
        }

        import('uploader.lib');
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE);
        $uploader->addFile($file);
        if ($uploader->file_info() === false)
        {
			$error = current($uploader->get_error());
            $this->json_error($error['msg']);
            return false;
        }

        $uploader->root_dir(ROOT_PATH);
        return $uploader->save('data/files/mall/portrait/' . ceil($user_id / 500), $user_id);
    }
}

?>
