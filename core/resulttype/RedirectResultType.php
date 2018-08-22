<?php

class RedirectResultType extends J7ResultType
{
    /**
     * @param $result
     * @param Action $action
     * @return mixed|string
     */
    public function process($result, $action ,  $retData=null, $app=null ,$isecho=true)
    {
        $action_config = $action->__getConfig();
        $resource = isset($action_config[$result]['resource']) ? $action_config[$result]['resource'] : '_ret';
        $r_type = isset($action_config[$result]['r_type']) ? $action_config[$result]['r_type'] : 'header';

        $url = $action->$resource;

        $url = $url!==null?$url:$retData;


        if( $isecho )
        {
            if( $r_type=='header' )
            {
                header('Location:'.trim($url));
            }
            else
            {
                if( isset($action_config[$result]['output']) )
                    echo $action_config[$result]['output'];

                echo "<script> setTimeout(function() {
                            top.location.href='" . trim($url) . "';
                        },2000) </script>";
            }
        }
        return [$url,$result,$action];
    }
}