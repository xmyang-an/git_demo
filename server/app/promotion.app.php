<?php

class PromotionApp extends ApibaseApp
{
    function price()
    {
		$data = array();

        import('promotool.lib');
		$promotool = new Promotool(array('_store_id' => $this->PostData['store_id']));
			
		$result = $promotool->getItemProInfo($this->PostData['goods_id'], $this->PostData['spec_id']);
		if($result !== FALSE) {
			if($result['pro_type'] == 'limitbuy') {
				$limitbuy_mod = &m('limitbuy');
				$limitbuy = $limitbuy_mod->get(array('conditions'=>"pro_id=".$result['pro_id'], 'fields' => 'end_time,pro_name'));
				$data['lefttime'] = Psmb_init()->lefttime($limitbuy['end_time']);
				$data['end_time'] = $limitbuy['end_time']+date('Z');
				
				$data['pro_name'] = $limitbuy['pro_name'];
			}
			
			else 
			{
				$data['pro_name'] = Lang::get($result['pro_type']);
			}
			
			$data['pro_price'] = $result['pro_price'];
		}

		if(!empty($data)){
			$this->json_success($data);
		}
		else{
			$this->json_fail('has_no_pro_price');
		}
    }
	
	function tool()
	{
		import('promotool.lib');
		$promotool = new Promotool(array('_store_id' => $this->PostData['store_id']));
        $promotool = $promotool->getGoodsAllPromotoolInfo($this->PostData['goods_id']);	
		if($promotool['storeFullfreeInfo'])
		{
			$promotool['storeFullfreeInfo'] = strip_tags($promotool['storeFullfreeInfo']);
		}
		
		if($promotool['storeFullPreferInfo'])
		{
			$promotool['storeFullPreferInfo'] = strip_tags($promotool['storeFullPreferInfo']);
		}
		
		$this->json_success($promotool);
	}
}

?>
