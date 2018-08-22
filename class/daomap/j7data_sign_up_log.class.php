<?php
/**
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * 请谨慎修改此文件,一般用不到直接修改,可以新建一个map类,然后在dao里做指定
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 */
 
class j7data_sign_up_log extends J7Data
{

    protected $__data = [];
    protected $__data_keys = ['uid'=>null,'pid'=>null,'signup'=>null,'created_at'=>null,];



    /*
    ** ActiveRecord 模式支持
    */

    /** @return $this */
    public function _uid()
    {
        $args = func_get_args();
        array_unshift($args,'uid');
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
    public function _signup()
    {
        $args = func_get_args();
        array_unshift($args,'signup');
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