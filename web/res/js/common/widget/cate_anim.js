(typeof(widget) !=='object' ) && (widget = {});
/*分类菜单效果*/
widget.cate_anim = function(){
	var ul = $('#jquery_widgetcateanim');
	var parent = ul.parent('div');
	var li = $('.li1' , ul);
	var divs = $('.div1' , li);
	var common_left = ul.outerWidth() ;
	var shift_bottom_height = 0; /*距离元素底部的最小距离*/
	var ulheight = ul.outerHeight();
	li.on('mouseenter' , function(){
		divs.hide();
		var $this = $(this);
		$this.addClass('hover');
		var div = $this.children('.div1').eq(0);
		var top = $this.position().top;
		if( (top + div.outerHeight() + shift_bottom_height) > ulheight )
		{
			/*不能溢出浏览器底*/
			top = ulheight - div.outerHeight() - shift_bottom_height;
		}
		if( top < 0 )
		{
			top = 0;
		}
		div.css({
			'left' : common_left + 'px' ,
			'top' : top + 'px'
		}).show();
	}).on('mouseleave' , function(){
		var $this = $(this);
		$this.removeClass('hover');
		divs.hide();
	});

	var mintop = parent.offset().top;
	var maxtop = $('.global_footer_parent:eq(0)').offset().top - parent.outerHeight();
	var left = parent.offset().left;
	//parent.css({
	//	'left' : left ,
	//	'top' : mintop ,
	//	'position' : 'absolute'
	//});
	$(window).on('resize' , function(){
		parent.css({
			'left' : $('.global_topmenu:eq(0)').offset().left
		});
	});
	//parent.appendTo( $('body') );
	$(window).on('scroll' , function(){
		var _top = $(window).scrollTop();
		if( _top > mintop && _top < maxtop )
		{
			parent.css('top' , _top-mintop );
		}
		else if( _top <= mintop )
		{
			parent.css('top' , mintop-mintop);
		}
		else if( _top >= maxtop )
		{
			parent.css('top' , maxtop-mintop);
		}
	});
};