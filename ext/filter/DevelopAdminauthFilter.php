<?php
require_once(__DIR__ . "/../../core/coreFilterAbstract.php");

class DevelopAdminauthFilter extends coreFilterAbstract
{
    public function routeStartup($ret=null)
    {
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
        /**
         * @var DevelopAdminService $DevelopAdminService
         */
        $DevelopAdminService = FactoryObject::Get('DevelopAdminService',[],'develop');
        $uid = isset($_SESSION['adminuid']) ? $_SESSION['adminuid'] : 0 ;
        $pwdcheck = isset($_SESSION['adminpwd']) ? $_SESSION['adminpwd']:'';
        
        if( $uid )
        {
            if( ! $DevelopAdminService->checkAdminSession($uid, $pwdcheck) )
            {
                $uid = null;
            }
            else
            {
                RuntimeData::set('j7_probizinfo','_meuid',$uid);
            }
        }
        
        if( $DevelopAdminService->checkPermission($uid, $action) )
        {
            return $action;
        }
        return new ActionToken('bizexception','notfound', array('error'=>'no permission'),'','develop');
    }
}
