<?php
/**
 * @var $this AdminPanelListHelper
 */
?>
<script type="text/javascript" src="<?php echo SITE_FILESDOMAIN.'/js/jq/jquery.ui.js';?>"></script>
<link rel="stylesheet" type="text/css" href="<?php echo SITE_FILESDOMAIN.'/js/jq/jquery-ui.css';?>">
<link rel="stylesheet" type="text/css" href="<?php echo SITE_FILESDOMAIN.'/res/css/common/jquery.ui.css';?>">
<script type="text/javascript" src="<?php echo SITE_FILESDOMAIN.'/js/jq/jquery-ui-timepicker-addon.js';?>"></script>
<div>
    <style type="text/css">
        #pagemain{width:745px;}
        .cellspacing0{margin:0;border-collapse:collapse;}
        .cellspacing0 td{padding:0;}
        .filtertable td{border-bottom: 1px solid #999;border-right: 1px solid #999;padding: 2px 5px;}
        .datatable{}
        .datatable td{border-bottom: 1px solid #999;border-right: 1px solid #999;padding: 5px 10px;}
        .a_operate,.a_operate a{color:#3d8901;}
        .title_tr{font-weight: bold}
        .trmouseover{background:#f0ffff;}
        .trmouseclick{background:#f0ffff;}
        a.button{color: #ffffff;background: #185C9F;padding: 5px 10px;display: inline-block;margin-right: 10px;}
        .global_form select.span3 {width: 222px;}

        a.button2{}
        table.searchbox td{width:250px;}
        table.searchbox td label{padding:3px;}
        table.searchbox td input{height:18px;line-height: 18px;vertical-align: middle;margin-left: 4px;}
        table.searchbox td input.text{width:140px;}
        table.searchbox td input.short{width:65px;}
        table.searchbox td select{width:140px;}
        table.searchbox td input.halfdate, table.searchbox td input.date{width:65px;}


        .global_table tr.bodysubtr {border-bottom: 3px solid #E9E9E9;}
        .global_table .subhtml{margin-left: 70px;border: 0;}
        .global_table .subhtml tr{border: 0;}
        .global_table .subhtml td{padding: 0 20px;}
        .subhtml .goodsList_pic{max-width: 30px;}
    </style>
    <div class="content-notice2">
        <?php foreach ($this->url as $url):?>
            <?php if ($url[0] == 'add' ):?>
                <a href="<?php echo $url[1];?>" class="global_btn global_btn_primary"><?php echo $url[2];?></a>
            <?php endif;?>
        <?php endforeach;?>
        <?php if (count($this->cond) < 0) : ?>
            <div class="search-wrap">
                <form method="GET" action="<?php echo $this->condurl;?>" class="global_form">
                    <ul class="search-filter<?php echo (count($this->cond) <1) ?' hide':'';?>" id="search-box">
                        <?php foreach ($this->condFields as $idx => $field):?>
                            <li>
                                <?php //debug($field,'$field cond');?>
                                <label for="cond_<?php echo $field[0];?>"><?php echo $field[1];?></label>
                                <?php if ('text' == $field[2]): // for plaint text ?>
                                    <input type="text" class="text span3" name="cond[<?php echo $field[0];?>]" id="cond_<?php echo $field[0];?>" value="<?php echo isset($this->cond[$field[0]]) ? $this->cond[$field[0]] : '';?>" />
                                <?php endif;?>
                                <?php if ('date' == $field[2]): // for datepicker ?>
                                    <input type="text" class="date halfdate span3" name="cond[<?php echo $field[0];?>_st]" id="cond_<?php echo $field[0];?>_st" value="<?php echo isset($this->cond[$field[0].'_st']) ? $this->cond[$field[0].'_st'] : '';?>" /> - <input type="text" class="date halfdate span3" name="cond[<?php echo $field[0];?>_end]" id="cond_<?php echo $field[0];?>_end" value="<?php echo isset($this->cond[$field[0].'_end']) ? $this->cond[$field[0].'_end'] : '';?>" />
                                <?php endif;?>
                                <?php if('date_bt' == $field[2]):?>
                                    <input type="text" class="date halfdate span3" name="cond[<?php echo $field[0];?>_st]" id="cond_<?php echo $field[0];?>_st" value="<?php echo isset($this->cond[$field[0].'_st']) ? $this->cond[$field[0].'_st'] : '';?>" /> - <input type="text" class="date halfdate span3" name="cond[<?php echo $field[0];?>_end]" id="cond_<?php echo $field[0];?>_end" value="<?php echo isset($this->cond[$field[0].'_end']) ? $this->cond[$field[0].'_end'] : '';?>" />
                                <?php endif;?>
                                <?php if ('select' == $field[2]): // for select ?>
                                    <select class="span3" name="cond[<?php echo $field[0];?>]" id="cond_<?php echo $field[0];?>">
                                        <option value="">请选择</option>
                                        <?php foreach ($field[3] as $option):?>
                                            <option value="<?php echo $option[$field[0]];?>"<?php if (isset($this->cond[$field[0]]) && $this->cond[$field[0]] == $option[$field[0]]):?> selected="selected"<?php endif;?>><?php echo $option['title'];?></option>
                                        <?php endforeach;?>
                                    </select>
                                <?php endif;?>
                            </li>
                        <?php endforeach;?>

                        <li style="margin-left: 75px;"><button type="submit" class="global_btn global_btn_primary">搜索</button></li>
                    </ul>
                    <div class="clear"></div>
                </form>
                <div class="center" style="margin: 10px; text-align: center;">
                    <a id="choose_filter" class="global_btn global_btn_brown global_btn_long">更多筛选条件</a>
                </div>
            </div>
        <?php endif; ?>
        <div class="content-data">
            <?php if(count($this->items) > 0): $tablecount = 0; ?>

                <div class="content-notice2">
                    <table class="global_table">
                        <thead>
                        <tr class="title_tr">
                            <?php foreach($this->showFields as $field):?>
                                <?php if (isset($field[3])&&$field[3]) : // 需要排序?>
                                    <th class="orderBy thc_<?php echo strtolower($field[2]);?> <?php echo isset($this->orderBy[$field[2]]) ? 'order_'. strtolower($this->orderBy[$field[2]]) :'';?>"><a href="<?php echo $this->condurl. http_build_query(
                                                array_merge( array('cond' =>$this->cond),
                                                    array('orderBy['.$field[2].']'=> isset($this->orderBy[$field[2]]) && strtolower($this->orderBy[$field[2]]) == 'desc'  ?'asc' :'desc')
                                                ) ,'', '&amp;');?>"><?php echo $field[1];?></a></th>
                                <?php else: // if (isset($field[2])) : 不变排序的?>
                                    <th class="orderBy thc_<?php echo strtolower($field[2]??$field[0]);?>"><?php echo $field[1];?></th>
                                <?php endif;?>
                            <?php endforeach ;?>
                            <?php if( $this->url ) { ?>
                            <th class="action">操作</th>
                            <?php } ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $allcolspans = count($this->showFields) + ($this->url?1:0);
                        foreach($this->items as $item) :
                            $tablecount++; ?>
                            <tr class="bodytr <?=($tablecount%2==1) ? 'tr2' : 'tr1' ?>">
                                <?php foreach($this->showFields as $field) :?>
                                    <td <?php if (isset($field[4])){ echo 'class="'.$field[4].'"';}?>>
                                        <?php if ('created' == $field[0] || 'updated' == $field[0]) :?>
                                            <?php echo isset($item[$field[0]]) ? date('Y-m-d H:i',$item[$field[0]]) : $field[0] ;?>
                                        <?php else :?>
                                            <?php echo isset($item[$field[0]]) ? $item[$field[0]]: $field[0] ;?>
                                        <?php endif;?>
                                    </td>
                                <?php endforeach;?>


                                <?php if( $this->url ) { ?>
                                <td class="a_operate">
                                    <?php
                                    $pk = $this->getpk($item);

                                    foreach ($this->url as $urlitem) {
                                        $url = $urlitem[1];
                                        $tp = isset($urlitem[2]) ? $urlitem[2] : $urlitem[0];
                                        if(!is_array($tp))
                                        {
                                            $tpname = $tp;
                                        } else {
                                            foreach($tp as $tpk => $tpv){
                                                $tpname = isset($item[$tpk]) && isset($tpv[$item[$tpk]])?$tpv[$item[$tpk]]:(isset($item[$tpk])?$item[$tpk]:'undefind');
                                            }
                                        }
										$target = isset($urlitem[3]) ? ' target="'.$urlitem[3].'"' : '';
										$urlend = isset($urlitem[4]) ? $urlitem[4] : '';
                                        switch (strtolower($urlitem[0])) {
                                            case 'sort':
                                            case 'add':
                                                break; // nothing yet
                                            case 'show':
                                            case 'edit':
                                                echo ' <a href="'.$url.$pk.$urlend.'"'.$target.'>'.$tpname.'</a> ';
                                                break;
                                            case 'delete':
                                                echo '<a onclick="return confirm(\'确认要删除 '.$pk.' ?\');" href="'.$url.$pk.$urlend.'"'.$target.'>'.$tpname.'</a> ';
                                                break;
                                            default:
                                                if (strpos( $url, '%d') !== false) {
                                                    $url = sprintf($url, $pk);
                                                }

                                                echo ' <a href="'.$url.$urlend.'"'.$target.'>'.$tpname.'</a> ';
                                                break;
                                        }
                                    }
                                    ?>
                                </td>
                                <?php } ?>
                            </tr>
                            <?php if( isset($item['_subhtml']) ) { ?>
                                <tr class="bodytr <?=($tablecount%2==1) ? 'tr2' : 'tr1' ?> bodysubtr">
                                    <td colspan="<?php echo $allcolspans;?>">
                                        <?php echo $item['_subhtml'];?>
                                    </td>
                                </tr>
                            <?php } ?>

                        <?php endforeach; //foreach($this->items as $item)?>
                        </tbody>
                    </table>
                </div>
                <div>
                    <?php echo $this->ps->showPages();?>
                </div>
            <?php else: // ?>

            <?php endif;?>
        </div>
    </div>
</div>
</div>
<script type="text/javascript">
    //<![CDATA[
    $(function(){
        $('.date').datepicker();
        $('.datatable tbody tr').hover(function(){ // mouseOver
            $(this).addClass('trmouseover');
        },function(){ // mouseOut
            $(this).removeClass('trmouseover');
        }).click(function(){
                $(this).toggleClass('trmouseclick');
            });
        //onmouseover="$(this).addClass('trmouseover');" onmouseout="$(this).removeClass('trmouseover');" onclick="if($(this).hasClass('trmouseclick')){$(this).removeClass('trmouseclick');}else{$(this).addClass('trmouseclick');};"
        $('#choose_filter').click(function(){
            $('#search-box').toggle();
            return false;
        })
    });
    //]]>
</script>