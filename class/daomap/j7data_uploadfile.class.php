<?php
/**
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * 请谨慎修改此文件,一般用不到直接修改,可以新建一个map类,然后在dao里做指定
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 */
 
class j7data_uploadfile extends J7Data
{

    protected $__data = [];
    protected $__data_keys = ['id'=>null,'path'=>null,'filename'=>null,'uid'=>null,'created_at'=>null,'filesize'=>null,'deleted'=>null,'gcode'=>null,'pid'=>null,'fromtype'=>null,'filetype'=>null,];



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
    public function _path()
    {
        $args = func_get_args();
        array_unshift($args,'path');
        return $this->__property($args);
    }

    /** @return $this */
    public function _filename()
    {
        $args = func_get_args();
        array_unshift($args,'filename');
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
    public function _filesize()
    {
        $args = func_get_args();
        array_unshift($args,'filesize');
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
    public function _gcode()
    {
        $args = func_get_args();
        array_unshift($args,'gcode');
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
    public function _fromtype()
    {
        $args = func_get_args();
        array_unshift($args,'fromtype');
        return $this->__property($args);
    }

    /** @return $this */
    public function _filetype()
    {
        $args = func_get_args();
        array_unshift($args,'filetype');
        return $this->__property($args);
    }


}