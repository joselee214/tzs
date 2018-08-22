<?php

class develop_index_ExecuteSql_Action extends develop_index_common
{
    public $sql = '';
    public $rs = [];//结果集
    public $k = [];//key数组
    public $thisa = 'ExecuteSql';

    public $action = '';

    public function execute()
    {
        $uid = $this->_meuid;
        $a = $this->action;
        if($a == 'ExecuteSql'){
            $this->sql = trim($this->sql);
            if($this->sql == ''){

            }else{
                $this->rs = $this->DevelopPlatformService->ExecuteSql($this->sql);
                if( substr(strtolower(trim($this->sql)),0,6)  == 'select'){
                    if(is_array($this->rs) && count($this->rs) > 0){
                        $this->k = array_keys(reset($this->rs));
                    }else{
                        $this->k = array('error');
                        $this->rs = array(array($this->rs));
                    }
                }elseif(substr(strtolower(trim($this->sql)),0,4)  == 'show'){
                    $this->k = array_keys(reset($this->rs));
                }else{
                    $this->k = array('rows_count');
                    $this->rs = array(array($this->rs));
                }
            }
        }

    }
}
