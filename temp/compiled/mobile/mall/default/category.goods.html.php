<?php echo $this->fetch('header.html'); ?>
<style>
body{background:#f3f5f7;}
#header{position:fixed;left:0;right:0;top:0;z-index:99;}
#footer{display:none;}
</style>
<script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/iscroll.js'; ?>" charset="utf-8"></script> 
<script type="text/javascript">
 	var leftScroll;
	window.onload=function() {
	 	leftScroll = new IScroll('#left-part', {mouseWheel: true, click: true});
	}
	$(function(){
		$(".left-part .inner-wrap .tt-item").click(function(){
			
			$(this).addClass("active").siblings().removeClass("active");
			var index = $(this).index();
			leftScroll.scrollToElement(document.querySelector('#left-part .tt-item:nth-child('+(index+1)+')'), null, null, true);
			
			$("#right-part .content-item:eq("+index+")").show().siblings().hide();
		})
		
		<?php if ($_GET['cate_id']): ?>
		setTimeout(function(){
			$('.itemKey<?php echo $_GET['cate_id']; ?>').trigger('click');
		},500);
		<?php endif; ?>
	})
</script>
<div id="main">
  	<div id="gcategory" class="category-viewport clearfix">
    	<div id="left-part" class="jd-category-tab left-part">
        	<div class="wraper">
                <ul id="category11" class="inner-wrap">
                    <?php $_from = $this->_var['gcategorys']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'gcategory');$this->_foreach['fe_gcategory'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_gcategory']['total'] > 0):
    foreach ($_from AS $this->_var['gcategory']):
        $this->_foreach['fe_gcategory']['iteration']++;
?>
                    <li class="<?php if (($this->_foreach['fe_gcategory']['iteration'] <= 1)): ?>active<?php endif; ?> tt-item itemKey<?php echo $this->_var['gcategory']['id']; ?>" >
                        <a href="javascript:;"><?php echo htmlspecialchars($this->_var['gcategory']['value']); ?></a>
                    </li>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </ul>
            </div>
    	</div>
    	<div class="jd-category-content right-part" id="right-part"> 
        	<div class="jd-category-content-wrapper">
            	<div id="branchList">	
                	<?php $_from = $this->_var['gcategorys']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'gcategory');$this->_foreach['fe_gcategory1'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_gcategory1']['total'] > 0):
    foreach ($_from AS $this->_var['gcategory']):
        $this->_foreach['fe_gcategory1']['iteration']++;
?>
                    <div class="content-item <?php if (! ($this->_foreach['fe_gcategory1']['iteration'] <= 1)): ?>hidden<?php endif; ?>">
                       <?php if ($this->_var['gcategory']['gads']): ?>
                        <div class="jd-category-third-promotion">
                            <?php $_from = $this->_var['gcategory']['gads']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'ads');$this->_foreach['fe_gad'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_gad']['total'] > 0):
    foreach ($_from AS $this->_var['ads']):
        $this->_foreach['fe_gad']['iteration']++;
?>
                            <a href="<?php echo $this->_var['ads']['link_url']; ?>" class="mb10"><img src="<?php echo $this->_var['ads']['file_path']; ?>" /></a>
                            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                        </div>
                        <?php endif; ?>
                        <?php $_from = $this->_var['gcategory']['children']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'child');$this->_foreach['fe_gcategory'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_gcategory']['total'] > 0):
    foreach ($_from AS $this->_var['child']):
        $this->_foreach['fe_gcategory']['iteration']++;
?>
                        <div class="jd-category-div ct-item">
							<h4><a href="<?php echo url('app=search&cate_id=' . $this->_var['child']['id']. ''); ?>"><?php echo $this->_var['child']['value']; ?></a></h4>
                            <ul class="jd-category-style-1 clearfix"<?php if (! $this->_var['child']['children']): ?> style="padding:0;<?php endif; ?>">
                                <?php $_from = $this->_var['child']['children']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'child2');$this->_foreach['fe_gcategory'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_gcategory']['total'] > 0):
    foreach ($_from AS $this->_var['child2']):
        $this->_foreach['fe_gcategory']['iteration']++;
?>
                                <li>
                                   <a href="<?php echo url('app=search&cate_id=' . $this->_var['child2']['id']. ''); ?>">
                                        <img src="<?php echo $this->_var['child2']['category_image']; ?>" />
                                        <span><?php echo $this->_var['child2']['value']; ?></span>
                                     </a>
                                </li>
                                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                            </ul>
                        </div> 
                        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
                    </div>   
 					<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
					
                </div>
            </div>
        </div>
        
        
  	</div>
</div>


<?php echo $this->fetch('footer.html'); ?>