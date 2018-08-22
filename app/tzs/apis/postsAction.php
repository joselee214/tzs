<?php
class tzs_apis_posts_Action extends tzs_apis_common
{
    public $title;
    public $content;

    public $allowReply;

    public $can_sign_up=0;
    public $sign_up_options='';

    public $upfiles;
    public $upimgs;

    public $id;

    public $edit=0;

    public $limit=10;

    public $shareTicket;
    public $lunchPid;

    public $__gcode;

    public $pid;
    /**
     * @var J7Page
     */
    protected $psvl;

    public function __j7construct()
    {
        parent::__j7construct();
        if( is_null($this->_userme) || !isset($this->_userme['uid']) || !$this->_userme['uid'] )
            throw new BizException(['error'=>'not login'],'loginwx');

    }

    public function execute()
    {
        $cond = ['uid'=>$this->_userme['uid'],'deleted'=>0];
        $this->ps = $this->tzsService->getPosts($this->ps,$cond);
        $this->_ret = ['status'=>1,'list'=>$this->ps];
    }


    //单项文档详情
    public function getOneDoc()
    {
        $post = $this->tzsService->getPostById($this->pid);
        $this->_ret = ['status'=>1,'post'=>$post];
        if( $post )
        {
            if( $this->edit )
            {
                //编辑模式下...
                $this->deleteUploadCache();
//                if( isset($post['fileslist']) && $post['fileslist'] )
//                    $this->setUploadCache($post['fileslist']);
                $post['uploads'] = $this->tzsService->getTempUpload($this->cachekeyTempUpload);
            }
            else
            {
                //检查阅读记录
                $nowgid = '';
                if( $this->lunchPid == $this->pid )
                {
                    $endata = $this->decryptedData();
                    if( $endata && isset($endata['openGId']) )
                    {
                        $nowgid = $endata['openGId'];
                    }
                }

                $vl = $this->tzsService->exeViewLog($post->_id(),$this->_userme['uid'],$post->_uid(),$nowgid);
                $vl['degid'] = $nowgid;

                if( $post->_can_sign_up() )
                {
                    $sign = $this->tzsService->getSignUpLogDetail($this->pid,$this->_userme['uid']);
                    $post['user_is_sign'] = $sign[0];
                    $post['sign_options'] = $sign[1];
                }
//                $post->_content(nl2br($post->_content()));

                $this->replys(0); //reset _ret
                $this->_ret['replys'] = $this->ps;

                $this->_ret['vl'] = $vl;
                $this->_ret['post'] = $post;


                $wxuser = $this->UserExtPlatformService->getWxUserByOpenid($this->openid,$this->appid);
                $this->_ret['me'] = ['nickName'=>$wxuser->_nickName()];

                $this->viewlogs(0);
                $this->_ret['viewlogs'] = $this->psvl;
            }
        }
    }

    //回复列表
    public function replys($call=1)
    {
        if($call)
            $this->_ret = ['status'=>0,'msg'=>'error'];
        $post = $this->tzsService->getPostById($this->pid);
        if( $post )
        {
            $isPostMaster = $post->_uid()==$this->_userme['uid'];
            $cond = ['pid'=>$this->pid,'deleted'=>0];
            $this->ps = $this->tzsService->getReplys($this->ps,$cond,$isPostMaster,$this->_userme['uid']);

            if($call)
                $this->_ret = ['status'=>1,'replys'=>$this->ps];
        }
    }

    public function topReply()
    {
        $this->_ret = ['status'=>0,'msg'=>'error'];
        $post = $this->tzsService->getPostById($this->pid);
        if( $post->_uid()==$this->_userme['uid'] )
        {
            $r = $this->tzsService->setReplyTop($this->id,$this->pid,$this->title);
            $this->_ret = ['status'=>$r?1:0];
        }
    }

    //添加回复
    public function addReply()
    {
        $this->_ret = ['status'=>0,'msg'=>'empty content'];
        if( trim($this->content) )
        {
            $v = ['pid'=>$this->pid,'content'=>Util::cleanhtml($this->content,''),'uid'=>$this->_userme['uid']];
            $reply = $this->tzsService->addReply($v);
            if( $reply && $reply->_id() )
            {
                $this->replys(0);
                $this->_ret = ['status'=>1,'msg'=>'post ok','reply'=>$reply,'replys'=>$this->ps];
            }
        }
    }

    //删除回复
    public function deleteReply()
    {
        $this->_ret = ['status'=>0,'msg'=>'error'];
        $reply = $this->tzsService->getReplyById($this->id);
        if( $reply  )
        {
            $post = $this->tzsService->getPostById($reply->_pid());
            if( ($reply->_uid()==$this->_userme['uid']) || ($post && $post->_uid()==$this->_userme['uid'])  )
            {
                $this->tzsService->deleteReplyById($this->id,$reply->_pid());
                $this->_ret = ['status'=>1,'id'=>$this->id];
            }
        }
    }

    //获取阅读记录
    public function viewlogs($call=1)
    {
        $cond = ['pid'=>$this->pid,'uid>0'];
        $this->psvl = new J7Page($this->limit, $this->page);
        $this->psvl = $this->tzsService->getViewLogs($this->psvl,$cond,'u');
        if($call)
            $this->_ret = ['status'=>1,'viewlogs'=>$this->psvl];
    }

    //获取我阅读过的文档
    public function getMyReadDocs()
    {
        $cond = ['uid'=>$this->_userme['uid']];
        $this->ps = $this->tzsService->getViewLogs($this->ps,$cond,'p',['updated_at'=>'desc']);
        $this->_ret = ['status'=>1,'docs'=>$this->ps];
    }

    public function delete()
    {
        $this->_ret = ['status'=>0,'msg'=>'error'];
        $post = $this->tzsService->getPostById($this->id);
        if( $post && ($post->_uid()==$this->_userme['uid']) )
        {
            $this->tzsService->deletePostById($this->id);
            $this->_ret = ['status'=>1,'id'=>$this->id];
        }
    }

    public function switchSign()
    {
        $this->_ret = ['status' => 0, 'msg' => 'error'];
        if( $this->pid )
        {
            $this->can_sign_up = intval($this->can_sign_up);
            $post = $this->tzsService->getPostById($this->pid);
            if ($post && ($post->_uid() == $this->_userme['uid'])) {
                if( !empty($post->_sign_up_options()) )
                {
                    $post->_can_sign_up($this->can_sign_up);
                    $post->save();
                    $this->_ret = ['status' => 1,'msg' => ($this->can_sign_up?'启用':'关闭').'报名成功!','can_sign_up'=>$this->can_sign_up];
                }
                else
                {
                    $this->_ret = ['status' => 0, 'errorcode'=>1, 'msg' => '没有设置报名项,无法操作'];
                }
            }
        }
    }
    public function doPost()
    {

        //删除缓存
        $this->deleteUploadCache();

        $this->title = Util::cleanhtml($this->title,'');
        $this->content = Util::cleanhtml($this->content,'');
        $this->sign_up_options = Util::cleanhtml($this->sign_up_options,'');
        $this->sign_up_options = preg_replace("/([ |\t]{0,})/","",$this->sign_up_options);
        $this->sign_up_options = preg_replace("/([\n]{1,})/","\n",$this->sign_up_options);

        if( empty($this->sign_up_options) )
        {
            $this->can_sign_up = 0;
        }

        if( $this->pid )
        {
            $this->_ret = ['status'=>0,'msg'=>'error'];
            $post = $this->tzsService->getPostById($this->pid);
            if( $post && ($post->_uid()==$this->_userme['uid']) )
            {
                $originImgs = array_filter(explode(',',$post->_imgs()));
                $originFiles = array_filter(explode(',',$post->_files()));
                $origins = array_merge($originImgs,$originFiles);
                $nowImgs = array_filter(explode(',',$this->upimgs));
                $nowFiles = array_filter(explode(',',$this->upfiles));
                $nows = array_merge($nowImgs,$nowFiles);

                $toDelete = array_diff($origins,$nows);
                if( $toDelete )
                {
                    foreach ($toDelete as $toDid)
                    {
                        $this->tzsService->deleteUpload($toDid);
                    }
                }
                $toMark = array_diff($nows,$origins);
                $this->tzsService->markPidForUpload($toMark,$post->_id());

                $post->_title($this->title)->_content($this->content)->_imgs($this->upimgs)->_files($this->upfiles);
                $post->_allowReply(intval($this->allowReply))->_can_sign_up(intval($this->can_sign_up));

                if( intval($this->can_sign_up)==1 )
                {
                    $post->_sign_up_options($this->sign_up_options);
                }

                $post->save();


                $this->_ret = ['status'=>1,'msg'=>'post ok','post'=>$post];
            }
        }
        else
        {
            $post = $this->tzsService->addPost(['title'=>$this->title,'content'=>$this->content,'uid'=>$this->_userme['uid'],
                'files'=>$this->upfiles,'imgs'=>$this->upimgs,'allowReply'=>intval($this->allowReply),'can_sign_up'=>intval($this->can_sign_up),
                'sign_up_options'=>$this->sign_up_options
            ]);

            $this->_ret = $post;
            if( $post && $post->_id() )
            {
                $this->tzsService->markPidForUpload($this->upimgs,$post->_id());
                $this->tzsService->markPidForUpload($this->upfiles,$post->_id());
                $this->_ret = ['status'=>1,'msg'=>'post ok','post'=>$post];
            }
        }
    }

    public function deleteUploadCache()
    {
        $ck = 'wxupload_'.$this->_userme['uid'].'_'.$this->__gcode;
        $cache = J7Cache::instance();
        $cache->del($ck);
    }

    public function setUploadCache($v)
    {
        $ck = 'wxupload_'.$this->_userme['uid'].'_'.$this->__gcode;
        $cache = J7Cache::instance();
        $cache->set($ck,$v);
    }

}