<?php
/**
 * curl类
 * Class Curl
 */
class Curl
{
	private $_curl;
	private $_url = null;
	private $_timeout = 10;
	private $_debuglevel=0;
	public function __construct( $url , $timeout=10 , $debuglevel=0 )
	{
		$this->_url = $url;
		$this->_timeout = $timeout;
		if( $debuglevel )
			$this->_debuglevel = $debuglevel;
		else
		{
			if( defined('J7SYS_DEBUG') && J7SYS_DEBUG )
				$this->_debuglevel = 1;
		}
		$this->_open();
	}

	public function __destruct()
	{
	    if( $this->_curl )
		    curl_close($this->_curl);
	}

	public function _getCurl()
	{
		return $this->_curl;
	}

	public function getError()
	{
		return curl_error($this->_curl);
	}

	public function getInfo($option)
	{
		return curl_getinfo( $this->_curl , $option );
	}

	private function _open()
	{
		$curlHandle = curl_init();
		curl_setopt( $curlHandle , CURLOPT_URL , $this->_url ); //指定url
		curl_setopt( $curlHandle , CURLOPT_RETURNTRANSFER , true ); //返回源码
		curl_setopt( $curlHandle , CURLOPT_SSL_VERIFYPEER, false); //关闭ssl
        if( isset($_SERVER['HTTP_USER_AGENT']) )
		    curl_setopt( $curlHandle , CURLOPT_USERAGENT , $_SERVER['HTTP_USER_AGENT']);//模拟一个header
		curl_setopt( $curlHandle , CURLOPT_TIMEOUT, $this->_timeout ); //读取的最大时间秒
		curl_setopt( $curlHandle , CURLOPT_FRESH_CONNECT ,true); //关闭内容缓存
		$this->_curl = $curlHandle;
	}

	private function _header_info($header=[])
	{
		$ret = [];
		if($header)
		{
			foreach ($header as $k=>$v)
			{
				if( is_numeric($k) )
					$ret[] = $v;
				else
					$ret[] = $k.': '.$v;
			}
		}
		return $ret;
	}

	public function setHeader($header=[])
    {
        return $this->_header_info($header);
    }

	public function get($data='',$header=[])
	{
		if( $header )
			$header = $this->_header_info($header);
		if( is_array($data) )
		{
			$data = http_build_query($data);
		}
		if( strpos($this->_url,'?')>0 )
		{
			$this->_url = $this->_url.'&'.$data;
		}
		else
		{
			$this->_url = $this->_url.'?'.$data;
		}
		curl_setopt( $this->_curl , CURLOPT_URL , $this->_url );
		curl_setopt( $this->_curl , CURLOPT_POST , false);
		curl_setopt($this->_curl, CURLOPT_HTTPHEADER, $header);
		if( $this->_debuglevel>0 )
			debug($this->_url,'Curl:GET:url');
		$ret = curl_exec( $this->_curl );
		return $ret;
	}

	public function post($data='',$tp='form',$header=[])
	{
		curl_setopt( $this->_curl , CURLOPT_POST , true);

		//content-type: application/x-www-form-urlencoded
		//content-type: multipart/form-data

		if( $tp=='json' )
		{
			$header = array_merge($header,array('Content-Type'=>'application/json; charset=utf-8'));
			$header = $this->_header_info($header);
			if( is_array($data) )
				$data = json_encode($data);
			curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $data);
			curl_setopt($this->_curl, CURLOPT_HTTPHEADER, $header);
		}
		elseif ( $tp=='x-www-form-urlencoded' )
		{
			$header = array_merge($header,array('Content-Type'=>'application/x-www-form-urlencoded'));
			$header = $this->_header_info($header);
			if( is_array($data) )
				$data = http_build_query($data);
			curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $data);
			curl_setopt($this->_curl, CURLOPT_HTTPHEADER, $header);
		}
		elseif ( $tp=='form-data' )
		{
			$header = array_merge($header,array('Content-Type'=>'multipart/form-data'));
			$header = $this->_header_info($header);
			curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $data);
			curl_setopt($this->_curl, CURLOPT_HTTPHEADER, $header);
		}
		else
		{
			curl_setopt( $this->_curl , CURLOPT_POSTFIELDS , $data);
		}

		if( $this->_debuglevel>0 )
		{
			debug($this->_url,'Curl:POST:url:'.$tp);
			debug($data,'Curl:POST:data');
		}
		$ret = curl_exec( $this->_curl );
		return $ret;
	}

	/**
	 * 暂时无用
	 */

	public function put($file,$header=[])
	{
		$header = array_merge($header,array('Content-Type'=>'text/xml;charset=UTF-8','x-bs-ad'=>'private'));
		$header = $this->_header_info($header);
		curl_setopt($this->_curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt ( $this->_curl, CURLOPT_NOSIGNAL, true );
		curl_setopt ( $this->_curl, CURLOPT_CUSTOMREQUEST, 'PUT' );

		$file_size = filesize($file);
		$h = fopen($file,'r');
		curl_setopt ( $this->_curl, CURLOPT_INFILESIZE, $file_size);
		curl_setopt ( $this->_curl, CURLOPT_INFILE, $h);
		curl_setopt ( $this->_curl, CURLOPT_UPLOAD, true );

		if( $this->_debuglevel>0 )
			debug($this->_url,'Curl:PUT:url');
		$ret = curl_exec( $this->_curl );
		return $ret;
	}

	public function delete($header=[])
	{
		$header = $this->_header_info($header);
		if( $header )
			curl_setopt($this->_curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt ( $this->_curl, CURLOPT_NOSIGNAL, true );
		curl_setopt ( $this->_curl, CURLOPT_CUSTOMREQUEST, 'DELETE' );
		if( $this->_debuglevel>0 )
			debug($this->_url,'Curl:DELETE:url');
		$ret = curl_exec( $this->_curl );
		return $ret;
	}

}