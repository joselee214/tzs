<?php
/**
 * facebook的数据调用类，请从这个类调用数据
 */
require_once( __DIR__ . '/platform_common.php' );

class platform_weibo extends platform_common_class implements platform_common
{
    private $_token;
    private $_config;

    const API_URL = 'https://api.weibo.com/2/';
    const API_AUTH_URL = 'https://api.weibo.com/oauth2/';
    
    public function __construct( $config )
    {
        //初始化参数
        $this->_config = $config;
    }

    /**
     * 玩家token
     */
    public function get_token()
    {
        return $this->_token;
    }
    public function set_token($auth_token)
    {
        $this->_token = $auth_token;
    }

    public function getCallUrl($calltp,$getparam=null)
    {
        if( in_array( $calltp,array('access_token') ) )
            $url = self::API_AUTH_URL.$calltp;
        else
            $url = self::API_URL.$calltp;
        if( $getparam )
            $url .='?'.$this->arrtourl($getparam);
        return $url;
    }

    public function arrtourl($params)
    {
        $url = '';
        foreach($params as $k=>$v)
        {
            if( is_integer($k) )
                $url .= '&'.$v;
            else
                $url .= '&'.$k.'='.$v;
        }
        return substr($url,1);
    }

    /**
     * 初始化平台数据
     */
    public function platform_init( $data=array() )
    {
        $postparams = $getparams = array();
        if(isset($data['signed_request'])){
            $postparams['client_id'] = $this->_config['AppId'];
            $postparams['client_secret'] = $this->_config['ApiSecret'];
            $postparams['code'] = $data['signed_request'];
            $postparams['grant_type'] = 'authorization_code';
            $postparams['code'] = $data['signed_request'];
            $postparams['redirect_uri'] = urlencode( $this->_config['AppRedirectUri'] );
        }
        else
        {
            return array(-1,$this->_config['LoginUrl']);
        }
        $url = $this->getCallUrl('access_token');
        $ret = $this->_geturl($url, $this->arrtourl($postparams) );
        if( !$ret )
            return array(-1,$this->_config['LoginUrl']);
        if( isset($ret['error']) && isset($ret['error_code']) )
            return array(-2,$this->_config['LoginUrl']);

        if( isset($ret['access_token']) && isset($ret['uid']) )
        {
            $this->set_token($data['oauth_token']);
            return array( 1, strval($ret['uid']) , $ret['access_token'] );
        }
        else
            return array(-3,$this->_config['LoginUrl']);
    }

    /**
     *获取用户信息
     */
    public function getUserInfo( $uid = null , $token=null )
    {
        $url = $this->getCallUrl('users/show.json',array( 'uid'=> $uid,'access_token'=>($token?:$this->get_token()) ));
        $ret = $this->_geturl($url);
        if( !$ret )
        {
            return false;
        }
        elseif (isset($ret['error']))
        {
            return array();
        }
        else
        {
            $ret['uid'] = $ret['id'];
            //$ret['name']
            $ret['sex'] = ($ret['gender'] == 'm') ? 1 : 2;
            $ret['lang'] = strtolower($ret['lang']);
        }
        return $ret;
    }

    /**
     * 获取用户的好友
     */
    public function getUserFriends( $uid = null , $isapp = false, $fields = null )
    {
        $pagecount = 200;
        $allfriend = $this->platform->get( 'user/friends' , array( 'page' =>1, 'count' =>$pagecount , 'trim' =>0 ) );
        $friends = $allfriend['users'];
        $total = $allfriend['total_number'];
        $count = ceil($total/$pagecount);
        for($i = 2 ; $i<=$count ; $i++ ){
            $ret = $this->platform->get( 'user/friends' , array( 'page' => $i , 'count' =>$pagecount , 'trim' =>0 ) );
            $friends += $ret['users'];
        }
        $retall =array();
        foreach($friends as $value){
            $retall[$value['id']] = array(
                'uid'=>$value['id'],
                'name'=>$value['name'],
                'first_name'=>$value['name'],
                'sex'=>$value['gender'] == 'm'?2:1,
                'installed' => false,
            );
        }
        $appfriend  = $this->platform->get( 'user/app_friends' , array( 'trim'=> 0 ));
        $retapp =array();
        foreach($appfriend as $value){
            $retapp[$value['id']] = array(
                'uid'=>$value['id'],
                'name'=>$value['name'],
                'first_name'=>$value['name'],
                'sex'=>$value['gender'] == 'm'?2:1,
                'installed' => true
            );
            $retall[$value['id']]['installed'] = true;
        }
        if($isapp === false)
        {
            $friend = $retall;

        }else if($isapp === true)
        {
            $friend = $retapp;
        }else{
            $friend  = array_diff_key($retall,$retapp);
        }
        return $friend;
    }

    /**
     * 读取order
     */
    public function getOrderInfo($order_id = null , $token = null )
    {
        
    }

    /**
     * 更新订单
     * @return void
     */
    public function updateOrderInfo()
    {

    }
    
    /*
     * 用户发起支付时，游戏调用pay/get_token接口获取token； 应用申请开通支付权限时，需要填写支付成功后的回调地址，用于通知游戏用户已经支付成功。
     * 通过支付权限的审核后，开发者请到开放平台-应用汇总信息页，查看应用是否有支付权限，存在“支付ID”则支付权限已开通）
     *获取订单状态
     */
    public function pay_get_token($order_id, $amount, $desc) { 
        $app_secret = $this->_config['ApiSecret'];
        $sign = md5("$order_id|$amount|$desc|$app_secret");
        $data = $this->platform->get('pay/get_token', array('order_id' => $order_id,'amount' => $amount, 'desc' => $desc, 'sign' => $sign));
        return $data;
    }
    /*
     *当支付成功，当没有收到支付中心的跳转，可调用此接口查询订单的状态
     */
    public function pay_order_status($order_id,$userId){ // TODO
        $appId = $this->_config['ApiKey'];//AppKey
        $sign = md5($order_id|$this->_config['ApiSecret']);
        $data = $this->platform->get( 'pay/order_status' , array( 'order_id' => $order_id , 'user_id'=> $userId , 'app_id' => $appId , 'sign' => $sign ) );
        return $data;
    }
}