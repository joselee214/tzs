<?php
require_once __DIR__.'/GlobalFunction.php';
require_once __DIR__.'/RuntimeData.php';

class J7Config
{
    private static $_instance = [];
    private $_config = null;
    private $_configfile_ext = [];
    private $_env;  //环境设置
    private $_configpath = null;   //跟着app走的配置文件地址

    private $loadedFile = [];

    private function __construct($app=null,$env=null)
    {
        $this->setEnv($app,$env);
    }

    public function setEnv($app=null,$env=null)
    {
        $app = $app?:RuntimeData::registry('SYS_APP');
        $this->_env = $env?:RuntimeData::registry('SYS_ENV');
        $this->_configfile_ext = J7SYS_CONFIG_DIR.DIRECTORY_SEPARATOR;
        if( $app!='_core' )
            $this->_configpath = $this->getAppDir($app).DIRECTORY_SEPARATOR.'__configs'.DIRECTORY_SEPARATOR;
    }

    /**
     * @param null $app
     * @return J7Config
     * @throws J7Exception
     */
    public static function instance($app=null,$env=null)
    {
        $app = $app?:RuntimeData::registry('SYS_APP');
        if( is_null($app) )
            $app = '_core';
        if ( !isset(self::$_instance[$app]) )
        {
            self::$_instance[$app] = new J7Config($app,$env);
            if( $app != '_core' && isset(self::$_instance['_core']) )
            {
                unset(self::$_instance['_core']);
            }
        }
        return self::$_instance[$app];
    }

    public function getAppDir($name)
    {
        $name = trim($name,'/\\');
        $config = $this->get('_j7_system_application_dir','appmap.php',false);
        if( $config && isset($config[$name]) && $config[$name] )
        {
            return $config[$name];
        }
        return J7SYS_APPLICATION_DIR.DIRECTORY_SEPARATOR.$name;
    }

    public function get($index=null, $config_file = null , $mustLoad=true)
    {
        if( empty($index) )
        {
            return $this->loadConfigFile($config_file,$mustLoad);
        }
        if ( empty($index) || !is_array($this->_config) || !array_key_exists($index, $this->_config) )
        {
            $this->loadConfigFile($config_file,$mustLoad);
        }

        if ( isset($this->_config[$index]) )
        {
            return $this->_config[$index];
        }
        else
        {
            return null;
        }
    }

    public function _set($index,$value)
    {
        return $this->_config[$index] = array_merge(isset($this->_config[$index])?$this->_config[$index]:[],$value);
    }
    public function _replace($index,$value)
    {
        return $this->_config[$index] = $value;
    }


    private function _getConfile($file = null)
    {
        if( substr($file,-4)!='.php' )
        {
            $file .= '.php';
        }
        return $file;
    }
    private function _getConfileWithEnv($file = null)
    {
        if ( $this->_env )
        {
            $file_withoutext = $file;
            if( substr($file,-4)=='.php' )
            {
                $file_withoutext = substr($file,0,-4);
            }
            return $file_withoutext.'_'.$this->_env.'.php';
        }
        return $file;
    }

    public function loadConfigFileWithApp($app,$config_file = null,$mustLoad=true)
    {
        $oldconfigpath = $this->_configpath;
        $this->_configpath = $this->getAppDir($app).DIRECTORY_SEPARATOR.'__configs'.DIRECTORY_SEPARATOR;
        $data = $this->loadConfigFile($config_file,$mustLoad,false);
        $this->_configpath = $oldconfigpath;
        return $data;
    }

    public function loadConfigFile($config_file = null,$mustLoad=true,$addintoAllConfig=true)
    {
        $config_file = $config_file?:'app.php';
        $config_file = $this->_getConfile($config_file);

        $config_file_ext = $this->_configfile_ext.$config_file;
        $config_file_ext_env = $this->_getConfileWithEnv($config_file_ext);

        if( $this->_configpath ){
            $config_file = $this->_configpath.$config_file;
            $config_file_env = $this->_getConfileWithEnv($config_file);
        }

        $require = null;

        // load config file
        if ( isset($config_file_env) && $config_file_env && is_readable($config_file_env) ) {
            if( !in_array($config_file_env,$this->loadedFile) )
                $this->loadedFile[] = $config_file_env;
            $require = require $config_file_env;
        }
        elseif( is_readable($config_file) )
        {
            if( !in_array($config_file,$this->loadedFile) )
                $this->loadedFile[] = $config_file;
            $require = require $config_file;
        }
        elseif( is_readable($config_file_ext_env) )
        {
            if( !in_array($config_file_ext_env,$this->loadedFile) )
                $this->loadedFile[] = $config_file_ext_env;
            $require = require $config_file_ext_env;
        }
        elseif( is_readable($config_file_ext) )
        {
            if( !in_array($config_file_ext,$this->loadedFile) )
                $this->loadedFile[] = $config_file_ext;
            $require = require $config_file_ext;
        }
        elseif($mustLoad) {
            //如果这里发生问题 " Fatal error: Allowed memory size of 134217728 bytes exhausted " ,需要注意是不是配置文件路径有问题
            throw new J7Exception('Could not load the config file: '.$config_file );
        }

        if( isset($J7CONFIG) && $J7CONFIG )
        {
            if( $addintoAllConfig )
            {
                if (is_array($this->_config)) {
                    $this->_config += $J7CONFIG;
                } else {
                    $this->_config  = $J7CONFIG;
                }
            }
            return $J7CONFIG;
        }
        return $require;
    }
}