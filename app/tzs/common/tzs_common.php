<?php
require_once J7SYS_CLASS_DIR.'/basichelp/tzs_basic_action.php';
class tzs_common extends tzs_basic_action
{

    public $page=1;
    public $limit=10;

    /**
     * @var J7Page
     */
    protected $ps;

    public function __j7construct()
    {
        parent::__j7construct();
        $this->ps = new J7Page($this->limit, $this->page);
    }
}