<?php

/* 用户购买应用续期表 */
class ApprenewalModel extends BaseModel
{
    var $table  = 'apprenewal';
    var $prikey = 'rid';
    var $_name  = 'apprenewal';
	
	/* 判断是购买还是续费 */
	function checkIsRenewal($appid, $user_id)
	{
		$result = FALSE;
		if($appid && $user_id) 
		{
			$renewal = parent::get(array('conditions' => 'user_id=' . $user_id . ' AND appid="'.$appid.'"', 'order' => 'rid DESC'));
		
			if($renewal && ($renewal['expired'] > gmtime())) {
				$result = $renewal;
			}
		}
		return $result;
	}
}

?>