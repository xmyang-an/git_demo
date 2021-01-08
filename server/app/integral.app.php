<?php

class IntegralApp extends ApibaseApp
{
	function sign()
	{
		$this->_checkUserAccess();
		
		$user_id = empty($this->PostData['user_id'])  ? 0 : intval($this->PostData['user_id']);

		$integral_log_mod = &m('integral_log');
		$log = $integral_log_mod->get(array('conditions'=>"type ='sign_in_integral' AND user_id = ".$user_id,'order'=>'add_time desc'));

		if(!empty($log)){
			if(local_date('Ymd', gmtime()) == local_date('Ymd', $log['add_time']))
			{
				$this->json_fail('you_have_got_integral_for_sign_in');
				return;
			}
		}
		
		$integral_mod=&m('integral');
		$data = array(
			'user_id' => $user_id,
			'type'    => 'sign_in_integral',
			'amount'  => $integral_mod->_get_sys_setting('sign_in_integral')
		);
		
	    $integral_mod->update_integral($data);
		
		$new_amount = $integral_mod->get($user_id);
		
		$this->json_success(array('amount' => floatval($new_amount['amount'])),sprintf(Lang::get('success_get_integral_for_sign_in'),$integral_mod->_get_sys_setting('sign_in_integral')));
	}
}

?>
