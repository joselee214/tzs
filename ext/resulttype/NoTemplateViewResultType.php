<?php
require_once __DIR__.'/../../core/resulttype/ViewResultType.php';

class NoTemplateViewResultType extends ViewResultType
{
	/**
	 * @param $result
	 * @param Action $action
	 * @param bool $isecho
	 * @return mixed|string
	 */
	public function process($result, $action , $retData=null ,$app=null, $isecho=true)
	{
		$action->_setLayout(null);
		$html = $this->render($result,$action,$app);
		if($isecho)
		{
			ob_start();
			echo J7ActionTag::instance()->parse( $html );
		}
		return $html;
	}
}