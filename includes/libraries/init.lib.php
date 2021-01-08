<?php

define("CUSTOMERID","");
define('LOCK_FILE', ROOT_PATH . '/data/init.lock');

if(!file_exists(LOCK_FILE)) 
{
	// 如果有手机版的，需要加上此项
	if(!defined('CHARSET')) {
		define('CHARSET', substr(LANG, 3));
	}
	
	Psmb_init::_create_table();
	
	/* 创建完表后，生成锁定文件 */
	file_put_contents(LOCK_FILE,1);
}

class Psmb_init 
{
	public static function _create_table()
	{	$sql = "CREATE TABLE IF NOT EXISTS `". DB_PREFIX . "groupbuy` (
  			  group_id int(10) unsigned NOT NULL AUTO_INCREMENT,
			  group_name varchar(255) NOT NULL DEFAULT '',
			  group_image varchar(255) NOT NULL,
			  group_desc varchar(255) NOT NULL DEFAULT '',
			  start_time int(10) unsigned NOT NULL DEFAULT '0',
			  end_time int(10) unsigned NOT NULL DEFAULT '0',
			  each_expire_time int(10) unsigned NOT NULL DEFAULT '0',
			  goods_id int(10) unsigned NOT NULL DEFAULT '0',
			  store_id int(10) unsigned NOT NULL DEFAULT '0',
			  spec_price text NOT NULL,
			  min_quantity smallint(5) unsigned NOT NULL DEFAULT '0',
			  max_per_user smallint(5) unsigned NOT NULL DEFAULT '0',
			  state tinyint(3) unsigned NOT NULL DEFAULT '0',
			  recommended tinyint(3) unsigned NOT NULL DEFAULT '0',
			  views int(10) unsigned NOT NULL DEFAULT '0',
			  PRIMARY KEY (group_id),
			  KEY goods_id (goods_id),
			  KEY store_id (store_id)
		) ENGINE = MYISAM DEFAULT CHARSET=".str_replace('-','',CHARSET).";";
		db()->query($sql);
		
		$sql = "CREATE TABLE IF NOT EXISTS `". DB_PREFIX . "group_team` (
  			  team_id int(10) unsigned NOT NULL AUTO_INCREMENT,
			  group_id int(10) unsigned NOT NULL,
			  user_id int(10) unsigned NOT NULL,
			  user_name varchar(255) NOT NULL DEFAULT '',
			  number int(10) unsigned NOT NULL,
			  add_time int(10) unsigned NOT NULL DEFAULT '0',
			  status tinyint(3) NULL,
			  refund tinyint(3) NULL,
			  PRIMARY KEY (team_id)
		) ENGINE = MYISAM DEFAULT CHARSET=".str_replace('-','',CHARSET).";";
		db()->query($sql);
		
		$sql = "CREATE TABLE IF NOT EXISTS `". DB_PREFIX . "material` (
  			`id` int(11) NOT NULL AUTO_INCREMENT,
  			`store_id` int(10) NOT NULL DEFAULT 0,
			`url` varchar(255) NOT NULL,
			`link` varchar(255) NOT NULL,
			`name` varchar(255) NOT NULL,
			`sort_order` int(10) NOT NULL default 0,
			`type` varchar(255)  NOT NULL ,
			`device` varchar(255)  NOT NULL ,
			`if_show` tinyint(3)  NOT NULL default 0,
  			PRIMARY KEY (`id`)
		) ENGINE = MYISAM DEFAULT CHARSET=".str_replace('-','',CHARSET).";";
		db()->query($sql);
		
		$result = db()->getAll('SHOW COLUMNS FROM '. DB_PREFIX . 'order');
		$fields = array();
		foreach($result as $v) {
			$fields[] = $v['Field'];
		}
		
		if(!in_array('group_id', $fields)){
			$sql = 'ALTER TABLE `'.DB_PREFIX.'order` ADD `group_id` int(11) NOT NULL default 0';
			db()->query($sql);
		}
		
		if(!in_array('team_id', $fields)){
			$sql = 'ALTER TABLE `'.DB_PREFIX.'order` ADD `team_id` int(11) NOT NULL default 0';
			db()->query($sql);
		}
		
		
		$result = db()->getAll('SHOW COLUMNS FROM '. DB_PREFIX . 'member');
		$fields = array();
		foreach($result as $v) {
			$fields[] = $v['Field'];
		}
		if(!in_array('referid', $fields)){
			$sql = 'ALTER TABLE `'.DB_PREFIX.'member` ADD `referid` int(11) NOT NULL default 0';
			db()->query($sql);
		}
		
		  $sql =" CREATE TABLE IF NOT EXISTS `". DB_PREFIX . "merchant` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `user_id` int(11) NOT NULL,
				  `appId` varchar(32) NOT NULL,
				  `appKey` varchar(32) NOT NULL,
				  `name` varchar(255) NOT NULL,
				  `closed` int(1) NOT NULL,
				  `add_time` int(11) NOT NULL,
				  PRIMARY KEY (`id`)
			) ENGINE = MYISAM DEFAULT CHARSET=".str_replace('-','',CHARSET).";";
			db()->query($sql);
			$sql =" CREATE TABLE IF NOT EXISTS `". DB_PREFIX . "merchantLog` (
				  `logid` int(11) NOT NULL AUTO_INCREMENT,
				  `appId` varchar(32) NOT NULL,
				  `token` varchar(255) NOT NULL,
				  `add_time` int(11) NOT NULL,
				  `expired` int(11) NOT NULL,
				  PRIMARY KEY (`logid`)
			) ENGINE = MYISAM DEFAULT CHARSET=".str_replace('-','',CHARSET).";";
			
				db()->query($sql);
		$result = db()->getAll('SHOW COLUMNS FROM '. DB_PREFIX . 'goods');
		$fields = array();
		foreach($result as $v) {
			$fields[] = $v['Field'];
		}
		if(!in_array('refer_reward_1', $fields)){
			$sql = 'ALTER TABLE `'.DB_PREFIX.'goods` ADD `refer_reward_1` decimal(10,4) NOT NULL default 0';
			db()->query($sql);
		}
		
		if(!in_array('refer_reward_2', $fields)){
			$sql = 'ALTER TABLE `'.DB_PREFIX.'goods` ADD `refer_reward_2` decimal(10,4) NOT NULL default 0';
			db()->query($sql);
		}
		
		if(!in_array('refer_reward_3', $fields)){
			$sql = 'ALTER TABLE `'.DB_PREFIX.'goods` ADD `refer_reward_3` decimal(10,4) NOT NULL default 0';
			db()->query($sql);
		}
		
		$result = db()->getAll('SHOW COLUMNS FROM '. DB_PREFIX . 'order');
		$fields = array();
		foreach($result as $v) {
			$fields[] = $v['Field'];
		}
		if(!in_array('referid', $fields)){
			$sql = 'ALTER TABLE `'.DB_PREFIX.'order` ADD `referid` text NOT NULL';
			db()->query($sql);
		}
		
		if(!in_array('refer_reward', $fields)){
			$sql = 'ALTER TABLE `'.DB_PREFIX.'order` ADD `refer_reward` text NOT NULL';
			db()->query($sql);
		}
		if(!in_array('allowenceid', $fields)){
			$sql = 'ALTER TABLE `'.DB_PREFIX.'order` ADD `allowenceid` text NOT NULL';
			db()->query($sql);
		}
		
		if(!in_array('allowence', $fields)){
			$sql = 'ALTER TABLE `'.DB_PREFIX.'order` ADD `allowence` text NOT NULL';
			db()->query($sql);
		}
		$result = db()->getAll('SHOW COLUMNS FROM '. DB_PREFIX . 'deposit_setting');
		$fields = array();
		foreach($result as $v) {
			$fields[] = $v['Field'];
		}
		if(!in_array('withdraw_rate', $fields)){
			$sql = 'ALTER TABLE `'.DB_PREFIX.'deposit_setting` ADD `withdraw_rate` decimal(10,3) NOT NULL default 0';
			db()->query($sql);
		}
	}
	
	function check_view_device($path = 'mobile', $redirect = TRUE)
	{
		if (defined('IN_BACKEND') && IN_BACKEND === true) {
			return;
		}
		
		$result = FALSE;
		
		if(isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
			$result = TRUE;
		}
		if(isset ($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], "wap")) {
			//找不到为flase,否则为true
			$result = TRUE;
		}
		if(isset($_SERVER['HTTP_USER_AGENT'])) {
			//此数组有待完善
			$clientkeywords = array (
			'nokia',
			'sony',
			'ericsson',
			'mot',
			'samsung',
			'htc',
			'sgh',
			'lg',
			'sharp',
			'sie-',
			'philips',
			'panasonic',
			'alcatel',
			'lenovo',
			'iphone',
			'ipod',
			'blackberry',
			'meizu',
			'android',
			'netfront',
			'symbian',
			'ucweb',
			'windowsce',
			'palm',
			'operamini',
			'operamobi',
			'openwave',
			'nexusone',
			'cldc',
			'midp',
			'wap',
			'mobile'
			);
			if(preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
				$result = TRUE;
			}
	 
		}
	 
		if (isset ($_SERVER['HTTP_ACCEPT'])) {
			if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
				$result = TRUE;
			}
		}
		
		//  如果是判断客户端后执行跳转
		if($redirect === TRUE)
		{
			$query_string = '';
			if(!empty($_SERVER['QUERY_STRING']))
			{
				$queryArray = explode('&', $_SERVER['QUERY_STRING']);
				foreach($queryArray as $key => $val)
				{
					if(in_array(strtolower($val), array('device=pc', 'device=wap'))) {
						unset($queryArray[$key]);
					}
				}
				$queryArray && $query_string = '?'. implode('&', $queryArray);
			}
			
			$redirect_uri = SITE_URL . "/" . $path;
			if($query_string){
				$redirect_uri .= "/index.php" . $query_string;
			}
		
			//  是手机端
			if($result === TRUE)
			{
				// 如果是手动从手机端跳转到PC端
				if(strtoupper(trim($_GET['device'])) == 'PC' || (strtoupper(trim($_GET['device'])) != 'WAP' && $_SESSION['device'] == 'PC'))
				{
					$_SESSION['device'] = 'PC';
					if(strripos($_SERVER['REQUEST_URI'], '/'.$path) !== FALSE) {
						//header("Location:" . str_replace('/mobile', '', $redirect_uri));
						//exit;
					}
				}
				elseif(strtoupper(trim($_GET['device'])) == 'WAP' || (strtoupper(trim($_GET['device'])) != 'PC' && $_SESSION['device'] == 'WAP'))
				{
					$_SESSION['device'] = 'WAP';
					if(strripos($_SERVER['REQUEST_URI'], '/'.$path) === FALSE) {
						header("Location:" . $redirect_uri);
						exit;
					}
				}
				//  如果没有手动跳转，也没有执行过手动跳转的操作，则自动跳转到手机端
				else
				{
					if(strripos($_SERVER['REQUEST_URI'], '/'.$path) === FALSE) {
						header("Location:" . $redirect_uri);
						exit;
					}
				}
			}
			// 是PC端
			else
			{
				// 如果是手动从PC端跳转到手机端
				if(isset($_GET['device']) && (strtoupper(trim($_GET['device'])) == 'WAP' || (strtoupper(trim($_GET['device'])) != 'PC' && ($_SESSION['device'] == 'WAP'))))
				{
					$_SESSION['device'] = 'WAP';
					if(strripos($_SERVER['REQUEST_URI'], '/'.$path) === FALSE) {
						header("Location:" . $redirect_uri);
						exit;
					}
				}
			}
		}
		//  如果不跳转，仅仅是判断是否为手机端
		else
		{
			return $result;
		}
	}
	
	// 判断后台是否启用快递跟踪插件
	function _check_express_plugin()
	{
		$plugin_inc_file = ROOT_PATH . '/data/plugins.inc.php';
        if (is_file($plugin_inc_file))
        {
            $plugins =  include($plugin_inc_file);
			return isset($plugins['on_query_express']['kuaidi100']);
        }

        return false;
	}
	
	/**
     *    以购物车为单位获取购物车列表及商品项
     */
	function get_carts_top($sess_id, $user_id = 0)
    {
		$where_user_id = $user_id ? " AND user_id={$user_id}" : '';
		
        $cart_items = array();
		$total_count=0;
		$total_amount=0;
		
        $cart_model =& m('cart');
        $cart_items = $cart_model->find(array(
            'conditions'    => 'session_id = ' . "'"  .$sess_id . "'" . $where_user_id,
			'fields'        => '',
        ));
		
		foreach($cart_items as $key=>$val){
			$total_count += $val['quantity'];
			$total_amount += round($val['price'] * $val['quantity'],2);
		}
		
        return array('cart_items' => $cart_items, 'total_count' => $total_count, 'total_amount' => $total_amount);
    }
	
	/* 所有商品类目，头部通用 */
	function get_header_gcategories($amount, $position, $brand_is_recommend=1)
	{
		$gcategory_mod =& bm('gcategory', array('_store_id' => 0));
		$gcategories = array();
		if(!$amount)
		{
			$gcategories = $gcategory_mod->get_list(-1, true);
		}
		else
		{
			$gcategory = $gcategory_mod->get_list(0, true);
			$gcategories = $gcategory;
			foreach ($gcategory as $val)
			{
				$result = $gcategory_mod->get_list($val['cate_id'], true);
				$result = array_slice($result, 0, $amount);
				$gcategories = array_merge($gcategories, $result);
			}
		}
		import('tree.lib');
        $tree = new Tree();
        $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');
		$gcategory_list = $tree->getArrayList(0);
		$i=0;
		$brand_mod=&m('brand');
		$uploadedfile_mod = &m('uploadedfile');	
		foreach($gcategory_list as $k => $v) {
			$gcategory_list[$k]['top']  =  isset($position[$i]) ? $position[$i] : '0px';
			$i++;
			
			$gcategory_list[$k]['brands'] = $brand_mod->find(array(
				'conditions'=>"tag = '".$v['value']."' AND recommended=".$brand_is_recommend, 
				'order'=>'sort_order asc,brand_id desc'
			));
			$gcategory_list[$k]['gads'] = $uploadedfile_mod->find(array(
					'conditions' => 'store_id = 0 AND belong = ' . BELONG_GCATEGORY . ' AND item_id=' . $v['id'],
					'fields' => 'this.file_id, this.file_name, this.file_path,this.link_url',
					'order' => 'add_time DESC'
			));
		}
		$group = array();
		$gcategories = $gcategory_mod->find(array(
			'conditions' => 'parent_id=0 AND if_show=1',
			'fields' => 'groupid',
			'order' => 'sort_order asc'
		));
		foreach($gcategories as $key => $val){
			if($val['groupid']){
				$group['group_'.$val['groupid']][] = $val;
			}else{
				$group[$val['cate_id']][] = $val;
			}
		}
		foreach($group as $k=>$v){
			foreach($v as $k1=>$v1){
				$group[$k][$k1] = $gcategory_list[$v1['cate_id']];
			}
		}
		return array('gcategories'=>$group);
	}
	/* 屏蔽掉当前筛选的品牌 */
	function get_group_by_info_by_brands($by_brands=array(),$param)
	{
		if(!empty($param["brand"])) {
			unset($by_brands[$param['brand']]);
		}
		$brand_mod = &m('brand');
		foreach($by_brands as $key => $val)
		{
			$brand = $brand_mod->get(array('conditions'=>"brand_name='" . addslashes_deep($val['brand']) . "'",'fields'=>'brand_logo'));	
			$by_brands[$key]['brand_logo'] = $brand['brand_logo'];
		}
		return $by_brands;
	}
	/* 屏蔽掉当前筛选的地区 */
	function get_group_by_info_by_region($sql,$param)
	{
		$goods_mod = &m('goods');
		$by_regions = $goods_mod->getAll($sql);
		if(!empty($param["region_id"])){
			foreach($by_regions as $k => $v){
				if($v["region_id"]==$param["region_id"]){
					unset($by_regions[$k]);
				}
			}
		}
		return $by_regions;
	} 
	
	function get_ultimate_store($conditions, $brand)
	{
		$store = array();
		$us_mod = &m('ultimate_store');
		$store_mod = &m('store');
		
		$ultimate_store = $us_mod->get(array('conditions'=>'status=1 ' . $conditions,'fields'=>'store_id,description'));

		if($ultimate_store)
		{
			$store = $store_mod->get(array('conditions'=>'store_id='.$ultimate_store['store_id'],'fields'=>'store_logo,store_name'));
			empty($store['store_logo']) && $store['store_logo'] = Conf::get('default_store_logo');
			
			if($brand && !empty($brand['brand_logo'])) {
				$store['store_logo'] = $brand['brand_logo'];
			}
			$store = array(array_merge($ultimate_store,$store));	
		}

		return $store;
	}
	function get_available_coupon($order = array(), $user_id = 0)
	{
		$time = gmtime();
		$coupon = db()->getAll("SELECT *FROM ".DB_PREFIX."coupon_sn couponsn ".
			"LEFT JOIN ".DB_PREFIX."coupon coupon ON couponsn.coupon_id=coupon.coupon_id ".
			"LEFT JOIN ".DB_PREFIX."user_coupon user_coupon ON user_coupon.coupon_sn=couponsn.coupon_sn ".
			"WHERE (coupon.store_id = 0 OR coupon.store_id = ".$order['store_id'] .") AND couponsn.remain_times >=1 ".
			"AND user_coupon.user_id=".$user_id." ".
			"AND coupon.start_time <= ".$time ." AND coupon.end_time >= ".$time ." AND coupon.min_amount <= ".$order['amount'].' ORDER BY coupon_value desc'	
		);
		
		if(!empty($coupon))
		{
			foreach($coupon as $key=>$val)
			{
				$coupon[$key]['coupon_value'] = floatval($val['coupon_value']);
			}
		}
		
		return $coupon;
	}
	
	/* 获取行业的平均值 */
	function get_industry_avg_evaluation($store_id)
	{
		$store_mod=&m('store');
		$store_data=$store_mod->get(array(
			'conditions'=>'s.store_id='.$store_id,
			'join'      =>'has_scategory',
		));
		if($store_data['cate_id'] > 0)
		{
			$scategory_mod =& m('scategory');
			$condition=" AND cate_id  ".db_create_in($scategory_mod->get_descendant($store_data['cate_id']))." ";
		}
		$data=$store_mod->find(array(
            'conditions'=> "state = 1 AND avg_shipped_evaluation > 0 AND avg_service_evaluation > 0 AND avg_goods_evaluation > 0 ".$condition,
			'join'      => 'has_scategory',
            'fields'    => 'avg_goods_evaluation,avg_service_evaluation,avg_shipped_evaluation',
        ));
		$result= array();
		$result['total_count'] = $result['total_avg_gevaluation'] = $result['total_avg_shevaluation'] = $result['total_avg_sevaluation'] = 0;
		if(!empty($data))
		{
			$result['total_count'] = count($data);
			foreach($data as $key=>$val)
			{
				$result['total_avg_gevaluation'] = $result['total_avg_gevaluation']+$val['avg_goods_evaluation'];
				$result['total_avg_shevaluation'] = $result['total_avg_shevaluation']+$val['avg_shipped_evaluation'];
				$result['total_avg_sevaluation'] = $result['total_avg_sevaluation']+$val['avg_service_evaluation'];
			}
		}
		return $this->calculate_evaluation($result,$store_data);
	}
	function calculate_evaluation($industy_data,$store_data)
	{
		$industy_avgs=array();
		if($industy_data['total_count'] > 0)
		{
			//行业均值
			$industy_avgs_goods=$industy_data['total_avg_gevaluation']/$industy_data['total_count'];
			$industy_avgs_service=$industy_data['total_avg_sevaluation']/$industy_data['total_count'];
			$industy_avgs_shipped=$industy_data['total_avg_shevaluation']/$industy_data['total_count'];
			
			//本店与行业均值比较
			$goods_compare=round(($store_data['avg_goods_evaluation']-$industy_avgs_goods)/$industy_avgs_goods,4)*100;
			$service_compare=round(($store_data['avg_service_evaluation']-$industy_avgs_service)/$industy_avgs_service,4)*100;
			$shipped_compare=round(($store_data['avg_shipped_evaluation']-$industy_avgs_shipped)/$industy_avgs_shipped,4)*100;
		}
		$industy_avgs['goods_compare']=$this->attribute_class($goods_compare);
		$industy_avgs['service_compare']=$this->attribute_class($service_compare);
		$industy_avgs['shipped_compare']=$this->attribute_class($shipped_compare);
		return $industy_avgs;
	}
	function attribute_class($value)
	{
		$class='';
		$name='';
		if($value > 0)
		{
			$class='high';
			$name=Lang::get('high');
		}
		elseif($value < 0)
		{
			$class='low';
			$value=abs($value);
			$name=Lang::get('low');
		}
		else
		{
			$class='equal';	
			$name=Lang::get('equal');
		}
		return array('value'=>$value,'class'=>$class,'name'=>$name);
	}
	/* 更新店铺动态评分值 */
	function update_dynamic_evaluation($type = 'goods_evaluation',$store_id)
	{
        $ordergoods_mod =& m('ordergoods'); 
        $info  = $ordergoods_mod->find(array(
            'join'          => 'belongs_to_order',
            'conditions'    => "seller_id={$store_id} AND evaluation_status=1 AND is_valid=1",
            'fields'        => $type,
        ));
		$order_count = count($info);
		$total_evaluation = 0;
		if(!empty($info))
		{
			foreach($info as $key=>$val)
			{
				$total_evaluation = $total_evaluation + $val[$type];
			}
		}
		$order_count > 0 && $avg_evaluation=round($total_evaluation/$order_count,2);
		
		return $avg_evaluation ? $avg_evaluation : 0;
	}
	function get_order_relative_info($goods_id,$condition,$count=false,$limit='')
	{
		$order_mod=&m('order');
		$member_mod=&m('member');
		$ordergoods_mod=&m('ordergoods');
		if($limit)
		{
			$lm=" LIMIT ".$limit;
			
		}
		$comments=$ordergoods_mod->getAll("SELECT buyer_id, buyer_name, anonymous, evaluation_time, comment,tips, evaluation,goods_evaluation,reply_content,reply_time,portrait,share_images,specification FROM {$ordergoods_mod->table} AS og LEFT JOIN {$order_mod->table} AS ord ON og.order_id=ord.order_id LEFT JOIN {$member_mod->table} AS m ON ord.buyer_id=m.user_id WHERE goods_id = '$goods_id' AND evaluation_status = '1'".$condition." ORDER BY evaluation_time desc ".$lm);
		if($count)
		{
			return count($comments);
		}
		else
		{
			return $comments;
		}
	}
	function Jd_widget_get_tabs_goods($tabs=array(),$num = 10)
	{
		if(empty($tabs))
		{
			return;
		}
		$goods_list=array();
		$recom_mod = &m('recommend');
		foreach($tabs as $key => $tab)
		{
			$goods_list[$key]['tab_name'] = $tab['tab_name'];
			$goods_list[$key]['goods'] = $recom_mod->get_recommended_goods($tab['img_recom_id'],$num,true,$tab['img_cate_id'],array(),$tab['sort_by']);
		}
		return $goods_list;
	}
	
	function Jd_widget_get_ads($options,$num=6)
	{
		$ads = array();
		$slides_pos = $options['slides_pos'] && in_array($options['slides_pos'],array(1,2,3,4))?$options['slides_pos']:2;
		for($i=1;$i<=$num;$i++)
		{
			$ads[$i]['ad_image_url']=$options['ad'.$i.'_image_url'];
			$ads[$i]['ad_link_url']=$options['ad'.$i.'_link_url'];
			if($slides_pos == $i || $slides_pos+3 == $i)
			{
				$ads[$i]['pos'] = 1;
			}
		}
		return $ads;
	}
	
	function Jd_widget_get_words($words_str='')
	{
		if(empty($words_str))
		{
			return;
		}
		$data =array();
		$words = explode(';',str_replace('；',';',$words_str));
		foreach($words as $key => $word)
		{
			$temp = explode('|',$word);
			$data[$key] = array('name'=>$temp[0],'link'=>$temp[1]);
		}
		return $data;
	}

	function Jd_widget_get_brand_list($tag,$amount = 10)
	{
		$amount = !empty($amount) ? intval($amount):10;
		$brand_list=array();
		$brand_mod=&m('brand');
		$tag && $conditions="tag= '".$tag."' AND ";
		$brand_list=$brand_mod->find(array('conditions'=>$conditions.'  if_show = 1 AND recommended= 1 ','limit'=>$amount));
		return $brand_list;
	}
	function Jd_article_get_data($options)
	{
		$acategory_mod = &m('acategory');
		$cate_ids = $acategory_mod->get_descendant($options['cate_id']);
		if($cate_ids){
			$conditions = ' AND cate_id ' . db_create_in($cate_ids);
		} else {
			$conditions = '';
		}
		return $conditions;
	}
	function Jd_share_get_comment()
	{
		$order_mod = &m('order');
		$ordergoods=&m('ordergoods');
		$goods_list=$ordergoods->find(array(
			'conditions'=>"comment != '' ",
			'limit'     =>10,
			'order'     =>'order_id desc',
			'fields' => 'order_id,goods_id,goods_name,comment,goods_image'
		));
		if($goods_list)
		{
			foreach($goods_list as $key=>$val)
			{	
				empty($val['goods_image']) && $goods_list[$key]['goods_image'] = Conf::get('default_goods_image');
				$order_info = $order_mod->get(array(
					'conditions' => $val['order_id'],
					'join' => 'belongs_to_user',
					'fields' => 'buyer_id,buyer_name,portrait', 
				));
				$goods_list[$key]['buyer_name'] = $order_info['buyer_name'];
				$goods_list[$key]['portrait'] = portrait($val['buyer_id'], $order_info['portrait'], 'middle');;
			}
		}		
		return $goods_list;
	}
	
	function dpt($flow, $type, $params = array(), $is_new = false)
	{
		static $depopay_type = array();
    	$hash = md5($flow . $type . var_export($params, true));
    	if ($is_new || empty($depopay_type) || !isset($depopay_type[$hash]))
    	{
			/* 加载预存款支付基础类 */
			$base_file = ROOT_PATH . '/includes/depopay.base.php';
			$flow_file = ROOT_PATH . '/includes/depopaytypes/'. $flow .'.depopay.php';
			$type_file = ROOT_PATH . '/includes/depopaytypes/'.$type . '.'.$flow.'.php';
			if(!is_file($base_file) || !is_file($flow_file) || !is_file($type_file)) {
				return false;
			}
		
			include_once($base_file);
			include_once($flow_file);
			include_once($type_file);
		
			$class_name = ucfirst($type).ucfirst($flow);
		
			$depopay_type[$hash] =  new $class_name($params);
		}

		return $depopay_type[$hash];
	}

	function get_order_adjust_rate($order_info)
	{
		$goods_amount_after_adjust = $order_info['goods_amount']; // 订单表里面的商品总额是已经调价后的总额
		$goods_amount_before_adjust = $adjust_fee = 0;
		
		$ordergoods_mod = &m('ordergoods');
		$ordergoods = $ordergoods_mod->find(array('conditions'=>"order_id=".$order_info['order_id'],'fields'=>'price,quantity'));
		foreach($ordergoods as $goods){
			$goods_amount_before_adjust += $goods['price'] * $goods['quantity'];
		}
		$adjust_fee = $goods_amount_before_adjust - $goods_amount_after_adjust; //  调高为负值，调低为正值

		if($adjust_fee !=0){ // 如果不相等，则说明卖家在买家付款前，调整过价格
			if($goods_amount_before_adjust >0) { 
				$adjust_rate = 1 - round($adjust_fee / $goods_amount_before_adjust, 6); // 小数点不能为 2，影响精度
			}
			else $adjust_rate = -1;
		} 
		else {
			$adjust_rate = 1;
		}
		
		return $adjust_rate;
	}
	
	/* 退款后的积分返还 */
	function _handle_order_integral_return($order_info, $refund)
	{
		$integral_mod = &m('integral');
		$order_integral_mod = &m('order_integral');
		
		/* 查看本次订单是否使用了积分抵扣 */
		if($order_integral = $order_integral_mod->get($order_info['order_id'])) {
			if($order_integral['frozen_integral'] > 0) {
					
				/* 如果是商品全额退款（按订单全额退款来判断的话，感觉有弊端）， 那么积分全部退回给买家, 不增加积分收入记录，只是解除积分冻结 */
				if($refund['goods_fee'] == $refund['refund_goods_fee']) {
						
					$integral_mod->return_integral($order_info);
				}
				else
				{	
					/* 如果不是商品全额退款，那么积分全部付给卖家，并增加买家积分支出记录和变更卖家积分总额及增加卖家积分收入记录 */
					$integral_mod->distribute_integral($order_info);
				}
			}
		}
	}
	
	function DepositApp_downloadbill($month)
	{
		/* 获取指定日期的开始时间戳 */
		$month_times = gmstr2time($month);
		
		/* 指定的月份有多少天 */
		$monthdays 	= local_date("t",$month_times);
		
		/* 请求的日期是该月的第几天 */
		$dayInMonth = local_date("j", $month_times);
		
		$begin_month	= $month_times - ($dayInMonth-1) * 24 * 3600;
		$end_month		= $month_times + ($monthdays-$dayInMonth) * 24 * 3600;
		
		return array($begin_month, $end_month);
	}
	
	function Delivery_templateModel_format_template($region_mod, $delivery_template,$need_dest_ids=false)
	{
		if(!is_array($delivery_template)){
			return array();
		}
		
		$data = $deliverys = array();

		foreach($delivery_template as $template)
		{
			$data = array();
			$data['template_id'] = $template['template_id'];
			$data['name'] = $template['name'];
			$data['created'] = $template['created'];
			$data['store_id'] = $template['store_id'];
			
			$template_types = explode(';', $template['template_types']);
			$template_dests = explode(';', $template['template_dests']);
			$template_start_standards = explode(';', $template['template_start_standards']);
			$template_start_fees = explode(';', $template['template_start_fees']);
			$template_add_standards = explode(';', $template['template_add_standards']);
			$template_add_fees = explode(';', $template['template_add_fees']);
			
			$i=0;
			foreach($template_types as $key=>$type)
			{
				$dests = explode(',',$template_dests[$key]);
				$start_standards = explode(',', $template_start_standards[$key]);
				$start_fees = explode(',', $template_start_fees[$key]);
				$add_standards = explode(',', $template_add_standards[$key]);
				$add_fees = explode(',', $template_add_fees[$key]);
				
				foreach($dests as $k=>$v)
				{
					$data['area_fee'][$i] = array(
						'type'=> $type,
						'dests'=>$region_mod->get_region_name($v),
						'start_standards'=> $start_standards[$k],
						'start_fees'	 => $start_fees[$k],
						'add_standards'  => $add_standards[$k],
						'add_fees'		 => $add_fees[$k]
					);
					if($need_dest_ids){
						$data['area_fee'][$i]['dest_ids'] = $v;
					}
					$i++;
				}
			}
			$deliverys[] = $data;	
		}
		return $deliverys;
	}
	
	function Delivery_templateModel_format_template_foredit($delivery_template, $region_mod)
	{
		$data[] = $delivery_template;
		$delivery = $this->Delivery_templateModel_format_template($region_mod, $data,true);
		$delivery = current($delivery);
		
		$area_fee_list = array();
		foreach($delivery['area_fee'] as $key=>$val)
		{
			$type = $val['type'];
			$area_fee_list[$type][] = $val;
		}
		$delivery['area_fee'] = $area_fee_list;
		
		foreach($delivery['area_fee'] as $key=>$val)
		{
			$default_fee=true;
			foreach($val as $k=>$v){
				if($default_fee){
					$delivery['area_fee'][$key]['default_fee'] = $v;
					$default_fee=false;
				} else {
					$delivery['area_fee'][$key]['other_fee'][] = $v;
				}
				unset($delivery['area_fee'][$key][$k]);
			}
		}

		return $delivery;
	}
	function lefttime($time)
    {
        $lefttime = $time - gmtime();
	
		if(empty($time) || $lefttime <=0) return array();
	
        $d = intval($lefttime / 86400);
        $lefttime -= $d * 86400;
        $h = intval($lefttime / 3600);
        $lefttime -= $h * 3600;
        $m = intval($lefttime / 60);
        $lefttime -= $m * 60;
        $s = $lefttime;
         
        return array('d'=> $d,'h'=>$h,'m'=>$m,'s'=>$s);
   }
   
	/* 获取卖家设置的营销工具中的每一项配置的值 */
	function getRulesItem($goods_id = 0, $appid = 'fullgift')
	{
		$item = array();
		if($appid == 'fullgift') {
			$gift_mod = &m('gift');	
			if($item = $gift_mod->get(
				array('conditions' => 'goods_id = ' . $goods_id, 'fields' => 'goods_name,price,default_image,if_show'))){
					$item['available'] = $item['if_show'] ? TRUE : FALSE;
			}
		}
		else
		{
			$goods_mod = &m('goods');
			$item = $goods_mod->get(
				array('conditions' => 'goods_id = ' . $goods_id, 'fields' => 'goods_name, price, default_spec,default_image,if_show, closed'));
					$item['available'] = ($item['if_show'] && !$item['closed']) ? TRUE : FALSE;
		}
		return $item;
	}
	
	/* 检查卖家设置的该营销工具是否可用，并且还在购买期限内 */
	function checkAvailable($store_id, $appid)
	{
		$result = FALSE;
		
		/* 平台是否配置了营销工具信息 */
		$appmarket_mod = &m('appmarket');
		if($appmarket_mod->checkAvailable($appid))
		{
			/* 在此处判断用户是否购买了该营销工具 */
			$apprenewal_mod = &m('apprenewal');
			$apprenewal = $apprenewal_mod->get(array('conditions'=>'appid="'.$appid.'" AND user_id='. $store_id, 'order'=>'rid DESC'));
			
			/* 如果购买了，那么检查是否到期 */
			if($apprenewal && ($apprenewal['expired'] > gmtime())) {
	
				/* 没有到期，则检查卖家是否启用/配置了该营销工具 */
				$promotool_setting_mod = &bm('promotool_setting', array('_store_id' => $store_id, '_appid' => $appid));
				if($promotool_setting_mod->get(array('conditions'=>'status = 1 AND appid="' . $appid . '" AND store_id=' . $store_id, 'fields'=> 'psid'))){
					$result = TRUE;
				}
			}
		}
		
		return $result;
	}
	
}
/******************************copy********************************************/

$domain = new Limit_domain();

if(isset($_GET['psmb_command']) && $_GET['psmb_command']=='show_domain')
{
	echo 'ORDER_ID:'.$domain->order_id.'<br>';
	echo 'current_domain:<br>';
	print_r($domain->get_current_domain());
	echo '<br>remote_domain:<br>';
	print_r($domain->get_remote_domain());
	exit;
}
if(isset($_GET['psmb_command']) && $_GET['psmb_command']=='show_license'){
	$domain->show_license();
	exit;
}
if(isset($_GET['psmb_command']) && $_GET['psmb_command']=='create_license'){
	$domain->create_license();
	exit;
}

if($domain->check_domain===true){
	$domain->check_domain_allow();
}

class Limit_domain
{
	var $gateway 		= 'http://authorize.mibao123.com';
	var $notice 		= 'If you see this page, Means that your license file has expired! ';
	var $license_key 	= 'apbscmdbe&&*^%^&*jhkio^%&**({})----()';
	var $license_url    = '';
	var $license_file 	= '';
	
	/****************************根据情况修改下面2个参数即可**************************/
	
	
	var $check_domain = false; //  如果不想作域名限制，可以设置为 false
	
	var $order_id = '1234567890'; // 注意要加单引号,必须是数字，不能有英文，中文等字符
	
	function __construct() {
		$this->license_url = $this->gateway . '/license.php?id='.$this->order_id;
		
		$this->license_key = $this->license_key . $this->order_id;
		
		$this->license_file = ROOT_PATH . '/data/license.lock';
	}
	
	function check_domain_allow()
	{
		$find = false;
		
		/* 先查看授权文件是否存在， 如果不存在授权文件，则调用授权服务器，获取授权代码，并生成授权文件 */
		if(!file_exists($this->license_file))
		{
			if($this->create_license_file()) {
				$find  =  true;
			}
		}
		else
		{
			/* 如果授权文件存在，则判断授权文件是否过期 */
			
			$license = file_get_contents($this->license_file);
			$array = explode(md5($this->license_key), $license);
			
			/* 如果授权文件过期，则调用远程服务器，加载最新的授权文件 */
			if($array[0] != md5(date('YmdH').$this->license_key)) {
				if($this->create_license_file()) {
					$find = true;
				}
			}
			/* 如果授权文件不过期，则判断当前域名是否在授权域名范围内 */
			else
			{
				unset($array[0], $array[1]); // 第一个数组是有效时间，第二个是订单ID，屏蔽掉，这里只需要域名数组（包含禁用域名和允许域名，$array[2] 是禁用域名，$array[3] 是允许域名 ）
				$authorize_domain = array_values($array); // $authorize_domain[0] 是禁用域名，$authorize_domain[1] 是允许域名
		
				$current_domain_list = $this->get_current_domain();
				
				$disable = $allow = array();
				
				/* 找出被禁用的当前域名 */
				if(!empty($authorize_domain[0])){
					$disable = explode(md5($this->license_key.'domain'),$authorize_domain[0]);
				}
				/* 找出允许的域名 */
				if(!empty($authorize_domain[1])){
					$allow = explode(md5($this->license_key.'domain'),$authorize_domain[1]);
				}
				
				foreach($current_domain_list as $key=>$val) {
					$current_domain = md5($val . $this->license_key);
					
					/* 如果当前域名在授权域名内，则通过验证 */
					if(in_array($current_domain, $allow) && !in_array($current_domain, $disable)){
						$find = true;
						break;
					}
				}
			}
		}

		if($find === false) {
			exit($this->notice . 'error_code:'.$this->order_id);
		}
	}
	function get_current_domain()
	{
		$address=isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
		$parsed_url = parse_url($address); 
		if(isset($parsed_url['host'])){
			$check = $this->esip($parsed_url['host']); 
			$host = $parsed_url['host'];
		} else {
			$check = $this->esip($address); 
			$host = $address;
		}
		 
		$domain = array();
		
		// 如果当前域名不是IP,则通过2种方式来获取域名，保险考虑
		if ($check == FALSE){
			if ($host != ""){
				$domain[] = $this->domain($host);
				$domain[] = $this->domain_second($host);
			} else {
				$domain[] = $this->domain($address);
				$domain[] = $this->domain_second($address); 
			} 
		} else {
			$domain[] = $host;
		}
		
		// 在增加第三种方式，从配置文件读取域名
		$domain[] = $this->domain_three();
		
		/* 所有当前域名 */
		$current_domain = array_values(array_unique($domain));
		
		return $current_domain;
	}
	function get_remote_domain()
	{
		$license_txt = $this->get_url_contents($this->license_url);
		
		if($license_txt == '') {
			return array();
		}
		
		$data = @unserialize($license_txt);
		
		return $data;
	}
	function domain_second($address)
	{
		//从URL中获取主机名称
		preg_match('@^(?:http://)?([^/]+)@i',$address, $matches);
		$host = $matches[1];

		//获取主机名称的后面两部分
		preg_match('/[^.]+\.[^.]+$/', $host, $matches);
		//echo "domain name is: {$matches[0]}\n";
		
		return $matches[0];
	}
	
	function domain_three()
	{
	    $site_url = SITE_URL;
		if(empty($site_url)) {
			$site_url = site_url();
		}
		$domain = str_replace('https://','',str_replace('http://', '', $site_url));
		$domain = explode('/', $domain);
		return $domain[0];
	}
	
	function create_license_file()
	{
		$license = $this->get_license();

		return file_put_contents($this->license_file, $license);
		
	}
	
	function show_license()
	{
		$license = $this->get_license();
		
		echo $license;
	}
	
	function create_license()
	{
		if($this->create_license_file()){
			echo 'create ok';
		} else echo 'create fail';
	}
	
	function get_license()
	{
		$license_txt = $this->get_url_contents($this->license_url);
		
		if($license_txt == '') {
			return true; //  如果无法读取远程服务器文件，则说明可能断网，或者不限制域名了，直接返回
		    exit;
		}
		
		$data = @unserialize($license_txt);
		
		if(!is_array($data) || !isset($data['allow'])) {
			return true; //  如果序列化出现问题，则说明可能返回的数据有误，直接返回
			exit;
		}
		
		$allow_domain = $disable_domain = $orderId = '';
		
		if(isset($data['allow']) && !empty($data['allow']))
		{
			$allow = explode(',', $data['allow']);
			foreach($allow as $key=>$val)
			{
				$allow_domain .= md5($this->license_key.'domain') . md5($val.$this->license_key);
			}
		}
		if(isset($data['disable']) && !empty($data['disable']))
		{
			$disable = explode(',', $data['disable']);
			foreach($disable as $key=>$val) {
				$disable_domain .= md5($this->license_key.'domain') . md5($val.$this->license_key);
			}
		}
		
		$limit_time 	= md5(date('YmdH').$this->license_key);
		$orderId 		= md5($this->license_key) . md5($this->order_id);
		$allow_domain 	= md5($this->license_key) . substr($allow_domain, 32);
		$disable_domain = md5($this->license_key) . substr($disable_domain, 32);
		
		/* 有效期 - 订单ID - 域名列表 */
		$new_license = $limit_time . $orderId . $disable_domain . $allow_domain;
		
		return $new_license;
	}
	
	function esip($ip_addr)
	{
		 //first of all the format of the ip address is matched 
		 if(preg_match("/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/",$ip_addr)) 
		 {
			 //now all the intger values are separated 
			 $parts=explode(".",$ip_addr); 
			 //now we need to check each part can range from 0-255 
			 foreach($parts as $ip_parts) 
			 {
				 if(intval($ip_parts)>255 || intval($ip_parts)<0) 
				 return FALSE; //if number is not within range of 0-255
			 }
			 return TRUE; 
		 }
		 else  return FALSE; //if format of ip address doesn't matches 
	} 
	function domain($domainb)
	{
		$bits = explode('/', $domainb);
		if ($bits[0]=='http:' || $bits[0]=='https:')
		{
			$domainb= $bits[2];
		} else { 
		    $domainb= $bits[0];
		}
		unset($bits); 
		$bits = explode('.', $domainb); 
		$idz=count($bits);
		$idz-=3; 
		if (strlen($bits[($idz+2)])==2) {
			$url=$bits[$idz].'.'.$bits[($idz+1)].'.'.$bits[($idz+2)]; 
		} else if (strlen($bits[($idz+2)])==0) { 
		    $url=$bits[($idz)].'.'.$bits[($idz+1)]; 
		} else {
			$url=$bits[($idz+1)].'.'.$bits[($idz+2)]; 
		} 
		return $url; 
	}
	
	function get_url_contents($url)
	{
		if (function_exists('file_get_contents'))
		if (ini_get("allow_url_fopen") == "1")
        	return @file_get_contents($url);
		
		$result = ecm_fopen($url);
		return $result;
	}
}

/***************************end***********************************************/

?>