<?php

class GetaccesstokenApp extends MallbaseApp
{
    function index()
    {
		$post = parent::_getPostData();
		$post = $post['data'];
	
		$appId = trim($post['appKey']);
		$appKey = trim($post['appSecret']);
		
		$merchant_mod = &m('merchant');
		$merchantLog_mod = &m('merchantLog');
		
		$result = array();
		
		if($appId && $appKey)
		{
			$merchant = $merchant_mod->get("appId='{$appId}' AND appKey='{$appKey}'");
			if(!$merchant || $merchant['closed'])
			{
				$result = array(
					'isSuccess' => FALSE,
					'accessToken' => '',
					'returnMsg' => '商户不存在或已被禁用，或商户号、商户秘钥有误',
				);
			}
			else
			{
				// 如果上次生成的token没有过期，则使用它
				if($merchantLog = $merchantLog_mod->get(array('conditions' => "appId='{$appId}' AND expired>" . gmtime(), 'order' => 'logid DESC'))) {
					$result = array(
						'isSuccess' 	=> TRUE,
						'returnMsg' 	=> '授权成功',
						'accessToken'	=> $merchantLog['token']
					);
				}
				else
				{
					$time = gmtime();
					$expired = $time + 3600 * 24;
					$token  = md5($appId.$appKey.$time.$expired.mt_rand(10,99));
					
					// 保存token以便下次使用
					if($merchantLog_mod->add(array('appId' => $appId, 'token' => $token, 'add_time' => $time, 'expired' => $expired))) {
						
						$result = array(
							'isSuccess' 	=> TRUE,
							'returnMsg' 	=> '授权成功',
							'accessToken'	=>	$token
							
						);
					}
				}
			}
			
		}
		else
		{
			$result = array(
				'isSuccess' => FALSE,
				'accessToken' => '',
				'returnMsg' => '商户号（appKey）或商户秘钥（appSecret）不能为空',
				
			);
		}
		
		//print_r($result);
		echo json_encode($result);
	}
}

?>
