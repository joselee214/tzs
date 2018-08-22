<?php

require_once __DIR__.'/../../core/J7Router.php';

class CommonRouter extends J7Router
{
    public function route()
    {
        $paths = $this->_url_path->getRequestPaths();
        $endpath = end($paths);
        $checkRouter = $this->checkHtmRouter($endpath);
        return $checkRouter?$checkRouter:parent::route();
    }

    public function checkHtmRouter($endpath)
    {
        if( substr($endpath,-4)=='.htm' )
        {
            $endpath = substr($endpath,0,-4);
            $config = config('htm_router','htm_router.php');

            $app = RuntimeData::registry('SYS_APP');
            if( isset($config[$app]) && $allr=$config[$app] )
            {
                foreach ($config[$app] as $k=>$v)
                {
                    if( substr($endpath,0,strlen($k))==$k )
                    {
                        //命中router
                        $param = [];
                        if( isset($v['exp']) && $v['exp'] && isset($v['param_name']) && $v['param_name'] )
                        {
                            $matchs = null;
                            preg_match_all($v['exp'], $endpath, $matchs, 2);
                            if( $matchs && isset($matchs[0]) )
                            {
                                $p = $matchs[0];
                                if( isset($p[0]) )
                                {
                                    array_shift($p);
                                }
                                $pn = explode(',',$v['param_name']);
                                foreach ($pn as $ek=>$pk)
                                {
                                    $param[$pk] = isset($p[$ek])?$p[$ek]:null;
                                }
                            }
                        }
                        $param = array_merge($param?:[], array_merge($_GET, $_POST));
                        $this->_setRequest($param);
                        return new ActionToken($v['module'],$v['action'],$param);
                    }
                }
            }
        }
        return false;
    }
}