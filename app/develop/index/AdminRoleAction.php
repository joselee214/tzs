<?php

class develop_index_AdminRole_Action extends develop_index_common
{
    public function execute()
    {
        $this->tree = $this->DevelopAdminService->GetAllCate(1);
    }

    public function delcate()
    {
        $this->DevelopAdminService->deleteCateByID($this->id);
        return $this->redirect($this->rewrite_base.'/index/AdminRole/');
    }

    public function editcate()
    {
        $this->item = $this->DevelopAdminService->GetCateByID($this->id);
        $this->execute();
    }

    public function addtree()
    {
        if( $this->input )
        {
            if(isset($this->input['url']))
                $this->input['url'] = trim($this->input['url']);
            if(isset($this->input['link']))
                $this->input['link'] = trim($this->input['link']);
            
            if( isset($this->input['id']) && $this->DevelopAdminService->GetCateByID($this->input['id']) )
                $this->DevelopAdminService->UpdateCateByPK($this->input);
            else
            {
                $this->DevelopAdminService->AddCate($this->input);
            }
        }
        return $this->redirect($this->rewrite_base.'/index/AdminRole/');
    }
}