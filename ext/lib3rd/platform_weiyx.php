<?php
/**
 * facebook的数据调用类，请从这个类调用数据
 */
require_once( __DIR__ . '/platform_common.php' );
require(__DIR__ . "/WeiyouxiClient.php");

class platform_weiyx extends platform_common_class implements platform_common
{
    private $_token;
    private $_config;
    public $app_key;
    public $weiyouxi;
    public $platform;
    public $urlparams;
    
    public function __construct( $config )
    {
        //初始化参数
        $this->_config = $config;
    }

    /**
     * 初始化平台数据
     */
    public function platform_init( $data=array() )
    {
        $this->platform = new WeiyouxiClient($this->_config['ApiKey'], $this->_config['ApiSecret']);
        $Check = 1;
        if(isset($data['wyx_signature'])){
             $Check = $this->platform->setAndCheckSignature($data['wyx_signature']);
        }
        else if(isset($data['signed_request'])){
            $Check = $this->platform->setAndCheckSignature($data['signed_request']);
        }
        if ($Check == 1) {
            return array(-1,$this->_config['LoginUrl']);
        } else {
            $prams = explode("&", $Check);
            $wyx_user = explode("=", $prams[3]);
            $user_id = $wyx_user[1];
        }
        if(isset($user_id)){
            return array(1,$user_id);
        }else{
            return array(-1);
        }
    }

    /**
     *获取用户信息
     */
    public function getuser( $uid = null , $fields = null )
    {
        $data = $this->platform->get( 'user/show' , array( 'uid'=> $uid ));
        $gender = $data['gender'] == 'm'?2:1;
        $platuser = array(
            'uid' => $data['id'],
            'name' =>$data['name'],
            'first_name' =>$data['name'],
            'sex'=>$gender,
			'lang'=>'zh-cn'
        );
        return $platuser;
    }

    /**
     * 获取用户的好友
     */
    public function getuserfriends( $uid = null , $isapp = false, $fields = null )
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
    public function getorderinfo($order_id = null , $token = null )
    {
        
    }

    /**
     * 更新订单
     * @return void
     */
    public function updateorderinfo()
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