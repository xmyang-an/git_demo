<?php

class Mobile_index_floor_2Widget extends BaseWidget
{
    var $_name = 'mobile_index_floor_2';

    function _get_data()
    {
		$images = array();
		for($i=1;$i<=2;$i++)
		{
			$images[] = array(
				'ad_image_url' => $this->options['ad'.$i.'_image_url'],
				'ad_link_url' => $this->options['ad'.$i.'_link_url']
			); 
		}
		
        $data = array(
			'model_name'     => $this->options['model_name'],
			'model_color'     => $this->options['model_color'],
            'images'  => $images
        );

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
        for ($i = 1; $i <= 2; $i++)
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