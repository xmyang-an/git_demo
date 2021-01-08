<?php echo $this->fetch('header.html'); ?>
<style>
#footer{display:none}
</style>
<div id="main">
<div class="pageSearchBox">
    <div class="true-search-box">
        <div class="hot-search mt20 ml10 mr10 clearfix">
            <?php $_from = $this->_var['formSearchBoxKeyword']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'searchKeyword');if (count($_from)):
    foreach ($_from AS $this->_var['searchKeyword']):
?>
            <a href="<?php echo $this->_var['searchKeyword']['url']; ?>" class="mb10 fs12 inline-block"><span><?php echo htmlspecialchars($this->_var['searchKeyword']['text']); ?></span></a>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </div>
    </div>
</div>
</div>
<?php echo $this->fetch('footer.html'); ?> 