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
			//$this->show_warning('has_no_store');
			header('Location:index.php?app=apply');
			exit;
		}
		$_SESSION['member_role'] = 'seller_admin';
	}
}

?>
