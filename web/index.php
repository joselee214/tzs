<?php
//require_once __DIR__.'/../core/FactoryObject.php';
//$a = FactoryObject::Get('front:index_test_Action');
//var_dump($a->message());
//die;

require __DIR__ . '/../j7init.php';

J7Config::instance(null,'prod')->loadConfigFile('domains_define.php');

error_reporting(0);

if (function_exists('header_remove')) {
    header_remove('X-Powered-By'); // PHP 5.3+
} else {
    ini_set('expose_php', 'off');
}
ini_set('display_errors','Off');

try {
    $front = J7Initalize::getInstance('tzs','prod');
    $front->dispatch();
} catch (Exception $e) {

    if( in_array(Util::getRemoteAddr(),['180.155.109.136','222.71.133.213']) )
    {
        error_reporting(6143);
        ini_set('display_errors','On');
        echo $e->getMessage();
    }
//    echo $e->getMessage();
    //test
}