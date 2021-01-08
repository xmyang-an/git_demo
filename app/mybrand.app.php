<?php
   class MybrandApp extends MallbaseApp{
    //    获取品牌
       function _list_brand()
       {
           $brand_mod=& m('brand');
           $brand=$brand_mod->get(
            array(
                'conditions' => 'if_show = 1',
            )

           );
           return $brand;
       }
       function index()
       {
           $mybrand=$this->_list_brand();
           $this->assign('mybrand',$mybrand);
           $this->display('mybrand.index.html');
       }
   }
?>