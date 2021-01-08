<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
  <p>店铺管理</p>
  <ul class="subnav">
    <li><a class="btn1" href="index.php?app=store">管理</a></li>
    <li><span>新增</span></li>
    <li><a class="btn1" href="index.php?app=store&amp;wait_verify=1">待审核</a></li>
	<li><a class="btn1" href="index.php?app=store&amp;wait_verify=3">已拒绝</a></li>
  </ul>
</div>

<div class="explanation" id="explanation">
  <div class="title" id="checkZoom">
  	<i class="fa fa-lightbulb-o"></i>
    <h4 title="操作提示">操作提示</h4>
    <span id="explanationZoom" title="收起提示"></span>
  </div>
  <ul>
    <li><i class="fa fa-angle-double-right"></i> 1. 验证用户信息,开店用户存在并且没有开有店铺;</li>
    <li><i class="fa fa-angle-double-right"></i> 2. 填写店铺信息,提交完成操作。</li>
  </ul>
</div>
<div class="info">
  <form method="post" enctype="multipart/form-data" id="test_form">
    <table class="infoTable">
      <tr>
        <th class="paddingT15"> 用户名:</th>
        <td class="paddingT15 wordSpacing5"><input class="infoTableInput2" name="user_name" type="text" id="user_name" /></td>
      </tr>
      <tr>
        <th class="paddingT15"> 密码:</th>
        <td class="paddingT15 wordSpacing5"><input class="infoTableInput2" name="password" type="text" id="password" />
          &nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="need_password" value="1" id="checkbox" checked="checked" />
        <label for="need_password">需要验证密码</label></td>
      </tr>
      <tr>
        <th></th>
        <td class="ptb20"><input class="formbtn J_FormSubmit" type="submit" name="Submit" value="提交" />
          <input class="formbtn" type="reset" name="Reset" value="重置" /></td>
      </tr>
    </table>
  </form>
</div>
<?php echo $this->fetch('footer.html'); ?> 