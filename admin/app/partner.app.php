<?php

/**
 *    合作伙伴控制器
 *
 *    @usage    none
 */
class PartnerApp extends BackendApp
{
    var $_partner_mod;

    function __construct()
    {
        $this->PartnerApp();
    }

    function PartnerApp()
    {
        parent::BackendApp();

        $this->_partner_mod =& m('partner');
    }
	
	function index()
    {
		$query = $this->get_query_conditions();
		$this->assign('filtered', $query);
        $this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js,inline_edit.js',
		));
        $this->display('partner.index.html');
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
        $partners = $this->_partner_mod->find(array(
            'conditions'    => 'store_id=0' . $conditions,
            'limit'         => $page['limit'],
            'order'         => $order,
            'count'         => true            
        ));
        $page['item_count'] = $this->_partner_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($partners as $k => $v){
			$list = array();
			$list['operation'] = "<a class='btn red' onclick=\"fg_delete({$k},'partner')\"><i class='fa fa-trash-o'></i>删除</a><a class='btn blue' href='index.php?app=partner&act=edit&id={$k}'><i class='fa fa-pencil-square-o'></i>编辑</a>";
			$list['title'] = '<span ectype="inline_edit" fieldname="title" fieldid="'.$k.'" required="1" class="editable" title="'.Lang::get('editable').'">'.$v['title'].'</span>';
			$list['link'] = $v['link'];
			$list['logo'] = $v['logo'] ? '<img src="'.dirname(site_url()) . '/' . $v['logo'].'" height="25"/>' : '';
			$list['sort_order'] = '<span ectype="inline_edit" fieldname="sort_order" fieldid="'.$k.'" datatype="pint" maxvalue="255" class="editable" title="'.Lang::get('editable').'">'.$v['sort_order'].'</span>';
			$list['if_show'] = $v['if_show'] == 0 ? '<em class="no" ectype="inline_edit" fieldname="if_show" fieldid="'.$k.'" fieldvalue="0" title="'.Lang::get('editable').'"><i class="fa fa-ban"></i>否</em>' : '<em class="yes" ectype="inline_edit" fieldname="if_show" fieldid="'.$k.'" fieldvalue="1" title="'.Lang::get('editable').'"><i class="fa fa-check-circle"></i>是</em>';
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
		
    /**
     *    新增
     *

     *    @return    void
     */
    function add()
    {
        if (!IS_POST)
        {
            /* 显示新增表单 */
            $partner = array(
            	'sort_order'    => '255',
            	'link'          => 'http://',
				'if_show'       => 1,
            );
            $this->assign('partner' , $partner);
            $this->display('partner.form.html');
        }
        else
        {
            $data = array();
            $data['store_id']   =   0;
            $data['title']      =   $_POST['title'];
            $data['link']       =   $_POST['link'];
			$data['if_show']    =   $_POST['if_show'];
            $data['sort_order'] =   $_POST['sort_order'];

            if (!$partner_id = $this->_partner_mod->add($data))  //获取partner_id
            {
				$error = current($this->_partner_mod->get_error());
            	$this->json_error($error['msg']);

                return;
            }

			if (!empty($_FILES['logo']))
            {
				$logo       =   $this->_upload_logo($partner_id);
            	$logo && $this->_partner_mod->edit($partner_id, array('logo' => $logo)); //将logo地址记下
			}

            $this->json_result('','add_partner_successed');
        }
    }

    /**
     *    编辑
     *

     *    @return    void
     */
    function edit()
    {
        $partner_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$partner_id)
        {
            $this->show_warning('no_such_partner');

            return;
        }
        if (!IS_POST)
        {
            $find_data     = $this->_partner_mod->find($partner_id);
            if (empty($find_data))
            {
                $this->show_warning('no_such_partner');

                return;
            }
            $partner    =   current($find_data);
            if ($partner['logo'])
            {
                $partner['logo']  =   dirname(site_url()) . "/" . $partner['logo'];
            }
            $this->assign('partner', $partner);
            $this->display('partner.form.html');
        }
        else
        {
            $data = array();
            $data['title']      =   $_POST['title'];
            $data['link']       =   $_POST['link'];
			$data['if_show']    =   $_POST['if_show'];
            $data['sort_order'] =   $_POST['sort_order'];
			if (!empty($_FILES['logo']))
            {
				$logo       =   $this->_upload_logo($partner_id);
            	$logo && $data['logo'] = $logo;
			}
            $rows = $this->_partner_mod->edit($partner_id, $data);
            if ($this->_partner_mod->has_error())    //有错误
            {
                $error = current($this->_partner_mod->get_error());
            	$this->json_error($error['msg']);

                return;
            }

            $this->json_result('','edit_partner_successed');
        }
    }

    //异步修改数据
   function ajax_col()
   {
       $id     = empty($_GET['id']) ? 0 : intval($_GET['id']);
       $column = empty($_GET['column']) ? '' : trim($_GET['column']);
       $value  = isset($_GET['value']) ? trim($_GET['value']) : '';
       $data   = array();

       if (in_array($column ,array('title', 'sort_order','if_show')))
       {
           $data[$column] = $value;
           $this->_partner_mod->edit($id, $data);
           if(!$this->_partner_mod->has_error())
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
        $partner_ids = isset($_GET['id']) ? trim($_GET['id']) : 0;
        if (!$partner_ids)
        {
            $this->json_error('no_such_partner');

            return;
        }
        $partner_ids = explode(',', $partner_ids);//获取一个类似array(1, 2, 3)的数组
        if (!$this->_partner_mod->drop($partner_ids))    //删除
        {
            $error = current($this->_partner_mod->get_error());
            $this->json_error($error['msg']);

            return;
        }

        $this->json_result('','drop_partner_successed');
    }

    /**
     *    处理上传标志
     *

     *    @param     int $partner_id
     *    @return    string
     */
    function _upload_logo($partner_id)
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
            $this->json_error($error['msg']);
            return false;
        }
        /* 指定保存位置的根目录 */
        $uploader->root_dir(ROOT_PATH);

        /* 上传 */
        if ($file_path = $uploader->save('data/files/mall/partner', $partner_id))   //保存到指定目录，并以指定文件名$partner_id存储
        {
            return $file_path;
        }
        else
        {
            return false;
        }
    }
}

?>