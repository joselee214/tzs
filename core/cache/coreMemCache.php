<?php
class coreMemCache extends J7Cache
{
    private $memcache = null;
    protected $memcacheserver = 0;
    protected $_compress = 1;
    private $isd=true;

    public function __construct($group=0)
    {
        $this->memcacheserver = $group;
    }

    public function setCompress($compress=1)
    {
        $this->_compress = $compress;
    }

    //此方法分段获取memcache值，防止memcache过载
    private function mget($key)
    {
        $splitnum = 200;
        if (count($key) > $splitnum) {
            $ret = [];
            for ($sk = 0, $al = ceil(count($key) / $splitnum); $sk < $al; ++$sk)
            {
                $ikey = array_slice($key, $sk * $splitnum, $splitnum);
                $ret += $this->memcache->get($ikey);
            }
            return $ret;
        }
        else
            $rtn = $this->memcache->get($key);
            return $rtn;
    }
    public function get($key, $perstring = '')
    {
        if (is_null($this->memcache)) {
            $this->getConn($this->memcacheserver);
        }
        $perstring = $this->cache_prefix . $perstring;
        if (is_string($key)) {
            $v = $this->memcache->get($perstring . $key);
            return $this->changeout($v, 'out');
        }
        elseif (is_array($key) && $perstring)
        {
            $newa = $newout = [];
            $lenpre = strlen($perstring);
            foreach ($key as $ek)
            {
                $newa[] = $perstring . $ek;
            }
            $out = $this->mget($newa);
            if ($out) {
                foreach ($out as $ek => $v)
                {
                    $v = $this->changeout($v, 'out');
                    $newout[substr($ek, $lenpre)] = $v;
                }
            }
            return $newout;
        }
        elseif (is_array($key))
        {
            $out = $this->mget($key);
            $newout = [];
            if ($out) {
                foreach ($out as $ek => $v)
                {
                    $v = $this->changeout($v, 'out');
                    $newout[$ek] = $v;
                }
            }
            return $newout;
        }
        else
        {
            $v = $this->memcache->get($perstring . $key);
            return $this->changeout($v, 'out');
        }
    }
    public function set($key, $value, $limit = 2592000) //2160000)
    {
        if (is_null($this->memcache)) {
            $this->getConn($this->memcacheserver);
        }
        if ($value) {
            $value = $this->changeout($value, 'in');
            if (!$value)
                return $this->del($key);
        }
        if( $this->isd )
        {
            if ($this->memcache->replace($this->cache_prefix . $key, $value, $limit) || $this->memcache->set($this->cache_prefix . $key, $value, $limit)) {
                return true;
            }
            else
            {
                if ($this->memcache->set($this->cache_prefix . $key, $value, $limit)) {
                    return true;
                }
                else {
                    return $this->del($key);
                }
            }
        }
        else
        {
            if ($this->memcache->replace($this->cache_prefix . $key, $value, MEMCACHE_COMPRESSED, $limit) || $this->memcache->set($this->cache_prefix . $key, $value,MEMCACHE_COMPRESSED, $limit)) {
                return true;
            }
            else
            {
                if ($this->memcache->set($this->cache_prefix . $key, $value, MEMCACHE_COMPRESSED, $limit)) {
                    return true;
                }
                else {
                    return $this->del($key);
                }
            }
        }
    }
    public function del($key)
    {
        if (is_null($this->memcache)) {
            $this->getConn($this->memcacheserver);
        }
        return $this->memcache->delete($this->cache_prefix . $key);
    }
    public function delete($k)
    {
        return $this->del($k);
    }

    protected function changeout($value, $changetype)
    {
//        if ($changetype == 'in') {
//            if ($value === [])
//                return 'J7PHPFCSxzpA2ucmu_array';
//            elseif ($value === '')
//                return 'J7PHPFCSxzpA2ucmu_string';
//            elseif ($value === 0)
//                return 'J7PHPFCSxzpA2ucmu_int';
//            elseif ($value === null)
//                return 'J7PHPFCSxzpA2ucmu_null';
//            elseif ($value === false)
//                return 'J7PHPFCSxzpA2ucmu_false';
//            elseif ( is_array($value) || is_object($value) )
//                return 'J7PHPFCSxzpA2ucmu_obj'.igbinary_serialize($value);
//            else
//                return $value;
//        }
//        elseif ($changetype == 'out')
//        {
//            if (is_string($value)) {
//                if (strlen($value) < 30) {
//                    if ($value === 'J7PHPFCSxzpA2ucmu_array')
//                        return [];
//                    elseif ($value === 'J7PHPFCSxzpA2ucmu_string')
//                        return '';
//                    elseif ($value === 'J7PHPFCSxzpA2ucmu_int')
//                        return 0;
//                    elseif ($value === 'J7PHPFCSxzpA2ucmu_null')
//                        return null;
//                    elseif ($value === 'J7PHPFCSxzpA2ucmu_false')
//                        return false;
//                }
//                if( strpos($value,'J7PHPFCSxzpA2ucmu_obj')===0 )
//                    return igbinary_unserialize(substr($value,21));
//                return $value;
//            }
//        }
//        return $value;
        if( $this->_compress )
        {
            if( function_exists('igbinary_serialize') )
                return $changetype=='in'?igbinary_serialize($value):(is_scalar($value)?igbinary_unserialize($value):$value);
            else
                return $changetype=='in'?serialize($value):(is_scalar($value)?unserialize($value):$value);
        }
        return $value;
    }
    //长连接模式下无需断开
//    public function __destruct()
//    {
//        if (isset($this->memcache))
//            $this->memcache->close();
//    }

    public function getConn($sid=0)
    {
        try {

            $CacheConfig = config('memcache');
            $groupConfig = config('group');
            $this->cache_prefix = config('cache_perfix');
            $server = $groupConfig['memcache'][$this->memcacheserver];

            if( extension_loaded('Memcache') )
            {
                $this->isd=false;
                $this->memcache = new Memcache;
                foreach ($CacheConfig[$server] as $eachmv)
                {
                    $this->memcache->addServer($eachmv['host'], $eachmv['port'], isset($eachmv['persistent'])?$eachmv['persistent']:true, isset($eachmv['weight'])?$eachmv['weight']:null);
                }
                $this->memcache->setCompressThreshold(20000, 0.2);
            }
            elseif( extension_loaded('Memcached') )
            {
                $this->isd=true;
                $this->memcache = new Memcached;
                foreach ($CacheConfig[$server] as $eachmv)
                {
                    $this->memcache->addServer($eachmv['host'], $eachmv['port'], isset($eachmv['weight'])?$eachmv['weight']:0);
                }
                $this->memcache->setOption(Memcached::OPT_COMPRESSION, true);
                if( $this->memcache->getVersion() === false )
                    throw new J7Exception('Memcached connect error');
            }
            else
            {
                throw new J7Exception('Cannt Find extension Memcached or Memcache');
            }
            return 1;
        }
        catch (Exception $e)
        {
            throw new coreException('Could not load the Memcache ServerId:'.$sid.' : '.$e->getMessage());
        }
    }

    function getConnFromConfig($config) {
        if( extension_loaded('Memcache') )
        {
            $this->isd=false;
            $this->memcache = new Memcache;
            foreach ($config as $eachmv)
            {
                $this->memcache->addServer($eachmv['host'], $eachmv['port'], true);
            }
            $this->memcache->setCompressThreshold(20000, 0.2);
        }
        elseif( extension_loaded('Memcached') )
        {
            $this->isd=true;
            $this->memcache = new Memcached;
            foreach ($config as $eachmv)
            {
                $this->memcache->addServer($eachmv['host'], $eachmv['port'], true);
            }
            $this->memcache->setOption(Memcached::OPT_COMPRESSION, true);
        }
        else
        {
            throw new J7Exception('Cannt Find extension Memcached or Memcache');
        }

        return 1;
    }
}