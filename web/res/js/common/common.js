(typeof(common) !=='object' ) && (common = {});

/*全局 JS 方法 */
/*弹窗*/
common.popup = function( str , autoclose , strhead ,domcss){
	//window.alert( str ); /*TODO 更复杂的效果*/

	if(typeof autoclose == 'undefined') {
		autoclose = false;
	}

	var popupBack = $('<div class="global_bg_black_80"></div>');

	popupBack.css({
		'width' : '100%',
		'height' : '100%',
		'position' : 'fixed',
		'z-index' : 200,
		'cursor' : 'pointer',
		'left' : 0,
		'top' : 0
	});

	var popupDom = $('<div></div>');

	popupDom.css({
		'width' : '400px',
		'position' : 'fixed',
		'z-index' : 999,
		'left' : ($(window).width() /2 ) - 200,
		'background' : "#fff",
		'border' : "1px solid #eee",
		'box-shadow' : "-1px -1px 10px rgba(0,0,0,0.8)"
	});
	if( domcss )
        popupDom.css(domcss);

	popupBack.appendTo( $('body') );
	popupDom.appendTo( $('body') );

	$( (strhead?('<h3 style="font-size: 14px;font-weight: bold;color: #3d8901;font-family:Arial,Tahoma,Verdana;border-bottom: 1px dotted #848484;padding:4px 12px;">'+strhead+'</h3>'):'') +
		'<div style="padding:10px;">' + str +
		'<div style="margin-top:10px;text-align: center;">' +
		'<button class="global_btn">关闭</button><span></span>' +
		'</div>' +
		'</div>').appendTo( popupDom );
		popupDom.css({
		'top' : ($(window).height() / 2 ) - (popupDom.outerHeight() / 2),
        'left' : ($(window).width() /2 ) - (popupDom.outerWidth() / 2)
	});

	var closebtn = $('button:last' , popupDom);
	var tipbox = closebtn; //closebtn.next('span');

	closebtn.on('click' , function() {
		popupBack.fadeOut(200,function(){$(this).remove()});
		popupDom.fadeOut(200,function(){$(this).remove()});
		$(document).unbind('keydown');
	});

	popupBack.on('click' , function(){
		closebtn.trigger('click');
	});

	$(document).bind('keydown', function(event) {
		switch(event.keyCode) {
			case 27:
				closebtn.trigger('click');
		}
	});

	if (autoclose != false) {
		var popupTime = autoclose===true?5:autoclose;
		var popupCountdown = window.setInterval(function() {
			if (popupTime > 1) {
				popupTime--;
				tipbox.text("关闭("+popupTime+")");  //popupTime+"秒后自动关闭"
			} else {
				window.clearInterval(popupCountdown);
                closebtn.trigger('click');
			}
		},  1000);
	}
    return [closebtn,popupDom,popupBack,tipbox];
};
/*一个等待效果：悬浮在元素之上的点点条效果*/
common.wait = function( domobj , str ){
	if( 'undefined' === typeof(DOMAIN)   || 'undefined' === typeof(DOMAIN.RES_DOMAIN) )
	{
		common.popup('请检查DOMAIN.RES_DOMAIN');
		return false;
	}
	('undefined' === typeof(str)) && (str = '请稍等 . . .');
	var loadDom = $('<div></div>');
	loadDom.css({
		'width' : domobj.outerWidth() ,
		'height' : domobj.outerHeight() ,
		'position' : 'absolute' ,
		'z-index' : 100 ,
		'left' : domobj.offset().left ,
		'top' : domobj.offset().top
	});
	loadDom.append('<div style="width:100%;height:100%;position:absolute;left:0;top:0;background:#000;opacity:0.2;filter:alpha(opacity=20);z-index:1;"></div>');
	loadDom.append('<div title="'+str+'" style="width:100%;height:100%;line-height:'+loadDom.outerHeight()+'px;position:absolute;left:0;top:0;z-index:2;text-align: center;cursor: pointer;">' +
		'<img src="' + DOMAIN.RES_DOMAIN +'/res/img/common/load.gif" alt="" />' +
		'</div>');
	loadDom.appendTo( $('body') );
	loadDom.on('click' , function(){
		window.alert(str);
		return false;
	});
	return loadDom;
};
/*js的+-x/*/
common.math = {
	s : 10000 , /*精确度*/
	jia : function(){
		var sum = 0;
		for( var i in arguments )
		{
			sum += Math.round(arguments[i] * this.s) / this.s;
		}
		sum = Math.round(sum * this.s) / this.s;
		return sum;
	} ,
	jian : function(){
		var sum = arguments[0];
		for( var i in arguments )
		{
			sum -= Math.round(arguments[i] * this.s) / this.s;
		}
		sum += Math.round(arguments[0] * this.s) / this.s;
		sum = Math.round(sum * this.s) / this.s;
		return sum;
	} ,
	cheng : function(){
		var sum = 1;
		for( var i in arguments )
		{
			sum *= Math.round(arguments[i] * this.s) / this.s;
		}
		sum = Math.round(sum * this.s) / this.s;
		return sum;
	} ,
	chu : function(){
		var sum = arguments[0];
		for( var i in arguments )
		{
			sum /= Math.round(arguments[i] * this.s) / this.s;
		}
		sum *= Math.round(arguments[0] * this.s) / this.s;
		sum = Math.round(sum * this.s) / this.s;
		return sum;
	}
};

common.getRealImgPath = function(url, type) {
	var ext = url.substr(-3,3);
	var fileext = ext.toLowerCase();
	// switch(fileext) {
	// 	case "jpg":
	// 	case "gif":
	// 	case "png":
	// 		return url.slice(0,-4) + "_" +  type + "." + ext;
	// }
	return url;
};
/* 返回裁剪后的图片地址*/
common.realimg = function(url , w , h ){
		if( !url )
			return DOMAIN.SITE_CONTENTDOMAIN+'/img/public/avatar.png';
    if( url.substr(0,4)!='http' )
			return DOMAIN.SITE_CONTENTDOMAIN+url;
    return url;
	//var dot = url.lastIndexOf('.');
	//return url.substr( 0 , dot ) + '_' + w + '_' + h + url.substr(dot);
};

common.toggle = function (tg,ck,init,dv) {
	var ckv = dv!=undefined?dv:jQuery.cookie(ck);
	if( init===true )
	{
		if( ckv==1 )
            jQuery(tg).show();
		else
            jQuery(tg).hide();
	}
	else
	{
		if( ckv==1 )
		{
            jQuery(tg).hide();
            jQuery.cookie(ck,0,{path:'/'});
		}
		else
		{
            jQuery(tg).show();
            jQuery.cookie(ck,1,{path:'/'});
		}
	}
};

common.jsonp = function (url,data,fun) {
  $.postJSON(url,data,function (res) {
  	if( res.scode=='login' && res.msg )
		{
      common.popup(res.msg,false,res.error);
		}
		else
		{
      fun && fun(res);
		}

  });
};

common.getCoupon = function (cid,func) {
	if (cid==0 || cid==undefined)
		return;
	var url = DOMAIN.USERFRONT_DOMAIN+'/coupon/get?cid='+cid+'&callback=?';
  common.jsonp(url,{},function (res) {
    var r = common.popup(res.msg);
    if( res.status==1 )
    {
      r[0].click(function () {
        func && func();
      });
    }
  });
};