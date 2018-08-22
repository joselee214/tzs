<?php
namespace j7tools;

class j7debug
{
    public static function debug($v,$k = null,$type='log')
    {
        return self::instance()->_debug($v,$k,$type);
    }
    public static function trace($v,$k = null)
    {
        return self::instance()->_debug($v,$k,'trace');
    }
    public static function config($v,$k=null)
    {
        return self::instance()->setConfig($v,$k);
    }

    private static $instance=null;
    public static function instance()
    {
        if( is_null(self::$instance) )
            self::$instance = new j7debug();
        return self::$instance;
    }

    protected $_debug_config='FirePHP,ChromePhp';   //FirePHP,ChromePhp,var_dump,filelog
    protected $_isdebug=false;

    // BACKTRACE_LEVEL trace时候跳过几个层级,如果自己封了用法的话,要调整这个,看起来好看点
    // BACKTRACE_DEEP  trace时候跟踪的深度,也就是好看点而已
    protected $config = array('BACKTRACE_LEVEL'=>2,'BACKTRACE_DEEP'=>4);

    public function __construct()
    {
        $this->init();
    }
    private function init()
    {
        if(defined('J7_DEBUG_CONFIG'))
            $this->_debug_config = J7_DEBUG_CONFIG;

        if( isset($_SERVER['HTTP_X_FIREPHP']) && strpos($this->_debug_config,'FirePHP')!==false )
        {
            $this->_isdebug = true;
            require_once __DIR__ . '/helper/FirePHP.class.php';
            helper\FirePHP::getInstance(true)->setEnabled(true);
            helper\FirePHP::getInstance(true)->setOption('BACKTRACE_LEVEL',$this->config['BACKTRACE_LEVEL']);
            helper\FirePHP::getInstance(true)->setOption('BACKTRACE_DEEP',$this->config['BACKTRACE_DEEP']);
        }
        if(  strpos($this->_debug_config,'ChromePhp')!==false )
        {
            $this->_isdebug = true;
            require_once __DIR__ . '/helper/ChromePhp.php';
        }
        if(  strpos($this->_debug_config,'var_dump')!==false )
        {
            $this->_isdebug = true;
        }
    }

    public function setConfig($value,$key='_debug_config')
    {
        $this->$key = $value;
        $this->init();
    }

    public function _debug($info,$key = 'Debug:',$showp='log')
    {
        if( $this->_isdebug )
        {
            $showp = $showp?:'trace';
            $showp = strtolower($showp); //默认应该是log

            if( !in_array($showp,array('dump','trace','log','info','error','warn')) ){ $showp='trace'; }

            if( isset($_SERVER['REQUEST_URI']) )
            {
                if(  isset($_SERVER['HTTP_X_FIREPHP']) && strpos($this->_debug_config,'FirePHP')!==false )
                {
                    helper\FirePHP::getInstance(true)->$showp($info, $key);
                }
                if(  strpos($this->_debug_config,'ChromePhp')!==false )
                {
                    $showpchrome = $showp; //默认应该是log
                    if (!in_array($showpchrome, array('log', 'info', 'error', 'warn'))) {
                        $showpchrome = 'warn';
                    }
                    helper\ChromePhp::$showpchrome($key, $info, $this->config['BACKTRACE_LEVEL']-1);
                }
            }
            if(  strpos($this->_debug_config,'var_dump')!==false )
            {
                echo $key.PHP_EOL;
                var_dump($info);
            }
            if(  strpos($this->_debug_config,'filelog')!==false )
            {
                echo $key.PHP_EOL;
                var_dump($info);
            }
        }
    }
}