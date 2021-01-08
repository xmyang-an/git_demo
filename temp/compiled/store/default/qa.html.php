<div class="qa-info-page">
    <?php $_from = $this->_var['qa_info']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'qainfo');$this->_foreach['fe_qa'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_qa']['total'] > 0):
    foreach ($_from AS $this->_var['qainfo']):
        $this->_foreach['fe_qa']['iteration']++;
?>
    <div  class="qa-list">
        <div class="leave-word">
        	<dl class="t clearfix">
            	<dt>咨询网友：</dt>
            	<dd>
                	<span><?php if ($this->_var['qainfo']['user_name']): ?><?php echo $this->_var['qainfo']['user_name']; ?><?php else: ?>游客<?php endif; ?></span>
                    <i class="ml20"><?php echo local_date("Y-m-d H:i:s",$this->_var['qainfo']['time_post']); ?></i>
                </dd>
            </dl>
            <dl class="leave-con clearfix">
            	<dt>咨询内容： </dt>
            	<dd><p><?php echo nl2br(htmlspecialchars($this->_var['qainfo']['question_content'])); ?></p></dd>
            </dl>
            <?php if ($this->_var['qainfo']['reply_content']): ?>
            <dl class="reply-con clearfix">
            	<dt>店主回复：</dt>
            	<dd>
               		<p><?php echo nl2br(htmlspecialchars($this->_var['qainfo']['reply_content'])); ?></p> 
                    <i><?php echo local_date("Y-m-d H:i:s",$this->_var['qainfo']['time_reply']); ?></i>
            	</dd>
            </dl>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; else: ?>
    <div style="border:0px;" class="no-record no-access">没有符合条件的记录</div>
    <?php endif; unset($_from); ?><?php $this->pop_vars();; ?> 
	<?php if ($this->_var['qa_info']): ?><?php echo $this->fetch('page.bottom.html'); ?><?php endif; ?>
    <?php if ($_GET['app'] == 'goods'): ?>
    <div class="fill-con">
    <?php if (! $this->_var['guest_comment_enable'] && ! $this->_var['visitor']['user_id']): ?>
    <div class="no-access">您需要先&nbsp;[<a href="index.php?app=member&act=login">登录</a>]&nbsp;后才可以发布咨询</div>
    <?php else: ?>
    <form method="post" id="message" action="index.php?app=<?php echo $_GET['app']; ?><?php if ($_GET['act']): ?>&amp;act=<?php echo $_GET['act']; ?><?php elseif ($_GET['app'] == 'goods'): ?>&amp;act=qa<?php endif; ?>&amp;id=<?php echo $_GET['id']; ?>">
    	<div class="qar-info">
        	<label for="qar-email">
        		<strong>电子信箱:</strong>
        		<input type="text" id="qar-email" class="txt" name="email" value="<?php echo $this->_var['email']; ?>" placeholder="非会员可输入邮件进行咨询，以便客服人员给您回执。" />
        	</label>
            <?php if ($this->_var['captcha']): ?>
            <label for="captcha_value">
            	<strong>验证码:</strong>
                <input type="text" class="text" name="captcha" id="captcha_value" />
                <img  id="captcha" class="captcha" src="index.php?app=captcha&amp;<?php echo $this->_var['random_number']; ?>" />
                <span onclick="change_captcha($('#captcha'));">看不清验证码？点击图片换一张。</span>
            </label>
             <?php endif; ?>
        </div>
    	<div class="qa-content">
        	<strong class="ml5">我要咨询:</strong>
        	<textarea name="content" class="qa-textarea"></textarea>
            <span class="field_message"><span class="field_notice"></span></span>
        </div>
        <div class="bottom">
        <?php if ($_SESSION['user_info']): ?>
        <label><input type="checkbox" name="hide_name" value="hide" /> 匿名发表</label>
        <?php endif; ?>
        <input type="submit" value="发布咨询" name="qa" class="submit"/>
        </div>
    </form>
    <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
