<?php

class ArticleApp extends MallbaseApp
{

    var $_article_mod;
    var $_acategory_mod;
    var $_ACC; //系统文章cate_id数据
    var $_cate_ids; //当前分类及子孙分类cate_id
    function __construct()
    {
        $this->ArticleApp();
    }
    function ArticleApp()
    {
        parent::__construct();
        $this->_article_mod = &m('article');
        $this->_acategory_mod = &m('acategory');
		
        /* 获得系统分类cate_id数据 */
        $this->_ACC = $this->_acategory_mod->get_ACC();
    }
    function index()
    {
		if(!IS_AJAX)
		{
			/* 文章分类 */
        	$acategories = $this->_get_acategory(0);
			$this->assign('acategories', $acategories);
		
			$this->import_resource(array('script' => 'mobile/jquery.plugins/jquery.infinite.js'));
			$this->assign('infiniteParams', json_encode($_GET));
			
			$this->_get_curlocal_title('article');
        	$this->display('article.index.html');
		}
		else
		{
			$conditions = '';
			isset($_GET['keyword']) && $condition .= " AND title LIKE '%".html_script($_GET['keyword'])."%'";
			isset($_GET['code']) && isset($this->_ACC[trim($_GET['code'])]) && $condition .= " AND cate_id=".$this->_ACC[trim($_GET['code'])]; //如果有code
			
			/* 取得当前分类及子孙分类cate_id */
			$cate_id = intval($_GET['cate_id']); 
			$cate_ids = array();
			if ($cate_id > 0 && $cate_id != $this->_ACC[ACC_SYSTEM]) //排除系统内置分类
			{
				$cate_ids = $this->_acategory_mod->get_descendant($cate_id);
				$condition .= ' AND cate_id'.db_create_in($cate_ids);
			}
			
			$page = $this->_get_page(intval($_GET['pageper']));
			$articles = $this->_article_mod->find(array(
				'conditions'  => 'if_show=1 AND store_id=0 AND code = "" AND  cate_id !='. $this->_ACC[ACC_SYSTEM].$condition,
				'limit'   => $page['limit'],
				'order'   => 'add_time desc',
				'fields'  => 'title,add_time',
				'count'   => true
			));
			foreach($articles as $key => $article) {
				$articles[$key]['add_time'] = local_date('Y-m-d', $article['add_time']);
			}
			$page['item_count'] = $this->_article_mod->getCount();
			$this->_format_page($page);
			
			// 必须加 array_values() js遍历顺序才对
			$data = array('result' => array_values($articles), 'totalPage' => $page['page_count']);

			echo json_encode($data);
		}
    }
	
    function view()
    {
        $article_id = empty($_GET['article_id']) ? 0 : intval($_GET['article_id']);
        $cate_ids = array();
        if ($article_id>0)
        {
            $article = $this->_article_mod->get('article_id=' . $article_id . ' AND code = "" AND if_show=1 AND store_id=0');
            if (!$article)
            {
                $this->show_warning('no_such_article');
                return;
            }
            if ($article['link']){ //外链文章跳转
                header("HTTP/1.1 301 Moved Permanently");
                header('location:'.$article['link']);
                return;
            }
        }
        else
        {
            $this->show_warning('no_such_article');
            return;
        }
        $this->assign('article', $article);
		
		$this->_get_curlocal_title('article_view');
        $this->_config_seo('title', $article['title'] . ' - ' . Conf::get('site_title'));
        $this->display('article.view.html');
    }

    function system()
    {
        $code = empty($_GET['code']) ? '' : trim($_GET['code']);
        if (!$code)
        {
            $this->show_warning('no_such_article');
            return;
        }
        $article = $this->_article_mod->get("code='" . $code . "'");
        if (!$article)
        {
            $this->show_warning('no_such_article');
            return;
        }
        if ($article['link']){ //外链文章跳转
      		header("HTTP/1.1 301 Moved Permanently");
       		header('location:'.$article['link']);
      		return;
     	}

        $this->assign('article', $article);
		
		$this->_get_curlocal_title($article['title']);
        $this->_config_seo('title', $article['title'] . ' - ' . Conf::get('site_title'));
        $this->display('article.view.html');

    }
	
	function _get_acategory($cate_id)
    {
        $acategories = $this->_acategory_mod->get_list($cate_id);
        if ($acategories){
            unset($acategories[$this->_ACC[ACC_SYSTEM]]);
            return $acategories;
        }
        else
        {
            $parent = $this->_acategory_mod->get($cate_id);
            if (isset($parent['parent_id']))
            {
                return $this->_get_acategory($parent['parent_id']);
            }
        }
    }
}

?>
