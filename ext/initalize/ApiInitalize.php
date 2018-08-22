<?php
class ApiInitalize extends J7Initalize
{
    protected function _initResultType()
    {
        $return = 'jsonp';
        if( isset($_REQUEST['returnType']) && in_array($_REQUEST['returnType'],['json','jsonp']) )
        {
            $return = $_REQUEST['returnType'];
        }
        $this->_disposal->setDefaultResuleType([$return,null]);
    }
}
