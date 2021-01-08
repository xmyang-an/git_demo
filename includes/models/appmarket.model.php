<?php

/* 应用市场 */
class AppmarketModel extends BaseModel
{
    var $table  = 'appmarket';
    var $prikey = 'aid';
    var $_name  = 'appmarket';
	
	function checkAvailable($appid, $checkStatus = FALSE)
	{
		$result = FALSE;
		if($appmarket = parent::get('appid="'.$appid.'"')) {
			if($checkStatus) {
				$appmarket['status'] && $result = TRUE;
			} else $result = TRUE;
		}
		
		return $result;
	}
	
	function getCheckAvailableInfo($appid, $store_id)
	{
		$result = TRUE;
		
		if(!$appmarket = parent::get('appid="'.$appid.'"')) {
			$result = array('msg' => Lang::get('appDisAvailable'), 'result_code' => 0);
		}
		else
		{		
			/* 在此处判断用户是否购买了该营销工具 */
			$apprenewal_mod = &m('apprenewal');
			if($apprenewal = $apprenewal_mod->get(array('conditions'=>'appid="'.$appid.'" AND user_id='. $store_id, 'order'=>'rid DESC'))) {
				
				/* 如果购买了，那么检查是否到期 */
				if($apprenewal['expired'] <= gmtime()) {
					
					/* 如果到期了，且目前该应用还开放购买 */
					if($appmarket['status']) {
						$result = array('msg' => sprintf(Lang::get('appHasExpired'), $appid), 'result_code' => -1);
					}
					/* 如果不开放购买 */
					else {
						$result = array('msg' => Lang::get('appDisAvailable'), 'result_code' => -31);
					}
				}
			
			} 
			/* 如果没有购买过 */
			else {
				
				/* 如果开放购买 */
				if($appmarket['status']) {
					$result = array('msg' => sprintf(Lang::get('appHasNotBuy'), $appid), 'result_code' => -2);
				}
				/* 如果不开放购买 */
				else {
					$result = array('msg' => Lang::get('appDisAvailable'), 'result_code' => -30);
				}
			}
		}
		
		return $result;
	}
}

?>