<?php

class TravelApp extends MallbaseApp
{

    function __construct()
    {
        $this->TravelApp();
    }
    function TravelApp()
    {
        parent::__construct();
    }
    function index()
    {
		$to = trim($_GET['to']);
		if(!empty($to))
		{
			$arr = explode(',', $to);
			
			$this->assign('travel', array('lat' => trim($arr[0]), 'lng' => trim($arr[1]), 'latlng' => $to));
		}
		
		$this->headtag('<script type="text/javascript" src="https://api.map.baidu.com/api?v=2.0&ak='.Conf::get('baidukey.browser').'"></script><script type="text/javascript" src="https://api.map.baidu.com/library/GeoUtils/1.2/src/GeoUtils_min.js"></script>');
		
		$this->_config_seo('title', Lang::get('travel') . ' - ' . Conf::get('site_title'));
		$this->_get_curlocal_title('travel');
		
        $this->display('travel.index.html');
    }
}

?>
