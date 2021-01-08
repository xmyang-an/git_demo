<?php

class CommonApp extends ApibaseApp
{
   function __constrcut(){
	   parent::__constrcut();
   }
   
   function baseinfo()
   {
	   $config_mod = &af('wx_mini');
       $setting = $config_mod->getAll(); //载入系统设置数据
		
	   $data = array(
	   		'site_name' => Conf::get('site_name'),
			'site_logo' => SITE_URL.'/'.Conf::get('site_logo'),
			'description' => Conf::get('site_description'),
			'hide_module' => $setting['hide_module']
	   );
	   
	   $this->json_success($data);
   }
	
   function sendcode()
   {
	   $phone_mob = $this->PostData['phone_mob'];
	   if(is_mobile($phone_mob) == false){
		   $this->json_fail('手机号码不正确');
		   exit;
	   }
	   
	   import('sms.lib');
	   $sms = new SMS();
	   
	   $code = rand(100000, 999999); // 产生6位数字验证码			
	   $smsText = sprintf(Lang::get('your_check_code'),$code);
			
	   $result = $sms->send(array('phone_mob' => $phone_mob, 'text' => $smsText, 'sender' => 0));	
	   if($result == true){
	  	  $this->json_success($code);
	   }
	   else{
		   $this->json_fail('短信发送失败');
	   }
   }
}

?>
