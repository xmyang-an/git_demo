<?php

/**
 *    商品品牌管理控制器
 *
 *    @author    Hyber
 *    @usage    none
 */
class BrandApp extends BackendApp
{
    var $_brand_mod;

    function __construct()
    {
        $this->BrandApp();
    }

    function BrandApp()
    {
        parent::BackendApp();

        $this->_brand_mod =& m('brand');
    }

    /**
     *    商品品牌索引
     *
     *    @author    Hyber
     *    @return    void
     */
    function index()
    {
		$query = $this->get_query_conditions();
		$this->assign('filtered', $query);
        $this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js,inline_edit.js',
		));
        $this->display('brand.index.html');
    }
	
	function get_xml()
	{
		$conditions = '1=1';
		if ($_GET['wait_verify'] == 1) {
            $conditions .= " AND if_show=0";
        }

		$conditions .= $this->get_query_conditions();
		$order = 'sort_order ASC,brand_id DESC';
        $param = array('brand_id','brand_name','tag','sort_order','recommended','if_show');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
        $brands=$this->_brand_mod->find(array(
        	'conditions'    => $conditions,
			'limit'   => $page['limit'],
			'order'   => $order,
			'count'   => true 
        )); 
        $page['item_count'] = $this->_brand_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($brands as $k => $v){
			$list = array();
			if ($_GET['wait_verify'] == 1) {
				$list['operation'] = "<a class='btn red' onclick=\"fg_delete({$v['brand_id']},'brand')\"><i class='fa fa-trash-o'></i>删除</a><a class='btn orange' href='javascript:void(0)' onclick=\"fg_apply(".$v['brand_id'].")\"><i class='fa fa-check-square'></i>审核</a>";
			} else {
				$list['operation'] = "<a class='btn red' onclick=\"fg_delete({$v['brand_id']},'brand')\"><i class='fa fa-trash-o'></i>删除</a><a class='btn blue' href='index.php?app=brand&act=edit&id={$v['brand_id']}'><i class='fa fa-pencil-square-o'></i>编辑</a>";
			}
			$list['brand_id'] = $v['brand_id'];
			$list['brand_name'] = '<span ectype="inline_edit" fieldname="brand_name" fieldid="'.$v['brand_id'].'" class="editable" title="'.Lang::get('editable').'">'.$v['brand_name'].'</span>';
			$list['tag'] = '<span ectype="inline_edit" fieldname="tag" fieldid="'.$v['brand_id'].'" required="1" class="editable" title="'.Lang::get('editable').'">'.$v['tag'].'</span>';
			$list['brand_logo'] = $v['brand_logo'] ? '<img src="'.dirname(site_url()) . '/' . $v['brand_logo'].'" height="25"/>' : '';
			$list['sort_order'] = '<span ectype="inline_edit" fieldname="sort_order" fieldid="'.$v['brand_id'].'" datatype="pint" maxvalue="255" class="editable" title="'.Lang::get('editable').'">'.$v['sort_order'].'</span>';
			$list['recommended'] = $v['recommended'] == 0 ? '<em class="no" ectype="inline_edit" fieldname="recommended" fieldid="'.$k.'" fieldvalue="0" title="'.Lang::get('editable').'"><i class="fa fa-ban"></i>否</em>' : '<em class="yes" ectype="inline_edit" fieldname="recommended" fieldid="'.$k.'" fieldvalue="1" title="'.Lang::get('editable').'"><i class="fa fa-check-circle"></i>是</em>';
			$list['if_show'] = $v['if_show'] == 0 ? '<em class="no" ectype="inline_edit" fieldname="if_show" fieldid="'.$k.'" fieldvalue="0" title="'.Lang::get('editable').'"><i class="fa fa-ban"></i>否</em>' : '<em class="yes" ectype="inline_edit" fieldname="if_show" fieldid="'.$k.'" fieldvalue="1" title="'.Lang::get('editable').'"><i class="fa fa-check-circle"></i>是</em>';
			$data['list'][$k] = $list;
		}
		$this->flexigridXML($data);
	}
	
	function get_query_conditions(){
		$conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'brand_name',
                'equal' => 'like',
            ),
            array(
                'field' => 'tag',
                'equal' => 'like',
            ),
        ));
		return $conditions;
	}
     /**
     *    新增商品品牌
     *
     *    @author    Hyber
     *    @return    void
     */
    function add()
    {
        if (!IS_POST)
        {
            /* 显示新增表单 */
            $brand = array(
                'sort_order' => 255,
                'recommended' => 0,
            );
            $yes_or_no = array(
                1 => Lang::get('yes'),
                0 => Lang::get('no'),
            );
            $this->assign('yes_or_no', $yes_or_no);
            $this->assign('brand', $brand);
            $this->display('brand.form.html');
        }
        else
        {
            $data = array();
            $data['brand_name']     = $_POST['brand_name'];
            $data['sort_order']     = $_POST['sort_order'];
            $data['recommended']    = $_POST['recommended'];
            $data['tag'] = $_POST['tag'];
            $data['if_show'] = 1;

            /* 检查名称是否已存在 */
            if (!$this->_brand_mod->unique(trim($data['brand_name'])))
            {
                $this->json_error('name_exist');
                return;
            }
            if (!$brand_id = $this->_brand_mod->add($data))  //获取brand_id
            {
				$error = current($this->_brand_mod->get_error());
                $this->json_error($error['msg']);
                return;
            }

            /* 处理上传的图片 */
			if (!empty($_FILES['logo']))
            {
				$logo = $this->_upload_logo($brand_id);
				$logo && $this->_brand_mod->edit($brand_id, array('brand_logo' => $logo)); //将logo地址记下
            }
            
            $this->json_result('','add_brand_successed');
        }
    }

    /* 检查品牌唯一 */
    function check_brand ()
    {
        $brand_name = empty($_GET['brand_name']) ? '' : trim($_GET['brand_name']);
        $brand_id   = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$brand_name) {
            echo ecm_json_encode(false);
        }
        if ($this->_brand_mod->unique($brand_name, $brand_id)) {
            echo ecm_json_encode(true);
        }
        else
        {
            echo ecm_json_encode(false);
        }
        return ;
    }

     /**
     *    编辑商品品牌
     *
     *    @author    Hyber
     *    @return    void
     */
    function edit()
    {
        $brand_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$brand_id)
        {
            $this->show_warning('no_such_brand');
            return;
        }
         if (!IS_POST)
        {
            $find_data     = $this->_brand_mod->find($brand_id);
            if (empty($find_data))
            {
                $this->show_warning('no_such_brand');

                return;
            }
            $brand    =   current($find_data);
            if ($brand['brand_logo'])
            {
                $brand['brand_logo']  =   dirname(site_url()) . "/" . $brand['brand_logo'];
            }
            /* 显示新增表单 */
            $yes_or_no = array(
                1 => Lang::get('yes'),
                0 => Lang::get('no'),
            );
            $this->assign('yes_or_no', $yes_or_no);
            $this->assign('brand', $brand);
            $this->display('brand.form.html');
        }
        else
        {
            $data = array();
            $data['brand_name']     =   $_POST['brand_name'];
            $data['sort_order']     =   $_POST['sort_order'];
            $data['recommended']    =   $_POST['recommended'];
            $data['tag'] = $_POST['tag'];
            if (!empty($_FILES['logo']))
            {
				$logo = $this->_upload_logo($brand_id);
				$logo && $data['brand_logo'] = $logo;
            }
             /* 检查名称是否已存在 */
            if (!$this->_brand_mod->unique(trim($data['brand_name']), $brand_id))
            {
                $this->json_error('name_exist');
                return;
            }
            $rows=$this->_brand_mod->edit($brand_id, $data);
            if ($this->_brand_mod->has_error())
            {
				$error = current($this->_brand_mod->get_error());
                $this->json_error($error['msg']);
                return;
            }
			
            $this->json_result('','edit_brand_successed');
        }
    }

         //异步修改数据
   function ajax_col()
   {
       $id     = empty($_GET['id']) ? 0 : intval($_GET['id']);
       $column = empty($_GET['column']) ? '' : trim($_GET['column']);
       $value  = isset($_GET['value']) ? trim($_GET['value']) : '';
       $data   = array();
       if (in_array($column ,array('brand_name', 'recommended', 'sort_order', 'tag','if_show')))
       {
           $data[$column] = $value;
           if($column == 'brand_name')
           {
               $brand = $this->_brand_mod->get_info($id);

               if(!$this->_brand_mod->unique($value, $id))
               {
                   echo ecm_json_encode(false);
                   return ;
               }
           }
           $this->_brand_mod->edit($id, $data);
           if(!$this->_brand_mod->has_error())
           {
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
        $brand_ids = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$brand_ids)
        {
            $this->json_error('no_such_brand');

            return;
        }
        $brand_ids=explode(',',$brand_ids);
        $this->_brand_mod->drop($brand_ids);
        if ($this->_brand_mod->has_error())    //删除
        {
			$error = current($this->_brand_mod->get_error());
            $this->json_error($error['msg']);
            return;
        }

        $this->json_result('','drop_brand_successed');
    }
        /**
     *    处理上传标志
     *
     *    @author    Hyber
     *    @param     int $brand_id
     *    @return    string
     */
    function _upload_logo($brand_id)
    {
        $file = $_FILES['logo'];
        if ($file['error'] == UPLOAD_ERR_NO_FILE) // 没有文件被上传
        {
            $this->json_error('logo_accept_error');
			return false;
        }
        import('uploader.lib');             //导入上传类
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE); //限制文件类型
        $uploader->addFile($_FILES['logo']);//上传logo
        if (!$uploader->file_info())
        {
			$error = current($uploader->get_error());
            $this->json_error($error['msg'], 'go_back', 'index.php?app=brand&amp;act=edit&amp;id=' . $brand_id);
            return false;
        }
        /* 指定保存位置的根目录 */
        $uploader->root_dir(ROOT_PATH);

        /* 上传 */
        if ($file_path = $uploader->save('data/files/mall/brand', $brand_id))   //保存到指定目录，并以指定文件名$brand_id存储
        {
            return $file_path;
        }
        else
        {
            return false;
        }
    }

    function pass()
    {
        $id = $_GET['id'];
        if (empty($id))
        {
            $this->json_error('request_error');
            exit;
        }
        $ids = explode(',', $id);
        $brands = $this->_brand_mod->find(db_create_in($ids, 'brand_id') . " AND if_show = 0");
        $this->_brand_mod->edit(db_create_in(array_keys($brands), 'brand_id'), array('if_show' => 1));
        if ($this->_brand_mod->has_error())
        {
			$error = current($this->_brand_mod->get_error());
            $this->json_error($error['msg']);
            exit;
        }
        $ms =& ms();
        $content = '';
        foreach ($brands as $brand)
        {
            $content = get_msg('toseller_brand_passed_notify', array('brand_name' => $brand['brand_name']));
            $ms->pm->send(MSG_SYSTEM, $brand['store_id'], '', $content);
        }
        $this->json_result('','brand_passed');
    }

    function refuse()
    {
        $id = $_GET['id'];
        if (empty($id))
        {
            $this->json_error('request_error');
            exit;
        }
            if (empty($_GET['content']))
            {
                $this->json_error('content_required');
                exit;
            }
            $ids = explode(',', trim($_GET['id']));
            $brands = $this->_brand_mod->find(db_create_in($ids, 'brand_id') . ' AND if_show = 0');
            $ms =& ms();
            $content = '';
            foreach ($brands as $brand)
            {
                $content = get_msg('toseller_brand_refused_notify', array('brand_name' => $brand['brand_name'], 'reason' => trim($_GET['content'])));
                $ms->pm->send(MSG_SYSTEM, $brand['store_id'], '', $content);
                if (is_file(ROOT_PATH . '/' . $brand['brand_logo']) && file_exists(ROOT_PATH . '/' . $brand['brand_logo']))
                {
                    unlink(ROOT_PATH . '/' . $brand['brand_logo']);
                }
                $this->_brand_mod->drop($brand['brand_id']);
            }
            $this->json_result('','brand_refused');
    }


}

?>