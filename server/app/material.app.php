<?php

class MaterialApp extends ApibaseApp
{
	function listing()
	{
		$post = parent::_getPostData();
		
		$page = $this->_get_page((isset($post['perpage']) && $post['perpage'] > 0) ? $post['perpage'] : 10);
		
		$sort = 'id';
		$order = ' desc';
		if(isset($post['order'])){
			$order_fields = explode('|',$post['order']);

			if(in_array($order_fields[0],array('sort_order'))){
				$sort = $order_fields[0];
			}
			
			if(in_array($order_fields[1],array('asc','desc'))){
				$order = $order_fields[1];
			}
		}
		
		$conditions = '';
		if(isset($post['type'])){
			$conditions .= ' AND type="'.$post['type'].'"';
		}
		
		if(isset($post['device'])){
			$conditions .= ' AND device="'.$post['device'].'"';
		}
		
		if(isset($post['store_id'])){
			$conditions .= ' AND store_id='.$post['store_id'];
		}
		
		$material_mod = &m('material');
		$materialList = $material_mod->find(array(
			'conditions' => "if_show=1".$conditions,
			'limit'   => $page['limit'],
			'order'   => $sort.' '.$order,
			'count'   => true 
		));
		
		if(!empty($materialList)){
			foreach($materialList as $key=>$val)
			{
				if(stripos($val['url'], '//:') == FALSE) {
					$val['url'] = SITE_URL . '/' . $val['url'];
				}
				
				$materialList[$key]['url'] = $val['url'];
				
			}
		}
		
		$this->json_success(array_values($materialList));
	}
}

?>
