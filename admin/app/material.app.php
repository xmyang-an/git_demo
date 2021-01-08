<?php


class MaterialApp extends BackendApp
{
    var $_material_mod;

    function __construct()
    {
        $this->MaterialApp();
    }

    function MaterialApp()
    {
        parent::BackendApp();

        $this->_material_mod =& m('material');
    }

    /**
     *    商品品牌索引
     *
     *    @author    Hyber
     *    @return    void
     */
    function index()
    {
		$this->assign('types', $this->_get_types());
		$this->assign('devices', $this->_get_device());
			
		$query = $this->get_query_conditions();
		$this->assign('filtered', $query);
        $this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js,inline_edit.js',
		));
        $this->display('material.index.html');
    }
	
	function get_xml()
	{
		$conditions = '1=1';

		$conditions .= $this->get_query_conditions();
		$order = 'sort_order ASC,id DESC';
        $param = array('brand_id','brand_name','tag','sort_order','recommended','if_show');

		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
        $brands=$this->_material_mod->find(array(
        	'conditions'    => $conditions,
			'limit'   => $page['limit'],
			'order'   => $order,
			'count'   => true 
        )); 
        $page['item_count'] = $this->_material_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($brands as $k => $v){
			$types = $this->_get_types();
			$devices = $this->_get_device();
			
			$list = array();
			$list['operation'] = "<a class='btn red' onclick=\"fg_delete({$v['id']},'material')\"><i class='fa fa-trash-o'></i>删除</a><a class='btn blue' href='index.php?app=material&act=edit&id={$v['id']}'><i class='fa fa-pencil-square-o'></i>编辑</a>";
			$list['name'] = '<span ectype="inline_edit" fieldname="name" fieldid="'.$v['id'].'" class="editable" title="'.Lang::get('editable').'">'.$v['name'].'</span>';
			$list['url'] = $v['url'] ? '<img src="'.dirname(site_url()) . '/' . $v['url'].'" height="25"/>' : '';
			$list['link'] = '<span ectype="inline_edit" fieldname="link" fieldid="'.$v['id'].'" class="editable" title="'.Lang::get('editable').'">'.$v['link'].'</span>';
			$list['type'] = '<span>'.$types[$v['type']].'</span>';
			$list['device'] = '<span>'.$devices[$v['device']].'</span>';
			$list['sort_order'] = '<span ectype="inline_edit" fieldname="sort_order" fieldid="'.$v['id'].'" datatype="pint" maxvalue="255" class="editable" title="'.Lang::get('editable').'">'.$v['sort_order'].'</span>';
			$list['if_show'] = $v['if_show'] == 0 ? '<em class="no" ectype="inline_edit" fieldname="if_show" fieldid="'.$k.'" fieldvalue="0" title="'.Lang::get('editable').'"><i class="fa fa-ban"></i>否</em>' : '<em class="yes" ectype="inline_edit" fieldname="if_show" fieldid="'.$k.'" fieldvalue="1" title="'.Lang::get('editable').'"><i class="fa fa-check-circle"></i>是</em>';
			$data['list'][$k] = $list;
		}
		$this->flexigridXML($data);
	}
	
	function get_query_conditions(){
		$conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'name',
                'equal' => 'like',
            ),
			array(
                'field' => 'type',
                'equal' => '=',
            ),
			array(
                'field' => 'device',
                'equal' => '=',
            )
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
            $material = array(
                'sort_order' => 255
            );
			
            $yes_or_no = array(
                1 => Lang::get('yes'),
                0 => Lang::get('no'),
            );
            $this->assign('yes_or_no', $yes_or_no);
            $this->assign('material', $material);
			
			$this->assign('types', $this->_get_types());
			$this->assign('devices', $this->_get_device());
			
            $this->display('material.form.html');
        }
        else
        {
            $data = array();
            $data['name']     = $_POST['name'];
            $data['sort_order']     = $_POST['sort_order'];
            $data['link']    = $_POST['link'];
            $data['type'] = $_POST['type'];
			$data['device'] = $_POST['device'];
            $data['if_show'] = 1;

            if (!$id = $this->_material_mod->add($data))  //获取brand_id
            {
				$error = current($this->_material_mod->get_error());
                $this->json_error($error['msg']);
                return;
            }

            /* 处理上传的图片 */
			if (!empty($_FILES['url']))
            {
				$url = $this->_upload_logo($id);
				$url && $this->_material_mod->edit($id, array('url' => $url));
            }
            
            $this->json_result('','新增成功');
        }
    }

     /**
     *    编辑商品品牌
     *
     *    @author    Hyber
     *    @return    void
     */
    function edit()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$id)
        {
            $this->show_warning('no_such_material');
            return;
        }
         if (!IS_POST)
        {
            $find_data     = $this->_material_mod->find($id);
            if (empty($find_data))
            {
                $this->show_warning('no_such_material');
                return;
            }
			
            $material    =   current($find_data);
            if ($material['url'])
            {
                $material['url']  =   dirname(site_url()) . "/" . $material['url'];
            }
            /* 显示新增表单 */
            $yes_or_no = array(
                1 => Lang::get('yes'),
                0 => Lang::get('no'),
            );
			
            $this->assign('yes_or_no', $yes_or_no);
            $this->assign('material', $material);
			
			
			$this->assign('types', $this->_get_types());
			$this->assign('devices', $this->_get_device());
			
            $this->display('material.form.html');
        }
        else
        {
            $data = array();
            $data['name']     = $_POST['name'];
            $data['sort_order']     = $_POST['sort_order'];
            $data['link']    = $_POST['link'];
            $data['type'] = $_POST['type'];
			$data['device'] = $_POST['device'];
            $data['if_show'] = 1;
			
            if (!empty($_FILES['url']))
            {
				$url = $this->_upload_logo($id);
				$url && $data['url'] = $url;
            }
			
            $rows=$this->_material_mod->edit($id, $data);
            if ($this->_material_mod->has_error())
            {
				$error = current($this->_material_mod->get_error());
                $this->json_error($error['msg']);
                return;
            }
			
            $this->json_result('','编辑成功');
        }
    }

         //异步修改数据
   function ajax_col()
   {
       $id     = empty($_GET['id']) ? 0 : intval($_GET['id']);
       $column = empty($_GET['column']) ? '' : trim($_GET['column']);
       $value  = isset($_GET['value']) ? trim($_GET['value']) : '';
       $data   = array();
       if (in_array($column ,array('name', 'link', 'sort_order', 'if_show')))
       {
           $data[$column] = $value;
           $this->_material_mod->edit($id, $data);
           if(!$this->_material_mod->has_error())
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
        $ids = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$ids)
        {
            $this->json_error('no_such_material');

            return;
        }
		
        $ids=explode(',',$ids);
        $this->_material_mod->drop($ids);
        if ($this->_material_mod->has_error())    //删除
        {
			$error = current($this->_material_mod->get_error());
            $this->json_error($error['msg']);
            return;
        }

        $this->json_result('','删除成功');
    }
        /**
     *    处理上传标志
     *
     *    @author    Hyber
     *    @param     int $brand_id
     *    @return    string
     */
    function _upload_logo($id)
    {
        $file = $_FILES['url'];
        if ($file['error'] == UPLOAD_ERR_NO_FILE) // 没有文件被上传
        {
            $this->json_error('logo_accept_error');
			return false;
        }
        import('uploader.lib');             //导入上传类
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE); //限制文件类型
        $uploader->addFile($_FILES['url']);//上传logo
        if (!$uploader->file_info())
        {
			$error = current($uploader->get_error());
            $this->json_error($error['msg'], 'go_back', 'index.php?app=material&amp;act=edit&amp;id=' . $id);
            return false;
        }
        /* 指定保存位置的根目录 */
        $uploader->root_dir(ROOT_PATH);

        /* 上传 */
        if ($file_path = $uploader->save('data/files/mall/material', $uploader->random_filename()))   //保存到指定目录，并以指定文件名$brand_id存储
        {
            return $file_path;
        }
        else
        {
            return false;
        }
    }
	
	function _get_types(){
		return array(
			'MP_HOME_SLIDE' => '首页幻灯片',
			'MP_HOME_NAV'    => '导航图标',
			'MP_HOME_FLLOR_ONE'    => '首页楼层广告（壹）',
			'MP_HOME_FLLOR_TWO'    => '首页楼层广告（贰）',
			'MP_HOME_FLLOR_THREE'    => '首页楼层广告（叁）',
			'MP_HOME_FLLOR_FOUR'    => '首页楼层广告（肆）'
		);
	}
	
	function _get_device(){
		return array(
			'MP' => '小程序'
		);
	}
}

?>