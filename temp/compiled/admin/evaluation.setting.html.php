<?php echo $this->fetch('header.html'); ?>
<style type="text/css">
.infoTable textarea{width:500px;}
</style>
<div id="rightTop">
  <p>评价管理</p>
  <ul class="subnav">
  	<li><a class="btn1" href="index.php?app=evaluation">管理</a></li>
  	<li><span>设置</span></li>
    <li><a class="btn1" href="index.php?app=evaluation&amp;act=auto">自动好评</a></li>
  </ul>
</div>
<div class="info">
  <form method="post">
    <table class="infoTable">
      <tr>
        <th class="paddingT15"> <label for="auto_user">随机用户:</label></th>
        <td class="paddingT15 wordSpacing5"><textarea name="auto_user" id="auto_user"><?php echo htmlspecialchars($this->_var['setting']['auto_user']); ?></textarea><label class="field_notice">多个用 | 号隔开, 列如: 刘华|张友|郭富城|张三|李思|旺旺|goods|boy|黑特|坏人</label></td>
      </tr>
      <tr>
        <th></th>
        <td class="ptb20"><input class="formbtn J_FormSubmit" type="submit" name="Submit" value="提交" />
          <input class="formbtn " type="reset" name="Submit2" value="重置" />
        </td>
      </tr>
    </table>
  </form>
</div>
<?php echo $this->fetch('footer.html'); ?>
