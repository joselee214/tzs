<?php

// 仅对 public 属性进行 json_encode 输出 !!!!!

class MapiViewResultType extends J7ResultType
{
	/**
	 * @param $result
	 * @param Action $action
	 * @param bool $isecho
	 * @return mixed|string
	 */
	public function process($result, $action , $retData=null ,$app=null, $isecho=true)
	{

        $data = [];

        $params = get_object_vars($action);   //$action->__getInjections(true);
        foreach( $params as $property=>$v )
        {
            if (substr($property, -7, 7) !== 'Service' && substr($property, -3, 3) !== 'DAO')
                $data[$property] = $v;
        }

        $html = $json = json_encode($data);

        $callback = $action->callback;

        if( $callback )
            $html = $callback.'('.$json.')';

        if( $isecho )
        {
            // do not cache
            header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
            header("Cache-Control: no-cache, must-revalidate,post-check=0, pre-check=0"); // HTTP/1.1
            header("Content-Type: application/x-javascript; charset=UTF-8");
            ob_start();
            echo $html;
        }
        return $html;

	}
}