<?php
require_once __DIR__ . '/RedisFactory.php';

class coreRedisData
{
    private $redis = null;
    private $redis_write = null;
    private static $_instance = null;

    public function getConn($sid = 0 , $redisdb=null)
    {
        try {
            $CacheConfig = config('redis');

            $cache_prefix = config('cache_perfix_redis');
            if(!$cache_prefix){$cache_prefix='';}
            if( !is_null($redisdb) && is_int($redisdb) )
                $CacheConfig[$sid]['source'] = $redisdb;

            if( isset($CacheConfig[$sid]['write']) && isset($CacheConfig[$sid]['read']) )
            {
                $this->redis_write = RedisFactory::factory($CacheConfig[$sid]['write'],$cache_prefix);
                $this->redis = RedisFactory::factory($CacheConfig[$sid]['read'],$cache_prefix);
            }
            else
            {
                $this->redis_write = $this->redis = RedisFactory::factory($CacheConfig[$sid]['source'],$cache_prefix);
            }
            return true;
        }
        catch (RedisException $e)
        {
            return false;
        }
        catch (Exception $e)
        {
            throw new coreException('Could not load the redis ServerId:'.$sid);
        }
    }

    public function __call($method, $params)
    {
        if( $this->redis_write instanceof RedisFactory  && $this->redis_write->_isconnect)
        {
            if( in_array($method,array('set','setnx','mset','hSet','hSetNx','sAdd','sMove','zAdd','zRem') ) )
            {
                $rtn = call_user_func_array(array($this->redis_write, $method), $params);
            }
            else
            {
                $rtn = call_user_func_array(array($this->redis, $method), $params);
            }
            return $rtn;
        }
        debug('redis not connected !');
        return false;
    }

    /**
     * @static
     * @param null $uid
     * @param null $servergroup
     * @param null $instancename
     * @param null $class
     * @return RedisFactory
     * @throws J7Exception
     */
    public static function instance($instancename = null, $class = null , $servergroup = null, $redisdb=null)
    {
        if (is_null($instancename)) { $instancename = 'coreRedisData'; }
        if (is_null($class)) { $class = 'coreRedisData'; }

        if( !is_null($redisdb) && is_int($redisdb) )
            $pstr = '_' . $servergroup.'_'.$redisdb;
        else
            $pstr = '_' . $servergroup;

        $insancenameset = $instancename . $pstr;

        //替代cache
        $cacheClass = config('_j7_system_'.$instancename);

        if( $cacheClass ){ $insancenameset = $cacheClass . '_' . $pstr; }

        if (!isset(self::$_instance[$insancenameset])) {
            if( isset($cacheClass) && $cacheClass ){ $class = $cacheClass; }
            if (!class_exists($class)) {
                $cachefile = __DIR__ . '/cache/' . $class . '.php';
                if (!file_exists($cachefile)) {
                    throw new J7Exception("Could not find Cache file: $cachefile .");
                }
                require_once $cachefile;
            }
            if (!class_exists($class)) {
                throw new J7Exception("Could not find class '$class' in file $cachefile .");
            }
            $tmp = new $class();

            $tmp->getConn($servergroup,$redisdb);
            self::$_instance[$insancenameset] = $tmp;
        }
        return self::$_instance[$insancenameset];
    }
}