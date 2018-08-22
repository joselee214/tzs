<?php

/**************************************
 * 框架的数据运行配置
 ***************************************/


$J7CONFIG['cache_perfix'] = '';
$J7CONFIG['cache_perfix_redis'] = '';

$J7CONFIG['rd_table_map'] = array('test'=>'1');		    //Redis表前缀,转映射,以节省key值容量

$J7CONFIG['dao']['data_source'] = 'DB';					//执行数据源具体类 DbDAOFactory
$J7CONFIG['db_table_perfix'] = '';					//所有表前缀


$J7CONFIG['db'][0] = array(
    'adapter'   => 'PDO_Mysql',
    'charset'  => 'utf8mb4',
    'host'      => '127.0.0.1',

    'username'  => 'jlq',  // nw
    'password'  => 'jlq',
    'dbname'    => 'jlq',
    'port'    => 3307,
    'profiler'  => '1',
	//'j7map_perfix' =>'a_',  //对象类名前缀 //参考目录下 map.php 设置 //先应用 table_map_rename ,再累加这个 j7map_perfix
);


//SLAVE组设置,供查询库对应使用,应该与db设置对应
$J7CONFIG['slavedb'] = array(0=>10);

$J7CONFIG['log_server'] = array('host' => '127.0.0.1','port' => '11211');
$J7CONFIG['memcache'][0] = array(
    array('host' => '127.0.0.1','port'  => 11211),
);

//Beanstalk
//$J7CONFIG['queue'][0] = array('type'=>'BeanStalk','servers' => array('127.0.0.1:11300'));
$J7CONFIG['queue'][0] = array('type'=>'Socket_Beanstalk','host' => '127.0.0.1','port'  => 11300 ,'persistent' => true,'timeout' => 1);


//redis的多服务器方案无需通过分组来实现，必须是一台台加数据
$J7CONFIG['redis'][0] = ['source'=> array('host' => '127.0.0.1', 'port' => 6379 ),];



/**************************************
 * 框架的分库运行配置
 ***************************************/
//索引表使用的配置项 //及核心文件使用(例如多包缓存结果)
$J7CONFIG['groupcore0'] = array('db'=>0,'memcache'=>0,'redis'=>0); //核心索引表，usersplit使用
$J7CONFIG['groupcore-1'] = array('db'=>0,'memcache'=>0,'redis'=>0); //系统的abtesting，fsadmin一些表使用
$J7CONFIG['groupcore-2'] = array('db'=>0,'memcache'=>0,'redis'=>0); //userfeedlog这个使用

//默认服务器数据组设置,0即默认组
$J7CONFIG['group']['database'][0] = array(
    'db'=>array(0),
//    'db_table_seg' => array(
//        'user_maps'=>array('ct'=>range(0,3)),   //'user_maps'=>array('ct'=>range(0,3),'ids'=>array(0,1,2),'tabledb'=>array(0=>0,1=>0,2=>0,3>0)),
//        'user_tasks'=>array('ct'=>range(0,2)),
//    ),
);

$J7CONFIG['group']['memcache'][0] = 0;
//redis的组即单台服务器来实现....
$J7CONFIG['group']['redis'][0] = 0;

//各分组权重分配 gid=>权重
$J7CONFIG['grouppres'] = array(
    'database'=>array(0=>100),
    'memcache'=>array(0=>100),
    'redis'=>array(0=>100),
);


/**************************************
 * Queue队列服务运行配置
 ***************************************/

$J7CONFIG['queue'][0] = array('type'=>'Socket_Beanstalk','host' => '127.0.0.1','port'  => 11300 ,'persistent' => true,'timeout' => 1);

$J7CONFIG['queue']['queue_alias'] = ['servicedao'=>0];