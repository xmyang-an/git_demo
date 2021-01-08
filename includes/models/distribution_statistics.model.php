<?php

class Distribution_statisticsModel extends BaseModel
{
    var $table = 'distribution_statistics';
    var $prikey= 'user_id';
    var $_name = 'distribution_statistics';
	
	function update_statistics($param)
	{
        foreach($param as $key=>$val)
		{
			$data = array();
			if($data = parent::get($val['user_id']))
			{
				$data['amount'] += $val['amount'];
				$data['layer'.($key+1)] += $val['amount'];  
				parent::edit($val['user_id'],$data);
			}
			else
			{
				$data['user_id'] = $val['user_id'];
				$data['amount'] = $val['amount'];
				$data['layer'.($key+1)] = $val['amount'];
				parent::add($data);
			}
		}
		return true;
	}
}
?>