<?php
require_once __DIR__ . '/DbDAO.php';
require_once __DIR__ . '/CrudInterface.php';

require_once __DIR__ . '/../J7Cache.php';

abstract class DbCrudDAO extends DbDAO implements CrudInterface
{
    /**
     * 当前 DAO 对应的默认表的主键，多关键字则使用数组。继承类可直接覆写此属性默认值达到设置主关键字的目的。
     * @var string|array
     */
    protected $_pk = null;

	/**
	 * $this->_datas_mode 采取与或模式，从左按位运算
	 * 第一位是DoingSql 操作数据日志记录,第二位是PendingSql 数据挂起到内存不操作数据库,第三位是DataLog 数据操作结果记录
	 */
    protected $_datas_mode = ''; //数据挂起模式 ，insert delete update操作需挂起
    protected $_dbindex = 0;

    protected $_table_ctid; //当前分表或分库的依据id,一般即uid
    protected $_table_ct; //同库内分表指定 原表_id 模式分，即 db_table_seg=>ct
    protected $_table_ctret; //用户分库、存值
    protected $_table_base; //基础表 原始对象，不含分表的id的基础表
    protected $_table_cttb; //分表暂存 _table_base . '_' . ii 模式
    protected $_table_ctids;  //分表分步式ids暂存

    public function __j7construct($sid=null)
    {
	    $sid = $sid?:$this->_dbindex;
	    //Constructor. mode模式{直接操作数据库，挂起数据模式}  table指定表  dbindex指定数据库配置  server默认缓存服务器
        parent::__j7construct($sid);
        $this->_table_base = $this->_table;
	    $this->_datas_mode = config('_j7_system_dao_mode');
    }

    //分库指定，此方法将切换好数据库及memcache缓存
    public function __SplitBy($sid = null)
    {
        $this->__changeDb($sid);
        return true;
    }

    public function __getPkName()
    {
        return $this->_pk;
    }

    public function __getClassInfo($t)
    {
        //内部使用...
        switch($t)
        {
            case 'abs':
                return $this->_abstable;
            case 'tb':
                return $this->_table;
        }
    }

    protected function changeDb($dbindex = 0)
    {
        return parent::__changeDb($dbindex);
    }

    public function __getDbIndex()
    {
        return parent::__getDbIndex();
    }


    protected function changeTable($table)
    {
        parent::_changeTable($table);
    }

    protected function _changeDatamode($datamode = false)
    {
        if ($datamode !== false)
            $this->_datas_mode = $datamode;
        else
            return $this->_datas_mode;
    }

    //======================================================================
    //  implements CrudDAO
    //======================================================================

    public function getThenResetKey($resetkey=null,$cond = null, $order = null, $count = null, $offset = null, $cols = null, $returnAr=null)
    {
        $ret = $this->get($cond,$order,$count,$offset,$cols,$returnAr);
        if( $resetkey && $ret )
        {
            $ret_ = [];
            foreach($ret as $er)
            {
                $ret_[$er[$resetkey]] = $er;
            }
            return $ret_;
        }
        return $ret;
    }

    //获取数据集，$cond是条件，array('id'=>1,'type'=>2);  $order是排序array(id=>desc,type=>asc), $count命中条数，$offset 偏移量, $cols列名
    public function get($cond = null, $order = null, $count = null, $offset = null, $cols = null, $returnAr=null)
    {
        $passtime = microtime(true);
        if (is_null($order) && isset($this->_pk) && is_string($this->_pk))
        {
            $order = array($this->_pk=>'desc');
        }
        $getitems = $this->_select($cond, $order, $count, $offset, $cols, $returnAr);

        if (($this->_datas_mode & '001') === '001') {
            $pinfo = array('sqlmode' => 'select');
            $pinfo['dbinfo'] = $this->_dbconindex === null ? $this->_dbconinfo : $this->_dbconindex;
            $pinfo['resultcounts'] = count($getitems);
            $pinfo['passtime'] = (microtime(true) - $passtime) * 1000;
            $pinfo['param'] = array($this->_table, $cond, $order, $count, $offset);
            RuntimeData::set('DataLog', $pinfo);
            $pinfo = null;
        }
        return $getitems;
    }

    public function getOne($cond = null, $order = null, $count = null, $offset = null, $cols = null, $returnAr=null)
    {
        $count = $count?:1;
        $data = $this->get($cond, $order, $count, $offset, $cols, $returnAr);
        return empty($data)?null:reset($data);
    }

    //获取count结果，$cond条件
    /**
     * @return int
     */
    public function getCount($cond = [])
    {
        $passtime = microtime(true);
        $getvalue = $this->_count($cond);
        if (($this->_datas_mode & '001') === '001') {
            $pinfo = array('sqlmode' => 'getCount');
            $pinfo['dbinfo'] = $this->_dbconindex === null ? $this->_dbconinfo : $this->_dbconindex;
            $pinfo['resultcounts'] = $getvalue;
            $pinfo['passtime'] = (microtime(true) - $passtime) * 1000;
            $pinfo['param'] = array($this->_table, $cond);
            RuntimeData::set('DataLog', $pinfo);
            $pinfo = null;
        }
        return $getvalue;
    }

    //通过主键获取单条数据
    public function getByPk($id, $pinfo = [])
    {
        $passtime = microtime(true);
        if (is_string($this->_pk)) {
            if (is_scalar($id))
                $item = $this->_getByFieldOne($this->_pk, $id);
            else
            {
                if( !isset($id[$this->_pk]) )
                    throw new coreException('null Pk @ getByPk');
                $item = $this->_getByFieldOne($this->_pk, $id[$this->_pk]);
            }
        } else {
            $pk = $this->_pk;
            $id = J7Data::rArray($id);
            if (is_array($id)) {
                if ( count($id)==count($pk) && isset($id[0]))
                    $cond = array_combine($pk, $id);
                else
                    $cond = array_intersect_key($id, array_flip($pk));
            } else
                $cond = array_combine($pk, array($id));

            $item = $this->_selectOne($cond);
        }
        if (($this->_datas_mode & '001') === '001') {
            $pinfo = array('sqlmode' => 'getByPk');
            $pinfo['dbinfo'] = $this->_dbconindex === null ? $this->_dbconinfo : $this->_dbconindex;
            $pinfo['result'] = $item;
            $pinfo['passtime'] = (microtime(true) - $passtime) * 1000;
            $pinfo['param'] = array($this->_table, $id, $this->_pk);
            RuntimeData::set('DataLog', $pinfo);
            $pinfo = null;
        }
        return $item;
    }

    public function getByFk($cond, $pinfo = [])
    {
        $passtime = microtime(true);
        $item = $this->getOne($cond); //_selectOne
        if (($this->_datas_mode & '001') === '001') {
            $pinfo = array('sqlmode' => 'getByFk');
            $pinfo['dbinfo'] = $this->_dbconindex === null ? $this->_dbconinfo : $this->_dbconindex;
            $pinfo['result'] = $item;
            $pinfo['passtime'] = (microtime(true) - $passtime) * 1000;
            $pinfo['param'] = array($this->_table, $cond);
            RuntimeData::set('DataLog', $pinfo);
            $pinfo = null;
        }
        return $item;
    }

    //直接添加数据
    /**
     * @return int
     */
    public function addinstant($datas)
    {
        $pinfo = array('sqlmode' => 'insert');
        $pinfo['dbinfo'] = $this->_dbconindex === null ? $this->_dbconinfo : $this->_dbconindex;
        $pinfo['param'] = array($this->_table, $datas);
        if (($this->_datas_mode & '01') === '01') {
            RuntimeData::set('PendingSql', $pinfo);
            return $pinfo;
        }
        if (($this->_datas_mode & '1') === '1') {
            RuntimeData::set('DoingSql', $pinfo);
        }
        $rtn = $this->_insert($datas);
        return $rtn;
    }

    //添加数据，并返回自增主键值
    /**
     * @return int
     */
    public function add($datas)
    {
        $datas = J7Data::rArray($datas);
        $result = $this->_insertGetLid($datas);
        if (($this->_datas_mode & '1') === '1') {
            $pinfo = array('sqlmode' => 'insert');
            $pinfo['dbinfo'] = $this->_dbconindex === null ? $this->_dbconinfo : $this->_dbconindex;
            $pinfo['param'] = array($this->_table, $datas);
            $pinfo['result'] = $result;
            RuntimeData::set('DoingSql', $pinfo);
        }
        return $result;
    }

    /**
     * 增量操作
     * @param $datas array 必须包含主键
     * @var bool $forceString 强制通过数据库CONCAT操作来运算
     * @param array $pinfo
     * @return int
     * @throws J7Exception
     */
    public function incrByPk($datas, $forceString = false, $pinfo = [])
    {
        $datas = J7Data::rArray($datas);
        if (!is_array($this->_pk))
            $cond = array($this->_pk => $datas[$this->_pk]);
        else
            $cond = array_intersect_key($datas, array_fill_keys($this->_pk, null));
        if (!$cond)
            throw new J7Exception('Cound Find _pk in ' . get_class($this) . '::incrByPk:datas!');
        if (!is_array($this->_pk))
            unset($datas[$this->_pk]);
        else
            $datas = array_diff_key($datas, $cond);
        $pinfo['sqlmode'] = 'incrByPk';
        $pinfo['dbinfo'] = $this->_dbconindex === null ? $this->_dbconinfo : $this->_dbconindex;
        $pinfo['param'] = array($this->_table, $datas, $this->_parseWhere($cond));
        if (($this->_datas_mode & '01') === '01') {
            RuntimeData::set('PendingSql', $pinfo);
            return $pinfo;
        }
        $result = $this->_updateincr($datas, $cond, $forceString);
        if (($this->_datas_mode & '1') === '1') {
            $pinfo['result'] = $result;
            RuntimeData::set('DoingSql', $pinfo);
        }
        return $result;
    }

    /**
     * @var array $datas ['needtoincr'=>2 ...]
     * @var bool $forceString 强制通过数据库CONCAT操作来运算
     * @return int
     */
    public function incr($datas, $cond = null, $forceString = false, $pinfo = [])
    {
        $datas = J7Data::rArray($datas);
        if (!isset($pinfo['sqlmode']))
            $pinfo['sqlmode'] = 'incr';
        $pinfo['dbinfo'] = $this->_dbconindex === null ? $this->_dbconinfo : $this->_dbconindex;
        $pinfo['param'] = array($this->_table, $datas, $this->_parseWhere($cond));
        if (($this->_datas_mode & '01') === '01') {
            RuntimeData::set('PendingSql', $pinfo);
            return $pinfo;
        }
        $result = $this->_updateincr($datas, $cond, $forceString);
        if (($this->_datas_mode & '1') === '1') {
            $pinfo['result'] = $result;
            RuntimeData::set('DoingSql', $pinfo);
        }
        return $result;
    }

    /**
     * 更新操作
     * @return int
     */
    public function update($datas, $cond = null, $pinfo = [])
    {
        $datas = J7Data::rArray($datas);
        if (!isset($pinfo['sqlmode']))
            $pinfo['sqlmode'] = 'update';
        $pinfo['dbinfo'] = $this->_dbconindex === null ? $this->_dbconinfo : $this->_dbconindex;
        $pinfo['param'] = array($this->_table, $datas, $this->_parseWhere($cond));
        if (($this->_datas_mode & '01') === '01') {
            RuntimeData::set('PendingSql', $pinfo);
            return $pinfo;
        }
        $result = $this->_update($datas, $cond);
        if (($this->_datas_mode & '1') === '1') {
            $pinfo['result'] = $result;
            RuntimeData::set('DoingSql', $pinfo);
        }
        return $result;
    }

    /**
     * 通过主键进行更新，主键由前端 DbDAO 中_pk指定
     * @param array $datas
     * @param array $pinfo
     * @return int
     * @throws J7Exception
     */
    public function updateByPk($datas, $pinfo = [])
    {
        $datas = J7Data::rArray($datas);
        if (!is_array($this->_pk))
            $cond = array($this->_pk => $datas[$this->_pk]);
        else
            $cond = array_intersect_key($datas, array_fill_keys($this->_pk, null));
        if (!$cond)
            throw new J7Exception('Cound Find _pk in ' . get_class($this) . '::updateByPk:datas!');
        if (!is_array($this->_pk))
            unset($datas[$this->_pk]);
        else
            $datas = array_diff_key($datas, $cond);

        $pinfo['sqlmode'] = 'updateByPk';
//        return $this->update($datas, $cond, $pinfo);

        $pinfo['dbinfo'] = $this->_dbconindex === null ? $this->_dbconinfo : $this->_dbconindex;
        $pinfo['param'] = array($this->_table, $datas, $this->_parseWhere($cond));
        if (($this->_datas_mode & '01') === '01') {
            RuntimeData::set('PendingSql', $pinfo);
            return $pinfo;
        }
        $result = $this->_update($datas, $cond);
        if (($this->_datas_mode & '1') === '1') {
            $pinfo['result'] = $result;
            RuntimeData::set('DoingSql', $pinfo);
        }
        return $result;
    }

    /**
     * @return int
     */
    public function delete($cond = [], $pinfo = [])
    {
        if (!isset($pinfo['sqlmode']))
            $pinfo['sqlmode'] = 'delete';
        $pinfo['dbinfo'] = $this->_dbconindex === null ? $this->_dbconinfo : $this->_dbconindex;
        $pinfo['param'] = array($this->_table, $this->_parseWhere($cond));
        if (($this->_datas_mode & '01') === '01') {
            RuntimeData::set('PendingSql', $pinfo);
            return $pinfo;
        }
        $result = $this->_delete($cond);
        if (($this->_datas_mode & '1') === '1') {
            $pinfo['result'] = $result;
            RuntimeData::set('DoingSql', $pinfo);
        }
        return $result;
    }

    //通过主键删除
    public function deleteByPk($ids, $pinfo = [])
    {
        $ids = J7Data::rArray($ids);
        if (empty($ids))
            return false;
        if (!is_array($this->_pk) && !is_array($ids))
            $cond = array($this->_pk => $ids);
        else {
            $pk = is_array($this->_pk) ? $this->_pk : array($this->_pk);
            if (is_array($ids)) {
                if (isset($ids[0]))
                    $cond = array_combine($pk, $ids);
                else
                    $cond = array_intersect_key($ids, array_fill_keys($pk, null)); // $ids;
            } else {
                $cond = array_combine($pk, array($ids));
            }
        }
        if (!$cond)
            throw new J7Exception('Cound Find _pk in ' . get_class($this) . '::deleteByPk:datas!');
        $pinfo['sqlmode'] = 'deleteByPk';
        $pinfo['dbinfo'] = $this->_dbconindex === null ? $this->_dbconinfo : $this->_dbconindex;
        $pinfo['param'] = array($this->_table, $this->_parseWhere($cond));
        if (($this->_datas_mode & '01') === '01') {
            RuntimeData::set('PendingSql', $pinfo);
            return $pinfo;
        }
        $result = $this->_delete($cond);
        if (($this->_datas_mode & '1') === '1') {
            $pinfo['result'] = $result;
            RuntimeData::set('DoingSql', $pinfo);
        }
        return $result;
    }

    public function deleteByFk($fk, $value)
    {
        return $this->delete(array($fk => $value));
    }


    protected function _getSelect($table, $cond = null, $order = null, $count = null, $offset = null, $cols = null)
    {
        $select = parent::_getSelect($table,$cond,$order,$count,$offset,$cols);
        if (($this->_datas_mode & '1') === '1') {
            RuntimeData::set('DoingSql:select', str_replace(PHP_EOL,' ',$select->__toString()).PHP_EOL.'@Class: '.get_called_class() );
        }
        return $select;
    }

    /**
     * @return PDOStatement
     */
    protected function query($sql,$bind = null)
    {
        $passtime = microtime(true);
        $ret = $this->_query($sql, $bind);
        if (($this->_datas_mode & '001') === '001') {
            $pinfo = array('sqlmode' => 'query');
            $pinfo['dbinfo'] = $this->_dbconindex === null ? $this->_dbconinfo : $this->_dbconindex;
            $pinfo['result'] = 'N/A';
            $pinfo['passtime'] = (microtime(true) - $passtime) * 1000;
            $pinfo['param'] = array($this->_table, $sql);
            RuntimeData::set('DataLog', $pinfo);
            $pinfo = null;
        }
        return $ret;
    }

    /**
     * @return int
     */
    protected function queryGetCount($sql, $bind = null)
    {
        $passtime = microtime(true);
        $stmp = $this->_query($sql, $bind);
        $ret = $stmp->rowCount();
        if (($this->_datas_mode & '001') === '001') {
            $pinfo = array('sqlmode' => 'queryGetCount');
            $pinfo['dbinfo'] = $this->_dbconindex === null ? $this->_dbconinfo : $this->_dbconindex;
            $pinfo['resultcounts'] = $ret;
            $pinfo['passtime'] = (microtime(true) - $passtime) * 1000;
            $pinfo['param'] = array($this->_table, $sql, $bind);
            RuntimeData::set('DataLog', $pinfo);
            $pinfo = null;
        }
        return $ret;
    }

    /**
     * @return array
     */
    protected function queryFetchAll($sql, $bind = null)
    {
        $passtime = microtime(true);
        $ret = $this->_fetchAll($sql, $bind);
        if (($this->_datas_mode & '001') === '001') {
            $pinfo = array('sqlmode' => 'queryFetchAll');
            $pinfo['dbinfo'] = $this->_dbconindex === null ? $this->_dbconinfo : $this->_dbconindex;
            $pinfo['result'] = 'N/A';
            $pinfo['passtime'] = (microtime(true) - $passtime) * 1000;
            $pinfo['param'] = array($this->_table, $sql, $bind);
            RuntimeData::set('DataLog', $pinfo);
            $pinfo = null;
        }
        return $ret;
    }



    //======================================================================
    //  implements CrudDAO  连贯数据操作
    //======================================================================

    /**
     * @var Db_Select
     */
    protected $_db_select = null;

    protected $_db_select_tmp = [];

    protected $_db_select_usMap=true;
    /**
     * @return $this
     */
    protected function selectCall($method,$argv)
    {
        if( !in_array($method,['fetchAll','find','findOne','count','from','forUpdate']) )
        {
            //累积操作数据以作适合缓存的检查判断
            if( $this->_db_select_usMap && in_array($method,['orHaving','having','group','joinInner','joinLeft','join','distinct','orWhere']) )
                $this->_db_select_usMap = false;
            if( !isset($this->_db_select_tmp[$method]) )
                $this->_db_select_tmp[$method] = [];
            if( $method=='where' && isset($argv[0]) && is_numeric($argv[0]) && $this->_pk )
                $argv[0] = [ $this->_pk => $argv[0] ];
            $this->_db_select_tmp[$method][] = $argv;
            return $this;
        }
        elseif( $method=='fetchAll' )
        {
            if ( $this->_db_select_usMap )  //常规查询
            {
                $cond_all = isset($this->_db_select_tmp['where'])?$this->_db_select_tmp['where']:[];
                if( $this->_db_select && count($cond_all)<=1 &&
                    $this->_db_select->get_parts('cols')==['count(*)'] )
                {
                    //检查转到 getCount
                    $this->_db_select_tmp = [];  //注销select状态
                    $this->_db_select = null; //注销select状态
                    $cond_new = $cond_all?$cond_all[0][0]:[];
                    return [['count(*)' => $this->getCount($cond_new)]]; //构造返回
                }
                elseif(  count($this->_db_select_tmp)==1 &&
                    $this->_pk && $cond_all &&
                    count($cond_all)==1 &&
                    count($cond_all[0])==1 &&
                    isset($cond_all[0][0][$this->_pk]) &&
                    is_scalar($cond_all[0][0][$this->_pk]) )
                {
                    //检查转到 getByPk
                    $this->_db_select_tmp = [];  //注销select状态
                    $this->_db_select = null; //注销select状态
                    return [ $this->getByPk($cond_all[0][0][$this->_pk]) ];
                }
                elseif( count($cond_all)<=1 )
                {
                    //检查
                    $tmp = $this->_db_select_tmp;
                    $order = $limit = null;
                    if ( isset($tmp['where']) )
                        unset($tmp['where']);
                    if ( isset($tmp['order']) ) {
                        $order = $tmp['order'];
                        unset($tmp['order']);
                    }
                    if ( isset($tmp['limit']) ) {
                        $limit = $tmp['limit'];
                        unset($tmp['limit']);
                    }
                    if( empty($tmp) )
                    {
                        //转到 get 处理  $cond  $order $count $offset ...

                        $this->_db_select_tmp = [];  //注销select状态
                        $this->_db_select = null; //注销select状态

                        $cond_new = $cond_all?$cond_all[0][0]:[];
                        $order_new = [];
                        if( $order ) {
                            foreach ($order as $eorder) {
                                if( isset($eorder[0]) )
                                    $order_new = array_merge($order_new,$eorder[0]);
                            }
                        }
                        $limit_new = [null,null];
                        if( $limit ) {
                            foreach ($limit as $elimit) {
                                if( isset($elimit[0]) )
                                    $limit_new[0] = $elimit[0];
                                if( isset($elimit[1]) )
                                    $limit_new[1] = $elimit[1];
                            }
                        }
                        return $this->get($cond_new,$order_new,$limit_new[0],$limit_new[1]);
                    }
                }
            }
            //其余的查询//组合后操作
            $tmp = $this->_db_select_tmp;
            $this->_db_select_tmp = [];  //注销select状态
            foreach ($tmp as $emethod=>$argv_s)
            {
                foreach ($argv_s as $eargv)
                    $this->doSelectCall($emethod,$eargv);
            }

        }
        return $this->doSelectCall($method,$argv);
    }

    protected function doSelectCall($method,$argv)
    {
        if( is_null($this->_db_select) ) {
            $this->_db_select = $this->_dbInstance->select();
            $this->_db_select->from($this->_table);
        }

        switch ($method)
        {
            case 'count':
                $this->_db_select->update_parts('cols',['count(*)']);
                $t = call_user_func_array([$this,'findOne'],$argv);
                if( $t instanceof J7Data )  //fix error when caching
                    return 1;
                return intval(array_values($t)[0]);
                break;
            case 'findOne':
                $ret = call_user_func_array([$this,'find'],$argv);
                if( $ret && isset($ret[0]) )
                    return $ret[0];
                else
                    return null;
                break;
            case 'find':
                if ( isset($argv[0]) )
                {
                    $this->selectCall('where',[$argv[0]]);
                    array_shift($argv);
                }
                return call_user_func_array([$this,'fetchAll'],$argv);
                break;
            case 'fetchAll':
                if (empty($argv) && $this->_db_select_usMap)
                    $argv = [$this->_getArMapName(),get_called_class()];

                $ret = call_user_func_array([$this->_db_select,'fetchAll'],$argv);
                slog(str_replace(PHP_EOL,' ',$this->_db_select->__toString()).PHP_EOL.'@Class: '.get_called_class(),'data','sql_select@fetchAll');
                $this->_db_select = null; //注销select状态
                return $ret;
                break;
            case 'where':
                if( isset($argv[0]) &&  !is_array($argv[0]) )
                    throw new coreException('must use array in condition!');
                if ( isset($argv[0]) && $where = $this->_parseWhere($argv[0],$this->_db_select))
                    $this->_db_select->where($where);
                break;
            case 'select':
                $this->_db_select->update_parts('cols',$argv[0]);
                break;
            default:
                call_user_func_array([$this->_db_select,$method],$argv);
                break;
        }
        return $this;
    }



    public function orWhere($cond)    {        return $this->selectCall('orWhere',func_get_args());    }


    //常规处理
    public function limit($count = null, $offset = null)    {        return $this->selectCall('limit',func_get_args());    }
    public function order($spec)    {        return $this->selectCall('order',func_get_args());    }
    public function where($cond)    {        return $this->selectCall('where',func_get_args());    }
    public function select($cond)   {        return $this->selectCall('select',func_get_args());    }

    //聚合处理 //需跳出某些业务逻辑
    public function orHaving($cond)    {        return $this->selectCall('orHaving',func_get_args());    }
    public function having($cond)    {        return $this->selectCall('having',func_get_args());    }
    public function group($spec)    {        return $this->selectCall('group',func_get_args());    }
    public function joinInner($name, $cond, $cols = null)    {        return $this->selectCall('joinInner',func_get_args());    }
    public function joinLeft($name, $cond, $cols = null)    {        return $this->selectCall('joinLeft',func_get_args());    }
    public function join($type, $name, $cond, $cols=null)    {        return $this->selectCall('join',func_get_args());    }
    public function distinct($flag = true)    {        return $this->selectCall('distinct',func_get_args());    }

    //外部处理
    public function from($name, $cols = '*')    {        return $this->selectCall('from',func_get_args());    }
    public function forUpdate($flag = true)    {        return $this->selectCall('forUpdate',func_get_args());    }

    //结果处理
    public function fetchAll($ar_map_name=null,$dao=null)    {        return $this->selectCall('fetchAll',func_get_args());    }
    public function find($cond=[],$ar_map_name=null,$dao=null)    {        return $this->selectCall('find',func_get_args());    }
    public function findOne($cond=[],$ar_map_name=null,$dao=null)    {        return $this->selectCall('findOne',func_get_args());    }
    public function count($cond=[])    {        return $this->selectCall('count',func_get_args());    }



}