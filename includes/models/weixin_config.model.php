<?php
class Weixin_configModel extends BaseModel 
{

    var $table = 'weixin_config';
    var $prikey = 'id';
    var $_name = 'weixin_config';
	
    function unique($user_id = 0) 
	{
		
		if($this->get("user_id='".$user_id."'"))
		{
			return true;
		}
		else
		{
			return false;
		}
    }
	
	function generate_token($length = 8) 
	{  
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';  
		$token = '';  
		for ( $i = 0; $i < $length; $i++ )  
		{   
			$token .= $chars[ mt_rand(0, strlen($chars) - 1) ];  
		}  
		return $token;  
	} 
}
?>