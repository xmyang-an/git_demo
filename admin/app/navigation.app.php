<?php

class NavigationApp extends BackendApp
{
    var $_navi_mod;

    function __construct()
    {
        $this->NavigationApp();
    }

    function NavigationApp()
    {
        parent::BackendApp();

        $this->_navi_mod =& m('navigation');
    }

	function index()
    {
		$query = $this->get_query_conditions();
		$this->assign('filtered', $query);
        $this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js,inline_edit.js',
		));
        $this->display('navigation.index.html');
    }
	
	function get_xml()
	{
		$conditions = '';
        $conditions .= $this->get_query_conditions();
		$order = 'sort_order ASC';
        $param = array('sort_order','title','if_show');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
        $navigations = $this->_navi_mod->find(array(
			'conditions'  => '1=1' . $conditions,
			'limit'   =>$page['limit'],
			'order'   => $order,
			'count'   => true   //允许统计
        ));
        $types = array(
            'header' => Lang::get('header'),
            'middle' => Lang::get('middle'),
            'footer' => Lang::get('footer'),
        );
        $page['item_count'] = $this->_navi_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($navigations as $k => $v){
			$list = array();
			$list['operation'] = "<a class='btn red' onclick=\"fg_delete({$k},'navigation')\"><i class='fa fa-trash-o'></i>删除</a><a class='btn blue' href='index.php?app=navigation&act=edit&id={$k}'><i class='fa fa-pencil-square-o'></i>编辑</a>";
			$list['title'] = '<span ectype="inline_edit" fieldname="title" fieldid="'.$k.'" required="1" class="editable" title="'.Lang::get('editable').'">'.$v['title'].'</span>';
			$list['type'] = $types[$v['type']];
			$list['link'] = $v['link'];
			$list['open_new'] = $v['open_new'] == 0 ? '<em class="no" ectype="inline_edit" fieldname="open_new" fieldid="'.$k.'" fieldvalue="0" title="'.Lang::get('editable').'"><i class="fa fa-ban"></i>否</em>' : '<em class="yes" ectype="inline_edit" fieldname="open_new" fieldid="'.$k.'" fieldvalue="1" title="'.Lang::get('editable').'"><i class="fa fa-check-circle"></i>是</em>';
			$list['sort_order'] = '<span ectype="inline_edit" fieldname="sort_order" fieldid="'.$k.'" datatype="pint" maxvalue="255" class="editable" title="'.Lang::get('editable').'">'.$v['sort_order'].'</span>';
			$data['list'][$k] = $list;
		}
		$this->flexigridXML($data);
	}

	function get_query_conditions(){
		$conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'title',
                'equal' => 'like',
            ),
        ));
		return $conditions;
	}
		
    function add()
    {
        if (!IS_POST)
        {
            /* 显示新增表单 */
            $model_acategory = &m('acategory');
            $navigation = array('type' => 'header', 'sort_order' => 255, 'link' => 'http://');
            $this->_assign_form();
            $this->import_resource(array('script' => 'mlselection.js'));
            $this->assign('gcategory_options', $this->_get_gcategory_options()); //商品分类树
            $this->assign('acategory_options', $this->_get_acategory_options()); //文章分类树
            $this->import_resource(array('script' => 'mlselection.js'));
            $this->assign('navigation', $navigation);
            $this->display('navigation.form.html');
        }
        else
        {
            $data = array();
            /* 当导航数据来自商品或文章分类时，将cate_id拼成连接 */
            $_POST['gcategory_cate_id'] && $_POST['link'] = 'index.php?app=search&cate_id='. $_POST['gcategory_cate_id'];
            $_POST['acategory_cate_id'] && $_POST['link'] = 'index.php?app=article&cate_id='. $_POST['acategory_cate_id'];

            $data['title']      =   $_POST['title'];
            $data['type']      =   $_POST['type'];
            $data['link']      =   $_POST['link'];
            $data['open_new']      =   $_POST['open_new'];
            $data['sort_order'] =   $_POST['sort_order'];

            if (!$nav_id = $this->_navi_mod->add($data))  //获取nav_id
            {
				$error = current($this->_navi_mod->get_error());
            	$this->json_error($error['msg']);
                return;
            }
            $this->_clear_cache();
            $this->json_result('','add_navigation_successed');
        }
    }

    function edit()
    {
        $nav_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$nav_id)
        {
            $this->show_warning('no_such_navigation');
            return;
        }
         if (!IS_POST)
        {
            $find_data     = $this->_navi_mod->find($nav_id);
            if (empty($find_data))
            {
                $this->show_warning('no_such_navigation');

                return;
            }
            $navigation    =   current($find_data);
            $this->_assign_form();
            $this->assign('gcategory_options', $this->_get_gcategory_options()); //商品分类树
            $this->assign('acategory_options', $this->_get_acategory_options()); //文章分类树
            $this->import_resource(array('script' => 'mlselection.js'));
            $this->assign('navigation', $navigation);
            $this->display('navigation.form.html');
        }
        else
        {
            $data = array();
            /* 当导航数据来自商品或文章分类时，将cate_id拼成连接 */
            $_POST['gcategory_cate_id'] && $_POST['link'] = 'index.php?app=search&cate_id='. $_POST['gcategory_cate_id'];
            $_POST['acategory_cate_id'] && $_POST['link'] = 'index.php?app=article&cate_id='. $_POST['acategory_cate_id'];

            $data['title']      =   $_POST['title'];
            $data['type']      =   $_POST['type'];
            $data['link']      =   $_POST['link'];
            $data['open_new']      =   $_POST['open_new'];
            $data['sort_order'] =   $_POST['sort_order'];

            $rows=$this->_navi_mod->edit($nav_id, $data);
            if ($this->_navi_mod->has_error())
            {
				$error = current($this->_navi_mod->get_error());
            	$this->json_error($error['msg']);

                return;
            }

            $this->_clear_cache();
            $this->json_result('','edit_navigation_successed');
        }
    }

     //异步修改数据
   function ajax_col()
   {
       $id     = empty($_GET['id']) ? 0 : intval($_GET['id']);
       $column = empty($_GET['column']) ? '' : trim($_GET['column']);
       $value  = isset($_GET['value']) ? trim($_GET['value']) : '';
       $data   = array();

       if (in_array($column ,array('title', 'sort_order','open_new')))
       {
           $data[$column] = $value;
           $this->_navi_mod->edit($id, $data);
           if(!$this->_navi_mod->has_error())
           {
               $this->_clear_cache();
               echo ecm_json_encode(true);
           }
       }
       else
       {
           return ;
       }
       return ;
   }

    function drop()
    {
        $nav_ids = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$nav_ids)
        {
            $this->json_error('no_such_navigation');

            return;
        }
        $nav_ids=explode(',',$nav_ids);
        if (!$this->_navi_mod->drop($nav_ids))    //删除
        {
			$error = current($this->_navi_mod->get_error());
            $this->json_error($error['msg']);

            return;
        }

        $this->_clear_cache();
        $this->json_result('','drop_navigation_successed');
    }

    /* 构造并返回树 */
    function &_tree($acategories)
    {
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($acategories, 'cate_id', 'parent_id', 'cate_name');
        return $tree;
    }
    /* 取得所有文章分类数据 */
    function _get_acategory_options()
    {
        $mod_acategory = &m('acategory');
        $acategorys = $mod_acategory->get_list();

        /* 去掉系统内置文章分类 */
        $system_cate_id = $mod_acategory->get_ACC(ACC_SYSTEM);
        unset($acategorys[$system_cate_id]);

        $tree =& $this->_tree($acategorys);
        return $tree->getOptions();
    }
    /* 取得商城的商品分类数据 */
    function _get_gcategory_options($parent_id = 0)
    {
        $mod_gcategory = &bm('gcategory');
        $gcategories = $mod_gcategory->get_list($parent_id, true);
        foreach ($gcategories as $gcategory)
        {
            $res[$gcategory['cate_id']] = $gcategory['cate_name'];
        }
        return $res;
    }

    /* 表单赋值 */
    function _assign_form()
    {
        $type = array(
            'header' => Lang::get('header'),
            'middle' => Lang::get('middle'),
            'footer' => Lang::get('footer'),
        );
        $open_new = array(
           '0' => Lang::get('no'),
           '1' => Lang::get('yes'),
        );
        $this->assign('type', $type);
        $this->assign('open_new', $open_new);
    }
}

?>
