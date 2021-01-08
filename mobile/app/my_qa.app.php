<?php
/* 商品咨询管理控制器 */
class My_qaApp extends StoreadminbaseApp
{
    var $my_qa_mod;
    function __construct()
    {
        $this->My_qaApp();
    }
    function My_qaApp()
    {
        parent::__construct();
        $this->my_qa_mod = & m('goodsqa');
    }
    function index()
    {
		if(!IS_AJAX)
		{
			$this->import_resource('mobile/jquery.plugins/jquery.form.min.js, mobile/jquery.plugins/jquery.infinite.js');
			$this->assign('infiniteParams', json_encode($_GET));
		
			$this->_get_curlocal_title('my_qa');
        	$this->_config_seo('title', Lang::get('my_qa') . ' - ' . Lang::get('member_center'));
        	$this->display('my_qa.index.html');
		}
		else
		{
			$type = (isset($_GET['type']) && $_GET['type'] != '') ? trim($_GET['type']) : 'all_qa';
			$conditions = ' AND goods_qa.store_id = '.$this->visitor->get('user_id');
			switch ($type)
			{
				case 'all_qa':
					$conditions .= ' ';
					break;
				case 'to_reply_qa' :
					$conditions .= ' AND reply_content = " " ';
					break;
				case 'replied' :
					$conditions .= ' AND reply_content != " " ';
					break;
			};
			
			$page = $this->_get_page(intval($_GET['pageper']));
			$my_qa_data = $this->my_qa_mod->find(array(
				'fields' => 'ques_id,question_content,reply_content,goods_qa.user_id,goods_qa.email,time_post,time_reply,user_name,goods_qa.item_id,goods_qa.item_name,goods_qa.type,portrait,store_logo',
				'join' => 'belongs_to_store,belongs_to_user',
				'conditions' => '1=1 '.$conditions,
				'count' => true,
				'limit' => $page['limit'],
				'order' => 'time_post desc',
				'index_key'  => false
			));
			foreach($my_qa_data as $key => $val)
			{
				$my_qa_data[$key]['time_post'] = local_date('Y-m-d H:i:s', $val['time_post']);
				$my_qa_data[$key]['time_reply'] = local_date('Y-m-d H:i:s', $val['time_reply']);
				
				empty($val['portrait']) && $my_qa_data[$key]['portrait'] = Conf::get('default_user_portrait');
				empty($val['store_logo']) && $my_qa_data[$key]['store_logo'] = Conf::get('default_store_logo');
			}
			$page['item_count'] = $this->my_qa_mod->getCount();
			$this->_format_page($page);
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($my_qa_data), 'totalPage' => $page['page_count']);
			echo json_encode($data);	
		}
    }
    function reply()
    {
		$ques_id = (isset($_GET['ques_id']) && $_GET['ques_id'] !='') ? intval($_GET['ques_id']) : 0;
		
        if (!IS_POST)
        {
            $conditions = ' AND goods_qa.store_id = '. $this->visitor->get('user_id') . ' AND ques_id = '.$ques_id;
            $my_qa_data = $this->my_qa_mod->get(array(
                'fields' =>'question_content,reply_content,goods_qa.user_id,goods_qa.email,time_post,user_name,goods_qa.item_id,goods_qa.item_name,goods_qa.type',
                'join' =>'belongs_to_store,belongs_to_user',
                'conditions' => '1=1 '.$conditions,
            ));
            if ($my_qa_data['reply_content'] != '')
            {
                $this->show_warning('already_replied');
                return;
            }
			
            $this->assign('page_info',$page);
            $this->assign('my_qa_data',$my_qa_data);
			
			$this->import_resource('mobile/jquery.plugins/jquery.form.min.js');
					
            $this->_config_seo('title', Lang::get('reply') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('reply');
            $this->display('my_qa.form.html');
        }
        else
        {   
            $content = (isset($_POST['content']) && $_POST['content'] != '') ? html_script($_POST['content']) : '';
            if ($content == '')
            {
                $this->json_error('content_not_null');
                return;
            }

            $user_info = $this->my_qa_mod->get(array(
				//'join' => 'belongs_to_goods',
				'conditions' => '1 = 1 AND ques_id = '.$ques_id,
				'fields' => 'user_id,email,item_id,item_name,type'
			));
			extract($user_info);
			$data = array(
				'reply_content' => $content,
				'time_reply' => gmtime(),
				'if_new' => '1',
			);
			if (!$this->my_qa_mod->edit($ques_id,$data))
			{
				$this->json_error('handle_fail');
				return;
			}
				
			$url = '';
			switch ($type)
			{
				case 'goods' : $url = SITE_URL . "/index.php?app={$type}&act=qa&id={$item_id}&amp;ques_id={$ques_id}&amp;new=yes";
				break;
			}

			$mail = get_mail('tobuyer_question_replied', array(
				'item_name'  => $item_name,
				'type'       => Lang::get($type),
				'url'        => $url
			));
				
			$this->_mailto($email, addslashes($mail['subject']), addslashes($mail['message']));
				
			$this->json_result(array('ret_url' => url('app=my_qa')), 'handle_ok');
        }
    }
    
    function edit_reply()
    {
		$ques_id = (isset($_GET['ques_id']) && $_GET['ques_id'] !='') ? intval($_GET['ques_id']) : 0;
		
        if (!IS_POST)
        {
			if (!$ques_id)
        	{
            	$this->show_warning('no_data');
				return;
        	}
            
            $conditions = ' AND goods_qa.store_id = '. $this->visitor->get('user_id') . ' AND ques_id = '.$ques_id;
            $my_qa_data = $this->my_qa_mod->get(array(
                'fields' =>'question_content,reply_content,goods_qa.user_id,goods_qa.email,time_post,user_name,goods_qa.item_id,goods_qa.item_name,goods_qa.type',
                'join' =>'belongs_to_store,belongs_to_user',
                'conditions' => '1=1 '.$conditions,
            ));
            $this->assign('my_qa_data',$my_qa_data);
			
			$this->import_resource('mobile/jquery.plugins/jquery.form.min.js');
			
			$this->_config_seo('title', Lang::get('edit_reply') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('edit_reply');
            $this->display('my_qa.form.html');
        }
        else
        {
			if (!$ques_id)
        	{
            	$this->json_error('no_data');
				return;
        	}
			
            $content = (isset($_POST['content']) && $_POST['content'] != '') ? html_script($_POST['content']) : '';
            if (empty($content))
            {
                $this->json_error('content_not_null');
                return;
            }

            $user_info = $this->my_qa_mod->get(array(
				'conditions' => '1 = 1 AND ques_id = '.$ques_id,
				'fields' => 'user_id,email,item_id,item_name,type'
			));
            extract($user_info);
            $data = array(
                'reply_content' => $content,
				'time_reply' => gmtime(),
				'if_new' => '1',
			);
			if (!$this->my_qa_mod->edit($ques_id,$data))
			{
				$this->json_error('edit_fail');
				return;
			}
			
			$mail = get_mail('tobuyer_question_replied', array('id' => $goods_id, 'ques_id' => $ques_id, 'goods_name' => $goods_name));
			$this->_mailto($email, addslashes($mail['subject']), addslashes($mail['message']));
					
			$this->json_result(array('ret_url' => url('app=my_qa')), 'edit_ok');
        }
    }
    //删除咨询
    function drop()
    {
        $id = (isset($_GET['id']) && $_GET['id'] != '') ? trim($_GET['id']) : '';
        if (!$id)
        {
            $this->json_error('drop_fail');
            return;
        }
        $ids = explode(',', $id);
        if (!$this->my_qa_mod->drop($ids))
        {
            $this->json_error('drop_fail');
            return;
        }
        $this->json_result('', 'drop_ok');
    }
    
}

?>