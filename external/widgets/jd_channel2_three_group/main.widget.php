<?php

/**
 * 图片切换挂件
 *
 * @return  array
 */
class Jd_channel2_three_groupWidget extends BaseWidget
{
    var $_name = 'jd_channel2_three_group';
    var $_ttl  = 86400;

    function _get_data()
    {
        $cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);		
        if($data === false)
        {
			$tabList = array();
			for($i=1;$i<=3;$i++)
			{
				$tab = array();
				if(!empty($this->options['cate_name_'.$i])) {
					
					$tab['cate_name'] = $this->options['cate_name_'.$i];
					
					for($j=1; $j<=5;$j++) {
						$tab['images'][] = array('url' => $this->options['ad'.$i.'_image_url_'.$j], 'link' => $this->options['ad'.$i.'_link_url_'.$j]);
					}
				}
				
				$tabList[] = $tab;
			}
			
			$data = array(
				'model_id'          => mt_rand(),
				'model_name'	 	=> $this->options['model_name'],
				'tabList'	        => $tabList,
			);
			$cache_server->set($key, $data, $this->_ttl);
        }
		
        return $data;
    }

    function parse_config($input)
    {
		$result = array();
        
		$images = $this->_upload_image();
		
        if ($images)
        {
            foreach ($images as $key => $image)
            {
				foreach($image as $k => $v) {
                	$input['ad' . $key . '_image_url_'.$k] = $v;
				}
            }
        }
		
        return $input;
    }

    function _upload_image()
    {
        import('uploader.lib');

        $images = array();
        for ($i = 1; $i <= 3; $i++)
        {
			for($j = 1; $j <= 5; $j++)
			{
				$file = $_FILES['ad'.$i.'_image_file_'.$j];

				if ($file['error'] == UPLOAD_ERR_OK)
				{
					$uploader = new Uploader();
					$uploader->allowed_type(IMAGE_FILE_TYPE);
					$uploader->addFile($file);
					$uploader->root_dir(ROOT_PATH);
					$images[$i][$j] = $uploader->save('data/files/mall/template', $uploader->random_filename());
				}
			}
		}

        return $images;
    }   
}
?>