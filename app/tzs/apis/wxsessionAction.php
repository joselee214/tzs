<?php
require_once J7SYS_EXTENSION_DIR . '/lib/Curl.php';
class tzs_apis_wxsession_Action extends tzs_apis_common
{

    public $_jscode;

    public $nickName;
    public $gender;
    public $avatarUrl;
    public $city;
    public $province;
    public $country;

    public $encryptedData;
    public $iv;

    public $realname;
    public $meconfig;
    public $formId;

    public $extparmas=[];

    //通过jscode去拿
    public function execute()
    {
        $this->_ret = [];

        if( $this->_jscode )
        {
            $url = $this->getWxLoginCheckUrl($this->appid,$this->_jscode); //'https://api.weixin.qq.com/sns/jscode2session?appid='.$this->appid.'&secret='.$this->secret.'&js_code='.$this->jscode.'&grant_type=authorization_code';

            $h = new Curl($url);
            $_get_data = $h->get();
            $data = $retData = json_decode($_get_data,true);

//            $_get_data = '';
//            $data = $retData = array ( 'session_key' => '+kFxQb/MEltpBCa0uVwxfw==', 'openid' => 'oSjTH5UdrWdp_MpiffgRdy9K1wes', );

//            echo var_export($data,true);
//            die;

            if( $data && isset($data['session_key']) && isset($data['openid']) )
            {
                //存储用户
                $nd = [ 'session_key'=>$data['session_key'],'openid'=>$data['openid'] ];
                //$nd = $this->expUserInfo($nd,$data['session_key']); //貌似可以去掉，也不影响

                //生成或者更新信息...
                session_regenerate_id();
                $user = $this->getUser($nd);

                $retData['unionid'] = $user->_unionid();
                $retData['gcode'] = $user->_gcode();
                $retData['sessionid'] = session_id();
                $retData['site_domain'] = SITE_DOMAIN;

                $retData['can_sign_up'] = 1;
                $retData['can_session_login'] = 0;
                $retData['show_web_version'] = '也可用电脑浏览器打开WEB版 '.SITE_DOMAIN.' 进行文件操作！'; //'用电脑打开WEB版网址 '.SITE_DOMAIN.'/ 文件上传下载更方便';


                $cache = J7Cache::instance();
                $key = $user->_openid().'_'.$user->_appid();
                if( empty($user->_nickName()) || empty($cache->get($key)) )
                {
                    $retData['updateUinfo'] = 4000; //wx.getLogin() 认证后，延时 更新用户信息 ms
                }

                $retData['expires_in'] = $user->_expires_in();

                if( $user->_uid() && ( $this->_userme = $this->UserService->getUserById($user->_uid()) ) )
                {
                    $this->UserService->setLogin($this->_userme);
                    $retData['uid'] = $user->_uid();

                    $retData['realname'] = $this->_userme->_name();
                    $retData['realname_seted'] = $this->_userme->_name_seted();
                }
//                else
//                {
//                    $retData['expires_in'] = 0;
//                }

                $retData['intro'] = '「通知说」免费文档工具，分享至微信群，可以统计文档的已读/未读人数；可以分享各种文档，在线报名/结果下载。电脑打开web版查看 '.SITE_DOMAIN;

                if( empty($retData['realname']) )
                    $retData['introrealname'] = '由于无法获取群用户备注名,为方便阅读统计,故需要确认您的真实姓名';

                $this->_ret['result'] = 1;
                $this->_ret['data'] = $retData;
                $this->_ret['f'] = 3;

            }
            else
            {
                $this->_ret['f'] = 4;
                $this->_ret = ['data'=>$_get_data,'result'=>0,'jscode'=>$this->_jscode];
            }
        }
        else
        {
            $this->_ret['f'] = 5;
            $this->_ret = ['msg'=>'empty jscode','result'=>0];
        }
    }

    public function updateUserInfo()
    {
        $this->_ret['status'] = 0;
        $this->_ret['msg'] = 'updateUserInfo';

        $xcx_config = Util::wxAppInfo($this->appid);
        $this->appid = $xcx_config[0];

        if( $this->openid && $this->appid )
        {
            $wxuser = $this->UserExtPlatformService->getWxUserByOpenid($this->openid,$this->appid);
            if( $wxuser && $wxuser->_session_key() && $this->encryptedData && $this->iv )
            {
                $nd = $this->expUserInfo([],$wxuser->_session_key());

                $wxuser = $this->getUser($nd);

                $this->_ret['status'] = 1;
                $this->_ret['reguid'] = $wxuser->_uid();

                $isReg = false;

                if( !$wxuser->_uid() && ( $this->nickName || $this->avatarUrl ) )
                {
                    //自动注册
                    $nuser = [
                        'name'=>$wxuser->_nickName(),
                        'username'=>'wx_'.$this->openid,
                        'avatarUrl' => $wxuser->_avatarUrl(),
                        'pw'=>time(),
                        'validate_status' => 0
                    ];
                    try
                    {
                        $uid = $this->UserService->register($nuser);
                        $isReg = true;
                        $wxuser->_uid($uid)->save();
                        $this->_ret['reguid'] = $uid;
                    }
                    catch (Exception $e){

                    }
                }
                if( $wxuser->_uid() )
                {
                    $this->_userme = $this->UserService->getUserById($wxuser->_uid());
                    $this->UserService->setLogin($this->_userme);

                    if( !$isReg && $this->avatarUrl )
                    {
                        $this->UserService->updateUser(['uid'=>$wxuser->_uid(),'avatarUrl'=>$this->avatarUrl]);
                    }

                    $cache = J7Cache::instance();
                    $key = $wxuser->_openid().'_'.$wxuser->_appid();
                    $cache->set($key,1,3*86400);
                }
            }

            if( $this->meconfig )
            {
                $this->realname = Util::cleanhtml($this->realname,'');
                if( $wxuser->_uid() )
                {
                    $user = $this->UserService->getUserById($wxuser->_uid());
                    if( $user )
                    {
                        $user->_name($this->realname)->_name_seted(1);
                        $user->save();
                        $this->_ret['status'] = 1;
                        $this->_ret['realname'] = $this->realname;
                    }
                }
            }
        }
    }








    //===============================================

    protected function getWxLoginCheckUrl($appid,$jscode)
    {
        $appid = $appid?:$this->appid;
        $xcx_config = Util::wxAppInfo($appid);
        $this->appid = $xcx_config[0];
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$xcx_config[0].'&secret='.$xcx_config[1].'&js_code='.$jscode.'&grant_type=authorization_code';

        return $url;
    }

    protected function expUserInfo($nd=[],$session_key)
    {
        $nd['nickName'] = Util::cleanhtml($this->nickName,'');
        $nd['gender'] = Util::cleanhtml($this->gender,'');
        $nd['avatarUrl'] = Util::cleanhtml($this->avatarUrl?:'','');
        $nd['city'] = Util::cleanhtml($this->city,'');
        $nd['province'] = Util::cleanhtml($this->province,'');
        $nd['country'] = Util::cleanhtml($this->country,'');

        if( $this->encryptedData && $this->iv )
        {
            require_once J7SYS_EXTENSION_DIR.'/lib3rd/weixin/wxBizDataCrypt.php';
            $pc = new WXBizDataCrypt($this->appid, $session_key);
            $errCode = $pc->decryptData($this->encryptedData, $this->iv, $endata );

            if ($errCode == 0 && $endata && ($en_data=json_decode($endata,true)) && isset($en_data['unionId'])) {
                $nd['unionid'] = $en_data['unionId'];
            }
        }
        return $nd;
    }


    protected function getUser($data)
    {
        $data['gcode'] = FactoryObject::Instance('sharedSessionManager')->initGcode();

        $wxuser = $this->UserExtPlatformService->getWxUserByOpenid($data['openid']??$this->openid,$this->appid);

//        if( !isset($data['expires_in']) )
        $data['expires_in'] = 300; //客户端与服务器之间的session关联，小于keepalive值

//        var_dump($wxuser);
//        die;
//        $wxuser->_tcode('44433222')->save();
//        var_dump($wxuser);
//        die;

        if( !$wxuser )
        {

            $wxuser = $this->UserExtPlatformService->regWxUser($data);
            $wxuser->_appid($this->appid);
            $wxuser->_uid(0);   //默认0

            if( isset($data['unionid']) && $data['unionid'] )
            {
                $wxuser->_unionid($data['unionid']);
                $uid = $this->UserExtPlatformService->getUidFromUnionid($data['unionid']);
                if( $uid )
                    $wxuser->_uid(0);
            }
            if( isset($data['session_key']) )
                $wxuser->_session_key($data['session_key']);

            $wxuser->_session_id(session_id())->_gcode($data['gcode'])
                ->_created(date('Y-m-d H:i:s'))
                ->_updated(date('Y-m-d H:i:s'))
                ->save();
        }
        else
        {
            if( isset($data['avatarUrl']) && $data['avatarUrl']!=$wxuser->_avatarUrl() )
                $wxuser->_avatarUrl($data['avatarUrl']);
            if( isset($data['nickName']) && $data['nickName']!=$wxuser->_nickName() )
                $wxuser->_nickName($data['nickName']);

            if( isset($data['unionid']) && $data['unionid'] )
            {
                $wxuser->_unionid($data['unionid']);
                if( !$wxuser->_uid() && ($uid = $this->UserExtPlatformService->getUidFromUnionid($data['unionid'])) )
                    $wxuser->_uid($uid);
            }
            if( isset($data['session_key']) )
                $wxuser->_session_key($data['session_key']);

            $wxuser->_session_id(session_id())->_gcode($data['gcode'])->_expires_in($data['expires_in'])
                ->_updated(date('Y-m-d H:i:s'))
                ->save();
        }
        return $wxuser;
    }

}