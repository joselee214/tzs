<?php
require_once __DIR__ . '/J7Config.php';

abstract class J7Cache implements CacheInterface
{
    public $cache_prefix='';
    /**
     * @return CacheInterface
     */
    public static function instance($instancename = 'J7Cache', $class = 'J7Cache', $servergroup = 0)
    {
        $instancename = $instancename?:'J7Cache';
        $class = $class?:'J7Cache';
        return FactoryObject::Instance($class,[$servergroup],function ($instance) use ($servergroup) {
            $instance->getConn($servergroup);
        });
    }
}


interface CacheInterface
{
    public function set($key, $value, $limit = 2592000);
    public function get($key,$perstring='');
    public function del($key);
    public function getConn($sid=0);
}