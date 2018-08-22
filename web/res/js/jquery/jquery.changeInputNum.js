/*
 * Author:陶留军
 * 按钮点击式的 数量选择器
*/
(function($){
$.fn.changeInputNum = function(options){
	var defaults = { //缺省option
		width : 20 , //宽度
		min: null , //允许的最小值，null表示不限制
		max: null , //允许的最大值，null表示不限制
		reset: true , //合法化重置value
		time : 100 , //鼠标按下后的持续时间后开始递增递减数量
		callback : function(){}  //点击后回调
	};
	var op = $.extend(defaults, options);

	return this.each(function(){
		var $this = $(this);
		$('div.' + $(this).data('fnkey_inputNum')).remove();
		var _temp = { min: op.min , max : op.max };
		if( op.min === null && $this.attr('min') !== '' && $this.attr('min') != undefined )
		{
			_temp.min = parseInt($this.attr('min'));
		}
		if( op.max === null && $this.attr('max') !== '' && $this.attr('max') != undefined )
		{
			_temp.max = parseInt($this.attr('max'));
		}

		_temp.min = Math.min(_temp.min , _temp.max);

		var offset = $this.offset();
		_temp.top = offset.top + parseInt($this.css('border-top-width'));
		_temp.left = offset.left + $this.outerWidth() - op.width - parseInt($this.css('border-left-width')) -1 ;
		_temp.height = $this.innerHeight();

		var _init = function(){
			$this.attr({
				'autocomplete' : 'off' ,
				'type' : 'text'
			});
			var _timeout1;
			$this.on('keyup blur' , function(){
				window.clearTimeout(_timeout1);
				_timeout1 = window.setTimeout(function(){
					_reset();
				} , 500 );
			});
			_reset();

			var div = _temp.div = $('<div></div>');
			var _randClass = "inputNum_"+ Math.floor(Math.random() * 100000000);
			div.addClass(_randClass+ ' jquery_inputNum');
			$this.data('fnkey_inputNum' , _randClass);
			div.html('<span onselectstart="javascript:return false;">+</span><span onselectstart="javascript:return false;">-</span>');
			div.css({
				'position' : 'absolute' ,
				'z-index' : '1' ,
				'left' : _temp.left + 'px' ,
				'top' : _temp.top + 'px' ,
				'width' : op.width + 'px' ,
				'height' : _temp.height+ 'px',
				'overflow' : 'hidden',
				'border-left' : '1px solid #ddd'
			});
			div.children('span').css({
				'position' : 'relative' ,
				'z-index' : '1',
				'height' : Math.ceil( ( div.innerHeight() ) / 2 ) + 'px' ,
				'line-height' : Math.ceil( (div.innerHeight() ) / 2 ) + 'px' ,
				'width' : '100%' ,
				'display' : 'block' ,
				'overflow' : 'hidden' ,
				'text-align' : 'center' ,
				'font-size' : '16px' ,
				'font-family' : 'Arial' ,
				'color' : '#666666' ,
				'background' : '#f9f6f3 -webkit-gradient(linear,left top,left bottom,color-stop(0,#fff),color-stop(0.5,#eee),color-stop(1,#fff))',
				'cursor' : 'pointer'
			}).on('mouseenter' ,function(){
				$(this).css({
					'color' : '#f30'
				});
			}).on('mouseleave' , function(){
				$(this).css({
					'color' : '#666'
				});
			});
			div.children('span:eq(0)').css({
				'border-bottom' : '1px solid #ddd' ,
				'height' : '-=1px'
			});
			div.appendTo($('body'));
			_bind();
		};

		var _bind = function(){
			var mm = op.time;
			var div = _temp.div;
			var _timeout;
			var _animate;
			var _changevalue = function(t){
				if( _animate === false )
				{
					return false;
				}
				var val = $this.val();
				if( t === '+' )
				{
					$this.val( Math.round((++val) * 100) / 100 );
				}
				else if( t === '-' )
				{
					$this.val( Math.round((--val) * 100) / 100 );
				}
				_reset();
				_timeout = window.setTimeout(function(){
					_changevalue(t);
				} , mm );
			};
			var _changvalue_clear = function(){
				_animate = false;
				window.clearTimeout(_timeout);
			};
			div.children('span').eq(0).on('mousedown' , function(){
				_animate = true;
				_changevalue('+');
			}).on('mouseup mouseleave' , function(){
				_changvalue_clear();
			});
			div.children('span').eq(1).on('mousedown' , function(){
				_animate = true;
				_changevalue('-');
			} ).on('mouseup mouseleave' , function(){
				_changvalue_clear();
			});
			$this.on('keydown' , function(e){
				_animate = true;
				if(e.which === 38)
				{
					_changevalue('+');
					return false;
				}
				else if(e.which === 40)
				{
					_changevalue('-');
					return false;
				}
			}).on('keyup blur' , function(e){
				_changvalue_clear();
			});
		};

		var _reset = function(){
			var val = $this.val().trim();
			if( !/^([1-9])(\d*)(\.)?(\d*)$/.test( val ) &&  !/^0(\.)?(\d*)$/.test( val ) && val != '' )
			{
				$this.val(0);
			}
			_temp.min !== null &&  $this.val( Math.max($this.val() , _temp.min) );
			_temp.max !== null && $this.val( Math.min($this.val() , _temp.max) );
			op.callback($this.val());
		};

		_init();
	});
};
})(jQuery);