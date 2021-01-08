<?php echo $this->fetch('member.header.html'); ?> 
<script type="text/javascript">
$(function(){
    var t = new EditableTable($('#my_goods'));
});
</script>
<div id="main" class="clearfix"> <?php echo $this->fetch('member.menu.html'); ?>
  <div id="right"> <?php echo $this->fetch('member.curlocal.html'); ?>
    <div class="wrapful"> <?php echo $this->fetch('member.submenu.html'); ?>
      <div class="wrap">
        <div class="public_select table">
          <table id="my_goods"  server="<?php echo $this->_var['site_url']; ?>/index.php?app=my_goods&amp;act=ajax_col" >
            <tr class="line_bold">
              <th colspan="10"> <div class="search_div clearfix">
                  <form id="my_goods_form" method="get" class="float-left">
                    <input type="hidden" name="app" value="my_goods">
                    <select class="select1" name='sgcate_id'>
                      <option value="0">本店分类</option>
                      
                                		<?php echo $this->html_options(array('options'=>$this->_var['sgcategories'],'selected'=>$_GET['sgcate_id'])); ?>
                            		
                    </select>
                    <select class="select2" name="character">
                      <option value="0">状态</option>
                      
                                		<?php echo $this->html_options(array('options'=>$this->_var['lang']['character_array'],'selected'=>$_GET['character'])); ?>
                            		
                    </select>
                    <input type="text" name="keyword" value="<?php echo htmlspecialchars($_GET['keyword']); ?>"/>
                    <input type="submit" class="btn" value="搜索" />
                  </form>
                  <?php if ($this->_var['filtered']): ?> 
                  <a class="detlink" href="<?php echo url('app=my_goods'); ?>">取消检索</a> 
                  <?php endif; ?> 
                </div>
              </th>
            </tr>
            <tr class="sep-row" height="20">
              <td colspan="10"></td>
            </tr>
            <?php if ($this->_var['goods_list']): ?>
            <tr class="gray"  ectype="table_header">
              <th class="align1" width="10"><input type="checkbox" id="all" class="checkall"/> <label for="all">全选</label></th>
              <th coltype="editable" column="goods_name" checker="check_required" inputwidth="90%" title="排序"  class="cursor_pointer align1"><span ectype="order_by">商品名称</span></th>
              <th width="80" column="cate_id" title="排序"  class="cursor_pointer"><span ectype="order_by">商品分类</span></th>
              <th coltype="editable" column="brand" checker="check_required" inputwidth="55px" title="排序"  class="cursor_pointer"><span ectype="order_by">品牌</span></th>
              <th class="cursor_pointer" coltype="editable" column="price" checker="check_number" inputwidth="50px" title="排序"><span ectype="order_by">价格</span></th>
              <th class="cursor_pointer" coltype="editable" column="stock" checker="check_pint" inputwidth="50px" title="排序"><span ectype="order_by">库存</span></th>
              <th coltype="switchable" column="if_show" onclass="right_ico" offclass="wrong_ico" title="排序"  class="cursor_pointer"><span ectype="order_by">上架</span></th>
              <th coltype="switchable" column="recommended" onclass="right_ico" offclass="wrong_ico" title="排序"  class="cursor_pointer"><span ectype="order_by">推荐</span></th>
              <th column="closed" title="排序" class="cursor_pointer"><span ectype="order_by">禁售</span></th>
              <th>操作</th>
            </tr>
            <tr class="sep-row">
              <td colspan="10"></td>
            </tr>
            
            <?php endif; ?> 
            <?php $_from = $this->_var['goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['_goods_f'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['_goods_f']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['_goods_f']['iteration']++;
?>
            <tr class="sep-row">
              <td colspan="10"></td>
            </tr>
            <tr class="line-hd">
              <th colspan="10" align="left"> <p>
                  <input id="checkbox_<?php echo $this->_var['goods']['goods_id']; ?>" type="checkbox" class="checkitem" value="<?php echo $this->_var['goods']['goods_id']; ?>" align="absmiddle" />
                  <label for="checkbox_<?php echo $this->_var['goods']['goods_id']; ?>">商家编码</label>
                  <?php echo $this->_var['goods']['specs']['0']['sku']; ?></p>
              </th>
            </tr>
            <tr class="line line-blue<?php if (($this->_foreach['_goods_f']['iteration'] == $this->_foreach['_goods_f']['total'])): ?> last_line<?php endif; ?>" ectype="table_item" idvalue="<?php echo $this->_var['goods']['goods_id']; ?>">
              <td width="10" class="align1 first"><a href="<?php echo url('app=goods&id=' . $this->_var['goods']['goods_id']. ''); ?>" target="_blank"><img src="<?php echo $this->_var['site_url']; ?>/<?php echo $this->_var['goods']['default_image']; ?>" width="50" height="50" /></a></td>
              <td class="align1"><p class="ware_text"><span class="color2" ectype="editobj"><?php echo htmlspecialchars($this->_var['goods']['goods_name']); ?></span></p></td>
              <td width="80" class="align2"><span class="color2"><?php echo nl2br($this->_var['goods']['cate_name']); ?></span></td>
              <td class="align2"><span class="color2" ectype="editobj"><?php echo htmlspecialchars($this->_var['goods']['brand']); ?></span></td>
              <td class="align2"><?php if ($this->_var['goods']['spec_qty']): ?><span ectype="dialog" dialog_width="430" uri="index.php?app=my_goods&amp;act=spec_edit&amp;id=<?php echo $this->_var['goods']['goods_id']; ?>" dialog_title="编辑价格和库存" dialog_id="my_goods_spec_edit" class="cursor_pointer"><?php echo $this->_var['goods']['price']; ?></span><?php else: ?><span class="color2" ectype="editobj"><?php echo $this->_var['goods']['price']; ?></span><?php endif; ?></td>
              <td class="align2"><?php if ($this->_var['goods']['spec_qty']): ?><span ectype="dialog" dialog_width="430" uri="index.php?app=my_goods&amp;act=spec_edit&amp;id=<?php echo $this->_var['goods']['goods_id']; ?>" dialog_title="编辑价格和库存" dialog_id="my_goods_spec_edit" class="cursor_pointer"><?php echo $this->_var['goods']['stock']; ?></span><?php else: ?><span class="color2" ectype="editobj"><?php echo $this->_var['goods']['stock']; ?></span><?php endif; ?></td>
              <td class="align2"><span style="margin-left:15px;" ectype="editobj" <?php if ($this->_var['goods']['if_show']): ?>class="right_ico" status="on"<?php else: ?>class="wrong_ico" stauts="off"<?php endif; ?>></span></td>
              <td class="align2"><span style="margin-left:15px;" ectype="editobj" <?php if ($this->_var['goods']['recommended']): ?>class="right_ico" status="on"<?php else: ?>class="wrong_ico" stauts="off"<?php endif; ?>></span></td>
              <td class="align2"><span style="margin-left:15px;" <?php if ($this->_var['goods']['closed']): ?>class="no_ico"<?php else: ?>class="no_ico_disable"<?php endif; ?>></span></td>
              <td class="last"><a href="<?php echo url('app=my_goods&act=edit&id=' . $this->_var['goods']['goods_id']. '&ret_page=' . $this->_var['page_info']['curr_page']. ''); ?>" class="edit">编辑</a> <a href="javascript:drop_confirm('您确定要删除它吗？', 'index.php?app=my_goods&amp;act=drop&id=<?php echo $this->_var['goods']['goods_id']; ?>');" class="delete">删除</a></td>
            </tr>
            <?php endforeach; else: ?>
            <tr>
              <td colspan="10"><div class="notice-word">
                  <p><?php echo $this->_var['lang'][$_GET['act']]; ?>没有符合条件的商品</p>
                </div></td>
            </tr>
            <?php endif; unset($_from); ?><?php $this->pop_vars();; ?> 
            <?php if ($this->_var['goods_list']): ?>
            <tr class="sep-row">
              <td colspan="10"></td>
            </tr>
            <tr class="operations btion">
              <th class="clearfix"><input type="checkbox" id="all2" class="checkall float-left" style="margin:3px 5px 0 0" />
                  <label for="all2">全选</label></th>
              <th colspan="9"> <p class="position1 clearfix">
                  
                  <a href="javascript:void(0);" class="edit" ectype="batchbutton" uri="index.php?app=my_goods&act=batch_edit" name="id">编辑</a> <a href="javascript:void(0);" class="delete" ectype="batchbutton" uri="index.php?app=my_goods&act=drop" name="id" presubmit="confirm('您确定要删除它吗？')">删除</a> </p>
                <div class="position2 clearfix"> <?php echo $this->fetch('member.page.bottom.html'); ?> </div>
              </th>
            </tr>
            <?php endif; ?>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<iframe name="iframe_post" id="iframe_post" width="0" height="0"></iframe>
<?php echo $this->fetch('member.footer.html'); ?>