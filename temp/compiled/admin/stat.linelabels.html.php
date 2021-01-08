<div id="container_<?php echo $this->_var['stattype']; ?>" style="width:100%;height:250px;"></div>
<script>	
	var barChart = echarts.init($('#container_<?php echo $this->_var['stattype']; ?>')[0]);
	barChart.setOption($.parseJSON('<?php echo $this->_var['stat_json']; ?>'));
</script>