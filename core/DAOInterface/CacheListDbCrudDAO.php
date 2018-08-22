<?php
require_once __DIR__ . '/CachePKDbCrudDAO.php';

/*
 * 此类做默认缓存模式使用。。。
 * 主要提供pk行缓存,通用的查询缓存
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

abstract class CacheListDbCrudDAO extends CachePKDbCrudDAO
{
    //get时候的cache，false:不缓存 array全自动 或者根据设置进行缓存
    //public $_getCache = array( array('uid') ,array('add_uid','gid'));
    public $_getCache = [];    //自动条件cache配置

    public $_ignoreResetWhenUpdateKeys = []; //不重要的keys，变动不引起关联缓存变化...

    public function __j7construct($sid = null)
    {
        parent::__j7construct($sid);
        if( $this->_getCache ){ array_walk($this->_getCache,function(&$v){sort($v);}); }
    }

    /*
     * *************************
     * 获得List主key
     */
    public function _getListMainKey($cond=null)
    {
        if( ($this->_getCache!==false) && ( is_null($cond) || ( $this->_getCache && !isset($cond[0]) && ($cond==array_filter($cond,function($v){return is_scalar($v);})) ) ) )
        {
            if( $cond )
            {
                ksort($cond);
                $condkeys = array_keys($cond);
                if( in_array( $condkeys , $this->_getCache ) )
                {
                    return 'J7L:'.$this->_cacheKeyPrefix.$this->_cachePrefixTable.'|'.implode(':',array_flip($cond) ).'|'.implode(':',$cond );
                }
            }
            else
                return 'J7L:'.$this->_cacheKeyPrefix.$this->_cachePrefixTable;
        }
        return false;
    }

    /*
     * 获取|设置|删除 List主缓存
     */
    protected function _ListMainCache($cond,$mixed='get')
    {
        $getCacheMailKey = $this->_getListMainKey($cond);
        if( $getCacheMailKey )
        {
            if( $mixed==='get' )
            {
                //var_dump($getCacheMailKey,empty($this->__cache->get($getCacheMailKey)));
                return $this->__cache->get($getCacheMailKey)?:[];
            }
            elseif( $mixed==='del' )
            {
//                var_dump($getCacheMailKey);
                //var_dump($getCacheMailKey,$this->__cache->del($getCacheMailKey));
                return $this->__cache->del($getCacheMailKey);
            }
            else
            {
                return $this->__cache->set($getCacheMailKey,$mixed);
            }
        }
        else
            return false;
    }

    /*
     * ****************
     * 数据有变动时候，删除关联缓存
     */
    protected function _ListCacheUpdate($data,$cachesets=null)
    {
        if( $data instanceof J7Data )
            $data = $data->toArray();
        if( is_scalar($data) && is_scalar($this->_pk) )
            $data = [$this->_pk=>$data];
        if( $this->_getCache!==false )
        {
            if( !$cachesets )
                $cachesets = $this->_getCache;

            $this->_ListMainCache(null,'del');
            if( $cachesets )
            {
                //历遍 $cachesets ， 取交集
                foreach($cachesets as $eachcachekey)
                {
                    if( !is_array($eachcachekey) )
                        throw new J7Exception('Not Array in '.get_class($this).'::_ListCacheUpdate:$cachesets!');
                    $conddata = array_intersect_key($data,array_flip($eachcachekey));
                    $this->_ListMainCache( $conddata ,'del');
                }
            }
            return true;
        }
        return false;
    }

    /**
     * 检查变动值是否在关联cache中,比对缓存
     */
    protected function _checkCacheUpdate($data=[],$cachesets=null)
    {
        if( $data instanceof J7Data )
            $data = $data->toArray();
        if( is_scalar($data) && is_scalar($this->_pk) )
            $data = [$this->_pk=>$data];
        if( $this->_getCache!==false )
        {
            if( !$cachesets )
                $cachesets = $this->_getCache;

            $needUpdateCaches = [];
            if( $cachesets )
            {
                //历遍 $cachesets ， 取交集
                foreach($cachesets as $eachcachekey)
                {
                    if( is_array($eachcachekey) && array_intersect_key($data,array_flip($eachcachekey)) )
                    {
                        $needUpdateCaches[] = $eachcachekey;
                    }
                }
            }
            return $needUpdateCaches;
        }
        return false;
    }

    /*
     * ***************************
     * 缓存get 与 getCount
     */

    //对应查询的重定义，以便查询结构数据能适用缓存需求
    protected function _reDirCol($cond)
    {
        return $cond;
    }

    public function get($cond = null, $order = null, $count = null, $offset = null, $cols = null, $returnAr=true)
    {
        //偏移时候不使用缓存
        $items = (!$offset)?$this->_ListMainCache($cond):false;
        if( $items!==false )
        {
            //根据order查询子缓存
            $orderStr = is_array($order)?(implode(':',array_keys($order)).'|'.implode(':',array_values($order))):$order;
            $getCacheSubKey = $orderStr.'|'.$cols;
            $needrebuild = false;
            if( !array_key_exists($getCacheSubKey,$items) )
                $needrebuild = true;
            elseif( !key_exists('items',$items[$getCacheSubKey]) || !key_exists('count',$items[$getCacheSubKey]) )
                $needrebuild = true;
            elseif ( ( is_null($count) || $items[$getCacheSubKey]['count']<$count ) && (!isset($items[$getCacheSubKey]['isAll']) || $items[$getCacheSubKey]['isAll']!=1) )
                $needrebuild = true;

            if( $needrebuild )
            {
                $getitems = parent::get($this->_reDirCol($cond), $order, $count, $offset, $cols, $returnAr);
                if( is_null($count) )
                    $items[$getCacheSubKey] = ['items'=>$getitems,'count'=>count($getitems),'isAll'=>1];
                else
                    $items[$getCacheSubKey] = ['items'=>$getitems,'count'=>$count];
                $this->_ListMainCache($cond,$items);
                return $getitems;
            }
            else
            {
                if( !is_null($count) && $items[$getCacheSubKey]['count']>$count )
                    return array_slice($items[$getCacheSubKey]['items'],0,$count);
                return $items[$getCacheSubKey]['items'];
            }
        }
        else
            return parent::get($this->_reDirCol($cond), $order, $count, $offset, $cols, $returnAr);
    }


    public function getCount($cond=null)
    {
        $items = $this->_ListMainCache($cond);
        if( $items!==false )
        {
            if( !array_key_exists('getCount',$items) )
            {
                $items['getCount'] = parent::getCount($this->_reDirCol($cond));
                $this->_ListMainCache($cond,$items);
            }
            return $items['getCount'];
        }
        return parent::getCount($this->_reDirCol($cond));
    }

    /*
     * ***************************
     * 重构基本影响数据的操作
     */
    public function add($datas)
    {
        $r = parent::add($datas);
        if( is_string($this->_pk) && is_scalar($r) && $r )
        {
            $datas = $this->getByPk($r);
        }
        $this->removeCache($datas,false);
        return $r;
    }

    public function addinstant($datas)
    {
        $this->removeCache($datas,false);
        return parent::addinstant($datas);
    }

    public function updateByPk($datas, $pinfo = [])
    {
        $olddata = self::getByPk($datas);
        $olddata = J7Data::rArray($olddata);
        $this->removeCache($olddata,false,$datas);
        $newdata = array_merge($olddata,J7Data::rArray($datas) );
        $this->removeCache($newdata,false,$datas);
        return parent::updateByPk($datas,$pinfo);
    }

    public function incrByPk($datas, $forceString=false,$pinfo = [])
    {
        $olddata = self::getByPk($datas);
        $this->removeCache($olddata,false,$datas);
        $newdata = $olddata;
        foreach($datas as $k=>$v)
        {
            if( (is_string($this->_pk) && $k==$this->_pk)|| (is_array($this->_pk)&&in_array($k,$this->_pk)) )
            {

            }
            elseif( isset($newdata[$k]) )
            {
                if( $forceString )
                {
                    if($forceString==='pre')
                        $newdata[$k] = $v.$newdata[$k];
                    else
                        $newdata[$k] = $newdata[$k].$v;
                }
                else
                    $newdata[$k] = $newdata[$k]+$v;
            }
        }
        $this->removeCache($newdata,false,$datas);
        return parent::incrByPk($datas,$forceString,$pinfo);
    }

    public function delete($cond = [], $pinfo = [])
    {
        //判断PK
        if( $this->_getMainkey($cond,false) )
        {
            return $this->deleteByPk($cond);
        }

        $olddatas = $this->get($cond,null,null,null,null,false);
        if( $olddatas && ( $isDelete = parent::delete($cond, $pinfo) ) )
        {
            foreach ($olddatas as $olddata)
            {
                $this->removeCache(J7Data::rArray($olddata),false);
            }
        }
        return isset($isDelete)?$isDelete:0;
    }

    public function update($datas, $cond = null, $pinfo = [])
    {
        //判断PK
        if( $this->_getMainkey($cond,false) )
            parent::removeCache($cond);
        if( $this->_getMainkey($datas,false) )
            parent::removeCache($datas);

        $olddatas = $this->get($cond,null,null,null,null,false);
        if( $olddatas && ($isUpdate = parent::update($datas, $cond, $pinfo)) )
        {
            foreach ($olddatas as $olddata)
            {
                $this->removeCache($olddata,false,$datas);
                $this->removeCache(array_merge(J7Data::rArray($olddata),$datas),false,$datas);
            }
        }
        return isset($isUpdate)?$isUpdate:0;
    }

    public function deleteByPk($pk,$pinfo = [])
    {
        $olddata = self::getByPk($pk);
        $this->removeCache($olddata,false);
        return parent::deleteByPk($pk,$pinfo);
    }

    //$checkArr 是变动比对缓存
    public function removeCache($datas, $throw = true,$checkArr=[])
    {
        if(empty($datas))
            return false;
        if( $checkArr )
        {
            if( is_string($this->_pk) )
            {
                if( isset($checkArr[$this->_pk]) )
                    unset($checkArr[$this->_pk]);
            }
            elseif( is_array($this->_pk) )
            {
                foreach ($this->_pk as $epk)
                {
                    if( isset($checkArr[$epk]) )
                        unset($checkArr[$epk]);
                }
            }
        }


        //不重要的key可以过滤过滤...
        if( empty($checkArr) || empty($this->_ignoreResetWhenUpdateKeys) )
        {
            $this->_ListCacheUpdate($datas);
        }
        else
        {
            //比对是否全忽略字段
            $c = array_intersect(array_keys($checkArr), $this->_ignoreResetWhenUpdateKeys);
            $checkRules = [];
            if( count($c)!=count($checkArr) )
                $checkRules = $this->_checkCacheUpdate($datas);

            //todo 这里应该设计全关注字段!!!
            if( empty($checkRules))
                $this->_ListMainCache(null,'del');
            else
                $this->_ListCacheUpdate($datas,$checkRules);
        }
//        $this->_ListCacheUpdate($datas);
        parent::removeCache(J7Data::rArray($datas), $throw);
    }

    /*
     * 其它操作是不好影响缓存的，要根据情况手动处理
     */

}