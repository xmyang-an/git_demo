<?php
/* 买家咨询管理控制器 */
class My_questionApp extends MemberbaseApp
{
    var $my_qa_mod;
    function __construct()
    {
        $this->My_questionApp();
    }
    function My_questionApp()
    {
        parent::__construct();
        $this->my_qa_mod = & m('goodsqa');
    }
    function index()
    {
		if(!IS_AJAX)
		{
			$this->import_resource('mobile/jquery.plugins/jquery.infinite.js');
			$this->assign('infiniteParams', json_encode($_GET));
			
			$this->_config_seo('title', Lang::get('my_question') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('my_question'); 	
        	$this->display('my_question.index.html');
		}
		else
		{
			$page = $this->_get_page(intval($_GET['pageper']));
			
			$type = (isset($_GET['type']) && $_GET['type'] != '') ? trim($_GET['type']) : 'all_qa';
			$conditions = '1=1 AND goods_qa.user_id = '.$this->visitor->get('user_id');
			if ($type == 'reply_qa')
			{
				$conditions .= ' AND reply_content !="" ';
			}
			
			$my_qa_data = $this->my_qa_mod->find(array(
				'fields' => 'ques_id,question_content,reply_content,time_post,time_reply,goods_qa.item_name,goods_qa.item_id,goods_qa.type,portrait,store_logo,store_name',
				'join' => 'belongs_to_store,belongs_to_user',
				'count' => true,
				'conditions' => $conditions,
				'limit' => $page['limit'],
				'order' => 'if_new desc,time_post desc',
				'index_key' => false
			));
	
			foreach($my_qa_data as $key => $val)
			{
				$my_qa_data[$key]['time_post'] = local_date('Y-m-d H:i:s', $val['time_post']);
				$my_qa_data[$key]['time_reply'] = local_date('Y-m-d H:i:s', $val['time_reply']);
				
				empty($val['store_logo']) && $my_qa_data[$key]['store_logo'] = Conf::get('default_store_logo');
				empty($val['portrait']) && $my_qa_data[$key]['portrait'] = portrait($this->visitor->get('user_id'), $val['portrait']);
			}
			
			$page['item_count'] = $this->my_qa_mod->getCount();   //获取统计的数据
			$this->_format_page($page);
			
			if ($type == 'reply_qa') {
				$this->my_qa_mod->edit($my_qa_data['ques_id'], array('if_new' => '0'));
			}
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($my_qa_data), 'totalPage' => $page['page_count']);
			echo json_encode($data);
		}
    }
}

?>