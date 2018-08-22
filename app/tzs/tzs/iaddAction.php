<?php
class tzs_tzs_iadd_Action extends tzs_tzs_common
{

    public $pid=0;

    public function execute()
    {
        $post = $this->tzsService->getPostById($this->pid);

        if( $post )
        {
            if( isset($post['imgslist']) && is_array($post['imgslist']) && count($post['imgslist'])>0 )
            {
                $imgslist = $post['imgslist'];
                foreach ($imgslist as $k=>$em)
                {
                    $imgslist[$k]['path'] = Util::relativeHttpsUrl($em['path']);
                }
                $post['imgslist'] = $imgslist;
            }
            $this->_ret = $post;
        }
        else
        {
            $addpost = $this->tzsService->newPost();
            $addpost['fileslist'] = [];
            $addpost['imgslist'] = [];
            $this->_ret = $addpost;
        }


        return $this->_setResultType('default');
    }

}