<?php
/* 商品咨询管理控制器 */
class My_commentApp extends StoreadminbaseApp
{
    var $ordergoods_mod;
    function __construct()
    {
        $this->My_commentApp();
    }
    function My_commentApp()
    {
        parent::__construct();
        $this->ordergoods_mod = & m('ordergoods');
    }
    function index()
    {
        $type = (isset($_GET['type']) && $_GET['type'] != '') ? trim($_GET['type']) : 'all_comment';
        $conditions = ' AND seller_id = '.$this->visitor->get('user_id');
        switch ($type)
        {
            case 'all_comment':
                $conditions .= ' ';
                break;
            case 'to_reply_comment' :
                $conditions .= ' AND reply_content = " " ';
                break;
            case 'replied_comment' :
                $conditions .= ' AND reply_content != " " ';
                break;
        };
        $page = $this->_get_page(8);
        $my_comment_data = $this->ordergoods_mod->find(array(
            'join' => 'belongs_to_order',
            'conditions' => '1=1 '.$conditions,
            'count' => true,
            'limit' => $page['limit'],
            'order' => 'evaluation_time desc',
			'fields' => 'rec_id,order.order_id, buyer_id, buyer_name, seller_id, seller_name, goods_id, goods_name,evaluation_time,comment,reply_content,reply_time',
        ));
        $page['item_count'] = $this->ordergoods_mod->getCount();
        $this->_format_page($page);
                /* 当前位置 */
        $this->_curlocal(LANG::get('my_comment'), 'index.php?app=my_comment',
                         LANG::get('my_comment_list'));

        /* 当前用户中心菜单 */
        $this->_curitem('my_comment');

        /* 当前所处子菜单 */
        $this->_curmenu('my_comment_list');
        $this->assign('_curmenu',$type);
        $this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'dialog/dialog.js',
                    'attr' => 'id="dialog_js"',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/smoothness/jquery.ui.css',
        ));
        $this->assign('page_info',$page);
        $this->assign('my_comment_data',$my_comment_data);
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('my_comment'));
        $this->display('my_comment.index.html');
    }
    function reply()
    {
        if (!IS_POST)
        {
			$rec_id = (isset($_GET['rec_id']) && $_GET['rec_id'] !='') ? intval($_GET['rec_id']) : 0;
            $my_comment_data = $this->ordergoods_mod->get(array(
                'join' => 'belongs_to_order',
                'conditions' => 'rec_id = '.$rec_id,
            ));
            if ($my_comment_data['reply_comment'] != '')
            {
                echo Lang::get('already_replied');
                return;
            }
                    /* 当前位置 */
            $this->_curlocal(LANG::get('my_qa'), 'index.php?app=my_comment',
                             LANG::get('reply'));

            /* 当前用户中心菜单 */
            $this->_curitem('my_comment');

            /* 当前所处子菜单 */
            $this->_curmenu('reply');
            $this->assign('_curmenu','reply');
            $this->assign('page_info',$page);
            $this->assign('my_comment_data',$my_comment_data);
            $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('reply'));
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->display('my_comment.form.html');
        }
        else
        {
			$rec_id = (isset($_POST['rec_id']) && $_POST['rec_id'] !='') ? intval($_POST['rec_id']) : 0;
            $content = (isset($_POST['content']) && $_POST['content'] != '') ? html_script(trim($_POST['content'])) : '';
            if (!$rec_id)
            {
                $this->pop_warning('Hacking Attempt');
                return;
            }
            if ($content == '')
            {
                $this->pop_warning('content_not_null');
                return;
            }
            if ($this->ordergoods_mod->edit($rec_id,array('reply_content'=>$content,'reply_time'=>gmtime())))
            {                    
               $this->pop_warning('ok', 'my_comment_reply');
            }
            else
            {
                $this->pop_warning('reply_failed');
                return;
           }
        }
    }
    
    //三级菜单:
    function _get_member_submenu()
    {
        $array = array(
            array(
                'name' => 'all_comment',
                'url' => 'index.php?app=my_comment&amp;type=all_comment',
            ),
            array(
                'name' => 'to_reply_comment',
                'url' => 'index.php?app=my_comment&amp;type=to_reply_comment',
            ),
            array(
                'name' => 'replied_comment',
                'url' => 'index.php?app=my_comment&amp;type=replied_comment',
            ),
        );
        if (ACT == 'index')
        {
            unset($array[3]);
        }
        return $array;
    }
}

?>