<?php

class WebimApp extends MallbaseApp
{
	var $_member_mod;
	var $_store_mod;
	var $_webim_log;
	var $_webim_onlineuser;
	
	function __construct()
    {
        $this->WebimApp();
    }
    function WebimApp()
    {   
        parent::__construct();
		$this->_member_mod = &m('member');
		$this->_store_mod  = &m('store');
		$this->_webim_log     = &m('webim_log');
		$this->_webim_onlineuser = &m('webim_onlineuser');
    }
	
	function getList()
	{
		list($avatar, $username) = $this->getAvatarById($this->visitor->get('user_id'));
		$result = array('mine' => array('username' => $username, 'id' => $this->visitor->get('user_id'), 'avatar' => $avatar, 'sign' => ''));
		
		// 好友数组
		$list = array();
			
	   	if($this->visitor->has_login)
	   	{
			$imlog = $this->_webim_log->find(array(
				// 跟我有过聊天的都算好友 不管是别人发起会话，还是我主动发起会话
				'conditions' => 'fromid='.$this->visitor->get('user_id').' OR toid='.$this->visitor->get('user_id'),
				'fields'     => 'fromid,toid',
				'order'		 => 'logid DESC',
			));
			
			if($imlog)
			{
				$friendList = array();
				
				// 把自己也加入到好友列表去，如果不加的话， 会话只能单向
				$friendList[] = $this->visitor->get('user_id');
				foreach($imlog as $log)
				{
					if($log['fromid'] == $this->visitor->get('user_id')) {
						$friendList[] = $log['toid'];
					} else $friendList[] = $log['fromid'];
				}
				
				$friendList = array_unique($friendList);
				$friendList = array_values($friendList);
				
				foreach($friendList as $friend)
				{
					list($avatar, $username) = $this->getAvatarById($friend);
					
					$list[] = array('username' => $username, 'id' => $friend, 'avatar' => $avatar, 'sign' => '');
				}
				
				// 如果好友当前不在线，则剔除（排除自己）
				foreach($list as $key => $friend)
				{
					if($friend['id'] != $this->visitor->get('user_id')) {
						
						// 30分钟内上线（或发过言的）都算在线
						$interval = 30 * 60;
						if(!$online = $this->_webim_onlineuser->get("user_id={$friend['id']}")) {
							unset($list[$key]);	
						} elseif ($online['lasttime'] < (gmtime() - $interval)) {
							unset($list[$key]);
						}
					}
				}
				$list = array_values($list);
			}
		}
		
		$result['friend'][] = array("groupname" => "我的好友", "id" => 1, "list" => $list);
	
		$result = array('code' => 0, 'msg'  => '', 'data' => $result);
		
		echo ecm_json_encode($result);
	}
	
	// 获取当前访客或者指定客服的信息
	function getUser()
	{
		if($this->visitor->has_login)
		{
			$userInfo = array();
			
			$toid = intval($_GET['toid']);
			
			// 如果不传toid 说明是读取当前访客的信息，如果传toid，说明是获取客服的信息
			if(!$toid) {
				$user_id = $this->visitor->get('user_id');
			} else $user_id = $toid;
			
			$info = $this->_member_mod->get(array('conditions' => 'user_id='.$user_id, 'fields' => 'user_id'));
						
			if($info) {
				
				list($avatar, $username) = $this->getAvatarById($info['user_id']);
				
				$userInfo = array('username' => $username, 'id' => $info['user_id'], 'avatar' => $avatar, 'type' => 'friend');
				
				if(!$toid) {
					$userInfo['groupid'] = 1;
					$userInfo['sign']    = '';
				}
				else
				{
					// 客服
					$userInfo['name'] 	= $userInfo['username'];
					$userInfo['type']   = 'friend';
				}
			}
		}
		else
		{
			$this->json_error(Lang::get('login_please'));
			return;
		}
		
		$this->json_result($userInfo);
	}
	
	function getChatLog()
	{
		$toid = intval($_GET['id']);
		$type = in_array($_GET['type'], array('friend', 'group')) ? $_GET['type'] : 'friend';
		$fromid = $this->visitor->get('user_id');
		$limit = intval($_GET['limit']) ? intval($_GET['limit']) : 20;
		
		$conditions = "( (fromid={$fromid} AND toid={$toid}) OR (fromid={$toid} AND toid={$fromid}) ) AND type='{$type}'";
		
		$imlog = $this->_webim_log->find(array(
			'conditions' 	=> $conditions,
			'limit'   		=> $limit,
			'order'   		=> 'logid DESC',
			'fields'  		=> 'add_time, content, fromid, fromName, toid',
		));
		
		$results = array();
		
		// 排序，让最后发言的在后面
		array_multisort($imlog, SORT_ASC);
		
		foreach($imlog as $log)
		{
			list($avatar) = $this->getAvatarById($log['fromid']);
					
			$result = array(
				'avatar' 	=> $avatar,
				'content' 	=> $log['content'],
				'id'     	=> $log['toid'],
				'timestamp' => local_date('Y-m-d H:i:s', $log['add_time']),
				'type'   	=> 'friend',
				'username' 	=> $log['fromName']
			);
			if($log['toid'] == $toid) {
				$result['mine'] = true;
			}
			
			// 将显示出来的数据全部设置为已读（即：减少“未读”的数量）
			$this->_webim_log->edit($log['logid'], array('unread' => 0));
			
			$results[] = $result;
		}
		
		echo $this->json_result($results); 
	}
	
	function getLog()
	{
		$toid = intval($_GET['id']);
		$type = in_array($_GET['type'], array('friend', 'group')) ? $_GET['type'] : 'friend';
		$fromid = $this->visitor->get('user_id');
		
		$page = $this->_get_page(8);
		
		$conditions = "( (fromid={$fromid} AND toid={$toid}) OR (fromid={$toid} AND toid={$fromid}) ) AND type='{$type}'";
		
		// （重写分页）首次加载，显示最后一页的内容，而非第一页（这样才符合聊天记录的现实情况）
		if(!$_GET['page'])
		{
			$records = $this->_webim_log->getAll("SELECT count(logid) as count FROM {$this->_webim_log->table} WHERE " . $conditions);
			$records = current($records);
			if(intval($records['count']) >　0) {
				$page_count = ceil($records['count'] / $page['pageper']);
				$start = ($page_count -1) * $page['pageper'];
				$page = array_merge($page, array('limit' => "{$start},{$page['pageper']}", 'curr_page' => $page_count));
			}	
		}
		
		$imlog = $this->_webim_log->find(array(
			'conditions' => $conditions,
			'limit'   => $page['limit'],
			'order'   => 'logid ASC',
			'fields'  => 'add_time, formatContent, fromid, fromName, toName, toid',
			'count'   => true
		));
		
		foreach($imlog as $key => $log)
		{
			list($avatar) = $this->getAvatarById($log['fromid']);
			$imlog[$key]['avatar'] = $avatar;
			
			// 将显示出来的数据全部设置为已读（即：减少“未读”的数量）
			$this->_webim_log->edit($log['logid'], array('unread' => 0));
		}
		
		$page['item_count'] = $this->_webim_log->getCount();
		$this->_format_page($page, 5);
        $this->assign('page_info', $page);
		$this->assign('imlog', $imlog);
		$this->display('webim.getlog.html');
	}
	
	// 在聊天窗上传图片
	function uploadImage()
	{
		$result = array("code" => 0, "msg" => "", "data" => NULL); 

		import('uploader.lib');
        $file = $_FILES['file'];
        if ($file['error'] == UPLOAD_ERR_OK)
        {
            $uploader = new Uploader();
            $uploader->allowed_type(IMAGE_FILE_TYPE);
            $uploader->addFile($file);
            $uploader->root_dir(ROOT_PATH);
            $url = $uploader->save('data/files/mall/im', $uploader->random_filename());
			
			$result["data"]["src"] = SITE_URL . '/' . $url;
        }
		else
		{
			$result["code"] = 1;
			$result["msg"]  = "upload fail!";
		}

        echo json_encode($result);
	}
	
	// 检查用户是否有发送信息的权限
	function checkUserForbid()
	{
		$user_id = intval($_GET['uid']);
		
		$user_info = $this->_member_mod->get(array('conditions' => 'user_id='.$user_id, 'fields' => 'imforbid'));
		
		if(!$user_info || $user_info['imforbid']) {
			echo json_encode(1);
		} else echo json_encode(0);
	}
	
	function setUserOut()
	{
		$token = 'abcdefghijklmn1234556789';
		
		$uid	   = intval($_GET['uid']);
		$sign      = trim($_GET['sign']);
		
		// 签名验证，防止人为恶意删除，导致下线用户不准确		
		if(md5($uid.$token) == $sign) {
			$this->_webim_onlineuser->drop("user_id={$uid}");
		}
	}

	// 上线的时候才会执行（发言不执行）
	function getAllOnlineUser()
	{
		$token = 'abcdefghijklmn1234556789';
		
		$result = array();
		
		$client_id = trim($_GET['client_id']);
		$uid	   = intval($_GET['uid']);
		$sign      = trim($_GET['sign']);
		
		// 签名验证，防止人为恶意请求，导致上线用户不准确	
		if(md5($uid.$client_id.$token) == $sign)
		{
			$now      = gmtime();
			
			if($onid = $this->_webim_onlineuser->get("user_id={$uid}"))
			{
				$this->_webim_onlineuser->edit($onid, array('client_id' => $client_id, 'lasttime' => $now));
			}
			else
			{
				$this->_webim_onlineuser->add(array('user_id' => $uid, 'client_id' => $client_id, 'lasttime' => $now));
			}
			
			// 找出所有在30分钟内上线（或发言）的用户，当作是在线用户， 不在此时间内的删除
			$interval = 30 * 60;
			$this->_webim_onlineuser->drop("lasttime < {$now} - {$interval}");
			
			// 找出符合条件的
			$allOnline = $this->_webim_onlineuser->find(array(
				'order' => 'lasttime DESC',
			));
	
			if($allOnline)
			{
				foreach($allOnline as $online)
				{
					$userInfo = array();
					
					$info = $this->_member_mod->get(array('conditions' => 'user_id='.$online['user_id'], 'fields' => 'user_name, user_id, portrait'));
							
					if($info) 
					{
						list($avatar, $username) = $this->getAvatarById($online['user_id']);
						
						$userInfo = array('username' => $username, 'id' => $online['user_id'], 'avatar' => $avatar, 'type' => 'friend');
						
						$result['f_user'][$online['user_id']] = $online['client_id'];
						$result['f_uuid'][$online['user_id']] = $online['user_id'];
						$result['f_uuser'][$online['user_id']]= $userInfo;
					}
					else
					{
						$this->_webim_onlineuser->drop("user_id={$online['user_id']}");
					}
				}
			}
		}
		
		echo json_encode($result);
	}
	
	// 获取用户头像
	function getAvatarById($uid = 0, $useLogoIfSeller = TRUE)
	{
		if(!$uid)
		{
			$avatar 	=  SITE_URL .'/' . Conf::get('default_user_portrait');
			$username 	= Lang::get('guest');
		}
		else
		{
			$member = $this->_member_mod->get(array('conditions' => 'user_id=' . $uid, 'fields' => 'user_name, portrait'));
			empty($member['portrait']) && $member['portrait'] = Conf::get('default_user_portrait');
				
			$avatar = SITE_URL .'/' . $member['portrait'];
			$username = $member['user_name'];
			
			if($useLogoIfSeller === TRUE)
			{
				// 如果该好友是店家
				if($store_info = $this->_store_mod->get(array('conditions' => 'store_id='. $uid , 'fields' => 'store_name, store_logo'))) {
					empty($store_info['store_logo']) && $store_info['store_logo'] = Conf::get('default_store_logo');
					$avatar	 	= SITE_URL .'/' . $store_info['store_logo'];
					$username	= $store_info['store_name'];
				}
			}
		}
		return array($avatar, $username);
	}
	
	function saveTalk()
	{
		// 加密签名(跟Events.php 文件中的一致）
		$token = 'abcdefghijklmn1234556789';
		
		$from 			= intval($_POST['from']);
		$fromName       = stripslashes($_POST['fromName']);
		$to   			= intval($_POST['to']);
		$toName       	= stripslashes($_POST['toName']);
		$type           = trim($_POST['type']);
		$content    	= stripslashes($_POST['content']);
		$formatContent 	= unserialize(stripslashes($_POST['formatContent']));
		$sign       	= $_POST['sign'];
		
		$result = 0;
		
		if(trim($content) != '')
		{
			$local_sign = md5($from.$to.$type.$content.$token);
			
			//logResult1('kkk', $local_sign.":".$sign);
			if($sign == $local_sign)
			{				
				$result = $this->_webim_log->add(array(
					'fromid' 		=> $from,
					'fromName'      => $fromName,
					'toid'   		=> $to,
					'toName'		=> $toName,
					'type'			=> $type,
					'content'		=> $content,
					'formatContent' => $formatContent,
					'add_time' 		=> gmtime()
				));
				
				// 用户发言后，即更新在线用户时间（以免用户30分钟内不刷新页面，导致被认为是离线用户删除处理
				$this->_webim_onlineuser->edit("user_id={$from}", array('lasttime' => gmtime()));
			}
			
		}
		echo $result;
	}
}

?>
