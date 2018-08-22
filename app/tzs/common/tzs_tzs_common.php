<?php
//require_once __DIR__.'/tzs_common.php';

class tzs_tzs_common extends tzs_common
{
    //Action 加载时候执行的方法...
    public function __j7construct()
    {
        parent::__j7construct();
        if( is_null($this->_userme) || !isset($this->_userme['uid']) || !$this->_userme['uid'] )
            throw new BizException(['error'=>'not login'],'login');
    }
}