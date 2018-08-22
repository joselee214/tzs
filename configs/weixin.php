<?php
// xcx_xcx_test1@upjiaju.com
//$J7CONFIG['wx_xcx']['AppID'] = 'wxb00616f23a844b5b';

//xcx_xcx1@upjiaju.com
$J7CONFIG['wx_xcx']['AppID'] = 'wxd987482b4d894bc5'; //默认，用于生成全局扫码图的appid
$J7CONFIG['wx_xcx']['wxPCwebLogin'] = 'wx911cf9324fd43301'; //开发平台登录Appid

//用于寻找 AppSecret //多app时候
//appid => [AppSercet,Mch_id,z支付API秘Sign,]
$J7CONFIG['wx_xcx']['AppSecretMap'] = [
    'wxd987482b4d894bc5' => ['','',''], //
    'wx911cf9324fd43301' => ['','',''],
];