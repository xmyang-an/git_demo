<?php echo $this->fetch('header.html'); ?>
<style>
#main{background:#f9f9f9}
#footer{margin-top:0;}
</style>
<div id="main" class="w-full">
<div id="page-article" class="w clearfix pb20 pt20">
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
      <div class="content">
	     <div class="article-info">
	        <h1><?php echo htmlspecialchars($this->_var['article']['title']); ?></h1>
            <h2><?php echo local_date("Y-m-d H:i",$this->_var['article']['add_time']); ?></h2>
         </div>
		 <div class="article-detail">
            <?php if ($this->_var['article']['store_id']): ?>
            <?php echo html_filter($this->_var['article']['content']); ?>
            <?php else: ?>
            <?php echo $this->_var['article']['content']; ?>
            <?php endif; ?>
		 </div>
         <div class="more-article mt20 hidden">
            <h3>上一篇：<?php if ($this->_var['pre_article']): ?><a target="<?php echo $this->_var['pre_article']['target']; ?>" href="<?php echo url('app=article&act=view&article_id=' . $this->_var['pre_article']['article_id']. ''); ?>"><?php echo htmlspecialchars($this->_var['pre_article']['title']); ?></a><?php else: ?>没有符合条件的记录<?php endif; ?></h3>
            <h3>下一篇：<?php if ($this->_var['next_article']): ?><a target="<?php echo $this->_var['next_article']['target']; ?>" href="<?php echo url('app=article&act=view&article_id=' . $this->_var['next_article']['article_id']. ''); ?>"><?php echo htmlspecialchars($this->_var['next_article']['title']); ?></a><?php else: ?>没有符合条件的记录<?php endif; ?></h3>
         </div>
	  </div>
   </div>
</div>
</div>
<?php echo $this->fetch('footer.html'); ?>