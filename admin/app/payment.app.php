<?php

/**
 *    支付方式管理控制器
 *    @usage    none
 */
class PaymentApp extends BackendApp
{
    function index()
    {
        /* 读取已安装的支付方式 */
        $model_payment =& m('payment');
        $payments      = $model_payment->get_builtin();
        $white_list    = $model_payment->get_white_list();
        foreach ($payments as $key => $value)
        {
            $payments[$key]['system_enabled'] = in_array($key, $white_list);
        }
        $this->assign('payments', $payments);
		
		$this->import_resource(array(
            'style'  => 'res:style/jqtreetable.css')
        );
        $this->display('payment.index.html');
    }

    /**
     *    启用
     *
     *    @return    void
     */
    function enable()
    {
        $code = isset($_GET['code'])    ? trim($_GET['code']) : 0;
        if (!$code)
        {
            $this->json_error('no_such_payment');
            return;
        }
        $model_payment =& m('payment');
        if (!$model_payment->enable_builtin($code))
        {
			$error = current($model_payment->get_error());
            $this->json_error($error['msg']);
            return;
        }
		
		if(in_array($code, array('cod'))) {
        	$this->json_result('','enable_payment_successed');
		} else {
			$this->json_result(array('ret_url'=>'index.php?app=payment&act=conf&code='.$code),'enable_payment_successed');
		}

    }

    /**
     *    禁用
     *

     *    @return    void
     */
    function disable()
    {
        $code = isset($_GET['code'])    ? trim($_GET['code']) : 0;
        if (!$code)
        {
            $this->json_error('no_such_payment');

            return;
        }
        $model_payment =& m('payment');
        if (!$model_payment->disable_builtin($code))
        {
			$error = current($model_payment->get_error());
            $this->json_error($error['msg']);

            return;
        }
		if($model_payment->get("store_id=0 AND payment_code='{$code}'"))
		{
			$model_payment->drop("store_id=0 AND payment_code='{$code}'");
		}
        $this->json_result('','disable_payment_successed');
    }
	
	/**
     *    配置
     *
     *    @author    mimall
     *    @return    void
     */
    function conf()
    {
        $code = isset($_GET['code']) ? trim($_GET['code']) : 0;
        if (!$code)
        {
            $this->json_error('no_such_payment');

            return;
        }
	
		$model_payment =& m('payment');
        if (!$model_payment->in_white_list($code))
        {
            $this->json_error('payment_disable');

            return;
        }
	
		if(in_array($code, array('cod')))
		{
			
			$this->json_error('can_not_conf');

            return;
		}
		
		$payment = $model_payment->get_builtin_info($code);
        if (!$payment)
        {
            $this->json_error('no_such_payment');

            return;
        }
		
		$payment_info = $model_payment->get("store_id = 0 AND payment_code='{$code}'");
		
        if (!IS_POST)
        {
			$payment['payment_id']  =   $payment_info['payment_id'];
            $payment['payment_desc']=   $payment_info['payment_desc'];
            $payment['enabled']     =   $payment_info['enabled'];
            $payment['sort_order']  =   $payment_info['sort_order'];
            $this->assign('config', unserialize($payment_info['config']));
            $this->assign('payment', $payment);
            $this->display('payment.form.html');
        }
        else
        {
			$config = $_POST['config'];
			
			/* 此处目前只有银联支付接口用到，作用为：当再次提交表单后能保留之前上传的密钥文件 */
			if($payment_info) {
				$payment_info['config'] = unserialize($payment_info['config']);
				if($payment_info['config']) $config = array_merge($payment_info['config'], $config);
			}
			
			/* 处理文件上传（目前只有银联支付接口需要上传密钥文件）*/
			if(in_array($code, array('chinapay')))
			{
				$files = $this->_upload_file($code);
				if(is_array($files) && count($files) > 0) {
					$config = array_merge($config, $files);
				}
			}
			
			$data = array(
                'store_id'      => '0', // 不能为数值型的0，为0的话提交不了
                'payment_name'  => strstr($payment['name'],'微信') !== false ? '微信支付' : $payment['name'],
                'payment_code'  => $code,
                'payment_desc'  => trim($_POST['payment_desc']),
                'config'        => $config,
                'is_online'     => $payment['is_online'],
                'enabled'       => 1,
                'sort_order'    => $_POST['sort_order'],
            );
			
			if (!empty($payment_info))
			{
				$model_payment->edit($payment_info['payment_id'],$data);
				$this->json_result('','edit_payment_successed');
			}
			else
			{
				if (!$payment_id = $model_payment->add($data))
				{
					$this->json_error('edit_payment_failed');
	
					return;
				}
				
				$this->json_result('','edit_payment_successed');
			}
        }
    }
	
	/**
     *    上传接口密钥文件等
     *
     *    @author    MiMall
     *    @return    void
     */
	 
	function _upload_file($code)
    {
        import('uploader.lib');
		
		$data = $files = array();

		for($i = 0; $i < count($_FILES); $i++)
		{
            foreach ($_FILES['config'] as $key => $value)
            {
				foreach($value as $k=>$v){
                	$files[$k][$key] = $v;
				}
            }  
		}
		
		if($files) 
		{
			foreach($files as $key=>$file)
			{
				if ($file['error'] == UPLOAD_ERR_OK && $file !='')
				{
					$uploader = new Uploader();
					$uploader->allowed_type('key');
					$uploader->addFile($file);
					if ($uploader->file_info() === false)
					{
						$error = current($uploader->get_error());
						$this->json_error($error['msg']);
						exit;
					}
					$uploader->root_dir(ROOT_PATH);
					$data[$key] = $uploader->save('data/files/mall/cert/'.$code, $key);
				}	
			}
		}
        return $data;
    }
}

?>