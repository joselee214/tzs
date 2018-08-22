<?php
//实现缓存挂起的类，主要是get时候先从外面要，set时候先更外面，不直接更新缓存
class EaccCache extends J7Cache
{
    public function get($key,$per=''){
        $key = $this->cache_prefix . $per . $key;
        return eaccelerator_get($key);
    }

    public function set($key, $value, $limit = 2592000){
        $key = $this->cache_prefix . $key;
        return eaccelerator_put($key, $value);
    }

    public function del($key){
        $key = $this->cache_prefix . $key;
        return eaccelerator_rm($key);
    }

    public function getConn($sid=0)
    {
        $this->cache_prefix = config('cache_perfix');
    }
}