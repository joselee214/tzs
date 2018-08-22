<?php
/**
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * 请谨慎修改此文件,一般用不到直接修改,可以新建一个map类,然后在dao里做指定
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 */
 
class j7data_wx_users extends J7Data
{

    protected $__data = [];
    protected $__data_keys = ['id'=>null,'openid'=>null,'session_id'=>null,'unionid'=>null,'appid'=>null,'uid'=>null,'gcode'=>null,'tcode'=>null,'tuid'=>null,'tfid'=>null,'tsid'=>null,'session_key'=>null,'expires_in'=>null,'nickName'=>null,'gender'=>null,'avatarUrl'=>null,'city'=>null,'province'=>null,'country'=>null,'created'=>null,'updated'=>null,];



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
    public function _openid()
    {
        $args = func_get_args();
        array_unshift($args,'openid');
        return $this->__property($args);
    }

    /** @return $this */
    public function _session_id()
    {
        $args = func_get_args();
        array_unshift($args,'session_id');
        return $this->__property($args);
    }

    /** @return $this */
    public function _unionid()
    {
        $args = func_get_args();
        array_unshift($args,'unionid');
        return $this->__property($args);
    }

    /** @return $this */
    public function _appid()
    {
        $args = func_get_args();
        array_unshift($args,'appid');
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
    public function _gcode()
    {
        $args = func_get_args();
        array_unshift($args,'gcode');
        return $this->__property($args);
    }

    /** @return $this */
    public function _tcode()
    {
        $args = func_get_args();
        array_unshift($args,'tcode');
        return $this->__property($args);
    }

    /** @return $this */
    public function _tuid()
    {
        $args = func_get_args();
        array_unshift($args,'tuid');
        return $this->__property($args);
    }

    /** @return $this */
    public function _tfid()
    {
        $args = func_get_args();
        array_unshift($args,'tfid');
        return $this->__property($args);
    }

    /** @return $this */
    public function _tsid()
    {
        $args = func_get_args();
        array_unshift($args,'tsid');
        return $this->__property($args);
    }

    /** @return $this */
    public function _session_key()
    {
        $args = func_get_args();
        array_unshift($args,'session_key');
        return $this->__property($args);
    }

    /** @return $this */
    public function _expires_in()
    {
        $args = func_get_args();
        array_unshift($args,'expires_in');
        return $this->__property($args);
    }

    /** @return $this */
    public function _nickName()
    {
        $args = func_get_args();
        array_unshift($args,'nickName');
        return $this->__property($args);
    }

    /** @return $this */
    public function _gender()
    {
        $args = func_get_args();
        array_unshift($args,'gender');
        return $this->__property($args);
    }

    /** @return $this */
    public function _avatarUrl()
    {
        $args = func_get_args();
        array_unshift($args,'avatarUrl');
        return $this->__property($args);
    }

    /** @return $this */
    public function _city()
    {
        $args = func_get_args();
        array_unshift($args,'city');
        return $this->__property($args);
    }

    /** @return $this */
    public function _province()
    {
        $args = func_get_args();
        array_unshift($args,'province');
        return $this->__property($args);
    }

    /** @return $this */
    public function _country()
    {
        $args = func_get_args();
        array_unshift($args,'country');
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
    public function _updated()
    {
        $args = func_get_args();
        array_unshift($args,'updated');
        return $this->__property($args);
    }


}