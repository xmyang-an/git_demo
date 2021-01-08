<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $this->_var['charset']; ?>" />
<title> 系统提示 </title>
<link href="templates/style/admin.css" rel="stylesheet" type="text/css" />
<link href="templates/style/font/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<style>
body {background-color:#fbfdff; overflow:auto; padding:20px;}
#page_footer {color: #999; border-top: 1px solid #cbe4f5; text-align: center; padding-top: 20px;}
</style>
</head>
<body>
<div class="message">
    <h1>系统提示</h1>
    <dl>
        <dt><i class="fa fa-info-circle"></i><?php echo $this->_var['message']; ?></dt>
        <?php if ($this->_var['err_file']): ?>
        <dd>Error File: <b><?php echo $this->_var['err_file']; ?></b> at <b><?php echo $this->_var['err_line']; ?></b> line.</dd>
        <?php endif; ?>
        <?php if ($this->_var['redirect']): ?>
        <dd>若不选择将自动跳转</dd>
        <?php endif; ?>
        <?php $_from = $this->_var['links']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?>
        <dd><a href="<?php echo $this->_var['item']['href']; ?>" class="forward"><?php echo $this->_var['item']['text']; ?></a></dd>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </dl>
</div>
<?php if ($this->_var['redirect']): ?>
<script type="text/javascript">
window.setTimeout("<?php echo $this->_var['redirect']; ?>", 3000);
</script>
<?php endif; ?>
<?php echo $this->fetch('footer.html'); ?>
