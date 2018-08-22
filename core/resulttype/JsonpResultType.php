<?php

class JsonpResultType extends J7ResultType
{
	/**
	 * @param $result
	 * @param Action $action
	 * @return mixed|string
	 */
	public function process($result, $action , $retData=null, $app=null,  $isecho=true)
	{
        $action_config = $action->__getConfig();
        $callback = isset($action_config[$result]['callback'])?$action_config[$result]['callback']:'';

        $resource = isset($action_config[$result]['resource'])?$action_config[$result]['resource']:'_ret';
        $retArr = $action->$resource;

        $retArr = $retArr!==null?$retArr:$retData;

        $callbackStr = $action->$callback;
        if( empty($callbackStr) )
            $callbackStr = isset($_GET['callback'])?$_GET['callback']:'callback';

		$html = $callbackStr.'('.json_encode($retArr).')';
		if( $isecho )
		{
			// do not cache
			header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
			header("Cache-Control: no-cache, must-revalidate,post-check=0, pre-check=0"); // HTTP/1.1
			header("Content-Type: application/x-javascript; charset=UTF-8");
			ob_start();
			echo $html;
		}
        return [$html,$result,$action];
	}
}
