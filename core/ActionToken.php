<?php
class ActionToken
{
	/**
	 * @var Action $_action
	 */
	protected $_action=null;
    protected $_app = null;
    protected $_controller     = '';
    protected $_module     = '';
	protected $_method     = null;
    protected $_params     = [];
    protected $_params_protected     = [];

    protected $_requestMethod = 'get';

	public function __construct($module, $controller='', $params=[],$method='execute',$app=null,$params_protected=[])
	{
		$this->setModule($module);
        $this->setController($controller);
		$this->setParams($params);

		$this->setApp($app);
		$this->setParamsProtected($params_protected);

        $this->setMethod($method?$method:'execute');
        $this->setRequestMethod(strtolower(isset($_SERVER['REQUEST_METHOD'])?$_SERVER['REQUEST_METHOD']:'get'));
	}

    public function getRequestMethod()
    {
        return $this->_requestMethod;
    }
    public function setRequestMethod($method)
    {
        return $this->_requestMethod = $method;
        //return $this;
    }

    //ACTION文件检查
    public function checkAction($module,$controller,$eparams,$method)
    {
        $file = J7Config::instance()->getAppDir($this->getApp()).'/'.$module.'/'.$controller.'Action.php';
        if (!file_exists($file))
        {
            // actions.php 方法检查 // $controller 作为方法
            $eparams['_method'] = $method;
            $moduleActionFile = J7Config::instance()->getAppDir($this->getApp()).'/'.$module.'/actions.php';
            if( file_exists($moduleActionFile) )
            {
                $class = FactoryObject::Get($module.'_Action',$eparams,$this->getApp(),$this->getParams(),$this->getParamsProtected());
                if( $class->__methodExists($controller) || $class->__methodExists('__empty') )
                {
                    $this->setController('');
                    $this->setParamsProtected(['__actionController'=>$controller]);
                    $this->setMethod($controller);
                    return true;
                }
            }
            elseif( $module!='emptyModule' )
            {
                if( $this->checkAction('emptyModule',$controller,$eparams,$method) )
                {
                    $this->setModule('emptyModule');
                    $this->setParamsProtected(['__actionModule'=>$module]);
                    return true;
                }
            }
        }
        if( $module!='emptyModule' )
            return true;

        return false;
    }

	public function getAction($reset=false)
	{
		if( !$this->_action )
		{
		    $this->checkAction($this->getModule(),$this->getController(),$this->getParams(),$this->getMethod());
//            throw new coreException('Could not find Action File:'.$module.':'.$controller);
			$this->_action = FactoryObject::Get($this->getActionName().'_Action',$this->getParams(),$this->getApp(),$this->getParams(),$this->getParamsProtected());
			$this->_action->_actionToken($this);
            $this->_action->__requestMethod = $this->getRequestMethod();
		}
		return $this->_action;
	}

	public function resetAction()
    {
        $this->_action = null;
    }

    public function getApp()
    {
        return $this->_app?$this->_app:RuntimeData::registry('SYS_APP');
    }
    public function setApp($app)
    {
        $this->_app = $app;
        $this->resetAction();
        return $this;
    }
	public function getActionName()
	{
		return '/'.$this->getModule().($this->getController()?'/'.$this->getController():'');
	}
    public function getFullActionName()
    {
        return $this->getApp().':/'.$this->getModule().($this->getController()?'/'.$this->getController():'');
    }
	public function getMethod()
	{
		return $this->_method;
	}
	public function setMethod($m)
	{
		$this->_method = $m;
		return $this;
	}

    public function getParamsProtected()
    {
        return $this->_params_protected;
    }
    public function setParamsProtected($paramsArray,$merge=true)
    {
        if (!is_array($paramsArray)) {
            throw new J7Exception('ActionToken : Parameters must be set as an array.');
        }
        if( $merge )
            $this->_params_protected = array_merge($this->_params_protected,$paramsArray);
        else
            $this->_params_protected = $paramsArray;

        return $this;
    }
    public function getParams()
    {
       return $this->_params;
    }
    public function setParams($paramsArray,$merge=true)
    {
        if (!is_array($paramsArray)) {
            throw new J7Exception('ActionToken : Parameters must be set as an array.');
        }
        if( $merge )
            $this->_params = array_merge($this->_params,$paramsArray);
        else
            $this->_params = $paramsArray;

        return $this;
    }
    public function setController($v='')
    {
        $this->_controller = $v;
        $this->resetAction();
        return $this;
    }
    public function setModule($v='')
    {
        $this->_module = $v;
        $this->resetAction();
        return $this;
    }
    public function getController()
    {
        return $this->_controller;
    }
    public function getModule()
    {
        return $this->_module;
    }

    public function setAction($actionName,$params=[],$method=null,$app=null,$module=null,$controller=null)
    {
        list($app,$module,$controller,$method) = self::explodeActionName($actionName,$method,$app,$module,$controller);
        return $this->setApp($app)->setModule($module)->setController($controller)->setMethod($method)->setParams($params);
    }

    static function explodeActionName($actionName,$method=null,$app=null,$module=null,$controller=null)
    {
	    if (substr($actionName, -7, 7) === '_Action')
		    $actionName = substr($actionName,0,-7);
        if( strpos($actionName,':')===false )
        {
            $tagname = $actionName;
            $app = $app?:RuntimeData::registry('SYS_APP');
        }
        else
        {
            $tagname = substr($actionName,strpos($actionName,':')+1);
            $app = substr($actionName,0,strpos($actionName,':'));
        }
        $tagname = trim($tagname,'/');
        $actionClassName = $tagname;
        $submethod = strpos($tagname,'~');
        if( $submethod!==false )
        {
            $actionClassName = substr($tagname,0,$submethod);
            $method = substr($tagname,$submethod+1);
        }
        $delimiter = strpos($tagname,'/')!==false?'/':'_';
        $classNameSplit = explode($delimiter,$actionClassName);
        if( empty($classNameSplit[0]) )
            array_shift($classNameSplit);
        if(!empty($classNameSplit[0]))
            $module = $classNameSplit[0];
        if(!empty($classNameSplit[1]))
            $controller = $classNameSplit[1];
        if($submethod===false && !empty($classNameSplit[2]))
            $method = $classNameSplit[2];
        return [$app,$module,$controller,$method];
    }
}