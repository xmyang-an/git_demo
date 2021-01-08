<?php echo $this->fetch('header.html'); ?>
<div id="page-goods-index">
    <?php echo $this->fetch('curlocal.html'); ?> 
    <?php echo $this->fetch('goodsinfo.html'); ?>
    <div class="w-shop clearfix">
        <div class="col-sub w210">
            <?php echo $this->fetch('left.html'); ?>
        </div>
        <div style="overflow:hidden;" class="col-main float-right w980">
			<?php echo $this->fetch('goods.meal.html'); ?>
            <div class="attr-tabs">
                <ul class="user-menu">
                    <li class="active">
                        <a style="border-left:1px solid #ddd;" href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>#module">
                            <span>
                                商品详情
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo url('app=goods&act=comments&id=' . $this->_var['goods']['goods_id']. ''); ?>#module">
                            <span>
                                商品评论(<?php echo $this->_var['goods']['sys_comment']; ?>)
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo url('app=goods&act=saleslog&id=' . $this->_var['goods']['goods_id']. ''); ?>#module">
                            <span>
                                销售记录
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo url('app=goods&act=qa&id=' . $this->_var['goods']['goods_id']. ''); ?>#module">
                            <span>
                                产品咨询
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <?php if ($this->_var['props']): ?>
            <div class="mb20 clearfix" style="padding:5px 0px;">
               <?php $_from = $this->_var['props']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'prop');if (count($_from)):
    foreach ($_from AS $this->_var['prop']):
?>
               <div style="float:left;width:23%;padding:0px 5px; height:25px; line-height:25px;color:#666">
                  <span title="<?php echo htmlspecialchars($this->_var['prop']['value']); ?>"><?php echo $this->_var['prop']['name']; ?>：<?php echo sub_str(htmlspecialchars($this->_var['prop']['value']),20); ?></span>
               </div>
               <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </div>
            <?php endif; ?>
            
            <div class="option_box">
                <div class="default">
                    <?php echo html_filter($this->_var['goods']['description']); ?>
                </div>
            </div>
            <?php echo $this->fetch('comments.html'); ?>
        </div>
    </div>
</div>
<?php echo $this->fetch('footer.html'); ?>
