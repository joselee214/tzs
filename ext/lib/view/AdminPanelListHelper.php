<?php
/**
 * @var $this AdminPanelListHelper
 */
?>

<div>

    <style type="text/css">
        .cellspacing0{margin:0;border-collapse:collapse;}
        .cellspacing0 td{padding:0;}
        .filtertable td{border-bottom: 1px solid #999;border-right: 1px solid #999;padding: 2px 5px;}
        .datatable{}
        .datatable td{border-bottom: 1px solid #999;border-right: 1px solid #999;padding: 5px 10px;}
        .a_operate,.a_operate a{color:#0066cc;}
        .title_tr{font-weight: bold;color: #0066cc;}
        .trmouseover{background:#E8EEF7;}
        .trmouseclick{background:#ffffff;}
        a.btn{cursor: pointer;color: #ffffff;background: #765834;padding: 5px 10px;display: inline-block;margin-right: 10px;border-radius: 6px;}

        .btn{cursor: pointer;background-color: #765834;color: #fff;border-radius: 6px;border: 0;}
        .search_form_div{position: relative;display: none;-webkit-box-shadow: 5px 5px 7px #999;border: 1px solid #999;z-index: 999;}
        .menu_cate{display: inline-block;border:6px solid transparent;
            border-top: 6px solid #fff;line-height: 0;font-size:0 ;vertical-align: middle;}
    </style>
    <div class="content-notice2" style="display: inline-block;">


        <?php
        $pageaction_show = str_replace('<:PAGE:>',$this->ps->getCurrentPage(),$this->exportUrl);
        $page = '<div style="display:inline-block;">'.$this->ps->showPages($this->exportUrl).'</div><div style="display:inline-block;">Total:'.$this->ps->getTotalCount().' /Page:'.$this->ps->getCountOnEachPage().'</div>';
        if(isset($this->url['add']) && $this->url['add'])
        {
            $page = '<a href="'.$this->url['add'].'" class="btn">Add</a>'.$page;
        }
        ?>


        <?php
        if( $this->extconfig['showcondarea'] && $this->extconfig['condposturl'] )
        {
            $rand = rand(0,99999).rand(0,99999).rand(0,99999);
            ?>
            <div class="content-notice" style="position: relative;">
                <div>
                    <button onclick="$('#filterpanel<?php echo $rand;?>').toggle()" class="btn">展开搜索<span class="menu_cate"></span></button>
                    Filter_Condition: <?php echo ($pageaction_show);?>
                </div>
                <div id="filterpanel<?php echo $rand;?>" class="content-notice search_form_div">
                    <form method="GET" action="<?php echo $this->extconfig['condposturl'];?>">
                        <table class="filtertable cellspacing0">
                            <?php
                            if( empty($this->showcol_search) )
                            {
                                foreach($this->item as $col=>$vcol)
                                {
                                    if( !in_array($col,$this->hidecol) && !in_array($col,$this->hidecol_search) )
                                    {
                                        echo '<tr>';
                                        echo '<td>' . $vcol['name'] . '</td>';
                                        echo '<td><input type="text" name="' . $this->condhander . '[' . $col . ']" value="' . (isset($this->cond[$col]) ? $this->cond[$col] : '') . '"></td>';
                                        echo '</tr>';
                                    }
                                }
                            }
                            else
                            {
                                foreach($this->showcol_search as $col=>$vcol)
                                {
                                    echo '<tr>';
                                    $name = (is_array($vcol)&&isset($vcol['name']))?$vcol['name']:(is_scalar($vcol)?$vcol:$col);
                                    $field = (is_array($vcol)&&isset($vcol['field']))?$vcol['field']:(is_scalar($vcol)?$vcol:$col);
                                    $field = is_numeric($col)?$field:$col;
                                    echo '<td>' . $name . '</td>';
                                    echo '<td><input type="text" name="' . $this->condhander . '[' . $field . ']" value="' . (isset($this->cond[$field]) ? $this->cond[$field] : '') . '"></td>';
                                    echo '</tr>';
                                }
                            }

                            ?>
                            <tr>
                                <td></td><td>
                                    <input type="SUBMIT">
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>
            <?php
        }
        ?>

        <div class="content-notice">
            <?php
            if($this->item)
            {
                ?>
                <div>
                    <?php
                    echo $page;
                    ?>
                </div>
                <div class="content-notice2">
                    <table cellspacing=0 class="datatable commentBox">
                        <tr class="title">
                            <td>
                            </td>
                            <?php
                            foreach($this->item as $k=>$v)
                            {
                                if( !in_array($k,$this->hidecol) )
                                {
                                    echo '<td>';
                                    echo $v['name'];
                                    if( in_array($k,$this->sortcol) )
                                    {
                                        if( isset($this->sort[$k]) )
                                            echo '<a href="'.$this->exportUrl.'&'.$this->sorthander.'['.$k.']=">▂</a>';
                                        if( !isset($this->sort[$k]) || $this->sort[$k]=='desc' )
                                            echo '<a href="'.$this->exportUrl.'&'.$this->sorthander.'['.$k.']=_asc">▲</a>';
                                        if( !isset($this->sort[$k]) || $this->sort[$k]=='asc' )
                                            echo '<a href="'.$this->exportUrl.'&'.$this->sorthander.'['.$k.']=_desc">▼</a>';
                                    }
                                    echo '</td>';
                                }
                            }
                            ?>
                        </tr>
                        <?php
                        foreach($this->items as $item)
                        {
                            ?>
                            <tr onmouseover="$(this).addClass('trmouseover');" onmouseout="$(this).removeClass('trmouseover');" onclick="if($(this).hasClass('trmouseclick')){$(this).removeClass('trmouseclick');}else{$(this).addClass('trmouseclick');};">
                                <td class="a_operate">
                                    <?php
                                    $pk = $this->getpk($item);
                                    foreach ($this->url as $tp => $url) {
                                        if( is_null($url) )
                                            continue;

                                        if (strpos( $url, '%d') !== false) {
                                            $url = sprintf($url, $pk);
                                        } else {
                                            $url .= $pk;
                                        }
                                        switch (strtolower($tp)) {
                                            case 'add':
                                                break;
                                            case 'show':
                                            case 'edit':
                                                echo ' <a href="'.$url.'">'.$tp.'</a> ';
                                                break;
                                            case 'delete':
                                                echo '<a onclick="return confirm(\'确认要删除 '.$pk.' ?\');" href="'.$url.'">Delete</a> ';
                                                break;
                                            default:
                                                echo ' <a href="'.$url.'">'.$tp.'</a> ';
                                                break;
                                        }
                                    }
                                    ?>
                                </td>
                                <?php
                                foreach($this->item as $k=>$v)
                                {
                                    if( !in_array($k,$this->hidecol) )
                                    {
                                        if( isset($item[$k]) )
                                        {
                                            echo '<td class="col_'.$k.'">';
                                            if( isset($this->colshowconfig[$k]) && isset($this->colshowconfig[$k]['url']) )
                                            {
                                                $link = $this->colshowconfig[$k]['url'];
                                                if( substr($link,-1)=='?' )
                                                    $link = substr($link,0,-1).$item[$k];
                                                $link = str_replace('{pk}',$pk,$link);
                                                echo '<a href="'.$link.'"><img src="/developend/images/icon_edit.gif"></a> ';
                                            }
                                            if( is_scalar($item[$k]) )
                                            {
                                                echo $item[$k];
                                            }
                                            else
                                                echo var_export($item[$k],true);
                                            echo '</td>';
                                        }
                                        else
                                            echo '<td></td>';
                                    }
                                }
                                ?>
                            </tr>
                            <?php
                        }
                        ?>
                    </table>
                </div>
                <div>
                    <?php
                    echo $page;
                    ?>
                </div>
                <?php
            }
            else
            {
                echo $this->errorMsg;
                if( isset($this->url['add']) && $this->url['add'] )
                    echo '<a href="'.$this->url['add'].'" class="btn">Add</a>';
            }
            ?>
        </div>
    </div>
</div>