<?php
class tzs_tzs_postdata_Action extends tzs_tzs_common
{

    public $gid;
    public $pid;
    public $from;

    public $post;

    public function execute()
    {
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

            $this->_ret = ['status'=>1,'post'=>$post,'datas'=>$datas];
        }
    }

    public $error='';
    public $data;
    public function sign()
    {
        $this->__j7action['title'] = '报名结果';
        $post = $this->tzsService->getPostById($this->pid);

        if( $post && ( $post->_uid() == $this->_userme['uid'] ) )
        {
            $this->post = $post;
            if( $post->_sign_up_options() )
            {
                $this->data = $this->tzsService->getSignUpLog($this->pid);
                $s = $this->tzsService->getSignUpLogDetail($this->pid);
                $this->post['sign_options'] = $s[1];

                if( isset($_GET['act']) && ($_GET['act']=='down') )
                {
//                    $fileName = '通知说报名_pid_'.$this->pid.'_'.date('YmdHis', time());
//                    header('Content-Type: application/vnd.ms-execl');
//                    header('Content-Disposition: attachment;filename="' . $fileName . '.csv"');

                    $filename = '通知说报名_pid_'.$this->pid.'_'.date('YmdHis', time()).'.xls';
                    header( "Content-Type: application/vnd.ms-excel; name='excel'" );
                    header( "Content-type: application/octet-stream" );
                    header( "Content-Disposition: attachment; filename=".$filename );
                    header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
                    header( "Pragma: no-cache" );
                    header( "Expires: 0" );

                    $title = ['用户名', '填表时间'];
                    foreach ($this->post['sign_options'] as $eso)
                    {
                        $title[] = $eso['t'];
                    }

                    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office"xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><meta http-equiv=Content-Type content="text/html; charset=utf-8"></head><body>';

                    echo "<table border=1><head><tr>";
                    foreach ( $title as $k=>$tt )
                    {
                        echo '<th>'.$tt.'</th>';
                    }
                    echo "</tr></head>";

                    $columNums = count($this->post['sign_options']);
                    foreach ($this->data as $d){
                        $row = [ $d['nickname'],$d['created_at']];

                        for ($i=0;$i<$columNums;$i++){
                            $row[] = $d['signs'][$i]??'';
                        }
                        echo '<tr>';
                        foreach ( $row as $k=>$tt )
                        {
                            echo '<td>'.$tt.'</td>';
                        }
                        echo '</tr>';
                    }
                    echo "</table></body></html>";

//                    foreach ( $title as $k=>$tt )
//                    {
//                        $title[$k] = iconv('UTF-8', 'GB18030', $tt);
//                    }

//                    $fp = fopen('php://output', 'a');
//                    fputcsv($fp, $title,';');
//
//                    $columNums = count($this->post['sign_options']);
//                    foreach ($this->data as $d){
//                        $row = [ iconv('UTF-8', 'GB18030', Util::filterEmoji($d['nickname'])),
//                            iconv('UTF-8', 'GB18030', $d['created_at'])];
//
//                        for ($i=0;$i<$columNums;$i++){
//                            $row[] = iconv('UTF-8', 'GB18030', $d['signs'][$i]??'');
//                        }
//                        fputcsv($fp, $row,';');
//                    }
                    return $this->_setResultType('output');
                }
            }
            else
            {
                $this->error = '没有设置报名项';
            }
        }
        $this->_setView('tzs/postdata_sign.php');
    }

    public $notReadUids;
    public $readUids;
    public $allGroupUsers;
    public $readedUsers;

    public function group()
    {
        $this->__j7action['title'] = '已读/未读 群用户数据';
        //群数据阅读比对
        $post = $this->tzsService->getPostById($this->pid);

        if( $post && ( $post->_uid() == $this->_userme['uid'] ) )
        {
            $this->post = $post;
            if( $this->gid )
            {

                $allgroupuser = $this->tzsService->getUserGroupList($this->gid);
                $readeduser = $this->tzsService->getUserGroupListForPid($this->gid,$this->pid);

                $this->allGroupUsers = []; //array_column($allgroupuser,null,'uid');
                foreach ($allgroupuser as $k=>$eg)
                {
                    $user = $this->UserService->getUserById($eg->_uid());
                    $this->allGroupUsers[$eg->_uid()] = $user;
                }

                $this->readedUsers = array_column($readeduser,null,'uid');

                $allgroupuserIds = array_column($this->allGroupUsers,'uid');
                $this->readUids = array_column($this->readedUsers,'uid');

                $this->notReadUids = array_diff($allgroupuserIds,$this->readUids);

            }
        }
        $this->_setView('tzs/postdata_group.php');
    }

}