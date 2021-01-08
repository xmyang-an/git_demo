<?php

/**
 *    插件管理控制器
 *
 *    @usage    none
 */
class PluginApp extends BackendApp
{
    function index()
    {
        /* 读取已安装的插件 */
        $plugins = $this->_list_plugins();
        $this->assign('plugins', $plugins);
		
		$this->import_resource(array(
            'style'  => 'res:style/jqtreetable.css')
        );
        $this->display('plugin.index.html');
    }

    /**
     *    启用一个插件
     *
     *    @param    none
     *    @return    void
     */
    function enable()
    {
        $id = empty($_GET['id']) ? 0 : trim($_GET['id']);
        if (!$id)
        {
            $this->json_error('no_such_plugin');

            return;
        }
        $plugin_info = $this->_get_plugin_info($id);
        if (!IS_POST)
        {
            $this->assign('plugin', $plugin_info);
            $this->display('plugin.form.html');
        }
        else
        {
            $config = empty($_POST['config']) ? array() : $_POST['config'];
            $result = $this->_enable_plugin($plugin_info['hook'], $id, $config);

            if (!$result)
            {
                $this->json_error('enable_plugin_failed');

                return;
            }

            $this->json_result('','enable_plugin_successed');
        }
    }

    /**
     *    禁用插件
     *
     *    @return    void
     */
    function disable()
    {
        $id = empty($_GET['id']) ? 0 : trim($_GET['id']);
        if (!$id)
        {
            $this->json_error('no_such_plugin');

            return;
        }
        if(!$this->_disable_plugin($id))
        {
            $this->json_error('disable_plugin_failed');

            return;
        }

        $this->json_result('','disable_plugin_successed');
    }

    function config()
    {
        $id = empty($_GET['id']) ? 0 : trim($_GET['id']);
        if (!$id)
        {
            $this->json_error('no_such_plugin');

            return;
        }
        $plugin_info = $this->_get_plugin_info($id);
        if (!IS_POST)
        {
            $config      = $this->_get_plugin_config($plugin_info['hook'], $id);
            $this->assign('plugin', $plugin_info);
            $this->assign('config', $config);
            $this->display('plugin.form.html');
        }
        else
        {
            $enabled = $this->_list_enabled_plugins();
            $enabled[$plugin_info['hook']][$id] = $_POST['config'];
            if (!$this->_save_enabled($enabled))
            {
                $this->json_error('config_plugin_failed');

                return;
            }

            $this->json_result('','config_plugin_successed');
        }
    }

    /**
     *    读取已安装的插件
     *
     *    @return    array
     */
    function _list_plugins()
    {
        $plugin_dir = ROOT_PATH . '/external/plugins';
        static $plugins    = null;
        if ($plugins === null)
        {
            $plugins = array();
            if (!is_dir($plugin_dir))
            {
                return $plugins;
            }
            $dir = dir($plugin_dir);
            while (false !== ($entry = $dir->read()))
            {
                if (in_array($entry, array('.', '..')) || $entry{0} == '.')
                {
                    continue;
                }
                $info = $this->_get_plugin_info($entry);
                $plugins[$entry] = $info;
                $plugins[$entry]['enabled'] = $this->_is_enabled($info['hook'], $entry);
            }
        }

        return $plugins;
    }

    /**
     *    获取已启用的插件列表
     *
     *    @return    array
     */
    function _list_enabled_plugins()
    {
        $plugin_inc_file = ROOT_PATH . '/data/plugins.inc.php';
        if (is_file($plugin_inc_file))
        {
            return include($plugin_inc_file);
        }

        return array();
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

    /**
     *    获取插件信息
     *
     *    @param     string $hook
     *    @param     string $id
     *    @return    array
     */
    function _get_plugin_config($hook, $id)
    {
        $enabled = $this->_list_enabled_plugins();
        return $enabled[$hook][$id];
    }

    /**
     *    判断指定的插件是否已启用
     *    @param     string $hook
     *    @param     string $id
     *    @return    bool
     */
    function _is_enabled($hook, $id)
    {
        static $enabled = null;
        if ($enabled === null)
        {
            $enabled = $this->_list_enabled_plugins();
        }

        return isset($enabled[$hook][$id]);
    }

    /**
     *    启用一个插件
     *
     *    @param     string $hook
     *    @param     string $id
     *    @param     array  $config
     *    @return    bool
     */
    function _enable_plugin($hook, $id, $config)
    {
        $enabled = $this->_list_enabled_plugins();
        $enabled[$hook][$id] = $config;

        return $this->_save_enabled($enabled);
    }

    /**
     *    彬用一个插件
     *
     *    @param     string $id
     *    @return    bool
     */
    function _disable_plugin($id)
    {
        $enabled = $this->_list_enabled_plugins();
        $info    = $this->_get_plugin_info($id);
        unset($enabled[$info['hook']][$id]);

        return $this->_save_enabled($enabled);
    }

    /**
     *    保存已安装的信息
     *
     *    @param     array $enabled
     *    @return    bool
     */
    function _save_enabled($enabled)
    {
        $plugin_inc_file = ROOT_PATH . '/data/plugins.inc.php';
        $php_data = "<?php\n\nreturn " . var_export($enabled, true) . ";\n\n?>";
        return file_put_contents($plugin_inc_file, $php_data, LOCK_EX);
    }
}

?>