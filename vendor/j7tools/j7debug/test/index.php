<?php
/**
 * Created by PhpStorm.
 * User: jose
 * Date: 16/9/1
 * Time: 上午11:19
 */

require_once __DIR__ . '/../src/j7debug.php';

//j7tools\j7debug::config(['BACKTRACE_LEVEL'=>0,'BACKTRACE_DEEP'=>5],'config');

function jdebug($value,$key='',$type='log'){
    if(class_exists('j7tools\j7debug'))
        j7tools\j7debug::debug($value,$key,$type);
}

function dtest(){
    jdebug('jdebug_v','jdebug_k');
}

dtest();

j7tools\j7debug::debug('value','key');

echo PHP_EOL.'PAGE Loaded !'.PHP_EOL;