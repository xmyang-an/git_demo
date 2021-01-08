<?php echo $this->fetch('header.html'); ?>
<div id="main">
  <div class="search-article article-detail">
    <div class="title">
      <h3><?php echo htmlspecialchars($this->_var['article']['title']); ?></h3>
    </div>
    <div class="content"> 
      <?php if ($this->_var['article']['store_id']): ?> 
      <?php echo html_filter($this->_var['article']['content']); ?> 
      <?php else: ?> 
      <?php echo $this->_var['article']['content']; ?> 
      <?php endif; ?> 
    </div>
  </div>
</div>
<?php echo $this->fetch('footer.html'); ?>