<?php
class userDbDAO extends CachePKDbCrudDAO
{

    //表名
    protected $_table = 'users';
    
    //数据库链接id
    protected $_dbindex = 0;

    //AR对象名
    //protected $_data_map = 'users';
    
    protected $_pk = 'uid';   //主键


    /**
     * @param $cond
     * @return j7data_users|null
     */
    function getUser($cond) {
//        $this->_pkCache = false;
        if (isset($cond['uid'])) {
            $user = $this->getByPk($cond['uid']);
        } else if (isset($cond['user'])) {
            $user = $this->get(array('username' => $cond['user']));
            $user = $user ? $user[0] : null;
            if( is_null($user) )
            {
                $user = $this->get(array('email=? OR mobile=?' => $cond['user']));
                $user = $user ? $user[0] : null;
            }
        } else if (isset($cond['mobile'])) {
            $user = $this->get(array('mobile' => $cond['mobile']));
            $user = $user ? $user[0] : null;
        } else if (isset($cond['email'])) {
            $user = $this->get(array('email' => $cond['email']));
            $user = $user ? $user[0] : null;
        } else if (isset($cond['username'])) {
            $user = $this->get(array('username' => $cond['username']));
            $user = $user ? $user[0] : null;
        }
        return $user;
    }

    function addUser($user) {
        $user['created'] = Util::getTime();
        return $this->add($user);
    }
    /**
     * 单向 hash 加密 密码
     *
     * @param string $pwd 原文
     * @param string $hash hash
     * @return string
     */
    function encryptPassword($pwd, $hash) {
        return md5($pwd.'-'.$hash).'|'.$hash;
    }


    /*
    ************************
    * new方法是生成activeRecord的对象,临时数据,保存需要 $obj->save()
    */

    /** @return j7data_users */
    public function _new($data=[])
    {
        $obj = new j7data_users(get_called_class(),true);
        $obj->fromArray($data);
        return $obj;
    }

    /*
    ************************
    * 以下接口主要是为了做代码提示
    */

    /** @return j7data_users|null */
    public function getByPk($pk,$pinfo = [])
    {
        return parent::getByPk($pk,$pinfo);
    }

    /** @return j7data_users|null */
    public function getByFk($cond, $pinfo = [])
    {
        return parent::getByFk($cond,$pinfo);
    }

    /** @return j7data_users[]|null */
    public function get($cond = null, $order = null, $count = null, $offset = null, $cols = null, $returnAr=null)
    {
        return parent::get($cond,$order,$count,$offset,$cols, $returnAr);
    }

    /** @return j7data_users|null */
    public function getOne($cond = null, $order = null, $count = null, $offset = null, $cols = null, $returnAr=null)
    {
        return parent::getOne($cond,$order,$count,$offset,$cols, $returnAr);
    }


}
