<?php

/* 申请开店 */
class ApplyApp extends MallbaseApp
{

    function index()
    {
        $step = isset($_GET['step']) ? intval($_GET['step']) : 0;
		
        /* 判断是否开启了店铺申请 */
        if (!Conf::get('store_allow'))
        {
            $this->show_warning('apply_disabled');
            return;
        }

        /* 只有登录的用户才可申请 */
        if (!$this->visitor->has_login)
        {
            header("Location:index.php?app=member&act=login");
            return;
        }

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
		$this->assign('setting', array('phone'=>Conf::get('site_phone_tel'),'email'=>Conf::get('site_email')));
        switch ($step)
        {
            case 1:
				$article_mod = &m('article');
				$setup_store = $article_mod->get(array('conditions'=>'article_id=4', 'fields' => 'content'));
				$this->assign('setup_store', $setup_store);
                $this->_config_seo('title', Lang::get('title_step1') . ' - ' . Conf::get('site_title'));
                $this->display('apply.step1.html');
                break;
            case 2:
				$sgrade_mod =& m('sgrade');
				$sgrades = $sgrade_mod->find(array(
						'order' => 'sort_order',
				));
                if(!IS_POST)
                { 
                    $region_mod =& m('region');
                    $this->assign('regions', $region_mod->get_options(0));
                    $this->assign('scategories', $this->_get_scategory_options());

                    $scategory = $store_mod->getRelatedData('has_scategory', $this->visitor->get('user_id'));
                    if ($scategory)
                    {
                        $scategory = current($scategory);
                    }
                    $this->assign('scategory', $scategory);
					
					foreach ($sgrades as $key => $sgrade)
					{
						if (!$sgrade['goods_limit'])
						{
							$sgrades[$key]['goods_limit'] = LANG::get('no_limit');
						}
						if (!$sgrade['space_limit'])
						{
							$sgrades[$key]['space_limit'] = LANG::get('no_limit');
						}
						$arr = explode(',', $sgrade['functions']);
						$subdomain = array();
						foreach ( $arr as $val)
						{
							if (!empty($val))
							{
								$subdomain[$val] = 1;
							}
						}
						$sgrades[$key]['functions'] = $subdomain;
						unset($arr);
						unset($subdomain);
					}
					$this->assign('domain', ENABLED_SUBDOMAIN);
					$this->assign('sgrades', $sgrades);
					
					$this->assign('store', $store);
					
					/* 导入jQuery的表单验证插件 */
                    $this->import_resource(array('script' => 'mlselection.js,jquery.plugins/jquery.validate.js'));
                    $this->_config_seo('title', Lang::get('title_step2') . ' - ' . Conf::get('site_title'));
                    $this->display('apply.step2.html');
                }
                else
                {
                    $store_mod  =& m('store');
                    $store_id = $this->visitor->get('user_id');
                    $data = array(
                        'store_id'     => $store_id,
                        'store_name'   => $_POST['store_name'],
                        'owner_name'   => $_POST['owner_name'],
                        'owner_card'   => $_POST['owner_card'],
                        'region_id'    => $_POST['region_id'],
                        'region_name'  => $_POST['region_name'],
                        'address'      => $_POST['address'],
                        'zipcode'      => $_POST['zipcode'],
                        'tel'          => $_POST['tel'],
                        'sgrade'       => intval($_POST['sgrade_id']),
                        'state'        => $sgrades[$_POST['sgrade_id']]['need_confirm'] ? 0 : 1,
                        'add_time'     => gmtime(),
                    );
                    $image = $this->_upload_image($store_id);
                    if ($this->has_error())
                    {
                        $this->show_warning($this->get_error());

                        return;
                    }
                    
                    /* 判断是否已经申请过 */
                    $state = $this->visitor->get('state');
                    if ($state != '' && ($state == STORE_APPLYING OR $state == STORE_REJECT))
                    {
						$data['apply_remark'] = '';
                        $store_mod->edit($store_id, array_merge($data, $image));
                    }
                    else
                    {
                        $store_mod->add(array_merge($data, $image));
                    }
                    
                    if ($store_mod->has_error())
                    {
                        $this->show_warning($store_mod->get_error());
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
						$this->show_message('apply_ok',
                            'go', url('app=apply&step=3'));
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
						
                        $this->show_message('store_opened',
                            'go', url('app=apply&step=4'));
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
					$this->display('apply.step4.html');
				}
				break;
            default:
                $article_mod = &m('article');
				$articles = $article_mod->find(array(
					'conditions' => "acategory.cate_id=4",
					'join' => 'belongs_to_acategory',
					'fields' => 'title,content,cate_name',
					'limit'	=> 2
				));
                $this->assign('articles', $articles);

                $this->_config_seo('title', Lang::get('title_step0') . ' - ' . Conf::get('site_title'));
                $this->display('apply.index.html');
                break;
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
	/* 异步删除附件 */
    function drop_uploadedfile()
    {
        $file_id = isset($_GET['file_id']) ? intval($_GET['file_id']) : 0;
		$uploadedfile_mod = &m('uploadedfile');
        if ($file_id && $uploadedfile_mod->drop($file_id))
        {
            $this->json_result('drop_ok');
            return;
        }
        else
        {
            $this->json_error('drop_error');
            return;
        }
    }
}

?>
