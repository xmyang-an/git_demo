<?php echo $this->fetch('header.html'); ?>

<link href="themes/mall/jd/styles/default/css/xunjia.css" rel="stylesheet" type="text/css">

<div class="gongyingshangshenqing_content w1000">
	<div class="gongyingshangshenqing_content_a">
		<form action="index.php?app=xunjia&act=add" method="post" enctype="multipart/form-data" onsubmit="return check(this)" >
			<div class="xunjia_b">
				<div class="xunjia_b_a xunjia_b_title">
					询价单
				</div>
				<div class="xunjia_warm_tips">
					<span>*</span>为必填项。7x24小时快速询价,请填写您所需要询价的产品信息以及您的联系人信息,然后点击提交询价单按钮,我们的销售代表会第一时间做好报价联系您！</div>
				<table cellspacing="0" width="100%" class="xunjia_b_b">
					<tbody>
						<tr>
							<th width="20%"><span>*</span>产品品牌</th>
							<th width="20%"><span>*</span>产品名称</th>
							<th width="15%"><span>*</span>规格型号</th>
							<th width="15%"><span>*</span>数量</th>
							<th width="14%">图片(非必选)</th>
							<th width="10%">操作</th>
						</tr>
						<tr>
							
							<td class="input_goods_brand">
								<input type="text" placeholder="例:siemens或西门子" value="" name="pp[]" class="chose_brand"
									list="cars1" autocomplete="off">
							</td>
							<td class="input_goods_proname">
								<input type="text" placeholder="例:压力变送器" value="" name="pro_name[]" 
									autocomplete="off">
								
							</td>
							<td class="input_goods_model"><input type="text" placeholder="例:7MF4433-1BA02-2DC6-Z"
									value="" name="cpxh[]"></td>
							<td class="input_goods_number"><input type="text" placeholder="例：1" value="" name="sl[]"></td>
							<td class="input_goods_price">
								<!--<div class="choose_btn">点击选择图片</div>-->
							      <a style="margin-left:80px">
									 <input type="file" name="pro_pic[]" >
								  </a>
							</td>
							<td class="input_del"></td>
						</tr>
						<tr>
							<td class="input_goods_brand"><input type="text" value="" name="pp[]" class="chose_brand"
									 autocomplete="off">
							</td>
							<td class="input_goods_proname"><input type="text" value="" name="pro_name[]"
									 autocomplete="off">
							</td>
							<td class="input_goods_model"><input type="text" value="" name="cpxh[]"></td>
							<td class="input_goods_number"><input type="text" value="" name="sl[]"></td>
							<td class="input_goods_price">
								<!--<div class="choose_btn">点击选择图片</div>-->
								<a style="margin-left:80px">
								   <input type="file" name="pro_pic[]" >
								</a></td>
							<td class="input_del"><a onclick="del_pro(this)">删除</a></td>
						</tr>
						<tr>
							<td class="input_goods_brand"><input type="text" value="" name="pp[]" class="chose_brand"
									 autocomplete="off"></td>
							<td class="input_goods_proname"><input type="text" value="" name="pro_name[]"
									list="proname3" autocomplete="off">
							</td>
							<td class="input_goods_model"><input type="text" value="" name="cpxh[]"></td>
							<td class="input_goods_number"><input type="text" value="" name="sl[]"></td>
							<td class="input_goods_price"><!--<div class="choose_btn">点击选择图片</div>-->
								<a style="margin-left:80px">
								   <input type="file" name="pro_pic[]" >
								</a></td>
							<td class="input_del"><a onclick="del_pro(this)">删除</a></td>
						</tr>
						<tr>
							<td class="input_goods_brand"><input type="text" value="" name="pp[]" class="chose_brand"
									list="cars4" autocomplete="off"></td>
							<td class="input_goods_proname"><input type="text" value="" name="pro_name[]"
									 autocomplete="off">
							</td>
							<td class="input_goods_model"><input type="text" value="" name="cpxh[]"></td>
							<td class="input_goods_number"><input type="text" value="" name="sl[]"></td>
							<td class="input_goods_price"><!--<div class="choose_btn">点击选择图片</div>-->
								<a style="margin-left:80px">
								   <input type="file" name="pro_pic[]" >
								</a></td>
							<td class="input_del"><a onclick="del_pro(this)">删除</a></td>
						</tr>
						<tr>
							<td class="input_goods_brand"><input type="text" value="" name="pp[]" class="chose_brand"
									 autocomplete="off"></td>
							<td class="input_goods_proname"><input type="text" value="" name="pro_name[]"
									 autocomplete="off">
							</td>
							<td class="input_goods_model"><input type="text" value="" name="cpxh[]"></td>
							<td class="input_goods_number"><input type="text" value="" name="sl[]"></td>
							<td class="input_goods_price">
								<!--<div class="choose_btn">点击选择图片</div>-->
								<a style="margin-left:80px">
								   <input type="file" name="pro_pic[]" >
								</a></td>
							<td class="input_del"><a onclick="del_pro(this)">删除</a></td>
						</tr>
					</tbody>
					<tbody class="xunjia_b_b_add">
					</tbody>
					<tbody>
						<tr>
							<td colspan="8" class="add_pro">
								<span class="pro_pic_tishi">图片格式只能是jpg,png,gif格式的,且每张图片小于1M</span>
								<a style="cursor:pointer">新增询价产品</a>
							</td>
						</tr>
					</tbody>
				</table>
				<script>
					var cars = 5;
					var cat_arr = "<option value='DIN导轨式电源'><option value='UPS不间断电源'><option value='万向节'><option value='丝锥'><option value='中压变频器'><option value='中继器'><option value='互感器'><option value='五金工具'><option value='交流电机'><option value='交流电源'><option value='仓储搬运'><option value='仪器仪表'><option value='传动带'><option value='传动带/链条'><option value='传感器'><option value='伺服放大器'><option value='伺服电机'><option value='伺服系统'><option value='伺服阀'><option value='伺服驱动器'><option value='位移传感器'><option value='低压变频器'><option value='供水专用变频器'><option value='信号放大器'><option value='倾斜传感器'><option value='光电传感器'><option value='光纤传感器'><option value='冷却器'><option value='冷却泵'><option value='冷却管'><option value='冷热交换器'><option value='减压阀'><option value='减速机（功率≤750W直流电动机、发电）'><option value='减速机（功率≤750W）'><option value='减速电机'><option value='减震元件'><option value='刀座'><option value='分度头'><option value='分析仪表'><option value='切割机'><option value='制动器'><option value='功率表'><option value='动力传动'><option value='卡尺'><option value='卡盘'><option value='压力传感器'><option value='压力开关'><option value='压力表'><option value='压力风机'><option value='压缩机'><option value='叉车'><option value='变压器'><option value='变压器（大于500KVA）'><option value='变送器'><option value='变频器'><option value='变频器模块'><option value='变频器电源模块'><option value='变频器（发动机）'><option value='可编程逻辑控制器/PLC'><option value='叶片泵'><option value='同轴阀'><option value='听力防护'><option value='吸尘器'><option value='呼吸防护'><option value='喷嘴'><option value='喷枪'><option value='回转风机'><option value='园林工具'><option value='塑料拖链'><option value='塑料接头'><option value='塑料风机'><option value='增量式编码器'><option value='多相交流电动机'><option value='头部防护'><option value='夹具'><option value='夹紧装置'><option value='套筒组套'><option value='安全传感器'><option value='安全制动器'><option value='安全帽'><option value='安全服'><option value='安全离合器'><option value='安全阀'><option value='安全鞋'><option value='安防劳保'><option value='定量阀'><option value='密封件'><option value='密封件（塑料密封）'><option value='密封件（钢铁密封）'><option value='对讲机'><option value='工业打印机'><option value='工业插头'><option value='工业插座'><option value='工业显示器'><option value='工业灯管'><option value='工业照明'><option value='工业电脑'><option value='工业电脑配件'><option value='工业电话机'><option value='工业空调'><option value='工业空调'><option value='工业自动化'><option value='工业计算机'><option value='工业连接器'><option value='工业风扇'><option value='工控仪表'><option value='工程型变频器'><option value='平板冷却器'><option value='开关'><option value='开关柜'><option value='开关电源'><option value='开关附件'><option value='开关（电压大于1KVA）'><option value='开槽机'><option value='异步电机'><option value='微型电机'><option value='总线端子模块'><option value='感应开关'><option value='截止阀'><option value='手动工具'><option value='手动扳手'><option value='手动螺丝刀'><option value='手动钉锤'><option value='手动钳子'><option value='手动锉刀'><option value='手动阀'><option value='手部防护'><option value='打磨机'><option value='执行器'><option value='扭矩传感器'><option value='扭矩限制器'><option value='抛光机'><option value='拉线开关'><option value='拉马组合'><option value='拖链'><option value='指示开关'><option value='按钮开关'><option value='振动传感器'><option value='捏合离合器'><option value='换向阀'><option value='换热器'><option value='排屑机'><option value='排污泵'><option value='排线器'><option value='接口模块'><option value='接地电阻测试仪'><option value='接头'><option value='接头,配管和附件'><option value='接头，配管和附件'><option value='接线端子'><option value='接触器'><option value='接触开关'><option value='接近传感器'><option value='接近开关'><option value='控制器'><option value='控制器'><option value='控制模块'><option value='控制阀'><option value='插头防护罩'><option value='插座插头'><option value='搅拌器'><option value='搅拌器'><option value='摆线针轮减速机'><option value='摩擦离合器'><option value='操纵杆开关'><option value='散热器'><option value='数字阀门定位器'><option value='数显表'><option value='整流器'><option value='料位开关'><option value='断路器'><option value='旋塞阀'><option value='旋转传感器'><option value='旋转开关'><option value='旋转接头'><option value='旋转编码器'><option value='无绳手持式电动工具'><option value='智能阀门定位器'><option value='机床垫铁'><option value='机床工具'><option value='机床护罩'><option value='机床灯具'><option value='机械式制动器'><option value='机油冷却器'><option value='杂质泵'><option value='板式冷却器'><option value='板式换热器'><option value='柱塞泵'><option value='柱塞阀'><option value='柴油电机'><option value='校准仪器'><option value='检测仪表'><option value='模块'><option value='模拟阀门定位器'><option value='横流风机'><option value='止回阀'><option value='步进电机'><option value='气体传感器'><option value='气体分析仪'><option value='气动元件'><option value='气动冲击扳手'><option value='气动工具'><option value='气动打磨机'><option value='气动执行器'><option value='气动抛光机'><option value='气动直磨机'><option value='气动砂轮机'><option value='气动螺丝刀'><option value='气动锯具'><option value='气动阀门定位器'><option value='气压式制动器'><option value='气控阀'><option value='气缸'><option value='气钻'><option value='水冷却器'><option value='污水泵'><option value='沉水泵'><option value='油冷却器'><option value='泄压阀'><option value='法兰轴承单元'><option value='波纹管联轴器'><option value='注油器'><option value='泵'><option value='流体控制'><option value='流量仪表'><option value='流量传感器'><option value='流量开关'><option value='流量阀'><option value='测速电机'><option value='测量仪表'><option value='测量测试仪器'><option value='测量线'><option value='浮子流量计'><option value='消防泵'><option value='涡街流量计'><option value='涡轮流量计'><option value='润滑剂'><option value='润滑系统'><option value='润滑系统'><option value='液位传感器'><option value='液位变送器'><option value='液位开关'><option value='液位指示器'><option value='液位计'><option value='液压万用表'><option value='液压元件'><option value='液压千斤顶'><option value='液压工具'><option value='液压式制动器'><option value='液压扳手'><option value='液压拉马'><option value='液压接头'><option value='液压接头、软管及附件'><option value='液压机'><option value='液压油缸'><option value='液压法兰分离器'><option value='液压泵'><option value='液压泵'><option value='液压测试仪'><option value='液压站'><option value='液压胶管'><option value='液压螺栓拉伸器'><option value='液压螺母'><option value='液压螺母破切器'><option value='液压车'><option value='液压配件'><option value='液压钳'><option value='液压锯'><option value='液压马达'><option value='混流风机'><option value='温度传感器'><option value='温度测量仪'><option value='温控器'><option value='湿度计'><option value='滑动轴承'><option value='滑环'><option value='滑轨|滑环'><option value='滑轨滑台'><option value='滚珠轴承'><option value='滚轮式滑轨'><option value='滤波器'><option value='滤芯'><option value='激光水平仪'><option value='激光测距仪'><option value='激光测量仪'><option value='熔断器'><option value='爆破盘'><option value='玻璃钢风机'><option value='球阀'><option value='电力仪表'><option value='电动工具'><option value='电动工具配件'><option value='电动执行器'><option value='电动绞盘'><option value='电动阀门定位器'><option value='电动雕刻机'><option value='电压表'><option value='电容器'><option value='电容式传感器'><option value='电工电气'><option value='电感式传感器'><option value='电抗器'><option value='电机/减速电机'><option value='电机配件'><option value='电机（功率≤750W）'><option value='电机（输出功率750W~75KW、直流）'><option value='电机（输出功率≤750瓦、直流）'><option value='电池充电器'><option value='电流传感器'><option value='电流放大器'><option value='电流表'><option value='电源'><option value='电源模块'><option value='电源配件'><option value='电热风枪'><option value='电焊机'><option value='电磁式制动器'><option value='电磁流量计'><option value='电磁线圈'><option value='电磁阀'><option value='电筒'><option value='电线电缆'><option value='电缆'><option value='电能质量测试仪'><option value='电葫芦'><option value='电钻'><option value='电锤'><option value='电锯'><option value='电镐'><option value='电阻器'><option value='直流电机'><option value='直流电源'><option value='直磨机'><option value='直线电机'><option value='真空传感器'><option value='真空发生器'><option value='真空吸盘'><option value='真空泵'><option value='眼部防护'><option value='砂轮机'><option value='碰撞传感器'><option value='磁力泵'><option value='磁力钻'><option value='磁性传感器'><option value='磁性开关'><option value='磁感应传感器'><option value='磁感应式流量计'><option value='磁栅编码器'><option value='磁翻板液位计'><option value='示波器'><option value='离合器'><option value='离心泵'><option value='离心风机'><option value='称重传感器'><option value='稳压电源'><option value='空压机'><option value='空气冷却器'><option value='系统组件'><option value='紧缩轴承'><option value='絶對值编码器'><option value='红外测量仪'><option value='纺机专用变频器'><option value='线性编码器'><option value='线管'><option value='线管（塑料）'><option value='线管（绝缘PVC）'><option value='线管（聚氯乙烯）'><option value='线管（铁）'><option value='线管（铝制）'><option value='绝缘手套'><option value='绝缘故障定位仪'><option value='绝缘检测仪'><option value='继电器'><option value='编码器'><option value='网络设备'><option value='罗茨风机'><option value='耦合器'><option value='耦合器'><option value='耳塞'><option value='耳罩'><option value='联轴器'><option value='胀套'><option value='脚踏开关'><option value='膜片联轴器'><option value='自吸泵'><option value='节流阀'><option value='蓄电池'><option value='蓄能器'><option value='蓄能式换热器'><option value='蜗杆减速机'><option value='蝶阀'><option value='螺丝刀'><option value='螺杆泵'><option value='螺杆风机'><option value='行星减速机'><option value='角度编码器'><option value='角磨机'><option value='计数器'><option value='计量泵'><option value='试压泵'><option value='调压器'><option value='调节器'><option value='调节阀/调节器'><option value='谐波减速机'><option value='负荷开关'><option value='质量流量计'><option value='起动器'><option value='超声波传感器'><option value='超声波流量计'><option value='超越离合器'><option value='足部防护'><option value='身体防护'><option value='车载台'><option value='转换器'><option value='转速/线速/脉冲表'><option value='转速传感器'><option value='软管'><option value='软管及附件'><option value='软管（塑料）'><option value='软管（钢铁）'><option value='软管（铝）'><option value='轴承'><option value='轴承保持架'><option value='轴承座滚子'><option value='轴承附属件'><option value='轴流风机'><option value='输入输出模块'><option value='过滤器'><option value='过滤器'><option value='过滤排风扇'><option value='运动控制器'><option value='连接器'><option value='退卸衬套'><option value='选择开关'><option value='通信设备'><option value='通用变频器'><option value='通讯模块'><option value='通讯电源'><option value='通风换热'><option value='速度传感器'><option value='采集模块'><option value='钢制拖链'><option value='钢珠式滑轨'><option value='钻头'><option value='铣刀'><option value='铰刀'><option value='铰链开关'><option value='链式联轴器'><option value='链条'><option value='锁紧装置'><option value='锂电池'><option value='锂电池工具'><option value='锯条'><option value='间壁式换热器'><option value='阀体及连接器附件'><option value='阀门'><option value='阀门定位器'><option value='防护口罩'><option value='防护眼镜'><option value='限位开关'><option value='限制器'><option value='隔离开关'><option value='隔膜阀'><option value='面板表'><option value='频率表'><option value='风扇'><option value='风扇过滤网'><option value='风机'><option value='风速仪'><option value='高低压电器'><option value='高压变频器'><option value='高压泵'><option value='高压清洗机'><option value='黄油泵'><option value='鼓风机'><option value='齿式联轴器'><option value='齿轮减速机'><option value='齿轮式滑轨'><option value='齿轮泵'><option value='齿轮流量计'>";
					$(".add_pro a").click(function () {
						cars = ++cars;
						var str = '<tr>'
							+ '<td class="input_goods_brand"><input type = "text" value = "" name = "pp[]"  class="chose_brand" list="cars' + cars + '" autocomplete="off"/></td>' 
							+ '<td class="input_goods_proname"><input type = "text" value = "" name = "pro_name[]"  list="proname' + cars + '"  autocomplete="off"/></td>'
							+ '<td class="input_goods_model"><input type = "text" value = "" name = "cpxh[]" /></td>'
							+ '<td class="input_goods_number"><input type = "text" value = "" name = "sl[]" /></td>'
							+ '<td class="input_goods_price"><input type="file" name="pro_pic[]" style="margin-left:80px"/></td>'
							+ '<td class="input_del"><a onclick = "del_pro(this)">删除<a/></td>'
							+ '</tr>';
						$(".xunjia_b_b_add").append(str);
					});
					function del_pro(obj) {
						$(obj).parent().parent().remove();
					}
					
				</script>
			</div>
			<div class="xunjia_c">
				<div>备注信息：</div>
				<textarea name="xunjia_content" id="yatdim" placeholder="请填写此次需要补充的询价信息"></textarea><br><span
					class="textarea_span">您现在可以输入<span id="word">250</span>个字符</span>
			</div>
			<div class="xunjia_d">
				<div class="xunjia_b_a">
					用户信息
				</div>
				<table width="100%">
					<tbody class="xunjia_b_tbody">
						<tr>
							<td class="xunjia_b_tbody_title">
								<div class="b_name"><span>*</span>单位名称：</div><input type="" name="company_name"
									value="">
							</td>
							<td class="xunjia_b_tbody_title">
								<div class="b_name"><span>*联系人：</span></div><input type="" name="linkman"
									value="">
							</td>
							<td class="xunjia_b_tbody_title">
								<div class="b_name"><span>*</span>手机：</div><input type="" id="mobile_phone"
									name="mobile_phone" value="">
							</td>
							
							<td class="xunjia_b_tbody_title">
								<div class="b_name"><span>*</span>邮箱：</div><input type="" name="contact_mail" id="email"
									value="">
							</td>
						</tr>
					</tbody>
					<tbody>
						<tr>
							<td colspan="5" align="center">
								<input type="submit" name="xunjia" id="xunjia_submit" class="xunjia_submit"
									value="提交询价单">
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</form>

		<div class="xunjia_right" style="position: relative;">
			<div class="xunjia_b_a">
				其它询价方式
			</div>
			<div class="xunjia_b_infomation">
				您好，感谢您对壹点网的支持，您也可以通过以下方式进行快速询价；<br>
				1、您可以下载我们的<a href="#" target="_blank">询价单</a>填写好,发送至业务邮箱：<span>sales@yatdim.com</span>,我们会尽快与您联系并报价。<br>
				2、可以直接电话与我司销售人员联系；<br>
				业务电话一：<span>400-659-9859</span>&nbsp;&nbsp;

				温馨提示：如果您<a
					href="https://shop.yatdim.com/index.php?app=member&act=login&ret_url=">登录</a>后再以上询价单中填写询价信息提交,待审核通过后您可以在您的会员中心看到您发布的询价单。
				<div class="">马上<a
						href="https://shop.yatdim.com/index.php?app=member&act=register&ret_url=">注册</a>成为商城用户,完善个人资料就可以免填联系人信息方便您更快捷的询价！
				</div>
			</div>
		</div>
    
	</div>
	
</div>

<?php echo $this->fetch('footer.html'); ?>