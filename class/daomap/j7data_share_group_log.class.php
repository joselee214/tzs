<?php
/**
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * 请谨慎修改此文件,一般用不到直接修改,可以新建一个map类,然后在dao里做指定
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 */
 
class j7data_share_group_log extends J7Data
{

    protected $__data = [];
    protected $__data_keys = ['sglid'=>null,'gid'=>null,'pid'=>null,'uid'=>null,'created_at'=>null,];



    /*
    ** ActiveRecord 模式支持
    */

    /** @return $this */
    public function _sglid()
    {
        $args = func_get_args();
        array_unshift($args,'sglid');
        return $this->__property($args);
    }

    /** @return $this */
    public function _gid()
    {
        $args = func_get_args();
        array_unshift($args,'gid');
        return $this->__property($args);
    }

    /** @return $this */
    public function _pid()
    {
        $args = func_get_args();
        array_unshift($args,'pid');
        return $this->__property($args);
    }

    /** @return $this */
    public function _uid()
    {
        $args = func_get_args();
        array_unshift($args,'uid');
        return $this->__property($args);
    }

    /** @return $this */
    public function _created_at()
    {
        $args = func_get_args();
        array_unshift($args,'created_at');
        return $this->__property($args);
    }


}