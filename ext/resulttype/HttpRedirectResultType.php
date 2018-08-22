<?php

class HttpRedirectResultType extends J7ResultType
{
    /**
     * @param $result
     * @param Action $action
     * @return mixed|string
     */
    public function process($result, $action , $retData=null , $app=null ,$isecho=true)
    {
        $action_config = $action->__getConfig();
        $resource = isset($action_config[$result]['resource'])?$action_config[$result]['resource']:'_ret';
        $r_type = isset($action_config[$result]['r_type'])?$action_config[$result]['r_type']:'header';
        $output = isset($action_config[$result]['output'])?$action_config[$result]['output']:'';

        $url = $action->$resource;

        if( $isecho )
        {
            if( $r_type=='header' )
            {
                header('Location:'.trim($url));
            }
            elseif( $output )
            {
                echo '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml" lang="zh-cn"><head>';
                echo '<meta http-equiv="content-type" content="text/html; charset=utf-8"/><link rel="shortcut icon" href="/favicon.ico" />';
                echo FuncHelper::paramJS('res/js/jquery/jquery.min.js','res/js/common/common.js');
                echo '</head><body>';
                echo "<script>common.popup('".$output."');setTimeout(function(){top.location.href='" . trim($url) . "'}, 3000)</script>";
                echo '</body></html>';
            }
            else
            {
                echo "<script> top.location.href='" . trim($url) . "'</script>";
            }
        }
        return $url;
    }
}