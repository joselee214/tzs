<?php

require_once(__DIR__ . "/../../core/coreFilterAbstract.php");

class SessionFilter extends coreFilterAbstract
{
    static $session_started = false;
    public function routeStartup($ret=null)
    {
        /**
         * @var sharedSessionManager $sessionManager
         */
        $sessionManager = FactoryObject::Instance('sharedSessionManager');

        $_SESSION['_time'] = Util::getTime();

        if (strpos( RuntimeData::registry('url_path')->_SERVER_REQUEST_URI , '_ucsl_chekret') !== false) {
            return $sessionManager->getSharedSessionByKey($_GET['_ucsid']??null,$_GET['_url']??null,'session');
        }

        if (isset($_SESSION['id']) && $_SESSION['id'])
            RuntimeData::set('j7_probizinfo', '_meuid',$_SESSION['id']);

        return $ret;
    }

	/**
	 * @param ActionToken $actionToken
	 * @return mixed
	 * @throws BizException
	 * @throws J7Exception
	 */
    public function routeShutdown($actionToken)
    {
        if (isset($_SESSION['id']) && $_SESSION['id'] != 0) { // 已经登陆就不处理
            return $actionToken;
        }

        $appName = $actionToken->getApp();
        $module = $actionToken->getModule();
        $actionName = $actionToken->getController();

        $params = $actionToken->getParams();

        $isShop = false;

        if( $appName=='shop' && $module=='factory' && $actionName=='user' )
        {
            $appName = 'userfront';
            $module = $params['_module'];
            $isShop = true;
        }

        // access controller
        $appsConfig = array(
            'front' => array(
                'type' => 'black', // 黑名单，在黑名单中的  module 需要登录
                'modules' => [],
            ),
            'userfront' => array(
                'type' => 'white', // 白名单，在白名单中的 module 不需要登陆，其他的需要登录
                'modules' => array('factorylogin','reglog', 'outer', 'shopcart','webservice','emlog'),
            ),
            'shop' => array(
                'type' => 'black',
                'modules' => [],
            ),
            'group' => array(
                'type' => 'black',
                'modules' => array('input'),
            ),
            'admin' => array( // 暂时不处理
                'type' => 'black',
                'modules' => []
            ),
            'develop' => array( // 暂时不处理
                'type' => 'black',
                'modules' => []
            ),
            'search' => array(
                'type' => 'black',
                'modules' => []
            ),
            'factory' => array(
                'type' => 'white',
                'modules' => []
            ),
            'sales' => array(
                'type' => 'white',
                'modules' => []
            ),
            'oa' => array(
                'type' => 'black',
                'modules' => []
            ),
            'pay' => array(
                'type' => 'black',
                'modules' => ['index']
            ),
            'img' => array( //暂不处理
                'type' => 'black',
                'modules' => []
            ),
            'activity' => array( //暂不处理
                'type' => 'black',
                'modules' => []
            ),
        );

        if( !isset($appsConfig[$appName]) )
        {
            return $actionToken;
        }
        $blackWhite = $appsConfig[$appName];

        // skip for login check
        if (($module == 'index' && $actionName == 'logincheck') || $module == 'bizexception') {
            return $actionToken;
        }

        if (($blackWhite['type'] == 'white' && !in_array($module, $blackWhite['modules'])) ||
            ($blackWhite['type'] == 'black' && in_array($module, $blackWhite['modules']))
        ) {
            $rurl = (($_SERVER['SERVER_PORT'] == '80') ? 'http' : 'https') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $rurl = (Util::isAjax()&&(isset($_SERVER['HTTP_REFERER'])&&$_SERVER['HTTP_REFERER']))?$_SERVER['HTTP_REFERER']:$rurl;
            $url = '/reglog/login/?url=' . urlencode($rurl);
	        if( strpos(RuntimeData::get('j7_probizinfo','rewrite_base'),'nolayout')!==false ) {
                $url = '/nolayout' . $url;
            }

            $url =  ($isShop?'/shopuser':USERFRONT_DOMAIN). $url;

	        $params = $actionToken->getParams();
            $params = array_merge($params,['url' => $url, 'method' => 'login_required']);
            return new ActionToken('bizexception','login',$params,'', $isShop?'shop':'userfront');
        }
        return $actionToken;
    }

}