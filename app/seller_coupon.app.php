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
        $page = $this->_get_page(10);
        $coupon = $this->_coupon_mod->find(array(
            'conditions' => 'store_id = '.$this->_store_id,
            'limit' => $page['limit'],
            'count' => true,
			'order' => 'coupon_id DESC'
        ));
        $this->_curlocal(Lang::get('seller_coupon'), 'index.php?app=seller_coupon', LANG::get('coupons_list'));
        $page['item_count'] = $this->_coupon_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);

        $this->_curitem('seller_coupon');
        $this->_curmenu('coupons_list');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('seller_coupon'));
        $this->assign('coupons', $coupon);
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
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/smoothness/jquery.ui.css',
        ));
        $this->assign('today', gmtime());
        $this->display('seller_coupon.index.html');
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
		
        $page = $this->_get_page(10);
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
				$u_info = $member_mod->get(array(
					'conditions' => 'user_id='.$v['user_id'],
					'fields'     => 'user_name'
				));
				
				$couponsns[$k]['user_name'] = $u_info['user_name'];
				$couponsns[$k]['status'] = ($v['remain_times'] && $v['end_time'] > gmtime()) ? 1 : 0;
			}
		}

        $this->_curlocal(Lang::get('seller_coupon'), 'index.php?app=seller_coupon', LANG::get('coupon_view'));
        $page['item_count'] = $this->_couponsn_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);

        $this->_curitem('seller_coupon');
        $this->_curmenu('coupon_view');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('coupon_view'));
        $this->assign('couponsns', $couponsns);
        $this->display('seller_coupon.view.html');
    }

    function add()
    {
        if (!IS_POST)
        {
            header("Content-Type:text/html;charset=" . CHARSET);
            $this->assign('today', gmtime());
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
				$this->pop_warning('coupon_name_required');
				exit;
			}
            if (empty($coupon_value) || $coupon_value < 0 )
            {
                $this->pop_warning('coupon_value_not');
                exit;
            }
            if (empty($use_times))
            {
                $this->pop_warning('use_times_not_zero');
                exit;
            }
            if ($min_amount < 0)
            {
                $this->pop_warning("min_amount_gt_zero");
                exit;
            }
            $start_time = gmstr2time(trim($_POST['start_time']));
            $end_time = gmstr2time_end(trim($_POST['end_time'])) - 1 ;
            if ($end_time < $start_time)
            {
                $this->pop_warning('end_gt_start');
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
                'if_issue'  => intval($_POST['if_issue']) == 1 ? 1 : 0,
				'clickreceive'  => intval($_POST['clickreceive']) == 1 ? 1 : 0,
            );
			
			if(($image = $this->_upload_image()) !== false) {
				$coupon = array_merge($coupon, $image);				
			}
			
            $this->_coupon_mod->add($coupon);
            if ($this->_coupon_mod->has_error())
            {
                $this->pop_warning($this->_coupon_mod->get_error());
                exit;
            }
            $this->pop_warning('ok', 'coupon_add');
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
            header("Content-Type:text/html;charset=" . CHARSET);
            $coupon = $this->_coupon_mod->get_info($coupon_id);
            $this->assign('coupon', $coupon);
            $this->display('seller_coupon.form.html');
        }
        else
        {
			$coupon_name = trim($_POST['coupon_name']);
            $coupon_value = floatval(trim($_POST['coupon_value']));
			$total		  = intval($_POST['total']);
            $use_times = 1; //intval(trim($_POST['use_times'])); 不再支持一张优惠券使用多次
            $min_amount = floatval(trim($_POST['min_amount']));
			if(empty($coupon_name)) {
				$this->pop_warning('coupon_name_required');
				exit;
			}
			
			if (empty($coupon_value) || $coupon_value < 0 )
            {
                $this->pop_warning('coupon_value_not');
                exit;
            }
            if (empty($use_times))
            {
                $this->pop_warning('use_times_not_zero');
                exit;
            }
            if ($min_amount < 0)
            {
                $this->pop_warning("min_amount_gt_zero");
                exit;
            }
            $start_time = gmstr2time(trim($_POST['start_time']));
            $end_time = gmstr2time_end(trim($_POST['end_time']))-1;
            if ($end_time < $start_time)
            {
                $this->pop_warning('end_gt_start');
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
			if(($image = $this->_upload_image()) !== false) {
				$coupon = array_merge($coupon, $image);				
			}
			
            $this->_coupon_mod->edit($coupon_id, $coupon);
            if ($this->_coupon_mod->has_error())
            {
                $this->pop_warning($this->_coupon_mod->get_error());
                exit;
            }
            $this->pop_warning('ok','coupon_edit');
        }
    }

    function issue()
    {
        $coupon_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (empty($coupon_id))
        {
            $this->show_warning("no_coupon");
            exit;
        }
        $this->_coupon_mod->edit($coupon_id, array('if_issue' => 1));
        if ($this->_coupon_mod->has_error())
        {
            $this->show_message($this->_coupon_mod->get_error());
            exit;
        }
        $this->show_message('issue_success',
            'back_list', 'index.php?app=seller_coupon');
    }

    function drop()
    {
        $coupon_id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (empty($coupon_id))
        {
            $this->show_warning('no_coupon');
            exit;
        }
        $time = gmtime();
        $coupon_ids = explode(',', $coupon_id);
        $this->_coupon_mod->drop("(if_issue = 0 OR (if_issue = 1 AND end_time < {$time})) AND coupon_id ".db_create_in($coupon_ids));
        if ($this->_coupon_mod->has_error())
        {
            $this->show_warning($this->_coupon_mod->get_error());
        }
        $this->show_message('drop_ok',
            'back_list', 'index.php?app=seller_coupon');
    }

    function export()
    {
        $coupon_id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (empty($coupon_id))
        {
            echo Lang::get('no_coupon');
            exit;
        }
        if (!IS_POST)
        {
            header("Content-Type:text/html;charset=" . CHARSET);
            $this->assign('id', $coupon_id);
            $this->display('seller_coupon.export.html');
        }
        else
        {
            $amount = intval(trim($_POST['amount']));
            if (empty($amount))
            {
                $this->pop_warning('involid_data');
                exit;
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
            echo Lang::get('no_coupon');
            exit;
        }
        if (!IS_POST)
        {
            header("Content-Type:text/html;charset=" . CHARSET);
            $this->assign('id', $coupon_id);
            $this->assign('send_model', Lang::get('send_model'));
            $this->display("seller_coupon.extend.html");
        }
        else
        {
            if (empty($_POST['user_name']))
            {
                $this->pop_warning("involid_data");
                exit;
            }
            $user_name = str_replace(array("\r","\r\n"), "\n", trim($_POST['user_name']));
            $user_name = explode("\n", $user_name);
            $user_mod =&m ('member');
            $users = $user_mod->find(db_create_in($user_name, 'user_name'));
            if (empty($users))
            {
                $this->pop_warning('involid_data');
                exit;
            }
            if (count($users) > 30)
            {
                $this->pop_warning("amount_gt");
                exit;
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
                $this->pop_warning("ok","coupon_extend");
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
	
	/**
     * 上传文件
     *
     */
    function _upload_image()
    {
        import('uploader.lib');
        $data      = array();
    
        $file = $_FILES['image'];
        if ($file['error'] == UPLOAD_ERR_OK && $file !='')
        {
            $uploader = new Uploader();
            $uploader->allowed_type(IMAGE_FILE_TYPE);
            $uploader->addFile($file);
            if ($uploader->file_info() !== false)
            {
            	$uploader->root_dir(ROOT_PATH);
            	$data['image'] = $uploader->save('data/files/store_' . $this->_store_id . '/coupon', $uploader->random_filename());
			}
        }
        return $data;
    }
	/* 异步删除附件 */
    function drop_image()
    {
        $coupon_id = isset($_GET['coupon_id']) ? intval($_GET['coupon_id']) : 0;
		$coupon = $this->_coupon_mod->get($coupon_id);
		if($coupon && $coupon['image'] && ($coupon['store_id'] == $this->visitor->get('manage_store'))) {
			if($this->_coupon_mod->edit($coupon_id, array('image' => ''))) {
				@unlink(ROOT_PATH . '/' . $coupon['image']);
				$this->json_result('drop_ok');
            	return;
			}
		}
		$this->json_error('drop_error');
    }

    function _get_member_submenu()
    {
        $menus = array(
            array(
                'name'  => 'coupons_list',
                'url'   => 'index.php?app=seller_coupon',
            ),
        );
		
		if(ACT == 'view')
		{
			$menus[] = array(
					'name'  => 'coupon_view',
					'url'   => 'index.php?app=seller_coupon&act=view',
			);
		}
        return $menus;
    }
}

?>