<?php
class debugAOPAspect implements AOPInterface
{
    public $type=1;

    public function filter(baseControllerModel $obj,$callmethod=null)
    {

//        if( get_class($obj) == 'Test1Service' )
//        {
//            print_r(debug_backtrace());
//        }

        if( get_class($obj) == 'share_orders_refund_Action' )
            return true;

        if( get_class($obj) == 'Test1Service' )
            return true;
//        if( substr(get_class($obj),-7) == 'Service' )
//            return true;
        return false;
    }

    public function init(baseControllerModel $obj)
    {
        if( $this->filter($obj) )
            $this->debug('init--'.get_class($obj).'::');
        return $obj;
    }
    public function before(baseControllerModel $obj,$callmethod,$arguments=[])
    {
        if( $this->filter($obj,$callmethod) )
            $this->debug('before--'.get_class($obj).'->'.$callmethod.'('.var_export($arguments,true).')');
    }
    public function after($ret,baseControllerModel $obj,$callmethod,$arguments=[])
    {
        if( $this->filter($obj,$callmethod) )
            $this->debug('after--'.get_class($obj).'->'.$callmethod.'('.var_export($arguments,true).')',var_export($ret,true));
    }

    public function throwing(Exception $e,baseControllerModel $obj,$callmethod,$arguments=[])
    {
    }

    private function debug($info)
    {
        $args = func_get_args();
        if( $this->type==1 )
        {
            echo '<br/>=============<br/>';
            foreach ($args as $arg)
            {
                echo str_replace(PHP_EOL, '', $arg);
                echo  '<br/>';
            }
            echo '=============<br/>';
        }
        elseif ($this->type==2)
        {
            foreach ($args as $arg)
            {
                debug( str_replace(PHP_EOL, '', $arg) ,'','log');
            }
        }
    }
}