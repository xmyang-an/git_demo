<?php

/**
 *    后台统计控制器
 */
class StatApp extends BackendApp
{	

	var $search_arr;
	var $_order_mod;
    var $_user_mod;
	var $_goods_mod;
	var $_store_mod;
	var $_order_goods_mod;

    function __construct()
    {
        $this->StatApp();
    }

    function StatApp()
    {
        parent::__construct();
		import('datehelper.lib');
        $this->search_arr = formatTime($_REQUEST);
		$this->_order_mod = &m('order');
		$this->_user_mod =& m('member');
		$this->_goods_mod = & m('goods');
		$this->_store_mod = & m('store');
		$this->_order_goods_mod = & m('ordergoods');
		
    }

    function index()
    {
        $this->searchContent();
        $this->display('stat.order.html');
    }
	
	// 设置订单为对账状态
	function checkout()
	{
		list($conditions, $useTime, $start_time, $end_time) = $this->getConditions();
		
		if ($_GET['id'] != '') {
            $ids = explode(',', $_GET['id']);
			$conditions = ' checkout=0 AND status='.ORDER_FINISHED.' AND order_id' . db_create_in($ids);
        }
		else
		{
			$conditions = ' checkout=0 AND status='.ORDER_FINISHED." AND finished_time BETWEEN {$start_time} AND {$end_time} ";
		}
		$result = $this->_order_mod->edit($conditions, array('checkout' => 1, 'checkout_time' => gmtime()));
		$this->show_message(sprintf(Lang::get('handle_checkout_order'), $result));
	}
	
	/**
     * 输出平台订单总数据
     */
    function get_plat_sale()
	{
        list($conditions) = $this->getConditions();
        $statcount_arr = $this->_order_mod->get(array(
			'conditions' => $conditions,
			'fields' => 'COUNT(*) as ordernum, SUM(order_amount) as orderamount',		
		));
		echo '<dl class="row"><dd class="opt"><ul>';
		echo '<li><h4>总销售额：</h4><h2 class="timer">'.number_format($statcount_arr['orderamount'],2).'</h2><h6>元</h6></li>';
		echo '<li><h4>总订单量：</h4><h2 class="timer">'. $statcount_arr['ordernum'].'</h2><h6>笔</h6></li>';
		echo '</ul></dd><dl>';
        exit();
    }

    /**
     * 输出订单统计XML数据
     */
    function get_order_xml()
	{
        list($conditions, $useTime) = $this->getConditions();
		$order = " {$useTime} DESC, order_id DESC ";
        $param = array('order_sn','seller_name', 'dateline','buyer_name','order_amount','payment_name','status', 'checkout');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
			if(trim($_POST['sortname']) == 'dateline') $_POST['sortname'] = $useTime;
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
		$model_order =& m('order');
		$orders = $model_order->find(array(
            'conditions'    => $conditions,
            'limit'         => $page['limit'],  //获取当前页的数据
            'order'         => "$order",
            'count'         => true             //允许统计
        ));
        $page['item_count'] = $model_order->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($orders as $k => $v){
			$list = array();
			$operation = "<a class='btn green' href='index.php?app=order&act=view&id={$k}'><i class='fa fa-search-plus'></i>查看</a>";
			$list['operation'] = $operation;
			$list['order_sn'] = $v['order_sn'];
			$list['seller_name'] = $v['seller_name'];
			$list['dateline'] = local_date('Y-m-d H:i:s',$v[$useTime]);
			$list['buyer_name'] = $v['buyer_name'];
			$list['order_amount'] = $v['order_amount'];
			$list['payment_name'] = $v['payment_name'];
			$list['status'] = order_status($v['status']);
			$list['checkout'] = ($v['checkout']==1) ? '<font color="red">已对账</font>' : '未对账';
			$data['list'][$k] = $list;
		}
        $this->flexigridXML($data);
    }
    
	/**
     * 订单走势
     */
    function sale_trend()
	{
		$conditions = "1=1";
		
        //默认统计当前数据
        if(!$this->search_arr['search_type']){
            $this->search_arr['search_type'] = 'day';
        }
		
		$useTime = 'add_time';
        if(trim($_GET['order_type']) != ''){
			$status = intval($_GET['order_type']);
            $conditions .= " AND status = " . $status;
			if(in_array($status, array(ORDER_FINISHED))) $useTime = 'finished_time';
			if(in_array($status, array(ORDER_SHIPPED)))  $useTime = 'ship_time';
			if(in_array($status, array(ORDER_ACCEPTED))) $useTime = 'pay_time';
        }
        if(trim($_GET['store_name']) != ''){
            $conditions .= " AND seller_name like '%".trim($_GET['store_name'])."%' ";
        }

        $stattype = trim($_GET['type']);
        if($stattype == 'ordernum'){
            $field = ' COUNT(*) as ordernum ';
        } else {
            $stattype = 'orderamount';
            $field = ' SUM(order_amount) as orderamount ';
        }
        if($this->search_arr['search_type'] == 'day'){
    	    $stime = $this->search_arr['day']['search_time'] - 86400;//昨天0点
    	    $etime = $this->search_arr['day']['search_time'] + 86400 - 1;//今天24点
            //构造横轴数据
            for($i=0; $i<24; $i++){
                //统计图数据
                $curr_arr[$i] = 0;//今天
                $up_arr[$i] = 0;//昨天
                //统计表数据
                $currlist_arr[$i]['timetext'] = $i;

                $uplist_arr[$i]['val'] = 0;
                $currlist_arr[$i]['val'] = 0;
                //横轴
                $stat_arr['xAxis']['data'][] = "$i";
            }
			
            $today_day = local_date('d', $etime);//今天日期
            $yesterday_day = local_date('d', $stime);//昨天日期
			
			$conditions .= " AND {$useTime} BETWEEN {$stime} AND {$etime} ";
            $field .= " ,DAY(FROM_UNIXTIME({$useTime}+".date('Z').")) as dayval, HOUR(FROM_UNIXTIME({$useTime}+".date('Z').")) as hourval ";
            $group = ' GROUP BY dayval,hourval';
			$orderlist= $this->_order_mod->find(array(
				'conditions' => $conditions.$group,
				'fields' => $field,
			));

            foreach($orderlist as $k => $v){
                if($today_day == $v['dayval']){
                    $curr_arr[$v['hourval']] = floatval($v[$stattype]);
                    $currlist_arr[$v['hourval']]['val'] = $v[$stattype];
                }
                if($yesterday_day == $v['dayval']){
                    $up_arr[$v['hourval']] = floatval($v[$stattype]);
                    $uplist_arr[$v['hourval']]['val'] = $v[$stattype];
                }
            }

			$stat_arr['legend']['data'][0] = '昨天';
            $stat_arr['legend']['data'][1] = '今天';
			$stat_arr['series'][0]['data'] = array_values($up_arr);
            $stat_arr['series'][1]['data'] = array_values($curr_arr);
        }

        if($this->search_arr['search_type'] == 'week'){
			$stime = getYearFirstDay($this->search_arr['searchweek_year']);
			$etime = getYearLastDay($this->search_arr['searchweek_year']);

			$current_weekarr = explode('|', $this->search_arr['week']['current_week']);
			$wstime = gmstr2time($current_weekarr[0]);
			$wetime = gmstr2time_end($current_weekarr[1]) - 1;
			
			// 当年的第几周
            $up_week = local_date('W', $wstime);//上周
            $curr_week = local_date('W', $wetime+1);//本周
			if(($curr_week < $up_week) && ($curr_week == 1)) { // 统计了去年的第几周了，故重置
				$up_week = 0;
			}
			
            //构造横轴数据
            for($i=1; $i<=7; $i++){
                //统计图数据
                $up_arr[$i] = 0;
                $curr_arr[$i] = 0;
                $tmp_weekarr = getSystemWeekArr();
                //统计表数据
                $uplist_arr[$i]['timetext'] = $tmp_weekarr[$i];
                $currlist_arr[$i]['timetext'] = $tmp_weekarr[$i];
                $uplist_arr[$i]['val'] = 0;
                $currlist_arr[$i]['val'] = 0;
                //横轴
                $stat_arr['xAxis']['data'][] = $tmp_weekarr[$i];
                unset($tmp_weekarr);
            }
            $conditions .= " AND {$useTime} BETWEEN {$stime} AND {$etime} ";
            $field .= ",WEEKOFYEAR(FROM_UNIXTIME({$useTime}))+1 as weekval, WEEKDAY(FROM_UNIXTIME({$useTime}))+1 as dayofweekval ";
            $group = ' GROUP BY weekval,dayofweekval';
            $orderlist= $this->_order_mod->find(array(
				'conditions' => $conditions.$group,
				'fields' => $field,
			));
			
            foreach($orderlist as $k => $v){
                if ($up_week == $v['weekval']){
                    $up_arr[$v['dayofweekval']] = floatval($v[$stattype]);
                    $uplist_arr[$v['dayofweekval']]['val'] = floatval($v[$stattype]);
                }
                if ($curr_week == $v['weekval']){
                    $curr_arr[$v['dayofweekval']] = floatval($v[$stattype]);
                    $currlist_arr[$v['dayofweekval']]['val'] = floatval($v[$stattype]);
                }
            }
			
			$stat_arr['legend']['data'][0] = '上周';
            $stat_arr['legend']['data'][1] = '本周';
			$stat_arr['series'][0]['data'] = array_values($up_arr);
            $stat_arr['series'][1]['data'] = array_values($curr_arr);
        }

        if($this->search_arr['search_type'] == 'month'){
			$stime = getYearFirstDay($this->search_arr['month']['current_year']);
			$etime = getYearLastDay($this->search_arr['month']['current_year']);
			
			$curr_month = $this->search_arr['month']['current_month'];
            $up_month = date('m', strtotime($this->search_arr['month']['current_year'].'-'.$curr_month.'-01 -1 month'));

            //计算横轴的最大量（由于每个月的天数不同）
            $up_dayofmonth = local_date('t', $stime-1);
            $curr_dayofmonth = local_date('t',$etime);

            $x_max = $up_dayofmonth > $curr_dayofmonth ? $up_dayofmonth : $curr_dayofmonth;

            //构造横轴数据
            for($i=1; $i<=$x_max; $i++){
                //统计图数据
                $up_arr[$i] = 0;
                $curr_arr[$i] = 0;
                //统计表数据
                $currlist_arr[$i]['timetext'] = $i;
                $uplist_arr[$i]['val'] = 0;
                $currlist_arr[$i]['val'] = 0;
                //横轴
                $stat_arr['xAxis']['data'][] = $i;
            }
            $conditions .= " AND {$useTime} BETWEEN {$stime} AND {$etime} ";
            $field .= ",MONTH(FROM_UNIXTIME({$useTime})) as monthval,day(FROM_UNIXTIME({$useTime})) as dayval ";
            $group = ' GROUP BY monthval,dayval';
            $orderlist= $this->_order_mod->find(array(
				'conditions' => $conditions.$group,
				'fields' => $field,
			));
			
            foreach($orderlist as $k => $v){
                if ($up_month == $v['monthval']){
                    $up_arr[$v['dayval']] = floatval($v[$stattype]);
                    $uplist_arr[$v['dayval']]['val'] = floatval($v[$stattype]);
                }
                if ($curr_month == $v['monthval']){
                    $curr_arr[$v['dayval']] = floatval($v[$stattype]);
                    $currlist_arr[$v['dayval']]['val'] = floatval($v[$stattype]);
                }
            }
            $stat_arr['legend']['data'][0] = '上月';
            $stat_arr['legend']['data'][1] = '本月';
			$stat_arr['series'][0]['data'] = array_values($up_arr);
            $stat_arr['series'][1]['data'] = array_values($curr_arr);
        }

        $stat_json = creatChartData($stat_arr);
        $this->assign('stat_json',$stat_json);
        $this->assign('stattype',$stattype);
        $this->display('stat.linelabels.html');
    }
	
	//店铺统计
	function shop_stat()
	{
		$this->searchContent();
		$this->display('stat.shop_stat.html');
	}
	
	function get_shop_stat_xml()
	{
		list($conditions, $useTime) = $this->getConditions();
	
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
		
		$orders = $this->_order_mod->getAll('select store_name,s.add_time,address,tel,sum(order_amount) as amount,count(*) as count,seller_name,order_id,seller_id from '.DB_PREFIX.'order order_alias left join '.DB_PREFIX.'store s on s.store_id=order_alias.seller_id where '.$conditions.' group by seller_id order by amount desc,count desc limit '.$page['limit']);
		
		$page['item_count'] = count($this->_order_mod->getAll('select seller_id from '.DB_PREFIX.'order order_alias where '.$conditions.' group by seller_id '));

		$data = array();
		$data['now_page'] = $page['curr_page'];
		$data['total_num'] = $page['item_count'];
		foreach ($orders as $k => $v)
		{
			$list = array();
			$list['seller_name'] = $v['seller_name'];
			$list['add_time'] = local_date('Y-m-d', $v['add_time']);
			$list['address'] = $v['address'] ? $v['address'] : '-';
			$list['tel'] = $v['tel'] ? $v['tel'] : '-';
			$list['amount'] = $v['amount'] ? price_format($v['amount']) : 0;
			$list['count'] = $v['count'];
			$data['list'][$v['seller_id']] = $list;
		}

		$this->flexigridXML($data);	
	}
	
	//商品统计
	function goods_stat()
	{
		$this->searchContent();
		$this->display('stat.goods_stat.html');
	}
	
	function get_goods_stat_xml()
	{	
		list($conditions, $useTime) = $this->getConditions();
		
		$pre_page = $_POST['rp'] ? intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
		
		$sql = 'select goods_name,seller_name,sum(quantity*price) as price,sum(quantity) as quantity,goods_id from '.DB_PREFIX.'order_goods og left join '.DB_PREFIX.'order order_alias on og.order_id=order_alias.order_id where '.$conditions.' group by goods_id order by quantity desc,price desc';
		
		$goodsList = $this->_order_goods_mod->getAll($sql.' limit '.$page['limit']);

		$page['item_count'] = count($this->_order_goods_mod->getAll($sql));
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($goodsList as $k => $v)
		{
			$list = array();
			$list['goods_name'] = $v['goods_name'];
			$list['store_name'] = $v['seller_name'];
			$list['price'] = price_format($v['price']);
			$list['quantity'] = $v['quantity'];
			$data['list'][$v['goods_id']] = $list;
		}	
		
		$this->flexigridXML($data);	
	}
	
	//分类统计
	function category_stat()
	{
		$this->searchContent();
		$this->display('stat.category_stat.html');
	}
	
	function get_category_stat_xml()
	{
		list($conditions, $useTime) = $this->getConditions();
		
		$cate_id = intval($_GET['cate_id']) ? intval($_GET['cate_id']) : 0;
		$cateConditions .= ' AND parent_id='.$cate_id;
		
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);

		$category_mod = &bm('gcategory', array('_store_id' => 0));
		$cates = $category_mod->find(array(
			'conditions' => '1=1 '.$cateConditions,
			'fields' => 'cate_name',
			'limit' => $page['limit'],
            'count' => true
		));

		if(!empty($cates))
		{
			$ordergoods_mod = &m('order'); 
			foreach($cates as $key=>$val)
			{
				$cateids = $category_mod->get_descendant($val['cate_id']);
				$cates[$key]['total'] = $ordergoods_mod->getAll('select sum(quantity*og.price) as amount,sum(quantity) as quantity from '.DB_PREFIX.'order_goods og left join '.DB_PREFIX.'order order_alias on og.order_id=order_alias.order_id left join '.DB_PREFIX.'goods g on g.goods_id=og.goods_id where '.$conditions.' AND cate_id '.db_create_in($cateids));
			}
		} 
		$page['item_count'] = $category_mod->getCount();
		$data = array();
		$data['now_page'] = $page['curr_page'];
        $data['total_num'] = $page['item_count'];
		foreach ($cates as $k => $v)
		{
			$children = $category_mod->get('parent_id='.$v['cate_id']);
			if(!empty($children))
			{
				$operation = "<a class='btn blue' href='index.php?app=stat&act=category_stat&".$this->_getUrlParam()."&cate_id=".$v['cate_id']."'><i class='fa fa-search-plus'></i>下级分类</a>";
			}
			else
			{
				$operation = '';
			}
			$list = array();
			$list['operation'] = $operation;
			$list['cate_name'] = $v['cate_name'];
			$list['amount'] = price_format($v['total'][0]['amount']);
			$list['quantity'] = $v['total'][0]['quantity'] ? $v['total'][0]['quantity'] : 0;
			$data['list'][$k] = $list;
		}
		$this->flexigridXML($data);	
	}
	
	function getConditions()
	{
		$conditions = " 1=1 ";
		
		//  根据不同的状态，使用不同的时间字段
		$useTime = 'add_time';
		if(trim($_GET['order_type']) != ''){
			$status = intval($_GET['order_type']);
			$conditions .= " AND order_alias.status = " . $status;
			if(in_array($status, array(ORDER_FINISHED))) $useTime = 'finished_time';
			if(in_array($status, array(ORDER_SHIPPED)))  $useTime = 'ship_time';
			if(in_array($status, array(ORDER_ACCEPTED))) $useTime = 'pay_time';
		}
			
		if($_GET['search_type'])
		{
        	//计算昨天和今天时间
			if($this->search_arr['search_type'] == 'day'){
				$stime = $this->search_arr['day']['search_time'] - 86400;//昨天0点
				$etime = $this->search_arr['day']['search_time'] + 86400 - 1;//今天24点
				$curr_stime = $this->search_arr['day']['search_time'];//今天0点
			} elseif ($this->search_arr['search_type'] == 'week'){
				$current_weekarr = explode('|', $this->search_arr['week']['current_week']);
				$stime = gmstr2time($current_weekarr[0]);
				$etime = gmstr2time_end($current_weekarr[1]) - 1;
				$curr_stime = $stime;//本周0点
			} elseif ($this->search_arr['search_type'] == 'month'){
				$stime = getMonthFirstDay($this->search_arr['month']['current_year'], $this->search_arr['month']['current_month']);
				$etime = getMonthLastDay($this->search_arr['month']['current_year'], $this->search_arr['month']['current_month']);
				$curr_stime = $stime; // 本月0点
			}
			
			$conditions .= " AND {$useTime} BETWEEN ".$curr_stime." AND ".$etime;
		}

		if(trim($_GET['store_name']) != ''){
            $conditions .= " AND seller_name like '%".trim($_GET['store_name'])."%' ";
        }
		
		return 	array($conditions, $useTime, $curr_stime, $etime);
	}
	
	// 导出订单统计
	function export_csv()
	{
		list($conditions, $useTime, $start_time, $end_time) = $this->getConditions();
		if ($_GET['id'] != '') {
            $ids = explode(',', $_GET['id']);
			$conditions .= ' AND order_alias.order_id' . db_create_in($ids);
        }
        $orders = $this->_order_mod->findAll(array(
            'conditions'    => $conditions,
			'join'          => 'has_orderextm',
            'order'         => "{$useTime} DESC, order_alias.order_id DESC",
			'include'       => array(
                'has_ordergoods',   //取出订单商品
            ),
        )); 
		if(!$orders) {
			$this->show_warning('no_such_order');
            return;
		}
		
		// 所有订单总金额
		$amount = 0;
		// 所有店铺总额
		$storeDetail = array();
		foreach($orders as $k=>$v)
		{
			foreach($v['order_goods'] as $ordergoods)
			{
				$orders[$k]['goods'] .= sprintf(Lang::get('goods_intro'), $ordergoods['goods_name'], $ordergoods['price'], $ordergoods['quantity']);
			}
			$amount += $v['order_amount'];
			
			if(!isset($storeDetail[$v['seller_id']])) {
				$storeDetail[$v['seller_id']]['seller_name'] = $v['seller_name'];
				$storeDetail[$v['seller_id']]['amount'] = $storeDetail[$v['seller_id']]['goodsAmount'] = 0;
			}
			$storeDetail[$v['seller_id']]['amount'] += $v['order_amount'];
			//$storeDetail[$v['seller_id']]['goodsAmount'] += ($v['goods_amount'] - $v['discount']);
		}
		
		/* xls文件数组 */
		$record_xls = array();		
		$record_title = array(
			'seller_name' 	=> 	'店铺名称',
    		'order_sn' 		=> 	'订单编号',
    		'dateline' 		=> 	'时间',
    		'buyer_name' 	=> 	'买家名称',
    		'order_amount' 	=> 	'订单总额',
    		'payment_name' 	=> 	'付款方式',
			'name' 			=> 	'收货人姓名',
    		'buyer_addr' 	=> 	'地址',
			'buyer_phone' 	=> 	'电话',
			'pay_message'	=>	'买家留言',
			'status'		=>	'订单状态',
			'checkout'      =>  '对账状态',
			'invoice_no'	=>	'快递单号',
			'postscript'	=>	'备注',
			'goods'			=>	'商品信息',
		);
		$folder = 'order_'.local_date('YmdHis', gmtime());
		$record_xls[] = $record_title;
		foreach($orders as $key=>$order)
    	{
			$record_value['seller_name']	=	$order['seller_name'];
			$record_value['order_sn']		=	$order['order_sn'];
			$record_value['dateline']		=	local_date('Y-m-d H:i:s', $order[$useTime]);
			$record_value['buyer_name']		=	$order['buyer_name'];
			$record_value['order_amount']	=	$order['order_amount'];
			$record_value['payment_name']	=	$order['payment_name'];
			$record_value['name']			=	$order['consignee'];
			$record_value['buyer_addr']		=	$order['region_name'].$order['address'];
			$record_value['buyer_phone']	=	$order['phone_mob'];
			$record_value['pay_message']   	=   $order['pay_message'];
			$record_value['status']			=	order_status($order['status']);
			$record_value['checkout'] 		= 	($order['checkout']==1) ? '已对账' : '未对账';
			$record_value['invoice_no']		=	$order['invoice_no'];
			$record_value['postscript']		=	$order['postscript'];
			$record_value['goods']			=	$order['goods'];
        	$record_xls[] 					= 	$record_value;
    	}
		// 使用此类不能合并单元格
		//import('excelwriter.lib');
		//$ExcelWriter = new ExcelWriter(CHARSET, $folder);
		//$ExcelWriter->add_array($record_xls);
		//$ExcelWriter->output();
		
		// 使用此类可以合并单元格等操作
		require_once ROOT_PATH .'/includes/PHPExcel/PHPExcel.php';
		require_once ROOT_PATH .'/includes/PHPExcel/PHPExcel/IOFactory.php';
		
		$objPHPExcel = new PHPExcel();
		foreach($record_xls as $key => $value){
			$i = 0;
			foreach($value as $k => $v) {
				$i++;
     			$objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr(320+$i).($key+1),$v);
			}
  		}
		// 订单列总数
		$orderColNum  = count($record_title);
		// 订单行总数（连标题）
		$orderCellNum = count($record_xls);
		
		//合并单元格
		$objPHPExcel->getActiveSheet()->mergeCells(chr(321).($orderCellNum+2).':'.chr(320+$orderColNum).($orderCellNum+2));
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr(321).($orderCellNum+2), sprintf(Lang::get('stat_intro'), local_date('Y-m-d H:i:s', $start_time), local_date('Y-m-d H:i:s', $end_time), $amount, count($storeDetail)));
		
		// 各个店铺汇总
		$record_xls = $record_value = array();		
		$record_title = array(
			'store_id'		=>  '店铺ID',
			'seller_name' 	=> 	'店铺名称',
    		'amount' 		=> 	'交易金额汇总',
			//'goodsAmount'   =>  '成交价金额汇总',
		);
		$record_xls[] = $record_title;
		foreach($storeDetail as $key=>$order)
    	{
			$record_value['store_id']		=   $key;
			$record_value['seller_name']	=	$order['seller_name'];
			$record_value['amount']			=   $order['amount'];
			//$record_value['goodsAmount'] 	=  	$order['goodsAmount'];
        	$record_xls[] 					= 	$record_value;
    	}
	
		foreach($record_xls as $key => $value){
			$i = 0;
			foreach($value as $k => $v) {
				$i++;
     			$objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr(320+$i).($key+$orderCellNum+3+1),$v);
			}
  		}

		//保存到某目录下
		//$objPHPExcel->getActiveSheet()->setTitle('订单统计');
		//$objPHPExcel->setActiveSheetIndex(0);
		//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		//$objWriter->save(ROOT_PATH . '/data/files/mall/excel/'.$folder.'.xlsx');
		
		//导出EXCEL
		$objPHPExcel->getActiveSheet()->setTitle('订单统计');
		$objPHPExcel-> setActiveSheetIndex(0);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . $folder . '.xlsx"');
		header('Cache-Control: max-age=0');
		$objWriter->save('php://output');
	}
	
	//店铺统计的导出
	function export_csv_shop()
	{
		list($conditions, $useTime) = $this->getConditions();
		
		if ($_GET['id'] != '') {
            $ids = explode(',', $_GET['id']);
			$conditions .= ' AND seller_id' . db_create_in($ids);
        }
		
		$sql = 'select store_name,s.add_time,address,tel,sum(order_amount) as amount,count(*) as count,seller_name,order_id,seller_id from '.DB_PREFIX.'order order_alias left join '.DB_PREFIX.'store s on s.store_id=order_alias.seller_id where '.$conditions.' group by seller_id order by amount desc,count desc ';
		$orders = $this->_order_mod->getAll($sql); 

		if(!$orders) 
		{
			$this->show_warning('没有指定的数据');
            return;
		}

		/* xls文件数组 */
		$record_xls = array();		
		$record_title = array(
			'seller_name' 	=> 	'店铺名称',
    		'add_time' 		=> 	'开店时间',
			'address' 		=> 	'地址',
			'tel' 		=> 	'联系电话',
    		'dateline' 		=> 	'金额',
    		'buyer_name' 	=> 	'订单总数',
		);
		$folder = 'order_'.local_date('Ymdhis', gmtime());
		$record_xls[] = $record_title;
		foreach($orders as $key=>$order)
    	{
			$record_value['seller_name']	=	$order['seller_name'];
			$record_value['add_time']		=	local_date('Y-m-d', $order['add_time']);
			$record_value['address']		=	$order['address'];
			$record_value['tel']		=	$order['tel'];
			$record_value['amount']			=	price_format($order['amount']);
			$record_value['count']			=	$order['count'];
        	$record_xls[] 					= 	$record_value;
    	}	
		
		
		import('excelwriter.lib');
		$ExcelWriter = new ExcelWriter(CHARSET, $folder);
		$ExcelWriter->add_array($record_xls);
		$ExcelWriter->output();
	}	
	
	//商品统计的导出
	function export_csv_goods()
	{
		list($conditions, $useTime) = $this->getConditions();
		
		if (trim($_GET['id']) != '') {
            $ids = explode(',', $_GET['id']);
			$conditions .= ' AND goods_id' . db_create_in($ids);
        }
        
		$sql = 'select goods_name,seller_name,sum(quantity*price) as price,sum(quantity) as quantity,goods_id from '.DB_PREFIX.'order_goods og left join '.DB_PREFIX.'order order_alias on og.order_id=order_alias.order_id where '.$conditions.' group by goods_id order by quantity desc,price desc';
		
		$goodsList = $this->_order_goods_mod->getAll($sql);
	
		if(!$goodsList) 
		{
			$this->show_warning('没有指定的订单');
            return;
		}
		/* xls文件数组 */
		$record_xls = array();		
		$record_title = array(
			'goods_name' 	=> 	'商品名称',
    		'store_name' 		=> 	'所属店铺',
    		'price' 		=> 	'总金额',
    		'quantity' 	=> 	'总销量',
		);
		$folder = 'order_'.local_date('Ymdhis', gmtime());
		$record_xls[] = $record_title;
		foreach($goodsList as $key=>$goods)
    	{
			$record_value['goods_name']		=	$goods['goods_name'];
			$record_value['store_name']		=	$goods['seller_name'];
			$record_value['price']			=	price_format($goods['price']);
			$record_value['quantity']		=	sprintf('%s件',$goods['quantity']);
        	$record_xls[] 					= 	$record_value;
    	}
		import('excelwriter.lib');
		$ExcelWriter = new ExcelWriter(CHARSET, $folder);
		$ExcelWriter->add_array($record_xls);
		$ExcelWriter->output();
	}
	
	//分类统计的导出
	function export_csv_category()
	{
		list($conditions, $useTime) = $this->getConditions();
		
		$cate_id = intval($_GET['cate_id']) ? intval($_GET['cate_id']) : 0;
		$cateConditions .= ' AND parent_id='.$cate_id;
		
		$ids = isset($_GET['id']) ? trim($_GET['id']) : '';
		if ($ids != '') {
            $ids = explode(',', $ids);
			$cateConditions .= ' AND cate_id' . db_create_in($ids);
        }
		 
		$category_mod = &bm('gcategory', array('_store_id' => 0));
		$cates = $category_mod->find(array(
			'conditions' => '1=1 '.$cateConditions,
			'fields' => 'cate_name',
			'limit' => $page['limit'],
            'count' => true
		));
		
		if(!empty($cates))
		{
			$ordergoods_mod = &m('order'); 
			foreach($cates as $key=>$val)
			{
				$cateids = $category_mod->get_descendant($val['cate_id']);
				$cates[$key]['total'] = $ordergoods_mod->getAll('select sum(quantity*og.price) as amount,sum(quantity) as quantity from '.DB_PREFIX.'order_goods og left join '.DB_PREFIX.'order order_alias on og.order_id=order_alias.order_id left join '.DB_PREFIX.'goods g on g.goods_id=og.goods_id where '.$conditions.' AND cate_id '.db_create_in($cateids));
			}	
		}
		
		if(!$cates) 
		{
			$this->show_warning('没有指定的订单');
            return;
		}
		
		/* xls文件数组 */
		$record_xls = array();		
		$record_title = array(
			'cate_name' 	=> 	'分类名称',
    		'amount' 		=> 	'总销额',
			'quantity' 		=> 	'总销量',
		);
		$folder = 'order_'.local_date('Ymdhis', gmtime());
		$record_xls[] = $record_title;
		foreach($cates as $key=>$cate)
    	{
			$record_value['cate_name']		=	$cate['cate_name'];
			$record_value['amount']			=	price_format($cate['total'][0]['amount']);
			$record_value['quantity']			=	$cate['total'][0]['quantity'];
        	$record_xls[] 					= 	$record_value;
    	}
		import('excelwriter.lib');
		$ExcelWriter = new ExcelWriter(CHARSET, $folder);
		$ExcelWriter->add_array($record_xls);
		$ExcelWriter->output();
	}	
	
	//右边弹出框内容
	function searchContent()
	{
        $this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js,echarts-all.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
			'style' =>  'jquery.ui/themes/smoothness/jquery.ui.css',
		));
		//获得系统年份
		$year_arr = getSystemYearArr();
		//获得系统月份
		$month_arr = getSystemMonthArr();
		
		$this->assign('order_status', array(
            ORDER_PENDING => Lang::get('order_pending'),
            ORDER_SUBMITTED => Lang::get('order_submitted'),
            ORDER_ACCEPTED => Lang::get('order_accepted'),
            ORDER_SHIPPED => Lang::get('order_shipped'),
            ORDER_FINISHED => Lang::get('order_finished'),
            ORDER_CANCELED => Lang::get('order_canceled'),
        ));
		
		//获得本月的周时间段
		$week_arr = getMonthWeekArr($this->search_arr['week']['current_year'], $this->search_arr['week']['current_month']);
		$this->assign('year_arr', $year_arr);
		$this->assign('month_arr', $month_arr);
		$this->assign('week_arr', $week_arr);
		$this->assign('search_arr', $this->search_arr);
	}
	
	function _getUrlParam()
	{
		$tmp = array();
		$str = '';
		
		$param  = $_GET;
		unset($param['app'],$param['act']);
		if(!empty($param))
		{
			foreach($param as $key=>$val)
			{
				$tmp[] = $key.'='.$val;
			}
			
			$str = join('&', $tmp);
		}
		
		return $str;
	}
}
?>
