<?php

class TeamModel extends BaseModel
{
    var $table  = 'group_team';
    var $prikey = 'team_id';
    var $_name  = 'team';
	
	var $_relation = array(
	    'belongs_to_user' => array(
            'model'         => 'member',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'user_id',
            'reverse'       => 'has_team',
        ),
		'belongs_to_group' => array(
            'model'         => 'member',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'group_id',
            'reverse'       => 'has_team',
        )
	);
	
	function __construct($db,$params)
	{
		parent::__construct($db,$params);
		$this->checkTeamStatus();
	}
	
	function checkTeamStatus()
	{
		$items = db()->getAll('select * from '.DB_PREFIX.'group_team t left join '.DB_PREFIX.'groupbuy gb on t.group_id=gb.group_id where status is null AND (add_time+each_expire_time*3600) < '.gmtime());

		if(!empty($items)){
			foreach($items as $key=>$val)
			{
				parent::edit($val['team_id'], 'status=0');
				//把订单关闭和把已支付的货款退回放到任务中操作
			}
		}
	}
	
	function handleAfterPayment($order)
	{
		if(!empty($order)){
			$team_id = $order['team_id'];
			if($team_id > 0){//team_id存在说明是跟别人的团，不存在则是自己开的团
				parent::edit($team_id,'number=number+1');
			}
			else{
				$team_id = parent::add(array(
					'group_id' => $order['group_id'],
					'user_id'  => $order['buyer_id'],
					'user_name'=> $order['buyer_name'],
					'number'   => 1,
					'add_time' => gmtime()
				));
			}
			
			if($team_id > 0){
				if(!$order['team_id'])
				{
					$order_mod = &m('order');
					$order_mod->edit($order['order_id'], 'team_id='.$team_id);
				}
				
				$groupbuy_mod = &m('groupbuy');
				$group = $groupbuy_mod->get(array(
					'conditions' => 'group_id=' .$order['group_id']
				));
				
				$team = parent::get($team_id);
				if($team['number'] >= $group['min_quantity']){
					parent::edit($team_id, 'status=1');
				}
			}
		}
	}
}

?>