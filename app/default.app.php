<?php

class DefaultApp extends MallbaseApp
{
    function index()
    {
        $this->assign('index', 1); // 标识当前页面是首页，用于设置导航状态

		$this->_config_seo(array(
            'title' => Conf::get('site_title'),
        ));
        $this->assign('page_description', Conf::get('site_description'));
        $this->assign('page_keywords', Conf::get('site_keywords'));
        $this->display('index.html');
    }
    function license()
    {
    	$LI_SESSION=$_POST['session_id'];
    	if(empty($LI_SESSION))
        {
        	exit;
        }
    	$db =& db();
    	$sql="SELECT count(`sesskey`) FROM `".DB_PREFIX."sessions`  WHERE `sesskey`='$LI_SESSION'";
    	$result=$db->getOne($sql);
    	if($result)
        {
           exit('{"res":"succ","msg":"","info":""}');	
        }
        
    }
}

?>