<?php
require_once __DIR__ . '/coreMemCache.php';
class CollectMemCache extends coreMemCache
{
    function get($key,$perstring='')
    {
        $ak = RuntimeData::get('CollecMemCache');
        if( is_array($key) )
        {
            $data = [];
            foreach($key as $eachkey)
            {
                $keywithsid = $this->memcacheserver.'_'.$eachkey;
                if( !is_null($ak) && is_array($ak) && isset($ak[$perstring.$keywithsid]) )
                    $data[$eachkey] = $ak[$perstring.$keywithsid]['value'];
                else
                {
                    $data[$eachkey] = parent::get($eachkey,$perstring);
                    $cachespot = array('servergroup'=>$this->memcacheserver,'value'=>$data[$eachkey],'key'=>$key);
                    RuntimeData::set('CollecMemCache',$keywithsid,$cachespot);
                }
            }
        }
        else
        {
            $keywithsid = $this->memcacheserver.'_'.$key;
            if( !is_null($ak) && is_array($ak) && array_key_exists($perstring.$keywithsid,$ak) )
            {
                $data = $ak[$perstring.$keywithsid]['value'];
            }
            else
            {
                $data = parent::get($key,$perstring);

                $cachespot = array('servergroup'=>$this->memcacheserver,'value'=>$data,'key'=>$key);
                RuntimeData::set('CollecMemCache',$keywithsid,$cachespot);
            }
        }
        return $data;
    }

    function set($key,$value,$limit=null)
    {
        $keywithsid = $this->memcacheserver.'_'.$key;
        $cachespot = array('servergroup'=>$this->memcacheserver,'limit'=>$limit,'value'=>$value,'key'=>$key);
        RuntimeData::set('CollecMemCache',$keywithsid,$cachespot);
        return parent::set($key,$value,$limit);
    }
    function del($key)
    {
        $keywithsid = $this->memcacheserver.'_'.$key;
        RuntimeData::del('CollecMemCache',$keywithsid);
        return parent::del($key);
    }
}