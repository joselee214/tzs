<?php

class HtmlResultType extends J7ResultType
{
    public function process($result, $action ,$retData=null,$app=null , $isecho= true)
    {
        $html = $this->render($result,$action,$app,$isecho);
        $ret = J7ActionTag::instance()->parse( $html );

        $ret = $ret!==null?$ret:$retData;

        if( $isecho )
        {
            header("Content-Type: text/html; charset=utf-8");
            ob_start();
            echo $ret;
        }
        return [$ret,$result,$action];
    }

    /**
     * @param $result
     * @param Action $action
     * @param null $viewinsName
     * @param null $app
     * @return mixed
     */
    protected function render($result, $action,$app=null ,$isecho=true)
    {
        if( $action instanceof AOPProxyControllerModel )
            $action = $action->__getProxyObject();

        $appFrom = $app?:$action->_actionToken()->getApp();
        $app = $app?:$action->__applicationName;

        $view = J7View::instance([$action,J7Config::instance()->getAppDir($app).'/',J7Config::instance()->getAppDir($appFrom).'/']);

        $action_config = $action->__getConfig();

        if( isset($action_config[$result]['resource']) )
            $resource = $action_config[$result]['resource'];

        $html = $action->$resource;

        $template = null;
        if (array_key_exists('layout', $action_config[$result])) {
            $template = $action_config[$result]['layout'];
        }

        return $view->renderHtml($app,$html, $template);
    }
}