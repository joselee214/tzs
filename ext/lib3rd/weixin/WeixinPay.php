<?php


/*
 * 小程序微信支付
 */


class WeixinPay {

    protected $appid;
    protected $mch_id;
    protected $sub_mch_id;
    protected $sub_appid;
    protected $sub_openid;

    protected $key;
    protected $openid;
    protected $out_trade_no;
    protected $body;
    protected $total_fee;

    protected $notify_url;

    protected $info = [];

    function __construct($out_trade_no=null,$body=null,$total_fee=1, $sub_mch_id=null,$sub_appid=null,$sub_openid=null) {

        $xcx_config = config('wx_xcx','weixin.php');
        $appid = $xcx_config['WECHAT_PAY']; //选用服务号作为支付基础//基础就是服务号那个,在JSAPI支付时候用换

        $this->appid = $appid;

        if( in_array($sub_mch_id,$xcx_config['ResetToUPJIAJUsubmchids']) )
        {
            $sub_mch_id = '';
        }

        $wxconfig = Util::wxAppInfo($appid,$sub_mch_id?'facilitator':null);

        $this->mch_id = $wxconfig[2];
        $this->key = $wxconfig[3];

        $out_trade_no_postfix = config('wxpay_out_trade_no_postfix','weixin.php');

        $this->out_trade_no = $out_trade_no.$out_trade_no_postfix;
        $this->body = $body;
        $this->total_fee = $total_fee;

        $this->sub_mch_id = $sub_mch_id;
        $this->sub_appid = $sub_appid;
        $this->sub_openid = $sub_openid;

        $this->notify_url = APIS_DOMAIN.'/wx/pay/notify';
//        if( RuntimeData::registry('SYS_ENV') == 'demo' )
//        {
//            $this->notify_url = APIS_DOMAIN.'/wx/pay/notify';
//        }
    }

    public static function getRealPayid($fromOuterPid)
    {
        return substr($fromOuterPid,0,-6);
    }

    public function pay($type=null,$enctype='MD5',$devinfo='WEB') {
        //统一下单接口
        if( empty($this->mch_id) || empty($this->key) )
        {
            return ['msg'=>'尚未配置支付','success'=>0];
        }
        if( empty($this->out_trade_no) || empty($this->total_fee) )
        {
            return ['msg'=>'参数有误','success'=>0];
        }
        return $this->weixinapp($type,$enctype,$devinfo);
    }

    public function info()
    {
        return $this->info;
    }

    //统一下单接口
    private function unifiedorder($type='JSAPI',$enctype='MD5',$devinfo='WEB') {
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        if( empty($type) )
            $type = 'JSAPI';

        if( $type=='JSAPI' )
        {
            if( empty($this->sub_openid) || empty($this->sub_openid) )
            {
                throw new J7Exception('JSAPI 模式下必须传 openid 与 openid');
            }
        }

        $parameters = array(
            'device_info' => $devinfo, //
            'nonce_str' => $this->createNoncestr(), //随机字符串
            'body' => $this->body,
            'spbill_create_ip' => Util::getRemoteAddr(), //终端IP
            'notify_url' => $this->notify_url, //通知地址  确保外网能正常访问
            'trade_type' => $type, //交易类型  //扫码 NATIVE  小程序/公众号// JSAPI
            'out_trade_no'=> $this->out_trade_no,//商户订单号
            'total_fee' => $this->total_fee, //总金额 单位 分
            'appid' => $this->appid, //小程序ID
            'mch_id' => $this->mch_id, //商户号
        );

        if( $this->sub_mch_id ) //服务商模式//必须传这个
        {
            $parameters['sub_mch_id'] = $this->sub_mch_id;
            if( $type=='JSAPI' )
            {
                $parameters['sub_openid'] = $this->sub_openid; //用户id
                $parameters['sub_appid'] = $this->sub_appid; //用户id
            }
        }
        else
        {
            //普通支付时候，jsapi时候，需传 openid 与 appid
            if( $type=='JSAPI' )
            {
                $parameters['openid'] = $this->sub_openid; //用户id
                $parameters['appid'] = $this->sub_appid; //用户id
            }
        }

        //统一下单签名
        $parameters['sign'] = $this->getSign($parameters,$enctype);
        $xmlData = $this->arrayToXml($parameters);
        $return = $this->xmlToArray($this->postXmlCurl($xmlData, $url, 60));

        $this->info['unifiedorder'] = $return;
        $this->info['parameters'] = $parameters;

        return $return;
    }


    private static function postXmlCurl($xml, $url, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); //严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);


        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 40);
        set_time_limit(0);


        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new WxPayException("curl出错，错误码:$error");
        }
    }



    //数组转换成xml
    private function arrayToXml($arr,$p='xml')
    {
        return Util::arrayToXml($arr,$p);
    }


    //xml转换成数组
    private function xmlToArray($xml) {
        return Util::xmlToArray($xml);
    }


    //微信小程序接口
    private function weixinapp($type=null,$enctype=null,$devinfo='WEB') {
        //统一下单接口
        $unifiedorder = $this->unifiedorder($type,$enctype,$devinfo);

        if( isset($unifiedorder['result_code']) && strtoupper($unifiedorder['result_code'])=='SUCCESS' )
        {
            $parameters = array(
                'appId' => $this->sub_appid?:$this->appid, //小程序ID
                'timeStamp' => '' . time() . '', //时间戳
                'nonceStr' => $this->createNoncestr(), //随机串
                'package' => 'prepay_id=' . $unifiedorder['prepay_id'], //数据包
                'signType' => strtoupper($enctype), //签名方式
            );

            if( $type=='NATIVE' && isset($unifiedorder['code_url']) )
                $parameters['code_url'] = $unifiedorder['code_url'];

            //签名
            $parameters['paySign'] = $this->getSign($parameters);
//            $parameters['success'] = 1;
            return $parameters;
        }
        else
        {
            //return_msg
            return ['msg'=>($unifiedorder['return_msg']??''),'success'=>0];
        }

    }


    //作用：产生随机字符串，不长于32位
    private function createNoncestr($length = 32) {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }


    //作用：生成签名
    public function getSign($Obj,$type='MD5') {
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //签名步骤二：在string后加入KEY
        $String = $String . "&key=" . $this->key;
        //签名步骤三：MD5加密

        if( strtoupper($type) =='HMAC-SHA256' )
        {
            $String = hash_hmac('sha256',$String,$this->key);
        }
        else
        {
            $String = md5($String);
        }
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        return $result_;
    }


    ///作用：格式化参数，签名过程需要使用
    private function formatBizQueryParaMap($paraMap, $urlencode) {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }


}