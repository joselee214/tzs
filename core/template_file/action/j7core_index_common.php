<?php
require_once J7SYS_CORE_DIR . '/DAOInterface/DbDAO.php';

class j7core_index_common extends Action
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