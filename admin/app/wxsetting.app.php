<?php

class WxsettingApp extends BackendApp
{
	var $weixin_config_mod;

    function __construct()
    {
        $this->WxsettingApp();
    }

    function WxsettingApp()
    {
        parent::__construct();
        $this->weixin_config_mod = & m('weixin_config');
    }
	
	function index()
    {
        if (!IS_POST)
        {
			$weixin = $this->weixin_config_mod->get('user_id=0');
            if (empty($weixin['token'])) {
				$weixin['token'] = $this->weixin_config_mod->generate_token();
            }
            $weixin['url'] = SITE_URL . '/mobile/index.php?app=weixin';
            $this->assign('weixin', $weixin);
            $this->import_resource('jquery.plugins/jquery.validate.js');
            $this->display('wxsetting.index.html');
        }
        else
        {
			$data = array(
                'user_id' 	=> 0,
				'name'		=> trim($_POST['name']),
				'appid' 	=> html_script($_POST['appid']),
                'appsecret' => html_script($_POST['appsecret']),
                'token' 	=> html_script($_POST['token']),
				'if_valid' 	=> 0,
				'auto_login' => intval($_POST['auto_login']),
            );
            if ($weixin = $this->weixin_config_mod->get('user_id=0'))
			{
                $this->weixin_config_mod->edit($weixin['id'], $data);
            }
			else
			{
                $this->weixin_config_mod->add($data);
            }
			
			if($this->weixin_config_mod->has_error()) 
			{
				$error = current($this->weixin_config_mod->get_error());
                $this->json_error($error['msg']);
                return;
            }

            $this->json_result(array('rel'=>1),'config_successed');
        }
    }
}

?>
