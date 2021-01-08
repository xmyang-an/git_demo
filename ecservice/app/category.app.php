<?php

class CategoryApp extends MemberbaseApp
{
    function index()
    {
		list($check, $loginResult) = TRUE;//list($check, $loginResult) = parent::_checkLogin();
		if($check === FALSE)
		{
			$result = array(
				'isSuccess' => FALSE,
				'returnMsg' => $loginResult['returnMsg']
			);
		}
		else
		{
			//$post = parent::_getPostData();
			//$post = $post['data'];
		
			$gcategory_mod =& bm('gcategory', array('_store_id' => 0));
            $gcategories = $gcategory_mod->get_list(-1,true);
    
            import('tree.lib');
            $tree = new Tree();
            $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');
			
			$result = array(
				'status' => 'SUCCESS',
				'title' => '商品分类信息',
				'retval' 	=> $tree->getArrayList(0)
			);
		}
		
		//print_r($result);
		echo json_encode($result);
	}
	
	function store()
    {
		list($check, $loginResult) = TRUE;//list($check, $loginResult) = parent::_checkLogin();
		if($check === FALSE)
		{
			$result = array(
				'isSuccess' => FALSE,
				'returnMsg' => $loginResult['returnMsg']
			);
		}
		else
		{
			//$post = parent::_getPostData();
			//$post = $post['data'];
		
			$scategory_mod =& m('scategory');
			$scategories = $scategory_mod->get_list(-1,true);
	
			import('tree.lib');
			$tree = new Tree();
			$tree->setTree($scategories, 'cate_id', 'parent_id', 'cate_name');
			
			$result = array(
				'status' => 'SUCCESS',
				'title' => '商品分类信息',
				'retval' 	=> $tree->getArrayList(0)
			);
		}
		
		//print_r($result);
		echo json_encode($result);
	}
}

?>
