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

        /* 当前位置 */
        $this->_curlocal(LANG::get('my_address'), 'index.php?app=my_address',
                         LANG::get('address_list'));

        /* 当前用户中心菜单 */
        $this->_curitem('my_address');

        /* 当前所处子菜单 */
        $this->_curmenu('address_list');

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
                array(
                    'path' => 'mlselection.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/smoothness/jquery.ui.css',
        ));
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('my_address'));
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
            /* 当前位置 */
            /*$this->_curlocal(LANG::get('my_address'), 'index.php?app=my_address',
                             LANG::get('add_address'));*/
            //$this->import_resource('mlselection.js, jquery.plugins/jquery.validate.js');
            $this->assign('act', 'add');
            $this->_get_regions();
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->display('my_address.form.html');
        }
        else
        {
            /* 电话和手机至少填一项*/ 
            if (!$_POST['phone_tel'] && !$_POST['phone_mob'])
            {
                $this->pop_warning('phone_required');

                return;
            }
            
            $data = array(
                'user_id'       => $this->visitor->get('user_id'),
                'consignee'     => $_POST['consignee'],
                'region_id'     => $_POST['region_id'],
                'region_name'   => $_POST['region_name'],
                'address'       => $_POST['address'],
                'zipcode'       => $_POST['zipcode'],
                'phone_tel'     => $_POST['phone_tel'],
                'phone_mob'     => $_POST['phone_mob'],
				'setdefault'    => $_POST['setdefault']
            );
			$model_address =& m('address');
            if (!($address_id = $model_address->add($data)))
            {
                $this->pop_warning($model_address->get_error());

                return;
            }
			if($_POST['setdefault'] == 1)
			{
				$model_address->edit('user_id = '.$this->visitor->get('user_id').' AND addr_id != '.$address_id,array('setdefault'=> 0));
			}
			else
			{
				$this->_setdefaultAddr();	
			}
			
            $this->pop_warning('ok', APP.'_'.ACT, $_GET['ret_url']);
        }
    }
    function edit()
    {
        $addr_id = empty($_GET['addr_id']) ? 0 : intval($_GET['addr_id']);
        if (!$addr_id)
        {
            echo Lang::get("no_such_address");
            return;
        }
        if (!IS_POST)
        {
            $model_address =& m('address');
            $find_data     = $model_address->find("addr_id = {$addr_id} AND user_id=" . $this->visitor->get('user_id'));
            if (empty($find_data))
            {
                echo Lang::get('no_such_address');

                return;
            }
            $address = current($find_data);

            /* 当前位置 */
            $this->_curlocal(LANG::get('my_address'), 'index.php?app=my_address',
                             LANG::get('edit_address'));

            /* 当前用户中心菜单 */
            /*$this->_curitem('my_address');

            
            /* 当前所处子菜单 */
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->_curmenu('edit_address');

            $this->assign('address', $address);
            //$this->import_resource('mlselection.js, jquery.plugins/jquery.validate.js');
            $this->assign('act', 'edit');
            $this->_get_regions();
            $this->display('my_address.form.html');
        }
        else
        {
            /* 电话和手机至少填一项 */
            if (!$_POST['phone_tel'] && !$_POST['phone_mob'])
            {
                $this->pop_warning('phone_required');

                return;
            }
            $data = array(
                'consignee'     => $_POST['consignee'],
                'region_id'     => $_POST['region_id'],
                'region_name'   => $_POST['region_name'],
                'address'       => $_POST['address'],
                'zipcode'       => $_POST['zipcode'],
                'phone_tel'     => $_POST['phone_tel'],
                'phone_mob'     => $_POST['phone_mob'],
				'setdefault'    => $_POST['setdefault']
            );
            $model_address =& m('address');
            $model_address->edit("addr_id = {$addr_id} AND user_id=" . $this->visitor->get('user_id'), $data);
            if ($model_address->has_error())
            {
                $this->pop_warning($model_address->get_error());

                return;
            }
			if($_POST['setdefault'] == 1){
				$model_address->edit(
					'user_id = '.$this->visitor->get('user_id').' AND addr_id != '.$addr_id,array('setdefault'=> 0
				));
			}
			else
			{
				$this->_setdefaultAddr();	
			}
			
            $this->pop_warning('ok', APP.'_'.ACT);
        }
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
	
    function drop()
    {
        $addr_id = isset($_GET['addr_id']) ? trim($_GET['addr_id']) : 0;
        if (!$addr_id)
        {
            $this->show_warning('no_such_address');

            return;
        }
        $ids = explode(',', $addr_id);//获取一个类似array(1, 2, 3)的数组
        $model_address  =& m('address');
        $drop_count = $model_address->drop("user_id = " . $this->visitor->get('user_id') . " AND addr_id " . db_create_in($ids));
        if (!$drop_count)
        {
            /* 没有可删除的项 */
            $this->show_warning('no_such_address');

            return;
        }

        if ($model_address->has_error())    //出错了
        {
            $this->show_warning($model_address->get_error());

            return;
        }

        $this->show_message('drop_address_successed');
    }
    function _get_regions()
    {
        $model_region =& m('region');
        $regions = $model_region->get_list(0);
        if ($regions)
        {
            $tmp  = array();
            foreach ($regions as $key => $value)
            {
                $tmp[$key] = $value['region_name'];
            }
            $regions = $tmp;
        }
        $this->assign('regions', $regions);
    }
    /**
     *    三级菜单
     *
     *    @author    MiMall
     *    @return    void
     */
    function _get_member_submenu()
    {
        $menus = array(
            array(
                'name'  => 'address_list',
                'url'   => 'index.php?app=my_address',
            ),
/*            array(
                'name'  => 'add_address',
                'url'   => 'index.php?app=my_address&act=add',
            ),*/
        );
/*        if (ACT == 'edit')
        {
            $menus[] = array(
                'name' => 'edit_address',
                'url'  => '',
            );
        }*/
        return $menus;
    }
}

?>