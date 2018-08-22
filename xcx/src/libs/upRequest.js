import wepy from 'wepy'
import util from './util'
import md5 from './md5'
import tip from './tip'

const TIMESTAMP = util.getCurrentTime()


// const apiGate = function(){

//     let env = wepy.$instance.globalData.env  //'prod' //-dev 或者 -test

//     if( env=='test' ) {
//       return 'http://tzs/'
//     } else if ( env=='demo' ) {
//       return 'https://demo.upimgs.cn/'
//     } else {
//       return 'https://upimgs.cn/'
//     }
// }

// const GO_TO_MEMBER = (back_delta,memberfunc) => {
//     wx.hideToast()
//     if( typeof memberfunc == 'function'){
//       memberfunc()
//     } else {
//         wepy.navigateTo({
//           url: "/pages/userinfo?goback=1"
//         })
//     }
//   return {}
// }

// const sleep = (ms)=> {
//   return new Promise(resolve => setTimeout(resolve, ms))
// }

// const upRequest = async(params = {}, url , needsession) => {
//     // if( needsession!==true && ( wepy.$instance.globalData.userme.sinfo == undefined || wepy.$instance.globalData.userme.sinfo.sessionid == undefined ) )
//     // {
//     //     console.log('sleep wait... sinfo')
//     //     //await sleep(250)
//     //     let zzz = await wepy.$instance.getGuestCode()
//     //     return upRequest(params,url,needsession)
//     // }
//     return doRequest(params,url,needsession)
// }

const sleep = (ms)=> {
  return new Promise(resolve => setTimeout(resolve, ms))
}

const getUrl = (url,forcesession=false) => {
    if( wepy.$instance.globalData.userkey )
    {
        var can_session_login = wepy.$instance.globalData.userkey.can_session_login || 0
        url = url+'?'
        if( forcesession || parseInt(can_session_login)==1 )
        {
            if( wepy.$instance.globalData.userkey.sessionid ) {
                //data.__session_id = wepy.$instance.globalData.userkey.sessionid
                url = url+'&__session_id=' + wepy.$instance.globalData.userkey.sessionid
            }
            if( wepy.$instance.globalData.userkey.gcode ) {
                //data.__gcode = wepy.$instance.globalData.userkey.gcode
                url = url+'&__gcode=' + wepy.$instance.globalData.userkey.gcode
            }
            if( wepy.$instance.globalData.userkey.openid ) {
                //data.openid = wepy.$instance.globalData.userkey.openid
                url = url+'&openid=' + wepy.$instance.globalData.userkey.openid
            }
        }
    }
    return url
}

const upRequest = async(params = {}, url , needsession) => {

    // if( needsession!==true && ( wepy.$instance.globalData.userme.sinfo == undefined || wepy.$instance.globalData.userme.sinfo.sessionid == undefined ) )
    // {
    //     console.log('sleep wait... sinfo')
    //     await sleep(250)
    //     return upRequest(params,url,needsession)
    // }

    let env = wepy.$instance.globalData.env  //'prod' //-dev 或者 -test
    let data = params.query || {};
    var originUrl = url

    url = getUrl(url,true)
    url = url+'&callfrom=xcxuprequest'


    // if(env=='prod')
    // {
    //     data.__c_sign = md5.hex_md5((TIMESTAMP + 'apis.upjiaju.com' + data.__session_id + data.__gcode).toLowerCase());
    //     data.__c_time = TIMESTAMP;
    // }

    //为了多应用做区别...
    data.appid = wepy.$instance.globalData.appid

   var showload = setTimeout( function () {
     wx.showLoading({
         title: '加载中',
         icon: 'loading'
     })
   } , 1000 )

    let res = await wepy.request({
        url: url,
        method: params.method || 'GET',
        data: data,
        header: { 'Content-Type': 'application/json' },
    }).then(
            res=>{
                if( res.data == undefined ){
                    console.log('无正常数据:::',url,res)
                    res.data = {status:0,msg:'网络错误,请稍后重试'}
                }
                wx.hideLoading()
                return res
            } ,
            res=>{
                console.log('网络错误:::',url,res)
                wx.hideLoading()
                if( res.errMsg ){
                    tip.confirm( res.errMsg,{},'网络错误,请稍后重试')
                }
            }
        )

    clearTimeout(showload);
    console.log('请求Api接口:'+url,data,res)

    if( res.data._ext_result ) {
        if( res.data._ext_result.needlogin==1 ){
            await wepy.$instance.getSession({forceRelogin:1})
            await sleep(500)
            return
            return upRequest(params , originUrl , needsession)
        }
        if( res.data._ext_result._alert_msg!=undefined ){
          wx.showModal({
            title: '重要提示',
            showCancel: false,
            content: res.data._ext_result._alert_msg,
            success: function (res) {
            }
          })
        }
        // if( res.data._ext_result.needrecheckuser==1 ){
        //     //return wepy.$instance.initUserLogin().then(function(){return upRequest(params, url)})
        //     wepy.$instance.initUserLogin()
        // }
    }
    return res
}


module.exports = {
    upRequest,getUrl
}
