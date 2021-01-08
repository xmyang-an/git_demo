<?php

class UserApp extends ApibaseApp
{
	var $_member_mod;
	var $_bind_mod;
	
	function __construct()
    {
	   parent::__construct();
       $this->_member_mod = &m('member');
	   $this->_bind_mod = &m('member_bind');
    }
	
	function baseinfo()
	{
		$user_id = empty($this->PostData['user_id'])  ? 0 : intval($this->PostData['user_id']);
		
		if($user_id > 0){
			$user = $this->_member_mod->get(array(
            	'conditions'    => "user_id = '{$user_id}'",
            	'join'          => 'has_store',                 //关联查找看看是否有店铺
            	'fields'        => 'user_id, user_name,portrait, phone_mob,reg_time, last_login, last_ip, store_id',
        	));
			
			$integral_mod = &m('integral');
			if($integral_mod->_get_sys_setting('integral_enabled'))
			{
				$integral = $integral_mod->get($user['user_id']);
				$user['integral'] = floatval($integral['amount']);
				$user['can_sign'] = TRUE;
				
				$integral_log_mod = &m('integral_log');
				$logs = $integral_log_mod->get(array(
					'conditions'=> "type='sign_in_integral' AND user_id=".$user['user_id'],
					'fields'    => "add_time",
					'order'		=> "log_id DESC",
				));
				
				if($logs) {
					if(local_date('Y-m-d', $logs['add_time']) == local_date('Y-m-d', gmtime())) {
						$user['can_sign'] = FALSE;
					}
				}
			}
			
			$user['count_collect_goods'] = count($this->collect_goods(false));
			$user['count_collect_store'] = count($this->collect_store(false));
			$user['count_footmark']		 = count($this->goods_history(false));
		}
		
		//未登录也要返回一个默认的图像
		$user['portrait'] = $user['portrait'] ? $user['portrait'] : Conf::get('default_user_portrait');
		if(stripos($user['portrait'], '//:') == FALSE) {
			$user['portrait'] = SITE_URL . '/' . $user['portrait'];
		}
			
		
		$this->json_success($user);
	}
	
	function collect_goods($ajax = true)
    {
        $model_goods =& m('goods');
		$collect_goods = $model_goods->find(array(
            'join'  => 'be_collect,belongs_to_store,has_default_spec',
            'fields'=> 'g.goods_id,g.goods_name,g.default_image',
            'conditions' => 'collect.user_id = ' . $this->PostData['user_id'],
        ));
        
		if($ajax == false){
			return $collect_goods;
		}
		
		$this->json_success($collect_goods);
    }
	
	/* 取得/设置浏览历史 */
    function goods_history($ajax = true)
    {

        return $goods_list;
    }
	

    function collect_store($ajax = true)
    {
        $model_store =& m('store');
        $collect_store = $model_store->find(array(
            'join'  => 'be_collect,belongs_to_user',
            'fields'=> 's.store_id,store_name,store_logo',
            'conditions' => 'collect.user_id = ' . $this->PostData['user_id'],
        ));
       
	   	if($ajax == false){
	    	return  $collect_store;
		}
		
		$this->json_success($collect_goods);
    }
	
	//必须是由小程序授权登录
	function auth()
	{
		if(!$this->PostData['code'])
		{
			$this->json_fail('black_hacker');
			exit;
		}
	
		$js_code = html_script($this->PostData['code']);
		$res = $this->_GetMiniOpenid($js_code);
		
		$openid = $res['openid'];
		$unionid = $res['unionid'] ? $res['unionid'] : $res['openid'];
		if(!$openid){
			$this->json_fail('身份验证失败！');
			exit;
		}

		$bind = $this->_bind_mod->get(array('conditions'=>"unionid='".$unionid."' AND app ".db_create_in(array('weixin', 'miniprogram')), 'fields'=>'user_id,enabled'));
			
		// 包含登录状态绑定的情况，如果当前登录用户与原有绑定用户不一致，则修改为新绑定
		if($bind && $bind['user_id'] && $bind['enabled'])
		{
			$user_id = $bind['user_id'];
			if(!$user = $this->_member_mod->get(array('conditions'=>'user_id='.$user_id, 'fields'=>'user_id'))) {
				$this->_bind_mod->drop('user_id='.$user_id);
				$this->json_fail('bind_data_error');
				exit;
			}
			
			$checkBind = $this->_bind_mod->get(array('conditions'=>"unionid='".$unionid."' AND app='miniprogram'", 'fields'=>'user_id,enabled'));
			if(empty($checkBind))
			{	
				$bindData = array(
					'unionid' 	=> $unionid, 
					'openid' 	=> $openid,
					'token' 	=> $token, 
					'user_id' 	=> $user_id, 
					'nickname'  => $this->PostData['nickName'],
					'app' 		=> 'miniprogram', 
					'enabled' 	=> 1
				);
					
				$this->_bind_mod->add($bindData);
			}
		}
		else{
			$nickname   = isset($this->PostData['nickName']) ? html_script($this->PostData['nickName']) : '';
			$app		= 'miniprogram';
			
			$email    	= gmtime().'@qq.com';
			$password  	= md5(gmtime());
			
			$bind = $this->_bind_mod->get("(unionid='{$openid}' OR openid='{$openid}') AND app='{$app}'");
			if(!empty($bind) && $member = $this->_member_mod->get($bind['user_id'])){
				$this->_member_mod->edit($bind['user_id'],$data);
				$user_id = $bind['user_id'];
			}else{
				do {
					$ms =& ms();
						 
					if(isset($this->PostData['nickName']) && !empty($nickname)) {
						$user_name  = $ms->user->check_username($nickname) ? $nickname : $nickname.mt_rand(10,99);
					} 
					else{ 
						$user_name  = gmtime() . mt_rand(10,99);
					}
					
					$data = array('referid' => $this->PostData['referid']);
					if(isset($this->PostData['avatarUrl']))
					{
						$file_name = ROOT_PATH.'/data/files/mall/settings/'.md5(mt_rand(1000,9999).gmtime()).'.jpg';
						file_put_contents($file_name, file_get_contents($this->PostData['avatarUrl']));
							
						$data['portrait'] = str_replace(ROOT_PATH.'/', '', $file_name);
					}
					
					$user_id = $ms->user->register($user_name, $password, $email,$data);
					
					if($user_id){
						$deposit_account_mod = &m('deposit_account');
						$deposit_account_mod->_create_deposit_account($user_id);
					}
					
				}while (!$user_id);
			}
			
			// 将绑定信息插入数据库
			$bindData = array(
				'unionid' 	=> $unionid, 
				'openid' 	=> $openid,
				'token' 	=> '', 
				'user_id' 	=> $user_id, 
				'nickname'  => $nickname,
				'app' 		=> $app, 
				'enabled' 	=> 1
			);
				
			// 如果存在有绑定，则修改
			if($bind = $this->_bind_mod->get("unionid='{$unionid}' AND app='{$app}'")) {
				$this->_bind_mod->edit($bind['id'], $bindData);
			} 
			// APP中微信登录兼容处理
			elseif($bind = $this->_bind_mod->get("openid='{$openid}' AND app='{$app}'")) {
				$this->_bind_mod->edit($bind['id'], $bindData);
			}
			else{
				$this->_bind_mod->add($bindData);
			}
		}
		
		$result = array();
		if($user_id > 0){
			$result = $this->_member_mod->get(array(
				'join'       => 'has_store',
				'conditions' => 'user_id='.$user_id,
				'fields'     => 'user_name,portrait,phone_mob,reg_time,s.store_id,state'
			));
		}
		
		$this->json_success($result);
	}
	
	function _GetMiniOpenid($js_code)
	{
		$data = false;
		
		if(!$js_code){
			return $data;
		}
		
		$params['appid'] = Conf::get('weixinminkey.AppID');// 小程序的appID
		$params['secret'] = Conf::get('weixinminkey.AppSecret');//小程序的appsecret
		$params['js_code'] = $js_code;
	 
		$params['grant_type'] = 'authorization_code';
	 
		$urls = "https://api.weixin.qq.com/sns/jscode2session?appid=".$params['appid']."&secret=".$params['secret']."&grant_type=authorization_code&js_code=".$params['js_code']."";
	 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $urls);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		$output = curl_exec($ch);
	 
	 
		if (false === $output) {
			$data = false;
		}
			
		$data = ecm_json_decode($output,true);

		return $data;
	}
	
	function check_phone()
	{
		$this->_checkUserAccess();
		
        $phone_mob = isset($this->PostData['phone_mob']) ? html_script($this->PostData['phone_mob']) : '';
        if (!$phone_mob)
        {
            $this->json_fail('请输入一个手机号码');
            return;
        }
		
		if (!is_mobile($phone_mob))
        {
            $this->json_fail('手机号码格式不正确');
			return;
        }
		
		$ms =& ms();
		if(!$ms->user->check_phone($phone_mob, $this->PostData['user_id']))
		{
			$error = current($ms->user->get_error());
            $this->json_fail($error['msg']);
			return;
		}
			
		$this->json_success(true);
	}
	
	function bind()
	{
		$this->_checkUserAccess();
		
		$phone_mob = isset($this->PostData['phone_mob']) ? html_script($this->PostData['phone_mob']) : '';
        if (!$phone_mob)
        {
            $this->json_fail('请输入一个手机号码');
            return;
        }
		
		if (!is_mobile($phone_mob))
        {
            $this->json_fail('手机号码格式不正确');
			return;
        }
		
		$ms =& ms();
		if(!$ms->user->check_phone($phone_mob, $this->PostData['user_id']))
		{
			$error = current($ms->user->get_error());
            $this->json_fail($error['msg']);
			return;
		}
		
		$member_mod = &m('member');
		$member_mod->edit($this->PostData['user_id'], array('phone_mob' => $phone_mob));
		
		$this->json_success(true);
	}
	
	//获得某个会员的基本信息
	function info()
	{
		$user_id = isset($this->PostData['user_id']) ? html_script($this->PostData['user_id']) : '';
        if (!$user_id)
        {
            $this->json_fail('会员不存在');
            return;
        }
		
		$member_mod = &m('member');
		$user = $member_mod->get(array(
			'conditions' => 'user_id='.$user_id,
			'join'       => 'has_store',
			'fields'     => 'user_name,phone_mob,store_name'
		));
		
		unset($user['user_id']);
		
		$this->json_success($user);
	}
}

?>
