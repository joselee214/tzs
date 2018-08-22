<?php

class develop_index_AdminIndex_Action extends develop_index_common
{
    public $ushow;
    public $tp;

    public $treemenu = array('TOP' => '');

    public $modulemenu;

    public $set;

    public function execute()
    {
        if ($this->ushow)
        {
//            $this->_config[$this->ushow] = array("type" => "view", "resource" => "index/panel/{$this->ushow}.php" ,'layout'=>$this->_config['default']['layout']);
            $this->_setView("index/panel/{$this->ushow}.php",'resource',$this->ushow);
            $this->_setView("view",'type',$this->ushow);
            $this->_setLayout($this->__getConfig('default','layout'),'layout',$this->ushow);

        }
        $this->_setView('index/panel/AdminIndex.php');
        $this->_setLayout(null);

        switch ($this->ushow)
        {
            case 'welcome' :
                break;
            case 'treemenu' :
                $allmenu = $this->DevelopAdminService->GetUserFlag($this->_meuid,false);
                if( $this->tp && isset($allmenu['menu'][$this->tp]) )
                    $this->treemenu = $allmenu['menu'][$this->tp];
                else
                    $this->treemenu = current($allmenu['menu']);
                break;
            case 'modulemenu' :
                $this->treemenu = $this->DevelopAdminService->GetUserFlag($this->_meuid,false);

                if( $this->set && is_numeric($this->set) && $this->set>0 )
                {
                    $_SESSION['pageperset'] = $this->set;
                }
                break;
            default:
                break;
        }

        $this->_setResultType($this->ushow ? $this->ushow : 'default');
        return $this->ushow ? $this->ushow : 'default';
    }
}