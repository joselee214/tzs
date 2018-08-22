<?php
class tzs_bizexception_loginwx_Action extends tzs_bizexception_common
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
        $this->_ret['_ext_result']['msg'] = '(WX)您需要登录!';
//        $this->_ret['_ext_result']['isXHR'] = Util::isAjax();


        $this->_setResultType('json');
    }

}