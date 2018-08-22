<?php echo '<?php';?>

require_once __DIR__.'/UnitTest_common.php';

class UnitTest_<?php echo $this->param['fclass'];?>_Action extends UnitTest_common
{
    public $cname='<?php echo ($this->param['fclass']!=$this->param['ftestclass']?$this->param['ftestclass']:'');?>';           //测试的class对象与方法 UnitTest_index_UnitTestAction_unittest // xxxService_unittest //默认将通过本类类名查找
    public $initparams=<?php echo var_export($this->param['initparams'],true);?>; //初始化类的参数
    public $callparams=<?php echo var_export($this->param['callparams'],true);?>; //方法调用的参数
    public $assertfun='';       //验证方法 //默认为AssertEquals

	public function execute()
	{
        parent::execute();

        //$this->cname = '<?php echo $this->param['fname'];?>';
        //$this->initparams = [];
        //$this->callparams = array(1,2);
        //$this->assertfun = 'AssertEquals2';
        //parent::execute();

        return $this->viewtype;
	}

    //返回值必须是 true | false
    //若有提示消息需使用推送 $this->data['msg'][] = 'xxSSxx';
    public function AssertEquals($ret)
    {
        return false;
    }

}