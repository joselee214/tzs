<?php
/**
 * @var $this tzs_index_index_Action
 * @var $_ tzs_index_index_Action
 * @see tzs_index_index_Action
 */
?>

<div class="zan-panel">
    <div class="zan-cell zan-field reset_cell tc">
        通知说 - WEB版
    </div>
    <div id="wxlogin_container" class="zan-cell zan-field reset_cell tc">
        使用微信扫码登录【通知说】...
    </div>
    <div class="zan-cell zan-field reset_cell tc c9">
        「通知说」是一款配套微信小程序的办公辅助工具，发布文档分享至微信群，可以统计群用户的已读/未读情况；也可以上传下载各种文档，进行在线报名/报名结果下载。
    </div>
</div>

<script type="text/javascript">

  $(document).ready(function()
  {
    if( $('#wxlogin_container').is(':visible') )
    {
      $.getScript('https://res.wx.qq.com/connect/zh_CN/htmledition/js/wxLogin.js',function () {
        var obj = new WxLogin({
          self_redirect:true,
          id:"wxlogin_container",
          appid: "<?php echo $this->appid;?>",
          scope: "snsapi_login",
          redirect_uri: "<?php echo SITE_DOMAIN?>/index/index/pccall",
          state: "<?php echo session_id();?>",
          style: "",
          href: ""
        });
      })
    }
  });
</script>