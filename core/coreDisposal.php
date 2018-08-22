<?php
require_once __DIR__.'/baseControllerModel.php';
require_once __DIR__.'/coreFilterBroker.php';
require_once __DIR__.'/Action.php';
require_once __DIR__.'/J7ResultType.php';
require_once __DIR__ .'/J7ActionTag.php';

class coreDisposal
{
	static private $_instance = null;
	private $_router = null;
	private $_dispatcher = null;
	private $_filters = null;
	private $_resulttype = null;
	private function __construct()
	{
		$this->_filters = new coreFilterBroker();
	}

	static public function getInstance()
	{
		if (!self::$_instance instanceof self) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	static public function run($controllerDirectory)
	{
		self::getInstance()->setControllerDirectory($controllerDirectory)->dispatch();
	}
	public function setControllerDirectory($directory)
	{
		$dispatcher = $this->getDispatcher();
		if (!method_exists($dispatcher, 'setControllerDirectory')) {
			throw new J7Exception('Custom dispatcher does not support setting controller directory.');
		}
		$dispatcher->setControllerDirectory($directory);
		return $this;
	}
	public function setRouter(Router_Interface $router)
	{
		$this->_router = $router;
	}

	/**
	 * @return J7Router
	 */
	public function getRouter()
	{
		return $this->_router;
	}
	public function setDispatcher(Dispatcher_Interface $dispatcher)
	{
		$this->_dispatcher = $dispatcher;
		return $this;
	}

    /**
     * @return J7Dispatcher
     */
	public function getDispatcher()
	{
		return $this->_dispatcher;
	}
	public function registerFilter(Filter_Interface $filter)
	{
		$this->_filters->registerFilter($filter);
		return $this;
	}
	public function unregisterFilter(Filter_Interface $filter)
	{
		$this->_filters->unregisterFilter($filter);
		return $this;
	}
	public function setDefaultResuleType($resulttype)
	{
		$this->_resulttype = $resulttype;
		return $this;
	}
	public function dispatch()
	{
        require_once J7SYS_CLASS_DIR .'/basichelp/basic_action.php';
		try
		{
			if( $_ret = $this->_filters->routeStartup(true) )
			{
                if( $_ret instanceof ActionToken )
                    $actionToken = $_ret;
                else
                    $actionToken = $this->getRouter()->route();

                if(file_exists(J7SYS_CLASS_DIR .'/basichelp/'.$actionToken->getApp().'_basic_action.php'))
                    require_once J7SYS_CLASS_DIR .'/basichelp/'.$actionToken->getApp().'_basic_action.php';

				$actionToken = $this->_filters->routeShutdown($actionToken);
				$dispatchReturn = $this->getDispatcher()->dispatch($actionToken,$this->_filters);
				$dispatchReturn = $this->_filters->dispatchShutdown($dispatchReturn);
				$this->ProcessReturn($dispatchReturn);
				$this->_filters->endReturn($dispatchReturn);
			}

		} catch (J7Exception $e) {
			throw $e;
		}
	}


    /**
     * //获得返回。。。通过这里统一集中处理。。//暂时不作独立层//
     *
     * @param array[string,Action] $dispatchReturn
     * @return mixed|string
     */
	public function ProcessReturn($dispatchReturn)
	{
	    return J7Dispatcher::ProcessReturn($dispatchReturn,$this->_resulttype);
	}
}