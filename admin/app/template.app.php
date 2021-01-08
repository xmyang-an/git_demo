<?php

/* 挂件基础类 */
include(ROOT_PATH . '/includes/widget.base.php');

/**
 *    模板编辑控制器
 *
 *    @usage    none
 */
class TemplateApp extends BackendApp
{
    /* 可编辑的页面列表 */
    function index()
    { 
		list($page, $client, $template_name) = $this->_get_client_params();
		
		$this->assign('client',  $client);
        $this->assign('pages', $this->_get_editable_pages());
        $this->display('template.index.html');
    }

    /**
     *    编辑页面
     *

     *    @return    void
     */
    function edit()
    {
        /* 当前所编辑的页面 */
        list($page, $client, $template_name) = $this->_get_client_params();
		
        if (!$page)
        {
            $this->show_warning('no_such_page');

            return;
        }

        /* 注意，通过这种方式获取的页面中跟用户相关的数据都是游客，这样就保证了统一性，所见即所得编辑不会因为您是否已登录而出现不同 */
        $html = $this->_get_page_html($page);
        if (!$html)
        {
            $this->show_warning('no_such_page');

            return;
        }
        /* 让页面可编辑 */
        $html = $this->_make_editable($page, $html, $client);

        echo $html;
    }

    /**
     *    保存编辑的页面
     *

     *    @return    void
     */
    function save()
    {
        /* 初始化变量 */
        /* 页面中所有的挂件id=>name */
        $widgets = !empty($_POST['widgets']) ? $_POST['widgets'] : array();

        /* 页面中所有挂件的位置配置数据 */
        $config  = !empty($_POST['config']) ? $_POST['config'] : array();

        /* 当前所编辑的页面 */
        list($page, $client, $template_name) = $this->_get_client_params();
		
        if (!$page)
        {
            $this->json_error('no_such_page');

            return;
        }
        $editable_pages = $this->_get_editable_pages();
        if (empty($editable_pages[$page]))
        {
            $this->json_error('no_such_page');

            return;
        }

        $page_config = get_widget_config($template_name, $page, $client);

        /* 写入位置配置信息 */
        $page_config['config'] = $config;

        /* 原始挂件信息 */
        $old_widgets = $page_config['widgets'];

        /* 清空原始挂件信息 */
        $page_config['widgets']  = array();

        /* 写入挂件信息,指明挂件ID是哪个挂件以及相关配置 */
        foreach ($widgets as $widget_id => $widget_name)
        {
            /* 写入新的挂件信息 */
            $page_config['widgets'][$widget_id]['name']     = $widget_name;
            $page_config['widgets'][$widget_id]['options']  = array();

            /* 如果进行了新的配置，则写入 */
            if (isset($page_config['tmp'][$widget_id]))
            {
                $page_config['widgets'][$widget_id]['options'] = $page_config['tmp'][$widget_id]['options'];

                continue;
            }

            /* 写入旧的配置信息 */
            $page_config['widgets'][$widget_id]['options'] = $old_widgets[$widget_id]['options'];
        }

        /* 清除临时的配置信息 */
        unset($page_config['tmp']);

        /* 保存配置 */
        $this->_save_page_config($template_name, $page, $page_config, $client);
        $this->json_result('', 'save_successed');
    }

    /**
     *    获取编辑器面板
     *

     *    @return    void
     */
    function get_editor_panel()
    {
		
        /* 获取挂件列表 */
        $widgets = list_widget();
		
		list($page, $client, $template_name) = $this->_get_client_params();
		
		$editable_pages = $this->_get_editable_pages();
		
		// 将不属于此页面的挂件去除		
		$pageDetail = isset($editable_pages[$page]) ? $editable_pages[$page] : array();	
		$pageKey = (isset($pageDetail['name']) && !empty($pageDetail['name'])) ? $pageDetail['name'] : $page;
		
		// 匹配某个模板某个终端某个页面 如：jd.pc.index
		$pageKey1 = $template_name.'.'.$client.'.'.$pageKey;
		// 匹配某个模板某个终端所有页面 如: jd.pc.*
		$pageKey2 = $template_name.'.'.$client.'.*';
		// 匹配某个模板所有终端所有页面 如：jd.*
		$pageKey3 = $template_name.'.*';
		
		//echo $pageKey1.'--'.$pageKey2.'--'.$pageKey3;
	
		foreach($widgets as $key => $widget) {
			if(isset($widget['belongs']) && !empty($widget['belongs'])) {
				$belongs = explode(',', $widget['belongs']);
				if(!in_array($pageKey1, $belongs) && !in_array($pageKey2, $belongs) && !in_array($pageKey3, $belongs)) {
					unset($widgets[$key]);
				}
			}
		}

        header('Content-Type:text/html;charset=' . CHARSET);
        $this->assign('widgets', ecm_json_encode($widgets));
        $this->assign('site_url', SITE_URL);
		$page = $this->_get_page();
		$this->assign('page', $page);
        $this->display('template.panel.html');
    }

    /**
     *    添加挂件到页面中
     *

     *    @return    void
     */
    function add_widget()
    {
        $name = !empty($_GET['name']) ? trim($_GET['name']) : null;
		
        /* 当前所编辑的页面 */
        list($page, $client, $template_name) = $this->_get_client_params();
		
        if (!$name || !$page)
        {
            $this->json_error('no_such_widget');

            return;
        }
        $page_config = get_widget_config($template_name, $page, $client);
        $id = $this->_get_unique_id($page_config);
        $widget =& widget($id, $name, array());
        $contents = $widget->get_contents();
        $this->json_result(array('contents' => $contents, 'widget_id' => $id));
    }

    function _get_unique_id($page_config)
    {
        $id = '_widget_' . rand(100, 999);
        if (array_key_exists($id, $page_config['widgets']))
        {
            return $this->_get_unique_id($page_config);
        }

        return $id;
    }

    /**
     *    获取挂件的配置表单
     *

     *    @return    void
     */
    function get_widget_config_form()
    {
        $name = !empty($_GET['name']) ? trim($_GET['name']) : null;
        $id   = !empty($_GET['id']) ? trim($_GET['id']) : null;
        
		/* 当前所编辑的页面 */
        list($page, $client, $template_name) = $this->_get_client_params();
		
        if (!$name || !$id || !$page)
        {
            $this->json_error('no_such_widget');

            return;
        }
        $page_config = get_widget_config($template_name, $page, $client);
        $options = empty($page_config['tmp'][$id]['options']) ? $page_config['widgets'][$id]['options'] : $page_config['tmp'][$id]['options'];
        $widget =& widget($id, $name, $options);
        header('Content-Type:text/html;charset=' . CHARSET);
        $widget->display_config();
    }

    /**
     *    配置挂件
     *

     *    @param    none
     *    @return    void
     */
    function config_widget()
    {
        if (!IS_POST)
        {
            return;
        }
        $name = !empty($_GET['name']) ? trim($_GET['name']) : null;
        $id   = !empty($_GET['id']) ? trim($_GET['id']) : null;
        
		/* 当前所编辑的页面 */
        list($page, $client, $template_name) = $this->_get_client_params();
		
        if (!$name || !$id || !$page)
        {
            $this->_config_respond('_d.setTitle("' . Lang::get('no_such_widget') . '");_d.setContents("message", {text:"' . Lang::get('no_such_widget') . '"});');

            return;
        }
        $page_config = get_widget_config($template_name, $page, $client);
        $widget =& widget($id, $name, $page_config['widgets'][$id]['options']);
        $options = $widget->parse_config($_POST);
        if ($options === false)
        {
			$error = current($widget->get_error());
            $this->json_error($error['msg']);

            return;
        }
        $page_config['tmp'][$id]['options'] = $options;

        /* 保存配置信息 */
        $this->_save_page_config($template_name, $page, $page_config, $client);

        /* 返回即时更新的数据 */
        $widget->set_options($options);
        $contents = $widget->get_contents();
        $this->_config_respond('DialogManager.close("config_dialog");parent.disableLink(parent.$(document.body));parent.$("#' . $id . '").replaceWith(document.getElementById("' . $id . '").parentNode.innerHTML);parent.init_widget("#' . $id . '");', $contents);
    }

    /**
     *    响应配置请求
     *

     *    @param    none
     *    @return    void
     */
    function _config_respond($js, $widget = '')
    {
        header('Content-Type:text/html;charset=' . CHARSET);
        echo  '<div>' . $widget . '</div>' . '<script type="text/javascript">var DialogManager = parent.DialogManager;var _d = DialogManager.get("config_widget");' . $js . '</script>';
    }

    /**
     *    保存页面配置文件
     *

     *    @param     string $template_name
     *    @param     string $page
     *    @param     array  $page_config
     *    @return    void
     */
    function _save_page_config($template_name, $page, $page_config, $client = 'pc')
    {
        $page_config_file = ROOT_PATH . '/data/page_config/'.$client.'/' . $template_name . '.' . $page . '.config.php';
        $php_data = "<?php\n\nreturn " . var_export($page_config, true) . ";\n\n?>";

        return file_put_contents($page_config_file, $php_data, LOCK_EX);
    }

    /**
     *    获取欲编辑的页面的HTML
     *

     *    @param     string $page
     *    @return    string
     */
    function _get_page_html($page)
    {
        $pages = $this->_get_editable_pages();
		
		$find = false;
		foreach($pages as $key=>$val){
			if($key==$page){
				$find = true;
				return file_get_contents($val['url']);
				break;
			}
		}
        if ($find === false){
            return false;
        }
    }

    /**
     *    让页面具有编辑功能
     *

     *    @param     string $html
     *    @return    string
     */
    function _make_editable($page, $html, $client = 'pc')
    {
        $real_backend_url = site_url();
        $editmode = '<script type="text/javascript" src="' . $real_backend_url . '/index.php?act=jslang"></script><script type="text/javascript">__PAGE__ = "' . $page . '"; __CLIENT__ ="'.$client.'"; REAL_BACKEND_URL = "' . $real_backend_url . '";</script><script type="text/javascript" src="' . SITE_URL . '/includes/libraries/javascript/jquery.ui/jquery.ui.js"></script><script type="text/javascript" charset="utf-8" src="' . SITE_URL . '/includes/libraries/javascript/jquery.ui/i18n/' . i18n_code() . '.js"></script><script id="dialog_js" type="text/javascript" src="' . SITE_URL . '/includes/libraries/javascript/dialog/dialog.js"></script><script id="template_editor_js" type="text/javascript" src="' . $real_backend_url . '/includes/javascript/template_panel.js"></script><link id="template_editor_css" href="' . $real_backend_url . '/templates/style/template_panel.css" rel="stylesheet" type="text/css" /><link rel="stylesheet" href="' . SITE_URL . '/includes/libraries/javascript/jquery.ui/themes/ui-lightness/jquery.ui.css" type="text/css" media="screen" /><link rel="stylesheet" href="' . SITE_URL . '/includes/libraries/javascript/hack.css" type="text/css" media="screen" />';

        return str_replace('<!--<editmode></editmode>-->', $editmode, $html);
    }

    /**
     *    获取可以编辑的页面列表
     *

     *    @param    none
     *    @return    void
     */
    function _get_editable_pages()
    {
        $real_site_url = dirname(site_url());
		list($page, $client, $template_name) = $this->_get_client_params();
		
		$data = array();
		
		if(in_array($client, array('pc'))) {
		
			$data['index'] 		= array('title'=>LANG::get('index'),'url'=> $real_site_url . '/index.php','action'=>array());
			$integral_mod = &m('integral');
			if($integral_mod->_get_sys_setting('integral_enabled')){
				$data['integral'] 	= array('title'=>LANG::get('integral_mall'),'url'=> $real_site_url . '/index.php?app=integral','action'=>array());
			}
			$data['gcategory'] 	= array('title'=>LANG::get('gcategory'),'url'=> $real_site_url . '/index.php?app=category','action'=>array());
			$data['scategory'] 	= array('title'=>LANG::get('scategory'),'url'=> $real_site_url . '/index.php?app=category&act=store','action'=>array());
			$data['login'] 	= array('title'=>LANG::get('login'),'url'=> $real_site_url . '/index.php?app=member&act=login','action'=>array());
	
			$model_channel = &af('channels');
			$channel = $model_channel->getAll(); //载入系统设置数据
			
			if($channel){
				foreach($channel as $id=>$page){
					$data[$id] = array(
						'title'=>$page['title'],
						'url'=>$real_site_url.'/index.php?app=channel&id='.$id,
						'action'=>array('edit','drop'), 
						'name' => 'channel_style'.$page['style']
					);
				}
			}
		}
		
		elseif(in_array($client, array('m'))) {
			
			$data['index'] 	= array('title'=>LANG::get('index'),'url'=> $real_site_url . '/mobile/index.php','action'=>array());
			
		}
		return $data;
    }
	
	function _get_page()
    {
        list($page, $client, $template_name) = $this->_get_client_params();
		
        $editable_pages = $this->_get_editable_pages();
        if(empty($editable_pages[$page]))
        {
        	return false;
        }
        else
        {
        	return $editable_pages[$page];
        }
    }
	
	function _get_client_params()
	{
		$page    		= !empty($_GET['page']) ? trim($_GET['page']) : null;
		$client  		= in_array(trim($_GET['client']), array('pc', 'm')) ? trim($_GET['client']) : 'pc';
		$template_name 	= in_array($client, array('m')) ? (Conf::get('wap_template_name') ? Conf::get('wap_template_name') : 'default') : Conf::get('template_name');

		!$template_name && $template_name = 'jd';
		
		return array($page, $client, $template_name);
	}
}

?>
