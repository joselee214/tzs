<?php
/**
* @var $this tzs_index_docs_Action
* @var $_ tzs_index_docs_Action
* @see tzs_index_docs_Action
*/
?>

<?php
include_view('tzs/headtabbar.php',['now'=>'docs'])
?>

<style>

    .zan-loadmore--nodata{
        margin: 120px auto;
    }
    .zan-panel {
        margin-top:0;
    }

    .post_item{
        cursor: pointer;
    }
    .zan-icon {
        font-size: 18px;
    }
    .zan-card{
        padding: 15px 20px;
    }
    .doc_content_title{
        margin-right: 0px;
        max-height: 40px;
    }
    .doc_content_area{
        margin-right: 0px;
        max-height: 100px;
        overflow: hidden;
    }
    .zan-card__thumb{
        width: 72px;
    }
    .zan-card__detail{
        margin-left: 85px;
        margin-right: 25px;
    }
    .doc_mark_row .zan-icon{
        font-size: 14px;
    }
    .doc_mark{
        margin:0 36px 0 4px;
    }
    .zan-card__right-col{
        margin-top:40px;
    }
    .avatar{
        width: 16px;
        height: 16px;
    }
    .nickname{
        font-size: 15px;
        margin-left: 24px;
    }
</style>

<div id="app">

    <div v-if="data.items.length>0" v-cloak>
        <div class="data-list">

            <div class="zan-card post_item" v-for="(item,index) in data.items" v-on:click="gotopost(item.id)">
                <div class="zan-card__thumb backgroundimg" :style="'background-image: url('+item.logo+');'">
                </div>
                <div class="zan-card__right-col">
                    <div class="zan-icon zan-icon-arrow" style="color: #4b0;"></div>
                </div>
                <div class="zan-card__detail">
                    <div class="zan-card__detail-row" v-if="item.nickname!=''">
                        <img class="zan-card__img avatar" :src="item.avatar" mode="aspectFit" />
                        <span class="nickname">{{item.nickname}}</span>
                    </div>
                    <div class="zan-card__detail-row" v-if="item.title!=''">
                        <div class="zan-card__left-col zan-ellipsis--l2 doc_content_title">
                            {{item.title}}
                        </div>
                    </div>
                    <div class="zan-card__detail-row">
                        <div class="zan-card__left-col zan-c-gray">{{item.created_at}}</div>
                    </div>

                    <div class="zan-card__detail-row zan-c-gray-darker">
                        <div class="zan-card__left-col zan-ellipsis--l3 doc_content_area">
                            {{item.content}}
                        </div>
                    </div>

                    <div class="zan-card__detail-row doc_mark_row">
                        <div class="zan-icon zan-icon-contact"  style="color: #4b0;"></div>
                        <span class="zan-c-gray doc_mark">{{item.viewtimes}}</span>
                        <div class="zan-icon zan-icon-chat"  style="color: #4b0;" v-if="item.allowReply==1"></div>
                        <span class="zan-c-gray-dark doc_mark" v-if="item.allowReply==1">{{item.replytimes}}</span>
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
      currentPage:1,
      msg:''
    },
    created:function () {
//            this.pageClick(1);
    },
    methods: {
      pageClick:function (page=1) {
        var that = this;
        var url = '/tzs/docs/data?page='+page;
        jQuery('html,body').animate({scrollTop:0},'slow');
        this.request(url).then(function(data){
          console.log('getPage:',page,url,data.body);
          var json=data.body;
          that.data=json;
        },function(response){
          console.info(response);
        })
      },
      gotopost:function (id) {
        top.location.href = '/tzs/d?pid='+id;
      }
    }
  });
</script>