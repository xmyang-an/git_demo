<?php

class RegionApp extends ApibaseApp
{
	var $_region_mod;
	
	function __construct()
    {
	   parent::__construct();
	   $this->_region_mod = &m('region');
    }
	
	function listing()
	{
		$region = $this->_region_mod->find('parent_id = 0');
		if(!empty($region)){
			$region = current($region);
			$regions = $this->_region_mod->find('parent_id <> 0');//默认第一级是中国
			
			$this->json_success(array('list' => $regions, 'firstLay' => $region));
			exit;
		}
		
		$this->json_fail('has_no_data');
	}
	
	function sortByLayer()
	{
		$data = array();
		
		$region = $this->_region_mod->find('parent_id = 0');
		if(!empty($region)){
			$region = current($region);
			$provinces = $this->_region_mod->find('parent_id ='.$region['region_id']);//默认第一级是中国
			
			if(!empty($provinces)){
				$i = 0;
				foreach($provinces as $key=>$val){
					$data[$i] = $val;
					$data[$i]['cities'] = array();
					
					$cities = $this->_region_mod->find('parent_id ='.$val['region_id']);
					if(!empty($cities)){
						$data[$i]['cities'] = array_values($cities);
					}
					
					$i++;
				}
			}
		}
		
		$this->json_success($data);
	}
	
	function currentLocationInformation()
	{
		$city_id = 0;
		
		$baidukey = Conf::get('baidukey');
		if(!$this->PostData['lat'] || !$this->PostData['lng'] || !$baidukey['browser']){
			$this->json_fail('params_lost');
			exit;
		}
		
		$find = false;
		
		$gateway = "http://api.map.baidu.com/reverse_geocoding/v3/?ak=".$baidukey['server']."&output=json&coordtype=wgs84ll&location=".$this->PostData['lat'].",".$this->PostData['lng'];
		
		$data = ecm_curl($gateway);
		if($data){
			$data = ecm_json_decode($data,true);
			$result = $data['result']['addressComponent'];
			
			if(!empty($result))
			{
				$region_mod = &m('region');
				$region = $region_mod->get(array('conditions'=>'parent_id=0','fields'=>'region_id'));
				$parent_id = $region['region_id'];

				$conditions = "region_name='".$result['province']."' OR region_name='".str_replace('省','',$result['province'])."' and parent_id=".$parent_id;
	
				$region = $region_mod->get(array('conditions'=>$conditions,'fields'=>'region_id,region_name'));
				if($region)
				{
					$province_id = $region['region_id'];
					$conditions = "region_name='".$result['city']."' OR region_name='".str_replace('市','',$result['city'])."' and parent_id=".$province_id;
	
					$region_city = $region_mod->get(array('conditions'=>$conditions,'fields'=>'region_id,region_name'));
					if($region_city) {	
						$find = true;
					}
				}
			}
			
		}
		
		if($find == true){
			$region_array = array(
				array(
					'region_id' => $region['region_id'],
					'region_name' => $region['region_name']
				),
				array(
					'region_id' => $region_city['region_id'],
					'region_name' => $region_city['region_name']
				),
			);	
			
			$address = $data['result']['addressComponent']['district'].$data['result']['addressComponent']['street'].$data['result']['addressComponent']['street_number'];
		}
		
		$this->json_success(array(
			'location' => array('lat' => $this->PostData['lat'], 'lng' => $this->PostData['lng']),
			'regions' => $region_array,
			'address' => $address ? $address  : ''
		));
	
	}
	
	function parseLocation()
	{
		$baidukey = Conf::get('baidukey');
		if(!$this->PostData['lat'] || !$this->PostData['lng'] || !$baidukey['browser']){
			$this->json_fail('params loss');
			exit;
		}
		
		$gateway = "http://api.map.baidu.com/geocoder/v2/?callback=renderReverse&location=".$this->PostData['lat'].",".$this->PostData['lng']."&output=json&pois=1&ak=".$baidukey['browser'];
		
		$data = ecm_curl($gateway);
		if($data){
			$str = substr($data,29,strlen($data)-30);
			$result = ecm_json_decode($str,true);
			$result = $result['result']['addressComponent'];
			
			$this->json_success($result);
			exit;
		}
		
		$this->json_fail('parse error');
	}
	
	function GetPosition()
	{
		$city_id = 0;
		
		$baidukey = Conf::get('baidukey');
		if(!$this->PostData['lat'] || !$this->PostData['lng'] || !$baidukey['browser']){
			$this->json_success($city_id);
			exit;
		}
		
		$gateway = "http://api.map.baidu.com/geocoder/v2/?callback=renderReverse&location=".$this->PostData['lat'].",".$this->PostData['lng']."&output=json&pois=1&ak=".$baidukey['browser'];
		
		$data = ecm_curl($gateway);
		if($data){
			$str = substr($data,29,strlen($data)-30);
			$result = ecm_json_decode($str,true);
			$result = $result['result']['addressComponent'];
			
			if(!empty($result))
			{
				$region_mod = &m('region');
				$region = $region_mod->get(array('conditions'=>'parent_id=0','fields'=>'region_id'));
				$parent_id = $region['region_id'];

				$conditions = "region_name='".$result['province']."' OR region_name='".str_replace('省','',$result['province'])."' and parent_id=".$parent_id;
	
				$region = $region_mod->get(array('conditions'=>$conditions,'fields'=>'region_id,region_name'));
				if($region)
				{
					$province_id = $region['region_id'];
					$conditions = "region_name='".$result['city']."' OR region_name='".str_replace('市','',$result['city'])."' and parent_id=".$province_id;
	
					$region_city = $region_mod->get(array('conditions'=>$conditions,'fields'=>'region_id,region_name'));
					if($region_city) {	
						$city_id = $region_city['region_id'];
					}
				}
			}
			
		}
		
		$this->json_success($city_id);
		exit;
	}
}

?>
