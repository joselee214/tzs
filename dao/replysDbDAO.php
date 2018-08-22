<?php
class replysDbDAO extends CachePKDbCrudDAO
{

    //表名
    protected $_table = 'replys';
    
    //数据库链接id
    protected $_dbindex = 0;

    //AR对象名
    //protected $_data_map = 'replys';
    
    protected $_pk = 'id';   //主键


    public function getMaxRepySort($pid)
    {
        $d = $this->get(['pid'=>$pid],null,null,null,'max(sort) as maxsort',false);
        if( $d && isset($d[0]) && isset($d[0]['maxsort']) )
        {
            return intval($d[0]['maxsort']);
        }
        return 0;
    }


    /*
    ************************
    * new方法是生成activeRecord的对象,临时数据,保存需要 $obj->save()
    */

    /** @return j7data_replys */
    public function _new($data=[])
    {
        $obj = new j7data_replys(get_called_class(),true);
        $obj->fromArray($data);
        return $obj;
    }

    /*
    ************************
    * 以下接口主要是为了做代码提示
    */

    /** @return j7data_replys|null */
    public function getByPk($pk,$pinfo = [])
    {
        return parent::getByPk($pk,$pinfo);
    }

    /** @return j7data_replys|null */
    public function getByFk($cond, $pinfo = [])
    {
        return parent::getByFk($cond,$pinfo);
    }

    /** @return j7data_replys[]|null */
    public function get($cond = null, $order = null, $count = null, $offset = null, $cols = null, $returnAr=null)
    {
        return parent::get($cond,$order,$count,$offset,$cols, $returnAr);
    }

    /** @return j7data_replys|null */
    public function getOne($cond = null, $order = null, $count = null, $offset = null, $cols = null, $returnAr=null)
    {
        return parent::getOne($cond,$order,$count,$offset,$cols, $returnAr);
    }


}
