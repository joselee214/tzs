<?php
/**
* @var $this tzs_tzs_iadd_Action
* @var $_ tzs_tzs_iadd_Action
* @see tzs_tzs_iadd_Action
*/
?>

<?php
include_view('tzs/headtabbar.php',['now'=>'doc'])
?>

<?php //echo $this->_ret['post']['content'];?>

<style>
    input{
        font-size: 0.8rem;
    }
    textarea{
        font-size: 0.7rem;
    }
    .zan-panel {
        margin-top:0;
    }
    .postForm{
        padding-top: 5px;
    }
    .zan-icon {
        font-size: 18px;
    }
    .newUploadText{
        margin-left:10px;
        color: #888888;
        font-size: 18px;
    }
    .uploadnote{
        font-size: 9px;
        margin-top: 3px;
        text-align: center;
    }

    .imgsfiles_img_cell{
        display: block;
        text-align:center;
        padding-bottom:0;
        padding-top: 5px;
        padding-right: 25px;
    }
    .imgsfiles_img_item{
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px dashed #cccccc;
        padding: 5px 0 5px 0;
    }
    .imgsfiles_file_item{
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px dashed #cccccc;
        padding: 5px 0 5px 0;
    }
    .imgsfiles_img_div{
        width: 200px;
        max-height: 80px;
        text-align: center;
        overflow: hidden;
    }
    .imgsfiles_img{
        max-height: 80px;
        text-align: center;
        margin: 0 auto;
        display: inline-block;
    }
    .imgsfiles_file_name{
        margin-right: 50px;
        overflow: hidden;
    }
    .hiddenbtn{
        position: absolute;top: 0;left: 0;width: 100%;height: 100%;opacity: 0;
    }
    .sign_options{
        height: 100px;
        margin: 0 20px;
        padding: 2px;
        background: #f3f3f3;
    }
</style>

<div id="app">
    <div>
        <form @submit.prevent="submit">

        <div class="zan-panel postForm">
            <div class="zan-cell zan-field">
                <input class="zan-field__input zan-cell__bd" name="title" placeholder="标题" type="text" v-model="post.title" />
            </div>
            <div class="zan-cell zan-field">
                <textarea name="content" class="tzs_textarea zan-field__input zan-cell__bd" placeholder="文字内容" v-model="post.content"></textarea>
            </div>
        </div>

        <div class="zan-panel" v-if="post.imgslist||post.fileslist">
            <div class="zan-cell zan-field zan-pull-center imgsfiles_img_cell" v-if="post.imgslist||post.fileslist">
                <div  v-for="(item,index) in post.imgslist">
                    <div class="imgsfiles_img_item">
                        <div class="imgsfiles_img_div a" v-on:click="preview(item.path,item.filename)" :alt="item.filename" :fid="item.id">
                            <img class="imgsfiles_img" :src="item.path" />
                        </div>
                        <div>
                            <div v-if="index>0" class="zan-icon zan-icon-upgrade a" style="color: red;margin-right:30px;" v-on:click="upfirstImg(index)"></div>
                            <div class="zan-icon zan-icon-close a" style="color: red;" v-on:click="deleteImgs(index)"></div>
                        </div>
                    </div>
                </div>

                <div v-for="(item,index) in post.fileslist">
                    <div class="imgsfiles_file_item">
                        <div class="imgsfiles_file_name a">
                            {{item.filename}}
                        </div>
                        <div>
                            <div class="zan-icon zan-icon-close a" style="color: blue;" v-on:click="deleteOriginUpload(index)"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="zan-panel">
            <div class="zan-cell zan-field zan-pull-center" style="justify-content:space-around;">
                <div class="a" style="position: relative;width: 49%;">
                    <button class="a hiddenbtn" id="uploadfile"></button>
                    <div style="display:flex;justify-content:center;">
                        <div class="zan-icon zan-icon-add2"  style="color: blue;"></div>
                        <span class="newUploadText">添加附件</span>
                    </div>
                    <div class="uploadnote" v-html="uploadtextfile"></div>
                </div>
                <div class="a" style="position: relative;width: 49%;">
                    <button class="a hiddenbtn" id="uploadimg"></button>
                    <div style="display:flex;justify-content:center;">
                        <div class="zan-icon zan-icon-add2"  style="color: #4b0;"></div>
                        <span class="newUploadText">上传图片</span>
                    </div>
                    <div class="uploadnote" v-html="uploadtextimg"></div>
                </div>
            </div>
        </div>

        <div class="zan-panel">
            <div class="zan-cell zan-cell--switch">
                <div class="zan-cell__bd">允许回复</div>
                <div class="zan-cell__ft">
                    <input type="checkbox" class="uiswitch" v-model="allowReply">
                </div>
            </div>

            <div class="zan-cell zan-cell--switch">
                <div class="zan-cell__bd">启用报名</div>
                <div class="zan-cell__ft">
                    <input type="checkbox" class="uiswitch" v-model="canSignUp">
                </div>
            </div>
            <div class="zan-cell zan-field" v-if="canSignUp">

                <textarea name="sign_up_options" class="tzs_textarea zan-field__input zan-cell__bd sign_options" placeholder="这里填写报名项,每一行为一个报名项,用于简单表格收集;不支持空行,发布后请仔细检查后再转发分享!" v-model="post.sign_up_options"></textarea>

            </div>

        </div>

        <div class="zan-panel">
            <div class="zan-btns">
                <button type="submit" class="zan-btn zan-btn--primary">提交发布</button>
                <button class="zan-btn button-hover" type="reset" v-on:click="cancel">取消</button>
            </div>
        </div>

        </form>

    </div>

    <j7mask ref="j7mask" v-on:j7maskclose="handleClose" v-on:j7maskclickimg="handleClickImg"></j7mask>

</div>

<script src="/res/js/jquery/plupload/plupload.full.min.js"></script>
<script>
  var vueapp = new Vue({
    el: '#app',
    data:{
      post:<?php echo json_encode($this->_ret);?>,
      pid:<?php echo $this->pid;?>,
      allowReply:false,
      canSignUp:false,
      uploadtextfile:'',
      uploadtextimg:''
    },
    created:function () {
      console.log(this.post);
      if(this.post && this.post.allowReply!=undefined){
        this.allowReply = parseInt(this.post.allowReply)==1;
      }
      if(this.post && this.post.can_sign_up!=undefined){
        this.canSignUp = parseInt(this.post.can_sign_up)==1;
      }
      this.post.fileslist = this.post.fileslist || [];
      this.post.imgslist = this.post.imgslist || [];
    },
    methods: {
      handleClose () {
      },
      handleClickImg(showImgSrc,text){
        window.open(showImgSrc);
      },
      preview(path,text=''){
        this.$refs.j7mask.showMask(text,path);
      },
      cancel(){
        history.go(-1);
      },
      upfirstImg(ind){
        var upitem = this.post.imgslist[ind];
        this.post.imgslist.splice(ind,1);
        this.post.imgslist.unshift(upitem);
      },
      deleteImgs(ind){
        this.post.imgslist.splice(ind,1);
      },
      deleteOriginUpload(ind){
        this.post.fileslist.splice(ind,1);
      },
      setUploadtext(str,type){
        if( type=='file' ){
          this.uploadtextfile = str;
        } else {
          this.uploadtextimg = str;
        }
      },
      setUploadItems(item,type){
        if( type=='file' ){
          this.post.fileslist.push(item);
        } else {
          this.post.imgslist.push(item);
        }
      },
      submit(){
        var that = this;
        if( that.post.content=='' && that.post.title=='' && that.post.imgslist.length==0 ){
          return;
        }
        var upimgs = '';
        if( that.post.imgslist.length>0 ){
          var upimgs_ = [];
          for (var i = 0; i < that.post.imgslist.length; i++) {
            upimgs_.push(that.post.imgslist[i].id);
          }
          upimgs = upimgs_.join(',');
        }
        var upfiles = '';
        if( that.post.fileslist.length>0 ){
          var upfiles_ = [];
          for (var i = 0; i < that.post.fileslist.length; i++) {
            upfiles_.push(that.post.fileslist[i].id);
          }
          upfiles = upfiles_.join(',');
        }

        var url = '/apis/posts/doPost';
        this.request(url,{
          title: that.post.title,
          content: that.post.content,
          upimgs:upimgs,
          upfiles:upfiles,
          pid:that.pid||0,
          allowReply:that.allowReply?1:0,
          can_sign_up:that.canSignUp?1:0,
          sign_up_options:that.post.sign_up_options
        },'post').then(function (data) {
          var json=data.body;
          if(json.status==1){
            that.$refs.j7mask.showMask('已成功发布!');
            setTimeout(function () {
              top.location.href = '/tzs/i';
            },1000);
          }
        })

      },
      test(e){
        console.log(e);
      }
    }
  });


  var uploader = new plupload.Uploader({
    runtimes : 'html5,html4', //html5,
    browse_button : 'uploadfile', //触发文件选择对话框的按钮，为那个元素id
    url : '/apis/xcxupload/upload?fromtype=2', //fromtype 0 小程序附件  1小程序图片 2 web附件 3 web图片
    max_file_size : '10mb',
    chunk_size : '10mb',
    unique_names : true,
    filters : [
      {title : "Any files", extensions : "*"}
    ]
  });

  var uploaderImg = new plupload.Uploader({
    runtimes : 'html5,html4', //html5,
    browse_button : 'uploadimg', //触发文件选择对话框的按钮，为那个元素id
    url : '/apis/xcxupload/upload?fromtype=3', //fromtype 0 小程序附件  1小程序图片 2 web附件 3 web图片
    max_file_size : '10mb',
    chunk_size : '10mb',
    unique_names : true,
    filters : [
      {title : "Image files", extensions : "jpg,jpeg,gif,png"}
    ]
  });

  var upfiles_showText = '作为附件下载';
  var upfiles_ErrorText = '';
  var upfiles_AllNums = 0;
  var upfiles_ProcessNum = 0;
  var upfiles_ErrNum = 0;

  var upimgs_showText = '图片直接显示';
  var upimgs_ErrorText = '';
  var upimgs_AllNums = 0;
  var upimgs_ProcessNum = 0;
  var upimgs_ErrNum = 0;

  uploader.init();
  uploaderImg.init();
  vueapp.setUploadtext(upfiles_showText,'file');
  vueapp.setUploadtext(upimgs_showText,'img');

  uploader.bind('Error', function(up, err){
    // console.log('Error',up,err);
    upfiles_ErrorText = upfiles_ErrorText + '<br/>' + err.file.name+' : '+err.message;
    upfiles_ErrNum = upfiles_ErrNum+1;
    vueapp.setUploadtext( upfiles_ErrorText,'file');
  });
  uploaderImg.bind('Error', function(up, err){
    // console.log('Error',up,err);
    upimgs_ErrorText = upimgs_ErrorText + '<br/>' + err.file.name+' : '+err.message;
    upimgs_ErrNum = upimgs_ErrNum+1;
    vueapp.setUploadtext( upimgs_ErrorText,'img');
  });

  uploader.bind('FilesAdded', function(up, files){
    upfiles_AllNums = upfiles_AllNums+files.length;
    vueapp.setUploadtext( upfiles_ProcessNum +'/'+ upfiles_AllNums + ' 待上传','file');
    uploader.start();
  });
  uploaderImg.bind('FilesAdded', function(up, files){
    upimgs_AllNums = upimgs_AllNums+files.length;
    vueapp.setUploadtext( upimgs_ProcessNum +'/'+ upimgs_AllNums + ' 待上传','img');
    uploaderImg.start();
  });

  uploader.bind('UploadProgress',function(uploader,file){
    //console.log('UploadProgress',uploader,file);
    vueapp.setUploadtext( file.percent+'% '+ upfiles_ProcessNum +'/'+ upfiles_AllNums + upfiles_ErrorText,'file');
  });
  uploaderImg.bind('UploadProgress',function(uploader,file){
    //console.log('UploadProgress',uploader,file);
    vueapp.setUploadtext( file.percent+'% '+ upimgs_ProcessNum +'/'+ upimgs_AllNums + upimgs_ErrorText,'img');
  });

  uploader.bind('FileUploaded',function(up,files,data){
    upfiles_ProcessNum = upfiles_ProcessNum+1;
    vueapp.setUploadtext( upfiles_ProcessNum +'/'+ upfiles_AllNums + ' 上传完成' + upfiles_ErrorText,'file');
    if( (upfiles_ProcessNum + upfiles_ErrNum) == upfiles_AllNums ){
      setTimeout(function () {
        vueapp.setUploadtext( upfiles_showText,'file');
      },5000);
    }
    //console.log('FileUploaded',up,files,data.response);
    var json = JSON.parse(data.response);
    console.log('FileUploaded',json);
    if( json.success==1 ){
      vueapp.setUploadItems(json.upload,'file');
    }
  });
  uploaderImg.bind('FileUploaded',function(up,files,data){
    upimgs_ProcessNum = upimgs_ProcessNum+1;
    vueapp.setUploadtext( upimgs_ProcessNum +'/'+ upimgs_AllNums + ' 上传完成' + upimgs_ErrorText,'img');
    if( (upimgs_ProcessNum + upimgs_ErrNum) == upimgs_AllNums ) {
      setTimeout(function () {
        vueapp.setUploadtext(upimgs_showText, 'img');
      }, 5000);
    }
    //console.log('FileUploaded',up,files,data.response);
    var json = JSON.parse(data.response);
    console.log('FileUploaded',json);
    if( json.success==1 ){
      vueapp.setUploadItems(json.upload,'img');
    }
  });

</script>