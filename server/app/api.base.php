<?php
class ApibaseApp extends ECBaseApp
{
	var $AppKey; 
	var $visitor;
	var $PostData;
	
    function __construct()
    {
        $this->ApibaseApp();
    }
    function ApibaseApp()
    {
		Lang::load(lang_file('common'));
        Lang::load(lang_file(APP));
		
		Lang::load(lang_file('server/common'));
        Lang::load(lang_file('server/' . APP));
		
        parent::__construct();
    }
	
	function _run_action()
    {
		$this->PostData = $this->_getPostData();//logResult1('post', $this->PostData);
		
		$error = $this->_CheckAccess();
		if($error != false){
			$this->json_fail($error);
			exit;
		}

        parent::_run_action();
    }
	
	function _CheckAccess()
	{
		$error = false;
		$post = $this->PostData;

		if((($post['time_stamp']+2000) < time()) || ($post['time_stamp'] > time()+2000)){
			$error = '10004';
		}
		else{
			if(isset($post['appid']) && isset($post['time_stamp']))
			{
				$merchant_mod = &m('merchant');
				$merchant = $merchant_mod->get('appId="'.$post['appid'].'"');
				if(!empty($merchant))
				{
					$this->AppKey = $merchant['appKey'];
					
					//请求验证通过
					if($this->_verifySign($post) == false)
					{
						$error = '10002';
					}
				}
				else{
					$error = '10001';
				}
			}
			else{
				$error = '10003';
			}
		}

		return $error;
	}
	
	function _checkUserAccess(){
		if(!isset($this->PostData['user_id']) || (isset($this->PostData['user_id']) && !intval($this->PostData['user_id']))){
			$this->json_fail('has_no_access');
			exit;
		}
		
		$member_mod = &m('member');
		$user_info = $member_mod->get(array(
            'conditions'    => "user_id = '{$user_id}'",
            'join'          => 'has_store',    
            'fields'        => 'user_id, locked',
        ));
		
		if(isset($user_info['locked']) && $user_info['locked'])
		{
			$this->json_fail('your_account_has_locked');
			exit;
		}
	}
	
	function _getPostData()
	{
		$post = file_get_contents("php://input");
		$post = json_decode($post, TRUE);
		
		if(!$post) $post = $_POST;
				
		$post = array_merge($_GET, $post);
		foreach($post as $key => $val)
		{
			if(is_string($val)) {
				if($key == 'JSON'){
					$val = stripslashes($val);
				}
				
				$post[$key] = trim($val);
			}
		}
	
		return $post;
	}
	
	function _MakeSign($param) 
	{
		$sign = $param['sign'];
		unset($param['sign']);unset($param['app']);unset($param['act']);
		
		// 对数组的值按key排序
		ksort($param);
		// 生成url的形式
		$params = http_build_query($param);
		// 生成sign
		$sign = md5($params.'&key='.$this->AppKey);

		return sha1($sign);
	}
	
	/*验证请求的合法性*/
	function _VerifySign($param) 
	{
		$result = false;
		
		$sign = $this->_MakeSign($param);
		if($sign == $param['sign']){
			$result = true;
		}

		return $result;
	}
	
	function GetImageTruePath($url)
	{
		if(stripos($url, '//:') == FALSE) {
			$url = SITE_URL . '/' . $url;
		}
		
		return $url;
	}
	
	function json_success($data,$msg=''){
		echo ecm_json_encode(array(
			'status' => 'SUCCESS',
			'msg'    => $msg,
			'result' => $data,
		));
	}
	
	function json_fail($msg,$data = array()){
		echo ecm_json_encode(array(
			'status' => 'FAIL',
			'result' => $data,
			'msg'    => Lang::get($msg)
		));
	}
	
	function json_redirect($data = array(),$msg=''){
		echo ecm_json_encode(array(
			'status' => 'REDIRECT',
			'result' => $data,
			'msg'    => Lang::get($msg)
		));
	}
	
	/* 取得支付方式实例 */
    function _get_payment($code, $payment_info)
    {
        include_once(ROOT_PATH . '/includes/payment.base.php');
        include(ROOT_PATH . '/includes/payments/' . $code . '/' . $code . '.payment.php');
        $class_name = ucfirst($code) . 'Payment';

        return new $class_name($payment_info);
    }
	
	function GetLocation()
	{
		$config_mod = &af('wx_mini');
        $setting = $config_mod->getAll(); //载入系统设置数据
		
		$cache_server =& cache_server();
		if($setting['enable_city']){
			$key = 'location' . $this->PostData['city_id'];
			$data = $cache_server->get($key);
			if($data === false)
			{
				$region_mod = &m('region');
				$ids = $region_mod->get_descendant($this->PostData['city_id']);
				
				$store_mod = &m('store');
				$stores = $store_mod->find(array(
					'conditions' => 'state = 1 AND 	region_id '.db_create_in($ids),
					'fields'     => 'store_id'
				));
				
				if(!empty($stores)){
					$data = array_keys($stores);
				}
				else{
					$data[] = -100;
				}
				
				$cache_server->set($key, $data, 1800);
			}
		}
		else{
			$key = 'location';
			$data = $cache_server->get($key);
			if($data === false)
			{
				$store_mod = &m('store');
				$stores = $store_mod->find(array(
					'conditions' => 'state = 1',
					'fields'     => 'store_id'
				));
				
				if(!empty($stores)){
					$data = array_keys($stores);
				}
				else{
					$data[] = -100;
				}
				
				$cache_server->set($key, $data, 1800);
			}
		}
		
		return $data;
	}
	
	function GetWxMPQRCode($canshu = array())
	{
		$file = ROOT_PATH.'/data/files/mall/phpqrcode/wxmp_'.md5(var_export($canshu, true)).'.jpg';
		if(!file_exists($file)){
			$params['appid'] = Conf::get('weixinminkey.AppID');// 小程序的appID
			$params['secret'] = Conf::get('weixinminkey.AppSecret');//小程序的appsecret
			
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$params['appid']."&secret=".$params['secret'];
			$result = ecm_curl($url);
			$data = json_decode($result,true);
			
			if(!$data['access_token']){
				return false;
			}

			$scene = $canshu['user_id'].':'.$canshu['id'];
			
			$url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.$data['access_token'];
			$result = ecm_curl($url,'POST',json_encode(array('scene' => $scene,'page' => $canshu['page'])),'','',TRUE);

			if(!empty(json_decode($result,true))){
				if($qrData['errcode']){
					return false;
				}
			}
			
			file_put_contents($file, $result);
		}
		
		return str_replace(ROOT_PATH, SITE_URL,$file);
	}
}



class ApiError
{
	public static function GetError($code)
	{
		$error = array(
			'10001' => '商户号不合法',
			'10002' => '签名验证失败',
			'10003' => '缺少参数:mid,time_stamp',
			'10004' => '请求已失效'
		);
		
		
		return $error[$code];
	}
	
	public static function EchoError($error)
	{
		$result = array(
			'result' => $error,
			'data'   => array(),
			'error'  => ApiError::GetError($error)
		);
			
		echo ecm_json_encode($result);
	}
}
?>
