<?php
require_once __DIR__ . '/../J7View.php';
class ViewResultType extends J7ResultType
{
    public function process($result, $action , $retData=null, $app=null, $isecho=true)
    {
        if( isset($_GET['devjson']) && RuntimeData::registry('SYS_ENV') != 'prod' ){
            $params = $action->__getInjections(true);
            echo json_encode($params);
            return;
        }

        $html = $this->render($result,$action,$app);
        if($isecho)
        {
			header( 'P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"' );
			header("Content-Type: text/html; charset=UTF-8");
			header('cache-control: no-cache');

            ob_start();
            echo J7ActionTag::instance()->parse( $html );
        }
        return [$html,$result,$action];
    }

    /**
	 *
	 *  view 或者 layout 文件使用绝对路径即可避免跨app调用
	 *
     * @param $result
     * @param Action $action
     * @param null $viewinsName
     * @param null $app
     * @return mixed|string
     */
    protected function render($result, $action,$app=null)
    {
		list($view,$app,$resource, $template) = $this->getViewContent($result,$action,$app);
	    /**
	     * @var J7View $view
	     */
        return $view->render($app,$resource, $template); //默认的是渲染HTML的方法
    }

	/**
	 * @param $result
	 * @var Action $action
	 * @param null $app
	 * @return array
	 */
	public function getViewContent($result, $action, $app=null)
	{
		if( $action instanceof AOPProxyControllerModel )
			$action = $action->__getProxyObject();


		$appFrom = $app?:$action->_actionToken()->getApp();
		$app = $app?:$action->__applicationName;

		$view = J7View::instance([$action,J7Config::instance()->getAppDir($app).'/',J7Config::instance()->getAppDir($appFrom).'/']);

		$action_config = $action->__getConfig();
		if( isset($action_config[$result]['resource']) )
		{
			$resource = $action_config[$result]['resource'];
			if( strpos($resource,'/')===false )
			{
				$resource = $action->_actionToken()->getModule().'/'.$resource;
			}
//			if(substr($resource,-4)!=='.php')
//			{
//				$resource .= '.php';
//			}
		}
		else
		{
			//先寻找内部名称
			if( $action->__actionFullName != $action->_actionToken()->getFullActionName() )
			{
				list($appN,$module,$controller,$method) = ActionToken::explodeActionName($action->__actionFullName);
				$resource = $module.'/'.$controller.'.php';
			}
			if( !isset($resource) )
			{
				if( $action->_actionToken()->getController() )
				{
					$resource = $action->_actionToken()->getModule().'/'.$action->_actionToken()->getController().'.php';
				}
				else
				{
					$resource = $action->_actionToken()->getModule().'/'.$action->_actionToken()->getMethod().'.php';
				}
			}
		}

		$template = null;
		if ( isset($action_config[$result]['layout']) ) {
			$template = $action_config[$result]['layout'];
		}

		if( $app!=$appFrom )
        {
            debug(J7Config::instance()->getAppDir($app).'/'.$resource,'执行View:: ','info');
            debug(J7Config::instance()->getAppDir($appFrom).'/'.$template,'执行layout:: ','info');
        }
        else
            debug(J7Config::instance()->getAppDir($app).'/'.$resource . ' ('.$template.')','执行View:: ','info');

		return [$view,$app,$resource,$template];
	}
}