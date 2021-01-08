<?php

/**
 * 商品挂件
 *
 */
class Jd_channel2_qianggouWidget extends BaseWidget
{
    var $_name = 'jd_channel2_qianggou';
    var $_ttl  = 1800;
    var $_num  = 5;

    function _get_data()
    {
        $cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data === false)
        {
            $recom_mod =& m('recommend');
            $goods_list = $recom_mod->get_recommended_goods($this->options['img_recom_id'], $this->_num, true, $this->options['img_cate_id']);
			$data = array(
				'model_id'			=> mt_rand(),
				'model_name'	 	=> $this->options['model_name'],
				'floor_title'	 	=> $this->options['floor_title'],
				'sub_title'	 	    => $this->options['sub_title'],
				'goods_list'	 	=> $goods_list,
			);
            $cache_server->set($key, $data, $this->_ttl);
        }

        return $data;
    }

    function get_config_datasrc()
    {
        // 取得推荐类型
        $this->assign('recommends', $this->_get_recommends());

        // 取得一级商品分类
        $this->assign('gcategories', $this->_get_gcategory_options(1));
    }

    function parse_config($input)
    {
        if ($input['img_recom_id'] >= 0)
        {
            $input['img_cate_id'] = 0;
        }

        return $input;
    }
}

?>