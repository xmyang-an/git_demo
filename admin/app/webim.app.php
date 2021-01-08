<?php

class WebimApp extends BackendApp
{
	var $_member_mod;
	var $_webim_log;

	function __construct()
    {
        $this->WebimApp();
    }
    function WebimApp()
    {   
        parent::__construct();
		$this->_member_mod = &m('member');
		$this->_webim_log  = &m('webim_log');
    }
	
	function index()
	{
		$conditions = '';
		
		$fromName = trim($_GET['fromName']);
		$toName   = trim($_GET['toName']);
		$formatContent = trim($_GET['formatContent']);
		
		if($fromName)
		{
			$conditions .= ' AND fromName LIKE "%'.$fromName.'%"';
		}
		if($toName)
		{
			$conditions .= ' AND toName LIKE "%'.$toName.'%"';
		}
		if($formatContent)
		{
			$conditions .= ' AND formatContent LIKE "%'.$formatContent.'%"';
		}
		
		$page = $this->_get_page(8);
		$imlog = $this->_webim_log->find(array(
			'conditions' => "1=1" . $conditions,
			'limit'   => $page['limit'],
			'order'   => 'add_time desc',
			'fields'  => 'add_time, formatContent, fromid, fromName, toid, toName',
			'count'   => true
		));
		
		// 赋值用户是否被禁言
		$checkTalk = array();
		foreach($imlog as $key => $log)
		{
			if(!isset($checkTalk[$log['fromid']])) {
				$user = $this->_member_mod->get(array('conditions' => 'user_id='.$log['fromid'], 'fildds' => 'imforbid'));
				$checkTalk[$log['fromid']] = $user['imforbid'];
			}
			$imlog[$key]['imforbid'] = $checkTalk[$log['fromid']];
		}
		
		$page['item_count'] = $this->_webim_log->getCount();
		$this->_format_page($page);
        $this->assign('page_info', $page);
		$this->assign('imlog', $imlog);
		$this->assign('filtered', $conditions ? 1 : 0);
		$this->display('webim.index.html');
	}
	
	function checkTalk()
	{
		$id = intval($_GET['id']);
		$imforbid = intval($_GET['imforbid']);
						
		if(!$this->_member_mod->edit($id, array('imforbid' => $imforbid))) {
			$this->json_error('操作失败!');
			return;
		}
		
		$this->json_result('', $imforbid ? '用户已被禁言' : '用户可正常发言了');
	}
	function delTalk()
	{
		$logid = intval($_GET['logid']);
						
		if(!$this->_webim_log->drop($logid)) {
			$this->json_error('删除失败!');
			return;
		}
		
		$this->json_result('', '已删除');
	}
}

?>
