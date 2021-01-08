<?php
class ReportApp extends BackendApp
{
    var $_report_mod;

    function __construct()
    {
        $this->ReportApp();
    }
    function ReportApp()
    {
        parent::BackendApp();
        $this->_report_mod =& m('report');
    }

    /* 商品列表 */
    function index()
    {
        $this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js,inline_edit.js',
		));

        $this->display('report.index.html');
    }
	
	function get_xml()
	{
		$member_mod = &m('member');
		$goods_mod = &m('goods');
		$store_mod = &m('store');

		$conditions = '1 = 1';
		if ($_POST['query'] != '') 
		{
			if($_POST['qtype'] == 'user_name')
			{
				$users = $member_mod->find(array(
					'conditions'=>"user_name like '%" . html_script($_POST['query']) . "%'",
					'fields' => 'user_id',
				));
				$conditions .= " AND user_id ".db_create_in(array_keys($users));
			}
			if($_POST['qtype'] == 'goods_name')
			{
				$goods = $goods_mod->find(array(
					'conditions'=>"goods_name like '%" . html_script($_POST['query']) . "%'",
					'fields' => 'goods_id',
				));
				$conditions .= " AND goods_id ".db_create_in(array_keys($goods));
			}
			if($_POST['qtype'] == 'store_name')
			{
				$stores = $store_mod->find(array(
					'conditions'=>"store_name like '%" . html_script($_POST['query']) . "%'",
					'fields' => 'store_id',
				));
				$conditions .= " AND store_id ".db_create_in(array_keys($stores));
			}
		}
		$order = 'report_id DESC';
        $param = array('add_time','status');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
		
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
		$lists = $this->_report_mod->find(array(
            'conditions' => $conditions,
            'count' => true,
            'order' => $order,
            'limit' => $page['limit'],
        ));
		
        $page['item_count'] = $this->_report_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		
		foreach ($lists as $k => $v){
			$list = array();
			$operation = "<a class='btn red' onclick=\"fg_delete({$k},'report')\"><i class='fa fa-trash-o'></i>删除</a>";
			$operation .= "<span class='btn'><em><i class='fa fa-cog'></i>设置 <i class='arrow'></i></em><ul>";
			$operation .= "<li><a href='javascript:;' onclick='javascrip:fg_verify({$k});'>审核</a></li>";
			$operation .= "<li><a href='index.php?app=report&act=sendMsg&id={$v['user_id']}'>通知举报人</a></li>";
			$operation .= "<li><a href='index.php?app=report&act=sendMsg&id={$v['store_id']}'>通知被举报人</a></li>";
			$operation .= "<li><a href='index.php?app=goods&goods_id={$v['goods_id']}'>管理举报商品</a></li>";
			$operation .= "</ul>";
			$list['operation'] = $operation;
			$goods = $goods_mod->get(array('conditions'=>$v['goods_id'],'fields'=>'goods_name,default_image'));
			$store = $store_mod->get(array('conditions'=>$v['store_id'],'fields'=>'store_name'));
			$member = $member_mod->get(array('conditions'=>$v['user_id'],'fields'=>'user_name'));
			$files = unserialize($v['images']);
			$filestr = '';
			if(!empty($files)){
				foreach($files as $file){
					$filestr .= '<a href="'.SITE_URL.'/'.$file.'" target="_blank"><img src="'.SITE_URL.'/'.$file.'" width="30" height="30" /></a> ';
				}
			}
			$list['add_time'] = local_date('Y-m-d H:i:s',$v['time']);
			$list['user_name'] = $member['user_name'];
			$list['goods_name'] = '<a href="'.SITE_URL.'/index.php?app=goods&id='.$v['goods_id'].'" title="'.$goods['goods_name'].'">'.$goods['goods_name'].'</a>';
			$list['store_name'] = '<a href="'.SITE_URL.'/index.php?app=store&id='.$v['store_id'].'" title="'.$store['store_name'].'">'.$store['store_name'].'</a>';
			$list['content'] = '<span title="'.$v['content'].'">'.$v['content'].'</span>';
			$list['files'] = $filestr;
			$list['status'] = $v['status'] ? '<span class="yes"><i class="fa fa-check-circle"></i>是</span>' : '<span class="no"><i class="fa fa-ban"></i>否</span>';
			$data['list'][$k] = $list;
		}
		$this->flexigridXML($data);
	}

    function verify()
    {
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
		if (!$id)
		{
			$this->json_error('Hacking Attempt');
			return;
		}
		$ids = explode(',', $id);
		$verify = trim($_GET['verify']);
		if(empty($verify)) {
			$this->json_error('verify_empty');
			return;
		}
		$this->_report_mod->edit($ids,array('status'=>1,'verify'=>$verify,'admin'=>$this->visitor->get('user_name')));
        if ($this->_report_mod->has_error())    //出错了
        {
			$error = $this->_report_mod->get_error();
            $this->json_error($error['msg']);
            return;
        }
		$this->json_result('','verify_ok');
    }
	
	function sendMsg()
	{
		$id = isset($_GET['id']) ? trim($_GET['id']) : '';
		if (!$id)
		{
			$this->json_error('Hacking Attempt');
			return;
		}
		$member_mod = &m('member');
		$member = $member_mod->get(array('conditions' => $id,'fields'=>'user_name'));
		if (!$member)
		{
			$this->json_error('no_such_user');
			return;
		}
		if (!IS_POST)
		{
			$this->assign('member', $member);
			$this->display('report.notice.html');
		}
		else 
		{
			if(empty($_POST['content']))
			{
				$this->json_error('no_content');
				return;
			}
			 $ms =& ms();
			 $msg_id = $ms->pm->send(MSG_SYSTEM, $id, '', $_POST['content']);
			 if (!$msg_id)
			 {
				 $error = current($ms->pm->get_error());
				 $this->json_error($error['msg']);
				 return;
			 }
			 $this->json_result('','send_msg_ok');
		}
	}

    function drop()
    {
		$id = isset($_GET['id']) ? trim($_GET['id']) : '';
		if (!$id)
		{
			$this->json_error('Hacking Attempt');
			return;
		}
		$ids = explode(',', $id);
		$file_ids = $this->_report_mod->reportRelationImages($ids);//举报相关的图片
		$this->_report_mod->drop($ids);
        if ($this->_report_mod->has_error())    //出错了
        {
            $this->json_error($this->_report_mod->get_error());
            return;
        }
		
		if(!empty($file_ids))
		{
			$upload_mod = &m('uploadedfile');
			$upload_mod->drop('file_id '.db_create_in($file_ids));
		}

		$this->json_result('','drop_ok');
    }

}

?>
