<?php
class tzs_tzs_docs_Action extends tzs_tzs_common
{
    public $limit = 5;

    public function execute()
    {
//        var_dump($this->_userme);
        $this->data();
        return $this->_setResultType('default');
    }

    public function data()
    {
        $cond = ['uid'=>$this->_userme['uid']];
        $this->ps = $this->tzsService->getViewLogs($this->ps,$cond,'p',['updated_at'=>'desc']);

        if( $items = $this->ps->getItems() )
        {
            foreach ($items as $k=>$item)
            {
                $items[$k]['logo'] = Util::relativeHttpsUrl($item['logo']);
                $items[$k]['avatar'] = Util::relativeHttpsUrl($item['avatar']);
            }
            $this->ps->setItems($items);
        }
        return $this->_setResultType('json',$this->ps);
    }
}