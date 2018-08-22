<?php
class coreUrlPath
{
    private $_rewrite_base = '/';
    private $_this_path = null;
    private $_params = [];

    public $_SERVER_REQUEST_URI;

    public function __construct($rurl=null)
    {
        if( is_null($rurl) )
            $this->_SERVER_REQUEST_URI = $_SERVER['REQUEST_URI']??'';
        else
            $this->_SERVER_REQUEST_URI = $rurl;
    }

    public function setParams($arr)
    {
		if($arr)
		{
			foreach($arr as $k=>$v)
			{
				if(strpos($k,'/')!==false)
				{
					unset($arr[$k]);
				}
			}
		}
        $this->_params = $arr;
    }

    public function getParams()
    {
        return $this->_params ;
    }

	public function getParamsWithSplit($sp='/',$jarr=[])
	{
		$ret= '';
		if($this->_params)
		{
			foreach($this->_params as $k=>$v)
			{
				if( !in_array($k,$jarr) && strpos($k,$sp)===false )
					$ret .= $sp.$k.$sp.$v;
			}
		}
		return $ret;
	}

    public function getRewriteBase()
    {
        return $this->_rewrite_base;
    }

    //设置当前路径的前置 url ，用于路径参数
    public function setRewriteBase($rewrite_base)
    {
        $this->_rewrite_base = $rewrite_base;
        $this->setActionRewriteBase($rewrite_base);
    }

    //设置Action里的 跳转路径的前置 url ，用于Action里跳转
    public function setActionRewriteBase($rewrite_base)
    {
        RuntimeData::set('j7_probizinfo','rewrite_base',$rewrite_base);
    }

    public function setThisPath($_this_path)
    {
        $this->_this_path = $_this_path;
    }

    public function getRequestUri()
    {
        if( $this->_SERVER_REQUEST_URI )
        {
            return preg_replace('#^http://[^/]+/#i', '/', $this->_SERVER_REQUEST_URI);
        }
        elseif( isset($_SERVER['argv']) && isset($_SERVER['argv'][1]) )
        {
            if( substr($_SERVER['argv'][0],-4)=='.php' )
                $uri = $_SERVER['argv'][1];
            else
                $uri = $_SERVER['argv'][0];
            if( DIRECTORY_SEPARATOR!='/' )
            {
                $uri = str_replace(DIRECTORY_SEPARATOR,'/',$uri);
            }
            return $uri;
        }
        else
            return '';

        //return isset($request)? preg_replace('#^http://[^/]+/#i', '/', $request):'';
    }

    public function getRequestPath($path = null)
    {
        if (!$path)
            $path = $this->getRequestUri();
        
        //过滤前置路径
        if ( $this->_rewrite_base && (strpos($path, $this->_rewrite_base) === 0) ) {
            $path = substr($path, strlen($this->_rewrite_base));
        }

        if( substr($path,0,1)=='?' || substr($path,0,2)=='/?' )
        {
            return null;
        }

        if ( strpos($this->_rewrite_base, '?')===false  ) {
            if ( strpos($path, '?') ) {
                $path = substr($path, 0, strpos($path, '?'));
            }
        }
        else{
            //如果前置路径有?则以&符号截取
            if ( strpos($path, '&') ) {
            $path = substr($path, 0, strpos($path, '&'));
            }
        }
        if (substr($path,0,1)=='&') {
            return null;
        }
        $path = preg_replace('#/+#', '/', $path);
        if( (strpos($path,'/')===false && strpos($path,'%2F')!==false) || strpos($path,'/')>strpos($path,'%2F') )
            $path = str_replace('%2F','/',$path);
        return '/'.trim($path,'/');
        //return preg_match('#^/#', $path) ? $path : '/'.$path;
    }

    public function getRequestPaths($path = null)
    {
        if( !$path && $this->_this_path )
            $path = $this->_this_path;
        $path  = $this->getRequestPath($path);
        $paths = $this->pathToArray($path);
        return $paths;
    }

    public function pathToArray($path,$split='/')
    {
        return explode($split, trim($path, $split));
    }

    public function getFirstPath($paths = null)
    {
        $paths = $this->getRequestPaths($paths);
        return $paths?($paths[0]?$paths[0]:''):'';
    }

}