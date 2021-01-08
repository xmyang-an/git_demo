<?php echo $this->fetch('top.html'); ?>
<div id="header" class="w-full">
  <div class="bar-wrap J_BarWrap">
    <div class="top-bar <?php if (in_array ( $_GET['app'] , array ( 'goods' ) )): ?>J_BarGradient<?php endif; ?>">
      <div class="barbg"></div>
      <ul class="barbtn">
        <li class="webkit-box">
          <div class="lp"> 
            <?php if ($this->_var['index']): ?> 
            <a href="<?php echo url('app=category'); ?>" class="float-left category"><i>&#xe644;</i></a> 
            <?php else: ?> 
            <a href="<?php if ($this->_var['back_url']): ?><?php echo $this->_var['back_url']; ?><?php else: ?>javascript:pageBack();<?php endif; ?>" class="float-left pageback"><i>&#xe628;</i></a> 
            <?php endif; ?> 
          </div>
          <div class="mp flex1 ml10 mr10"> 
            <?php if (! in_array ( $_GET['app'] , array ( 'search' ) )): ?>
            	<?php if ($this->_var['index']): ?>
                <span class="yahei curlocal-title J_SearchInputHome"><i class="psmb-icon-font mr5 fs11">&#xe62a;</i>请输入搜索关键词</span>
                <?php else: ?>
            	<span class="yahei curlocal-title"><?php echo ($_GET['keyword'] == '') ? $this->_var['curlocal_title'] : $_GET['keyword']; ?></span>
                <?php endif; ?>
            <?php else: ?>
            <form action="<?php echo $this->_var['real_site_url']; ?>/index.php" method="get">
              <?php $_from = $this->_var['formSearchBoxParams']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
              <input type="hidden" name="<?php echo $this->_var['key']; ?>" value="<?php echo $this->_var['item']; ?>" />
              <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
              <div class="input-wraper">
              	<i class="psmb-icon-font">
					<input type="submit" class="search-btn" style="width:30px;margin-left:-20px;z-index:9999;;position:absolute;" value=""><span style="margin-top:-5px;margin-left:-10px;position:absolute;">&#xe62a;</span>
				</i>
                <input class="<?php if ($_GET['act'] != 'form'): ?>J_SearchInputGradient<?php endif; ?>" searchType="<?php echo $_GET['act']; ?>" value="<?php echo $_GET['keyword']; ?>" name="keyword" placeholder="请输入搜索关键词" />
              </div>
            </form>
            <?php endif; ?> 
          </div>
          <div class="rp"> <a href="javascript:;" class="float-right pagemore J_PageMenu"><i>&#xe648;</i></a> 
            <?php if (! $this->_var['index']): ?> 
            <a href="<?php echo url('app=cart'); ?>" class="float-right mr5 pagecart"><i>&#xe663;</i></a> 
            <?php endif; ?> 
          </div>
        </li>
      </ul>
      <div class="J_PageMenuBox hidden">
        <div class="page-menu-box"> <span class="arrow"></span>
          <ul class="clearfix">
            <li><a href="<?php echo $this->_var['real_site_url']; ?>"><i class="psmb-icon-font">&#xe63c;</i> 首页</a></li>
            <li><a href="<?php echo url('app=cart'); ?>"><i class="psmb-icon-font">&#xe663;</i> 购物车</a></li>
            <li><a href="<?php echo url('app=member'); ?>"><i class="psmb-icon-font">&#xe635;</i> 用户中心</a></li>
            <li><a href="<?php echo url('app=message&act=newpm'); ?>"><i class="psmb-icon-font">&#xe642;</i> 消息</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
