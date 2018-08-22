<?php
require_once __DIR__.'/resulttype/ViewResultType.php';
class J7ActionTag
{
    /**
     * @return J7ActionTag
     */
    public static function instance($instancename = 'J7ActionTag')
    {
        $instancename = $instancename?:'J7ActionTag';
        return FactoryObject::Instance($instancename);
    }


    public function parse($html)
    {
        $pattern = "/<J7Action:([a-zA-Z0-9\_\~]+)[ ]{0,}(.*?)\/>/si";
        preg_match_all($pattern, $html, $matchs, 2);
        if (is_array($matchs) && $matchs)
        {
            foreach($matchs as $match)
            {
                $html = substr_replace($html , $this->getTag($match[1], self::parseArgs($match[2])), strpos($html, $match[0]), strlen($match[0]) );
            }
        }
        return $html;
    }

    /*
     * $withAct 即是否是action内部调用,内部调用的话是返回结构体,调用的action处 return 'action'
     */
    public function getTag($actionName, $params = [], $paramsProtected=[],$defaultMethod='tagexecute')
    {
        try
        {
            list($app,$module,$controller,$method) = ActionToken::explodeActionName($actionName,$defaultMethod);
            $actionToken = new ActionToken($module,$controller,$params,$method,$app,$paramsProtected);
            /**
             * @var Action $action
             */
            $action = $actionToken->getAction();

            $method = $actionToken->getMethod();
            if( $method=='tagexecute' && !$action->__methodExists('tagexecute') )
            {
                $actionToken->setMethod('execute');
            }

            //获取action的结果
            $dispatchReturn =  J7Dispatcher::run($actionToken);

            /**
             * @var Action $retAction
             */
            list($actionReturn,$retAction) = $dispatchReturn;
            $retType = J7Dispatcher::getActRetType($retAction);
            $retAction->_setLayout(null,null,$retType); // 内action不使用layout //

            $ret = J7Dispatcher::ProcessReturn([$actionReturn,$retAction],null,false);
            list($html,$reall_ret,$reall_action) = $ret;

            return $html;
        }
        catch( Exception $e )
        {
            throw new J7Exception('J7ActionTag->getTag error :'.$actionName.' : ' .$e->getMessage() . ' :::::: '.$e->getTraceAsString());
        }
    }

    public static function parseArgs($arg)
    {
        $ret = [];
        //
        $pattern = "/([a-zA-Z_]+)=([^ ]+)/si";
        preg_match_all($pattern, $arg, $matchs, 2);
        if (is_array($matchs))
        {
            //
            foreach($matchs as $match)
            {
                $ret[$match[1]] = self::removeQuote($match[2]);
            }
        }
        return $ret;
    }

    private static function removeQuote($value)
    {
        if (substr($value, 0,1) == "\"" || substr($value, 0,1) == "\'")
        {
            $value = substr($value, 1);
        }
        if (substr($value, -1,1) == "\"" || substr($value, -1,1) == "\'")
        {
            $value = substr($value, 0, strlen($value)-1);
        }

        return $value;
    }

}


