<?php echo $this->fetch('header.html'); ?> 
<script language="javascript">
$(document).ready(function(){
	$('.J-orderAmountStatic').load('index.php?app=stat&act=sale_trend&type=orderamount');
	$('.J-orderCountStatic').load('index.php?app=stat&act=sale_trend&type=ordernum');
	$('.J-userStatic').load('index.php?app=user&act=increase_trend');
	$('.J-storeStatic').load('index.php?app=store&act=increase_trend');
	$(".J-slide").slide({titCell:".hd a", mainCell:".bd", effect:"leftLoop",autoPlay:false, titOnClassName:"active", delayTime:500, interTime: 5000});
	$('.J_viewIP').click(function(){
		var index = parent.layer.load(0, {shade: false,time: 10*1000});
		var id = $(this).attr('data-id');
		var td = $(this).parent('td');
		var url = REAL_BACKEND_URL + '/index.php?app=default&act=getIpInfo';
		$.getJSON(url,{'id':id},function(data){
			if(data.done){
				td.html(data.retval);
				parent.layer.close(index);
			}else{
				parent.layer.msg(data.msg);
				parent.layer.close(index);
			}
		});
	});
	
});
</script>
<style>
#rightCon{background:#f2f2f2;font-family:'microsoft yahei' ;padding:5px;margin:0;}
</style>
<div id="rightCon" class="clearfix welcome"> 
	<div class="lp">
    	<div class="box-s-1">
            <div class="box weekly-info J-slide">
                <div class="mt">
                    <h3>一周概览</h3>
                    <div class="nav hd"><a href="javascript:;" class="active"></a><a href="javascript:;"></a></div>
                </div>
                <div class="mc">
                	<div class="container bd">
                        <ul>
                            <li class="user"> <a class="item" href="index.php?app=user">
                                <div class="detail">  <span>新增会员</span> <p><cite><?php echo $this->_var['news_in_a_week']['new_user_qty']; ?></cite></p></div>
                                </a> </li>
                            <li class="goods"> <a class="item" href="index.php?app=goods">
                                <div class="detail">  <span>新增商品</span> <cite><?php echo $this->_var['news_in_a_week']['new_goods_qty']; ?></cite></div>
                                </a> </li>
                            <li class="store"> <a class="item" href="index.php?app=store">
                                <div class="detail">  <span>新增店铺</span><cite><?php echo $this->_var['news_in_a_week']['new_store_qty']; ?></cite> </div>
                                </a> </li>
                            <li class="order"> <a class="item" href="index.php?app=order">
                                <div class="detail">  <span>新增订单</span> 
                                <cite><?php echo $this->_var['news_in_a_week']['new_order_qty']; ?></cite></div></a> </li>
                        </ul>
                        <ul>
                            <li class="pinglun"> <a class="item" href="index.php?app=evaluation">
                                <div class="detail">  <span>新增评价</span> <cite style="color:#FF5722;"><?php echo $this->_var['news_in_a_week']['new_pinglun']; ?></cite></div>
                                </a> </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="box-s-1">
            <div class="box weekly-info J-slide">
                <div class="mt">
                    <h3>统计信息</h3>
                    <div class="nav hd"><a href="javascript:;" class="active"></a><a href="javascript:;"></a></div>
                </div>
                <div class="mc">
                	<div class="container bd">
                        <ul>
                            <li class="user"> <em class="item" >
                                <div class="detail">  <span>会员总数</span> <p><cite style="color:#FF5722;"><?php echo $this->_var['stats']['user_qty']; ?></cite></p></div>
                                </em> </li>
                            <li class="goods"> <em class="item" >
                                <div class="detail">  <span>商品总数</span> <cite style="color:#FF5722;"><?php echo $this->_var['stats']['goods_qty']; ?></cite></div>
                                </a> </li>
                            <li class="pinglun"> <em class="item" >
                                <div class="detail">  <span>订单总数</span> <cite><?php echo $this->_var['stats']['order_qty']; ?></cite></div>
                                </em> </li>
                                <li class="pinglun"> <em class="item" >
                                <div class="detail">  <span>订单总金额</span> <cite><?php echo price_format($this->_var['stats']['order_amount']); ?></cite></div>
                                </em> </li>
                        </ul>
                        <ul>
							<li class="store"> <em class="item" >
                                <div class="detail">  <span>待审核店铺</span><cite style="color:#FF5722;"><?php echo $this->_var['stats']['apply_qty']; ?></cite> </div>
                                </em> </li>
                            <li class="order"> <em class="item" >
                                <div class="detail">  <span>店铺总数</span> 
                                <cite style="color:#FF5722;"><?php echo $this->_var['stats']['store_qty']; ?></cite></div></em> </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="box-s-2">
            <div class="box weekly-info J-slide">
                <div class="mt">
                    <h3>数据概览</h3>
                    <div class="nav hd"><a href="javascript:;" class="active"></a><a href="javascript:;"></a><a href="javascript:;"></a><a href="javascript:;"></a></div>
                </div>
                <div class="mc">
                	<div class="container bd">
                        <ul>
                            <p style="text-align:center;font-size:14px;margin-bottom:10px;">订单额概览</p>
                            <div class="J-orderAmountStatic"></div>
                        </ul>
                        <ul>
                            <p style="text-align:center;font-size:14px;margin-bottom:10px;">订单量概览</p>
                            <div class="J-orderCountStatic"></div>
                        </ul>
                        <ul>
                            <p style="text-align:center;font-size:14px;margin-bottom:10px;">会员概览</p>
                            <div class="J-userStatic"></div>
                        </ul>
                        <ul>
                            <p style="text-align:center;font-size:14px;margin-bottom:10px;">店铺概览</p>
                            <div class="J-storeStatic"></div>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="rp">
    	<?php if ($this->_var['loginLogs']): ?>
    	<div class="box-s-3">
            <div class="box weekly-info">
                <div class="mt">
                    <h3>登陆记录</h3>
                </div>
                <div class="mc"  style="padding: 10px 15px 7px 15px;">
                	<table>
                    	<tr class="th">
                        	<td>ip</td>	
                            <td>登陆地区</td>	
                            <td>时间</td>	
                        </tr>
                        <?php $_from = $this->_var['loginLogs']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'log');if (count($_from)):
    foreach ($_from AS $this->_var['log']):
?>
                        <tr>
                        	<td><?php echo $this->_var['log']['ip']; ?></td>	
                            <td><?php if ($this->_var['log']['region_name']): ?><?php echo $this->_var['log']['region_name']; ?><?php else: ?><a href="javascript:;" class="J_viewIP" data-id="<?php echo $this->_var['log']['log_id']; ?>">点击查看</a><?php endif; ?></td>
                            <td><?php echo local_date("Y-m-d H:i:s",$this->_var['log']['add_time']); ?></td>	
                        </tr>
                        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?> 
    	<?php if ($this->_var['dangerous_apps']): ?>
    	<div class="box-s-2">
            <div class="box weekly-info">
                <div class="mt">
                    <h3>警告</h3>
                </div>
                <div class="mc"  style="padding: 10px 15px 7px 15px;">
                	<ul>
                        <?php $_from = $this->_var['dangerous_apps']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'da');if (count($_from)):
    foreach ($_from AS $this->_var['da']):
?>
                        <li style="float:none;width:100%;float:none;width:100%;line-height:20px;padding-bottom:5px;"><?php echo $this->_var['da']; ?></li>
                        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php endif; ?> 
        <?php if ($this->_var['remind_info']): ?>
        <div class="box-s-2">
            <div class="box weekly-info">
                <div class="mt">
                    <h3>站长提醒</h3>
                </div>
                <div class="mc" style="padding: 10px 15px 7px 15px;">
                	<ul>
                        <?php $_from = $this->_var['remind_info']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'remind');if (count($_from)):
    foreach ($_from AS $this->_var['remind']):
?>
						<li style="float:none;width:100%;float:none;width:100%;line-height:20px;padding-bottom:5px;"><?php echo $this->_var['remind']; ?></li>
						<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php endif; ?> 
    	<div class="box-s-2">
            <div class="box weekly-info">
                <div class="mt">
                    <h3>系统信息</h3>
                </div>
                <div class="mc">
                	<table>
                    	<tr>
                            <th>MiMall版本</th>
                            <td><?php echo $this->_var['sys_info']['version']; ?></td>
                        </tr>
                        <tr>
                            <th>安装日期</th>
                            <td><?php echo $this->_var['sys_info']['install_date']; ?></td>
                        </tr>
                        <tr>
                            <th>服务器操作系统</th>
                            <td><?php echo $this->_var['sys_info']['server_os']; ?></td>
                        </tr>
                        <tr>
                            <th>WEB 服务器</th>
                            <td><?php echo $this->_var['sys_info']['web_server']; ?></td>
                        </tr>
                        <tr>
                            <th>PHP 版本</th>
                            <td><?php echo $this->_var['sys_info']['php_version']; ?></td>
                        </tr>
                        <tr>
                            <th>MYSQL 版本</th>
                            <td><?php echo $this->_var['sys_info']['mysql_version']; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>