<?php
require_once __DIR__ . '/../Util.php';
require_once __DIR__ . '/CrudInterface.php';
require_once __DIR__ . "/../J7Config.php";
require_once __DIR__ . "/../J7Debuger.php";
require_once __DIR__ . '/../RedisAdapter/coreRedisData.php';

/*
 * 此基类功能：
 * 把redis当成简单的关系型数据库来操作，提供基本方法，实现快速数据库
 * 注意本方法数据不过期，
 * 单结构数据，类似 CacheRedisDbCrudDAO
 */

abstract class RedisSimpleDAO implements CrudInterface
{
    protected $_db = null;
    protected $_table = null;
    protected $_table_map = null;
    protected $_abstable = null;
    protected $_dbconinfo = [];
    protected $_dbconindex = null; //连接信息

    protected $_cacheKeyPrefix = '';
    protected $_pk = null;
    protected $_pk_store = null; //缓存存储主键//一般是uid//存储键按ksort排序
    protected $_pk_dbset = null; //uid即跟分库主键

    public $_tStructure_dValue;

    public function __construct(){}
    public function __j7construct($dbindex=0,$redisdb=null)
    {
        $this->_abstable = substr(get_called_class(),-8,8)=='RedisDAO'?substr(get_called_class(),0,-8):get_called_class();
        if (empty($this->_table))
        {
            $this->_table = end(explode('\\',$this->_abstable));
        }
        $this->__getAllSets();  //重新整理数据的需要//更改表设置后，需要执行这个的。。
        $this->changeDb($dbindex,$redisdb);
    }


    protected function __getAllSets() //重新整理数据
    {
        $db_prefix_map = config('rd_table_map');
        $this->_table_map = isset($db_prefix_map[$this->_table]) ? $db_prefix_map[$this->_table] : $this->_table;
        if( empty($this->_tStructure_dValue) )
        {
            throw new J7Exception('_tStructure_dValue cannt be empty ! from:'.get_class($this));
        }
        if (!is_string($this->_pk_dbset))
            throw new J7Exception("__getAllSets Error!");
        if (is_array($this->_pk_store))
            ksort($this->_pk_store);
        if (is_array($this->_pk))
            ksort($this->_pk);
    }

    //外部直接更改DB连接
    protected function changeDb($dbindex=0,$redisdb=null)
    {
        $_dbconinfo = [];
        $_dbconinfo['database'] = $redisdb;
        if( $this->_dbconindex!==$dbindex && $_dbconinfo!==$this->_dbconinfo )
        {
            $this->_dbconinfo = $_dbconinfo;
            $this->_dbconindex = $dbindex;
            $this->_db = coreRedisData::instance(null,null,$dbindex,$redisdb);
        }
    }

    protected function __getDbIndex()
    {
        return $this->_dbconindex;
    }
    protected function changeTable($table)
    {
        $this->_table = $table;
        $db_prefix_map = config('rd_table_map');
        $this->_table_map = isset($db_prefix_map[$this->_table])?$db_prefix_map[$this->_table]:$this->_table;
    }

    public function __getPkName()
    {
        return $this->_pk;
    }
    /**************************************************************
     * 结构化处理数据,业务层方法
     **************************************************************/

    /**
     * 添加数据，并返回主键值
     * @param  $datas
     * @return array|bool
     */
    public function add($datas)
    {
        if (is_array($this->_pk)) {
            $ret = [];
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
        $datas =  array_merge($this->_tStructure_dValue , $datas);
        $this->__SplitBy($dbsid);
        $mcachekey = $this->__getMainKey($datas);
        $pk = $this->__getSubKey($datas);
        return $this->_db->hSetNx($mcachekey, $pk, $datas);
    }

    /**
     * @param  $datas
     * @return array|int
     *
     * 通过主键进行更新单条记录
     */
    public function updateByPk($datas, $pinfo=[])
    {
        $dbsid = $this->getPks($datas, $this->_pk_dbset);
        $this->__SplitBy($dbsid);
        $mcachekey = $this->__getMainKey($datas);
        $pk = $this->__getSubKey($datas);
        $olddata = $this->getByPk($datas);
        if ($olddata === false) {
            $olddata = [];
        }
        $datas = array_merge($olddata, $datas);
        return $this->_db->hSet($mcachekey, $pk, $datas);
    }

    /**
     * @param  $datas
     * @return array|bool|int
     *
     * 通过存储键，删除多条记录
     */
    public function deleteBySk($datas)
    {
        if ($this->_pk == $this->_pk_store)
            return $this->deleteByPk($datas);
        else
        {
            $dbsid = $this->getPks($datas, $this->_pk_dbset);
            $this->__SplitBy($dbsid);
            $mcachekey = $this->__getMainKey($datas);
            return $this->_db->del($mcachekey);
        }
    }

    /**
     * @param  $dbsinfo
     * @param null $pkcond
     * @return array|bool|int
     *
     * 通过主键删除单条记录
     */
    public function deleteByPk($datas, $pinfo = [])
    {
        $dbsid = $this->getPks($datas, $this->_pk_dbset);
        $this->__SplitBy($dbsid);
        $pkcond = $this->getPks($datas, $this->_pk, 'cond');
        $mcachekey = $this->__getMainKey($datas);
        $pk = $this->__getSubKey($pkcond);
        return $this->_db->hDel($mcachekey, $pk);
    }

    /**
     * @param  $dbsinfo
     * @param null $pk
     * @param array $pinfo
     * @return bool
     *
     * 获得主键单条数据
     */
    public function getByPk($datas, $pinfo = [])
    {
        $dbsid = $this->getPks($datas, $this->_pk_dbset);
        $mcachekey = $this->__getMainKey($datas);
        $this->__SplitBy($dbsid);
        $pk = $this->__getSubKey($datas);
        $ret = $this->_db->hGet($mcachekey, $pk);
        if ($ret === false) {
            return [];
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
            if (!$ret = $this->_db->hGetAll($mcachekey)) {
                return [];
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
    public function get($cond)
    {
        if (!$allstoreset = $this->getBySk($cond)) {
            return [];
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
    public function getCount($cond=[])
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
            if ($this->_db->hExists($mcachekey, $pk))
                return 1;
            else
                return 0;
        }
        else
        {
            $ret = $this->_db->hLen($mcachekey);
            return $ret;
        }
    }

    /**
     * @param  $dbsinfo
     * @return
     *
     * 查询数据库，建立缓存
     */
    public function __getMainKey($datas, $type = null) //默认作为hash的存储
    {
        $cond = $this->getPks($datas, $this->_pk_store);
        if (is_array($cond)) {
            ksort($cond);
            $kstr = 'J7T:'.$this->_cacheKeyPrefix. $this->_table_map . ':H:' . implode(':', $cond);
        }
        else
            $kstr = 'J7T:'.$this->_cacheKeyPrefix. $this->_table_map . ':H:' . $cond;
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

    public function getPks($data, $pkset, $astp = 'strkey')
    {
        if (!is_array($data)) {
            if (!is_array($pkset))
            {
                $ret = array($pkset => $data);
            }
            else
            {
                if( count($pkset)==1 )
                    $ret = array($pkset[0] => $data);
                else
                    throw new J7Exception("getPks Error!!!");
            }
        }
        else
        {
            if (!is_array($pkset)) {
                $ret = array($pkset => $data[$pkset]);
            }
            else
            {
                ksort($data);
                $ret = array_intersect_key($data, array_fill_keys($pkset, null));
                if (count($ret) != count($pkset))
                    throw new J7Exception('Could not find pkset:' . $pkset . ' in $data:' . var_export($data, true));
            }
        }
        if ($astp == 'strkey')
            return implode(':', $ret);
        else
            return $ret;
    }


    public function update($datas, $cond = null,$pinfo=[])
    {
        $alldatas = $this->get($cond);
        if( $alldatas )
        {
            $mcachekey = $this->__getMainKey($datas);
            $arrnew = [];
            foreach($alldatas as $edata)
            {
                $pk = $this->__getSubKey($edata);
                $arrnew[$pk] = array_merge($edata,$datas);
            }
            $this->_Redis->hMset($mcachekey,$arrnew);
            return count($arrnew);
        }
        else
            return 0;
    }
    public function delete($cond = [],$pinfo=[])
    {
        $alldatas = $this->get($cond);
        if( $alldatas )
        {
            $mcachekey = $this->__getMainKey($cond);
            foreach($alldatas as $edata)
            {
                $pk = $this->__getSubKey($edata);
                $this->_Redis->hDel($mcachekey, $pk);
            }
            return count($alldatas);
        }
        else
            return 0;
    }

}