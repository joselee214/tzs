import wepy from 'wepy'
import Promise from 'promise-polyfill'

/**
 * 提示与加载工具类
 */
export default class Tips {
  constructor() {
    this.isLoading = false
    this.use('promisify')
  }

  /**
   * 弹出提示框
   */
  static success(title, duration = 1000) {
    wx.showToast({
      title: title==undefined?'':title,
      icon: 'success',
      mask: true,
      duration: duration
    })
    if (duration > 0) {
      return new Promise((resolve, reject) => {
        setTimeout(() => {
          resolve();
        }, duration);
      });
    }
  }

  /**
   * 弹出确认窗口
   */
  static confirm(text, payload = {}, title = '确认',showCancel=true) {
    return new Promise((resolve, reject) => {
      wx.showModal({
        title: title==undefined?'':title,
        content: text==undefined?'':text,
        showCancel: showCancel==undefined?true:showCancel,
        success: res => {
          if (res.confirm) {
            resolve(payload)
          } else if (res.cancel) {
            reject(payload)
          }
        },
        fail: res => {
          reject(payload)
        }
      })
    })
  }


  /**
   * 错误框
   */

  static error(text, showCancel=false,title = '', payload = {}) {

    return new Promise((resolve, reject) => {
      wx.showModal({
        title: title==undefined?'':title,
        content: text==undefined?'':text,
        showCancel: showCancel==undefined?false:showCancel,
        success: res => {
          if (res.confirm) {
            resolve(payload)
          } else if (res.cancel) {
            reject(payload)
          }
        },
        fail: res => {
          reject(payload)
        }
      })
    })

    // wx.showToast({
    //   title: title==undefined?'':title,
    //   image: '../images/error.png',
    //   mask: true,
    //   duration: 1000
    // })
    // // 隐藏结束回调
    // if (onHide) {
    //   setTimeout(() => {
    //     onHide()
    //   }, 500)
    // }
  }

  static toast(title, onHide, icon = 'success',duration=500) {
    wx.showToast({
      title: title==undefined?'':title,
      icon: icon,
      mask: true,
      duration: duration
    })
    // 隐藏结束回调
    if (onHide) {
      setTimeout(() => {
        onHide()
      }, 500)
    }
  }

  /**
   * 警告框
   */
  static alert(title,duration=1500) {
    wx.showToast({
      title: title==undefined?'':title,
      image: '../images/alert.png',
      mask: true,
      duration: duration
    })
  }

  /**
   * 弹出加载提示
   */
  static loading(title = '加载中') {
    if (Tips.isLoading) {
      return
    }
    Tips.isLoading = true
    wx.showLoading({
      title: title==undefined?'':title,
      mask: true
    })
  }

  /**
   * 加载完毕
   */
  static loaded() {
    if (Tips.isLoading) {
      Tips.isLoading = false
      wx.hideLoading()
    }
  }



  static share(title, url, desc) {
    return {
      title: title==undefined?'':title,
      path: url==undefined?'':url,
      desc: desc==undefined?'':desc,
      success: function(res) {
        Tips.toast('分享成功')
      }
    }
  }
}

/**
 * 静态变量，是否加载中
 */
Tips.isLoading = false;
