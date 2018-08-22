<?php
class J7Page implements JsonSerializable
{
	// attributes
	private $items;
    private $totalCount;
    private $startIndex;
    private $countOnEachPage;
    private $currentPage = 1;
    private $totalpage = 0;

    static public function reuse($countOnEachPage=10,$thispage=1,$class='J7Page')
    {
        $class = $class?:'J7Page';
        return FactoryObject::Instance($class,[$countOnEachPage,$thispage]);
    }

	// method
    public function __construct($countOnEachPage,$thispage)
    {
        $this->setCurrentPage($thispage);
        if( $thispage<1 ){$thispage=1;}
        $start = $countOnEachPage * (intval($thispage) - 1);
        $this->PageSupport($countOnEachPage,$start);
    }

    public function toArray($resetKeyWith=null)
    {
        $ret = [];
        $ret['limit'] = $this->getCountOnEachPage();
        $ret['page'] = $this->getCurrentPage();
        $ret['currentPage'] = $this->getCurrentPage();
        $ret['totalCount'] = $this->getTotalCount();
        $ret['totalPage'] = $this->getTotalpage();
        $ret['items'] = $this->getItems(true,$resetKeyWith);
        return $ret;
    }

	public function PageSupport($countOnEachPage = 10, $startIndex = 0, $items = [], $totalCount = 0)
	{
		//
		$this->countOnEachPage = $countOnEachPage;
		$this->items = $items;
		$this->startIndex = $startIndex;
		$this->totalCount = $totalCount;
	}

	//
    public function setStartIndex($startIndex) {
        $this->startIndex = $startIndex;
		return $this;
    }

    public function setCountOnEachPage($countOnEachPage) {
        $this->countOnEachPage = $countOnEachPage;
		return $this;
    }

    public function getItems($asArray=false,$resetKeyWith=null) {
        if( $asArray && $this->items )
        {
            $data = [];
            foreach ($this->items as $k=>$v)
            {
                $key = $k;
                $value = $v;
                if( $v instanceof J7Data)
                    $value = $v->toArray(true);
                if( $resetKeyWith && isset($value[$resetKeyWith]) )
                    $key = $value[$resetKeyWith];
                $data[$key] = $value;
            }
            return $data;
        }
        return $this->items;
    }

    public function getTotalCount() {
        return $this->totalCount;
    }

    public function getEndIndex() {
        $endIndex = $this->getStartIndex() + $this->countOnEachPage;
        if ($endIndex > $this->totalCount)
            return $this->totalCount;
        else
            return $endIndex;
    }

    public function getStartIndex() {
        //if ($this->startIndex > $this->totalCount)
        //    return $this->totalCount;
        if ($this->startIndex < 0)
            return 0;
        else
            return $this->startIndex;
    }

    public function getNextIndex() {
        $tmp = $this->getNextStartIndexes();
		return $tmp[0];
    }

    public function getPreviousIndex() {
        $previousIndexes[] = $this->getPreviousStartIndexes();
        return $previousIndexes[count($previousIndexes) - 1];
    }

    public function getNextStartIndexes() {

		$result = [];

        $index = $this->getEndIndex();
        if ($index == $this->totalCount)
            return null;
        $count = ($this->totalCount - $index) / $this->countOnEachPage;
        if (($this->totalCount - $index) % $this->countOnEachPage > 0)
            $count++;
        for ($i = 0; $i < $count; $i++) {
            $result[$i] = $index;
            $index += $this->countOnEachPage;
        }

        return $result;
    }

    public function getPreviousStartIndexes() {
		$result = [];

		$index = $this->getStartIndex();
        if ($index == 0)
            return null;
        $count = $index / $this->countOnEachPage;
        if ($index % $this->countOnEachPage > 0)
            $count++;
        for ($i = 0; $i < $count; $i++) {
            $index -= $this->countOnEachPage;
            $result[$i] = $index;
        }

        return $result;
    }
    
    public function getCountOnEachPage() {
        return $this->countOnEachPage;
    }

    public function setItems($items) {
        $this->items = $items;
 		return $this;
   }

    public function setTotalCount($totalCount) {
        $this->totalCount = $totalCount;
        if( $this->currentPage==='latest' || $this->getTotalpage()<$this->currentPage )
        {
            $this->setCurrentPage($this->getTotalpage());
        }
		return $this;
     }

	public function getCurrentPage() {
        if( $this->currentPage<1){ return 1;}
		return $this->currentPage;
	}

	public function setCurrentPage($currentPage) {
        if( $currentPage<1){$currentPage=1;}
        $this->currentPage = intval($currentPage);
        $this->startIndex = ($currentPage-1)*$this->countOnEachPage;
		return $this;
	}

	public function getTotalpage() {
		$this->totalpage = J7Page::_result($this->totalCount, $this->countOnEachPage);
		return $this->totalpage;
	}

	public function setTotalpage($totalpage) {
		$this->totalpage = $totalpage;
		return $this;
	}
	
    private static function _result($total, $size) {
        if( !$size )
            return 0;
        if ($total % $size == 0) {
            return $total / $size;
        } else {
            return intval(ceil($total / $size));
        }
    }

    /*
     * ****************************
     * JsonSerializable  json_encode 序列化接口
     */
    /**
     * @return array
     */
    function jsonSerialize()
    {
        return $this->toArray();
    }
}
