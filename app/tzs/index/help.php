<?php
/**
* @var $this tzs_index_help_Action
* @var $_ tzs_index_help_Action
* @see tzs_index_help_Action
*/
?>


<?php
//var_dump( $this- );
?>


<?php
include_view('tzs/headtabbar.php',['now'=>'help']);
?>


<style>
    .h{
        padding:5px 0 5px 40px;
    }
</style>

<div>

    <div class="zan-panel">
        <div class="zan-cell zan-field tc reset_cell">
            使用帮助
        </div>
        <div class="zan-cell zan-field reset_cell tc c9">
            「通知说」是一款配套微信小程序的办公辅助工具，发布文档分享至微信群，<span class="red">可以统计群用户的已读/未读情况；也可以上传下载各种文档，进行在线报名/报名结果下载</span>。
        </div>
    </div>
    <div class="zan-panel">
        <div class="zan-cell zan-field reset_cell">
            1.发布文档
            <div class="h">
                step1:
                <img src="/help/1.jpg" style="width: 350px;">
            </div>
            <div class="h">
                step2:
                <img src="/help/2.jpg" style="width: 350px;">
            </div>
        </div>
    </div>

    <div class="zan-panel">
        <div class="zan-cell zan-field reset_cell">
            2.完成文档发布后转发分享到微信群
            <div class="h blue">
                必须进入微信小程序(可用微信扫码页面底部的二维码)
            </div>
        </div>
        <div class="zan-cell zan-field reset_cell">
            <div class="h">
                step3:
                <img src="/help/3.jpg" style="width: 350px;">
            </div>
            <div class="h">
                step4:
                <img src="/help/4.jpg" style="width: 350px;">
            </div>
        </div>
    </div>

    <div class="zan-panel">
        <div class="zan-cell zan-field reset_cell">
            3.数据查看
            <div class="h blue">
                下载报名结果，必须通过web版进入
            </div>
        </div>
        <div class="zan-cell zan-field reset_cell">
            <div class="h">
                step5:
                <img src="/help/5.jpg" style="width: 350px;">
            </div>
            <div class="h">
                step6:
                <img src="/help/6.jpg" style="width: 350px;">
            </div>
        </div>
    </div>

</div>