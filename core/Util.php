<?php
class Util
{
    static function wxAppInfo($appid=null,$type=null) //$type= null | facilitator
    {
        if( is_null($appid) && isset($_GET['appid']) )
            $appid = $_GET['appid'];

        $xcx_config = config('wx_xcx','weixin.php');
        if( empty($appid) )
        {
            $appid = $xcx_config['AppID'];
        }
        $ckey = $appid.($type?('.'.$type):'');
        if( !isset($xcx_config['AppSecretMap'][$ckey]) )
        {
            $ckey = $xcx_config['AppID'];
        }
        return [$appid,$xcx_config['AppSecretMap'][$ckey][0],
            $xcx_config['AppSecretMap'][$ckey][1]??'',
            $xcx_config['AppSecretMap'][$ckey][2]??''];
    }
    static function checkRefferAndHost()
    {
        return  ( php_sapi_name() == 'cli' || strpos($_SERVER['HTTP_REFERER']??'','https://'.($_SERVER['HTTP_HOST']??''))==0 || strpos($_SERVER['HTTP_REFERER']??'','http://'.($_SERVER['HTTP_HOST']??'')) );
    }

    static function xrange($start, $end, $step = 1) {
        for ($i = $start; $i <= $end; $i += $step) {
            yield $i;
        }
    }

    //通过函数来获得
    static function getVars($className)
    {
        return get_class_vars($className);
    }

    static function def($name, $value)
    {
        if (!defined($name))
            define($name, $value);
    }

    static function getProperties($object)
    {
        if (!is_object($object)) {
            throw new J7Exception(__CLASS__ . "" . __LINE__);
        }
        $properties_return = [];
        $class = new ReflectionClass(get_class($object));
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            if ($property->isPublic()) {
                $properties_return[$property->name] = $property->getValue($object);
            }
        }
        return $properties_return;
    }

    static function checkPropertryExists($name, $properties)
    {
        foreach ($properties as $property) {
            if ($property->name == $name) {
                return $property;
            }
        }
        return false;
    }

    static function isReadable($filename)
    {
        return is_readable($filename);
        //return is_readable($filename)?is_readable($filename):RuntimeData::isReadable($filename);
    }


    static function arrayToXml($arr,$p='xml') {
        if($p)
            $xml = '<'.$p.'>';
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $xml .= "<" . $key . ">" . Util::arrayToXml($val,null) . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            }
        }
        if($p)
            $xml .= '</'.$p.'>';
        return $xml;
    }
    static function xmlToArray($xml) {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring), true);
        return $val;
    }

    static function isWritable($filename){
        return is_writable($filename);
    }

    static function underscore($name)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1_$2", $name));
    }

    static function getRemoteAddr()
    {
        $ip = false;
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ip) {
                array_unshift($ips, $ip);
                $ip = FALSE;
            }
            for ($i = 0; $i < count($ips); $i++) {
                if (!preg_match("/^(10|172\.16|192\.168)\./i", $ips[$i])) {
                    $ip = $ips[$i];
                    break;
                }
            }
        }
        return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }

    static function getRemoteAddr_other()
    {
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) {
            $ip = getenv("REMOTE_ADDR");
        } else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = '';
        } //end if
        return $ip;
    }

    static function getIPLocation($ip = null)
    {
        if (!$ip) {
            $ip = self::getRemoteAddr();
        }
        try {
            var_dump('service/SysRegionService.php :: setCurrentIp');
//            require_once J7SYS_EXTENSION_DIR . '/lib/Curl.php';
//            $x = new Curl('http://ip.taobao.com/service/getIpInfo.php?ip='.$ip);
//            $d = $x->get();
//            var_dump($d);
            die;
        } catch (Exception $e) {
            return 'N/A';
        }
    }

    static private $g_time = null;

    static public function dateTime($t=null,$format='Y-m-d H:i:s')
    {
        return date($format,$t?:time());
    }
    static public function getTime()
    {
        $time = self::$g_time;
        if ($time == null) {
            $time = $_SERVER['REQUEST_TIME']??time();
            self::$g_time = $time;
        }
        return $time;
    }

    static public function setTime($time)
    {
        self::$g_time = $time;
    }

    static function gb_substr($str, $length)
    {
        $strlen = strlen($str);
        $array = [];
        for ($i = 0; $i < $strlen; $i++) {
            if (ord(substr($str, $i, 1)) > 0xa0) {
                array_push($array, (substr($str, $i, 3)));
                $i += 2;
            } else {
                array_push($array, (substr($str, $i, 1)));
            }
        }
        if ($length > count($array))
            $length = count($array);
        $returnStr = '';
        for ($i = 0; $i < $length; $i++) {
            $returnStr .= $array[$i];
        }
        return $returnStr;
    }

    static function getIdMicroTime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return $sec . substr($usec, 2, 6);
    }

    static function getIncrId($Map, $inc = 1)
    {
        $k = 'J7T:' . $Map . ':IncrId';
        return coreRedisData::instance()->incr($k, $inc);
    }

    static function isEmail($string)
    {
        //$exp_match = '/([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,4})(\]?)/';
        $exp_match = '/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,3})$/';
        $isRightFormat = preg_match($exp_match, $string) ? true : false;
        return $isRightFormat;
    }

    static function checkVcode($vcode)
    {
        if ($vcode && isset($_SESSION['validate_code']) && $_SESSION['validate_code'] == $vcode) {
            $_SESSION['validate_code'] = null;
            unset($_SESSION['validate_code']);
            return true;
        }
        return false;
    }

    static function filterIPAddr($ip = null)
    {
        if (!$ip) {
            $ip = self::getRemoteAddr();
        }
        $notallow = config('banip', 'filteripandwords.php');
        foreach ($notallow as $eachcheck) {
            if ($eachcheck == substr($ip, 0, strlen($eachcheck)))
                return false;
        }
        return true;
    }

    static function filterWords($str, $type = 'replace')
    {
        if ($type == 'replace') {
            //return $str;
            $notallow = config('wordsB', 'filteripandwords.php');
            //视图级过滤输出
            $returnstr = preg_replace($notallow, '*XX*', $str);
            return $returnstr;
        } else {
            $notallow = config('wordsB', 'filteripandwords.php');
            $notallowA = config('wordsA', 'filteripandwords.php');
            $newallpw = array_merge($notallow, $notallowA);
            foreach ($newallpw as $eachcheck) {
                if (preg_match($eachcheck, strtolower($str))) {
                    return true;
                }
            }
            return false;
        }
    }

    //$file = array('name'=>'','type'=>'','size'=>'','tmp_name'=>'')
    static public $filetype = array('img' => array('image/jpeg', 'image/png', 'image/gif', 'image/x-png', 'image/pjpeg')); //,'image/webp'

    static function uploadfile($file, $filepath = null, $fileext = 'img', $filesize = array(0, 10000000))
    {
        if (!$file)
            return array('success' => 0, 'msg' => 'file error');
        if( isset(self::$filetype[$fileext]) ){
            if (!in_array($file['type'], self::$filetype[$fileext]))
                return array('success' => -1, 'msg' => 'file type error');
        }
        if ($file['size'] < $filesize[0] || $file['size'] > $filesize[1])
            return array('success' => -2, 'msg' => 'file size error');

        if (is_null($filepath))
            $filepath = SITE_CONTENTDIR . '/';
        if (!$filepath || substr($filepath, -1) == '/') {
            $tmp = explode('.', $file['name']);
            $filepath = $filepath . self::getIdMicroTime() . '.' . $tmp[count($tmp) - 1];
        }
        self::movefile($file['tmp_name'], $filepath, 'upload');
        return array('success' => 1, 'file' => $filepath,'size'=>$file['size'],'originname'=>$file['name'],'type'=>$file['type']);
    }

    static function movefile($file, $tofile, $tp = 'upload')
    {
        if ($tp == 'del') {
            if (file_exists($file)) {
                unlink($file);
                return true;
            }
            return false;
        }

        self::TmpMkDir($tofile);

        if (file_exists($tofile))
            @unlink($tofile);
        //chmod($tofile, 0766);
        if ($tp == 'upload') {
            $ec = strtolower(substr($tofile, -4, 4));
            if (in_array($ec, array('.php', 'php3', 'php4', 'php5')))
                return false;
            move_uploaded_file($file, $tofile);
        } elseif ($tp == 'rename' || $tp == 'move') {
            rename($file, $tofile);
        } elseif ($tp == 'save') {
            file_put_contents($tofile,$file);
        } elseif ($tp == 'copy') {
            @copy($file, $tofile);
        } else {
            return false;
        }
        chmod($tofile, 0766);
        return $tofile;
    }

    //循环本地建目录
    static function TmpMkDir($tofile)
    {
        $folder = explode('/', $tofile);
        unset($folder[count($folder) - 1]);

        if ($folder && !is_dir(implode('/', $folder))) {
            $dir = '';
            foreach ($folder as $fsp) {
                $dir .= $fsp . '/';
                if (!is_dir($dir)) {
                    mkdir($dir, 0777);
                    chmod($dir, 0777);
                }
            }
        }
    }

    static function fwrite_stream($fp, $string)
    {
        $strlen = strlen($string);
        for ($written = 0; $written < $strlen; $written += $fwrite) {
            $fwrite = fwrite($fp, substr($string, $written));
            if ($fwrite === false or $fwrite === 0) {
                fclose($fp);
                return $written;
            }
        }
        return $written;
    }

    /**
     * 是否是 post 请求
     * @return bool
     */
    static function isPost()
    {
        return isset($_SERVER['REQUEST_METHOD']) && ( strtolower($_SERVER['REQUEST_METHOD']) == 'post' );
    }

    /**
     * only work for jquery post & get
     * @return bool
     */
    static function isAjax()
    {
        if( isset($_GET['callback']) && $_GET['callback'] )
            return true;
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == strtolower('XMLHttpRequest'));
    }

    /**
     * @param $filename
     * @return string
     */
    static function getUniqueFileName($filename)
    {
        $tmp = pathinfo($filename);
        //$objname = md5(uniqid(mt_rand(), true). $filename).'.' . $tmp['extension'];
        $objname = md5(time() . $filename) . '.' . $tmp['extension'];
        return $objname;
    }

    public static function getReferer($encode = true)
    {
        $referer = $_SERVER['HTTP_REFERER']??'';
        return $encode?urlencode($referer):$referer;
    }

    public static function getRequestUri()
    {
        return ($_SERVER['REQUEST_SCHEME']??'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }


    static function orderByStr($fields, $default, $allowFields)
    {
        $orderBy = [];
        if (empty($fields)) {
            $fields = $default;
        }

        foreach ($fields as $field => $direction) {
            if (!in_array($field, $allowFields)) {
                continue;
            }

            $orderBy[] = "{$field} {$direction}";
        }

        $str = implode(',', $orderBy);

        return $str;
    }
    
    //更新url参数
    public static function updateUrlRequestParameters($nameNode,$updateValue=null,$url=null)
    {
        if( empty($url) )
            $url = $_SERVER["REQUEST_URI"];
        if( is_array($nameNode) )
        {
            foreach($nameNode as $n=>$v)
            {
                $url = self::updateUrlRequestParameters($n,$v,$url);
            }
            return $url;
        }

        if( ($k=strpos($url,'/'.$nameNode.'/'))!==false )
        {
            if( $updateValue===null )
            {
                $_prestrlen = $k;
                $_prestr = substr($url,0,$_prestrlen);
                $_endprestr = substr($url,$_prestrlen+strlen($nameNode)+2);
                if (($d = strpos($_endprestr, '/')) !== false) {
                    $url = $_prestr.substr($_endprestr,$d);
                }
                else
                {
                    $url = $_prestr;
                }
            }
            else
            {
                $_prestrlen = $k + strlen($nameNode) + 2;
                $_prestr = substr($url, 0, $_prestrlen);
                $_endprestr = substr($url, $_prestrlen);
                if (($d = strpos($_endprestr, '/')) !== false) {
                    $url = $_prestr . $updateValue . substr($_endprestr, $d);
                } else {
                    $url = $_prestr . $updateValue;
                }
            }
        }
        elseif( ($k=strpos($url,'?'.$nameNode.'='))!==false || ($k=strpos($url,'&'.$nameNode.'='))!==false )
        {
            if( $updateValue===null )
            {
                $_prestrlen = $k;
                $_prestr = substr($url,0,$_prestrlen);
                $_endprestr = substr($url,$_prestrlen+strlen($nameNode)+2);
                if( ($d=strpos($_endprestr,'&'))!==false )
                {
                    $url = $_prestr.substr($_endprestr,$d);
                }
                else
                {
                    $url = $_prestr;
                }
            }
            else
            {
                $_prestrlen = $k+strlen($nameNode)+2;
                $_prestr = substr($url,0,$_prestrlen);
                $_endprestr = substr($url,$_prestrlen);
                if( ($d=strpos($_endprestr,'&'))!==false )
                {
                    $url = $_prestr.$updateValue.substr($_endprestr,$d);
                }
                else
                {
                    $url = $_prestr.$updateValue;
                }
            }
        }
        else
        {
            if( $updateValue!==null )
                $url = $url.(strpos($url,'?')===false?'?':'&').$nameNode.'='.$updateValue;
        }
        return $url;
    }

    static function debugTrace($limit=null,$offset=0)
    {
        $trace = debug_backtrace();
        if( $limit )
            $trace = array_splice($trace,$offset,$limit);
        else
            $trace = array_splice($trace,$offset,count($trace)-$offset);
        foreach ($trace as $k=>$t)
        {
//            if( isset($trace[$k]['args']) )
//                unset($trace[$k]['args']);
            if( isset($trace[$k]['object']) )
                unset($trace[$k]['object']);
        }
        return $trace;
    }

    static function check_remote_file_exists($url)
    {
        $curl = curl_init($url);
        // 不取回数据
        curl_setopt($curl, CURLOPT_NOBODY, true);
        // 发送请求
        $result = curl_exec($curl);
        $found = false;
        // 如果请求没有发送失败
        if ($result !== false) {
        // 再检查http响应码是否为200
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($statusCode == 200) {
                $found = true;
            }
        }
        curl_close($curl);
        return $found;
    }

    static public function cleanhtml($str,$tags='<img><br>')
    {
        //过滤时默认保留html中的<a><img>标签
        $search = array(
            '@<script[^>]*?>.*?</script>@si',  // Strip out javascript
            /* '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags*/
            '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
            '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments including CDATA
        );
        $str = preg_replace($search, '', $str);
        $str = strip_tags($str,$tags);
        return $str;
    }

    static public function getHttpOrigin()
    {
        if( isset($_SERVER['HTTP_ORIGIN']) )
            return $_SERVER['HTTP_ORIGIN'];
        elseif( isset($_SERVER['HTTP_HOST']) )
        {
            if( isset($_SERVER['REQUEST_SCHEME']) )
                return $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];
            elseif( isset($_SERVER['SERVER_PORT']) )
                return ($_SERVER['SERVER_PORT']==80?'http':'https').'://'.$_SERVER['HTTP_HOST'];
        }
        return '';
    }


    public static function getRealImgPath($path, $type = null)
    {
        if( !$path || !is_string($path) )
        {
            return FRONT_DOMAIN.DEFAULT_SITE_LOGO;
        }
        $tmp = pathinfo($path);
        if (!in_array(strtolower($tmp['extension']), self::$resizeImgUse)) {
            return '';
        }
        if( substr($path,0,4)=='http' )
            return $path;
        else
            return SITE_CONTENTDOMAIN.$path;

        //return $type ? $tmp['dirname'] . '/' . $tmp['filename'] . '_' . $type . '.' . $tmp['extension'] : $tmp['dirname'] . '/' . $tmp['filename'] . '.' . $tmp['extension'];

    }

    /**
     * @param $ptah
     * @param $size_type
            logo        100*100     订单列表等小的主图
            plist    200*200     产品列表页面
     *      mobilemain400  400*400     小程序详情页
     *      mobilemain800   800*800     大屏小程序详情
     *      pcmain          1000*1000   PC详情主图
     *      origin                      原图
     *
     * @param array $resize_method
     *      null            无图状态，设了null 则返回 false 值，否则使用默认图
     *      forcepull       强制拉升(小图变大图),没有的话小图亟需小图;压缩的话无妨
     *      cutmiddle       以小边取中间图,没有的话以大边计算,其余留空
     *          transparent     背景透明//有背景
     *          backgroundWhite 白底
     *          advancing       图片周边主要颜色
     *          没有 transparent | backgroundWhite | advancing 的话，无背景填充部分
     * @param string $getType url|cacheurl|bin ,用于后续处理,cacheurl临时url,在img.upjiaju.com处理
     */
    public static $resizeImgUse = ['jpg', 'png', 'gif','jpeg'];
    public static $resizeImgTypes = ['logo','plist','p400','mx400','mx800','pcmain'];
    public static function getUploadImgUrl($path,$size_type='origin')
    {
        //详细查看 img_upimgs_Action !
        if( empty($path) )
        {
            return FRONT_DOMAIN.DEFAULT_SITE_LOGO;
        }

        if( substr($path,0,4)=='http' )
            return $path;

        //输出原图路径:::
        if( !in_array($size_type,self::$resizeImgTypes) )
        {
            return SITE_CONTENTDOMAIN.$path;
        }
        $tmp = pathinfo($path);
        if (!in_array(strtolower($tmp['extension']), self::$resizeImgUse)) {
            return SITE_CONTENTDOMAIN.$path;
        }

//        $rpath = IMG_DOMAIN.'/upimgs'.$size_type.'/'.trim($path,'/');
        $info = pathinfo($path);
        $rpath = IMG_DOMAIN.'/upimgs'.$size_type.'/'.trim($info['dirname'].'/'.$info['extension'].'_'.$info['filename'].'.jpg','/');
        return $rpath;
    }

    public static function readImg($file)
    {
        $type = getimagesize($file);
        switch ($type[2])
        {
            case 1:
                if( !function_exists('imagegif') )
                    throw new J7Exception('Filetype imagegif not supported');
                $image = imagecreatefromgif($file);
                break;
            case 2:
                if( !function_exists('imagejpeg') )
                    throw new J7Exception('Filetype imagejpeg not supported');
                $image = imagecreatefromjpeg($file);
                break;
            case 3:
                if( !function_exists('imagepng') )
                    throw new J7Exception('Filetype imagepng not supported');
                $image = imagecreatefrompng($file);
                break;
            default:
                throw new J7Exception( ($type['mime']??'').'Filetype not supported');
                break;
        }
        return $image??false;
    }

    static function create_thumbnail($file, $max_side = 100, $thumbpath = null)
    {
        // 1 = GIF, 2 = JPEG, 3 = PNG
        if (file_exists($file)) {
            $type = getimagesize($file);
            $image = self::readImg($file);

            {
                if ($type[2] == 1) {
                    $image = imagecreatefromgif($file);
                } elseif ($type[2] == 2) {
                    $image = imagecreatefromjpeg($file);
                } elseif ($type[2] == 3) {
                    $image = imagecreatefrompng($file);
                }
                if (function_exists('imageantialias'))
                    imageantialias($image, TRUE);
                $image_attr = getimagesize($file);

                if ($image_attr[0] > $image_attr[1]) {
                    $image_width = $image_attr[0];
                    $image_height = $image_attr[1];
                    $image_new_width = $max_side;

                    $image_ratio = $image_width / $image_new_width;
                    $image_new_height = $image_height / $image_ratio;
                    //width is > height
                } else {
                    $image_width = $image_attr[0];
                    $image_height = $image_attr[1];
                    $image_new_height = $max_side;

                    $image_ratio = $image_height / $image_new_height;
                    $image_new_width = $image_width / $image_ratio;
                    //height > width
                }
                $thumbnail = imagecreatetruecolor($image_new_width, $image_new_height);
                imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $image_new_width, $image_new_height, $image_attr[0], $image_attr[1]);
                // move the thumbnail to it's final destination
                if (!$thumbpath) {
                    $path = explode('/', $file);
                    $thumbpath = substr($file, 0, strrpos($file, '/')) . '/thumb_' . $path[count($path) - 1];
                }

                if ($type[2] == 1) {
                    if (!imagegif($thumbnail, $thumbpath)) {
                        throw new J7Exception("Thumbnail path invalid");
                    }
                } elseif ($type[2] == 2) {
                    if (!imagejpeg($thumbnail, $thumbpath)) {
                        throw new J7Exception("Thumbnail path invalid");
                    }
                } elseif ($type[2] == 3) {
                    if (!imagepng($thumbnail, $thumbpath)) {
                        throw new J7Exception("Thumbnail path invalid");
                    }
                }
            }
        }
        return $thumbpath;
    }

    static function relativeHttpsUrl($url)
    {
        return str_replace(['https://','http://'],'//',$url);
    }

    static function filterEmoji($str)
    {
        $str = preg_replace_callback(
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);
        return $str;
    }
}

