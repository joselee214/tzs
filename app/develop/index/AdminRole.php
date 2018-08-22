<style type="text/css">
    table {
        border: 1px solid #555555;
    }

    table td {
        border-bottom: 1px solid #cccccc;
        border-right: 1px solid #bbbbbb;
    }

    .deep{
        padding: 3px;
    }
    .deep0
    {
        float: left;
        margin: 10px;
        border: 1px solid #0058a3;
    }
</style>

<div class="content-notice">
    <?php if(!$this->item){ ?>
    <a href="#" onclick="javascript:addExtent()" class="button">添加新目录</a>
    <?php } ?>

    <div id="addtree" style="display: <?php echo $this->item?'':'none';?>;">
        <form action="<?php echo $this->rewrite_base;?>/index/AdminRole/~addtree" method="post">
            <table>
                <tr>
                    <td>id</td><td>名称</td><td>权限路径(:app/module/action 全部以:app开头)</td><td>外部连接</td><td>是否显示</td><td>父ID</td><td>排序</td><td>MenuId</td>
                </tr>
                <tr>
                    <td><input type="text" name="input[id]" size="1" value="<?php echo $_->item?$_->item['id']:''?>"></td>
                    <td><input type="text" name="input[name]" value="<?php echo $_->item?$_->item['name']:''?>"></td>
                    <td>
                        <input type="text" name="input[url]" size="40" value="<?php echo $_->item?$_->item['url']:''?>">
                    </td>
                    <td>
                        <input type="text" name="input[link]" size="70" value="<?php echo $_->item?$_->item['link']:''?>">
                    </td>
                    <td>
                        <select name="input[is_show]">
                            <option value="1">是</option>
                            <option value="0" <?php echo ($_->item&&($_->item['is_show'])==0)?'selected':''?>>否</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" name="input[parent_id]" value="<?php echo $_->item?$_->item['parent_id']:''?>" size="3">
                    </td>
                    <td>
                        <input type="text" name="input[sort]" value="<?php echo $_->item?$_->item['sort']:''?>" size="3">
                    </td>
                    <td>
                        <input type="text" name="input[menu_id]" value="<?php echo $_->item?$_->item['menu_id']:'1'?>" size="3">
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="7">
                        <input type="submit" value="Submit">
                        <br>
                        权限验证以系统执行Action为限制 :app/module/action~method , 比如 :app 表示所有该app下均有权限
                    </td>
                </tr>
            </table>
        </form>
    </div>

</div>

<div class="content-notice">
    <div>
    <?php
    function showmenu($d,$per=0,&$count,$adminrootdir='')
    {
        $count++;
        $margin = 15*$per;
        echo '<div id="f'.$d['id'].'" class="deep deep'.$per.'">';
        echo '<div style="margin-left:'.$margin.'px;" id="'.$d['id'].'">'.'id:'.$d['id'].'  ';
        echo '<label for="p_'.$d['id'].'" style="color:#'.($d['is_show']?'000':'aaa').';">'.$d['name'].' <span style="color:#bbb;">sort:'.$d['sort'].'</span></label>';
        echo '<a href="'.$adminrootdir.'/index/AdminRole/~editcate?id='.$d['id'].'"><img src="/developend/images/icon_edit.gif"></a><a href="'.$adminrootdir.'/index/AdminRole/~delcate?id='.$d['id'].'" onclick="return confirm(\'are you sure ?\')"><img src="/developend/images/no.gif"></a>';
        echo $d['url']?'<br/><span style="color:#555;"> &nbsp;&nbsp;&nbsp;&nbsp;'.$d['url'].'</span>':'';
        echo '</div>';
        if( isset($d['child']) )
        {
            foreach($d['child'] as $e)
            {
                showmenu($e,$per+1,$count,$adminrootdir);
            }
        }
        echo '</div>';
    }

    $count = 0;
    foreach($this->tree as $em)
    {
        showmenu($em,0,$count,$this->rewrite_base);
    }
    ?>
    <div style="clear: both;"></div>
    </div>

</div>


<script type="text/javascript">
    function addExtent(){
        $('#addtree').show();
    }
</script>
