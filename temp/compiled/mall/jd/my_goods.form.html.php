<?php echo $this->fetch('member.header.html'); ?>
<?php echo $this->_var['images_upload']; ?>
<?php echo $this->_var['editor_upload']; ?>
<?php echo $this->_var['build_editor']; ?>
<style>
.box_arr .table_btn {width: 222px;}
.box_arr .table_btn a {float: left;}
.box_arr .table_btn a.disable_spec { background: url(<?php echo $this->res_base . "/" . 'images/member/btn.gif'; ?>) repeat 0 -1018px; float: right; }
.add_spec .add_link {color:#919191;}
.add_spec .add_link:hover {color:red;}
add_spec h2 {padding-left: 10px;}
.width7{width: 250px;}
.f_l{float:left;}
.mls_id {width: 0; filter: alpha(opacity=0);opacity: 0;}

.spec_image{width:90px;text-align:center; position:relative;}
.spec_image input{display:block;z-index:10; position:absolute;cursor: hand;  opacity:0; filter: alpha(opacity=0); width:65px;}
.spec_image i{font-size:20px;font-weight:normal; color: #AD9696;display:block; width:60px; height:22px; margin-left:20px;margin-top:5px;}
.spec_image i b{ position:absolute; top:5px; right:10px; display:none;width: 10px; height: 10px; background: url('<?php echo $this->res_base . "/" . 'images/member/ico.gif'; ?>') 0 -634px; cursor:pointer; z-index:99; }
.spec_image i.no-pic{ background:url('<?php echo $this->res_base . "/" . 'images/pic.png'; ?>') no-repeat center center ; background-size:50%;}
</style>
<script type="text/javascript">
//<!CDATA[
var SPEC = <?php echo $this->_var['goods']['spec_json']; ?>;


function add_uploadedfile(file_data)
{
    if(file_data.instance == 'goods_image'){
        $('#goods_images').append('<li style="z-index:4" ectype="handle_pic" file_id="'+ file_data.file_id +'" thumbnail="<?php echo $this->_var['site_url']; ?>/'+ file_data.thumbnail +'"><input type="hidden" value="'+ file_data.file_id +'" name="goods_file_id[]"/><div class="pic"><img src="<?php echo $this->_var['site_url']; ?>/'+ file_data.thumbnail +'" width="100" height="100" alt="" /><div ectype="handler" class="bg"><p class="operation"><span class="cut_in" ectype="set_cover" ecm_title="设为封面"></span><span class="delete" ectype="drop_image" ecm_title="删除"></span></p></div></div></li>');
                trigger_uploader();
        if($('#big_goods_image').attr('src') == '<?php echo $this->_var['goods']['default_goods_image']; ?>'){
            set_cover(file_data.file_id);
        }
        if(GOODS_SWFU.getStats().progressNum == 0){
            window.setTimeout(function(){
                $('#uploader').css('opacity', 0);
				//$('*[ectype="handle_pic"]').css('z-index', 999);
                $('#open_uploader').find('.show').attr('class','hide');
            }, 5000);
        }
    }else if(file_data.instance == 'desc_image'){
        $('#desc_images').append('<li style="z-index:4" file_name="'+ file_data.file_name +'" file_path="'+ file_data.file_path +'" ectype="handle_pic" file_id="'+ file_data.file_id +'"><input type="hidden" name="desc_file_id[]" value="'+ file_data.file_id +'"><div class="pic" style="z-index: 2;"><img src="<?php echo $this->_var['site_url']; ?>/'+ file_data.file_path +'" width="80" height="80" alt="'+ file_data.file_name +'" /></div><div ectype="handler" class="bg" style="z-index: 3;display:none"><p class="operation"><a href="javascript:void(0);" class="cut_in" ectype="insert_editor" ecm_title="插入编辑器"></a><span class="delete" ectype="drop_image" ecm_title="删除"></span></p><p class="name">'+ file_data.file_name +'</p></div></li>');
                trigger_uploader();
        if(EDITOR_SWFU.getStats().progressNum == 0){
            window.setTimeout(function(){
				//$('*[ectype="handle_pic"]').css('z-index', 999);
                $('#editor_uploader').css('opacity', 0);
            },5000);
        }
    }
}


function set_cover(file_id){
    if(typeof(file_id) == 'undefined'){
        $('#big_goods_image').attr('src','<?php echo $this->_var['goods']['default_goods_image']; ?>');
        return;
    }
    var obj = $('*[file_id="'+ file_id +'"]');
    $('*[file_id="'+ file_id +'"]').clone(true).prependTo('#goods_images');
    $('*[ectype="handler"]').hide();
    $('#big_goods_image').attr('src',obj.attr('thumbnail'));
    obj.remove();
}

$(function(){
     $('#goods_form').validate({
        errorPlacement: function(error, element){
            $(element).next('label').remove();
            $(element).after(error);
        },
        success       : function(label){
			label.removeClass('error');
            label.addClass('validate_right').text('OK!');
        },
        onkeyup : false,
        rules : {
            goods_name : {
                required   : true
            },
            price      : {
                number     : true,
                required : true,
                min : 0
            },
            stock      : {
                digits    : true
            },
			
			max_exchange : {
				digits : true,
				min    : 0
			},
			

            cate_id    : {
                remote   : {
                    url  : 'index.php?app=my_goods&act=check_mgcate',
                    type : 'get',
                    data : {
                        cate_id : function(){
                            return $('#cate_id').val();
                        }
                    }
                }
            },
            "exclusive[discount]" : {
                number     : true,
				min		   : 0.01,
				max  	   : 9.99
            },
            "exclusive[decrease]" : {
                 number    : true,
				 min       : 0.01
            }
        },
        messages : {
            goods_name  : {
                required   : '商品名称不能为空'
            },
            price       : {
                number     : '此项仅能为数字',
                required : '价格不能为空',
                min : '价格必须大于或等于零'
            },
            stock       : {
                digits  : '此项仅能为数字'
            },
			
			max_exchange : {
				digits : '此项仅能为数字',
				min    : '抵扣的积分值必须为大于或等于0的整数'
			},
			
            cate_id     : {
                remote  : '请选择商品分类（必须选到最后一级）'
            },
            "exclusive[discount]"  : {
                number     : '折扣必须是大于0.01小于9.99的数值',
                min : '折扣必须是大于0.01小于9.99的数值',
				max : '折扣必须是大于0.01小于9.99的数值'
            },
            "exclusive[decrease]" : {
                number  : '减价金额必须是数值',
				min	     : '减价金额必须大于0.01元'
            }
        }
    });
    // init cover
    set_cover($("#goods_images li:first-child").attr('file_id'));

    // init spec
    spec_update();
    
	$('input[ectype="spec_image"]').wrap("<form action='index.php?app=my_goods&act=ajax_spec_image' method='post' enctype='multipart/form-data'></form>");
	$('input[ectype="spec_image"]').change(function(){
		  var sp = $(this).parents('.spec_image').find('i');
		  $(this).parent('form').ajaxSubmit({
			  dataType:  'json',
			  success: function(data) {
				  if(data.done){
					sp.removeClass('no-pic');
					sp.html('<img width="30" height="30" src="'+ data.retval +'"><b title="删除"></b>');
				  }else{
					  alert(data.msg);
				  }
			  }
		  });
		  return false; // 阻止表单自动提交事件
	});
    /* 手机专享 */
    $('.J_ExclusiveCheckbox').click(function(){
    	if($(this).prop('checked')) {
    		$('.J_ExclusiveDetailSetting').show();
    	} else {
    		$('.J_ExclusiveDetailSetting').hide();
    	}
    });
});
//]]>
</script>
<div id="main" class="clearfix"> <?php echo $this->fetch('member.menu.html'); ?>
  <div id="right"> <?php echo $this->fetch('member.curlocal.html'); ?>
    <div class="wrapful"> <?php echo $this->fetch('member.submenu.html'); ?>
      <div  class="wrap publish">
        <div class="public">
          <form method="post" id="goods_form">
            <div class="information_index">
              <div class="add_spec" dialog_title="编辑商品规格" ectype="dialog_contents" style="display: none"> 
                <!--<form>-->
                <p>您最多可以添加两种规格（如：颜色和尺码）规格名称可以自定义,如只有一项规格另一项留空;<br/>规格图片关联第一项规格，如果第一项规格名称相同，请把规格图片放置在该规格名称最前面的一项。</p>
                <div class="table" ectype="spec_editor">
                  <ul class="th">
                    <li>
                      <input col="spec_name_1" type="text" class="text width4" />
                    </li>
                    <li>
                      <input col="spec_name_2" type="text" class="text width4" />
                    </li>
                    <li class="distance1">价格</li>
                    <li class="distance1">库存</li>
                    <li class="distance2">货号</li>
                    <li class="distance1">规格图片</li>
                    <li class="distance3">操作</li>
                  </ul>
                  <ul class="td" ectype="spec_item">
                    <li>
                      <input item="spec_1" type="text" class="text width4" />
                    </li>
                    <li>
                      <input item="spec_2" type="text" class="text width4" />
                    </li>
                    <li>
                      <input item="price" type="text" class="text width4" />
                    </li>
                    <li>
                      <input item="stock" type="text" class="text width4" />
                    </li>
                    <li>
                      <input item="sku" type="text" class="text width8" />
                      <input item="spec_id" type="hidden" />
                    </li>
                    <li class="spec_image">
                      <input ectype="spec_image" type="file" name="file" size="1" hidefocus="true" maxlength="0" runat="server">
                      <i class="no-pic"></i> </li>
                    <li class="padding3"> <span ectype="up_spec_item" class="up_btn"></span> <span ectype="down_spec_item" class="down_btn"></span> <span ectype="drop_spec_item" class="delete_btn"></span> </li>
                  </ul>
                  <ul>
                    <li class="add"><a href="javascript:;" ectype="add_spec_item" class="add_link">添加新的规格属性</a></li>
                  </ul>
                </div>
                <div class="btn_wrap">
                  <input ectype="save_spec" type="submit" class="btn-alipay" value="保存规格" />
                </div>
                <!--</form>--> 
              </div>
            </div>
            <h5 class="box-title">1.商品基本信息</h5>
            <ul class="box-body">
              <li class="subbox clearfix">
                <label class="subbox-title">商品类目：</label>
                <div class="subbox-body"> 
                  <?php $_from = $this->_var['publish_gcategory']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');$this->_foreach['fe_item'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_item']['total'] > 0):
    foreach ($_from AS $this->_var['item']):
        $this->_foreach['fe_item']['iteration']++;
?> 
                  <?php if (! ($this->_foreach['fe_item']['iteration'] <= 1)): ?> > <?php endif; ?> 
                  <span class="border-field"><?php echo $this->_var['item']['cate_name']; ?></span> 
                  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
                  <?php if ($_GET['act'] == 'edit'): ?> 
                  <a class="btn-gedit" href="<?php echo url('app=my_goods&act=publish&id=' . $_GET['id']. ''); ?>">编辑类目</a> 
                  <?php else: ?> 
                  <a class="btn-gedit" href="<?php echo url('app=my_goods&act=add'); ?>">编辑类目</a> 
                  <?php endif; ?>
                  <input type="text" id="cate_id" name="cate_id" value="<?php echo $this->_var['goods']['cate_id']; ?>" class="mls_id" />
                  <input type="hidden" name="cate_name" value="<?php echo htmlspecialchars($this->_var['goods']['cate_name']); ?>" class="mls_names" />
                </div>
              </li>
              <li class="subbox clearfix">
                <label class="subbox-title">商品标题：</label>
                <div class="subbox-body">
                  <input style="width:500px;" type="text" name="goods_name" value="<?php echo htmlspecialchars($this->_var['goods']['goods_name']); ?>" class="text width_normal" />
                </div>
              </li>
              <?php if ($this->_var['prop_list']): ?>
              <li class="subbox clearfix">
                <label class="subbox-title">商品属性：</label>
                <div class="subbox-body">
                  <div id="props"> <?php $_from = $this->_var['prop_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'prop');if (count($_from)):
    foreach ($_from AS $this->_var['prop']):
?>
                    <dl class="clearfix">
                      <dt class="float-left"><?php echo $this->_var['prop']['name']; ?>：</dt>
                      <dd class="float-left"> 
                        <?php if ($this->_var['prop']['prop_type'] == 'checkbox' || $this->_var['prop']['prop_type'] == 'radio'): ?> 
                        <?php $_from = $this->_var['prop']['value']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?>
                        <label>
                          <input type="<?php echo $this->_var['prop']['prop_type']; ?>" name="props[<?php echo $this->_var['prop']['pid']; ?>][]" value="<?php echo $this->_var['item']['pid']; ?>:<?php echo $this->_var['item']['vid']; ?>" <?php if ($this->_var['item']['selected']): ?> checked="checked"<?php endif; ?> />
                          <?php if ($this->_var['prop']['is_color_prop']): ?><i <?php if ($this->_var['item']['color_value']): ?>style="background:<?php echo $this->_var['item']['color_value']; ?>"<?php else: ?>class="duocai"<?php endif; ?>></i><?php endif; ?>
                          <?php echo $this->_var['item']['prop_value']; ?> </label>
                        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
                        <?php else: ?>
                        <select name="props[<?php echo $this->_var['prop']['pid']; ?>][]">
                          <option value=""></option>
                          
                        <?php $_from = $this->_var['prop']['value']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?>
                        
                          <option value="<?php echo $this->_var['item']['pid']; ?>:<?php echo $this->_var['item']['vid']; ?>" <?php if ($this->_var['item']['selected']): ?> selected="selected"<?php endif; ?>><?php echo $this->_var['item']['prop_value']; ?></option>
                          
                        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                      
                        </select>
                        <?php endif; ?> 
                      </dd>
                    </dl>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> </div>
                  <div class="notice-word mt5">
                    <p style="margin-bottom:0;">填错宝贝属性，可能会引起宝贝下架，影响您的正常销售。请认真准确填写</p>
                  </div>
                </div>
              </li>
              <?php endif; ?>
              
              <li class="subbox clearfix">
                <label class="subbox-title">商品品牌：</label>
                <div class="subbox-body">
                  <select name="brand">
                    <option value="">请选择...</option>
                    <?php $_from = $this->_var['brand_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?>
                    <option value="<?php echo htmlspecialchars($this->_var['item']['brand_name']); ?>" <?php if ($this->_var['goods']['brand'] == $this->_var['item']['brand_name']): ?> selected="selected"<?php endif; ?>><?php echo htmlspecialchars($this->_var['item']['brand_name']); ?></option>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                  </select>
                </div>
              </li>
              <li class="subbox clearfix"  ectype="no_spec">
                <label class="subbox-title">商品价格：</label>
                <div class="subbox-body">
                  <input name="spec_id" value="<?php echo $this->_var['goods']['_specs']['0']['spec_id']; ?>" type="hidden" />
                  <input name="price" value="<?php echo $this->_var['goods']['_specs']['0']['price']; ?>" type="text" class="text width_short" />
                </div>
              </li>
              <?php if ($this->_var['integral_enabled']): ?>
              <li class="subbox clearfix"  ectype="max_exchange">
                <label class="subbox-title">积分抵扣：</label>
                <div class="subbox-body">
                  <input name="max_exchange" value="<?php echo $this->_var['goods']['max_exchange']; ?>" type="text" class="text width_short" />
                  <span class="gray">设置允许买家最多可使用多少积分抵扣价款。</span> </div>
              </li>
              <?php endif; ?>
              
              <li class="subbox clearfix">
                <label class="subbox-title">商品规格：</label>
                <div class="subbox-body">
                  <div class="arrange">
                    <div class="box_arr" ectype="no_spec"  style="display: none;">
                      <p class="pos_btn"><a ectype="add_spec" href="javascript:;" class="add_btn">开启规格</a></p>
                      <p class="pos_txt">您最多可以添加两种商品规格（如：颜色，尺码）如商品没有规格则不用添加</p>
                    </div>
                    <div class="box_arr has_spec" ectype="has_spec"  style="display: none;">
                      <table ectype="spec_result">
                        <tr>
                          <th col="spec_name_1">loading..</th>
                          <th col="spec_name_2">loading..</th>
                          <th col="price">价格</th>
                          <th col="stock">库存</th>
                          <th col="sku">货号</th>
                          <th col="spec_image" width="80">规格图片</th>
                        </tr>
                        <tr ectype="spec_item" style="display:none">
                          <td item="spec_1"></td>
                          <td item="spec_2"></td>
                          <td item="price"></td>
                          <td item="stock"></td>
                          <td item="sku"></td>
                          <td item="spec_image"></td>
                        </tr>
                      </table>
                      <p class="table_btn"> <a ectype="edit_spec" href="javascript:;" class="add_btn edit_spec">编辑规格</a> <a ectype="disable_spec" href="javascript:;" class="add_btn disable_spec">关闭规格</a> </p>
                    </div>
                  </div>
                </div>
              </li>
              <li class="subbox clearfix"  ectype="no_spec">
                <label class="subbox-title">商品库存：</label>
                <div class="subbox-body">
                  <input name="stock" value="<?php echo $this->_var['goods']['_specs']['0']['stock']; ?>" type="text" class="text width_short" />
                </div>
              </li>
              <li class="subbox clearfix"  ectype="no_spec">
                <label class="subbox-title">商品货号：</label>
                <div class="subbox-body">
                  <input name="sku" value="<?php echo $this->_var['goods']['_specs']['0']['sku']; ?>" type="text" class="text width_normal" />
                </div>
              </li>
            </ul>
            <h5 class="box-title">2.商品图片及描述</h5>
            <ul class="box-body">
              <li class="subbox clearfix">
                <label class="subbox-title">商品图片：</label>
                <div class="subbox-body">
                  <div class="multimage-wrapper">
                    <ul class="multimage-tabs clearfix">
                      <li class="selected">本地上传</li>
                      <!--<li>图片空间</li>-->
                    </ul>
                    <div class="multimage-panels clearfix">
                      <div class="upload_btn">
                        <div class="btn-upload-image" id="open_uploader"><b class="hide">上传商品图片</b></div>
                        <div class="upload_con" id="uploader" style="opacity:0;filter:Alpha(opacity=0)" >
                          <div class="upload_con_top"></div>
                          <div class="upload_wrap">
                            <ul>
                              <li class="GOODS_SWFU_filePicker">
                                <div id="divSwfuploadContainer">
                                  <div id="divButtonContainer"> <span id="spanButtonPlaceholder"></span> </div>
                                </div>
                              </li>
                              <li>
                                <iframe src="index.php?app=comupload&act=view_iframe&id=<?php echo $this->_var['id']; ?>&belong=<?php echo $this->_var['belong']; ?>&instance=goods_image" width="86" height="30" scrolling="no" frameborder="0"></iframe>
                              </li>
                              <li id="open_remote" class="btn2">远程地址</li>
                            </ul>
                            <div id="remote" class="upload_file" style="display:none">
                              <iframe src="index.php?app=comupload&act=view_remote&id=<?php echo $this->_var['id']; ?>&belong=<?php echo $this->_var['belong']; ?>&instance=goods_image" width="272" height="39" scrolling="no" frameborder="0"></iframe>
                            </div>
                            <div id="goods_upload_progress"></div>
                          </div>
                          <div class="upload_con_bottom"></div>
                        </div>
                      </div>
                      <div class="text">支持JPEG和静态的GIF格式图片，不支持GIF动画图片，上传图片大小不能超过2M.浏览文件时可以按住ctrl或shift键多选</div>
                    </div>
                    <div class="multimage-piclist clearfix">
                      <div class="small_pic">
                        <ul id="goods_images">
                          <?php $_from = $this->_var['goods_images']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods_iamge');if (count($_from)):
    foreach ($_from AS $this->_var['goods_iamge']):
?>
                          <li ectype="handle_pic" file_id="<?php echo $this->_var['goods_iamge']['file_id']; ?>" thumbnail="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['goods_iamge']['thumbnail']; ?>">
                            <input type="hidden" name="goods_file_id[]" value="<?php echo $this->_var['goods_iamge']['file_id']; ?>">
                            <div class="pic"> <img src="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['goods_iamge']['thumbnail']; ?>" width="100" height="100" />
                              <div ectype="handler" class="bg">
                                <p class="operation"> <span class="cut_in" ectype="set_cover" ecm_title="设为封面"></span> <span class="delete" ectype="drop_image" ecm_title="删除"></span> </p>
                              </div>
                            </div>
                          </li>
                          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                        </ul>
                        <div class="clear"></div>
                      </div>
                      <div class="notice-word" style="margin-right:14px;">
                        <p class="yellow">注：第一张为商品主图，也即是在前台页面显示。将鼠标移至图片上可以调整顺序。</p>
                      </div>
                    </div>
                  </div>
                </div>
              </li>
              <li class="subbox clearfix">
                <label class="subbox-title">商品描述：</label>
                <div class="subbox-body">
                  <div class="add_wrap">
                    <div class="editor">
                      <div>
                        <textarea name="description" id="description"  style="width:713px; height:400px;">
                                    			<?php echo htmlspecialchars($this->_var['goods']['description']); ?>
                                    			</textarea>
                      </div>
                      <div class="multimage-wrapper descimage-wrapper">
                        <ul class="multimage-tabs clearfix">
                          <li class="selected">插入描述图片</li>
                        </ul>
                        <div class="multimage-panels clearfix">
                          <div style=" position: relative; top: 10px; z-index: 5;"> <a class="btn-upload-image" id="open_editor_uploader"><b>上传图片</b></a>
                            <div class="upload_con" id="editor_uploader" style="opacity:0; filter:Alpha(opacity=0)">
                              <div class="upload_con_top"></div>
                              <div class="upload_wrap">
                                <ul>
                                  <li class="EDITOR_SWFU_filePicker">
                                    <div id="divSwfuploadContainer">
                                      <div id="divButtonContainer"> <span id="spanButtonPlaceholder"></span> </div>
                                    </div>
                                  </li>
                                  <li>
                                    <iframe src="index.php?app=comupload&act=view_iframe&id=<?php echo $this->_var['id']; ?>&belong=<?php echo $this->_var['belong']; ?>&instance=desc_image" width="86" height="30" scrolling="no" frameborder="0"></iframe>
                                  </li>
                                  <li id="open_editor_remote" class="btn2">远程地址</li>
                                </ul>
                                <div id="editor_remote" class="upload_file" style="display:none">
                                  <iframe src="index.php?app=comupload&act=view_remote&id=<?php echo $this->_var['id']; ?>&belong=<?php echo $this->_var['belong']; ?>&instance=desc_image" width="272" height="39" scrolling="no" frameborder="0"></iframe>
                                </div>
                                <div id="editor_upload_progress"></div>
                              </div>
                              <div class="upload_con_bottom"></div>
                            </div>
                            <div class="text">支持JPEG和静态的GIF格式图片，不支持GIF动画图片，上传图片大小不能超过2M.浏览文件时可以按住ctrl或shift键多选</div>
                          </div>
                        </div>
                        <div class="multimage-piclist clearfix">
                          <ul id="desc_images" class="preview J_descriptioneditor">
                            <?php $_from = $this->_var['desc_images']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'desc_image');if (count($_from)):
    foreach ($_from AS $this->_var['desc_image']):
?>
                            <li ectype="handle_pic" file_name="<?php echo htmlspecialchars($this->_var['desc_image']['file_name']); ?>" file_path="<?php echo $this->_var['desc_image']['file_path']; ?>" file_id="<?php echo $this->_var['desc_image']['file_id']; ?>">
                              <input type="hidden" name="desc_file_id[]" value="<?php echo $this->_var['desc_image']['file_id']; ?>">
                              <div class="pic"> <img src="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['desc_image']['file_path']; ?>" width="80" height="80" alt="<?php echo htmlspecialchars($this->_var['desc_image']['file_name']); ?>" title="<?php echo htmlspecialchars($this->_var['desc_image']['file_name']); ?>" /> </div>
                              <div ectype="handler" class="bg">
                                <p class="operation"> <a class="cut_in" ectype="insert_editor" href="javascript:void(0);" ecm_title="插入编辑器"></a> <span class="delete" ectype="drop_image" ecm_title="删除"></span> </p>
                              </div>
                            </li>
                            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                          </ul>
                        </div>
                        <?php if ($this->_var['desc_images']): ?>
                        <div class="notice-word" style="padding:14px;">
                          <p class="yellow">插入方法：将鼠标移至需要插入的图片上面，然后点击插入按钮，即可将图片插入到编辑器中。</p>
                        </div>
                        <?php endif; ?> 
                      </div>
                    </div>
                  </div>
                </div>
              </li>
              <?php if ($this->_var['sgcategories']): ?>
              <li class="subbox clearfix">
                <label class="subbox-title">本店分类：</label>
                <div class="subbox-body">
                  <div class="shop-cat-list">
                    <div class="shop-cat-each"> 
                      <?php $_from = $this->_var['sgcategories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'sgcate');if (count($_from)):
    foreach ($_from AS $this->_var['sgcate']):
?>
                      <p>
                        <label>
                          <input type="checkbox" name="sgcate_id[]" value="<?php echo $this->_var['sgcate']['cate_id']; ?>" <?php if ($this->_var['sgcate']['selected']): ?> checked="checked"<?php endif; ?> />
                          <?php echo $this->_var['sgcate']['cate_name']; ?></label>
                      </p>
                      <?php $_from = $this->_var['sgcate']['children']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?>
                      <p class="children-1">
                        <label>
                          <input type="checkbox" name="sgcate_id[]" value="<?php echo $this->_var['item']['cate_id']; ?>" <?php if ($this->_var['item']['selected']): ?> checked="checked"<?php endif; ?>/>
                          <?php echo $this->_var['item']['cate_name']; ?></label>
                      </p>
                      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
                      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
                    </div>
                  </div>
                </div>
              </li>
              <?php endif; ?>
            </ul>
            <h5 class="box-title">3.物流及配送设置</h5>
            <ul class="box-body">
              <li class="subbox clearfix">
                <label class="subbox-title">运费模板：</label>
                <div class="subbox-body">
                  <select class="text" name="delivery_template_id">
                    <?php $_from = $this->_var['deliverys']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'delivery');$this->_foreach['fe_delivery'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_delivery']['total'] > 0):
    foreach ($_from AS $this->_var['delivery']):
        $this->_foreach['fe_delivery']['iteration']++;
?>
                    <option value="<?php echo $this->_var['delivery']['template_id']; ?>" <?php if ($_GET['act'] == 'add' && ($this->_foreach['fe_delivery']['iteration'] <= 1)): ?> checked="checked" <?php elseif ($this->_var['goods']['delivery_template_id'] == $this->_var['delivery']['template_id']): ?> selected="selected" <?php endif; ?> ><?php echo $this->_var['delivery']['name']; ?></option>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                  </select>
                  <a href="<?php echo url('app=my_delivery'); ?>" target="_blank" style="color:blue; text-decoration:none">运费模板列表</a> </div>
              </li>
            </ul>
            <h5 class="box-title">4.其他设置</h5>
            <ul class="box-body">
              <?php if ($this->_var['growbuy_list']): ?>
              <li class="subbox clearfix">
                <label class="subbox-title">加<span style="margin: 5px">价</span>购：</label>
                <div class="subbox-body" style="padding-left:20px;"> 
                  <?php $_from = $this->_var['growbuy_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'list');if (count($_from)):
    foreach ($_from AS $this->_var['list']):
?>
                  <label>
                    <input <?php if ($this->_var['list']['selected']): ?> checked="checked"<?php endif; ?> type="checkbox" name="growbuy[]" value="<?php echo $this->_var['list']['psid']; ?>" style="margin-left:-17px;" />
                    <?php $_from = $this->_var['list']['items']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?> 
                    <a href="<?php echo url('app=goods&id=' . $this->_var['item']['goods_id']. ''); ?>" target="_blank">[查看商品]</a> <?php echo $this->_var['item']['goods_name']; ?> 
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
                    <span class="red yahei">+<?php echo price_format($this->_var['list']['money']); ?></span> </label>
                  <br />
                  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
                </div>
              </li>
              <?php endif; ?> 
              <?php if ($this->_var['exclusive']): ?>
              <li class="subbox clearfix">
                <label class="subbox-title">手机专享：</label>
                <div class="subbox-body"> <span class="clearfix">
                  <label class="switch-checkbox-radio box-align-center float-left J_SwtcherInput <?php if ($this->_var['exclusive']['selected'] != '0'): ?>checked<?php endif; ?>" for="exclusive[status]"><em class="block flex1 gray"></em><span class="switcher-style block"></span></label>
                  <input type="checkbox" name="exclusive[status]" value="1" id="exclusive[status]" class="J_ExclusiveCheckbox hidden" <?php if ($this->_var['exclusive']['selected'] != '0'): ?>checked="checked" <?php endif; ?>/>
                  <em class="gray ml10 inline-block" style="margin-top:3px;"><?php echo $this->_var['exclusive']['desc']; ?></em> </span>
                  <div class="J_ExclusiveDetailSetting mt10 <?php if ($this->_var['exclusive']['selected'] == '0'): ?>hidden<?php endif; ?>"> 享
                    <input type="text" name="exclusive[discount]" id="exclusive[discount]" class="width_short" value="<?php echo $this->_var['exclusive']['config']['discount']; ?>">
                    折，或减
                    <input type="text"  name="exclusive[decrease]" id="exclusive[decrease]" class="width_short" value="<?php echo $this->_var['exclusive']['config']['decrease']; ?>">
                    元 <em class="gray ml10 inline-block mt5">如果留空，则执行默认优惠。仅需设置折扣<font class="red">或</font>减价</em> </div>
                </div>
              </li>
              <?php endif; ?>
              <li class="subbox clearfix">
                <label class="subbox-title">商品上架：</label>
                <div class="subbox-body">
                  <label class="switch-checkbox-radio box-align-center J_SwtcherInput <?php if ($this->_var['goods']['if_show']): ?>checked<?php endif; ?>" for="if_show"><em class="block flex1 gray"></em><span class="switcher-style block"></span></label>
                  <input type="checkbox" name="if_show" value="1" id="if_show" class="hidden" <?php if ($this->_var['goods']['if_show']): ?>checked="checked" <?php endif; ?>/>
                </div>
              </li>
              <li class="subbox clearfix">
                <label class="subbox-title">商品推荐：</label>
                <div class="subbox-body clearfix">
                  <label class="switch-checkbox-radio inline-block box-align-center float-left J_SwtcherInput <?php if ($this->_var['goods']['recommended']): ?>checked<?php endif; ?>" for="recommended"><span class="switcher-style block"></span></label>
                  <input type="checkbox" name="recommended" value="1" id="recommended" class="hidden" <?php if ($this->_var['goods']['recommended']): ?>checked="checked" <?php endif; ?>/>
                  <span class="gray float-left block mt5 ml10">被推荐的商品会显示在店铺首页</span> </div>
              </li>
              <li class="subbox clearfix">
                <label class="subbox-title">分销提成：</label>
                <div class="subbox-body">
                  <input style="width:100px;margin-right:10px;" type="text" name="refer_reward_1" value="<?php echo htmlspecialchars($this->_var['goods']['refer_reward_1']); ?>" class="text width_normal" />
                  <input style="width:100px;margin-right:10px;" type="text" name="refer_reward_2" value="<?php echo htmlspecialchars($this->_var['goods']['refer_reward_2']); ?>" class="text width_normal" />
                  <input style="width:100px;margin-right:10px;" type="text" name="refer_reward_3" value="<?php echo htmlspecialchars($this->_var['goods']['refer_reward_3']); ?>" class="text width_normal" />
                  <span class="gray">请填写三级分销的比率,零代表无提成</span></div>
              </li>
              <li class="subbox clearfix">
                <label class="subbox-title">商品标签：</label>
                <div class="subbox-body">
                  <input style="width:300px;" type="text" name="tags" value="<?php echo htmlspecialchars($this->_var['goods']['tags']); ?>" class="text width_normal" />
                  <span class="gray">多个标签请用半角逗号隔开</span></div>
              </li>
            </ul>
            <div class="issuance">
              <input type="submit" class="btn" value="提交" />
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php echo $this->fetch('member.footer.html'); ?> 