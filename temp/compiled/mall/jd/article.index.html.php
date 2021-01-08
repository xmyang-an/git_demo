<?php echo $this->fetch('header.html'); ?>
<style>
#main{background:#f9f9f9}
#footer{margin-top:0;}
</style>
<div id="main" class="w-full">
<div id="page-article" class="w pb20 pt20 clearfix">
   <div class="col-sub">
	  <div class="title">文章分类</div>
	  <ul class="content mb10">
	     <?php $_from = $this->_var['acategories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'acategory');if (count($_from)):
    foreach ($_from AS $this->_var['acategory']):
?>
         <li><a href="<?php echo url('app=article&cate_id=' . $this->_var['acategory']['cate_id']. ''); ?>"><?php echo htmlspecialchars($this->_var['acategory']['cate_name']); ?></a><b></b></li>
         <div class="child">
         	<?php $_from = $this->_var['acategory']['children']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'children');if (count($_from)):
    foreach ($_from AS $this->_var['children']):
?>
            <p><a href="<?php echo url('app=article&cate_id=' . $this->_var['children']['cate_id']. ''); ?>"><?php echo htmlspecialchars($this->_var['children']['cate_name']); ?></a></p>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
         </div>
         <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
	  </ul>
   </div>
   <div class="col-main">
      <ul class="content">
      	 <li class="title"><?php echo $this->_var['categoryName']; ?></li>
	     <?php $_from = $this->_var['articles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'article');if (count($_from)):
    foreach ($_from AS $this->_var['article']):
?>
	     <li class="clearfix"><a <?php if ($this->_var['article']['link']): ?>target="_blank"<?php endif; ?> href="<?php echo url('app=article&act=view&article_id=' . $this->_var['article']['article_id']. ''); ?>" class="float-left">> <?php echo htmlspecialchars($this->_var['article']['title']); ?></a> <span class="float-right">[ <?php echo local_date("Y-m-d",$this->_var['article']['add_time']); ?> ]</span></li>
		 <?php endforeach; else: ?>
		 <li>没有符合条件的记录</li>
		 <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
	  </ul>
	 <?php echo $this->fetch('page.bottom.html'); ?>
   </div>
</div>
</div>
<?php echo $this->fetch('footer.html'); ?>