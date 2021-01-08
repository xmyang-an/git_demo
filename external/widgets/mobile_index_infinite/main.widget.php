<?php

class Mobile_index_infiniteWidget extends BaseWidget
{
    var $_name = 'mobile_index_infinite';
    var $_num  = 5;

    function _get_data()
    {
       $data = array(
	   		'model_id'		=> mt_rand(),
			'model_name'    => $this->options['model_name'],
			'model_color'  	=> $this->options['model_color'],
			'recom_id' 		=> $this->options['recom_id'],
			'cate_id' 		=> $this->options['cate_id'],
			'sort_by' 		=> $this->options['sort_by'],
			'maxshow' 		=> intval($this->options['maxshow']) ? intval($this->options['maxshow']) : 100
        );

		return $data;
    }
	
	function parse_config($input)
    {
        return $input;
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