<?php
///**
// * 数据库ORM模型分库分表，Cache分列,分配方法类
// * 返回值对应的是 j7config 里的 databasegroup memcachegroup 组别
// */
////require_once __DIR__.'/Factory.php';
////require_once __DIR__."/J7Config.php";
////require_once __DIR__."/RuntimeData.php";
//
//class coreSplit
//{
//	public static $splitby=[]; //根据的id
//    public static $splitresult=[]; //split结果
//    public static $config;  //配置信息
//    public static $splitdb; //用户索引表db
//
//    public static function clear()
//    {
//        self::$splitby = [];
//        self::$splitresult = [];
//    }
//
//    //注意，此方法返回的是以uid为下标的数组，但是极其可能是反序的 $gettype 是返回格式组,根据type返回
//    public static function GetArray($uids,$type=null,$groupby=false,$isneedaddtodb=false)
//    {
//        $dbret = self::getUserInfoArray($uids);
//        $outarr = [];
//        $addnum = 0;
//        $retgroupid = array('database'=>0,'memcache'=>0,'redis'=>0);
//        $outgroupby = array('database'=>[],'memcache'=>[],'redis'=>[]);
//        if( $isneedaddtodb && array_search(null,$outarr) !== false )
//        {
//            $needaddtodb = [];
//            $retgroupid = self::GetSplit();
//        }
//        foreach( $dbret as $uid=>$eret )
//        {
//            if( is_null($eret) )
//            {
//                $outarr[$uid] = $retgroupid;
//                if( $isneedaddtodb )
//                {
//                    //判断并新增
//                    $indata = [];
//                    $indata['table_ctid'] = $uid;
//                    $indata['database_gid'] = $retgroupid['database'];
//                    $indata['memcache_gid'] = $retgroupid['memcache'];
//                    $indata['redis_gid'] = $retgroupid['redis'];
//                    $needaddtodb[] = $indata;
//                    ++$addnum;
//                }
//            }
//            else
//            {
//                $outarr[$uid]['database'] = $eret['database_gid'];
//                $outarr[$uid]['memcache'] = $eret['memcache_gid'];
//                $outarr[$uid]['redis'] = $eret['redis_gid'];
//            }
//            //返回结果过滤
//            if( $groupby===false )
//            {
//                if( $type )
//                {
//                    $outarr[$uid] = $outarr[$uid][$type];
//                }
//                else
//                {
//                    $outarr[$uid]['id'] = $uid;
//                }
//            }
//            else
//            {
//                if( $type )
//                {
//                    if( ! isset($outgroupby[$type][$outarr[$uid][$type]]) )
//                        $outgroupby[$type][$outarr[$uid][$type]] = [];
//                    $outgroupby[$type][$outarr[$uid][$type]][] = $uid;
//                }
//                else
//                {
//                    foreach($retgroupid as $typekey=>$kkkv)
//                    {
//                        if( ! isset($outgroupby[$typekey][$outarr[$uid][$typekey]]) )
//                            $outgroupby[$typekey][$outarr[$uid][$typekey]] = [];
//                        $outgroupby[$typekey][$outarr[$uid][$typekey]][] = $uid;
//                    }
//                }
//            }
//        }
//        unset($dbret);
//        if( $addnum )
//        {
//            $coreDataConfig = config('groupcore0');
//            $_redis = RedisData::instance(null,$coreDataConfig['redis'],null,null);
//            foreach($retgroupid as $ekey=>$rev)
//            {
//                $intkey = 'j7PHPFrame:coreSplit:'.$ekey.':gid:'.$retgroupid[$ekey];
//                $_redis->incr($intkey,$addnum);
//            }
//            unset($_redis);
//            self::addUserInfoMult($needaddtodb);
//        }
//        if( $groupby===false )
//            return $outarr;
//        else
//        {
//            unset($outarr);
//            if( $type )
//                return $outgroupby[$type];
//            else
//                return $outgroupby;
//        }
//    }
//
//
//    public static function GetSplit( $retgroupid = array('database'=>0,'memcache'=>0,'redis'=>0) )
//    {
//        //分配新用户落组
//        $coreDataConfig = config('groupcore0');
//        //权重分配信息
//        $coreGroupPreConfig = config('grouppres');
//        $_redis = RedisData::instance(null,$coreDataConfig['redis'],null,null);
//
//        foreach($coreGroupPreConfig as $ktype=>$preval)
//        {
//            $groupgids = array_keys($preval);
//            if( count($groupgids)==1 )
//                $retgroupid[$ktype] = $groupgids[0];
//            else
//            {
//                $groupkeys = $groupgids;
//                array_walk($groupkeys, function(&$value,$key,$ktype){ $value = 'j7PHPFrame:coreSplit:'.$ktype.':gid:'.$value; },$ktype );
//                $gprret = $_redis->get( $groupkeys );
//                if( array_search(false,$gprret) !== false ) //有false存在找不到值，需重建索引redis
//                {
//                    $retv = self::getGroupCount($ktype);
//                    $setinfo = $gprret = [];
//                    foreach( $groupgids as $gidv )
//                    {
//                        $pv = isset($retv[$gidv])?intval($retv[$gidv]):0;
//                        $setinfo['j7PHPFrame:coreSplit:'.$ktype.':gid:'.$gidv] = $pv;
//                        $gprret[] = $pv;
//                    }
//                    $_redis->mset($setinfo);
//                }
//                $nowpreval = array_combine($groupgids,$gprret);
//                $tootalprev = array_sum($preval);
//                $tootalprevnow = array_sum($nowpreval);
//                $outgid = $groupgids[0];
//                foreach( $preval as $egid=>$equanzv )
//                {
//                    if( ($equanzv/$tootalprev) > ($nowpreval[$egid]/$tootalprevnow) )
//                    {
//                        $outgid = $egid;
//                        break;
//                    }
//                }
//                $retgroupid[$ktype] = $outgid;
//            }
//        }
//        return $retgroupid;
//    }
//
//    public static function Get($uid,$type=null)
//    {
//        if( ! isset(self::$splitby[$uid]) )
//        {
//            self::$splitby[$uid] = $uid;
//        }
//        if( ! isset(self::$splitresult[$uid]) )
//        {
//            self::$splitresult[$uid] = [];
//        }
//
//        if( ! self::$splitresult[$uid]  )
//        {
//            if( $uinfo = self::getUserInfo($uid) )
//            {
//                self::$splitresult[$uid]['database'] = $uinfo['database_gid'];
//                self::$splitresult[$uid]['memcache'] = $uinfo['memcache_gid'];
//                self::$splitresult[$uid]['redis'] = $uinfo['redis_gid'];
//            }
//            else
//            {
//                $retgroupid = self::GetSplit();
//                self::$splitresult[$uid] = $retgroupid;
//
//                $coreDataConfig = config('groupcore0');
//                $_redis = RedisData::instance(null,$coreDataConfig['redis'],null,null);
//                foreach($retgroupid as $ekey=>$rev)
//                {
//                    $intkey = 'j7PHPFrame:coreSplit:'.$ekey.':gid:'.$retgroupid[$ekey];
//                    $_redis->incr($intkey);
//                }
//                
//                $indata = [];
//                $indata['database_gid'] = self::$splitresult[$uid]['database'];
//                $indata['memcache_gid'] = self::$splitresult[$uid]['memcache'];
//                $indata['redis_gid'] = self::$splitresult[$uid]['redis'];
//                self::addUserInfo($indata,$uid);
//                $_redis=null;
//            }
//        }
//        if( $type )
//        {
//            $ret = self::$splitresult[$uid][$type];
//        }
//        else
//        {
//            $ret = self::$splitresult[$uid];
//        }
//        if( count(self::$splitresult)>10 )
//        {
//            $randkey = array_rand(self::$splitresult,5);
//            foreach($randkey as $eachrkey)
//            {
//                unset( self::$splitresult[$eachrkey] );
//            }
//        }
//        return $ret;
//    }
//    public static function delUserInfo($uid)
//    {
//        if( ! self::$splitdb )
//		    self::$splitdb = Factory::Get('syssplitDbDAO',null,null,true);
//        return self::$splitdb->del($uid);
//    }
//
//    private static function getGroupCount($type)
//    {
//        if( ! self::$splitdb )
//		    self::$splitdb = Factory::Get('syssplitDbDAO',null,null,true);
//        return self::$splitdb->getGroupCount($type);
//    }
//    private static function addUserInfo($data,$uid=null)
//    {
//        if( ! self::$splitdb )
//		    self::$splitdb = Factory::Get('syssplitDbDAO',null,null,true);
//        return self::$splitdb->add($data,$uid);
//    }
//    private static function addUserInfoMult($data)
//    {
//        if( ! self::$splitdb )
//		    self::$splitdb = Factory::Get('syssplitDbDAO',null,null,true);
//        return self::$splitdb->addmult($data);
//    }
//    private static function getUserInfo($uid)
//    {
//        if( ! self::$splitdb )
//		    self::$splitdb = Factory::Get('syssplitDbDAO',null,null,true);
//        return self::$splitdb->getByPk($uid);
//    }
//    private static function getUserInfoArray($uid)
//    {
//        if( ! self::$splitdb )
//		    self::$splitdb = Factory::Get('syssplitDbDAO',null,null,true);
//        return self::$splitdb->getByPkArray($uid);
//    }
//}
