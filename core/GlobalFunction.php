<?php

//注册 全局函数
set_error_handler("J7SYS_error_handler");  //报错时调用
register_shutdown_function('J7SYS_shutdown');   //进程结束 or Fatal Error
set_exception_handler('J7SYS_exception_handler'); //异常


function J7SYS_shutdown()
{
    $argv = func_get_args();
    if( !$argv || empty($argv) )
        return;
    error( $argv ,'exception', 'J7SYS_shutdown:');
}

function J7SYS_exception_handler() {
    error( func_get_args() ,'exception', 'J7SYS_exception_handler:');
}

function J7SYS_error_handler($error_level, $error_message, $error_file, $error_line, $error_context) {
        $err = array(
            "error_file" => $error_file,
            "error_line" => $error_line,
            "error_message" => $error_message,
            "error_context"=>$error_context,
        );
    error( $err ,'exception', 'J7SYS_error_handler:');
    return TRUE;
}

/**
 * =================================================
 * AutoLoad
 * =================================================
 */

spl_autoload_register('j7_autoload');

function j7_autoload($class)
{
    if( substr($class,0,7)=='j7data_'  )
    {
        $file = J7SYS_CLASS_DIR.DIRECTORY_SEPARATOR.'daomap'.DIRECTORY_SEPARATOR.$class.'.class.php';
        if( file_exists($file) )
            require_once $file;
    }
    elseif (strpos($class,'\\j7data_')>1)
    {
        $fname = explode('\\',$class)[count(explode('\\',$class))-1];
        $app = explode('\\',$class)[1];
        $file = J7SYS_CLASS_DIR.DIRECTORY_SEPARATOR.'daomap'.DIRECTORY_SEPARATOR.$app.DIRECTORY_SEPARATOR.$fname.'.class.php';
        if( file_exists($file) )
            require_once $file;
    }
    elseif ( ( ($classType=substr($class,-5)) == 'Queue' || ($classType=substr($class,-5)) == 'Cache' || ($classType=substr($class,-9)) == 'Initalize' || ($classType=substr($class,-6)) == 'Router' || ($classType=substr($class,-6)) == 'Filter' || ($classType=substr($class,-10)) == 'ResultType' || ($classType=substr($class,-9)) == 'AOPAspect' ) && j7_loadClass($classType,$class) )
    {
    }
    elseif( substr($class,-7)=='_common' && count(explode('_',$class))>1 )
    {
        //加载 action 的 common 文件
        $commons = explode('_',$class);
        if ( count(explode('_',$class))==3 )
            $file = J7Config::instance()->getAppDir($commons[0]).DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR.$commons[0].'_'.$commons[1].'_common.php';
        if ( count(explode('_',$class))==2 )
            $file = J7Config::instance()->getAppDir($commons[0]).DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR.$commons[0].'_common.php';
        if( file_exists($file) )
            require_once $file;
    }
    else
    {
        $autoloadfile = config('_j7_system_classload');
        if( isset($autoloadfile[$class]) && $autoloadfile[$class] )
        {
            $file = $autoloadfile[$class];
            require_once $file;
        }
    }
}
function j7_loadClass($classType,$class)
{
    //front //filter //cache //initalize
    $classType = strtolower($classType); //类型

    $file =  J7Config::instance()->getAppDir(RuntimeData::registry('SYS_APP')).DIRECTORY_SEPARATOR.'__ext'.DIRECTORY_SEPARATOR.$classType.DIRECTORY_SEPARATOR. $class .'.php';
    if (!file_exists($file)) {
        $file = J7SYS_EXTENSION_DIR . DIRECTORY_SEPARATOR . $classType . DIRECTORY_SEPARATOR . $class . '.php';
        if (!file_exists($file)) {
            $file = J7SYS_CORE_DIR . DIRECTORY_SEPARATOR . $classType . DIRECTORY_SEPARATOR . $class . '.php';
            if (!file_exists($file)) {
                throw new coreException("Could not find class:'.$class.' @ file: $file .");
            }
        }
    }
    require_once $file;
    return true;
}




/**
 * =================================================
 * 调试 日志等 全局方法
 * =================================================
 */
function debug($info,$key = 'debug-=>',$showp=null)
{
    return J7Debuger::instance()->debug($info,$key,$showp);
}
function slog($info,$type='log',$key='log')
{
    return J7Debuger::instance()->log($info,$type,$key);
}
function error($info,$type='error',$key='error')
{
    return J7Debuger::instance()->error($info,$type,$key);
}

/**
 * 快速读取日志方法
 * $index 非空时候,会把配置存到内部数组中,配置文使直接用辅助变量 $J7CONFIG['****'] = ...
 * $index 为空时候, 配置文件中使用return [...] 直接返回
 */
function config($index,$file=null,$mustLoad=true,$app=null,$env=null)
{
    return J7Config::instance($app,$env)->get($index,$file,$mustLoad);
}


/**
 * =================================================
 * Tag / J7ActionTag / View 调用另外的 action / view 方法
 * =================================================
 */
function include_action($action,$params=[],$isecho=true,$paramsProtected=[])
{
    $r = J7ActionTag::instance()->getTag($action,$params,$paramsProtected);
    if( $isecho )
    {
        echo $r;
    }
    return $r;
}

function include_view($viewfile,$params=[],$isecho=true)
{
    if( strpos($viewfile,':')===false )
    {
        $app = J7View::endApp();
        $viewtp = $viewfile;
    }
    else
    {
        $app = substr($viewfile,0,strpos($viewfile,':'));
        $viewtp = substr($viewfile,strpos($viewfile,':')+1);
    }

    $view = J7View::instance([$params,J7Config::instance()->getAppDir($app).'/']);
    $c = $view->render($app,$viewtp, null);
    if( $isecho )
        echo $c;
    return $c;
}


/**
 * =================================================
 * Slots , 页面引用
 * =================================================
 */
function slot_include($name)
{
    return ($v = get_slot($name)) ? print $v : false;
}
function slot_has($name)
{
    return array_key_exists($name,J7View::$slots);
}
function slot_get($name,$default=false)
{
    if( isset(J7View::$slots[$name]) )
    {
        return J7View::$slots[$name];
    }
    return $default;
}
function slot($name, $value = null)
{
    if (!is_null($value))
    {
        J7View::$slots[$name] = $value;
        return;
    }
    J7View::$slots[$name] = $name;
    ob_start();
    ob_implicit_flush(0);
}
function slot_end($name=null)
{
    $value = ob_get_clean();
    if( is_null($name) )
    {
        $slotnames = array_keys(J7View::$slots);
        $name = end($slotnames);
    }
    J7View::$slots[$name] = $value;
}