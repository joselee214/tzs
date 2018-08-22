<?php
//phpinfo();die;
define('J7SYS_CORE_DIR',__DIR__.'/core');
define('J7SYS_CONFIG_DIR',__DIR__.'/configs');

define('J7SYS_APPLICATION_DIR',__DIR__.'/app');
define('J7SYS_EXTENSION_DIR',__DIR__.'/ext');
define('J7SYS_SERVICE_DIR',__DIR__.'/service');
define('J7SYS_DAO_DIR',__DIR__.'/dao');
define('J7SYS_CLASS_DIR',__DIR__.'/class');


date_default_timezone_set('Asia/Shanghai');

require_once __DIR__ . '/core/J7Initalize.php';