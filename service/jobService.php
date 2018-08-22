<?php
class jobService extends basic_service
{

    public function deleteNouseFiles()
    {
        echo '=============='.PHP_EOL;
        $p = $this->uploadfileDbDAO->get(['pid'=>0,
            'deleted'=>0,
            'created_at>="'.Util::dateTime( strtotime('-3 day'),'Y-m-d 00:00:00' ).'"',
            'created_at<"'.Util::dateTime( time(),'Y-m-d 00:00:00' ).'"',
        ]);
        if( $p )
        {
            foreach ($p as $ep)
            {
                $realf = SITE_CONTENTDIR.$ep->_path();
                if( file_exists($realf) )
                    unlink($realf);
                echo $realf.PHP_EOL;
                $ep->_deleted(1)->save();
            }
        }
        echo '=============='.PHP_EOL;
        echo count($p).PHP_EOL;
    }

    public function getOnceViewLogs()
    {
        $allvls =  $this->viewlogDbDAO->get(['gid IS NOT NULL'],['created_at'=>'asc']);
        foreach ($allvls as $evl)
        {
            if( trim($evl->_gid()) && $evl->_uid() )
            {
                echo $evl->_uid() .' ------ '.$evl->_gid() . '---' . $evl->_created_at() . PHP_EOL;

                if( ! $this->userGroupDbDAO->getByPk(['uid'=>$evl->_uid(),'gid'=>$evl->_gid()]) )
                {
                    $c = $this->userGroupDbDAO->_new(['uid'=>$evl->_uid(),'gid'=>$evl->_gid()]);
                    if( $evl->_created_at() )
                    {
                        $c->_created_at($evl->_created_at());
                    }
                    else
                    {
                        $c->_created_at(Util::dateTime());
                    }
                    $c->save();

                    echo 'INITED'.PHP_EOL;
                }
            }
        }
    }
}
