<?php

class develop_index_WebTest_Action extends develop_index_common
{
    public $param;
    public $clitype;

    public $data;
    public $callback;
    public $autoexecutechecked;

    public $__cache;

    public $test = 1;


    public function __j7construct()
    {
        ini_set("eaccelerator.enable", "0");
        parent::__j7construct();
        $this->__cache = J7Cache::instance();
    }

    /**
     * @return void
     */
    public function execute()
    {
        debug('UnitTest::execute', '这里是测试debug正常与否的!');
    }

    public $issucctoend;

    public function __destruct()
    {
        if ($this->issucctoend === false) {
            $this->data['msgtest'] = RuntimeData::get('FactoryForTest');
            $this->data['msg'][] = '执行过程没有正常中止,请检查过程中的语法错误或者exit指令！';
            echo $this->callback . '(' . json_encode($this->data) . ')';
        }
    }

    public function executeall()
    {
        $dir = dir(J7Config::instance()->getAppDir('develop').'/UnitTest/');
        $this->data['testf'] = [];
        while ($entry = $dir->read()) {
            if ($entry == '.' || $entry == '..') continue;
            if (substr($entry, -10, 10) == 'Action.php') {
                $ftime = filemtime( J7Config::instance()->getAppDir('develop').'/UnitTest/' . $entry);
                $this->data['testf'][$ftime] = str_replace('__Action.php', '', $entry);
            }
        }
        krsort($this->data['testf']);
        $this->_setView('index/unittestAll.php');
    }

    public function changeselect()
    {
        $this->_setView('data','resource','jsonp');
        if ($this->clitype == 'submit') {
            $this->issucctoend = false;
            try {
                $this->param['initparams'] = json_decode($this->param['initparams'], true);
                $this->param['callparams'] = json_decode($this->param['callparams'], true);
            }
            catch (Exception $e) {
                $this->data['msg'][] = '传入参数不是标准的json格式字符串!!';
                return $this->_setResultType('jsonp');
            }
            try
            {
                RuntimeData::del('FactoryForTest');
                $classname = ($this->param['folder'] == 'null' ? '' : $this->param['folder'] . '_') . $this->param['class'];
                $classnamef = $classname;


                if( $this->autoexecutechecked==1 )
                {
                    debug('in autoexecutechecked',1);
                    $ccckey = 'logtime|Unittest|'.$classnamef.'|uid|'.$this->_meuid;

                    if( $this->param['stype']=='Action' )
                    {
                        $refile = J7Config::instance()->getAppDir($this->param['app']).'/'.($this->param['folder'] == 'null' ? '' : $this->param['folder'] . '/').$this->param['class'].'.php';
                    }
                    elseif ($this->param['stype'] == 'service') {
                        $refile = J7SYS_SERVICE_DIR.'/'.$this->param['class'].'.php';
                    }
                    elseif ($this->param['stype'] == 'service') {
                        $refile = J7SYS_DAO_DIR.'/'.$this->param['class'].'.php';
                    }



                    $ftime = filemtime($refile);
                    $ccctime = $this->__cache->get($ccckey);
                    if( $ccctime == $ftime )
                    {
                        $this->data['ret']='J7AUTOEXECUTE';
                        $this->issucctoend = true;
                        return $this->_setResultType('jsonp');
                    }
                    else
                    {
                        RuntimeData::set('FactoryForTest', 'J7AUTOEXECUTE__RUN__TIME__'.date('Y-m-d H:i:s') );
                        $this->__cache->set($ccckey,$ftime,3600);
                    }
                }

//                debug($refile);
                switch ($this->param['stype']) {
                    case 'dao':
                    case 'service':
                        $classname = $this->param['class'];
                        break;
                    default:
                        if($this->param['class']!='actions')
                            $classname = substr($classname,0,-6).'_Action';
                        else{
                            $classname = substr($classname,0,-8).'_Action';
                        }
                }



                $methodf = $this->param['method'];
                $split = explode(' :: ', $this->param['method']);
                $method = $split[0];

                if( $this->param['logparam'] = explode(',',trim($this->param['logparam'],',')) )
                {
                    $logconfig = [];
                    foreach($this->param['logparam'] as $es)
                    {
                        $logconfig[$es] = 1;
                    }
                    RuntimeData::set('FactoryForTestParam', 'logconfig',$logconfig);
                }

                $cachearr = array('stype'=>$this->param['stype'],'classname'=>$classnamef,'method'=>$methodf,'initparams'=>json_encode($this->param['initparams']),'callparams'=>json_encode($this->param['callparams']));
                $this->__cache->set('tmpsavelog|unittest|id='.$this->_meuid,$cachearr,3600*5);

                RuntimeData::set('FactoryForTest', 'J7SYS_CLASS >>> START_INIT >>> '.$classname );
                $action = FactoryObject::Get($classname, $this->param['initparams'], $this->param['app']);

                RuntimeData::set('FactoryForTest', 'J7SYS_CLASS >>> START_CALL >>>'.$classname.' -> '.$method );
                if ($this->param['callparams'])
                    $ret = call_user_func_array(array($action, $method), $this->param['callparams']);
                else
                    $ret = $action->$method();

                if( is_array($ret))
                    RuntimeData::set('FactoryForTest', 'J7SYS_CLASS >>> RESULT >>>'.json_encode($ret) );
                else
                    RuntimeData::set('FactoryForTest', 'J7SYS_CLASS >>> RESULT >>>'.var_export($ret,true) );

                $this->data['ret'] = $ret;
            }
            catch (Exception $e) {
                $this->data['msg'][] = '执行过程中发生异常!!';
                $this->data['msg'][] = 'J7Exception in ' . $e->getFile() . ', on line :' . $e->getLine();
            }
            $this->data['msgtest'] = RuntimeData::get('FactoryForTest');
            $this->issucctoend = true;
            return $this->_setResultType('jsonp');
        }

        /*
        ********************************************************************
        ********************************************************************
         * 保存测试过程
         */
        if ($this->clitype == 'savesubmit') {

            try {
                $this->param['initparams'] = json_decode($this->param['initparams'], true);
                $this->param['callparams'] = json_decode($this->param['callparams'], true);
            }
            catch (Exception $e) {
                $this->data['strmsg'][] = '传入参数不是标准的json格式字符串!!';
                return $this->_setResultType('jsonp');
            }
            $classname = ($this->param['folder'] == 'null' ? '' : $this->param['folder'] . '_') . $this->param['class'];

            $split = explode(' :: ', $this->param['method']);
            $method = $split[0];

            $filename = $classname.'_'.$method;
            $this->param['ftestclass'] = $filename;
            $this->param['fclass'] = $filename;


            $fname = J7Config::instance()->getAppDir('develop').'/UnitTest/'.$this->param['fclass'].'Action.php';
            $this->param['fname'] = $this->param['fclass'];

            if( Util::isReadable($fname) )
            {
                $this->data['strmsg'][] = $classname.'测试文件已存在';
                $this->param['fclass'] = $filename.'T'.Util::getIdMicroTime();
                $fname = 'app/UnitTest/'.$this->param['fclass'].'__Action.php';
            }

            //生成文件
            ob_clean();
            require( J7SYS_CORE_DIR.'/template_file/unittest.php');


            $filestr = ob_get_clean();
            file_put_contents($fname,$filestr);


            $this->data['strmsg'][] = '保存完成!!,注意修改AssertEquals方法';
            $this->data['strmsg'][] = '保存文件为:'.$fname;
            $this->data['strmsg'][] = '<a href="/develop.php/UnitTest/'.$this->param['fclass'].'" target="_blank">访问地址</a>';
            return $this->_setResultType('jsonp');
        }


        /*
        ********************************************************************
        ********************************************************************
         * init界面选择
         */

        $iii = 0;
        $this->data['msg'] = [];
        //$cachearr = array('stype'=>$this->param['stype'],'classname'=>$classname,'method'=>$method,'initparams'=>json_encode($this->param['initparams']),'callparams'=>json_encode($this->param['callparams']));
        $cachearr = $this->__cache->get('tmpsavelog|unittest|id='.$this->_meuid);

        try {
            $this->issucctoend = false;

            $dir = dir( J7SYS_APPLICATION_DIR.DIRECTORY_SEPARATOR);
            $this->data['app'] = [];
            while ($entry = $dir->read()) {
                if (substr($entry, 0, 1) == '.' || substr($entry, 0, 1) == '_') continue;
                $ftime = filemtime(J7Config::instance()->getAppDir($entry));
                if (is_dir(J7Config::instance()->getAppDir($entry))) {
                    if (isset($this->data['app'][$ftime]))
                        $ftime = $ftime . '_' . $iii;
                    $this->data['app'][$ftime] = $entry;
                }
                $iii++;
            }
            krsort($this->data['app']);

            if ( $this->param['stype'] ) {
                if ($this->param['stype'] == 'Action' && $this->param['app']=='null') {
                    $this->clitype = 'app';
                    $this->param['app'] = current($this->data['app']);
                }
                elseif ($this->param['stype'] == 'service') {
                    $pathtoclass = J7SYS_SERVICE_DIR.DIRECTORY_SEPARATOR;
                    $this->data['class'] = [];
                    if( isset($this->param['app']) && $this->param['app'] && $this->param['app']!='__all__' && $this->param['app']!='null' )
                        $pathtoclass = J7Config::instance()->getAppDir($this->param['app']).'/service/';
                    $dir = dir($pathtoclass);
                    while ($entry = $dir->read()) {
                        if (is_file($pathtoclass . $entry))
                            if (substr($entry, -11, 11) == 'Service.php') {
                                $ftime = filemtime($pathtoclass . $entry );
                                if (isset($this->data['class'][$ftime]))
                                    $ftime = $ftime . '_' . $iii;
                                $this->data['class'][$ftime] = str_replace('.php', '', $entry);
                                $iii++;
                            }
                    }
                    array_unshift($this->data['app'],'__all__');
                }
                elseif ($this->param['stype'] == 'dao') {
                    $pathtoclass = J7SYS_DAO_DIR;
                    $this->data['class'] = [];
                    if( isset($this->param['app']) && $this->param['app'] && $this->param['app']!='__all__' && $this->param['app']!='null' )
                        $pathtoclass = J7Config::instance()->getAppDir($this->param['app']).'/dao/';
                    $dir = dir($pathtoclass);
                    while ($entry = $dir->read()) {
                        if( is_file($pathtoclass.$entry) )
                        {
                            $ftime = filemtime($pathtoclass.$entry);
                            if (isset($this->data['class'][$ftime]))
                                $ftime = $ftime . '_' . $iii;
                            $this->data['class'][$ftime] = str_replace('.php', '', $entry);
                            $iii++;
                        }
                    }
                    array_unshift($this->data['app'],'__all__');
                }
            }

            if ($this->clitype == 'app' && $this->param['app']) {
                if($this->param['stype'] == 'Action')
                {
                    $dir = dir( J7Config::instance()->getAppDir($this->param['app']).'/');
                    $this->data['folder'] = [];
                    while ($entry = $dir->read()) {
                        if ($entry == '.' || $entry == '..' || $entry == 'UnitTest') continue;
                        if (substr($entry, 0, 1) == '.' || substr($entry, 0, 1) == '_') continue;
                        $ftime = filemtime( J7Config::instance()->getAppDir($this->param['app']).'/' . $entry);
                        if (is_dir( J7Config::instance()->getAppDir($this->param['app']).'/' . $entry)) {
                            if (isset($this->data['folder'][$ftime]))
                                $ftime = $ftime . '_' . $iii;
                            $this->data['folder'][$ftime] = $entry;
                        }
                        $iii++;
                    }
                    krsort($this->data['folder']);
                    $this->clitype = 'folder';
                    $this->param['folder'] = current($this->data['folder']);
                }
            }
            if ($this->clitype == 'folder' && $this->param['folder']) {
                $dir = dir( J7Config::instance()->getAppDir($this->param['app']).'/' . $this->param['folder']);
                $this->data['class'] = [];
                while ($entry = $dir->read()) {
                    if ($entry == '.' || $entry == '..') continue;
                    if (substr($entry, -10, 10) == 'Action.php' || $entry=='actions.php')
                    {
                        $ftime = filemtime( J7Config::instance()->getAppDir($this->param['app']).'/' . $this->param['folder'] . '/' . $entry);
                        if (isset($this->data['class'][$ftime]))
                            $ftime = $ftime . '_' . $iii;
                        $this->data['class'][$ftime] = str_replace('.php', '', $entry);
                        $iii++;
                    }
                }
            }

            if ( (!$this->param['class'] || $this->param['class']=='null') && isset($this->data['class']) && $this->data['class']) {
                krsort($this->data['class']);
                //$this->clitype='class';
                $this->param['class'] = current($this->data['class']);
            }

            //开始构建class
            if ($this->param['class'] && $this->param['class'] != 'null') {
                if($this->param['stype']=='Action')
                {
                    $filepath = J7Config::instance()->getAppDir($this->param['app']) . '/' . $this->param['folder'] . '/' . $this->param['class'] . '.php';
                    if( $this->param['class']=='actions' )
                        $classname = $this->param['folder'].'_Action';
                    else
                        $classname = $this->param['folder'].'_'.substr($this->param['class'],0,-6).'_Action';
                }
                else
                {

                    if( isset($this->param['app']) && $this->param['app'] && $this->param['app']!='__all__' && $this->param['app']!='null' )
                        $filepath = J7Config::instance()->getAppDir($this->param['app']).'/'.strtolower($this->param['stype']).'/'. $this->param['class'] . '.php';
                    elseif( $this->param['stype']=='service' )
                        $filepath = J7SYS_SERVICE_DIR.'/'. $this->param['class'] . '.php';
                    elseif( $this->param['stype']=='dao' )
                        $filepath = J7SYS_DAO_DIR.'/'. $this->param['class'] . '.php';

                    $classname = $this->param['class'];
                }

                if (!Util::isReadable($filepath)) {
                    $this->data['msg'][] = '无法读取文件' . $filepath;
                }
                else {

                    require_once($filepath);
                    try {

                        if($this->param['stype']=='Action')
                        {
                            $classref = new ReflectionClass($this->param['app'].'_'.$classname);
                        }
                        else
                        {
                            $classref = new ReflectionClass($classname);
                        }
                        $instanceclass = FactoryObject::Get($classname,[],$this->param['app']); // new $classname();
                    }
                    catch (Exception $e) {
                        throw new J7Exception('Error @ load class:' . $classname . ' in File:' . $filepath);
                    }

                    //var_dump($classname);die;

                    try {
                        //获取Method
                        $allmethod = $classref->getMethods(ReflectionMethod::IS_PUBLIC);
                        if ($allmethod) {
                            foreach ($allmethod as $em) {
                                if ($em->class != 'Action' && $em->class!='baseControllerModel')
                                    $this->data['method'][] = $em->name . ' :: (' . $em->class . ')';
                            }
                        }
                        //获取Properties
                        $allprops = $classref->getProperties(ReflectionProperty::IS_PUBLIC);

                        if ($allprops) {
                            $this->data['props'] = [];
                            foreach ($allprops as $em) {
                                if ($em->class != 'Action' && substr($em->name, -7, 7) != 'Service' && substr($em->name, -5, 5) != 'DbDAO' ) {
                                    $defaultValue = $classref->getProperty($em->name)->getValue($instanceclass);
                                    $this->data['props'][$em->name . ' :: ' . '(' . $em->class . ')'] = $defaultValue;
                                }
                            }
                        }
                    }
                    catch (Exception $e) {
                        throw new J7Exception('Error @ getMethods/getProperties class:' . $classname . ' in File:' . $filepath);
                    }
                }
            }


            if ($this->param['method'] == 'null' || !$this->param['method'])
            {
                if (isset($this->data['method']) && $this->data['method'])
                {
                    $this->param['method'] = current($this->data['method']);
                    $nowselectclass = (!$this->param['folder']||$this->param['folder']=='null')?$this->param['class']:$this->param['folder'].'_'.$this->param['class'];
                    if( $nowselectclass==$cachearr['classname'] )
                    {
                        $this->param['method'] = $cachearr['method'];
                    }
                }
            }

            $this->data['param'] = $this->param;

            if ($this->param['method'] && isset($classref) && $classref) {
                $this->data['parameters'] = [];
                $split = explode(' :: ', $this->param['method']);
                $method = $split[0];
                $params = $classref->getMethod($method)->getParameters();
                $this->data['DocComment'] = '';
                foreach ($params as $em) {
                    $v = $em->isDefaultValueAvailable() ? $em->getDefaultValue() : '';
                    $this->data['parameters'][] = $v;
                    $this->data['DocComment'] .= '参数:$' . $em->name . '=' . var_export($v,true) . '
';
                }
                $this->data['DocComment'] .= $classref->getMethod($method)->getDocComment();
            }

        }
        catch (Exception $e) {
            ob_clean();
            $this->data['msg'][] = $e->getMessage();
        }

        $this->issucctoend = true;
        return $this->_setResultType('jsonp');
    }
}