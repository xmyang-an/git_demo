<?php

class Seller_adminApp extends MemberbaseApp
{
    function __construct()
    {
        $this->Seller_adminApp();
    }
    function Seller_adminApp()
    {
        parent::__construct();
		$this->_get_member_role();
    }
    function index()
    {
		header('location:index.php?app=member');
		
	}
	function _get_member_role()
	{
        if (!$this->visitor->has_login)
        {
			header('Location:index.php?app=member&act=login&ret_url=' . rawurlencode(get_domain() . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']));
			return;
		}
		
		if(!$this->visitor->get('has_store')){
			
			// 已申请了店铺，但处于审核中
			if($this->visitor->get('store_id') && in_array($this->visitor->get('state'), array(0))) {
				header('location:index.php?app=apply&step=3');
			}
			else {
				$this->show_warning('has_no_store');
			}
			exit;
		}
		$_SESSION['member_role'] = 'seller_admin';
	}
}

?>
