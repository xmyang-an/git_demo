<?php

/* 申请开店 */
class ApplyApp extends MallbaseApp
{
    function index()
    {
        $step = isset($_GET['step']) ? intval($_GET['step']) : 0;

        /* 已申请过或已有店铺不能再申请 */
        $store_mod =& m('store');
        $store = $store_mod->get($this->visitor->get('user_id'));
        if ($store)
        {
            if ($store['state'] AND $store['state'] <> 3) //已开通包括关闭状态
            {
                $step = 4;
            }
			elseif($store['state'] == 3)
			{	
				if($step <> 2)
				{
					$step = 4;
				}
			}
            elseif($step <> 2) // 编辑模式
            {
                $step = 3;  //跳转到状态             
            }
        }
		
        switch ($step)
        {
            case 1:
				
				/* 判断是否开启了店铺申请 */
        		if (!Conf::get('store_allow'))
        		{
            		$this->show_warning('apply_disabled');
            		return;
        		}
		
				$article_mod = &m('article');
				$setup_store = $article_mod->get(array('conditions'=>'article_id=4', 'fields' => 'content'));
				$this->assign('setup_store', $setup_store);
                $this->_config_seo('title', Lang::get('title_step1') . ' - ' . Conf::get('site_title'));
				$this->_get_curlocal_title('title_step1');
                $this->display('apply.step1.html');
                break;
            case 2:
				$sgrade_mod =& m('sgrade');
				$sgrades = $sgrade_mod->find(array(
						'order' => 'sort_order',
				));
                if(!IS_POST)
                { 
					/* 判断是否开启了店铺申请 */
        			if (!Conf::get('store_allow'))
        			{
            			$this->show_warning('apply_disabled');
            			return;
        			}
				
                    $region_mod =& m('region');
                    $this->assign('regions', $region_mod->get_options(0));
                    $this->assign('scategories', $this->_get_scategory_options());

                    $scategory = $store_mod->getRelatedData('has_scategory', $this->visitor->get('user_id'));
                    if ($scategory)
                    {
                        $scategory = current($scategory);
                    }
                    $this->assign('scategory', $scategory);
					
					$this->assign('domain', ENABLED_SUBDOMAIN);
					$this->assign('sgrades', $sgrades);
					
					$this->assign('store', $store);
					
					/* 导入jQuery的表单验证插件 */
                    $this->import_resource('mobile/jquery.plugins/jquery.form.min.js,webuploader/webuploader.js,webuploader/webuploader.compressupload.js');
                    $this->_config_seo('title', Lang::get('title_step2') . ' - ' . Conf::get('site_title'));
					$this->_get_curlocal_title('title_step2');
                    $this->display('apply.step2.html');
                }
                else
                {
					/* 判断是否开启了店铺申请 */
        			if (!Conf::get('store_allow'))
        			{
            			$this->json_error('apply_disabled');
            			return;
        			}
					
                    $store_mod  =& m('store');
                    $store_id = $this->visitor->get('user_id');
                    $data = array(
                        'store_id'     => $store_id,
                        'store_name'   => trim($_POST['store_name']),
                        'owner_name'   => trim($_POST['owner_name']),
                        'owner_card'   => trim($_POST['owner_card']),
                        'region_id'    => intval($_POST['region_id']),
                        'region_name'  => $_POST['region_name'],
                        'address'      => trim($_POST['address']),
                        'zipcode'      => trim($_POST['zipcode']),
                        'tel'          => trim($_POST['tel']),
                        'sgrade'       => intval($_POST['sgrade_id']),
                        'state'        => $sgrades[intval($_POST['sgrade_id'])]['need_confirm'] ? 0 : 1,
						'image_1'      => trim($_POST['image_1']),
						'image_2'      => trim($_POST['image_2']),
                        'add_time'     => gmtime(),
                    );
					
					if(empty($data['store_name'])) 
					{
						$this->json_error('input_store_name');
            			return;
					}
        			if (!$store_mod->unique($data['store_name'], $store_id))
        			{
            			$this->json_error('name_exist');
            			return;
        			}
					
					if(empty($data['owner_name'])) 
					{
						$this->json_error('input_owner_name');
            			return;
					}
					
					if(empty($data['tel']) || strlen($data['tel']) <=6)
					{
						$this->json_error('phone_tel_error');
            			return;
					}
					
					if(!$data['region_id'] || empty($data['address'])) {
						$this->json_error('region_error');
            			return;
					}
					
                    /*$image = $this->_upload_image($store_id);
                    if ($this->has_error())
                    {
						$error = current($this->get_error());
                        $this->json_error($error['msg']);

                        return;
                    }*/
                    
                    /* 判断是否已经申请过 */
                    $state = $this->visitor->get('state');
                    if ($state != '' && ($state == STORE_APPLYING OR $state == STORE_REJECT))
                    {
						$data['apply_remark'] = '';
                        $store_mod->edit($store_id, $data);
                    }
                    else
                    {
                        $store_mod->add($data);
                    }
                    
                    if ($store_mod->has_error())
                    {
						$error = current($store_mod->get_error());
                        $this->json_error($error['msg']);
                        return;
                    }
                    
                    $cate_id = intval($_POST['cate_id']);
                    $store_mod->unlinkRelation('has_scategory', $store_id);
                    if ($cate_id > 0)
                    {                        
                        $store_mod->createRelation('has_scategory', $store_id, $cate_id);
                    }

                    if ($sgrades[$_POST['sgrade_id']]['need_confirm'])
                    {
						$this->json_result(array('ret_url' => url('app=apply&step=3')), 'apply_ok');
            			return;
                    }
                    else
                    {
						// 给商家赠送积分
						$integral_mod=&m('integral');
						$result = array(
							'user_id' => $this->visitor->get('user_id'),
							'type'    => 'open_integral',
							'amount'  => $integral_mod->_get_sys_setting('open_integral')
						);
						$integral_mod->update_integral($result);

                        $this->send_feed('store_created', array(
                            'user_id'   => $this->visitor->get('user_id'),
                            'user_name'   => $this->visitor->get('user_name'),
                            'store_url'   => SITE_URL . '/' . url('app=store&id=' . $store_id),
                            'seller_name'   => $data['store_name'],
                        ));
                        $this->_hook('after_opening', array('user_id' => $store_id));
						
						/* 店铺开通后，添加一条默认的运费模板 */
						$data = array(
							'name'=> Lang::get('default_delivery_template'),
                			'store_id'      => $store_id,
							'template_types'=>'express;ems;post',
							'template_dests'=>'1;1;1',
							'template_start_standards'=>'1;1;1',
							'template_start_fees'=>'15;22;10',
							'template_add_standards'=>'1;1;1',
							'template_add_fees'=>'8;10;5',
							'created'=> gmtime()
            			);
						$delivery_template_mod = &m('delivery_template');
						$delivery_template_mod->add($data);
						
						$this->json_result(array('ret_url' => url('app=apply&step=4')), 'store_opened');
            			return; 
                    }
                }
                break;
			case 3://待审核
				$state = $this->visitor->get('state');
				if($state == '')
				{
					header('Location: index.php?app=apply');
            		return;
				}
				else if($state == STORE_APPLYING)
				{
					$this->assign('store', $store);
					$this->_config_seo('title', Lang::get('title_step3') . ' - ' . Conf::get('site_title'));
					$this->_get_curlocal_title('title_step3');
					$this->display('apply.step3.html');
				}
				else
				{
					header('Location: index.php?app=apply&step=4');
            		return;
				}
				break;
			case 4://已开通
				$state = $this->visitor->get('state');
				if($state == '')
				{
					header('Location: index.php?app=apply');
            		return;
				}
				elseif(in_array($state,array(STORE_APPLYING,STORE_REJECT)))
				{
					$this->assign('store', $store);
					$this->_config_seo('title', Lang::get('title_step3') . ' - ' . Conf::get('site_title'));
					$this->display('apply.step3.html');
				}
				else
				{
					$this->_config_seo('title', Lang::get('title_step4') . ' - ' . Conf::get('site_title'));
					$this->_get_curlocal_title('title_step4');
					$this->display('apply.step4.html');
				}
				break;
            default:
                
                $this->_config_seo('title', Lang::get('title_step0') . ' - ' . Conf::get('site_title'));
				$this->_get_curlocal_title('title_step0');
                $this->display('apply.index.html');
                break;
        }
    }
	
	//申请加入分销
	function distribution()
    {
		$cookieDid = $this->getCookieDid();
		if(empty($cookieDid))
		{
			$this->show_warning('Hacking Attempt');
			return;
		}
		
		$distribution_mod = &m('distribution');
		$joinDistributionInfo = $distribution_mod->getCheckJoinDistributionInfo($this->visitor->get('user_id'), $cookieDid['store_id'], $cookieDid['did']);
		if($joinDistributionInfo['canJoinInStore'] === FALSE) {
			$this->show_warning($joinDistributionInfo['joinDisableMsg']);
			return;
		}
		
		$store_mod = &m('store');
		$store = $store_mod->get(array(
			'conditions' => $cookieDid['store_id'],
			'fields'	 => 'store_name,owner_name,enable_distribution,distribution_1,distribution_2,distribution_3',
		));
		if($cookieDid['did'])  //分销商推荐
		{
			$distb = $distribution_mod->get('did='.$cookieDid['did']);
			$parent_id = $distb['user_id'];
			$store['parent_name'] = $distb['real_name'];
		}
		else  //原始店铺推荐
		{
			$parent_id = 0;
			$store['parent_name'] = $store['owner_name'];
		}
		
		if(!IS_POST)
		{
			$this->assign('store',$store);
			
			$this->import_resource('mobile/jquery.plugins/jquery.form.min.js');
					
			$this->_get_curlocal_title('join_distribution');
			$this->_config_seo('title', Lang::get('join_distribution'));
			$this->display('dcenter.join.html');
		}
		else
		{
			$real_name = trim($_POST['real_name']);
			$phone_mob = trim($_POST['phone_mob']);
			if(empty($real_name) || empty($phone_mob))
			{
				$this->show_warning('name_or_phone_empty');
				return;
			}
			if(!is_mobile($phone_mob)){
				$this->show_warning('input_phone_mob');
				return;
			}
			$did = $distribution_mod->_gen_did();
			$data = array(
				'user_id'	=>	$this->visitor->get('user_id'),
				'parent_id'	=>	$parent_id,
				'store_id'	=>	$cookieDid['store_id'],
				'did'		=>	$did,
				'real_name' => 	$real_name,
				'phone_mob' => 	$phone_mob,
				'add_time' 	=> 	gmtime(),
			);
			$distribution_mod->add($data);
			if ($distribution_mod->has_error())
            {
               $this->show_warning($distribution_mod->get_error());
               return;
            }
			$this->show_warning('join_ok', 'gotowd', url('app=store&id='.$cookieDid['store_id'].'&did='.$did));
		}
    }
	
	function check_name()
    {
        $store_name = empty($_GET['store_name']) ? '' : trim($_GET['store_name']);
        $store_id = empty($_GET['store_id']) ? 0 : intval($_GET['store_id']);

        $store_mod =& m('store');
        if (!$store_mod->unique($store_name, $store_id))
        {
            echo ecm_json_encode(false);
            return;
        }
        echo ecm_json_encode(true);
    }
	
	function upload()
	{
		$key = in_array($_GET['type'] , array(1,2)) ? $_GET['type']  : 1;
		
		import('uploader.lib');
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE);
		
		$file = $_FILES['image_' . $key];
        if ($file['error'] == UPLOAD_ERR_OK)
        {
            $uploader->addFile($file);
            if (!$uploader->file_info())
            {
                $this->_error($uploader->get_error());
                return false;
            }

            $uploader->root_dir(ROOT_PATH);
            $dirname   = 'data/files/mall/apply';
            $url = $uploader->save($dirname, $uploader->random_filename());
			
			echo json_encode($url);
			exit;
       }
	}
	
    /* 上传图片 */
    function _upload_image($store_id)
    {
        import('uploader.lib');
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE);
        $uploader->allowed_size(SIZE_STORE_CERT); // 400KB

        $data = array();
        for ($i = 1; $i < 3; $i++)
        {
            $file = $_FILES['image_' . $i];
            if ($file['error'] == UPLOAD_ERR_OK)
            {
                if (empty($file))
                {
                    continue;
                }
                $uploader->addFile($file);
                if (!$uploader->file_info())
                {
                    $this->_error($uploader->get_error());
                    return false;
                }

                $uploader->root_dir(ROOT_PATH);
                $dirname   = 'data/files/mall/application';
                $filename  = 'store_' . $store_id . '_' . $i;
                $data['image_' . $i] = $uploader->save($dirname, $filename);
            }
        }
        return $data;
    }

    /* 取得店铺分类 */
    function _get_scategory_options()
    {
        $mod =& m('scategory');
        $scategories = $mod->get_list();
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($scategories, 'cate_id', 'parent_id', 'cate_name');
        return $tree->getOptions();
    }
}

?>
