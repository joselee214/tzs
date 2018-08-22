<?php
interface platform_common
{
    function getUserInfo( $uid = null ); //获取用户信息
    function getUserFriends( $uid = null , $isapp = false ); //用户用户好友
    function platform_init( $data = array() ); //初始化平台，获得用户验证的相关信息
    function getOrderInfo(); //获取订单信息
    function updateOrderInfo(); //更新订单
}

abstract class platform_common_class
{
    protected $is_curl_post = null;
    final protected function _geturl( $url = null , $postdata=array() )
    {
        if( $url == null )
        {
            return false;
        }
        set_time_limit(15);
        $curlHandle = curl_init();
        if( $postdata || $this->is_curl_post )
        {
            curl_setopt( $curlHandle , CURLOPT_POST , TRUE );
            if( $postdata )
                curl_setopt( $curlHandle, CURLOPT_POSTFIELDS, $postdata);
        }
        curl_setopt( $curlHandle , CURLOPT_URL, $url );
        curl_setopt( $curlHandle , CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $curlHandle , CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt( $curlHandle , CURLOPT_CONNECTTIMEOUT , 3);
        curl_setopt( $curlHandle , CURLOPT_TIMEOUT, 10 );
        $result = curl_exec( $curlHandle );
        curl_close( $curlHandle );
        if( $result===false )
            return false;
        $ret = json_decode($result, true);
        return $ret === NULL ? $result : $ret;
    }

}