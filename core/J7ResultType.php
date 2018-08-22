<?php
abstract class J7ResultType implements ResultType
{
    public static function instance($resulttype)
    {
        $resulttype = str_replace('ResultType','',$resulttype);
        $class = strtoupper(substr($resulttype, 0, 1)).substr($resulttype, 1, strlen($resulttype)-1).'ResultType';

        return FactoryObject::Instance($class);
    }
}

interface ResultType
{
    public function process($result, $action , $retData=null,$app=null , $isecho= true);
}