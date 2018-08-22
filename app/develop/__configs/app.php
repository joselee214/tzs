<?php
require __DIR__ . '/../../../configs/app.php';

$J7CONFIG['j7initalize']['_default'] = ['filters'=>['DevelopAdminauth']];

$J7CONFIG['_j7_system_basic_class_action'] = 'ActionBackendAdmin'; //Action基类 //Service基类 _j7_system_basic_class_service