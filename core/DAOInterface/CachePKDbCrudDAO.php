<?php
require_once __DIR__ . '/DbCrudDAO.php';

/*
 * 此类做默认缓存模式使用。。。
 * 主要提供pk行缓存
 * 主要方法有:
 * add($datas)
 * addinstant($datas)
 * updateByPk($dbsinfo, $datas)
 * deleteBySk($dbsinfo)
 * deleteByPk($dbsinfo, $pkcond = null)
 * getByPk($dbsinfo, $pk = null, $pinfo = [])
 * getBySk($dbsinfo)
 * get($cond)
 * getCount($cond)
 * getCountBySk($dbsinfo)
 */

abstract class CachePKDbCrudDAO extends DbCrudDAO
{
    protected $_cacheKeyPrefix = '';
    /**
     * @var J7Cache
     */
    protected $__cache = null;
    protected $_pkCache=true;   //是否使用主键cache
    protected $_cachePrefixTable = null;    //cache key 缩减

    public function __j7construct($sid = null)
    {
        parent::__j7construct($sid);
        $this->__cache = J7Cache::instance();
        $this->_cachePrefixTable = $this->_abstable;
        $arVersionConfig = config('ar_version','db/ar_version.php');
        if( isset($arVersionConfig[$this->_data_map]) )
            $this->_cacheKeyPrefix = $arVersionConfig[$this->_data_map];
        elseif( isset($arVersionConfig['_j7c']) )
            $this->_cacheKeyPrefix = $arVersionConfig['_j7c'];
        $zi = strpos($this->_abstable,'j7f');
        if( $zi===0 || $zi===1 )
        {
            $this->_cachePrefixTable = substr( $this->_abstable,$zi+4 );
        }
    }

    protected function _getMainkey($pk,$throw=true)
    {
        if( is_string($this->_pk) )
        {
            if( (is_array($pk)||$pk instanceof J7Data) && isset($pk[$this->_pk]) )
                return 'J7T:'.$this->_cacheKeyPrefix.$this->_cachePrefixTable.'|'.$pk[$this->_pk];
            elseif( is_scalar($pk) )
                return 'J7T:'.$this->_cacheKeyPrefix.$this->_cachePrefixTable.'|'.$pk;
            elseif($throw)
                throw new J7Exception('_getMainkey Error'.var_export($pk,true) );
        }
        else
        {
            //会造成更新时候 ['xxx=xxx+1',...] 缓存出错
//            if(isset($pk[0]))
//                return 'J7T:'.$this->_cacheKeyPrefix.$this->_cachePrefixTable.'|'.implode('|',$pk);
//            else
//            {
                ksort($pk);
                $zpk = array_intersect_key($pk, array_fill_keys($this->_pk, null)); //array_merge(array_fill_keys($this->_pk,null),$pk);
                if( (count($zpk) != count($this->_pk)) )
                {
                    if( $throw )
                        throw new J7Exception('_getMainkey array_intersect_key Error'.var_export($pk,true) );
                }
                else
                    return 'J7T:'.$this->_cacheKeyPrefix.$this->_cachePrefixTable.'|'.implode('|',$zpk);
//            }
        }
        return false;
    }

    public function removeCache($datas,$throw=true)
    {
        $this->_removeCachePk($datas,$throw);
    }

    public function _removeCachePk($datas,$throw=true)
    {
        if( $this->_pkCache )
        {
            $mkey = $this->_getMainkey($datas,$throw);
            if($mkey)
                $this->__cache->del($mkey);
        }
    }

    /*
     * 通过MemCache缓存数据的...
     */
    public function add($datas)
    {
        if( is_string($this->_pk) )
        {
            if( isset($datas[$this->_pk]) )
            {
                $pk = $datas[$this->_pk];
                parent::addinstant($datas);
            }
            else
            {
                $pk = parent::add($datas);
                $datas[$this->_pk] = $pk;
            }
        }
        else
            $pk = parent::add($datas);

        $this->_removeCachePk($datas,true);

        return $pk;
    }

    public function addinstant($datas)
    {
        if( is_string($this->_pk) && isset($datas[$this->_pk]) )
        {
            $rtn = parent::addinstant($datas);
            $this->_removeCachePk($datas,true);
            return $rtn;
        }
        return parent::addinstant($datas);
    }

    public function getByPk($pk,$pinfo = [])
    {
        if( $this->_pkCache )
        {
            $mkey = $this->_getMainkey($pk);
            $item = $this->__cache->get($mkey);
            if( $item === false  )
            {
                $item = parent::getByPk($pk);
                $this->__cache->set($mkey,$item);
            }
            return $item;
        }
        else
            return parent::getByPk($pk);
    }

    public function updateByPk($datas, $pinfo = [])
    {
        if( ( $ret = parent::updateByPk($datas, $pinfo) ) )
        {
            $this->_removeCachePk($datas,true);
        }
        return $ret;
    }

    public function update($datas, $cond = null, $pinfo = [])
    {
        $this->_removeCachePk($cond,false);
        $allc = array_merge($cond,$datas);
        $this->_removeCachePk($allc,false);
        return parent::update($datas, $cond, $pinfo);
    }

    public function incrByPk($datas, $forceString=false,$pinfo=[])
    {
        if( ( $ret = parent::incrByPk($datas,$forceString, $pinfo) ) )
        {
            $this->_removeCachePk($datas,true);
        }
        return $ret;
    }

    public function incr($datas, $cond = null, $forceString = false, $pinfo = [])
    {
        $this->_removeCachePk($cond,false);
        $allc = array_merge($cond,$datas);
        $this->_removeCachePk($allc,false);
        return parent::incr($datas, $cond, $forceString, $pinfo);
    }

    public function deleteByPk($pk,$pinfo = [])
    {
        $this->_removeCachePk($pk,true);
        if( ($ret = parent::deleteByPk($pk, $pinfo)) )
        {
        }
        return $ret;
    }

    public function delete($cond = [], $pinfo = [])
    {
        $this->_removeCachePk($cond,false);
        return parent::delete($cond, $pinfo);
    }


    //仅更新缓存内容
    protected function updateCache($datas)
    {
        $mkey = $this->_getMainkey($datas);
        $item = self::getByPk($datas);
        $item = array_merge($item,$datas);
        $this->__cache->set($mkey,$item);
        return $item;
    }

    //======================================================================
    //  implements CrudDAO  连贯数据操作
    //======================================================================
    protected function selectCall($method,$argv)
    {
        return parent::selectCall($method,$argv);
    }
}