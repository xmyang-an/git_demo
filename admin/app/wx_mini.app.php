<?php

define('UPLOAD_DIR', 'data/files/mall/wx_mini');

class Wx_miniApp extends BackendApp
{
    function __construct()
    {
        $this->Wx_miniApp();
    }

    function Wx_miniApp()
    {
        parent::BackendApp();
        $_POST = stripslashes_deep($_POST);
    }

    function index()
    {
        $config_mod = &af('wx_mini');
        $setting = $config_mod->getAll(); //载入系统设置数据
        if (!IS_POST)
        {
            $this->assign('setting', $setting);

            $this->display('wx_mini.index.html');
        }
        else
        {
            $data['hot_search']     = $_POST['hot_search'];
			$data['enable_city']     = $_POST['enable_city'];
			$data['hide_module']     = $_POST['hide_module'];
            $config_mod->setAll($data);

            $this->json_result(array('rel'=>1),'设置成功');
        }
	}
}

?>
