--
-- 表的结构 `swd_acategory`
--

DROP TABLE IF EXISTS `swd_acategory`;
CREATE TABLE `swd_acategory` (
  `cate_id` int(10) unsigned NOT NULL auto_increment,
  `cate_name` varchar(100) NOT NULL default '',
  `parent_id` int(10) unsigned NOT NULL default '0',
  `sort_order` tinyint(3) unsigned NOT NULL default '255',
  `code` varchar(10) default NULL,
  PRIMARY KEY  (`cate_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_address`
--

DROP TABLE IF EXISTS `swd_address`;
CREATE TABLE `swd_address` (
  `addr_id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `consignee` varchar(60) NOT NULL default '',
  `region_id` int(10) unsigned default NULL,
  `region_name` varchar(255) default NULL,
  `address` varchar(255) default NULL,
  `zipcode` varchar(20) default NULL,
  `phone_tel` varchar(60) default NULL,
  `phone_mob` varchar(60) default NULL,
  `setdefault` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`addr_id`),
  KEY `user_id` (`user_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_article`
--

DROP TABLE IF EXISTS `swd_article`;
CREATE TABLE `swd_article` (
  `article_id` int(10) unsigned NOT NULL auto_increment,
  `code` varchar(20) NOT NULL default '',
  `title` varchar(100) NOT NULL default '',
  `cate_id` int(10) NOT NULL default '0',
  `store_id` int(10) unsigned NOT NULL default '0',
  `link` varchar(255) default NULL,
  `content` text,
  `sort_order` tinyint(3) unsigned NOT NULL default '255',
  `if_show` tinyint(3) unsigned NOT NULL default '1',
  `add_time` int(10) unsigned default NULL,
  PRIMARY KEY  (`article_id`),
  KEY `code` (`code`),
  KEY `cate_id` (`cate_id`),
  KEY `store_id` (`store_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_attribute`
--

DROP TABLE IF EXISTS `swd_attribute`;
CREATE TABLE `swd_attribute` (
  `attr_id` int(10) unsigned NOT NULL auto_increment,
  `attr_name` varchar(60) NOT NULL default '',
  `input_mode` varchar(10) NOT NULL default 'text',
  `def_value` varchar(255) default NULL,
  PRIMARY KEY  (`attr_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_brand`
--

DROP TABLE IF EXISTS `swd_brand`;
CREATE TABLE `swd_brand` (
  `brand_id` int(10) unsigned NOT NULL auto_increment,
  `brand_name` varchar(100) NOT NULL default '',
  `brand_logo` varchar(255) default NULL,
  `sort_order` tinyint(3) unsigned NOT NULL default '255',
  `recommended` tinyint(3) unsigned NOT NULL default '0',
  `store_id` int(10) unsigned NOT NULL default '0',
  `if_show` tinyint(2) unsigned NOT NULL default '1',
  `tag` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`brand_id`),
  KEY `tag` (`tag`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_cart`
--

DROP TABLE IF EXISTS `swd_cart`;
CREATE TABLE `swd_cart` (
  `rec_id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `session_id` varchar(32) NOT NULL default '',
  `store_id` int(10) unsigned NOT NULL default '0',
  `goods_id` int(10) unsigned NOT NULL default '0',
  `goods_name` varchar(255) NOT NULL default '',
  `spec_id` int(10) unsigned NOT NULL default '0',
  `specification` varchar(255) default NULL,
  `price` decimal(10,2) unsigned NOT NULL default '0.00',
  `quantity` int(10) unsigned NOT NULL default '1',
  `goods_image` varchar(255) default NULL,
  `selected` tinyint(3) UNSIGNED NOT NULL default 0,
  PRIMARY KEY  (`rec_id`),
  KEY `session_id` (`session_id`),
  KEY `user_id` (`user_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_category_goods`
--

DROP TABLE IF EXISTS `swd_category_goods`;
CREATE TABLE `swd_category_goods` (
  `cate_id` int(10) unsigned NOT NULL default '0',
  `goods_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`cate_id`,`goods_id`),
  KEY `goods_id` (`goods_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_category_store`
--

DROP TABLE IF EXISTS `swd_category_store`;
CREATE TABLE `swd_category_store` (
  `cate_id` int(10) unsigned NOT NULL default '0',
  `store_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`cate_id`,`store_id`),
  KEY `store_id` (`store_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_collect`
--

DROP TABLE IF EXISTS `swd_collect`;
CREATE TABLE `swd_collect` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `type` varchar(10) NOT NULL default 'goods',
  `item_id` int(10) unsigned NOT NULL default '0',
  `keyword` varchar(60) default NULL,
  `add_time` int(10) unsigned default NULL,
  PRIMARY KEY  (`user_id`,`type`,`item_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_coupon`
--

DROP TABLE IF EXISTS `swd_coupon`;
CREATE TABLE `swd_coupon` (
  `coupon_id` int(10) unsigned NOT NULL auto_increment,
  `store_id` int(10) unsigned NOT NULL default '0',
  `coupon_name` varchar(100) NOT NULL default '',
  `coupon_value` decimal(10,2) unsigned NOT NULL default '0.00',
  `use_times` int(10) unsigned NOT NULL default '0',
  `start_time` int(10) unsigned NOT NULL default '0',
  `end_time` int(10) unsigned NOT NULL default '0',
  `min_amount` decimal(10,2) unsigned NOT NULL default '0.00',
  `if_issue` tinyint(3) unsigned NOT NULL default '0',
  `total` int(11) NOT NULL,
  `surplus` int(11) NOT NULL,
  `clickreceive` tinyint(3) NOT NULL DEFAULT '0',
  `image` varchar(255) NOT NULL,
  PRIMARY KEY  (`coupon_id`),
  KEY `store_id` (`store_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_coupon_sn`
--

DROP TABLE IF EXISTS `swd_coupon_sn`;
CREATE TABLE `swd_coupon_sn` (
  `coupon_sn` varchar(20) NOT NULL,
  `coupon_id` int(10) unsigned NOT NULL default '0',
  `remain_times` int(10) NOT NULL default '-1',
  PRIMARY KEY  (`coupon_sn`),
  KEY `coupon_id` (`coupon_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_friend`
--

DROP TABLE IF EXISTS `swd_friend`;
CREATE TABLE `swd_friend` (
  `owner_id` int(10) unsigned NOT NULL default '0',
  `friend_id` int(10) unsigned NOT NULL default '0',
  `add_time` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`owner_id`,`friend_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_function`
--

DROP TABLE IF EXISTS `swd_function`;
CREATE TABLE `swd_function` (
  `func_code` varchar(20) NOT NULL default '',
  `func_name` varchar(60) NOT NULL default '',
  `privileges` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`func_code`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_gcategory`
--

DROP TABLE IF EXISTS `swd_gcategory`;
CREATE TABLE `swd_gcategory` (
  `cate_id` int(10) unsigned NOT NULL auto_increment,
  `store_id` int(10) unsigned NOT NULL default '0',
  `cate_name` varchar(100) NOT NULL default '',
  `parent_id` int(10) unsigned NOT NULL default '0',
  `sort_order` tinyint(3) unsigned NOT NULL default '255',
  `if_show` tinyint(3) unsigned NOT NULL default '1',
  `groupid` tinyint(3) NOT NULL,
  `eval_tips` varchar(255) NOT NULL,
  `eval_templates` text(0) NOT NULL,
  `category_image` text(0) NOT NULL,
  PRIMARY KEY  (`cate_id`),
  KEY `store_id` (`store_id`,`parent_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_goods`
--

DROP TABLE IF EXISTS `swd_goods`;
CREATE TABLE `swd_goods` (
  `goods_id` int(10) unsigned NOT NULL auto_increment,
  `store_id` int(10) unsigned NOT NULL default '0',
  `type` varchar(10) NOT NULL default 'material',
  `goods_name` varchar(255) NOT NULL default '',
  `description` text,
  `cate_id` int(10) unsigned NOT NULL default '0',
  `cate_name` varchar(255) NOT NULL default '',
  `brand` varchar(100) NOT NULL,
  `spec_qty` tinyint(4) unsigned NOT NULL default '0',
  `spec_name_1` varchar(60) NOT NULL default '',
  `spec_name_2` varchar(60) NOT NULL default '',
  `if_show` tinyint(3) unsigned NOT NULL default '1',
  `closed` tinyint(3) unsigned NOT NULL default '0',
  `close_reason` varchar(255) default NULL,
  `add_time` int(10) unsigned NOT NULL default '0',
  `last_update` int(10) unsigned NOT NULL default '0',
  `default_spec` int(11) unsigned NOT NULL default '0',
  `default_image` varchar(255) NOT NULL default '',
  `recommended` tinyint(4) unsigned NOT NULL default '0',
  `cate_id_1` int(10) unsigned NOT NULL default '0',
  `cate_id_2` int(10) unsigned NOT NULL default '0',
  `cate_id_3` int(10) unsigned NOT NULL default '0',
  `cate_id_4` int(10) unsigned NOT NULL default '0',
  `price` decimal(10,2) NOT NULL default '0.00',
  `delivery_template_id` INT (11) NOT NULL ,
  `tags` varchar(102) NOT NULL,
  PRIMARY KEY  (`goods_id`),
  KEY `store_id` (`store_id`),
  KEY `cate_id` (`cate_id`),
  KEY `cate_id_1` (`cate_id_1`),
  KEY `cate_id_2` (`cate_id_2`),
  KEY `cate_id_3` (`cate_id_3`),
  KEY `cate_id_4` (`cate_id_4`),
  KEY `brand` (`brand`(10)),
  KEY `tags` (`tags`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_goods_attr`
--

DROP TABLE IF EXISTS `swd_goods_attr`;
CREATE TABLE `swd_goods_attr` (
  `gattr_id` int(10) unsigned NOT NULL auto_increment,
  `goods_id` int(10) unsigned NOT NULL default '0',
  `attr_name` varchar(60) NOT NULL default '',
  `attr_value` varchar(255) NOT NULL default '',
  `attr_id` int(10) unsigned default NULL,
  `sort_order` tinyint(3) unsigned default NULL,
  PRIMARY KEY  (`gattr_id`),
  KEY `goods_id` (`goods_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_goods_image`
--

DROP TABLE IF EXISTS `swd_goods_image`;
CREATE TABLE `swd_goods_image` (
  `image_id` int(10) unsigned NOT NULL auto_increment,
  `goods_id` int(10) unsigned NOT NULL default '0',
  `image_url` varchar(255) NOT NULL default '',
  `thumbnail` varchar(255) NOT NULL default '',
  `sort_order` tinyint(4) unsigned NOT NULL default '0',
  `file_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`image_id`),
  KEY `goods_id` (`goods_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_goods_qa`
--

DROP TABLE IF EXISTS `swd_goods_qa`;
CREATE TABLE `swd_goods_qa` (
  `ques_id` int(10) unsigned NOT NULL auto_increment,
  `question_content` varchar(255) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `store_id` int(10) unsigned NOT NULL,
  `email` varchar(60) NOT NULL,
  `item_id` int(10) unsigned NOT NULL default '0',
  `item_name` varchar(255) NOT NULL default '',
  `reply_content` varchar(255) NOT NULL,
  `time_post` int(10) unsigned NOT NULL,
  `time_reply` int(10) unsigned NOT NULL,
  `if_new` tinyint(3) unsigned NOT NULL default '1',
  `type` varchar(10) NOT NULL default 'goods',
  PRIMARY KEY  (`ques_id`),
  KEY `user_id` (`user_id`),
  KEY `goods_id` (`item_id`),
  KEY `store_id` (`store_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_goods_spec`
--

DROP TABLE IF EXISTS `swd_goods_spec`;
CREATE TABLE `swd_goods_spec` (
  `spec_id` int(10) unsigned NOT NULL auto_increment,
  `goods_id` int(10) unsigned NOT NULL default '0',
  `spec_1` varchar(60) NOT NULL default '',
  `spec_2` varchar(60) NOT NULL default '',
  `color_rgb` varchar(7) NOT NULL default '',
  `price` decimal(10,2) NOT NULL default '0.00',
  `stock` int(11) NOT NULL default '0',
  `sku` varchar(60) NOT NULL default '',
  `spec_image` VARCHAR( 255 ) NOT NULL,
  `sort_order` tinyint(3) unsigned DEFAULT '255',
  PRIMARY KEY  (`spec_id`),
  KEY `goods_id` (`goods_id`),
  KEY `price` (`price`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_goods_statistics`
--

DROP TABLE IF EXISTS `swd_goods_statistics`;
CREATE TABLE `swd_goods_statistics` (
  `goods_id` int(10) unsigned NOT NULL default '0',
  `views` int(10) unsigned NOT NULL default '0',
  `collects` int(10) unsigned NOT NULL default '0',
  `carts` int(10) unsigned NOT NULL default '0',
  `orders` int(10) unsigned NOT NULL default '0',
  `sales` int(10) unsigned NOT NULL default '0',
  `comments` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`goods_id`)
) TYPE=MyISAM;

--
-- 表的结构 `swd_mail_queue`
--

DROP TABLE IF EXISTS `swd_mail_queue`;
CREATE TABLE `swd_mail_queue` (
  `queue_id` int(11) unsigned NOT NULL auto_increment,
  `mail_to` varchar(150) NOT NULL default '',
  `mail_encoding` varchar(50) NOT NULL default '',
  `mail_subject` varchar(255) NOT NULL default '',
  `mail_body` text NOT NULL,
  `priority` tinyint(3) unsigned NOT NULL default '2',
  `err_num` tinyint(3) unsigned NOT NULL default '0',
  `add_time` int(11) NOT NULL default '0',
  `lock_expiry` int(11) NOT NULL default '0',
  PRIMARY KEY  (`queue_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_member`
--

DROP TABLE IF EXISTS `swd_member`;
CREATE TABLE `swd_member` (
  `user_id` int(10) unsigned NOT NULL auto_increment,
  `user_name` varchar(60) NOT NULL default '',
  `email` varchar(60) NOT NULL default '',
  `password` varchar(32) NOT NULL default '',
  `real_name` varchar(60) default NULL,
  `gender` tinyint(3) unsigned NOT NULL default '0',
  `birthday` date default NULL,
  `phone_tel` varchar(60) default NULL,
  `phone_mob` varchar(60) default NULL,
  `im_qq` varchar(60) default NULL,
  `im_skype` varchar(60) default NULL,
  `im_yahoo` varchar(60) default NULL,
  `im_aliww` varchar(60) default NULL,
  `reg_time` int(10) unsigned default '0',
  `last_login` int(10) unsigned default NULL,
  `last_ip` varchar(15) default NULL,
  `logins` int(10) unsigned NOT NULL default '0',
  `ugrade` tinyint(3) unsigned NOT NULL default '0',
  `portrait` varchar(255) default NULL,
  `outer_id` int(10) unsigned NOT NULL default '0',
  `activation` varchar(60) default NULL,
  `feed_config` text NOT NULL,
  `locked` tinyint(3) unsigned NOT NULL default '0',
  `imforbid` int(1) NOT NULL default 0,
  PRIMARY KEY  (`user_id`),
  KEY `user_name` (`user_name`),
  KEY `email` (`email`),
  KEY `outer_id` (`outer_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_message`
--

DROP TABLE IF EXISTS `swd_message`;
CREATE TABLE `swd_message` (
  `msg_id` int(10) unsigned NOT NULL auto_increment,
  `from_id` int(10) unsigned NOT NULL default '0',
  `to_id` int(10) unsigned NOT NULL default '0',
  `title` varchar(100) NOT NULL default '',
  `content` text NOT NULL,
  `add_time` int(10) unsigned NOT NULL default '0',
  `last_update` int(10) unsigned NOT NULL default '0',
  `new` tinyint(3) unsigned NOT NULL default '0',
  `parent_id` int(10) unsigned NOT NULL default '0',
  `status` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`msg_id`),
  KEY `from_id` (`from_id`),
  KEY `to_id` (`to_id`),
  KEY `parent_id` (`parent_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_module`
--

DROP TABLE IF EXISTS `swd_module`;
CREATE TABLE `swd_module` (
  `module_id` varchar(30) NOT NULL default '',
  `module_name` varchar(100) NOT NULL default '',
  `module_version` varchar(5) NOT NULL default '',
  `module_desc` text NOT NULL,
  `module_config` text NOT NULL,
  `enabled` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`module_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_navigation`
--

DROP TABLE IF EXISTS `swd_navigation`;
CREATE TABLE `swd_navigation` (
  `nav_id` int(10) unsigned NOT NULL auto_increment,
  `type` varchar(10) NOT NULL default '',
  `title` varchar(60) NOT NULL default '',
  `link` varchar(255) NOT NULL default '',
  `sort_order` tinyint(3) unsigned NOT NULL default '255',
  `open_new` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`nav_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_order`
--

DROP TABLE IF EXISTS `swd_order`;
CREATE TABLE `swd_order` (
  `order_id` int(10) unsigned NOT NULL auto_increment,
  `order_sn` varchar(20) NOT NULL default '',
  `type` varchar(10) NOT NULL default 'material',
  `extension` varchar(10) NOT NULL default '',
  `seller_id` int(10) unsigned NOT NULL default '0',
  `seller_name` varchar(100) default NULL,
  `buyer_id` int(10) unsigned NOT NULL default '0',
  `buyer_name` varchar(100) default NULL,
  `buyer_email` varchar(60) NOT NULL default '',
  `status` tinyint(3) unsigned NOT NULL default '0',
  `add_time` int(10) unsigned NOT NULL default '0',
  `payment_id` int(10) unsigned default NULL,
  `payment_name` varchar(100) default NULL,
  `payment_code` varchar(20) NOT NULL default '',
  `out_trade_sn` varchar(20) NOT NULL default '',
  `pay_time` int(10) unsigned default NULL,
  `pay_message` varchar(255) NOT NULL default '',
  `ship_time` int(10) unsigned default NULL,
  `invoice_no` varchar(255) default NULL,
  `finished_time` int(10) unsigned NOT NULL default '0',
  `goods_amount` decimal(10,2) unsigned NOT NULL default '0.00',
  `discount` decimal(10,2) unsigned NOT NULL default '0.00',
  `order_amount` decimal(10,2) unsigned NOT NULL default '0.00',
  `evaluation_status` tinyint(3) unsigned NOT NULL default '0',
  `evaluation_time` int(10) unsigned NOT NULL default '0',
  `anonymous` tinyint(3) unsigned NOT NULL default '0',
  `postscript` varchar(255) NOT NULL default '',
  `pay_alter` tinyint(3) unsigned NOT NULL default '0',
  `express_company` VARCHAR( 50 ) NOT NULL,
  `checkout` int(1) NOT NULL default '0',
  `checkout_time` int(11) NOT NULL default '0',
  `adjust_amount` decimal(10,2) NOT NULL default '0',
  `flag` int( 1 ) NOT NULL,
  `memo` varchar( 255 ) NOT NULL,
  `did` int(11) DEFAULT NULL,
  `distribution_rate` TEXT,
  PRIMARY KEY  (`order_id`),
  KEY `order_sn` (`order_sn`,`seller_id`),
  KEY `seller_name` (`seller_name`),
  KEY `buyer_name` (`buyer_name`),
  KEY `add_time` (`add_time`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_order_extm`
--

DROP TABLE IF EXISTS `swd_order_extm`;
CREATE TABLE `swd_order_extm` (
  `order_id` int(10) unsigned NOT NULL default '0',
  `consignee` varchar(60) NOT NULL default '',
  `region_id` int(10) unsigned default NULL,
  `region_name` varchar(255) default NULL,
  `address` varchar(255) default NULL,
  `zipcode` varchar(20) default NULL,
  `phone_tel` varchar(60) default NULL,
  `phone_mob` varchar(60) default NULL,
  `shipping_id` int(10) unsigned default NULL,
  `shipping_name` varchar(100) default NULL,
  `shipping_fee` decimal(10,2) NOT NULL default '0.00',
  PRIMARY KEY  (`order_id`),
  KEY `consignee` (`consignee`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_order_goods`
--

DROP TABLE IF EXISTS `swd_order_goods`;
CREATE TABLE `swd_order_goods` (
  `rec_id` int(10) unsigned NOT NULL auto_increment,
  `order_id` int(10) unsigned NOT NULL default '0',
  `goods_id` int(10) unsigned NOT NULL default '0',
  `goods_name` varchar(255) NOT NULL default '',
  `spec_id` int(10) unsigned NOT NULL default '0',
  `specification` varchar(255) default NULL,
  `price` decimal(10,2) unsigned NOT NULL default '0.00',
  `quantity` int(10) unsigned NOT NULL default '1',
  `goods_image` varchar(255) default NULL,
  `evaluation` tinyint(3) unsigned NOT NULL default '0',
  `comment` varchar(255) NOT NULL default '',
  `credit_value` tinyint(3) NOT NULL default '0',
  `is_valid` tinyint(3) unsigned NOT NULL default '1',
  `reply_content` TEXT NOT NULL,
  `reply_time` INT(10) NOT NULL,
  `shipped_evaluation` decimal(4,2) NOT NULL  default '5',
  `service_evaluation` decimal(4,2) NOT NULL  default '5',
  `goods_evaluation` decimal(4,2) NOT NULL  default '5',
  `tips` varchar(255) NOT NULL ,
  `share_images` text NOT NULL ,
  `status` varchar(50) NOT NULL,
  PRIMARY KEY  (`rec_id`),
  KEY `order_id` (`order_id`,`goods_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_order_log`
--

DROP TABLE IF EXISTS `swd_order_log`;
CREATE TABLE `swd_order_log` (
  `log_id` int(10) unsigned NOT NULL auto_increment,
  `order_id` int(10) unsigned NOT NULL default '0',
  `operator` varchar(60) NOT NULL default '',
  `order_status` varchar(60) NOT NULL default '',
  `changed_status` varchar(60) NOT NULL default '',
  `remark` varchar(255) default NULL,
  `log_time` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`log_id`),
  KEY `order_id` (`order_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_pageview`
--

DROP TABLE IF EXISTS `swd_pageview`;
CREATE TABLE `swd_pageview` (
  `rec_id` int(10) unsigned NOT NULL auto_increment,
  `store_id` int(10) unsigned NOT NULL default '0',
  `view_date` date NOT NULL default '0000-00-00',
  `view_times` int(10) unsigned NOT NULL default '1',
  PRIMARY KEY  (`rec_id`),
  UNIQUE KEY `storedate` (`store_id`,`view_date`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_partner`
--

DROP TABLE IF EXISTS `swd_partner`;
CREATE TABLE `swd_partner` (
  `partner_id` int(10) unsigned NOT NULL auto_increment,
  `store_id` int(10) unsigned NOT NULL default '0',
  `title` varchar(100) NOT NULL default '',
  `link` varchar(255) NOT NULL default '',
  `logo` varchar(255) default NULL,
  `sort_order` tinyint(3) unsigned NOT NULL default '255',
  `if_show` int(1) NOT NULL,
  PRIMARY KEY  (`partner_id`),
  KEY `store_id` (`store_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_payment`
--

DROP TABLE IF EXISTS `swd_payment`;
CREATE TABLE `swd_payment` (
  `payment_id` int(10) unsigned NOT NULL auto_increment,
  `store_id` int(10) unsigned NOT NULL default '0',
  `payment_code` varchar(20) NOT NULL default '',
  `payment_name` varchar(100) NOT NULL default '',
  `payment_desc` varchar(255) default NULL,
  `config` text,
  `is_online` tinyint(3) unsigned NOT NULL default '1',
  `enabled` tinyint(3) unsigned NOT NULL default '1',
  `sort_order` tinyint(3) unsigned NOT NULL default '255',
  `cod_regions` TEXT NOT NULL,
  PRIMARY KEY  (`payment_id`),
  KEY `store_id` (`store_id`),
  KEY `payment_code` (`payment_code`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_privilege`
--

DROP TABLE IF EXISTS `swd_privilege`;
CREATE TABLE `swd_privilege` (
  `priv_code` varchar(20) NOT NULL default '',
  `priv_name` varchar(60) NOT NULL default '',
  `parent_code` varchar(20) default NULL,
  `owner` varchar(10) NOT NULL default 'mall',
  PRIMARY KEY  (`priv_code`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_recommend`
--

DROP TABLE IF EXISTS `swd_recommend`;
CREATE TABLE `swd_recommend` (
  `recom_id` int(10) unsigned NOT NULL auto_increment,
  `recom_name` varchar(100) NOT NULL default '',
  `store_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`recom_id`),
  KEY `store_id` (`store_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_recommended_goods`
--

DROP TABLE IF EXISTS `swd_recommended_goods`;
CREATE TABLE `swd_recommended_goods` (
  `recom_id` int(10) unsigned NOT NULL default '0',
  `goods_id` int(10) unsigned NOT NULL default '0',
  `sort_order` tinyint(3) unsigned NOT NULL default '255',
  PRIMARY KEY  (`recom_id`,`goods_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_region`
--

DROP TABLE IF EXISTS `swd_region`;
CREATE TABLE `swd_region` (
  `region_id` int(10) unsigned NOT NULL auto_increment,
  `region_name` varchar(100) NOT NULL default '',
  `parent_id` int(10) unsigned NOT NULL default '0',
  `sort_order` tinyint(3) unsigned NOT NULL default '255',
  PRIMARY KEY  (`region_id`),
  KEY `parent_id` (`parent_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_scategory`
--

DROP TABLE IF EXISTS `swd_scategory`;
CREATE TABLE `swd_scategory` (
  `cate_id` int(10) unsigned NOT NULL auto_increment,
  `cate_name` varchar(100) NOT NULL default '',
  `parent_id` int(10) unsigned NOT NULL default '0',
  `sort_order` tinyint(3) unsigned NOT NULL default '255',
  PRIMARY KEY  (`cate_id`),
  KEY `parent_id` (`parent_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_sessions`
--

DROP TABLE IF EXISTS `swd_sessions`;
CREATE TABLE `swd_sessions` (
  `sesskey` char(32) NOT NULL default '',
  `expiry` int(11) NOT NULL default '0',
  `userid` int(11) NOT NULL default '0',
  `adminid` int(11) NOT NULL default '0',
  `ip` char(15) NOT NULL default '',
  `data` char(255) NOT NULL default '',
  `is_overflow` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`sesskey`),
  KEY `expiry` (`expiry`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_sessions_data`
--

DROP TABLE IF EXISTS `swd_sessions_data`;
CREATE TABLE `swd_sessions_data` (
  `sesskey` varchar(32) NOT NULL default '',
  `expiry` int(11) NOT NULL default '0',
  `data` longtext NOT NULL,
  PRIMARY KEY  (`sesskey`),
  KEY `expiry` (`expiry`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_sgrade`
--

DROP TABLE IF EXISTS `swd_sgrade`;
CREATE TABLE `swd_sgrade` (
  `grade_id` tinyint(3) unsigned NOT NULL auto_increment,
  `grade_name` varchar(60) NOT NULL default '',
  `goods_limit` int(10) unsigned NOT NULL default '0',
  `space_limit` int(10) unsigned NOT NULL default '0',
  `skin_limit` int(10) unsigned NOT NULL default '0',
  `charge` varchar(100) NOT NULL default '',
  `need_confirm` tinyint(3) unsigned NOT NULL default '0',
  `description` varchar(255) NOT NULL default '',
  `functions` varchar(255) default NULL,
  `skins` text NOT NULL,
  `wap_skins` VARCHAR(255) NOT NULL,
  `wap_skin_limit` INT(3) NOT NULL,
  `sort_order` tinyint(4) unsigned NOT NULL default '0',
  PRIMARY KEY  (`grade_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_shipping`
--

DROP TABLE IF EXISTS `swd_shipping`;
CREATE TABLE `swd_shipping` (
  `shipping_id` int(10) unsigned NOT NULL auto_increment,
  `store_id` int(10) unsigned NOT NULL default '0',
  `shipping_name` varchar(100) NOT NULL default '',
  `shipping_desc` varchar(255) default NULL,
  `first_price` decimal(10,2) NOT NULL default '0.00',
  `step_price` decimal(10,2) NOT NULL default '0.00',
  `cod_regions` text,
  `enabled` tinyint(3) unsigned NOT NULL default '1',
  `sort_order` tinyint(3) unsigned NOT NULL default '255',
  PRIMARY KEY  (`shipping_id`),
  KEY `store_id` (`store_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_store`
--

DROP TABLE IF EXISTS `swd_store`;
CREATE TABLE `swd_store` (
  `store_id` int(10) unsigned NOT NULL default '0',
  `store_name` varchar(100) NOT NULL default '',
  `owner_name` varchar(60) NOT NULL default '',
  `owner_card` varchar(60) NOT NULL default '',
  `region_id` int(10) unsigned default NULL,
  `region_name` varchar(100) default NULL,
  `address` varchar(255) NOT NULL default '',
  `zipcode` varchar(20) NOT NULL default '',
  `tel` varchar(60) NOT NULL default '',
  `sgrade` tinyint(3) unsigned NOT NULL default '0',
  `apply_remark` varchar(255) NOT NULL default '',
  `credit_value` int(10) NOT NULL default '0',
  `praise_rate` decimal(5,2) unsigned NOT NULL default '0.00',
  `domain` varchar(60) default NULL,
  `state` tinyint(3) unsigned NOT NULL default '0',
  `close_reason` varchar(255) NOT NULL default '',
  `add_time` int(10) unsigned default NULL,
  `end_time` int(10) unsigned NOT NULL default '0',
  `certification` varchar(255) default NULL,
  `sort_order` smallint(5) unsigned NOT NULL default '0',
  `recommended` tinyint(4) NOT NULL default '0',
  `theme` varchar(60) NOT NULL default '',
  `store_banner` varchar(255) default NULL,
  `store_logo` varchar(255) default NULL,
  `description` text,
  `image_1` varchar(255) NOT NULL default '',
  `image_2` varchar(255) NOT NULL default '',
  `image_3` varchar(255) NOT NULL default '',
  `im_qq` varchar(60) NOT NULL default '',
  `im_ww` varchar(60) NOT NULL default '',
  `store_slides` TEXT NOT NULL,
  `wap_store_slides` TEXT NOT NULL,
  `wap_store_banner` TEXT NOT NULL,
  `wap_theme` VARCHAR(255) NOT NULL,
  `business_scope` VARCHAR( 50 ) NOT NULL,
  `avg_goods_evaluation` decimal(8,2)  NOT NULL default '5',
  `avg_service_evaluation` decimal(8,2) NOT NULL default '5',
  `avg_shipped_evaluation` decimal(8,2) NOT NULL default '5',
  `lat` varchar(100) NOT NULL,
  `lng` varchar(100) NOT NULL,
  `zoom` varchar(10) NOT NULL,
  `nav_color` varchar(10) NOT NULL,
  `enable_distribution` tinyint(3) DEFAULT NULL,
  `distribution_1` decimal(10,1) DEFAULT NULL,
  `distribution_2` decimal(10,1) DEFAULT NULL,
  `distribution_3` decimal(10,1) DEFAULT NULL,
  PRIMARY KEY  (`store_id`),
  KEY `store_name` (`store_name`),
  KEY `owner_name` (`owner_name`),
  KEY `region_id` (`region_id`),
  KEY `domain` (`domain`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_uploaded_file`
--

DROP TABLE IF EXISTS `swd_uploaded_file`;
CREATE TABLE `swd_uploaded_file` (
  `file_id` int(10) unsigned NOT NULL auto_increment,
  `store_id` int(10) unsigned NOT NULL default '0',
  `file_type` varchar(60) NOT NULL default '',
  `file_size` int(10) unsigned NOT NULL default '0',
  `file_name` varchar(255) NOT NULL default '',
  `file_path` varchar(255) NOT NULL default '',
  `add_time` int(10) unsigned NOT NULL default '0',
  `belong` tinyint(3) unsigned NOT NULL default '0',
  `item_id` int(10) unsigned NOT NULL default '0',
  `link_url` VARCHAR( 100 ) NOT NULL,
  PRIMARY KEY  (`file_id`),
  KEY `store_id` (`store_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_user_coupon`
--

DROP TABLE IF EXISTS `swd_user_coupon`;
CREATE TABLE `swd_user_coupon` (
  `user_id` int(10) unsigned NOT NULL,
  `coupon_sn` varchar(20) NOT NULL,
  PRIMARY KEY  (`user_id`,`coupon_sn`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_user_priv`
--

DROP TABLE IF EXISTS `swd_user_priv`;
CREATE TABLE `swd_user_priv` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `store_id` int(10) unsigned NOT NULL default '0',
  `privs` text NOT NULL,
  PRIMARY KEY  (`user_id`,`store_id`)
) TYPE=MyISAM;


-- --------------------------------------------------------

--
-- 表的结构 `swd_ultimate_store`
--

DROP TABLE IF EXISTS `swd_ultimate_store`;
CREATE TABLE `swd_ultimate_store` (
  `ultimate_id` int(255) NOT NULL AUTO_INCREMENT,
  `brand_id` int(50) NOT NULL,
  `keyword` varchar(20) NOT NULL,
  `cate_id` int(50) NOT NULL,
  `store_id` int(50) NOT NULL,
  `status` tinyint(3) NOT NULL DEFAULT '0',
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY  (`ultimate_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_limitbuy`
--

DROP TABLE IF EXISTS `swd_limitbuy`;
CREATE TABLE `swd_limitbuy` (
	`pro_id` int(11) NOT NULL auto_increment,
  	`goods_id` int(11) NOT NULL,
  	`pro_name` varchar(50) NOT NULL,
  	`pro_desc` varchar(255) NOT NULL,
  	`start_time` int(11) NOT NULL,
  	`end_time` int(11) NOT NULL,
  	`store_id` int(11) NOT NULL,
  	`spec_price` text NOT NULL,
	`image` VARCHAR( 255 ) NOT NULL,
  	PRIMARY KEY  (`pro_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_member_bind`
--

DROP TABLE IF EXISTS `swd_member_bind`;
CREATE TABLE `swd_member_bind` (
	`id` int(11) NOT NULL auto_increment,
	`unionid` varchar(255) NOT NULL,
  	`openid` varchar(255) NOT NULL,
  	`user_id` int(11) NOT NULL,
  	`app` varchar(50) NOT NULL,
	`token` varchar(255) NOT NULL,
	`nickname` varchar(60) NOT NULL,
	`enabled` int(1) NOT NULL default 0,
	`locked` int(1) NOT NULL default 0,
	PRIMARY KEY  (`id`)
) TYPE=MyISAM;


-- --------------------------------------------------------

--
-- 表的结构 `swd_cate_pvs`
--

DROP TABLE IF EXISTS `swd_cate_pvs`;
CREATE TABLE `swd_cate_pvs` (
	`cate_id` int(11) NOT NULL,
  	`pvs` text NOT NULL,
	PRIMARY KEY  (`cate_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_goods_prop`
--

DROP TABLE IF EXISTS `swd_goods_prop`;
CREATE TABLE `swd_goods_prop` (
	`pid` int(11) NOT NULL auto_increment,
  	`name` varchar(50) NOT NULL,
	`prop_type` VARCHAR( 20 ) NOT NULL DEFAULT 'select',
	`is_color_prop` INT NOT NULL DEFAULT '0',
  	`status` int(1) NOT NULL,
  	`sort_order` int(11) NOT NULL,
  	PRIMARY KEY  (`pid`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_goods_prop_value`
--

DROP TABLE IF EXISTS `swd_goods_prop_value`;
CREATE TABLE `swd_goods_prop_value` (
	`vid` int(11) NOT NULL auto_increment,
  	`pid` int(11) NOT NULL,
  	`prop_value` varchar(255) NOT NULL,
	`color_value` VARCHAR( 255 ) NOT NULL,
  	`status` int(1) NOT NULL,
  	`sort_order` int(11) NOT NULL,
  	PRIMARY KEY  (`vid`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_goods_pvs`
--

DROP TABLE IF EXISTS `swd_goods_pvs`;
CREATE TABLE `swd_goods_pvs` (
	`goods_id` int(11) NOT NULL,
  	`pvs` text NOT NULL,
  	PRIMARY KEY  (`goods_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_deposit_account`
--

DROP TABLE IF EXISTS `swd_deposit_account`;
CREATE TABLE `swd_deposit_account` (
	`account_id` int(11) NOT NULL AUTO_INCREMENT,
  	`user_id` int(11) NOT NULL,
  	`account` varchar(100) NOT NULL,
  	`password` varchar(255) NOT NULL,
  	`money` decimal(10,2) NOT NULL,
  	`frozen` decimal(10,2) NOT NULL,
  	`real_name` varchar(30) NOT NULL,
  	`pay_status` varchar(3) NOT NULL DEFAULT 'off',
  	`add_time` int(11) NOT NULL,
  	`last_update` int(11) NOT NULL,
  	PRIMARY KEY (`account_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_deposit_recharge`
--

DROP TABLE IF EXISTS `swd_deposit_recharge`;
CREATE TABLE `swd_deposit_recharge` (
	`recharge_id` int(11) NOT NULL AUTO_INCREMENT,
  	`orderId` varchar(30) NOT NULL,
  	`user_id` int(11) NOT NULL,
	`examine` varchar(100) NOT NULL,
  	`is_online` int(1) NOT NULL,
  	PRIMARY KEY (`recharge_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_deposit_trade`
--

DROP TABLE IF EXISTS `swd_deposit_trade`;
CREATE TABLE `swd_deposit_trade` (
	`trade_id` int(11) NOT NULL AUTO_INCREMENT,
  	`tradeNo` varchar(32) NOT NULL COMMENT '支付交易号',
	`outTradeNo` varchar(32) NOT NULL COMMENT '第三方支付接口的交易号',
	`payTradeNo` varchar(32) NOT NULL COMMENT '第三方支付接口的商户订单号',
	`merchantId` varchar(32) NOT NULL COMMENT '商户号',
  	`bizOrderId` varchar(32) NOT NULL COMMENT '商户订单号',
	`bizIdentity` varchar(20) NOT NULL COMMENT '商户交易类型识别号',
  	`buyer_id` int(11) NOT NULL COMMENT '交易买家',
	`seller_id` int(11) NOT NULL COMMENT '交易卖家',
  	`amount` decimal(10,2) NOT NULL COMMENT '交易金额',
	`status` varchar(30) NOT NULL,
	`payment_code` varchar(20) NOT NULL COMMENT '支付方式代号',
	`payment_bank` varchar(20) NOT NULL COMMENT '网银支付代号',
	`pay_alter` int(11) NOT NULL COMMENT '支付方式变更标记',
	`tradeCat` varchar(20) NOT NULL COMMENT '交易分类',
	`payType` varchar(20) NOT NULL COMMENT '支付类型(担保即时)',
	`flow` varchar(10) NOT NULL COMMENT '资金流向',
	`fundchannel` varchar(20) NOT NULL COMMENT '资金渠道',
	`payTerminal` varchar(10) NOT NULL COMMENT '支付终端',
	`title` varchar(100) NOT NULL COMMENT '交易标题',
  	`buyer_remark` varchar(255) NOT NULL COMMENT '买家备注',
	`seller_remark` varchar(255) NOT NULL COMMENT '卖家备注',
	`add_time` int(11) NOT NULL,
  	`pay_time` int(11) NOT NULL,
  	`end_time` int(11) NOT NULL,
  	PRIMARY KEY (`trade_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_deposit_record`
--

DROP TABLE IF EXISTS `swd_deposit_record`;
CREATE TABLE `swd_deposit_record` (
	`record_id` int(11) NOT NULL AUTO_INCREMENT,
  	`tradeNo` varchar(30) NOT NULL,
  	`user_id` int(11) NOT NULL,
	`amount` decimal(10,2) NOT NULL COMMENT '收支金额',
  	`balance` decimal(10,2) NOT NULL COMMENT '账户余额',
	`flow` varchar(10) NOT NULL COMMENT '收支',
	`tradeType` varchar(20) NOT NULL COMMENT '交易类型',
	`tradeTypeName` varchar(20) NOT NULL COMMENT '交易类型名称',
	`name` varchar(100) NOT NULL COMMENT '名称',
	`remark` varchar(255) NOT NULL COMMENT '备注',
  	PRIMARY KEY (`record_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_deposit_setting`
--

DROP TABLE IF EXISTS `swd_deposit_setting`;
CREATE TABLE `swd_deposit_setting` (
	`setting_id` int(11) NOT NULL AUTO_INCREMENT,
  	`user_id` int(11) NOT NULL,
  	`trade_rate` decimal(10,3) NOT NULL COMMENT '交易手续费',
  	`transfer_rate` decimal(10,3) NOT NULL,
	`auto_create_account` int(1)  NOT NULL,	
  	PRIMARY KEY (`setting_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_deposit_withdraw`
--

DROP TABLE IF EXISTS `swd_deposit_withdraw`;
CREATE TABLE `swd_deposit_withdraw` (
	`withdraw_id` int(11) NOT NULL AUTO_INCREMENT,
  	`orderId` varchar(30) NOT NULL,
  	`user_id` int(11) NOT NULL,
  	`card_info` text NOT NULL,
  	PRIMARY KEY (`withdraw_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_bank`
--

DROP TABLE IF EXISTS `swd_bank`;
CREATE TABLE `swd_bank` (
	`bid` int(11) NOT NULL AUTO_INCREMENT,
  	`user_id` int(11) NOT NULL,
  	`bank_name` varchar(100) NOT NULL,
  	`short_name` varchar(20) NOT NULL,
  	`account_name` varchar(20) NOT NULL,
  	`open_bank` varchar(100) NOT NULL,
  	`type` varchar(10) NOT NULL,
  	`num` varchar(50) NOT NULL,
	PRIMARY KEY (`bid`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_bank`
--

DROP TABLE IF EXISTS `swd_bank`;
CREATE TABLE `swd_bank` (
	`bid` int(11) NOT NULL AUTO_INCREMENT,
  	`user_id` int(11) NOT NULL,
  	`bank_name` varchar(100) NOT NULL,
  	`short_name` varchar(20) NOT NULL,
  	`account_name` varchar(20) NOT NULL,
  	`open_bank` varchar(100) NOT NULL,
  	`type` varchar(10) NOT NULL,
  	`num` varchar(50) NOT NULL,
	PRIMARY KEY (`bid`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_refund`
--

DROP TABLE IF EXISTS `swd_refund`;
CREATE TABLE `swd_refund` (
	`refund_id` int(11) NOT NULL AUTO_INCREMENT,
	`tradeNo` varchar(30) NOT NULL,
  	`refund_sn` varchar(30) NOT NULL,
	`title` varchar(255) NOT NULL,
  	`refund_reason` varchar(50) NOT NULL,
  	`refund_desc` varchar(255) NOT NULL,
  	`total_fee` decimal(10,2) NOT NULL,
  	`goods_fee` decimal(10,2) NOT NULL,
  	`shipping_fee` decimal(10,2) NOT NULL,
	`refund_total_fee` decimal(10,2) NOT NULL,
 	`refund_goods_fee` decimal(10,2) NOT NULL,
  	`refund_shipping_fee` decimal(10,2) NOT NULL,
  	`buyer_id` int(10) NOT NULL,
  	`seller_id` int(10) NOT NULL,
  	`status` varchar(100) NOT NULL DEFAULT '',
  	`shipped` int(11) NOT NULL,
  	`ask_customer` int(1) NOT NULL DEFAULT '0',
  	`created` int(11) NOT NULL,
  	`end_time` int(11) NOT NULL,
  	PRIMARY KEY (`refund_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_refund_message`
--

DROP TABLE IF EXISTS `swd_refund_message`;
CREATE TABLE `swd_refund_message` (
	`rm_id` int(11) NOT NULL AUTO_INCREMENT,
  	`owner_id` int(11) NOT NULL,
  	`owner_role` varchar(10) NOT NULL,
  	`refund_id` int(11) NOT NULL,
  	`content` varchar(255) DEFAULT NULL,
  	`pic_url` varchar(255) DEFAULT NULL,
  	`created` int(11) NOT NULL,
  	PRIMARY KEY (`rm_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_refund_message`
--

DROP TABLE IF EXISTS `swd_refund_message`;
CREATE TABLE `swd_refund_message` (
	`rm_id` int(11) NOT NULL AUTO_INCREMENT,
  	`owner_id` int(11) NOT NULL,
  	`owner_role` varchar(10) NOT NULL,
  	`refund_id` int(11) NOT NULL,
  	`content` varchar(255) DEFAULT NULL,
  	`pic_url` varchar(255) DEFAULT NULL,
  	`created` int(11) NOT NULL,
  	PRIMARY KEY (`rm_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_delivery_template`
--

DROP TABLE IF EXISTS `swd_delivery_template`;
CREATE TABLE `swd_delivery_template` (
	`template_id` int(11) NOT NULL AUTO_INCREMENT,
  	`name` varchar(50) NOT NULL,
  	`store_id` int(10) NOT NULL,
  	`template_types` text NOT NULL,
  	`template_dests` text NOT NULL,
  	`template_start_standards` text NOT NULL,
  	`template_start_fees` text NOT NULL,
  	`template_add_standards` text NOT NULL,
  	`template_add_fees` text NOT NULL,
  	`created` int(10) NOT NULL,
  	PRIMARY KEY (`template_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_goods_integral`
--

DROP TABLE IF EXISTS `swd_goods_integral`;
CREATE TABLE `swd_goods_integral` (
	`goods_id` int(11) NOT NULL,
  	`max_exchange` int(11) NOT NULL,
  	PRIMARY KEY  (`goods_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_integral`
--

DROP TABLE IF EXISTS `swd_integral`;
CREATE TABLE `swd_integral` (
	`user_id` int(11) NOT NULL,
	`amount` decimal(10,2) NOT NULL,
	PRIMARY KEY  (`user_id`)
) TYPE=MyISAM;


-- --------------------------------------------------------

--
-- 表的结构 `swd_integral_log`
--

DROP TABLE IF EXISTS `swd_integral_log`;
CREATE TABLE `swd_integral_log` (
	`log_id` int(11) NOT NULL AUTO_INCREMENT,
	`user_id` int(10) NOT NULL,
	`order_id` int(10) NOT NULL DEFAULT '0',
	`order_sn` varchar(20) NOT NULL,
	`changes` decimal(25,2) NOT NULL,
	`balance` decimal(25,2) NOT NULL,
	`type` varchar(50) NOT NULL,
	`state` varchar(50) NOT NULL,
	`flag` varchar(255) NOT NULL ,
	`add_time` int(11) NOT NULL,
	PRIMARY KEY (`log_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_order_integral`
--

DROP TABLE IF EXISTS `swd_order_integral`;
CREATE TABLE `swd_order_integral` (
	`order_id` int(11) NOT NULL,
	`buyer_id` int(11) NOT NULL,
	`frozen_integral` decimal(10,2) NOT NULL,
	PRIMARY KEY  (`order_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_msg`
--

DROP TABLE IF EXISTS `swd_msg`;
CREATE TABLE `swd_msg` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  	`user_id` int(10) unsigned NOT NULL DEFAULT '0',
  	`num` int(10) unsigned NOT NULL DEFAULT '0',
  	`functions` varchar(255) DEFAULT NULL,
  	`state` tinyint(3) unsigned NOT NULL DEFAULT '0',
  	PRIMARY KEY (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_msg_log`
--

DROP TABLE IF EXISTS `swd_msg_log`;
CREATE TABLE `swd_msg_log` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  	`user_id` int(10) unsigned NOT NULL DEFAULT '0',
  	`to_mobile` varchar(100) DEFAULT NULL,
  	`content` text DEFAULT NULL,
 	`quantity` tinyint(3) unsigned NOT NULL DEFAULT '0',
  	`state` tinyint(3) unsigned NOT NULL DEFAULT '0',
  	`result` varchar(50) DEFAULT NULL,
  	`type` int(10) unsigned NULL DEFAULT '0',
  	`time` int(10) unsigned DEFAULT NULL,
  	PRIMARY KEY (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_msg_setting`
--

DROP TABLE IF EXISTS `swd_msg_setting`;
CREATE TABLE `swd_msg_setting` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`msg_pid` varchar(60) NOT NULL DEFAULT '0',
	`msg_key` varchar(50) NOT NULL DEFAULT '0',
	`msg_status` varchar(255) DEFAULT NULL,
	PRIMARY KEY (`id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_msg_statistics`
--

DROP TABLE IF EXISTS `swd_msg_statistics`;
CREATE TABLE `swd_msg_statistics` (
	`user_id` int(10) unsigned NOT NULL DEFAULT '0',
  	`available` int(10) unsigned NOT NULL DEFAULT '0',
  	`used` int(10) unsigned NOT NULL DEFAULT '0',
  	`allocated` int(10) unsigned NOT NULL DEFAULT '0',
  	PRIMARY KEY (`user_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_meal`
--

DROP TABLE IF EXISTS `swd_meal`;
CREATE TABLE `swd_meal` (
	`meal_id` int(11) NOT NULL auto_increment,
  	`user_id` int(11) NOT NULL,
  	`title` varchar(255) NOT NULL,
  	`price` decimal(10,2) NOT NULL,
  	`description` text NOT NULL,
  	`status` int(1) NOT NULL,
  	PRIMARY KEY  (`meal_id`)
) TYPE=MyISAM;


-- --------------------------------------------------------

--
-- 表的结构 `swd_meal_goods`
--

DROP TABLE IF EXISTS `swd_meal_goods`;
CREATE TABLE `swd_meal_goods` (
	`mg_id` int(11) NOT NULL auto_increment,
  	`meal_id` int(11) NOT NULL,
  	`goods_id` int(11) NOT NULL,
  	`goods_name` varchar(255) NOT NULL,
  	`sort_order` int(3) NOT NULL,
  	PRIMARY KEY  (`mg_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_appmarket`
--

DROP TABLE IF EXISTS `swd_appmarket`;
CREATE TABLE `swd_appmarket` (
	`aid` int(11) NOT NULL AUTO_INCREMENT,
	`appid` varchar(20) NOT NULL,
	`title` varchar(100) NOT NULL,
	`summary` varchar(255) NOT NULL,
	`category` int(11) NOT NULL,
	`description` TEXT NOT NULL,
	`logo` varchar(200) NOT NULL,
	  `config` TEXT NOT NULL,
	`sales` int(11) NOT NULL DEFAULT '0',
	`views` int(11) NOT NULL DEFAULT '0',
	  `status` tinyint(3) NOT NULL DEFAULT '0',
	`add_time` int(11) NOT NULL,
	  PRIMARY KEY (`aid`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_appbuylog`
--

DROP TABLE IF EXISTS `swd_appbuylog`;
CREATE TABLE `swd_appbuylog` (
	`bid` int(11) NOT NULL AUTO_INCREMENT,
	`orderId` varchar(20) NOT NULL,
	`appid` varchar(20) NOT NULL,
	`user_id` int(11) NOT NULL,
	`period` int(11) NOT NULL,
	`amount` decimal(10,2) NOT NULL,
	`status` tinyint(3) NOT NULL,
	`add_time` int(11) NOT NULL,
	`pay_time` int(11) NOT NULL,
	`end_time` int(11) NOT NULL,
	 PRIMARY KEY (`bid`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_apprenewal`
--

DROP TABLE IF EXISTS `swd_apprenewal`;
CREATE TABLE `swd_apprenewal` (
	`rid` int(11) NOT NULL AUTO_INCREMENT,
	`appid` varchar(20) NOT NULL,
	`user_id` int(11) NOT NULL,
	`add_time` int(11) NOT NULL,
	`expired` int(11) NOT NULL,
	PRIMARY KEY (`rid`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_promotool_setting`
--

DROP TABLE IF EXISTS `swd_promotool_setting`;
CREATE TABLE `swd_promotool_setting` (
	`psid` int(11) NOT NULL AUTO_INCREMENT,
	`appid` varchar(20) NOT NULL,
	`store_id` int(11) NOT NULL,
	`rules` TEXT NOT NULL,
	`status` tinyint(3) NOT NULL DEFAULT '0',
	`add_time` int(11) NOT NULL,
	PRIMARY KEY (`psid`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_promotool_item`
--

DROP TABLE IF EXISTS `swd_promotool_item`;
CREATE TABLE `swd_promotool_item` (
	`piid` int(11) NOT NULL AUTO_INCREMENT,
	`goods_id` int(11) NOT NULL,
	`appid` varchar(20) NOT NULL,
	`store_id` int(11) NOT NULL,
	`config` TEXT NOT NULL,
	`status` int(1) NOT NULL,
	`add_time` int(11) NOT NULL,
	PRIMARY KEY (`piid`)
) TYPE=MyISAM;


-- --------------------------------------------------------

--
-- 表的结构 `swd_gift`
--

DROP TABLE IF EXISTS `swd_gift`;
CREATE TABLE `swd_gift` (
	`goods_id` int(11) NOT NULL AUTO_INCREMENT,
	`goods_name` varchar(100) NOT NULL,
	`store_id` int(11) NOT NULL,
	`price` decimal(10,2) NOT NULL,
	`stock` int(11) NOT NULL,
	`default_image` varchar(255) NOT NULL,
	`description` TEXT NOT NULL,
	`if_show` tinyint(3) NOT NULL DEFAULT '0',
	`add_time` int(11) NOT NULL,
	PRIMARY KEY (`goods_id`)
) TYPE=MyISAM;


-- --------------------------------------------------------

--
-- 表的结构 `swd_order_gift`
--

DROP TABLE IF EXISTS `swd_order_gift`;
CREATE TABLE `swd_order_gift` (
	`rec_id` int(10) NOT NULL AUTO_INCREMENT,
	`order_id` int(10) NOT NULL,
	`goods_id` int(10) NOT NULL,
	`goods_name` varchar(100) NOT NULL,
	`price` decimal(10,2) NOT NULL,
	`quantity` int(11) NOT NULL,
	`default_image` varchar(255) NOT NULL,
	PRIMARY KEY (`rec_id`)
) TYPE=MyISAM;


-- --------------------------------------------------------

--
-- 表的结构 `swd_distribution`
--

DROP TABLE IF EXISTS `swd_distribution`;
CREATE TABLE `swd_distribution` (
  `dst_id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `parent_id` int(11) unsigned NOT NULL,
  `store_id` int(11) NOT NULL,
  `did` int(11) NOT NULL DEFAULT '0',
  `real_name` varchar(255) DEFAULT NULL,
  `phone_mob` varchar(20) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `add_time` int(10) DEFAULT NULL,
  PRIMARY KEY (`dst_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_distribution_statistics`
--

DROP TABLE IF EXISTS `swd_distribution_statistics`;
CREATE TABLE `swd_distribution_statistics` (
  `user_id` int(11) unsigned NOT NULL,
  `amount` decimal(10,2) unsigned NOT NULL,
  `layer1` decimal(10,2) DEFAULT NULL,
  `layer2` decimal(10,2) DEFAULT NULL,
  `layer3` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_webim_log`
--

DROP TABLE IF EXISTS `swd_webim_log`;
CREATE TABLE `swd_webim_log` (
	`logid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  	`fromid` int(10) unsigned NOT NULL DEFAULT '0',
	`fromName` varchar(100) NOT NULL,
	`toid` int(10) unsigned NOT NULL DEFAULT '0',
	`toName` varchar(100) NOT NULL,
	`type` varchar(20) NOT NULL,
  	`content` varchar(255) NOT NULL,
	`formatContent` varchar(255) NOT NULL,
	`unread` int(10) unsigned NOT NULL DEFAULT '0',
	`add_time` int(10) unsigned NOT NULL DEFAULT '0',
  	PRIMARY KEY (`logid`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_webim_onlineuser`
--

DROP TABLE IF EXISTS `swd_webim_onlineuser`;
CREATE TABLE `swd_webim_onlineuser` (
	`onid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  	`user_id` int(10) unsigned NOT NULL DEFAULT '0',
	`client_id` varchar(100) NOT NULL,
	`lasttime` int(10) unsigned NOT NULL DEFAULT '0',
  	PRIMARY KEY (`onid`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_weixin_config`
--

DROP TABLE IF EXISTS `swd_weixin_config`;
CREATE TABLE `swd_weixin_config` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`user_id` int(11) NOT NULL,
	`name` varchar(100) DEFAULT NULL,
	`token` varchar(255) NOT NULL,
	`appid` varchar(255) DEFAULT NULL,
	`appsecret` varchar(255) DEFAULT NULL,
	`if_valid` tinyint(3) unsigned DEFAULT '0',
	`auto_login` tinyint(3) unsigned DEFAULT '0',
	PRIMARY KEY (`id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_weixin_menu`
--

DROP TABLE IF EXISTS `swd_weixin_menu`;
CREATE TABLE `swd_weixin_menu` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`user_id` int(10) unsigned DEFAULT NULL,
	`parent_id` int(10) DEFAULT NULL,
	`name` varchar(255) DEFAULT NULL,
	`type` varchar(20) DEFAULT NULL,
	`add_time` int(10) DEFAULT NULL,
	`sort_order` tinyint(3) unsigned DEFAULT NULL,
	`link` varchar(255) DEFAULT NULL,
	`reply_id` int(10) DEFAULT NULL,
	 PRIMARY KEY (`id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_weixin_reply`
--

DROP TABLE IF EXISTS `swd_weixin_reply`;
CREATE TABLE `swd_weixin_reply` (
  `reply_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` tinyint(3) unsigned NOT NULL COMMENT '回复类型0文字1图文',
  `action` varchar(20) DEFAULT NULL COMMENT '回复命令 关注、消息、关键字',
  `title` varchar(255) DEFAULT NULL,
  `link` varchar(50) DEFAULT NULL,
  `image` varchar(100) DEFAULT NULL,
  `rule_name` varchar(255) DEFAULT NULL,
  `keywords` varchar(255) DEFAULT NULL,
  `content` text,
  `add_time` int(10) DEFAULT NULL,
  PRIMARY KEY (`reply_id`)
) TYPE=MyISAM;


-- --------------------------------------------------------

--
-- 表的结构 `swd_cashcard`
--

DROP TABLE IF EXISTS `swd_cashcard`;
CREATE TABLE `swd_cashcard` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(30),
	  `cardNo` varchar(30),
	  `password` varchar(100),
	  `money` decimal(10,2),
	  `useId` int(11),
	`printed` int(1),
	`add_time` int(11),
	`active_time` int(11),
	`expire_time` int(11),
	 PRIMARY KEY  (`id`)
) TYPE=MyISAM;

--
-- 表的结构 `swd_cashcard`
--

DROP TABLE IF EXISTS `swd_login_log`;
CREATE TABLE `swd_login_log` (
	`log_id` int(10) NOT NULL AUTO_INCREMENT,
	`user_id` int(10) DEFAULT NULL ,
	`user_name` varchar(50) DEFAULT NULL,
	`ip` varchar(50) DEFAULT NULL,
	`region_name` varchar(255) DEFAULT NULL,
	`add_time` int(10) DEFAULT NULL,
	PRIMARY KEY (`log_id`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 `swd_report`
--
DROP TABLE IF EXISTS `swd_report`;
CREATE TABLE `swd_report` (
	`report_id` int(10) NOT NULL AUTO_INCREMENT,
	`user_id` int(10) DEFAULT NULL COMMENT '举报人ID',
	`store_id` int(10) DEFAULT NULL COMMENT '被举报店铺ID',
	`goods_id` int(10) DEFAULT NULL COMMENT '被举报商品ID',
	`content` varchar(255) DEFAULT NULL COMMENT '举报内容',
	`images` text NOT NULL,
	`add_time` int(10) DEFAULT NULL COMMENT '添加时间',
	`status` int(3) DEFAULT NULL COMMENT '状态',
	`admin` varchar(20) DEFAULT NULL COMMENT '审核员',
	`verify` varchar(255) DEFAULT NULL COMMENT '审核说明',
	PRIMARY KEY (`report_id`)
) TYPE=MyISAM;