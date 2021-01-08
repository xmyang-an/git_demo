<?php

class Seller_couponApp extends StoreadminbaseApp
{
    var $_coupon_mod;
    var $_store_id;
    var $_store_mod;
    var $_couponsn_mod;
    function __construct()
    {
        $this->Seller_couponApp();
    }
    function Seller_couponApp()
    {
        parent::__construct();
        $this->_store_id  = intval($this->visitor->get('manage_store'));
        $this->_store_mod =& m('store');
        $this->_coupon_mod =& m('coupon');
        $this->_couponsn_mod =& m('couponsn');
    }
    function index()
    {
		if(!IS_AJAX)
		{
			$this->import_resource(array(
				'script' => array(
					array(
						'path' => 'mobile/jquery.plugins/jquery.form.min.js',
						'attr' => '',
					),
					array(
						'path' => 'mobile/jquery.plugins/weui/js/jquery-weui.min.js',
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
						'path' => 'mobile/jquery.plugins/jquery.infinite.js',
						'attr' => ''
					)
				),
				'style' =>'mobile/jquery.plugins/weui/lib/weui.min.css,mobile/jquery.plugins/weui/css/jquery-weui.min.css'
			));
			$this->assign('infiniteParams', json_encode($_GET));
	
			$this->_config_seo('title',  Lang::get('seller_coupon') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('seller_coupon');
			$this->display('seller_coupon.index.html');
		}
		else
		{
			$page = $this->_get_page(intval($_GET['pageper']));
			$coupons = $this->_coupon_mod->find(array(
				'conditions' => 'store_id = '.$this->_store_id,
				'limit' => $page['limit'],
				'count' => true,
				'order' => 'coupon_id DESC'
			));	 
			
			if($coupons)
			{
				foreach($coupons as $key => $val)
				{
					$coupons[$key]['min_amount'] = ceil($val['min_amount']);
					$coupons[$key]['coupon_value'] = ceil($val['coupon_value']);
					
					$coupons[$key]['valid'] = 0;
					$time = gmtime();
					if($val['end_time'] == 0 || $val['end_time'] > $time)
					{
						$coupons[$key]['valid'] = 1;
						if(!$val['if_issue'])
						{
							$coupons[$key]['waitPublish'] = 1;
						}
					}
					if($val['end_time'] > 0) { // JS infinite need
						$coupons[$key]['end_time'] = local_date('Y-m-d H:i', $val['end_time']);
					}
				}
			}
							 
			$page['item_count'] = $this->_coupon_mod->getCount();
			$this->_format_page($page);
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($coupons), 'totalPage' => $page['page_count']);
			echo json_encode($data);
		}
    }

    function add()
    {
        if (!IS_POST)
        {
			$this->assign('today', gmtime());
			
			$this->import_resource(array(
				'script' => array(
					array(
						'path' => 'mobile/jquery.plugins/jquery.form.min.js',
						'attr' => '',
					),
					array(
						'path' => 'mobile/jquery.plugins/weui/js/jquery-weui.min.js',
						'attr' => '',
					)
				),
				'style' =>'mobile/jquery.plugins/weui/lib/weui.min.css,mobile/jquery.plugins/weui/css/jquery-weui.min.css'
			));
			
			$this->_config_seo('title',  Lang::get('add_coupon') . ' - ' . Lang::get('member_center'));
            $this->_get_curlocal_title('add_coupon');
            $this->display('seller_coupon.form.html');
        }
        else
        {
			$coupon_name = trim($_POST['coupon_name']);
            $coupon_value = floatval(trim($_POST['coupon_value']));
			$total        = intval($_POST['total']);
            $use_times = 1; //intval(trim($_POST['use_times'])); 不再支持一张优惠券使用多次
            $min_amount = floatval(trim($_POST['min_amount']));
			
			if(empty($coupon_name)) {
				$this->json_error('coupon_name_required');
				return;
			}
            if (empty($coupon_value) || $coupon_value < 0 )
            {
                $this->json_error('coupon_value_not');
                return;
            }
            if (empty($use_times))
            {
                $this->json_error('use_times_not_zero');
                return;
            }
            if ($min_amount < 0)
            {
                $this->json_error("min_amount_gt_zero");
                return;
            }
            $start_time = gmstr2time(trim($_POST['start_time']));
            $end_time = gmstr2time_end(trim($_POST['end_time'])) - 1 ;
            if ($end_time < $start_time)
            {
                $this->json_error('end_gt_start');
               	return;
            }
            $coupon = array(
                'coupon_name' => $coupon_name,
                'coupon_value' => $coupon_value,
				'total'		   => $total,
				'surplus'	   => $total,
                'store_id' => $this->_store_id,
                'use_times' => $use_times,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'min_amount' => $min_amount,
                'if_issue'  => intval($_POST['if_issue']) == 1 ? 1 : 0,
				'clickreceive'  => intval($_POST['clickreceive']) == 1 ? 1 : 0,
            );
			
			//if(($image = $this->_upload_image()) !== false) {
				//$coupon = array_merge($coupon, $image);				
			//}
			
            $this->_coupon_mod->add($coupon);
            if ($this->_coupon_mod->has_error())
            {
				$error = current($this->_coupon_mod->get_error());
                $this->json_error($error['msg']);
                return;
            }
			
            $this->json_result(array('ret_url' => url('app=seller_coupon')), 'add_ok');
        }
    }

    function edit()
    {
        $coupon_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (empty($coupon_id))
        {
            echo Lang::get("no_coupon");
        }
        if (!IS_POST)
        {
            $coupon = $this->_coupon_mod->get_info($coupon_id);
			$this->assign('coupon', $coupon);
			
			$this->import_resource(array(
				'script' => array(
					array(
						'path' => 'mobile/jquery.plugins/jquery.form.min.js',
						'attr' => '',
					),
					array(
						'path' => 'mobile/jquery.plugins/weui/js/jquery-weui.min.js',
						'attr' => '',
					)
				),
				'style' =>'mobile/jquery.plugins/weui/lib/weui.min.css,mobile/jquery.plugins/weui/css/jquery-weui.min.css',
			));
			
			$this->_config_seo('title',  Lang::get('edit_coupon') . ' - ' . Lang::get('member_center'));
            $this->_get_curlocal_title('edit_coupon');
            $this->display('seller_coupon.form.html');
        }
        else
        {
			$coupon_name = trim($_POST['coupon_name']);
            $coupon_value = floatval(trim($_POST['coupon_value']));
			$total		  = intval($_POST['total']);
            $use_times = 1; //intval(trim($_POST['use_times'])); 不再支持一张优惠券使用多次
            $min_amount = floatval(trim($_POST['min_amount']));
            if (empty($coupon_value) || $coupon_value < 0 )
            {
                $this->json_error('coupon_value_not');
                exit;
            }
            if (empty($use_times))
            {
                $this->json_error('use_times_not_zero');
                exit;
            }
            if ($min_amount < 0)
            {
                $this->json_error("min_amount_gt_zero");
                exit;
            }
            $start_time = gmstr2time(trim($_POST['start_time']));
            $end_time = gmstr2time_end(trim($_POST['end_time']))-1;
            
            if ($end_time < $start_time)
            {
                $this->json_error('end_gt_start');
                exit;
            }
            $coupon = array(
                'coupon_name' => $coupon_name,
                'coupon_value' => $coupon_value,
				'total'		   => $total,
				'surplus'	   => $total,
                'store_id' => $this->_store_id,
                'use_times' => $use_times,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'min_amount' => $min_amount,
                'if_issue'  => trim($_POST['if_issue']) == 1 ? 1 : 0,
				'clickreceive'  => intval($_POST['clickreceive']) == 1 ? 1 : 0,
            );
			//if(($image = $this->_upload_image()) !== false) {
				//$coupon = array_merge($coupon, $image);				
			//}
			
            $this->_coupon_mod->edit($coupon_id, $coupon);
            if ($this->_coupon_mod->has_error())
            {
                $error = current($this->_coupon_mod->get_error());
                $this->json_error($error['msg']);
                return;
            }
			
            $this->json_result(array('ret_url' => url('app=seller_coupon')),'edit_ok');
        }
    }
	
	function view()
    {
		$id = intval($_GET['id']);
        $coupon = $this->_coupon_mod->get(array(
            'conditions' => 'store_id = '.$this->_store_id.' AND coupon_id='.$id,
		));

		if(empty($coupon))
		{
			$this->show_warning('优惠券不存在!');
			exit;
		}
		
		if(!IS_AJAX)
		{
			$this->import_resource(array(
				'script' => array(
					array(
						'path' => 'mobile/jquery.plugins/jquery.form.min.js',
						'attr' => '',
					),
					array(
						'path' => 'mobile/jquery.plugins/jquery.infinite.js',
						'attr' => ''
					)
				)
			));
			
			$this->assign('infiniteParams', json_encode($_GET));
			$this->_get_curlocal_title('coupon_view');
       		$this->display('seller_coupon.view.html');
		}
		else
		{
			$page = $this->_get_page(intval($_GET['pageper']));
			$couponsns = $this->_couponsn_mod->find(array(
				'conditions' => 'coupon.coupon_id='.$id,
				'join'       => 'belongs_to_coupon,bind_user',
				'order'      => 'coupon_sn.coupon_sn desc',
				'count' => true,
				'limit' => $page['limit'],
			));
			
			if(!empty($couponsns))
			{
				$member_mod = &m('member');
				foreach ($couponsns as $k => $v){
					/*$u_info = $member_mod->get(array(
						'conditions' => 'user_id='.$v['user_id'],
						'fields'     => 'user_name'
					));
					
					$couponsns[$k]['user_name'] = $u_info['user_name'];*/
					$couponsns[$k]['status'] = ($v['remain_times'] && $v['end_time'] > gmtime()) ? 1 : 0;
				}
			}
	
			$page['item_count'] = $this->_couponsn_mod->getCount();
			$this->_format_page($page);

			$data = array('result' => array_values($couponsns), 'totalPage' => $page['page_count']);
			echo json_encode($data);
		}
    }

    function issue()
    {
        $coupon_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (empty($coupon_id))
        {
            $this->json_error("no_coupon");
            exit;
        }
        $this->_coupon_mod->edit($coupon_id, array('if_issue' => 1));
        if ($this->_coupon_mod->has_error())
        {
            $this->json_error($this->_coupon_mod->get_error());
            exit;
        }
		
		$this->json_result('','issue_success');
    }

    function drop()
    {
        $coupon_id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (empty($coupon_id))
        {
            $this->json_error('no_coupon');
            exit;
        }
        $time = gmtime();
        $coupon_ids = explode(',', $coupon_id);
        $this->_coupon_mod->drop("(if_issue = 0 OR (if_issue = 1 AND end_time < {$time})) AND coupon_id ".db_create_in($coupon_ids));
        if ($this->_coupon_mod->has_error())
        {
			$error = current($this->_coupon_mod->get_error());
            $this->json_error($error['msg']);
        }
        $this->json_result('','drop_ok');
    }

    function export()
    {
        $coupon_id = isset($_GET['id']) ? trim($_GET['id']) : '';
        
        if (!IS_POST)
        {	
			if (empty($coupon_id))
        	{
            	echo Lang::get('<div class="padding10">'.Lang::get('no_coupon').'</div>');
            	exit;
        	}
		
            header("Content-Type:text/html;charset=" . CHARSET);
            $this->display('seller_coupon.export.html');
        }
        else
        {
			if (empty($coupon_id))
        	{
            	$this->show_warning('no_coupon');
            	return;
        	}
			
            $amount = intval($_POST['amount']);
            if (!$amount)
            {
                $this->show_warning('amount_range');
                return;
            }
            $info = $this->_coupon_mod->get_info($coupon_id);
            $coupon_name = ecm_iconv(CHARSET, 'gbk', $info['coupon_name']);
            header('Content-type: application/txt');
            header('Content-Disposition: attachment; filename="coupon_' .date('Ymd'). '_' .$coupon_name.'.txt"');
            $sn_array = $this->generate($amount, $coupon_id);
            $crlf = get_crlf();
            foreach ($sn_array as $val)
            {
                echo $val['coupon_sn'] . $crlf;
            }
        }
    }

    function extend()
    {
        $coupon_id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (empty($coupon_id))
        {
            echo Lang::get('<div class="padding10">'.Lang::get('no_coupon').'</div>');
            exit;
        }
        if (!IS_POST)
        {
            header("Content-Type:text/html;charset=" . CHARSET);
            $this->display("seller_coupon.extend.html");
        }
        else
        {
            if (empty($_POST['user_name']))
            {
                $this->json_error("involid_data");
                return;
            }
            $user_name = str_replace(array("\r","\r\n"), "\n", trim($_POST['user_name']));
            $user_name = explode("\n", $user_name);
            $user_mod =&m ('member');
            $users = $user_mod->find(db_create_in($user_name, 'user_name'));
            if (empty($users))
            {
                $this->json_error('involid_data');
                exit;
            }
            if (count($users) > 30)
            {
                $this->pop_warning("amount_gt");
                return;
            }
            else
            {
                $users = $this->assign_user($coupon_id, $users);
                $store = $this->_store_mod->get_info($this->_store_id);
                $coupon = $this->_coupon_mod->get_info($coupon_id);
                $coupon['store_name'] = $store['store_name'];
                $coupon['store_id'] = $this->_store_id;
                $this->_message_to_user($users, $coupon);
                $this->_mail_to_user($users, $coupon);
				
                $this->json_result('', "coupon_extend_ok");
            }
        }
    }

    function _message_to_user($users, $coupon)
    {
        $ms =& ms();
        foreach ($users as $key => $val)
        {
            $content = get_msg('touser_send_coupon', array(
            'price' => $coupon['coupon_value'],
            'start_time' =>  local_date('Y-m-d',$coupon['start_time']),
            'end_time' => local_date("Y-m-d", $coupon['end_time']),
            'coupon_sn' => $val['coupon']['coupon_sn'],
            'min_amount' => $coupon['min_amount'],
            'url' => SITE_URL . '/' . url('app=store&id=' . $coupon['store_id']),
            'store_name' => $coupon['store_name'],
            ));
            $msg_id = $ms->pm->send(MSG_SYSTEM, $val['user_id'], '',$content);
        }
    }

    function _mail_to_user($users, $coupon)
    {
        foreach ($users as $val)
        {
            $mail = get_mail('touser_send_coupon', array('user' => $val, 'coupon' => $coupon));
            if (!$mail)
            {
                continue;
            }
            $this->_mailto($val['email'], addslashes($mail['subject']), addslashes($mail['message']));
        }
    }

    function assign_user($id, $users)
    {
        $_user_mod =& m('member');
        $count = count($users);
        $users = array_values($users);
        $arr = $this->generate($count, $id);
        $i = 0;
        foreach ($users as $key => $user)
        {
      		$users[$key]['coupon'] = $arr[$i];
        	$_user_mod->createRelation('bind_couponsn', $user['user_id'], array($arr[$i]['coupon_sn'] => array('coupon_sn' =>$arr[$i]['coupon_sn'])));
      		$i = $i + 1;
        }
        return $users;
    }

    function generate($num, $id)
    {
        $coupon = $this->_coupon_mod->get(array('fields' => 'use_times', 'conditions' => 'store_id = ' . $this->_store_id . ' AND coupon_id = ' . $id));
		
        if ($num > 1000)
        {
            $num = 1000;
        }
        if ($num < 1)
        {
            $num = 1;
        }
        $times = $coupon['use_times'];
        $add_data = array();
        $str = '';
        $pix = 0;
        if (file_exists(ROOT_PATH . '/data/generate.txt'))
        {
            $s = file_get_contents(ROOT_PATH . '/data/generate.txt');
            $pix = intval($s);
        }
        $max = $pix + $num;
        file_put_contents(ROOT_PATH . '/data/generate.txt', $max);
        $couponsn = '';
        $tmp = '';
        $cpm = '';
        $str = '';
        for ($i = $pix + 1; $i <= $max; $i++ )
        {
            $cpm = sprintf("%08d", $i);
            $tmp = mt_rand(1000, 9999);
            $couponsn = $cpm . $tmp;
            $str .= "('{$couponsn}', {$id}, {$times}),";
            $add_data[] = array(
                'coupon_sn' => $couponsn,
                'coupon_id' => $id,
                'remain_times' => $times,
                );
        }
        $string = substr($str,0, strrpos($str, ','));
        $this->_couponsn_mod->db->query("INSERT INTO {$this->_couponsn_mod->table} (coupon_sn, coupon_id, remain_times) VALUES {$string}", 'SILENT');
        return $add_data;
    }

    function _sql_insert($data)
    {
        $str = '';
        foreach ($data as $val)
        {
            $str .= "('{$val['coupon_sn']}', {$val['coupon_id']}, {$val['remain_times']}),";
        }
        $string = substr($str,0, strrpos($str, ','));
        $res = $this->_couponsn_mod->db->query("INSERT INTO {$this->_couponsn_mod->table} (coupon_sn, coupon_id, remain_times) VALUES {$string}", 'SILENT');
        $error = $this->_couponsn_mod->db->errno();
        return array('res' => $res, 'errno' => $error);
    }

    function _create_random($num, $id, $times)
    {
        $arr = array();
        for ($i = 1; $i <= $num; $i++)
        {
            $arr[$i]['coupon_sn'] =  mt_rand(10000, 99999);
            $arr[$i]['coupon_id'] = $id;
            $arr[$i]['remain_times'] = $times;
        }
        return $arr;
    }
}

?>