<?php
/**
 * Class FuncHelper
 * @description 公用的一些方法，可能和其他类中的方法重复
 * @author tao
 */
class FuncHelper
{

	/**
	 * 截断字符串并且加上后缀
	 * @param $string
	 * @param $length
	 * @param int $start
	 * @param string $overflow
	 * @return string
	 */
	final public static function cn_substr( $string , $length , $start = 0 , $overflow = '...' )
	{
		if( !$string || !$length )
		{
			return $string;
		}
		$strlen = mb_strlen( $string , 'utf-8' );
		return $strlen > $length ? ( mb_substr( $string , $start , $length - 1 , 'utf-8') . $overflow ) : $string;
	}

	/**
	 * 返回带小数点的数字
	 * @param $num
	 * @return string
	 */
	final public static function number_format( $num )
	{
		return number_format($num , 2 , '.' , '');
	}

	/**
	 * 生成随机目录
	 * @static
	 * @return string
	 */
	final public static function randdir()
	{
		return date('Ym') . '/' . date('d') . '/' . substr(md5(date('YmdHis') . mt_rand(1000,9999)) , mt_rand(1,15) ,16);
	}

	/**
	 * 返回字符串的unicode编码
	 * @param $str
	 * @return string
	 */
	final public static function html2hex( $str )
	{
		$ret = '';
		for( $i = 0 ; $i < strlen($str) ; $i++)
		{
			$ret .= '&#'. ord($str[$i]) . ';';
		}
		return $ret;
	}

	/**
	 * 返回一个带压缩的js文件，并且指定资源域名的script src
	 * @return string
	 */
	final public static function paramJS( )
	{
		$domain = RES_DOMAIN . '/';
        if(isset($_SERVER['HTTPS'])&&($_SERVER['HTTPS']=='on'))
            $domain = '/';
		//nginx cotact写法
		//return '<script src="' . ($domain . '??' . implode( func_get_args() , ',' ) ) .'"></script>';
		$str = "";
		$time = date('Ymd' , Util::getTime());
		foreach( func_get_args() as $val )
		{
			$str .= '<script src="' . (!strstr($val, 'http') ? $domain : '') . $val .'?' . $time . '" charset="utf-8"></script>' . "\n";
		}
		return $str;
	}

	/**
	 * 返回一个带压缩的css文件，并且指定资源域名的rel href
	 * @return string
	 */
	final public static function paramCSS()
	{
		$domain = RES_DOMAIN . '/';
		if(isset($_SERVER['HTTPS'])&&($_SERVER['HTTPS']=='on'))
		    $domain = '/';
		//return '<link rel="stylesheet" href="' . ($domain . '??' . implode( func_get_args() , ',' ) ) .'"/>';
		$str = "";
		$time = date('Ymd' , Util::getTime());
		foreach( func_get_args() as $val )
		{
			$str .= '<link rel="stylesheet" href="' . (!strstr($val, 'http') ? $domain : '') . ltrim($val,'/') . '?' . $time . '"/>' . "\n";
		}
		return $str;
	}


	/**
	 * @param $text
	 * @return string
	 */
	final public static function encode($text)
	{
		return htmlspecialchars($text,ENT_QUOTES,'utf-8');
	}

	/**
	 * @param $text
	 * @return string
	 */
	final public static function decode($text)
	{
		return htmlspecialchars_decode($text,ENT_QUOTES);
	}

	/**
	 * 过滤富文本编辑器中的危险HTML v1.0
	 * @param $html
	 * @return mixed|string
	 */
	final public static function filterHtml($html)
	{
		/**
		2、禁止使用任何javascript
		2、禁止在html文档中写任何样式代码
		2、禁止使用iframe,frame,flash
		 */
		$html = preg_replace('/(<script[^>]*>(.*)<\/script>)/is' , '' , $html);
		$html = preg_replace('/(<style[^>]*>(.*)<\/style>)/is' , '' , $html);
		$html = strip_tags( $html , '<a>,<audio>,<b>,<big>,<caption>,<center>,<dd>,<del>,<div>,<dl>,<dt>,<em>,<fieldset>,<figcaption>,<font>,<footer>,<h1>,<h2>,<h3>,<h4>,<h5>,<h6>,<header>,<hgroup>,<hr>,<i>,<img>,<label>,<legend>,<li>,<mark>,<menu>,<nav>,<ol>,<output>,<p>,<pre>,<q>,<s>,<section>,<small>,<span>,<strike>,<strong>,<sub>,<summary>,<sup>,<table>,<tbody>,<td>,<tfoot>,<th>,<thead>,<time>,<tr>,<tt>,<u>,<ul>,<var>,<video>');
		$html = preg_replace('/on[a-zA-Z]+\=/is' , "_$0" , $html);
		$html = preg_replace('/style\=/is' , "_$0" , $html);
		$html = preg_replace('/javascript:/is' , "" , $html);
		return $html;
	}

	/**
	 * 过滤空格
	 * @param $f
	 * @return array
	 */
	final public static function strTrim( $f )
	{
		return array_map( function($v){
			if( is_string($v) )
			{
				return trim($v);
			}
			else
			{
				return $v;
			}
		} , $f );
	}

	/**
	 * 返回替换参数值后的url
	 */
	final public static function buildUrl()
	{
		$params = func_get_args();
		$get = $_GET?array_filter($_GET):[];
		foreach($params as $arr)
		{
			$value = reset($arr);
			$get[key($arr)] = $value;
		}
		return '?' . http_build_query($get);
	}

    /**
     * 过滤非法字符
     * @static
     * @param string $string
     * @return mixed
     */
    final static function filterIllegalStr($string)
    {
        $string = preg_replace('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/','',$string);
        $string = str_replace(array("\0","%00","\r"),'',$string);
        empty($isurl) && $string = preg_replace("/&(?!(#[0-9]+|[a-z]+);)/si",'&',$string);
        $string = str_replace(array("%3C",'<'),'<',$string);
        $string = str_replace(array("%3E",'>'),'>',$string);
		//$string = str_replace(array('"',"'","\t",' '),array('"',''',' ',' '),$string);
        $string = str_replace(array('"',"'","\t",' '), array('"','\'',' ',' '), $string);
        return trim($string);
    }

    /**
     * 随机生成一定程度的字符串或数字
     * @static
     * @param int $len
     * @param string $format
     * @return string
     */
    final static function randStr($len=6,$format='ALL') {
        switch($format) {
            case 'ALL':
                $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@#~';
                break;
            case 'CHAR':
                $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-@#~';
                break;
            case 'NUMBER':
                $chars='0123456789';
                break;
            default :
                $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@#~';
                break;
        }
//        mt_srand((double)microtime()*1000000*getmypid());
        $generateStr = '';
        while(strlen($generateStr) < $len)
            $generateStr .= substr($chars,(mt_rand()%strlen($chars)),1);
        return $generateStr;
    }

    /**
     * @param $str
     * @return string
     */
    final static function formatPrice($str)
    {
        return '&yen'.number_format($str, 2, '.', ',');
    }

    /**
     * @param $province
     * @param $city
     * @param $area
     * @return string
     */
    final static function formatCity($province, $city, $area)
    {
        $retStr = '';
        if ($province != false)
            $retStr .= $province;
        if ($city != $province && $city != false)
            $retStr .= ' '.$city;
        if ($area != false)
            $retStr .= ' '.$area;
        return $retStr;
    }


    /**
     * @return string
     * 获取需要登陆的链接地址
     * 注意链接的a标签里面需要额外的添加 class ＝ 'login'
     */
    final static function reqLoginUrl($referer = null,$logdomain=null,$path='/reglog/login/?url='){
        if(!defined('USERFRONT_DOMAIN')){
            return '';
        }
        $url = ($logdomain?: USERFRONT_DOMAIN ) . $path;
        $url .= $referer?$referer:( (($_SERVER['SERVER_PORT'] == '80') ?'http':'https') .'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        return $url;
    }

    /**
     * @return string
     * 获取需要注册的链接地址
     * 注意链接的a标签里面需要额外的添加 class ＝ 'reg'
     */
    final static function reqRegUrl($referer = null){
        if(!defined('USERFRONT_DOMAIN')){
            return '';
        }
        $url = USERFRONT_DOMAIN . '/reglog/reg/?url=';
        $url .= $referer?$referer:( (($_SERVER['SERVER_PORT'] == '80') ?'http':'https') .'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        return $url;
    }

    /**
     * @param $section
     * @param $code
     * @param $ext
     * @return string
     */
    static function formatPhone($section, $code, $ext)
    {
        if (empty($section) || empty($code)){
            return '';
        }
        $ret = $section.(($section&&$code)?'-':'').$code;

        if (!empty($ext))
            $ret .= '-'.$ext;
        return $ret;
    }
}