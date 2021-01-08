<?php
    class XunjiaApp extends MallbaseApp{
		var $_xunjia_mod;

		function __construct(){
			$this->XunjiaApp();
		}

		function XunjiaApp(){
			parent::__construct();
			$this->_xunjia_mod = & m('xunjia');
		}
		 
	     function index(){
		
			
			 $this->display('xunjia.index.html');

		 }
		 function add(){
			  //  表格数据（数组）
			 $pp = $_POST['pp'];
			 $pro_name =$_POST['pro_name'];
			 $cpxh=$_POST['cpxh'];
			 $sl=$_POST['sl'];
			 $pro_pic=$_FILES['pro_pic']['tmp_name'];
			//  备注及信息
			$xunjia_content=$_POST['xunjia_content'];
			
			if($_POST['company_name']){
			    $company_name=$_POST['company_name'];
			} else {
							 $this->show_warning('xunjia_company_name');
							 return;
			}
			
			if($_POST['linkman']){
			    $linkman=$_POST['linkman'];
			} else {
							 $this->show_warning('xunjia_linkman');
							 return;
			}
			
			if($_POST['mobile_phone']){
			    $mobile_phone=$_POST['mobile_phone'];
			} else {
							 $this->show_warning('xunjia_mobile_phone');
							 return;
			}
			
			if($_POST['contact_mail']){
			   $contact_mail=$_POST['contact_mail'];
			} else {
							 $this->show_warning('xunjia_contact_mail');
							 return;
			}
			
			// 获取时间
			$create_time=mktime();
			//图片处理
			$upload_ret=fasle;

			
			// 遍历数组
			$info_list=[];
			for($i=0;$i<count($pp);$i++){
				
				    if ($pp[$i]) {
						// 上传路径
					$uploadDir='static/xunjia';
					//创建文件夹
					if(!file_exists($uploadDir)){
						mkdir($uploadDir,0777);
					}
					 // 用时间戳来保存图片，防止重复
					 $targetFile = $uploadDir . '/' . time() . $_FILES['pro_pic']['name'][$i];    
					 // 将临时文件 移动到我们指定的路径，返回上传结果
					 $upload_ret = move_uploaded_file($pro_pic[$i], $targetFile);
					//  数组
					 $info_list[$i]=array("brand"=>$pp[$i],"product"=>$pro_name[$i],"model"=>$cpxh[$i],"number"=>$sl[$i],"img"=>$targetFile);
					    $info_str=json_encode($info_list);
				    }
			}
			
			//存数据库
			$data=array();
			$data['goods_info']=$info_str;
			$data['xunjia_content']=$xunjia_content;
			$data['company_name']=$company_name;
			$data['linkman']=$linkman;
			$data['mobile_phone']=$mobile_phone;
			$data['contact_mail']=$contact_mail;
			$data['create_time']=$create_time;
			
			print_r($info_str . "\n");

			// 保存
			//$xunjia_mod=& m('xunjia');
			//$xunjia_mod->add($data);
			
			
			
			echo($xunjia_content);
			$this->assign('pan',$d);

		 }
		 
}
?>