PHP 调试小工具
==================

### 介绍
    通过浏览器插件 fireBug/chromeLogger ,把php代码的调试信息输出在 console
    通过header头传递数据,不会中断 response , 适合调试 ajax 等过程

###	安装
    -	composer require j7tools/j7debug
    -   或者在你的 composer.json 的 "require" 加入 "j7tools/j7debug": "dev-master"
    -   安装浏览器插件
        Chrome 可以使用
            Webug : https://chrome.google.com/webstore/detail/cjbeipenlpoeifpkjhgakejmikdhlhcj
            Chrome Logger : https://chrome.google.com/webstore/detail/noaneddfkdjfnfdakjjmocngnfkfehhd
        FireFox 可以使用 firebug + firePHP 插件
            firebug : https://addons.mozilla.org/zh-CN/firefox/addon/firebug/?src=collection&collection_id=da0ecd99-2289-7ab0-7d57-e7c489c845c3
            firePHP : https://addons.mozilla.org/zh-CN/firefox/addon/firephp/?src=search
    
    -   注意: 打印大量信息会造成header过大,可能造成nginx错误,upstream too big 什么的错误,可以在nginx配置里调整缓冲区大小
    fastcgi_buffers  128 512k;
    fastcgi_buffer_size  10m;
    
    -   Chrome 没有 firefox 支持的好, 当大量打印trace时候, chrome容易莫名其妙崩溃

### 使用方式
-	直接在代码里插入使用

    1.	打印对象
            j7tools\j7debug::debug(['debug_value_array']);
    2.	输出到console.error
            j7tools\j7debug::debug(['debug_value_array'],'key');
    3.  打印调用堆栈信息
            j7tools\j7debug::debug(['debug_value_array'],'key','trace');
    4. 浏览器 console 支持 'log','trace','error','warn','dump','info',
        php 代码通过 define('J7_DEBUG_CONFIG','FirePHP,ChromePhp,var_dump') 配置,默认 FirePHP,ChromePhp
    5. 建议封装成自己的全局方法
        function jdebug($value,$key='',$type='log'){
            if(class_exists('j7tools\j7debug'))
                j7tools\j7debug::debug($value,$key,$type);
                }