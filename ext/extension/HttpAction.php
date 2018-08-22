<?php
require_once J7SYS_CORE_DIR . '/Action.php';
require_once __DIR__ . '/../lib/FuncHelper.php';

abstract class HttpAction extends Action
{
    protected $_userme = null;

    public $_passport_config;

    public $input = [];

    public $_cart = [];
    public $_gcode;

    /**
     * @var UserService
     */
    protected $UserService;

    protected $__j7action = array(
        'title' => '',
        'description' => '',
        'keywords' => '',
        'siteheadplus' => '',
        'siteheadscript' => ''
    );

    public function _error($method=null)
    {
        $error = J7Validate::instance()->getErrors();
        debug('validate_error_info',$error);
        throw new BizException($error,'notfound');
    }

    public function __j7construct()
    {
        parent::__j7construct();

        $this->_setView('view','type','tips');
        $this->_setView('tips/show.php','resource','tips');
        $this->_setView('view','type','empty');
        $this->_setView('_public/empty.php','resource','empty');
        $this->_setView('view','type','nolayout');
        $this->_setLayout('nolayout','layout','nolayout');
        $this->_setView('view','type','notp');
        $this->_setLayout('notp','layout','notp');

        if ( RuntimeData::get('j7_probizinfo', '_meuid') ) {
            $this->_userme = $this->UserService->getUserById(RuntimeData::get('j7_probizinfo', '_meuid'));
        }

        if ( strpos($this->rewrite_base,'/nolayout')!==false ) {
            $this->_setLayout('_layout/nolayout.php');
        } else {
            $this->_setLayout('_layout/base.php');
        }

        if( Util::isAjax() )
            $this->_setLayout(null);

        $_config = config('j7initalize');
        if( isset($_config['_default']) && isset($_config['_default']['filters']) && in_array('Session',$_config['_default']['filters']) )
        {
            if ($this->__applicationName!='admin' && $this->__applicationName!='develop')
                $this->_passport_config = FactoryObject::Instance('sharedSessionManager')->getPassportConfig();

            //这里判断gcode与pcdaddr 并生成跟踪
            $this->_gcode = FactoryObject::Instance('sharedSessionManager')->initGcode();

            //记录转换跟踪源
            if( isset($_GET['__t']) && empty($_COOKIE['__t']) && empty($_SESSION['__t'])  )
                FactoryObject::Instance('sharedSessionManager')->setShareData('__t',$_GET['__t'],'cookie');
        }
    }

    /**
     * 请求转向
     * @param $url
     */
    protected function redirect($url='/',$type='header',$output='',$isAddRewrite=true)
    {
        if($output)
            $this->_setView($output,'output','redirect'); //设置显示内容
        return parent::redirect($url,$type,$isAddRewrite);
    }
}
?>