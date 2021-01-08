<?php

class OrderApp extends BackendApp
{	

    function index()
    {
		$query = $this->get_query_conditions();
		$this->assign('filtered', $query);
		$search_options = array(
            'seller_name'   => Lang::get('store_name'),
            'buyer_name'   => Lang::get('buyer_name'),
            'payment_name'   => Lang::get('payment_name'),
            'order_sn'   => Lang::get('order_sn'),
        );
		$this->assign('search_options', $search_options);
		$this->assign('order_status_list', array(
            ORDER_PENDING => Lang::get('order_pending'),
            ORDER_SUBMITTED => Lang::get('order_submitted'),
            ORDER_ACCEPTED => Lang::get('order_accepted'),
            ORDER_SHIPPED => Lang::get('order_shipped'),
            ORDER_FINISHED => Lang::get('order_finished'),
            ORDER_CANCELED => Lang::get('order_canceled'),
        ));
		$this->import_resource(array(
			'script' => 'jquery.plugins/flexigrid.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
            'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'
		));
        $this->display('order.index.html');
    }
	
	function get_xml()
	{
		$conditions = 'order_sn > 0';
		$conditions .= $this->get_query_conditions();
		$order = 'add_time DESC';
        $param = array('order_sn','seller_name','add_time','buyer_name','order_amount','payment_name','status');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
		if($_GET['type'] == 'distribution')
		{
			$conditions .= ' AND did>0';
		}
		$pre_page = $_POST['rp']?intval($_POST['rp']):10;
		$page   =   $this->_get_page($pre_page);
		$model_order =& m('order');
		$orders = $model_order->findAll(array(
            'conditions'    => $conditions.$where,
            'limit'         => $page['limit'],  //获取当前页的数据
            'order'         => $order,
            'count'         => true,             //允许统计
			'include'       =>  array(
                'has_ordergoods',       //取出商品
            ),
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
			$list['add_time'] = local_date('Y-m-d H:i:s',$v['add_time']);
			$list['buyer_name'] = $v['buyer_name'];
			$list['order_amount'] = $v['order_amount'];
			$list['payment_name'] = $v['payment_name'];
			$list['status'] = order_status($v['status']);
			$list['distribution'] = $v['did']>0?'是':'否';
			$data['list'][$k] = $list;
		}
		$this->flexigridXML($data);
	}

	function get_query_conditions(){
		$search_options = array(
            'seller_name'   => Lang::get('store_name'),
            'buyer_name'   => Lang::get('buyer_name'),
            'payment_name'   => Lang::get('payment_name'),
            'order_sn'   => Lang::get('order_sn'),
        );
        /* 默认搜索的字段是店铺名 */
        $field = 'seller_name';
        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
        $conditions = $this->_get_query_conditions(array(array(
                'field' => $field,       //按用户名,店铺名,支付方式名称进行搜索
                'equal' => 'LIKE',
                'name'  => 'search_name',
            ),array(
                'field' => 'status',
                'equal' => '=',
                'type'  => 'numeric',
            ),array(
                'field' => 'add_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'add_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time_end',
            ),array(
                'field' => 'order_amount',
                'name'  => 'order_amount_from',
                'equal' => '>=',
                'type'  => 'numeric',
            ),array(
                'field' => 'order_amount',
                'name'  => 'order_amount_to',
                'equal' => '<=',
                'type'  => 'numeric',
            ),
        ));
		return $conditions;
	}
	
    /**
     *    查看
     *

     *    @param    none
     *    @return    void
     */
    function view()
    {
        $order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$order_id)
        {
            $this->show_warning('no_such_order');

            return;
        }

        /* 获取订单信息 */
        $model_order =& m('order');
        $order_info = $model_order->get(array(
            'conditions'    => $order_id,
            'join'          => 'has_orderextm',
            'include'       => array(
                'has_ordergoods',   //取出订单商品
            ),
        ));

        if (!$order_info)
        {
            $this->show_warning('no_such_order');
            return;
        }
		
		$distribution_mod = &m('distribution');
		$order_info['distribution'] = $distribution_mod->get_profit($order_id);
		$member_mod = &m('member');
		foreach($order_info['distribution'] as $k=>$val)
		{
			$member = $member_mod->get(array('conditions' => $val['user_id'],'fields'=>'user_name'));
			$order_info['distribution'][$k]['user_name'] = $member['user_name'];
			$order_info['distribution'][$k]['layer'] = $k+1;
		}
		
        $order_type =& ot($order_info['extension']);
        $order_detail = $order_type->get_order_detail($order_id, $order_info);

        foreach ($order_detail['data']['goods_list'] as $key => $goods)
        {
            if (substr($goods['goods_image'], 0, 7) != 'http://')
            {
                $order_detail['data']['goods_list'][$key]['goods_image'] = SITE_URL . '/' . $goods['goods_image'];
            }
        }
        $this->assign('order', $order_info);
        $this->assign($order_detail['data']);
        $this->display('order.view.html');
    }
	
	function export_csv()
	{
		$conditions = '1=1';
		if ($_GET['id'] != '') {
            $ids = explode(',', $_GET['id']);
			$conditions .= ' AND order_alias.order_id' . db_create_in($ids);
        }
		$conditions .= $this->get_query_conditions();
		if($_GET['type'] == 'distribution')
		{
			$conditions .= ' AND did>0';
		}
        $model_order =& m('order');
        $orders = $model_order->findAll(array(
            'conditions'    => $conditions,
			'join'          => 'has_orderextm',
            'order'         => "add_time desc",
			'include'       => array(
                'has_ordergoods',   //取出订单商品
            ),
        )); 
		if(!$orders) {
			$this->show_warning('no_such_order');
            return;
		}
		
		/* xls文件数组 */
		$record_xls = array();		
		$record_title = array(
			'seller_name' 		=> 	'店铺名称',
    		'order_sn' 		=> 	'订单编号',
    		'add_time' 		=> 	'下单时间',
    		'buyer_name' 		=> 	'买家名称',
    		'order_amount' => 	'订单总额',
    		'payment_name' 	=> 	'付款方式',
			'name' => '收货人姓名',
    		'buyer_addr' 	=> 	'地址',
			'buyer_phone' => '电话',
			'pay_message'		=>	'买家留言',
			'status'		=>	'订单状态',
			'invoice_no'		=>	'快递单号',
			'postscript'		=>	'备注',
			'goods'		=>	'商品信息',
		);
		$folder = 'order_'.local_date('Ymdhis', gmtime());
		$record_xls[] = $record_title;
		$amount = 0;
		foreach($orders as $key=>$order)
    	{
			$record_value['seller_name']	=	$order['seller_name'];
			$record_value['order_sn']	=	$order['order_sn'];
			$record_value['add_time']	=	local_date('Y/m/d H:i:s',$order['add_time']);
			$record_value['buyer_name']	=	$order['buyer_name'];
			$record_value['order_amount']	=	$order['order_amount'];
			$record_value['payment_name']	=	$order['payment_name'];
			$record_value['name']	=	$order['consignee'];
			$record_value['buyer_addr']	=	$order['region_name'].$order['address'];
			$record_value['buyer_phone']	=	$order['phone_mob'];
			$record_value['pay_message']   =   $order['pay_message'];
			$record_value['status']	=	order_status($order['status']);
			$record_value['invoice_no']	=	$order['invoice_no'];
			$record_value['postscript']	=	$order['postscript'];
			$record_value['goods'] = '';
			foreach($order['order_goods'] as $ordergoods)
			{
				$record_value['goods'] .= '商品名称：'.$ordergoods['goods_name'].',价格：'.$ordergoods['price'].',数量：'.$ordergoods['quantity'].'；';
			}
        	$record_xls[] = $record_value;
			$amount += $order['order_amount'];
    	}
		$record_xls[] = array('订单总数:',count($orders).'笔','订单总额:',$amount.'元');
		import('excelwriter.lib');
		$ExcelWriter = new ExcelWriter(CHARSET, $folder);
		$ExcelWriter->add_array($record_xls);
		$ExcelWriter->output();
	}
}
?>
