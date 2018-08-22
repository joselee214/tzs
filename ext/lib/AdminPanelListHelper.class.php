<?php
class AdminPanelListHelper
{
    /*
     *
        $this->ps = new J7Page($this->limit, $this->page);
        $this->_aplh = new AdminPanelListHelper($this->ps);


        list($_cond,$this->cond,$this->sort) = $this->_aplh->filterCond($this->cond,$this->sort);
        $this->ps = $this->HotelService->getHotels($this->ps,$_cond);

        $this->_aplh->set('cond',$this->cond)->set('sort',$this->sort)                //过滤传值
            ->set('hidecol',['hotel_cn_address'])            //隐藏字段
            ->set('colshowconfig',array('hotel_cn_name'=>array('name'=>'hotel_cn_name 酒店中文名','url'=>'xxzz/{pk}/?')))   // 修改thead头名称/字段链接
            ->set('url',array('edit'=>null,'show'=>null,'delete'=>null))
            ->set('sortcol',['id'])
    ;


     *
     *
     */



    /**
     * @var J7Page
     */
    public $ps;
    public $items;
    public $item;

    /*
     * array( 'id'=>array('name'=>'id','url'=>'xxx?xxx={pk}') )
     *        字段名             显示名        附着链接地址,最后可以带"?"即字段值
     */
    public $colshowconfig=[];

    /*
     * 隐藏不显示字段
     */
    public $hidecol = [];
    public $hidecol_search = [];
    public $showcol_search = []; //用于显示的字段
    /*
     * 可以用于排序字段
     */
    public $sortcol = [];

    /*
     * showcondarea 显示查询界面  condposturl 查询界面/列表页面提交action  pk主键名
     */
    public $extconfig=array('showcondarea'=>true,'condposturl'=>'?','pk'=>null);

    public $cond=[];
    public $sort=[];
    public $condhander = 'cond';
    public $sorthander = 'sort';
    public $pagehander = 'page';

    public $errorMsg = '';

    public $url = array('edit'=>'?param%5btp%5d=edit&amp;param%5bpk%5d=','delete'=>'?param%5btp%5d=del&amp;param%5bpk%5d=','show'=>'?param%5btp%5d=show&amp;param%5bpk%5d=');

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

    public $exportUrl;
    protected $exportview;
    public function __construct($ps=null)
    {
        $this->exportview = __DIR__.'/view/AdminPanelListHelper.php';
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
        if($this->items)
        {
            $item = array_keys( J7Data::rArray(current($this->items)) );
            foreach($item as $ep)
            {
                if($this->colshowconfig && isset($this->colshowconfig[$ep]) )
                    $this->item[$ep] = $this->colshowconfig[$ep];
                else
                    $this->item[$ep] = array('name'=>$ep);
            }
            if(!$this->extconfig['pk'])
            {
                $this->extconfig['pk'] = $item[0];
            }
        }
        else
        {
            $this->item = $this->colshowconfig;
        }
        $this->exportUrl = $this->preparePage();
        include $this->exportview;
    }

    protected function preparePage()
    {
        $pageaction = $this->extconfig['condposturl'];
        if( $this->cond )
            foreach($this->cond as $cond=>$vcond)
            {
                $pageaction .= '&amp;'.$this->condhander.'['.$cond.']='.$vcond;
            }
        if( $this->sort )
            foreach($this->sort as $col=>$descOrasc)
            {
                $pageaction .= '&amp;'.$this->sorthander.'['.$col.']='.$descOrasc;
            }
        $pageaction .= '&amp;'.$this->pagehander.'=<:PAGE:>';
        return $pageaction;
    }

    public function filterCond($cond=[],$sort=[])
    {
        $cond = $cond?array_filter($cond,function($v){if(!empty($v)){return true;}}):[];
        $_cond = $cond;
        foreach ($cond as $k=>$v)
        {
            if( substr($v,0,1)=='*' || substr($v,-1)=='*' )
            {
                unset($_cond[$k]);
                $_cond[$k.'  LIKE ?'] = (substr($v,0,1)=='*'?'%':'') . trim($v,'*') . (substr($v,-1)=='*'?'%':'') ;
            }
        }
        $sort = $sort?array_filter($sort,function($v){if($v=='desc'||$v=='asc'||$v=='_desc'||$v=='_asc'){return true;}}):[];
        foreach ($sort as $k=>$v)
        {
            if( substr($v,0,1)=='_' )
                $sort = [$k=>substr($v,1)]+$sort;
        }
        return array($_cond,$cond,$sort);
    }
}