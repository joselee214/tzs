<?php
require_once __DIR__ . '/../Util.php';
require_once __DIR__ . "/../J7Config.php";
require_once __DIR__ . "/../J7Debuger.php";
require_once __DIR__ . '/../RedisAdapter/coreRedisData.php';

/*
 * 此基类功能：
 * 把redis当成简单的关系型数据库来操作，提供基本方法，实现快速数据库
 * 注意本方法数据不过期，
 * 删除与更新操作对服务器影响比较繁重，尽量注意!!
 * 使用时候尽量注意存储小数据，即每个查询总数不要太大的数据...
 */

abstract class RedisDAO implements CrudInterface
{
    protected $_db = null;
    protected $_table = null;
    protected $_table_map = null;
    protected $_abstable = null;
    protected $_dbconinfo = [];
    protected $_dbconindex = null; //连接信息

    protected $_cacheKeyPrefix = '';
    protected $_pk = null;
    protected $_pk_inset = []; //array('fId');  //无序集合，将无序信息组合进set结构，每个是value对应
    protected $_pk_inzset = [];  //有序集合，将信息组合进set结构，array('cond'=>'xxx','order'=>'xxx'), 对应  //即单条件的desc或者asc
    protected $_tStructure_dValue = [];
    protected $_pk_sets = [];  //存储 _pk_inset + _pk_inzset 组合的数据。。。//不应赋值
    protected $_pk_perName = '';

    public function __construct(){}
    protected function __j7construct($dbindex=0,$redisdb=null)
    {
        $this->__changeDb($dbindex,$redisdb);
        $this->_abstable = substr(get_called_class(),-8,8)=='RedisDAO'?substr(get_called_class(),0,-8):get_called_class();

        if (empty($this->_table))
        {
            $this->_table = end(explode('\\',$this->_abstable));
        }

        $this->__getAllSets();  //重新整理数据的需要//更改表设置后，需要执行这个的。。
    }
    //外部直接更改DB连接
    protected function __changeDb($dbindex=0,$redisdb=null)
    {
        $_dbconinfo = [];
        $_dbconinfo['database'] = $redisdb;
        if( $this->_dbconindex!==$dbindex || $_dbconinfo!==$this->_dbconinfo )
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
    protected function _changeTable($table)
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

    //$cond 结构 ... 必须作判断 $order : 'xxx desc',
    protected function _select($cond = null, $order = null, $count = null, $offset = null,$orderstart=null,$orderend=null,$moreinfo=[]) //$gettype='getkeys'列出ids
    {
        if( isset($moreinfo['gettype']) && $moreinfo['gettype']=='getkeys' ){ $gettype = 'getkeys';}else{$gettype = 'items';}
        if( isset($moreinfo['filterout']) && $moreinfo['filterout']==false ){ $filterout = false;}else{$filterout = true;}
        if( is_null($orderstart) && is_null($orderend) )
        {
            $check = $this->__getIsCondSet($cond,'allset');
            if( !is_null($check['evalCond']) )
            {
                $resule = $this->__getItemsBySet($check['evalCond'],$order,$offset,$count,$check['ctype'],$gettype);
            }
            else
            {
                $check = $this->__getIsCondSet($cond,'pk');
                if( !is_null($check['evalCond']) )
                {
                    if( $gettype=='getkeys' )
                        $resule = array( implode(':',$check['evalCond']) );
                    else
                        $resule = array($this->__getByPk($check['evalCond']));
                }
            }
        }
        else
        {
            $check = $this->__getIsCondSet($cond,'zset');
            if( is_null($check['evalCond']) )
                throw new J7Exception('_select Error ,cond not correct ! from'.get_class($this));
            $resule = $this->__getItemsByZSet($check['evalCond'],$order,$offset,$count,$orderstart,$orderend,$gettype);
        }

        if( isset($resule) && $resule )
        {
            if( !$filterout || $gettype=='getkeys' )
                return array('check'=>$check,'result'=>$resule);
            if( empty($check['outCond']) )
            {
                $output = array('check'=>$check,'result'=>array_filter($resule));
            }
            else
            {
                $output = array('check'=>$check,'result'=>array_filter($resule,function($val)use($check){ if( $val && array_intersect_assoc($check['outCond'],$val)==$check['outCond']){ return true;}else{return false;} }));
            }
            return $output;
        }
        return false;
    }
    protected function _count($cond,$orderstart=null,$orderend=null)
    {
        if( is_null($orderstart) && is_null($orderend) )
        {
            $check = $this->__getIsCondSet($cond,'allset');
            if( !is_null($check['evalCond']) && empty($check['outCond']) )
            {
                return $this->__getItemsBySet($check['evalCond'],'count',null,null,$check['ctype']);
            }
            $check = $this->__getIsCondSet($cond,'pk');
            if( !is_null($check['evalCond']) && empty($check['outCond']) )
            {
                $key = $this->__getMainKey( $check['evalCond'] ,'pk');
                if( $this->db->exists($key) )
                    return 1;
                else
                    return 0;
            }
            throw new J7Exception('_count Error , cond not correct ! from'.get_class($this));
        }
        else
        {
            $check = $this->__getIsCondSet($cond,'zset');
            if( is_null($check['evalCond']) || !empty($check['outCond']) )
                throw new J7Exception('_count Error ,cond not correct ! from'.get_class($this));
            return $this->__getItemsByZSet($check['evalCond'],'count',null,null,$orderstart,$orderend);
        }
        return false;
    }

    /**************************************************************
     * 结构化处理数据,业务层方法1
     **************************************************************/

    protected function _insert($datas)
    {
        //必须要有主键
        return $this->__addByPk($datas);
    }
    protected function _insertGetLid($datas)
    {
        $ret = [];
        foreach($this->_pk as $eachpkkey)
        {
            if( ! isset($datas[$eachpkkey]) )
            {
                $datas[$eachpkkey] = Util::getIncrId($this->_table_map);
            }
            $ret[$eachpkkey] = $datas[$eachpkkey];
        }
        if( $this->__addByPk($datas) )
        {
            return $ret;
        }
        else
            return false;
    }

    /**************************************************************
     * 结构化处理数据,核心转换
     **************************************************************/
    protected function _delete($cond = [] , $orderstart=null,$orderend=null)
    {
        if( empty($cond) )
            return false;

        if( count($this->_pk_sets)==1 ) //判断处理方式，一次性删除,
        {
            $check = $this->__getIsCondSet($cond,'allset');
            if( !is_null($check['evalCond']) && empty($check['outCond']) )
            {
                //do All//一次性删除
                $keysret =  $this->_select($cond,null,null,null,$orderstart,$orderend,array('gettype'=>'getkeys'));
                if( $keysret )
                {
                    $this->db->delete($keysret,$this->_pk_perName);
                    $key = $this->__getMainKey($check['evalCond'],$check['ctype']);
                    //再删关联key
                    if( is_null($orderstart) && is_null($orderend) )
                    {
                        return $this->db->delete($key);
                    }
                    else
                    {
                        return $this->_db->zDeleteRangeByScore( $key , $orderstart ,$orderend );
                    }
                }
            }
        }
        $rows = $this->_select($cond,null,null,null,$orderstart,$orderend);
        if( $rows && $rows['result'] )
        {
            $ret = 0;
            foreach($rows['result'] as $eachrow)
            {
                if( $eachrow )
                {
                    $eachret = $this->__deleteByPk($eachrow);
                    $ret += $eachret;
                }
                $ret += 1;
            }
            return $ret;
        }
        return false;
    }
    protected function _update($datas, $cond = [], $orderstart=null,$orderend=null)   //不能修改主键数据
    {
        if( empty($cond) || empty($datas) )
            return false;
        if( count($this->_pk_sets)==1 ) //判断处理方式，一次性处理,
        {
            $datas = array_diff_assoc($datas,$cond);    //取得差集
            $check = $this->__getIsCondSet($cond,'allset');
            if( !is_null($check['evalCond']) && empty($check['outCond']) )
            {
                if( !( $check['ctype'] == 'zset' && isset($check['order']) && isset($datas[$check['order']]) ) && ! array_intersect_key($cond,$datas)  )
                {
                    //无交集//仅需要处理单key的值
                    $keysret =  $this->_select($cond,null,null,null,$orderstart,$orderend,array('gettype'=>'getkeys'));
                    if( $keysret )
                    {
                        //do All//一次性处理
                        $arrset = [];
                        $oldret = $this->_db->get( $keysret , $this->_pk_perName );
                        foreach($oldret as $ek=>$ev)
                        {
                            if($ev)
                                $arrset[$this->_pk_perName.$keysret[$ek]] = array_merge($ev,$datas);
                        }
                        if($arrset)
                            return $this->_db->mset($arrset);
                        return false;
                    }
                }
            }
        }
        $rows = $this->_select($cond,null,null,null,$orderstart,$orderend);
        if( $rows && $rows['result'] )
        {
            $ret = 0;
            foreach($rows['result'] as $eachrow)
            {
                //需要删除成功了再加。。。
                if( $eachrow )
                {
                    $pkcond = $this->__pktopkcond($eachrow);    //旧的每条数据主键
                    if( ($olddata = $this->__getByPk($pkcond)) && $this->__deleteByPk($eachrow) )
                    {
                        $newdata = array_merge($olddata,$datas);
                        $this->__addByPk($newdata);
                        $ret += 1;
                    }
                }
            }
            return $ret;
        }
        return false;
    }
    protected function __updateByPk($datas)
    {
        //先获取主键
        $condcheck = $this->__getIsCondSet($datas,'pk');
        if( is_null($condcheck['evalCond']) )
            throw new J7Exception('__updateByPk Error , _pk not correct ! from:'.get_class($this));
        $pkcond = $condcheck['evalCond'];
        $key = $this->__getMainKey( $pkcond ,'pk');
        $olddata = $this->_db->get( $key );
        if( $olddata )
        {
            $this->__deleteByPk($pkcond);
            $newdata = array_merge($olddata,$datas);
            return $this->__addByPk($newdata);
        }
        return false;
    }

    protected function __deleteByPk($pk)
    {
        $pkcond = $this->__pktopkcond($pk);
        $key = $this->__getMainKey( $pkcond ,'pk');
        $newdata = $this->_db->get( $key );
        $pkstring = implode(':',$pkcond);
        if( $delret = $this->_db->delete($key) )
        {
            if( $this->_pk_sets )
            {
                foreach( $this->_pk_sets as $eallset )
                {
                    $member = $eallset['cond'];
                    if( $eallset['ctype']=='set' )
                    {
                        $key = $this->__getMainKey( array_intersect_key($newdata,array_fill_keys($member,null)) ,'set');
                        $this->_db->sRem( $key , $pkstring );
                    }
                    else
                    {
                        $key = $this->__getMainKey( array_intersect_key($newdata,array_fill_keys($member,null)) ,'zset');
                        $this->_db->zRem( $key, $pkstring );
                    }
                }
            }
        }
        return $delret;
    }

    //添加数据记录
    protected function __addByPk($datas)
    {
        //自动处理结构化数据
        $newdata =  array_merge($this->_tStructure_dValue , $datas);
        $condcheck = $this->__getIsCondSet($newdata,'pk');
        if( is_null($condcheck['evalCond']) )
            throw new J7Exception('__addByPk Error , _pk not correct ! from:'.get_class($this));
        $pkstring = implode(':',$condcheck['evalCond']);
        $keymain = $this->__getMainKey($condcheck['evalCond'],'pk');
        if ( $this->_db->setnx( $keymain , $newdata ) )    //存储主键数据
        {
            if( $this->_pk_sets )
            {
                foreach( $this->_pk_sets as $eallset )
                {
                    $member = $eallset['cond'];
                    if( $eallset['ctype']=='set' )
                    {
                        $key = $this->__getMainKey( array_intersect_key($newdata,array_fill_keys($member,null)) ,'set');
                        $this->_db->sAdd( $key , $pkstring );
                    }
                    else
                    {
                        $score = $eallset['order'];
                        $key = $this->__getMainKey( array_intersect_key($newdata,array_fill_keys($member,null)) ,'zset');
                        $this->_db->zAdd( $key, $newdata[$score], $pkstring );
                    }
                }
            }
            return true;
        }
        else
        {
            throw new J7Exception('__addByPk Error , setnx error !from'.get_class($this));
        }
        return false;
    }
    //根据添加的数据，获取关联的所有key
    public function __getOutKeys($cond,$isadd=true,$orderstart=null,$orderend=null)
    {
        if( $isadd )
        {
            $newdata =  array_merge($this->_tStructure_dValue , $cond);
            $condcheck = $this->__getIsCondSet($newdata,'pk');
            if( is_null($condcheck['evalCond']) )
                throw new J7Exception('__getOutKeys Error , _pk not correct ! from:'.get_class($this));
            $keymain = $this->__getMainKey($condcheck['evalCond'],'pk');

            $out = [];
            $out[] = array('ctype'=>'pk','key'=>$keymain);

            if( $this->_pk_sets )
            {
                foreach( $this->_pk_sets as $eallset )
                {
                    $member = $eallset['cond'];
                    if( $eallset['ctype']=='set' )
                    {
                        $key = $this->__getMainKey( array_intersect_key($newdata,array_fill_keys($member,null)) ,'set');
                        $out[] = array('ctype'=>'set','key'=>$key);
                    }
                    else
                    {
                        $key = $this->__getMainKey( array_intersect_key($newdata,array_fill_keys($member,null)) ,'zset');
                        $out[] = array('ctype'=>'zset','key'=>$key);
                    }
                }
            }
        }
        else
        {
            //根据条件获得命中的所有key
            $out =  $this->_select($cond,null,null,null,$orderstart,$orderend,array('gettype'=>'getkeys'));
            if( $this->_pk_sets )   //必须是 $cond >= $eallset 才需要操作
            {
                foreach( $this->_pk_sets as $eallset )
                {
                    $member = $eallset['cond'];
                    $eachpk = array_fill_keys($member,null);
                    $check = array_intersect_key($cond,$eachpk);
                    if( count($check) == count($eachpk) )
                    {
                        if( $eallset['ctype']=='set' )
                        {
                            $key = $this->__getMainKey( $check ,'set');
                            $out[] = array('ctype'=>'set','key'=>$key);
                        }
                        else
                        {
                            $key = $this->__getMainKey( $check ,'zset');
                            $out[] = array('ctype'=>'zset','key'=>$key);
                        }
                    }
                }
            }
        }
        return $out;
    }
    protected function __getByPk($pk)
    {
        //获取单条数据
        $key = $this->__getMainKey( $this->__pktopkcond($pk) ,'pk');
        return $this->_db->get( $key );
    }
    protected function __pktopkcond($pk)
    {
        if( !is_array($pk) )
            $pk = array($pk);
        if( isset($pk[0]) )
        {
            return array_combine($this->_pk,$pk);
        }
        else
        {
            return array_intersect_key($pk,array_fill_keys($this->_pk,null));
        }
    }
    protected function __getItemsBySet($cond,$order='desc',$offset=0,$count=-1,$stype=null,$gettype='items')    //$cond = array( 'pk'=>?? ) //只能根据主键排序
    {
        //获取无序数据
        if( $stype!='zset' ){$stype='set';}
        $key = $this->__getMainKey($cond,$stype);

        if( $order == 'count' )
        {
            if( $stype=='zset' )
                return $this->_db->zSize($key);
            else
                return $this->_db->sSize($key);
        }

        $sortkey = [];
        $sortkey['sort'] = $order=='desc'?'desc':'asc';
        if( $count )
            $sortkey['limit'] = array($offset,$count);

        if( $gettype == 'getkeys' )
        {
            return $this->_db->sortget($key,$sortkey);
        }
        $sortkey['get'] = array($this->_pk_perName.'*');
        return $this->_db->sortget($key,$sortkey);
    }
    //没有 orderby 时候，即按主键排序，有orderby时候，根据score排序，$orderstart，$orderend及范围限定值
    protected function __getItemsByZSet($cond,$order='desc',$offset=0,$count=-1,$orderstart=null,$orderend=null,$gettype='items')
    {
        $key = $this->__getMainKey($cond,'zset');
        if( is_null($orderend) && is_null($orderstart) )
        {
            $offset = $offset!=null?$offset:0;
            $count = $count!=null?$count:-1;
            if( $order == 'desc' )
                $valarr = $this->_db->zRevRange( $key , $offset ,$count  );
            else
                $valarr = $this->_db->zRange( $key , $offset ,$count  );
        }
        else
        {
            if( $order == 'count' )
            {
                return $this->_db->zCount($key,$orderstart,$orderend);
            }
            if( is_null($offset) || is_null($count) )
                $option = [];
            else
                $option = array('limit' => array($offset, $count));
            if( $order == 'desc' )
                $valarr = $this->_db->zRevRangeByScore( $key , $orderstart ,$orderend , $option );
            else
                $valarr = $this->_db->zRangeByScore( $key , $orderstart ,$orderend , $option );

            if( $gettype == 'getkeys' )
            {
                return $valarr;
            }
        }
        return $this->_db->get($valarr,$this->_pk_perName);
    }

    //获得键名
    protected function __getMainKey($cond,$type)
    {
        ksort($cond);
        switch($type)
        {
            case 'pk':
                if( count($cond) != count($this->_pk) )
                {
                    throw new J7Exception('__getMainKey Error , _pk cond not correct ! from:'.get_class($this));
                }
                $mainkey = $this->_pk_perName.implode(':',$cond);
                break;
            case 'set':
                $mainkey = 'J7T:'.$this->_cacheKeyPrefix.$this->_table_map.':S:'.implode(':',array_keys($cond)).':'.implode(':',$cond);
                break;
            case 'zset':
                $mainkey = 'J7T:'.$this->_cacheKeyPrefix.$this->_table_map.':Z:'.implode(':',array_keys($cond)).':'.implode(':',$cond);
                break;
            default:
                throw new J7Exception('__getMainKey Error , $type not correct ! from: '.get_class($this));
                break;
        }
        return $mainkey;
    }
    //检查查询条件是否满足配置设置
    protected function __getIsCondSet($datas,$forcetype)
    {
        switch($forcetype)
        {
            case 'pk':
                $eachpk = array_fill_keys($this->_pk,null);
                $check = array_intersect_key($datas,$eachpk);
                $checkout = array_diff_key($datas,$eachpk);
                if( count($check) == count($eachpk) )
                {
                    ksort($check);
                    $ret = array('evalCond'=>$check,'outCond'=>$checkout);
                }
                else
                    $ret = array('evalCond'=>null,'outCond'=>$checkout);
                break;
            case 'set':
                $ret = array('evalCond'=>null,'outCond'=>$datas);
                if($this->_pk_inset)
                {
                    foreach($this->_pk_inset as $eachpk)
                    {
                        $eachpk = array_fill_keys($eachpk,null);
                        $check = array_intersect_key($datas,$eachpk);
                        if( count($check) == count($eachpk) )
                        {
                            $checkout = array_diff_key($datas,$eachpk);
                            $ret = array('evalCond'=>$check,'outCond'=>$checkout);
                            break;
                        }
                    }
                }
                break;
            case 'zset':
                $ret = array('evalCond'=>null,'outCond'=>$datas);
                if($this->_pk_inzset)
                {
                    foreach($this->_pk_inzset as $eachinz)
                    {
                        $eachpk = $eachinz['cond'];
                        $eachpk = array_fill_keys($eachpk,null);
                        $check = array_intersect_key($datas,$eachpk);
                        if( count($check) == count($eachpk) )
                        {
                            $checkout = array_diff_key($datas,$eachpk);
                            $ret = array('evalCond'=>$check,'outCond'=>$checkout,'order'=>$eachinz['order']);
                            break;
                        }
                    }
                }
                break;
            case 'allset':
                $ret = array('evalCond'=>null,'outCond'=>$datas);
                if($this->_pk_sets)
                {
                    foreach($this->_pk_sets as $eachinz)
                    {
                        $eachpk = $eachinz['cond'];
                        $eachpk = array_fill_keys($eachpk,null);
                        $check = array_intersect_key($datas,$eachpk);
                        if( count($check) == count($eachpk) )
                        {
                            $checkout = array_diff_key($datas,$eachpk);
                            $forcetype = $eachinz['ctype'];
                            $ret = array('evalCond'=>$check,'outCond'=>$checkout);
                            if( $forcetype=='zset' )
                                $ret['order'] = $eachinz['order'];
                            break;
                        }
                    }
                }
                break;
            default:
                throw new J7Exception('__getIsCondSet Error , $forcetype not correct ! from:'.get_class($this));
                break;
        }
        $ret['ctype'] = $forcetype;
        return $ret;
    }
    protected function __getAllSets()   //重新整理pkset数据
    {
        $db_prefix_map = config('rd_table_map');
        $this->_table_map = isset($db_prefix_map[$this->_table])?$db_prefix_map[$this->_table]:$this->_table;
        $this->_pk_perName = 'J7T:'.$this->_cacheKeyPrefix.$this->_table_map.':K:';
        $this->_pk_sets = [];

        if( empty($this->_tStructure_dValue) )
        {
            throw new J7Exception('_tStructure_dValue cannt be empty ! from:'.get_class($this));
        }
        if( $this->_pk_inset )
        {
            foreach($this->_pk_inset as $_pk_inset)
            {
                $this->_pk_sets[] = array('ctype'=>'set','cond'=>$_pk_inset);
            }
        }
        if( $this->_pk_inzset )
        {
            foreach($this->_pk_inzset as $_pk_inzset)
            {
                $this->_pk_sets[] = array('ctype'=>'zset','cond'=>$_pk_inzset['cond'],'order'=>$_pk_inzset['order']);
            }
        }
        $this->_pk_sets = $this->__sortCondArr($this->_pk_sets,'cond');
        $this->_pk_inzset = $this->__sortCondArr($this->_pk_inzset,'cond');
        $this->_pk_inset = $this->__sortCondArr($this->_pk_inset,null);
    }
    protected function __sortCondArr($arr,$ep=null)   //排序
    {
        $newarr = $out = [];
        foreach($arr as $eacharr)
        {
            if( $ep )
                $count = count($eacharr[$ep]);
            else
                $count = count($eacharr);
            if( !isset($newarr[$count]) )
                $newarr[$count] = [];
            $newarr[$count][] = $eacharr;
        }
        krsort($newarr);
        foreach($newarr as $ev)
        {
            foreach($ev as $eev)
            {
                $out[] = $eev;
            }
        }
        return $out;
    }

}