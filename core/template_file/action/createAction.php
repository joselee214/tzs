<?php
require_once J7SYS_CORE_DIR.'/template_file/createHelper.php';

class j7core_index_create_Action extends j7core_index_common
{
    public $act=null;
    public $param;
    public $path;

    public function execute()
    {
        $helper = new createHelper();
        $mp = $this->_actionToken()->getParams();

        if( isset($mp['r']) )
            $mp['return'] = $mp['r'];

        if( isset($mp['p']) )
            $mp['app'] = $mp['p'];
        if( isset($mp['f']) )
            $mp['module'] = $mp['f'];

        if( isset($mp['n']) )
            $mp['name'] = $mp['n'];




        if( isset($this->path) && $this->path )
        {
            $ret = false;
            $pathsp = explode(':',$this->path);
            if( count($pathsp)==2 )
            {
                $app = $pathsp[0];
                $helper->checkapp($app);
                echo 'checkapp OK'.PHP_EOL;

                if( isset($pathsp[1]) && $pathsp[1] && ($actpath = trim($pathsp[1],'/')) )
                {
                    $actpathsp = explode('/',$actpath);

                    if( isset($actpathsp[0]) )
                    {
                        $moduleName = $actpathsp[0];
                        $dir = J7Config::instance()->getAppDir($app);
                        $fpath = $dir.'/'.$moduleName;
                        if( ! $helper->checkDir($fpath) )
                        {
                            $params = ['name'=>$moduleName,'app'=>$app];
                            $mmp = array_merge($mp,$params);
                            $ret = $helper->fileTemplate('appfolder',$this->param,$mmp);
                            print_r(isset($ret['msg'])?$ret['msg']:$ret);
                        }

                        if( isset($actpathsp[1]) )
                        {
                            $actionName = $actpathsp[1];
                            $params = ['name'=>$actionName,'module'=>$actpathsp[0],'app'=>$app];
                            $mmp = array_merge($mp,$params);
                            $ret = $helper->fileTemplate('action',$this->param,$mmp);
                            print_r(isset($ret['msg'])?$ret['msg']:$ret);
                        }
                        $ret = true;
                    }

                }

            }
            echo $ret?'':'path必须类似 front:index/index 模式,否则无法识别';
        }
        else
        {
            if( !isset($this->param[0]) )
                throw new coreException('必须传入类型');
            if( is_null($this->act) )
                $this->act = $this->param[0];

            if( !isset($this->param['_reset']) )
            {
                $this->param['_reset'] = false;
            }

            if( in_array( $this->act,['dao','service','appfolder','action','module','app','a','s','d','f','p'] ) )
            {
                // module=appfolder=f   dao=d service=s action=a app=p

                if( $this->act=='a' )
                    $this->act = 'action';
                if( $this->act=='s' )
                    $this->act = 'service';
                if( $this->act=='d' )
                    $this->act = 'dao';
                if( $this->act=='f' )
                    $this->act = 'appfolder';
                if( $this->act=='module' )
                    $this->act = 'appfolder';
                if( $this->act=='p' )
                    $this->act = 'app';


                if( $this->act != 'app' )
                {
                    if( !isset($mp['app']) )
                        throw new coreException('必须设置app,全局可用%');
                    if($mp['app']=='%') //__basic__
                        unset($mp['app']);
                }
                ob_start();
                $ret = $helper->fileTemplate($this->act,$this->param,$mp);
                print_r(isset($ret['msg'])?$ret['msg']:$ret);
            }
        }
    }
}