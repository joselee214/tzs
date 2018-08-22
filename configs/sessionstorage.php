<?php

$J7CONFIG['sessionstorage'] = array(
//    'passport_chekret' => array('reglog', 'logincheckresult', [],'','userfront'),   //登录后的配合 getScrpit 的地址
//
//    'passportgetsidurl' => USERFRONT_DOMAIN . '/reglog/login/~getsid/',
//    'passportloginurl' => USERFRONT_DOMAIN . '/reglog/login/',
//    'passportlogouturl' => USERFRONT_DOMAIN . '/reglog/login/~logout',
    //用于单点登陆的session配置
    'sessioncacheserver' => array(
        'handler' => 'memcached',  // memcached | redis
        'save_path' => '127.0.0.1:11211',   //  tcp://127.0.0.1:6379   |  127.0.0.1:11211
        'serverid'=>0,  //指向配置里的memcache server id
    ),
    'handlerClass'=>'coreMemCache',  //coreMemCache RedisCache
    'session_prefix' => ini_get('memcached.sess_prefix'), // 'PHPREDIS_SESSION:'  ini_get('memcached.sess_prefix')
    'gc_maxlifetime' => 86400,
);