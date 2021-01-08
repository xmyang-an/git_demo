<?php echo $this->fetch('top.html'); ?>
<style type="text/css">
.friend ul li:hover, .friend ul li:active{background:#f4f4f4;}
</style>
<div id="main">
  <div class="page-webim">
    <div class="layim-chat-title" style="background-color: #36373C;">
      <p><span class="layim-back psmb-icon-font" onclick="javascript:window.history.back();">&#xe628;</span>消息<span class="layim-chat-status"></span><span class="layim-message psmb-icon-font" onclick="javascript:go('<?php echo $this->_var['real_site_url']; ?>')">&#xe63c;</span></p>
    </div>
    <div class="friend J_Friend">
      <ul class="layui-unselect layim-tab-content layui-show layim-list-friend">
        <li> 
          <?php $_from = $this->_var['friendList']['data']['friend']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'list');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['list']):
?>
          <h5 layim-event="spread" lay-type="true" style="display:none"><i class="layui-icon"></i><span><?php echo $this->_var['list']['groupname']; ?></span><em>(<cite class="layim-count">2</cite>)</em></h5>
          <ul class="layui-layim-list layui-show">
            <?php if ($this->_var['list']['list']): ?> 
            <?php $_from = $this->_var['list']['list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'friend');$this->_foreach['fe_friend'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_friend']['total'] > 0):
    foreach ($_from AS $this->_var['friend']):
        $this->_foreach['fe_friend']['iteration']++;
?>
            <?php if ($this->_var['friend']['id'] != $this->_var['visitor']['user_id']): ?>
            <li layim-event="chat" data-type="friend" data-index="<?php echo $this->_var['key']; ?>" id="layim-friend<?php echo $this->_foreach['fe_friend']['iteration']; ?>" class="J_StartLayim clearfix" data-toid="<?php echo $this->_var['friend']['id']; ?>"> <img src="<?php echo $this->_var['friend']['avatar']; ?>" class="avatar"><span><?php echo $this->_var['friend']['username']; ?></span><?php if ($this->_var['friend']['unread']): ?><ins class="unread"><?php echo $this->_var['friend']['unread']; ?></ins><?php endif; ?>
              <p class="last-talk"><?php echo ($this->_var['friend']['lastTalk'] == '') ? '暂无新消息' : $this->_var['friend']['lastTalk']; ?></p>
            </li>
            <?php endif; ?> 
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
            <?php else: ?>
            <li>暂时没有消息</li>
            <?php endif; ?>
          </ul>
          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
        </li>
      </ul>
    </div>
  </div>
</div>

<link type="text/css" href="<?php echo $this->lib_base . "/" . 'layui/css/layui.mobile.css'; ?>" rel="stylesheet" media="all"/>
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'layui/layui.js'; ?>" charset="utf-8"></script> 
<script type="text/javascript" src="<?php echo $this->lib_base . "/" . 'layui/webim.mobile.js'; ?>" charset="utf-8"></script>
</body></html>