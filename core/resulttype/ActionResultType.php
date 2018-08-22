<?php

class ActionResultType extends J7ResultType
{
    public static $_list_ret = [];
    public static $_list_action = [];

    public function process($result, $action , $retData=null,$app=null , $isecho= true)
    {

        list($html,$reall_ret,$reall_action,$reall_layout) = $this->render($result,$action);

        if( $isecho && $html )
        {
            //最终输出时候...//显示的时候用...//多次 _forword 时候
            $lastRet = self::$_list_ret[0];

            $actConf = $action->__getConfig();
            $type = $actConf[$lastRet]['type'];

            if( $type=='view' )
            {
                $template = null;
                if ( $reall_layout && array_key_exists('layout', $actConf[$lastRet])) {
                    $template = $actConf[$lastRet]['layout'];
                }

                $app = $app?:$action->_actionToken()->getApp();
                $view = J7View::instance([$action,J7Config::instance()->getAppDir($app).'/',J7Config::instance()->getAppDir($app).'/']);

                $html = $view->renderHtml($app,$html, $template);
            }
            elseif( $type=='redirect' )
            {
                /**
                 * @var RedirectResultType $processor
                 */
                $processor = J7ResultType::instance('redirect');
                return $processor->process($lastRet, self::$_list_action[0], $html,null,true);
            }
            elseif( $type=='json' )
            {
                $processor = J7ResultType::instance('json');
                return $processor->process($lastRet, self::$_list_action[0], $html,null,true);
            }
            elseif( $type=='jsonp' )
            {
                $processor = J7ResultType::instance('jsonp');
                return $processor->process($lastRet, self::$_list_action[0], $html,null,true);
            }


            ob_start();
            echo  $html;
        }
        return [$html,$result, $action];
    }

    protected function getActionToken($result, $action)
    {
        if( $action instanceof AOPProxyControllerModel )
            $action = $action->__getProxyObject();

        $action_config = $action->__getConfig();
        if( isset($action_config[$result]['resource']) )
            $resource = $action_config[$result]['resource'];

        return $action->$resource;
    }

    protected function render($result, $action)
    {

        $at = $this->getActionToken($result,$action);

        //获取action的结果
        $dispatchReturn =  J7Dispatcher::run($at);
        /**
         * @var Action $retAction
         */
        list($actionReturn,$retAction) = $dispatchReturn;
        $retType = J7Dispatcher::getActRetType($retAction);

        //内action不使用layout //
        //但是要把原来的layout取出来。。。

        $reall_config = $retAction->__getConfig();
        $reall_layout = isset($reall_config[$retType]['layout'])?$reall_config[$retType]['layout']:null;

        if( $retType=='action' ) {
            return $this->render($retType, $retAction);
        }

        $retAction->_setLayout(null,null,$retType);

        $ret = J7Dispatcher::ProcessReturn([$actionReturn,$retAction],null,false);
        list($html,$reall_ret,$reall_action) = $ret;

        array_push(self::$_list_ret,$reall_ret);
        array_push(self::$_list_action,$reall_action);

        return [$html,$reall_ret,$reall_action,$reall_layout];

//        $actConf = $action->__getConfig();
//        $retTypeHander = isset($actConf[$reall_ret])?$actConf[$reall_ret]['type']:'view';
//
//        $template = null;
//        if (array_key_exists('layout', $actConf[$reall_ret])) {
//            $template = $actConf[$reall_ret]['layout'];
//        }
//
//        var_dump($action);
//        die;
//
//        if( $retTypeHander=='view' )
//        {
//            //补上 layout
//            $view = J7View::instance([$action,J7Config::instance()->getAppDir($app).'/',J7Config::instance()->getAppDir($app).'/']);
//
//            $template = null;
//            if (array_key_exists('layout', $actConf[$reall_ret])) {
//                $template = $actConf[$reall_ret]['layout'];
//            }
//
//            var_dump($actConf);
//            var_dump($template);
//            die;
//
//            return $view->renderHtml($app,$html, $template);
//
//            $template = null;
//
////            $ret_type = $r_action->_getResultType()?:'default';
//
//            if (array_key_exists('layout', $action_config['default'])) {
//                $template = $action_config['default']['layout'];
//            }
//            return J7ActionTag::instance()->parse($view->renderHtml($app,$r_ret,$template));
//        }
//        else
//        {
//            return $html;
//        }

    }
}