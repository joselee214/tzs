<?php
/**
* @var $this tzs_tzs_postdata_Action
* @var $_ tzs_tzs_postdata_Action
* @see tzs_tzs_postdata_Action
*/
?>


<?php
if( $this->from == 'wxxcx' )
{
    slot('show_foot_text',1);
//    slot('show_head_placeholder',1);
}
else
{
    include_view('tzs/headtabbar.php',['now'=>'me']);
}
?>

<style>
    .zan-panel {
        margin-top:0;
    }
    table {
        color: #333333;
        font-size: 12px;
    }
    table, th, td {
        border: 1px solid #333333;
    }
    td{
        padding: 2px 5px;
    }
    th{
        background: #dddddd;
    }
    .fn{
        background: #dedede;
    }
</style>

<?php if($this->post):?>

<div class="zan-panel">
    <div class="zan-cell zan-field">
        【<?php echo $this->post->_title();?>】 报名结果:
    </div>
    <?php if($this->error){ ?>
    <div class="zan-cell zan-field red">
        <?php echo $this->error;?>
    </div>
    <?php } else { ?>
        <div class="zan-cell zan-field red tc reset_cell">

            <?php
            if( $this->from == 'wxxcx' )
            {
                echo '请去WEB版本下载 excel 结果 网址: &nbsp;&nbsp;'.SITE_DOMAIN;
            }
            else
            {
                echo '<a href="/tzs/postdata/sign?pid='.$this->pid.'&act=down" class="red">下载 excel 文件</a>';
            }
            ?>
        </div>
    <?php } ?>
</div>

<div style="padding: 20px 10px;">
<?php if($this->data):?>
    <table>
        <thead>
            <th>用户名</th>
            <th>填表时间</th>
            <?php
            $columNums = count($this->post['sign_options']);
            foreach ($this->post['sign_options'] as $eso)
            {
                echo '<th>'.$eso['t'].'</th>';
            }
            ?>
        </thead>
        <?php
        foreach ($this->data as $d){
        ?>
        <tr>
            <td class="fn">
                <img class="smallavatar" src="<?php echo $d['avatar']?>">
                <?php echo $d['nickname']?>
            </td>
            <td class="fn">
                <?php echo $d['created_at']?>
            </td>
            <?php
            for ($i=0;$i<$columNums;$i++){
                echo '<td>'.($d['signs'][$i]??'').'</td>';
            }
            ?>
        </tr>
        <?php } ?>
    </table>
<?php endif;?>
</div>

<div class="zan-panel" style="margin: 10px;border: 1px dashed green;padding: 10px;font-size: 13px;">
    <div>
        1.第一项为"用户名"列,非设置的报名项
    </div>
    <div>
        2.第二项为"填表时间"列,非设置的报名项
    </div>
    <div>
        3."用户名"列为:用户在小程序里自定的名称,仅用作参考
    </div>
    <div>
        4.可用 excel 打开 csv 文件 , (表情文字会被忽略!)
    </div>
</div>

<?php endif;?>