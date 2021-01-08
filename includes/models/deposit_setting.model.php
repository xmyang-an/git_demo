<?php

class Deposit_settingModel extends BaseModel
{
    var $table  = 'deposit_setting';
    var $prikey = 'setting_id';
    var $_name  = 'deposit_setting';
	
	function _get_system_setting()
	{
		$setting = parent::get('user_id=0');
		
		// 如果没有系统默认配置，则增加一条
		if(!$setting) {
			parent::add(array('user_id' => 0, 'trade_rate' => 0, 'transfer_rate' => 0, 'auto_create_account' => 1));
			
			$setting = parent::get('user_id=0');
		}
		
		return $setting;
	}
	
	function _get_deposit_setting($user_id = 0, $fields='')
	{
		if(!$user_id) 
		{
			$setting = $this->_get_system_setting();
		}
		else
		{
			$setting = $this->get('user_id='.$user_id);
			
			if(!$setting) {
				$setting = $this->_get_system_setting();
			}
		}
		
		if(empty($fields))
		{
			return $setting;
		}
		
		$result = $setting[$fields];
		
		if($result <0 || $result>1) return 0;
		
		return $result;
	}
} 

?>