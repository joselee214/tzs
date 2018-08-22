<?php
//require_once __DIR__.'/tzs_common.php';

class tzs_apis_common extends tzs_common
{
    public $appid;
    public $openid;

    public $__gcode;

    public $encryptedData;
    public $iv;

    public $cachekeyTempUpload;

    public $formId;

    //Action 加载时候执行的方法...
    public function __j7construct()
    {
        parent::__j7construct();
        $this->_setResultType('json');

        $this->cachekeyTempUpload = 'wxupload_'.$this->_userme['uid'].'_'.$this->__gcode;
    }

    public function decryptedData()
    {
        $xcx_config = Util::wxAppInfo($this->appid);
        $this->appid = $xcx_config[0];

        if( $this->openid && $this->appid )
        {
            $wxuser = $this->UserExtPlatformService->getWxUserByOpenid($this->openid, $this->appid);
        }

        if( $wxuser && $this->encryptedData && $this->encryptedData!='undefined' && $this->iv && $this->iv!='undefined' )
        {
            require_once J7SYS_EXTENSION_DIR.'/lib3rd/weixin/wxBizDataCrypt.php';
            $pc = new WXBizDataCrypt($this->appid, $wxuser->_session_key());
            $errCode = $pc->decryptData($this->encryptedData, $this->iv, $endata );

            if ($errCode == 0 && $endata && ($en_data=json_decode($endata,true)) ) {
                return $en_data;
            }
        }

        return false;
    }

}