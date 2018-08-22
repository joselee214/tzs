<?php
class tzs_apis_xcxupload_Action extends tzs_apis_common
{

    public $__session_id;
    public $__gcode;
    public $openid;

    public $id;

    public $fromtype=0;

    protected $cachekey;

    public function __j7construct()
    {
        parent::__j7construct();
        if( is_null($this->_userme) || !isset($this->_userme['uid']) || !$this->_userme['uid'] )
            throw new BizException(['error'=>'not login'],'loginwx');
        $this->cachekey = $this->cachekeyTempUpload;
    }

    public function execute()
    {
        $this->_setResultType('view');
    }

    public function upload()
    {
        //
        if (isset($_FILES['file'])) {
            $upfile = $_FILES['file'];
            $tofilePath = SITE_CONTENTDIR . '/tzs/'.date('Y-m-d').'/';

            try
            {
                $this->_ret = $movefile = Util::uploadfile($upfile, $tofilePath,'any');
            }
            catch (Exception $e)
            {
                $this->_ret = array('success'=>0,'status'=>0,'msg'=>$e->getMessage());
            }

            if( $movefile && isset($movefile['success']) && $movefile['success']==1 )
            {
                $refilepath = substr($movefile['file'],strlen(SITE_CONTENTDIR));

                $inarr = array(
                    'path'=>$refilepath,
                    'filename'=>$movefile['originname']??'',
                    'uid'=>$this->_userme['uid'],
                    'filesize'=>$movefile['size']??0,
                    'deleted'=>0,
                    'gcode'=>$this->__gcode,
                    'fromtype'=>$this->fromtype,
                    'filetype'=>$movefile['type']??''
                );
                $upload = $this->tzsService->addUpload($inarr);

                if( $this->fromtype==0 )  //0 小程序附件  1小程序图片 2 web附件 3 web图片
                {
                    //add to cache
                    $cache = J7Cache::instance();
                    $o = $cache->get($this->cachekey);
                    $o = $o?:[];
                    array_push($o,$upload);
                    $cache->set($this->cachekey,$o);
                }

                $upload['path'] = SITE_CONTENTDOMAIN.$upload['path'];

                if( $this->fromtype==3 )
                {
                    $upload['path'] = Util::relativeHttpsUrl($upload['path']);
                }

                $this->_ret = array('status'=>1,'success'=>1,'upload'=>$upload);
            }
        }
        return $this->_setResultType('json');
    }

    public function getTempUpload()
    {
        $this->_ret = array('status'=>1,'uploads'=>$this->tzsService->getTempUpload($this->cachekey));
    }

    public function deleteTempUpload()
    {
        $cache = J7Cache::instance();
        $o = $cache->get($this->cachekey);
        $o = $o?:[];
        $this->_ret = array('status'=>1,'uploads'=>$o);
//        $this->tzsService->deleteUpload($this->id);
        if( $o )
        {
            foreach ($o as $k=>$f)
            {
                if( $f['id'] == $this->id )
                {
                    unset($o[$k]);
                    $cache->set($this->cachekey,$o);
                    break;
                }
            }
            $this->_ret = array('status'=>1,'uploads'=>array_values($o));
        }
    }

    public function download()
    {
        if( isset($_GET['__session_id']) )
        {
            return $this->redirect('/apis/xcxupload/download?id='.$this->id);
        }
        $this->_ret = array('status'=>0,'success'=>0,'error'=>'没有找到文件','id'=>$this->id);
        $d = $this->tzsService->getUploadfilesByIds([$this->id]);
        if( $d && isset($d[0]) && $d[0] instanceof j7data_uploadfile )
        {
            /**
             * @var j7data_uploadfile $file
             */
            $file = $d[0];
            $refilepath = SITE_CONTENTDIR . substr($file->_path(),strlen(SITE_CONTENTDOMAIN));
            if( file_exists($refilepath) )
            {
                $file_size = filesize($refilepath);
                header("Content-Disposition:attachment;filename = ".$file->_filename());
                header("Content-type:application/octet-stream");
                Header("Accept-Ranges:bytes");
                Header("Accept-Length:".$file_size);
                $buffer = 102400;
                $fp = fopen($refilepath,"r+") or die('打开文件错误');
                while(!feof($fp)){
                    $file_data = fread($fp,$buffer);
                    echo $file_data;
                }
                fclose($fp);
                $this->_setResultType('output');
            }
        }
    }

}