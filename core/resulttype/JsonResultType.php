<?php
 
class JsonResultType extends J7ResultType
{
    /**
     * @param $result
     * @param Action $action
     * @return mixed|string
     */
	public function process($result, $action, $retData=null, $app=null, $isecho=true)
	{
        $action_config = $action->__getConfig();
        $resource = isset($action_config[$result]['resource'])?$action_config[$result]['resource']:'_ret';
        if (is_string($action->$resource))
            $retArr = array('status' => $action->$resource);
        else
            $retArr = $action->$resource;

        $retArr = $retArr!==null?$retArr:$retData;

        if($isecho)
        {
            $html = json_encode($retArr);
            // do not cache
            header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
            header("Cache-Control: no-cache, must-revalidate,post-check=0, pre-check=0"); // HTTP/1.1
            header("Content-Type: application/json; charset=utf-8");

            ob_start();
            echo $html;
            return $html;
        }
        else
        {
            return [$action->$resource,$result,$action];
        }
	}
}
