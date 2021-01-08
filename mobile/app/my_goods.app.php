<?php

define('THUMB_WIDTH', 300);
define('THUMB_HEIGHT', 300);
define('THUMB_QUALITY', 85);

/* 品牌申请状态 */
define('BRAND_PASSED', 1);
define('BRAND_REFUSE', 0);

/* 商品管理控制器 */
class My_goodsApp extends StoreadminbaseApp
{
    var $_goods_mod;
    var $_spec_mod;
    var $_image_mod;
    var $_uploadedfile_mod;
    var $_store_id;
    var $_brand_mod;
    var $_last_update_id;
	var $_limitbuy_mod;
	var $_integral_mod;

    /* 构造函数 */
    function __construct()
    {
         $this->My_goodsApp();
    }

    function My_goodsApp()
    {
        parent::__construct();

        $this->_store_id  = intval($this->visitor->get('manage_store'));
        $this->_goods_mod =& bm('goods', array('_store_id' => $this->_store_id));
        $this->_spec_mod  =& m('goodsspec');
        $this->_image_mod =& m('goodsimage');
        $this->_uploadedfile_mod =& m('uploadedfile');
        $this->_brand_mod =& m('brand');
		$this->_limitbuy_mod =&m('limitbuy');
		$this->_integral_mod = &m('integral');
    }

    function index()
    {
		if(!IS_AJAX)
		{
			$this->import_resource('mobile/jquery.plugins/jquery.infinite.js');
			$this->assign('infiniteParams', json_encode($_GET));
			
			$this->_get_curlocal_title('my_goods');
			$this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('my_goods'));

			$store_mod  =& m('store');
			$store = $store_mod->get_info($this->_store_id); 
			$this->assign('store', $store);
			/* 取得店铺商品分类 */
        	$this->assign('sgcategories', $this->_get_sgcategory_options());
			$this->display('my_goods.index.html');
		}
		else
		{
			$conditions = $this->_get_conditions();
			$page = $this->_get_page(intval($_GET['pageper']));
			$page_nolimit = array();
			$goods_list = $this->_get_goods($conditions, $page);

			foreach ($goods_list as $key => $goods)
			{
				$goods_list[$key]['cate_name'] = $this->_goods_mod->format_cate_name($goods['cate_name']);
			}
			$this->assign('goods_list', $goods_list);

			$data = array('result' => array_values($goods_list), 'totalPage' => $page['page_count']);
			echo json_encode($data);
		}
    }
	
	/* 获取店铺的运费模板 - psmb */ 
	function _get_delivery_template()
	{
		$delivery_mod = &m('delivery_template');
		$delivery = $delivery_mod->find(array(
			'conditions'=>'store_id='.$this->_store_id,
			
		));
		
		return $delivery;
	}

    function _get_goods($conditions, &$page)
    {
        if (intval($_GET['sgcate_id']) > 0)
        {
            $cate_mod =& bm('gcategory', array('_store_id' => $this->_store_id));
            $cate_ids = $cate_mod->get_descendant_ids(intval($_GET['sgcate_id']));
        }
        else
        {
            $cate_ids = 0;
        }

        // 标识有没有过滤条件
        if ($conditions != '1 = 1' || !empty($_GET['sgcate_id']))
        {
            $this->assign('filtered', 1);
        }

        //更新排序
        if (isset($_GET['sort']) && isset($_GET['order']))
        {
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc')))
            {
                $sort  = 'goods_id';
                $order = 'desc';
            }
        }
        else
        {
            $sort  = 'goods_id';
            $order = 'desc';
        }
        
        if ($page)
        {
            $limit = $page['limit'];
            $count = true;
        }
        else
        {
            $limit = '';
            $count = false;
        }

        /* 取得商品列表 */

        $goods_list = $this->_goods_mod->get_list(array(
            'conditions' => $conditions,
            'count' => $count,
            'order' => "$sort $order",
            'limit' => $limit,
        ), $cate_ids);
		
		$page['item_count'] = $this->_goods_mod->getCount();   //获取统计的数据
		$this->_format_page($page);
		
        return $goods_list;
    }
    
    function _get_conditions()
    {
        /* 搜索条件 */
        $conditions = "1 = 1";
        if (trim($_GET['keyword']))
        {
            $str = "LIKE '%" . trim($_GET['keyword']) . "%'";
            $conditions .= " AND (goods_name {$str} OR brand {$str} OR cate_name {$str})";
        }
        if ($_GET['character'])
        {
            switch ($_GET['character'])
            {
                case 'show':
                    $conditions .= " AND if_show = 1";
                    break;
                case 'hide':
                    $conditions .= " AND if_show = 0";
                    break;
                case 'closed':
                    $conditions .= " AND closed = 1";
                    break;
                case 'recommended':
                    $conditions .= " AND g.recommended = 1";
                    break;
            }
        }
        
        return $conditions;
    }

   
    /* 检查商品分类：添加、编辑商品表单验证时用到 */
    function check_mgcate()
    {
        $cate_id = isset($_GET['cate_id']) ? intval($_GET['cate_id']) : 0;

        echo ecm_json_encode($this->_check_mgcate($cate_id));
    }


    /**
     * 检查商品分类（必选，且是叶子结点）
     *
     * @param   int     $cate_id    商品分类id
     * @return  bool
     */
    function _check_mgcate($cate_id)
    {
        if ($cate_id > 0)
        {
            $gcategory_mod =& bm('gcategory');
            $info = $gcategory_mod->get_info($cate_id);
            if ($info && $info['if_show'] && $gcategory_mod->is_leaf($cate_id))
            {
                return true;
            }
        }

        return false;
    }
	
	function ajaxPropList()
	{
		if(IS_AJAX)
		{
			$cate_id = intval($_GET['cate_id']);
			if($cate_id)
			{
				$goods_id = intval($_GET['id']);
				$propList = $this->_get_gcategory_props($cate_id, $goods_id);
				
				$this->json_result($propList);
			}
		}
	}
	
	/* 获取分类的属性 */
	function _get_gcategory_props($cate_id, $goods_id = 0)
	{
		$prop_list = $gpvs = $prop_value_list = array();
		
		//  初始化商品属性
		$cate_pvs_mod = &m('cate_pvs');
		$goods_pvs_mod = &m('goods_pvs');
		$props_mod = &m('props');
		$prop_value_mod = &m('prop_value');
		$cate_pvs = $cate_pvs_mod->get($cate_id);
		
		if($goods_id) {
			$goods_pvs = $goods_pvs_mod->get($goods_id);
			
			// 取出该商品的所有属性  $cpvs >= $gpvs,用来设置 checked=checked
			$gpvs = explode(';',$goods_pvs['pvs']);
		}
		
		if(!empty($cate_pvs['pvs']))
		{
			$pv_arr = explode(';',$cate_pvs['pvs']);
			foreach($pv_arr as $key=>$pv)
			{
				if(!$pv || !$this->_check_props_item($pv))
				{
					continue;
				}
				$item = explode(':',$pv);
				$props = $props_mod->get('status=1 and pid='.$item[0]);
				$prop_list[$item[0]] = $props;

				$prop_value = $prop_value_mod->get('status=1 and vid='.$item[1]);
				
				if(in_array($pv,$gpvs)) {
					$prop_value['selected'] = 1;
				}
				else{
					$prop_value['selected'] = 0;
				}
				$prop_value_list[$item[0]][] = $prop_value;
				
				$prop_list[$item[0]]['value'] = $prop_value_list[$item[0]];
			}
		}
		// 按sort_order字段排序
		$prop_list = $props_mod->prop_sort($prop_list);
		
		return $prop_list;
	}
	
	/* 检验属性名和属性值是否存在 */
	function _check_props_item($pv)
	{
		$result = true;
		$props_mod = &m('props');
		$prop_value_mod = &m('prop_value');
		
		$item = explode(':',$pv);
		if($props_mod->get(array('conditions'=>'pid='.$item[0].' AND status=1','fields'=>'pid'))) 
		{		   
			// 如果属性名存在，则检查该属性名下的当前属性值是否存在
			if(!$prop_value_mod->get(array('conditions'=>'pid='.$item[0].' AND vid='.$item[1].' and status=1','fields'=>'vid')))
			{
				$result = false;
			}
		} 
		else {
			$result = false;
		}
		
		return $result;
	}
	
	function _format_goods_cate_name($cate_id)
	{
		$string = '';
		$gcategory_mod =& bm('gcategory', array('_store_id' => 0));
		$gcategories = $gcategory_mod->get_ancestor($cate_id);
		$i = 0;
		foreach($gcategories as $key=>$val) {
			$i++;
			if($i != count($gcategories)) {
				$string .= $val['cate_name'] . "\t";
			}
			else $string .= $val['cate_name'];
		}

		return $string;
	}
	
	function _get_first_cateline($cate_id = 0)
	{
		$gcategory_mod =& bm('gcategory', array('_store_id' => 0));
		
		if($cate_id) {
			$conditions = 'parent_id='.$cate_id;
		} else $conditions = 'parent_id=0';
		
		$first = $gcategory_mod->get(array('conditions' => $conditions, 'order' => 'cate_id ASC', 'fields' => 'cate_id'));
		
		if($first) {
			$cate_id = $first['cate_id'];
			return $this->_get_first_cateline($first['cate_id']);
		}
		return $cate_id;
	}

    function add()
    {
        /* 检测支付方式、配送方式、商品数量等 */
        if (!$this->_addible()) {
            return;
        }
		
		$cate_id = intval($_GET['cate_id']);
		
		// 移动端兼容处理（如果没有选择分类，则默认选择第一级分类）
		if(!$cate_id) {
			$cate_id = $this->_get_first_cateline($cate_id);
		}
		
		/* 选择的分类不是最末级， 返回选择分类页面 */
		$gcategory_mod =& bm('gcategory', array('_store_id' => 0));
		if($gcategory_mod->get_children($cate_id, true)) {
			$this->show_warning('select_leaf_category');
			return;
		}

        if (!IS_POST)
        {			
             /* 添加传给iframe空的id,belong*/
			$this->assign("id", 0);
			$this->assign("belong", BELONG_GOODS);
			 
			$goods = $this->_get_goods_info(0);
			
			/* 类目数据 */
			$goods['cate_id'] =  $cate_id;
			$goods['cate_name'] = $this->_format_goods_cate_name($cate_id);		
			
			$this->assign('goods', $goods);
			
			/* 取得该商品分类的所有父级分类 */
			$this->assign('publish_gcategory', $gcategory_mod->get_ancestor($cate_id, true));
			
			/* 取得品牌列表 */
			$this->assign('brand_list', $this->_brand_mod->find(array('conditions'=>'(store_id = ' . $this->_store_id . ' OR store_id=0) AND if_show = 1')));
			
			
             /* 取得游离状的图片 */
             $goods_images =array();
             $desc_images =array();
             $uploadfiles = $this->_uploadedfile_mod->find(array(
                 'join' => 'belongs_to_goodsimage',
                 'conditions' => "belong=".BELONG_GOODS." AND item_id=0 AND store_id=".$this->_store_id,
                 'order' => 'add_time ASC'
             ));
             foreach ($uploadfiles as $key => $uploadfile)
             {
                 if ($uploadfile['goods_id'] == null)
                 {
                     $desc_images[$key] = $uploadfile;
                 }
                 else
                 {
                     $goods_images[$key] = $uploadfile;
                 }
             }

             $this->assign('goods_images', $goods_images);
             $this->assign('desc_images', $desc_images);

			 $this->_get_curlocal_title('goods_add');
             $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('goods_add'));

			/* 取得商品分类 */
            $this->assign('mgcategories', $this->_get_mgcategory_options(0)); // 商城分类第一级
             $this->assign('sgcategories', $this->_get_sgcategory_list());  // 店铺分类
			
			$this->import_resource(array(
                'script' => array(
					array(
                         'path' => 'mobile/my_goods.js',
                         'attr' => 'charset="utf-8"',
                    ),
					array(
                         'path' => 'mobile/jquery.form.js',
                         'attr' => 'charset="utf-8"',
                    ),
					 array(
						'path' => 'webuploader/webuploader.js',
						'attr' => 'charset="utf-8"',
					),
					array(
                        'path' => 'webuploader/webuploader.compressupload.js',
						'attr' => 'charset="utf-8"',
                    ),
					array(
                        'path' => 'mobile/jquery.plugins/artEditor.js',
						'attr' => 'charset="utf-8"',
                    )
                )
            ));
             
			 
	      	$this->assign('integral_enabled',$this->_integral_mod->_get_sys_setting('integral_enabled')); // by psmb 判断系统是否开启积分功
             

			/* 赋值运费模板 - psmb */ 
			$this->assign('deliveries', $this->_get_delivery_template());
			 
			 /* 支持加价购列表 */
			$growbuy_mod = &bm('promotool_setting', array('_store_id' => $this->_store_id, '_appid' => 'growbuy'));
			if($growbuy_mod->checkAvailable()){
				$growbuy_list = $growbuy_mod->getGrowBuyList();
				$this->assign('growbuy_list', $growbuy_list);
			}
			
			/* 手机专享 */
			$exclusive_mod = &bm('promotool_setting', array('_store_id' => $this->_store_id, '_appid' => 'exclusive'));
			if($exclusive_mod->checkAvailable()){
				$exclusive = $exclusive_mod->getExclusive();
				$this->assign('exclusive', $exclusive);
			}
			
			$this->display('my_goods.form.html');
        }
        else
        {
            /* 取得数据 */
            $data = $this->_get_post_data(0);
			
            /* 检查数据 */
            if (!$this->_check_post_data($data, 0))
            {
                $error = current($this->get_error());
                $this->json_error($error['msg']);
                return;
            }
            /* 保存数据 */
            if (!$this->_save_post_data($data, 0))
            {
               	$error = current($this->get_error());
                $this->json_error($error['msg']);
                return;
            }
            $goods_info = $this->_get_goods_info($this->_last_update_id);
            if ($goods_info['if_show'])
            {
                $goods_url = SITE_URL . '/' . url('app=goods&id=' . $goods_info['goods_id']);
                $feed_images = array();
                $feed_images[] = array(
                    'url'   => SITE_URL . '/' . $goods_info['default_image'],
                    'link'  => $goods_url,
                );
                $this->send_feed('goods_created', array(
                    'user_id' => $this->visitor->get('user_id'),
                    'user_name' => $this->visitor->get('user_name'),
                    'goods_url' => $goods_url,
                    'goods_name' => $goods_info['goods_name'],
                    'images' => $feed_images
                ));
            }			

			$this->json_result(array('ret_url' => 'index.php?app=my_goods'), 'add_ok');
        }
    }
	
	/* 保存商品属性 */
	function _save_goods_props($post, $goods_id)
	{
		if(!empty($post) && is_array($post))
		{
			$goods_pvs_mod = &m('goods_pvs');
				
			//构造新的数组和去空值
			$props = array();
			foreach($post as $key=>$val)
			{
				if(empty($val)) continue;
				
				foreach($val as $k=>$v){
					if(!empty($v)) $props[] = $v; 
				}
			}
			
			// 生成如 6:1;20:4 的字符串
			$prop_str = implode(';',$props);
			
			$goods_pvs = array(
				  'goods_id'=> $goods_id,
				  'pvs'     => $prop_str
			);
			
			if($goods_pvs_mod->get($goods_id)) {
				$goods_pvs_mod->edit($goods_id, $goods_pvs);
			}
			else {
				$goods_pvs_mod->add($goods_pvs);
			}
		}
	}

    function edit()
    {
        import('image.func');
        import('uploader.lib');
		
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		
		$gcategory_mod =& bm('gcategory', array('_store_id' => 0));
		
        if (!IS_POST)
        {
            /* 传给iframe id */
            $this->assign('id', $id);
            $this->assign('belong', BELONG_GOODS);
            if(!$id || !($goods = $this->_get_goods_info($id)))
            {
                $this->show_warning('no_such_goods');
                return;
            }
            $goods['tags'] = trim($goods['tags'], ',');
			
			/* 保证编辑类目后数据完整 */
			$cate_id = empty($_GET['cate_id']) ? $goods['cate_id'] : intval($_GET['cate_id']);
			$goods['cate_id'] =  $cate_id;
			$goods['cate_name'] = $this->_format_goods_cate_name($cate_id);
			
            $this->assign('goods', $goods);
			
			/* 取得该商品分类的所有父级分类 */
			$this->assign('publish_gcategory', $gcategory_mod->get_ancestor($goods['cate_id'], true));
			
			/* 取得品牌列表 */
			$brand_list = $this->_brand_mod->find(array('conditions'=>'(store_id = ' . $this->_store_id . ' OR store_id=0) AND if_show = 1'));
			$this->assign('brand_list', $brand_list);
			
            /* 取到商品关联的图片 */
            $uploadedfiles = $this->_uploadedfile_mod->find(array(
                'fields' => "f.*,goods_image.*",
                'conditions' => "store_id=".$this->_store_id." AND belong=".BELONG_GOODS." AND item_id=".$id,
                'join'       => 'belongs_to_goodsimage',
                'order' => 'add_time DESC'
            ));

            $default_goods_images = array(); // 默认商品图片
            $other_goods_images = array(); // 其他商品图片
            $desc_images = array(); // 描述图片

            foreach ($uploadedfiles as $key => $uploadedfile)
            {
                if ($uploadedfile['goods_id'] == null)
                {
                    $desc_images[$key] = $uploadedfile;
                }
                else
                {
                    if (!empty($goods['default_image']) && ($uploadedfile['thumbnail'] == $goods['default_image']))
                    {
                        $default_goods_images[$key] = $uploadedfile;
                    }
                    else
                    {
                        $other_goods_images[$key] = $uploadedfile;
                    }
                }
            }

            $this->assign('goods_images', array_merge($default_goods_images, $other_goods_images));
            $this->assign('desc_images', $desc_images);

            /* 取得商品分类 */
            $this->assign('mgcategories', $this->_get_mgcategory_options(0)); // 商城分类第一级
            $this->assign('sgcategories', $this->_get_sgcategory_list($goods['_scates']));  // 店铺分类

            $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('edit_goods'));

            $this->import_resource(array(
                'script' => array(
					array(
                         'path' => 'mobile/my_goods.js',
                         'attr' => 'charset="utf-8"',
                    ),
					array(
                         'path' => 'mobile/jquery.form.js',
                         'attr' => 'charset="utf-8"',
                    ),
					 array(
						'path' => 'webuploader/webuploader.js',
						'attr' => 'charset="utf-8"',
					),
					array(
                        'path' => 'webuploader/webuploader.compressupload.js',
						'attr' => 'charset="utf-8"',
                    ),
					array(
                        'path' => 'mobile/jquery.plugins/artEditor.js',
						'attr' => 'charset="utf-8"',
                    )
                )
            ));
            
            $this->assign('integral_enabled',$this->_integral_mod->_get_sys_setting('integral_enabled')); // by psmb 判断系统是否开启积分功

			
			/* 赋值运费模板 - psmb */
			$this->assign('deliveries', $this->_get_delivery_template());
			
			/* 支持加价购列表 */
			$growbuy_mod = &bm('promotool_setting', array('_store_id' => $this->_store_id, '_appid' => 'growbuy'));
			if($growbuy_mod->checkAvailable()){
				$growbuy_list = $growbuy_mod->getGrowBuyList($id, TRUE, FALSE);
				$this->assign('growbuy_list', $growbuy_list);
			}
			
			/* 手机专享 */
			$exclusive_mod = &bm('promotool_setting', array('_store_id' => $this->_store_id, '_appid' => 'exclusive'));
			if($exclusive_mod->checkAvailable()){
				$exclusive = $exclusive_mod->getExclusive($id);
				$this->assign('exclusive', $exclusive);
			}
			
			//$this->_set_nav_exclusive_handle('<a style="width:auto;" href="javascript:$(\'form\').submit();" class="float-right add filter">保存</a>');
			$this->_get_curlocal_title('edit_goods');
			
            $this->display('my_goods.form.html');
        }
        else
        {
            /* 取得数据 */
            $data = $this->_get_post_data($id);

            /* 检查数据 */
            if (!$this->_check_post_data($data, $id))
            {
				$error = current($this->get_error());
                $this->json_error($error['msg']);
                return;
            }
            /* 保存商品 */
            if (!$this->_save_post_data($data, $id))
            {
				$error = current($this->get_error());
                $this->json_error($error['msg']);
                return;
            }
			
			/* 更新详情页缓存（如果编辑了商品促销信息，那么不更新缓存，商品详情页的一些促销信息没有及时变更）*/
			$cache_server =& cache_server();
        	$cache_server->delete('page_of_goods_' . $id);
						
			$ret_page = isset($_GET['ret_page']) ? intval($_GET['ret_page']) : 1;
            
			$this->json_result(array('ret_url' => 'index.php?app=my_goods&page='.$ret_page), 'edit_ok');
        }
    }

   function spec_edit()
   {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!IS_POST)
        {
            $goods_spec = $this->_goods_mod->findAll(array(
                'fields' => "this.goods_name,this.goods_id,this.spec_name_1,this.spec_name_2",
                'conditions' => "goods_id = $id",
                'include' => array('has_goodsspec' => array('order'=>'spec_id')),
            ));

            header('Content-Type:text/html;charset=' . CHARSET);
            $this->assign('goods', current($goods_spec));
            $this->display("spec_edit.html");
        }
        else
        {
            $data = $this->save_spec($_POST);
            if (empty($data))
            {
                $this->pop_warning('not_data');
            }
            $default_spec = array(); // 更新商品中默认规格的信息
            foreach ($data as $key => $val)
            {
                if (empty($default_spec))
                {
                    $default_spec = array('price' => $val['price']);
                }
                $this->_spec_mod->edit($key, $val);
            }
            $this->_goods_mod->edit($id, $default_spec);
            $this->pop_warning('ok', 'my_goods_spec_edit');
        }
   }

   function save_spec($spec)
   {
        $data = array();
        if (empty($spec['price']) || empty($spec['stock']))
        {
            return $data;
        }
        foreach ($spec['price'] as $key => $val)
        {
            $data[$key]['price'] = $this->_filter_price($val);
        }
        foreach ($spec['stock'] as $key => $val)
        {
            $data[$key]['stock'] = intval($val);
        }
        return $data;
   }

    function drop()
    {
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$id)
        {
            $this->show_warning('no_goods_to_drop');
            return;
        }

        $ids = explode(',', $id);
        $this->_goods_mod->drop_data($ids);
        $rows = $this->_goods_mod->drop($ids);
        if ($this->_goods_mod->has_error())
        {
            $this->show_warning($this->_goods_mod->get_error());
            return;
        }

        $this->show_message('drop_ok');
    }

    function drop_image()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        $uploadedfile = $this->_uploadedfile_mod->get(array(
                  'conditions' => "f.file_id = '$id' AND f.store_id = '{$this->_store_id}'",
                  'join' => 'belongs_to_goodsimage',
                  'fields' => 'goods_image.image_url, goods_image.thumbnail, goods_image.image_id, f.file_id',
        ));
        if ($uploadedfile)
        {
            $this->_uploadedfile_mod->drop($id);
            if ($this->_image_mod->drop($uploadedfile['image_id']))
            {
                // 删除文件
                if (file_exists(ROOT_PATH . '/' . $uploadedfile['image_url']))
                {
                       @unlink(ROOT_PATH . '/' . $uploadedfile['image_url']);
                }
                if (file_exists(ROOT_PATH . '/' . $uploadedfile['thumbnail']))
                {
                       @unlink(ROOT_PATH . '/' . $uploadedfile['thumbnail']);
                }

                $this->json_result($id);
                return;
            }
            $this->json_result($id);
            return;
        }
        $this->json_error(Lang::get('no_image_droped'));
    }
    
    function drop_spec_image()
	{
		$image_name = basename($_GET['file_name']);
		if (file_exists(ROOT_PATH . '/data/files/store_' . $this->_store_id . '/spec_images/' . $image_name))
		{
		   @unlink(ROOT_PATH . '/data/files/store_' . $this->_store_id . '/spec_images/' . $image_name);
		   $this->json_result('ok');
		   return;
		}
		$this->json_error(Lang::get('no_image_droped'));
	}

    function _get_member_submenu()
    {
		$menus = array(
			array(
				'name' => 'goods_list',
				'url'  => 'index.php?app=my_goods',
			 ),
			array(
				'name' => 'goods_add',
				'url'  => 'index.php?app=my_goods&amp;act=add',
			),
			array(
				'name' => 'import_taobao',
				'url'  => 'index.php?app=my_goods&amp;act=import_taobao',
			),
			array(
				'name' => 'brand_apply_list',
				'url' => 'index.php?app=my_goods&amp;act=brand_list'
			),
		); 
        
        if (ACT == 'batch_edit')
        {
            $menus[] = array(
                'name' => 'batch_edit',
                'url'  => '',
            );
        }
        elseif (ACT == 'edit')
        {
            $menus[] = array(
                'name' => 'edit_goods',
                'url'  => '',
            );
        }
        
        return $menus;
    }

    /* 构造并返回树 */
    function &_tree($gcategories)
    {
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');
        return $tree;
    }

    /* 取得本店所有商品分类 */
    function _get_sgcategory_options()
    {
        $mod =& bm('gcategory', array('_store_id' => $this->_store_id));
        $gcategories = $mod->get_list();
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');
        return $tree->getOptions();
    }
	
	/* 取得本店所有商品分类 */
    function _get_sgcategory_list($sgcate = array())
    {
		$sg_ids = array();
		if($sgcate) {
			foreach($sgcate as $k=>$v)
			{
				$sg_ids[] = $v['cate_id'];
			}
		}
        $mod =& bm('gcategory', array('_store_id' => $this->_store_id));
        $gcategories = $mod->get_list(0);
        foreach($gcategories as $key=>$val)
		{
			if(in_array($val['cate_id'], $sg_ids)) $gcategories[$key]['selected'] = 1;
			
			$children = $mod->get_list($val['cate_id']);
			
			foreach($children as $child_key => $child_value) {
				if(in_array($child_value['cate_id'], $sg_ids)) {
					$children[$child_key]['selected'] = 1;
				}
			}
			$gcategories[$key]['children'] = $children;
		}
		return $gcategories;
    }

    /* 取得商城商品分类，指定parent_id */
    function _get_mgcategory_options($parent_id = 0)
    {
        $res = array();
        $mod =& bm('gcategory', array('_store_id' => 0));
        $gcategories = $mod->get_list($parent_id, true);
        foreach ($gcategories as $gcategory)
        {
			$res[$gcategory['cate_id']] = $gcategory['cate_name'];
        }
        return $res;
    }

    /**
     * 上传商品图片
     *
     * @param int $goods_id
     * @return bool
     */
    function _upload_image($goods_id)
    {
        import('image.func');
        import('uploader.lib');
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE);
        $uploader->allowed_size(SIZE_GOODS_IMAGE); // 400KB

        /* 取得剩余空间（单位：字节），false表示不限制 */
        $store_mod  =& m('store');
        $settings      = $store_mod->get_settings($this->_store_id);
        $upload_mod =& m('uploadedfile');
        $remain        = $settings['space_limit'] > 0 ? $settings['space_limit'] * 1024 * 1024 - $upload_mod->get_file_size($this->_store_id) : false;

        $files = $_FILES['new_file'];
        foreach ($files['error'] as $key => $error)
        {
            if ($error == UPLOAD_ERR_OK)
            {
                /* 处理文件上传 */
                $file = array(
                    'name'            => $files['name'][$key],
                    'type'            => $files['type'][$key],
                    'tmp_name'  => $files['tmp_name'][$key],
                    'size'            => $files['size'][$key],
                    'error'        => $files['error'][$key]
                );
                $uploader->addFile($file);
                if (!$uploader->file_info())
                {
                    $this->_error($uploader->get_error());
                    return false;
                }

                /* 判断能否上传 */
                if ($remain !== false)
                {
                    if ($remain < $file['size'])
                    {
                        $this->_error('space_limit_arrived');
                        return false;
                    }
                    else
                    {
                        $remain -= $file['size'];
                    }
                }

                $uploader->root_dir(ROOT_PATH);
                $dirname      = 'data/files/store_' . $this->_store_id . '/goods_' . (time() % 200);
                $filename  = $uploader->random_filename();
                $file_path = $uploader->save($dirname, $filename);
                $thumbnail = dirname($file_path) . '/small_' . basename($file_path);
                make_thumb(ROOT_PATH . '/' . $file_path, ROOT_PATH . '/' . $thumbnail, THUMB_WIDTH, THUMB_HEIGHT, THUMB_QUALITY);

                /* 处理文件入库 */
                $data = array(
                    'store_id'  => $this->_store_id,
                    'file_type' => $file['type'],
                    'file_size' => $file['size'],
                    'file_name' => $file['name'],
                    'file_path' => $file_path,
                    'add_time'  => gmtime(),
                );
                $uf_mod =& m('uploadedfile');
                $file_id = $uf_mod->add($data);
                if (!$file_id)
                {
                    $this->_error($uf_mod->get_error());
                    return false;
                }

                /* 处理商品图片入库 */
                $data = array(
                    'goods_id'      => $goods_id,
                    'image_url'  => $file_path,
                    'thumbnail'  => $thumbnail,
                    'sort_order' => 255,
                    'file_id'       => $file_id,
                );
                if (!$this->_image_mod->add($data))
                {
                    $this->_error($this->_image_mod->get_error());
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 检测店铺是否能添加商品
     *
     */
    function _addible()
    {
        $payment_mod =& m('payment');
        $payments = $payment_mod->get_enabled($this->_store_id);
        if (empty($payments))
        {
            $this->show_warning('please_install_payment', 'go_payment', 'index.php?app=my_payment');
            return false;
        }
		
		$delivery_mod = &m('delivery_template');
		$delivery = $delivery_mod->find("store_id='{$this->_store_id}'");
		if(empty($delivery))
		{
			$this->show_warning('add_delivery_template_before_send_goods', 'add_delivery_template', 'index.php?app=my_delivery');
            		return false;
		}

        /* 判断商品数是否已超过限制 */
        $store_mod =& m('store');
        $settings = $store_mod->get_settings($this->_store_id);
        if ($settings['goods_limit'] > 0)
        {
                  $goods_count = $this->_goods_mod->get_count();
                  if ($goods_count >= $settings['goods_limit'])
                  {
                         $this->show_warning('goods_limit_arrived');
                         return false;
                  }
        }
        return true;
    }
    /**
     * 保存远程图片
     */
    function _add_remote_image($goods_id)
    {
        foreach ($_POST['new_url'] as $image_url)
        {
            if ($image_url && $image_url != 'http://')
            {
                $data = array(
                    'goods_id' => $goods_id,
                    'image_url' => $image_url,
                    'thumbnail' => $image_url, // 远程图片暂时没有小图
                    'sort_order' => 255,
                    'file_id' => 0,
                );
                if (!$this->_image_mod->add($data))
                {
                    $this->_error($this->_image_mod->get_error());
                    return false;
                }
            }
        }

        return true;
    }
    /**
     * 编辑图片
     */
    function _edit_image($goods_id)
    {
        if (isset($_POST['old_order']))
        {
            foreach ($_POST['old_order'] as $image_id => $sort_order)
            {
                $data = array('sort_order' => $sort_order);
                if (isset($_POST['old_url'][$image_id]))
                {
                    $data['image_url'] = $_POST['old_url'][$image_id];
                }
                $this->_image_mod->edit("image_id = '$image_id' AND goods_id = '$goods_id'", $data);
            }
        }

        return true;
    }

    /**
     * 取得商品信息
     */
    function _get_goods_info($id = 0)
    {
        $default_goods_image = Conf::get('default_goods_image'); // 商城默认商品图片
        if ($id > 0)
        {
            $goods_info = $this->_goods_mod->get_info($id);
            if ($goods_info === false)
            {
                return false;
            }
            $goods_info['default_goods_image'] = $default_goods_image;
            if (empty($goods_info['default_image']))
            {
                   $goods_info['default_image'] = $default_goods_image;
            }
        }
        else
        {
            $goods_info = array(
                'cate_id' => 0,
                'if_show' => 1,
                'recommended' => 1,
                'price' => 1,
                'stock' => 1,
                'spec_qty' => 0,
                'spec_name_1' => Lang::get('color'),
                'spec_name_2' => Lang::get('size'),
                'default_goods_image' => $default_goods_image,
            );
        }
		
		if($goods_info['_specs']) {
			$goods_info['_specs'] = array_values($goods_info['_specs']);
		}
        $goods_info['spec_json'] = ecm_json_encode(array(
            'spec_qty' => $goods_info['spec_qty'],
            'spec_name_1' => isset($goods_info['spec_name_1']) ? $goods_info['spec_name_1'] : '',
            'spec_name_2' => isset($goods_info['spec_name_2']) ? $goods_info['spec_name_2'] : '',
            'specs' => $goods_info['_specs'],
        ));
		/* 如果开启积分功能，则：读取商品积分设置 */
		if($this->_integral_mod->_get_sys_setting('integral_enabled'))
		{
			$goods_integral_mod =& m('goods_integral');
			$goods_integral = $goods_integral_mod->get($id);
			$goods_info += $goods_integral ? $goods_integral : array();
		}
	
        return $goods_info;
    }

    /**
     * 提交的数据
     */
    function _get_post_data($id = 0)
    {
        $goods = array(
            'goods_name'       => $_POST['goods_name'],
            'description'      => html_script($_POST['description']),
            'cate_id'             => intval($_POST['cate_id']),
            'cate_name'        => $_POST['cate_name'],
            'brand'                  => $_POST['brand'],
            'if_show'             => $_POST['if_show'],
            'last_update'      => gmtime(),
            'recommended'      => $_POST['recommended'],
	    	'delivery_template_id' => intval($_POST['delivery_template_id']), // psmb
            'tags'             => html_script(trim($_POST['tags'])),
			'refer_reward_1'   => round(floatval($_POST['refer_reward_1']),4),
			'refer_reward_2'   => round(floatval($_POST['refer_reward_2']),4),
			'refer_reward_3'   => round(floatval($_POST['refer_reward_3']),4),
        );
        $spec_name_1 = !empty($_POST['spec_name_1']) ? $_POST['spec_name_1'] : '';
        $spec_name_2 = !empty($_POST['spec_name_2']) ? $_POST['spec_name_2'] : '';
        if ($spec_name_1 && $spec_name_2)
        {
            $goods['spec_qty'] = 2;
        }
        elseif ($spec_name_1 || $spec_name_2)
        {
            $goods['spec_qty'] = 1;
        }
        else
        {
            $goods['spec_qty'] = 0;
        }

        $goods_file_id = array();
        $desc_file_id =array();
        if (isset($_POST['goods_file_id']))
        {
            $goods_file_id = $_POST['goods_file_id'];
        }
        if (isset($_POST['desc_file_id']))
        {
            $desc_file_id = $_POST['desc_file_id'];
        }
        if ($id <= 0)
        {
            $goods['type'] = 'material';
            $goods['closed'] = 0;
            $goods['add_time'] = gmtime();
        }

        $specs = array(); // 原始规格
        switch ($goods['spec_qty'])
        {
            case 0: // 没有规格
                $specs[intval($_POST['spec_id'])] = array(
                    'price' => $this->_filter_price($_POST['price']),
                    'stock' => intval($_POST['stock']),
                    'sku'      => trim($_POST['sku']),
                    'spec_id'  => trim($_POST['spec_id']),
                );
                break;
            case 1: // 一个规格
                $goods['spec_name_1'] = $spec_name_1 ? $spec_name_1 : $spec_name_2;
                $goods['spec_name_2'] = '';
                $spec_data = $spec_name_1 ? $_POST['spec_1'] : $_POST['spec_2'];
                foreach ($spec_data as $key => $spec_1)
                {
                    $spec_1 = trim($spec_1);
                    if ($spec_1)
                    {
                        if (($spec_id = intval($_POST['spec_id'][$key]))) // 已有规格ID的
                        {
                            $specs[$key] = array(
                                'spec_id' => $spec_id,
                                'spec_1' => $spec_1,
								'spec_2' => '',
                                'price'  => $this->_filter_price($_POST['price'][$key]),
                                'stock'  => intval($_POST['stock'][$key]),
                                'sku'       => html_script(trim($_POST['sku'][$key])),
                            );
                        }
                        else  // 新增的规格
                        {
                            $specs[$key] = array(
                                'spec_1' => $spec_1,
                                'price'  => $this->_filter_price($_POST['price'][$key]),
                                'stock'  => intval($_POST['stock'][$key]),
                                'sku'       => html_script(trim($_POST['sku'][$key])),
                            );
                        }

                    }
                }
                break;
            case 2: // 二个规格
                $goods['spec_name_1'] = $spec_name_1;
                $goods['spec_name_2'] = $spec_name_2;
                foreach ($_POST['spec_1'] as $key => $spec_1)
                {
                    $spec_1 = trim($spec_1);
                    $spec_2 = trim($_POST['spec_2'][$key]);
                    if ($spec_1 && $spec_2)
                    {
                        if (($spec_id = intval($_POST['spec_id'][$key]))) // 已有规格ID的
                        {
                            $specs[$key] = array(
                                'spec_id'   => $spec_id,
                                'spec_1'    => $spec_1,
                                'spec_2'    => $spec_2,
                                'price'     => $this->_filter_price($_POST['price'][$key]),
                                'stock'     => intval($_POST['stock'][$key]),
                                'sku'       => html_script(trim($_POST['sku'][$key])),
                            );
                        }
                        else // 新增的规格
                        {
                            $specs[$key] = array(
                                'spec_1'    => $spec_1,
                                'spec_2'    => $spec_2,
                                'price'     => $this->_filter_price($_POST['price'][$key]),
                                'stock'     => intval($_POST['stock'][$key]),
                                'sku'       => html_script(trim($_POST['sku'][$key])),
                            );
                        }


                    }
                }
                break;
            default:
                break;
        }

        /* 分类 */
        $cates = array();

		if(isset($_POST['sgcate_id']) && is_array($_POST['sgcate_id'])) {
        	foreach ($_POST['sgcate_id'] as $cate_id)
        	{
            	if (intval($cate_id) > 0)
           	 	{
               	 	$cates[$cate_id] = array(
                   	 	'cate_id'      => $cate_id,
                	);
            	}
       	 	}
		}
		
		$data = array(
			'goods' => $goods, 
			'specs' => $specs, 
			'cates' => $cates, 
			'goods_file_id' => $goods_file_id, 
			'desc_file_id' => $desc_file_id,
			'props' => $_POST['props'],
			'max_exchange'=>$_POST['max_exchange'],
			'growbuy'		   => $_POST['growbuy'],
			'exclusive'		   => $_POST['exclusive'],
		);

		return $data;
    }

    /**
     * 检查提交的数据
     */
    function _check_post_data($data, $id = 0)
    {
		if($id > 0)
		{
			$groupbuy_mod = &m('groupbuy');
			$group = $groupbuy_mod->get('goods_id='.$id.' AND state '.db_create_in(array(GROUP_PENDING,GROUP_ON)));
			if(!empty($group))
			{
				$this->_error('无法完成修改：商品已被设置成正在进行的团购活动。');
				return false;
			}
		}
		
		if(!$data['goods']['goods_name'])
		{
			$this->_error('goods_name_empty');
			return false;
		}
		
		//最低抵扣不能超过商品的最大价格
		$max_exchange = $this->_integral_mod->_get_sys_setting('exchange_rate') * $data['max_exchange'];
		$spec=array();
		foreach($data['specs'] as $key=>$val){
			$spec[]=$val['price'];
		}
		if(($max_exchange > 0 && ($max_exchange >= max($spec))) || ($data['max_exchange'] < 0) || ($max_exchange < 0)){
			$this->_error('max_exchange_illege');
			return false;
		}
		if($data['exclusive']['decrease']){
		    if($data['exclusive']['decrease'] >= min($spec)){
		        $this->_error('exclusive_decrease_illege');
		        return false;
		    }
		}
		if($data['exclusive']['discount']){
		    if($data['exclusive']['discount'] >= 9.99 || $data['exclusive']['discount'] <=0.01){
		        $this->_error('discount_invalid');
		        return false;
		    }
		}
		
        if (!$this->_check_mgcate($data['goods']['cate_id']))
        {
            $this->_error('select_leaf_category');
            return false;
        }
		if(!isset($data['goods']['delivery_template_id']) || intval($data['goods']['delivery_template_id'])<0){
			$this->_error('select_delivery_template');
			return false;
		}
        if (!$this->_goods_mod->unique(trim($data['goods']['goods_name']), $id))
        {
            $this->_error('name_exist');
            return false;
        }
        if ($data['goods']['spec_qty'] == 1 && empty($data['goods']['spec_name_1']) || $data['goods']['spec_qty'] == 2 && (empty($data['goods']['spec_name_1']) || empty($data['goods']['spec_name_2'])))
        {
            $this->_error('fill_spec_name');
            return false;
        }
        if (empty($data['specs']))
        {
            $this->_error('fill_spec');
            return false;
        }
		
			$fenxiao = $data['goods']['refer_reward_1']+$data['goods']['refer_reward_2']+$data['goods']['refer_reward_3'];
			if($fenxiao>1){
                 $this->show_warning('分销提成比例之和不得超过1！');
                return;
            }

        return true;
    }

    function _format_goods_tags($tags)
    {
        if (!$tags)
        {
            return '';
        }
        $tags = explode(',', str_replace(Lang::get('comma'), ',', $tags));
        array_walk($tags, create_function('&$item, $key', '$item=trim($item);'));
        $tags = array_filter($tags);
        $tmp = implode(',', $tags);
        if (strlen($tmp) > 100)
        {
            $tmp = sub_str($tmp, 100, false);
        }

        return ',' . $tmp . ',';
    }

    /**
     * 保存数据
     */
    function _save_post_data($data, $id = 0)
    {
        import('image.func');
        import('uploader.lib');

        if ($data['goods']['tags'])
        {
            $data['goods']['tags'] = $this->_format_goods_tags($data['goods']['tags']);
        }
		
        /* 保存商品 */
        if ($id > 0)
        {
            // edit
            if (!$this->_goods_mod->edit($id, $data['goods']))
            {
                $this->_error($this->_goods_mod->get_error());
                return false;
            }
			
            $goods_id = $id;
        }
        else
        {
            // add
            $goods_id = $this->_goods_mod->add($data['goods']);
            if (!$goods_id)
            {
                $this->_error($this->_goods_mod->get_error());
                return false;
            }

            if (($data['goods_file_id'] || $data['desc_file_id'] ))
            {
                $uploadfiles = array_merge($data['goods_file_id'], $data['desc_file_id']);
                $this->_uploadedfile_mod->edit(db_create_in($uploadfiles, 'file_id'), array('item_id' => $goods_id));
            }
            if (!empty($data['goods_file_id']))
            {
                $this->_image_mod->edit(db_create_in($data['goods_file_id'], 'file_id'), array('goods_id' => $goods_id));
            }
        }
		
		$this->_save_goods_integral($goods_id,$data['max_exchange']); // 更新商品积分设置
		
        /* 保存规格 */
        if ($id > 0)
        {
            /* 删除的规格 */
            $goods_specs = $this->_spec_mod->find(array(
                'conditions' => "goods_id = '{$id}'",
                'fields' => 'spec_id, price' // 促销功能,需要price字段 psmb
            ));
            $drop_spec_ids = array_diff(array_keys($goods_specs), array_keys($data['specs']));
            if (!empty($drop_spec_ids))
            {
                $this->_spec_mod->drop($drop_spec_ids);
            }

        }
        $default_spec = array(); // 初始化默认规格
        foreach ($data['specs'] as $key => $spec)
        {
            if ($spec_id = $spec['spec_id']) // 更新已有规格ID
            {
                $this->_spec_mod->edit($spec_id,$spec);
            }
            else // 新加规格ID
            {
                $spec['goods_id'] = $goods_id;
                $spec_id = $this->_spec_mod->add($spec);
            }
            if (empty($default_spec))
            {
                $default_spec = array('default_spec' => $spec_id, 'price' => $spec['price']);
            }
        }

        /* 更新默认规格 */
        $this->_goods_mod->edit($goods_id, $default_spec);
        if ($this->_goods_mod->has_error())
        {
            $this->_error($this->_goods_mod->get_error());
            return false;
        }

        /* 保存商品分类 */
        $this->_goods_mod->unlinkRelation('belongs_to_gcategory', $goods_id);
        if ($data['cates'])
        {
            $this->_goods_mod->createRelation('belongs_to_gcategory', $goods_id, $data['cates']);
        }

        /* 设置默认图片 */
        if (isset($data['goods_file_id'][0]))
        {
            $default_image = $this->_image_mod->get(array(
                'fields' => 'thumbnail',
                'conditions' => "goods_id = '$goods_id' AND file_id = '{$data[goods_file_id][0]}'",
            ));
            $this->_image_mod->edit("goods_id = $goods_id", array('sort_order' => 255));
            $this->_image_mod->edit("goods_id = $goods_id AND file_id = '{$data[goods_file_id][0]}'", array('sort_order' => 1));
        }
		
        $this->_goods_mod->edit($goods_id, array(
            'default_image' => $default_image ? $default_image['thumbnail'] : '',
        ));

		/* 保存商品属性 */
		$this->_save_goods_props($data['props'], $goods_id);
		
		/* 保存加价购设置 */
		$growbuy_mod = &bm('promotool_setting', array('_store_id' => $this->_store_id, '_appid' => 'growbuy'));
		if($growbuy_mod->checkAvailable()){
			/* 排除不是本店铺的ID */
			$config = array('toolId' => $growbuy_mod->growbuyDataExclude($data['growbuy']));
			$growbuy_item_mod = &bm('promotool_item', array('_store_id' => $this->_store_id, '_appid' => 'growbuy'));
			if($config['toolId']) {
				$growbuy_item_mod->savePromotoolItem(array('goods_id' => $goods_id, 'config' => $config, 'status' => 1));
			}
			else $growbuy_item_mod->drop('goods_id='.$goods_id);
		}
			
		/* 保存手机专享设置 */
		$exclusive_mod = &bm('promotool_setting', array('_store_id' => $this->_store_id, '_appid' => 'exclusive'));
		if($exclusive_mod->checkAvailable()){
			$config = $data['exclusive'];
			unset($config['status']);
			if($config['discount']) unset($config['decrease']);
		    $exclusive_item_mod = &bm('promotool_item', array('_store_id' => $this->_store_id, '_appid' => 'exclusive'));
			$exclusive_item_mod->savePromotoolItem(array('goods_id' => $goods_id, 'config' => $config, 'status' => intval($data['exclusive']['status'])));
		}
		
        $this->_last_update_id = $goods_id;

        return true;
    }

    //品牌申请列表
    function brand_list()
    {
        $_GET['store_id'] = $this->_store_id;
        $_GET['if_show'] = BRAND_PASSED;
        $con = array(
            array(
                'field' => 'store_id',
                'name'  => 'store_id',
                'equal' => '=',
            ),
            array(
                'field' => 'if_show',
                'name'  => 'if_show',
                'equal' => '=',
                'assoc' => 'or',
            ),);
        $filtered = '';
        if (!empty($_GET['brand_name']) || !empty($_GET['store']))
        {
            $_GET['brand_name'] && $filtered = " AND brand_name LIKE '%{$_GET['brand_name']}%'";
            $_GET['store'] && $filtered = $filtered . " AND store_id = " . $this->_store_id;
        }
        if (isset($_GET['sort']) && isset($_GET['order']))
        {
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc')))
            {
                $sort  = 'store_id';
                $order = 'desc';
            }
        }
        else
        {
            $sort  = 'store_id';
            $order = 'desc';
        }
        $page = $this->_get_page(10);
        $conditions = $this->_get_query_conditions($con);
        $brand = $this->_brand_mod->find(array(
            'conditions' => "(1=1 $conditions)" . $filtered,
            'limit' => $page['limit'],
            'order' => "$sort $order",
            'count' => true,
        ));
        $page['item_count'] = $this->_brand_mod->getCount();
        $this->_format_page($page);
        $this->_curlocal(LANG::get('member_center'), 'index.php?app=member',
                         LANG::get('my_goods'), 'index.php?app=my_goods',
                         LANG::get('brand_list'));
        $this->_curitem('my_goods');
        $this->_curmenu('brand_apply_list');
        $this->import_resource(array(
                 'script' => array(
                     array(
                         'path' => 'jquery.plugins/jquery.validate.js',
                         'attr' => 'charset="utf-8"',
                     ),
                     array(
                         'path' => 'jquery.ui/jquery.ui.js',
                         'attr' => 'charset="utf-8"',
                     ),
                     array(
                         'attr' => 'id="dialog_js" charset="utf-8"',
                         'path' => 'dialog/dialog.js',
                     ),
                 ),
                 'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
             ));
        $this->assign('page_info', $page);
        $this->assign('filtered', empty($filtered) ? 0 : 1);
        $this->assign('brands', $brand);
        $this->display('brand_list.html');
    }

    //品牌申请
    function brand_apply()
    {
        if (!IS_POST)
        {
            header("Content-Type:text/html;charset=" . CHARSET);
            $this->display('brand_apply.html');
        }
        else
        {
            $brand_name = trim($_POST['brand_name']);
            if (empty($brand_name))
            {
                $this->pop_warning("brand_name_required");
                exit;
            }

            if (!$this->_brand_mod->unique($brand_name))
            {
                $this->pop_warning('brand_name_exist');
                return;
            }
            if (!$brand_id = $this->_brand_mod->add(array('brand_name' => $brand_name, 'store_id' => $this->_store_id, 'if_show' => 0, 'tag' => trim($_POST['tag']))))  //获取brand_id
            {
                $this->pop_warning($this->_brand_mod->get_error());

                return;
            }

            $logo = $this->_upload_logo($brand_id);
            if ($logo === false)
            {
                return;
            }
            $this->_brand_mod->edit($brand_id, array('brand_logo' => $logo));
            $this->pop_warning('ok',
                'my_goods_brand_apply', 'index.php?app=my_goods&act=brand_list');
        }
    }

    function brand_edit()
    {
        $id = intval($_GET['id']);
        $brand = $this->_brand_mod->find('store_id = ' . $this->_store_id . ' AND if_show = ' . BRAND_REFUSE . ' AND brand_id = ' . $id);
        $brand = current($brand);
        if (empty($brand))
        {
            $this->show_warning("not_rights");
            exit;
        }
        if (!IS_POST)
        {
            header("Content-Type:text/html;charset=" . CHARSET);
            $this->assign('brand', $brand);
            $this->display('brand_apply.html');
        }
        else
        {
            $brand_name = trim($_POST['brand_name']);
            if (!$this->_brand_mod->unique($brand_name, $id))
            {
                $this->pop_warning('brand_name_exist');
                return;
            }
            $data = array();
            if (isset($_FILES['brand_logo']))
            {
                $logo = $this->_upload_logo($id);
                if ($logo === false)
                {
                    return;
                }
                $data['brand_logo'] = $logo;
            }
            $data['brand_name'] = $brand_name;
            $data['tag'] = trim($_POST['tag']);
            $this->_brand_mod->edit($id, $data);
            if ($this->_brand_mod->has_error())
            {
                $this->pop_warning($this->_brand_mod->get_error());
                exit;
            }
            $this->pop_warning('ok', 'my_goods_brand_edit');
        }

    }

    function brand_drop()
    {
        $id = intval($_GET['id']);
        if (empty($id))
        {
            $this->show_warning('request_error');
            exit;
        }
        $brand = $this->_brand_mod->find("store_id = " . $this->_store_id . " AND if_show = " . BRAND_REFUSE . " AND brand_id = " . $id);
        $brand = current($brand);
        if (empty($brand))
        {
            $this->show_warning('request_error');
            exit;
        }
        if (!$this->_brand_mod->drop($id))
        {
            $this->show_warning($this->_brand_mod->get_error());
            exit;
        }
        if (!empty($brand['brand_logo']) && file_exists(ROOT_PATH . '/' . $brand['brand_logo']))
        {
            @unlink(ROOT_PATH . '/' . $brand['brand_logo']);
        }
        $this->show_message('drop_brand_ok',
            'back_list', 'index.php?app=my_goods&act=brand_list');

    }

    function check_brand()
    {
        $brand_name = $_GET['brand_name'];
        if (!$brand_name)
        {
            echo ecm_json_encode(true);
            return ;
        }
        if ($this->_brand_mod->unique($brand_name))
        {
            echo ecm_json_encode(true);
        }
        else
        {
            echo ecm_json_encode(false);
        }
        return ;
    }
    function _upload_logo($brand_id)
    {
        $file = $_FILES['brand_logo'];
        if ($file['error'] == UPLOAD_ERR_NO_FILE || !isset($_FILES['brand_logo'])) // 没有文件被上传
        {
            return '';
        }
        import('uploader.lib');             //导入上传类
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE); //限制文件类型
        $uploader->addFile($_FILES['brand_logo']);//上传logo
        if (!$uploader->file_info())
        {
            $this->pop_warning($uploader->get_error());
            if (ACT == 'brand_apply')
            {
                $m_brand = &m('brand');
                $m_brand->drop($brand_id);
            }            
            return false;
        }
        /* 指定保存位置的根目录 */
        $uploader->root_dir(ROOT_PATH);

        /* 上传 */
        if ($file_path = $uploader->save('data/files/mall/brand', $brand_id))   //保存到指定目录，并以指定文件名$brand_id存储
        {
            return $file_path;
        }
        else
        {
            return false;
        }
    }
    
    /* 价格过滤，返回非负浮点数 */
    function _filter_price($price)
    {
        return abs(floatval($price));
    }
	
    /* 保存商品积分设置 add by psmb */
	function _save_goods_integral($id,$max_exchange)
	{
		if(!$this->_integral_mod->_get_sys_setting('integral_enabled'))
		{
			return;
		}
		
		$goods_integral_mod =& m('goods_integral');
		if($goods_integral_mod->get($id)) // 积分设置已经存在，则修改积分设置
		{
			$goods_integral_mod->edit($id,array('max_exchange' => $max_exchange));
		}
		else
		{
			$goods_integral_mod->add(array('goods_id' => $id,'max_exchange' => $max_exchange));
		}
	}
}

?>
