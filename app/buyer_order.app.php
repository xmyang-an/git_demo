<?php

/**
 *    买家的订单管理控制器
 *
 *    @author    MiMall
 *    @usage    none
 */
class Buyer_orderApp extends MemberbaseApp
{
    function index()
    {
        /* 获取订单列表 */
        $this->_get_orders();

        /* 当前位置 */
        $this->_curlocal(LANG::get('my_order'), 'index.php?app=buyer_order', LANG::get('order_list'));

        /* 当前用户中心菜单 */
        $this->_curitem('my_order');
        $this->_curmenu('order_list');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('my_order'));
        $this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'dialog/dialog.js',
                    'attr' => 'id="dialog_js"',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.ui/i18n/' . i18n_code() . '.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/smoothness/jquery.ui.css',
        ));


        /* 显示订单列表 */
        $this->display('buyer_order.index.html');
    }
    /**
     *    查看订单详情
     *
     *    @author    MiMall
     *    @return    void
     */
    function view()
    {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $model_order =& m('order');
        $order_info = $model_order->get(array(
            'fields'        => "*, order.add_time as order_add_time",
            'conditions'    => "order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'),
            'join'          => 'belongs_to_store',
            ));
        if (!$order_info)
        {
            $this->show_warning('no_such_order');
            return;
        }
		
		// 从用户表读取卖家手机号
		$member_mod = &m('member');
		$seller_info = $member_mod->get(array('conditions' => 'user_id='.$order_info['seller_id'], 'fields' => 'phone_mob,im_qq, im_aliww'));
		$order_info = array_merge($order_info, $seller_info);

        /* 当前位置 */
        $this->_curlocal(LANG::get('my_order'), 'index.php?app=buyer_order', LANG::get('view_order'));

        /* 当前用户中心菜单 */
        $this->_curitem('my_order');

        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('order_detail'));

        /* 调用相应的订单类型，获取整个订单详情数据 */
        $order_type =& ot($order_info['extension']);
        $order_detail = $order_type->get_order_detail($order_id, $order_info);
        foreach ($order_detail['data']['goods_list'] as $key => $goods)
        {
            empty($goods['goods_image']) && $order_detail['data']['goods_list'][$key]['goods_image'] = Conf::get('default_goods_image');
        }
		
		/* 读取订单的赠品（如果有）*/
		$ordergift_mod = &m('ordergift');
		$order_detail['data']['gift_list'] = $ordergift_mod->find('order_id='.$order_id);
		
        $this->assign('order', $order_info);
        $this->assign($order_detail['data']);
        $this->display('buyer_order.view.html');
    }
	
	/**
     *    取消订单
     *
     *    @author    MiMall
     *    @return    void
     */
    function cancel_order()
    {
        $order_id = isset($_GET['order_id']) ? html_script($_GET['order_id']) : '';
        if (!$order_id)
        {
            echo Lang::get('no_such_order');
	    	return;
        }
		
        // 只有已提交和待付款的订单才可取消
        $status = array(ORDER_SUBMITTED, ORDER_PENDING);
        $order_ids = explode(',', $order_id);
        if ($ext)
        {
            $ext = ' AND ' . $ext;
        }

        $model_order    =&  m('order');
        $order_info     = $model_order->find(array(
            'conditions'    => "order_id" . db_create_in($order_ids) . " AND buyer_id=" . $this->visitor->get('user_id') . " AND status " . db_create_in($status) . $ext,
        ));
        
        if (!$order_info)
        {
            echo Lang::get('no_such_order');

            return;
        }
		$ids = array_keys($order_info);
		
        if (!IS_POST)
        {
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->assign('orders', $order_info);
            $this->assign('order_id', implode(',', $ids));
            $this->display('buyer_order.cancel.html');
        }
        else
        {
			$deposit_trade_mod = &m('deposit_trade');
            foreach ($ids as $val)
            {
                $id = intval($val);
				
                $model_order->edit($id, array('status' => ORDER_CANCELED));
                if ($model_order->has_error())
                {
                    //$_erros = $model_order->get_error();
                    //$error = current($_errors);
                    //$this->json_error(Lang::get($error['msg']));
                    //return;
                    continue;
                }
				
				/* 修改交易记录状态为关闭 */
				$deposit_trade_mod->edit("merchantId='" . MERCHANTID . "' AND bizIdentity='".TRADE_ORDER."' AND bizOrderId='" . $order_info[$id]['order_sn']. "' AND buyer_id=" . $this->visitor->get('user_id'), array('status' => 'CLOSED', 'end_time' => gmtime()));

                /* 加回商品库存 */
                $model_order->change_stock('+', $id);
                $cancel_reason = (!empty($_POST['remark'])) ? $_POST['remark'] : $_POST['cancel_reason'];
                /* 记录订单操作日志 */
                $order_log =& m('orderlog');
                $order_log->add(array(
                    'order_id'     => $id,
                    'operator'     => addslashes($this->visitor->get('user_name')),
                    'order_status' => order_status($order_info[$id]['status']),
                    'changed_status'=> order_status(ORDER_CANCELED),
                    'remark'    => $cancel_reason,
                    'log_time'  => gmtime(),
                ));
				
				// 订单取消后，归还买家之前被预扣积分 
				$integral_mod = &m('integral');
				$integral_mod ->return_integral($order_info[$id]);

                /* 发送给卖家订单取消通知 */
                $model_member =& m('member');
                $seller_info   = $model_member->get($order_info[$id]['seller_id']);
                $mail = get_mail('toseller_cancel_order_notify', array('order' => $order_info[$id], 'reason' => $_POST['remark']));
                $this->_mailto($seller_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

                $new_data = array(
                    'status'    => Lang::get('order_canceled'),
                    'actions'   => array(), //取消订单后就不能做任何操作了
                );
            }
            $this->pop_warning('ok', 'buyer_order_cancel_order');
        }
    }

    /**
     *    确认订单
     *
     *    @author    MiMall
     *    @return    void
     */
    function confirm_order()
    {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        if (!$order_id)
        {
            echo Lang::get('no_such_order');

            return;
        }
        $model_order    =&  m('order');
        /* 只有已发货的订单可以确认 */
        $order_info     = $model_order->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id') . " AND status=" . ORDER_SHIPPED);
        if (empty($order_info))
        {
            echo Lang::get('no_such_order');

            return;
        }
		
		/* 交易信息 */
		$deposit_trade_mod = &m('deposit_trade');
		$tradeInfo = $deposit_trade_mod->get(array(
			'conditions' 	=> "merchantId='".MERCHANTID."' AND bizIdentity='".TRADE_ORDER."' AND bizOrderId='".$order_info['order_sn']."' AND buyer_id=".$this->visitor->get('user_id')));
		
		if (empty($tradeInfo))
        {
            echo Lang::get('no_such_order');

            return;
        }
		
        if (!IS_POST)
        {
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->assign('order', $order_info);
            $this->display('buyer_order.confirm.html');
        }
        else
        {
			/* 有退款功能： 如果该订单有退款商品（退款关闭的除外），则不允许确认收货*/
			$refund_mod 	= &m('refund');
			$refund = $refund_mod->get(array('conditions'=>"tradeNo='".$tradeInfo['tradeNo']."'", 'fields'=>'refund_id, status'));

			if($refund && !in_array($refund['status'], array('CLOSED', 'SUCCESS'))) {
				
				$this->pop_warning('order_not_confirm_for_refund');

                return;
			}
			
			/* 如果订单中的商品为空，则认为订单信息不完整，不执行 */
			$ordergoods_mod =& m('ordergoods');
            $order_goods = $ordergoods_mod->find("order_id={$order_id}");
			
			if(empty($order_goods)) {

				$this->pop_warning('no_confirm_goods');
				return;
			}


			/* 更新订单状态 */
            $model_order->edit($order_id, array('status' => ORDER_FINISHED, 'finished_time' => gmtime()));

            if ($model_order->has_error())
            {
                $this->pop_warning($model_order->get_error());

                return;
            }
			
			/* 转到对应的业务实例，不同的业务实例用不同的文件处理，如购物，卖出商品，充值，提现等，每个业务实例又继承支出或者收入 */
			$depopay_type    =&  dpt('income', 'sellgoods');
			$result  		 = $depopay_type->submit(array(
				'trade_info' =>  array('user_id' => $order_info['seller_id'], 'party_id' => $order_info['buyer_id'], 'amount' => $order_info['order_amount']),
				'extra_info' =>  $order_info + array('tradeNo' => $tradeInfo['tradeNo']),
				'post'		 =>	 $_POST,
			));
			
			if(!$result)
			{
				$this->pop_warning($depopay_type->_get_errors());
				return;
			}
			
			$mod_distribution = &m('distribution');
		 	$d_profit = $mod_distribution->get_profit($order_info['order_id']);
			$depopay_type    =&  dpt('income', 'distribution');
			$result = $depopay_type->submit(array(
				'trade_info' =>  array('user_id'=>$order_info['seller_id'], 'party_id'=>$order_info['buyer_id'], 'amount'=>$order_info['order_amount']),
				'extra_info' =>  $order_info + array('tradeNo' => $tradeInfo['tradeNo'],'d_profit' => $d_profit),
				'post'		 =>	 $_POST,
			));
			if(!$result)
			{
				$this->pop_warning($depopay_type->_get_errors());
				return;
			}
			
			$depopay_type    =&  dpt('income', 'refer');
			$result = $depopay_type->submit(array(
				'trade_info' =>  array('user_id'=>$order_info['seller_id'], 'party_id'=>$order_info['buyer_id']),
				'extra_info' =>  $order_info + array('tradeNo' => $tradeInfo['tradeNo']),
				'post'		 =>	 $_POST,
			));
			if(!$result)
			{
				$this->pop_warning($depopay_type->_get_errors());
				return;
			}

			/* 买家确认收货后，即交易完成，将订单积分表中的积分进行派发 */
			$integral_mod = &m('integral');
			$integral_mod->distribute_integral($order_info);
			
	
			/* 更新累计销售件数 以及将本次确认的商品 状态值修改为 交易成功 */
            $model_goodsstatistics =& m('goodsstatistics');
            	
            foreach ($order_goods as $key => $goods)
            {
				$model_goodsstatistics->edit($goods['goods_id'], "sales=sales+{$goods['quantity']}");
				$ordergoods_mod->edit($goods['rec_id'], array('status'=>'SUCCESS'));
            }
			
			/* 记录订单操作日志 */
            $order_log =& m('orderlog');
            $order_log->add(array(
 				'order_id'  => $order_id,
               	 'operator'  => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status(ORDER_FINISHED),
               	 'remark'    => Lang::get('buyer_confirm'),
                'log_time'  => gmtime(),
            ));

            $new_data = array(
                'status'    => Lang::get('order_finished'),
                'actions'   => array('evaluate'),
            );
			
			/* 短信和邮件提醒： 买家已确认通知卖家 */
			$this->sendMailMsgNotify($order_info, array(
					'key' => 'toseller_finish_notify'
				),
				array(
					'key' => 'check', 
					'body' => sprintf(Lang::get('sms_check'), $order_info['order_sn'], $order_info['buyer_name'])
				)
			);
	
            $this->pop_warning('ok','', 'index.php?app=buyer_order&act=evaluate&order_id='.$order_id);
        }

    }

    /**
     *    给卖家评价
     *
     *    @author    MiMall
     *    @param    none
     *    @return    void
     */
    function evaluate()
    {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        if (!$order_id)
        {
            $this->show_warning('no_such_order');

            return;
        }

        /* 验证订单有效性 */
        $model_order =& m('order');
        $order_info  = $model_order->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
        if (!$order_info)
        {
            $this->show_warning('no_such_order');

            return;
        }
        if ($order_info['status'] != ORDER_FINISHED)
        {
            /* 不是已完成的订单，无法评价 */
            $this->show_warning('cant_evaluate');

            return;
        }
        if ($order_info['evaluation_status'] != 0)
        {
            /* 已评价的订单 */
            $this->show_warning('already_evaluate');

            return;
        }
        $model_ordergoods =& m('ordergoods');

        if (!IS_POST)
        {
            /* 显示评价表单 */
            /* 获取订单商品 */
            $goods_list = $model_ordergoods->find("order_id={$order_id}");
			$goods_mod = &m('goods');
			$gcategory_mod = &bm('gcategory');
			$uploadedfile_mod = &m('uploadedfile');
            foreach ($goods_list as $key => $goods)
            {
                empty($goods['goods_image']) && $goods_list[$key]['goods_image'] = Conf::get('default_goods_image');
				$goodsinfo = $goods_mod->get($goods['goods_id']);
				$cate_ids = $gcategory_mod->get_parents($goodsinfo['cate_id']);
				foreach($cate_ids as $id){
					$tpl = $gcategory_mod->get($id);
					if(!empty($tpl['eval_tips'])){
						$goods_list[$key]['eval_tips'] = explode(',',$tpl['eval_tips']);
						break;
					}
				}
				
				$goods_list[$key]['eval_images'] = $uploadedfile_mod->find(array(
					 'conditions' => "belong=".BELONG_EVALUATION." AND item_id=".$goods['rec_id']." AND store_id=".$this->visitor->get('user_id'),
					 'order' => 'add_time ASC'
				 ));
            }
            $this->_curlocal(LANG::get('my_order'), 'index.php?app=buyer_order', LANG::get('evaluate'));
            $this->assign('goods_list', $goods_list);
            $this->assign('order', $order_info);

			$this->import_resource(array(
            	'script' => 'jquery.plugins/raty/jquery.raty.js,webuploader/webuploader.js,webuploader/webuploader.compressupload.js',
            	'style'  => 'jquery.plugins/raty/jquery.raty.css'
			));
			
			$this->assign("belong", BELONG_EVALUATION);
			
            $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('credit_evaluate'));
            $this->display('buyer_order.evaluate.html');
        }
        else
        {
            $evaluations = array();
            /* 写入评价 */
            foreach ($_POST['evaluations'] as $rec_id => $evaluation)
            {
                if ($evaluation['evaluation'] <= 0 || $evaluation['evaluation'] > 3)
                {
                    $this->show_warning('evaluation_error');

                    return;
                }
                switch ($evaluation['evaluation'])
                {
                    case 3:
                        $credit_value = 1;
                    break;
                    case 1:
                        $credit_value = -1;
                    break;
                    default:
                        $credit_value = 0;
                    break;
                }
				
                $evaluations[intval($rec_id)] = array(
                    'evaluation'    => $evaluation['evaluation'],
                    'comment'       => addslashes($evaluation['comment']),
                    'credit_value'  => $credit_value,
					'goods_evaluation'=>$evaluation['goods_evaluation'],
					'service_evaluation'=>$evaluation['service_evaluation'],
					'shipped_evaluation'=>$evaluation['shipped_evaluation'],
					'tips' => !empty($evaluation['tips']) ? implode(',',array_filter($evaluation['tips'])) : '',
					'share_images' => !empty($evaluation['eval_file_id']) ? serialize($evaluation['eval_file_id']) : ''
                );
            }
            $goods_list = $model_ordergoods->find("order_id={$order_id}");
            foreach ($evaluations as $rec_id => $evaluation)
            {
                $model_ordergoods->edit("rec_id={$rec_id} AND order_id={$order_id}", $evaluation);
                $goods_url = SITE_URL . '/' . url('app=goods&id=' . $goods_list[$rec_id]['goods_id']);
                $goods_name = $goods_list[$rec_id]['goods_name'];
                $this->send_feed('goods_evaluated', array(
                    'user_id'   => $this->visitor->get('user_id'),
                    'user_name'   => $this->visitor->get('user_name'),
                    'goods_url'   => $goods_url,
                    'goods_name'   => $goods_name,
                    'evaluation'   => Lang::get('order_eval.' . $evaluation['evaluation']),
                    'comment'   => $evaluation['comment'],
                    'images'    => array(
                        array(
                            'url' => SITE_URL . '/' . $goods_list[$rec_id]['goods_image'],
                            'link' => $goods_url,
                        ),
                    ),
                ));
            }

            /* 更新订单评价状态 */
            $model_order->edit($order_id, array(
                'evaluation_status' => 1,
                'evaluation_time'   => gmtime()
            ));

            /* 更新卖家信用度及好评率 */
            $model_store =& m('store');
            $model_store->edit($order_info['seller_id'], array(
                'credit_value'  =>  $model_store->recount_credit_value($order_info['seller_id']),
                'praise_rate'   =>  $model_store->recount_praise_rate($order_info['seller_id']),
				'avg_goods_evaluation'   =>   Psmb_init()->update_dynamic_evaluation('goods_evaluation',$order_info['seller_id']),
				'avg_service_evaluation'   =>   Psmb_init()->update_dynamic_evaluation('service_evaluation',$order_info['seller_id']),
				'avg_shipped_evaluation'   =>   Psmb_init()->update_dynamic_evaluation('shipped_evaluation',$order_info['seller_id']),
            ));

            /* 更新商品评价数 */
            $model_goodsstatistics =& m('goodsstatistics');
            $goods_ids = array();
            foreach ($goods_list as $goods)
            {
                $goods_ids[] = $goods['goods_id'];
            }
            $model_goodsstatistics->edit($goods_ids, 'comments=comments+1');


            $this->show_message('evaluate_successed',
                'back_list', url('app=buyer_order'));
        }
    }

    /**
     *    获取订单列表
     *
     *    @author    MiMall
     *    @return    void
     */
    function _get_orders()
    {
        $page = $this->_get_page(10);
        $model_order =& m('order');
        !$_GET['type'] && $_GET['type'] = 'all_orders';
        $con = array(
            array(      //按订单状态搜索
                'field' => 'status',
                'name'  => 'type',
                'handler' => 'order_status_translator',
            ),
            array(      //按店铺名称搜索
                'field' => 'seller_name',
                'equal' => 'LIKE',
            ),
            array(      //按下单时间搜索,起始时间
                'field' => 'add_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),
            array(      //按下单时间搜索,结束时间
                'field' => 'add_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'=> 'gmstr2time_end',
            ),
            array(      //按订单号
                'field' => 'order_sn',
            ),
        );
        $conditions = $this->_get_query_conditions($con);
        /* 查找订单 */
        $orders = $model_order->findAll(array(
            'conditions'    => "buyer_id=" . $this->visitor->get('user_id') . "{$conditions}",
            'fields'        => 'this.*,extension,group_id,team_id',
            'count'         => true,
            'limit'         => $page['limit'],
            'order'         => 'order_id DESC, add_time DESC',
            'include'       =>  array(
                'has_ordergoods',       //取出商品
            ),
        ));
		
		$member_mod =& m('member');
		$deposit_trade_mod = &m('deposit_trade');
		$refund_mod = &m('refund');
		$team_mod = &m('team');
		$ordergift_mod = &m('ordergift');
        foreach ($orders as $key1 => $order)
        {
			if(!$order['order_goods']) {
				unset($orders[$key1]);
				continue;
			}
            foreach ($order['order_goods'] as $key2 => $goods)
            {
                empty($goods['goods_image']) && $orders[$key1]['order_goods'][$key2]['goods_image'] = Conf::get('default_goods_image');
            }
				
			/* 是否申请过退款 */
			$tradeInfo = $deposit_trade_mod->get(array(
				'conditions' => 'merchantId="'.MERCHANTID.'" AND bizIdentity="'.TRADE_ORDER.'" AND bizOrderId="'.$order['order_sn'].'"', 'fields' => 'tradeNo'));
			if($tradeInfo) {
				if( $refund = $refund_mod->get(array('conditions'=>'tradeNo="'.$tradeInfo['tradeNo'].'"','fields'=>'status'))) {
					$orders[$key1]['refund_status'] = $refund['status'];
					$orders[$key1]['refund_id'] = $refund['refund_id'];
            	}
			}
			
			/* 读取订单的赠品（如果有）*/
			$orders[$key1]['order_gift'] = $ordergift_mod->find('order_id='.$order['order_id']);

			$orders[$key1]['goods_quantities'] = count($order['order_goods']) + count($orders[$key1]['order_gift']);
			$orders[$key1]['seller_info'] = $member_mod->get(array('conditions'=>'user_id='.$order['seller_id'],'fields'=>'real_name,im_qq,im_aliww'));
			
			$orders[$key1]['can_refund'] = true;
			$orders[$key1]['can_ship'] = true;
			if($order['extension'] == 'groupbuy')//团购的订单不允许退款
			{
				$orders[$key1]['can_refund'] = false;
				if($order['team_id'] > 0){
					$team = $team_mod->get($order['team_id']);
					if($team['status'] == 1){
						$orders[$key1]['can_refund'] = true;
					}
				}
			}
        }
		
        $page['item_count'] = $model_order->getCount();
        $this->assign('types', array('all'     => Lang::get('all_orders'),
                                     'pending' => Lang::get('pending_orders'),
                                     'submitted' => Lang::get('submitted_orders'),
                                     'accepted' => Lang::get('accepted_orders'),
                                     'shipped' => Lang::get('shipped_orders'),
                                     'finished' => Lang::get('finished_orders'),
                                     'canceled' => Lang::get('canceled_orders')));
        $this->assign('type', $_GET['type']);
        $this->assign('orders', $orders);
        $this->_format_page($page);
        $this->assign('page_info', $page);
    }
	
	function uploadEvaluationImages()
	{
		import('image.func');
        import('uploader.lib');
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE);
        $uploader->allowed_size(SIZE_GOODS_IMAGE); // 2M
        $upload_mod =& m('uploadedfile');
		
		$user_id = $this->visitor->get('user_id');
		$id = intval($_GET['item_id']);

        $files = $_FILES['file'];
        if ($files['error'] === UPLOAD_ERR_OK)
        {
			$uploaded = $upload_mod->getOne('select count(*) from '.DB_PREFIX.'uploaded_file where item_id='.$id.' AND belong='. BELONG_EVALUATION.' AND store_id='.$user_id);
			if($uploaded >= 10)
			{
				$this->json_error('一个订单商品晒图最多可以上传10张图片');
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
            $dirname = 'data/files/mall/evaluations';

            $filename  = $uploader->random_filename();
            $file_path = $uploader->save($dirname, $filename);
             /* 处理文件入库 */
            $data = array(
                'store_id'  => $user_id,
                'file_type' => $file['type'],
                'file_size' => $file['size'],
                'file_name' => $file['name'],
                'file_path' => $file_path,
                'belong'    => BELONG_EVALUATION,
                'item_id'   => $id,
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
            'conditions' => 'f.file_id = '.$id.' AND belong='. BELONG_EVALUATION.' AND store_id='.$this->visitor->get('user_id')
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

    function _get_member_submenu()
    {
        $menus = array(
            array(
                'name'  => 'order_list',
                'url'   => 'index.php?app=buyer_order',
            ),
        );
        return $menus;
    }

}

?>
