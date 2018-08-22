<?php

require_once __DIR__.'/../../core/J7Router.php';

class imgRouter extends J7Router
{
    public function route()
    {
        $fpath = $this->_url_path->getFirstPath();
        if( $fpath=='qr' )
        {
            return parent::route();
        }
        if( substr($fpath,0,6)=='upimgs' && in_array(substr($fpath,6),Util::$resizeImgTypes) )
        {
            return new ActionToken('upimgs',substr($fpath,6), array('paths'=>$this->_url_path->getRequestPaths(),'path'=>$this->_url_path->getRequestPath()) );
        }

        $args = $_SERVER['QUERY_STRING'] ? $_SERVER['QUERY_STRING'] : $_SERVER['REQUEST_URI'];
        //var_dump($_SERVER);die();
        //$_SERVER['QUERY_STRING']
        return new ActionToken('index','404', array('path'=>$args) );
    }
}