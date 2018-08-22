<?php
/**
 * facebook的数据调用类，请从这个类调用数据
 */
require_once(__DIR__ . '/platform_common.php');

class platform_facebook extends platform_common_class implements platform_common
{
    private $_token;
    private $_config;

    const REST_URL = 'https://graph.facebook.com/';
    const API_REST_URL = 'https://api.facebook.com/method/';

    public function __construct($config)
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

    /**
     * 初始化平台数据
     */
    public function platform_init( $data=array() )
    {
        $signed_request = '';
        if( isset($data['signed_request']) )
        {
            $signed_request = $data['signed_request'];
            unset($data['signed_request']);
        }
        $params = array();  //传入参数组合
        foreach ($data as $key => $value)
        {
            if ($key != 'signed_request' && $value != '' && !is_array($value)) {
                $params[] = $key . '=' . urlencode($value);
            }
        }
        $appUrl = $this->_config['AppUrl'].urlencode( '?' . implode('&', $params) );
        $loginUrl = $this->_config['LoginUrl'].'&redirect_uri='.$appUrl;


        if( ! $data = $this->parse_signed_request($signed_request)  )
        {
            return array(-1, $loginUrl); //signed_request错误
        }
        else
        {
            if ( isset($data['user_id']) && $data['user_id'] ) {
                $this->set_token($data['oauth_token']);
                return array( 1, strval($data['user_id']) , $data['oauth_token'] );
            }
            return array(-2, $loginUrl); //获取uid错误
        }
    }

    /**
     *获取用户信息
     */
    public function getUserInfo($uid = null , $token = null )
    {
        $ret = $this->_read_data($uid);
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
            $ret['sex'] = $ret['gender'] == 'female' ? 2 : 1;
			$ret['lang'] = strtolower($ret['locale']);
        }
        return $ret;
    }

    /**
     * 获取用户的好友
     */
    public function getUserFriends($uid = null, $isapp = false)
    {
        $fields = 'id,name,first_name,gender,installed,birthday';
        $ret = $this->_read_data($uid.'/friends',array("fields" => urlencode($fields) ));
        //$time_start = microtime(true);
        if (isset($ret['error']) || !isset($ret['data']) || !is_array($ret['data']))
        {
            debug($ret , '读取好友失败');
            $ret = array();
        }
        else
        {
            //$boolen = 1;
            //$err_code = 1;
            if ($isapp === true)
            {
                //$ckey = 'read_AppFriendOnly';
                function appfilter($val)
                {
                    return(isset($val['installed']) && $val['installed']==true);
                }
            }
            else if ($isapp === false)
            {
                //$ckey = 'read_AllFriends';
                function appfilter($val)
                {
                    return true;
                }
            }
            else
            {
                //$ckey = 'read_NonAppFriendOnly';
                function appfilter($val)
                {
                    return(!isset($val['installed']) || (isset($val['installed']) && $val['installed']==false));
                }
            }
            $ret = array_filter($ret['data'],'appfilter');
        }
        if( $ret )
        {
            foreach( $ret as &$v)
            {
                $v['uid'] = $v['id'];
                $v['installed'] = isset($v['installed']) ? $v['installed'] : false;
                $v['sex'] = (isset($v['gender'])&&$v['gender'] == 'male')? 2 : 1;
                if( isset($v['birthday']) && $v['birthday'] )
                {
					/*
                    $time = $v['birthday'];
                    preg_match('/^(\d{2})\/(\d{2})(\/\d{4})?$/i',$time,$arr);
                    $str = "00:00:00 {$arr[1]}/{$arr[2]}/2000";
                    $v['birthday'] = strtotime($str);
					*/
					$time = $v['birthday'];
                    preg_match('/^(\d{2})\/(\d{2})(\/\d{4})?$/i',$time,$arr);
                    $v['birthday'] = "{$arr[1]}/{$arr[2]}/2000";
                }
                unset($v['gender']);
                unset($v['id']);
            }
        }
        return $ret;
    }

    /**
     * 读取order
     */
    public function getOrderInfo($order_id = null , $token = null )
    {
        if( $order_id == null || $token == null )
        {
            return false;
        }
        $token = str_replace( 'access_token=' , '' , $token);
        $ret = $this->_read_data($order_id , array('access_token'=>$token));
        return $ret;
    }

    /**
     * 获取用户的各种信息
     * @param string $uid not null
     * @param string $api 要查询的graph api
     * @param string $query 附加信息
     * @return json
     */
    public function get( $uid = null , $api = null , $query = null )
    {
        $urlPre = $uid;
        if( $api != null )
        {
            $urlPre .= "/{$api}";
        }
        $ret = $this->_read_data($urlPre, $query);
        if (isset($ret['error']))
        {
            return array();
        }
        else
        {
            return ($api?$ret['data']:$ret);
        }
    }

    /**
     * 更新订单
     * @param string $order_id 订单ID
     * @param integer $statu 更新的状态 1:解决;2:退钱
     * @param string $message 如果更新有争议的订单，应当附上原因(备注)
     * @param string $token facebook token
     * @return boolen `true` or `false`
     */
    public function updateOrderInfo( $order_id = null , $statu = null , $message = '' , $token = null )
    {
        if( $order_id == null || $statu === null || $token == null )
        {
            return false;
        }
        $order_statu = '';
        if( $statu === 1 )
        {
            $order_statu = 'settled';
        }
        else if( $statu === 0 )
        {
            $order_statu = 'refunded';
        }
        $this->is_curl_post = true; //需要post来更新订单
        $token = str_replace( 'access_token=' , '' , $token);
        $ret = $this->_read_data($order_id , array('access_token'=>$token,'status'=>$order_statu,'message'=>htmlspecialchars($message)));
        $this->is_curl_post = false; //重置为curl默认的方式
        return $ret;
    }

    /**
     * 获取app的access_token
     */
    public function getAppToken( $config )
    {
        $ret = $this->_read_data('oauth/access_token',array('client_id'=>$config['AppId'],'client_secret'=>$config['ApiSecret'],'grant_type'=>'client_credentials'));
        return $ret;
    }


    /**
     * 读取facebook json api
     */
    private function _read_data($query, $params = null,$token=null)
    {
//        if ($isapi) {
//            $prefix = self::API_REST_URL . $query . '?format=json-strings&';
//        }
//        else
//        {
            $prefix = self::REST_URL . $query . '?';
//        }

//        if( $this->is_curl_post )
//            return $this->_geturl($prefix,$params);

        if ($params == null) {
            $url = $prefix . 'access_token=' . ($token?:$this->_token);
        }
        elseif( !is_array($params))
        {
            $url = $prefix . 'access_token=' . ($token?:$this->_token) . '&' . $params;
        }
        else
        {
            $paramstr = '';
            foreach($params as $k=>$v)
            {
                if( is_integer($k) )
                    $paramstr .= '&'.$v;
                else
                    $paramstr .= '&'.$k.'='.$v;
            }
            $url = $prefix . 'access_token=' . ($token?:$this->_token) . $paramstr;
        }
//        if( ! $rtn = $this->_geturl($url) )
//            throw new J7Exception('can not read URL:'.$url);
//        return $rtn;
        return $this->_geturl($url);
    }

    //验证 signed_request
    public function parse_signed_request($signed_request = null)
    {
        try
        {
            if( !$signed_request )
            {
                if(  !isset($_REQUEST['signed_request']) ){ return false; }
                $signed_request = $_REQUEST['signed_request'];
            }
            list($encoded_sig, $payload) = explode('.', $signed_request, 2);
            $sig = $this->base64_url_decode($encoded_sig);
            $data = json_decode($this->base64_url_decode($payload), true);
        }
        catch(Exception $e)
        {
            return false;
        }

        if (!isset($data['algorithm']) || strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
            return false;
        }
        $appSecret = $this->_config['ApiSecret'];
        $expected_sig = hash_hmac('sha256', $payload, $appSecret, true);
        if ($sig != $expected_sig) {
            return false;
        }
        return $data;
    }

    function base64_url_decode($input)
    {
        return base64_decode(strtr($input, '-_', '+/'));
    }
}