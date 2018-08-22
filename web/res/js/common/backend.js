(typeof(backendjs) !=='object' ) && (backendjs = {});


backendjs.sendCoupon = function (cid,uid) {
  var url = '/promotion/couponsend/?cid='+cid+'&uid='+uid;
  $.get(url,{},function (res) {
    log(res);
    var r = common.popup(res);
  });
};