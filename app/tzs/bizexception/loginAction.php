<?php
class tzs_bizexception_login_Action extends tzs_bizexception_common
{
    public $error = '';
    public $msg = '';

    public function execute()
    {
        $this->_ret['error'] = $this->error;
        $this->_ret['msg'] = $this->msg;
        $this->_ret['status'] = -1;
        $this->_ret['_ext_result']['login'] = 0;
        $this->_ret['_ext_result']['needlogin'] = 1;
        $this->_ret['_ext_result']['loginurl'] = '/index/login';
        $this->_ret['_ext_result']['msg'] = '您需要登录!';
//        $this->_ret['_ext_result']['isXHR'] = Util::isAjax();

        if( Util::isAjax() ) //web XHR
        {
            $this->_setResultType('json');
        }
        elseif( isset($_GET['from']) && ($_GET['from'] == 'wxxcx') ) //小程序webview
        {
            $this->_setResultType('default');
        }
        elseif( isset($_GET['__session_id']) && isset($_GET['openid']) ) //小程序内数据访问
        {
            $this->_setResultType('json');
        }
        else
        {
            //redirect to login
            $this->_forwordUrl('/index/index');
        }
    }

}