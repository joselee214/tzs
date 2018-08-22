<?php
require_once J7SYS_CORE_DIR.'/template_file/createHelper.php';
class j7core_index_basicmap_Action extends j7core_index_common
{

    public function execute()
    {
        $appdir = dir(J7SYS_APPLICATION_DIR);
        $applist = [];

        while ($entry = $appdir->read()) {
            if (substr($entry, 0, 1) == '.' || substr($entry, 0, 1) == '_') continue;
            echo $entry.PHP_EOL;
            $applist[$entry] = J7SYS_APPLICATION_DIR.DIRECTORY_SEPARATOR.$entry;
        }
        $applistconfig = config('_j7_system_application_dir','appmap.php',false);
        if( $applistconfig )
        {
            foreach ($applistconfig as $k=>$v)
            {
                if ( is_dir($v) )
                    $applist[$k] = $v;
            }
        }


        $helper = new createHelper();

        foreach ( $applist as $appname=>$apppath )
        {
            $helper->flushAppBasicAction($appname);
            $helper->flushAppBasicService($appname);
        }

        $helper->flushRootBasicAction();
        $helper->flushRootBasicService();

        echo '================='.PHP_EOL;
        echo '成功重新生成基类辅助文件'.PHP_EOL;
    }

}