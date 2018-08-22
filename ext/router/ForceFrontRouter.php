<?php

require_once __DIR__.'/../../core/J7Router.php';

class ForceFrontRouter extends J7Router
{
    protected function getActionToken($module,$controller,$params,$method)
    {
        return new ActionToken($module,$controller,$params,$method,'front',['fromhost'=>$_SERVER['HTTP_HOST']]);
    }
}