<?php
require_once __DIR__ . '/J7Cache.php';
require_once __DIR__ . "/J7Page.php";

abstract class baseServiceClass extends baseControllerModel
{
    /**
     * @var CacheInterface
     */
    protected $__cache;

    public function __j7construct()
    {
        $this->__cache = J7Cache::instance();
    }


    /**
     * 转成队列调用 eg: xxxService->__queue('servicedao')->testcate(array('cid'=>2,'id'=>10));
     * 延时... $params = [$delay=0,$pri=0,$ttr=120]
     * @return $this
     */
    protected function __queue($tubename='servicedao',$params=[])
    {
        if( config('_j7_system_queue_on') )
        {
            return J7Queue::instance(get_called_class(),$tubename,$params);
        }
        else
            return $this;
    }


    /**
     * 获取分页数据
     * @return J7Page
     */
    protected function getItems($dao,$ps=null,$condition = null,$order=null,$tp='get',$counttp='getCount')
    {
        if( is_null($ps) )
            $ps = new J7Page();
        $ps->setTotalCount($dao->$counttp($condition));
        $ps->setItems($dao->$tp($condition, $order, $ps->getCountOnEachPage(), $ps->getStartIndex(), "*"));
        return $ps;
    }

    /**
     * 获取分页数据
     * @return J7Page
     */
    function pager($daoName, $ps, $cond = null, $order = null)
    {
        if( is_string($daoName) )
        {
            if( substr($daoName,-5)!='DbDAO' )
            {
                $daoName = $daoName . 'DbDAO';
            }
            return $this->getItems($this->$daoName, $ps, $cond, $order);
        }
        elseif( $daoName instanceof DbCrudDAO )
        {
            return $this->getItems($daoName, $ps, $cond, $order);
        }
        elseif( ($daoName instanceof AOPProxyControllerModel) && ($daoName->__getProxyObject() instanceof DbCrudDAO) )
        {
            return $this->getItems($daoName, $ps, $cond, $order);
        }
    }

    /**
     * 速度操作dao
     */
    function crudTp($tp = 'getByPk', $datas = [], $daoName = 'user') {
        if( substr($daoName,-5)!='DbDAO' )
        {
            $daoName = $daoName . 'DbDAO';
        }
        return call_user_func_array( array($this->$daoName, $tp), $datas );
    }
}