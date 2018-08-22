Vue.filter('momentTimestamp', function (value, formatString) {
  formatString = formatString || 'YYYY-MM-DD HH:mm:ss';
  return moment.unix(value).format(formatString);
});

Vue.prototype.request = function (url,datas={},method='get',header={}) {
  var that = this;
  return new Promise(function(resolve,reject){
    var _request;
    if( method=='get' ){
      _request = that.$http.get(url,{params:datas})
    } else if (method=='post'){
      _request = that.$http.post(url,datas,{emulateJSON: true})
    }
    _request.then(function (res) {
      var json = res.body;
      if(json._ext_result!=undefined && json._ext_result.needlogin==1 ){
        top.location.reload();
        reject(res);
      } else {
        resolve(res);
      }
    },function (res) {
      alert('网络问题,请重试!');
      reject(res);
    });
  });
};

Vue.component('j7mask',{
  data:function(){
    return {
      maskStyle:'',
      showImgSrc:'',
      msg:''
    }
  },
  template: '#j7-smallmask-template',
  methods:{
    handleClose: function () {
      this.showImgSrc = '';
      this.msg = '';
      this.$emit('j7maskclose',this.showImgSrc,this.msg);
    },
    close:function(){
      this.showImgSrc = '';
      this.msg = '';
    },
    handleClickText: function () {
      this.$emit('j7maskclicktext',this.showImgSrc,this.msg);
    },
    handleClickImg: function () {
      this.$emit('j7maskclickimg',this.showImgSrc,this.msg);
    },
    showMask:function(msg,imgSrc=''){
      this.maskStyle = 'height: 100%;';
      this.msg = msg;
      this.showImgSrc = imgSrc;
    }
  }
});

Vue.component('j7page', {
  props: {
    currentpage:{
      type: Number,
      default: 1
    },
    totalpage:{
      type: Number,
      default: 1
    },
    totalcount:{
      type: Number,
      default: 0
    },
    numspagesp:{
      type: Number,
      default: 2
    }
  },
  template: '#j7-pagination-template',
  computed:{
    previousPage:function () {
      return this.currentpage>1?this.currentpage-1:1;
    },
    nextPage:function () {
      return (this.currentpage<this.totalpage)?(this.currentpage+1):this.totalpage;
    },
    arrPages:function () {
      var start = this.currentpage-this.numspagesp < 1 ? 1 : this.currentpage-this.numspagesp;
      var end = this.currentpage+this.numspagesp > this.totalpage ? this.totalpage : this.currentpage+this.numspagesp;
      var length = end - start +1;
      var step = start - 1;
      var arr = Array.apply(null,{length:length}).map(function (v,i){step++;return step;});
      return arr;
    }
  },
  methods:{
    pageClick: function (nowp) {
      this.$emit('clickpage',nowp)
    }
  }
});