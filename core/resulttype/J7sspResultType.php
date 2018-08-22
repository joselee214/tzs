<?php

class J7sspResultType extends J7ResultType
{
	/**
	 * @param $result
	 * @param Action $action
	 * @return mixed|string
	 */
	public function process($result, $action, $retData=null, $app=null , $isecho = true)
	{

        $action_config = $action->__getConfig();
        $resource = $action_config[$result]['resource'];
        $retData = $action->$resource;

        $html = 'j7format:json:______' . json_encode($retData);
		if( $isecho )
        	echo $html;
        return $html;
	}
}