<?php
class tzs_tzs_d_Action extends tzs_tzs_common
{
    public $pid;
    /**
     * @var J7Page
     */
    protected $psvl;

//    public $limit = 2;

    public function execute()
    {
        $post = $this->tzsService->getPostById($this->pid);

        if( $post )
        {
            //检查阅读记录
            $nowgid = '';

            $vl = $this->tzsService->exeViewLog($post->_id(),$this->_userme['uid']??0,$post->_uid(),$nowgid);
            $vl['degid'] = $nowgid;

            if( $post->_can_sign_up() )
            {
                $sign = $this->tzsService->getSignUpLogDetail($this->pid,$this->_userme['uid']);
                $post['user_is_sign'] = $sign[0];
                $post['sign_options'] = $sign[1];
            }

            $post->_content(nl2br($post->_content()));

            $post['avatar'] = Util::relativeHttpsUrl($post['avatar']);
            if( isset($post['imgslist']) && is_array($post['imgslist']) && count($post['imgslist'])>0 )
            {
                $imgslist = $post['imgslist'];
                foreach ($imgslist as $k=>$em)
                {
                    $imgslist[$k]['path'] = Util::relativeHttpsUrl($em['path']);
                }
                $post['imgslist'] = $imgslist;
            }

            $this->replys(0); //reset _ret
            $this->_ret['replys'] = $this->ps;

            $this->_ret['vl'] = $vl;
            $this->_ret['post'] = $post;

            $this->viewlogs(0);
            $this->_ret['viewlogs'] = $this->psvl;
        }

        return $this->_setResultType('default');
    }

    public function replys($call=1)
    {
        $post = $this->tzsService->getPostById($this->pid);
        if( $post )
        {
            $isPostMaster = $post->_uid()==$this->_userme['uid'];
            $cond = ['pid'=>$this->pid,'deleted'=>0];
            $this->ps = $this->tzsService->getReplys($this->ps,$cond,$isPostMaster,$this->_userme['uid']);

            if( $items = $this->ps->getItems() )
            {
                foreach ($items as $k=>$item)
                {
                    $items[$k]['avatar'] =  Util::relativeHttpsUrl($item['avatar']);
                    $items[$k]['content'] =  nl2br($item['content']);
                }
                $this->ps->setItems($items);
            }
        }
        if( $call==1 )
            return $this->_setResultType('json',$this->ps);
    }

    //获取阅读记录
    public function viewlogs($call=1)
    {
        $cond = ['pid'=>$this->pid,'uid>0'];
        $this->psvl = new J7Page($this->limit, $this->page);
        $this->psvl = $this->tzsService->getViewLogs($this->psvl,$cond,'u');

        if( $items = $this->psvl->getItems() )
        {
            foreach ($items as $k=>$item)
            {
                $items[$k]['avatar'] =  Util::relativeHttpsUrl($item['avatar']);
            }
            $this->psvl->setItems($items);
        }

        if( $call==1 )
            return $this->_setResultType('json',$this->psvl);
    }
}