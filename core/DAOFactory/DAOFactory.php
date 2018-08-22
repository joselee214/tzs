<?php
require_once __DIR__ . "/../J7Config.php";
require_once __DIR__ . "/../J7Exception.php";
require_once __DIR__ . "/../FactoryObject.php";

abstract class DAOFactory
{
    const DB = 'Db';
    const RD = 'Redis';
	private static $_instance = [];
    /**
     * 通过前端 Config 里 $J7CONFIG['dao']['data_source'] = 'DB'; 指定实例化的类
     */
    public static function getDAOFactory($data_source)
    {
        if (empty($data_source)) {
            throw new J7Exception('getDAOFactory:$data_source cannt be null');
        }
        $class = $data_source.__CLASS__;
        $instancename = 'Factory_'.$class;
        if ( !isset(self::$_instance[$instancename]))
        {
            $file = __DIR__ . '/DAOFactory/' .$class.'.php';
            if (!file_exists($file)) {
                throw new J7Exception("Could not find DAOFactory file: $file .");
            }
            require_once $file;
            if (!class_exists($class)) {
                throw new J7Exception("Could not find class '$class' in file $file .");
            }
            self::$_instance[$instancename] = new $class();
        }
        return self::$_instance[$instancename];
    }

    protected function _getDAO($method,$args)
    {
        $endstr = str_replace('DAOFactory','',get_class($this));
        $startstr = substr($method,3);
        $daoclass = $startstr.$endstr;
        return FactoryObject::Get($daoclass,$args); //这里因为经常在service里会判断 $_adminrole_dao instanceof adminroleDAO ，所以得 true 实例化???
    }

	public function __call($method, $args)
	{
		return $this->_getDAO($method,$args);
	}
}