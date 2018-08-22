<?php
class J7Data implements ArrayAccess, Iterator, Countable ,JsonSerializable
{
    protected $__data = [];
    protected $__data_keys,$__data_pk;
    private $__data_modify = [];
    private $__pkName,$__daoName;
    private $__isnew=false;
    private $__deleted=false;

    protected $_columns_config; //安全性检查 //非空//过滤//等等等等


//    public function __debugInfo()
//    {
//        $vars = get_object_vars($this);
//        if( isset($vars['__data_keys']) )
//            $vars['__data_keys'] = implode(',',array_keys($vars['__data_keys']));
//        return $vars;
//    }

    function __construct($daoname,$isnew=true)
    {
        if( !$daoname )
            throw new J7Exception('daoname must set'.var_export($this,true));

        $this->__daoName = $daoname;
        $this->__isnew = $isnew;

        $this->__data = $this->__data_keys;

        $this->__pkName = $this->getDaoObj()->__getPkName();
    }

    /**
     * @return DbCrudDAO
     */
    function getDaoObj()
    {
        return $this->getServiceDao($this->__daoName);
    }

    function __clone()
    {
        $this->__isnew = true;
        $this->__deleted = false;

        if( $this->__pkName && is_numeric($this->__property([$this->__pkName])) )
        {
            $this->__property([$this->__pkName,null]);
        }
    }

    function __toString()
    {
        return json_encode($this->toArray());
    }

    /*
     * ****************************
     * 对外可用方法
     */
    function toArray($withOther=false) // true
    {
        if( $withOther )
            return $this->__data;
        else
        {
            return array_intersect_key($this->__data,$this->__data_keys);
        }
    }

    /**
     * @param array $data
     * @param bool $isInit true初始化时候,不需要modify
     * @return $this
     * @throws J7Exception
     */
    function fromArray($data,$isInit=false)
    {
        //获取修改的并集
        $modify = array_intersect_key($data,$this->__data_keys);
        if( $modify )
        {
            foreach ($modify as $key=>$v)
            {
                $this->__property([$key,$v]);
            }
        }
        if($isInit)
        {
            if( $this->__pkName )
                $this->__data_pk = is_array($this->__pkName)?array_intersect_key($this->__data, array_fill_keys($this->__pkName, null)):$this->__data[$this->__pkName]; //主键备份
            $this->__data_modify = [];
        }
        return $this;
    }


    /**
     * @return $this|array|null
     * @throws J7Exception
     */
    function save()
    {
        if( $this->isDeleted() )
            throw new J7Exception('data is deleted 2 @:'.var_export($this,true));

        if( !$this->isNew() && empty( $this->__data_modify ) )
            return $this;

        if( $this->isNew() )
        {
            $this->__isnew = false;
            $this->__data_modify = [];

            if( $this->__pkName && is_string($this->__pkName) )
            {
                $adddata = array_filter($this->__data,function ($v){return !is_null($v);});
                $pk = $this->getDaoObj()->add($adddata);
                $result = $this->getDaoObj()->getByPk($pk);
                if($result)
                {
                    $this->fromArray($result->toArray());
                    $this->__isnew = false;
                    $this->__data_modify = [];
                }
                return $pk;
            }
            elseif( count($this->__pkName) == count(array_intersect_key($this->__data, array_fill_keys($this->__pkName, null))) )
            {
                $ret = $this->getDaoObj()->add($this->__data);
                $logicPk = array_intersect_key($this->__data, array_fill_keys($this->__pkName, null));
                $result = $this->getDaoObj()->getByPk($logicPk);
                if( $result )
                {
                    $this->fromArray($result->toArray());
                    $this->__isnew = false;
                    $this->__data_modify = [];
                }
                return $ret;
            }
            else
            {
                return $this->getDaoObj()->addinstant($this->__data);
            }
        }
        else
        {
            if( $this->__pkName )
            {
                if (is_string($this->__pkName))
                {
                    if( empty($this->__data_modify[$this->__pkName]) || $this->__data_modify[$this->__pkName] == $this->__data_pk )
                    {
                        $this->__data_modify[$this->__pkName] = $this->__data[$this->__pkName];
                        $this->getDaoObj()->updateByPk($this->__data_modify);
                    }
                    else
                    {
                        if( is_null($this->__data_pk) )
                            throw new J7Exception('Cound Find _pk in ' . get_class($this) . '@J7Data save!');

                        $this->getDaoObj()->update($this->__data_modify,[$this->__pkName=>$this->__data_pk]);
                    }
                    $this->__data_modify = [];
                    return $this->getDaoObj()->getByPk($this->__data[$this->__pkName]);
                }
                else
                {
                    //更新时候,从备份数据拿主键值...
                    if( is_null($this->__data_pk) || ( count($this->__pkName)!=count(array_intersect_key($this->__data, array_fill_keys($this->__pkName, null))) ) )
                        throw new J7Exception('Cound Find _pk in ' . get_class($this) . '@J7Data save!');
                    $this->getDaoObj()->update($this->__data_modify,$this->__data_pk);
                    $this->__data_pk = array_intersect_key($this->__data, array_fill_keys($this->__pkName, null));
                    $this->__data_modify = [];
                    return $this->getDaoObj()->getByPk($this->__data_pk);
                }
            }
        }
        throw new J7Exception('can not save() in:'.var_export($this,true));
    }
    function delete($isForce=false)
    {
        $this->__deleted = true;
        if( $this->isNew() )
        {
            if( $isForce && !empty($this->__data) )
                return $this->getDaoObj()->delete($this->__data);
            throw new J7Exception('can not delete() 3 @:'.var_export($this,true));
        }

        if( $this->__pkName )
        {
            return $this->getDaoObj()->deleteByPk($this->__data);
        }
        if( $isForce )
            return $this->getDaoObj()->delete($this->__data);
        throw new J7Exception('can not delete() 1 @:'.var_export($this,true));
    }
    function isNew()
    {
        return $this->__isnew;
    }
    function isDeleted()
    {
        return $this->__deleted;
    }
    function isModify()
    {
        if( !$this->isNew() && empty( $this->__data_modify ) )
            return false;
        return true;
    }

    /*
     * ****************************
     * 数据处理方法 :: 注意是 static 的
     */

    static function rArrayRecursive(&$data) //Recursive 递归to_array
    {
        if( $data instanceof J7Data )
            $data = $data->toArray(true);
        array_walk_recursive($data,function(&$d){ if($d instanceof J7Data){$d=$d->toArray(true);} });
        return $data;
    }
    static function rArray($data) //单结构 to_array
    {
        return ($data instanceof J7Data)?$data->toArray(true):$data;
    }








    /*
     * ****************************
     * 框架实现
     */

    function __call($name, $arguments)
    {
        throw new J7Exception('not support in 2 J7Data:'.var_export($this,true));
    }

    /**
     * @param array $args
     * @return $this|bool|mixed
     * @throws J7Exception
     */
    protected function __property($args=[])
    {
        if( !key_exists($args[0],$this->__data_keys) && count($args)==2 )
            return $this->__data[$args[0]] = $args[1];
//        var_dump(get_called_class());
//        var_dump($args);
        if( empty($args) || !key_exists($args[0],$this->__data) )
            return false;
        if( $this->isDeleted() )
            throw new J7Exception('data is deleted 1 @:'.var_export($this,true));

        if(count($args)==1)
        {
            return $this->__data[$args[0]];
        }
        elseif(count($args)==2 && $args[1] instanceof Closure)
        {
            return $args[1]($this->__data[$args[0]]);
        }
        if( $this->__data[$args[0]] !== $args[1] )
        {
            if( is_array($args[1]) )
                $args[1] = json_encode($args[1]);
            $this->__data_modify[$args[0]] = $args[1];
            $this->__data[$args[0]] = $args[1];
        }
        return $this;
    }


    function __set($index,$newvalue)
    {
        if( $this->__pkName )
        {
            if( is_array($this->__pkName) )
            {
                if( in_array($index,$this->__pkName) && !isset($this->__data_pk[$index]) )
                    $this->__data_pk[$index] = $newvalue; //主键备份
            }
            elseif ($this->__pkName==$index && empty($this->__data_pk))
                $this->__data_pk = $newvalue;
        }
        return $this->__data[$index] = $newvalue;
    }
    function __get($index)
    {
        if( method_exists($this,$method = '_'.$index) )
        {
            return $this->$method();
        }
        if( isset($this->__data[$index]) )
        {
            return $this->__data[$index];
        }
        throw new J7Exception('not support in 3 J7Data:'.var_export($this,true));
    }

    function __isset($index)
    {
        return isset($this->__data[$index]);
    }

    /**
     * @access private
     */
    protected function getServiceDao($name)
    {
        if( substr($name, -3, 3) === 'DAO' )
        {
            return FactoryObject::Get($name);
        }
        throw new J7Exception('only DAO can use in DAO '.$name.' @ '.var_export($this,true));
    }

    /*
     * ****************************
     * 递归接口实现
     */

    function current()
    {
        return current($this->__data);
    }
    function end()
    {
        return end($this->__data);
    }
    function next()
    {
        return next($this->__data);
    }
    function key()
    {
        return key($this->__data);
    }
    function valid()
    {
        return ( $this->current() !== false );
    }
    function rewind()
    {
        reset($this->__data);
    }

    /*
     * ****************************
     * 数组接口实现
     */

    function count()
    {
        return count($this->__data);
    }

    /**
     * @access private
     */
    function offsetExists($index)
    {
        return key_exists($index,$this->__data);
    }
    /**
     * @access private
     */
    function offsetGet($index) {
        if( key_exists($index,$this->__data) )
        {
            return $this->__data[$index];
        }
        elseif( method_exists($this,$method = '_'.$index) )
        {
            return $this->$method($index);
        }
        return false;
    }
    /**
     * @access private
     */
    function offsetSet($index, $newvalue) {
        if( !isset($this->__data_keys[$index]) && isset($newvalue) )
        {
            return $this->__data[$index] = $newvalue;
        }

        if( isset($newvalue) )
        {
            $this->__property([$index,$newvalue]);
            return $this;
        }
        if( method_exists($this,$method = '_'.$index) )
        {
            return $this->$method($newvalue);
        }
        return $this->__property([$index,$newvalue]);
    }
    /**
     * @access private
     */
    function offsetUnset($index) {
        if( key_exists($index,$this->__data) )
            unset($this->__data[$index]);
    }

    /*
     * ****************************
     * JsonSerializable  json_encode 序列化接口
     */
    /**
     * @return array
     */
    function jsonSerialize()
    {
        return $this->toArray(true);
    }
}