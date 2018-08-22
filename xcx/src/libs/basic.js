import wepy from 'wepy'
import api from './api'

export default class libsBasic extends wepy.mixin {


  data = {
    _go_url_click : false,
    styles:{},
    scheight:1000
  }


    onLoad () {
//      console.log('onLoad in libsBasic')
      wepy.$instance.getSession().then(res=>{
//        console.log('===========success getsession',res)
        this.onLoadM(res)
      },res=>{
       console.log('===========error getsession',res)
        this.onLoadM({})
      })
    }
    
    onShow () {
//      console.log('onShow in libsBasic')
    }

    onPullDownRefresh() {
      console.log('onPullDownRefresh')
      wx.stopPullDownRefresh()
    }

    scene_explode(option,frompage) {

        console.log( 'scene_explode' ,option, frompage )

        if( option && option.scene ) {
            var scene = decodeURIComponent(option.scene)
            var data = {}
            var scenelist = scene.split(';')
            for (var i = 0; i < scenelist.length; i++) {
                var keyvalue = scenelist[i].split(':')
                data[keyvalue[0]] = keyvalue[1]
            }

            console.log( 'scene_explode 1' ,data )

            if( data.__t != undefined ) {
              setTimeout(() => {
                api.setTraceCode({
                  query: data
                })
              }, 5000)
            }
            var url = ''
            if( data.gog ){
              url = '/pages/good_detail?gid='+data.gog
              data.gid = data.gog
            } else if( data.mid ){
              url = '/pages/shop_index?mainid='+data.mid
              data.mainid = data.mid
            } else if( data.goc ){
              url = '/pages/shop_coupon?cid='+data.goc
              data.cid = data.goc
            } else if(data.url){
                url = data.url
            }
            if( frompage=='index' || frompage=='ad' ){
                data.isgo = 1
                this.go_url(url,data.t)
                return data
            }
            return data
        }
        return option
    }
    go_url(url,type="navigate"){
        if( type=='navigate' || type=='n' || type==undefined|| type=='') {
            var pages = getCurrentPages()
            if(pages.length==4) {
                console.log('!!!!!!!!!!!!!!!!!!!!!!!!!')
            }
            wepy.navigateTo({url: url})
        } else if(type=='redirect' || type=='r') {
            wepy.redirectTo({url: url})
        } else if(type=='switchTab'||type=='s') {
            wepy.switchTab({url: url})
        } else if(type=='reLaunch'||type=='rl') {
            wepy.reLaunch({url: url})
        } else if(type=='navigateBack'||type=='g') {
            wepy.navigateBack({delta: url})
        } else if(type=='checkbackorswitchTab') {
          var pages = getCurrentPages()
          if( pages.length>1 ){
            wepy.navigateBack({delta: 1})
          } else {
            wepy.switchTab({url: url})
          }
        } else {
            wepy.navigateBack({delta: url})
        }
    }

  methods = {
    phonecall(phone){
      wx.makePhoneCall({
        phoneNumber: phone
      })
    },
    _go_url(url,type="navigate") {
        this.go_url(url,type)
    },
    onShareAppMessage: function (res) {
      return {
        title: '通知说',
        path: '/pages/docs'
      }
    },
    showImage(e) {
      var current = e.target.dataset.src
       wepy.previewImage({
           urls: [current]
         })
    }
  }
}
