<?php
require_once __DIR__.'/AOPAspect.php';
abstract class baseControllerModel implements interfaceControllerModel
{
    public function __construct(){}
    public function __j7construct(){}

    /*
     * ****************************
     * ****************************
     */
    public function __getInjections($run=false)
    {
        $data = get_object_vars($this);
        unset($data['_InjectionsParamStore']);
        return $data;
    }
    public function __setInjections($pro,$inject)
    {
        $this->$pro = $inject;
    }
    public $__applicationName; //action真正所在的app
	public $__actionFullName; //全局名
    private $_InjectionsParamStore = [];
    public function __doInjections($initParams,$outInjections=[]) //$outInjections 强行注入的对象
    {
        $Injections = $this->__getInjections();
        if( $Injections ) {

            if (substr(get_called_class(), -7, 7) === '_Action') {
                $callDaoInAction = config('_j7_system_allow_call_dao_in_action',null,true);
                foreach ($Injections as $pro=>$v) {
                    if ( substr($pro, -7, 7) === 'Service' || ($callDaoInAction && substr($pro, -3, 3) === 'DAO') ) {
                        $this->_InjectionsParamStore[$pro] = $v;
                        unset($this->$pro);
                    }
                }
                $paramInjections = Util::getVars(get_called_class()); //通过外部 注入public变量
                foreach ($paramInjections as $pro=>$v)
                {
                    if (isset($initParams[$pro])) {
                        $this->$pro = $initParams[$pro];       //参数设置
                    }
                }

            } elseif (substr(get_called_class(), -7, 7) === 'Service') {
                foreach ($Injections as $pro=>$v) {
                    if (substr($pro, -3, 3) === 'DAO' || substr($pro, -7, 7) === 'Service') {
                        $this->_InjectionsParamStore[$pro] = $v;
                        unset($this->$pro);
                    }
                }
            } elseif ( substr(get_called_class(), -3, 3) === 'DAO' ) {
                foreach ($Injections as $pro=>$v) {
                    if (substr($pro, -3, 3) === 'DAO') {
                        $this->_InjectionsParamStore[$pro] = $v;
                        unset($this->$pro);
                    }
                }
            }
        }
        if( $outInjections )
            foreach ($outInjections as $ek=>$ev)
            {
                $this->__setInjections($ek,$ev);
            }
    }
    public function __get($k)
    {
        if( key_exists($k,$this->_InjectionsParamStore) )
        {
            if( substr($k,0,1) != '_' && (substr($k, -3, 3) === 'DAO' || substr($k, -7, 7) === 'Service') )
            {
                $ins = FactoryObject::Get( $k,$this->_InjectionsParamStore[$k], $this->__applicationName);
//                return AOPAspectInject::aopInject($this,$k,$ins); //切面注入
                $this->__setInjections($k,$ins);
                return $ins;
            }
        }
        if ( RuntimeData::registry('SYS_ENV') != 'prod' )
        {
            $d = Util::debugTrace(null,1);
            throw new coreException('没有找到 '.$k.' @ '.get_called_class().' 属性!'.( json_encode($d) ) );
        }
        return null;
    }
    public function __methodExists($method)
    {
        return method_exists($this,$method);
    }
    public function __get_class()
    {
        return get_class($this);
    }
}

interface interfaceControllerModel
{
    public function __construct();
    public function __j7construct();
}