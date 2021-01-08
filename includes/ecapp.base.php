<?php

define('IMAGE_FILE_TYPE', 'gif|jpg|jpeg|png'); // 图片类型，上传图片时使用

define('SIZE_GOODS_IMAGE', '10485760');   // 商品大小限制10M
define('SIZE_STORE_LOGO', '102400');      // 店铺LOGO大小限制10OK
define('SIZE_STORE_BANNER', '1048576');  // 店铺BANNER大小限制1M
define('SIZE_STORE_CERT', '409600');     // 店铺证件执照大小限制400K
define('SIZE_STORE_PARTNER', '102400');  // 店铺合作伙伴图片大小限制100K
define('SIZE_CSV_TAOBAO', '2097152');     // 淘宝助理CSV大小限制2M

/* 店铺状态 */
define('STORE_APPLYING', 0); // 申请中
define('STORE_OPEN',     1); // 开启
define('STORE_CLOSED',   2); // 关闭
define('STORE_REJECT',   3); // 拒绝

/* 订单状态 */
define('ORDER_SUBMITTED', 10);                 // 针对货到付款而言，他的下一个状态是卖家已发货
define('ORDER_PENDING', 11);                   // 等待买家付款
define('ORDER_ACCEPTED', 20);                  // 买家已付款，等待卖家发货
define('ORDER_SHIPPED', 30);                   // 卖家已发货
define('ORDER_FINISHED', 40);                  // 交易成功
define('ORDER_CANCELED', 0);                   // 交易已取消

/* 特殊文章分类ID */
define('STORE_NAV',    -1); // 店铺导航
define('ACATE_HELP',    1); // 商城帮助
define('ACATE_NOTICE',  2); // 商城快讯（公告）
define('ACATE_SYSTEM',  3); // 内置文章

/* 系统文章分类code字段 */
define('ACC_NOTICE', 'notice');                 //acategory表中code字段为notice时——商城公告类别
define('ACC_SYSTEM', 'system');                 //acategory表中code字段为system时——内置文章类别
define('ACC_HELP', 'help');                     //acategory表中code字段为help时——商城帮助类别

/* 邮件的优先级 */
define('MAIL_PRIORITY_LOW',     1);
define('MAIL_PRIORITY_MID',     2);
define('MAIL_PRIORITY_HIGH',    3);

/* 发送邮件的协议类型 */
define('MAIL_PROTOCOL_LOCAL',       0, true);
define('MAIL_PROTOCOL_SMTP',        1, true);

/* 数据调用的类型 */
define('TYPE_GOODS', 1);

/* 上传文件归属 */
define('BELONG_ARTICLE',    1);
define('BELONG_GOODS',      2);
define('BELONG_STORE',      3);
define('BELONG_GCATEGORY',  4);
define('BELONG_MEAL',		5);
define('BELONG_GIFT',		6);
define('BELONG_APPMARKET',  7);
define('BELONG_EVALUATION',  8);
define('BELONG_REPORT',  9);

/* 商户号（此为预留项，用于今后把支付系统独立出来后的兼容考虑，参照支付宝的合作者身份ID（16位），以1688开头 */
define('MERCHANTID', '');// 参考值：'1688000000000000'

/* 商务业务类型代码 */
define('TRADE_ORDER', 		'trade10001'); // 购物订单
define('TRADE_RECHARGE', 	'trade20001'); // 充值订单
define('TRADE_DRAW', 		'trade30001'); // 提现订单
define('TRADE_CHARGE', 		'trade40001'); // 系统扣费
define('TRADE_BUYAPP', 		'trade50001'); // 应用订单
define('TRADE_TRANS', 		'trade60001'); // 转账订单
define('TRADE_FX',			'trade70001'); // 分销订单

/* 二级域名开关 */
!defined('ENABLED_SUBDOMAIN') && define('ENABLED_SUBDOMAIN', 0);

/* 环境 */
!defined('CHARSET') && define('CHARSET', substr(LANG, 3));
define('IS_AJAX', isset($_REQUEST['ajax']));
/* 短消息的标志 */
define('MSG_SYSTEM' , 0); //系统消息

/* 拼团活动状态 */
define('GROUP_PENDING',  0);            // 未发布
define('GROUP_ON',       1);            // 正在进行
define('GROUP_END',      2);            // 已结束

/* 通知类型 */
define('NOTICE_MAIL',   1); // 邮件通知
define('NOTICE_MSG',    2); // 站内短消息

/**
 *    ECBaseApp
 *
 *    @author    MiMall
 *    @usage    none
 */
class ECBaseApp extends BaseApp
{
    var $outcall;
    function __construct()
    {
        $this->ECBaseApp();
    }
    function ECBaseApp()
    {
        parent::__construct();

        if (!defined('MODULE')) // 临时处理方案，此处不应对模块进行特殊处理
        {
            /* GZIP */
            if ($this->gzip_enabled())
            {
                ob_start('ob_gzhandler');
            }
            else
            {
                ob_start();
            }

            /* 非utf8转码 */
            if (CHARSET != 'utf-8' && isset($_REQUEST['ajax']))
            {
                $_FILES = ecm_iconv_deep('utf-8', CHARSET, $_FILES);
                $_GET = ecm_iconv_deep('utf-8', CHARSET, $_GET);
                $_POST = ecm_iconv_deep('utf-8', CHARSET, $_POST);
            }

            /* 载入配置项 */
            $setting =& af('settings');
            Conf::load($setting->getAll());

            /* 初始化访问者(放在此可能产生问题) */
            $this->_init_visitor();

            /* 计划任务守护进程 */
            $this->_run_cron();
        }
    }
	
	
	
		//框架显示百度地图以及坐标
	function showBaiduMap()
	{
		$data = array();
		
		$data['lat'] = floatval($_GET['lat']);
		$data['lng'] = floatval($_GET['lng']);
		
		$this->assign($data);
		$this->display('map.index.html');
	}
	
	function baiduParseAddress()
	{
		$address = html_script($_GET['address']);
		if(!$address)
		{
			$this->json_error(false);
			exit;
		}
		
		$baidukey = Conf::get('baidukey');
		$url = 'https://api.map.baidu.com/geocoding/v3/?address='.preg_replace("/\t/","",$address).'&output=json&ak='.$baidukey['server'].'&callback=showLocation';
		$result = file_get_contents($url);
		if($result)
		{
			$result = substr($result,27,-1);
			$data = json_decode($result,true);

			if($data['status'] == 0)
			{
				$this->json_result($data['result']['location']);
				exit;
			}
		}
		
		$this->json_error(false);
		exit;
	}
	
	function locationInformation()
	{
		$lat = html_script($_GET['lat']);
		$lng = html_script($_GET['lng']);
		
		if(!$lat || !$lng){
			$this->json_error('param_is_lost');
			exit;
		}
		
		$baidukey = Conf::get('baidukey');
		$url = "http://api.map.baidu.com/reverse_geocoding/v3/?ak=".$baidukey['browser']."&output=json&coordtype=wgs84ll&location=".$lat.",".$lng;
		
		$find = false;
		$result = file_get_contents($url);
		if($result)
		{
			$data = json_decode($result,true);
			if($data['status'] == 0)
			{
				$taobao_province_name = $data['result']['addressComponent']['province'];
				$taobao_city_name = $data['result']['addressComponent']['city'];
				
				$region_mod = &m('region');
				$region = $region_mod->get(array('conditions'=>'parent_id=0','fields'=>'region_id'));
				$parent_id = $region['region_id'];

				$conditions = "region_name='".$taobao_province_name."' OR region_name='".str_replace('省','',$taobao_province_name)."' and parent_id=".$parent_id;
	
				$region = $region_mod->get(array('conditions'=>$conditions,'fields'=>'region_id,region_name'));
				if($region)
				{
					$province_id = $region['region_id'];
						
					//（不完善，如果遇到特殊情况，需要修改地区，使名称跟淘宝的一致）简单处理广州市!=广州的情况，淘宝的市一般加上"市"
					$conditions = "region_name='".$taobao_city_name."' OR region_name='".str_replace('市','',$taobao_city_name)."' and parent_id=".$province_id;
	
					$region_city = $region_mod->get(array('conditions'=>$conditions,'fields'=>'region_id,region_name'));
					if($region_city) {	
						$find = true; //只有省份和城市名称正确匹配的时候，才返回true
					}
				}
			}
		}
		
		if($find == true){
			$region_array = array(
				array(
					'region_id' => $region['region_id'],
					'region_name' => $region['region_name']
				),
				array(
					'region_id' => $region_city['region_id'],
					'region_name' => $region_city['region_name']
				),
			);	
			
			$address = $data['result']['addressComponent']['district'].$data['result']['addressComponent']['street'].$data['result']['addressComponent']['street_number'];
		}
		
		$this->json_result(array(
			'location' => array('lat' => $lat, 'lng' => $lng),
			'regions' => $region_array,
			'address' => $address
		));
	}
	
	// 生成二维码
    function generateQRCode($code, $params = array())
    {
        include_once ROOT_PATH . '/includes/phpqrcode/phpqrcode.php';
        $frame = isset($params['frame']) ? $params['frame'] : 'TEXT';
        $filename = isset($params['filename']) ? $params['filename'] : FALSE;
        $pixelPerPoint = isset($params['pixelPerPoint']) ? $params['pixelPerPoint'] : 4;
        $outerFrame = isset($params['outerFrame']) ? $params['outerFrame'] : 10;
        $saveandprint = isset($params['saveandprint']) ? $params['saveandprint'] : FALSE;
        
        if ($code == 'goods_qrcode') {
			$frame = SITE_URL . '/mobile/index.php?app=goods&id=' . $params['goods_id'];
			$filename = ROOT_PATH . '/data/files/mall/phpqrcode/' . md5($frame) . '.PNG';
			
        } 
		elseif($code == 'store_qrcode') {
			$frame = SITE_URL . '/mobile/index.php?app=store&id=' . $params['store_id'];
			$filename = ROOT_PATH . '/data/files/mall/phpqrcode/' . md5($frame) . '.PNG';
		}
		elseif($code == 'dtb_qrcode') {
			$frame = SITE_URL . '/mobile/index.php?app=store&id='.$params['store_id'].'&did=' . $params['did'];
			$filename = ROOT_PATH . '/data/files/mall/phpqrcode/' . md5($frame) . '.PNG';
		}
		elseif($code == 'refer_qrcode') {
			$frame = SITE_URL . '/mobile/index.php?app=member&act=register&r=' . $params['user_id'];
			$filename = ROOT_PATH . '/data/files/mall/phpqrcode/' . md5($frame) . '.PNG';
		}
		
		if($filename && !file_exists($filename)){
			QRcode::png($frame, $filename, $pixelPerPoint, $outerFrame, $saveandprint);
		}
		
        return str_replace(ROOT_PATH, SITE_URL, $filename);
    }
	
	function gendtbQRCode($code,$params=array())
	{
		$qr = $this->generateQRCode($code,$params);  
		$filename = basename($qr);
		$src_image = imagecreatefrompng($qr); 
		$src_w = imagesx($src_image);  
		$src_h = imagesy($src_image);
		if(in_array($code, array('dtb_qrcode')) && $src_w != 640) //如果不是微分销640宽度的图，则重新生成
		{
			$dst_image = imagecreatefrompng(ROOT_PATH . '/static/images/dtb.png');
			imagecopyresampled($dst_image , $src_image , 64 , 48 , 0 , 0 , 234 , 234 , $src_w , $src_h);
			imagepng($dst_image,ROOT_PATH . '/data/files/mall/phpqrcode/'.$filename); 
		}
		return SITE_URL . '/data/files/mall/phpqrcode/'.$filename;
	}

	/* 七日免登录 */
	function setAutoLoginCookie($user_id, $user_name, $password, $expire = 0)
	{
		/* 此参数可设置为 user_info, admin_info 分别兼容保存前台登录和后台登录的COOKIE */
		if(defined('IN_BACKEND') && IN_BACKEND === TRUE) {
			$info_key = 'admin_info';
		} else $info_key = 'user_info';
		
		if(!$expire)  $expire = strtotime(local_date('Y-m-d H:i:s', gmtime() + 3600 * 24 * 7));
		ecm_setcookie('AutoLogin_'.$info_key, json_encode(array('user_id' => $user_id, 'password' => md5($password))), $expire,  "/");
	}
	// 检查COOKIE中的密码是否正确
	static function checkAutoLoginCookie($info_key = 'user_info')
	{
		$result = FALSE;
		
		if($cookie = ecm_getcookie('AutoLogin_'.$info_key))
		{
			$cookie = json_decode(stripslashes($cookie), true);
			if(is_array($cookie))
			{
				/* 验证密码是否正确（有可能用户已经改了密码）*/
				$member_mod = &m('member');
				if($member = $member_mod->get(array('conditions' => 'user_id='.$cookie['user_id'], 'fields' => 'password'))) {
					if($member && ($member['password'] == $cookie['password'])) {
						$result = $member['user_id'];
					}
				}
			}
		}
		return $result;
	}
	
	// 手动退出后，清空免登录时效
	function clearAutoLoginCookie($info_key = 'user_info')
	{
		ecm_setcookie('AutoLogin_'.$info_key, '', strtotime(local_date('Y-m-d H:i:s', gmtime() - 1)));
		unset($_SESSION['ret_url']);// 针对登录绑定
	}
	
	function sendMailMsgNotify($order_info = array(), $mail = array(), $msg = array())
	{
		/* 发送邮件提醒 */
		if($mail) 
		{
			$touser = isset($mail['touser']) ? intval($mail['touser']) : $order_info['seller_id'];
			$model_member =& m('member');
			$info  = $model_member->get(array('conditions' => 'user_id='.$touser, 'fields' => 'email'));
			$email = get_mail($mail['key'], array('order' => $order_info));
			$this->_mailto($info['email'], addslashes($email['subject']), addslashes($email['message']));
		}

		/* 发送短信提醒 */
		if($msg)
		{
			import('sms.lib');
			$sms = new SMS();
			
			$sender = isset($msg['sender']) ? intval($msg['sender']) : $order_info['seller_id'];
			$sms->send(array('phone_mob' => $msg['phone_mob'], 'fun' => $msg['key'], 'text' => $msg['body'], 'sender' => $sender));
		}
	}
	
	//发送手机验证（目前兼容注册发送的短信，找回密码发送的短信）
	function sendcode()
	{
		if (!IS_POST)
        {
            $this->show_warning('Hacking Attempt');
			return;
        }
        else
        {
			$from 		= trim($_POST['from']);
			$phone_mob 	= trim($_POST['phone_mob']);
			$user_id 	= intval($_POST['user_id']);
			
			$member_mod = &m('member');
			
			// 如果是找回密码，则通过传递的user_id 的值，找出手机号，避免POST过程中被串改
			if(in_array($from, array('find_password')))
			{
				$member = $member_mod->get($user_id);
				if(!$member || !$member['phone_mob']){
					$this->json_error('phone_mob_no_exist');
					return;
				}
				else
				{
					// 重新赋值，不允许串改
					$phone_mob = $member['phone_mob'];
				}
			}
			
			// 如果是注册，则验证传递的手机号是否唯一
			if(in_array($from, array('register', 'member_bind')))
			{
				$member = $member_mod->get('phone_mob ="'.$phone_mob.'"');
				if($member){
					$this->json_error('phone_mob_exist');
					return;
				}
			}
			// 如果是手机短信登录，则验证传递的手机号是否存在
			if(in_array($from, array('login')))
			{
				$member = $member_mod->get('phone_mob ="'.$phone_mob.'"');
				if(!$member){
					$this->json_error('phone_mob_no_register');
					return;
				}
			}
					
			import('sms.lib');
			$sms = new SMS();
			
			if(!$sms->checkSendMsg($phone_mob)) {
				$error = current($sms->get_error());
				$this->json_error($error['msg']);
				return;
			}
			
			$code = rand(100000, 999999); // 产生6位数字验证码			
			$smsText = sprintf(Lang::get('your_check_code'),$code);
			
			$result = $sms->send(array('phone_mob' => $phone_mob, 'text' => $smsText, 'sender' => 0));
			
			$_SESSION['_sendcode_ing'] = FALSE;
			
			if ($result !== false)
			{
				unset($_SESSION['email_code'],$_SESSION['last_send_time_email_code']);
				$_SESSION['phone_code'] = md5($phone_mob.$code);
				$_SESSION['last_send_time_phone_code'] = gmtime();
				$this->json_result('','send_msg_successed');
			}
			else
			{
				$this->json_error('msg_send_failure');
			}
        }
	}
	
	// 找回密码等的发送邮件验证码
	function sendemail()
	{
		if (!IS_POST)
        {
			$this->show_warning('Hacking Attempt');
			return;
        }
        else
        {
            $from 		= trim($_POST['from']);
			$email 		= trim($_POST['email']);
			$user_id 	= intval($_POST['user_id']);
			
			$member_mod = &m('member');
			
			$member = array();
			
			// 如果是找回密码，则通过传递的user_id 的值，找出手机号，避免POST过程中被串改
			if(in_array($from, array('find_password')))
			{
				$member = $member_mod->get($user_id);
				if(!$member || !$member['email']){
					$this->json_error('email_no_exist');
					return;
				}
				else
				{
					// 重新赋值，不允许串改
					$email = $member['email'];
				}
			}
			// 如果是第三方登录绑定，则验证传递的电子邮箱是否唯一
			if(in_array($from, array('member_bind')))
			{
				$member = $member_mod->get('email ="'.$email.'"');
				if($member){
					$this->json_error('email_exist');
					return;
				}
			}
			
			$code = rand(100000, 999999); // 产生6位数字验证码		
		
			$mail = get_mail('touser_send_code', array('user' => $member, 'word' => $code));
			$mailer =& get_mailer();
			$mail_result = $mailer->send($email, addslashes($mail['subject']), addslashes($mail['message']), CHARSET, 1);
			if ($mail_result)
            {
				unset($_SESSION['phone_code'], $_SESSION['last_send_time_phone_code']);
				$_SESSION['email_code'] = md5($email.$code);
				$_SESSION['last_send_time_email_code'] = gmtime();
                $this->json_result('', 'captcha_send_succeed');
            }
            else
            {
                $this->json_error('mail_send_failure', implode("\n", $mailer->errors));
            }
       }
		
	}
	
	// 登录后跳转地址
	function getRetUrl($extra = FALSE)
	{
		if (isset($_GET['ret_url']) && !empty($_GET['ret_url'])) {
     		$ret_url = trim($_GET['ret_url']);
     	}
		elseif(isset($_POST['ret_url']) && !empty($_POST['ret_url'])) {
			$ret_url = trim($_POST['ret_url']);
		}
		elseif(isset($_SESSION['ret_url']) && !empty($_SESSION['ret_url'])) {
			$ret_url = $_SESSION['ret_url'];
		}
      	elseif(isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
           	$ret_url = $_SERVER['HTTP_REFERER'];
    	}
		
		// 没有设定跳转地址的情况下，可以选择进入首页，还是进入用户中心
        if(!$ret_url) {
			$ret_url = $extra ? SITE_URL . '/' . url('app=member') : SITE_URL;
		}
		return $ret_url;
	}
	
	function _get_bank_inc()
	{
		$bank_list = include ROOT_PATH .'/data/bank.inc.php';
		if(!is_array($bank_list) || count($bank_list)<1)
		{
			$this->show_warning('bank_inc_error');
			return;
		}
		return $bank_list;
	}
	
	/**
     *    获取插件开启状态
     *
	 */
	function _get_enabled_plugins($event, $plugin_id) 
	{
		$plugin = array();
		$plugin_inc_file = ROOT_PATH .'/data/plugins.inc.php';
		if (is_file($plugin_inc_file)) {
			$plugin = include($plugin_inc_file);
		}
		return $plugin[$event][$plugin_id];
	}
	
	/**
     *    获取插件信息
     *
     *    @param     string $id
     *    @return    array
     */
    function _get_plugin_info($id)
    {
        $plugin_info_path = ROOT_PATH . '/external/plugins/' . $id . '/plugin.info.php';

        return include($plugin_info_path);
    }
	
	// 读取某个文件夹下的文件
	function _get_exist_file($folder = '', $allpath = FALSE)
    {
        $files = array();
		$file_dir = ROOT_PATH . '/' . $folder;
		
        if (!$folder || !is_dir($file_dir))
        {
            return $files;
        }
        $dir  = dir($file_dir);
        while (false !== ($item = $dir->read()))
        {
            if (in_array($item, array('.', '..', 'index.htm', 'Thumbs.db')) || $item{0} == '.')
            {
                continue;
            }
            $files[] = ($allpath ? $file_dir : $folder) . '/' . $item;
        }

        return $files;
    }
	
    function _init_visitor()
    {
    }

    /**
     *    初始化Session
     *
     *    @author    MiMall
     *    @param    none
     *    @return    void
     */
    function _init_session()
    {
        import('session.lib');
        if(!defined('SESSION_TYPE'))
        {
           define('SESSION_TYPE','mysql');
        }
        if (SESSION_TYPE == 'mysql' || defined('IN_BACKEND'))
        {
            $this->_session = new SessionProcessor(db(), '`ecm_sessions`', '`ecm_sessions_data`', 'ECM_ID');
            /* 清理超时的购物车项目 */
            $this->_session->add_related_table('`ecm_cart`', 'cart', 'session_id', 'user_id=0');
        }
        else if (SESSION_TYPE == 'memcached')
        {
            $this->_session = new MemcacheSession(SESSION_MEMCACHED, 'ECM_ID');
        }
        else
        {
            exit('Unkown session type.');
        }
        define('SESS_ID', $this->_session->get_session_id());

        $this->_session->my_session_start();
        env('session', $this->_session);
    }
    function _config_view()
    {
        $this->_view->caching       = ((DEBUG_MODE & 1) == 0);  // 是否缓存
        $this->_view->force_compile = ((DEBUG_MODE & 2) == 2);  // 是否需要强制编译
        $this->_view->direct_output = ((DEBUG_MODE & 4) == 4);  // 是否直接输出
        $this->_view->gzip          = (defined('ENABLED_GZIP') && ENABLED_GZIP === 1);
        $this->_view->lib_base      = site_url() . '/includes/libraries/javascript';
    }

    /**
     *    转发至模块
     *
     *    @author    MiMall
     *    @param    none
     *    @return    void
     */
    function do_action($action)
    {
        /* 指定了要运行的模块则调用模块控制器 */
        (!empty($_GET['module']) && !defined('MODULE')) && $action = 'run_module';
        parent::do_action($action);
    }

    function _run_action()
    {
        /*
        if (!$this->visitor->i_can('do_action'))
        {
            if (!$this->visitor->has_login)
            {
                $this->login();
            }
            else
            {
                $this->show_warning($this->visitor->get_error());
            }

            return;
        }
        */
        if ($this->_hook('on_run_action'))
        {
            return;
        }
        parent::_run_action();

        if ($this->_hook('end_run_action'))
        {
            return;
        }
    }

    function run_module()
    {
        $module_name = empty($_REQUEST['module']) ? false : strtolower(preg_replace('/(\W+)/', '', $_REQUEST['module']));
        if (!$module_name)
        {
            $this->show_warning('no_such_module');

            return;
        }
        $file = defined('IN_BACKEND') ? 'admin' : 'index';
        $module_class_file = ROOT_PATH . '/external/modules/' . $module_name . '/' . $file . '.module.php';
        require(ROOT_PATH . '/includes/module.base.php');
        require($module_class_file);
        define('MODULE', $module_name);
        $module_class_name = ucfirst($module_name) . 'Module';

        /* 判断模块是否启用 */
        $model_module =& m('module');
        $find_data = $model_module->find('index:' . $module_name);
        if (empty($find_data))
        {
            /* 没有安装 */
            $this->show_warning('no_such_module');

            return;
        }
        $info = current($find_data);
        if (!$info['enabled'])
        {
            /* 尚未启用 */
            $this->show_warning('module_disabled');

            return;
        }

        /* 加载模块配置 */
        Conf::load(array($module_name . '_config' => unserialize($info['module_config'])));

        /* 运行模块 */
        $module = new $module_class_name();
        c($module);
        $module->do_action(ACT);
        $module->destruct();
    }


    function login()
    {
        $this->display('login.html');
    }
    function logout()
    {
        $this->visitor->logout();
    }
    function jslang($lang)
    {
        header('Content-Encoding:'.CHARSET);
        header("Content-Type: application/x-javascript\n");
        header("Expires: " .date(DATE_RFC822, strtotime("+1 hour")). "\n");
        if (!$lang)
        {
            echo 'var lang = null;';
        }
        else
        {
            echo 'var lang = ' . ecm_json_encode($lang) . ';';
            echo <<<EOT
lang.get = function(key){
    eval('var langKey = lang.' + key);
    if(typeof(langKey) == 'undefined'){
        return key;
    }else{
        return langKey;
    }
}
EOT;
        }
    }

    /**
     *    插件
     *
     *    @author    MiMall
     *    @param    none
     *    @return    void
     */
    function _hook($event, $data = array())
    {
        if ($this->outcall)
        {
            return;
        }
        static $plugins = null;
        $conf_file = ROOT_PATH . '/data/plugins.inc.php';
        if ($plugins === null)
        {
            is_file($conf_file) && $plugins = include($conf_file);
            if (!is_array($plugins))
            {
                $plugins = false;
            }
        }
        if (!isset($plugins[$event]))
        {
            return null;
        }

        /* 获取可用插件列表 */
        $plugin_list = $plugins[$event];
        if (empty($plugin_list))
        {
            return null;
        }
        foreach ($plugin_list as $plugin_name => $plugin_info)
        {
            $plugin_main_file = ROOT_PATH . "/external/plugins/{$plugin_name}/main.plugin.php";
            if (is_file($plugin_main_file))
            {
                include_once($plugin_main_file);
            }
            $plugin_class_name = ucfirst($plugin_name) . 'Plugin';
            $plugin = new $plugin_class_name($data, $plugin_info);
            $this->outcall = true;

            /* 返回一个结果，若要停止当前控制器流程则会返回true */
            $stop_flow = $this->_run_plugin($plugin);
            $plugin = null;
            $this->outcall = false;
            /* 停止原控制器流程 */
            if ($stop_flow)
            {
                return $stop_flow;
            }
        }
    }

    /**
     *    运行插件
     *
     *    @author    MiMall
     *    @param     Plugin $plugin
     *    @return    void
     */
    function _run_plugin(&$plugin)
    {
        return $plugin->execute();
    }


	/**
     *    读取插件的配置信息，跟_hook不同不需要终止原有流程。
     *
     *    @author    Cengnlaeng
     *    @param    none
     *    @return    void
     */
	 
	 function _get_plugin_conf($data)
	 {
		extract($data);
		$conf_file = ROOT_PATH . '/data/plugins.inc.php';
		if ($plugins === null)
        {
            is_file($conf_file) && $plugins = include($conf_file);
            if (!is_array($plugins))
            {
                $plugins = false;
            }
        }
        if (!isset($plugins[$event]))
        {
            return null;
        }
        $plugin_main_file = ROOT_PATH . "/external/plugins/{$name}/main.plugin.php";
		
		if (is_file($plugin_main_file))
        {
           include_once($plugin_main_file);
        }
        $plugin_class_name = ucfirst($name) . 'Plugin';
        $plugin = new $plugin_class_name($plugins[$event][$name]);
		return $plugin->_config_info();
	 }


    /**
     *    head标签内的内容
     *
     *    @author    MiMall
     *    @param     string $contents
     *    @return    void
     */
    function headtag($string)
    {
        $this->_init_view();
        $this->assign('_head_tags', $this->_view->fetch('str:' . $string));
    }

    /**
     *    导入资源到模板
     *
     *    @author    MiMall
     *    @param     mixed $resources
     *    @return    string
     */
    function import_resource($resources, $spec_type = null)
    {
        $headtag = '';
        if (is_string($resources) || $spec_type)
        {
            !$spec_type && $spec_type = 'script';
            $resources = $this->_get_resource_data($resources);
            foreach ($resources as $params)
            {
                $headtag .= $this->_get_resource_code($spec_type, $params) . "\r\n";
            }
            $this->headtag($headtag);
        }
        elseif (is_array($resources))
        {
            foreach ($resources as $type => $res)
            {
                $headtag .= $this->import_resource($res, $type);
            }
            $this->headtag($headtag);
        }

        return $headtag;
    }
    
    /**
     * 配置seo信息
     *
     * @param array/string $seo_info
     * @return void
     */
    function _config_seo($seo_info, $ext_info = null)
    {
        if (is_string($seo_info))
        {
            $this->_assign_seo($seo_info, $ext_info);
        }
        elseif (is_array($seo_info))
        {
            foreach ($seo_info as $type => $info)
            {
                $this->_assign_seo($type, $info);
            }
        }
    }
    
    function _assign_seo($type, $info)
    {
        $this->_init_view();
        $_seo_info = $this->_view->get_template_vars('_seo_info');
        if (is_array($_seo_info))
        {
            $_seo_info[$type] = $info;
        }
        else
        {
            $_seo_info = array($type => $info);
        }
        $this->assign('_seo_info', $_seo_info);
        $this->assign('page_seo', $this->_get_seo_code($_seo_info));
    }
    
    function _get_seo_code($_seo_info)
    {
        $html = '';
        foreach ($_seo_info as $type => $info)
        {
            $info = trim(htmlspecialchars($info));
            switch ($type)
            {
                case 'title' :
                    $html .= "<{$type}>{$info}</{$type}>";
                    break;
                case 'description' :
                case 'keywords' :
                default :
                    $html .= "<meta name=\"{$type}\" content=\"{$info}\" />";
                    break;
            }
            $html .= "\r\n";
        }        
        return $html;
    }

    /**
     *    获取资源数据
     *
     *    @author    MiMall
     *    @param     mixed $resources
     *    @return    array
     */
    function _get_resource_data($resources)
    {
        $return = array();
        if (is_string($resources))
        {
            $items = explode(',', $resources);
            array_walk($items, create_function('&$val, $key', '$val = trim($val);'));
            foreach ($items as $path)
            {
                $return[] = array('path' => $path, 'attr' => '');
            }
        }
        elseif (is_array($resources))
        {
            foreach ($resources as $item)
            {
                !isset($item['attr']) && $item['attr'] = '';
                $return[] = $item;
            }
        }

        return $return;
    }

    /**
     *    获取资源文件的HTML代码
     *
     *    @author    MiMall
     *    @param     string $type
     *    @param     array  $params
     *    @return    string
     */
    function _get_resource_code($type, $params)
    {
        switch ($type)
        {
            case 'script':
                $pre = '<script charset="utf-8" type="text/javascript"';
                $path= ' src="' . $this->_get_resource_url($params['path']) . '"';
                $attr= ' ' . $params['attr'];
                $tail= '></script>';
            break;
            case 'style':
                $pre = '<link rel="stylesheet" type="text/css"';
                $path= ' href="' . $this->_get_resource_url($params['path']) . '"';
                $attr= ' ' . $params['attr'];
                $tail= ' />';
            break;
        }
        $html = $pre . $path . $attr . $tail;

        return $html;
    }

    /**
     *    获取真实的资源路径
     *
     *    @author    MiMall
     *    @param     string $res
     *    @return    void
     */
    function _get_resource_url($res)
    {
        $res_par = explode(':', $res);
        $url_type = $res_par[0];
        $return  = '';
        switch ($url_type)
        {
            case 'url':
                $return = $res_par[1];
            break;
            case 'res':
                $return = '{res file="' . $res_par[1] . '"}';
            break;
            default:
                $res_path = empty($res_par[1]) ? $res : $res_par[1];
                $return = '{lib file="' . $res_path . '"}';
            break;
        }

        return $return;
    }

    function display($f)
    {
        if ($this->_hook('on_display', array('display_file' => & $f)))
        {
            return;
        }
		$this->assign('site_description', Conf::get('site_description'));
        $this->assign('site_url', SITE_URL);
        $this->assign('real_site_url', defined('IN_BACKEND') ? dirname(site_url()) : site_url());
        $this->assign('random_number', rand());

        /* 语言项 */
        $this->assign('lang', Lang::get());

        /* 用户信息 */
        $this->assign('visitor', isset($this->visitor) ? $this->visitor->info : array());

        
        $this->assign('charset', CHARSET);
        $this->assign('price_format', Conf::get('price_format'));
        $this->assign('async_sendmail', $this->_async_sendmail());
        $this->_assign_query_info();
		
		if(isset($_GET['r']) && intval($_GET['r']))
		{
			$_SESSION['referid'] = intval($_GET['r']);
		}

        parent::display($f);

        if ($this->_hook('end_display', array('display_file' => & $f)))
        {
            return;
        }
    }

    /* 页面查询信息 */
    function _assign_query_info()
    {
        $query_time = ecm_microtime() - START_TIME;

        $this->assign('query_time', $query_time);
        $db =& db();
        $this->assign('query_count', $db->_query_count);
        $this->assign('query_user_count', $this->_session->get_users_count());

        /* 内存占用情况 */
        if (function_exists('memory_get_usage'))
        {
            $this->assign('memory_info', memory_get_usage() / 1048576);
        }

        $this->assign('gzip_enabled', $this->gzip_enabled());
        $this->assign('site_domain', urlencode(get_domain()));
        $this->assign('ecm_version', VERSION . ' ' . RELEASE);
    }

    function gzip_enabled()
    {
        static $enabled_gzip = NULL;

        if ($enabled_gzip === NULL)
        {
            $enabled_gzip = (defined('ENABLED_GZIP') && ENABLED_GZIP === 1 && function_exists('ob_gzhandler'));
        }

        return $enabled_gzip;
    }

    /**
     *    显示错误警告
     *
     *    @author    MiMall
     *    @param    none
     *    @return    void
     */
    function show_warning()
    {
        $args = func_get_args();
        call_user_func_array('show_warning', $args);
    }


    /**
     *    显示提示消息
     *
     *    @author    MiMall
     *    @return    void
     */
    function show_message()
    {
        $args = func_get_args();
        call_user_func_array('show_message', $args);
    }
    /**
     * Make a error message by JSON format
     *
     * @param   string  $msg
     *
     * @return  void
     */
    function json_error ($msg='', $retval=null, $jqremote = false)
    {
        if (!empty($msg))
        {
			// 兼容处理 mod->get_error()
			if(!is_string($msg)) {
				$error = current($msg);
				if(isset($error['msg'])) {
					$msg = $error['msg'];
				} else $msg = json_encode($error);
			}
            $msg = Lang::get($msg);
        }
        $result = array('done' => false , 'msg' => $msg);
        if (isset($retval)) $result['retval'] = $retval;

        $this->json_header();
        $json = ecm_json_encode($result);
        if ($jqremote === false)
        {
            $jqremote = isset($_GET['jsoncallback']) ? trim($_GET['jsoncallback']) : false;
        }
        if ($jqremote)
        {
            $json = $jqremote . '(' . $json . ')';
        }

        echo $json;
    }

    /**
     * Make a successfully message
     *
     * @param   mixed   $retval
     * @param   string  $msg
     *
     * @return  void
     */
    function json_result ($retval = '', $msg = '', $jqremote = false)
    {
        if (!empty($msg))
        {
            $msg = Lang::get($msg);
        }
        $this->json_header();
        $json = ecm_json_encode(array('done' => true , 'msg' => $msg , 'retval' => $retval));
        if ($jqremote === false)
        {
            $jqremote = isset($_GET['jsoncallback']) ? trim($_GET['jsoncallback']) : false;
        }
        if ($jqremote)
        {
            $json = $jqremote . '(' . $json . ')';
        }

        echo $json;
    }

    /**
     * Send a Header
     *
     * @author weberliu
     *
     * @return  void
     */
    function json_header()
    {
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
        header("Content-type:text/plain;charset=" . CHARSET, true);
    }

    /**
     *    验证码
     *
     *    @author    MiMall
     *    @return    void
     */
    function _captcha($width, $height)
    {
        import('captcha.lib');
        $word = generate_code();
        $_SESSION['captcha'] = base64_encode($word);
        $code = new Captcha(array(
            'width' => $width,
            'height'=> $height,
        ));
        $code->display($word);
    }

    /**
     *    获取分页信息
     *
     *    @author    MiMall
     *    @return    array
     */
    function _get_page($page_per = 10)
    {
        $page = empty($_REQUEST['page']) ? 1 : intval($_REQUEST['page']);
        $start = ($page -1) * (intval($page_per) > 0 ? intval($page_per) : 10);

        return array('limit' => "{$start},{$page_per}", 'curr_page' => $page, 'pageper' => $page_per);
    }

    /**
     * 格式化分页信息
     * @param   array   $page
     * @param   int     $num    显示几页的链接
     */
    function _format_page(&$page, $num = 7)
    {
        $page['page_count'] = ceil($page['item_count'] / $page['pageper']);
        $mid = ceil($num / 2) - 1;
        if ($page['page_count'] <= $num)
        {
            $from = 1;
            $to   = $page['page_count'];
        }
        else
        {
            $from = $page['curr_page'] <= $mid ? 1 : $page['curr_page'] - $mid + 1;
            $to   = $from + $num - 1;
            $to > $page['page_count'] && $to = $page['page_count'];
        }

        /*
        if (preg_match('/[&|\?]?page=\w+/i', $_SERVER['REQUEST_URI']) > 0)
        {
            $url_format = preg_replace('/[&|\?]?page=\w+/i', '', $_SERVER['REQUEST_URI']);
        }
        else
        {
            $url_format = $_SERVER['REQUEST_URI'];
        }
        */

        /* 生成app=goods&act=view之类的URL */
        if (preg_match('/[&|\?]?page=\w+/i', $_SERVER['QUERY_STRING']) > 0)
        {
            $url_format = preg_replace('/[&|\?]?page=\w+/i', '', $_SERVER['QUERY_STRING']);
        }
        else
        {
            $url_format = $_SERVER['QUERY_STRING'];
        }

        $page['page_links'] = array();
        $page['first_link'] = ''; // 首页链接        
        $page['first_suspen'] = ''; // 首页省略号
        $page['last_link'] = ''; // 尾页链接
        $page['last_suspen'] = ''; // 尾页省略号
        for ($i = $from; $i <= $to; $i++)
        {
            $page['page_links'][$i] = url("{$url_format}&page={$i}");
        }
        if (($page['curr_page'] - $from) < ($page['curr_page'] -1) && $page['page_count'] > $num)
        {
            $page['first_link'] = url("{$url_format}&page=1");
            if (($page['curr_page'] -1) - ($page['curr_page'] - $from) != 1)
            {
                $page['first_suspen'] = '..';
            }
        }
        if (($to - $page['curr_page']) < ($page['page_count'] - $page['curr_page']) && $page['page_count'] > $num)
        {
            $page['last_link'] = url("{$url_format}&page=" . $page['page_count'],null,true);
            if (($page['page_count'] - $page['curr_page']) - ($to - $page['curr_page']) != 1)
            {
                $page['last_suspen'] = '..';
            }
        }

        if($page['curr_page'] > $from)
		{
			$page['prev_page'] = $page['curr_page'] - 1;
			$page['prev_link'] = url("{$url_format}&page=" . ($page['curr_page'] - 1), NULL, true);
		}
		if($page['curr_page'] < $to)
		{ 
			$page['next_page'] = $page['curr_page'] + 1;
			$page['next_link'] = url("{$url_format}&page=" . ($page['curr_page'] + 1), NULL, true);
		}
    }

    /**
     *    获取查询条件
     *
     *    @author    MiMall
     *    @param    none
     *    @return    void
     */
    function _get_query_conditions($query_item){
        $str = '';
        $query = array();
        foreach ($query_item as $options)
        {
            if (is_string($options))
            {
                $field = $options;
                $options['field'] = $field;
                $options['name']  = $field;
            }
            !isset($options['equal']) && $options['equal'] = '=';
            !isset($options['assoc']) && $options['assoc'] = 'AND';
            !isset($options['type'])  && $options['type']  = 'string';
            !isset($options['name'])  && $options['name']  = $options['field'];
            !isset($options['handler']) && $options['handler'] = 'trim';
            if (isset($_GET[$options['name']]))
            {
                $input = $_GET[$options['name']];
                $handler = $options['handler'];
                $value = ($input == '' ? $input : $handler($input));
                if ($value === '' || $value === false)  //若未输入，未选择，或者经过$handler处理失败就跳过
                {
                    continue;
                }
                strtoupper($options['equal']) == 'LIKE' && $value = "%{$value}%";
                if ($options['type'] != 'numeric')
                {
                    $value = "'{$value}'";      //加上单引号，安全第一
                }
                else
                {
                    $value = floatval($value);  //安全起见，将其转换成浮点型
                }
                $str .= " {$options['assoc']} {$options['field']} {$options['equal']} {$value}";
                $query[$options['name']] = $input;
            }
        }
        $this->assign('query', stripslashes_deep($query));

        return $str;
    }
	
	function flexigridXML($flexigridXML)
	{
		$page = $flexigridXML['now_page'];
		$total = $flexigridXML['total_num'];
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
		header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" ); 
		header("Cache-Control: no-cache, must-revalidate" ); 
		header("Pragma: no-cache" );
		header("Content-type: text/xml");
		$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		$xml .= "<rows>";
		$xml .= "<page>$page</page>";
		$xml .= "<total>$total</total>";
		if(empty($flexigridXML['list'])){
			$xml .= "<row id=''>";
			$xml .= "<cell></cell>";
			$xml .= "</row>";	
			}else{
			foreach ($flexigridXML['list'] as $k => $v){
				$xml .= "<row id='".$k."'>";
			   foreach ($v as $kk => $vv){
					$xml .= "<cell><![CDATA[".$v[$kk]."]]></cell>";
					}
			$xml .= "</row>";	
			}
		}
		$xml .= "</rows>";
		echo $xml;
	}
	
    /**
     *    使用编辑器
     *
     *    @author    MiMall
     *    @param     array $params
     *    @return    string
     */
    function _build_editor($params = array())
    {
        $name = isset($params['name']) ?  $params['name'] : 'description';
        $theme = isset($params['theme']) ?  $params['theme'] : 'simple';
        $ext_js = isset($params['ext_js']) ? $params['ext_js'] : true;
        $content_css = isset($params['content_css']) ? 'content_css:"' . $params['content_css'] . '",' : null;
        $if_media = false;
        $visit = $this->visitor->get('manage_store');
        $store_id = isset($visit) ? intval($visit) : 0;
        $privs = $this->visitor->get('privs');
        if (!empty($privs))
        {
            if ($privs == 'all')
            {
                $if_media = true;
            }
            else
            {
                $privs_array = explode(',', $privs);
                if (in_array('article|all', $privs_array))
                {
                    $if_media = true;
                }
            }
        }
        if (!empty($store_id) && !$if_media)
        {
            $store_mod =& m('store');
            $store = $store_mod->get_info($store_id);
            $sgrade_mod =& m('sgrade') ;
            $sgrade = $sgrade_mod->get_info($store['sgrade']);
            $functions = explode(',', $sgrade['functions']);
            if (in_array('editor_multimedia', $functions))
            {
                $if_media = true;
            }
        }
		
		/* 指定使用哪种主题 */
		$themes = array(
			'default' => "'source','|','undo','redo','|','preview','print','template'".($if_media ? ",'media' ":'').",'plainpaste','wordpaste','|','justifyleft','justifycenter','justifyright','justifyfull','insertorderedlist','insertunorderedlist','indent','outdent','subscript','superscript','clearhtml','quickformat','selectall','|','fullscreen','/','formatblock','fontname','fontsize','|','forecolor','hilitecolor','bold','italic','underline','strikethrough','lineheight','removeformat','|','table','hr','emoticons','baidumap','anchor','link','unlink','|','about'",
			'simple' => "'source','|','formatblock','fontname','fontsize','|','forecolor','hilitecolor','bold','italic','underline','removeformat','|','justifyleft','justifycenter','justifyright','insertorderedlist','insertunorderedlist','|','emoticons','image','link','template'".($if_media ? ",'media' ":'')."",
		);
		
        switch ($theme)
        {
            case 'simple':
                $theme_config = $themes['simple'];
            break;
            case 'default':
                $theme_config = $themes['default'];
            break;
            default:
                $theme_config = $themes['default'];
            break;
        }
        /* 配置界面语言 */
        $_lang = substr(LANG, 0, 2);
        switch ($_lang)
        {
            case 'sc':
                $lang = 'zh_CN';
            break;
            case 'tc':
                $lang = 'zh_TW';
            break;
            case 'en':
                $lang = 'en';
            break;
            default:
                $lang = 'zh_CN';
            break;
        }
		$lang_file = 'kindeditor/lang/'.$lang.'.js';
		
		$include_js = $ext_js ? '<script type="text/javascript" src="{lib file="kindeditor/kindeditor-min.js"}"></script><script charset="utf-8" src="{lib file="'.$lang_file.'"}"></script>' : '';

$str = <<<EOT
$include_js
<script>
	KindEditor.ready(function(K) {
		{$name}editor = K.create('textarea[name="{$name}"]', {
			themeType : '{$theme}',
			items : [$theme_config],
			allowImageUpload : false,
			allowFlashUpload : false,
			allowMediaUpload : false,
			allowFileUpload  : false,
			allowFileManager : false,
			afterBlur: function(){
				this.sync();
			}
		});
		/* 兼容同一个页面存在多个编辑的情况下，插入图片到编辑器的问题 */
		$('.J_{$name}editor').on('click', '*[ectype="insert_editor"]', function() {
			handle_pic = $(this).parents('*[ectype="handle_pic"]');
			html = '<img src="'+ SITE_URL +'/' + handle_pic.attr("file_path") + '" alt="' + handle_pic.attr("file_name") + '">';
			{$name}editor.insertHtml(html);
		});
	});
</script>

EOT;

        return $this->_view->fetch('str:' . $str);;
    
    }

    /**
     *    使用swfupload
     *
     *    @author    Hyber
     *    @param     array $params
     *    @return    string
     */
    function _build_upload($params = array())
    {
        $belong = isset($params['belong']) ? $params['belong'] : 0; //上传文件所属模型
        $item_id = isset($params['item_id']) ? $params['item_id']: 0; //所属模型的ID
        $file_size_limit = isset($params['file_size_limit']) ? $params['file_size_limit']: '10 MB'; //默认最大2M
        $button_text = isset($params['button_text']) ? Lang::get($params['button_text']) : Lang::get('bat_upload'); //上传按钮文本
        $image_file_type = isset($params['image_file_type']) ? $params['image_file_type'] : IMAGE_FILE_TYPE;
        $upload_url = isset($params['upload_url']) ? $params['upload_url'] : 'index.php?app=swfupload';
        $button_id = isset($params['button_id']) ? $params['button_id'] : 'spanButtonPlaceholder';
        $progress_id = isset($params['progress_id']) ? $params['progress_id'] : 'divFileProgressContainer';
        $if_multirow = isset($params['if_multirow']) ? $params['if_multirow'] : 1;
        !isset($params['obj']) && $params['obj'] = 'WebUpload';
        $obj    = $params['obj'];
        $define = isset($params['obj']) ? 'var ' . $params['obj'] . ';' : '';
        $assign = isset($params['obj']) ? $params['obj'] . ' = ' : '';
        $ext_js = isset($params['ext_js']) ? $params['ext_js'] : true;
        $ext_css = isset($params['ext_css']) ? $params['ext_css'] : true;

        $include_js = $ext_js ? '<script type="text/javascript" charset="utf-8" src="{lib file="webuploader/webuploader.js"}"></script>
<script type="text/javascript" charset="utf-8" src="{lib file="webuploader/js/handlers.js"}"></script>' : '';
        $include_css = $ext_css ? '<link type="text/css" rel="stylesheet" href="{lib file="webuploader/webuploader.css"}"/>' : '';
        /* 允许类型 */
        $file_types = '';
        $image_file_type = explode('|', $image_file_type);
        foreach ($image_file_type as $type)
        {
            $file_types .=  $type . ',';
        }
        $file_types = trim($file_types, ',');
        $str = <<<EOT

{$include_js}
{$include_css}
<script type="text/javascript">
{$define}
$(function(){

    {$assign}WebUploader.create({
    	auto: true,
        server: "{$upload_url}",
        swf: "{lib file="webuploader/Uploader.swf"}",
        formData: {
            "ECM_ID": "{$_COOKIE['ECM_ID']}",
            "HTTP_USER_AGENT":"{$_SERVER['HTTP_USER_AGENT']}",
            'belong': {$belong},
            'item_id': {$item_id},
            'ajax': 1
        },
        
        // 只允许选择图片文件。
    	accept: {
        	title: 'Images',
        	extensions: '{$file_types}',
        	//mimeTypes: 'image/*' // google浏览器下点击弹窗慢
            mimeTypes: 'image/jpg,image/jpeg,image/png'
    	},
        
        // 禁掉全局的拖拽功能。这样不会出现图片拖进页面的时候，把图片打开。
 		disableGlobalDnd: true,
    	fileNumLimit: 300,
        fileSizeLimit: 200 * 1024 * 1024,    // 200 M
        fileSingleSizeLimit: 50 * 1024 * 1024,    // 50 M
    	duplicate: true,//可以重复上传 但是筛选不了重复图片 根据文件名字、文件大小和最后修改时间来生成hash Key
        fileVal: 'Filedata',
        pick: {
 			id: ".{$obj}_filePicker",
   			label: '批量上传',
            multiple: {$if_multirow}
		}
    });
    $obj.on( 'fileQueued', function( file ) {
    	fileQueued(file, '{$progress_id}');
    });
    $obj.on( 'uploadProgress', function( file, percentage ) {
    	uploadProgress(file, percentage);
    });
    $obj.on( 'uploadSuccess', function( file, response) {
		uploadSuccess(file, response);
	});
    $obj.on( 'uploadError', function( file ) {
    	uploadError(file);
    });
    $obj.on( 'uploadComplete', function( file ) {
    	uploadComplete(file);
    });
    $obj.on( 'uploadFinished', function() {
    	uploadFinished('{$progress_id}');
    });
});
</script>
EOT;
        return $this->_view->fetch('str:' . $str);
    }

    /**
     *    发送邮件
     *
     *    @author    MiMall
     *    @param     mixed  $to
     *    @param     string $subject
     *    @param     string $message
     *    @param     int    $priority
     *    @return    void
     */
    function _mailto($to, $subject, $message, $priority = MAIL_PRIORITY_LOW)
    {
        /* 加入邮件队列，并通知需要发送 */
        $model_mailqueue =& m('mailqueue');
        $mails = array();
        $to_emails = is_array($to) ? $to : array($to);
        foreach ($to_emails as $_to)
        {
            $mails[] = array(
                'mail_to'       => $_to,
                'mail_encoding' => CHARSET,
                'mail_subject'  => $subject,
                'mail_body'     => $message,
                'priority'      => $priority,
                'add_time'      => gmtime(),
            );
        }

        $model_mailqueue->add($mails);

        /* 默认采用异步发送邮件，这样可以解决响应缓慢的问题 */
        $this->_sendmail();
    }

    /**
     *    发送邮件
     *
     *    @author    MiMall
     *    @param     bool $is_sync
     *    @return    void
     */
    function _sendmail($is_sync = false)
    {
        if (!$is_sync)
        {
            /* 采用异步方式发送邮件，与模板引擎配合达到目的 */
            $_SESSION['ASYNC_SENDMAIL'] = true;

            return true;
        }
        else
        {
            /* 同步发送邮件，将异步发送的命令去掉 */
            unset($_SESSION['ASYNC_SENDMAIL']);
            $model_mailqueue =& m('mailqueue');

            return $model_mailqueue->send(5);
        }
    }

    /**
     *     获取异步发送邮件代码
     *
     *    @author    MiMall
     *    @return    string
     */
    function _async_sendmail()
    {
        $script = '';
        if (isset($_SESSION['ASYNC_SENDMAIL']) && $_SESSION['ASYNC_SENDMAIL'])
        {
            /* 需要异步发送 */
            $async_sendmail = SITE_URL . '/index.php?app=sendmail';
            $script = '<script type="text/javascript">sendmail("' . $async_sendmail . '");</script>';
        }

        return $script;
    }
    function _get_new_message()
    {
        $user_id = $this->visitor->get('user_id');
        if(empty($user_id))
        {
            return '';
        }
        $ms =& ms();
        return $ms->pm->check_new($user_id);
    }

    /**
     *    计划任务守护进程
     *
     *    @author    MiMall
     *    @return    void
     */
    function _run_cron()
    {

        register_shutdown_function(create_function('', '
            /*if (ob_get_level() > 0)
            {
                ob_end_flush();         //输出
            }*/
            if (!is_file(ROOT_PATH . "/data/tasks.inc.php"))
            {
                $default_tasks = array(
                    "cleanup" =>
                        array (
                            "cycle" => "custom",
                            "interval" => 3600,    
                        ),
                     "shortcleanup" =>
                        array (
                            "cycle" => "custom",
                            "interval" => 300,     //每五分钟执行一次清理
                       ),
                );
                file_put_contents(ROOT_PATH . "/data/tasks.inc.php", "<?php\r\n\r\nreturn " . var_export($default_tasks, true) . ";\r\n\r\n", LOCK_EX);
            }
            import("cron.lib");
            $cron = new Crond(array(
                "task_list" => ROOT_PATH . "/data/tasks.inc.php",
                "task_path" => ROOT_PATH . "/includes/tasks",
                "lock_file" => ROOT_PATH . "/data/crond.lock"
            ));                     //计划任务实例
            $cron->execute();       //执行
        '));
    }

    /**
     * 发送Feed
     *
     * @author MiMall
     * @param
     * @return void
     **/
    function send_feed($event, $data)
    {
        $ms = &ms();
        if (!$ms->feed->feed_enabled())
        {
            return;
        }

        $feed_config = $this->visitor->get('feed_config');
        $feed_config = empty($feed_config) ? Conf::get('default_feed_config') : unserialize($feed_config);
        if (!$feed_config[$event])
        {
            return;
        }

        $ms->feed->add($event, $data);
    }

}

/**
 *    访问者基础类，集合了当前访问用户的操作
 *
 *    @author    MiMall
 *    @return    void
 */
class BaseVisitor extends Object
{
    var $has_login = false;
    var $info      = null;
    var $privilege = null;
    var $_info_key = '';
    function __construct()
    {
        $this->BaseVisitor();
    }
    function BaseVisitor()
    {
        if (!empty($_SESSION[$this->_info_key]['user_id']))
        {
            $this->info         = $_SESSION[$this->_info_key];
            $this->has_login    = true;
        }
		elseif($user_id = ECBaseApp::checkAutoLoginCookie($this->_info_key))
		{
			$mod_user =& m('member');

        	$user_info = $mod_user->get(array(
            	'conditions'    => "user_id = '{$user_id}'",
            	'join'          => 'has_store',                 //关联查找看看是否有店铺
            	'fields'        => 'user_id, user_name, reg_time, last_login, last_ip, store_id',
        	));
		
			$this->info         = $_SESSION[$this->_info_key] = $user_info;
            $this->has_login    = true;
		}
        else
        {
            $this->info         = array(
                'user_id'   => 0,
                'user_name' => Lang::get('guest')
            );
            $this->has_login    = false;
        }
    }
    function assign($user_info)
    {
        $_SESSION[$this->_info_key]   =   $user_info;
    }

    /**
     *    获取当前登录用户的详细信息
     *
     *    @author    MiMall
     *    @return    array      用户的详细信息
     */
    function get_detail()
    {
        /* 未登录，则无详细信息 */
        if (!$this->has_login)
        {
            return array();
        }

        /* 取出详细信息 */
        static $detail = null;

        if ($detail === null)
        {
            $detail = $this->_get_detail();
        }

        return $detail;
    }

    /**
     *    获取用户详细信息
     *
     *    @author    MiMall
     *    @return    array
     */
    function _get_detail()
    {
        $model_member =& m('member');

        /* 获取当前用户的详细信息，包括权限 */
        $member_info = $model_member->findAll(array(
            'conditions'    => "member.user_id = '{$this->info['user_id']}'",
            'join'          => 'has_store',                 //关联查找看看是否有店铺
            'fields'        => 'email, password, real_name, logins, ugrade, portrait, store_id, state, sgrade , feed_config',
            'include'       => array(                       //找出所有该用户管理的店铺
                'manage_store'  =>  array(
                    'fields'    =>  'user_priv.privs, store.store_name',
                ),
            ),
        ));
        $detail = current($member_info);

        /* 如果拥有店铺，则默认管理的店铺为自己的店铺，否则需要用户自行指定 */
        if ($detail['store_id'] && $detail['state'] != STORE_APPLYING) // 排除申请中的店铺
        {
            $detail['manage_store'] = $detail['has_store'] = $detail['store_id'];
        }

        return $detail;
    }

    /**
     *    获取当前用户的指定信息
     *
     *    @author    MiMall
     *    @param     string $key  指定用户信息
     *    @return    string  如果值是字符串的话
     *               array   如果是数组的话
     */
    function get($key = null)
    {
        $info = null;

        if (empty($key))
        {
            /* 未指定key，则返回当前用户的所有信息：基础信息＋详细信息 */
            $info = array_merge((array)$this->info, (array)$this->get_detail());
        }
        else
        {
            /* 指定了key，则返回指定的信息 */
            if (isset($this->info[$key]))
            {
                /* 优先查找基础数据 */
                $info = $this->info[$key];
            }
            else
            {
                /* 若基础数据中没有，则查询详细数据 */
                $detail = $this->get_detail();
                $info = isset($detail[$key]) ? $detail[$key] : null;
            }
        }

        return $info;
    }

    /**
     *    登出
     *
     *    @author    MiMall
     *    @return    void
     */
    function logout()
    {
        unset($_SESSION[$this->_info_key]);
    }
    function i_can($event, $privileges = array())
    {
        $fun_name = 'check_' . $event;

        return $this->$fun_name($privileges);
    }

    function check_do_action($privileges)
    {
        $mp = APP . '|' . ACT;

        if ($privileges == 'all')
        {
            /* 拥有所有权限 */
            return true;
        }
        else
        {
            /* 查看当前操作是否在白名单中，如果在，则允许，否则不允许 */
            $privs = explode(',', $privileges);
            if (in_array(APP . '|all', $privs) || in_array($mp, $privs))
            {
                return true;
            }

            return false;
        }
    }

}
?>
