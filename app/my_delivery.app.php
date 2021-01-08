<?php

/* 运费模板控制器 */
class My_deliveryApp extends StoreadminbaseApp
{
    var $_delivery_mod;
	var $_region_mod;
	var $_store_id;

    function __construct()
    {
        $this->My_deliveryApp();
    }

    function My_deliveryApp()
    {
        parent::__construct();

        $this->_delivery_mod =& m('delivery_template');
		$this->_region_mod =& m('region');
		$this->_store_id  = intval($this->visitor->get('manage_store'));
    }

    /* 管理 */
    function index()
    {
		$page = $this->_get_page(2);
		$delivery_template = $this->_delivery_mod->find(array(
			'conditions'=>'store_id='.$this->_store_id,
			'limit'=> $page['limit'],
			'count' => true,
			'order'=>'template_id desc'
		));
		
		$page['item_count'] = $this->_delivery_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
		
		$deliverys = $this->_delivery_mod->format_template($delivery_template);
		
		
		/* 当前页面信息 */
        $this->_curlocal(LANG::get('my_delivery'), 'index.php?app=my_delivery',
                         LANG::get('delivery_list'));
        $this->_curitem('my_delivery');
        $this->_curmenu('delivery_list');
		
		$this->assign('deliverys', $deliverys);
		
		$this->display('my_delivery.index.html');
		
    }

	
	function add()
	{
		if(!IS_POST)
		{
			$this->assign('regions', $this->_region_mod->get_options(0));
			/* 导入jQuery的表单验证插件 */
            $this->_import_resource();
			
			$this->assign('delivery', array('plus_type'=>$this->_delivery_mod->get_plus_type()));
			
			/* 当前页面信息 */
        	$this->_curlocal(LANG::get('my_delivery'), 'index.php?app=my_delivery',
                         LANG::get('delivery_add'));
        	$this->_curitem('my_delivery');
        	$this->_curmenu('delivery_add');
			
			$this->display('my_delivery.form.html');
		}
		else
		{			
			$data = array();
			$post_types = $_POST['template_types'];
			
			foreach($post_types as $type)
			{
				$post_dests .=';'.implode(',',$_POST[$type.'_dests']);
				$post_start .=';'.implode(',',$_POST[$type.'_start']);
				$post_postage .=';'.implode(',', $_POST[$type.'_postage']);
				$post_plus    .=';'.implode(',', $_POST[$type.'_plus']);
				$post_postageplus .=';'.implode(',', $_POST[$type.'_postageplus']);
			}
			
			$data = array(
				'name'						=> trim($_POST['name']),
				'store_id'					=> $this->_store_id,
				'template_types'			=> implode(';',$post_types),
				'template_dests' 			=> substr($post_dests,1),
				'template_start_standards' 	=> substr($post_start,1),
				'template_start_fees'	 	=> substr($post_postage,1),
				'template_add_standards'   	=> substr($post_plus,1),
				'template_add_fees'			=> substr($post_postageplus,1),
				'created'					=> time()
			);
			if(!$this->_check_data($data['template_start_standards']) || !$this->_check_data($data['template_start_fees']) || !$this->_check_data($data['template_add_standards']) || !$this->_check_data($data['template_add_fees'])){
				$this->show_warning('fee_and_quantity_must_number');
				return false;
			}
			
			$this->_delivery_mod->add($data);
			
			$this->show_message('add_ok',
                'back_list',    'index.php?app=my_delivery',
                'continue_add', 'index.php?app=my_delivery&amp;act=add'
            );
			
		}
	}
	function edit()
	{
		$template_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		if(!$template_id){
			$this->show_warning('Hacking Attempt');
            return;
		}
		if(!IS_POST)
		{
			$delivery = $this->_delivery_mod->format_template_foredit($template_id);
			if($delivery['store_id'] != $this->_store_id) {
				$this->show_warning('Hacking Attempt');
				return;
			}
			
			$this->assign('delivery', $delivery);
			
			$this->assign('regions', $this->_region_mod->get_options(0));
			/* 导入jQuery的表单验证插件 */
			$this->_import_resource();
     
			/* 当前页面信息 */
        	$this->_curlocal(LANG::get('my_delivery'), 'index.php?app=my_delivery',
                         LANG::get('delivery_edit'));
        	$this->_curitem('my_delivery');
        	$this->_curmenu('delivery_edit');
			
			$this->display('my_delivery.form.html');
		}
		else 
		{
			$template_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
			if(!$template_id){
				$this->show_warning('Hacking Attempt');
				return;
			}
			
			$data = array();
			$post_types = $_POST['template_types'];
			
			foreach($post_types as $type)
			{
				$post_dests .=';'.implode(',',$_POST[$type.'_dests']);
				$post_start .=';'.implode(',',$_POST[$type.'_start']);
				$post_postage .=';'.implode(',',$_POST[$type.'_postage']);
				$post_plus    .=';'.implode(',', $_POST[$type.'_plus']);
				$post_postageplus .=';'.implode(',', $_POST[$type.'_postageplus']);
			}
			
			$data = array(
				'name'						=> trim($_POST['name']),
				'store_id'					=> $this->_store_id,
				'template_types'			=> implode(';',$post_types),
				'template_dests' 			=> substr($post_dests,1),
				'template_start_standards' 	=> substr($post_start,1),
				'template_start_fees'	 	=> substr($post_postage,1),
				'template_add_standards'   	=> substr($post_plus,1),
				'template_add_fees'			=> substr($post_postageplus,1),
			);	
			if(!$this->_check_data($data['template_start_standards']) || !$this->_check_data($data['template_start_fees']) || !$this->_check_data($data['template_add_standards']) || !$this->_check_data($data['template_add_fees'])){
				$this->show_warning('fee_and_quantity_must_number');
				return;
			}
			
			if(!$this->_delivery_mod->edit("template_id={$template_id} AND store_id={$this->_store_id}", $data)) {
				$this->show_warning('edit_error');
				return;
			}
			$this->show_message('edit_ok',
                'back_list',    'index.php?app=my_delivery',
                'continue_edit', 'index.php?app=my_delivery&amp;act=edit&amp;id='.$template_id
            );
		}
	}
	function copy_tpl()
	{
		$template_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		if(!$template_id){
			$this->show_warning('Hacking Attempt');
            return;
		}
		$delivery_template = $this->_delivery_mod->get($template_id);
		
		if($delivery_template['store_id'] != $this->_store_id) {
			$this->show_warning('Hacking Attempt');
			return;
		}
			
		$delivery_template['name'] = $delivery_template['name'] . LANG::get('copy_word');
		unset($delivery_template['template_id']);
		$this->_delivery_mod->add($delivery_template);
		$this->show_message('copy_ok',
                'back_list','index.php?app=my_delivery'
        );
		
	}
	function drop()
	{
		$template_id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		if(!$template_id){
			$this->show_warning('Hacking Attempt');
            return;
		}
		if(!$this->_delivery_mod->get(array('conditions'=>'store_id='.$this->_store_id.' AND template_id !='.$template_id,'fields'=>'template_id'))){
			$this->show_warning('no_allow_drop_last_delivery_template');
			return;
		}
		
		if(!$this->_delivery_mod->drop("template_id={$template_id} AND store_id={$this->_store_id}")) {
			$this->show_warning('drop_fail');
			return;
		}
		
		$this->show_message('drop_ok',
                'back_list','index.php?app=my_delivery'
        );
	}
	
	function _check_data($data)
	{
		$data = explode(';', $data);
		
		foreach($data as $key=>$val)
		{
			$arr = explode(',', $val);
			foreach($arr as $k=>$v)
			{
				if(!is_numeric($v) || $v<0 || $v==''){
					return false;
				}
			}
		}
		return true;
	}

	function _import_resource()
    {
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
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
				array(
                    'path' => 'mlselection.js',
                    'attr' => '',
                ),
				array(
                    'path' => 'delivery.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/smoothness/jquery.ui.css',
        ));
    }
	
	function _get_member_submenu()
    {

		$menus = array(
			array(
				'name' => 'delivery_list',
				'url'  => 'index.php?app=my_delivery',
			),
			array(
				'name' => 'delivery_add',
				'url'  => 'index.php?app=my_delivery&amp;act=add',
			),
		); 
        if (ACT == 'edit')
        {
            $menus[] = array(
                'name' => 'delivery_edit',
                'url'  => '',
            );
        }
        return $menus;
    }
}

?>