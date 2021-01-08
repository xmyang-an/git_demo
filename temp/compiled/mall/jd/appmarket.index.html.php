<?php echo $this->fetch('member.header.html'); ?>
<div id="main" class="clearfix">
  <div id="page-promotool" class="page-promotool clearfix"> <?php echo $this->fetch('member.menu.html'); ?>
    <div id="right"> <?php echo $this->fetch('member.curlocal.html'); ?>
      <div class="wrapful"> <?php echo $this->fetch('member.submenu.html'); ?>
        <div class="wrap">
          <div class="public_select">
            <div class="appmarket">
              <div class="bundle bundle-list">
                <div class="notice-word" style="display:none">
                  <p class="yellow-big">note_for_create</p>
                </div>
                <div class="applist"> 
                  <?php if ($this->_var['appmarket']): ?>
                  <div class="list-sort clearfix">
                    <ul class="float-left clearfix">
                      <li><a href="<?php echo url('app=appmarket'); ?>" <?php if (! $_GET['sort']): ?>class="active" <?php endif; ?>>默认</a></li>
                      <li><a href="<?php if ($_GET['order'] == 'desc'): ?><?php echo url('app=appmarket&sort=sales&order=asc'); ?><?php else: ?><?php echo url('app=appmarket&sort=sales&order=desc'); ?><?php endif; ?>" <?php if ($_GET['sort'] == 'sales'): ?>class="active" <?php endif; ?>>使用人数</a></li>
                      <li><a href="<?php if ($_GET['order'] == 'desc'): ?><?php echo url('app=appmarket&sort=add_time&order=asc'); ?><?php else: ?><?php echo url('app=appmarket&sort=add_time&order=desc'); ?><?php endif; ?>" <?php if ($_GET['sort'] == 'add_time'): ?>class="active" <?php endif; ?>>上架时间</a></li>
                      <li><a href="<?php if ($_GET['order'] == 'desc'): ?><?php echo url('app=appmarket&sort=views&order=asc'); ?><?php else: ?><?php echo url('app=appmarket&sort=views&order=desc'); ?><?php endif; ?>" <?php if ($_GET['sort'] == 'views'): ?>class="active" <?php endif; ?>>人气</a></li>
                    </ul>
                    <div class="total fs14 float-right mr10">找到符合条件的应用<b class="f60"> <?php echo ($this->_var['page_info']['item_count'] == '') ? '0' : $this->_var['page_info']['item_count']; ?></b> 个</div>
                  </div>
                  <ul class="list-each clearfix">
                    <?php $_from = $this->_var['appmarket']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');$this->_foreach['fe_item'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_item']['total'] > 0):
    foreach ($_from AS $this->_var['item']):
        $this->_foreach['fe_item']['iteration']++;
?>
                    <li <?php if ($this->_foreach['fe_item']['iteration'] % 3 == 0): ?> style="margin-right:0"<?php endif; ?>>
                      <div class="pic"><a href="<?php echo url('app=appmarket&act=view&id=' . $this->_var['item']['aid']. ''); ?>" target="_blank"><img width="260" height="181"  src="<?php echo $this->_var['item']['logo']; ?>" /></a></div>
                      <div class="info">
                        <p class="title"><font class="f60">[<?php echo $this->_var['lang'][$this->_var['item']['appid']]; ?>]</font> <?php echo $this->_var['item']['title']; ?></p>
                        <p class="summary"><?php echo $this->_var['item']['summary']; ?></p>
                        <p class="price clearfix"> <em class="float-left"><strong><?php echo price_format($this->_var['item']['config']['charge']); ?></strong> 元/月</em> <em class="float-right"><b><?php echo ($this->_var['item']['sales'] == '') ? '0' : $this->_var['item']['sales']; ?></b> 人使用</em> </p>
                        <p class="mt10 clearfix"><a class="btn-buy" href="<?php echo url('app=appmarket&act=view&id=' . $this->_var['item']['aid']. ''); ?>" target="_blank"><?php if (! $this->_var['item']['checkIsRenewal']): ?>购买<?php else: ?>续费<?php endif; ?></a></p>
                      </div>
                    </li>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                  </ul>
                  <div class="mt20 clearfix"><?php echo $this->fetch('member.page.bottom.html'); ?></div>
                  <?php else: ?>
                  <div class="notice-word">
                    <p>没有符合条件的记录</p>
                  </div>
                  <?php endif; ?> 
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php echo $this->fetch('member.footer.html'); ?> 