<?php echo $this->fetch('member.header.html'); ?> 
<script>
function ajax_gcategory(id, multilevel)
{
	$('.J_GetPublishId').val(id);
	
	var url = SITE_URL + '/index.php?app=my_goods&act=ajax_gcategory';
	
	$.getJSON(url,{'cate_id':id, 'multilevel' : multilevel},function(data){
		if(data.done)
		{
			li = '';
			$.each(data.retval,function(id, result){
				str = '';
				$.each(result, function(i, item) {
					noChildClass = selectedClass = ''
					if(item.hasChild==0) {
						noChildClass = 'cc-tree-each-noChild';
					}
					if(item.selected==1) {
						selectedClass = 'cc-tree-each-select';
					}
					str += '<ul class="cc-tree-gcont">';
					str += ' <li class="cc-tree-each '+noChildClass+' '+selectedClass+'" hasChild="'+item.hasChild+'" parent_id="'+item.parent_id+'" id="'+item.cate_id+'">'+item.cate_name+'</li>';
					str += '</ul>';
				});
						
				if(str != '') {	
					li += '<li class="cc-list-each" parent_id="'+id+'"><ul class="cc-tree"><li class="cc-tree-group">' + str + '</li></ul></li>';
				}
			})
			
			if(li != '') {
				if(multilevel == 1) {
					$('.cc-list').html('');
				}
				$('.cc-list').append(li);
			}
		
			category_path = '';
			$('.cc-list').find('.cc-tree-each-select').each(function(index, element) {
				category_path += '>&nbsp;' + $(this).text()+'&nbsp;';
			});
					
			if($('.cc-list').find('.cc-tree-each-select:last').attr('hasChild')==0){
				$('.J_CategoryPath').html(category_path.substr(1));
				$('#J_Publish').attr('class','submit').attr('disabled', false);
			}
			else
			{
				$('.J_CategoryPath').html(category_path.substr(1) + '...');
				$('#J_Publish').attr('class','').attr('disabled', true);
			}
		}
	});
}


$(function(){
	
	$('#J_Publish').click(function(){
		cate_id = $('.J_GetPublishId').val();
		document.location.href='index.php?app=my_goods&&act=<?php echo $this->_var['action']; ?>&id=<?php echo $this->_var['id']; ?>&cate_id='+cate_id;
	});
	
	$('.J_CategoryTree').on('click', '.cc-tree-each', function(){
		$(this).parent().parent().parent().parent().nextAll('li').remove();
		
		$(this).parent().parent().find('.cc-tree-each').each(function(){
			$(this).removeClass('cc-tree-each-select');
		});
		$(this).addClass('cc-tree-each-select');
		
		ajax_gcategory(this.id, 0);

	});
	
	$('.J_SearchKeyWord').focus(function(){
		$('.J_SearchHolder').hide();
	});
	$('.J_SearchKeyWord').blur(function(){
		if($('.J_SearchKeyWord').val()=='') {
			$('.J_SearchHolder').show();
		}
		$('.J_SearchList').hide();
	});
	$('.J_SearchButton').click(function(){
		if($('.J_SearchKeyWord').val()=='') {
			alert('请输入分类名称');
			return;
		}
		
		var url = SITE_URL + '/index.php?app=my_goods&act=ajax_search_gcategory';

		$.getJSON(url,{'keyword' : $('.J_SearchKeyWord').val()}, function(data){
			html = '';
			if(data.done) {
				$('.J_SearchList').html('');
				$.each(data.retval,function(i, item){	
					html += '<li data-id="'+i+'"><span>'+item+'</span></li>';
				});
			}
			if(html=='') {
				html = '<p class="ml10">'+lang.no_records+'</p>';
			}
			$('.J_SearchList').append(html);
			$('.J_SearchList').show();
		});
	});
	
	$('.J_SearchList').on('click', 'li', function(){
		id = $(this).attr('data-id');
		$('.J_SearchList').hide();

		ajax_gcategory(id, 1);
	});

	
});
</script>
<div id="main">
  <div class="wrapful">
  	<div style="padding:15px 24px;">
    <div id="J_CategorySearch" class="category-search">
      <div class="searchbox"> <span calss="caption">类目搜索：</span>
        <label class="search-holder J_SearchHolder">请输入分类名称</label>
        <input name="keyword" class="search-keyword J_SearchKeyWord" type="text" />
        <button type="button" class="btn-primary J_SearchButton">
        <div class="btn-txt">快速找到类目</div>
        </button>
        <ul class="J_SearchList search-list hidden">
        </ul>
      </div>
    </div>
    <div class="cate-cascading J_CategoryTree">
      <div class="cc-listwrap">
        <ol class="cc-list">
          <li class="cc-list-each" parent_id="0">
            <ul class="cc-tree">
              <li class="cc-tree-group"> <?php $_from = $this->_var['gcategories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
                <ul class="cc-tree-gcont">
                  <li class="cc-tree-each <?php if (! $this->_var['item']['hasChild']): ?>cc-tree-each-noChild<?php endif; ?>" id="<?php echo $this->_var['key']; ?>" hasChild="<?php echo $this->_var['item']['hasChild']; ?>" parent_id="<?php echo $this->_var['item']['parent_id']; ?>"><?php echo $this->_var['item']['cate_name']; ?></li>
                </ul>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> </li>
            </ul>
          </li>
        </ol>
      </div>
    </div>
    <div class="cate-path">
      <dl>
        <div class="clearfix">
          <dt>您当前选择的是：</dt>
          <dd>
            <div class="text J_CategoryPath">无</div>
          </dd>
        </div>
      </dl>
      <span class="arrow up"></span> </div>
    <div class="w cateBottom">
      <div class="cateBtn">
        <input type="hidden" value="" class="J_GetPublishId"/>
        <button disabled="disabled" hidefocus="true" id="J_Publish" />
        我已选择好分类，现在上传商品
        </button>
      </div>
    </div>
    <div class="agreement"><?php echo $this->_var['article']['content']; ?></div>
    </div>
  </div>
</div>
<?php echo $this->fetch('member.footer.html'); ?>