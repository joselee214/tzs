<?php
//require_once(__DIR__ . "/../../../../core/coreRedisCache.php");

class develop_index_clearCache_Action extends develop_index_common
{
    public $cachekey;
    public $cacheuid;
    public $submittype;
    public $cachevalue;
    public $ctype='Memcache';

    public $allr;
    public $alld = array('J7L:apis|0');

    public function execute()
    {
        $uid = $this->_meuid;
        $cache = J7Cache::instance();
        //$cacheredis = RedisCache::instance(null,0,null,null);

        $this->allr = $cache->get('index|clearCache');
        if( $this->cachekey )
        {
            if( !$this->allr )
                $this->allr = [];
            $k=array_search($this->cachekey,$this->allr);
            if( $k!==false )
            {
                unset($this->allr[$k]);
            }
            array_unshift($this->allr,$this->cachekey);
            if(count($this->allr)>$this->limit)
            {
                array_slice($this->allr, 0, $this->limit);
            }
            $cache->set('index|clearCache',$this->allr);
        }

        if( $this->cachekey && $this->submittype=='GET' )
        {
            switch($this->ctype)
            {
//                case 'RedisString':
//                    $this->cachevalue = $cacheredis->get($this->cachekey);
//                    break;
//                case 'RedisList':
//                    $this->cachevalue = $cacheredis->lRange($this->cachekey,0,-1);
//                    break;
//                case 'RedisHash':
//                    $this->cachevalue = $cacheredis->hGetAll($this->cachekey);
//                    break;
//                case 'RedisSet':
//                    $this->cachevalue = null;
//                    break;
//                case 'RedisSortedSet':
//                    $this->cachevalue = null;
//                    break;
                default:
                    $this->cachevalue = $cache->get($this->cachekey);
                    break;
            }
        }
        elseif( $this->cachekey && $this->submittype=='DEL' )
        {
//            if( $this->ctype=='Memcache' )
                $this->cachevalue = $cache->del($this->cachekey);
//            else
//                $this->cachevalue = $cacheredis->del($this->cachekey);
        }
//		else if( $this->submittype=='clearSystem' )
//		{
//			switch($this->ctype)
//			{
//				case 'item':
//					$this->cachekey = 'SYSTEM_ITEMS_CACHE_3hzR_';
//					break;
//				case 'task':
//					$this->cachekey = 'SYSTEM_TASKS_CACHE_3hzR_';
//					break;
//			}
//			if($this->cachekey)
//			{
//				$langs = $this->BaseModelService->Getgame('setting');
//				$langs = array_values($langs['lang']);
//				foreach($langs as $lang)
//				{
//					$cache->del($this->cachekey . $lang);
//				}
//				$this->cachevalue = true;
//			}
//			$this->_config = array("jsonp"=>array("type"=>"jsonp","resource"=>"data",'callback'=>"callback"));
//            return $this->_setResultType('jsopnp');
//		}
    }
}