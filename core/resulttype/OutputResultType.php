<?php

class OutputResultType extends J7ResultType
{
	/**
	 * @param $result
     * @param Action $action
	 * @return mixed|string
	 */
    public function process($result, $action, $retData=null, $app=null ,$isecho=true)
   	{
        $output = '';

        $action_config = $action->__getConfig();
        if( isset($action_config[$result]) && isset($action_config[$result]['resource']) )
        {
            $resource = $action_config[$result]['resource'];
            $output = $action->$resource;
        }

        $output = !empty($output)?$output:$retData;

        if( $isecho )
        {
            ob_start();
            if( is_array($output) )
                echo json_encode($output);
            else
                echo $output;
        }
        return [$output,$result,$action];

   	}
}