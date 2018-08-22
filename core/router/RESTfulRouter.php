<?php

require_once __DIR__ . '/../../core/J7Router.php';

class RESTfulRouter extends J7Router
{
    protected $_default_method = '';

    public function route()
    {
        $tk = parent::route();

        $action = $tk->getAction();
        $method = $tk->getMethod();

        $restful_method = strtolower($_SERVER['REQUEST_METHOD']);

        if( !$method )
        {
            if( method_exists($action,$restful_method) )
            {
                $tk->setMethod($restful_method);
            }
            else
            {
                $tk->setMethod('execute');
            }
        }
        return $tk;
    }
}