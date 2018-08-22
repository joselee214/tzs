$(function(){
	var dom = $('<div class="global_scrolltop"></div>');
	dom.html('<a href="#">回到<br/>顶部</a>');
	dom.appendTo( $('body') );
	var header = $('.global_header_parent:eq(0)');
	var mintop = header.length ? header.outerHeight() : 100;
	$(window).on('scroll' , function(){
		if( $(window).scrollTop() > mintop )
		{
			dom.fadeIn();
		}
		else
		{
			dom.fadeOut();
		}
	});
	dom.children('a').on('click' , function(){
		$(window).scrollTop(0);
		return false;
	});
});
