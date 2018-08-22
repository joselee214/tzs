<?php
include_once J7Config::instance()->getAppDir('admin').'/common/ActionBackendAdmin.php';

class develop_common extends ActionBackendAdmin
{
    public function __j7construct()
    {
        parent::__j7construct();
        $this->_setLayout('_layout/adminTemplate.php');
    }
}