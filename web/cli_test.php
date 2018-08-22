<?php
//require_once __DIR__.'/../core/FactoryObject.php';
//$a = FactoryObject::Get('front:index_test_Action');
//var_dump($a->message());
//die;

require __DIR__ . '/../j7init.php';
error_reporting(6143);

J7Config::instance(null,'test')->loadConfigFile('domains_define_test.php');
J7Config::instance(null,'test')->loadConfigFile('cli.php');

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