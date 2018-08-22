
function addPicSpaceAfter(obj,rewrite_base,zIndex)
{
    var wysiwyg_addimg = $('#wysiwyg_addimg');
    if( wysiwyg_addimg.length<1 )
    {
        wysiwyg_addimg = $('<div></div>');
        wysiwyg_addimg.attr('id','wysiwyg_addimg');
        if( obj!=undefined &&  (obj.length==1) )
        {
            $('body').append(wysiwyg_addimg.hide());
            wysiwyg_addimg.css({
                'top' : obj.offset().top+obj.height(),
                '-webkit-box-shadow' : '#666 0px 0px 10px',
                '-moz-box-shadow' : '#666 0px 0px 10px',
                'box-shadow' : ' #666 0px 0px 10px',
                'width':'600px',
                'background':'#ffffff',
                'border':'1px solid #d5d5d5',
                'position':'absolute'
            });
            if(parseInt(zIndex)>1){
              wysiwyg_addimg.css({'z-index':zIndex});
            }

            if( $(window).outerWidth() - obj.offset().left < wysiwyg_addimg.outerWidth() )
            {
                wysiwyg_addimg.css('left' , $(window).outerWidth() - wysiwyg_addimg.outerWidth()+obj.width() );
            }
            else
            {
                wysiwyg_addimg.css('left' , obj.offset().left +obj.width() );
            }
            wysiwyg_addimg.show();
        }
        else
        {
            log('无法定位!');
        }
    }
    else
    {
        if( obj==undefined )
        {
            wysiwyg_addimg.hide();
        }
        else
        {
            if( obj.length==1 )
            {
                wysiwyg_addimg.css({
                    'top' : obj.offset().top+obj.height()
                });
                if( $(window).outerWidth() - obj.offset().left < wysiwyg_addimg.outerWidth() )
                {
                    wysiwyg_addimg.css('left' , $(window).outerWidth() - wysiwyg_addimg.outerWidth()+obj.width() );
                }
                else
                {
                    wysiwyg_addimg.css('left' , obj.offset().left +obj.width() );
                }
            }
            wysiwyg_addimg.show();
        }
    }


    var wysiwyg_addimg_title = $('#wysiwyg_addimg_title');
    var wysiwyg_addimg_content = $('#wysiwyg_addimg_content');
    if( wysiwyg_addimg_content.length<1 )
    {
        wysiwyg_addimg_title = $('<div style="text-align: right;cursor: move;margin: 3px;"><a id="closePicSpace" style="cursor: pointer;" onclick="addPicSpaceAfter();" title="关闭图片空间"><i class="closeicon"></i></a>&nbsp;</div>');

        wysiwyg_addimg_title.attr('id','wysiwyg_addimg_title');

//        wysiwyg_addimg_title.click(function(){addPicSpaceAfter();});
        wysiwyg_addimg.append(wysiwyg_addimg_title);
        if( $.fn.fixedmove == undefined )
        {
          $.getScript('/js/jq/fixed.move.js',function(){
            $('#wysiwyg_addimg_title').fixedmove($('#wysiwyg_addimg'));
          });
        }
        else
        {
          $('#wysiwyg_addimg_title').fixedmove($('#wysiwyg_addimg'));
        }

        wysiwyg_addimg_content = $('<div></div>');
        wysiwyg_addimg_content.attr('id','wysiwyg_addimg_content');
        wysiwyg_addimg.append(wysiwyg_addimg_content);
    }
    if( $('#picspace_main_div').length<1 )
    {
        wysiwyg_addimg_content.load(rewrite_base+'/notp/basic/picspace');
    }
    else
    {
        wysiwyg_addimg_content.append($('#picspace_main_div'));
    }
}