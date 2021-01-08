<?php

/* 店铺等级控制器 */
class SgradeApp extends BackendApp
{
    var $_grade_mod;

    function __construct()
    {
        $this->SgradeApp();
    }

    function SgradeApp()
    {
        parent::__construct();
        $this->_grade_mod =& m('sgrade');
    }

    function index()
    {
		$query = $this->get_query_conditions();
		$this->assign('filtered', $query);
        $this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js',
		));

        $this->display('sgrade.index.html');
    }
	
	function get_xml()
	{
		$conditions = '';
		$conditions .= $this->get_query_conditions();
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
		$sgrades = $this->_grade_mod->find(array(
            'conditions' => '1=1' . $conditions,
            'limit' => $page['limit'],
            'count' => true,
            'order' => 'sort_order asc',
        ));
        $page['item_count'] = $this->_grade_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($sgrades as $k => $v){
			$list = array();
			$operation = "<a class='btn red' onclick=\"fg_delete({$k},'sgrade')\"><i class='fa fa-trash-o'></i>删除</a>";
			$operation .= "<span class='btn'><em><i class='fa fa-cog'></i>设置 <i class='arrow'></i></em><ul>";
			$operation .= "<li><a href='index.php?app=sgrade&act=edit&id={$k}'>编辑</a></li>";
			$operation .= "<li><a href='index.php?app=sgrade&act=set_skins&id={$k}'>电脑模板</a></li>";
			$operation .= "<li><a href='index.php?app=sgrade&act=set_skins&type=wap&id={$k}'>手机模板</a></li>";
			$operation .= "</ul>";
			$list['operation'] = $operation;
			$list['grade_name'] = $v['grade_name'];
			$list['goods_limit'] = $v['goods_limit'] ? $v['goods_limit'] : LANG::get('no_limit');
			$list['space_limit'] = $v['space_limit'] ? $v['space_limit'] : LANG::get('no_limit');
			$list['skin_limit'] = $v['skin_limit'];
			$list['charge'] = $v['charge'];
			$list['need_confirm'] = $v['need_confirm'] == 0 ? '<span class="no"><i class="fa fa-ban"></i>否</span>' : '<span class="yes"><i class="fa fa-check-circle"></i>是</span>';
			$data['list'][$k] = $list;
		}
		$this->flexigridXML($data);
	}

	function get_query_conditions(){
		$conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'grade_name',
                'equal' => 'like',
            ),
        ));
		return $conditions;
	}
	
    function add()
    {
        if (!IS_POST)
        {
            $this->assign('sgrade', array(
                'need_confirm' => 1,
                'sort_order'   => 255,
            ));
            $functions = $this->_get_functions();
            $this->assign('functions', $functions);
            $this->display('sgrade.form.html');
        }
        else
        {
            /* 检查名称是否已存在 */
            if (!$this->_grade_mod->unique(trim($_POST['grade_name'])))
            {
                $this->json_error('name_exist');
                return;
            }

            $functions = isset($_POST['functions']) ? implode(',', $_POST['functions']) : '';
            $data = array(
                'grade_name'   => $_POST['grade_name'],
                'goods_limit'  => $_POST['goods_limit'],
                'space_limit'  => $_POST['space_limit'],
                'charge'       => $_POST['charge'],
                'need_confirm' => $_POST['need_confirm'],
                'description'  => $_POST['description'],
                'sort_order'   => $_POST['sort_order'],
                'functions'    => $functions,
            );

            $grade_id = $this->_grade_mod->add($data);
            if (!$grade_id)
            {
				$error = current($this->_grade_mod->get_error());
                $this->json_error($error['msg']);
                return;
            }
            $this->json_result('','add_ok');
        }
    }

    /* 检查登记名称的唯一性 */
    function check_grade()
    {
        $grade_name = empty($_GET['grade_name']) ? '' : trim($_GET['grade_name']);
        $grade_id   = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$grade_name)
        {
            echo ecm_json_encode(false);
            return ;
        }
        if ($this->_grade_mod->unique($grade_name, $grade_id))
        {
            echo ecm_json_encode(true);
        }
        else
        {
            echo ecm_json_encode(false);
        }
        return ;
    }

    function edit()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }

        if (!IS_POST)
        {
            /* 是否存在 */
            $sgrade = $this->_grade_mod->get_info($id);
            if (!$sgrade)
            {
                $this->json_error('sgrade_empty');
                return;
            }
            $checked_functions = $functions = array();
            $functions = $this->_get_functions();
            $tmp = explode(',', $sgrade['functions']);
            if ($functions)
            {
                foreach ($functions as $func)
                {
                    $checked_functions[$func] = in_array($func, $tmp);
                }
            }
            $this->assign('sgrade', $sgrade);
            $this->assign('functions', $functions);
            $this->assign('checked_functions', $checked_functions);
            $this->display('sgrade.form.html');
        }
        else
        {
            $functions = isset($_POST['functions']) ? implode(',', $_POST['functions']) : '';
            $data = array(
                'grade_name'   => $_POST['grade_name'],
                'goods_limit'  => $_POST['goods_limit'],
                'space_limit'  => $_POST['space_limit'],
                'charge'       => $_POST['charge'],
                'need_confirm' => $_POST['need_confirm'],
                'description'  => $_POST['description'],
                'sort_order'   => $_POST['sort_order'],
                'functions'    => $functions,
            );
            $this->_grade_mod->edit($id, $data);
            $this->json_result('','edit_ok');
        }
    }

    function drop()
    {
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$id)
        {
            $this->json_error('no_sgrade_to_drop');
            return;
        }

        $ids = explode(',', $id);
        $ids = array_diff($ids, array(1)); // 默认等级不能删除
        if (!$this->_grade_mod->drop($ids))
        {
			$error = current($this->_grade_mod->get_error());
            $this->json_error($error['msg']);
            return;
        }

        $store_mod =& m('store');
        $store_mod->edit("sgrade " . db_create_in($ids), array('sgrade' => 1));

        $this->json_result('','drop_ok');
    }

    function set_skins()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		$type = empty($_GET['type']) ? 'pc' : $_GET['type'];
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
		
		 $sgrade = $this->_grade_mod->get_info($id);
         if (!$sgrade)
         {
             $this->show_warning('sgrade_empty');
             return;
         }

        if (!IS_POST)
        {
			$skin_type = ($type == 'wap') ? $sgrade['wap_skins'] : $sgrade['skins'];
            $available_skins = explode(',', $skin_type);
            $skins = $this->_get_skins($type);
            foreach ($skins as $key => $skin)
            {
                if (in_array($skin['value'], $available_skins))
                {
                    $skins[$key]['checked'] = 1;
                }
            }
            $this->assign('skins', $skins);

            $this->display('sgrade.skins.html');
        }
        else
        {
			if($type == 'pc')
			{
				$data['skins'] = isset($_POST['skins']) ? join(',', $_POST['skins']) : 'default|default';
				$data['skin_limit'] = isset($_POST['skins']) ? count($_POST['skins']) : 1;
			}
			else
			{
				$data['wap_skins']  = isset($_POST['wap_skins']) ? join(',', $_POST['wap_skins']) : 'default|default';
				$data['wap_skin_limit'] = isset($_POST['wap_skins']) ? count($_POST['wap_skins']) : 1;
			}
            $this->_grade_mod->edit($id, $data);
            $this->json_result('','set_skins_ok');
        }
    }

    function _get_skins($type='pc')
    {
        $skins = array();
		if($type == 'pc')
		{
       	    $layout_dir = ROOT_PATH . '/themes/store/';
		}
		else
		{
			$layout_dir = ROOT_PATH . '/mobile/themes/store/';
			$app_root_path = 'mobile/';
		}
        if (is_dir($layout_dir))
        {
            if ($ldh = opendir($layout_dir))
            {
                while (($lfile = readdir($ldh)) !== false)
                {
                    if ($lfile[0] != '.' && filetype($layout_dir . $lfile) == 'dir')
                    {
                        $skin_dir = $layout_dir . $lfile . '/styles/';
                        if (is_dir($skin_dir))
                        {
                            if ($sdh = opendir($skin_dir))
                            {
                                while (($sfile = readdir($sdh)) !== false)
                                {
                                    if ($sfile[0] != '.' && filetype($skin_dir . $sfile) == 'dir')
                                    {
                                        $skins[] = array(
                                            'value'     => $lfile . '|' . $sfile,
                                            'preview'   => $app_root_path.'themes/store/' . $lfile . '/styles/' . $sfile . '/preview.jpg',
                                            'screenshot'=> $app_root_path.'themes/store/' . $lfile . '/styles/' . $sfile . '/screenshot.jpg',
                                        );
                                    }
                                }
                                closedir($sdh);
                            }
                        }
                    }
                }
                closedir($ldh);
            }
        }

        return $skins;
    }

    /**
     *    获取可用功能列表
     *

     *    @return    array
     */
    function _get_functions()
    {
        $arr = array();
        if (ENABLED_SUBDOMAIN)
        {
            $arr[] = 'subdomain';
        }
        $arr[] = 'editor_multimedia';
        $arr[] = 'coupon';
        return $arr;
    }
}

?>
