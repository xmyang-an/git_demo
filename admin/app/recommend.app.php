<?php

class RecommendApp extends BackendApp
{
    var $_recommend_mod;

    function __construct()
    {
        $this->RecommendApp();
    }

    function RecommendApp()
    {
        parent::BackendApp();

        $this->_recommend_mod =& bm('recommend', array('_store_id' => 0));
    }

	function index()
    {
		$query = $this->get_query_conditions();
		$this->assign('filtered', $query);
        $this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js',
		));
        $this->display('recommend.index.html');
    }
	
	function get_xml()
	{
		$conditions = '';
		$conditions .= $this->get_query_conditions();
		$order = 'recom_id DESC';
        $param = array('recom_id','recom_name');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
        $recommends = $this->_recommend_mod->find(array(
            'conditions' => '1=1' . $conditions,
            'count' => true,
            'order' => $order,
            'limit' => $page['limit'],
        ));
        $count = $this->_recommend_mod->count_goods();
        $page['item_count'] = $this->_recommend_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($recommends as $k => $v){
			$list = array();
			$list['operation'] = "<a class='btn red' onclick=\"fg_delete({$v['recom_id']},'recommend')\"><i class='fa fa-trash-o'></i>删除</a><a class='btn blue' href='index.php?app=recommend&act=edit&id={$v['recom_id']}'><i class='fa fa-pencil-square-o'></i>编辑</a><a class='btn orange' href='index.php?app=recommend&act=view_goods&id={$v['recom_id']}'><i class='fa fa-search'></i>查看商品</a>";
			$list['recom_id'] = $v['recom_id'];
			$list['recom_name'] = $v['recom_name'];
			$list['goods_count'] = $count[$v['recom_id']];
			$data['list'][$k] = $list;
		}
		$this->flexigridXML($data);
	}

	function get_query_conditions(){
		$conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'recom_name',
                'equal' => 'like',
            ),
        ));
		return $conditions;
	}
		
	function add()
    {
        if (!IS_POST)
        {
            $this->display('recommend.form.html');
        }
        else
        {
            /* 检查名称是否已存在 */
            if (!$this->_recommend_mod->unique(trim($_POST['recom_name'])))
            {
                $this->json_error('name_exist');
                return;
            }

            $data = array(
                'recom_name'   => $_POST['recom_name'],
            );

            $recom_id = $this->_recommend_mod->add($data);
            if (!$recom_id)
            {
				$error = current($this->_recommend_mod->get_error());
				$this->json_error($error['msg']);
                return;
            }

            $this->json_result(array('ret_url'=>'index.php?app=recommend'),'add_ok');
        }
    }

    /* 检查商品推荐的唯一性 */
    function check_recom()
    {
        $recom_name = empty($_GET['recom_name']) ? '' : trim($_GET['recom_name']);
        $recom_id   = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$recom_name) {
            echo ecm_json_encode(false);
            return ;
        }
        if ($this->_recommend_mod->unique($recom_name, $recom_id)) {
            echo ecm_json_encode(true);
        }
        else
        {
            echo ecm_json_encode(false);
        }
        return;
    }

    function edit()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!IS_POST)
        {
            /* 是否存在 */
            $recommend = $this->_recommend_mod->get_info($id);
            if (!$recommend)
            {
                $this->show_warning('recommend_empty');
                return;
            }
            $this->assign('recommend', $recommend);

            $this->display('recommend.form.html');
        }
        else
        {
            /* 检查名称是否已存在 */
            if (!$this->_recommend_mod->unique(trim($_POST['recom_name']), $id))
            {
                $this->json_error('name_exist');
                return;
            }

            $data = array(
                'recom_name'   => $_POST['recom_name'],
            );

            $this->_recommend_mod->edit($id, $data);
            $this->json_result(array('ret_url'=>'index.php?app=recommend'),'edit_ok');
        }
    }

    function drop()
    {
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$id)
        {
            $this->json_error('no_recommend_to_drop');
            return;
        }

        $ids = explode(',', $id);
        if (!$this->_recommend_mod->drop($ids))
        {
            $error = current($this->_recommend_mod->get_error());
			$this->json_error($error['msg']);
            return;
        }

        $this->json_result('','drop_ok');
    }

    /* 查看推荐类型下的商品 */
    function view_goods()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->json_error('Hacking Attempt');
            return;
        }
        $this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js,inline_edit.js',
		));
        $this->display('recommend.goods.html');
    }
	
	function get_xml_goods()
	{
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
		$goods_mod =& m('goods');
		$goods_list = $goods_mod->find(array(
            'join' => 'be_recommend, belongs_to_store, has_goodsstatistics',
            'fields' => 'g.goods_name, s.store_id, s.store_name, g.cate_name, g.brand, recommended_goods.sort_order, recommended_goods.recom_id, g.closed, g.if_show, views,g.price',
            'conditions' => "recommended_goods.recom_id =".intval($_GET['id']),
            'limit' => $page['limit'],
            'order' => 'recommended_goods.sort_order',
            'count' => true,
        ));
        $page['item_count'] = $goods_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($goods_list as $k => $v){
			$recom = $this->_recommend_mod->get($v['recom_id']);
			$list = array();
			$list['operation'] = "<a class='btn red' onclick=\"cancel_recommend({$v['goods_id']},{$_GET['id']})\"><i class='fa fa-ban'></i>取消推荐</a>";
			$list['sort_order'] = $v['sort_order'];
			$list['recom_name'] = $recom['recom_name'];
			$list['goods_name'] = $v['goods_name'];
			$list['price'] = $v['price'];
			$list['store_name'] = $v['store_name'];
			$list['brand'] = $v['brand'];
			$list['cate_name'] = $goods_mod->format_cate_name($v['cate_name']);
			$list['if_show'] = $v['if_show'] == 0 ? '<span class="no"><i class="fa fa-ban"></i>否</span>' : '<span class="yes"><i class="fa fa-check-circle"></i>是</span>';
			$list['closed'] = $v['closed'] == 0 ? '<em class="no"><i class="fa fa-ban"></i>否</em>' : '<em class="yes"><i class="fa fa-check-circle"></i>是</em>';
			$list['views'] = $v['views'];
			$data['list'][$k] = $list;
		}
		$this->flexigridXML($data);
	}

    /* 取消推荐 */
    function drop_goods_from()
    {
        if (empty($_GET['id']) || empty($_GET['goods_id']))
        {
            $this->json_error('Hacking Attempt');
            return;
        }

        $id = intval($_GET['id']);
        $goods_ids = explode(',', $_GET['goods_id']);
        $this->_recommend_mod->unlinkRelation('recommend_goods', $id, $goods_ids);

        $this->json_result('','drop_goods_from_ok');
    }

    // 异步修改数据
    function ajax_col()
    {
        $id     = $_GET['id'];
        $column = empty($_GET['column']) ? '' : trim($_GET['column']);
        $value  = intval($_GET['value']);
        $data   = array();
        $arr    = explode('-', $id);
        $recom_id = intval($arr[0]);
        $goods_id = intval($arr[1]);

        if (in_array($column ,array('sort_order')))
        {
            $data[$column] = $value;
            $this->_recommend_mod->createRelation('recommend_goods', $recom_id, array($goods_id => array('sort_order' => $value)));
            if(!$this->_recommend_mod->has_error())
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
}

?>