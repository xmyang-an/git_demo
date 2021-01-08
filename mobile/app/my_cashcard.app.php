<?php

class My_cashcardApp extends MemberbaseApp 
{
    var $_user_mod;
	var $_cashcard_mod;
    
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
						'path' => 'mobile/jquery.plugins/weui/js/jquery-weui.min.js',
						'attr' => '',
					),
					array(
						'path' => 'mobile/jquery.plugins/jquery.infinite.js',
						'attr' => ''
					)
				),
				'style' =>  'mobile/jquery.plugins/weui/lib/weui.min.css,mobile/jquery.plugins/weui/css/jquery-weui.min.css',
			));
			$this->assign('infiniteParams', json_encode($_GET));

       
	   		$this->_config_seo('title',  Lang::get('my_cashcard') . ' - ' . Lang::get('member_center'));
	   		$this->_get_curlocal_title('my_cashcard');
       		$this->display('my_cashcard.index.html');
		}
		else
		{
			
			$this->_user_mod =& m('member');
			$this->_cashcard_mod = &m('cashcard');
			
			$page = $this->_get_page(intval($_GET['pageper']));
			$cashcards = $this->_cashcard_mod->find(array(
				'conditions' => 'useId = ' . $this->visitor->get('user_id'),
				'count' => true,
				'limit' => $page['limit'],
				'order' => 'id DESC'
			));
			$page['item_count'] = $this->_cashcard_mod->getCount();
			$this->_format_page($page);
			
			foreach($cashcards as $key => $val)
			{
				$cashcards[$key]['active_time'] = local_date('Y-m-d H:i:s');
				
				$cashcards[$key]['valid'] = 1;
				if($val['expire_time'] > 0) {
					$cashcards[$key]['expire_time'] = local_date('Y-m-d H:i:s', $val['expire_time']);
				
					if(gmtime() > $val['expire_time']) {
						$cashcards[$key]['valid'] = 0;
					}
				} else $cashcards[$key]['expire_time'] = '';
			}
					   
		   	// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($cashcards), 'totalPage' => $page['page_count']);
			echo json_encode($data);
	   }
    }
}

?>