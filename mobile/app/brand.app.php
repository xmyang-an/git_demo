<?php

class BrandApp extends MallbaseApp
{
    function index()
    {
        $recommended_brands = $this->_recommended_brands(10);
        $this->assign('recommended_brands', $recommended_brands);
        //对品牌重新组合排序
        $brand_mod =& m('brand');
        $brands_tmp = $brand_mod->find(array(
            'order' => "tag DESC,sort_order asc"));
        $brands_tmp = array_values($brands_tmp);
        $brands = array();
        $i = 0;
        foreach ($brands_tmp as $key => $val)
        {
            if (empty($key))
            {
               $brands[$i]['tag'] = $val['tag'];
               $brands[$i]['count'] = 1;
               $brands[$i]['brands'][] = $val;
               $i++;
            }
            else
            {
                if ($val['tag'] == $brands[$i-1]['tag'])
                {
                    $brands[$i-1]['count'] = $brands[$i-1]['count'] + 1;
                    $brands[$i-1]['brands'][] = $val;
                }
                else
                {
                    $brands[$i]['tag'] = $val['tag'];
                    $brands[$i]['count'] = 1;
                    $brands[$i]['brands'][] = $val;
                    $i++;
                }
            }
        }
        $brands_sort = array();
        foreach ($brands as $key => $val)
        {
            $brands_sort[$key] = $val['count'];
        }
        arsort($brands_sort);
        foreach ($brands_sort as $key => $val)
        {
            $brands_sort[$key] = $brands[$key];
        }
        $this->assign('brands', $brands_sort);
		$this->_get_curlocal_title('all_brands');
        $this->_config_seo('title', Lang::get('all_brands'));
        $this->display('brand.index.html');
    }

    function _recommended_brands($num)
    {
        $brand_mod =& m('brand');
        $brands = $brand_mod->find(array(
            'conditions' => 'recommended = 1 AND if_show = 1',
            'order' => 'sort_order',
            'limit' => '0,' . $num));
        return $brands;
    }
}

?>
