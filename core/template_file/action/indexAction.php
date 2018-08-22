<?php

class j7core_index_index_Action extends j7core_index_common
{
    public function execute()
    {
        $s_argv = $_SERVER['argv'][0];
        $module = $this->_actionToken()->getModule();

//        echo '0.==============================脚手架==============='.PHP_EOL.PHP_EOL;
        echo '1.生成 app (application即一个app域)';
        echo PHP_EOL;
//        echo '  php '.$s_argv.' '.$module.'/create app name=test ...';
        echo '  php '.$s_argv.' c app name=test ...                                                       //参数: name必选,即application名, 即下面命令中使用的参数app';
        echo PHP_EOL.'-------------------------------';
        echo PHP_EOL;
        echo '2.生成 DAO/Service                  (若要生成全局可用DAO/Service时,app=%)'; //__basic__
        echo PHP_EOL;
        echo '  php '.$s_argv.' c dao db=wd app=frontend table=develop_admin_cate,develop_admin_flag';
        echo PHP_EOL.'               //参数: 设置表可以自动附带生成AR ORM对象';
        echo PHP_EOL.'               //参数: name或者table必选, name:生成的DAO名, table:数据表名, db:数据库连接, pk:主键 map:ActiveRecord对象, class:数据基类';
        echo PHP_EOL.'               //手动指定参数:  php '.$s_argv.' c dao name=test map=test app=frontend class=DbCrudDAO|CachePKDbCrudDAO|CacheListDbCrudDAO|DAO ... pk=id';
        echo PHP_EOL;
        echo '  php '.$s_argv.' c service name=test3 app=frontend';
        echo PHP_EOL.'-------------------------------';
        echo PHP_EOL;
        echo '3.生成 module文件夹 //多个action控制器的文件夹,用以区分业务';
        echo PHP_EOL;
        echo '  php '.$s_argv.' c module name=test app=frontend';
        echo PHP_EOL.'-------------------------------';
        echo PHP_EOL;
        echo '4.生成 Action';
        echo PHP_EOL;
        echo '  php '.$s_argv.' c action name=test app=frontend module=test return=view|json ';
        echo PHP_EOL.'-------------------------------';
        echo PHP_EOL;
        echo '5. ';
        echo PHP_EOL;
        echo '  php '.$s_argv.' cc                                                                        //刷新Action/Service基类 (手动增加、删除Service或DAO后需要刷新)';
        echo PHP_EOL;
        echo '  php '.$s_argv.' d db=0 ... app=frontend tables=aaa,bbb show=3|2|1|orders,goods            //刷新ORM (数据库结构变动时需要刷新重新生成ActiveRecord对象)';
        echo PHP_EOL;
        echo PHP_EOL.'-------------------------------';
        echo PHP_EOL;
        echo '6 +++ .一次性生成 Module / Action ++++++++';
        echo PHP_EOL;
        echo '  php '.$s_argv.' c path=front:test/test return=view|json ';
        echo PHP_EOL.'-------------------------------';
        echo PHP_EOL;
        echo '配置环境默认 test 如需修改:命令行后加 --someenv';
        echo PHP_EOL;
    }
}