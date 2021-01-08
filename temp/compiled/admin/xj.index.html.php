<?php echo $this->fetch('header.html'); ?>

<div id="rightTop">
    <p>询价管理</p>
    
  </div>
  <div class="search-form clearfix">
      <form method="get" id="formSearch">
            <input type="hidden" name="app" value="evaluation" />
            <select class="querySelect" name="field_name"><?php echo $this->html_options(array('options'=>$this->_var['query_fields'],'selected'=>$_GET['field_name'])); ?>
            </select>
            <input class="queryInput" type="text" name="field_value" value="<?php echo htmlspecialchars($_GET['field_value']); ?>" />
           
            有无内容 : 
            <select class="querySelect" name="comment">
                <option>全部</option>
                <option value="1" <?php if ($_GET['comment'] == '1'): ?>selected<?php endif; ?>>无</option>
                <option value='2' <?php if ($_GET['comment'] == '2'): ?>selected<?php endif; ?>>有</option>
            </select>
            <input type="submit" class="formbtn" value="查询" />
        <?php if ($this->_var['filtered']): ?>
        <a class="formbtn formbtn1" href="index.php?app=evaluation">撤销检索</a>
        <?php endif; ?>
      </form>
  </div>
  <div id="flexigrid"></div>
  <script type="text/javascript">
  $(function(){
      var data_url = 'index.php?app=evaluation&act=get_xml&'+$("#formSearch").serialize();
      $("#flexigrid").flexigrid({
          url: data_url,
          colModel : [
              {display: '操作', name : 'operation', width : 100, sortable : false, align: 'center', className: 'handle'},
              {display: '询价时间', name : 'evaluation_time', width : 100, sortable : true, align: 'center'},
              {display: '询价人', name : 'buyer_name', width : 100, sortable : true, align: 'center'},
              {display: '询价内容', name : 'store_name', width : 150, sortable : true, align: 'center'},
              {display: '备注信息', name : 'goods_name', width : 100, sortable : true, align: 'center'},
              {display: '公司名称', name : 'goods_name', width : 200, sortable : true, align: 'center'},
              {display: '联系电话', name : 'evaluation', width: 100, sortable : true, align : 'center'},    		
              {display: '联系邮箱', name : 'comment', width: 200, sortable : true, align : 'center'}	
              ],
          buttons : [
              {display: '<i class="fa fa-trash"></i>批量删除', name : 'del', bclass : 'del', title : '将选定行数据批量删除', onpress : fg_operate }	
          ],
          title: '询价列表'
      });
  });
  function fg_operate(name, bDiv) {
      var itemlist = new Array();
      $('.trSelected',bDiv).each(function(){
          itemlist.push($(this).attr('data-id'));
      });
      if (name == 'del') {
         if($('.trSelected',bDiv).length==0){
             parent.layer.alert('没有选择操作项',{icon: 0});
              return false;
         }
         fg_delete(itemlist,'evaluation');
      }
  }
  </script>
<?php echo $this->fetch('footer.html'); ?> 