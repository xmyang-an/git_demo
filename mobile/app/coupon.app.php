<?php

class CouponApp extends MallbaseApp
{
	var $_coupon_mod;
	var $_couponsn_mod;
	
	function __construct()
	{
		$this->CouponApp();
	}
	function CouponApp()
	{
		parent::__construct();
		
		$this->_coupon_mod = &m('coupon');
		$this->_couponsn_mod = &m('couponsn');
	}
	
	function index()
	{
	   if(!IS_AJAX)
	   {
		   $this->import_resource(array('script' => 'mobile/jquery.plugins/jquery.infinite.js'));
		   $this->assign('infiniteParams', json_encode($_GET));
			
		   $this->_get_curlocal_title('voucher_center');
		   $this->_config_seo('title', Lang::get('voucher_center') . ' - ' . Conf::get('site_title'));   
		   $this->display('coupon.index.html');
	   }
	   else
	   {
		   /*$conditions = ($_GET['type'] == 'store')?' AND coupon.store_id > 0 ':' AND coupon.store_id = 0';*/
		   $page = $this->_get_page(intval($_GET['pageper']) > 0 ? intval($_GET['pageper']) : 10);
		   $coupons = $this->_coupon_mod->find(array(
				'conditions' => 'clickreceive = 1 AND if_issue = 1 AND (total = 0 OR (total > 0 && surplus > 0)) AND  coupon.end_time > '.gmtime().$conditions,
				'join'    => 'belong_to_store',
				'fields'  => 'this.*,store_logo',
				'limit'   => $page['limit'],
				'count'   => true 
		   ));
		   
		   if(!empty($coupons))
		   {
			   foreach($coupons as $key=>$val)
			   {
				   empty($val['store_logo']) && $coupons[$key]['store_logo'] = Conf::get('default_store_logo'); 
				   $coupons[$key]['start_time'] = local_date('Y-m-d', $val['start_time']);
				   $coupons[$key]['end_time'] = local_date('Y-m-d', $val['end_time']);
				   $coupons[$key]['coupon_value'] = floatval($val['coupon_value']);
			   	   $coupons[$key]['min_amount'] = floatval($val['min_amount']);
			   }
		   }

		   $data = array('result' => array_values($coupons), 'totalPage' => $page['page_count']);
		   echo json_encode($data);
	   }
	}
	
	// 优惠券列表
	function search()
	{
		$store_id = intval($_GET['store_id']);
		
		$coupon_mod = &m('coupon');
		$coupons = $coupon_mod->find(array(
			'conditions' => 'clickreceive = 1 AND if_issue = 1 AND (total = 0 OR (total > 0 && surplus > 0)) AND  coupon.end_time > '.gmtime().' AND (store_id='.$store_id.' OR store_id=0)',
			'order' => 'store_id asc,coupon_value asc'
		));
		$this->assign('coupons', $coupons);
		
		header('Content-Type:text/html;charset=' . CHARSET);
		$this->_config_seo('title', Lang::get('coupon_search') . ' - ' . Lang::get('site_title'));
		$this->_get_curlocal_title('search_coupon');
    	$this->display('coupon.search.html');
	}
	
	// 领取优惠券
	function receive()
	{
		$user_id = $this->visitor->get('user_id');
		if(!$user_id)
		{
			$this->json_error('login_please');
			exit;
		}
		
		$coupon_id = intval($_GET['id']) ? intval($_GET['id']) : 0;
		$coupon = $this->_coupon_mod->get('coupon_id='.$coupon_id.' AND clickreceive = 1 AND if_issue = 1 AND end_time >'.gmtime());
		if($coupon['store_id'] == $this->visitor->get('user_id')) {
			$this->json_error('not_receive_self');
			exit;
		}
		
		if(empty($coupon))
		{
			$this->json_error('not_existed');
			exit;
		}
		
		if($coupon['total'] > 0 && $coupon['surplus'] <= 0)
		{
			$this->json_error('coupon_receive_all');
			exit;
		}
		
		$record = db()->getAll("SELECT *FROM ".DB_PREFIX."user_coupon uc LEFT JOIN ".DB_PREFIX."coupon_sn sn ON uc.coupon_sn=sn.coupon_sn LEFT JOIN ".DB_PREFIX."coupon c on sn.coupon_id=c.coupon_id WHERE user_id=".$user_id.' AND remain_times > 0 AND c.coupon_id='.$coupon['coupon_id']);
		
		if(!empty($record))
		{
			$this->json_error('coupon_has_receive');
			exit;
		}
		
		$couponsn = $this->_generate(1, $coupon['coupon_id']);
		
		$user_mod = &m('member');
        $user_mod->createRelation('bind_couponsn', $user_id, array($couponsn[0]['coupon_sn'] => array('coupon_sn' =>$couponsn[0]['coupon_sn'])));
		
		$coupon['surplus'] > 0 && $this->_coupon_mod->edit($coupon['coupon_id'], "surplus = surplus - 1");
		
		$this->json_result(array('ret_url' => NULL), Lang::get('receive_success'));
	}
	
	function _generate($num, $id)
    {
        $coupon = $this->_coupon_mod->get(array('fields' => 'use_times', 'conditions' => ' coupon_id = ' . $id));
		
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
}

?>