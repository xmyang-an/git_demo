<?php

class ReportModel extends BaseModel
{
    var $table  = 'report';
    var $prikey = 'report_id';
    var $alias  = 'report';
    var $_name  = 'report'; 
	
	function reportRelationImages($id)
	{
		$ids = array();
		if(is_array($id))
		{
			$ids = $id;
		}
		else
		{
			$ids = array($id);
		}
		
		$reports = parent::find(array(
			'conditions' => 'report_id '.db_create_in($ids),
			'fields'     => 'images'
		));
		
		$file_ids = array();
		if(!empty($reports))
		{
			foreach($reports as $key=>$report)
			{
				$images = unserialize($report['images']);
				$file_ids = array_merge($file_ids, array_keys($images));
			}
		}
		
		return $file_ids;
	}
}
?>