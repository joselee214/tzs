/**
 * Ajax提示插件
 * author：陶留军
 */
(function($)
{
	$.fn.ajaxtip = function(options)
	{
		var defaults=
		{
			'msg' : '请稍等，正在提交...',
			'color' : '#ffffff',
			'bgcolor' : '#008000'
		};

		var op = $.extend(defaults, options);

		op.windowwidth = $(window).outerWidth();
		op.windowheight = $(window).outerHeight();
		op.documentwidth = $(document).width();
		op.documentheight = $(document).height();

		var body = $('body').eq(0);

		var dom0 = $('<div>');
		var dom1 = $('<div>');

		var _show = function(){
			body.css({
				'width' : op.windowwidth + 'px',
				'height' : op.windowheight + 'px',
				'overflow':'hidden'
			});

			dom0.css({
				'width': '100%',
				'height': op.documentheight + 'px',
				'background' : '#000000',
				'opacity': '0.1',
				'filter': 'alpha(opacity=10)',
				'position': 'absolute',
				'left': '0px',
				'top': '0px',
				'cursor': 'wait',
				'z-index': '998'
			}).appendTo(body);

			dom1.css({
				'position': 'absolute',
				'z-index': '999',
				'display': 'inline-block',
				'padding': '10px 100px',
				'height': '20px',
				'line-height': '20px',
				'cursor':'default',
				'background' : op.bgcolor,
				'color': op.color ,
				'box-shadow' : '0 0 5px rgba(0,0,0,0.3)'
			}).html(op.msg).appendTo($('body'));

			dom1.css({
				'left': (op.windowwidth - dom1.outerWidth())/2 +'px',
				'top': ($(document).scrollTop() + (op.windowheight - dom1.outerHeight() ) /2 ) + 'px'
			});
		};

		var _hide = function(){
			dom1.fadeOut(200,function(){
				body.css({
					'width' : 'auto',
					'height' : 'auto',
					'overflow':'visible',
					'cursor' : 'default'
				});
				dom1.remove();
				dom0.remove();
			});
		};

		$(document).ajaxComplete(function() {
			_hide();
		});
		_show();

		return {
			show : function(){_show();} ,
			hide : function(){_hide();}
		};
		
	};
}
)
(jQuery);