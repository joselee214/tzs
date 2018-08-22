<?php
class tzs_apis_postdata_Action extends tzs_apis_common
{
    public function __j7construct()
    {
        parent::__j7construct();
        if( is_null($this->_userme) || !isset($this->_userme['uid']) || !$this->_userme['uid'] )
            throw new BizException(['error'=>'not login'],'loginwx');
    }

    public $pid;

    public function execute()
    {
        $this->_ret = ['status'=>0];
        $post = $this->tzsService->getPostById($this->pid);
        if( $post && ($post->_uid()==$this->_userme['uid']) )
        {
            $datas = [];
            $datas['signnums'] = $this->tzsService->getSignUpLogNums($this->pid);

            $groups = [];
            if( $post->_gids() )
            {
                $_ag = json_decode($post->_gids(),true);
                $groups = array_unique($_ag);
            }

            $datas['groups'] = $groups;
            $datas['sharelogs'] = $this->tzsService->getShareLog($this->pid);

            $groupUserNums = [];
            if( $groups )
            {
                foreach ($groups as $gid)
                {
                    $allRegUserNums = $this->tzsService->getUserGroupNums($gid);
                    $thisPostGroupUserNums = $this->tzsService->getUserGroupNumsForPid($gid,$this->pid);
                    $groupUserNums[] = ['gid'=>$gid,'allusernums'=>$allRegUserNums,'postgroupnums'=>$thisPostGroupUserNums];
                }
            }

            $datas['groupUserNums'] = $groupUserNums;

            $this->_ret = ['status'=>1,'post'=>$post,'datas'=>$datas,'dataintro'=>'查看数据可以至WEB版, 网址 '.SITE_DOMAIN.''];
//            $this->_ret = ['status'=>1,'post'=>$post,'datas'=>$datas,'dataintro'=>''];
        }
    }

}