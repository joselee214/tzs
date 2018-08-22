<?php
/**
 * @var $this admin_index_AdminUser_Action
 * @var $_ admin_index_AdminUser_Action
 * @see admin_index_AdminUser_Action
 */
?>
    <style>
        #mytable{
            /* 表格边框 %%%%%%%%%%%%非常好的边框设置%%%%%%%%%*/
            font-family: Arial;
            border-collapse: collapse; /* 边框重叠 */
            background-color: #eaf5ff; /* 表格背景色  %%%%%%%%%%%%%%%%% 非常好的背景颜色 %%%%%%%%%%%%% */
            font-size: 14px;
            table-layout: fixed;
            word-wrap: break-word;
            word-break: break-all;
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
<?php
if ($this->action == 'edit')
{
    ?>
    <div>
        <a href="<?php echo $this->rewrite_base;?>/index/AdminUser" class="button">返回列表</a>
    </div>
    <form action="<?php echo $this->rewrite_base;?>/index/AdminUser/?action=update" method="post">
        <div  class='content-notice'>
            <table>
                <tr>
                    <td>id</td>
                    <td>name</td>
                    <td>password</td>
                    <td>truename</td>
                    <td>隶属管理组</td>
                    <td></td>
                </tr>
                <tr>
                    <td><input type="hidden" name="input[uid]" value="<?php echo $this->user?$this->user['uid']:'';?>"><?php echo $this->user?$this->user['uid']:'';?></td>
                    <td>
                        <input type="text" name="input[name]" value="<?php echo $this->user?$this->user['name']:'';?>">
                    </td>
                    <td>
                        <input type="text" name="input[password]" value="">
                    </td>
                    <td>
                        <input type="text" name="input[truename]" value="<?php echo $this->user?$this->user['truename']:'';?>">
                    </td>
                    <td>
                        <?php
                        $sgid = isset($this->user['flag'])?$this->user['flag']['gidlist']:[];
                        foreach($this->grouplist as $g)
                        {
                            echo '<label><input type="checkbox" name="input[gids][]" value="'.$g['id'].'" '.(in_array($g['id'],$sgid)?'checked':'').'>'.$g['title'].'</label>';
                        }
                        ?>
                    </td>
                    <td>
                        <input type="submit" value="提交">
                    </td>
                </tr>
            </table>
        </div>
    </form>

    <?php if( $this->user ){ ?>
    <form action="<?php echo $this->rewrite_base;?>/index/AdminUser/?action=updateflag" method="post">
        <div class='content-notice2'>
            以下是独立权限
            <input type="hidden" name="input[uid]" value="<?php echo $this->user?$this->user['uid']:'';?>"><?php echo $this->user?$this->user['uid']:'';?>
        </div>
        <div class='content-notice'>
            <div>
                <input type="submit" value="提交">
                <span style="float: right;margin: 0 10px;" onclick="jQuery('input.cs').prop('checked', true);">全选</span>
                <span style="float: right;" onclick="jQuery('input.cs').prop('checked', false);">全否</span>
            </div>
            <?php
            function showmenu($d,$per=0,$gs,$ggs)
            {
                $margin = 15*$per;
                echo '<div id="f'.$d['id'].'" class="deep deep'.$per.'">';
                echo '<div style="margin-left:'.$margin.'px;" id="'.$d['id'].'">'.'~'.$d['id'];
                if( in_array($d['id'],$ggs) )
                    echo '<input type="checkbox" checked disabled>';
                else
                    echo '<input type="checkbox" class="cs" id="p_'.$d['id'].'" name="input[c_id][]" '.(in_array($d['id'],$gs)?'checked':'').' value="'.$d['id'].'">';
                echo '<label for="p_'.$d['id'].'" style="color:#'.($d['is_show']?'000':'aaa').';">'.$d['name'].' '.$d['url'].'</label></div>';
                if( isset($d['child']) )
                {
                    foreach($d['child'] as $e)
                    {
                        showmenu($e,$per+1,$gs,$ggs);
                    }
                }
                echo '</div>';
            }

            foreach($this->cateinfo as $em)
            {
                showmenu($em,0,$this->user['flags']['user'],$this->user['flags']['group']);
            }
            ?>
            <div style="clear: both;"></div>
        </div>
    </form>
<?php } ?>

    <?php
} else
{
    ?>
    <div>
        <?php
        $this->_aplh->export();
        ?>
    </div>
    <?php
}
?>