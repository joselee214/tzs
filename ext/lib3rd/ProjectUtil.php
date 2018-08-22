<?php
/**
 * App扩展方法类, 常用的方法存放在这个类里
 */
class ProjectUtil
{
    static function showTips($tiphtml)
    {
        echo '<div style="width:100%;padding-top:10px;padding-bottom:10px;text-align:center;background:#F2F9FD"><h4 style="color:#090;font-size:14px;font-weight:700;">'.$tiphtml.'</h4></div>';
    }

    /**
     * 生成分页的html
     * @param $curPage
     * @param $pageCount
     * @param $pageOffset
     * @param $pageUrl
     * @return mixed
     */
    static function pagination($curPage, $pageCount, $pageOffset, $pageUrl)
    {
        $pageHml = '<div class="page">';
        if ($curPage != 1) {
            $pageHml .= '<a href='.$pageUrl.'&pageId='.($curPage - 1).'><<上一页</a>';
        }

        if ($curPage != 1){
            $pageHml .= '<a href='.$pageUrl.'&pageId=1>1</a>';
        }

        for ($i = $pageOffset; $i >=0; $i--) {
            if (($curPage - $i > 1) && ($i !=0)) {
                $pageHml .= '<a style="" href='.$pageUrl.'&pageId='.($curPage - $i).'>'.($curPage -$i).'</a>';
            }
            if ($i == 0) {
                $pageHml .= '<span class="selected">'.$curPage.'</span>';
            }
        }

        for ($i=1; $i <= $pageOffset; $i++) {
            if ($curPage + $i < $pageCount) {
                $pageHml .= '<a style="" href='.$pageUrl.'&pageId='.($curPage + $i).'>'.($curPage + $i).'</a>';
            }
        }
        if ($curPage != $pageCount) {
            $pageHml .= '<a href='.$pageUrl.'&pageId='.$pageCount.'>'.$pageCount.'</a>';
        }
//        $pageHml .= '<input type="text" title="输入页码数, 回车即可到达指定页码" onkeydown='if(event.keyCode == 13){console.log(\'sss\';)}' />';
        $pageHml .= "<input type='text' title='输入页码数, 回车即可到达指定页码' onkeydown='if(event.keyCode == 13){location=\"$pageUrl\"+\"&pageId=\"+this.value;return false;}'>";
//        echo '<script type="text/javascript">console.log("'.$pageUrl.'")</script>';
        if ($curPage != $pageCount) {
            $pageHml .= '<a href='.$pageUrl.'&pageId='.($curPage + 1).'>下一页>></a>';
        }
        $pageHml .="</div>";
        return $pageHml;
    }
}