<?php

/**
 * 商品分类挂件
 *
 * @return  array   $category_list
 */
class Jd_channel2_gcategory_listWidget extends BaseWidget
{
    var $_name = 'jd_channel2_gcategory_list';
    var $_ttl  = 86400;

   function _get_data()
    {
        $cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data === false)
        {
            $gcategories = $this->get_all_cate_tree($this->options['cate_id']);

			$data = array(
				'model_id'		=> mt_rand(),
				'model_name' 	=> $this->options['model_name'],
				'gcategories' 	=> $gcategories,
				'keywords'    	=> trim($this->options['keyword']) ? explode(' ', trim($this->options['keyword'])) : '',
				'layer'		  	=> intval($this->options['layer']),
				'amount'	  	=> intval($this->options['amount']),
				'model_height'	=> floatval($this->options['model_height']),
			);
			
            $cache_server->set($key, $data, $this->_ttl);
        }
		return $data;
    }

	function get_config_datasrc()
    {
        // 取得一级商品分类
        $this->assign('gcategories', $this->_get_gcategory_options(1));
    }
	
	function get_all_cate_tree($cate_id)
	{ 
		$gcategory_mod =& bm('gcategory',array('store_id' => 0));
		$data = $gcategory_mod->get_children($cate_id);
		
		if(!empty($data))
		{
			foreach($data as $key => $val)
			{
				$data[$key]['children'] = $this->get_all_cate_tree($val['cate_id']);
			}
		}

		return $data;
	} 
}

?>