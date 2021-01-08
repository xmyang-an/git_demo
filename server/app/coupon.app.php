<?php

class CouponApp extends ApibaseApp
{
	var $_coupon_mod;
	var $_couponsn_mod;
	
	function __construct(){
		parent::__construct();
		
		$this->_coupon_mod = &m('coupon');
		$this->_couponsn_mod = &m('couponsn');
	}
	
    function listing()
    {
		$store_id = empty($this->PostData['store_id']) ? 0 : intval($this->PostData['store_id']);
        if (!$store_id)
        {
            $this->json_fail('Store_is_not_exsist');
            return;
        }
		
		$page = $this->_get_page((isset($this->PostData['perpage']) && $this->PostData['perpage'] > 0) ? $this->PostData['perpage'] : 10);
		
		$coupon_mod = &m('coupon');
		$coupons = $this->_coupon_mod->find(array(
			'conditions' => 'clickreceive = 1 AND if_issue = 1 AND (total = 0 OR (total > 0 && surplus > 0)) AND  coupon.end_time > '.gmtime().' AND store_id='.$store_id,
			'limit'      => $page['limit'],
			'count'      => true
		));
		
		$page['item_count'] = $this->_coupon_mod->getCount();
		
		$this->json_success($coupons);
    }
	
	function receive()
	{
		$user_id = $this->PostData['user_id'];
		if(!$user_id)
		{
			$this->json_fail('login_please');
			exit;
		}
		
		$coupon_id = intval($this->PostData['id']) ? intval($this->PostData['id']) : 0;
		$coupon = $this->_coupon_mod->get('coupon_id='.$coupon_id.' AND clickreceive = 1 AND if_issue = 1 AND end_time >'.gmtime());
		if($coupon['store_id'] == $user_id) {
			$this->json_fail('not_receive_self');
			exit;
		}
		
		if(empty($coupon))
		{
			$this->json_fail('not_existed');
			exit;
		}
		
		if($coupon['total'] > 0 && $coupon['surplus'] <= 0)
		{
			$this->json_fail('coupon_receive_all');
			exit;
		}
		
		$record = db()->getAll("SELECT *FROM ".DB_PREFIX."user_coupon uc LEFT JOIN ".DB_PREFIX."coupon_sn sn ON uc.coupon_sn=sn.coupon_sn LEFT JOIN ".DB_PREFIX."coupon c on sn.coupon_id=c.coupon_id WHERE user_id=".$user_id.' AND remain_times > 0 AND c.coupon_id='.$coupon['coupon_id']);
		
		if(!empty($record))
		{
			$this->json_fail('coupon_has_receive');
			exit;
		}
		
		$couponsn = $this->_generate(1, $coupon['coupon_id']);
		
		$user_mod = &m('member');
        $user_mod->createRelation('bind_couponsn', $user_id, array($couponsn[0]['coupon_sn'] => array('coupon_sn' =>$couponsn[0]['coupon_sn'])));
		
		$coupon['surplus'] > 0 && $this->_coupon_mod->edit($coupon['coupon_id'], "surplus = surplus - 1");
		
		$this->json_success('',Lang::get('receive_success'));
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
