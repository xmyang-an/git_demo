<?php

/**
 *    主题设置控制器
 *
 *    @author    MiMall
 *    @usage    none
 */
class My_themeApp extends StoreadminbaseApp
{
    function index()
    {
        extract($this->_get_themes());

        if (empty($themes))
        {
            $this->show_warning('no_themes');

            return;
        }

        /* 当前位置 */
        $this->_curlocal(LANG::get('theme_list'));

        /* 当前用户中心菜单 */
        $this->_curitem('my_theme');
        $this->_curmenu('theme_config');
        $this->assign('themes', $themes);
        $this->assign('curr_template_name', $curr_template_name);
        $this->assign('curr_style_name', $curr_style_name);
        $this->assign('manage_store', $this->visitor->get('manage_store'));
        $this->assign('id',$this->visitor->get('user_id'));
        $this->import_resource(array(
            'script' => array(
                    array(
                    	'path' => 'dialog/dialog.js',
                    	'attr' => 'id="dialog_js"',
               	 	),
                	array(
                    	'path' => 'jquery.ui/jquery.ui.js',
                    	'attr' => '',
                	),
                	array(
                    	'path' => 'jquery.ui/i18n/' . i18n_code() . '.js',
                    	'attr' => '',
                	)
            	),
            	'style' =>  'jquery.ui/themes/smoothness/jquery.ui.css',
        ));
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('my_theme'));
        $this->display('my_theme.index.html');
    }
	
    function set()
    {
        $template_name = isset($_GET['template_name']) ? trim($_GET['template_name']) : null;
        $style_name = isset($_GET['style_name']) ? trim($_GET['style_name']) : null;
		$type = isset($_GET['type']) ? trim($_GET['type']) : null;
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
        extract($this->_get_themes($type));
        $theme = $template_name . '|' . $style_name;

        /* 检查是否可以选择此主题 */
        if (!isset($themes[$theme]))
        {
            $this->json_error('no_such_theme');

            return;
        }
        $model_store =& m('store');
		if($type == 'mobile')
		{
			$data = array('wap_theme' => $theme);
		}
		else
		{
			$data = array('theme' => $theme);
		}
        $model_store->edit($this->visitor->get('manage_store'),$data);

        $this->json_result('', 'set_theme_successed');
    }

    function _get_themes($wap=false)
    {
        /* 获取当前所使用的风格 */
        $model_store =& m('store');
        $store_info  = $model_store->get($this->visitor->get('manage_store'));
        $theme = !empty($store_info['theme']) ? $store_info['theme'] : 'default|default';
		$wap_theme = !empty($store_info['wap_theme']) ? $store_info['wap_theme'] : 'default|default';

        /* 获取待选主题列表 */
        $model_grade =& m('sgrade');
        $grade_info  =  $model_grade->get($store_info['sgrade']);
		if($wap == true)
		{
			list($curr_template_name, $curr_style_name) = explode('|', $wap_theme);
        	$grade_info['wap_skins'] && $skins = explode(',', $grade_info['wap_skins']);
		}
		else
		{
			list($curr_template_name, $curr_style_name) = explode('|', $theme);
			$grade_info['skins'] && $skins = explode(',', $grade_info['skins']);
		}
        $themes = array();
		if(!empty($skins))
		{
			foreach ($skins as $skin)
			{
				list($template_name, $style_name) = explode('|', $skin);
				$themes[$skin] = array('template_name' => $template_name, 'style_name' => $style_name);
			}
		}
		else
		{
			$themes['default|default'] = array('template_name' => 'default', 'style_name' => 'default');
		}

        return array(
            'curr_template_name' => $curr_template_name,
            'curr_style_name'    => $curr_style_name,
            'themes'             => $themes
        );
    }
	/*三级菜单*/
    function _get_member_submenu()
    {
        $menus = array(
            array(
                'name' => 'theme_config',
                'url'  => 'index.php?app=my_theme',
            )
        );
        return $menus;
    }
}
?>