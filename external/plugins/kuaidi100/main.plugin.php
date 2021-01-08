<?php

/**
 * MiMall（#）
 *
 * 快递跟踪
 *
 * @return  array
 */
class Kuaidi100Plugin extends BasePlugin
{
	var $_url1 	 	= null;
	var $_url2 		= null;
	var $_url3		= null;
    var $_config 	= array();
	var $_data   	= array();
    
    function __construct($data, $plugin_info)
    {
        $this->Kuaidi100Plugin($data, $plugin_info);
    }
    function Kuaidi100Plugin($data, $plugin_info)
    {
        $this->_config 		= $plugin_info;
		$this->_data   		= $data;
		
		$this->_url1		= 'http://poll.kuaidi100.com/poll/query.do'; // 企业版 返回JSON 稳定
		$this->_url2    	= 'http://api.kuaidi100.com/api?id='.$this->_config['key']; // 免费版 返回JSON 不稳定
		$this->_url3   		= 'http://www.kuaidi100.com/applyurl?key='.$this->_config['key']; // 免费版 返回固定格式的html，并带广告 较稳定，体验不友好
		
        parent::__construct($data, $plugin_info);
    }
    function execute()
    {
		if (defined('IN_BACKEND') && IN_BACKEND === true){
            return; // 后台无需执行
        }
		
		$data = array();
		
		// 物流公司名称及运单号
		$data['express_num'] = $this->_data['nu'];
		$all_express_company = include_once(ROOT_PATH . '/data/express_company.inc.php');
		if(is_array($all_express_company))
		{
			foreach($all_express_company as $key=>$val){
				if($key==$this->_data['com']){
					$data['express_company'] = $val;
					break;
				}
			}
		}
		
		// 企业版优先
		if(trim($this->_config['customer']))
		{
			$post['customer'] = $this->_config['customer'];
			$post['param'] = json_encode(array('com' => $this->_data['com'], 'num' => $this->_data['nu']));
			$post['sign']   = strtoupper(md5($post['param'] . $this->_config['key'] . $this->_config['customer']));
	
			$o = "";
			foreach ($post as $k=>$v) {
				$o.= "$k=".urlencode($v)."&"; //默认UTF-8编码格式
			}
			$post = substr($o,0,-1);

			$result = $this->curl($this->_url1, 'POST', $post);
			$return = str_replace("\"",'"', $result);
			$return = json_decode($return, true);
			
			// 快递单当前签收状态，包括0在途中、1已揽收、2疑难、3已签收、4退签、5同城派送中、6退回、7转单等7个状态，其中4-7需要另外开通才有效
			if(isset($return['state']) && in_array($return['state'], array(0,1,2,3,4,5,6,7)))
			{
				$return['status'] = 1; // 兼容免费版接口状态值（企业版：返回200，免费版返回1）
				$data = array_merge($data, $return);
			}
		}
		
		// 免费版
		else
		{
			$url = $this->_url2 . '&com='.$this->_data['com'].'&nu='.$this->_data['nu'].'&show=2&muti=1&order=desc';
			
			$get_content = $this->curl($url);
			$return = json_decode($get_content,true);
			
			/* status 查询的结果状态。0：运单暂无结果，1：查询成功，2：接口出现异常，408：验证码出错（仅适用于APICode url，可忽略) */  
			if($return && ($return['status'] == 0 || $return['status'] == 1))
			{
				$data = array_merge($data, $return);
			}
			else
			{
				/* 调用第三个网关，因为上面的网关不支持 EMS、顺丰和申通，返回的是一个url地址 */
				$url = $this->_url3 . '&com='.$this->_data['com'].'&nu='.$this->_data['nu'];
				$get_content = $this->curl($url);
				$return = json_decode($get_content,true);
				if($return['status']==0 || $return['status']==1){
					$data['url'] = $get_content;
				}
				else
				{
					$data = array_merge($data, $return);
				}
			}
		}

		return $data;
	}
	
	function curl($url, $method = 'GET', $post = array(), $cacert_url = '', $input_charset = '')
	{
		if (trim($input_charset) != '') {
			$url = $url."_input_charset=".$input_charset;
		}
		
		//初始化curl
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		if($cacert_url) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
			curl_setopt($ch, CURLOPT_CAINFO, $cacert_url);//证书地址
		}
		
		//设置超时
		//curl_setopt($ch, CURLOP_TIMEOUT, $this->curl_timeout);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		if(strtoupper($method) == 'POST'){
			curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		}
		
		//运行curl，结果以jason形式返回
		$res = curl_exec($ch);
		//var_dump( curl_error($ch) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
		curl_close($ch);
		return $res;
	}
	
	
}

?>