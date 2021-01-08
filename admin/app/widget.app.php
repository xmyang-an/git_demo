<?php

class WidgetApp extends BackendApp
{
    function index()
    {
        /* 读取已安装的挂件 */
        $widgets = list_widget();
        $this->assign('widgets', $widgets);
		
		$this->import_resource(array(
            'style'  => 'res:style/jqtreetable.css')
        );
        $this->display('widget.index.html');
    }

    function edit()
    {
        $name = empty($_GET['name']) ? 0 : trim($_GET['name']);
        $site_id = empty($_POST['site_id']) ? 0 : $_POST['site_id'];

        if (!$name)
        {
            $this->show_warning('no_such_widget');

            return;
        }
		
		if(in_array('script', array($_GET['file'])))
		{
			// 防止脚本攻击，不允许编辑PHP文件
			exit(0);
		}
		
        $script_file = $this->_get_file($name, $_GET['file']);
        if (!IS_POST)
        {
			$this->assign('name',$this->_get_widget_name($name));
            $this->assign('code', file_get_contents($script_file));
            $this->assign('site_id', md5(MALL_SITE_ID));
            $this->display('widget.form.html');
        }
        else
        {
            if ($site_id != md5(MALL_SITE_ID) || !file_put_contents($script_file, stripslashes($_POST['code'])))
            {
                $this->json_error('edit_file_failed');

                return;
            }

            $this->json_result('','edit_file_successed');
        }
    }

    function clean_file()
    {
        $continue = isset($_GET['continue']);
        $isolcated_file = $this->_get_isolated_file();
        if (empty($isolcated_file))
        {
            $this->json_error('no_isocated_file');

            return;
        }
        $file_count = count($isolcated_file);
        if (!$continue)
        {
            $this->json_result('', sprintf(Lang::get('isolcated_file_count'), $file_count));

            return;
        }
        else
        {
            foreach ($isolcated_file as $f)
            {
                _at('unlink', ROOT_PATH . '/' . $f);
            }

            $this->json_result('', sprintf('clean_file_successed', $file_count));
        }
    }

    function _get_isolated_file()
    {
        /* 获取存在的文件列表 */
        $exist_files    = $this->_get_exist_file();
        if (empty($exist_files))
        {
            return array();
        }
        /* 获取所有的选项值 */
        $option_values  = $this->_get_option_value();
		
		/* 筛选图片文件 */
		$option_values = $this->_get_option_value_of_image($option_values);
		
		
        /* 无任何选项，则表示，所有文件都是孤立的，可以删除 */
        if (empty($option_values))
        {
            return $exist_files;
        }
        /* 逐个判断是否被使用 */
        foreach ($exist_files as $k => $f)
        {
            /* 若$f存在于选项中，则表示该文件正被使用，不能删除 */
            /* $options_values可以是二维数组，三维四维可能会有问题，因此，需要注意，所有的存储上传文件的option必须放在第一级数组中 */
            if($this->_check_use($f, $option_values))
            {
                unset($exist_files[$k]);
            }
        }
        return $exist_files;
    }

    /**
     *   检查挂件文件是否在使用
     *
     * @param  $f
     * @param array $option_values
     * @return true | 正在使用中，不能删除
     *         false | 没有使用，可以删除
     */
    function _check_use($f, $option_values)
    {
        if (in_array($f, $option_values, true))
        {
            return true;
        }
        foreach ($option_values as $key => $val)
        {
            if (is_array($val))
            {
                if (in_array($f, $val))
                {
                    return true;
                }
            }
        }
       return false;
    }

    function _get_exist_file()
    {
        $files = array();
        $file_dir = ROOT_PATH . '/data/files/mall/template';
        if (!is_dir($file_dir))
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
            $files[] = 'data/files/mall/template/' . $item;
        }

        return $files;
    }

    function _get_option_value(&$config_values = array(), $config_dir = '')
    {
		if(!$config_dir) {
        	$config_dir = ROOT_PATH . '/data/page_config';
		}
        $dir  = dir($config_dir);
        while (false !== ($item = $dir->read()))
        {
            if (!$item || in_array($item, array('.', '..', 'index.htm', 'Thumbs.db')) || $item{0} == '.')
            {
                continue;
            }
			$file = $config_dir . '/' . $item;
			if(!is_dir($file)) {
            	$tmp = include($file);
            	$config_values = array_merge($config_values, $this->_get_all_value($tmp));
			}
			else
			{
				$this->_get_option_value($config_values, $file);
			}
        }
        return $config_values;
    }
	
	/* 将挂件配置文件中的所有图片文件路径提炼出来 */
	function _get_option_value_of_image($config_values = array(), &$result = array())
	{
		if(!is_array($config_values)) {
			$fileType = strtolower(substr($config_values, strrpos($config_values, '.') + 1));
			if(in_array($fileType, array('jpg', 'jpeg','png','gif','bmp'))) {
				$result[] = $config_values;
			}
		}
		else
		{
			foreach($config_values as $key => $val)
			{
				$this->_get_option_value_of_image($val, $result);
			}
		}
		
		return $result;
	}
	
    function _get_all_value($widgets)
    {
        $values = array();
        if (isset($widgets['widgets']))
        {
            foreach ($widgets['widgets'] as $widget)
            {
                if (is_array($widget['options']))
                {
                    $values = array_merge($values, array_values($widget['options']));
                }
            }
        }
        if (isset($widgets['tmp']))
        {
            foreach ($widgets['tmp'] as $widget)
            {
                if (is_array($widget['options']))
                {
                    $values = array_merge($values, array_values($widget['options']));
                }
            }
        }
		// 针对手机端的商城配置文件和店铺配置文件
		if (isset($widgets['config']))
        {
            foreach ($widgets['config'] as $widget)
            {
                if (is_array($widget))
                {
                    $values = array_merge($values, array_values($widget));
                }
            }
        }

        return $values;
    }

    function _get_file($name, $type = 'script')
    {
        $file = ROOT_PATH . '/external/widgets/' . $name;
        switch ($type)
        {
            case 'script':
                return $file . '/main.widget.php';
            break;
            case 'template':
                return $file . '/widget.html';
            break;
        }
    }
	
	function _get_widget_name($name)
	{
		$widgets = list_widget();
		return $widgets[$name]['display_name'];
	}
}

?>
