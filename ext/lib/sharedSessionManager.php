<?php
class sharedSessionManager
{
    public $memcacheconfig;
    public $memcachehander;

    static $session_started = false;
    static $cookie_data_in_session = false;

    public function __construct()
    {
        $this->memcacheconfig = config('sessionstorage', 'sessionstorage.php');

        $this->memcachehander = J7Cache::instance('sessionCache',$this->memcacheconfig['handlerClass'],$this->memcacheconfig['sessioncacheserver']['serverid']);
        $this->memcachehander->setCompress(0);


        if( self::$session_started === false )
        {
            if( ini_get('session.save_handler') != $this->memcacheconfig['sessioncacheserver']['handler'] )
            {
                ini_set("session.save_handler", $this->memcacheconfig['sessioncacheserver']['handler']);
                ini_set("session.save_path", $this->memcacheconfig['sessioncacheserver']['save_path']);
                if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
                    ini_set('session.lazy_write', 0);
                }
            }

            session_set_cookie_params($this->memcacheconfig['gc_maxlifetime'],'/',null);
            if( isset($_GET['__session_id']) && $_GET['__session_id'] )
            {
                session_id($_GET['__session_id']);
                //通过session处理时候...
                self::$cookie_data_in_session = true;
            }
            session_name('J7S');
            session_start();
            self::$session_started = true;
        }

        if( isset($_SESSION['id']) )
        {
            if( ($skey = $this->getSessionSaveKey()) && ($d=$this->memcachehander->get($skey.'_logininfo')) && isset($d['loginedid']) && $d['loginedid']==$_SESSION['id'] )
            {
                //强制验证用户登录
            }
            else
            {
                $_SESSION['id'] = null;
                session_destroy();
            }
        }
    }

    public function getPassportConfig()
    {
        return $this->memcacheconfig;
    }
    public function getPassportInsHander()
    {
        return $this->memcachehander;
    }

    /*
     * 注意: userfront_reglog_login_Action SessionFilter 配合使用
     * 
     * 前端同步登录的代码 //配合显示 passport_chekret @see Line 159
     */
    public function frontScriptSyncSession($rewrite_base='',$script='')
    {
        debug(isset($_SESSION['_checked'])?($_SESSION['_checked'].'_'.$this->getSessionSaveKey()):'not_checked','frontScriptSyncSession');

        if( !isset($_SESSION['_checked']) ){
            ?>
            <script>
                $(function(){
                    $.getJSON( '<?php echo $this->memcacheconfig['passportgetsidurl'];?>?callback=?' ,{fromdomain:window.location.host,sid:'<?php echo session_id();?>',f:1}, function(req){
                        <?php if( RuntimeData::registry('SYS_ENV') != 'prod' ){ ?>
                        console.log('==============sync login data==========>>>');
                        console.log(req);
                        <?php } ?>
                        if( req && req.success==1 && req.sid!=undefined )
                        {
                            $.getScript('<?php echo $rewrite_base;?>/_ucsl_chekret/?_ucsid='+req.sid,function(response){
                                <?php echo $script;?>
                            });
                        }
                    },'json');
                });
            </script>
            <?php
        }
    }

    /*
     * 获取登录后的跳转验证地址
     */
    public function getUcslCheckUrl($host=USERFRONT_HOST,$url='',$gcode='')
    {
        return (in_array($host,HOSTS_FORCE_HTTPS)?'https://':'http://').$host.'/_ucsl_chekret/?_ucsid='.$this->getSessionSaveKey().($url?('&_url='.urlencode($url)):'').($gcode?('&_gcode='.$gcode):'');
    }

    /*
     * $result = session 通过 $_ucsid 同步登陆状态
     * $result = data    获取同步本域名的登录信息
     */
    public function getSharedSessionByKey($_ucsid,$url=null,$result='session')
    {
        //注意同步 userfront_reglog_passportcheck_Action

        $ret = ['session'=>[],'cookie'=>[],'BASE_HOST'=>'']; //结果处理

        if ( $_ucsid ) {

            $d = $this->memcachehander->get(trim($_ucsid . '_logininfo'));    //获取结构信息

            if( $result=='session' && isset($_SESSION['_session_save_key']) && $_SESSION['_session_save_key']!=$_ucsid )
            {
                $this->memcachehander->del(trim($_SESSION['_session_save_key'] . '_logininfo'));
                //同浏览器更新缓存key
            }

            if ( $d && is_array($d)) {

                $ret['cookie'] = $d['cookie']??[];
                $ret['session'] = $d['session']??[];

                //解析session结构
                if (isset($d['loginedid']) && $d['loginedid']) {
                    if( $result=='session' )
                        RuntimeData::set('j7_probizinfo', '_meuid',$d['loginedid']);

                    $ret['session']['_checked'] = 1;
                    $ret['session']['id'] = $d['loginedid'];

                } else {
                    $ret['session']['_checked'] = -1; //仅第一次时候同步session
                }

                //记录登陆来源 sites 集合
                if (isset($d['loginedsites']) && is_array($d['loginedsites']) && !in_array(session_id(), $d['loginedsites'])) {
                    $d['loginedsites'][] = session_id();
                    $this->setLoginInfo($_ucsid,$d);
                }
            }
            $ret['session']['_session_save_key'] = $_ucsid; //种本域名session

            if( $result=='session' )
            {
                if( $ret['cookie'] )
                {
                    foreach ($ret['cookie'] as $k=>$v)
                    {
                        setcookie($k, $v, time() + 3600 * 24 * 300, '/',null);
                    }
                }
                if( $ret['session'] )
                {
                    foreach ($ret['session'] as $k=>$v)
                    {
                        $_SESSION[$k] = $v;
                    }
                }

                if (isset($_SESSION['id']) && $_SESSION['id'])
                    RuntimeData::set('j7_probizinfo', '_meuid',$_SESSION['id']);

                if ($url) {
                    return new ActionToken('bizexception','redirect',['url'=>$url,'type'=>'header'],null,'front');
                } else {
                    return new ActionToken($this->memcacheconfig['passport_chekret'][0],$this->memcacheconfig['passport_chekret'][1], $this->memcacheconfig['passport_chekret'][2],$this->memcacheconfig['passport_chekret'][3],$this->memcacheconfig['passport_chekret'][4]);
                }
            }
            return $ret;
        }
        return false;
    }


    /*
     * 初始化获取统一的key
     */
    function getSessionSaveKey()
    {
        if( !isset($_SESSION['_session_save_key']) )
            $_SESSION['_session_save_key'] = md5(time().session_id());
        return $_SESSION['_session_save_key'];
    }


    /*
     * 设置共享登陆的资料
     */
    function setShareData($k,$v,$ty='cookie') //$ty : cookie session
    {
        if( in_array($ty,['cookie','session']) )
        {
            $data = [$ty=>[$k=>$v]];
            $this->setShareSessionData($data);

            if( !self::$cookie_data_in_session && $ty=='cookie' )
            {
                if( is_null($v) )
                {
                    $_COOKIE[$k] = $v;
                    setcookie($k, $v, time() + 3600 * 24 * 300, '/',null);
                }
                else
                {
                    unset($_COOKIE[$k]);
                    setcookie($k);
                }
            }
            else
            {
                $_SESSION[$k] = $v;
                if( is_null($v) )
                    unset($_SESSION[$k]);
            }
        }
    }

    function setShareSessionData($data = [],$loginedsites=[])
    {
        $skey = $this->getSessionSaveKey();
        $d = $this->memcachehander->get($skey.'_logininfo');

        if(!$d){
            $d=array('loginedid'=>0,'loginedsites'=>[],'gcode'=>'','session'=>[],'cookie'=>[]);
        }

        $oldd = $d;
        if( $loginedsites )
        {
            $d['loginedsites'] = array_unique(array_merge( $d['loginedsites'] , $loginedsites));
            if( isset($data['loginedsites']) )
                unset($data['loginedsites']);
        }
        if( $data )
        {
            if( isset($data['session']) )
            {
                $d['session'] = array_merge( $d['session'] , $data['session'] );
                unset($data['session']);
            }
            if( isset($data['cookie']) )
            {
                $d['cookie'] = array_merge( $d['cookie'] , $data['cookie'] );
                unset($data['cookie']);
            }
            if( $data )
                $d = array_merge($d,$data);
        }

        if( $d != $oldd ) {
            $this->setLoginInfo($skey,$d);
        }
    }

    function initGcode($tp='cookie')
    {
        if( self::$cookie_data_in_session )
            $tp = 'session';

        if( $tp=='cookie' && isset($_COOKIE['gcode']) )
        {
            return $_COOKIE['gcode'];
        }
        if( $tp=='session' && isset($_SESSION['gcode']) )
        {
            return $_SESSION['gcode'];
        }
        if (isset($_GET['__gcode']))
            $g = $_GET['__gcode'];
        else
            $g = time().rand(1,9999).'_'.rand(0,10000);
        $this->setShareData('gcode',$g,$tp);
        return $g;
    }

    function setLoginInfo($skey,$d)
    {
        if($d && isset($d['loginedsites']) && $d['loginedsites'])
            $d['loginedsites'] = array_unique($d['loginedsites']);

        if( $d && isset($d['loginedid']) && $d['loginedid'] )
        {
            $this->setUserLogined($d['loginedid'],$skey);
        }

        $this->memcachehander->set($skey.'_logininfo',$d,$this->memcacheconfig['gc_maxlifetime']);
    }

    function setUserLogined($uid,$skey)
    {
        $userLoginedkey = '_user_is_login_'.$uid;
        $udata = $this->memcachehander->get($userLoginedkey);
        $udata = $udata?:[];
        if( !in_array($skey,$udata) )
        {
            $udata[] = $skey;
            $this->memcachehander->set($userLoginedkey,$udata,$this->memcacheconfig['gc_maxlifetime']);
        }
    }

    //用户修改密码注销所有登录
    function clearAllUserLogined($uid)
    {
        $userLoginedkey = '_user_is_login_'.$uid;
        $udata = $this->memcachehander->get($userLoginedkey);
        if( $udata )
        {
            foreach ($udata as $eskey)
            {
                $this->logoutAllSites($eskey);
                $this->memcachehander->del($eskey.'_logininfo');
            }
        }
    }

    /*
     * 获取共享登陆的资料
     */
    function getShareSessionData($hander=null,$skey=null,$exhander=null)
    {
        if( empty($skey) )
            $skey = $this->getSessionSaveKey();
        $d = $this->memcachehander->get($skey.'_logininfo');
        if($d)
        {
            if( empty($hander) )
                return $d;
            elseif( $exhander )
            {
                if( isset($d[$hander]) && isset($d[$hander][$exhander]) )
                {
                    return $d[$hander][$exhander];
                }
                return null;
            }
            elseif( isset($d[$hander]) )
                return $d[$hander];
        }
        return null;
    }


    function getUserShareSessionData($uid,$hander=null,$exhander=null)
    {

        $c = $this->getShareSessionData($hander,null,$exhander);
        if( $c )
            return $c;

        if( $uid )
        {
            $userLoginedkey = '_user_is_login_'.$uid;
            $udata = $this->memcachehander->get($userLoginedkey);
            $udata = $udata?:[];
            if( $udata ) {
                foreach ( $udata as $eskey )
                {
                    $r = $this->getShareSessionData($hander,$eskey,$exhander);
                    if( $r )
                        return $r;
                }
            }
        }
        return false;
    }

    /*
     * 登录
     */
    function setLogin($uid)
    {
        $this->logoutAllSites();
        $_SESSION['id'] = $uid;
        RuntimeData::set('j7_probizinfo', '_meuid',$uid);
        $d = array('loginedid' => $uid, 'loginedsites' => [session_id()]);
        $this->setShareSessionData($d);
    }

    /*
     * 注销所有网站登陆session
     */
    function logoutAllSites($skey=null)
    {
        $d = $this->getShareSessionData('loginedsites',$skey);
        if ($d && is_array($d)) {
            $session_prefix = $this->memcacheconfig['session_prefix'];
            foreach ($d as $ep) {
                $this->memcachehander->del( $session_prefix.$ep );
            }
        }
    }

    /*
     * 退出
     */
    function setlogout()
    {
        session_destroy();
        $this->logoutAllSites();
        $skey = $this->getSessionSaveKey();
        $this->setLoginInfo($skey,['loginedid' => 0, 'loginedsites' => [], 'gcode' => $this->initGcode()]);
    }

}