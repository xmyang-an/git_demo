<?php

/**
 * 商品楼层挂件
 *
 * @return  array   $image_list
 */
class Jd_channel2_floor2Widget extends BaseWidget
{
    var $_name = 'jd_channel2_floor2';
	var $_ttl  = 1800;

    function _get_data()
    {
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data === false)
        {
			$recom_mod =& m('recommend');
			$gcategory_mod = & bm('gcategory', array('_store_id' => 0));

			// 左侧分类
			if(!empty($this->options['cate_ids']))
			{
				$temp_cate = explode(',',$this->options['cate_ids']);
				foreach($temp_cate as $key=>$val) {
					// 过滤
					$temp_cate[$key] = intval($val);
				}
				
				$cates = $gcategory_mod->find(array(
				     'conditions' 	=> 'cate_id IN ('. implode(',',$temp_cate).')',
					 'fields'  		=> 'cate_name'
				));
			}
			
			// 中部切换标题
			$cate_name = array();
			for($i=1;$i<=4;$i++){
				$cate_name[] = $this->options['cate_name_'.$i];
			}
			
			// 中部幻灯片
			$slide_images = array();
			for($i=2; $i<=4; $i++) {
				$slide_images[] = array('url' => $this->options['ad'.$i.'_image_url'], 'link' => $this->options['ad'.$i.'_link_url']);
			}
			
			// 中部其他5张图片
			$floor_images = array();
			for($i=5;$i<=9;$i++) {
				$floor_images[] = array('url' => $this->options['ad'.$i.'_image_url'], 'link' => $this->options['ad'.$i.'_link_url']);
			}
			
			// 中部其他3个切换（读商品）
			$floor_goods = array();
			for($i=2;$i<=4;$i++){
				$floor_goods_item = array();
				if($this->options['cate_name_'.$i]) {
					$floor_goods_item['cate_name'] = $this->options['cate_name_'.$i];
					$floor_goods_item['goods_list'] = $recom_mod->get_recommended_goods($this->options['img_recom_id_'.$i], 8, true, $this->options['img_cate_id_'.$i]);
				}
				
				$floor_goods[] = $floor_goods_item;
			}
			
			// 右侧2个切换（读商品）
			$tab_goods = array();
			for($i=5;$i<=6;$i++){
				$tab_goods_item = array();
				if($this->options['cate_name_'.$i]) {
					$tab_goods_item['cate_name'] = $this->options['cate_name_'.$i];
					$tab_goods_item['goods_list'] = $recom_mod->get_recommended_goods($this->options['img_recom_id_'.$i], 5, true, $this->options['img_cate_id_'.$i]);
				}
				
				$tab_goods[] = $tab_goods_item;
			}
			
			$data = array(
				'model_id'			=> mt_rand(),
				'model_name'	 	=> $this->options['model_name'],
				'keywords'  	 	=> explode('|',$this->options['keyword']),
				
				'cates'             => $cates,
				'ad1_image_url'  	=> $this->options['ad1_image_url'],
				'ad1_link_url'   	=> $this->options['ad1_link_url'],
				
				'cate_name'			=> $cate_name,
				'slide_images'      => $slide_images,
                'floor_images'      => $floor_images,
				'floor_goods'		=> $floor_goods,
				
				'tab_goods'			=> $tab_goods,
			);
        	$cache_server->set($key, $data,$this->_ttl);
        }
        return $data;
    }

    function parse_config($input)
    {
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
        for ($i = 1; $i <=9; $i++)
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
