<?php
require_once J7SYS_CORE_DIR . '/DAOInterface/DbDAO.php';
require_once __DIR__ . '/develop_common.php';

class develop_cli_common extends develop_common
{
    public function __j7construct()
    {
        $this->_setView('output','type','default');
    }

    protected function getDb($k=0)
    {
        $dbc = config('db');
        return J7Factory_Db::factory($dbc[$k] );
    }
}