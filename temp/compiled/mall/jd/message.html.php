<?php echo $this->fetch('member.header.html'); ?>
<div id="main" class="clearfix">
    <div class="message-box">
        <div class="particular_wrap" style="border:0;">
            <div class="message-detail <?php if ($this->_var['icon'] == "notice"): ?>success<?php else: ?>defeated<?php endif; ?>">
                <span></span>
                <div style="float:left;">
                    <b style="width:380px;font-size:14px;font-weight:400;"><?php echo $this->_var['message']; ?></b>
                    <?php if ($this->_var['err_file']): ?>
                    <b style="font-size: 14px;">Error File: <strong><?php echo $this->_var['err_file']; ?></strong> at <strong><?php echo $this->_var['err_line']; ?></strong> line.</b>
                    <?php endif; ?>
                    <?php if ($this->_var['icon'] != "notice"): ?>
                    <font style="clear: both; display:block; margin:10px 0 0 0;">
                    <?php $_from = $this->_var['links']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?>
                            <a style="color:#888;" href="<?php echo $this->_var['item']['href']; ?>">>> <?php echo $this->_var['item']['text']; ?></a><br />
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                    </font>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
//<!CDATA[
<?php if ($this->_var['redirect']): ?>
window.setTimeout("<?php echo $this->_var['redirect']; ?>", 1000);
<?php endif; ?>
//]]>
</script>
<?php echo $this->fetch('member.footer.html'); ?>