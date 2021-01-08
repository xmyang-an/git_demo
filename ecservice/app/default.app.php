<?php

class DefaultApp extends MallbaseApp
{
	function boutique()
    {
		list($check, $loginResult) = TRUE;//parent::_checkLogin();
		if($check === FALSE)
		{
			$result = array(
				'status' => 'FAILED',
				'errorMsg' => $loginResult['errorMsg']
			);
		}
		else
		{
			$post = parent::_getPostData();
		
			$conditions = '';
			if($_POST['keyword']) {
				$conditions .= ' goods_name LIKE "'.$_POST['keyword'].'%"';
			}
			
			$order = 'goods_id DESC';
			if($_POST['sort'] && in_array($_POST['sort'], array('price', 'sales'))) {
				$order = $_POST['sort'] . ' DESC';
			}
			
			$goods_mod = &m('goods');
			
			$page = $this->_get_page(10);
			$goodsList = $goods_mod->find(array(
				'conditions' => $conditions,
				'limit'     =>$page['limit'],
				'fields' => 'goods_name, default_image, goods_id, price, cate_id',
				'order' => $order,
				'count'     =>true,
			));
			foreach($goodsList as  $key => $goods) {
				empty($goods['default_image']) && $goods['default_image'] = Conf::get('default_goods_image');
				$goodsList[$key]['default_image'] = SITE_URL . '/' . $goods['default_image'];
			}
			
			$goodsList = array_values($goodsList);
			
			$result = array(
				'status'=> 'SUCCESS',
				'title' => "首页推荐商品",
				'retval'=> $goodsList
			);
		}
		echo json_encode($result);
    }
	
	/*
    function index()
    {
		$post = parent::_getPostData();
		$service = $post['service']; 
		
		list($app, $act) = $this->getappact($service);
		
		$url = SITE_URL . '/'.API_NAME.'/index.php?app='.$app.'&act='.$act;
		
		$result = $this->getHttpResponsePOST($url, '', json_encode($post));
		
		echo $result;
    }
	
	function getappact($service = '')
	{
		$service = strtolower($service);
		
		$result = array(
			'getaccesstoken' 		=> array('getaccesstoken', 'index'),
			'getproductcategory' 	=> array('gcategory', 'index'),
			'getproductpool' 		=> array('search', 'getProductPool'),
			'getproductdetail' 		=> array('goods', 'getProductDetail'),
			'getproductimage'		=> array('goods', 'getProductImage'),
			'getproductonshelvesinfo'=> array('goods', 'getProductOnShelvesInfo'),
			'querycountprice'		=> array('goods', 'queryCountPrice'),
			'getproductinventory' 	=> array('goods', 'getProductInventory'),
			'createorder'			=> array('order', 'createOrder'),
			'getprovinceinfo'		=> array('region', 'getProvinceInfo'),
			'getcityinfo'			=> array('region', 'getCityInfo'),
			'getdistrictinfo'		=> array('region', 'getDistrictInfo'),
			'gettowninfo'			=> array('region', 'getTownInfo'),
			
			
			
			
			'deleterejectorder' 	=> array('order', 'deleteRejectOrder'),
			'getorderdetail'		=> array('order', 'getOrderDetail'),
			'getorderstatus'		=> array('order', 'getOrderStatus'),
			'getorderlogist'		=> array('order', 'getOrderLogist'),
			'getshipcarriage'		=> array('order', 'getShipCarriage'),
			'confirminvoice'		=> array('order', 'confirmInvoice'),
			
		);
		
		if($service && isset($result[$service])) {
			return $result[$service];
		} else return '';
	}
	
	/**
	 * 远程获取数据，POST模式
	 * 注意：
	 * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
	 * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
	 * @param $url 指定URL完整路径地址
	 * @param $cacert_url 指定当前工作目录绝对路径
	 * @param $para 请求的数据
	 * @param $input_charset 编码格式。默认值：空值
	 * return 远程输出的数据
	 */
	/*function getHttpResponsePOST($url, $cacert_url = '', $para, $input_charset = '') {

		if (trim($input_charset) != '') {
			$url = $url."_input_charset=".$input_charset;
		}
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
		
		if($cacert_url) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
			curl_setopt($curl, CURLOPT_CAINFO, $cacert_url);//证书地址
		}
		// 0 ->  过滤HTTP头
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(  
       	 	"Content-Type: application/json; charset=utf-8",  
        	"Content-Length: " . strlen($para))  
    	); 
			
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
		curl_setopt($curl,CURLOPT_POST,true); // post传输数据
		curl_setopt($curl,CURLOPT_POSTFIELDS,$para);// post传输数据
		$responseText = curl_exec($curl);
		//var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
		curl_close($curl);
		
		//$responseText = json_decode($responseText, true);
		//print_r($responseText);exit;
		
		return $responseText;
	}*/

}

?>
