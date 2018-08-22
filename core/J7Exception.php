<?php
class BizException extends Exception
{
    public $params;
    public $exToken;
	public function __construct( $params=[] , $exActionName='bizexception_Index' ,$method='execute' )
	{
        $this->params = $params;
        if (strpos($exActionName, '_') === false && strpos($exActionName, '/') === false) {
            $exActionName = '/bizexception/'.$exActionName;
        }
        list($app,$module,$controller,$method) = ActionToken::explodeActionName($exActionName,$method);
        $this->exToken = new ActionToken($module,$controller,$params,$method,$app);
    }
}
class coreException extends Exception
{
	public function __construct( $msg='' )
	{
        $this->message = $msg;
    }
}
class J7Exception extends Exception
{
	public function __construct()
	{
		$argv = func_get_args();
        $lmessage = 'J7Exception in '.$this->getFile() .', on line :'.$this->getLine().' , ErrorInfo : '.var_export($argv[0],true).' !';

            //header('HTTP/1.1 500 Internal Server Error');
            $lmessage .= 'REQUEST_URI:'.(isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI']:'');
            $lmessage .= var_export($this->getTraceAsString(),true);

			if( 'J7Config' != $this->getTrace()[0]['class'] )
			{
				slog($lmessage,'exception','J7Exception');
			}
        
		$this->message = ' J7Exception Catched : '. str_replace(PHP_EOL,'',var_export($argv[0],true));
	}
}

class ForwordException extends Exception
{
    public $url,$app;
    public function __construct($url='',$app=null)
    {
        $this->url = $url;
        $this->app = $app;
    }
}