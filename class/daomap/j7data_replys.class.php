<?php
/**
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * 请谨慎修改此文件,一般用不到直接修改,可以新建一个map类,然后在dao里做指定
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 */
 
class j7data_replys extends J7Data
{

    protected $__data = [];
    protected $__data_keys = ['id'=>null,'pid'=>null,'uid'=>null,'content'=>null,'created_at'=>null,'deleted'=>null,'sort'=>null,];



    /*
    ** ActiveRecord 模式支持
    */

    /** @return $this */
    public function _id()
    {
        $args = func_get_args();
        array_unshift($args,'id');
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
    public function _content()
    {
        $args = func_get_args();
        array_unshift($args,'content');
        return $this->__property($args);
    }

    /** @return $this */
    public function _created_at()
    {
        $args = func_get_args();
        array_unshift($args,'created_at');
        return $this->__property($args);
    }

    /** @return $this */
    public function _deleted()
    {
        $args = func_get_args();
        array_unshift($args,'deleted');
        return $this->__property($args);
    }

    /** @return $this */
    public function _sort()
    {
        $args = func_get_args();
        array_unshift($args,'sort');
        return $this->__property($args);
    }


}