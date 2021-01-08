<!DOCTYPE html>
<html>
<head>
	<meta charset="<?php echo $this->_var['charset']; ?>">
<style>
.payform-submit {
	margin: 20px;
	border: 1px #40B3FF solid;
	padding: 20px;
	background: #E5F5FF;
	font-weight: normal;
	font-size: 16px;
}
</style>
</head>
<body>
<div class="payform-submit">正在跳转至收银台, 请稍等...</div>
<form action="<?php echo $this->_var['payform']['gateway']; ?>" id="payform" method="<?php echo $this->_var['payform']['method']; ?>" style="display:none">
  <?php $_from = $this->_var['payform']['params']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('_k', 'value');if (count($_from)):
    foreach ($_from AS $this->_var['_k'] => $this->_var['value']):
?>
  <input type="hidden" name="<?php echo $this->_var['_k']; ?>" value="<?php echo $this->_var['value']; ?>" />
  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</form>
<script type="text/javascript">
	document.getElementById('payform').submit();
</script>
</body>
</html>