<?php

class ReportApp extends MemberbaseApp
{
	var $report_mod;
	
    function __construct()
    {
        $this->ReportApp();
    }
	
    function ReportApp()
    {
        parent::__construct();
        $this->report_mod = &m('report');
    }
	
    function index()
    {
		$goods_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		$goods_mod = &m('goods');
		$goods = $goods_mod->get(array(
			'conditions' => $goods_id,
			'join'		 => 'belongs_to_store',
			'fields'     => 'this.*,store_name'
		));
		if(!$goods){
			$this->show_warning('no_such_goods');
			return;
		}
		
		$uploadedfile_mod = &m('uploadedfile');
		if(!IS_POST)
		{
			$images = $uploadedfile_mod->find(array(
				'conditions' => "belong=".BELONG_REPORT." AND item_id=0 AND store_id=".$this->visitor->get('user_id'),
				'order' => 'add_time ASC'
			));

			$this->assign("images", $images);
			$this->assign("belong", BELONG_REPORT);
			
			$this->_get_curlocal_title('add_report');
			$this->_config_seo('title', Lang::get('add_report'));
			
			$this->assign('goods', $goods);
			$this->import_resource(array(
            	'script' => 'webuploader/webuploader.js,webuploader/webuploader.compressupload.js,mobile/jquery.plugins/jquery.form.min.js'
			));
			$this->display('report.index.html');
		}
		else
		{
			$content = trim($_POST['content']);
			$files = $_POST['file_id'];
			if(empty($content)){
				$this->json_error('content_no_empty');
				return;
			}
			if(empty($files)){
				$this->json_error('file_empty');
				return;
			}
			if(count($files) > 5){
				$this->json_error('file_gt_5');
				return;
			}
			$data = array(
				'user_id'  => $this->visitor->get('user_id'),
				'goods_id' => $goods['goods_id'],
				'store_id' => $goods['store_id'],
				'content'  => $content,
				'add_time' => gmtime(),
			);
			$report_id = $this->report_mod->add($data);
			if(!$report_id){
				$error = $this->report_mod->get_error();
				$error = current($error);
				$this->json_error($error['msg']);
				return;
			}
			
			$images = array();
			foreach($files as $key=>$val)
			{
				if($file = $uploadedfile_mod->get($val))
				{
					$images[$file['file_id']] = $file['file_path'];
					$uploadedfile_mod->edit($file['file_id'], 'item_id='.$report_id);
				}
			}
			
			!empty($images) && $this->report_mod->edit($report_id,array('images' => serialize($images)));
			
			$this->json_result(array('ret_url' => 'index.php?app=my_report'),'add_report_ok');
			return;
		}
    }
	
	function uploadImages()
	{
		import('image.func');
        import('uploader.lib');
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE);
        $uploader->allowed_size(SIZE_GOODS_IMAGE); // 2M
        $upload_mod =& m('uploadedfile');
		
		$user_id = $this->visitor->get('user_id');

        $files = $_FILES['file'];
        if ($files['error'] === UPLOAD_ERR_OK)
        {
			$uploaded = $upload_mod->getOne('select count(*) from '.DB_PREFIX.'uploaded_file where item_id=0 AND belong='. BELONG_REPORT.' AND store_id='.$user_id);
			if($uploaded >= 5)
			{
				$this->json_error('最多可以上传5张图片');
				return false;
			}
					
            /* 处理文件上传 */
            $file = array(
                'name'      => $files['name'],
                'type'      => $files['type'],
                'tmp_name'  => $files['tmp_name'],
                'size'      => $files['size'],
                 'error'     => $files['error']
            );
            $uploader->addFile($file);
            if(!$uploader->file_info())
            {
                $data = current($uploader->get_error());
                $this->json_error($data['msg']);
                return false;
            }
			
            $uploader->root_dir(ROOT_PATH);
            $dirname = 'data/files/mall/report';

            $filename  = $uploader->random_filename();
            $file_path = $uploader->save($dirname, $filename);
             /* 处理文件入库 */
            $data = array(
                'store_id'  => $user_id,
                'file_type' => $file['type'],
                'file_size' => $file['size'],
                'file_name' => $file['name'],
                'file_path' => $file_path,
                'belong'    => BELONG_REPORT,
                'add_time'  => gmtime(),
            );
			
            $file_id = $upload_mod->add($data);
            if (!$file_id)
            {
                $data = current($uf_mod->get_error());
                $this->json_error($data['msg']);
                return false;
             }
			 
             $data['file_id'] = $file_id;
			 $this->json_result($data);
         }
         elseif ($files['error'] == UPLOAD_ERR_NO_FILE)
         {
             $this->json_error(Lang::get('file_empty'));
             return false;
         }
         else
         {
             $this->json_error(Lang::get('sys_error'));
             return false;
         }
	}
	
	function drop_image()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		$uploadedfile_mod = &m('uploadedfile');
        $uploadedfile = $uploadedfile_mod->get(array(
            'conditions' => "f.file_id = '$id' AND f.store_id = ".$this->visitor->get('user_id')
        ));
        if ($uploadedfile)
        {
            if ($uploadedfile_mod->drop($id))
            {
                // 删除文件
                if (file_exists(ROOT_PATH . '/' . $uploadedfile['file_path']))
                {
                       @unlink(ROOT_PATH . '/' . $uploadedfile['file_path']);
                }

                $this->json_result($id);
                return;
            }
            $this->json_result($id);
            return;
        }
		
        $this->json_error(Lang::get('no_image_droped'));
    }

}
?>