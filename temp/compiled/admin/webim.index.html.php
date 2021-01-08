<?php echo $this->fetch('header.html'); ?>
<script type="text/javascript">
$(function(){
	$('.J_CheckTalk').click(function(){
		var o = $(this);
		var id = $(this).attr('data-fromid');
		var imforbid = $(this).attr('data-imforbid');
		var url = "index.php?app=webim&act=checkTalk&id="+id+"&imforbid="+imforbid;
		goConfirm('您确定要禁止或解禁该用户发言？',url,true);
	});
	$('.J_DelTalk').click(function(){
		var o = $(this);
		var logid = $(this).attr('data-logid');
		var url = "index.php?app=webim&act=delTalk&logid="+logid;
		goConfirm('您确定要删除此项？',url,true);
	});
});
</script>
<div id="rightTop">
  <p>客服管理</p>
    <ul class="subnav">
    <li><span>管理</span></li>
  </ul>
</div>
<div class="search-form clearfix">
    <form method="get" id="formSearch">
          <input type="hidden" name="app" value="webim" />
          <input class="queryInput" type="text" name="fromName" value="<?php echo htmlspecialchars($_GET['fromName']); ?>" placeholder="用户" />给
          <input class="queryInput" type="text" name="toName" value="<?php echo htmlspecialchars($_GET['toName']); ?>" placeholder="用户" />发言，
          内容为
          <input class="queryInput" type="text" name="formatContent" value="<?php echo htmlspecialchars($_GET['formatContent']); ?>" />
          <input type="submit" class="formbtn" value="查询" style="float:none; display:inline-block" />
      <?php if ($this->_var['filtered']): ?>
       <a class="formbtn formbtn1" href="index.php?app=webim">撤销检索</a>
     <?php endif; ?>
    </form>
</div>
<div class="tdare webim">

    <?php $_from = $this->_var['imlog']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'log');$this->_foreach['fe_item'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_item']['total'] > 0):
    foreach ($_from AS $this->_var['log']):
        $this->_foreach['fe_item']['iteration']++;
?>
  <div class="each">
	  <div class="hd clearfix"><h3><?php echo $this->_var['log']['fromName']; ?> > <?php echo $this->_var['log']['toName']; ?></h3><span style="color: #8B8B8B; margin-left: 10px;">发送时间 :  <?php echo local_date("Y-m-d H:i:s",$this->_var['log']['add_time']); ?></span><p><a href="javascript::" class="J_CheckTalk formbtn" data-imforbid="<?php if ($this->_var['log']['imforbid']): ?>0<?php else: ?>1<?php endif; ?>" data-fromid="<?php echo $this->_var['log']['fromid']; ?>"><?php if (! $this->_var['log']['imforbid']): ?>禁言<?php else: ?><font color="#333">解禁</font><?php endif; ?></a> <a href="javascript:;" class="J_DelTalk formbtn formbtn1" data-logid="<?php echo $this->_var['log']['logid']; ?>">删除</a></p></div>
    <div class="bd">
    	<div class="wrap"><?php echo $this->_var['log']['formatContent']; ?></div>
    </div>
  </div>
  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
    <div id="dataFuncs">
        <div class="pageLinks"><?php echo $this->fetch('page.bottom.html'); ?></div>
        
    </div>
    <div class="clear"></div>
  </div>
</div>
<?php echo $this->fetch('footer.html'); ?>