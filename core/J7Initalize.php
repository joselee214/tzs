<?php
require_once __DIR__.'/J7Config.php';
require_once __DIR__ . '/Util.php';
require_once __DIR__.'/RuntimeData.php';
require_once __DIR__.'/J7Debuger.php';
require_once __DIR__.'/coreUrlPath.php';
require_once __DIR__.'/J7Exception.php';

require_once __DIR__.'/coreDisposal.php';
require_once __DIR__.'/FactoryObject.php';
require_once __DIR__.'/J7Dispatcher.php';

require_once __DIR__.'/J7Router.php';

class J7Initalize
{
    protected $_url_path = null;

    /**
     * @var coreDisposal
     */
    protected $_disposal = null;

    protected $_app_mapping = null;
    protected $_app_configs = [];
    protected $_dispatcher = null;
    protected $_router = null;
    protected $_filters = [];
    protected $_resulttype = null;

    public static $_init_path,$_init_firstpath; //过程辅助

    public function __construct(coreUrlPath $url_path=null)
    {
        //载入GlobalFunction...
        $globalfiles = config('_j7_system_globalfunctions');
        foreach ($globalfiles as $eachfile)
        {
            require_once $eachfile;
        }

        $this->_url_path = $url_path;
        RuntimeData::register('url_path', $url_path,false);
        $this->_setRewriteBaseUrlPath();
    }

    /*
     * return J7Initalize
     */
    static public function getInstance($app=null,$env=null,$init_name = null,$path_url=null)
    {
        try {
            self::$_init_firstpath = self::$_init_path = null;

            if( $env )
                RuntimeData::register('SYS_ENV',$env,false);
            if( $app )
                RuntimeData::register('SYS_APP',$app,false);

            $url_path = self::_getDefaultUrlPath($path_url);
            if( !$app )
            {
                $app = $url_path->getFirstPath();
                $url_path->setRewriteBase( $url_path->getRewriteBase(). DIRECTORY_SEPARATOR .$app );
                if( $app )
                    RuntimeData::register('SYS_APP',$app,false);
            }
            if( is_null($init_name) )
                $init_class = self::_getInitalizeNameByRequest($url_path);
            else
                $init_class = $init_name;

            return FactoryObject::Instance($init_class,[$url_path]);

        } catch (J7Exception $e) {
            throw $e;
        }
    }

    //自定义执行...
    public function doDispatch($routerName=null,$dispatcherName=null,$filterNames = null,$resulttype=null)
    {
        try {
            if( $routerName )
                $this->_router = new $routerName($this->_url_path);
            else
                $this->_initRouter();

            if( $dispatcherName )
                $this->_dispatcher = new $dispatcherName();
            else
                $this->_initDispatcher();

            if( $filterNames )
            {
                foreach ($filterNames as $eachFilter)
                    $this->_filters[] = new $eachFilter();
            }
            elseif( $filterNames===null )
                $this->_initFilters();

            $this->_initFront();

            if( $resulttype )
                $this->_disposal->setDefaultResuleType($resulttype);
            else
                $this->_initResultType();				//设置View模式

            $this->_dispatch();             //执行 Disposal

        }catch (J7Exception $e) {
            throw $e;
        }
    }

    public function dispatch()
    {
        try {
            $this->_initDispatcher();		//选择Dispatcher
            $this->_initRouter();			//处理路由
            $this->_initFilters();			//设置前置插件
            $this->_initFront();			//前业务处理
            $this->_initResultType();				//设置View模式
            $this->_dispatch();             //执行 Disposal
        }
        catch (ForwordException $e)
        {
            J7Initalize::getInstance($e->app?:RuntimeData::registry('SYS_APP'),null,null,$e->url)->dispatch();
        }
        catch (J7Exception $e) {
            throw $e;
        }
    }



    protected function _initDispatcher()
    {
        $cset = self::getInitalizeConfig('dispatcher',$this->_url_path);
        if( $cset )
        {
            $name = $cset.'Dispatcher';
            $this->_dispatcher = new $name($this->_url_path);
        }
        else
            $this->_dispatcher = new J7Dispatcher();
    }
    protected function _initRouter()
    {

        $cset = self::getInitalizeConfig('router',$this->_url_path);
        if( $cset )
        {
            $name = $cset.'Router';
            $this->_router = new $name($this->_url_path);
        }
        else
            $this->_router = new J7Router($this->_url_path);
    }
    protected function _initFilters()
    {
        $filters = self::getInitalizeConfig('filters',$this->_url_path);
        if( $filters )
        {
            foreach ( $filters as $efiltername )
            {
                $filtername = $efiltername.'Filter';
                $this->_filters[] = new $filtername();
            }
        }
    }





    //获得本次执行对应的 Initalize 配置
    protected static function _getInitalizeNameByRequest(coreUrlPath $url_path , $init_name='J7Initalize')
    {
        $init_name_config = self::getInitalizeConfig('initalize',$url_path);
        if( $init_name_config )
            $init_name = $init_name_config.'Initalize';

        return $init_name;
    }

    //设置resultType 为 ['json',object(JsonResultType)] 这样格式
    protected function _initResultType()
    {
        if( Util::isAjax() )
            $this->_disposal->setDefaultResuleType([null,['view'=>'NullLayoutView']]);
        else
            $this->_disposal->setDefaultResuleType($this->_resulttype);
    }

    public static function getInitalizeConfig($hander = 'initalize',coreUrlPath $url_path=null)
    {
        if( is_null(self::$_init_firstpath) )
            self::$_init_firstpath = $url_path->getFirstPath();
        if( is_null(self::$_init_path) )
            self::$_init_path = $url_path->getRequestPath();
        $firstpath = self::$_init_firstpath;
        $path = self::$_init_path;

        $_config = config('j7initalize');

        $set = null;
        if( $path && isset($_config[$path]) && isset($_config[$path][$hander]) )
            $set = $_config[$path][$hander];
        elseif( $firstpath && isset($_config[$firstpath]) && isset($_config[$firstpath][$hander]) )
            $set = $_config[$firstpath][$hander];
        elseif( isset($_config['_default']) && isset($_config['_default'][$hander]) )
            $set = $_config['_default'][$hander];

        return $set;
    }

    //加载流程解析器
    protected function _initFront()
    {
        $this->_disposal = coreDisposal::getInstance();
        //载入 dispatcher
        if ($this->_dispatcher instanceof Dispatcher_Interface)
            $this->_disposal->setDispatcher($this->_dispatcher);
        //载入 router
        if ($this->_router instanceof Router_Interface)
            $this->_disposal->setRouter($this->_router);

        RuntimeData::register('current_router', $this->_router,false);
        //设置 plugins
        foreach ($this->_filters as $filters)
            if ($filters instanceof Filter_Interface)
                $this->_disposal->registerFilter($filters);
    }

    protected function _dispatch()
    {
        $this->_disposal->dispatch();
    }

    /**
     * 获取路径解析对象
     */
    protected static function _getDefaultUrlPath($path_url=null)
    {
        $url_path = new coreUrlPath($path_url);
        $url_path->setRewriteBase( self::_getSubDirectory($url_path) );
        return $url_path;
    }

    //更改路径 //用于继承的 ***Initalize 设置前置路径
    protected function _setRewriteBaseUrlPath($url_prefix_path='')
    {
        if( $url_prefix_path )
            $this->_url_path->setRewriteBase( $url_prefix_path );
        return $this->_url_path;
    }

    /**
     * 过滤配置跳过
     */
    protected static function _getSubDirectory(coreUrlPath $url_path)
    {
        $dir = self::getInitalizeConfig('_sub_directory',$url_path); //这个是跳过引入路径的配置的，在入口文件不在根目录下，防止冲突用的，不常用

        //注意不用 PHP_SELF
        if( $url_path->_SERVER_REQUEST_URI )
        {
            //增加 引入执行php文件的路径过滤
            // 指定 .php 引入文件的
            if( strpos($url_path->_SERVER_REQUEST_URI,$_SERVER['SCRIPT_NAME'])===0 )
                $dir = $_SERVER['SCRIPT_NAME'].DIRECTORY_SEPARATOR.trim($dir,'/');
            else
            {
                //引入文件路径
                if( $p = strrpos($_SERVER['SCRIPT_NAME'],'/') )
                {
                    $dir = substr($_SERVER['SCRIPT_NAME'],0,$p).DIRECTORY_SEPARATOR.trim($dir,'/');
                }
            }
        }
        return rtrim($dir,'/');
    }

}


/**
 * 很多辅助结构基础支持
 */
interface Dispatcher_Interface
{
    public function dispatch($route);
}
interface Router_Interface
{
    public function route();
}