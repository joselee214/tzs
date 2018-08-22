<?php
require_once J7SYS_CORE_DIR.'/template_file/createHelper.php';

class j7core_index_daomap_Action extends j7core_index_common
{
    public $db='%';
    public $tables;
    public $app=null;
    public $show=false;
    public $ignorepre='_';
    /**
     * 生成辅助数据类对象//扫描全库
     * 生成 /dao/map/ 下所有文件
     */
    public function execute()
    {
        $helper = new createHelper();

        if( $this->show )
        {
            if( is_numeric($this->show) )
                $helper->showContent = $this->show;
            else
                $helper->showContent = explode(',',$this->show);
        }

        if( $this->db == '%' )
        {
            $dbc = J7Config::instance($this->app)->get('db','db/data_source.php');
            foreach ($dbc as $ek=>$v)
            {
                echo '=================DB:'.$ek.PHP_EOL;
                echo '=================:'.PHP_EOL;
                echo '=================:'.PHP_EOL;
                echo '=================:'.PHP_EOL;
                $helper->daomap($ek,$this->app,$this->tables,$this->ignorepre);
            }
        }
        else
        {
            $helper->daomap($this->db,$this->app,$this->tables,$this->ignorepre);
        }

        $helper->writeArVersionConfig();

        echo '================='.PHP_EOL;
        echo 'read config @ ...'.PHP_EOL;
        echo 'success create ORM active record object files'.PHP_EOL;
    }

}