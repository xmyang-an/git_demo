<?php

include_once(ROOT_PATH . '/includes/ordertypes/normal.otype.php');

class GroupbuyOrder extends NormalOrder
{
    var $_name = 'groupbuy';
	
	function __construct($params)
    {
        parent::__construct($params);
    }
}

?>
