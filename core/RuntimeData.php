<?php
class RuntimeData
{
    static private $_registry = []; //全局保存的对象
    static private $_data = []; //全局保存的数据//二维结构
    static private $_request = []; //全局的get/post/等 数据
    static private $_responseClosures = null; //设置response的闭包

    static public function _setRequest($type,$data=null)
    {
        if( $type == 'responseClosures' )
            self::$_responseClosures = $data;
        else
            self::$_request[$type] = $data;
    }
    static public function request($type=null)
    {
        if( $type ){
            return isset(self::$_request[$type])?self::$_request[$type]:[];
        }
        return self::$_request;
    }

    //截获 setcookie // setsession
    static public function response()
    {
        if( !is_null(self::$_responseClosures) &&  self::$_responseClosures instanceof Closure )
            return call_user_func_array(self::$_responseClosures,func_get_args());
        return call_user_func_array('RuntimeData::_response',func_get_args());
    }

    static public function _response()
    {
        $argvs = func_get_args();
        if( $argvs && isset($argvs[0]) && is_string($argvs[0]) )
        {
            $func = array_shift($argvs);
            if( ($func=='session' || $func=='setsession') && isset($argvs[0]) && isset($argvs[1]) )
                return $_SESSION[$argvs[0]] = $argvs[1];
            if( $func=='cookie')
                $func = 'setcookie';
            if( $func=='setheader')
                $func = 'header';
            if( function_exists($func) )
                return call_user_func_array($func,$argvs);
        }
        throw new coreException('not support @RuntimeData::response :'.var_export(func_get_args(),true));
    }

    static public function cleardata()
    {
        self::$_data = [];
    }

    static public function set()
    {
        if( func_num_args()<2 )
            throw new Exception('params must bigger than 1');
        $args=func_get_args();
        $index = $args[0];
        if( !isset(self::$_data[$index]) )
            self::$_data[$index] = [];
        if( func_num_args()==3 )
        {
            self::$_data[$index][$args[1]] = $args[2];
        }
        else
        {
            self::$_data[$index][] = $args[1];
        }
    }
    static public function get($index,$dataindex=null,$default=null)
    {
        if( $dataindex )
            return (isset(self::$_data[$index])&&isset(self::$_data[$index][$dataindex]))?self::$_data[$index][$dataindex]:$default;
        else
            return isset(self::$_data[$index])?self::$_data[$index]:$default;
    }
    static public function del($index,$dataindex=null)
    {
        if( $dataindex )
        {
            if( isset(self::$_data[$index]) && isset(self::$_data[$index][$dataindex]) )
                unset(self::$_data[$index][$dataindex]);
        }
        else
        {
            if( isset(self::$_data[$index]) )
                unset(self::$_data[$index]);
        }
    }

    //取出全局对象
    static public function registry($name=null)
    {
        if ($name === null) {
            $registry = [];
            foreach (self::$_registry as $name=>$obj) {
                $registry[$name] = get_class($obj);
            }
            return $registry;
        }
        if (!is_string($name)) {
            throw new J7Exception('First argument $name must be a string, or null to list registry.');
        }
        if (!array_key_exists($name, self::$_registry)) {
            return null;
        }
        return self::$_registry[$name];
    }
    static public function isRegistered($name)
    {
        return isset(self::$_registry[$name]);
    }
    //保存全局对象
    static public function register($name, $obj , $canntreplace=true)
    {
        if (!is_string($name)) {
            throw new J7Exception('First argument $name must be a string.');
        }
        //重复检查
        if( $canntreplace )
        {
            if (array_key_exists($name, self::$_registry)) {
               throw new J7Exception("Object named '$name' already registered.  Did you mean to call registry()?");
            }
            $e = '';
            foreach (self::$_registry as $dup=>$registeredObject) {
                if ($obj === $registeredObject) {
                    $e = "Duplicate object handle already exists in the registry as \"$dup\".";
                    break;
                }
            }
            if ($e) {
                throw new J7Exception($e);
            }
        }
        self::$_registry[$name] = $obj;
    }
}