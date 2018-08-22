/*
 * Author:陶留军
 * 图文横向列表走马灯插件
*/
(function($){
$.fn.listScroll = function(options){
	var defaults = { //缺省option
		'parent' : null , //所有元素所在的父元素
		'prev' : null , //往左DOM
		'next' : null , //往右DOM
		'autotime' : null , //如果设置了该属性，则自动运行走马灯效果
		'speed' : 500 , //一次滚动的效果时间
		'width' : null , //容器宽度
		'step' : null , //一次滚动效果的长度
		'callback' : function(){} //一次滚动效果后的回调
	};

	var op = $.extend(defaults, options);

	if( !op.prev || !op.next || !op.width || !op.step )
	{
		window.alert("prev,next,width,step参数不能为空");
		return false;
	}

	return this.each(function(){
		var $this = $(this);

		/**
		 * 初始化
		 * @private
		 */
		var _init = function(){
			op.oldDom = $this; //存储原始对象
			op.oldDomLength = op.oldDom.children('li').length;
			op.oldDomSimpleWidth = op.oldDom.children('li').eq(0).outerWidth(true);
			op.oldDomWidth = op.oldDomSimpleWidth * op.oldDomLength;
			op.oldDom.css({
				'float' : 'left' ,
				'position' : 'initial'
			}).width(op.oldDomWidth);

			op.div = $('<div></div>');
			op.div.css({
				'position' : 'absolute' ,
				'z-index' : $this.css('z-index') ,
				'left' : 0 ,
				'top' : $this.css('top') ,
				'display' : 'block' ,
				'height' : $this.height()
			});

			op.div.appendTo( $this.parent() );
			$this.remove();

			_pad();
			_event();

			op.autostatu = true;
			_auto();

		};

		/**
		 * 绑定事件
		 * @private
		 */
		var _event = function(){
			op.next.on('click' , function(){
				if( op.next.data('animating') )
				{
					return false;
				}
				op.next.data('animating' , true);
				_anim( 'right' , function(){
					op.next.removeData('animating');
					_auto();
				} );
				return false;
			});
			op.prev.on('click' , function(){
				if( op.prev.data('animating') )
				{
					return false;
				}
				op.prev.data('animating' , true);
				_anim( 'left' , function(){
					op.prev.removeData('animating');
				} );
				return false;
			});
			op.parent.on('mouseenter' , function(){
				op.autostatu = false;
				_auto();
			}).on('mouseleave' , function(){
				op.autostatu = true;
				_auto();
			});
		};

		var _auto = function(){
			window.clearTimeout( op.timeout );
			if( op.autotime && op.autostatu )
			{
				op.timeout = window.setTimeout(function(){
					op.next.removeData('animating');
					op.next.trigger('click');
				} , op.autotime )
			}
		};

		/**
		 * 动画过程
		 * @param s
		 * @param _callback
		 * @private
		 */
		var _anim = function( s , _callback ){
			// s = right | left
			op.div.animate({
				'left' : (s==='left' ? '+' : '-') + '=' + op.step + 'px'
			} , op.speed , function() {
				_pad(_callback );
			} );
		};

		/**
		 * 补充左右元素以满足下一次走马灯
		 * @private
		 */
		var _pad = function( _callback ){
			undefined === _callback && (_callback = function(){});
			if( op.div.position().left > 0 )
			{
				op.div.width( op.div.width() + op.oldDomWidth );
				op.div.prepend( op.oldDom.clone(true).attr('data-rand' , Math.random() ).fadeIn() );
				op.div.css({
					'left' : '-=' + op.oldDomWidth +'px'
				});
			}
			if( op.div.width() + op.div.position().left < op.width )
			{
				op.div.width( op.div.width() + op.oldDomWidth );
				op.div.append( op.oldDom.clone(true).attr('data-rand' , Math.random() ).fadeIn() );
			}
			if( op.div.position().left < -op.oldDomWidth * 2)
			{
				var ii = Math.ceil( Math.abs(op.div.position().left) / op.oldDomWidth );
				op.div.children('ul').slice( 0 , ii - 2 ).remove();
				op.div.width( op.div.width() - (ii-2) * op.oldDomWidth);
				op.div.css({
					'left' : '+='+((ii-2) * op.oldDomWidth)+'px'
				});
			}
			if( (op.div.position().left + op.div.width() ) > op.oldDomWidth * 3)
			{
				var ii = Math.ceil( Math.abs(op.div.position().left + op.div.width()) / op.oldDomWidth );
				var currentLength = op.div.children('ul').length;
				op.div.children('ul').slice( currentLength - ii + 2 ).remove();
				op.div.width( op.div.width() - ( ii - 2) * op.oldDomWidth);
			}
			_callback();
			op.callback();
		};

		_init();

	});
};
})(jQuery);