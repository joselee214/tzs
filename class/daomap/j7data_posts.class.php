<?php
/**
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * 请谨慎修改此文件,一般用不到直接修改,可以新建一个map类,然后在dao里做指定
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 */
 
class j7data_posts extends J7Data
{

    protected $__data = [];
    protected $__data_keys = ['id'=>null,'uid'=>null,'created_at'=>null,'title'=>null,'content'=>null,'imgs'=>null,'files'=>null,'viewtimes'=>null,'replytimes'=>null,'signtimes'=>null,'deleted'=>null,'gids'=>null,'allowReply'=>null,'can_sign_up'=>null,'sign_up_options'=>null,];



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

    /** @return $this */
    public function _title()
    {
        $args = func_get_args();
        array_unshift($args,'title');
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
    public function _imgs()
    {
        $args = func_get_args();
        array_unshift($args,'imgs');
        return $this->__property($args);
    }

    /** @return $this */
    public function _files()
    {
        $args = func_get_args();
        array_unshift($args,'files');
        return $this->__property($args);
    }

    /** @return $this */
    public function _viewtimes()
    {
        $args = func_get_args();
        array_unshift($args,'viewtimes');
        return $this->__property($args);
    }

    /** @return $this */
    public function _replytimes()
    {
        $args = func_get_args();
        array_unshift($args,'replytimes');
        return $this->__property($args);
    }

    /** @return $this */
    public function _signtimes()
    {
        $args = func_get_args();
        array_unshift($args,'signtimes');
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
    public function _gids()
    {
        $args = func_get_args();
        array_unshift($args,'gids');
        return $this->__property($args);
    }

    /** @return $this */
    public function _allowReply()
    {
        $args = func_get_args();
        array_unshift($args,'allowReply');
        return $this->__property($args);
    }

    /** @return $this */
    public function _can_sign_up()
    {
        $args = func_get_args();
        array_unshift($args,'can_sign_up');
        return $this->__property($args);
    }

    /** @return $this */
    public function _sign_up_options()
    {
        $args = func_get_args();
        array_unshift($args,'sign_up_options');
        return $this->__property($args);
    }


}