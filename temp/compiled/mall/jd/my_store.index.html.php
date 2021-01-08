<?php echo $this->fetch('member.header.html'); ?>
<script type="text/javascript" src="https://api.map.baidu.com/api?v=2.0&ak=<?php echo $this->_var['baidukey']['browser']; ?>"></script>
<style>
.information .info table{width :auto;}
</style>
<script type="text/javascript">
//<!CDATA[
$(function(){
        $('input[ectype="change_store_logo"]').change(function(){
            $('input[ectype="change_store_logo"]').removeAttr('name');
            $(this).attr('name', 'store_logo');
        });
        $('input[ectype="change_store_banner"]').change(function(){
            $('input[ectype="change_store_banner"]').removeAttr('name');
            $(this).attr('name', 'store_banner');
        });

        $('#my_store_form').validate({
            errorPlacement: function(error, element){
                $(element).next('.field_notice').hide();
                if($(element).parent().parent().is('b'))
                {
                    $(element).parent().parent('b').next('.explain').hide();
                    $(element).parent().parent('b').after(error);
                }
                else
                {
                    $(element).after(error);
                }
            },
            success       : function(label){
                if($(label).attr('for') != 'store_logo' && $(label).attr('for') != 'store_banner'  ){
                    label.addClass('validate_right').text('OK!');
                    }
            },
            rules : {
                store_name : {
                    required   : true,
                    remote : {
                        url  : 'index.php?app=apply&act=check_name&ajax=1',
                        type : 'get',
                        data : {
                            store_name : function(){
                                return $('#store_name').val();
                            },
                            store_id : <?php echo $this->_var['store']['store_id']; ?>
                        }
                    },
                    maxlength: 20
                },
                tel      : {
                    required     : true
                }
            },
            messages : {
                store_name  : {
                    required   : '此项不允许为空',
                    remote: '店铺名称已经存在，请换一个',
                    maxlength: '请控制在20个字以内'
                },
                tel      : {
                    required   : '此项不允许为空'
                } 
            }
    });
   regionInit("region");
   
   // 百度地图API功能
	var longitude = '<?php echo $this->_var['store']['lng']; ?>';
	var latitude = '<?php echo $this->_var['store']['lat']; ?>';
					
	if(longitude > 0 && latitude > 0){
		baiduMapInit(longitude, latitude);
	}else{
		currentLocation();
	}
	
	$(document).on('change', '#region select',function(){
		paseAdress();
	})
	
	var t;			
	$('[name="address"]').bind('keyup change',function(){
		clearTimeout(t);
		t = setTimeout(function(){
				paseAdress()
			},1000);
	})
	
	function G(){//初始化地图
		return new BMap.Map("allmap");
	}
	
	function currentLocation(){
		var map = G();			   
		var geolocation = new BMap.Geolocation();
		geolocation.getCurrentPosition(function(r){
			if(this.getStatus() == BMAP_STATUS_SUCCESS){
				var point = new BMap.Point(r.point.lng,r.point.lat); 
								
				map.centerAndZoom(point,15);
				map.enableScrollWheelZoom(true);     //开启鼠标滚轮缩放
				map.addControl(new BMap.NavigationControl());// 左上角，添加比例尺
								
				baiduEvent(map);	   
			}     
		},{enableHighAccuracy: true})   
	}
		
	function baiduMapInit(longitude, latitude){
		// 百度地图API功能
		var map = G();
		var zoom = '<?php echo $this->_var['store']['zoom']; ?>' || 15;
	
		var point = new BMap.Point(longitude, latitude);
		map.centerAndZoom(point, zoom);
	
		map.addControl(new BMap.NavigationControl());
		map.enableScrollWheelZoom();                            //启用滚轮放大缩小
	
	
		var marker = new BMap.Marker(point);  // 创建标注
		map.addOverlay(marker);              // 将标注添加到地图中
		marker.setAnimation(BMAP_ANIMATION_BOUNCE); //跳动的动画	
					
		baiduEvent(map);
	}

	function paseAdress(){
		var region = $('[name="region_name"]').val();
		var address = $('[name="address"]').val();
		var url = REAL_SITE_URL+'/index.php?app=my_store&act=baiduParseAddress';

		$.getJSON(url, {'address' : region+address},function(data){
			if(data.done){
				var map = G();
							
				var point = new BMap.Point(data.retval.lng,data.retval.lat); 
								
				map.centerAndZoom(point,15);
				map.enableScrollWheelZoom(true);     //开启鼠标滚轮缩放
				map.addControl(new BMap.NavigationControl());// 左上角，添加比例尺
								
				var marker = new BMap.Marker(point);
				map.addOverlay(marker);             // 将标注添加到地图中
				marker.setAnimation(BMAP_ANIMATION_BOUNCE); //跳动的动画
							
				setPoint(data.retval.lng, data.retval.lat,map);
							
				baiduEvent(map);
			}
		})
	}
			   
	function baiduEvent(mapObj){
		mapObj.addEventListener("click", function(e){
			mapObj.clearOverlays();  //清除标注  或者可以把market 放入数组
			var point = new BMap.Point(e.point.lng , e.point.lat);
			var marker = new BMap.Marker(point);
			mapObj.addOverlay(marker);
			marker.setAnimation(BMAP_ANIMATION_BOUNCE); //跳动的动画
							
			setPoint(e.point.lng, e.point.lat,mapObj)
		});
	}
			   
	function setPoint(lng, lat, mapObj){
		$("input[name='lat']").val(lat); 
		$("input[name='lng']").val(lng);    
		$("input[name='zoom']").val(mapObj.getZoom());
	}
});
function add_uploadedfile(file_data)
{
        $('#desc_images').append('<li style="z-index:4" file_name="'+ file_data.file_name +'" file_path="'+ file_data.file_path +'" ectype="handle_pic" file_id="'+ file_data.file_id +'"><input type="hidden" name="desc_file_id[]" value="'+ file_data.file_id +'"><div class="pic" style="z-index: 2;"><img src="<?php echo $this->_var['site_url']; ?>/'+ file_data.file_path +'" width="80" height="80" alt="'+ file_data.file_name +'" /></div><div ectype="handler" class="bg" style="z-index: 3;display:none"><p class="operation"><a href="javascript:void(0);" class="cut_in" ectype="insert_editor" ecm_title="插入编辑器"></a><span class="delete" ectype="drop_image" ecm_title="删除"></span></p></div></li>');
        trigger_uploader();
        if(EDITOR_SWFU.getStats().progressNum == 0){
     		window.setTimeout(function(){
         		$('#editor_uploader').css('opacity', 0);
				$('*[ectype="handle_pic"]').css('z-index', 999);
        	},5000);
        }
}
function drop_image(file_id)
{
    if (confirm(lang.uploadedfile_drop_confirm))
        {
            var url = SITE_URL + '/index.php?app=my_store&act=drop_uploadedfile';
            $.getJSON(url, {'file_id':file_id}, function(data){
                if (data.done)
                {
                    $('*[file_id="' + file_id + '"]').remove();
                }
                else
                {
                    alert(data.msg);
                }
            });
        }
}

//]]>

</script> 
<?php echo $this->_var['editor_upload']; ?>
<?php echo $this->_var['build_editor']; ?>
<div id="main" class="clearfix"> <?php echo $this->fetch('member.menu.html'); ?>
  <div id="right"> <?php echo $this->fetch('member.curlocal.html'); ?>
    <div class="wrapful"> <?php echo $this->fetch('member.submenu.html'); ?>
      <div class="wrap">
        <div class="public">
          <div class="information">
            <form method="post" enctype="multipart/form-data" id="my_store_form">
              <div class="setup">
                <div class="photo relative1">
                  <p><img src="<?php if ($this->_var['store']['store_logo'] != ''): ?><?php echo $this->_var['store']['store_logo']; ?><?php else: ?>data/system/default_store_logo.gif<?php endif; ?>" width="120" height="120" ectype="store_logo" /></p>
                  <b>
                  <input ectype="change_store_logo" type="file" name="file" size="1" hidefocus="true" style="display:block;z-index:10; position:absolute;width: 120px; height: 28px; cursor: hand; cursor: pointer;  opacity:0; filter: alpha(opacity=0);">
                  <div class="txt" style="position:absolute;z-index:9">更换店标</div>
                  </b> <span class="explain">建议尺寸100*100像素</span> </div>
                <div class="photo relative2">
                  <p><img src="<?php if ($this->_var['store']['store_banner'] != ''): ?><?php echo $this->_var['store']['store_banner']; ?><?php else: ?><?php echo $this->res_base . "/" . 'images/member/banner.gif'; ?><?php endif; ?>" width="607" height="120" ectype="store_banner" /></p>
                  <b>
                  <input ectype="change_store_banner" type="file" name="file" size="1" hidefocus="true" style="display:block;z-index:10; position:absolute;width: 120px; height: 28px; cursor: hand; cursor: pointer;  opacity:0; filter: alpha(opacity=0);">
                  <span class="txt">更换店铺横幅</span> </b>
                  <label style="position:absolute;left:160px;top:135px;font-size:14px">导航背景色
                    <input class="pickcolor" type="text" placeholder="#3E4789" name="nav_color" style="width:70px;" value="<?php echo $this->_var['store']['nav_color']; ?>" />
                  </label>
                  <span class="explain">此处为您的店铺条幅，将显示在店铺导航上方的banner位置，建议尺寸 >=1920*120 像素</span> </div>
                <div class="clear"></div>
              </div>
              <div class="setup info shop">
                <table style="width: 100%">
                  <?php if ($this->_var['subdomain_enable']): ?>
                  <tr>
                    <th>二级域名:</th>
                    <td><input type="text" name="domain" value="<?php echo htmlspecialchars($this->_var['store']['domain']); ?>"<?php if ($this->_var['store']['domain']): ?> disabled<?php endif; ?> class="text width11" />
                      &nbsp;<?php if (! $this->_var['store']['domain']): ?>可留空,注意！设置后将不能修改，域名长度应为:<?php echo $this->_var['domain_length']; ?><?php else: ?><?php endif; ?></td>
                  </tr>
                  <?php endif; ?>
                  <tr>
                    <th class="width2">店铺名称:</th>
                    <td><p class="td_block">
                        <input id="store_name" type="text" class="text width_normal" name="store_name" value="<?php echo htmlspecialchars($this->_var['store']['store_name']); ?>"/>
                        <label class="field_notice">店铺名称</label>
                      </p>
                      <b class="padding1">*</b><a href="<?php echo url('app=store&id=' . $this->_var['store']['store_id']. ''); ?>" target="_blank" class="btn1">我的店铺首页</a></td>
                  </tr>
                  <tr>
                    <th>所在地区:</th>
                    <td><div id="region">
                        <input type="hidden" name="region_id" value="<?php echo $this->_var['store']['region_id']; ?>" class="mls_id" />
                        <input type="hidden" name="region_name" value="<?php echo htmlspecialchars($this->_var['store']['region_name']); ?>" class="mls_names" />
                        <?php if ($this->_var['store']['store_id']): ?> 
                        <span><?php echo htmlspecialchars($this->_var['store']['region_name']); ?></span>
                        <input type="button" value="编辑" class="edit_region" />
                        <select style="display:none">
                          <option>请选择...</option>
                          
                                      <?php echo $this->html_options(array('options'=>$this->_var['regions'])); ?>
                                    
                        </select>
                        <?php else: ?>
                        <select class="select">
                          <option>请选择...</option>
                          
                                      <?php echo $this->html_options(array('options'=>$this->_var['regions'])); ?>
                                    
                        </select>
                        <?php endif; ?></div></td>
                  </tr>
                  <tr>
                    <th>详细地址:</th>
                    <td><p class="td_block">
                        <input type="text" name="address" class="text width_normal" id="address" value="<?php echo htmlspecialchars($this->_var['store']['address']); ?>" />
                        <span class="field_notice">不必重复填写所在地区</span></p></td>
                  </tr>
                  <tr>
                    <th>地图坐标:</th>
                    <td class="gray">地图上点击即可标注店铺坐标</td>
                  </tr>
                  <tr>
                  	<th>&nbsp;</th>
                    <td class="map">
                        <input type="hidden" name="lat" value="<?php echo $this->_var['store']['lat']; ?>" />
                		<input type="hidden" name="lng" value="<?php echo $this->_var['store']['lng']; ?>" />
                        <input type="hidden" name="zoom" value="<?php echo $this->_var['store']['zoom']; ?>" />
                        <div id="allmap" style="height:280px;border:3px #ddd solid;margin:0px 0 8px;"></div>
                    </td>
                  </tr>
                  <tr>
                    <th>联系电话:</th>
                    <td><input name="tel" type="text" class="text width_normal" id="tel" value="<?php echo htmlspecialchars($this->_var['store']['tel']); ?>" /></td>
                  </tr>
                  <tr>
                    <th>联系QQ:</th>
                    <td><input name="im_qq" type="text" class="text width_normal" id="im_qq" value="<?php echo htmlspecialchars($this->_var['store']['im_qq']); ?>" /></td>
                  </tr>
                  <tr>
                    <th>阿里旺旺:</th>
                    <td><input name="im_ww" type="text" class="text width_normal" id="im_ww" value="<?php echo htmlspecialchars($this->_var['store']['im_ww']); ?>" /></td>
                  </tr>
                  <tr>
                    <th>主营业务：</th>
                    <td><input type="text" name="business_scope" class="text width_normal"  value="<?php echo $this->_var['store']['business_scope']; ?>" style="width:350px;" />
                      <span class="padding1">如：女装上衣 t恤 衬衫 长袖 纯色 打底裤</span></td>
                  </tr>
                  <tr>
                    <th class="align3">店铺简介:</th>
                    <td><div class="editor">
                        <div>
                          <textarea name="description" id="description" style="width:100%; height:350px;"><?php echo htmlspecialchars($this->_var['store']['description']); ?></textarea>
                        </div>
                        <div style=" position: relative; top: 10px; "><a class="btn3" id="open_editor_uploader">上传图片</a>
                          <div class="upload_con" id="editor_uploader" style="opacity:0; filter:Alpha(opacity=0)">
                            <div class="upload_con_top"></div>
                            <div class="upload_wrap">
                              <ul>
                                <li class="EDITOR_SWFU_filePicker">
                                  <div id="divSwfuploadContainer">
                                    <div id="divButtonContainer"> <span id="editor_upload_button"></span> </div>
                                  </div>
                                </li>
                                <li>
                                  <iframe src="index.php?app=comupload&act=view_iframe&id=<?php echo $this->_var['id']; ?>&belong=<?php echo $this->_var['belong']; ?>&instance=desc_image" width="86" height="30" scrolling="no" frameborder="0"></iframe>
                                </li>
                                <li id="open_editor_remote" class="btn4">远程地址</li>
                              </ul>
                              <div id="editor_remote" class="upload_file" style="display:none">
                                <iframe src="index.php?app=comupload&act=view_remote&id=<?php echo $this->_var['id']; ?>&belong=<?php echo $this->_var['belong']; ?>&instance=desc_image" width="272" height="39" scrolling="no" frameborder="0"></iframe>
                              </div>
                              <div id="editor_upload_progress"></div>
                              <div class="upload_txt"> <span>支持JPEG和静态的GIF格式图片，不支持GIF动画图片，上传图片大小不能超过2M.浏览文件时可以按住ctrl或shift键多选</span> </div>
                            </div>
                            <div class="upload_con_bottom"></div>
                          </div>
                        </div>
                        <ul id="desc_images" class="preview J_descriptioneditor clearfix">
                          <?php $_from = $this->_var['files_belong_store']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'file');if (count($_from)):
    foreach ($_from AS $this->_var['file']):
?>
                          <li ectype="handle_pic" file_name="<?php echo htmlspecialchars($this->_var['file']['file_name']); ?>" file_path="<?php echo $this->_var['file']['file_path']; ?>" file_id="<?php echo $this->_var['file']['file_id']; ?>">
                            <input type="hidden" name="file_id[]" value="<?php echo $this->_var['file']['file_id']; ?>">
                            <div class="pic"> <img src="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['file']['file_path']; ?>" width="80" height="80" alt="<?php echo htmlspecialchars($this->_var['file']['file_name']); ?>" title="<?php echo htmlspecialchars($this->_var['file']['file_name']); ?>" /> </div>
                            <div ectype="handler" class="bg">
                              <p class="operation"> <a href="javascript:void(0);" class="cut_in" ectype="insert_editor" ecm_title="插入编辑器"></a> <span class="delete" ectype="drop_image" ecm_title="删除"></span> </p>
                            </div>
                          </li>
                          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                        </ul>
                        <div class="clear"></div>
                      </div>
                      <div class="issuance">
                        <input type="submit" class="btn" value="提交" />
                      </div></td>
                  </tr>
                </table>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php echo $this->fetch('member.footer.html'); ?>