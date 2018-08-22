#!/usr/bin/env php
<?php
require __DIR__ . '/j7init.php';
require J7SYS_CORE_DIR . '/template_file/action/actions.php';
header('Content-Type: text/html; charset=UTF-8');
error_reporting(6143);
ini_set('display_errors','On');

$env = 'test';
foreach ($_SERVER['argv'] as $eargv)
{
    if( substr($eargv,0,2)=='--')
    {
        var_dump($eargv);
        $env = substr($eargv,2);
    }
}

try {
    $hander = J7Initalize::getInstance('j7core',$env,'J7Initalize');

    $routers = [];
    $routers['#/c$#'] = ['index','create','execute',[]];
    $routers['#/d$#'] = ['index','daomap','execute',[]];
    $routers['#/cc$#'] = ['index','basicmap','execute',[]];

    J7Config::instance()->_set('routes',$routers);
    $hander->doDispatch('cliRouter',null,[]);
} catch (Exception $e) {
    var_dump($e);
    echo PHP_EOL.PHP_EOL.PHP_EOL.'========================'.PHP_EOL.$e->getMessage();
}