<?php

class develop_index_AdminUser_Action extends develop_index_common
{
    public $action;
    public $grouplist;
    public $group;

    public $user;


    public function execute()
    {
        $action = $this->action;
        if(is_null($action)){
            $this->cond = $this->cond?array_filter($this->cond,function($v){if($v!==''){return true;}}):$this->cond;
            $this->ps = $this->DevelopAdminService->GetAllUser($this->ps,$this->cond);
            $items = $this->ps->getItems();

            foreach($items as $k=>$v)
            {
                unset($items[$k]['password']);
                unset($items[$k]['extent']);
                unset($items[$k]['oa_position_id']);
                unset($items[$k]['first_entry']);
                if( $v['gids'] )
                {
                    $items[$k]['group'] = '';
                    $gids = explode(',',$v['gids']);
                    foreach ($gids as $gid)
                    {
                        if( $g = $this->DevelopAdminService->_GetGroupByPK($gid) )
                            $items[$k]['group'] .= $g['title'].' , ';
                    }
                }
                unset($items[$k]['gids']);
            }

            $this->ps->setItems($items);

            $this->_aplh = (new AdminPanelListHelper($this->ps))
                ->set('cond',$this->cond)->set('colshowconfig',array('name'=>array('name'=>'登陆账户')))
                ->set('url',array('edit'=>$this->rewrite_base.'/index/AdminUser/?action=edit&id=','add'=>$this->rewrite_base.'/index/AdminUser/?action=edit&id=','Lock'=>$this->rewrite_base.'/index/AdminUser/?action=lock&id=','show'=>null,'delete'=>$this->rewrite_base.'/index/AdminUser/?action=del&id=')
                );

            $this->action = 'default';
        }

        if($action == 'edit'){
            if( $this->id )
            {
                $this->user = $this->DevelopAdminService->GetUserByPK($this->id);
                $this->user['flag'] = $this->DevelopAdminService->getAdminFlagInfo($this->id);
                $this->user['flags'] = $this->DevelopAdminService->getAdminUserFlagIds($this->id);
            }

            $this->cateinfo = $this->DevelopAdminService->GetAllCate(1);
            $this->grouplist = $this->DevelopAdminService->GetAllGroup();
        }
        if( $action=='updateflag' )
        {
            if( $this->input['uid'] )
            {
                $f=[];
                if( isset($this->input['c_id']) )
                {
                    $f = $this->input['c_id'];
                }
                $this->DevelopAdminService->UpdateUserFlag(array('uid'=>$this->input['uid'],'cateid'=>implode(',',$f)));
            }
            return $this->redirect($this->rewrite_base.'/index/AdminUser/?action=edit&id='.$this->input['uid']);
        }
        if($action == 'update'){

            if( $this->input['uid'] )
            {
                if( $this->DevelopAdminService->GetUserByPK($this->input['uid']) )
                {
                    if( isset($this->input['gids']) )
                    {
                        $this->DevelopAdminService->UpdateUserFlag(array('gids'=>$this->input['gids'],'uid'=>$this->input['uid']));
                        unset($this->input['gids']);
                    }
                    $this->DevelopAdminService->updateAdminUser($this->input);
                }
                return $this->redirect($this->rewrite_base.'/index/AdminUser/?action=edit&id='.$this->input['uid']);
            }
            else
            {
                if(key_exists('uid',$this->input))
                    unset($this->input['uid']);
                $uid = $this->DevelopAdminService->AddUser($this->input);
                $this->DevelopAdminService->UpdateUserFlag(array('uid'=>$uid,'gids'=>$this->input['gids'],'add_uid'=>$this->_meuid,'add_date'=>time()));
                return $this->redirect($this->rewrite_base.'/index/AdminUser/?action=edit&id='.$uid);
            }
        }
        if($action == 'lock'){
            if( $this->user = $this->DevelopAdminService->GetUserByPK($this->id) )
            {
                $this->DevelopAdminService->updateAdminUser(array('is_lock'=>$this->user['is_lock']?0:1,'uid'=>$this->id));
            }
            return $this->redirect($this->rewrite_base.'/index/AdminUser');
        }
        if($action == 'del'){
            $this->DevelopAdminService->DeleteUserByPK($this->id);
            return $this->redirect($this->rewrite_base.'/index/AdminUser');
        }

    }
}