<?php

class develop_index_index_Action extends develop_index_common
{
    public $tp='login';
    public $name;
    public $password;
    public $repassword;

    public function execute()
    {
        if( $this->tp=='logout' )
        {
            $this->DevelopAdminService->AdminLogout();
            return $this->redirect($this->rewrite_base.'/');
        }
        elseif( $this->tp=='editpassword' )
        {
            $this->password = trim($this->password);
            if( $this->password && $this->password==$this->repassword )
            {
                $update = array('uid'=>$_SESSION['adminuid'],'password'=>$this->password);
                $this->DevelopAdminService->updateAdminUser($update);
                return $this->redirect($this->rewrite_base.'/');
            }
            $this->_setView('index/panel/editpassword.php');
        }
        else
        {
            if( $this->name && $this->password )
            {
                $c = $this->DevelopAdminService->checkAdminPWD($this->name,$this->password);
                if( $c && isset($c['ret']) && $c['ret']==1 )
                {
                    $url = $this->rewrite_base. '/index/AdminIndex';
                    return $this->redirect($url);
                }
            }
        }
    }
}