<?php

/*分类控制器*/
class CategoryApp extends ApibaseApp
{

    function goods()
    {
		$store_id = isset($this->PostData['id']) ? intval($this->PostData['id']) : 0;
		
        $cache_server =& cache_server();
        $key = 'page_goods_category_api'.$store_id;
        $data = $cache_server->get($key);
        if ($data === false)
        {
            $gcategory_mod =& bm('gcategory', array('_store_id' => $store_id));
            $gcategories = $gcategory_mod->get_list(-1,true);
			
			foreach($gcategories as $key => $val){
				if(empty($val['category_image']))
				{
					$gcategories[$key]['category_image'] = SITE_URL.'/'.Conf::get('default_goods_image');
				}
				else{
					$gcategories[$key]['category_image'] = SITE_URL.'/'.$val['category_image'];
				}
			}
    
            import('tree.lib');
            $tree = new Tree();
            $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');
            
			$data = $tree->getArrayListAll(0,NULL,array('category_image'));

            $cache_server->set($key, $data, 3600);
        }

        $this->json_success($data);
    }

        /* 店铺分类 */
    function store()
    {
		$cache_server =& cache_server();
        $key = 'page_goods_category_api';
        $data = $cache_server->get($key);
        if ($data === false)
        {
			$scategory_mod =& m('scategory');
			$scategories = $scategory_mod->get_list(-1,true);
	
			import('tree.lib');
			$tree = new Tree();
			$tree->setTree($scategories, 'cate_id', 'parent_id', 'cate_name');
			$data = $tree->getArrayList(0);
			
			
			$cache_server->set($key, $data, 3600);
        }

        $this->json_success($data);
    }
}

?>