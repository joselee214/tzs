<?php

class ListPageHelper
{
    /**
     * @var J7Page
     */
    public $ps;
    public $items;
    public $item;

    /*
     * array( 'id'=>array('name'=>'id','url'=>'xxx?xxx={$id}') )
     */
    public $cols=array(
        // 'key' => 'keyName' ,
    );

    public $condurl = '?';

    public $extconfig = [];

    public $cond = [];
    public $condFields = array(
//        array('key' ,'type' ,$extras) ,
//         type : 'text' ,'date' , between , select ($options required)
        // for select item 4 in array must be array( array ('key' => keyId ,'title' => optionTitle  )
    );

    public $showFields = array(
//        array( 'keyname' ,'title','sort' ),
    );

    public $orderBy = [];
    public $condhander = 'cond';
    public $pagehander = 'page';

    public $url = array(
        // array('tag' ,url_pattern=%d , title )
//        'add'=>'?param%5btp%5d=add',
//        'edit'=>'?param%5btp%5d=edit&amp;param%5bpk%5d=',
//        'delete'=>'?param%5btp%5d=del&amp;param%5bpk%5d=',
//        'sort'=>'?param[tp]=sort&param[pk]=',
//        'show'=>'?param%5btp%5d=show&amp;param%5bpk%5d='
    );

    public function set($h,$v)
    {
        if( isset($this->$h) )
        {
            if( $this->$h && is_array($this->$h) )
            {
                $this->$h = array_merge( $this->$h , $v );
            }
            else
                $this->$h = $v;
        }
        return $this;
    }

    function uset($h ,$v) {

    }

    protected $exportview;
    public function __construct($ps=null)
    {
        $this->exportview = __DIR__.'/view/ListPageHelper.php';
        if( !is_null($ps) )
            $this->ps = $ps;
    }

    public function getpk($item)
    {
        $pk = $this->extconfig['pk'];
        if( is_scalar($pk) )
            return isset($item[$pk])?$item[$pk]:'';
        else
        {
            return implode(array_intersect_key($item,array_fill_keys($pk,1)),'_');
        }
    }

    public function export()
    {
        $this->items = $this->ps->getItems();
        //debug($this->condFields,'$this->condFields');
        include $this->exportview;
    }
}