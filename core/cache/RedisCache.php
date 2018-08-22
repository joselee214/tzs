<?php
require_once __DIR__.'/../RedisAdapter/coreRedisData.php';

class RedisCache extends J7Cache
{
    /**
     * @var RedisFactory
     */
    private $_ins = null;

    public function __construct()
    {
    }

    public function set($key, $value, $limit = 2592000)
    {
        //$ret = $this->_ins->set($key, $value, $limit);
        return $this->_ins->set($key, $value, $limit);
    }
    public function get($key,$perstring='')
    {
        //$data = $this->_ins->get($key,$perstring='');
        return $this->_ins->get($key,$perstring='');
    }
    public function del($key)
    {
        return $this->_ins->del($key);
    }
    public function getConn($sid=0)
    {
        $this->_ins = coreRedisData::instance(null,null,$sid);
    }
}