<?php
class Action extends baseControllerModel
{
    public $_ret = [];                //模式返回,数据放这个上
    public $callback;    //一般给jsonp用
    public $rewrite_base = '';        //框架引入路径
    public $__runingMethod = 'execute';
    public $__requestMethod = 'get';    //get post ...  = strtolower($_SERVER['REQUEST_METHOD'])
    /**
     * @var ActionToken
     */
    private $__actionToken;

    private $_config = array(
        "default"   => array('type' => 'view'),
        'jsonp'     => array('type' => 'jsonp',   'resource'=>'_ret', 'callback'=>'callback'),
        'json'      => array('type' => 'json',    'resource'=>'_ret',),
        'action'      => array('type' => 'action',    'resource'=>'_ret'),
        'redirect'  => array('type' => 'redirect','r_type'=>'header', 'resource'=>'_ret'),
        'output'      => array('type' => 'Output',    'resource'=>'_ret' ),
        'html'      => array('type' => 'Html',    'resource'=>'_ret' ),
    );

	/*
	 * **********************************************************
	 * 模版层方法
	 * **********************************************************
	 */
	private $__resultType = null;
    //返回模式的设置,可以子类做覆盖
    public function _setResultType( $rettype = 'default' , $data=null)
    {
        $ret = $this->__getConfig($rettype,'resource');
        if( isset($this->$ret) && $data )
        {
            $this->$ret = $data;
        }
        $this->__resultType = $rettype;
        return;
    }
    public function _getResultType()
    {
        return $this->__resultType;
    }
    public function _setView($template,$res=null,$handel=null)
    {
        $handel = $handel?:($this->__resultType?:'default');
        $res=$res?:'resource';
        $this->_config[$handel][$res] = $template;
    }
    public function _setLayout($template,$res=null,$handel=null)
    {
        $handel = $handel?:($this->__resultType?:'default');
        $res=$res?:'layout';
        if( isset($this->_config[$handel]) )
            $this->_config[$handel][$res] = $template;
    }
    /*
     * **********************************************************
     * 内部方法 :: 在业务中一般不用
     * **********************************************************
     */
    public function __getConfig($handel=null,$res=null)
    {
        if( is_null($handel) && is_null($res) )
            return $this->_config;
        elseif( is_null($res) )
            return $this->_config[$handel];
        else
            return $this->_config[$handel][$res]??null;
    }

    /*
     * **********************************************************
     * 跨Action方法
     * **********************************************************
     *@var string $fullActionName 'share:orders_orderLogs'
     */
    //执行另外一个action + 另一个view方法，但是还是使用本身的layout
    protected function _forword($fullActionName,$param=[],$paramsProtected=[])
    {
        $param = array_merge($this->_actionToken()->getParams(),$param);
        list($app,$module,$controller,$method) = ActionToken::explodeActionName($fullActionName,$this->_actionToken()->getMethod());
        $this->_ret = new ActionToken($module,$controller,$param,$method,$app,$paramsProtected);
        return $this->_setResultType('action');
    }

    //隐式 跳转 url //包括执行前置等等
    protected function _forwordUrl($url)
    {
        throw new ForwordException($url);
    }

    /**
     * 跳转
     */
    protected function redirect($url='/',$type='header',$isAddRewrite=true)
    {
        $this->_ret = trim($url);
        if( $this->rewrite_base && $isAddRewrite )
        {
            if( strpos($this->_ret,'http://')!==0 && strpos($this->_ret,'https://')!==0 && strpos($this->_ret,$this->rewrite_base)!==0 )
                $this->_ret = $this->rewrite_base.$this->_ret;
        }

        $type = $type?:'header';
        $this->_setView($type,'r_type','redirect');
        return $this->_setResultType('redirect');
    }
    /**
     * 获得另外一个action执行体内容
     */
    protected function _getActionAnother($fullActionName,$param=[])
    {
        $param = array_merge($this->_actionToken()->getParams(),$param);
        return J7ActionTag::instance()->getTag($fullActionName,$param);
    }

	/*
	 * **********************************************************
	 * 默认初始执行方法
	 * **********************************************************
	 */
	public function __j7construct()
	{
		$this->rewrite_base = RuntimeData::get('j7_probizinfo','rewrite_base'); //框架位于的分目录
	}
	public function __j7destruct()
	{

	}


    /**
     * @param ActionToken|null $v
     * @return ActionToken
     */
    public function _actionToken(ActionToken $v=null)
    {
        if( $v )
            return $this->__actionToken = $v;
        return $this->__actionToken;
    }
}