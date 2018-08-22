<?php
require_once __DIR__ . '/../RuntimeData.php';
require_once __DIR__ . '/RedisDAO.php';
require_once __DIR__ . '/CrudInterface.php';


/*
 * 此基类功能：
 * 把redis当成简单的关系型数据库来操作，提供基本方法，实现快速数据库
 * 注意本方法数据不过期，
 * 删除与更新操作对服务器影响比较繁重，尽量注意!!
 * 使用时候尽量注意存储小数据，即每个查询总数不要太大的数据...
 */

abstract class RedisCrudDAO extends RedisDAO
{
    protected $_datas_mode=0; //数据挂起模式 ，insert delete update操作需挂起
    protected $_table_ctid;   //当前分表或分库的依据id,一般即uid
    protected $_table_ctret;  //用户分库、存值
    protected $_expiretime = 0;  //数据自动过期时间
    protected $_pk_dbset = null; //uid即跟分库主键

	//public function __construct($mode=null, $table = null, $pk = null,$dbindex = 0,$server=0)
	public function __j7construct($uid=null)
	{
        if ( !(is_string($this->_pk_dbset) || $this->_pk_dbset==null) )
            throw new J7Exception("_pk_dbset Error!");
        $this->_table_base = $this->_table;
        $this->_datas_mode = config('_j7_system_dao_mode');
		$this->__SplitBy($uid);
	}

    //分库指定，此方法将切换存储库
    public function __SplitBy( $uid=null )
    {
        return true;
    }


    protected function changeDb($dbindex=0,$redisdb=null)
	{
		return parent::__changeDb($dbindex,$redisdb);
	}

	public function __getDbIndex()
	{
		return parent::__getDbIndex();
	}

    protected function changeTable($table)
	{
		parent::_changeTable($table);
	}

	protected function __SplitByCond($datas)
	{
        if( $this->_pk_dbset )
        {
            if( is_array($datas) )
            {
                if( isset($datas[$this->_pk_dbset]) )
                    $uid = $datas[$this->_pk_dbset];
                else
                    throw new J7Exception("__SplitByCond Error! with datas:".var_export($datas,true) );
            }
            else
                $uid = $datas;
	        $this->__SplitBy($uid);
        }
	}
	//======================================================================
	//  直接对应的操作....注意此方法很需要与设置里指定
	//======================================================================

    //$cond 条件必须已经被指定的，即一开始就是指定的条件的，$orderstart与$orderend必须是根据inzset结构里设定的order的区间值
	public function get($cond = null, $order = null, $count = null, $offset = null,$orderstart=null,$orderend=null)
    {
        $this->__SplitByCond($cond);
        if( $this->_expiretime )
        {
            $ret = $this->_select($cond,$order,$count,$offset,$orderstart,$orderend,array('filterout'=>false));
            //处理及修正返回结果
            $resule = $ret['result'];
            if( $resule )
                $cfalse = array_keys($resule,false);    //检索出过期的items
            else
                return $resule;
            if( $resule && count($cfalse)>0 && $ret['check']['ctype']!='pk' ) //反向过滤设置过期items
            {
                //找到keys
                $keysret =  $this->_select($cond,$order,$count,$offset,$orderstart,$orderend,array('gettype'=>'getkeys'));
                $key = $this->__getMainKey($keysret['check']['evalCond'],$keysret['check']['ctype']);
                foreach( $cfalse as $eachindex )
                {
                    if( $keysret['check']['ctype']=='set' )
                    {
                        $this->_db->sRem( $key , $keysret['result'][$eachindex] );
                    }
                    elseif( $keysret['check']['ctype']=='zset' )
                    {
                        $this->_db->zRem( $key , $keysret['result'][$eachindex] );
                    }
                }
                $this->_db->expire($key,$this->_expiretime);
            }
            return array_filter($resule);
        }
        $ret = $this->_select($cond,$order,$count,$offset,$orderstart,$orderend);
        return $ret['result'];
    }

	public function getOne($cond = null, $order = null, $offset = null,$orderstart=null,$orderend=null)
	{
        $this->__SplitByCond($cond);
		$data = $this->get($cond, $order , 1, $offset, $orderstart, $orderend );
		return count($data) > 0 ?  $data[0] : null;
	}

    //redis getCount 里面不会忽略 缓存失效的key ，使用时需注意过滤get结果值中的false
	public function getCount($cond = [],$orderstart=null,$orderend=null)
	{
        $this->__SplitByCond($cond);
        return $this->_count($cond,$orderstart,$orderend);
    }
	public function getByPk($pk,$pinfo = [])
    {
        $this->__SplitByCond($pk);
        return $this->__getByPk($pk);
    }
    //直接添加数据
	public function addinstant($datas)
    {
        $this->__SplitByCond($datas);
        return $this->_insert($datas);
    }
    //添加数据，并返回自增主键值
	public function add($datas)
    {
        $this->__SplitByCond($datas);
        return $this->_insertGetLid($datas);
    }
    //更新操作
	public function update($datas, $cond = null ,$pinfo=[])
    {
        $this->__SplitByCond( array_merge($cond,$datas) );
        return $this->_update($datas,$cond);
    }
    //通过主键进行更新，主键由前端 DbDAO 中_pk指定
	public function updateByPk($datas,$pinfo = [])
    {
        $this->__SplitByCond($datas);
        return $this->__updateByPk($datas);
    }
    //删除
	public function delete($cond = [] ,$pinfo=[])
    {
        $this->__SplitByCond($cond);
        return $this->_delete($cond);
    }
    //通过主键删除
	public function deleteByPk($pk,$pinfo=null)
    {
        $this->__SplitByCond($pk);
        return $this->__deleteByPk($pk);
    }

    /*
     * ====================================
     * 覆写父类的操作，主要是用于过期时间设置
     * ====================================
     */
	protected function __addByPk($datas)
    {
        if( $fret = parent::__addByPk($datas) && $this->_expiretime )
        {
            $nowdata = array_intersect_key($datas,$this->_tStructure_dValue);
            $this->setExptimeByData($nowdata);
        }
        return $fret;
    }
    protected function __deleteByPk($pk)
    {
        if($this->_expiretime)
        {
            $pkcond = $this->__pktopkcond($pk);
            $key = $this->__getMainKey( $pkcond ,'pk');
            $olddata = $this->_db->get( $key );
        }
        if( $fret = parent::__deleteByPk($pk) && $this->_expiretime )
        {
            if( $this->_pk_sets )
            {
                foreach( $this->_pk_sets as $eallset )
                {
                    $member = $eallset['cond'];
                    if( $eallset['ctype']=='set' )
                    {
                        $key = $this->__getMainKey( array_intersect_key($olddata,array_fill_keys($member,null)) ,'set');
                    }
                    else
                    {
                        $key = $this->__getMainKey( array_intersect_key($olddata,array_fill_keys($member,null)) ,'zset');
                    }
                    $this->_db->expire($key,$this->_expiretime);
                }
            }
        }
        return $fret;
    }

	protected function _update($datas, $cond = [], $orderstart=null,$orderend=null)
    {
        if($this->_expiretime)
            $outkeys = $this->__getOutKeys($cond,false,$orderstart,$orderend);
        $fret = parent::_update($datas,$cond,$orderstart,$orderend);
        if( $fret && $this->_expiretime )
        {
            $newcond = array_merge($cond,$datas);
            $outkeys2 = $this->__getOutKeys($newcond,false,$orderstart,$orderend);
            $this->setExptimeByData(null,$outkeys+$outkeys2);
        }
        return $fret;
    }

    //格外操作
    public function setExptimeByData($data,$keys=null)
    {
        if($this->_expiretime)
        {
            if( is_null($keys) )
                $keys = $this->__getOutKeys($data);
            if($keys)
            {
                foreach($keys as $eachkeys)
                {
                    if( is_array($eachkeys) && isset($eachkeys['key']) )
                    {
                        $this->_db->expire($eachkeys['key'],$this->_expiretime);
                    }
                    elseif( is_string($eachkeys) )
                    {
                         $this->_db->expire($eachkeys,$this->_expiretime);
                    }
                }
            }
        }
    }

}