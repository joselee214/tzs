<?php
require_once(__DIR__ . "/../../core/coreFilterAbstract.php");

class OaFilter extends coreFilterAbstract
{
    public function routeStartup($ret=null)
    {
//        $memcacheconfig = config('sessionstorage', 'sessionstorage.php');
//        ini_set("session.save_handler", $memcacheconfig['sessioncacheserver']['handler']);
//        ini_set("session.save_path", $memcacheconfig['sessioncacheserver']['save_path']);
//        session_set_cookie_params($memcacheconfig['gc_maxlifetime'],'/');
//        if( !session_status() )
            session_start();
        return $ret;
    }

    /**
     * @var ActionToken $action
     * @return ActionToken
     */
    public function routeShutdown($action)
    {
        //数据库数据
        $DevelopAdminService = FactoryObject::Get('DevelopAdminService');
        $uid = isset($_SESSION['adminuid']) ? $_SESSION['adminuid'] : 0 ;

        $actionname = '/'.$action->getControllerName().'/'.$action->getActionName();

        $whiteList = array('/index/index');
        if (in_array($actionname ,$whiteList)) {
            return $action;
        }

        if ( $uid ) {
            // 啥都不做
        }else{
            $action = new ActionToken('/');
        }
        return $action;
    }
}