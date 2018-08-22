<?php
/**
* @var $this tzs_tzs_d_Action
* @var $_ tzs_tzs_d_Action
* @see tzs_tzs_d_Action
*/
?>

<?php
include_view('tzs/headtabbar.php',['now'=>'doc'])
?>

<?php //echo $this->_ret['post']['content'];?>

<style>

    .doc-title{
        padding:10px 10px;line-height:30px;text-align:center;
    }

    .doc{
        margin: 0 15px;
        padding: 10px 0 5px;
        text-align: center;
        font-size: 14px;
    }

    .doc_auth{
        border-top: 1px dashed #cccccc;
    }
    .avatar{
        width: 16px;
        height: 16px;
    }
    .nickname{
        font-size: 15px;
        margin-left: 5px;
    }
    .created_at{
        color: #cccccc;
        padding: 0 0 5px;
    }


    .doc_mark{
        padding: 2px 0 0;
        display: flex;
        justify-content: center;
    }

    .zan-col {
        line-height: 30px;
        text-align: center;
        background-color: #eee;
        font-size: 12px;
        color: #999;
    }

    .doc_content{
        padding: 5px 0;
        color: #333333;
        text-align: left;
    }
    .doc_imgs{
        padding: 0;
        margin: 0;
        font-size:0;
    }
    .doc_imgs img {
        display:block;
        margin: 0 auto;
        max-width: 100%;
    }

    .filecell{
        padding: 12px 0;
        justify-content:space-between;
    }
    .zan-cell__bd{
        text-align: left;
        overflow:hidden;
    }
    .zan-cell__ft{
        text-align: right;
        max-width: 38%;
    }

    .active_title{
        color: #a3c83a;
    }

    .viewlogs_time{
        font-size: 12px;
    }

    .act-help{
        text-align: center;
        font-size: 10px;
        color: #bbbbbb;
        display:block;
    }


    .post_bar{
        margin-top: 5px;
        border-top: 2px solid #a3c83a;
        justify-content: space-between;
    }
    .post_bars{
        padding-bottom:5px;
    }
    .replytimes{
        text-align: right;
    }
    .viewtimes{
        text-align: left;
    }
    .reply_title{
        margin-top: 5px;
        padding: 4px 4px 4px;
        background: #f9f9f9;
        font-size: 12px;
        color: #999999;
    }
    .reply_content{
        padding: 6px 0 6px 10px;
        text-align:left;
    }
    .reply_delete{
        text-align: right;
        justify-content: flex-end;
        padding: 0 0 6px 0;
    }
    .reply_opt_a{
        display: inline-block;
        margin-left: 10px;
        color:#c5e66b;
    }
    .reply_form_text{
        position:relative;width:100%;
    }
    .reply_form_content{
        width:96%;border:1px solid #cccccc;height:60px;padding:2px;margin:0 auto 2px;
    }
    .reply_form_btn{
        margin: 0 0 10px;
    }

    .sign_up_div{
        background: #f3f3f3;
    }
    .sign_a{
        justify-content: center;
        color: green;
    }
    .sign_up_div .zan-cell{
        padding: 2px 5px;
    }
    .sign_form{
        display: block;
    }
    .sign_form_cell{
        display: block;
        width: 100%;
        text-align: left;
    }
    .sign_content{
        text-align: right;
        position: relative;
    }
    .sign_title{
        padding: 10px 0 3px 0;
        color: #666666;
    }
    .sign_value{
        padding: 0 3px;
        margin-left: 20px;
        background: #ffffff;
        width: 90%;
    }
</style>

<div id="app">
    <div v-cloak>
        <div v-if="doc.post.title!=''" class="doc-title">
            {{doc.post.title}}
        </div>

        <div class="doc doc_auth" v-if="doc.post.nickname!=''">
            <img class="avatar" :src="doc.post.avatar" />
            <span class="nickname">{{doc.post.nickname}}</span>
        </div>

        <div class="doc created_at">
            {{doc.post.created_at}}
        </div>

        <div class="doc zan-row doc_mark">
            <div class="zan-col zan-col-8"> <div class="zan-icon zan-icon-contact"></div> 已阅 * {{doc.post.viewtimes}} </div>
             <div class="zan-col zan-col-8" v-if="doc.post.allowReply==1"> <div class="zan-icon zan-icon-chat"></div> 评论 * {{doc.post.replytimes}} </div>
            <div class="zan-col zan-col-8"> <div class="zan-icon zan-icon-tosend"></div> 附件 * {{doc.post.filenums}} </div>
        </div>

        <div style="padding: 10px 0;">

            <div class="doc doc_content" v-if="doc.post.content!=''" v-html="doc.post.content"></div>

            <div class="doc doc_imgs" v-if="doc.post.imgslist&&(doc.post.imgslist.length>0)">
                <div v-for="(item,index) in doc.post.imgslist">
                    <img :src="item.path" data-index="index" />
                </div>
            </div>

        </div>

        <div>
            <div class="doc doc_files" v-if="doc.post.fileslist&&(doc.post.fileslist.length>0)" id="downloads_a">
                <div class="zan-cell filecell active_title">
                    <div class="zan-cell__bd filename">
                        <div class="zan-icon zan-icon-tosend"></div> 附件下载
                    </div>
                    <div class="zan-cell__ft filedown"></div>
                </div>
                <div v-for="(item,index) in doc.post.fileslist">
                    <a :href="'/apis/xcxupload/download?id='+item.id" :download="item.filename">
                    <div class="zan-cell filecell a">
                        <div class="zan-icon zan-icon-add"></div>
                        <div class="zan-cell__bd filename"> {{item.filename}}</div>
                    </div>
                    </a>
                </div>
            </div>
        </div>

        <div class="doc sign_up_div" v-if="doc.post.can_sign_up==1">
            <div class="zan-cell a sign_a" v-on:click="toggleSinUpDiv">{{doc.post.user_is_sign==1?'已经报名':'立即报名'}}</div>
            <div class="zan-cell sign_form" v-if="showSignUpDiv">
                <form @submit.prevent="signSubmit" id="id_sign_form" class="form_sign_form">
                    <div class="sign_form_cell" v-for="(item,index) in doc.post.sign_options">
                        <div class="sign_title">{{item.t}}:</div>
                        <div class="sign_content">
                            <input class="zan-field__input zan-cell__bd sign_value" :name="'sign_values_'+index" placeholder="" placeholder-class="zan-field__placeholder" type="text" :value="item.v" />
                        </div>
                    </div>
                    <div style="margin-top:10px;">
                        <button class="zan-btn zan-btn--primary" type="submit">提交报名</button>
                        <button type="reset" class="zan-btn" v-on:click="deleteSign" v-if="doc.post.user_is_sign==1">删除报名</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="doc doc_bars">
            <div class="zan-cell filecell post_bar">
                <div class="post_bars viewtimes a" style="color: #a3c83a;border-bottom: 2px solid #a3c83a;" id="viewlogs_a" v-if="showViewlogs">
                    <div class="zan-icon zan-icon-contact"></div> 已阅用户 * {{doc.post.viewtimes}}
                </div>
                <div class="post_bars viewtimes a" id="viewlogs_a" v-if="!showViewlogs" v-on:click="switchshow('viewlogs_a')">
                    <div class="zan-icon zan-icon-contact"></div> 已阅用户 * {{doc.post.viewtimes}}
                </div>
                <div class="post_bars replytimes a" style="color: #a3c83a;border-bottom: 2px solid #a3c83a;" v-if="showRelpys&&(doc.post.allowReply==1)" id="replys_a">
                    <div class="zan-icon zan-icon-chat"></div> 回复 * {{doc.post.replytimes}}
                </div>
                <div class="post_bars replytimes a" id="replys_a" v-if="(!showRelpys)&&(doc.post.allowReply==1)" v-on:click="switchshow('replys_a')">
                    <div class="zan-icon zan-icon-chat"></div> 回复 * {{doc.post.replytimes}}
                </div>
            </div>

            <div v-if="showViewlogs">
                <div v-for="(item,index) in doc.viewlogs.items">
                    <div class="zan-cell filecell">
                        <div class="zan-cell__bd divlogs_user">
                            <div>
                                <img :src="item.avatar"  class="smallavatar" />
                                {{item.nickname}}
                            </div>
                        </div>
                        <div class="zan-cell__ft viewlogs_time">{{item.updated_at}}</div>
                    </div>
                </div>
                <div class="zan-cell filecell act-help a" v-on:click="getMoreViewLog">{{moreViewLog}}</div>
            </div>


            <div v-if="showRelpys">

                <form @submit.prevent="replySubmit" class="reply_form">
                    <div class="zan-panel">
                        <div class="reply_form_text">
                            <textarea name="replycontent" class="tzs_textarea zan-field__input zan-cell__bd reply_form_content" placeholder="回复内容" v-model="replycontent"></textarea>
                        </div>
                        <div class="reply_form_btn">
                            <button class="zan-btn zan-btn--primary button-hover" type="submit">发表回复</button>
                        </div>
                    </div>
                </form>

                <div v-for="(item,index) in doc.replys.items">
                    <div class="zan-cell filecell reply_title">
                        <div class="zan-cell__bd viewlogs_user">
                            <div>
                                <img :src="item.avatar" class="smallavatar" mode="widthFix" />
                                {{item.nickname}}
                            </div>
                        </div>
                        <div class="zan-cell__ft viewlogs_time">{{item.created_at}}</div>
                    </div>
                    <div class="filecell reply_content" v-html="item.content"></div>
                    <div class=" filecell reply_delete" v-if="item.candelete==1">
                        <div class="reply_opt_a a" v-on:click="setTop(item.id,index,0)" v-if="(item.sort>0)&&(item.cansettop==1)">取消置顶</div>
                        <div class="reply_opt_a a" v-on:click="setTop(item.id,index,1)" v-if="(index>0)&&(item.cansettop==1)">置顶部</div>
                        <div class="reply_opt_a a" v-on:click="deleteReply(item.id,index)">删除</div>
                    </div>
                </div>

                <div class="zan-cell filecell act-help a" v-on:click="getmoreReplys">{{moreReplys}}</div>
            </div>

        </div>
        
    </div>

    <j7mask ref="j7mask" v-on:j7maskclose="maskClose"></j7mask>

</div>


<script>
  new Vue({
    el: '#app',
    data:{
      doc:<?php echo json_encode($this->_ret);?>,
      pid:<?php echo $this->pid;?>,
      moreViewLog:'', //没有更多了
      moreReplys:'',
      currentPage:1,
      currentRPage:1,
      showViewlogs:true,
      showRelpys:false,
      msg:'',
      replycontent:'',

      showSignUpDiv:false
    },
    created:function () {
      // console.log(this.doc);
      this.expNotice();
      if( this.doc.post.allowReply==1 ){
        this.showViewlogs=false;
        this.showRelpys=true;
      }
    },
    methods: {
      signSubmit(event,ddd){
        var that = this;
        // var formData = new FormData($(this.$el).find("#id_sign_form")[0]);
        var formData = $(this.$el).find('#id_sign_form').serializeArray();

        var values={};
        for (var i = 0; i < formData.length; i++) {
          values[formData[i]['name']] = formData[i]['value'];
        }
        // console.log(values);

        var url = '/apis/signUp/signUp';
        this.request(url,{
          pid: that.pid,
          values: values
        },'post').then(function (data) {
          var json=data.body;
          if( json.status==1 ){
            that.showSignUpDiv = false;
            that.doc.post.sign_options = json.sign_options;
            that.doc.post.user_is_sign = json.user_is_sign;
            that.$refs.j7mask.showMask('成功提交了报名!');
            setTimeout(function () {
              that.$refs.j7mask.close();
            },1000);
          }
        })
      },
      maskClose(imgsrc,msg){
      },
      toggleSinUpDiv(){
        this.showSignUpDiv = !this.showSignUpDiv
      },
      deleteSign(){
        var that = this;
        var url = '/apis/signUp/deleteSign?pid='+this.pid;
        this.request(url).then(function(data){
          var json=data.body;
          if(json.status==1){
            that.showSignUpDiv = false;
            that.doc.post.sign_options = json.sign_options;
            that.doc.post.user_is_sign = json.user_is_sign;
            that.$refs.j7mask.showMask('成功删除了报名!');
            setTimeout(function () {
              that.$refs.j7mask.close();
            },1000);
          }
        })
      },
      replySubmit(){
        var that = this;
        var url = '/apis/posts/addReply?id='+this.pid;
        if( that.replycontent=='' ){
          return;
        }
        this.request(url,{content:that.replycontent,pid:that.pid},'post').then(function (data) {
          var json=data.body;
          if(json.status==1){
            that.doc.replys = json.replys;
            that.replycontent = '';
            that.expNotice();
          }
        })
      },
      setTop(id,ind,opt){
        var that = this;
        if (confirm('是否'+(parseInt(opt)==0?'取消':'')+'置顶?')) {
          var url = '/apis/posts/topReply?id=' + id + '&pid='+that.pid+'&title='+opt;
          this.request(url).then(function (data) {
            var json=data.body;
            if( json.status==1 ) {

              if( parseInt(opt)==1 ){
                var pop = that.doc.replys.items[ind];
                pop.sort=1;
                that.doc.replys.items.splice(ind,1);
                that.doc.replys.items.unshift(pop);
              } else {
                that.getReplys(1)
              }
            }
          })
        }
      },
      deleteReply(id,ind){
        var that = this;
        console.log(id,ind);
        if (confirm('是否删除该回复?')) {
          var url = '/apis/posts/deleteReply?id=' + id;
          this.request(url).then(function (data) {
            var json=data.body;
            if( json.status==1 ) {
              that.doc.replys.items.splice(ind, 1);
              that.expNotice();
            }
          })
        }
      },
      switchshow(s){
        if( s=='viewlogs_a' ){
          this.showViewlogs=true;
          this.showRelpys=false;
        } else if(s=='replys_a'){
          this.showViewlogs=false;
          this.showRelpys=true;
        }
      },
      getmoreReplys(){
        var that = this;
        if( this.currentRPage>=that.doc.replys.totalPage ){
          return;
        }
        var page = this.currentRPage + 1;
        this.getReplys(page);
      },
      getReplys(page){
        var that = this;
        var url = '/tzs/d/replys?pid='+this.pid+'&page='+page;
        this.request(url).then(function(data){
          var json=data.body;
          if(json.currentPage==1) {
            that.doc.replys.items = []
          }
          that.doc.replys.totalPage = json.totalPage;
          that.doc.replys.currentPage = json.currentPage;
          that.doc.replys.totalCount = json.totalCount;
          that.currentRPage = json.currentPage;
          that.doc.replys.items.push.apply(that.doc.replys.items,json.items);
          that.expNotice();
        })
      },
      getMoreViewLog()
      {
        var that = this;
        if( this.currentPage>=that.doc.viewlogs.totalPage ){
          return;
        }
        var page = this.currentPage + 1;
        console.log('getMoreViewLoggetMoreViewLoggetMoreViewLog');
        var url = '/tzs/d/viewlogs?pid='+this.pid+'&page='+page;
        this.request(url).then(function(data){
          var json=data.body;
          // console.log('getMoreViewLog:',json);
          that.doc.viewlogs.totalPage = json.totalPage;
          that.doc.viewlogs.currentPage = json.currentPage;
          that.doc.viewlogs.totalCount = json.totalCount;
          that.currentPage = json.currentPage;
          that.doc.viewlogs.items.push.apply(that.doc.viewlogs.items,json.items);
          that.expNotice();
        })
      },
      expNotice(){
        if( this.doc.viewlogs.totalPage>this.doc.viewlogs.currentPage ){
          this.moreViewLog = '更多阅读记录('+(this.doc.viewlogs.currentPage*this.doc.viewlogs.limit)+'/'+this.doc.viewlogs.totalCount+')';
        } else {
          this.moreViewLog = '没有更多了('+this.doc.viewlogs.totalCount+')';
        }
        if( this.doc.replys.totalPage>this.doc.replys.currentPage ){
          this.moreReplys = '更多回复记录('+(this.doc.replys.currentPage*this.doc.replys.limit)+'/'+this.doc.replys.totalCount+')';
        } else {
          this.moreReplys = '没有更多回复了('+this.doc.replys.totalCount+')';
        }
      },
      download:function (id) {
        // apis/xcxupload/download?id=
      }
    }
  });
</script>