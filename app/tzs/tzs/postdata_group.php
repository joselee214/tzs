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
</style>

<?php if($this->post):?>

<div class="zan-panel">
    <div class="zan-cell zan-field">
        群ID：<?php echo $this->gid;?> <br/>
        【<?php echo $this->post->_title();?>】阅读比对:
    </div>
</div>

<div class="zan-panel" style="margin: 10px;border: 1px dashed green;padding: 10px;font-size: 13px;">
    <div>重要提示:</div>
    <div>1.由于没有微信群数据接口,故数据仅通过分析得出</div>
    <div>2.群总的用户数据 来源于累积的查看过小程序的用户</div>
    <div>3.用户必须通过群内分享的小程序进入才能被统计到</div>
    <div>4.目前无法剔除离群用户,请鉴别</div>
    <div>5.用户昵称来自于用户自行登记,请鉴别</div>
    <div>6.数据目前测试中,若有问题请及时反馈</div>
</div>


<div class="zan-panel">
    <div class="zan-cell zan-field">
        <div class="zan-cell__bd">
            用户
        </div>
        <div class="zan-cell__ft">
            阅览时间
        </div>
    </div>

    <?php
    foreach ($this->notReadUids as $uid)
    {
        /**
         * @var $user j7data_users
         */
        $user = $this->allGroupUsers[$uid];
        ?>
        <div class="zan-cell zan-field">
            <div class="zan-cell__bd">
                <img src="<?php echo $user->_avatarUrl();?>"  class="smallavatar" />
                <?php echo $user->_name();?>
            </div>
            <div class="zan-cell__ft zan-c-red">
                X
            </div>
        </div>
        <?php
    }
    ?>

    <?php
    foreach ($this->readUids as $uid)
    {
        /**
         * @var $user j7data_users
         */
        $user = $this->allGroupUsers[$uid];
        /**
         * @var $viewlog j7data_viewlog
         */
        $viewlog = $this->readedUsers[$uid];
        ?>
        <div class="zan-cell zan-field">
            <div class="zan-cell__bd">
                <img src="<?php echo $user->_avatarUrl();?>"  class="smallavatar" />
                <?php echo $user->_name();?>
            </div>
            <div class="zan-cell__ft">
                <?php echo $viewlog->_created_at();?>
            </div>
        </div>
        <?php
    }
    ?>
</div>

<?php endif;?>