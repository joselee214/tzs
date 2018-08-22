<?php
require_once J7SYS_CORE_DIR.'/J7Debuger.php';

class Debuger extends J7Debuger
{

    public function __construct()
    {
        parent::__construct();

        if( php_sapi_name() != 'cli' && class_exists('j7tools\j7debug') )
        {
            j7tools\j7debug::config($this->_debug_config,'_debug_config');
            j7tools\j7debug::config(['BACKTRACE_LEVEL'=>4,'BACKTRACE_DEEP'=>5],'config');
        }
    }

    public function debug($value,$key = 'debug:',$type='log')
    {
        if( $this->_isdebug )
        {
            if( php_sapi_name() != 'cli' && class_exists('j7tools\j7debug'))
                j7tools\j7debug::debug($value,$key,$type);
            elseif (isset($_GET['debug']) && $_GET['debug'] ) {
                return parent::debug($value,$key,$type);
            }
        }
    }

    public function log($info, $type = '', $key = null)
    {
        return $this->debug($info,$key,'log');
    }
//    public function error($info, $type = '', $key = null)
//    {
//        return $this->debug($info,$key,'error');
//    }
}