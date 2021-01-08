<?php echo $this->fetch('header.html'); ?>
<div id="rightTop">
    <p><strong>挂件管理</strong></p>
    <ul class="subnav">
    	<li><a class="btn1" href="index.php?app=widget">管理</a></li>
        <li><span>编辑文件</span></li>
  	</ul>
</div>

<div class="info">
    <form method="post" enctype="multipart/form-data">
        <table class="infoTable">
        	<tr>
                <th class="paddingT15">
                    <label>挂件名称:</label></th>
                <td class="paddingT15 wordSpacing5">
                    <?php echo htmlspecialchars($this->_var['name']); ?>
                </td>
            </tr>
            <tr>
                <th class="paddingT15">
                    <label for="widget_contents">文件内容:</label></th>
                <td class="paddingT15 wordSpacing5">
                    <textarea id="widget_contents" style="width:500px; height:300px" name="code"><?php echo $this->_var['code']; ?></textarea>
                </td>
            </tr>
            <tr>
                <th></th>
                <td class="ptb20">
                    <input class="formbtn J_FormSubmit" type="submit" value="提交" />
					<input type="hidden" value="<?php echo $this->_var['site_id']; ?>" name="site_id">
                    <input class="formbtn" type="button" onclick="window.history.go(-1)" value="返回" />

                </td>
            </tr>
        </table>
    </form>
</div>
<?php echo $this->fetch('footer.html'); ?>
