<?php
/**
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * 请谨慎修改此文件,一般用不到直接修改,可以新建一个map类,然后在dao里做指定
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 */
 
class j7data_user_login_log extends J7Data
{

    protected $__data = [];
    protected $__data_keys = ['ulid'=>null,'uid'=>null,'created'=>null,'ip'=>null,];



    /*
    ** ActiveRecord 模式支持
    */

    /** @return $this */
    public function _ulid()
    {
        $args = func_get_args();
        array_unshift($args,'ulid');
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
    public function _created()
    {
        $args = func_get_args();
        array_unshift($args,'created');
        return $this->__property($args);
    }

    /** @return $this */
    public function _ip()
    {
        $args = func_get_args();
        array_unshift($args,'ip');
        return $this->__property($args);
    }


}