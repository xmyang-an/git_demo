<?php

/**
 *    商品属性管理控制器
 *
 *    @author   Mimall
 *    @usage    none
 */
class PropsApp extends BackendApp
{
	var $_gcate_mod;
	var $_props_mod;
	var $_prop_value_mod;
	var $_cate_pvs_mod;
    function __construct()
    {
        $this->PropsApp();
    }

    function PropsApp()
    {
        parent::BackendApp();
		$this->_gcate_mod = &m('gcategory');
		$this->_props_mod = &m('props');
		$this->_prop_value_mod = &m('prop_value');
		$this->_cate_pvs_mod = &m('cate_pvs');
    }

    function index()
    {
		$prop_list = $this->_props_mod->find(array('conditions'=>'','order'=>'sort_order,pid'));
		foreach($prop_list as $key => $item)
		{
			$prop_list[$key]['prop_value'] = $this->_prop_value_mod->find(array('conditions'=>'status=1 and pid='.$item['pid'],'order'=>'sort_order,vid'));
		}
		/* 导入css */
        $this->import_resource(array(
            'style'  => 'res:style/jqtreetable.css'
        ));
		$this->assign('prop_list',$prop_list);
		
		$this->display('props.index.html');
	}
	function add()
	{
		if(!IS_POST)
		{
			$props = array('pid' => $pid, 'sort_order' => 255, 'status' => 1);
            $this->assign('props', $props);		
			$this->display('props.form.html');
		}
		else
		{
			if($this->check_prop_name(trim($_POST['name']))){
				$this->json_error('prop_name_exist');
				return;
			}
			$props = array(
			   'name' => trim($_POST['name']),
			   'prop_type' => trim($_POST['prop_type']),
			   'is_color_prop'=> intval($_POST['is_color_prop']),
			   'sort_order'=> intval($_POST['sort_order']),
			   'status'=> intval($_POST['status']),
			);
			$pid = $this->_props_mod->add($props);
			if(!empty($_POST['prop_value']))
			{
				$prop_values = explode(',',trim($_POST['prop_value']));
				foreach($prop_values as $value)
				{
					$prop_value = array(
				       'pid'   => $pid,
					   'prop_value' => $value,
					   'status' => 1,
					   'sort_order'=> 255
				     );
					 $this->_prop_value_mod->add($prop_value);
				}			
			}
			$this->json_result('','add_ok');
		}
	}
	function edit()
	{
		$pid = empty($_GET['pid']) ? 0 : intval($_GET['pid']);
        if (!$pid)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
		if(!IS_POST)
		{
			$props = $this->_props_mod->get($pid);
			$this->assign('props',$props);
			$this->display('props.form.html');
		}
		else
		{
			if($this->check_prop_name(trim($_POST['name']),$pid)){
				$this->json_error('prop_name_exist');
				return;
			}
			$props = array(
			   'name' => trim($_POST['name']),
			   'prop_type' => trim($_POST['prop_type']),
			   'is_color_prop'=> intval($_POST['is_color_prop']),
			   'sort_order'=> intval($_POST['sort_order']),
			   'status'=> intval($_POST['status']),
			);
			$this->_props_mod->edit($pid,$props);
			$this->json_result('','edit_ok');
		}
		
	}
	
	function drop()
    {
        $pid = empty($_GET['pid']) ? 0 : $_GET['pid'];
        if (!$pid)
        {
            $this->json_error('drop_error');
            return;
        }
        $this->_props_mod->drop($pid);
        if ($this->_props_mod->has_error())    //删除
        {
			$error = current($this->_props_mod->get_error());
            $this->json_error($error['msg']);
            return;
        }
		$this->_prop_value_mod->drop('pid'.db_create_in($pid));

        $this->json_result('','drop_ok');
    }
	
	function add_value()
	{
		$pid = empty($_GET['pid']) ? 0 : intval($_GET['pid']);
        if (!$pid || !$this->_props_mod->get($pid))
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
		if(!IS_POST)
		{
			$prop_value = array('pid' => $pid, 'sort_order' => 255, 'status' => 1);
            $this->assign('prop_value', $prop_value);
			
			$props = $this->_props_mod->find();
			$this->assign('props',$props);
			$this->display('prop_value.form.html');
		}
		else
		{
			if($this->_prop_value_mod->get(array('conditions'=>"pid=".$pid." and prop_value='".trim($_POST['prop_value'])."'")))
			{
				$this->json_error('prop_value_exist');
				return;
			}
			$data = array(
			   'pid' => $pid,
			   'prop_value'=> trim($_POST['prop_value']),
			   'color_value'=> trim($_POST['color_value']),
			   'sort_order'=> intval($_POST['sort_order']),
			   'status'=> intval($_POST['status'])
			);
			$this->_prop_value_mod->add($data);
			$this->json_result('','add_ok');
		}
	}
	function edit_value()
	{
		$vid = empty($_GET['vid']) ? 0 : intval($_GET['vid']);
        if (!$vid)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
		if(!IS_POST)
		{
			$props = $this->_props_mod->find();
			$this->assign('props',$props);
			$prop_value = $this->_prop_value_mod->get($vid);
			$prop_value['is_color_prop'] = $props[$prop_value['pid']]['is_color_prop'];
			$this->assign('prop_value',$prop_value);
			
			/* 导入css */
			$this->import_resource('jquery.plugins/pickcolor.js');
			$this->display('prop_value.form.html');
		}
		else
		{
			$pid = intval($_POST['pid']);
			if(!$props = $this->_props_mod->get($pid)) {
				$this->json_error('Hacking Attempt');
            	return;
			}
			$prop_value = trim($_POST['prop_value']);
			
			// 用一属性名下 属性值不能重复，不同属性名下可以重复
			if($this->_prop_value_mod->get(array('conditions'=>"pid=".$pid." and vid!=".$vid." and prop_value='".$prop_value."'",'fields'=>'vid,prop_value')))
			{
				$this->json_error('prop_value_exist');
				return;
			}
			$data = array(
			   'pid'=>$pid,
			   'prop_value'=>$prop_value,
			   'sort_order'=>intval($_POST['sort_order']),
			   'status'=>intval($_POST['status'])
			);
			if($props['is_color_prop']) {
				$data['color_value'] = trim($_POST['color_value']);
			} else $data['color_value'] = '';
			
			$this->_prop_value_mod->edit($vid,$data);
			$this->json_result('','edit_ok');
			
		}
	}
	
	function drop_value()
	{
		$id = empty($_GET['vid']) ? 0 : intval($_GET['vid']);
        if (!$id)
        {
            $this->json_error('Hacking Attempt');
            return;
        }
        $ids = explode(',',$id);
        $this->_prop_value_mod->drop($ids);
        if ($this->_prop_value_mod->has_error())    //删除
        {
			$error = current($this->_prop_value_mod->get_error());
            $this->json_error($error['msg']);
            return;
        }

        $this->json_result('','drop_ok');
	}
	// 分配属性，将属性派发给商品分类
	function distribute()
	{
		if(!IS_POST)
		{
			$cate_id = empty($_GET['cate_id']) ? 0 : intval($_GET['cate_id']);
			if (!$cate_id)
			{
				$this->show_warning('Hacking Attempt');
				return;
			}
			//当前分类的级层，如 女装/连衣裙
			$this->_gcate_mod = &bm('gcategory');
			$this->assign('distribute_cate',$this->_gcate_mod->get_ancestor($cate_id));
			
			$prop_list = $this->_props_mod->find(array('conditions'=>'status=1','order'=>'sort_order,pid'));
			foreach($prop_list as $key => $prop)
			{
				$prop_list[$key]['prop_value']=$this->_prop_value_mod->find(array('conditions'=>'status=1 and pid='.$prop['pid'],'order'=>'sort_order,vid'));
			}
			// 如果已经分配过属性，则进行 checked="checked" 设置
			$cate_pvs = $this->_cate_pvs_mod->get($cate_id);
			if($cate_pvs)
			{
				$pvs = $cate_pvs['pvs'];
				$pv = explode(';',$pvs);
				$p = array();// 存储分配有属性名 prop 的数组
				$v = array();// 存储分配有属性值 prop_value 的数组
				foreach($pv as $pitem)
				{
					$item = explode(':',$pitem);
					$p[] = $item[0];
					$v[] = $item[1];
				}
				$p = array_unique($p); // 去掉重复值
				$v = array_unique($v);// 去掉重复值
			}
			foreach($prop_list as $key => $prop)
			{
				if(isset($p) && in_array($prop['pid'],$p)) {
					$prop_list[$key]['checked'] = 1;
				}
				else {
					$prop_list[$key]['checked'] = 0;
				}
				foreach($prop['prop_value'] as $key_v => $pvalue)
				{
					if(isset($v) && in_array($pvalue['vid'],$v)) {
						$prop_list[$key]['prop_value'][$key_v]['checked'] = 1;
					}
					else {
						$prop_list[$key]['prop_value'][$key_v]['checked'] = 0;
					}
				}
			}
			$this->assign('prop_list',$prop_list);
			
			/* 导入css */
			$this->import_resource(array(
			   'style'  => 'res:style/jqtreetable.css'
			));
		    $this->assign('cate_id',$cate_id);
			$this->display('props.distribute.html');
		}
		else
		{
			$cate_id = intval($_POST['cate_id']);
			$pids = $_POST['pid'];
			$vids = $_POST['vid'];
			//if(!isset($pids) || !isset($vids)) {
				//$this->show_message('prop_empty',
                //'back', 'index.php?app=props&act=distribute&cate_id='.$cate_id);
				//return;				
			//}
			$data = array('cate_id'=>$cate_id,'pvs'=>'');
			if(isset($pids) && isset($vids))
			{
				// 去除非选中的 pid
				foreach($vids as $item)
				{
					$vid = explode(':',$item);
					if(in_array($vid[0],$pids))
					{
						$pvs .= ';' . $item;
					}
				}
				$data['pvs'] =  substr($pvs,1);
			}
			if($this->_cate_pvs_mod->get($cate_id)) {
				$this->_cate_pvs_mod->edit($cate_id,$data);
			}
			else {
				$this->_cate_pvs_mod->add($data);
			}
			$this->json_result(array('ret_url'=>'index.php?app=props&act=distribute&cate_id='.$cate_id),'save_ok');
		}
	}
	 /* 异步取商品属性子元素 */
	function ajax_prop_value()
	{
		if(!isset($_GET['pid']) || empty($_GET['pid']))
        {
            echo ecm_json_encode(false);
            return;
        }
		
		$pid = intval($_GET['pid']);
		if(!$props = $this->_props_mod->get($pid)) {
			echo ecm_json_encode(false);
            return;
		}
		$prop_value = $this->_prop_value_mod->find(array('conditions'=>'pid='.$pid, 'order'=>'sort_order'));
		foreach ($prop_value as $key => $val)
        {
            $prop_value[$key]['switchs'] = 0;
			$prop_value[$key]['is_color_prop'] = $props['is_color_prop'];
        }

        header("Content-Type:text/html;charset=" . CHARSET);
        echo ecm_json_encode(array_values($prop_value));
        return;
	}
	// 重复的属性名不能添加
	function check_prop_name($name,$not_include_pid=0)
	{
		return $this->_props_mod->get(array('conditions'=>"pid != ".$not_include_pid." and name='".$name."'",'fields'=>'pid,name'));
	}
}

?>