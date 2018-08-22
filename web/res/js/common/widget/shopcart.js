(typeof(widget) !=='object' ) && (widget = {});
/*购物车挂件*/
widget.shopcart = {
	/*初始化*/
	dom: {} ,
	init : function(){
		var _this = this;
		$.get( DOMAIN.RES_DOMAIN+'/res/css/common/widget/shopcart.css' , function(){
			$('<link rel="stylesheet" href="'+DOMAIN.RES_DOMAIN+'/res/css/common/widget/shopcart.css" />').appendTo($('head'));
			window.setTimeout( function(){
				var html =
					'<div class="widget_shopcart" id="jquery_widgetshopcart">' +
						'<div class="div1">' +
						'<div class="div1_bg">' +
						'<p><a href="'+DOMAIN.USERFRONT_DOMAIN+'/shopcart/" title="查看购物车">购物车 <span>0</span></a></p>' +
						'</div>' +
						'<div class="div1_box clearfix">' +
						'<div class="div2_1">' +
						'<a href="'+DOMAIN.USERFRONT_DOMAIN+'/shopcart" class="a1">去购物车结算</a>' +
						'</div>' +
						'<div class="div2_2">' +
						'正在加载' +
						'</div>' +
						'<div class="div2_1" style="padding-top:10px;">' +
						'<a href="'+DOMAIN.USERFRONT_DOMAIN+'/shopcart/" class="a1">去购物车结算</a>' +
						'</div>' +
						'</div>' +
						'</div>' +
						'</div>';
				$(html).appendTo( $('body') );

				_this.dom.main = $('#jquery_widgetshopcart');
				_this.dom.bg = $('.div1_bg' , _this.dom.main).eq(0);
				_this.dom.box = $('.div1_box' , _this.dom.main).eq(0);
				_this.dom.box.data('top' , _this.dom.box.css('top') );

				_this.dom.main.on('mouseenter' , function(){
					_this.dom.box.css({
						'box-shadow' : '-2px -2px 5px rgba(0,0,0,0.2)'
					});
					_this.dom.main.animate({
						'right' : 5
					} , 300 );
				});

				_this.dom.bg.one('click' , function(){
					_this.list( function(){
						_this.dom.bg.trigger('click');
					} );
				});

				window.onresize = function(){
					_this.event();
				};
				_this.list();
			} , 100);
		});
	} ,
	/*绑定事件*/
	event : function(){
		var _this = this;
		var windowheight = $(window).height();
		var bgheight = _this.dom.bg.outerHeight();
		var _height1 = _this.dom.box.oldheight;
		var maxtop = windowheight * 2 / 3;
		var top = Math.min( _height1 , maxtop) + bgheight - 10;
		_this.dom.bg.off('click').on('click' , function(e){
			if( _this.dom.box.data('anim_finished') !== 1 )
			{
				_this.dom.box.animate({
					'top' : - top ,
					'height' : Math.min( _height1 , windowheight * 2 / 3 )
				} , 100 , function(){
					$(this).data('anim_finished' , 1);
				});
			}
			else
			{
				_this.dom.box.animate({
					'top' : _this.dom.box.data('top')
				} , 100 , function(){
					$(this).data('anim_finished' , 0);
				});
			}
			e.stopPropagation();
		});
		_this.dom.main.off('click').on('click' , function(e){
			e.stopPropagation();
		});

		$(document).off('click').on('click' , function(e){
			_this.dom.box.data('anim_finished' , 1);
			_this.dom.box.css({
				'box-shadow' : 'none'
			});
			_this.dom.main.animate({
				'right' : -195
			} , 300 );
			_this.dom.bg.trigger('click');
		});
		$('.dropMeFromShopCartA' , _this.dom.box).off('click').on('click' , function(){
			_this.remove( $(this).parent('div') , $(this).parent('div').attr('data-gid') );
		});
	} ,
	/*添加到购物车*/
	add : function( gid , gnum , imgurl , domobj ){
		var _this = this;
		if( !gid )
		{
			return {statu:false , error : '商品编号不能为空'};
		}
		if( !gnum )
		{
			return {statu:false , error : '商品数量不能为空'};
		}
		if( domobj.length )
		{
			var loadDom = common.wait(domobj , '正在将商品放购物车里 . . .');
		}
		$.getJSON(
			DOMAIN.USERFRONT_DOMAIN + '/outer/cart/~add/?callback=?' ,
			{ buyid : gid , buynum : gnum } ,
			function(req){
				loadDom.remove();
				if( req.statu === true )
				{
					_this._anim( imgurl , domobj );
					_this._relist( req.data );
				}
				else
				{
					common.popup(req.error);
				}
			}
		);
		return false;
	} ,
	/*移除出购物车*/
	remove : function( domobj , gid ){
		var _this = this;
		if( !gid )
		{
			return {statu:false , error : '商品编号不能为空'};
		}
		domobj = $(domobj);
		if( domobj.length )
		{
			var loadDom = common.wait(domobj , '正在删除');
		}
		$.getJSON(
			DOMAIN.USERFRONT_DOMAIN + '/outer/cart/~del/?callback=?' ,
			{ buyid : gid } ,
			function(req){
				loadDom.remove();
				if( req.statu === true )
				{
					_this.dom.box.data('anim_finished' , 0);
					_this._relist( req.data );
					_this.dom.bg.trigger('click');
				}
				else
				{
					common.popup(req.error);
				}
			}
		);
	} ,
	/*获取购物车列表*/
	list : function( callback ){
		var _this = this;
		$.getJSON( DOMAIN.USERFRONT_DOMAIN +'/outer/cart/~getDetail?callback=?' , function(req){
			_this._relist( req );
			callback && callback();
		});
	} ,
	/*重排购物车*/
	_relist : function( data ){
		var _this  = this;
		$('.div2_1' , _this.dom.box).hide();
		var html = '';
		var _count = 0, _total_count = 0, _total_price=0;
		for( var i in data )
		{
				html += '<p class="p1" data-fid="'+data[i]['fid']+'">' +
						'<a href="' + '/shop-'+data[i]['fid']+'.htm" target="_blank">'+data[i]['fname']+' </a>' +
						'</p>';
				for( var i2 in data[i]['goods'] )
				{
					var good = data[i]['goods'][i2];
                    var good_url = '/goods-' + good['gid'] + '.htm';
                    html += '<div class="div2_2_1" data-gid="'+good['gid']+'">'
						+ '<div class="div2_2_1_1">'
						+ '<a href="'+good_url+'" target="_blank"><img src="'+common.realimg(good['thumb'] , 50 , 50)+'" /></a>'
						+ '</div>'
						+ '<div class="div2_2_1_2">'
						+ '<a href="'+good_url+'" target="_blank">'+good['title']+'<br/>'+good['sku']+'</a>'
						+ '</div>'
						+ '<div class="div2_2_1_3">'
						+ '¥<span class="cartprice">'+good['price']+'</span>'
						+ '*<span>'+good['nums']+'</span>'
						+ '</div>'
						+ '<a href="javascript:;" class="a1 dropMeFromShopCartA" title="从购物车扔掉">x</a>'
						+ '</div>';
					_total_price += good['nums']*good['price'];
					_total_count += parseInt(good['nums']);
					_count++;
				}
		}
		if( html )
		{
			$('.div2_1' , _this.dom.box).eq(0).show();
			if( _count > 4 )
			{
				$('.div2_1' , _this.dom.box).eq(1).show();
			}
		}
		else
		{
			html = '<p style="text-align: center;line-height: 30px;">购物车还是空的，先逛逛吧！</p>';
		}
		$('.div2_2' , _this.dom.box).html( html );
		$('p a span' , _this.dom.bg).text( _total_count + '-' +_count + '  ¥ '+ _total_price.toFixed(2) );
		$('.shop_cart_btn span').text(_total_count);
		$('.shop_cart_btn span').show();
		//重置高度
		_this.dom.box.css('height' , 'auto');
		_this.dom.box.oldheight = _this.dom.box.height();
		_this.event();
	} ,
	/*添加到购物车的动画效果*/
	_anim : function( img , domObj ){
		var _this = this;
		var dom = $('<div></div>');
		dom.html('<img src="' + img  + '" style="width:50px;" />');
		dom.css({
			'position' : 'absolute' ,
			'z-index' : 1 ,
			'overflow' : 'hidden' ,
			'width' : 50 ,
			'height' : 50 ,
			'line-height' : '50px' ,
			'border' : '3px solid #58BC00' ,
			'left' : domObj.offset().left ,
			'top' : domObj.offset().top
		});
		dom.appendTo( $('body') );
		var left = _this.dom.bg.offset().left + _this.dom.bg.outerWidth() / 2 - dom.outerWidth() / 2;
		if( left > $(window).width() - dom.outerWidth() )
		{
			left = $(window).width() - dom.outerWidth();
		}
		dom.animate({
			'left' : left ,
			'top' : _this.dom.bg.offset().top - dom.outerHeight()
		} , 500 );
		dom.animate({
			'top' : _this.dom.bg.offset().top + dom.outerHeight()
		} , 100 , function(){
			dom.remove();
		});
	}
};

$(function(){
	widget.shopcart.init();
});