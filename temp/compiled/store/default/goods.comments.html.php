<?php echo $this->fetch('header.html'); ?>
<div id="page-goods-index" class="mb10">
	<?php echo $this->fetch('curlocal.html'); ?>
    <?php echo $this->fetch('goodsinfo.html'); ?>
	<div class="w-shop clearfix">
        <div class="col-sub w210">
        	<?php echo $this->fetch('left.html'); ?>	
        </div>
        <div class="col-main float-right w980">
        	<div class="attr-tabs">
                <ul class="user-menu">
                    <li>
                      <a style="border-left:1px solid #ddd;"  href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>#module">
                        <span>
                          商品详情
                        </span>
                      </a>
                    </li>
                    <li class="active">
                      <a href="<?php echo url('app=goods&act=comments&id=' . $this->_var['goods']['goods_id']. ''); ?>#module"><span>商品评论(<?php echo $this->_var['goods']['sys_comment']; ?>)</span></a>
                    </li>
                    <li>
                      <a href="<?php echo url('app=goods&act=saleslog&id=' . $this->_var['goods']['goods_id']. ''); ?>#module"><span>销售记录</span></a>
                    </li>
                    <li>
                      <a href="<?php echo url('app=goods&act=qa&id=' . $this->_var['goods']['goods_id']. ''); ?>#module"><span>产品咨询</span></a>
                    </li>
                  </ul> 
              </div>
            <?php echo $this->fetch('comments.html'); ?>
        </div>
	</div>
</div>
<?php echo $this->fetch('footer.html'); ?>