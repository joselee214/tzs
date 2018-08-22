
<?php
if(RuntimeData::registry('SYS_ENV') != 'prod')
    echo '<script type="text/javascript" src="http://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    <script type="text/javascript" src="http://cdn.jsdelivr.net/npm/vue-resource"></script>
    <script type="text/javascript" src="http://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>';
else
    echo '<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/vue"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/vue-resource"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>';
//
?>

<script type="text/x-template" id="j7-pagination-template">
    <div>
        <div class="page_div">
            <a :class="currentpage>1?'page_pp':'page_pp page_disable'" v-on:click="pageClick(previousPage)">前一页</a>

            <a v-for="nowp in arrPages" :class="currentpage==nowp?'page_sp page_thisp':'page_sp'" v-on:click="pageClick(nowp)">{{nowp}}</a>

            <a :class="currentpage<totalpage?'page_sp page_np':'page_np page_disable'" v-on:click="pageClick(nextPage)">下一页</a>
            <span class="page_total_text">共{{totalcount}}</span>
        </div>
    </div>
</script>
<script type="text/x-template" id="j7-smallmask-template">
    <div class="j7small_maskbox a" v-if="showImgSrc!=''||msg!=''" :style="maskStyle" v-on:click="handleClose">
        <div>
            <div v-html="msg" v-if="msg!=''" v-on:click.stop="handleClickText"></div>
            <div v-if="showImgSrc">
                <img :src="showImgSrc" v-on:click.stop="handleClickImg">
            </div>
        </div>
    </div>
</script>
<script type="text/javascript" src="/res/js/vue/vueplus.js"></script>