<?php
//require_once __DIR__.'/../core/FactoryObject.php';
//$a = FactoryObject::Get('front:index_test_Action');
//var_dump($a->message());
//die;

require __DIR__ . '/../j7init.php';

J7Config::instance(null,'prod')->loadConfigFile('domains_define.php');
J7Config::instance(null,'test')->loadConfigFile('cli.php');

error_reporting(6143);

try {
    $front = J7Initalize::getInstance('tzs','prod');
    $front->dispatch();
} catch (Exception $e) {

//    if( in_array(Util::getRemoteAddr(),$whiteIps) )
//    {
//        echo $e->getMessage();
//    }
    //echo $e->getMessage();
    //test
}