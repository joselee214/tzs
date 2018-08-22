<?php
/**
* @var $this tzs_tzs_i_Action
* @var $_ tzs_tzs_i_Action
* @see tzs_tzs_i_Action
*/
?>



<style>

    .tzs_intro{
        margin: 10px 40px 10px;
        padding: 15px;
        background: #ffffff;
        color: #999999;
        font-size: 14px;
        text-align: center;
        border: 1px dashed #cccccc;
    }
    .zan-loadmore--nodata{
        margin: 120px auto;
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
    .title_icon{
        margin: 0 2px 0 20px;
        font-size: 14px;
    }
    .title_posts_num{
        font-size: 14px;
        color: #777;
    }
    .newUploadText{
        margin-left:10px;
        color: #888888;
        font-size: 18px;
    }

    .zan-card{
        padding: 15px 20px;
    }
    .zan-card__left-col{
        margin-right: 0px;
    }
    .zan-card__thumb{
        width: 72px;
    }
    .zan-card__detail{
        margin-left: 85px;
        margin-right: 25px;
    }
    .zan-card__right-col{
        margin-top:40px;
    }
    .doc_mark_row .zan-icon{
        font-size: 14px;
    }
    .doc_mark{
        margin:0 36px 0 4px;
    }


    .doc_admin_row{
        margin-top: 8px;
        opacity:0.5;
    }
    .doc_admin_row .zan-icon{
        font-size: 12px;
    }
    .doc_admin_row .zan-btn{
        margin-right: 20px;
    }
    a.zan-btn--primary{
        color: #ffffff;
    }
</style>

<?php
include_view('tzs/headtabbar.php',['now'=>'me'])
?>

<div id="app">

    <div class="tc" style="margin: 20px 20px;">
        <a href="/tzs/iadd" class="zan-btn zan-btn--primary" style="width: 100%;">+ 新增发布</a>
    </div>

    <div v-if="data.items.length>0" v-cloak>
        <div class="data-list">

            <div class="zan-card" v-for="(item,index) in data.items">
                <div class="zan-card__thumb a" v-on:click="gotopost(item.id)">
                    <img class="zan-card__img" :src="item.logo" mode="aspectFit" />
                </div>
                <div class="zan-card__right-col a" v-on:click="gotopost(item.id)">
                    <div class="zan-icon zan-icon-arrow" style="color: #4b0;"></div>
                </div>
                <div class="zan-card__detail">
                    <div class="zan-card__detail-row a" v-if="item.title!=''" v-on:click="gotopost(item.id)">
                        <div class="zan-card__left-col zan-ellipsis--l2 doc_content_area">
                            {{item.title}}
                        </div>
                    </div>
                    <div class="zan-card__detail-row a" v-on:click="gotopost(item.id)">
                        <div class="zan-card__left-col zan-c-gray">{{item.created_at}}</div>
                    </div>

                    <div class="zan-card__detail-row zan-c-gray-darker a" v-on:click="gotopost(item.id)">
                        <div class="zan-card__left-col zan-ellipsis--l3 doc_content_area">
                            {{item.content}}
                        </div>
                    </div>

                    <div class="zan-card__detail-row doc_mark_row a" v-on:click="gotopost(item.id)">
                        <div class="zan-icon zan-icon-contact"  style="color: #4b0;"></div>
                        <span class="zan-c-gray doc_mark">{{item.viewtimes}}</span>
                        <div class="zan-icon zan-icon-chat"  style="color: #4b0;" v-if="item.allowReply==1"></div>
                        <span class="zan-c-gray-dark doc_mark" v-if="item.allowReply==1">{{item.replytimes}}</span>

                        <div class="zan-icon zan-icon-pending-orders"  style="color: #4b0;" v-if="item.sign_up_options"></div>
                        <span class="zan-c-gray-dark doc_mark" v-if="item.sign_up_options">{{item.signtimes}}</span>
                    </div>

                    <div class="zan-card__detail-row doc_admin_row">
                        <div class="zan-btn zan-btn--mini zan-btn--danger zan-btn--plain a" v-on:click="deleteDoc(item.id,index)">
                            <div class="zan-icon zan-icon-delete zan-c-red"></div> 删除
                        </div>
                        <div class="zan-btn zan-btn--mini zan-btn--primary zan-btn--plain a" v-on:click="editeDoc(item.id)">
                            <div class="zan-icon zan-icon-edit-data zan-c-green"></div> 编辑
                        </div>
                        <div class="zan-btn zan-btn--mini zan-btn--primary zan-btn--plain a" v-on:click="dataDoc(item.id)">
                            <div class="zan-icon zan-icon-search zan-c-green"></div> 数据
                        </div>
                    </div>

                </div>
            </div>

        </div>
        <div class="tc">
            <j7page :currentpage="parseInt(data.currentPage)" :totalpage="parseInt(data.totalPage)" :totalcount="parseInt(data.totalCount)" v-on:clickpage="pageClick"></j7page>
        </div>
    </div>
</div>


<script>
  new Vue({
    el: '#app',
    data:{
      data:<?php echo json_encode($this->ps);?>,
      currentPage:<?php echo $this->ps->getCurrentPage();?>,
      msg:''
    },
    created:function () {
//            this.pageClick(1);
    },
    methods: {
      pageClick:function (page=1) {
        var url = '/tzs/i/data';
        var that = this;
        jQuery('html,body').animate({scrollTop:0},'slow');
        this.currentPage = page;
        this.request(url,{page:page}).then(function(data){
          var json=data.body;
          that.data=json;
        },function(response){
          console.info(response);
        })
      },
      gotopost:function (id) {
        top.location.href = '/tzs/d?pid='+id;
      },
      deleteDoc:function (id,ind) {
        var that = this;
        if (confirm('是否删除该文档?')){
          var url = '/apis/posts/delete?id='+id;
          this.request(url).then(function(data){
            var json=data.body;
            if( json.status==1 ){
              //that.data.items.splice(ind, 1);
              that.pageClick(that.currentPage);
            }
          },function(response){
            console.info(response);
          })
        }
      },
      editeDoc:function(id){
        top.location.href = '/tzs/iadd?pid='+id;
      },
      dataDoc:function (id) {
        top.location.href = '/tzs/postdata?pid='+id;
      }
    }
  });
</script>