<?php
class AOPAspectInject
{
    static $_config = [];

    public static function aopWeaving(baseControllerModel $injectObject,$app='')
    {
        if( empty(self::$_config) )
        {
            self::$_config = config('_j7_system_aop_config');
            self::$_config['aops'] = (isset(self::$_config['aops']) && !empty(self::$_config['aops']))?self::$_config['aops']:[];
            self::$_config['exclude'] = (isset(self::$_config['exclude']) && !empty(self::$_config['exclude']))?self::$_config['exclude']:[];
        }

        $property = get_class($injectObject);
        $propertyWithApp = ($app?$app.':':'').$property;
        if( self::$_config['on'] &&
            !in_array($property,self::$_config['exclude']) &&
            !in_array($propertyWithApp,self::$_config['exclude']) &&
            ( !empty(self::$_config['aops']) || isset(self::$_config[$property]) || isset(self::$_config[$propertyWithApp]) ) )
        {
	        $basicAops = !empty(self::$_config[$propertyWithApp])?self::$_config[$propertyWithApp]:[];
	        if( empty($basicAops) )
		        $basicAops = !empty(self::$_config[$property])?self::$_config[$property]:[];
            $aops = array_merge($basicAops,self::$_config['aops']?:[]);
            $aopProxy = FactoryObject::Instance('AOPProxyControllerModel',[$app,$aops,$injectObject]);
            return $aopProxy;
        }
        else
        {
            return $injectObject;
        }
    }
}


/**
 * 代理被切片的类,通过这个代理实现切片方法
 * Class AOPProxyControllerModel
 */
class AOPProxyControllerModel
{
    private $aops=[];
    private $aopsIns=[];
    /**
     * @var baseControllerModel
     */
    private $injectObject;

    public function __construct($app,$aops,$injectObject)
    {
        $this->aops = $aops;
        $this->injectObject = $injectObject;
        foreach ($this->aops as $eachAopName)
        {
            $aopIns = FactoryObject::Instance($eachAopName,['injectClass'=>$app.':'.get_class($this->injectObject)],function ($instance) {
                /**
                 * @var AOPInterface $instance
                 */
                $this->injectObject = $instance->init($this->injectObject);
            });
            $this->aopsIns[] = $aopIns;
            unset($aopIns);
        }
    }
    
//    public function

    public function __getProxyObject()
    {
        return $this->injectObject;
    }

    public function __set($name,$value)
    {
        return $this->injectObject->$name = $value;
    }
    public function __get($name)
    {
        return $this->injectObject->$name;
    }
    public function __call($name, $arguments)
    {

        if( in_array($name,array('__doInjections','__setInjections','__getInjections','__methodExists','__get_class','_setView','_setLayout','__getConfig','_actionToken','__j7destruct','_getResultType','_setResultType')) )
            return call_user_func_array( array($this->injectObject,$name) , $arguments );

        array_walk($this->aopsIns,function($eachAOPIns,$k,$data){$eachAOPIns->before($data[0], $data[1], $data[2]);},[$this->injectObject, $name, $arguments]);

        try
        {
            $ret = call_user_func_array( array($this->injectObject,$name) , $arguments );
        }
        catch (Exception $e)
        {
            array_walk($this->aopsIns,function($eachAOPIns,$k,$data){$eachAOPIns->throwing($data[0], $data[1], $data[2], $data[3]);},[$e, $this->injectObject, $name, $arguments]);
            throw $e;
        }

        array_walk($this->aopsIns,function($eachAOPIns,$k,$data){$eachAOPIns->after($data[0], $data[1], $data[2], $data[3]);},[$ret, $this->injectObject, $name, $arguments]);
        
        return $ret;
    }
}

interface AOPInterface
{
    public function init(baseControllerModel $obj);     //仅仅init才可以返回,其余方法不需要返回值
    public function before(baseControllerModel $obj,$callmethod,$arguments=[]);
    public function after($ret,baseControllerModel $obj,$callmethod,$arguments=[]);
    public function throwing(Exception $e,baseControllerModel $obj,$callmethod,$arguments=[]);
}