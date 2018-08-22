<?php
class J7Dispatcher implements Dispatcher_Interface
{
    public $_actionname;
    public static function _getClass($actionname)
    {
        return strpos($actionname,-7,7)=='_Action'?$actionname:$actionname.'_Action';
    }

    /**
     * @param ActionToken $dispatcherToken
     * @param $front_filters
     * @return array
     * @throws Exception
     * @throws J7Exception
     */
    public function _dispatch(ActionToken $dispatcherToken,$front_filters)
    {
        try
        {
            //return self::run($dispatcherToken->getAction(),$dispatcherToken->getMethod(),$dispatcherToken->getParams(),$dispatcherToken,$front_filters);
            return self::run($dispatcherToken,$front_filters);
        }catch (J7Exception $e) {
            throw $e;
        }
    }


    /**
     * @param Action $action
     * @param $method
     * @param $params
     * @param ActionToken $dispatcherToken
     * @param null $front_filters
     * @return array
     * @throws coreException
     */
    public static function run(ActionToken $dispatcherToken,$front_filters=null)
    {
        $action = $dispatcherToken->getAction();
        if( $action instanceof Action || $action instanceof AOPProxyControllerModel)
        {
            try
            {
                $result = self::invoke($dispatcherToken,$front_filters);
                if( $result instanceof ActionToken )
                {
                    return self::run($result,$front_filters);
                }
                return array($result,$action);
            }
            catch( BizException $e )
            {
                $token = $e->exToken;
                return self::run($token,$front_filters);
            }
        }
        throw new coreException('Argument 1 passed to J7Dispatcher::run() must be an ActionToken');
    }

    //无需参数直接执行
    /**
     * @param Action $action
     * @param string $method
     * @param $params
     * @param null $front_filters
     * @return mixed
     * @throws J7Exception
     * @throws coreException
     */
    public static function invoke(ActionToken $dispatcherToken,$front_filters=null)
    {
        $action = $dispatcherToken->getAction();
        $method = $dispatcherToken->getMethod();
        $params = $dispatcherToken->getParams();
        if( $action instanceof Action || $action instanceof AOPProxyControllerModel)
        {
            try
            {
                //内部记录
                RuntimeData::set('DoingAction', $dispatcherToken->getActionName());
                debug(get_class($action).'::'.$method,'执行Action::','info');
                RuntimeData::set('DoingActionMethod', $method);
                RuntimeData::set('DoingActionParams', $params);

                if( $front_filters && $front_filters instanceof coreFilterBroker )
                {
                    $front_filters->preDispatch($action);
                }

                $action->__j7construct();

                //validate //验证入参
                $action->__runingMethod = $method;
                if( ($action->__methodExists('_validate') && $action->_validate($method,$params)===false )
                    || self::validate($action,$params,$method) === false  )
                {
                    $ret = $action->_error($method);
                }
                elseif( !$action->__methodExists($method) && $action->__methodExists('__empty') )
                {
                    $ret = $action->__empty($method,$params);
                }
                else
                {
                    $ret = $action->$method();
                }
                $action->__j7destruct();

                RuntimeData::del('DoingActionMethod', 'now');

                if( $front_filters && $front_filters instanceof coreFilterBroker )
                {
                    $front_filters->postDispatch($action);
                }
                return $ret;
            }
            catch( PDOException $e )
            {
                throw new J7Exception( 'PDOException:'.$e->getMessage() );
            }
            catch( J7Exception $e )
            {
                throw $e;
            }
        }
        throw new coreException('Argument 1 passed to J7Dispatcher::invoke() must be an instance of Action or AOPProxyControllerModel');
    }


    public function dispatch($token,$front_filters=null)
    {
        return $this->_dispatch($token, $front_filters);
    }

    /**
     * 验证参数
     */
    public static function validate(Action $action,$params,$configRuleMethod='execute')
    {
        if( $action->__methodExists('_validate_config') )
        {
            $validateConfig = $action->_validate_config();
            if( $validateConfig )
            {
                if( isset($validateConfig[$configRuleMethod]) )
                    $rules = $validateConfig[$configRuleMethod];
                elseif( isset($validateConfig['__all__']) )
                    $rules = $validateConfig['__all__'];
                if( isset($rules) && $rules )
                    return J7Validate::instance()->clearErrors()->validate($params,$rules);
            }
        }
        return null;
    }

    /**
     * //获得返回。。。通过这里统一集中处理。。//暂时不作独立层//
     *
     * @param array[string,Action] $dispatchReturn
     * @param null|array $_resulttype
     * @return mixed|string
     */
    public static function ProcessReturn($dispatchReturn,$_resulttype=null,$isecho=true)
    {
        /**
         * @var Action $retAction
         */
        list($actionReturn,$retAction) = $dispatchReturn;

        $retType = self::getActRetType($retAction,$_resulttype);

        $actConf = $retAction->__getConfig();

        $retTypeHander = isset($actConf[$retType])?$actConf[$retType]['type']:'view';

        if( $_resulttype && isset($_resulttype[1]) && is_array($_resulttype[1]) )
        {
            $handerMap = $_resulttype[1];
            $retTypeHander = isset($handerMap[$retTypeHander])?$handerMap[$retTypeHander]:$retTypeHander;
        }
        /**
         * @var ViewResultType $processor
         */
        $processor = J7ResultType::instance($retTypeHander);
        return $processor->process($retType, $retAction, $actionReturn,null,$isecho);
    }

    public static function getActRetType($retAction,$_resulttype=null)
    {
        $retType = $retAction->_getResultType(); //action的返回
        $retTypeConf = ($_resulttype&&isset($_resulttype[0]))?$_resulttype[0]:null;
        return $retType?:($retTypeConf?:'default');
    }
}