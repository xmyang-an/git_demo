<?php

class StoreApp extends ApibaseApp
{
    function baseinfo()
    {
        $id = empty($this->PostData['id']) ? 0 : intval($this->PostData['id']);
        if (!$id)
        {
            $this->json_fail('Store_is_not_exsist');
            return;
        }
		
        $cache_server =& cache_server();
        $key = 'get_store_baseinfo_' .$this->PostData['id'];
        $store = $cache_server->get($key);
        //if ($store === false)
		if(1==1)
        {
            $store_mod  =& m('store');
			
            $store = $store_mod->get_info($this->PostData['id']);
            if (empty($store))
            {
                $this->json_fail('the_store_not_exist');
                exit;
            }
            if ($store['state'] == 2)
            {
                $this->json_fail('the_store_is_closed');
                exit;
            }
			
			empty($store['store_logo']) && $store['store_logo'] = Conf::get('default_store_logo');
			if(stripos($store['store_logo'], '//:') == FALSE) {
				$store['store_logo'] = SITE_URL . '/' . $store['store_logo'];
			}
			
			empty($store['wap_store_banner']) && $store['wap_store_banner'] = site_url().'/static/images/default_store_banner.jpg';
			if(stripos($store['wap_store_banner'], '//:') == FALSE) {
				$store['store_banner'] = SITE_URL . '/' . $store['wap_store_banner'];
			}
			
			$goods_mod =& m('goods');
            $store['goods_count'] = $goods_mod->get_count_of_store($this->PostData['id']);
			
			$collects = db()->getOne("SELECT count(*) FROM ".DB_PREFIX."collect c WHERE type='store' AND item_id=".$this->PostData['id']);
			if($collects >= 10000) {
				$collects = ($collects/10000).'万';
			}
			
			$store['region_name'] = str_replace('中国','',$store['region_name']);
			
			$store['collects'] = $collects;
			
			$store['industy_compare'] = Psmb_init()->get_industry_avg_evaluation($this->PostData['id']);
			
			$step = intval(Conf::get('upgrade_required'));
            $step < 1 && $step = 5;
            $store['credit_image'] = site_url(). '/static/images/' . $store_mod->compute_credit($store['credit_value'], $step);

            $cache_server->set($key, $store, 1800);
        }
		
		$this->json_success($store);
    }
	
	function GetStoreQrcode()
	{
		$this->_checkUserAccess();
		
		$file = $this->GetWxMPQRCode(array(
			'user_id' => $this->PostData['user_id'],
			'id'      => $this->PostData['id'],
			'page'    => 'pages/store/index'
		));
		
		$this->json_success($file);
	}
	
	function buyIntegral()
	{
		$id = empty($this->PostData['id']) ? 0 : intval($this->PostData['id']);
        if (!$id)
        {
            $this->json_fail('store_is_not_exsist');
            return;
        }
		
		$data = array();
		
		$integral_mod = &m('integral');
		if($integral_mod->_get_sys_setting('integral_enabled'))
		{
			$store_mod  =& m('store');
            $store = $store_mod->get(array(
				'conditions' => $this->PostData['id'],
				'fields'     => 'sgrade',
			));
			
			// 购买商品可获得多少积分
			$integralRadio = $integral_mod->_get_sys_setting(array('buying_integral', $store['sgrade']));
				
			if($integralRadio > 0 && $integralRadio <=1) {
				$data['radio'] = $integralRadio;
			}
		}
		
		$this->json_success($data);
	}
	
	function collectinfo(){
		$data = array();
		
		$collects = db()->getOne("SELECT count(*) FROM ".DB_PREFIX."collect c WHERE type='store' AND item_id=".$this->PostData['id']);
		if($collects >= 10000) {
			$collects = ($collects/10000).'万';
		}
		
		$data['collect_count'] = $collects;
		$data['collected'] = 0;
		
		$member_mod = &m('member');
		if($this->PostData['user_id'] > 0)
		{
			$collect = $member_mod->get(array(
				'join' => 'collect_store',
				'conditions' => 'member.user_id='.$this->PostData['user_id'].' AND type="store" AND item_id='.$this->PostData['id'],
				'fields' => 'item_id'
			));
				
			if($collect['item_id'] > 0)
			{
				$data['collected'] = 1;
			}
		}
		
		$this->json_success($data);
	}
	
	function collect()
	{
		$user_id = empty($this->PostData['user_id'])  ? 0 : intval($this->PostData['user_id']);
		$store_id = empty($this->PostData['id'])  ? 0 : intval($this->PostData['id']);
        $keyword = empty($this->PostData['keyword'])  ? '' : trim($this->PostData['keyword']);
		
		if (empty($user_id))
        {
            $this->json_fail('login_pls');
            return;
        }
	
		$model_store =& m('store');
        $store_info  = $model_store->get($store_id);
        if (empty($store_info))
        {
			$this->json_fail('no_such_store');
            return;
        }
		
        $model_user =& m('member');
        $model_user->createRelation('collect_store', $user_id, array(
            $store_id   =>  array(
                'keyword'   =>  $keyword,
                'add_time'  =>  gmtime(),
            )
        ));
		
		$this->json_success('','collect_store_ok');
	}
}

?>
