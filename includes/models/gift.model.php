<?php

class GiftModel extends BaseModel
{
    var $table  = 'gift';
    var $prikey = 'goods_id';
    var $_name  = 'gift';
	
	/**
     * 删除赠品相关数据：包括赠品图片、赠品描述图，要在删除赠品之前调用
     *
     * @param   string  $goods_ids  商品id，用逗号隔开
     */
    function drop_data($goods_ids, $store_id = 0)
    {
		// 删除主图
		$images = parent::find(array(
			'conditions' => 'goods_id'.db_create_in($goods_ids) . ' AND store_id='.$store_id . ' AND default_image <> "' . Conf::get('default_goods_image') . '"',
			'fields' 	 => 'default_image as image_url',
		));
		
		foreach ($images as $image)
        {
            if (!empty($image['image_url']) && trim($image['image_url']) && substr($image['image_url'], 0, 4) != 'http' && file_exists(ROOT_PATH . '/' . $image['image_url']))
            {
                _at(unlink, ROOT_PATH . '/' . $image['image_url']);
            }
        }
		
		// 删除描述图
        $uploadedfile_mod =& m('uploadedfile');
        $images = $uploadedfile_mod->find(array(
            'conditions' => 'item_id' . db_create_in($goods_ids) . ' AND belong =' .BELONG_GIFT . ' AND store_id=' . $store_id,
            'fields' => 'file_path as image_url',
        ));

        foreach ($images as $image)
        {
            if (!empty($image['image_url']) && trim($image['image_url']) && substr($image['image_url'], 0, 4) != 'http' && file_exists(ROOT_PATH . '/' . $image['image_url']))
            {
                _at(unlink, ROOT_PATH . '/' . $image['image_url']);
            }
        }
    }
}

/* 赠品业务模型 business model */
class GiftBModel extends GiftModel
{
    var $_store_id = 0;
}

?>