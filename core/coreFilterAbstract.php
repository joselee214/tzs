<?php
abstract class coreFilterAbstract implements Filter_Interface
{
	/**
	 * @param ActionToken|bool $ret
	 * @return ActionToken|bool
	 */
	public function routeStartup($ret=null)
	{
		return $ret;
	}
	public function routeShutdown($actionToken)
	{
	    return $actionToken;
	}
	public function preDispatch($action)
	{
	}
	public function postDispatch($action)
	{
	}
	public function dispatchShutdown($dispatchReturn)
	{
		return $dispatchReturn;
	}
	public function endReturn($dispatchReturn)
	{}
}


interface Filter_Interface
{
	public function routeStartup($ret);
	public function routeShutdown($actionToken);
	public function preDispatch($action);
	public function postDispatch($action);
	public function dispatchShutdown($dispatchReturn);
	public function endReturn($dispatchReturn);
}