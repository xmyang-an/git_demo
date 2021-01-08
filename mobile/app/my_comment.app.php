<?php

/* 商品咨询管理控制器 */
class My_commentApp extends StoreadminbaseApp
{
    var $ordergoods_mod;
    function __construct()
    {
        $this->My_commentApp();
    }
    function My_commentApp()
    {
        parent::__construct();
        $this->ordergoods_mod = & m('ordergoods');
    }
    function index()
    {
		if(!IS_AJAX)
		{
			$this->import_resource(array(
				'script' => array(
					array(
						'path' => 'mobile/jquery.plugins/jquery.form.min.js',
						'attr' => '',
					),
					array(
						'path' => 'mobile/dialog/dialog.js',
						'attr' => 'id="dialog_js"',
					),
					array(
						'path' => 'mobile/jquery.ui/jquery.ui.js',
						'attr' => '',
					),
					array(
						'path' => 'mobile/jquery.plugins/jquery.infinite.js',
						'attr' => ''
					)
				),
			));
			$this->assign('infiniteParams', json_encode($_GET));
			
			$this->_config_seo('title', Lang::get('my_comment') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('my_comment');
			$this->display('my_comment.index.html');
		}
		else
		{
			$type = (isset($_GET['type']) && $_GET['type'] != '') ? trim($_GET['type']) : 'all_comment';
			$conditions = ' AND seller_id = '.$this->visitor->get('user_id');
			switch ($type)
			{
				case 'all_comment':
					$conditions .= ' ';
					break;
				case 'to_reply_comment' :
					$conditions .= ' AND reply_content = " " ';
					break;
				case 'replied_comment' :
					$conditions .= ' AND reply_content != " " ';
					break;
			};
			$page = $this->_get_page(intval($_GET['pageper']));
			$comments = $this->ordergoods_mod->find(array(
				'join' => 'belongs_to_order',
				'conditions' => '1=1 '.$conditions,
				'count' => true,
				'limit' => $page['limit'],
				'order' => 'evaluation_time desc',
				'fields' => 'rec_id,order.order_id, buyer_id, buyer_name, seller_id, seller_name, goods_id, goods_name,evaluation_time,comment,reply_content,reply_time',
			));
			
			if($comments)
			{
				$member_mod = &m('member');
				foreach($comments as $key => $val)
				{
					$comments[$key]['evaluation_time'] = local_date('Y-m-d H:i:s', $val['evaluation_time']);
					if($val['reply_time']) $comments[$key]['reply_time'] = local_date('Y-m-d H:i:s', $val['reply_time']);
					
					$buyer_info = $member_mod->get(array(
						'conditions' => $val['buyer_id'],
						'fields'     => 'portrait'
					));
					
					$comments[$key]['buyer_portrait'] = portrait($buyer_info['user_id'],$buyer_info['portrait'], 'middle');
					
					$seller_info = $member_mod->get(array(
						'conditions' => $val['seller_id'],
						'fields'     => 'portrait'
					));
					
					$comments[$key]['seller_portrait'] = portrait($seller_info['user_id'],$seller_info['portrait'], 'middle');	
				}
			}
			
			
			$page['item_count'] = $this->ordergoods_mod->getCount();
			$this->_format_page($page);
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($comments), 'totalPage' => $page['page_count']);
			echo json_encode($data);
		}
    }
    function reply()
    {
		$rec_id = (isset($_GET['rec_id']) && $_GET['rec_id'] !='') ? intval($_GET['rec_id']) : 0;
		
        if (!IS_POST)
        {
            $comment = $this->ordergoods_mod->get(array(
                'join' => 'belongs_to_order',
                'conditions' => 'rec_id = '.$rec_id,
            ));
            if ($comment['reply_comment'] != '')
            {
                $this->show_warning('already_replied');
                return;
            }

            $this->assign('comment',$comment);
			
			header('Content-Type:text/html;charset=' . CHARSET);
            $this->_config_seo('title', Lang::get('reply') . ' - ' . Lang::get('member_center'));
			$this->_get_curlocal_title('reply');
            $this->display('my_comment.form.html');
        }
        else
        {
            $content = (isset($_POST['content']) && $_POST['content'] != '') ? html_script(trim($_POST['content'])) : '';
            if (!$rec_id)
            {
                $this->json_error('Hacking Attempt');
                return;
            }
            if ($content == '')
            {
                $this->json_error('content_not_null');
                return;
            }
            if ($this->ordergoods_mod->edit($rec_id, array('reply_content'=>$content,'reply_time'=>gmtime())))
            {                    
               $this->json_result('', 'replied_comment');
			   return;
            }
            else
            {
                $this->json_error('reply_failed');
                return;
           }
        }
    }
}

?>