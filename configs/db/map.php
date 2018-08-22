<?php

//map config,用于生成map时候,有可能导致的命名重复冲突解决问题 // 数据库连接id =>表名=>对象类名 //表名可以重复..对象类名不能重复
$J7CONFIG['table_map_rename'] = [
    'config_connect_indexId' =>['real_table_name'=>'data_map_class_name'], //'0'=>['orders'=>'new_orders','_refund'=>'x_refund'],
];

//ORM继承的类,默认为 J7Data , //注意在全局引入 _j7_system_globalfunctions
$J7CONFIG['table_map_baseclass'] = [
    '0' =>['__baseClass'=>'J7Data','users'=>'j7data_for_output','wx_users'=>'j7data_for_output'],
//    'config_connect_indexId' =>['__baseClass'=>'J7Data','real_table_name'=>'data_map_base_class_name'],                 //默认的基类
];