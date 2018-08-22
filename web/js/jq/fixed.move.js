var fixedmoveZindex = 1;

(function($){
    $.fn.removefixedmove = function(tomoveobj){
//        if( typeof tomoveobj != 'object' )
//            tomoveobj = $(this);
//        var moveobj = $(this);
//        var moveobjid = moveobj[0].id;
//        moveobj.unbind('mousedown.'+moveobjid);
    },
    $.fn.fixedmove = function(tomoveobj){
        if( typeof tomoveobj != 'object' )
            tomoveobj = $(this);
        var moveobj = $(this);
        var old_position = {};
        var new_position = {};
        var offset = {};
        var isover = 1;
        var offset_fake = {};
        var moveobjid = moveobj[0].id;
        if(fixedmoveZindex<parseInt(tomoveobj.css('z-index')))
            fixedmoveZindex=parseInt(tomoveobj.css('z-index'));
        fixedmoveZindex +=1;
        tomoveobj.css({'zIndex': fixedmoveZindex});

        moveobj.unbind('mousedown.'+moveobjid).bind('mousedown.'+moveobjid,
            function (e) {
                fixedmoveZindex +=1;
                tomoveobj.css({'zIndex': fixedmoveZindex});
                old_position = {X:e.clientX,Y:e.clientY};
                offset = moveobj.offset();
                isover = 0; //待移动
                offset_fake = moveobj.offset();
                $('body').bind('mousemove.'+moveobjid,
                    function (eb) {
                        if (!isover) {
                            new_position = {X:eb.clientX,Y:eb.clientY};
                            tomoveobj.css({
                                left: offset.left+new_position.X-old_position.X,
                                top: offset.top+new_position.Y-old_position.Y   //-$(window).scrollTop()
                            });
                        }
                    }
                ).bind('mouseup.'+moveobjid,
                    function (e) {
                        isover = 1;
                        $('body').unbind('mousemove.'+moveobjid).unbind('mouseup.'+moveobjid);
                    }
                );
            }
        );
        tomoveobj.unbind('click.'+moveobjid).bind('click.'+moveobjid,
            function (e) {
                fixedmoveZindex +=1;
                tomoveobj.css({'zIndex': fixedmoveZindex});
            }
        );
    }
})(jQuery);