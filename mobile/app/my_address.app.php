<?php

/**
 *    我的收货地址控制器
 *
 *    @author    MiMall
 *    @usage    none
 */
class My_addressApp extends MemberbaseApp
{
    function index()
    {
        /* 取得列表数据 */
        $model_address =& m('address');
        $addresses     = $model_address->find(array(
            'conditions'    => 'user_id = ' . $this->visitor->get('user_id'),
			'order'         => 'setdefault desc, addr_id desc'
        ));
		
        $this->assign('addresses', $addresses);
		
		$this->import_resource('mobile/jquery.plugins/jquery.form.min.js');

		$this->_config_seo('title', Lang::get('my_address') . ' - ' . Lang::get('member_center'));
		$this->_get_curlocal_title('my_address');
        $this->display('my_address.index.html');
    }

    /**
     *    添加地址
     *
     *    @author    MiMall
     *    @return    void
     */
    function add()
    {
        if (!IS_POST)
        {
			$this->assign('ret_url', $_GET['ret_url']);
			
			$this->import_resource('mobile/jquery.plugins/jquery.form.min.js');
			
			$this->assign('location' , true);
            
			$this->_config_seo('title', Lang::get('add_address') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('add_address');
           	$this->display('my_address.form.html');
        }
        else
        {
			$consignee = trim($_POST['consignee']);
			$region_id = intval($_POST['region_id']);
			$address   = trim($_POST['address']);
			
			if(!$consignee) {
				$this->json_error('consignee_required');
				return;
			}
			if(!$region_id){
				$this->json_error('region_required');
				return;
			}
			if(!$address){
				$this->json_error('address_required');
				return;
			}
			
            /* 电话和手机至少填一项 */
            if (!$_POST['phone_tel'] && !$_POST['phone_mob'])
            {
                $this->json_error('phone_required');
                return;
            }

            $data = array(
                'user_id'       => $this->visitor->get('user_id'),
                'consignee'     => $consignee,
                'region_id'     => $region_id,
                'region_name'   => $_POST['region_name'],
                'address'       => $address,
                'zipcode'       => $_POST['zipcode'],
                'phone_tel'     => $_POST['phone_tel'],
                'phone_mob'     => $_POST['phone_mob'],
				'setdefault'    => $_POST['setdefault']
            );
            $model_address =& m('address');
            if (!($address_id = $model_address->add($data)))
            {
				$error = current($model_address->get_error());
                $this->json_error($error['msg']);

                return;
            }
	    	if($_POST['setdefault'] == 1){
				$model_address->edit('user_id = '.$this->visitor->get('user_id').' AND addr_id != '.$address_id, array('setdefault'=> 0));
			}
			else
			{
				$this->_setdefaultAddr();
			}
			
			$this->json_result('', 'add_ok');
        }
    }

    function edit()
    {
        $addr_id = empty($_GET['addr_id']) ? 0 : intval($_GET['addr_id']);
        if (!$addr_id)
        {
            $this->json_error("no_such_address");
            return;
        }
		
        if (!IS_POST)
        {
            $model_address =& m('address');
            $find_data     = $model_address->find("addr_id = {$addr_id} AND user_id=" . $this->visitor->get('user_id'));
            if (empty($find_data))
            {
                $this->json_error('no_such_address');

                return;
            }
            $address = current($find_data);
            $this->assign('address', $address);
			
			$this->assign('location' , true);
			
            $this->import_resource('mobile/jquery.plugins/jquery.form.min.js');
            
			$this->_config_seo('title', Lang::get('edit_address') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('edit_address');
            $this->display('my_address.form.html');
        }
        else
        {
            $consignee = trim($_POST['consignee']);
			$region_id = intval($_POST['region_id']);
			$address   = trim($_POST['address']);
			
			if(!$consignee) {
				$this->json_error('consignee_required');
				return;
			}
			if(!$region_id){
				$this->json_error('region_required');
				return;
			}
			if(!$address){
				$this->json_error('address_required');
				return;
			}
			
            /* 电话和手机至少填一项 */
            if (!$_POST['phone_tel'] && !$_POST['phone_mob'])
            {
                $this->json_error('phone_required');
                return;
            }
			
            $data = array(
                'consignee'     => $consignee,
                'region_id'     => $region_id,
                'region_name'   => $_POST['region_name'],
                'address'       => $address,
                'zipcode'       => $_POST['zipcode'],
                'phone_tel'     => $_POST['phone_tel'],
                'phone_mob'     => $_POST['phone_mob'],
				'setdefault'    => $_POST['setdefault']
            );
            $model_address =& m('address');
            $model_address->edit("addr_id = {$addr_id} AND user_id=" . $this->visitor->get('user_id'), $data);
            if ($model_address->has_error())
            {
				$error = current($model_address->get_error());
                $this->json_error($error['msg']);
                return;
            }
	    	if($_POST['setdefault'] == 1){
				$model_address->edit('user_id = '.$this->visitor->get('user_id').' AND addr_id != '.$addr_id, array('setdefault'=> 0));
			}
			else
			{
				$this->_setdefaultAddr();
			}
			
			$this->json_result('', 'edit_ok');
        }
    }
	
    function drop()
    {
        $addr_id = isset($_GET['addr_id']) ? trim($_GET['addr_id']) : 0;
        if (!$addr_id)
        {
            $this->json_error('no_such_address');

            return;
        }
        $ids = explode(',', $addr_id);//获取一个类似array(1, 2, 3)的数组
        $model_address  =& m('address');
        $drop_count = $model_address->drop("user_id = " . $this->visitor->get('user_id') . " AND addr_id " . db_create_in($ids));
        if (!$drop_count)
        {
            /* 没有可删除的项 */
            $this->json_error('no_such_address');

            return;
        }

        if ($model_address->has_error())    //出错了
        {
			$error = current($model_address->get_error());
            $this->json_error($error['msg']);

            return;
        }

		$this->json_result('', 'drop_address_successed');
    }
	
	function setdefault()
	{
		$addr_id = intval($_GET['addr_id']);
		
		$model_address =& m('address');
		if($address = $model_address->get(array('conditions' => 'addr_id='.$addr_id.' AND user_id='.$this->visitor->get('user_id'), 'fields' => 'setdefault'))) {
			if($model_address->edit('', array('setdefault' => 0))){
				$model_address->edit($addr_id, array('setdefault' => 1));
			}
		}
		
		$this->_setdefaultAddr();
			
		$this->json_result('', '默认地址设置成功');
	}
	
	//确保有一个默认收货地址的存在
	function _setdefaultAddr()
	{
		$model_address =& m('address');
		$check_dfault = $model_address->get('user_id = '.$this->visitor->get('user_id').' AND setdefault = 1');
		if(empty($check_dfault))
		{
			$default_addr = $model_address->get('user_id = '.$this->visitor->get('user_id'));	
			$model_address->edit($default_addr['addr_id'], array('setdefault' => 1));
		}
	}
}

?>