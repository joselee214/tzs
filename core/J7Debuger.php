<?php

class J7Debuger implements coreJ7Debuger
{
    protected $_debug_config;
    protected $_isdebug=false;
    public function __construct()
    {
        $debugConfig =  config('_j7_system_debug');
        $this->_debug_config = $debugConfig['hander'];
        if( $debugConfig['on'] )
        {
            $this->_isdebug = true;
        }
    }

    /**
     * @static
     * @return J7Debuger
     */
    public static function instance()
    {
        return FactoryObject::Instance('J7Debuger',[]);
    }

    public function log($info,$type='biz',$key=null)
    {
        $info = is_scalar($info)?$info:print_r($info,true);
        $info = substr($info,0,1000);
        return $this->debug($info,$key);
    }

    public function error($info, $type='error',$key='error')
    {
        if ( $type=='exception' &&  RuntimeData::registry('SYS_ENV') != 'prod' )
        {
            print_r( $info ,false );
//            print_r( debug_backtrace(0,6) );
        }
        else
            $this->debug($info,$key,'error');
        return false;
    }

    public function debug($info,$key = 'debug:',$showp='log')
    {
        if( $this->_isdebug )
        {
            echo '<br/>';
            echo '---------------';
            echo '<br/>';
            echo 'DebugInfo:'.$key.':';
            var_dump($info);
            echo '<br/>';
            echo '---------------';
            echo '<br/>';
        }
        return true;
    }

}


interface coreJ7Debuger
{
	public function log($info,$type,$key=null);
    public function error($info,$type,$key);
	public function debug($info,$key,$showp);
}


class debugData implements ArrayAccess
{
	/**
	 * @param null $datatype
	 * @return self
	 */
    public static function instance($datatype=null)
    {
        return FactoryObject::Instance('debugData',[$datatype]);
    }

    public function __construct()
    {
    }

    private $_debug = [];
    function offsetExists($index)
    {
        return isset($this->_debug[$index]);
    }
    function offsetGet($index) {
        return isset($this->_debug[$index]) ? $this->_debug[$index] : null;
    }
    function offsetSet($index, $newvalue) {
        if ( $index != '' )
        {
            $this->_debug[$index][] = $newvalue;
        }
        else
        {
            $this->_debug[] = $newvalue;
        }
    }
    function offsetUnset($index) {
        unset($this->_debug[$index]);
    }
}