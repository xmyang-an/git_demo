<?php
define('MAX_ID_NUM_OF_IN', 10000); // IN语句的最大ID数
define('MAX_HIT_RATE', 0.05);      // 最大命中率（满足条件的记录数除以总记录数）
define('MAX_STAT_PRICE', 10000);   // 最大统计价格
define('PRICE_INTERVAL_NUM', 5);   // 价格区间个数
define('MIN_STAT_STEP', 50);       // 价格区间最小间隔
define('NUM_PER_PAGE', 10);        // 每页显示数量
define('ENABLE_SEARCH_CACHE', true); // 启用商品搜索缓存
define('SEARCH_CACHE_TTL', 3600);  // 商品搜索缓存时间
class SearchApp extends MallbaseApp
{
	function goods()
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
		
			$param = $this->_get_query_param($post);
			$conditions = $this->_get_goods_conditions($param);

			$order = 'goods_id DESC';
			if(isset($post['sort']) && $post['sort'] && in_array($post['sort'], array('price', 'sales', 'add_time'))) {
				$order = $post['sort'] . ' DESC';
			}
			
			$goods_mod = &m('goods');
			
			$page = $this->_get_page(NUM_PER_PAGE);
			$goodsList = $goods_mod->get_list(array(
				'conditions' => $conditions,
				'limit'     =>$page['limit'],
				'order' => $order
			));

			$list  = array();
			foreach($goodsList as  $key => $goods) {
				empty($goods['default_image']) && $goods['default_image'] = Conf::get('default_goods_image');
				$goodsList[$key]['default_image'] = SITE_URL . '/' . $goods['default_image'];
				
				$list[] = $goodsList[$key];
			}
			
			$res = array('goodsList' => $list);
			
			if(!$post['justRequestGoods'])
			{
				$res['stats'] = $this->_get_group_by_info($param, ENABLE_SEARCH_CACHE);
				$res['filter'] = $this->_get_filter($param);
			}

			$result = array(
				'status'=> 'SUCCESS',
				'title' => "搜索商品",
				'retval'=> $res
			 );
		}
		echo json_encode($result);
    }
	
	/**
     * 取得查询参数（有值才返回）
     *
     * @return  array(
     *              'keyword'   => array('aa', 'bb'),
     *              'cate_id'   => 2,
     *              'layer'     => 2, // 分类层级
     *              'brand'     => 'ibm',
     *              'region_id' => 23,
     *              'price'     => array('min' => 10, 'max' => 100),
     *          )
     */
    function _get_query_param($post)
    {
        static $res = null;
        if ($res === null)
        {
            $res = array();
    
            // keyword
            $keyword = isset($post['keyword']) ? trim($post['keyword']) : '';
            if ($keyword != '')
            {
                $tmp = str_replace(array(Lang::get('comma'),Lang::get('whitespace'),' '),',', $keyword);
                $keyword = explode(',',$tmp);
                sort($keyword);
                $res['keyword'] = $keyword;
            }
    
            // cate_id
            if (isset($post['cate_id']) && intval($post['cate_id']) > 0)
            {
                $res['cate_id'] = $cate_id = intval($post['cate_id']);
                $gcategory_mod  =& bm('gcategory');
                $res['layer']   = $gcategory_mod->get_layer($cate_id, true);
            }
    
            // brand
            if (isset($post['brand']))
            {
                $brand = trim($post['brand']);
                $res['brand'] = $brand;
            }
    
            // region_id
            if (isset($post['region_id']) && intval($post['region_id']) > 0)
            {
                $res['region_id'] = intval($post['region_id']);
            }
    
            // price
            if (isset($post['price']) && !empty($post['price']))
            {
                $arr = explode('-', $post['price']);
                $min = abs(floatval($arr[0]));
                $max = abs(floatval($arr[1]));
                if ($min * $max > 0 && $min > $max)
                {
                    list($min, $max) = array($max, $min);
                }
    
                $res['price'] = array(
                    'min' => $min,
                    'max' => $max
                );
            }
        }

        return $res;
    }
	
	/**
     * 取得查询条件语句
     *
     * @param   array   $param  查询参数（参加函数_get_query_param的返回值说明）
     * @return  string  where语句
     */
    function _get_goods_conditions($param)
    {
        /* 组成查询条件 */
        $conditions = " g.if_show = 1 AND g.closed = 0 AND s.state = 1"; // 上架且没有被禁售，店铺是开启状态,
        if (isset($param['keyword']))
        {
            $conditions .= $this->_get_conditions_by_keyword($param['keyword'], ENABLE_SEARCH_CACHE);
        }
        if (isset($param['cate_id']))
        {
            $conditions .= " AND g.cate_id_{$param['layer']} = '" . $param['cate_id'] . "'";
        }
        if (isset($param['brand']))
        {
            $conditions .= " AND g.brand = '" . $param['brand'] . "'";
        }
        if (isset($param['region_id']))
        {
            $conditions .= " AND s.region_id = '" . $param['region_id'] . "'";
        }
        if (isset($param['price']))
        {
            $min = $param['price']['min'];
            $max = $param['price']['max'];
            $min > 0 && $conditions .= " AND g.price >= '$min'";
            $max > 0 && $conditions .= " AND g.price <= '$max'";
        }
		// sku psmb 
		if (isset($param['props']))
		{
			$pv_arr = explode(';',$param['props']);
			foreach($pv_arr as $pv)
			{
				if(is_numeric(str_replace(':','',$pv))){ //安全监测，防止sql注入，去掉分号后，监测是否全为数字。
					$conditions .= " AND instr(gp.pvs,'".$pv."')>0 ";
				}
			}
		}

        return $conditions;
    }
	
	/**
     * 根据查询条件取得分组统计信息
     *
     * @param   array   $param  查询参数（参加函数_get_query_param的返回值说明）
     * @param   bool    $cached 是否缓存
     * @return  array(
     *              'total_count' => 10,
     *              'by_category' => array(id => array('cate_id' => 1, 'cate_name' => 'haha', 'count' => 10))
     *              'by_brand'    => array(array('brand' => brand, 'count' => count))
     *              'by_region'   => array(array('region_id' => region_id, 'region_name' => region_name, 'count' => count))
     *              'by_price'    => array(array('min' => 10, 'max' => 50, 'count' => 10))
     *          )
     */
    function _get_group_by_info($param, $cached)
    {
        $data = false;

        if ($cached)
        {
            $cache_server =& cache_server();
            $key = 'group_by_info_' . var_export($param, true);
            $data = $cache_server->get($key);
        }

        if ($data === false)
        {
            $data = array(
                'total_count' => 0,
                'by_category' => array(),
                'by_brand'    => array(),
                'by_region'   => array(),
                'by_price'    => array()
            );

            $goods_mod =& m('goods');
            $store_mod =& m('store');

            $table = " {$goods_mod->table} g LEFT JOIN {$store_mod->table} s ON g.store_id = s.store_id "; 

            $conditions = $this->_get_goods_conditions($param);
            $sql = "SELECT COUNT(*) FROM {$table} WHERE" . $conditions;
            $total_count = $goods_mod->getOne($sql);
            if ($total_count > 0)
            {
                $data['total_count'] = $total_count;
                /* 按分类统计 */
                $cate_id = isset($param['cate_id']) ? $param['cate_id'] : 0;
                $sql = "";
                if ($cate_id > 0)
                {
                    $layer = $param['layer'];
                    if ($layer < 4)
                    {
                        $sql = "SELECT g.cate_id_" . ($layer + 1) . " AS id, COUNT(*) AS count FROM {$table} WHERE" . $conditions . " AND g.cate_id_" . ($layer + 1) . " > 0 GROUP BY g.cate_id_" . ($layer + 1) . " ORDER BY count DESC";
                    }
                }
                else
                {
                    $sql = "SELECT g.cate_id_1 AS id, COUNT(*) AS count FROM {$table} WHERE" . $conditions . " AND g.cate_id_1 > 0 GROUP BY g.cate_id_1 ORDER BY count DESC";
                }

                if ($sql)
                {
                    $category_mod =& bm('gcategory');
                    $children = $category_mod->get_children($cate_id, true);
                    $res = $goods_mod->db->query($sql);
                    while ($row = $goods_mod->db->fetchRow($res))
                    {
                        $data['by_category'][$row['id']] = array(
                            'cate_id'   => $row['id'],
                            'cate_name' => $children[$row['id']]['cate_name'],
                            'count'     => $row['count']
                        );
                    }
                }

                /* 按品牌统计 */
                $sql = "SELECT g.brand, COUNT(*) AS count FROM {$table} WHERE" . $conditions . " AND g.brand > '' GROUP BY g.brand ORDER BY count DESC";
                $by_brands = $goods_mod->db->getAllWithIndex($sql, 'brand');
                
                /* 滤去未通过商城审核的品牌 */
                if ($by_brands)
                {
                    $m_brand = &m('brand');
                    $brand_conditions = db_create_in(addslashes_deep(array_keys($by_brands)), 'brand_name');
                    $brands_verified = $m_brand->getCol("SELECT brand_name FROM {$m_brand->table} WHERE " . $brand_conditions . ' AND if_show=1');
                    foreach ($by_brands as $k => $v)
                    {
                        if (!in_array($k, $brands_verified))
                        {
                            unset($by_brands[$k]);
                        }
                    }
                }
				
				$by_brands = Psmb_init()->get_group_by_info_by_brands($by_brands,$param);
				
                $data['by_brand'] = $by_brands;
                
                
                /* 按地区统计 */
                $sql = "SELECT s.region_id, s.region_name, COUNT(*) AS count FROM {$table} WHERE" . $conditions . " AND s.region_id > 0 GROUP BY s.region_id ORDER BY count DESC";
				
				$by_regions = Psmb_init()->get_group_by_info_by_region($sql,$param);
                $data['by_region'] = $by_regions;
				/*  end  */
				

                /* 按价格统计 */
                if ($total_count > NUM_PER_PAGE)
                {
                    $sql = "SELECT MIN(g.price) AS min, MAX(g.price) AS max FROM {$table} WHERE" . $conditions;
                    $row = $goods_mod->getRow($sql);
                    $min = $row['min'];
                    $max = min($row['max'], MAX_STAT_PRICE);
                    $step = max(ceil(($max - $min) / PRICE_INTERVAL_NUM), MIN_STAT_STEP);
                    $sql = "SELECT FLOOR((g.price - '$min') / '$step') AS i, count(*) AS count FROM {$table} WHERE " . $conditions . " GROUP BY i ORDER BY i";
                    $res = $goods_mod->db->query($sql);
                    while ($row = $goods_mod->db->fetchRow($res))
                    {
                        $data['by_price'][] = array(
                            'count' => $row['count'],
                            'min'   => $min + $row['i'] * $step,
                            'max'   => $min + ($row['i'] + 1) * $step,
                        );
                    }
                }
            }

            if ($cached)
            {
                $cache_server->set($key, $data, SEARCH_CACHE_TTL);
            }
        }

        return $data;
    }
	
	/**
     * 取得过滤条件
     */
    function _get_filter($param)
    {
        static $filters = null;
        if ($filters === null)
        {
            $filters = array();
            if (isset($param['keyword']) && !empty($param['keyword']))
            {
                $keyword = join(' ', $param['keyword']);
                $filters['keyword'] = array('key' => 'keyword', 'name' => LANG::get('keyword'), 'value' => $keyword);
            }
			
			if (isset($param['cate_id']) && $param['cate_id'] > 0)
            {
				$gcategory_mod = &m('gcategory');
				$cate = $gcategory_mod->get('cate_id='.$param['cate_id']);
				
				if(!empty($cate))
				{
                	$filters['cate_id'] = array('key' => 'cate_id', 'name' =>LANG::get('gcategory'), 'value' => $cate['cate_name']);
				}
            }
			
            (isset($param['brand']) && !empty($param['brand']))  && $filters['brand'] = array('key' => 'brand', 'name' => LANG::get('brand'), 'value' => $param['brand']);
			
            if (isset($param['region_id']) && !empty($param['region_id']))
            {
                // todo 从地区缓存中取
                $region_mod =& m('region');
                $row = $region_mod->get(array(
                    'conditions' => $param['region_id'],
                    'fields' => 'region_name'
                ));
                $filters['region_id'] = array('key' => 'region_id', 'name' => LANG::get('region'), 'value' => $row['region_name']);
            }
            if (isset($param['price']) && !empty($param['price']))
            {
                $min = $param['price']['min'];
                $max = $param['price']['max'];
                if ($min <= 0)
                {
                    $filters['price'] = array('key' => 'price', 'name' => LANG::get('price'), 'value' => LANG::get('le') . ' ' . price_format($max));
                }
                elseif ($max <= 0)
                {
                    $filters['price'] = array('key' => 'price', 'name' => LANG::get('price'), 'value' => LANG::get('ge') . ' ' . price_format($min));
                }
                else
                {
                    $filters['price'] = array('key' => 'price', 'name' => LANG::get('price'), 'value' => price_format($min) . ' - ' . price_format($max));
                }
            }
        }
        return $filters;
    }
	
	function store()
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
	
			/* 取得该分类及子分类cate_id */
			$cate_id = empty($post['cate_id']) ? 0 : intval($post['cate_id']);
			$cate_ids=array();
			$condition_id='';
			if ($cate_id > 0)
			{
				$scategory_mod =& m('scategory');
				$cate_ids = $scategory_mod->get_descendant($cate_id);
			}
	
			/* 店铺分类检索条件 */
			$condition_id=implode(',',$cate_ids);
			$condition_id && $condition_id = ' AND cate_id IN(' . $condition_id . ')';
			
			$step= intval(Conf::get('upgrade_required'));
			$step < 1 && $step = 5;
			$level_1 = $step * 5;
			$level_2 = $level_1 * 6;
			$level_3 = $level_2 * 6;
			if($post['credit_value']){
				switch(intval($post['credit_value'])){
					case 1;
					$credit_condition=' AND credit_value<'.$level_1.' ';
					break;
					case 2;
					$credit_condition=' AND credit_value<'.$level_2.' AND credit_value>='.$level_1.' ';
					break;
					case 3;
					$credit_condition=' AND credit_value<'.$level_3.' AND credit_value>='.$level_2.' ';
					break;
					case 4;
					$credit_condition=' AND credit_value>='.$level_3.' ';
					break;	
				}
			}
	
			/* 其他检索条件 */
			$conditions = $this->_get_query_conditions(array(
				array( //店铺名称
					'field' => 'store_name',
					'equal' => 'LIKE',
					'assoc' => 'AND',
					'name'  => 'keyword',
					'type'  => 'string',
				),
				array( //地区名称
					'field' => 'region_name',
					'equal' => 'LIKE',
					'assoc' => 'AND',
					'name'  => 'region_name',
					'type'  => 'string',
				),
				array( //地区id
					'field' => 'region_id',
					'equal' => '=',
					'assoc' => 'AND',
					'name'  => 'region_id',
					'type'  => 'string',
				),
				array( //店铺等级id
					'field' => 'sgrade',
					'equal' => '=',
					'assoc' => 'AND',
					'name'  => 'sgrade',
					'type'  => 'string',
				),
				array( //是否推荐
					'field' => 'recommended',
					'equal' => '=',
					'assoc' => 'AND',
					'name'  => 'recommended',
					'type'  => 'string',
				),
				array( //好评率
					'field' => 'praise_rate',
					'equal' => '>',
					'assoc' => 'AND',
					'name'  => 'praise_rate',
					'type'  => 'string',
				),
				array( //商家用户名
					'field' => 'user_name',
					'equal' => 'LIKE',
					'assoc' => 'AND',
					'name'  => 'user_name',
					'type'  => 'string',
				),
			));
			
			$order = 'sort_order ASC,recommended DESC';
			if(isset($post['sort']) && $post['sort'] && in_array($post['sort'], array('add_time', 'praise_rate', 'credit_value'))) {
				$order = $post['sort'] . ' DESC, recommended DESC';
			}
			
			$store_mod = &m('store');
			
			$page = $this->_get_page(10);
			$storeList = $store_mod->find(array(
				'conditions' => 'state = ' . STORE_OPEN .$credit_condition.$condition_id . $conditions,
				'join'    => 'belongs_to_user,has_scategory',
				'limit'     =>$page['limit'],
				'fields' => 's.store_id, store_name, store_logo, praise_rate, credit_value, region_name, owner_name',
				'order' => $order,
				'count'     =>true,
			));
			
			$step= intval(Conf::get('upgrade_required'));
			$step < 1 && $step = 5;
			
			$list  = array();
			foreach($storeList as  $key => $store) {
				empty($store['store_logo']) && $store['store_logo'] = Conf::get('default_store_logo');
				$storeList[$key]['store_logo'] = SITE_URL . '/' . $store['store_logo'];
				
				//商品数量
            	$storeList[$key]['goods_count'] = $this->_get_count_of_store($store['store_id']);
			
				 //等级图片
            	$storeList[$key]['credit_image'] = $this->_view->res_base . '/images/' . $store_mod->compute_credit($store['credit_value'], $step);
				
				$list[] = $storeList[$key];
			
			}
	
			sort($storeList);
			
			$result = array(
				'status'=> 'SUCCESS',
				'title' => "搜索店铺",
				'retval'=> $list
			);
		}
		echo json_encode($result);
    }
	
	/**
     * 取得店铺商品数量
     *
     * @param int $store_id
     */
    function _get_count_of_store($store_id)
    {
        static $data = array();
        if (!isset($data[$store_id]))
        {
			$goods_mod = &m('goods');
            $cache_server =& cache_server();
            $data = $cache_server->get('goods_count_of_store');
            if($data === false)
            {
                $sql = "SELECT store_id, COUNT(*) AS goods_count FROM {$goods_mod->table} WHERE if_show = 1 AND closed = 0 GROUP BY store_id";
                $data = array();
                $res = db()->query($sql);
                while ($row = db()->fetchRow($res))
                {
                    $data[$row['store_id']] = $row['goods_count'];
                }
                $cache_server->set('goods_count_of_store', $data, 3600);
            }
        }
        return isset($data[$store_id]) ? $data[$store_id] : 0;
    }
}

?>
