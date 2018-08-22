<?php

date_default_timezone_set("Asia/Shanghai");  //   Etc/GMT  Asia/Chongqing   America/New_York
gc_enable();

/**************************************
 * 框架的数据参数配置
 ***************************************/

//调试输出设置


$J7CONFIG['_j7_system_debug'] = ['on'=>true,'hander'=>'ChromePhp']; //FirePHP,ChromePhp

/**************************************
 * 支持框架运行的一些设置 -------- 基础PHP类等 //不跟环境走
 ***************************************/

//允许action里执行dao //这个如果按app开启的话,注意单独app下的basic_action与根的basic_action不同,即全局dao跟子dao还是不同的
$J7CONFIG['_j7_system_allow_call_dao_in_action'] = false;

//是否使用queue //否的话,部分service里->queue()->xxx 将直接执行
$J7CONFIG['_j7_system_queue_on'] = true;

// 101 即记录操作日志
$J7CONFIG['routes'] = []; // exp=>[module,controller,params=[],method]
$J7CONFIG['_j7_system_dao_mode'] = '000'; //参考DbCrudDAO说明
$J7CONFIG['_j7_system_basic_class_action'] = 'HttpAction'; //Action基类 //Service基类 _j7_system_basic_class_service
//核心类载入 单例时候实现替代
$J7CONFIG['_j7_system_replace'] = ['J7Cache'=>'coreMemCache','J7Page'=>'Pager','J7Debuger'=>'Debuger','J7Validate'=>'ValidateMore'];
//Action 的替代
$J7CONFIG['_j7_system_replace_action'] = ['userfront:/order/detailxxx'=>['share:/orders/detail',['_tt'=>'12345']]];
//用于 autoload 时候招不到类时候
$J7CONFIG['_j7_system_classload'] = ['Pager'=>J7SYS_EXTENSION_DIR.'/extension/Pager.php',
    'Debuger'=>J7SYS_EXTENSION_DIR.'/extension/Debuger.php',
    'ValidateMore'=>J7SYS_EXTENSION_DIR.'/extension/ValidateMore.php'];
//全局加载文件
$J7CONFIG['_j7_system_globalfunctions'] = [ J7SYS_EXTENSION_DIR.'/extension/j7data_for_output.class.php',J7SYS_EXTENSION_DIR.'/extension/HttpAction.php',J7SYS_EXTENSION_DIR.'/lib/sharedSessionManager.php',J7SYS_CORE_DIR.'/../vendor/autoload.php']; //J7SYS_EXTENSION_DIR.'/lib/testCoroutine.php'

//面向切面//aop会增加系统负担,视业务而定
$J7CONFIG['_j7_system_aop_config'] = [
    'on'=>false, //整体开关
    'aops'=>['debugAOPAspect'], //统一挂载的AOP实例//会与独立挂载相合并
    'exclude'=>['syslogDbDAO'], //不切片的类
	//'front:UserService'=>['debugAOPAspect'],
    //'front:Test1Service'=>['debugAOPAspect'], //独立类挂载的AOP实例//注意:这里使用类的全名(可以不加app:前缀,加前缀意味着跟随app调用的)
];



/**************************************
 * 支持框架运行的一些设置 ------- 业务相关 initalize/dispatcher/Router/filters 等配置 , 每个application可以不同 //
 ***************************************/

//目前框架的执行模式是可与其它php程序共同运行的。。需指定 sub_directory
//$J7CONFIG['j7initalize']['_sub_directory'] = '/';  //$_SERVER['SCRIPT_NAME'];	//指定的根执行文件...
$J7CONFIG['j7initalize']['_default'] = ['initalize'=>'Common','router'=>'Common','filters'=>['Session','SysLog'] ] ; 		//默认的执行Front类
$J7CONFIG['j7initalize']['notp'] = ['initalize'=>'Notp'];
$J7CONFIG['j7initalize']['nolayout'] = ['initalize'=>'Nolayout'];


//跟着application走的Initalize //子application使用的_sub_directory既front路由跳过解析的一层

//正则路由配置//
//$J7CONFIG['routes']['/\/aaa\/(\d+)\/(\d+)\/(\d+)\/testq/'] = ['index','test','testq',['test'=>'{$1}']];

//$J7CONFIG['j7initalize']['_sub_directory'] = 'testsub/';   //这个是跳过引入路径的配置的，在入口文件不在根目录下，防止冲突用的，几乎不用
//$J7CONFIG['j7initalize']['_sub_directory_exclude'] = 'zzz'; //

/**************************************
 * 业务配置类
 ***************************************/
J7Config::instance()->loadConfigFile('db/data_source.php');