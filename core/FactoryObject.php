<?php
require_once __DIR__.'/J7Config.php';
require_once __DIR__.'/J7Debuger.php';
require_once __DIR__.'/J7Validate.php';
require_once __DIR__.'/J7Exception.php';
require_once __DIR__.'/baseControllerModel.php';
require_once __DIR__ .'/DAOInterface/CacheListDbCrudDAO.php';
require_once __DIR__.'/J7Queue.php';
require_once __DIR__ .'/baseServiceClass.php';
require_once J7SYS_CLASS_DIR .'/basichelp/basic_service.php';


class FactoryObject
{
    static $instanceStorages=[];

    /**
     * 共用的工厂模式生成单例
     * @param $className
     * @param array $initParams
     * @param Closure|null $initFunction
     * @param string $applicationName
     * @return object $className
     *
     * @throws ReflectionException
     * @throws coreException
     */
    static public function Instance($className,$initParams=[],Closure $initFunction=null,$applicationName='')
    {
        $instanceid = ($applicationName?($applicationName.':'):'').$className;
        if( $initParams )
            $instanceid .= '_'.hash('crc32',print_r($initParams,true));

        if( !isset(self::$instanceStorages[$instanceid]) )
        {
            $replaceClass = config('_j7_system_replace','app.php');
            if ( isset($replaceClass[$instanceid]) && $replaceClass[$instanceid] )
                $className = $replaceClass[$instanceid];
            elseif ( isset($replaceClass[$className]) && $replaceClass[$className] )
                $className = $replaceClass[$className];

            if (substr($className, -7, 7) === '_Action')
                $instance = self::NewInstanceAction($className,$initParams,$applicationName);
            elseif ( substr($className, -7, 7) === 'Service' || substr($className, -3, 3) === 'DAO' )
                $instance = self::NewInstanceServiceOrDao($className,$initParams,$applicationName);
            else
            {
	            if( $className && count($initParams)>1 )
	            {
		            $rf = new ReflectionClass($className);
		            $instance = $rf->newInstanceArgs($initParams);
	            }
	            else
	            {
		            $instance = new $className(isset($initParams[0])?$initParams[0]:null);
	            }
            }

            if( $initFunction )
            {
                $initFunction($instance);
            }
            self::$instanceStorages[$instanceid] = $instance;
        }
        return self::$instanceStorages[$instanceid];
    }

    /*
     * 获得 Action Service Dao 单例
     * $classType  null | action ...
     */
    static public function Get($className,$initParams=[],$appName=null,$injectionsParams=[],$outInjectionsParams=[])
    {
	    $initParams = $initParams?:[];
        $appName = $appName?:RuntimeData::registry('SYS_APP');
        $injectionsParams = $injectionsParams?:$initParams;
	    if( !is_string($className) || !is_array($initParams) || !is_string($appName) || !is_array($injectionsParams) )
		    throw new coreException('Fatel exception @ FactoryObject::Get !');
	    //寻找action替代@_j7_system_replace_action
        $actionFullName = $appName.':/'.$className;
	    if (substr($className, -7, 7) === '_Action')
	    {
            //Action类替代 //通过配置进行替代
            $classReplaceConfig = config('_j7_system_replace_action');
            if( !empty($classReplaceConfig) )
            {
                list($app,$module,$controller,$method) = ActionToken::explodeActionName($className,null,$appName);
                $actionFullName = $app.':/'.$module.($controller?'/'.$controller:'');

                if( isset($classReplaceConfig[$actionFullName]) && isset($classReplaceConfig[$actionFullName][0]) )
                {
                    $actionFullName = $classReplaceConfig[$actionFullName][0];
                    $className = $actionFullName.'_Action';
                    $r_params = isset($classReplaceConfig[$actionFullName]['1'])?$classReplaceConfig[$actionFullName]['1']:[];
                    $injectionsParams = array_merge($injectionsParams,$r_params);
                    $appName = substr($actionFullName,0,strpos($actionFullName,':'));
                }
            }
	    }
        else
        {
            if( substr($className,0,3) == 'j7f' )
                $className = substr($className,3);
            $className = trim($className,'/\\ ');
            if( ($firstFlag = strpos($className,'\\'))>0 )
            {
                //把namespace 转换成内部 $app:/$className 的形式
                $className = substr($className,0,$firstFlag).':/'.substr($className,$firstFlag+1);
                $actionFullName = $className;
            }
        }

        $ins = self::Instance($className,$initParams,function ($instance) use($injectionsParams,$outInjectionsParams,$appName,$actionFullName) {
            /**
             * @var $instance baseControllerModel
             */
            $instance->__doInjections($injectionsParams,$outInjectionsParams);
            $instance->__applicationName = $appName;
	        $instance->__actionFullName = $actionFullName;
        },$appName);
        return $ins;
    }

    /*
     * 实例化 ACTION
     * @param $className ::: 有以下几种: [_app_:]xxx_xxx_xxx_Action   [_app_:]/module/xxxxxx app:/module/xxxxxx_Action
     */
    static public function NewInstanceAction($className,$initParams=[],$app=null)
    {
        if (substr($className, -7, 7) === '_Action')
            $className = substr($className,0,-7);
        if( strpos($className,':')!==false )
        {
            $app = substr($className,0,strpos($className,':'));
            $className = substr($className,strpos($className,':')+1);
        }
        $app = $app?:RuntimeData::registry('SYS_APP');
        $className = trim($className,'/');
        $delimiter = strpos($className,'/')!==false?'/':'_';

        $classNameSplit = explode($delimiter,$className);

        //$actionModule = $classNameSplit[0];
        $classFileName = implode('_',array_slice($classNameSplit, 1));
        if( $classFileName )
        {
            $filePath  = J7Config::instance()->getAppDir($app).'/'.$classNameSplit[0].'/'.$classFileName.'Action.php';
            $objectClass = $app.'_'.$classNameSplit[0].'_'.$classFileName.'_Action';;
        }
        else
        {
            $filePath  = J7Config::instance()->getAppDir($app).'/'.$classNameSplit[0].'/actions.php';
            $objectClass = $app.'_'.$classNameSplit[0].'_Action';
        }
	    $objectClass = self::GetFile($objectClass,$filePath);
        if (class_exists($objectClass))
        {
            $aopConfig = config('_j7_system_aop_config');
            if( $aopConfig['on'] )
                return AOPAspectInject::aopWeaving(new $objectClass());
            else
                return new $objectClass();
        }
        else
        {
            throw new coreException('Class: '.$objectClass.' is not exists! please check file: '.$filePath );
        }
    }

    /*
     * 实例化 Service 或者 Dao
     * $app 首先来着ACTION的自身
     * @param $className :::  [_app_:]xxxService   [_app_:]xxxDbDAO
     */
    static public function NewInstanceServiceOrDao($className,$initParams=[],$app=null)
    {
	    $app_s = RuntimeData::registry('SYS_APP');
	    $app_f = $app?:$app_s;
        if( strpos($className,':')!==false )
        {
	        $app_f = substr($className,0,strpos($className,':'));
            $className = substr($className,strpos($className,':')+1);
        }
        $className = trim($className,'/');
        if(substr($className, -7, 7) === 'Service')
        {
            $class = $className;
	        $classCheck = ['\\j7f\\'.$app_s.'\\'.$class,$class];
            $file  = array( J7Config::instance()->getAppDir($app_s).'/__service/'.$class.'.php', J7SYS_SERVICE_DIR.DIRECTORY_SEPARATOR.$class.'.php');
	        if ($app_f != $app_s)
	        {
		        array_unshift($classCheck,'\\j7f\\'.$app_f.'\\'.$class);
		        array_unshift($file,J7Config::instance()->getAppDir($app_f).'/service/'.$class.'.php');
	        }
        }
        elseif( substr($className, -3, 3) === 'DAO' )
        {
            $class = $className;
	        $classCheck = ['\\j7f\\'.$app_s.'\\'.$class,$class];
            $file  = array(J7Config::instance()->getAppDir($app_s).'/__dao/'.$className.'.php',J7SYS_DAO_DIR.DIRECTORY_SEPARATOR.$className.'.php');
	        if ($app_f != $app_s)
	        {
		        array_unshift($classCheck,'\\j7f\\'.$app_f.'\\'.$class);
		        array_unshift($file,J7Config::instance()->getAppDir($app_f).'/__dao/'.$class.'.php');
	        }
        }
        else
        {
            throw new coreException('Cannot Create : '.$className.' via method:GetServiceOrDao ' );
        }

	    $class = self::GetFile($classCheck,$file); //同名Service/dao会出错!!!

        $aopConfig = config('_j7_system_aop_config');
	    $ins = new $class();
	    call_user_func_array([$ins,'__j7construct'],$initParams);
        if( $aopConfig['on'] )
	        return AOPAspectInject::aopWeaving($ins,$app_s);
        else
            return $ins;
    }

    /**
     * 读取文件
     */
    static private function GetFile($class,$file)
    {
        if(is_array($file))
        {
            foreach( $file as $k=>$ef )
            {
                if( file_exists($ef) )
                {
	                $classCheck = is_string($class)?$class:$class[$k];
	                if( class_exists($classCheck) )
		                return $classCheck;
	                require_once $ef;
                    if( class_exists($classCheck) )
                    {
	                    return $classCheck;
                    }
                }
            }
            //有文件名必须有class
            throw new J7Exception('Could not find class : '.var_export($class,true).' in file : '.var_export($file,true) );
        }
        else
        {
	        if( !is_string($class) )
		        throw new J7Exception('Could not check class [3]:'.var_export($class,true).' in file : '.$file);
            if (!class_exists($class)) {
                if (!file_exists($file)) {
                    throw new J7Exception('Could not find  file [2]:'.$file);
                }
                require_once $file;
                if (!class_exists($class)) {
                    throw new J7Exception('Could not find class [1]:'.$class.' in file : '.$file);
                }
            }
	        return $class;
        }
    }
}
