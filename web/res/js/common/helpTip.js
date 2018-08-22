(function(){
    if(!window.$){
        alert('请先加载jquery');
        return false;
    }
    $.fn.helpTip = function(key){
        var jDom = this;
        if(!key){
          key = $(this).attr('key');
        }
        if(!key){
            return false;
        }
        jDom.css('cursor', 'pointer');

        var newDom;
        var newDomTitle;
        var newDomBody;

        var getContent = false;

        var showDialog = function(){
            $('.js-helpTip').fadeOut();
            if(!newDom){
                newDom = $('<div class="js-helpTip">');
                newDomTitle = $('<div>');
                newDomBody = $('<div>');
                newDom.append(newDomTitle);
                newDom.append(newDomBody);


                newDom.on('click', function(e){
                    e.stopPropagation();
                });

                newDomBody.css({
                    'white-space': 'wrap',
                    'min-height': '100px',
                    'max-height': '300px',
                    'overflow': 'hidden',
                    'overflow-y': 'auto',
                    'padding': '15px',
                    'box-sizing': 'border-box',
                    'line-height': '1.8'
                });

                newDomTitle.css({
                    'font-size': '16px',
                    'color': '#999',
                    'padding': '10px 15px',
                    'border-bottom': '1px solid #ccc',
                    'background': '#f9f9f9',
                    'overflow': 'hidden'
                });

                newDom.css({
                    'position': 'absolute',
                    'max-width': '600px',
                    'min-width': '300px',
                    'overflow': 'hidden',
                    'z-index': 1002,
                    'background': '#fff',
                    'border-radius': '5px',
                    'box-shadow': '-5px 5px 5px rgba(0,0,0,.2)',
                    'border': '1px solid #ccc'
                }).hide();


                newDomTitle.html(getContent.title);
                newDomBody.html(getContent.content);

                $('body').append(newDom);
            }
            var jP = jDom.position();

            var cssParams = {};

            if( jP.left+jDom.outerWidth()+10 > $(window).width() - newDom.outerWidth() )
            {
                cssParams.right = ($(window).width()-jP.left + jDom.outerWidth())+'px';
            }
            else
            {
                cssParams.left = (jP.left+jDom.outerWidth()+10) + 'px';
            }

            if( jP.top > $(window).height() - newDom.outerHeight()){
                cssParams.bottom = ($(window).height()-jP.top)+'px';
            }
            else {
                cssParams.top = jP.top + 'px';
            }

            newDom.css(cssParams).fadeIn();

        };

        $(document).on('click',function(){
            if(newDom){
                newDom.fadeOut();
            }
        });

        jDom.on('click', function(e){
            e.stopPropagation();

            if(getContent){
                showDialog();
            }
            else
            {
                $.get('/help/apihelp', {hkey: key,url: location.href }, function(ret){
                    var status = ret.status;
                    var data = ret.data;
                    if(status!==200){
                        data = {
                            title: '没有帮助 key:'+key,
                            content: '没有找到适合的帮助内容'
                        };
                    }
                    getContent = data;
                    showDialog();
                });
            }
        });

    };
})();