<?php

class My_couponApp extends MemberbaseApp 
{
    var $_user_mod;
    var $_store_mod;
    var $_coupon_mod;
    
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
						'path' => 'mobile/jquery.plugins/jquery.infinite.js',
						'attr' => ''
					)
				),
				'style' =>  'mobile/jquery.plugins/weui/lib/weui.min.css,mobile/jquery.plugins/weui/css/jquery-weui.min.css',
			));
			$this->assign('infiniteParams', json_encode($_GET));

       
	   		$this->_config_seo('title',  Lang::get('my_coupon') . ' - ' . Lang::get('member_center'));
	   		$this->_get_curlocal_title('my_coupon');
       		$this->display('my_coupon.index.html');
		}
		else
		{
			
			$this->_user_mod =& m('member');
			$this->_store_mod =& m('store');
			$this->_coupon_mod =& m('coupon');
			
			$page = $this->_get_page(intval($_GET['pageper']));
			$msg = $this->_user_mod->findAll(array(
				'conditions' => 'user_id = ' . $this->visitor->get('user_id'),
				'count' => true,
				'limit' => $page['limit'],
				'include' => array('bind_couponsn' => array())
			));
			$page['item_count'] = $this->_user_mod->getCount();
			$this->_format_page($page);
			
			$coupon = array();
			$coupon_ids = array();
			$msg = current($msg);
		   	if (!empty($msg['coupon_sn']))
		   	{
			   	foreach ($msg['coupon_sn'] as $key=>$val)
			   	{
				   	$coupon_tmp = $this->_coupon_mod->get(array(
						'fields' => "this.*,store.store_name,store_logo,store.store_id",
						'conditions' => 'coupon_id = ' . $val['coupon_id'],
						'join' => 'belong_to_store',
					));
					
					empty($coupon_tmp['store_logo']) && $coupon_tmp['store_logo'] = Conf::get('default_store_logo');
					$coupon_tmp['min_amount'] = ceil($coupon_tmp['min_amount']);
					$coupon_tmp['coupon_value'] = ceil($coupon_tmp['coupon_value']);
					
					$coupon_tmp['valid'] = 0;
					$time = gmtime();
					if (($val['remain_times'] > 0) && ($coupon_tmp['end_time'] == 0 || $coupon_tmp['end_time'] > $time))
					{
						$coupon_tmp['valid'] = 1;
					}
					if($coupon_tmp['end_time'] > 0) $coupon_tmp['end_time'] = local_date('Y-m-d', $coupon_tmp['end_time']);
				   	$coupon[$key] = array_merge($val, $coupon_tmp);
			   	}
		   	}
					   
		   	// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($coupon), 'totalPage' => $page['page_count']);
			echo json_encode($data);
	   }
    }
    
    function bind()
    {
        if (!IS_POST)
        {
			$this->import_resource('mobile/jquery.plugins/jquery.form.min.js');

			$this->_config_seo('title',  Lang::get('bind') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('bind');
            $this->display('my_coupon.form.html');
        }
        else 
        {
            $coupon_sn = isset($_POST['coupon_sn']) ? trim($_POST['coupon_sn']) : '';
            if (empty($coupon_sn))
            {
                $this->json_error('coupon_sn_not_empty');
				return;
            }
			
            $coupon_sn_mod =&m ('couponsn');
            $coupon = $coupon_sn_mod->get_info($coupon_sn);
            if (empty($coupon))
            {
                $this->json_error('coupon_sn_not_empty_invalid');
                return;
            }
			if(!$coupon_sn_mod->createRelation('bind_user', $coupon_sn, $this->visitor->get('user_id'))) {
				$this->json_error('coupon_bind_fail');
				return;
			}
            $this->json_result('', 'coupon_bind_ok');
        }
    }
    
    function drop()
    {
        if (!isset($_GET['id']) || empty($_GET['id']))
        {
            $this->json_error('drop_error');
            return;
        }
        $ids = explode(',', trim($_GET['id']));
        $couponsn_mod =& m('couponsn');
        $couponsn_mod->unlinkRelation('bind_user', db_create_in($ids, 'coupon_sn'));
        if ($couponsn_mod->has_error())
        {
			$error = current($couponsn_mod->get_error());
           	$this->json_error($error['msg']);
           	return;
        }
		
       	$this->json_result('', 'drop_ok');
    }
    
}

?>