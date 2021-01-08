<?php

/* 购买应用记录表 */
class AppbuylogModel extends BaseModel
{
    var $table  = 'appbuylog';
    var $prikey = 'bid';
    var $_name  = 'appbuylog';
	
	/**
     *    生成订单号
     *
     *    @author   Mimall
     *    @return    string
     */
	function genOrderId( $length = 12 )
	{  
		// 密码字符集，可任意添加你需要的字符  
		$chars = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');  

		// 在 $chars 中随机取 $length 个数组元素键名  
		$orderId = '';  

		for($i = 0; $i < $length; $i++){  

   			// 将 $length 个数组元素连接成字符串  

   			$orderId .= $chars[array_rand($chars)];
		}
		
		if(substr( $orderId, 0, 1 ) == '0') {
			
			$orderId = $this->genOrderId( $length );
		}
		
		$buylog = parent::get("orderId='{$orderId}'");
        if (!$buylog)
        {
            /* 否则就使用这个订单号 */
            return $orderId;
        }

		/* 如果有重复的，则重新生成 */
        return $this->genOrderId( $length );
	}
}

?>