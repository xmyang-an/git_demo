<?php

/* 定义like语句转换为in语句的条件 */
define('MAX_ID_NUM_OF_IN', 10000); // IN语句的最大ID数
define('MAX_HIT_RATE', 0.05);      // 最大命中率（满足条件的记录数除以总记录数）
define('MAX_STAT_PRICE', 10000);   // 最大统计价格
define('PRICE_INTERVAL_NUM', 5);   // 价格区间个数
define('MIN_STAT_STEP', 50);       // 价格区间最小间隔
define('ENABLE_SEARCH_CACHE', true); // 启用商品搜索缓存
define('SEARCH_CACHE_TTL', 3600);  // 商品搜索缓存时间

class SearchApp extends ApibaseApp
{
    /* 搜索商品 */
    function goods()
    {
		$post = parent::_getPostData();
		
		$param = $this->_get_query_param();

		if (isset($param['cate_id']) && $param['layer'] === false)
		{
			$this->show_warning('no_such_category');
			return;
		}
	
		/* 按分类、品牌、地区、价格区间统计商品数量 */
		$stats = $this->_get_group_by_info($param, ENABLE_SEARCH_CACHE);
			
		$page = $this->_get_page((isset($post['perpage']) && $post['perpage'] > 0) ? $post['perpage'] : 10);
		
		$page['item_count'] = $stats['total_count'];
		
		$sort = 'add_time desc';
		if(isset($post['order'])){
			$order_fields = explode('|',$post['order']);
			if(!empty($order_fields)){
				$sort = $order_fields[0].' '.$order_fields[1];
			}
		}
	
		/* 商品列表 */
		$conditions = $this->_get_goods_conditions($param);
		
		$orders = $this->_get_orders();

		$goods_mod  = &m('goods');
		$goods_list = $goods_mod->get_list(array(
			'conditions' => $conditions,
			'order'      =>  isset($orders[$sort]) ? $sort : 'add_time DESC',
			'limit'      => $page['limit'],
		));
		
		foreach ($goods_list as $key => $goods)
		{
			empty($goods['default_image']) && $goods['default_image'] = Conf::get('default_goods_image');
			
			if(stripos($goods['default_image'], '//:') == FALSE) {
				$goods['default_image'] = SITE_URL . '/' . $goods['default_image'];
			}
			
			$goods_list[$key]['default_image'] = $goods['default_image'];
		}
		
		$this->json_success(array_values($goods_list));
			
    }
	
	function statistic()
	{
		$param = $this->_get_query_param();
	
		/* 按分类、品牌、地区、价格区间统计商品数量 */
		$stats = $this->_get_group_by_info($param, ENABLE_SEARCH_CACHE);
		
		$this->json_success($stats);
	}
	
	function selectedfiter()
	{
		$param = $this->_get_query_param();
		$filter = $this->_get_filter($param);
		
		$this->json_success($filter);
	}
	
	function hot_keyword()
	{
		$config_mod = &af('wx_mini');
		$setting = $config_mod->getAll();
		
		if($setting['hot_search']){
			$data = array_filter(explode(' ', $setting['hot_search']));
		}
		
		$this->json_success($data);
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
    function _get_query_param()
    {
		$post = parent::_getPostData();
		
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
            if (isset($post['price']))
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
			// 获取属性参数
			if (isset($post['props']))
			{
				if($this->_check_query_param_by_props()){
					$res['props'] = trim($post['props']);
				}
			}
        }

        return $res;
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
            if (isset($param['keyword']))
            {
                $keyword = join(' ', $param['keyword']);
                $filters['keyword'] = array('key' => 'keyword', 'name' => LANG::get('keyword'), 'value' => $keyword);
            }
            isset($param['brand']) && $filters['brand'] = array('key' => 'brand', 'name' => LANG::get('brand'), 'value' => $param['brand']);
            if (isset($param['region_id']))
            {
                // todo 从地区缓存中取
                $region_mod =& m('region');
                $row = $region_mod->get(array(
                    'conditions' => $param['region_id'],
                    'fields' => 'region_name'
                ));
                $filters['region_id'] = array('key' => 'region_id', 'name' => LANG::get('region'), 'value' => $row['region_name']);
            }
            if (isset($param['price']))
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
			// sku psmb 
			if (isset($param['props']))
			{
				$props_mod = &m('props');
				$prop_value_mod = &m('prop_value');
				foreach(explode(';',$param['props']) as  $pv)
				{
					$pv_arr = explode(':',$pv);
					if(is_numeric($pv_arr[0]) && is_numeric($pv_arr[1])){// 安全监测，防止 sql 注入
						$props = $props_mod->get($pv_arr[0]);
						$prop_value = $prop_value_mod->get($pv_arr[1]);
						$filters['props_'.$props['pid']] = array('key' => $pv, 'name' => $props['name'], 'value'=> $prop_value['prop_value']);
					}
				}
			}
			if(isset($param['cate_id'])) {
				$gcategory_mod =&bm('gcategory', array('_store_id' => 0));
				$gcategory = $gcategory_mod->get(array('conditions' => 'cate_id='.intval($param['cate_id']), 'fields' => 'cate_name'));
				$filters['cate_id'] = array('key' => 'cate_id', 'name' => Lang::get('gcategory'), 'value' => $gcategory['cate_name']);
			}
        }
		
        return $filters;
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
		
		$conditions .= " AND s.store_id ".db_create_in($this->GetLocation());

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
			$post = parent::_getPostData();
			
            $data = array(
                'total_count' => 0,
                'by_category' => array(),
                'by_brand'    => array(),
                'by_region'   => array(),
                'by_price'    => array()
            );

            $goods_mod =& m('goods');
            $store_mod =& m('store');
			// sku psmb
			$goods_pvs_mod =& m('goods_pvs'); 
			$props_mod = &m('props');
			$prop_value_mod = &m('prop_value');
            $table = " {$goods_mod->table} g LEFT JOIN {$store_mod->table} s ON g.store_id = s.store_id LEFT JOIN {$goods_pvs_mod->table} gp ON gp.goods_id=g.goods_id "; 
			// end sku
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
                            'cate_name' => isset($children[$row['id']]) ? $children[$row['id']]['cate_name'] : '',
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
				
				$num_per_page = (isset($post['perpage']) && $post['perpage'] > 0) ? $post['perpage'] : 10;
                /* 按价格统计 */
                if ($total_count > $num_per_page)
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
				
				// 按属性统计 
				$sql = "SELECT gp.* FROM {$table} WHERE " . $conditions . " AND gp.pvs > '' ";
				$prop_list = $goods_mod->getAll($sql);
				$pvs = '';
				foreach($prop_list as $key => $prop) {
					$pvs .=';' . $prop['pvs'];
				}
				$pvs = substr($pvs,1);// 去掉前面的";"
				$props_data = array();
				if(!empty($pvs))
				{
				   $pv_arr = array_unique(explode(';',$pvs));// 去除重复值，形成新的数组
				   $pid = 0;
				   $prop_value = array();
				
				   //  先排序
				   foreach ($pv_arr as $key => $row) {
					  $volume[$key]  = $row[0];
				   }
				   array_multisort($volume,SORT_DESC,$pv_arr); // 排序后才能做以下 $pid!=$item[0] 的判断
				   
				   /* 检查属性名和属性值是否存在，有可能是之前有，但后面删除了 */
				   foreach($pv_arr as $key=>$pv)
				   {
					   if($pv)
					   {
					   		$item = explode(':',$pv);
					   		$check_prop = $props_mod->get(array('conditions'=>'pid='.$item[0].' AND status=1','fields'=>'pid'));
					   
					   		// 如果属性名存在，则检查该属性名下的当前属性值是否存在
					   		if($check_prop)
					   		{
						   		$check_prop_value = $prop_value_mod->get(array('conditions'=>'pid='.$item[0].' AND vid='.$item[1].' and status=1','fields'=>'vid'));
						   		if(!$check_prop_value){
							   		unset($pv_arr[$key]);
						   		}
					   		} else {
						   		unset($pv_arr[$key]);
					  		}
					   }
				   }
				
				   //  将当前的筛选数据除掉
				   $p = array();
				   if(!empty($post['props']))
				   {
					   foreach(explode(';',$post['props']) as $pv)
					   {
						   $pv = explode(':',$pv);
						   $p[] = $pv[0];
					   }
					   $p = array_unique($p);
				   }
				   //  end 当前的筛选数据除掉
				   
				   
				   foreach($pv_arr as $key=>$pv)
				   {
					   $item = explode(':',$pv);
					   if(!empty($item[1]) && !in_array($item[0],$p)) //  如果参数已经筛选过了，那么就屏蔽掉。
					   { 

					      $props = $props_mod->get(array('conditions'=>'status=1 and pid='.$item[0],'fields'=>'name,pid,is_color_prop'));
					      $props_data[$item[0]] = $props;
					      if ($pid!=$item[0]) { // 不是同一个 pid 的属性值，不做累加
					         $prop_value = array();
					         $pid = $item[0];
				          }
					      $prop_value[] = $prop_value_mod->get(array('conditions'=>'status=1 and pid='.$item[0].' and vid='.$item[1],'fields'=>'prop_value,vid,pid,color_value'));

				          $props_data[$item[0]] += array('value'=>$prop_value);
					   }
				   }
				}
				if($props_data)
				{
					foreach($props_data as $key => $val)
					{
						foreach($val['value'] as $k=>$v)
						{
							$sql = "SELECT COUNT(*) AS count FROM {$table} WHERE" . $conditions ." AND instr(gp.pvs,'".$v['pid'].":".$v['vid']."')";
							$count = $goods_mod->getAll($sql);
							$props_data[$key]['value'][$k]['count']=$count[0]['count'];
						}
						$props_data[$key]['prop_count'] = count($props_data[$key]['value']);
					}
				}
				$data['by_props'] = $props_data;
				
            }

            if ($cached)
            {
                $cache_server->set($key, $data, SEARCH_CACHE_TTL);
            }
        }

        return $data;
    }

    /**
     * 根据关键词取得查询条件（可能是like，也可能是in）
     *
     * @param   array       $keyword    关键词
     * @param   bool        $cached     是否缓存
     * @return  string      " AND (0)"
     *                      " AND (goods_name LIKE '%a%' AND goods_name LIKE '%b%')"
     *                      " AND (goods_id IN (1,2,3))"
     */
    function _get_conditions_by_keyword($keyword, $cached)
    {
        $conditions = false;

        if ($cached)
        {
            $cache_server =& cache_server();
            $key1 = 'query_conditions_of_keyword_' . join("\t", $keyword);
            $conditions = $cache_server->get($key1);
        }

        if ($conditions === false)
        {
            /* 组成查询条件 */
            $conditions = array();
            foreach ($keyword as $word)
            {
                $conditions[] = "g.goods_name LIKE '%{$word}%'";
            }
            $conditions = join(' AND ', $conditions);

            /* 取得满足条件的商品数 */
            $goods_mod =& m('goods');
            $sql = "SELECT COUNT(*) FROM {$goods_mod->table} g WHERE " . $conditions;
            $current_count = $goods_mod->getOne($sql);
            if ($current_count > 0)
            {
                if ($current_count < MAX_ID_NUM_OF_IN)
                {
                    /* 取得商品表记录总数 */
                    $cache_server =& cache_server();
                    $key2 = 'record_count_of_goods';
                    $total_count = $cache_server->get($key2);
                    if ($total_count === false)
                    {
                        $sql = "SELECT COUNT(*) FROM {$goods_mod->table}";
                        $total_count = $goods_mod->getOne($sql);
                        $cache_server->set($key2, $total_count, SEARCH_CACHE_TTL);
                    }

                    /* 不满足条件，返回like */
                    if (($current_count / $total_count) < MAX_HIT_RATE)
                    {
                        /* 取得满足条件的商品id */
                        $sql = "SELECT goods_id FROM {$goods_mod->table} g WHERE " . $conditions;
                        $ids = $goods_mod->getCol($sql);
                        $conditions = 'g.goods_id' . db_create_in($ids);
                    }
                }
            }
            else
            {
                /* 没有满足条件的记录，返回0 */
                $conditions = "0";
            }

            if ($cached)
            {
                $cache_server->set($key1, $conditions, SEARCH_CACHE_TTL);
            }
        }

        return ' AND (' . $conditions . ')';
    }
	
	function _check_query_param_by_props()
	{
		$post = parent::_getPostData();
		
		$pvs = $post['props'];
		if(!empty($pvs)){
			$pvs_arr = explode(';',$pvs);
			foreach($pvs_arr as $pv){
				$pv_arr = explode(':', $pv);
				if(is_array($pv_arr)){
					if(!is_numeric($pv_arr[0]) || !is_numeric($pv_arr[1])){
						return false;
					}
				} else {
					return false;
				}
			}
		}
		return true;		
	}	

    /* 商品排序方式 */
    function _get_orders()
    {
        return array(
            ''                  => Lang::get('select_pls'),
            'sales desc'        => Lang::get('sales_desc'),
            'credit_value desc' => Lang::get('credit_value_desc'),
            'price asc'         => Lang::get('price_asc'),
            'price desc'        => Lang::get('price_desc'),
            'views desc'        => Lang::get('views_desc'),
			'views asc'         => Lang::get('views_asc'),
            'add_time desc'     => Lang::get('add_time_desc'),
			'add_time asc'      => Lang::get('add_time_asc'),
        );
    }
	
	function store(){
		$cate_id = empty($this->PostData['cate_id']) ? 0 : intval($this->PostData['cate_id']);
		
		if ($cate_id > 0)
		{
			$scategory_mod =& m('scategory');
			$cate_ids = $scategory_mod->get_descendant($cate_id);
		
			/* 店铺分类检索条件 */
			$condition_id = implode(',',$cate_ids);
			$condition_id && $condition_id = ' AND cate_id IN(' . $condition_id . ')';
		}
		
		$_GET = $this->PostData;

		/* 其他检索条件 */
		$conditions = $this->_get_query_conditions(array(
			array( //店铺名称
				'field' => 'store_name',
				'equal' => 'LIKE',
				'assoc' => 'AND',
				'name'  => 'keyword',
				'type'  => 'string',
			),
			array( //地区id
				'field' => 'region_id',
				'equal' => '=',
				'assoc' => 'AND',
				'name'  => 'region_id',
				'type'  => 'string',
			)
		));
			
		$orders = array(
			'add_time_desc' => 'add_time desc',
			'add_time_asc' => 'add_time asc',
			'praise_rate_desc' => 'praise_rate desc',
			'praise_rate_asc' => 'praise_rate asc',
			'credit_value_desc' => 'credit_value desc',
			'credit_value_asc' => 'credit_value asc',
			'distance_asc' => 'distance asc',
			'distance_desc' => 'distance desc'
		);
		
		$default_order = ' add_time desc';
		
		$lat = $this->PostData['lat'];
		$lng = $this->PostData['lng'];
		if($lat && $lng)
		{
			$mapPiontCaculation = ' (2 * 6378.137* ASIN(SQRT(POW(SIN(PI()*(' . $lat . '-lat)/360),2)+COS(PI()*' . $lat . '/180)* COS(lat * PI()/180)*POW(SIN(PI()*(' . $lng . '-lng)/360),2))))';
			$distanceFields = ','.$mapPiontCaculation.' as distance';
				
			$default_order = 'distance asc';
			
			$mapPiontCaculation = ' AND '.$mapPiontCaculation; 
		}
		
		$page   =   $this->_get_page((isset($this->PostData['perpage']) && $this->PostData['perpage'] > 0) ? $this->PostData['perpage'] : 10);   //获取分页信息
		
		$store_mod = &m('store');
		$stores = $store_mod->find(array(
			'conditions'  => 'state = ' . STORE_OPEN . $condition_id . $conditions.$mapPiontCaculation,
			'limit'   =>$page['limit'],
			'order'   => empty($this->PostData['order']) || !isset($orders[$this->PostData['order']]) ? $default_order : $orders[$this->PostData['order']],
			'join'    => 'belongs_to_user,has_scategory',
			'fields' => 's.address,s.store_name, s.store_id, credit_value, praise_rate,store_logo'.$distanceFields,
			'count'   => true   //允许统计
				
		));
		
		$page['item_count'] = $store_mod->getCount();   //获取统计数据
			
		$model_goods = &m('goods');
		foreach ($stores as $key => $store)
		{
			//店铺logo
			empty($store['store_logo']) && $store['store_logo'] = Conf::get('default_store_logo');
			if(stripos($store['store_logo'], '//:') == FALSE) {
				$stores[$key]['store_logo'] = SITE_URL . '/' . $store['store_logo'];
			}
	
			//商品数量
			$stores[$key]['goods_count'] = $model_goods->get_count_of_store($store['store_id']);
	
			//等级图片
			$step = intval(Conf::get('upgrade_required'));
			$step < 1 && $step = 5;
			$stores[$key]['credit_image'] = site_url(). '/static/images/' . $store_mod->compute_credit($store['credit_value'], $step);
				
			$goods_list = $model_goods->find(array(
				'conditions'=> 'if_show=1 AND closed=0 AND store_id='. $store['store_id'],
				'order'     => 'add_time desc',
				'limit'     => 5,
				'fields'=> 'goods_name,default_image,price'
			));
				
			$stores[$key]['goods_list'] = $goods_list ? array_values($goods_list) : array();
				
			$collects = db()->getOne("SELECT count(*) FROM ".DB_PREFIX."collect c WHERE type='store' AND item_id=".$store['store_id']);
			if($collects >= 10000) {
				$collects = ($collects/10000).'万';
			}
			
			$stores[$key]['collect'] = $collects;
			
			$stores[$key]['distance'] = (floatval($store['distance']) < 1) ? sprintf('%s 米', round(floatval($store['distance']),2)*1000) : sprintf('%s 千米', round(floatval($store['distance']),2));
		}
			
		$this->json_success(array_values($stores));
	}
	
	
	function filter_store(){
		$data = array();
		
		$scategorys[] = array('value' => '全部', 'id' => 0, 'selected' => 'active');
		$cates = $this->_list_scategory();
		if(!empty($cates)){
			$scategorys = array_merge($scategorys,$cates);
		}
		
		$data['scategories'] = $scategorys;
		
		$model_store =& m('store');
		$regions = $model_store->list_regions();
		
		if(!empty($regions)){
			foreach($regions as $key=>$region_name){
				if($region_name){
					$region_name = explode(',',preg_replace('/\s+/',',',$region_name));
					$region_name = array_reverse($region_name);
					$region_name = $region_name[0];
				}
				
				$regions[$key] = array('region_name' => $region_name, 'region_id' => $key);
			}
		}
		
		$data['regions'] = array_values($regions);
		
		$this->json_success($data);
	}
	
	function _list_scategory()
    {
        $scategory_mod =& m('scategory');
        $scategories = $scategory_mod->get_list(-1,true);

        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($scategories, 'cate_id', 'parent_id', 'cate_name');
        return $tree->getArrayList(0);
    }
}

?>
