<?php
require_once __DIR__ . '/coreFilterAbstract.php';

class coreFilterBroker extends coreFilterAbstract
{
    protected $_filters = [];
    public function registerFilter(Filter_Interface $filter)
    {
        if (array_search($filter, $this->_filters, true) !== false) {
            throw new J7Exception('Filter already registered.');
        }
        $this->_filters[] = $filter;
        return $this;
    }
    public function unregisterFilter(Filter_Interface $filter)
    {
        $key = array_search($filter, $this->_filters, true);
        if ($key===false) {
            throw new J7Exception('Filter never registered.');
        }
        unset($this->_filters[$key]);
        return $this;
    }
	public function routeStartup($ret=null)
	{
	    foreach ($this->_filters as $filter) {
			$ret = $filter->routeStartup($ret);
	    }
		return $ret;
	}
	public function routeShutdown($actionToken)
	{
	    foreach ($this->_filters as $filter) {
			$actionToken = $filter->routeShutdown($actionToken);
	    }
	    return $actionToken;
	}
	public function preDispatch($action)  //TODO:可以并行处理
	{
		array_walk($this->_filters,function($filter,$k,$action){$filter->preDispatch($action);},$action);
	}
	public function postDispatch($action) //TODO:可以并行处理
	{
		array_walk($this->_filters,function($filter,$k,$action){$filter->postDispatch($action);},$action);
	}
	public function dispatchShutdown($dispatchReturn)
	{
	    foreach ($this->_filters as $filter) {
		    $dispatchReturn = $filter->dispatchShutdown($dispatchReturn);
	    }
		return $dispatchReturn;
	}
	public function endReturn($dispatchReturn)  //TODO:可以并行处理
	{
		array_walk($this->_filters,function($filter,$k,$dispatchReturn){$filter->endReturn($dispatchReturn);},$dispatchReturn);
    }
}