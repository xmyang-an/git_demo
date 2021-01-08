<?php

class RegionApp extends MallbaseApp
{
    function getCity() 
	{
		list($check, $loginResult) = TRUE;//parent::_checkLogin();
		if($check === FALSE)
		{
			$result = array(
				'status' => 'FAILED',
				'errorMsg' => $loginResult['errorMsg']
			);
		}
		else
		{
			//$post = parent::_getPostData();
			
			$result = $hotCity = array();
			$region_mod = &m('region');
			
			// 国
			$regions = $region_mod->find(array(
				'conditions' => 'parent_id=0',
				'fields' => 'region_id, region_name, parent_id'
			));
			
			// 省
			foreach($regions as $key => $val) {
				$provinces = $region_mod->find(array(
					'conditions' => 'parent_id='.$val['region_id'],
					'fields' => 'region_id, region_name, parent_id',
					//'limit' => 10
				));
				
				// 城市
				foreach($provinces as $k => $v) 
				{
					// 找出热门城市(直辖市)
					if(in_array($v['region_name'], array('北京市', '上海市'))) {
						$hotCity[$k] = $v;
					}
					
					
					if(in_array($v['region_name'], array('北京市', '天津市', '上海市', '重庆市'))) {
						$cities = array($k => $v); 
						sort($cities);
						
						//$provinces[$k]['region_name'] = str_replace('市', '', $v['region_name']);
						$provinces[$k]['cities'] = $cities;
					}
					else { 
						$cities = $region_mod->find(array(
							'conditions' => 'parent_id='.$v['region_id'],
							'fields' => 'region_id, region_name, parent_id'
						));
						sort($cities);
						$provinces[$k]['cities'] = $cities;
						
						foreach($cities as $k1 => $v1) {
							if(in_array($v1['region_name'], array('南宁', '桂林', '柳州', '齐齐哈尔'))) {
								$hotCity[$v1['region_id']] = $v1;
							}
						}
						sort($hotCity);
					}
				}
			}
			sort($provinces);
		
			$result = array(
				'status'	=> 'SUCCESS',
				'title' 	=> "获取城市数据",
				'errorMsg' 	=> 'kkkk',
				'retval'	=> array('provinces' => $provinces, 'hotCity' => $hotCity)
			);
			
			echo json_encode($result);
		}
	}
	
}

?>
