<?php
//require_once __DIR__.'/../core/FactoryObject.php';
//$a = FactoryObject::Get('front:index_test_Action');
//var_dump($a->message());
//die;

require __DIR__ . '/../j7init.php';

J7Config::instance(null,'test')->loadConfigFile('domains_define_test.php');

error_reporting(6143);

if (function_exists('header_remove')) {
    header_remove('X-Powered-By'); // PHP 5.3+
} else {
    ini_set('expose_php', 'off');
}
ini_set('display_errors','Off');

try {
    $front = J7Initalize::getInstance('tzs','test');
    $front->dispatch();
} catch (Exception $e) {

//    if( in_array(Util::getRemoteAddr(),$whiteIps) )
//    {
//        echo $e->getMessage();
//    }
    echo $e->getMessage();
    //test
}