<?php
/**
* @var $this tzs_tzs_postdata_Action
* @var $_ tzs_tzs_postdata_Action
* @see tzs_tzs_postdata_Action
*/
?>

<?php
//var_dump( $this- );
?>

<style>
    .zan-panel {
        margin-top:0;
    }
    .groupid,.created_at{
        color: #999999;
        font-size: 11px;
    }
</style>

<?php
include_view('tzs/headtabbar.php',['now'=>'me'])
?>


<div id="app">
    <div class="zan-panel" v-if="data.post">
        <div class="zan-cell zan-field">
            <div class="zan-cell__bd">文档ID</div>
            <div class="zan-cell__ft">
                {{data.post.id}}
            </div>
        </div>
        <div class="zan-cell zan-field">
            <div class="zan-cell__bd">标题</div>
            <div class="zan-cell__ft">
                {{data.post.title}}
            </div>
        </div>
        <div class="zan-cell zan-field">
            <div class="zan-cell__bd">发布时间</div>
            <div class="zan-cell__ft">
                {{data.post.created_at}}
            </div>
        </div>
        <div class="zan-cell zan-field">
            <div class="zan-cell__bd">已阅人数</div>
            <div class="zan-cell__ft">
                {{data.post.viewtimes}}
            </div>
        </div>
        <div class="zan-cell zan-field">
            <div class="zan-cell__bd">回复人数</div>
            <div class="zan-cell__ft">
                {{data.post.replytimes}}
            </div>
        </div>

        <div class="zan-cell zan-field"></div>


        <div class="zan-cell zan-cell--switch" v-if="data.post.sign_up_options">
            <div class="zan-cell__bd">启用报名 (关闭不影响报名数据)</div>
            <div class="zan-cell__ft">
                <input type="checkbox" class="uiswitch" v-model="canSignUp">
            </div>
        </div>

        <div class="zan-cell zan-field" v-if="data.post.sign_up_options">
            <div class="zan-cell__bd">已报名人数</div>
            <div class="zan-cell__ft a" v-on:click="gotosign">
                {{data.datas.signnums}}
                <span class="green" v-if="data.datas.signnums>0">下载查看数据</span>
                <div class="zan-icon zan-icon-arrow" style="color: #4b0;" v-if="data.datas.signnums>0"></div>
            </div>
        </div>

        <div class="zan-cell zan-field"></div>

        <div class="zan-cell zan-field" v-if="data.datas.groupUserNums.length>0">
            <div class="zan-cell__bd green">已转发群 ({{data.datas.groups.length}})</div>
            <div class="zan-cell__ft">
                已阅人数/群总人数
            </div>
        </div>

        <div v-if="data.datas.groupUserNums.length>0">
            <div class="zan-cell zan-field" v-for="(item,index) in data.datas.groupUserNums">
                <div class="zan-cell__bd">
                    <div class="groupid">{{item.gid}}</div>
                </div>
                <div class="zan-cell__ft a" v-on:click="gotogroup(item.gid)">
                    {{item.postgroupnums}} / {{item.allusernums}}
                    <span class="green">查看</span>
                    <div class="zan-icon zan-icon-arrow" style="color: #4b0;"></div>
                </div>
            </div>
            <div class="tc" style="font-size: 12px;color: #999999;">WEB版无法获取群名</div>
        </div>


    </div>
    
    <j7mask ref="j7mask" v-on:j7maskclose="handleClose"></j7mask>
</div>


<script>
  new Vue({
    el: '#app',
    data:{
      data:<?php echo json_encode($this->_ret);?>,
      pid:<?php echo $this->pid;?>,
      canSignUp:<?php echo $this->_ret['post']['can_sign_up']?'true':'false';?>,
      msg:''
    },
    created:function () {
    },
    watch:{
      canSignUp: 'switchChange'
    },
    methods: {
      handleClose:function () {

      },
      switchChange(){
        var that = this;
        var url = '/apis/posts/switchSign?pid='+this.pid+'&can_sign_up='+(this.canSignUp?1:0);
        this.request(url).then(function (data) {
          var json=data.body;
          if(json.status==1){
            that.data.post.can_sign_up = parseInt(json.can_sign_up);
            that.canSignUp = that.data.post.can_sign_up==1
            that.$refs.j7mask.showMask(json.msg);
          } else if(json.errorcode==1){
            that.canSignUp = false;
            that.$refs.j7mask.showMask(json.msg);
          }
          setTimeout(function () {
            that.$refs.j7mask.close();
          },1000);
        })
      },
      gotosign(){
        top.location.href = '/tzs/postdata/sign?pid='+this.pid;
      },
      gotogroup:function (gid) {
        top.location.href = '/tzs/postdata/group?pid='+this.pid+'&gid='+gid;
      }
    }
  });
</script>