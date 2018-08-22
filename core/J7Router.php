<?php
require_once __DIR__ . '/ActionToken.php';

/*
 * Sub Directory Router
 * if path = / then translate to controller=index, action=index 
 * if path = /controller_name then translate to controller=controller_name, action=index
 * if path = /controller_name/action_name then translate to controller=controller_name, action=action_name
*/

class J7Router implements Router_Interface
{
    /**
     * @var coreUrlPath
     */
    protected $_url_path;

    //初始化
    public function __construct(coreUrlPath $url_path=null)
    {
        $this->_url_path = $url_path;
        $this->_setRewriteBase();
    }

    /**
     * 设置 coreUrlPath，REQUEST URI 相关的操作均委托给 coreUrlPath 处理。
     */
    public function setUrlPath(coreUrlPath $url_path)
    {
        $this->_url_path = $url_path;
    }

    protected static function _getConfig($index=null,$mkey='j7initalize')
    {
        $front_config = config($mkey?:'j7initalize');
        return $index?(isset($front_config[$index])?$front_config[$index]:null):$front_config;
    }

    //过滤前置路径问题
    public function _setRewriteBase()
    {
//        $path = $this->_url_path->getRequestPath();
//        $_config = self::_getConfig();
//        if( $path )
//        {
//            if( isset($_config['_sub_directory_exclude']) &&
//	            in_array($path,$_config['_sub_directory_exclude'])
//            )
//                $url_prefix_path = rtrim($this->_url_path->getRewriteBase(),'/').'/'.ltrim($path,'/');
//        }
//        if( isset($url_prefix_path) && $url_prefix_path )
//            $this->_url_path->setRewriteBase( $url_prefix_path );
    }

    protected $_default_module = 'index';
    protected $_default_controller = 'index';
    protected $_default_method = 'execute';

    protected $_default_params = [];

    protected function getRouterInfo($staticf='.htm')
    {
        $paths = $this->_url_path->getRequestPaths();

        $params = $this->_default_params;
        $module = $this->_default_module;
        $controller = $this->_default_controller;
        $method = $this->_default_method;

	    if(count($paths) <= 1)
	    {
		    if ($paths[0] != '')
		    {
			    $c_staticf = strlen($staticf);

			    if( ( $p = strpos($paths[0],'~') )!==false )
			    {
				    $module = $p?substr($paths[0],0,$p):$module;
				    $method = substr($paths[0],$p+1);
			    }
			    elseif( substr($paths[0],-$c_staticf)==$staticf )
			    {
				    $urisplit = explode('-',substr($paths[0],0,-$c_staticf));
				    if( !is_numeric(reset($urisplit)) )
				    {
					    $module = $urisplit[0];
					    array_shift($urisplit);
				    }
				    if( $urisplit && !is_numeric(reset($urisplit)) )
				    {
					    $controller = reset($urisplit);
					    array_shift($urisplit);
				    }
				    $params['j7htmget'] = $urisplit;
			    }
			    else
			    {
				    $module = $paths[0];
			    }
		    }
	    }
	    else
	    {
		    $start = 0;
		    if( ( $p = strpos($paths[0],'~') )!==false )
		    {
			    $module = $p?substr($paths[0],0,$p):$module;
			    $method = substr($paths[0],$p+1);
			    $start = 1 ;
		    }
		    elseif ( ( $p = strpos($paths[1],'~') )!==false )
		    {
			    $module = $paths[0];
			    $controller = $p?substr($paths[1],0,$p):$controller;
			    $method = substr($paths[1],$p+1);
			    $start = 2;
		    }
		    elseif ( isset($paths[2]) && substr($paths[2],0,1) == '~' )
		    {
			    $module = $paths[0];
			    $controller = $paths[1];
			    $method = substr($paths[2],1);
			    $start = 3;
		    }
		    else
		    {
			    if( count($paths)==3 )
				    $method = $paths[2];
			    $module = $paths[0];
			    $controller = $paths[1];
			    $start += 2;
		    }
		    if( count($paths)>3 )
		    {
			    for ($i=$start; $i<count($paths); $i=$i+2) {
				    if( substr($paths[$i],-1,1)==']' && strpos($paths[$i],'[') )
				    {
					    $k = substr($paths[$i],0,strpos($paths[$i],'['));
					    $v = substr($paths[$i],strpos($paths[$i],'[')+1,-1);
					    if( !isset($params[$k]) )
						    $params[$k] = [];
					    $params[$k][$v] = isset($paths[$i+1]) ? $paths[$i+1] : null;
				    }
				    $params[$paths[$i]] = isset($paths[$i+1]) ? $paths[$i+1] : null;
			    }
		    }
	    }
        return [$module,$controller,$method,$params];
    }

	protected function regularRoute()
	{
		$routes = $this->_getConfig(null,'routes');
        if( $routes )
        {
            $path = $this->_url_path->getRequestPath();
            if( isset($routes[$path]) )
                return $routes[$path];
            if( $path=='/' || empty($path) )
                return false;
            foreach ($routes as $regular=>$map)
            {
                $matchs = null;
                preg_match_all($regular, $path, $matchs, 2);
                if( $matchs && isset($matchs[0]) )
                {
                    $match = $matchs[0];
                    $vstr = var_export($map,true);
                    for( $i=1;$i<count($match);$i++ )
                    {
                        $vstr = str_replace('{$'.$i.'}',$match[$i],$vstr);
                    }
                    eval('$map='.$vstr.';');
                    return $map;
                }
            }
        }
		return false;
	}

    /**
     * @return ActionToken|mixed|null
     * @throws J7Exception
     */
	public function route()
	{
		if( ($regular = $this->regularRoute())===false )
			$regular = $this->getRouterInfo();
        list($module,$controller,$method,$params) = $regular;

//        $fp = $this->_url_path->getRequestPath();
//        if( isset($_GET[$fp]) || isset($_GET[$fp.'/']) )    //更新传递参数时候问题,本框架内处理
//        {
//            unset($_GET[$fp]);
//            unset($_GET[$fp.'/']);
//        }

        $eparams = array_merge($params?:[], array_merge($_GET, $_POST));		//获取执行参数
        if( isset($_SERVER['HTTP_CONTENT_TYPE']) && ( strtolower($_SERVER['HTTP_CONTENT_TYPE']) == 'application/json') )
        {
            $inp = file_get_contents("php://input");
            if( $jsonparams = json_decode($inp,true) )
            {
                $eparams = array_merge($eparams,$jsonparams);
            }
        }
        $this->_url_path->setParams($eparams);

        $this->_setRequest($params);

		return $this->getActionToken($module,$controller,$eparams,$method);
	}

	protected function _setRequest($params)
    {
        //设置全局request内容
        RuntimeData::_setRequest('server',isset($_SERVER)?$_SERVER:[]);
        RuntimeData::_setRequest('path',$params);
        RuntimeData::_setRequest('get',isset($_GET)?$_GET:[]);
        RuntimeData::_setRequest('post',isset($_POST)?$_POST:[]);
        RuntimeData::_setRequest('request',isset($_REQUEST)?$_REQUEST:[]);
        RuntimeData::_setRequest('cookie',isset($_COOKIE)?$_COOKIE:[]);
        RuntimeData::_setRequest('originPost',file_get_contents("php://input"));
        RuntimeData::_setRequest('session',isset($_SESSION)?$_SESSION:[]);
        RuntimeData::_setRequest('files',isset($_FILES)?$_FILES:null);
    }

	protected function getActionToken($module,$controller,$params,$method)
	{
//		//ACTION文件检查
//		$file = __DIR__.'/../app/'.RuntimeData::registry('SYS_APP').'/'.$module.'/'.$controller.'Action.php';
//		if (!file_exists($file))
//		{
//			// actions.php 方法检查 // $controller 作为方法
//			$eparams['_method'] = $method;
//			$moduleActionFile = __DIR__.'/../app/'.RuntimeData::registry('SYS_APP').'/'.$module.'/actions.php';
//			if( file_exists($moduleActionFile) )
//			{
//				$class = FactoryObject::Get($module.'_Action',$eparams);
//				if( $class->__methodExists($controller) || $class->__methodExists('__empty') )
//					return new ActionToken($module ,'', $params ,$controller);
//			}
//			elseif( $module!='emptyModule' )
//			{
//				if( $checkAction = $this->getActionToken('emptyModule',$controller,$eparams,$method) )
//				{
//					$checkAction->getAction()->__actionModule = $module;
//					return $checkAction;
//				}
//			}
//		}
//		if( $module!='emptyModule' )
			return new ActionToken($module,$controller,$params,$method);
	}
}