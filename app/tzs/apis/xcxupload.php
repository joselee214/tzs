<?php
/**
* @var $this tzs_apis_xcxupload_Action
* @var $_ tzs_apis_xcxupload_Action
* @see tzs_apis_xcxupload_Action
*/
?>



<?php
//var_dump( $this- );
?>



<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <title>附件上传</title>
    <meta name="keywords" content="keywords"/>
    <meta name="description" content="description"/>
    <link rel="shortcut icon" href="/favicon.ico"/>
    <meta content="always" name="referrer"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="/res/reset.css"/>

    <script src="/res/js/jquery/jquery3.2.1.min.js" charset="utf-8"></script>
    <script src="/res/js/jquery/jquery.plus.js" charset="utf-8"></script>
    <link rel="stylesheet" href="/js/jq/jquery-ui.css" type="text/css" />
    <script src="/js/jq/jquery-ui.min.js"></script>

    <link rel="stylesheet" href="/js/plupload/jqui-plupload/css/jqui-plupload.css" type="text/css" />
    <script src="/res/js/jquery/plupload/plupload.full.min.js"></script>
    <script src="/js/plupload/i18n/cn.js"></script>
    <script src="/js/plupload/jqui-plupload/jqui-plupload.js"></script>

    <script type="text/javascript" src="https://res.wx.qq.com/open/js/jweixin-1.3.2.js"></script>
</head>
<body>

<style>
    .layoutmain{
        padding: 12px 15px;
    }
    .upbtn{
        position:relative;
        display:block;

        width: 100%;
        margin: 0 auto 10px;
        padding-left:15px;
        padding-right:15px;
        border-radius:2px;
        font-size:16px;
        line-height:45px;
        border: 0;

        text-align:center;
        text-decoration:none;
        overflow:hidden;
        color:#ffffff;
        background-color:#4b0;
    }
    .plupload_button{
        border: 0;
        color:#ffffff !important;
        background-color:#4b0;
        margin: 5px 0 0 5px;
    }
    /*.ui-button .ui-icon {*/
        /*background-image: url(http://www.upjiaju.com/images/ui-icons_ffffff_256x240.png);*/
    /*}*/
    .plupload_scroll{
        height: 100%;
        min-height: 280px;
    }
    .plupload_header_content .ui-state-error {
        height: 70px;
    }
</style>

<script>
  $.loadJs(['/js/plupload/jqui-plupload/jqui-plupload.js'],function(){upload_init();},'getScript');

  var uploader;
  function upload_init()
  {
    if( (typeof plupload)=='object')
    {
        $("#uploader").plupload({
          // General settings
          runtimes : 'html4', //html5,
          url : '/apis/xcxupload/upload?__session_id=<?php echo $this->__session_id;?>&__gcode=<?php echo $this->__gcode;?>',
          max_file_size : '10mb',
          chunk_size : '10mb',
          unique_names : true,
          filters : [
            {title : "Any files", extensions : "*"}
          ]
        });
        uploader = $('#uploader').plupload('getUploader');
        uploader.bind('FilesAdded', function(up, files){ upload_fileadd(up,files); });
        uploader.bind('Error', function(up, err){ upload_error(up,err); });
        uploader.bind('FileUploaded',function(up,files){ upload_uploaded(up,files); });   //UploadComplete
    }
    else
    {
      setTimeout('upload_init()',100);
    }
  }

  function upload_fileadd(up,files)
  {
    //log(uploader.settings.url);
  }
  function upload_error(up,err)
  {
    popup({'text':err.message});
  }
  function upload_uploaded(up,files)
  {
    wx.miniProgram.navigateBack();
//		popup({'text':'上传完成,请从图片空间中进选择','delayclose':5000});
  }

</script>

<div class="layoutmain">
    <div style="flex: 1;">
        <div id="uploader" style="">
            <p>
                loading...
                <br/><br/>
                (or You browser doesn't have HTML5 support !)
            </p>
        </div>
    </div>
    <div style="padding: 10px 0 0;color: #999999;text-align: center;" onclick="wx.miniProgram.navigateBack()">
        附件最大10M
        <br/>
        小程序下上传附件无法获取实时进度,请耐心等等
        <br/>
        上传完成后，请后退到文档界面
    </div>
</div>

<?php
//var_dump($_SERVER);
?>


</body>
</html>
