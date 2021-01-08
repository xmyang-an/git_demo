<?php

/* 搭配套餐商品 */
class MealgoodsModel extends BaseModel
{
    var $table  = 'meal_goods';
    var $prikey = 'mg_id';
    var $_name  = 'mealgoods';
    var $_relation = array(
        // 一个套餐商品只能属于一个套餐
        'belongs_to_meal' => array(
            'model'         => 'meal',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'meal_id',
            'reverse'       => 'has_mealgoods',
        ),
    );
}

?>