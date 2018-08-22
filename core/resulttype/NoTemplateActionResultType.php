<?php

class NoTemplateActionResultType extends ActionResultType
{
    public function process($result, $action , $retData=null,$app=null , $isecho= true)
    {
        $ret = $this->render($result,$action,$retData,$app,$isecho);
        if( $isecho && $ret )
        {
            ob_start();
            echo  $ret;
        }
        return [$ret,$result,$action];
    }

    /**
     * @param $result
     * @param Action $action
     * @param null $viewinsName
     * @param null $app
     * @return null
     */
    protected function render($result, $action , $retData=null, $app=null , $isecho= true)
    {
        if( $action instanceof AOPProxyControllerModel )
            $action = $action->__getProxyObject();

        $app = $app?:$action->_actionToken()->getApp();

        $action_config = $action->__getConfig();


        if( isset($action_config[$result]['resource']) )
            $resource = $action_config[$result]['resource'];

        if( $action->$resource instanceof ActionToken )
        {
            return parent::render($result,$action,$retData,$app,$isecho);
        }
        
        list($r_type,$r_action,$r_app,$r_ret) = $action->$resource;

        if( $r_type=='view' )
        {
            $view = J7View::instance([$action,J7Config::instance()->getAppDir($r_app).'/',J7Config::instance()->getAppDir($app).'/']);

            $template = null;
            return J7ActionTag::instance()->parse($view->renderHtml($app,$r_ret,$template));
        }
        else
        {
            $processor = J7ResultType::instance($r_type);
            $processor->process($r_type,$r_action,$r_ret,$app,true);
            return;
        }

    }
}