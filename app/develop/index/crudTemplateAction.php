<?php


class develop_index_crudTemplate_Action extends develop_index_common
{


    public $data = [];
    public $act;
    public $param;

    public $cparam;

    public $extrownums = 0;

	public function execute()
	{


        $this->data['db_table_perfix'] = config('db_table_perfix');


        if( $this->act )
        {
            $this->_setView('data','resource','jsonp');

            $this->data['msg'] = [];

            switch($this->act)
            {
                case 'table':
                    $tablename = isset($this->data['db_table_perfdix'])?$this->data['db_table_perfdix']:''.$this->param['table_name'];
                    $dbc = config('db');
                    $db = J7Factory_Db::factory($dbc[0] );
                    $this->param['tablestr'] = [];

                    try
                    {
                        $r = $db->query("desc ".$tablename);
                        foreach($r as $row)
                        {
                            $inarr = array('field'=>$row['Field'],'type'=>$row['Type'],'default'=>$row['Default'],'isneed'=>1);
                            $this->param['tablestr'][] = $inarr;
                        }
                    }
                    catch(PDOException $e)
                    {
                    }
                    if( $this->extrownums )
                    for($i=0;$i<$this->extrownums;$i++)
                    {
                        $this->param['tablestr'][] = array('field'=>'ext'.$i,'type'=>'','default'=>'','isneed'=>1);
                    }
                    return;
                break;
                case 'create':
                    $this->param['tablestr'] = $this->cparam;
                    foreach($this->param['tablestr'] as $k=>$v)
                    {
                        //$this->param['tablestr'][]
                    }
                    //var_dump($this->param['tablestr']);die;
                    return;
                break;
            }
            return $this->_setResultType('jsonp');
        }
	}
}