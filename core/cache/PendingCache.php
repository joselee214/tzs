<?php
class PendingCache extends J7Cache
{
    function get($key,$perstring='')
    {
        $ak = RuntimeData::get('PendingMemCache');
        if( is_array($key) )
        {
            $data = [];
            foreach($key as $eachkey)
            {
                if( !is_null($ak) && is_array($ak) && isset($ak[$perstring.$eachkey]) )
                    $data[$eachkey] = $ak[$perstring.$eachkey]['value'];
                else
                    $data[$eachkey] = parent::get($eachkey,$perstring);
            }
        }
        else
        {
            if( !is_null($ak) && is_array($ak) && array_key_exists($perstring.$key,$ak) )
            {
                $data = $ak[$perstring.$key]['value'];
            }
            else
            {
                $data = parent::get($key,$perstring);
            }
        }
        return $data;
    }
    function set($key,$value,$limit=0)
    {
        $cachespot = array('servergroup'=>$this->memcacheserver,'limit'=>$limit,'value'=>$value);
        RuntimeData::set('PendingMemCache',$key,$cachespot);
        return true;
    }
    function del($key)
    {
        RuntimeData::del('PendingMemCache',$key);
        return parent::del($key);
    }
    function getConn($sid=0)
    {

    }
}