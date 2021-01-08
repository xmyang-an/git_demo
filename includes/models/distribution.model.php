<?php

class DistributionModel extends BaseModel
{
    var $table = 'distribution';
    var $prikey= 'dst_id';
    var $_name = 'distribution';
	
	function _gen_did()
	{
        mt_srand((double) microtime() * 1000000);
        $timestamp = gmtime();
        $y = date('y', $timestamp);
        $z = date('z', $timestamp);
        $did = $y . str_pad($z, 3, '0', STR_PAD_LEFT) . str_pad(mt_rand(1, 99), 2, '0', STR_PAD_LEFT);
        $distrition = $this->get('did=' . $did);
        if (empty($distrition))
        {
            return $did;
        }
        return $this->_gen_did();
	}

	//取倒数三个user_id 包含自己
	function get_parent_ids($did, $findall = false)
	{
		$data = array();
		if(!$did)
		{
			return false;
		}
		$distrition = $this->get('did=' . $did);
		if($distrition)
		{
			$id = $distrition['parent_id'];
			$store_id = $distrition['store_id'];
			$data[] = $distrition['user_id']; 
			while ($id > 0)
            {
                $sql = "SELECT user_id,parent_id FROM {$this->table} WHERE user_id = '$id' AND store_id = '{$store_id}'";
                $row = $this->getRow($sql);
                if ($row)
                {
                    $data[] = $row['user_id'];
                    $id    = $row['parent_id'];
                }
            }
		}

		return $findall ? $data : array_slice($data,0,3);
	}

	//通过user_id 找出 三代did  包含自己
	function get_dids_by_user($user_id)
	{
		$data = array();
		$distributions = $this->find('user_id='.$user_id);
		foreach($distributions as $key=> $val)
		{
			$data[] = $val['did'];//自己
			$dsts1 = $this->find('parent_id='.$user_id);//1代
			foreach($dsts1 as $d1)
			{
				$data[] = $d1['did'];
				$dsts2 = $this->find('parent_id='.$d1['user_id']);//2代
				foreach($dsts2 as $d2)
				{
					$data[] = $d2['did'];
				}
			}
		}
		return $data;
	}
	

	function get_profit($order_id, $refund_goods_fee = 0)
	{
		$data = array();
		$mod_order = &m('order');
		$order = $mod_order->get(array(
			'conditions' => $order_id,
			'join'		 => 'has_orderextm',
			'fields'	 => 'order_amount,shipping_fee,did,distribution_rate,buyer_id'
		));
		$distribution_rate = unserialize($order['distribution_rate']);
		$ids = $this->get_parent_ids($order['did']);
		if($ids)
		{
			foreach($ids as $k => $id)
			{
				$data[$k]['user_id'] = $id;
				
				$data[$k]['amount']  = $order['order_amount'] - $order['shipping_fee'];
				
				//如果有退款则按订单商品金额减去退款商品金额 计算并分配分销佣金
				if($refund_goods_fee) {
					$data[$k]['amount']  -= $refund_goods_fee;
				}
				
				$data[$k]['amount']  = round($data[$k]['amount'] * $distribution_rate[$k]/100, 2);				
			}
		}
		return $data;
	}
	
	
	
	function get_distribution_rate($store_id)
	{
		$mod_store = &m('store');
		$store_info = $mod_store->get(array('conditions'=>$store_id,'fields'=>'distribution_1,distribution_2,distribution_3,enable_distribution'));
		if(!$store_info['enable_distribution'])
		{
			return false;
		}
		$distribution_rate = array($store_info['distribution_1'],$store_info['distribution_2'],$store_info['distribution_3']);
		return $distribution_rate;
	}
	
	//user 下单记录did 返回 user在该商品销售中所在的等级
	function get_layer($did,$user_id)
    {
        $ids = $this->get_parent_ids($did);
        if (empty($ids) || !in_array($user_id,$ids))
        {
            return false; 
        }
        return array_search($user_id,$ids)+1;        
    }
	
	// 检查是否允许“加入分销”（登陆和非登陆状态）
	function getCheckJoinDistributionInfo($user_id = 0, $store_id = 0, $getDid = '')
	{		
		$result = array();
		
		// 如果供货商购买了微分销功能，并且没有到期
		$appmarket_mod = &m('appmarket');
		if(($appAvailable = $appmarket_mod->getCheckAvailableInfo('distribution', $store_id)) === TRUE)
		{
			$did = $getDid;
			
			//通过did链接进入
			if($did) 
			{			
				if($distribution = parent::get('did='.$did)) 
				{
					$result['did'] = $did;
					
					//显示分销商名字和logo
					$result['store_name'] = $distribution['real_name'];
					$distribution['logo'] && $result['store_logo'] = $distribution['logo'];
					
					//  如果当前进入的分销商的店铺不是自己的 并且当前用户还没有加入该店铺的分销
					if($distribution['user_id'] != $user_id && !parent::get('store_id='.$store_id.' AND user_id='.$user_id)) 
					{
						// 此时显示允许加入分销按钮
						$result['canJoinInStore'] = TRUE;
					}
					else
					{
						$result['canJoinInStore'] = FALSE;
						$result['joinDisableMsg'] = Lang::get('join_already');
						$result['isMyDistributionStore'] = TRUE;
					}
					
				}
				
				// 如果所传递的did没有对应的分销店铺
				else
				{
					// 如果当前用户已经登录
					if($user_id)
					{
						// 并且已经成为了该店铺的分销商
						if($distribution = parent::get('store_id='.$store_id.' AND user_id='.$user_id))
						{
							$result['canJoinInStore'] = FALSE;
							$result['joinDisableMsg'] = Lang::get('join_already');
							$result['isMyDistributionStore'] = TRUE;
							
							// 记录下did的值，使自己购买自己分销店铺的商品，也能获得分销佣金
							$result['did'] = $distribution['did'];
						}
						else
						{
							$result['canJoinInStore'] = TRUE;
						}
					}
					else
					{
						// 用户没有登录，且传递到did无效，即：进入到了供货商店铺，则显示允许加入分销按钮
						$result['canJoinInStore'] = TRUE;
					}
				}

			}
			else
			{
				// 如果当前用户已经登录
				if($user_id)
				{
					// 并且已经成为了该店铺的分销商
					if($distribution = parent::get('store_id='.$store_id.' AND user_id='.$user_id))
					{
						$result['canJoinInStore'] = FALSE;
						$result['joinDisableMsg'] = Lang::get('join_already');
						$result['isMyDistributionStore'] = TRUE;
						
						// 记录下did的值，使自己购买自己分销店铺的商品，也能获得分销佣金
						$result['did'] = $distribution['did'];
					}
					// 如果当前用户不是该店铺的分销商
					else
					{
						$result['canJoinInStore'] = TRUE;
					}
				}
				 //未登录  可以加入分销
				else  {
					$result['canJoinInStore'] = TRUE;
				}		
			}
		}
		
		// 如果供货商购买的微分销功能到期了，则不允许任何人再加入分销
		else
		{
			$result['canJoinInStore'] = FALSE;
			$result['joinDisableMsg'] = Lang::get('store_distribution_expired');
		}
		
		// 如果当前用户是供货商，则
		if($user_id == $store_id)
		{
			$result['canJoinInStore'] = FALSE;
			$result['joinDisableMsg'] = Lang::get('not_join_yourself');
		}
		
		return $result;
	}
}
?>