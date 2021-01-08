<?php

/**
 * 图片挂件
 *
 * @return  array   $image_list
 */
class Jd_channel2_brandWidget extends BaseWidget
{
    var $_name = 'jd_channel2_brand';
	var $_ttl  = 1800;

    function _get_data()
    {
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        $ads = array();
        if($data === false)
        {
			for($i=1;$i<7;$i++)
			{
				$ads[$i]['ad_image_url'] = $this->options['ad'.$i.'_image_url'];
				$ads[$i]['ad_link_url'] = $this->options['ad'.$i.'_link_url'];
				!empty($this->options['ad'.$i.'_title_url']) && $title[$i] = explode(',',$this->options['ad'.$i.'_title_url']);
				$ads[$i]['ad_m_title_url'] = $title[$i][0];
				$ads[$i]['ad_s_title_url'] = $title[$i][1];
			}
			
			$data = array(
				'model_id' 		 => mt_rand(),
				'ads'  			 => $ads,
				'model_name'	 => $this->options['model_name'],
				'ad_m_title_url' => $this->options['ad_m_title_url'],
				'ad_s_title_url' => $this->options['ad_s_title_url'],
			);
			
        	$cache_server->set($key, $data);
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
        for ($i = 1; $i <= 7; $i++)
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
}
?>