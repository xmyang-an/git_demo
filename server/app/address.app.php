<?php

class AddressApp extends ApibaseApp
{
	function baseinfo()
	{
		$user_id = empty($this->PostData['user_id'])  ? 0 : intval($this->PostData['user_id']);
		$id = empty($this->PostData['id'])  ? 0 : intval($this->PostData['id']);

		if(empty($user_id))
        {
            $this->json_fail('login_pls');
            return;
        }
		
		if(empty($id))
        {
            $this->json_fail('has_no_such_address');
            return;
        }
		
        /* 取得列表数据 */
        $model_address =& m('address');
        $address    = $model_address->get(array(
            'conditions'    => 'user_id = ' . $user_id.' AND addr_id='.$id,
        ));
		
		if(empty($address))
        {
            $this->json_fail('has_no_such_address');
            return;
        }
		
		$this->json_success($address);
	}
	
    function listing()
    {
		$user_id = empty($this->PostData['user_id'])  ? 0 : intval($this->PostData['user_id']);

		if(empty($user_id))
        {
            $this->json_fail('login_pls');
            return;
        }
		
		$page = $this->_get_page((isset($post['perpage']) && $post['perpage'] > 0) ? $post['perpage'] : 10);
        /* 取得列表数据 */
        $model_address =& m('address');
        $addresses     = $model_address->find(array(
            'conditions'    => 'user_id = ' . $user_id,
			'order'         => 'setdefault desc, addr_id desc',
			'count'         => true,
			'limit'         => $page['limit']
        ));
		
		$page['item_count'] = $model_address->getCount();
		
		$this->json_success($addresses);
    }
	
	function drop()
    {
        $user_id = empty($this->PostData['user_id'])  ? 0 : intval($this->PostData['user_id']);
		$addr_id = empty($this->PostData['id'])  ? 0 : intval($this->PostData['id']);
        if (!$addr_id)
        {
            $this->json_fail('no_such_address');
            return;
        }
		
        $ids = explode(',', $addr_id);//获取一个类似array(1, 2, 3)的数组
        $model_address  =& m('address');
        $drop_count = $model_address->drop("user_id = " . $user_id . " AND addr_id " . db_create_in($ids));
        if (!$drop_count)
        {
            /* 没有可删除的项 */
            $this->json_fail('no_such_address');
            return;
        }

        if ($model_address->has_error())    //出错了
        {
			$error = current($model_address->get_error());
            $this->json_fail($error['msg']);
            return;
        }

		$this->json_success('', 'drop_address_successed');
    }
	
	function setdefault()
	{
		$user_id = empty($this->PostData['user_id'])  ? 0 : intval($this->PostData['user_id']);
		$addr_id = empty($this->PostData['id'])  ? 0 : intval($this->PostData['id']);
		
		if(!$user_id)
        {
            $this->json_fail('login_pls');
            return;
        }
		
		if(!$addr_id)
        {
            $this->json_fail('no_such_address');
            return;
        }
		
		$model_address =& m('address');
		if($address = $model_address->get(array('conditions' => 'addr_id='.$addr_id.' AND user_id='.$user_id, 'fields' => 'setdefault'))) {
			if($model_address->edit('', array('setdefault' => 0))){
				$model_address->edit($addr_id, array('setdefault' => 1));
			}
		}
		
		$this->_setdefaultAddr();
			
		$this->json_success('', '默认地址设置成功');
	}
	
	//确保有一个默认收货地址的存在
	function _setdefaultAddr()
	{
		$model_address =& m('address');
		$check_dfault = $model_address->get('user_id = '.$this->PostData['user_id'].' AND setdefault = 1');
		if(empty($check_dfault))
		{
			$default_addr = $model_address->get('user_id = '.$this->PostData['user_id']);	
			$model_address->edit($default_addr['addr_id'], array('setdefault' => 1));
		}
	}
	
	function save()
    {
		$user_id = empty($this->PostData['user_id'])  ? 0 : intval($this->PostData['user_id']);
        $consignee = trim($this->PostData['consignee']);
		$region_id = intval($this->PostData['region_id']);
		$address   = trim($this->PostData['address']);
		
		if(!$user_id)
        {
            $this->json_fail('login_pls');
            return;
        }
		
		if(!$consignee) {
			$this->json_fail('consignee_required');
			return;
		}
		if(!$region_id){
			$this->json_fail('region_required');
			return;
		}
		if(!$address){
			$this->json_fail('address_required');
			return;
		}
			
        /* 电话和手机至少填一项 */
        if (!$this->PostData['phone_tel'] && !$this->PostData['phone_mob'])
        {
            $this->json_fail('phone_required');
            return;
        }
			
        $data = array(
           'consignee'     => $consignee,
           'region_id'     => $region_id,
           'region_name'   => $this->PostData['region_name'],
           'address'       => $address,
           'phone_tel'     => $this->PostData['phone_tel'],
           'phone_mob'     => $this->PostData['phone_mob'],
		   'setdefault'    => $this->PostData['setdefault']
        );
		
		$model_address =& m('address');
		$addr_id = intval($this->PostData['id']);
		if($addr_id){
			$model_address->edit("addr_id = ".$addr_id." AND user_id=" . $user_id, $data);
		}
		else{
			$data['user_id'] = intval($this->PostData['user_id']);
			$addr_id = $model_address->add($data);
		}
		
        if ($model_address->has_error())
        {
			$error = current($model_address->get_error());
            $this->json_fail($error['msg']);
            return;
        }
	    if($this->PostData['setdefault'] == 1){
			$model_address->edit('user_id = '.$user_id.' AND addr_id != '.$addr_id, array('setdefault'=> 0));
		}
		else
		{
			$this->_setdefaultAddr();
		}
			
		$this->json_success($model_address->get($addr_id),'保存成功');
    }
}

?>