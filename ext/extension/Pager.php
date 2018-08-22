<?php
class Pager extends J7Page
{
    public function showPagesInJs($url_prefix = '', $url_postfix = '',$showJstype='PageList',$replacediv='',$PageListNum=3)
    {
        if( !$showJstype ){ $showJstype = 'PageList'; }
        $output = '<script language="javascript">'.$showJstype.'('.$this->getCurrentPage().','.$this->getCountOnEachPage().','.$this->getTotalCount().',\''.$url_prefix.'\',\''.$url_postfix.'\',\''.$replacediv.'\','.$PageListNum.');</script>';
        return $output;
    }


    public function showPages($url_prefix = '', $nameNode='page',$url_postfix = '',$PageListNum=3,$showtp=1,$pageDivclass='page_div')
    {
        $isshowlong = true;
        $pwsetsplit = RuntimeData::get('j7_probizinfo','localpageset');
        if( isset($pwsetsplit[0]) && $pwsetsplit[0]<750 )
        {
            $isshowlong = false;
            $PageListNum = 0;
        }

        $totalcount =  $this->getTotalCount();
        $this_page = intval($this->getCurrentPage());
        $per_page = $this->getCountOnEachPage();
        $this_page_end = $this->getTotalpage();
        $n=$this_page_end;
        if ($this_page > $PageListNum) {
            $m = $this_page - $PageListNum;
            if ($n - $this_page > $PageListNum) {
                $n = $this_page + $PageListNum;
            }
            else {
                if ($n > 2 * $PageListNum) {
                    $m = $n - 2 * $PageListNum;
                }
                else {
                    $m = 1;
                }
            }
        }
        else {
            $m = 1;
            if ($n > 2 * $PageListNum) {
                $n = $m + 2 * $PageListNum;
            }
        }
        if ($totalcount <= $per_page) {
        }

        $url_prefix = $this->getNewUrlPrefixInPage($url_prefix,$nameNode);
        $newpageout='';

        if( $showtp==1 )
        {
            if ($this_page<=1) { if($isshowlong){$newpageout='<a class="page_pp page_disable">前一页</a>';} }
            else { $newpageout='<a href="'.str_replace('<:PAGE:>',$this_page-1,$url_prefix).$url_postfix.'" title="上页" class="page_sp page_pp">上'.($isshowlong?'一':'').'页</a>' ;}
        }

       	if($m>1)
       		$newpageout .= '<a href="'.str_replace('<:PAGE:>',1,$url_prefix).$url_postfix.'" class="page_sp">1</a>';
       	if($m>2 && $isshowlong)
       		$newpageout .='<span class="page_dot">...</span>';

        if( $isshowlong )
        {
            for ($i=$m ; $i<$this_page ; $i++)
                $newpageout .= '<a href="'.str_replace('<:PAGE:>',$i,$url_prefix).$url_postfix.'" class="page_sp">'.$i.'</a>';
        }

        $newpageout .= '<a href="'.str_replace('<:PAGE:>',$this_page,$url_prefix).$url_postfix.'" class="page_sp page_thisp">'.$this_page.'</a>';

        if( $isshowlong )
        {
            for ($i=$this_page+1 ;$i<$n+1;$i++)
                 $newpageout .= '<a href="'.str_replace('<:PAGE:>',$i,$url_prefix).$url_postfix.'" class="page_sp">'.$i.'</a>';
        }

       	if($n+1<$this_page_end && $isshowlong)
              $newpageout .='<span class="page_dot">...</span>';
       	if($n<$this_page_end)
             $newpageout .= '<a href="'.str_replace('<:PAGE:>',$this_page_end,$url_prefix).$url_postfix.'" class="page_sp" title="最后页">'.$this_page_end.'</a>';


        if( $showtp==1 )
        {
        if ($totalcount>($per_page*$this_page))
            $newpageout.='<a href="'.str_replace('<:PAGE:>',$this_page+1,$url_prefix).$url_postfix.'" title="下页" class="page_sp page_np">下'.($isshowlong?'一':'').'页</a>' ;
       	else if( $isshowlong)
            $newpageout .='<a class="page_np page_disable">后一页</a>';
        }

        if( $this_page_end>2 )
        {
            //$newpageout .='<form onsubmit="return goToPage(this,\''.$url_prefix.'\',\''.$url_postfix.'\','.$this_page_end.');"><span class="page_jp">到第<input onclick="this.select();" name="page" type="text" value="'.$this_page.'" class="page_ip">页<input class="page_js" type="submit" value="确定"/></span></form>';
        }

        $newpageout = '<div class="'.$pageDivclass.'">'.$newpageout.'</div>';

        return $newpageout;
    }


    public function showPagesShort($url_prefix = '', $url_postfix = '')
    {
        $Total =  $this->getTotalCount();
        $output = '<div class="page_div page_div_short">';
        
        $endstart = $this->getCurrentPage()*$this->getCountOnEachPage();
        $thisstart = $endstart-$this->getCountOnEachPage()+1;
        if( strpos($url_prefix,'<:PAGE:>')===false )
        {
            $url_prefix = $url_prefix.'<:PAGE:>';
        }
        if ($this->getCurrentPage() >1)
        {
            $output .= '<a href="'.str_replace('<:PAGE:>',$this->getCurrentPage()-1,$url_prefix).$url_postfix.'" title="上页" class="page_pp">上一页</a>' ;
        }
        $output .= '<a class="page_np page_disable">第'.$this->getCurrentPage().'页('.$thisstart.'-'.$endstart.')</a>';
        if ($Total>$endstart)
       	{
           $output .= '<a href="'.str_replace('<:PAGE:>',$this->getCurrentPage()+1,$url_prefix).$url_postfix.'" class="page_np" title="下页">下一页</a>';
        }
        $output .= '</div>';
        return $output;
    }

    //前缀,翻页name,后缀
	public function showPagesFront($url_prefix = '', $nameNode='page',$url_postfix = '',$PageListNum=5,$showtp=1)
	{
		$isshowlong = true;
		$pwsetsplit = RuntimeData::get('j7_probizinfo','localpageset');
		if( isset($pwsetsplit[0]) && $pwsetsplit[0]<750 )
		{
			$isshowlong = false;
			$PageListNum = 0;
		}

		$totalcount =  $this->getTotalCount();
		$this_page = intval($this->getCurrentPage());
		$per_page = $this->getCountOnEachPage();
		$this_page_end = $this->getTotalpage();
		$n=$this_page_end;
		if ($this_page > $PageListNum) {
			$m = $this_page - $PageListNum;
			if ($n - $this_page > $PageListNum) {
				$n = $this_page + $PageListNum;
			}
			else {
				if ($n > 2 * $PageListNum) {
					$m = $n - 2 * $PageListNum;
				}
				else {
					$m = 1;
				}
			}
		}
		else {
			$m = 1;
			if ($n > 2 * $PageListNum) {
				$n = $m + 2 * $PageListNum;
			}
		}
		if ($totalcount <= $per_page) {
		}

        $url_prefix = $this->getNewUrlPrefixInPage($url_prefix,$nameNode);

        $newpageout='';

		if( $showtp==1 )
		{
			if ($this_page<=1) { if($isshowlong){$newpageout='<li><a class="page_pp page_disable">前一页</a></li>';} }
			else { $newpageout='<li><a href="'.str_replace('<:PAGE:>',$this_page-1,$url_prefix).$url_postfix.'" title="上页" class="page_sp page_pp">上'.($isshowlong?'一':'').'页</a></li>' ;}
		}

		if($m>1)
			$newpageout .= '<li><a href="'.str_replace('<:PAGE:>',1,$url_prefix).$url_postfix.'" class="page_sp">1</a></li>';
		if($m>2 && $isshowlong)
			$newpageout .='<span class="dot">...</span>';

		if( $isshowlong )
		{
			for ($i=$m ; $i<$this_page ; $i++)
				$newpageout .= '<li><a href="'.str_replace('<:PAGE:>',$i,$url_prefix).$url_postfix.'" class="page_sp">'.$i.'</a></li>';
		}

		$newpageout .= '<li class="active"><a href="'.str_replace('<:PAGE:>',$this_page,$url_prefix).$url_postfix.'">'.$this_page.'</a></li>';

		if( $isshowlong )
		{
			for ($i=$this_page+1 ;$i<$n+1;$i++)
				$newpageout .= '<li><a href="'.str_replace('<:PAGE:>',$i,$url_prefix).$url_postfix.'" class="page_sp">'.$i.'</a></li>';
		}

		if($n+1<$this_page_end && $isshowlong)
			$newpageout .='<span class="page_dot">...</span>';
		if($n<$this_page_end)
			$newpageout .= '<li><a href="'.str_replace('<:PAGE:>',$this_page_end,$url_prefix).$url_postfix.'" class="page_sp" title="最后页">'.$this_page_end.'</a></li>';


		if( $showtp==1 )
		{
			if ($totalcount>($per_page*$this_page))
				$newpageout.='<li><a href="'.str_replace('<:PAGE:>',$this_page+1,$url_prefix).$url_postfix.'" title="下页" class="page_sp page_np">下'.($isshowlong?'一':'').'页</a></li>' ;
			else if( $isshowlong)
				$newpageout .='<li><a class="page_np page_disable">后一页</a></li>';
		}

		if( $this_page_end>2 )
		{
			//$newpageout .='<form onsubmit="return goToPage(this,\''.$url_prefix.'\',\''.$url_postfix.'\','.$this_page_end.');"><span class="page_jp">到第<input onclick="this.select();" name="page" type="text" value="'.$this_page.'" class="page_ip">页<input class="page_js" type="submit" value="确定"/></span></form>';
		}

		$newpageout = '<div class="global_pagination">'.$newpageout.'</div>';

		return $newpageout;
	}


    public function getNewUrlPrefixInPage($url_prefix = '', $nameNode='page')
    {
        if( strpos($url_prefix,'<:PAGE:>')===false )
        {
            $url_prefix = Util::updateUrlRequestParameters($nameNode,'<:PAGE:>',$url_prefix);
        }
        return $url_prefix;
    }
}
