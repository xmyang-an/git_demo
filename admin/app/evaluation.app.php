<?php

class EvaluationApp extends BackendApp
{
    var $_order_goods_mod;
    function __construct() 
	{
        parent::__construct();
        $this->_order_goods_mod = & m('ordergoods');
    }
	
    function index()
    {
        $this->assign('query_fields', array(
            'buyer_name' => LANG::get('buyer_name'),
            'seller_name'     => LANG::get('seller_name'),
        ));
		$query = $this->get_query_conditions();
		$this->assign('filtered', $query);
        $this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js',
		));
        $this->display('evaluation.index.html');
    }
	
	function get_xml()
	{
        $conditions = 'evaluation_status = 1';		 
		$conditions .= $this->get_query_conditions();
		$order = 'evaluation_time desc';
        $param = array('evaluation_time','buyer_name','seller_name','goods_name','evaluation');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
		$ordergoods = $this->_order_goods_mod->find(array(
            'conditions' => $conditions,
            'join' => 'belongs_to_order',
            'limit' => $page['limit'],
            'order' => $order,
            'count' => true,
        ));
		$page['item_count'] = $this->_order_goods_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($ordergoods as $k => $v){
			$list = array();
			$operation = "<a class='btn red' onclick=\"fg_delete({$k},'evaluation')\"><i class='fa fa-trash-o'></i>删除</a>";
			$operation .= "<a class='btn blue' href='index.php?app=evaluation&act=edit&id={$k}'><i class='fa fa-pencil-square-o'></i>编辑</a>";
			$operation .= "<a class='btn green' href='".SITE_URL."/index.php?app=goods&act=comments&id=".$v['goods_id']."#module'  target=\"_blank\"><i class='fa fa-search-plus'></i>查看</a>";
			$list['operation'] = $operation;
			$list['evaluation_time'] = local_date('Y-m-d H:i:s',$v['evaluation_time']);
			$list['buyer_name'] = $v['buyer_name'];
			$list['store_name'] = $v['seller_name'];
			$list['goods_name'] = '<span title="'.$v['goods_name'].'">'.$v['goods_name'].'</span>';
			if($v['evaluation']==3){
				$list['evaluation'] = Lang::get('evaluation_3');
			}elseif($v['evaluation']==2){
				$list['evaluation'] = Lang::get('evaluation_2');
			}elseif($v['evaluation']==1){
				$list['evaluation'] = Lang::get('evaluation_1');
			}
			$list['comment'] = '<span title="'.$v['comment'].'">'.$v['comment'].'</span>';
			$data['list'][$k] = $list;
		}
		$this->flexigridXML($data);
	}

	function get_query_conditions(){
		$conditions = $this->_get_query_conditions(array(
            array(
                'field' => $_GET['field_name'],
                'name'  => 'field_value',
                'equal' => 'like',
            ),
        ));
        if(in_array($_GET['evaluation'], array('1','2','3'))){
            $conditions .= ' AND evaluation = '.$_GET['evaluation'];
        }
        if($_GET['comment']=='1'){
            $conditions .= " AND comment = ''";
        }elseif($_GET['comment']=='2'){
            $conditions .= " AND comment !=''";
        }
		return $conditions;
	}
	
	function setting()
	{
		$model_setting = &af('settings');
        $setting = $model_setting->getOne('evaluation'); //载入系统设置数据
        if (!IS_POST)
        {
            $this->assign('setting', $setting);
            $this->display('evaluation.setting.html');
        }
        else
        {
			$data['evaluation'] = array(
				'auto_user' => $_POST['auto_user'],
			);
            $model_setting->setAll($data);
            $this->json_result('','setting_successed');
        }
	}
	
    /**
     * 修改买家评价
     */
    function edit()
    {
        $rec_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$rec_id)
        {
            $this->show_warning('no_such_item');
            return;
        }
        $order_goods = $this->_order_goods_mod->get(array('conditions'=>$rec_id,'join' => 'belongs_to_order','fields'=>'this.*,buyer_name,seller_name'));
        if(empty($order_goods)){
            $this->show_warning('no_such_item');
            return;
        }
        if(!IS_POST){
			$order_goods['share_images'] && $order_goods['images'] = unserialize($order_goods['share_images']);
            $this->assign('order_goods', $order_goods);
            $this->display('evaluation.form.html');
        }else{
			
			$images = '';
			if($order_goods['share_images'])
			{
				if(!empty($_POST['images']))
				{
					$images = serialize($_POST['images']);
				}
			}
			
            $data['comment'] = $_POST['comment'];
            $data['evaluation'] = $_POST['evaluation'];
			$data['share_images'] = $images;
            $this->_order_goods_mod->edit($rec_id,$data);
            $this->json_result('','edit_ok');
        }
    }
	
	function auto()
    {
		$model_setting = &af('settings');
		$setting = $model_setting->getOne('evaluation');
		if(empty($setting['auto_user'])){
			$this->show_warning('please_setting');
			return;
		}
		!empty($setting['auto_user']) && $auto_user = explode('|',$setting['auto_user']);
        if (!IS_POST)
        {
			$store_mod = &m('store');
			$stores = $store_mod->find(array(
				'conditions' => 'state=1',
				'fields' => 'store_name',
			));
			$this->assign('stores', $stores);
			$this->assign('gcategories', $this->_get_gcategory_options(1));
            $this->display('evaluation.auto.html');
        }
        else
        {
			if(empty($_POST['cate'])){
				$this->json_error('no_select_cate');
                return;
			}
			if(empty($_POST['store']) && empty($_POST['goods'])){
				$this->json_error('no_select');
                return;
			}
			$goods_mod = &m('goods');
			if(!empty($_POST['goods'])){
				$goods = $goods_mod->find(array(
					'conditions' => intval($_POST['goods']),
					'join' => 'belongs_to_store',
					'fields' => 'this.*,store_name' 
				));
			}else{
				$conditions = 'if_show=1 AND closed=0';
				if(!empty($_POST['store'])){
					$conditions .= ' AND g.store_id='.intval($_POST['store']);
				}
				if(!empty($_POST['cate'])){
					$gcate_mod = &bm('gcategory');
					$gcates = $gcate_mod->get_descendant_ids(intval($_POST['cate']));
					$conditions .= ' AND cate_id '.db_create_in($gcates);
				}
				$goods = $goods_mod->find(array(
					'conditions' => $conditions,
					'join' => 'belongs_to_store',
					'fields' => 'this.*,store_name'  
				));
			}
			$eval_tpl = $this->_getTpl(intval($_POST['cate']));
			$order_mod = &m('order');
			$model_goodsstatistics =& m('goodsstatistics');
			$model_store =& m('store');
			foreach($goods as $key=>$val){
				$time = gmtime();
				$evaluation_time = $time-24 * 3600-mt_rand(10000,99999);
				$add_time = $evaluation_time-24 * 3600-mt_rand(10000,99999);
				$buyer_name = $auto_user[array_rand($auto_user)];
				$order = array(
					'buyer_name' => $buyer_name,
					'seller_name' => $val['store_name'],
					'add_time' => $add_time,
					'evaluation_time' => $evaluation_time,
					'evaluation_status' => 1, 
					'status' => ORDER_FINISHED,
				);
				$order_id = $order_mod->add($order);
				if(!$order_id){
					continue;
				}
				$comment = empty($_POST['comment']) ? $eval_tpl['eval_templates'][array_rand($eval_tpl['eval_templates'])] : $_POST['comment'];
				$tips = $_POST['tips'] ? implode(',',$_POST['tips']) : '';
				$quantity = $_POST['quantity'] ? intval($_POST['quantity']) : mt_rand(1,9);
				$comment_arr = array(
					'order_id' => $order_id,
					'goods_id' => $val['goods_id'],
					'goods_name' => html_script($val['goods_name']),
					'spec_id' => $val['default_spec'],
					'price' => $val['price'], 
					'quantity' => $quantity,
					'goods_image' => $val['default_image'], 
					'evaluation' => 3, 
					'comment' => $comment,
					'is_valid' => 1, 
					'tips' => $tips,
					'goods_evaluation' => 5,
					'service_evaluation' => 5,
					'shipped_evaluation' => 5,
					'status' => 'SUCCESS',
				);
				$rec_id = $this->_order_goods_mod->add($comment_arr);
				if(!$rec_id){
					continue;
				}
				$model_store->edit($val['store_id'], array(
					'credit_value'  =>  $model_store->recount_credit_value($val['store_id']),
					'praise_rate'   =>  $model_store->recount_praise_rate($val['store_id']),
					'avg_goods_evaluation'   =>   Psmb_init()->update_dynamic_evaluation('goods_evaluation',$val['store_id']),
					'avg_service_evaluation'   =>   Psmb_init()->update_dynamic_evaluation('service_evaluation',$val['store_id']),
					'avg_shipped_evaluation'   =>   Psmb_init()->update_dynamic_evaluation('shipped_evaluation',$val['store_id']),
				));
				$model_goodsstatistics->edit($val['goods_id'], 'comments=comments+1,sales=sales+'.$quantity);
			}
            $this->json_result('','evaluate_successed');
        }
    }
	
	function drop()
    {
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$id)
        {
            $this->json_error('no_such_item');
            return;
        }

        $ids = explode(',', $id);

        if (!$this->_order_goods_mod->drop($ids))
        {
            $this->json_error($this->_order_goods_mod->get_error());

            return;
        }
		
        $this->json_result('','drop_ok');
    }
	
	/* 取得分类列表 */
    function _get_gcategory_options($layer = 0)
    {
        $gcategory_mod =& bm('gcategory', array('_store_id' => 0));
        $gcategories = $gcategory_mod->get_list();

        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');

        return $tree->getOptions($layer);
    }
	
	function getGoods()
	{
		$store_id = isset($_GET['store_id']) ? intval($_GET['store_id']) : 0;
		if(!$store_id){
			$this->json_error('no_such_store');
			return;
		}
		$goods_mod =&m('goods');
		$goods = $goods_mod->find(array(
			'conditions' => 'if_show=1 AND closed=0 AND store_id='.$store_id,
			'fields' => 'goods_name',
		));
		if(empty($goods)){
			$this->json_error('goods_empty');
			return;
		}
		$this->json_result($goods);
	}
	
	function getTpl()
	{
		$cate_id = isset($_GET['cate_id']) ? intval($_GET['cate_id']) : 0;
		if(!$cate_id){
			$this->json_error('no_select_cate');
			return;
		}
		$gcategory_mod =& bm('gcategory', array('_store_id' => 0));
		$gcategorys = $gcategory_mod->get_ancestor($cate_id);
		$tpl = array();
		foreach($gcategorys as $key => $val){
			$cate = $gcategory_mod->get($val['cate_id']);
			$cate['eval_tips'] && $tpl['eval_tips'] .= $cate['eval_tips'].'|';
			$cate['eval_templates'] && $tpl['eval_templates'] .= $cate['eval_templates'].'|';
		}
		if(empty($tpl['eval_tips']) || empty($tpl['eval_tips'])){
			$this->json_error('eval_tpl_not_set');
			return;
		}
		$tpl['eval_tips'] = explode(',',substr($tpl['eval_tips'],0,-1));
		$tpl['eval_templates'] = explode(',',substr($tpl['eval_templates'],0,-1));
		$this->json_result($tpl);
	}
	
	function _getTpl($cate_id)
	{
		if(!$cate_id){
			$this->show_warning('no_select_cate');
			return;
		}
		$gcategory_mod =& bm('gcategory', array('_store_id' => 0));
		$gcategorys = $gcategory_mod->get_ancestor($cate_id);
		$tpl = array();
		foreach($gcategorys as $key => $val){
			$cate = $gcategory_mod->get($val['cate_id']);
			$cate['eval_tips'] && $tpl['eval_tips'] .= $cate['eval_tips'].'|';
			$cate['eval_templates'] && $tpl['eval_templates'] .= $cate['eval_templates'].'|';
		}
		if(empty($tpl['eval_tips']) || empty($tpl['eval_tips'])){
			$this->show_warning('eval_tpl_not_set');
			return;
		}
		$tpl['eval_tips'] = explode('|',substr($tpl['eval_tips'],0,-1));
		$tpl['eval_templates'] = explode('|',substr($tpl['eval_templates'],0,-1));
		return $tpl;
	}
}
?>