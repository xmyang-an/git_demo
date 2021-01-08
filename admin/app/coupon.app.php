<?php

class CouponApp extends BackendApp
{
    var $_coupon_mod;
    var $_store_id;
    var $_store_mod;
    var $_couponsn_mod;
    function __construct()
    {
        $this->CouponApp();
    }
    function CouponApp()
    {
        parent::__construct();
        $this->_store_id  = 0;
        $this->_store_mod =& m('store');
        $this->_coupon_mod =& m('coupon');
        $this->_couponsn_mod =& m('couponsn');
    }
    function index()
    {
		$this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js',
		));
        $this->display('coupon.index.html');
    }
	
	function get_coupon_xml()
	{
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
        $coupons = $this->_coupon_mod->find(array(
            'conditions' => 'store_id = 0',
            'limit' => $page['limit'],
            'count' => true,
			'order' => 'coupon_id desc'
        ));

        $page['item_count'] = $this->_coupon_mod->getCount();
        $data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($coupons as $k => $v){
			$list = array();
			$list['operation'] = '';
			if($v['if_issue']){
				$list['operation'] = "<a class='btn blue' href='index.php?app=coupon&act=view&id={$v['coupon_id']}'><i class='fa fa-eye'></i>领取记录</a>";
				if($v['end_time'] <= gmtime())
				{
					$list['operation'] .= "<a class='btn red' onclick=\"fg_delete({$v['coupon_id']},'coupon')\"><i class='fa fa-trash-o'></i>删除</a>";
				}
			}
			
			if(!$v['if_issue']){
				$list['operation'] = "><a class='btn blue'  onclick=\"goConfirm('".Lang::get('issue_desc')."','index.php?app=coupon&act=issue&id={$v['coupon_id']}',true)\"><i class='fa fa-pencil-square-o'></i>发布</a><a class='btn blue' href='index.php?app=coupon&act=edit&id={$v['coupon_id']}'><i class='fa fa-pencil-square-o'></i>编辑</a><a class='btn red' onclick=\"fg_delete({$v['coupon_id']},'coupon')\"><i class='fa fa-trash-o'></i>删除</a";
			}
			
			$list['coupon_name'] = $v['coupon_name'];
			$list['coupon_value'] = $v['coupon_value'];
			$list['total'] = $v['total'] ?  $v['total'] : Lang::get('no_limit');
			$list['surplus'] = ($v['surplus'] > 0) ? $v['surplus'] : Lang::get('no_limit');
			$list['time_limit'] = local_date('Y-m-d', $v['start_time']).Lang::get('to').($v['end_time'] ? local_date('Y-m-d', $v['end_time']) : Lang::get('no_limit'));
			$list['min_amount'] = $v['min_amount'] ? sprintf(Lang::get('limit_desc'),$v['min_amount']) : Lang::get('no_limit');
			$data['list'][$k] = $list;
		}

		$this->flexigridXML($data);	
	}
	
	function view()
	{
		$id = intval($_GET['id']);
		$coupon = $this->_coupon_mod->get('coupon_id='.$id.' AND if_issue=1 AND store_id=0');
		if(empty($coupon))
		{
			$this->show_warning('优惠券不存在!');
			exit;
		}
		
		$this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js',
		));
        $this->display('coupon.view.html');
	}
	
	function get_view_xml()
	{
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$id = intval($_GET['id']);
		
        $coupon = $this->_coupon_mod->get(array(
            'conditions' => 'store_id = 0 AND coupon_id='.$id,
		));

		if(empty($coupon))
		{
			$this->show_warning('优惠券不存在!');
			exit;
		}
		
		$page   =   $this->_get_page($pre_page);
		$couponsns = $this->_couponsn_mod->find(array(
			'conditions' => 'coupon.coupon_id='.$id,
			'join'       => 'belongs_to_coupon,bind_user',
			'order'      => 'coupon_sn.coupon_sn desc',
			'count' => true,
            'limit' => $page['limit'],
		));

        $page['item_count'] = $this->_couponsn_mod->getCount();
        $data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		$member_mod = &m('member');
		foreach ($couponsns as $k => $v){
			$list = array();
			
			$u_info = $member_mod->get(array(
				'conditions' => 'user_id='.$v['user_id'],
				'fields'     => 'user_name'
			));
			
			$list['coupon_sn'] = $v['coupon_sn'];
			$list['user_name'] = $u_info['user_name'];
			$list['status'] = (!$v['remain_times'] || $v['end_time'] < gmtime()) ?  '<span class="no"><i class="fa fa-ban"></i>否</span>' : '<span class="yes"><i class="fa fa-check-circle"></i>是</span>';
			$data['list'][$k] = $list;
		}

		$this->flexigridXML($data);	
	}

    function add()
    {
        if (!IS_POST)
        {
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
				'style' =>  'jquery.ui/themes/smoothness/jquery.ui.css',
			));
		
            header("Content-Type:text/html;charset=" . CHARSET);
            $this->assign('today', gmtime());
            $this->display('coupon.form.html');
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
				exit;
			}
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
            $end_time = gmstr2time_end(trim($_POST['end_time'])) - 1 ;
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
                'store_id' => 0,
                'use_times' => $use_times,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'min_amount' => $min_amount,
                'if_issue'  => intval($_POST['if_issue']) == 1 ? 1 : 0,
				'clickreceive'  => 1,
            );
			
			if(($image = $this->_upload_image()) !== false) {
				$coupon = array_merge($coupon, $image);				
			}
			
            $this->_coupon_mod->add($coupon);
            if ($this->_coupon_mod->has_error())
            {
				$error = $this->_coupon_mod->get_error();
				$msg = current($error);
                $this->json_error($msg['msg']);
                exit;
            }
			
            $this->json_result('', '成功添加优惠券');
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
            $this->display('coupon.form.html');
        }
        else
        {
            $$coupon_name = trim($_POST['coupon_name']);
            $coupon_value = floatval(trim($_POST['coupon_value']));
			$total		  = intval($_POST['total']);
            $use_times = 1; //intval(trim($_POST['use_times'])); 不再支持一张优惠券使用多次
            $min_amount = floatval(trim($_POST['min_amount']));
			
			if(empty($coupon_name)) {
				$this->json_error('coupon_name_required');
				exit;
			}
			
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
                'store_id' => 0,
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
                $error = $this->_coupon_mod->get_error();
				$msg = current($error);
                $this->json_error($msg['msg']);
                exit;
            }
            $this->json_result('','成功编辑优惠券');
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
            $error = $this->_coupon_mod->get_error();
			$msg = current($error);
            $this->json_error($msg['msg']);
            exit;
        }

		$this->json_result('','issue_success');
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
            	$data['image'] = $uploader->save('data/files/mall/coupon', $uploader->random_filename());
			}
        }
        return $data;
    }
	/* 异步删除附件 */
    function drop_image()
    {
        $coupon_id = isset($_GET['coupon_id']) ? intval($_GET['coupon_id']) : 0;
		$coupon = $this->_coupon_mod->get($coupon_id);
		if($coupon && $coupon['image'] && ($coupon['store_id'] == 0)) {
			if($this->_coupon_mod->edit($coupon_id, array('image' => ''))) {
				@unlink(ROOT_PATH . '/' . $coupon['image']);
				$this->json_result('drop_ok');
            	return;
			}
		}
		$this->json_error('drop_error');
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
            'back_list', 'index.php?app=coupon');
    }
}

?>