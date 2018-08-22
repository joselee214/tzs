<?php
require_once __DIR__ . '/DbCrudDAO.php';
require_once __DIR__ . '/../RedisAdapter/coreRedisData.php';

/*
 * 此类做默认缓存使用。。。
 * 每次传入的 $dbsinfo 必须含有 _pk_dbset 跟 _pk_store 信息。。。注意使用来着。。。
 *
 * 主要方法有:
 * add($datas)
 * addinstant($datas)
 * updateByPk($dbsinfo, $datas)
 * deleteBySk($dbsinfo)
 * deleteByPk($dbsinfo, $pkcond = null)
 * getByPk($dbsinfo, $pk = null, $pinfo = array())
 * getBySk($dbsinfo)
 * get($cond)
 * getCount($cond)
 * getCountBySk($dbsinfo)
 */

abstract class CacheRedisDbCrudDAO extends DbCrudDAO
{
    protected $_pk = null; // string | array | 能定义到单条数据的主键
    protected $_pk_store = null; //缓存存储主键 //一般是uid //存储键按ksort排序
    protected $_pk_dbset = null; //分库键名string ,可以与pk不同,由这个来分库认定
    //$dbsinfo必须含有 $_pk_dbset 与 $_pk_store 的数据,否则 throw new J7Exception

    protected $_table_map = null;
    protected $_Redis = null;

    public function __j7construct($sid = null)
    {
        parent::__j7construct($sid);
        $this->_Redis = coreRedisData::instance(null,null,$sid);
        $this->__getAllSets();
    }

    public function __SplitBy($dbsid=null)
    {
        return true;
    }

    /**
     * 添加数据，并返回主键值
     * @param  $datas
     * @return array|bool
     */
    public function add($datas)
    {
        if (is_array($this->_pk)) {
            $ret = array();
            foreach ($this->_pk as $eachpkkey)
            {
                if (!isset($datas[$eachpkkey])) {
                    $datas[$eachpkkey] = Util::getIncrId($this->_table_map);
                }
                $ret[$eachpkkey] = $datas[$eachpkkey];
            }
        }
        else
        {
            if (!isset($datas[$this->_pk])) {
                $datas[$this->_pk] = Util::getIncrId($this->_table_map);
            }
            $ret = $datas[$this->_pk];
        }
        if ($this->addinstant($datas)) {
            return $ret;
        }
        else
            return false;
    }

    /**
     * @param  $datas
     * @return array
     *
     * 添加数据，直接返回
     */
    public function addinstant($datas)
    {
        $dbsid = $this->getPks($datas, $this->_pk_dbset);
        $this->__SplitBy($dbsid);
        if ($ret = parent::addinstant($datas)) {
            $mcachekey = $this->__getMainKey($datas);
            $pk = $this->__getSubKey($datas);
            return $this->_Redis->hSetNx($mcachekey, $pk, $datas);
        }
        return $ret;
    }

    /**
     * @param  $dbsinfo
     * @param  $datas
     * @return array|int
     *
     * 通过主键进行更新单条记录
     */
    public function updateByPk($datas, $pinfo=null)
    {
        $dbsid = $this->getPks($datas, $this->_pk_dbset);
        $this->__SplitBy($dbsid);
        if ($ret = parent::updateByPk($datas)) {
            $mcachekey = $this->__getMainKey($datas);
            $pk = $this->__getSubKey($datas);
            $olddata = $this->getByPk($datas, array('justfromcache' => true));
            if ($olddata === false) {
                $olddata = array();
            }
            $datas = $this->mergetoObject($olddata,$datas);
            $this->_Redis->hSet($mcachekey, $pk, $datas);
            return $ret;
        }
        return 0;
    }

    /**
     * @param  $dbsinfo
     * @return array|bool|int
     *
     * 通过存储键，删除多条记录
     */
    public function deleteBySk($dbsinfo)
    {
        if ($this->_pk == $this->_pk_store)
            return $this->deleteByPk($dbsinfo);
        else
        {
            $dbsid = $this->getPks($dbsinfo, $this->_pk_dbset);
            $this->__SplitBy($dbsid);
            $scond = $this->getPks($dbsinfo, $this->_pk_store, 'cond');
            $ret = parent::delete($scond);
            $mcachekey = $this->__getMainKey($dbsinfo);
            $this->_Redis->del($mcachekey);
            return $ret;
        }
    }

    /**
     * @param  $dbsinfo
     * @param null $pkcond
     * @return array|bool|int
     *
     * 通过主键删除单条记录
     */
    public function deleteByPk($datas, $pinfo = null)
    {
        $dbsid = $this->getPks($datas, $this->_pk_dbset);
        $this->__SplitBy($dbsid);
        $pkcond = $this->getPks($datas, $this->_pk, 'cond');

        if ($ret = parent::deleteByPk($datas)) {
            $mcachekey = $this->__getMainKey($datas);
            $pk = $this->__getSubKey($pkcond);
            $this->_Redis->hDel($mcachekey, $pk);
            return $ret;
        }
        return 0;
    }

    /**
     * @param  $dbsinfo
     * @param null $pk
     * @param array $pinfo
     * @return bool
     *
     * 获得主键单条数据
     */
    public function getByPk($dbsinfo , $pinfo = array())
    {
        $dbsid = $this->getPks($dbsinfo, $this->_pk_dbset);
        $mcachekey = $this->__getMainKey($dbsinfo);
        $this->__SplitBy($dbsid);
        $pk = $this->__getSubKey($dbsinfo);
        $ret = $this->_Redis->hGet($mcachekey, $pk);
        if ( $ret === false) {
            if (isset($pinfo['justfromcache']) && $pinfo['justfromcache']) {
                return false;
            }
            $this->getDbCondByStore($dbsinfo);
            return $this->_Redis->hGet($mcachekey, $pk);
        }
        return $ret;
    }

    /**
     * @param  $dbsinfo
     * @return array
     *
     * 通过存储键获得多条数据
     */
    public function getBySk($dbsinfo)
    {
        if ($this->_pk == $this->_pk_store)
            return array($this->getByPk($dbsinfo));
        else
        {
            $dbsid = $this->getPks($dbsinfo, $this->_pk_dbset);
            $this->__SplitBy($dbsid);
            $mcachekey = $this->__getMainKey($dbsinfo);
            if (!$ret = $this->_Redis->hGetAll($mcachekey)) {
                $ret = $this->getDbCondByStore($dbsinfo);
            }
            return $ret;
        }
    }

    /**
     * @param  $cond
     * @return array
     *
     * 通过存储键，获得数据并过滤。。
     */
    public function get($cond =null, $order = null, $count = null, $offset = null, $cols = null)
    {
        if (!$allstoreset = $this->getBySk($cond)) {
            return array();
        }
        if (is_array($this->_pk_store)) {
            foreach ($this->_pk_store as $epks)
            {
                unset($cond[$epks]);
            }
        }
        else
        {
            unset($cond[$this->_pk_store]);
        }
        if (isset($cond[$this->_pk_dbset]))
            unset($cond[$this->_pk_dbset]);
        if (!$cond) {
            return $allstoreset;
        }
        else
        {
            return array_filter($allstoreset, function($val)use($cond)
                {
                    if ($val && array_intersect_assoc($cond, $val) == $cond) {
                        return true;
                    } else {
                        return false;
                    }
                });
        }
    }

    /**
     * @param  $cond
     * @return int
     *
     * 通过存储键，并过滤，获得记录数
     */
    public function getCount($cond = array())
    {
        return count($this->get($cond));
    }

    /**
     * @param  $dbsinfo
     * @return int
     *
     * 通过存储键获得记录数
     */
    public function getCountBySk($dbsinfo)
    {
        $dbsid = $this->getPks($dbsinfo, $this->_pk_dbset);
        $this->__SplitBy($dbsid);
        $mcachekey = $this->__getMainKey($dbsinfo);
        if ($this->_pk == $this->_pk_store) {
            $pk = $this->__getSubKey($dbsinfo);
            if ($this->_Redis->hExists($mcachekey, $pk))
                return 1;
            else
                return 0;
        }
        else
        {
            $ret = $this->_Redis->hLen($mcachekey);
            if ($ret === false) {
                $retdata = $this->getDbCondByStore($dbsinfo);
                $ret = count($retdata);
            }
            return $ret;
        }
    }

    /**
     * @param  $dbsinfo
     * @return
     *
     * 查询数据库，建立缓存
     */
    public function getDbCondByStore($dbsinfo)
    {
        $mcachekey = $this->__getMainKey($dbsinfo);
        $cond = $this->getPks($dbsinfo, $this->_pk_store, 'cond');
        $ret = parent::get($cond);
        if ($ret) {
            $sret = array();
            foreach ($ret as $ev)
            {
                $sk = $this->__getSubKey($ev);
                $sret[$sk] = $ev;
            }
            $this->_Redis->hMset($mcachekey, $sret);
        } else {
			$this->_Redis->delete($mcachekey);
			$sret = array();
		}
        return $sret;
    }

    public function __getMainKey($datas, $type = null) //默认作为hash的存储
    {
        $cond = $this->getPks($datas, $this->_pk_store);
        if (is_array($cond)) {
            ksort($cond);
            $kstr = 'J7T:' . $this->_table_map . ':H:' . implode(':', $cond);
        }
        else
            $kstr = 'J7T:' . $this->_table_map . ':H:' . $cond;
        if ($this->_pk == $this->_pk_store)
            return substr($kstr, 0, -3);
        else
            return $kstr;
    }

    public function __getSubKey($cond) //获取子健
    {
        if ($this->_pk == $this->_pk_store)
            return substr($this->getPks($cond, $this->_pk), -3);
        else
            return $this->getPks($cond, $this->_pk);
    }

    public function getPkCond($data,$pkset=null)
    {
        if( is_null($pkset) )
            $pkset = $this->_pk;
        return $this->getPks($data, $pkset, $astp = 'cond');
    }

    public function mergetoObject($a1,$a2)
    {
        if( $a1 instanceof J7Data )
        {
            return $a1->fromArray($a2);
        }
        else
            return array_merge($a1,$a2);
    }

    /**
     * 获取存储键值
     */
    public function getPks($data, $pkset, $astp = 'strkey')
    {
        if( is_array($data) || $data instanceof J7Data )
        {
            if (!is_array($pkset)) {
                $ret = array($pkset => $data[$pkset]);
            }
            else
            {
                ksort($data);
                $ret = array_intersect_key($data, array_fill_keys($pkset, null));
                if (count($ret) != count($pkset))
                    throw new J7Exception('Could not find '.get_class($this).'::pkset:' . $pkset . ' in $data:' . var_export($data, true));
            }
        }
        else
        {
            if (!is_array($pkset))
            {
                $ret = array($pkset => $data);
            }
            else
            {
                if( count($pkset)==1 )
                    $ret = array($pkset[0] => $data);
                else
                    throw new J7Exception( get_class($this).'::getPks Error!!!');
            }
        }
        if ($astp == 'strkey')
        {
            return implode(':', $ret);
        }
        else
            return $ret;
    }

    protected function __getAllSets() //重新整理数据
    {
        $db_prefix_map = config('rd_table_map');
        $this->_table_map = isset($db_prefix_map[$this->_table]) ? $db_prefix_map[$this->_table] : $this->_table;
        if (!is_string($this->_pk_dbset))
            throw new J7Exception( get_class($this).'::__getAllSets Error ! not set _pk_dbset' );
        if (is_array($this->_pk_store))
            ksort($this->_pk_store);
        if (is_array($this->_pk))
            ksort($this->_pk);
    }

    public function update($datas, $cond = null,$pinfo=array())
    {
        $alldatas = $this->get($cond);
        if( $alldatas && $ret=parent::update($datas,$cond,$pinfo) )
        {
            $mcachekey = $this->__getMainKey($cond);
            $arrnew = array();
            foreach($alldatas as $edata)
            {
                $pk = $this->__getSubKey($edata);
                $arrnew[$pk] = $this->mergetoObject($edata,$datas);
            }
            $this->_Redis->hMset($mcachekey,$arrnew);
            return $ret;
        }
        else
            return 0;
    }
    public function delete($cond = array(),$pinfo=array())
    {
        $alldatas = $this->get($cond);
        if( $alldatas && $ret=parent::delete($cond,$pinfo) )
        {
            $mcachekey = $this->__getMainKey($cond);
            foreach($alldatas as $edata)
            {
                $pk = $this->__getSubKey($edata);
                $this->_Redis->hDel($mcachekey, $pk);
            }
            return $ret;
        }
        else
            return 0;
    }
   

}