<?php
/*
 * 没在业务中大规模使用过
 * 因为改写过数组, 会有bug!!!!!!!!
 * 依赖 Redis 组件
 * 命令参考 https://github.com/nicolasff/phpredis   //http://my.oschina.net/cniiliuqi/blog/67423
 */

class RedisFactory
{
    private static $_instance = [];
    /**
     * @var Redis
     */
    private $redis = null;
    public $_isconnect=false;

    //连接构造
    static public function factory($config,$cacheprefix='')
    {
        $insancenameset = hash('crc32',json_encode($config));
        if (!isset(self::$_instance[$insancenameset])) {
            $tmp = new RedisFactory();
            $tmp->redis = new Redis;
            if( isset($config['pconnect']) && $config['pconnect']===false )
                $tmp->redis->connect($config['host'], $config['port'], isset($config['timeout'])?$config['timeout']:0.1 , $insancenameset );
            else
                $tmp->redis->pconnect($config['host'], $config['port'], isset($config['timeout'])?$config['timeout']:0.1 , $insancenameset );

            $cacheprefix = (isset($config['prefix'])?$config['prefix']:'') . $cacheprefix;
            $tmp->redis->setOption(Redis::OPT_PREFIX, $cacheprefix);
//            $tmp->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
//            $serialize = isset($config['serialize'])?$config['serialize']:2; //Redis::SERIALIZER_IGBINARY;
//            $tmp->redis->setOption(Redis::OPT_SERIALIZER, $serialize);

            $tmp->_isconnect = true;
            if (isset($config['database']) && is_int($config['database'])) {
                $tmp->redis->select($config['database']);
            }
            else
                $tmp->redis->select(0);
            self::$_instance[$insancenameset] = $tmp;
        }
        return self::$_instance[$insancenameset];
    }

    public function unserializedata(&$data)
    {
        if ( is_string($data) && substr($data, 0, 18) == 'J7:STORE|SERIALIZE') {
            $data = unserialize(substr($data, 18));
        }
        return $data;
    }
    public function serializedata(&$data)
    {
        if ( is_array($data) || is_object($data) ) {
            $data = 'J7:STORE|SERIALIZE' . serialize($data);
        }
        return $data;
    }

    public function __call($method,$params)
    {
        return call_user_func_array(array($this->redis, $method), $params);
    }

    /*
     * Redis 常用
     */

    public function incr($key,$incrnum=1) // incr, incrBy
    {
        return $this->redis->incrBy($key , $incrnum);
    }
    public function decr($key,$incrnum=1) //decr, decrBy
    {
        return $this->redis->decrBy($key , $incrnum);
    }
    public function delete($key,$perstring='')
    {
        return $this->del($key,$perstring);
    }

    /*
     * Redis 基本操...改写数组转换
     */

    public function get($key,$perstring='')
    {
        if (is_array($key)) {
            if ($perstring) {
                foreach ($key as $i_key => $i_value)
                {
                    $key[$i_key] = $perstring . $i_value;
                }
            }
            $ret = $this->redis->getMultiple($key);
            if($ret)
                array_walk($ret, '$this->unserializedata');
            return $ret;
        }
        else
        {
            $ret = $this->redis->get($perstring . $key);
            $ret = $this->unserializedata($ret);
            return $ret;
        }
    }

    //仅限于key是字符串模式 //数组模式请使用 mset
    public function set($key, $value, $limit = 2592000)
    {
        if ( is_null($value) ) {
            return $this->del($key);
        }
        if ( is_array($value) || is_object($value) ) {
            $value = $this->serializedata($value);
        }
        if (is_string($value) || is_int($value)) {
            if ($limit) {
                return ($this->redis->setex($key, $limit, $value));
            }
            else
            {
                return ($this->redis->set($key, $value));
            }
        }
        else
        {
            return false;
        }
    }

    public function setnx($key,$value)
    {
        $value = $this->serializedata($value);
        return $this->redis->setnx($key, $value);
    }

    //msetnx
    public function mset($keyvaluearray)
    {
        $newarr = [];
        foreach($keyvaluearray as $key=>$value)
        {
            $value = $this->serializedata($value);
            $newarr[$key] = $value;
        }
        return $this->redis->mset($newarr);
    }

    public function del($key,$perstring='')
    {
        if (is_array($key) ) {
            if( $perstring )
                foreach ($key as $i_key => $i_value)
                {
                    $key[$i_key] = $perstring . $i_value;
                }
        }
        return ($this->redis->delete($key));
    }

    //getBit setBit flushDB flushAll

    //$sortkey 结构
//    'by' => 'some_pattern_*',
//    'limit' => array(0, 1),   //修饰符接受两个参数：offset和count
//    'get' => 'some_other_pattern_*' or an array of patterns,
//    'sort' => 'asc' or 'desc',
//    'alpha' => TRUE,  //即字符串排序
//    'store' => 'external-key'

    public function sortget($key,$sortkey=[])  //建议使用这个，这个根据get的结果会进行重组数据
    {
        if( ! isset($sortkey['by']) )
            $sortkey['by'] = microtime(true);
        $ret = $this->redis->sort($key,$sortkey);
        if( isset($sortkey['get']) && is_array($sortkey['get']) && count($sortkey['get'])>1 )
        {
            $tecount = 0; $outret=$smarr=[];$ecount=count($sortkey['get']);
            foreach($ret as $eachret)
            {
                $eachret = $this->unserializedata($eachret);

                $smarr[] = $eachret;
                if($tecount<$ecount-1)
                    $tecount++;
                else
                {
                    $tecount = 0;
                    $outret[] = $smarr;
                    $smarr=[];
                }
            }
            $ret=$smarr=null;
            return $outret;
        }
        else
        {
            foreach($ret as $knum=>$eachret)
            {
                $eachret = $this->unserializedata($eachret);
                $ret[$knum] = $eachret;
            }
            return $ret;
        }
    }

    //list操作
    //左插入list
    public function lPush($key,$listvalue)
    {
        if( is_string($listvalue) )
        {
            return $this->redis->lPush($key,$listvalue);
        }
        foreach($listvalue as $eachv)
        {
            $this->redis->lPush($key,$eachv);
        }
        return count($listvalue);
    }
    //右插入list
    public function rPush($key,$listvalue)
    {
        if( is_string($listvalue) )
        {
            return $this->redis->rPush($key,$listvalue);
        }
        foreach($listvalue as $eachv)
        {
            $this->redis->rPush($key,$eachv);
        }
        return count($listvalue);
    }

    public function lInsert($key,$pivot,$value,$before=true)
    {
        if( $before )
            return $this->redis->lRem($key,Redis::BEFORE,$pivot,$value);
        else
            return $this->redis->lRem($key,Redis::AFTER,$pivot,$value);
    }

    //Hash
    public function hMset($key,$valuearr)   //array('name' => 'Joe', 'salary' => 2000) //key=>value
    {
        array_walk($valuearr, '$this->serializedata');
        return $this->redis->hMset($key,$valuearr);
    }
    public function hMGet($key,$valuearr)   //array('field1', 'field2')
    {
        $ret = $this->redis->hMGet($key,$valuearr);
        if($ret)
            array_walk($ret, '$this->unserializedata');
        return $ret;
    }
    public function hGetAll($key)
    {
        $ret = $this->redis->hGetAll($key);
        if($ret)
            array_walk($ret, '$this->unserializedata');
        return $ret;
    }

    public function hGet($key,$mkey)    //get hash里面的子健
    {
        $value = $this->redis->hGet($key,$mkey);
        $value = $this->unserializedata($value);
        return $value;
    }
    public function hSet($key,$mkey,$value)    //set hash里面的子健
    {
        $value = $this->serializedata($value);
        return $this->redis->hSet($key,$mkey,$value);
    }
    public function hSetNx($key,$mkey,$value)    //setxn hash里面的子健
    {
        $value = $this->serializedata($value);
        return $this->redis->hSetNx($key,$mkey,$value);
    }


    //sAdd ...... stored set结构，无序stored
    public function sAdd($key,$value)
    {
        $value = $this->serializedata($value);
        return $this->redis->sAdd($key,$value);
    }
    public function sRemove($key,$value)
    {
        return $this->redis->sRem($key,$value);
    }
    public function sMove($key,$key1,$value) //将$value从$key移到$key1
    {
        $value = $this->serializedata($value);
        return $this->redis->sMove($key,$key1,$value);
    }
    public function sContains($key,$value)
    {
        return $this->redis->sIsMember($key,$value);
    }
    public function sCard($key)
    {
        return $this->redis->sSize($key);
    }


    public function sGetMembers($key)
    {
        return $this->redis->sMembers($key);
    }

    //有序集(Sorted Set)
    public function zAdd($key,$score,$value)
    {
        $value = $this->serializedata($value);
        return $this->redis->zAdd($key,$score,$value);
    }
    public function zRem($key,$value)
    {
        $value = $this->serializedata($value);
        return $this->redis->zRem($key,$value);
    }
    public function zRevRangeByScore($key,$start,$end,$options=[])
    {
        $ret = $this->redis->zRangeByScore($key,$start,$end,$options);
        return array_reverse($ret);
        //这里测出来有问题，得用php数组倒排解决
    }

}
