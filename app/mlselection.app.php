<?php

/* 多级选择：地区选择，分类选择 */
class MlselectionApp extends MallbaseApp
{
    function index()
    {
        in_array($_GET['type'], array('region', 'gcategory')) or $this->json_error('invalid type');
        $pid = empty($_GET['pid']) ? 0 : $_GET['pid'];
		$store_id = intval($_GET['store_id']) ? intval($_GET['store_id']) : 0;

        switch ($_GET['type'])
        {
            case 'region':
                $mod_region =& m('region');
                $regions = $mod_region->get_list($pid);
                foreach ($regions as $key => $region)
                {
                    $regions[$key]['mls_name'] = htmlspecialchars($region['region_name']);
					$regions[$key]['mls_id'] = htmlspecialchars($region['region_id']);
                }
                $this->json_result(array_values($regions));
                break;
            case 'gcategory':
                $mod_gcategory =& bm('gcategory', array('_store_id' => $store_id));
                $cates = $mod_gcategory->get_list($pid, true);
                foreach ($cates as $key => $cate)
                {
                    $cates[$key]['mls_name'] = htmlspecialchars($cate['cate_name']);
					$cates[$key]['mls_id'] = htmlspecialchars($cate['cate_id']);
                }
                $this->json_result(array_values($cates));
                break;
        }
    }
}

?>