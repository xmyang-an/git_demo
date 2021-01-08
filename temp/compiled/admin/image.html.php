<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7 charset=<?php echo $this->_var['charset']; ?>" />
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $this->_var['charset']; ?>" />
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'jquery-1.11.1.js'; ?>" charset="utf-8"></script>
<script type="text/javascript" src="index.php?act=jslang"></script>
<script type="text/javascript">
function submit_form(obj)
{
    obj.prop('disabled', true);
    $('#image_form').submit();
    obj.prop('disabled', false);
}
</script>
</head>
<body>
<form action="index.php?app=comupload&act=uploadedfile" method="post" enctype="multipart/form-data" style="display:inline;" id="image_form">
<input type="hidden" name="id" value="<?php echo $this->_var['id']; ?>">
<input type="hidden" name="belong" value="<?php echo $this->_var['belong']; ?>">
<input type="file" name="file">
<input type="button" value="上传" onClick="submit_form($(this))">
</form>
</body>
</html>