<?php
/**
 *    商品管理控制器
 */
class GoodsApp extends BackendApp
{
    var $_goods_mod;
	var $_distribution_mod;
	
    function __construct()
    {
        $this->GoodsApp();
    }
    function GoodsApp()
    {
        parent::BackendApp();

        $this->_goods_mod =& m('goods');
		$this->_distribution_mod = &m('distribution');
    }

    /* 商品列表 */
    function index()
    {
		$cate_mod =& bm('gcategory', array('_store_id' => 0));
		$this->assign('gcategories', $cate_mod->get_options(0, true));
		$query = $this->get_query_conditions();
		$this->assign('filtered', $query);
        $this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js,inline_edit.js',
		));
        $this->display('goods.index.html');
    }
	
	function get_xml()
	{
		$conditions = "1=1";
		$conditions .= $this->get_query_conditions();
		if ($_GET['closed'] == 1) {
            $conditions .= " AND closed=1";
        }
		$order = 'goods_id DESC';
        $param = array('goods_name','price','store_name','brand','cate_name','if_show','closed','views');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
		$goods_list = $this->_goods_mod->get_list(array(
            'conditions' => $conditions,
            'count' => true,
            'order' => $order,
            'limit' => $page['limit'],
        ));
        $page['item_count'] = $this->_goods_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($goods_list as $k => $v){
			$list = array();
			$operation = "<a class='btn red' onclick=\"fg_delete({$v['goods_id']},'goods','',true)\"><i class='fa fa-trash-o'></i>删除</a>";
			$operation .= "<span class='btn'><em><i class='fa fa-cog'></i>设置 <i class='arrow'></i></em><ul>";
			$operation .= "<li><a href='" .SITE_URL."/index.php?app=goods&id=".$v['goods_id']. "' target=\"_blank\">查看</a></li>";
			$operation .= "<li><a href='index.php?app=goods&act=edit&id={$v['goods_id']}'>编辑</a></li>";
			$operation .= "<li><a href='index.php?app=goods&act=recommend&id={$v['goods_id']}'>推荐</a></li>";
			$operation .= "</ul>";
			$list['operation'] = $operation;
			$list['goods_name'] = '<span ectype="inline_edit" fieldname="goods_name" fieldid="'.$v['goods_id'].'"  required="1" class="editable" title="'.Lang::get('editable').'">'.$v['goods_name'].'</span>';
			$list['goods_id'] = $v['goods_id'];
			$list['price'] = $v['price'];
			$list['store_name'] = $v['store_name'];
			$list['brand'] = '<span ectype="inline_edit" fieldname="brand" fieldid="'.$v['goods_id'].'"  required="1" class="editable" title="'.Lang::get('editable').'">'.$v['brand'].'</span>';
			$list['cate_name'] = $this->_goods_mod->format_goods_cate_name($v['cate_id']);
			$list['distribution_rate'] = $this->_distribution_mod->get_distribution_rate($v['store_id']) ? implode(' / ',$this->_distribution_mod->get_distribution_rate($v['store_id'])) : '-';
			$list['if_show'] = $v['if_show'] == 0 ? '<span class="no"><i class="fa fa-ban"></i>否</span>' : '<span class="yes"><i class="fa fa-check-circle"></i>是</span>';
			$list['closed'] = $v['closed'] == 0 ? '<em class="no" ectype="inline_edit" fieldname="closed" fieldid="'.$v['goods_id'].'" fieldvalue="0" title="'.Lang::get('editable').'"><i class="fa fa-ban"></i>否</em>' : '<em class="yes" ectype="inline_edit" fieldname="closed" fieldid="'.$v['goods_id'].'" fieldvalue="1" title="'.Lang::get('editable').'"><i class="fa fa-check-circle"></i>是</em>';
			$list['views'] = $v['views'];
			$data['list'][$k] = $list;
		}
		$this->flexigridXML($data);
	}
	
	function get_query_conditions(){
		$conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'goods_name',
                'equal' => 'like',
            ),
            array(
                'field' => 'store_name',
                'equal' => 'like',
            ),
            array(
                'field' => 'brand',
                'equal' => 'like',
            )
        ));
		 // 分类
        $cate_id = empty($_GET['cate_id']) ? 0 : intval($_GET['cate_id']);
        if ($cate_id > 0)
        {
            $cate_mod =& bm('gcategory');
            $cate_ids = $cate_mod->get_descendant_ids($cate_id);
            $conditions .= " AND cate_id" . db_create_in($cate_ids);
        }
		
		return $conditions;
	}

    /* 推荐商品到 */
    function recommend()
    {
        if (!IS_POST)
        {
            /* 取得推荐类型 */
            $recommend_mod =& bm('recommend', array('_store_id' => 0));
            $recommends = $recommend_mod->get_options();
            if (!$recommends)
            {
                $this->show_warning('no_recommends', 'go_back', 'javascript:history.go(-1);', 'set_recommend', 'index.php?app=recommend');
                return;
            }
            $this->assign('recommends', $recommends);
            $this->display('goods.batch.html');
        }
        else
        {
            $id = isset($_POST['id']) ? trim($_POST['id']) : '';
            if (!$id)
            {
                $this->json_error('Hacking Attempt');
                return;
            }

            $recom_id = empty($_POST['recom_id']) ? 0 : intval($_POST['recom_id']);
            if (!$recom_id)
            {
                $this->json_error('recommend_required');
                return;
            }

            $ids = explode(',', $id);
            $recom_mod =& bm('recommend', array('_store_id' => 0));
            $recom_mod->createRelation('recommend_goods', $recom_id, $ids);
            $this->json_result('','recommend_ok');
        }
    }

    /* 编辑商品 */
    function edit()
    {
        if (!IS_POST)
        {
            // 第一级分类
            $cate_mod =& bm('gcategory', array('_store_id' => 0));
            $this->assign('gcategories', $cate_mod->get_options(0, true));

            $this->headtag('<script type="text/javascript" src="{lib file=mlselection.js}"></script>');
            $this->display('goods.batch.html');
        }
        else
        {
            $id = isset($_POST['id']) ? trim($_POST['id']) : '';
            if (!$id)
            {
                $this->json_error('Hacking Attempt');
                return;
            }

            $ids = explode(',', $id);
            $data = array();
            if ($_POST['cate_id'] > 0)
            {
                $data['cate_id'] = $_POST['cate_id'];
                $data['cate_name'] = $_POST['cate_name'];
            }
            if (trim($_POST['brand']))
            {
                $data['brand'] = trim($_POST['brand']);
            }
            if ($_POST['closed'] >= 0)
            {
                $data['closed'] = $_POST['closed'] ? 1 : 0;
                $data['close_reason'] = $_POST['closed'] ? $_POST['close_reason'] : '';
            }

            if (empty($data))
            {
                $this->json_error('no_change_set');
                return;
            }

            $this->_goods_mod->edit($ids, $data);
            $this->json_result('','edit_ok');
        }
    }

    //异步修改数据
   function ajax_col()
   {
       $id     = empty($_GET['id']) ? 0 : intval($_GET['id']);
       $column = empty($_GET['column']) ? '' : trim($_GET['column']);
       $value  = isset($_GET['value']) ? trim($_GET['value']) : '';
       $data   = array();

       if (in_array($column ,array('goods_name', 'brand', 'closed')))
       {
           $data[$column] = $value;
           $this->_goods_mod->edit($id, $data);
           if(!$this->_goods_mod->has_error())
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

    /* 删除商品 */
    function drop()
    {
		$id = isset($_GET['id']) ? trim($_GET['id']) : '';
		if (!$id)
		{
			$this->json_error('Hacking Attempt');
			return;
		}
		$ids = explode(',', $id);
		$ms =& ms();
		$goods_list = $this->_goods_mod->find(array(
			"conditions" => $ids,
			"fields" => "goods_name, store_id",
		));
		foreach ($goods_list as $goods)
		{
			$content = get_msg('toseller_goods_droped_notify', array('reason' => trim($_GET['content']),
				'goods_name' => addslashes($goods['goods_name'])));
			$ms->pm->send(MSG_SYSTEM, $goods['store_id'], '', $content);
		}
		$this->_goods_mod->drop_data($ids);
		$this->_goods_mod->drop($ids);
		$this->json_result('','drop_ok');
    }
	
	function export_csv()
	{
		$conditions = '1=1';
		if ($_GET['closed'] == 1) {
            $conditions .= " AND closed=1";
        }
		if ($_GET['id'] != '') {
            $ids = explode(',', $_GET['id']);
			$conditions .= ' AND g.goods_id' . db_create_in($ids);
        }
		if ($_GET['query'] != '') 
		{
			$conditions .= " AND ".$_GET['qtype']." like '%" . $_GET['query'] . "%'";
		}
        $goods_list = $this->_goods_mod->get_list(array(
            'conditions' => $conditions,
        ));
		if(!$goods_list) {
			$this->show_warning('no_such_goods');
            return;
		}
		/* xls文件数组 */
		$record_xls = array();		
		$record_title = array(
			'goods_id' 		=> 	'ID',
			'goods_name' 	=> 	'商品名称',
    		'price' 		=> 	'价格',
    		'store_name' 	=> 	'店铺名称',
			'brand' 		=>  '品牌',
    		'cate_name' 	=> 	'所属分类',
			'distribution_rate' => 	'分销比率',
    		'if_show' 		=> 	'上架',
    		'closed' 		=> 	'禁售',
			'views'  		=>  '浏览数',
		);
		$folder = 'goods_'.local_date('Ymdhis', gmtime());
		$record_xls[] = $record_title;
		$record_value = array();
		foreach($goods_list as $key=>$val)
    	{
			$record_value['goods_id']	=	$val['goods_id'];
			$record_value['goods_name']	=	$val['goods_name'];
			$record_value['price']		=	$val['price'];
			$record_value['store_name']	=	$val['store_name'];
			$record_value['brand']		=	$val['brand'];
			$record_value['cate_name']	=	$this->_goods_mod->format_cate_name($val['cate_name']);
			$record_value['distribution_rate']	=	$this->_distribution_mod->get_distribution_rate($val['goods_id']) ? implode(' / ',$this->_distribution_mod->get_distribution_rate($val['goods_id'])) : '-';
			$record_value['if_show']	=	$val['if_show'] == 0 ? '否' : '是';
			$record_value['closed']		=	$val['closed'] == 0 ? '否' : '是';
			$record_value['views']		=	$val['views'];
        	$record_xls[] = $record_value;
    	}
		$record_xls[] = array('商品总数:',count($goods_list));
		import('excelwriter.lib');
		$ExcelWriter = new ExcelWriter(CHARSET, $folder);
		$ExcelWriter->add_array($record_xls);
		$ExcelWriter->output();
	}
}

?>
