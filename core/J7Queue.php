<?php
//queue实现是每个serviceName一个代理类,然后再传递给queue
class J7Queue
{
    /**
     * @param $serviceName //用于生成queue，watch消费时候，该值几乎不用...
     * @param $tubeName
     * @param array $params
     * @return J7Queue
     * @throws coreException
     */
    public static function instance($serviceName,$tubeName,$params=[])
    {
        if( empty($serviceName) || empty($tubeName) )
            throw new coreException('must set serviceName and tubeName !');
        return FactoryObject::Instance('J7Queue',func_get_args(),function ($ins){
            $ins->init();
        });
    }

    protected $serviceName;
    protected $tubeName = [];
    public function __construct()
    {
        $argv = func_get_args();
        $this->serviceName = $argv[0];
        $this->tubeName = $argv[1];
        $this->initParam = isset($argv[2])?$argv[2]:[];
    }


    public function init()
    {
        $configall = config('queue');
        $_configId = 0;
        if( isset($configall['queue_alias']) && isset($configall['queue_alias'][$this->tubeName]) )
            $_configId = $configall['queue_alias'][$this->tubeName];

        $_config = $configall[$_configId];
        return $this->conn($_config);
    }

    protected $q; //queue对象

    public function getQueue()
    {
        return $this->q;
    }
    /**************************************
     * Queue 以下队列方法可子类覆盖
     ***************************************/

    protected function conn($config=[])
    {
        if( isset($config['type']) )
        {
            $type = $config['type'];
            unset($config['type']);
        }
        else
        {
            $type = 'BeanStalk';
        }

        if( $type == 'Socket_Beanstalk' )
        {
            $config = array_intersect_key($config,array('host'=>0,'port'=>0,'persistent'=>0,'timeout'=>0));
            require_once __DIR__.'/queue/Socket_Beanstalk.php';

            $this->q = FactoryObject::Instance('Socket_Beanstalk',[$config],function ($ins){
                $ins->connect();
            });
        }
        else
        {
            require_once __DIR__.'/queue/BeanStalk.php';
            $this->q = BeanStalk::open($config);
        }
        return true;
    }

    protected $usingTube;
    public function put($qname,$classname,$method,$params=[],$delay=null,$pri=null,$ttr=null)
    {
        if( $this->q )
        {
            $delay = is_null($delay)?(isset($this->initParam[0])?$this->initParam[0]:0):0;
            $pri = is_null($pri)?(isset($this->initParam[1])?$this->initParam[1]:0):0;
            $ttr = is_null($ttr)?(isset($this->initParam[2])?$this->initParam[2]:120):120;

            $this->q->useTube($qname);
            $this->usingTube=$qname;

            $data = array('class'=>$classname,'method'=>$method,'params'=>$params);
            $this->q->put($pri, $delay, $ttr, serialize($data) );
        }
    }

    // 优先级 delay 处理时间 data
    // $this->q->put(0, 0, 120, 'say hello world'.time() );
    public function __call($method,$params)
    {
        return $this->put($this->tubeName,$this->serviceName,$method,$params);
    }
}