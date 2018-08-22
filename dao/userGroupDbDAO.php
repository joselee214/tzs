<?php
class userGroupDbDAO extends CachePKDbCrudDAO
{

    //表名
    protected $_table = 'user_group';
    
    //数据库链接id
    protected $_dbindex = 0;

    //AR对象名
    //protected $_data_map = 'user_group';
    
    protected $_pk = ['uid','gid'];   //主键




    /*
    ************************
    * new方法是生成activeRecord的对象,临时数据,保存需要 $obj->save()
    */

    /** @return j7data_user_group */
    public function _new($data=[])
    {
        $obj = new j7data_user_group(get_called_class(),true);
        $obj->fromArray($data);
        return $obj;
    }

    /*
    ************************
    * 以下接口主要是为了做代码提示
    */

    /** @return j7data_user_group|null */
    public function getByPk($pk,$pinfo = [])
    {
        return parent::getByPk($pk,$pinfo);
    }

    /** @return j7data_user_group|null */
    public function getByFk($cond, $pinfo = [])
    {
        return parent::getByFk($cond,$pinfo);
    }

    /** @return j7data_user_group[]|null */
    public function get($cond = null, $order = null, $count = null, $offset = null, $cols = null, $returnAr=null)
    {
        return parent::get($cond,$order,$count,$offset,$cols, $returnAr);
    }

    /** @return j7data_user_group|null */
    public function getOne($cond = null, $order = null, $count = null, $offset = null, $cols = null, $returnAr=null)
    {
        return parent::getOne($cond,$order,$count,$offset,$cols, $returnAr);
    }


}
