<?php

/**
 * 楼层挂件
 *
 * @return  array   $goods_list
 */
class Jd_channel2_floor1Widget extends BaseWidget
{
    var $_name = 'jd_channel2_floor1';
	var $_ttl  = 1800;

    function _get_data()
    {
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data === false)
        {
			$recom_mod =& m('recommend');
			$gcategory_mod = & m('gcategory');

			if(!empty($this->options['cate_ids']))
			{
				$temp_cate = explode(',',$this->options['cate_ids']);
				foreach($temp_cate as $key=>$val)
				{
					$temp_cate[$key] = intval($val);
				}
				
				$cates = $gcategory_mod->find(array(
				     'conditions' 	=> 'cate_id IN ('. implode(',',$temp_cate).')',
					 'fields'  		=> 'cate_name'
				));
			}
			
            $images = array();
			for($i=1;$i<5;$i++)
			{
				$images['ad'.$i.'_image_url'] = $this->options['ad'.$i.'_image_url'];
				$images['ad'.$i.'_link_url']  = $this->options['ad'.$i.'_link_url'];
			}
			
			$goods_list= $recom_mod->get_recommended_goods($this->options['img_recom_id_1'], 5, true, $this->options['img_cate_id_1']);
			$rank = $recom_mod->get_recommended_goods($this->options['img_recom_id_2'], 5, true, $this->options['img_cate_id']);
			$data = array(
				'model_id'			=> mt_rand(),
				'model_name'	 	=> $this->options['model_name'],
				'goods_list'	 	=> $goods_list,
				'rank'              => $rank,
				'cates'             => $cates,
				'images'            => $images,
				'keywords'  	 	=> explode('|',$this->options['keyword']),
			);
        	$cache_server->set($key, $data,$this->_ttl);
        }
	
        return $data;
    }

    function parse_config($input)
    {
        if ($input['img_recom_id'] >= 0)
        {
            $input['img_cate_id'] = 0;
        }
		
		$images = $this->_upload_image();
        if ($images)
        {
            foreach ($images as $key => $image)
            {
                $input['ad' . $key . '_image_url'] = $image;
            }
        }
        return $input;
    }
	
	function _upload_image()
    {
        import('uploader.lib');
        $images = array();
        for ($i=1;$i<5;$i++)
        {
            $file = $_FILES['ad' . $i . '_image_file'];
            if ($file['error'] == UPLOAD_ERR_OK)
            {
                $uploader = new Uploader();
                $uploader->allowed_type(IMAGE_FILE_TYPE);
                $uploader->addFile($file);
                $uploader->root_dir(ROOT_PATH);
                $images[$i] = $uploader->save('data/files/mall/template', $uploader->random_filename());
            }
        }

        return $images;
    }
	function get_config_datasrc()
    {
         // 取得推荐类型
        $this->assign('recommends', $this->_get_recommends());

        // 取得一级商品分类
        $this->assign('gcategories', $this->_get_gcategory_options(2));
    }
}
      

?>