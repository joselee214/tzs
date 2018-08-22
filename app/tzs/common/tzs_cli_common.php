<?php
//require_once __DIR__.'/tzs_common.php';

class tzs_cli_common extends tzs_basic_action
{
    //Action 加载时候执行的方法...
    public function __j7construct()
    {
        parent::__j7construct();
        $this->_setView('output','type','default');
    }
}