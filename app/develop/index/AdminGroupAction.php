<?php
class develop_index_AdminGroup_Action extends develop_index_common
{
    public $grouplist;

    public $action;
    public $user;
    public function execute()
    {
        $action = $this->action;
        if(is_null($action)){
            $this->grouplist = $this->DevelopAdminService->GetAllGroup();

//            foreach ($this->grouplist as $k=>$v)
//            {
//                $t = $v['users'];
//                $this->grouplist[$k]['users'] = '';
//                if( $t )
//                {
//                    foreach ($t as $tuser)
//                    {
//                        $this->grouplist[$k]['users'] .= '<a href="/index/AdminUser/?action=edit&id='.$tuser['uid'].'">'.$tuser['name'].'('.$tuser['truename'].')</a> , ';
//                    }
//                }
//            }

            $this->ps->setItems($this->grouplist);
            $this->_aplh = (new AdminPanelListHelper($this->ps))
                ->set('extconfig',array('showcondarea'=>false))->set('colshowconfig',array('title'=>array('name'=>'名称'),'cate_ids'=>array('name'=>'权限id')))
                ->set('url',array('edit'=>$this->rewrite_base.'/index/AdminGroup/action/editgroup/id/','add'=>$this->rewrite_base.'/index/AdminGroup/action/addGroup','del'=>$this->rewrite_base.'/index/AdminGroup/action/delGroup/id/','show'=>null,'delete'=>null)
                );

            $this->cateinfo = $this->DevelopAdminService->GetAllCate(1);
            $this->action = 'default';
        }

        if($action == 'delGroup'){
            $this->grouplist = $this->DevelopAdminService->DelGroupByPK($this->id);
            return $this->redirect($this->rewrite_base.'/index/AdminGroup');
        }

        if($action == 'addGroup'){
            $this->grouplist = $this->DevelopAdminService->GetAllGroup();
            $this->cateinfo = $this->DevelopAdminService->GetAllCate(1);
            $this->action = 'addgroup';
        }

        if($action == 'editgroup'){//编辑分组 。。 准备

            $id = $this->id;
            $this->groupinfo = $this->DevelopAdminService->_GetGroupByPK($id);
            $this->cateinfo = $this->DevelopAdminService->GetAllCate(1);
            $this->action = 'editgroup';
        }

        if($action == 'updateGroup'){
            //更新分组 。。
            $id = $this->input['gid'];
            $c_ids = isset($this->input['c_id']) ? $this->input['c_id'] : 0;

            if($c_ids != 0){
                $c_ids = implode(',',$c_ids);
            }else{
                return $this->redirect($this->rewrite_base.'/index/AdminGroup');
            }

            $name = $this->input['group_name'];
            if(! $name) {
                return $this->redirect($this->rewrite_base.'/index/AdminGroup');
            }
            $this->DevelopAdminService->UpdateGroupByPK(array('title'=>$name,'cate_ids'=>$c_ids,'id'=>$id));
            return $this->redirect($this->rewrite_base.'/index/AdminGroup');
        }
        if($action == 'doGroup'){
            $c_ids = isset($this->input['c_id']) ? $this->input['c_id'] : 0;
            if($c_ids != 0){
                $c_ids = implode(',',$c_ids);
            }else{
                return $this->redirect($this->rewrite_base.'/index/AdminGroup');
            }
            $name = $this->input['group_name'];
            if(! $name) {
                return $this->redirect($this->rewrite_base.'/index/AdminGroup');
            }
            $this->DevelopAdminService->AddGroup(array('title'=>$name,'cate_ids'=>$c_ids));
            return $this->redirect($this->rewrite_base.'/index/AdminGroup');
        }

    }
}