<?php
/**
 * Zend Framework
 */
class Db_Profiler_Query
{
    protected $_query ='';

    protected $_queryType = 0;

    protected $_startedMicrotime = null;

    protected $_endedMicrotime = null;

    public function __construct($query, $queryType)
    {
        $this->_query = $query;
        $this->_queryType = $queryType;
        $this->_startedMicrotime = microtime(true);
        return true;
    }

    public function end()
    {
        $this->_endedMicrotime = microtime(true);
        return true;
    }


    public function hasEnded()
    {
        return ($this->_endedMicrotime != null);
    }

    public function getQuery()
    {
        return $this->_query;
    }

    public function getQueryType()
    {
        return $this->_queryType;
    }

    public function getElapsedSecs()
    {
        if (is_null($this->_endedMicrotime)) {
            return false;
        }

        return ($this->_endedMicrotime - $this->_startedMicrotime);
    }
}

