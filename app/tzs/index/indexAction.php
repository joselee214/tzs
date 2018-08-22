<?php
require_once J7SYS_EXTENSION_DIR . '/lib/Curl.php';

class tzs_index_index_Action extends tzs_index_common
{
    public $appid;

    public $code;
    public $state;

    public $msg;

    public function __j7construct()
    {
        parent::__j7construct();

        $c = config('wx_xcx','weixin.php');
        $this->appid = $c['wxPCwebLogin'];

        if( (RuntimeData::registry('SYS_ENV') == 'prod') && strtolower($_SERVER['REQUEST_SCHEME'])=='http' )
        {
            $this->redirect(SITE_DOMAIN);
        }
    }

    public function execute()
    {
        if( $this->_userme )
        {
            return $this->redirect('/tzs/i');
        }
    }

    public function logout()
    {
        session_destroy();
        return $this->redirect('/');
    }

    public function pccall()
    {
        $appinfo = Util::wxAppInfo($this->appid);

        $this->_setView('index/index_err.php');

        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->appid.'&secret='.$appinfo[1].'&code='.$this->code.'&grant_type=authorization_code';

        $h = new Curl($url);
        $_get_data = $h->get();
        $data = json_decode($_get_data,true);

        //$data = array ( 'access_token' => '12_i-GAVME5l-jgBmayKxKhavAsozyEmdP0aS70K9CdssYC0xmlKRqfe1ZupEx7kXEQ6UntGx8R3-6wS7HHQZNTgA', 'expires_in' => 7200, 'refresh_token' => '12_MhoxbUVNpfOqkC-ExaN2Paa8rGx1ksL-SOji28UoHTiAIPbYXY5c4ZDShQaxEYXHbyeJtHHZplOCnzDIwPyT0w', 'openid' => 'oZZXg1LRbc3UIh6lndYbOLZMmQkE', 'scope' => 'snsapi_login', 'unionid' => 'ogT8swQSKro-ATqXHf7XQbC4U2HE', );


        if( empty($data) || !is_array($data) || isset($data['errcode']) )
        {
            $this->msg = '登录解析出错!请刷新重试 1';
            return;
        }


        $userinfourl = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$data['access_token'].'&openid='.$data['openid'];

        $hu = new Curl($userinfourl);
        $_get_data = $hu->get();
        $udata = json_decode($_get_data,true);

        //$udata = array ( 'openid' => 'oZZXg1LRbc3UIh6lndYbOLZMmQkE', 'nickname' => '李鑫蔚 Jose', 'sex' => 1, 'language' => 'zh_CN', 'city' => 'Nantong', 'province' => 'Jiangsu', 'country' => 'CN', 'headimgurl' => 'http://thirdwx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTKDmW3drYa2QAKjJUOcC2lrfE9PTfy2ajPe7P1NKCHt0deGYFMXqlyS2C1W6yI0p7sAYLF4ic9Tz3A/132', 'privilege' => array ( ), 'unionid' => 'ogT8swQSKro-ATqXHf7XQbC4U2HE', );



        if( empty($udata) || !is_array($udata) || isset($udata['errcode']) )
        {
            $this->msg = '登录解析出错!请刷新重试 2';
            return;
        }

        $wxuser = $this->UserExtPlatformService->getWxUserByOpenid($data['openid'],$this->appid);

        $gcode =  FactoryObject::Instance('sharedSessionManager',[$this->state])->initGcode();

        if( !$wxuser )
        {
            $wxuser = $this->UserExtPlatformService->regWxUser($data)->fromArray($udata);
            $wxuser->_openid($data['openid'])->_appid($this->appid)
                ->_nickName($udata['nickname'])
                ->_gender($udata['sex'])
                ->_avatarUrl($udata['headimgurl']);

            if( isset($data['unionid']) && $data['unionid'] )
            {
                $wxuser->_unionid($data['unionid']);
                $uid = $this->UserExtPlatformService->getUidFromUnionid($data['unionid']);

                if( $uid )
                    $wxuser->_uid($uid);
            }
            $wxuser->_session_id(session_id())
                ->_created(Util::dateTime())
                ->_updated(Util::dateTime())
                ->_gcode($gcode)
                ->save();
        }
        else
        {
            $wxuser
                ->_nickName($udata['nickname'])
                ->_gender($udata['sex'])
                ->_avatarUrl($udata['headimgurl']);

            if( isset($data['unionid']) && $data['unionid'] )
            {
                $wxuser->_unionid($data['unionid']);
                if( !$wxuser->_uid() && ($uid = $this->UserExtPlatformService->getUidFromUnionid($data['unionid'])) )
                    $wxuser->_uid($uid);
            }

            $wxuser->_gcode($gcode)->_session_id(session_id());
            if( $wxuser->isModify() )
            {
                $wxuser->_updated(Util::dateTime())->save();
            }
        }

        $wxuser = $this->UserExtPlatformService->getWxUserByOpenid($data['openid'],$this->appid);

        //处理登录
        if( $wxuser->_uid() )
        {
            //更新信息// 不用更新昵称
            $this->UserService->updateUser(['uid'=>$wxuser->_uid(),'avatarUrl'=>$wxuser->_avatarUrl()]);

            $this->UserService->setLogin(['uid'=>$wxuser->_uid()]);
            $this->msg = '<script>top.location.reload();</script>';
            return;
        }
        else
        {
            //新注册用户
            $uname = 'wx_'.$data['openid'];
            if( $user = $this->UserService->getUserByCond(['username'=>$uname]) )
            {
                $uid = $user['uid'];
            }
            else
            {
                //自动注册
                $nuser = [
                    'name'=>$wxuser->_nickName(),
                    'username'=>'wx_'.$data['openid'],
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
            $this->UserService->setLogin(['uid'=>$uid]);
            $this->msg = '<script>top.location.reload();</script>';
            return;
        }
    }

}