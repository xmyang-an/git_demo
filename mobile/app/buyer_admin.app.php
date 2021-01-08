<?php

class Buyer_adminApp extends MemberbaseApp
{
    function __construct()
    {
        $this->Buyer_adminApp();
    }
    function Buyer_adminApp()
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
		$_SESSION['member_role'] = 'buyer_admin';
	}  
}

?>
