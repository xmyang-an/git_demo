<?php echo $this->fetch('header.html'); ?>
<style>
.pl10{padding-left:10px;}
.formbtn{float:none; display:inline-block; /*line-height:30px;*/}
</style>
<div id="rightTop">
    <p>商户</p>
    <ul class="subnav">
        <li><span>管理</span></li>
        <li><a class="btn1" href="index.php?app=merchant&amp;act=add">新增</a></li>
    </ul>
</div>
<div class="mrightTop" style="margin-top:10px;">
    <div class="fontl">
        <form method="get">
            <div class="left pl10">
                <input type="hidden" name="app" value="merchant" />
                <input type="hidden" name="act" value="index" />
                商户名:
                <input class="queryInput" type="text" name="name" value="<?php echo htmlspecialchars($this->_var['query']['name']); ?>" />
                商户号:
                <input class="queryInput" type="text" name="appId" value="<?php echo htmlspecialchars($this->_var['query']['appId']); ?>" />
                <input type="submit" class="formbtn" value="查询" />
            </div>
            <?php if ($this->_var['filtered']): ?>
            <a class="formbtn" href="index.php?app=merchant">撤销检索</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="fontr">
        <?php echo $this->fetch('page.top.html'); ?>
    </div>
</div>
<div class="tdare">
    <table width="100%" cellspacing="0" class="dataTable">
        <?php if ($this->_var['merchants']): ?>
        <tr class="tatr1">
            <td width="20" class="firstCell"><input type="checkbox" class="checkall" /></td>
            <td align="left">商户名</td>
            <td>商户号</td>
            <td>商户密钥</td>
            <td>状态</td>
            <td>创建时间</td>
            <td>操作</td>
        </tr>
        <?php endif; ?>
        <?php $_from = $this->_var['merchants']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'merchant');if (count($_from)):
    foreach ($_from AS $this->_var['merchant']):
?>
        <tr class="tatr2">
            <td class="firstCell"><input type="checkbox" class="checkitem" value="<?php echo $this->_var['merchant']['id']; ?>"/></td>
            <td><?php echo htmlspecialchars($this->_var['merchant']['name']); ?></td>
            <td><?php echo htmlspecialchars($this->_var['merchant']['appId']); ?></td>
            <td><?php echo $this->_var['merchant']['appKey']; ?></td>
            <td><?php if ($this->_var['merchant']['closed'] == '0'): ?><img src="templates/style/images/positive_enabled.gif" /><?php else: ?><img src="templates/style/images/positive_disabled.gif" /><?php endif; ?></td>
            <td><?php echo local_date("Y-m-d H:i:s",$this->_var['merchant']['add_time']); ?></td>
            <td><a href="index.php?app=merchant&amp;act=edit&amp;id=<?php echo $this->_var['merchant']['id']; ?>">编辑</a></td>
        </tr>
        <?php endforeach; else: ?>
        <tr class="no_data">
            <td colspan="7">没有符合条件的记录</td>
        </tr>
        <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </table>
    <?php if ($this->_var['merchants']): ?>
    <div id="dataFuncs">
        <div class="pageLinks">
            <?php echo $this->fetch('page.bottom.html'); ?>
        </div>
        <div id="batchAction" class="left paddingT15">
            &nbsp;&nbsp;
            <input class="formbtn batchButton" type="button" value="删除" name="id" uri="index.php?app=merchant&act=drop" presubmit="confirm('您确定要删除它吗？');" />
        </div>
    </div>
    <div class="clear"></div>
    <?php endif; ?>
</div>
<?php echo $this->fetch('footer.html'); ?>
