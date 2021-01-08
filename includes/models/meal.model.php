<?php

/* 搭配套餐 */
class MealModel extends BaseModel
{
    var $table  = 'meal';
    var $prikey = 'meal_id';
    var $_name  = 'meal';

    /* 添加编辑时自动验证 */
    var $_autov = array(
        'title' => array(
            'required'  => true,    //必填
            'min'       => 1,       //最短1个字符
            'max'       => 255,     //最长255个字符
            'filter'    => 'trim',
        ),
    );
	var $_relation = array(
        // 一个套餐对应多个搭配宝贝
        'has_mealgoods' => array(
            'model'         => 'mealgoods',
            'type'          => HAS_MANY,
            'foreign_key'   => 'meal_id',
            'dependent'     => true
        ),
	);
	
	function add($data)
	{
		$selected_ids = $data['selected_ids'];
		if(!$selected_ids) return false;
		
		unset($data['selected_ids']);

		if($meal_id = parent::add($data)) {
			
			/* 添加套餐宝贝到 */
			$mealgoods_mod = &m('mealgoods');
			
			foreach($selected_ids as $goods_id){
				$mealgoods_mod->add(array(
					'meal_id' 	=> $meal_id,
					'goods_id'	=> $goods_id,
				));
			}
		}
		return $meal_id;
	}
	
	function edit_data($conditions, $data, $meal_id)
	{
		$selected_ids = $data['selected_ids'];
		if(!$selected_ids) return false;

		$mealgoods_mod = &m('mealgoods');
		
		/* 找出本套餐的搭配宝贝，如果相同则不用修改，如果不同，则添加 */
		$goods_ids = array();
		$mealgoods = $mealgoods_mod->find(array('conditions'=>'meal_id='.$meal_id,'fields'=>'goods_id'));
		foreach($mealgoods as $goods) {
			$goods_ids[] = $goods['goods_id'];
		}
		if($diff = array_diff($goods_ids, $selected_ids)) {
			
			/* 删除原套餐中部分商品 */
			$mealgoods_mod->drop('meal_id='.$meal_id.' AND goods_id ' . db_create_in($diff));

		}
		
		/* 添加新的套餐宝贝到 */
		foreach($selected_ids as $goods_id){
			if(!in_array($goods_id, $goods_ids)) {
				$mealgoods_mod->add(array(
					'meal_id' 	=> $meal_id,
					'goods_id'	=> $goods_id,
				));
			}
		}
		
		unset($data['selected_ids']);
		parent::edit($conditions, $data);
	}
	
	function has_meal($goods_id){
		if(!$goods_id){
			return false;
		}
		$mealgoods_mod = &m('mealgoods');
		$mealgoods = $mealgoods_mod->find(array(
			'conditions' => 'status = 1 AND goods_id='.$goods_id,
			'join'       => 'belongs_to_meal',
			'fields'	 => 'this.meal_id',
		));
		if(empty($mealgoods)){
			return false;
		}else{
			return true;
		}
	}
}  

?>