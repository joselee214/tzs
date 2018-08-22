<?php
class tzsService extends basic_service
{
    

    public function test($test)
    {
        return 'test:'.$test;
    }

    public function addUpload($data=[])
    {
        $new = $this->uploadfileDbDAO->_new($data);
        $new->_created_at(Util::dateTime())->save();
        return $new;
    }

    public function getTempUpload($k)
    {
        $cache = J7Cache::instance();
        $o = $cache->get($k);
        $o = $o?:[];
        return array_values($o);
    }


    public function deleteUpload($id)
    {
        $upload = $this->uploadfileDbDAO->getByPk($id);
        if( $upload )
        {
            $realf = SITE_CONTENTDIR.$upload->_path();
            if( file_exists($realf) )
                unlink($realf);
            $upload->_deleted(1)->save();
            return 1;
        }
        return null;
    }

    public function markPidForUpload($str,$pid)
    {
        if( is_string($str) )
            $files = array_filter(explode(',',$str));
        else
            $files = array_filter($str);

        if( $files && is_array($files) )
        {
            foreach ($files as $ef)
            {
                $f = $this->uploadfileDbDAO->getByPk($ef);
                $f->_pid($pid)->save();
            }
        }
    }

    public function newPost($data=[])
    {
        return $this->postsDbDAO->_new($data);
    }

    public function addPost($data=[])
    {
        $new = $this->postsDbDAO->_new($data);
        $new->save();
        return $new;
    }

    public function addReply($data=[])
    {
        $new = $this->replysDbDAO->_new($data);
        $new->_created_at(Util::dateTime())->_sort(0);
        $new->save();
        $this->postsDbDAO->incrByPk(['id'=>$data['pid'],'replytimes'=>1]);
        return $new;
    }

    public function getPosts(J7Page $ps,$cond=[])
    {
        $ps = $this->pager($this->postsDbDAO,$ps,$cond,['id'=>'desc']);
        if($ps->getTotalCount())
        {
            $items = $ps->getItems();
            if( $items )
            {
                foreach ($items as $k=>$item)
                {
//                    if( $user = $this->UserService->getUserById($item->_uid()) )
//                    {
//                        $items[$k]['logo'] = $user->_avatarUrl();
//                        $items[$k]['nickname'] = $user->_name();
//                    }
//                    $items[$k]['imgnums'] = $items[$k]['filenums'] = 0;
//                    if( $item['imgs'] && ($imgs=explode(',',$item['imgs'])) )
//                    {
//                        $items[$k]['imgnums'] = count($imgs);
//                        if( $firstImg = $this->uploadfileDbDAO->getByPk($imgs[0]) )
//                        {
//                            $items[$k]['logo'] = $firstImg->_path();
//                        }
//                    }
//                    $items[$k]['filenums'] = count(explode(',',$item['files']));
                    $items[$k] = $this->explainPost($item);
                }
                $ps->setItems($items);
            }
        }
        return $ps;
    }

    public function explainPost(j7data_posts $item)
    {
        if( $user = $this->UserService->getUserById($item->_uid()) )
        {
            $item['logo'] = $user->_avatarUrl();
            $item['avatar'] = $user->_avatarUrl();
            $item['nickname'] = $user->_name();
        }
        else
        {
            $item['logo'] = SITE_DOMAIN.DEFAULT_SITE_LOGO;
            $item['avatar'] = SITE_DOMAIN.DEFAULT_SITE_LOGO;
            $item['nickname'] = '已删用户';
        }
        $item['imgnums'] = $item['filenums'] = 0;

        if( $item['imgs'] && ($imgs=array_filter(explode(',',$item['imgs']))) )
        {
            $item['imgslist'] = $this->getUploadfilesByIds($imgs);
            $item['imgnums'] = count($item['imgslist']);
            if( $item['imgslist'] )
            {
                $item['logo'] = $item['imgslist'][0]->_path();
            }
        }
        if($item['files'] &&  ($files = array_filter(explode(',',$item['files']))) )
        {
            $item['fileslist'] = $this->getUploadfilesByIds($files);
            $item['filenums'] = count($item['fileslist']);
        }
        return $item;
    }


    public $wx_fileopen_type = ['doc','xls','ppt','pdf','docx','xlsx','pptx'];
    public $wx_img_type = ['gif','png','jpg','jpeg'];

    public function getUploadfilesByIds($imgs)
    {
        $imgslist = [];
        foreach ($imgs as $eimgid)
        {
            if( $eimg = $this->uploadfileDbDAO->getByPk($eimgid) )
            {
                $tmp = explode('.', $eimg['path']);
                $ext = $tmp[count($tmp) - 1];

                $eimg['wxtype'] = 'down';
                if( in_array($ext,$this->wx_img_type) )
                {
                    $eimg['wxtype'] = 'img';
                }
                elseif ( in_array($ext,$this->wx_fileopen_type) )
                {
                    $eimg['wxtype'] = 'opendoc';
                }

                $eimg['path'] = SITE_CONTENTDOMAIN.$eimg['path'];
                $imgslist[] = $eimg;
            }
        }
        return $imgslist;
    }


    public function exeViewLog($pid,$uid,$postuid=null,$nowgid='')
    {
        if( empty($uid) || empty($pid) )
        {
            return null;
        }
        $k = ['uid'=>$uid,'pid'=>$pid];
        $c = $this->viewlogDbDAO->getByPk($k);

        if( $c )
        {
            $c->_updated_at(Util::dateTime());
        }
        else
        {
            $c = $this->viewlogDbDAO->_new($k)->_created_at(Util::dateTime())->_updated_at(Util::dateTime());
            $this->postsDbDAO->incrByPk(['id'=>$pid,'viewtimes'=>1]);
        }
        if( $nowgid ){
            $c->_gid($nowgid);
            //插入用户记录//
            $this->exeUserJoinGroup($uid,$nowgid);
        }
        $c->save();
        return $c;
    }

    public function exeUserJoinGroup($uid,$gid)
    {
        if( $uid && $gid )
        {
            if( ! $this->userGroupDbDAO->getByPk(['uid'=>$uid,'gid'=>$gid]) )
            {
                $c = $this->userGroupDbDAO->_new(['uid'=>$uid,'gid'=>$gid]);
                $c->_created_at(Util::dateTime());
                $c->save();
                return $c;
            }
        }
        return null;
    }

    public function setReplyTop($id,$pid,$isTop)
    {
        $reply = $this->replysDbDAO->getByPk($id);
        if( $reply )
        {
            if( $isTop )
            {
                $maxsort = $this->replysDbDAO->getMaxRepySort($pid);
                $reply->_sort($maxsort+1);
            }
            else
            {
                $reply->_sort(0);
            }
            $reply->save();
            return true;
        }
        return false;
    }

    public function getReplys(J7Page $ps,$cond=[],$isPostMaster=false,$meuid=null)
    {
        $ps = $this->pager($this->replysDbDAO,$ps,$cond,['sort'=>'desc','id'=>'desc']);
        if($ps->getTotalCount())
        {
            $items = $ps->getItems();
            if( $items )
            {
                foreach ($items as $k=>$item)
                {
                    if( $user = $this->UserService->getUserById($item->_uid()) )
                    {
                        $items[$k]['avatar'] = $user->_avatarUrl();
                        $items[$k]['nickname'] = $user->_name();
                    }

                    if($isPostMaster)
                    {
                        $items[$k]['cansettop'] = 1;
                    }

                    if($isPostMaster || $item->_uid()==$meuid)
                    {
                        $items[$k]['candelete'] = 1;
                    }

//                    $items[$k]['content'] =  nl2br($item['content']);
                }
                $ps->setItems($items);
            }
        }
        return $ps;
    }

    public function getViewLogs(J7Page $ps,$cond=[],$detail='',$order=['created_at'=>'desc'])
    {
        if( $detail=='u' )
        {
            $ps = $this->pager($this->viewlogDbDAO,$ps,$cond,$order);
        }
        else
        {
            $ps = $this->viewlogDbDAO->getMyReadedViewLog($ps,$cond,$order);
        }
        if($ps->getTotalCount())
        {
            $items = $itemsResult = $ps->getItems();
            if( $items )
            {
                foreach ($items as $k=>$item)
                {
                    if( $detail=='u' )
                    {
                        if( $user = $this->UserService->getUserById($item->_uid()) )
                        {
                            $itemsResult[$k]['avatar'] = $user->_avatarUrl();
                            $itemsResult[$k]['nickname'] = $user->_name();
                        }
                        else
                        {
                            unset($itemsResult[$k]);
                        }
                    }
                    elseif ( $detail=='p' )
                    {
                        $item = $this->getPostById($item['id']);
                        $itemsResult[$k] = $item;
                    }
                }
                $ps->setItems(array_values($itemsResult));
            }
        }
        return $ps;
    }

    public function getPostById($id)
    {
        $post = $this->postsDbDAO->getByPk($id);
        if( $post )
        {
            $post = $this->explainPost($post);
        }
        return $post;
    }

    public function getReplyById($id)
    {
        return $this->replysDbDAO->getByPk($id);
    }

    public function deletePostById($id)
    {
        return $this->postsDbDAO->updateByPk(['id'=>$id,'deleted'=>1]);
    }

    public function deleteReplyById($id,$pid=0)
    {
        $r = $this->replysDbDAO->updateByPk(['id'=>$id,'deleted'=>1]);
        if($r && $pid){
            $this->postsDbDAO->incrByPk(['id'=>$pid,'replytimes'=>-1]);
        }
        return $r;
    }



    public function addShareGroupLog($data)
    {
        $sl = $this->shareGroupLogDbDAO->_new($data);
        $sl->_created_at(Util::dateTime());
        $sl->save();
        return $sl;
    }

    public function getSignUpLogNums($pid)
    {
        return $this->signUpLogDbDAO->count(['pid'=>$pid]);
    }

    public function getSignUpLog($pid)
    {
        $data = $this->signUpLogDbDAO->get(['pid'=>$pid]);
        if( $data )
        {
            foreach ($data as $k=>$d)
            {
                if( $user = $this->UserService->getUserById($d->_uid()) )
                {
                    $data[$k]['avatar'] = Util::relativeHttpsUrl($user->_avatarUrl());
                    $data[$k]['nickname'] = $user->_name();
                }
                $data[$k]['signs'] = json_decode($d->_signup(),true);
                $data[$k]['signs'] = $data[$k]['signs']?:[];
            }
        }
        return $data;
    }

    public function getSignUpLogDetail($pid,$uid=null)
    {
        $post = $this->tzsService->getPostById($pid);
        if( $post )
        {
            $sign_options = array_filter(explode(PHP_EOL,$post->_sign_up_options()));
            if( $sign_options )
            {
                $values = [];
                $userIsSign = 0;
                if( $uid && ($usersign = $this->signUpLogDbDAO->getByPk(['uid'=>$uid,'pid'=>$pid]) ) )
                {
                    $userIsSign = 1;
                    $values = json_decode($usersign->_signup(),true);
                }
                $sign_options_arr = [];
                foreach ($sign_options as $k=>$option)
                {
                    $sign_options_arr[] = ['t'=>$option,'v'=>$values[$k]??''];
                }
                return [$userIsSign,$sign_options_arr];
            }
        }
        return [0,[]];
    }

    public function deleteSignUpLog($pid,$uid)
    {
        $s = $this->signUpLogDbDAO->deleteByPk(['uid'=>$uid,'pid'=>$pid]);
        if( $s )
        {
            $this->postsDbDAO->incrByPk(['id'=>$pid,'signtimes'=>-1]);
        }
        return $s;
    }

    public function execSignUpLog($uid,$pid,$val)
    {
        $s = $this->signUpLogDbDAO->getByPk(['uid'=>$uid,'pid'=>$pid]);
        if( empty($s) )
        {
            $s = $this->signUpLogDbDAO->_new(['uid'=>$uid,'pid'=>$pid]);
            $this->postsDbDAO->incrByPk(['id'=>$pid,'signtimes'=>1]);
        }
        $s->_signup($val)->_created_at(Util::dateTime())->save();
        return $s;
    }

    public function getShareLog($pid)
    {
        $items = $this->shareGroupLogDbDAO->get(['pid'=>$pid],['sglid'=>'desc'],50);
        if( $items )
        {
            foreach ($items as $k=>$item)
            {
                if( $user = $this->UserService->getUserById($item->_uid()) )
                {
                    $items[$k]['avatar'] = $user->_avatarUrl();
                    $items[$k]['nickname'] = $user->_name();
                }
                else
                {
                    $items[$k]['avatar'] = SITE_DOMAIN.DEFAULT_SITE_LOGO;
                    $items[$k]['nickname'] = '已删用户';
                }
            }
        }
        return $items;
    }

    public function getUserGroupNums($gid)
    {
        return $this->userGroupDbDAO->count(['gid'=>$gid]);
    }

    public function getUserGroupList($gid)
    {
        return $this->userGroupDbDAO->get(['gid'=>$gid]);
    }

    public function getUserGroupListForPid($gid,$pid)
    {
        return $this->viewlogDbDAO->get(['pid'=>$pid,'gid'=>$gid]);
    }

    public function getUserGroupNumsForPid($gid,$pid)
    {
        return $this->viewlogDbDAO->count(['pid'=>$pid,'gid'=>$gid]);
    }
}
