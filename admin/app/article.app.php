<?php

/**
 *    文章管理控制器
 *
 *    @author    Hyber
 *    @usage    none
 */
class ArticleApp extends BackendApp
{
    var $_article_mod;
    var $_uploadedfile_mod;

    function __construct()
    {
        $this->ArticleApp();
    }

    function ArticleApp()
    {
        parent::BackendApp();

        $this->_article_mod =& m('article');
        $this->_uploadedfile_mod = &m('uploadedfile');
    }

    /**
     *    文章索引
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
		$this->assign('parents', $this->_get_options()); //分类树
        $this->display('article.index.html');
    }
	
	function get_xml()
	{
		$conditions = 'store_id=0';
		$conditions .= $this->get_query_conditions();
		$order = 'article.sort_order ASC,article.add_time DESC';
        $param = array('sort_order','title','cate_name','if_show','add_time');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
        $articles = $this->_article_mod->find(array(
			'fields'   => 'article.*,acategory.cate_name',
			'conditions'  => $conditions,
			'limit'   => $page['limit'],
			'join'    => 'belongs_to_acategory',
			'order'   => $order,
			'count'   => true 
        )); 
        $page['item_count'] = $this->_article_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($articles as $k => $v){
			$list = array();
			$list['operation'] = "<a class='btn red' onclick=\"fg_delete({$v['article_id']},'article')\"><i class='fa fa-trash-o'></i>删除</a><a class='btn blue' href='index.php?app=article&act=edit&id={$v['article_id']}'><i class='fa fa-pencil-square-o'></i>编辑</a><a class='btn green' href='".SITE_URL."/index.php?app=article&act=view&article_id={$k}' target='_blank'><i class='fa fa-search-plus'></i>查看</a>";
			$list['sort_order'] = '<span ectype="inline_edit" fieldname="sort_order" fieldid="'.$k.'" datatype="pint" maxvalue="255" class="editable" title="'.Lang::get('editable').'">'.$v['sort_order'].'</span>';
			$list['title'] = $v['title'];
			$list['cate_name'] = $v['cate_name'];
			$list['if_show'] = $v['if_show'] == 0 ? '<em class="no" ectype="inline_edit" fieldname="if_show" fieldid="'.$k.'" fieldvalue="0" title="'.Lang::get('editable').'"><i class="fa fa-ban"></i>否</em>' : '<em class="yes" ectype="inline_edit" fieldname="if_show" fieldid="'.$k.'" fieldvalue="1" title="'.Lang::get('editable').'"><i class="fa fa-check-circle"></i>是</em>';
			$list['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
			$data['list'][$k] = $list;
		}
		$this->flexigridXML($data);
	}

	function get_query_conditions(){
		$conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'title',
                'equal' => 'like',
            )
        ));
		$cate_id = !empty($_GET['cate_id'])? intval($_GET['cate_id']) : 0;
        if ($cate_id > 0) //取得该分类及子分类cate_id
        {
            $acategory_mod = & m('acategory');
            $cate_ids = $acategory_mod->get_descendant($cate_id);
        }
        !empty($cate_ids)&& $conditions .= ' AND article.cate_id ' . db_create_in($cate_ids);
		return $conditions;
	}
	
     /**
     *    新增文章
     *
     *    @author    Hyber
     *    @return    void
     */
    function add()
    {
        if (!IS_POST)
        {
            /* 显示新增表单 */
            $cate_id = isset ($_GET['cate_id']) ? intval($_GET['cate_id']) : 0;//方便在某个分类下新增文章
            $article = array('cate_id' => $cate_id, 'sort_order' => 255, 'link' => '', 'if_show' => 1);

            /* 文章模型未分配的附件 */
            $files_belong_article = $this->_uploadedfile_mod->find(array(
                'conditions' => 'store_id = 0 AND belong = ' . BELONG_ARTICLE . ' AND item_id = 0',
                'fields' => 'this.file_id, this.file_name, this.file_path',
                'order' => 'add_time DESC'
            ));

            $this->assign("id", 0);
            $this->assign('belong', BELONG_ARTICLE);

            $this->import_resource(array('script' => 'change_upload.js'));
            $this->assign('article', $article);
            $this->assign('files_belong_article', $files_belong_article);
            $this->assign('parents', $this->_get_options()); //分类树
            
            $template_name = $this->_get_template_name();
            $style_name    = $this->_get_style_name();
            $this->assign('build_editor', $this->_build_editor(array(
                'name' => 'content'
            )));
            
            $this->assign('build_upload', $this->_build_upload(array('obj' => 'EDITOR_SWFU','belong' => BELONG_ARTICLE, 'item_id' => 0))); // 构建WebUploader上传组件
            $this->display('article.form.html');
        }
        else
        {
            $data = array();
            $data['title']      =   $_POST['title'];
            $data['cate_id']    =   $_POST['cate_id'];
            $data['link']       =   $_POST['link'] == 'http://' ? '' : $_POST['link'];
            $data['if_show']    =   $_POST['if_show'];
            $data['sort_order'] =   $_POST['sort_order'];
            $data['content'] =   $_POST['content'];
            $data['add_time']   =   gmtime();
            if (!$article_id = $this->_article_mod->add($data))  //获取article_id
            {
				$error = current($this->_article_mod->get_error());
            	$this->json_error($error['msg']);

                return;
            }

            /* 附件入库 */
            if (isset($_POST['file_id']))
            {
                foreach ($_POST['file_id'] as $file_id)
                {
                    $this->_uploadedfile_mod->edit($file_id, array('item_id' => $article_id));
                }
            }
            $this->json_result('','add_article_successed');
        }
    }
     /**
     *    编辑文章
     *
     *    @author    Hyber
     *    @return    void
     */
    function edit()
    {
        $article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$article_id)
        {
            $this->show_warning('no_such_article');
            return;
        }
         if (!IS_POST)
        {
            /* 当前文章的附件 */
            $files_belong_article = $this->_uploadedfile_mod->find(array(
                'conditions' => 'store_id = 0 AND belong = ' . BELONG_ARTICLE . ' AND item_id=' . $article_id,
                'fields' => 'this.file_id, this.file_name, this.file_path',
                'order' => 'add_time DESC'
            ));

            $find_data     = $this->_article_mod->find($article_id);
            if (empty($find_data))
            {
                $this->show_warning('no_such_article');

                return;
            }
            $article    =   current($find_data);
            $article['link'] = $article['link'] ? $article['link'] : '';
            $this->assign("id", $article_id);
            $this->assign("belong", BELONG_ARTICLE);
            $this->import_resource(array('script' => 'change_upload.js'));
            $this->assign('parents', $this->_get_options());
            $this->assign('files_belong_article', $files_belong_article);
            $this->assign('article', $article);
            
            $template_name = $this->_get_template_name();
            $style_name    = $this->_get_style_name();
            $this->assign('build_editor', $this->_build_editor(array(
                'name' => 'content'
            )));
            
            $this->assign('build_upload', $this->_build_upload(array('obj' => 'EDITOR_SWFU','belong' => BELONG_ARTICLE, 'item_id' => $article_id))); // 构建WebUploader上传组件
            $this->display('article.form.html');
        }
        else
        {
            $data = array();
            $data['title']          =   $_POST['title'];
            if (!empty($_POST['cate_id']))
            {
                $data['cate_id']        =   $_POST['cate_id'];
            }
            $data['link']           =   $_POST['link'] == 'http://' ? '' : $_POST['link'];
            $data['if_show']        =   $_POST['if_show'];
            $data['sort_order']     =   $_POST['sort_order'];
            $data['content']        =   $_POST['content'];

            $rows = $this->_article_mod->edit($article_id, $data);
            if ($this->_article_mod->has_error())
            {
                $error = current($this->_article_mod->get_error());
            	$this->json_error($error['msg']);

                return;
            }

            $this->json_result('','edit_article_successed');
        }
    }

    //异步修改数据
   function ajax_col()
   {
       $id     = empty($_GET['id']) ? 0 : intval($_GET['id']);
       $column = empty($_GET['column']) ? '' : trim($_GET['column']);
       $value  = isset($_GET['value']) ? trim($_GET['value']) : '';
       $data   = array();

       if (in_array($column ,array('if_show', 'sort_order')))
       {
           $data[$column] = $value;
           $this->_article_mod->edit($id, $data);
           if(!$this->_article_mod->has_error())
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
        $article_ids = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$article_ids)
        {
            $this->json_error('no_such_article');

            return;
        }
        $article_ids=explode(',', $article_ids);
        $message = 'drop_ok';
        foreach ($article_ids as $key=>$article_id){
            $article=$this->_article_mod->find(intval($article_id));
            $article=current($article);
            if($article['code']!=null)
            {
                unset($article_ids[$key]);  //有部分是系统文章 过滤掉
                $message = 'drop_ok_system_article';
            }
        }
        if (!$article_ids)
        {
            $message = 'system_article'; //全是系统文章
            $this->json_error($message);

            return;
        }
        if (!$this->_article_mod->drop($article_ids))    //删除
        {
			$error = current($this->_article_mod->get_error());
            $this->json_error($error['msg']);
            return;
        }

        $this->json_result($message);
    }

        /* 构造并返回树 */
    function &_tree($acategories)
    {
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($acategories, 'cate_id', 'parent_id', 'cate_name');
        return $tree;
    }
        /* 取得可以作为上级的文章分类数据 */
    function _get_options()
    {
        $mod_acategory = &m('acategory');
        $acategorys = $mod_acategory->get_list();
        $tree =& $this->_tree($acategorys);
        return $tree->getOptions();
    }

    /* 异步删除附件 */
    function drop_uploadedfile()
    {
        $file_id = isset($_GET['file_id']) ? intval($_GET['file_id']) : 0;
        if ($file_id && $this->_uploadedfile_mod->drop($file_id))
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