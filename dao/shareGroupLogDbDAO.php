<?php
class shareGroupLogDbDAO extends DbCrudDAO
{

    //表名
    protected $_table = 'share_group_log';
    
    //数据库链接id
    protected $_dbindex = 0;

    //AR对象名
    //protected $_data_map = 'share_group_log';
    
    protected $_pk = 'sglid';   //主键




    /*
    ************************
    * new方法是生成activeRecord的对象,临时数据,保存需要 $obj->save()
    */

    /** @return j7data_share_group_log */
    public function _new($data=[])
    {
        $obj = new j7data_share_group_log(get_called_class(),true);
        $obj->fromArray($data);
        return $obj;
    }

    /*
    ************************
    * 以下接口主要是为了做代码提示
    */

    /** @return j7data_share_group_log|null */
    public function getByPk($pk,$pinfo = [])
    {
        return parent::getByPk($pk,$pinfo);
    }

    /** @return j7data_share_group_log|null */
    public function getByFk($cond, $pinfo = [])
    {
        return parent::getByFk($cond,$pinfo);
    }

    /** @return j7data_share_group_log[]|null */
    public function get($cond = null, $order = null, $count = null, $offset = null, $cols = null, $returnAr=null)
    {
        return parent::get($cond,$order,$count,$offset,$cols, $returnAr);
    }

    /** @return j7data_share_group_log|null */
    public function getOne($cond = null, $order = null, $count = null, $offset = null, $cols = null, $returnAr=null)
    {
        return parent::getOne($cond,$order,$count,$offset,$cols, $returnAr);
    }


}
