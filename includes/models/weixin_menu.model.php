<?php

class Weixin_menuModel extends BaseModel {

    var $table = 'weixin_menu';
    var $prikey = 'id';
    var $_name = 'weixin_menu';
    /* 与其它模型之间的关系 */
    var $_relation = array(
    );
	
	/*
     * 判断名称是否唯一
     */
    function unique($name, $parent_id, $id = 0, $user_id = 0)
    {
        $conditions = "parent_id = '$parent_id' AND name = '$name' AND user_id = '{$user_id}'";
        $id && $conditions .= " AND id <> '" . $id . "'";
        return count($this->find(array('conditions' => $conditions))) == 0;
    }
	
	function check_menu_name($name, $parent_id = 0, $id = 0, $user_id = 0)
	{
		$conditions = "parent_id = '$parent_id' AND user_id = '$user_id'";
		$id && $conditions .= " AND id <> '" . $id . "'";
		$counts = count($this->find(array(
			'conditions' => $conditions,
			'fields'	 => 'id',
		)));
		$namelen = iconv_strlen($name,CHARSET);
		if(!$parent_id){
			if($counts >= 3){
				$this->_error('menu_gt_3');
            	return false;
			}
			if($namelen > 5){
				$this->_error('name_gt_4');
            	return false;
			}
		}else{
			if($counts >= 5){
				$this->_error('menu_gt_5');
            	return false;
			}
			if($namelen > 8){
				$this->_error('name_gt_8');
            	return false;
			}
		}
		return true;		
	}
	
	function get_list($parent_id = -1,$user_id = 0)
    {
        $conditions = "1 = 1";
        $parent_id >= 0 && $conditions .= " AND parent_id = '$parent_id'";
		$user_id >= 0 && $conditions .= " AND user_id = '$user_id'";
        return $this->find(array(
            'conditions' => $conditions,
            'order' => 'sort_order, id',
        ));
    }
	
	function get_menus()
	{
		$menus = array();
		$data = $this->get_list(0);
		if(!empty($data)){
			foreach(array_values($data) as $key => $val){
				$menus['button'][$key]['name'] = urlencode($val['name']); 
				$child = $this->get_list($val['id']);
				if(!empty($child)){
					foreach(array_values($child) as $k => $v){
						$menus['button'][$key]['sub_button'][$k]['name'] = urlencode($v['name']);
						$menus['button'][$key]['sub_button'][$k]['type'] = $v['type'];
						if($v['type'] == 'view')
						{
							$menus['button'][$key]['sub_button'][$k]['url'] = $v['link'];
						}
						else
						{
							$menus['button'][$key]['sub_button'][$k]['key'] = $v['reply_id'];
						}
					}
				}else{
					$menus['button'][$key]['type'] = $val['type'];
					if($val['type'] == 'view')
					{
						$menus['button'][$key]['url'] = $val['link'];
					}
					else
					{
						$menus['button'][$key]['key'] = $val['reply_id'];
					}
				}
			}
		}else{
			$this->_error('menu_empty');
            return false;
		}
		
		return urldecode(json_encode($menus));
	}
}

?>