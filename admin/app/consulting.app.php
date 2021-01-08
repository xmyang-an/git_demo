<?php
class ConsultingApp extends BackendApp
{
    var $goodsqa_mod;
    function __construct()
    {
        $this->ConsultingApp();
    }
    function ConsultingApp()
    {
        $this->goodsqa_mod = & m('goodsqa');
        parent::__construct();
    }
	
	function index()
    {
		$query = $this->get_query_conditions();
		$this->assign('filtered', $query);
        $this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js',
		));
        $this->display('goodsqa.index.html');
    }
	
	function get_xml()
	{
		$conditions = '1=1 ';
  		$conditions .= $this->get_query_conditions();
		$order = 'ques_id DESC';
        $param = array('user_name','type','store_name','time_post');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
		$list_data = $this->goodsqa_mod->find(array(
            'join' => 'belongs_to_user,belongs_to_store',
            'fields' => 'ques_id,question_content, reply_content,goods_qa.user_id,goods_qa.store_id,goods_qa.type,goods_qa.item_name,goods_qa.item_id,user_name,store_name,time_post,goods_qa.reply_content',
            'limit' => $page['limit'],
            'order' => $order,
            'count' => true,
            'conditions' => $conditions,
        ));
        $page['item_count'] = $this->goodsqa_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($list_data as $k => $v){
			$list = array();
			$operation = "<a class='btn red' onclick=\"fg_delete({$k},'consulting')\"><i class='fa fa-trash-o'></i>删除</a>";
			$list['operation'] = $operation;
			$list['user_name'] = empty($v['user_name']) ? Lang::get('guest') : $v['user_name'];
			$list['type'] = Lang::get($v['type']);
			$list['item_name'] = "<a target='_blank' href='".SITE_URL."/index.php?app={$v['type']}&amp;id={$v['item_id']}'>".$v['item_name']."</a>";
			$list['question_content'] = $v['question_content'];
			$list['reply_content'] = $v['reply_content'];
			$list['store_name'] = $v['store_name'];
			$list['time_post'] = local_date('Y-m-d H:i:s',$v['time_post']);
			$data['list'][$k] = $list;
		}
		$this->flexigridXML($data);
	}

	function get_query_conditions(){
		$conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'user_name',
                'equal' => 'like',
            ),
            array(
                'field' => 'question_content',
                'equal' => 'like',
            ),
            array(
                'field' => 'store_name',
                'equal' => 'like',
            ),
        ));
		return $conditions;
	}
		
    function drop()
    {
		$ques_id = empty($_GET['id']) ? 0 :trim($_GET['id']);
		$ids = explode(',',$ques_id);
		$conditions = "1 = 1 AND ques_id ".db_create_in($ids);
		if ((!$res = $this->goodsqa_mod->drop($conditions)))
		{
			$this->json_error('drop_failed');
			return;
		}
		else
		{
			$this->json_result('','drop_successful');
			return;
		}
    }
}
?>
