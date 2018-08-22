<?php
class CommonInitalize extends J7Initalize
{
    public function dispatch()
    {
//        $origin = $_SERVER['HTTP_ORIGIN']??'';
//        $allow_origin = array(FRONT_DOMAIN,USERFRONT_DOMAIN,ACTIVITY_DOMAIN,ADMIN_DOMAIN,FACTORY_DOMAIN,GROUP_DOMAIN,OA_DOMAIN,PAY_DOMAIN,SALES_DOMAIN,SEARCH_DOMAIN,SITE_DOMAIN);
//
//        if( $origin )
//        {
//            if(in_array($origin, $allow_origin)){
//                header('Access-Control-Allow-Origin:'.$origin);
//            }
//            $p = parse_url($origin);
//            $l = strlen(BASE_HOST);
//
//            if( isset($p['scheme']) && isset($p['host']) && ( substr($p['host'],-$l,$l)==BASE_HOST || substr($p['host'],-9,9)=='.uj.local' ) )
//            {
//                header('Access-Control-Allow-Origin:'.$p['scheme'].'://'.$p['host']);
//            }
//        }
        parent::dispatch();
    }


    protected function _setRewriteBaseUrlPath($url_prefix_path='')
    {
        if( self::$_init_firstpath=='__all__' )
        {
            $this->_url_path->setRewriteBase( '/__all__');
        }
        return $this->_url_path;
    }


    protected function _initRouter()
    {
        if( self::$_init_firstpath=='__all__' )
        {
            $name = 'ForceFrontRouter';
            $this->_router = new $name($this->_url_path);
        }
        else
            parent::_initRouter();
    }
}