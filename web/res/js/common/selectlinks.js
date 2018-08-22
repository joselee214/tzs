function showselectlinks(obj,rewrite_base,zIndex) {
  var selectlinks = $('#selectlinks');
  if( selectlinks.length<1 )
  {
    selectlinks = $('<div></div>');
    selectlinks.attr('id','selectlinks');
    if( obj!=undefined &&  (obj.length==1) )
    {
      $('body').append(selectlinks.hide());
      selectlinks.css({
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
        selectlinks.css({'z-index':zIndex});
      }

      if( $(window).outerWidth() - obj.offset().left < selectlinks.outerWidth() )
      {
        selectlinks.css('left' , $(window).outerWidth() - selectlinks.outerWidth()+obj.width() );
      }
      else
      {
        selectlinks.css('left' , obj.offset().left +obj.width() );
      }
      selectlinks.show();
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
      selectlinks.hide();
    }
    else
    {
      if( obj.length==1 )
      {
        selectlinks.css({
          'top' : obj.offset().top+obj.height()
        });
        if( $(window).outerWidth() - obj.offset().left < selectlinks.outerWidth() )
        {
          selectlinks.css('left' , $(window).outerWidth() - selectlinks.outerWidth()+obj.width() );
        }
        else
        {
          selectlinks.css('left' , obj.offset().left +obj.width() );
        }
      }
      selectlinks.show();
    }
  }


  var selectlinks_title = $('#selectlinks_title');
  var selectlinks_content = $('#selectlinks_content');
  if( selectlinks_content.length<1 )
  {
    selectlinks_title = $('<div style="text-align: right;cursor: move;margin: 3px;"><a id="closeSelectLinks" style="cursor: pointer;" onclick="showselectlinks();" title="关闭"><i class="closeicon"></i></a>&nbsp;</div>');

    selectlinks_title.attr('id','selectlinks_title');

    selectlinks.append(selectlinks_title);
    if( $.fn.fixedmove == undefined )
    {
      $.getScript('/js/jq/fixed.move.js',function(){
        $('#selectlinks_title').fixedmove($('#selectlinks'));
      });
    }
    else
    {
      $('#selectlinks_title').fixedmove($('#selectlinks'));
    }

    selectlinks_content = $('<div></div>');
    selectlinks_content.attr('id','selectlinks_content');
    selectlinks.append(selectlinks_content);

    selectlinks_content.load(rewrite_base+'/notp/basic/selectlinks');
  }

}