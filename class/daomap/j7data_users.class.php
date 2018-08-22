<?php
/**
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * 请谨慎修改此文件,一般用不到直接修改,可以新建一个map类,然后在dao里做指定
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 */
 
class j7data_users extends J7Data
{

    protected $__data = [];
    protected $__data_keys = ['uid'=>null,'username'=>null,'email'=>null,'mobile'=>null,'encrypted_password'=>null,'validate_status'=>null,'created'=>null,'updated'=>null,'last_login'=>null,'is_reg_user'=>null,'identity'=>null,'trace_code'=>null,'name'=>null,'name_seted'=>null,'avatarUrl'=>null,];



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
    public function _username()
    {
        $args = func_get_args();
        array_unshift($args,'username');
        return $this->__property($args);
    }

    /** @return $this */
    public function _email()
    {
        $args = func_get_args();
        array_unshift($args,'email');
        return $this->__property($args);
    }

    /** @return $this */
    public function _mobile()
    {
        $args = func_get_args();
        array_unshift($args,'mobile');
        return $this->__property($args);
    }

    /** @return $this */
    public function _encrypted_password()
    {
        $args = func_get_args();
        array_unshift($args,'encrypted_password');
        return $this->__property($args);
    }

    /** @return $this */
    public function _validate_status()
    {
        $args = func_get_args();
        array_unshift($args,'validate_status');
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

    /** @return $this */
    public function _last_login()
    {
        $args = func_get_args();
        array_unshift($args,'last_login');
        return $this->__property($args);
    }

    /** @return $this */
    public function _is_reg_user()
    {
        $args = func_get_args();
        array_unshift($args,'is_reg_user');
        return $this->__property($args);
    }

    /** @return $this */
    public function _identity()
    {
        $args = func_get_args();
        array_unshift($args,'identity');
        return $this->__property($args);
    }

    /** @return $this */
    public function _trace_code()
    {
        $args = func_get_args();
        array_unshift($args,'trace_code');
        return $this->__property($args);
    }

    /** @return $this */
    public function _name()
    {
        $args = func_get_args();
        array_unshift($args,'name');
        return $this->__property($args);
    }

    /** @return $this */
    public function _name_seted()
    {
        $args = func_get_args();
        array_unshift($args,'name_seted');
        return $this->__property($args);
    }

    /** @return $this */
    public function _avatarUrl()
    {
        $args = func_get_args();
        array_unshift($args,'avatarUrl');
        return $this->__property($args);
    }


}