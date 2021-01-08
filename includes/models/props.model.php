<?php

/* 属性名 */
class PropsModel extends BaseModel
{
    var $table  = 'goods_prop';
    var $prikey = 'pid';
    var $_name  = 'gp';
	
	// 属性名和属性值按sort_order字段排序
	function prop_sort($data)
	{
		if(!$data || !is_array($data)){
			return $data;
		}
		
		foreach ($data as $key => $row) {
			$prop_sort_order[$key]  = $row['sort_order'];
			
			$value_sort_order = array();
			foreach($row['value'] as $k=>$v)
			{
				$value_sort_order[$k] = $v['sort_order'];
			}
			//  属性值按sort_order排序
			array_multisort($value_sort_order, SORT_ASC, $data[$key]['value']);
		}
		// 属性名按soort排序
		array_multisort($prop_sort_order, SORT_ASC, $data);
		
		return $data;
	}
}

?>