import wepy from 'wepy'
import {
  upRequest,getUrl
} from './upRequest'

const apiGate = function(){

    let env = wepy.$instance.globalData.env  //'prod' //-dev 或者 -test

    if( env=='test' ) {
      return 'http://tzs/'
    } else if ( env=='demo' ) {
      return 'https://demo.upimgs.cn/'
    } else {
      return 'https://upimgs.cn/'
    }
}

const datatest = (params) => upRequest(params, apiGate() + "wx/datatest")
//微信的jscode换取sessionKey
const wxJsCode2Session = (params) => upRequest(params, apiGate() + "apis/wxsession")
const updateUserInfo = (params) => upRequest(params, apiGate() + 'apis/wxsession/updateUserInfo')
const doPost = (params) => upRequest(params, apiGate() + 'apis/posts/doPost')
const deletePost = (params) => upRequest(params, apiGate() + 'apis/posts/delete')
const getMyDocs = (params) => upRequest(params, apiGate() + 'apis/posts')
const getOneDoc = (params) => upRequest(params, apiGate() + 'apis/posts/getOneDoc')

const replys = (params) => upRequest(params, apiGate() + 'apis/posts/replys')
const addReply = (params) => upRequest(params, apiGate() + 'apis/posts/addReply')
const deleteReply = (params) => upRequest(params, apiGate() + 'apis/posts/deleteReply')
const viewlogs = (params) => upRequest(params, apiGate() + 'apis/posts/viewlogs')
const getMyReadDocs = (params) => upRequest(params, apiGate() + 'apis/posts/getMyReadDocs')
const topReply = (params) => upRequest(params, apiGate() + 'apis/posts/topReply')

const getTempUpload = (params) => upRequest(params, apiGate() + 'apis/xcxupload/getTempUpload')
const deleteTempUpload = (params) => upRequest(params, apiGate() + 'apis/xcxupload/deleteTempUpload')
const uploadimgurl = (params) => getUrl( apiGate() + 'apis/xcxupload/upload',true)  //上传地址
const downloadfile = (params) => getUrl( apiGate() + 'apis/xcxupload/download',true) //下载地址
const docdwebpage = (params) => getUrl( apiGate() + 'tzs/d') //下载 黏贴的地址

const uploadwebview = (params) => getUrl( apiGate() + 'apis/xcxupload',true) //webview

const shareInfo = (params) => upRequest(params, apiGate() + "apis/shareInfo")
const signUp = (params) => upRequest(params, apiGate() + 'apis/signUp/signUp')
const deleteSign = (params) => upRequest(params, apiGate() + 'apis/signUp/deleteSign')

const postdata = (params) => upRequest(params, apiGate() + 'apis/postdata')
const switchSign = (params) => upRequest(params, apiGate() + 'apis/posts/switchSign')
const postSigndataUrl = (params) => getUrl( apiGate() + 'tzs/postdata/sign',true)  //报名
const postGroupdataUrl = (params) => getUrl( apiGate() + 'tzs/postdata/group',true) //webview

module.exports = {
  datatest,apiGate,
  wxJsCode2Session,updateUserInfo,doPost,deletePost,getMyDocs,getOneDoc,
  replys,addReply,deleteReply,viewlogs,getMyReadDocs,topReply,
  uploadwebview,getTempUpload,deleteTempUpload,uploadimgurl,downloadfile,docdwebpage,
  shareInfo,signUp,deleteSign,
  postdata,switchSign,postGroupdataUrl,postSigndataUrl
}