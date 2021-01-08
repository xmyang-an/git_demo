<?php

/**
 *    我的收藏控制器
 *
 *    @author    Garbin
 *    @usage    none
 */
class My_favoriteApp extends MemberbaseApp
{
    
    /**
     *    收藏项目
     *
     *    @author    Garbin
     *    @return    void
     */
    function add()
    {
		list($check, $loginResult) = TRUE;//parent::_checkLogin();
		if($check === FALSE)
		{
			$result = array(
				'status' => 'FAILED',
				'errorMsg' => $loginResult['errorMsg']
			);
		}
		else
		{
			$post = parent::_getPostData();

			$type = empty($post['type'])    ? 'goods' : trim($post['type']);
			$item_id = empty($post['item_id'])  ? 0 : intval($post['item_id']);
			$keyword = empty($post['keyword'])  ? '' : trim($post['keyword']);
			if ($item_id)
			{
				if ($type == 'goods')
				{
					$data = $this->_add_collect_goods($item_id, $keyword);
				}
				elseif ($type == 'store')
				{
					$data = $this->_add_collect_store($item_id, $keyword);
				}
				
				if($data['result'] == true)
				{
					$result = array(
						'status'=> 'SUCCESS',
						'title' => "",
						'retval'=> ""
					);
				}
				else
				{
					$result = array(
						'status' => 'FAILED',
						'errorMsg' => $data['msg']
					);
				}
			}
		}
		
		echo json_encode($result);
    }

    /**
     *    收藏商品
     *
     *    @author    Garbin
     *    @param     int    $goods_id
     *    @param     string $keyword
     *    @return    void
     */
    function _add_collect_goods($goods_id, $keyword)
    {
		$return = array('result' => false, 'msg' => '');
		
        /* 验证要收藏的商品是否存在 */
        $model_goods =& m('goods');
        $goods_info  = $model_goods->get($goods_id);

        if (empty($goods_info))
        {
			$return['msg'] = Lang::get('no_such_goods');
            return $return;
        }
		
        $model_user =& m('member');
        $model_user->createRelation('collect_goods', $this->visitor->get('user_id'), array(
            $goods_id   =>  array(
                'keyword'   =>  $keyword,
                'add_time'  =>  gmtime(),
            )
        ));

        /* 更新被收藏次数 */
        $model_goods->update_collect_count($goods_id);

        $goods_image = $goods_info['default_image'] ? $goods_info['default_image'] : Conf::get('default_goods_image');
        $goods_url  = SITE_URL . '/' . url('app=goods&id=' . $goods_id);
        $this->send_feed('goods_collected', array(
            'user_id'   => $this->visitor->get('user_id'),
            'user_name'   => $this->visitor->get('user_name'),
            'goods_url'   => $goods_url,
            'goods_name'   => $goods_info['goods_name'],
            'images'    => array(array(
                'url' => SITE_URL . '/' . $goods_image,
                'link' => $goods_url,
            )),
        ));

        /* 收藏成功 */
		$return = array('result' => true, 'msg' => Lang::get('collect_goods_ok'));
        return $return;
    }

    /**
     *    收藏店铺
     *
     *    @author    Garbin
     *    @param     int    $store_id
     *    @param     string $keyword
     *    @return    void
     */
    function _add_collect_store($store_id, $keyword)
    {
		$return = array('result' => false, 'msg' => '');
		
        /* 验证要收藏的店铺是否存在 */
        $model_store =& m('store');
        $store_info  = $model_store->get($store_id);
        if (empty($store_info))
        {
            /* 店铺不存在 */
			$return['msg'] = Lang::get('no_such_store');
            return $return;
        }
        $model_user =& m('member');
        $model_user->createRelation('collect_store', $this->visitor->get('user_id'), array(
            $store_id   =>  array(
                'keyword'   =>  $keyword,
                'add_time'  =>  gmtime(),
            )
        ));
        $this->send_feed('store_collected', array(
            'user_id'   => $this->visitor->get('user_id'),
            'user_name'   => $this->visitor->get('user_name'),
            'store_url'   => SITE_URL . '/' . url('app=store&id=' . $store_id),
            'store_name'   => $store_info['store_name'],
        ));

        /* 收藏成功 */
		$return = array('result' => true, 'msg' => Lang::get('collect_store_ok'));
        return $return;
    }
}

?>