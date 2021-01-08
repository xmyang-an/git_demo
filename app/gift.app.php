<?php

class GiftApp extends StorebaseApp
{
    function index()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
		$gift_mod = &m('gift');
        $gift = $gift_mod->get($id);
        if (!$gift)
        {
            $this->show_warning('gift_not_exist');
            return;
        }
		if(!$gift['if_show']) {
			$this->show_warning('gift_off');
			return;
		}
		
        $this->assign('goods', $gift);

        /* 店铺信息 */
        $this->set_store($gift['store_id']);
        $store = $this->get_store_data();
        $this->assign('store', $store);

        /* 当前位置 */
        $this->_curlocal(LANG::get('all_stores'), 'index.php?app=search&amp;act=store',
            $store['store_name'], 'index.php?app=store&amp;id=' . $store['store_id'],
            $gift['goods_name']
        );

        $this->_config_seo('title', $gift['goods_name'] . ' - ' . $store['store_name']);
        $this->display('gift.view.html');
    }

    
}

?>
