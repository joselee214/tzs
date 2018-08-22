<?php
require_once(__DIR__ . "/../../core/J7Config.php");
require_once(__DIR__ . "/../../core/J7Debuger.php");
require_once(__DIR__ . "/../../core/Util.php");

class j7ssp
{
    public $__socket;
    public $__connsuccess = true;
    public $__isadminlogined = false;
    public $__dsn = array();

    public function __construct($dsnconfigid = null,$timeout = null)
    {
        $dsn = config('j7sspconfig','j7ssp.php');
        if( $dsn )
        {
            if( is_null($dsnconfigid) )
            {
                if(isset($dsn['default']))
                    $dsnconfigid = 'default';
                else
                {
                    $dsnconfigid = array_rand($dsn);
                }
            }
            $this->__dsn = $dsn[$dsnconfigid];
            if( $timeout )
                $this->__dsn['timeout'] = $timeout;
            else
                if (!isset($this->__dsn['timeout']))
                    $this->__dsn['timeout'] = 0.1;
        }
        else
        {
            $this->__connsuccess = false;
        }
    }

    private static $_instance = array();

    /**
     * @static
     * @param int $dsnconfigid
     * @param string $instancename
     * @return j7ssp
     */
    public static function instance($dsnconfigid = null, $instancename = 'j7ssp' ,$timeout = null)
    {
    	if( !$instancename ) { $instancename = 'j7ssp'; }
        if( is_null($dsnconfigid) )
            $instanceid = $instancename.'_null';
        else
            $instanceid = $instancename.'_'.$dsnconfigid;
        if (!isset(self::$_instance[$instanceid])) {
            $tmp = new j7ssp($dsnconfigid,$timeout);
            self::$_instance[$instanceid] = $tmp;
            $tmp = null;
        }
        return self::$_instance[$instanceid];
    }

    private function getconn()
    {
        if( $this->__connsuccess === false )
            return null;

        $this->__socket = fsockopen($this->__dsn['ip'], $this->__dsn['port'], $errno, $errstr, $this->__dsn['timeout']);
            // $this->__socket = stream_socket_client( 'tcp://'.$this->__dsn['ip'].':'.$this->__dsn['port'], $errno, $errstr, $this->__dsn['timeout']);
        if (!$this->__socket) {
            //需要记录入错误日志
            debug(ob_get_clean(),'j7ssp::getconn Error');
            $this->__connsuccess = false;
        }
        else
        {
            stream_set_blocking($this->__socket, 0); //0非阻塞模式
            stream_set_timeout($this->__socket, $this->__dsn['timeout']);
        }
    }

    public function send($data, $withget, $needadminlogin=false, $admininfo = array(),$callid=0)
    {
        try
        {
            if (!$this->__socket)
                $this->getconn();
            if ($this->__connsuccess)
            {
                if( $withget )
                {
                    stream_set_blocking($this->__socket, 1);
                    stream_set_timeout($this->__socket, isset($this->__dsn['blockingtimeout'])?$this->__dsn['blockingtimeout']:1 );
                }
                if( $needadminlogin && !$this->__isadminlogined )
                {
                    $j7sspadminlogin = config('j7sspadminlogin');
                    $p = array_merge($j7sspadminlogin, $admininfo);
                    $this->sent($p,$withget);
                    $this->__isadminlogined = true;
                }
                else
                {
                    $data['autoadmin'] = 1;
                }
                $ret = $this->sent($data, $withget,$callid);
//                if( $withget )
//                {
//                    stream_set_blocking($this->__socket, 1);
//                }
                return $ret;
            }
        }
        catch (Exception $e)
        {
            //do nothing
            return false;
        }
    }


    public function debug($data, $debugkey = 'Debug_Default_Key')
    {
        $msg = array('j7act' => 'debug', 'j7debug' => $debugkey, 'j7debuginfo' => $data);
        return $this->brocadcast($msg);
    }
    public function brocadcast($data, $roomid = -999)
    {
        $p = array('j7act' => 'brocadcast', 'j7SPD' => 'broadcastData', 'roomid' => $roomid);
        $p['msg'] = $data;
        return $this->send($p,false);
    }

    //系统监控传输值设置
    public function logMonitor($data, $roomid = -999) // array('$logkey'=>$lognum)
    {
        $p = array('j7act' => 'logmonitor', 'j7SPD' => 'logmonitor', 'roomid' => $roomid,'data'=>$data);
        return $this->send($p,false);
    }

    public function sent($data = array(), $withget = false , $callid = 0)
    {
        if (!$this->__socket)
            $this->getconn();

        if ($this->__connsuccess)
        {
            $d = json_encode($data);
            $s = gzcompress($d);
            $strlen = strlen($s) + 4;
            $procode = chr(0).chr(0).chr(0).chr(0);
            $lencode = chr(0).chr(0).chr(0).chr(0);
            $reqid = chr(0).chr(0).chr(0).chr(0);

            if( is_int($callid) && $callid>0 )
            {
                if( $callid>=16777216 )
                {
                    $reqid[0] = chr( $callid / 16777216 );
                    $callid = $callid % 16777216;
                }
                else
                {
                    $reqid[0] = chr(0);
                }
                if( $callid>=65536 )
                {
                    $reqid[1] = chr( $callid / 65536 );
                    $callid = $callid % 65536;
                }
                else
                {
                    $reqid[1] = chr(0);
                }
                if( $callid>=256 )
                {
                    $reqid[2] = chr( $callid / 256 );
                    $callid = $callid % 256;
                }
                else
                {
                    $reqid[2] = chr(0);
                }
                $reqid[3] = chr($callid);
            }

            if( $strlen>=16777216 )
            {
                $lencode[0] = chr( $strlen / 16777216 );
                $strlen = $strlen % 16777216;
            }
            else
            {
                $lencode[0] = chr(0);
            }
            if( $strlen>=65536 )
            {
                $lencode[1] = chr( $strlen / 65536 );
                $strlen = $strlen % 65536;
            }
            else
            {
                $lencode[1] = chr(0);
            }
            if( $strlen>=256 )
            {
                $lencode[2] = chr( $strlen / 256 );
                $strlen = $strlen % 256;
            }
            else
            {
                $lencode[2] = chr(0);
            }
            $lencode[3] = chr($strlen);
            $d = $procode . $lencode . $reqid . $s;
            Util::fwrite_stream($this->__socket, $d);
            if ($withget)
            {
                return $this->get();
            }
            return true;
        }
    }

    public function get()
    {
        if (!$this->__socket)
            $this->getconn();
        $read = '';
        $recv_data = '';
        $readmore = 13;
        $loopi = 0;
        $recv_size = 0;
        while ( $readmore && !feof($this->__socket )) {
            if ($loopi > 30)
            {
                break;
            }
            $read .= fgets($this->__socket, $readmore);
            ++$loopi;
            if (strlen($read) < 12)
            {
                $readmore = $readmore-strlen($read);
                continue;
            }
            if( $recv_size==0 && strlen($read)>=12 )
            {
                $recv_size = ord($read[4]) * 16777216 + ord($read[5]) * 65536 + ord($read[6]) * 256 + ord($read[7]) + 8; //数据包长度
                $readmore =  $recv_size - 12 +1;
            }

            if ($readmore >0 && $recv_size!=strlen($read) ) {
                $readmore = $recv_size - strlen($read) +1;
                continue;
            }
            else
            {
                $recv_data = substr($read, 12);
                $read = '';
                break;
            }
        }
        if ($recv_data) {
            try
            {
                return json_decode(gzuncompress($recv_data),true);
            }
            catch (Exception $e)
            {
                return $recv_data;
            }
        }
        else
        {
            return '';
        }
    }

//    public function __destruct()
//    {
//        if ($this->__socket)
//            fclose($this->__socket);
//    }
}