<?php echo $this->fetch('header.html'); ?>
<script type="text/javascript">
var template_name = '<?php echo $this->_var['curr_template_name']; ?>';
var style_name = '<?php echo $this->_var['curr_style_name']; ?>';
var type = '<?php echo $_GET['type']; ?>' ? '<?php echo $_GET['type']; ?>' : '';
function use_theme(template, style){
    if (template != template_name)
    {
		parent.layer.confirm('您选择的该主题模板与当前使用的主题模板不一致，因此您当前的挂件设置将不能在新主题中显示，您需要重新设置，您确定要使用该模板吗？',{icon: 3, title:'提示'},function(index){
			parent.layer.close(index);
			window.location.href = 'index.php?app=theme&act=set&template_name=' + template + '&style_name=' + style+'&type='+type;
			return false;	
		},function(index){
			parent.layer.close(index);
			return false;
		});
    }
    
}
function preview_theme(template, style,type){
    $('#template_name').val(template);
    $('#style_name').val(style);
	$('#type').val(type);	
    $('#preview_form').submit();
}
function go_index(client){
    $('#go_'+client+'index').submit();
}
</script>
<style type="text/css">
#rightCon {list-style:none; width:100%; border:0;}
#rightCon h3{font-weight:normal;}
#rightCon h3 em{font-size:14px; font-weight:normal; color:#E4393C;}
#rightCon li {float:left; margin:10px;}
#rightCon .title_name {font-size:15px; font-weight:bold; color:#4DA1E0; text-align:center;}
#rightCon .templet_style {margin:5px; background:#eee; border:#ddd 1px solid; padding:3px;}
#rightCon .templet_btn {text-align:center;}
.formbtn{width:60px; font-size:12px; margin:0; float:none; display:inline;}
</style>
<div id="rightTop">
  <p>主题设置</p>
  <ul class="subnav">
    <li><span>主题列表</span></li>
  </ul>
</div>
<div class="info">
<ul id="rightCon">
    <h3> 当前您使用的主题是 : <em><?php echo $this->_var['curr_template_name']; ?>&nbsp;&nbsp;<?php echo $this->_var['curr_style_name']; ?></em> </h3>
    <?php $_from = $this->_var['theme_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('template_name', 'styles');if (count($_from)):
    foreach ($_from AS $this->_var['template_name'] => $this->_var['styles']):
?>
    <?php $_from = $this->_var['styles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'style_name');if (count($_from)):
    foreach ($_from AS $this->_var['style_name']):
?>
    <li>
        <div class="title_name"><?php echo $this->_var['template_name']; ?>&nbsp;<?php echo $this->_var['style_name']; ?></div>
        <div class="templet_style"><img width="115" src="<?php echo $this->_var['site_url']; ?>/<?php echo $_GET['type']; ?>/themes/mall/<?php echo $this->_var['template_name']; ?>/styles/<?php echo $this->_var['style_name']; ?>/preview.jpg" onclick="preview_theme('<?php echo $this->_var['template_name']; ?>', '<?php echo $this->_var['style_name']; ?>','<?php echo $_GET['type']; ?>');" /></div>
        <div class="templet_btn">
        <?php if (( $this->_var['curr_template_name'] != $this->_var['template_name'] ) || ( $this->_var['curr_style_name'] != $this->_var['style_name'] )): ?>
        <input type="submit" value="使用" onclick="goConfirm('您选择的该主题模板与当前使用的主题模板不一致，因此您当前的挂件设置将不能在新主题中显示，您需要重新设置，您确定要使用该模板吗？', 'index.php?app=theme&act=set&template_name=<?php echo $this->_var['template_name']; ?>&style_name=<?php echo $this->_var['style_name']; ?>&type=<?php echo $_GET['type']; ?>',true);" class="formbtn" />&nbsp;&nbsp;
        <input type="button" value="预览" onclick="preview_theme('<?php echo $this->_var['template_name']; ?>', '<?php echo $this->_var['style_name']; ?>');" class="formbtn" />
        <?php else: ?>
        <input type="button" value="查看商城" onclick="go_index('<?php echo $_GET['type']; ?>')" class="formbtn" />
        <?php endif; ?> 
        </div>
    </li>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</ul>
<form id="preview_form" method="POST" action="index.php?app=theme&act=preview" target="_blank">
	<input type="hidden" name="template_name" id="template_name" />
	<input type="hidden" name="style_name" id="style_name" /><input type="hidden" name="type" id="type" />
</form>
<form id="go_index" method="GET" action="<?php echo $this->_var['site_url']; ?>" target="_blank"></form>
<form id="go_mobileindex" method="GET" action="<?php echo $this->_var['site_url']; ?>/mobile" target="_blank"></form>
</div>
<?php echo $this->fetch('footer.html'); ?>
