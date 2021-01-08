<?php

/**
 *    主题设置控制器
 *
 *    @author    MiMall
 *    @usage    none
 */
class ThemeApp extends BackendApp
{
    /* 列表 */
    function index()
    {
		$type = isset($_GET['type']) ? $_GET['type'] : '';
        $themes = list_template('mall',$type);
        $theme_list = array();
        foreach ($themes as $theme)
        {
            $theme_list[$theme] = list_style('mall', $theme, $type);
        }
        $this->assign('curr_template_name', ($type == 'mobile') ? Conf::get('wap_template_name') : Conf::get('template_name'));
        $this->assign('curr_style_name', ($type == 'mobile') ? Conf::get('wap_style_name') : Conf::get('style_name'));
        $this->assign('theme_list', $theme_list);

        $this->display('theme.index.html');
    }
	
    function set()
    {
        $template_name = isset($_GET['template_name']) ? trim($_GET['template_name']) : null;
        $style_name = isset($_GET['style_name']) ? trim($_GET['style_name']) : null;
        if (!$template_name)
        {
            $this->json_error('no_such_template');

            return;
        }
        if (!$style_name)
        {
            $this->json_error('no_such_style');

            return;
        }
        $af_setting =& af('settings');
		if($_GET['type'] == 'mobile')
		{
			$data = array('wap_template_name' => $template_name, 'wap_style_name' => $style_name);
		}
		else
		{
			$data = array('template_name' => $template_name, 'style_name' => $style_name);
		}
        $af_setting->setAll($data);

        $this->json_result('','set_theme_successed');
    }
    function preview()
    {
        $template_name = isset($_POST['template_name']) ? trim($_POST['template_name']) : null;
        $style_name = isset($_POST['style_name']) ? trim($_POST['style_name']) : null;
        if (!$template_name)
        {
            $this->show_warning('no_such_template');

            return;
        }
        if (!$style_name)
        {
            $this->show_warning('no_such_style');

            return;
        }
        header('Location:' . SITE_URL .'/'.trim($_POST['type']). '/themes/mall/' .  $template_name . '/styles/' . $style_name . '/screenshot.jpg');
    }
}

?>