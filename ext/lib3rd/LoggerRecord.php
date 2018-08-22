<?php
/**
 * 日志记录类
 *
 * @example 该类只能通过LoggerRecord::Get()方法获得全局唯一实例.不能通过new方式构造新对象
 * @version 0.9.0.1
 * @author kelezyb
 */
class LoggerRecord
{
    /**
     * Logger日志服务器
     * @var string
     */
    private $url = "http://monitor-flowershop-fb.shinezone.com/logger.php";
    
    /**
     * 错误信息
     * @var
     */
    private $http_error_info;
    
    /**
     * 错误代码
     * @var mixed
     */
    private $http_error;

    /**
     * 浏览器标识
     * @var string
     */
    private $user_agent = "Shinezone Logger 0.9.0.1";

    /**
     * 连接超市
     * @var int
     */
    private $connect_timeout = 1;

    /**
     * 执行超时
     * @var int
     */
    private $exec_timeout = 2;

    /**
     * Log缓存
     * @var array
     */
    private $logger_data = array();

    /**
     * 是否POST过数据
     * @var bool
     */
    private $is_post = false;

    /**
     * 计时器
     * @var array
     */
    private $times = array();
	
	private $is_write = false;

    private $cid_rates = array(
        20010 => 100,
        20011 => 100,
        20020 => 100,
        20021 => 100,
    );

	private $START_TIMER = 0;

    /**
     * 日志记录器实例
     * @var LoggerRecord
     */
    private static $instance = null;

    /**
     * 获得唯一LoggerRecord对象实例
     * @static
     * @param string $url
     * @return LoggerRecord
     */
    public static function Get($url = null)
    {
        if(null === LoggerRecord::$instance)
        {
            LoggerRecord::$instance = new LoggerRecord();
        }

        LoggerRecord::$instance->setUrl($url);

        return LoggerRecord::$instance;
    }

    /**
     * 构造函数
     */
    private function __construct()
    {
        register_shutdown_function(array($this, '__destruct'));
		$this->START_TIMER = microtime(true);
		if(isset($_SERVER['USER_AGENT']))
		{
			$this->user_agent = $_SERVER['USER_AGENT'];
		}
    }

	private function vaild_write($cid)
	{
		$rate = isset($this->cid_rates[$cid]) ?
                $this->cid_rates[$cid] : 10;    //默认10%几率
        
        $mt_rand = mt_rand(0, 100);
        
        if($mt_rand <= $rate)
        {
            $this->is_write = true;
        }
	}

    /**
     * 设置Logger服务器 URL
     * @param string $url
     * @return void
     */
    public function setUrl($url)
    {
        if(!empty($url))
        {
            $this->url = $url;
        }
    }

    /**
     * 构造查询函数
     * @return array
     */
    private function build_post_data()
    {
        $logs = urlencode(json_encode($this->logger_data));

        return array('logs' => $logs);
    }

    /**
     * POST log data
     * @return mixed
     */
    private function post()
    {
		$this->is_post = true;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($this->build_post_data()));
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_0 );
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT , $this->connect_timeout);
		curl_setopt($curl, CURLOPT_TIMEOUT , $this->exec_timeout);
		curl_setopt($curl, CURLOPT_USERAGENT , $this->user_agent);
        $tuData = curl_exec($curl);
        $this->http_error = curl_errno($curl);
        if($this->http_error){
            $this->http_error_info = curl_error($curl);
        }
        curl_close($curl);
        $this->logger_data = array();   //clear log datas

        return $tuData;
    }

    private function getIp()
    {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        else if (isset($_SERVER["HTTP_CLIENT_IP"]))
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        else if (isset($_SERVER["REMOTE_ADDR"]))
            $ip = $_SERVER["REMOTE_ADDR"];
        else if (getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("HTTP_CLIENT_IP"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("REMOTE_ADDR"))
            $ip = getenv("REMOTE_ADDR");
        else
            $ip = "Unknown";
        
        return $ip;
    }

    public function startTiming($dtname)
    {
        $this->times[$dtname] = microtime(true);
    }

    /**
     * 记录日志
     * @param int $uid
     * @param int $cid
     * @param string $dtname  标示符
     * @param int $mesg
     * @param int $value
     * @param int $item
     * @return void
     */
    public function Write($uid, $cid, $dtname, $mesg = 0, $value = 0, $item = 0)
    {
		$this->vaild_write($cid);
    	
		if( !$this->is_write ) return ;

        if(isset($this->times[$dtname]))
        {
            $dtime = intval((microtime(true) - $this->times[$dtname]) * 1000);
        }
        else
        {
            $dtime = intval((microtime(true) - $this->START_TIMER) * 1000);
			$this->times[$dtname] = microtime(true);
        }
				

        $datas = array(
            $cid,               /*采集ID*/
			$uid,               /*用户ID*/
            $dtime,             /*执行时间*/
            $mesg,              /*错误信息*/
            $value,             /*采集状态*/
            $item,              /*SQL等操作信息*/
            $this->getIp(),     /*IP地址*/
            time()              /*当前时间*/
        );
        //FB::info($datas, 'LOGGER');
        $this->logger_data[] = $datas;
    }

    /**
     * 析构函数页面结束执行
     */
    public function __destruct()
    {
        if(!$this->is_post) //没有执行POST过程
        {
            if(!empty($this->logger_data))
            {
                $postret = $this->post();
            }
        }
    }
}

// $logger = LoggerRecord::Get();

// $logger->startTiming('S1');
// $logger->startTiming('S2');
// for($i = 0; $i < 100000; $i++)
// {}

// $logger->Write(123, 10086, 'S2', '192.168.20.23:6379', 1, "KEY");

// sleep(1);
// $logger->Write(123, 10088, 'S1', '192.168.20.21:8181', 0, "USER_123");


