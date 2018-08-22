<?php

class J7View {

	private $_path;
	private $_path_layout;
	private static $_apps = [];
	public static $slots = [];

	/**
	 * @return J7View
	 */
	public static function instance($params)
	{
		return FactoryObject::Instance('J7View',[$params]);
	}

	public static function endApp()
	{
		return end(self::$_apps);
	}

	public function __construct($initparam=[])
	{
		list($action,$viewPath) = $initparam;
		$layoutPath = isset($initparam[2])?$initparam[2]:$viewPath;
		$this->setScriptPath( $viewPath , $layoutPath );

        if( $action instanceof Action )
            $params = $action->__getInjections(true);
        elseif ( is_array($action) )
            $params = $action;
        else
            throw new coreException('view instance must use Actin or array');

		foreach( $params as $property=>$v )
		{
			if (substr($property, -7, 7) !== 'Service' && substr($property, -3, 3) !== 'DAO')
				$this->$property = $v;
		}
	}


	public function setScriptPath($path,$loyoutPath=null)
	{
		$this->_path = rtrim($path, '\\/' . DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
		$this->_path_layout = $loyoutPath?:$this->_path;
	}

	//实际的载入模板 list
	protected function includeview($templatefile)
	{
		ob_start();
		$_ = $this;
		if( is_array($templatefile) )
		{
			foreach( $templatefile as $etemplate )
			{
				if( is_readable($etemplate) )
				{
					include( $etemplate );
				}
				else
					throw new J7Exception('include file Error:'.$etemplate);
			}
		}
		else
		{
			if( is_readable($templatefile) )
			{
				include( $templatefile );
			}
			else
				throw new J7Exception('include file Error:'.$templatefile);
		}
		return ob_get_clean();
	}

	private function getFilePath($file,$absPath)
	{
		if( file_exists($file) )
			return $file;
		else
			return $absPath.$file;
	}

	public function render($app,$j7template,$j7layout=null)
	{
		array_push(self::$_apps,$app);
		if( !$j7layout )
		{
			$file = $this->getFilePath($j7template,$this->_path);
			$result = $this->includeview($file);
		}
		else
		{
			$layoutfile = $this->getFilePath($j7layout,$this->_path_layout);
			if( is_array($j7template) )
			{
				$templatefile = [];
				foreach( $j7template as $j7k=>$j7v )
				{
					$templatefile[$j7k] = $this->getFilePath($j7v,$this->_path);
				}
			}
			else
				$templatefile = $this->getFilePath($j7template,$this->_path);

			$templateContent = $this->includeview($templatefile); //先执行主模板内容过程，主要为slot服务
			$result = str_replace('<J7|VIEW|CONTENT>', $templateContent, $this->includeview($layoutfile));
		}
		array_pop(self::$_apps);
		return $result;
	}

	public function renderHtml($app,$html,$j7layout=null)
	{
		array_push(self::$_apps,$app);
		if( $j7layout )
		{
			$layoutfile = $this->getFilePath($j7layout,$this->_path_layout);
			$result = str_replace('<J7|VIEW|CONTENT>', $html, $this->includeview($layoutfile));
		}
		else
		{
			$result = $html;
		}
		array_pop(self::$_apps);
		return $result;
	}

}