<?php


class Mobile_index_articleWidget extends BaseWidget
{
    var $_name = 'mobile_index_article';
    var $_ttl  = 86400;
    var $_num;

    function _get_data()
    {
        $cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data === false)
        {
            $acategory_mod 	= &m('acategory');
            $article_mod 	= &m('article');
			
			$this->_num = $this->options['amount'] ? intval($this->options['amount']) : 5;
			
			$conditions = Psmb_init()->Jd_article_get_data($this->options);
            $data = $article_mod->find(array(
                'conditions'    => 'if_show = 1 AND store_id = 0 '.$conditions,
                'order'         => 'sort_order ASC, add_time DESC',
                'fields'        => 'article_id, title, add_time',
                'limit'         => $this->_num,
            ));
            $cache_server->set($key, $data, $this->_ttl);
        }
		
		return array('article' => array_chunk($data,2), 'model_id' => mt_rand(), 'model_name' => $this->options['model_name'],'ad_image_url'  => $this->options['ad_image_url']);
    }

    function parse_config($input)
    {
        $image = $this->_upload_image();
        if ($image)
        {
            $input['ad_image_url'] = $image;
        }

        return $input;
    }

    function _upload_image()
    {
        import('uploader.lib');
        $file = $_FILES['ad_image_file'];
        if ($file['error'] == UPLOAD_ERR_OK)
        {
            $uploader = new Uploader();
            $uploader->allowed_type(IMAGE_FILE_TYPE);
            $uploader->addFile($file);
            $uploader->root_dir(ROOT_PATH);
            return $uploader->save('data/files/mall/template', $uploader->random_filename());
        }

        return '';
    }
	function get_config_datasrc()
    {
		// 取得多级文章分类
        $this->assign('acategories', $this->_get_acategory_options(2));
    }
	function _get_acategory_options($layer = 0)
	{
		$acategory_mod =& m('acategory');
        $acategories = $acategory_mod->get_list();
		foreach($acategories as $key=>$val)
		{
			if($val['code'] == ACC_SYSTEM){
				unset($acategories[$key]);
			}
		}

        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($acategories, 'cate_id', 'parent_id', 'cate_name');

        return $tree->getOptions($layer);
	}
}
?>