<?php

/* 充值卡数据模型 */

class CashcardModel extends BaseModel {
	
 	var $table 	= 'cashcard';
    var $prikey = 'id';
	var $name 	= 'cashcard';
	
	function genCardNo( $length = 16 )
	{
		// 如果批量生成的数量大，卡了， 就直接考虑下面这个吧
		//$cardNo = EPAY_CARD_RECHARGE . date('YmdHis',gmtime()+8*3600).rand(1000,9999);
		
        $cardNo = $this->make_char($length);
		
        $record = parent::get("cardNo='{$cardNo}'");
        if (!$record)
        {
            /* 否则就使用这个交易号 */
            return $cardNo;
        }

        /* 如果有重复的，则重新生成 */
        else {
			
			return $this->genCardNo( $length );
		}
	}
	
	/* 生成指定长度的随机字符串 */
	function make_char( $length = 16 )
	{  
		// 密码字符集，可任意添加你需要的字符  
		$chars = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'G', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'S', 'Y', 'Z');  

		// 在 $chars 中随机取 $length 个数组元素键名  
		$str = '';  

		for($i = 0; $i < $length; $i++){  

   			// 将 $length 个数组元素连接成字符串  

   			$str .= $chars[array_rand($chars)];
		}
		
		//if(substr( $str, 0, 1 ) == '0') {
			
			//$str = $this->make_char( $length );
		//}

		return $str;
	}
}

?>