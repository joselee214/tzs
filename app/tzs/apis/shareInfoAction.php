<?php
class tzs_apis_shareInfo_Action extends tzs_apis_common
{

    public $shareTicket;
    public $pid;

    public function __j7construct()
    {
        parent::__j7construct();
        if( is_null($this->_userme) || !isset($this->_userme['uid']) || !$this->_userme['uid'] )
            throw new BizException(['error'=>'not login'],'loginwx');

    }

    public function execute()
    {
        $this->_ret = ['success'=>0];
        $endata = $this->decryptedData();
        if( $endata && isset($endata['openGId']) && $this->pid && ($post = $this->tzsService->getPostById($this->pid)) )
        {
            $nowgids = json_decode($post->_gids(),true)?:[];
            if( !in_array($endata['openGId'],$nowgids))
            {
                $nowgids[] = $endata['openGId'];
            }
            if( $post->_gids() != json_encode($nowgids)  )
            {
                $post->_gids( json_encode($nowgids) );
                $post->save();
            }

            //分享记录
            $this->tzsService->addShareGroupLog(['uid'=>$this->_userme['uid'],'pid'=>$this->pid,'gid'=>$endata['openGId']]);

            $this->_ret = ['success'=>1,'data'=>$en_data??[]];
        }
    }

}