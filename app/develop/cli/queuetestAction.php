<?php
require_once __DIR__ . '/../common/develop_cli_common.php';

class develop_cli_queuetest_Action extends develop_cli_common
{

    protected $q;

    // php web/develop.php /cli/queuetest/servicedao
    public function servicedao()
    {
        $queueIns = J7Queue::instance('watch','servicedao');
        $this->q = $queueIns->getQueue();
        $this->q->watch('servicedao');

        $m_start = 0;
        foreach ( Util::xrange(1, 1000000) as $i )
        {
            try
            {
                $job = $this->q->reserve(1);
                if( $job && isset($job['id']) && isset($job['body']) && $job['id'] )
                {
                    $data = unserialize($job['body']);
                    $class = FactoryObject::Get($data['class']);

                    echo '============> call:' . PHP_EOL . str_replace(PHP_EOL, '', var_export($data, true));

                    $r = call_user_func_array(array($class, $data['method']), $data['params']);

                    echo PHP_EOL . '============> result:' . PHP_EOL . str_replace(var_export($r, true));

                    $this->q->delete($job['id']);
                }
            }
            catch( Exception $e)
            {
                var_dump( $e );
            }

            $m_use = memory_get_usage();

            echo chr(10).chr(13);
            echo '@'.$i.'    memuse:'.$m_use.' : ('.($m_use-$m_start).')    ';
            echo date('H:i:s').PHP_EOL.PHP_EOL;
            $m_start = $m_use;
        }
    }

    public function consumer()
    {
        $queueIns = J7Queue::instance('consumer','test');
        $this->q = $queueIns->getQueue();
        $this->q->watch('test');
        $i=0;
        while($i<5)
        {
            $job = $this->q->reserve(2);
            if( $job && isset($job['id']) && $job['id'] )
            {
                $this->q->delete($job['id']);
                var_dump($job);
            }

            echo chr(10).chr(13);
            echo '@'.$i.'    memuse:'.memory_get_usage().'    ';
            echo date('H:i:s').chr(10).chr(13).chr(10).chr(13).chr(10).chr(13);
            $i++;

        }
    }

    public function producer()
    {
        echo 'run in cli/queuetest/producer';
        $queueIns = J7Queue::instance('producer','test');
        $queueIns->put('test', 'producer', 'method', 'say hello world'.time() );
        echo 'ok'.PHP_EOL;
    }
}