/**
 * 分类选择器
 * @usage $(domCheck).selectCate(cateJson);
 * @param cateJson nested json like
 * [{gcid: id1 , title : hasChild ,child:[cateJson]},{gcid:id1, title:NoChild}]
 */
(function($){
    $.extend($.fn ,{
        selectCate:function(cate,options) {
            // if nothing is selected, return nothing; can't chain anyway
            if ( !this.length ) {
                if ( options && options.debug && window.console ) {
                    console.warn( "Nothing selected, can't validate, returning nothing." );
                }
                return;
            }

            var self = this;

            // check if a validator for this form was already created
            if ( $.data( this[0], "cate" ) ) {
                return ;
            }

            var nextSltr = options && options.next ? options.next : '#next-step',nextVal  = options && options.val ? options.val : '#next-val';

            $.data( this[0], 'cate', cate);
            self.addClass('t_selectCate');

            thisId = self.attr('id');
            if (!thisId) {
                if( !Date.now) {
                    Date.now = function() {return new Date().valueOf();};
                }
                thisId = 'selectCate_'+Date.now().toString();
                self.attr('id', thisId);
            }

            $('.t_selectCate').on( 'click' ,'li' ,function(){
                var t = $(this) , gcid = t.data('gcid') , $ul = t.parent();
                if (t.hasClass('sltd')) {
                    return ;
                }

                $ul.find('li').removeClass('sltd');
                t.addClass('sltd');
                $(nextSltr).attr('disabled', t.data('haschild') == 1);

                if (t.data('haschild') == 1 ) { // if hasChild
                    // if not changed
                    $(nextSltr).css('backgroundColor','grey');
                    // get current categories data
                    var allData = self.data('cate');

                    var cur = allData[t.data('idx')] ;
                    $ul.parent('td').find('li.sltd').each( function(idx, li){
                        var midx = $(li).data('idx'), _gcid=$(li).data('gcid');

                        allData = allData[midx].child;
                        if (_gcid == gcid) {
                            return false;// break ,已经选中第二次点击
                        }
                    });

                    //cur = allData[t.data('idx')];
                    // clear all next
                    $ul.nextAll('ul').remove();

                    var thtml = '<ul  data-parent="'+ gcid+'">';
                    if (options.type && options.type == 'select') {
                        var checked = [];
                        if ( options.checked && $(options.checked).val() !=''  ) {
                            checked = $.grep($(options.checked).val().split(',') ,function(t ,idx){return t !=''});
                        }
                    }
                    $(allData).each(function(idx, item ) {
                        var title = item.title;
                        // if checked
                        if (options.type && options.type == 'select' && !item.child ) {
                            var _checked = checked && $.inArray(item.gcid , checked)> -1 ? ' checked="checked"' : '';
                            title = '<label for="_gcid_'+item.gcid+'"><input type="checkbox" name="gcid" class="_gcid" value="'+item.gcid+'" id="_gcid_'+item.gcid+'"'+ _checked+' />'+title+'</label>';
                        }
                        thtml += $.format('<li data-gcid="{0}" data-haschild="{2}" data-idx="{3}" class="has-child-{2}{4}">{1}</li>', item.gcid, title ,item.child ? 1:0, idx, item.status==1 ?'' :' _hide');


                    });

                    thtml += '</ul>';
                    $(thtml).insertAfter($ul);
                } else {
                    // clear up
                    $ul.nextAll('ul').remove();
                    $(nextSltr).css('backgroundColor','').data('gcid',t.data('gcid'));
                }

                $(nextVal).val(t.data('gcid'));
                if ( options && options.onSelected ) {
                    options.onSelected();
                }
//                return false; // 让连接不跳转
            }); // $('.t_selectCate li').live('click',function(){

            // table html
            var thtml = '<ul>';

            $(cate).each(function(idx,item){
                thtml += $.format('<li data-gcid="{0}" data-haschild="{2}" data-idx="{3}" class="has-child-{2}{4}">{1}</a></li>\n', item.gcid,item.title ,item.child ? 1:0,idx , item.status==1 ?'' :' _hide');
            });
            thtml += '</ul><div class="clear"></div>';

            self.html(thtml);

            $(nextSltr).css('backgroundColor','grey').attr('disabled',1);
            return self;
        }
    });
})(jQuery);


//      规格选择
var GoodsspecComponent = {
    savedata:{},
    renderGrid:function() {
        var ospecArr = this.getSpecs(); // {gcaid : { val11:title11 ,val12:title12 ,val3:title13 ,val4:title14] , gcaid2:{val21:title21, val22:title: ,val23 ]}
        var specArr = this.flatize(ospecArr); // gcaid-val1_gcaid2-val21 , gcaid

        this.renderTable(specArr);
    },
    renderTable : function(specArr) {

        var template = '<tr id="{0}" class="inputline_{0} _spec">' +
            '<td rowspan="2"><a class="disable" href="#">删除</a></td>' +
            '<td rowspan="2" style="text-align: center;"><span class="addpic_spec global_btn global_btn_brown up_img_spec">选图片</span></td>'+
            '<td colspan="5">规格名:<input type="text" name="ispec[{1}][attribute_good_title]" value="{2}" placeholder="{2}"/> <span class="origin_attr_name">对应规格项:{2}</span>  <input type="hidden" name="ispec[{1}][gid]" value="">SKU编号<input class="digi sku" type="text" name="ispec[{1}][sku]" /></td>' +
            '</tr><tr class="inputline_{0}">' +
            '<td>实际售价<br/><input class="digi price" type="number" step="0.01" name="ispec[{1}][price]" value="100000" /></td>' +
            '<td>定金<br/><input class="digi prepay_price" type="number" step="0.01" name="ispec[{1}][prepay_price]" value="0" /></td>' +
            '<td>库存<br/><input class="digi stock" type="number" step="1" name="ispec[{1}][stock]" value="1" /></td>'+
            '<td>市场价<br/><input class="digi market_price" type="number" step="0.01" name="ispec[{1}][market_price]" value="0" /></td>' +
            '<td><div class="sales_min_max">经销商现价范围<br/><input class="digi min_price" type="number" step="0.01" name="ispec[{1}][min_price]" value="0" /> - <input class="digi max_price" type="number" step="0.01" name="ispec[{1}][max_price]" value="0" /></div></td>'+
            +'</tr>';

        var html = '';
        var to_delete = {};

        $('._spec').each(function(idx ,tr){
            to_delete[ tr.id ] = 1;
        });

          // //现在有的spec
          // console.log( to_delete );
          // //选项的spec
          // console.log( specArr );

//        $('.spec_del_all').show();
        for ( var key in specArr) {
            var _key = key;//.split('_')[1];
            var id = 'spec_'+_key.replace(/\./g,'_');
            if ( to_delete[id] ) {
                delete to_delete[id];
//                delete specArr[key];
            } else {
                if( GoodsspecComponent.savedata[ id ] )
                {
                    console.log('cache'+id);
                  html += GoodsspecComponent.savedata[ id ];
                }
                else
                {
                  var title = specArr[key];
                  var chtml = $.format(template, id, key, title);
                  GoodsspecComponent.savedata[ id ] = chtml;
                  html += chtml;
                }
            }
        }

        for (var id in to_delete ) {
            var ssshtml = '';
              $('.inputline_'+id).each(function (sss,ssstr) {
                ssshtml += $(ssstr).prop('outerHTML');
              });
            GoodsspecComponent.savedata[ id ] = ssshtml;
          $('.inputline_'+id).remove();
        }

        if ($('#spec_price tbody ._spec').length < 1) {
            $('#spec_price tbody').html(html);
        } else {
            $(html).insertAfter($('#spec_price tbody tr:last'));
        }
    },
    // input : {gcaid : { val11:title11 ,val12:title12 ,val3:title13 ,val4:title14] , gcaid2:{val21:title21, val22:title: ,val23 ]}
    // output : gcaid1-val1_gcaid2-val21 , gcaid1-val2_gcaid2-val21 , gcaid1-val2_gcaid2-val2
    flatize:function(ospecArr) {
        if ($.isEmptyObject(ospecArr)) {
            return {};
        }
        var s = [] , len = 1;
        for (var gcaid in ospecArr){
            var _gcaid = gcaid.split('_')[1],
                item = ospecArr[gcaid], ti = 0,tmp = [];
           for ( var val in item) {
               tmp.push([_gcaid +'-'+val, item[val]]);
               ti++;
           }

            len = len * ti;
            s.push(tmp);
        };

        var rkey = new Array(len) , rval = new Array(len);

        var tempMulti = 1;
        for ( var idx in s) {
            var item = s[idx], thislen = item.length ,ti = 0;
            for (var i =0; i < len; i ++) {
                if (undefined == rkey[i]) {
                    rkey[i] = [];
                    rval[i] = [];
                }
                var index = Math.floor( i / tempMulti );
                if (index >= thislen ) {
                    index = index - Math.floor(index / thislen )*  thislen;
                }

                rkey[i][idx] = item[index][0];
                rval[i][idx] = item[index][1];
            }
            tempMulti = tempMulti * thislen;
        }

        var r = {};
        for (var i = 0, len = rkey.length; i < len;i++ ) {
            var key = rkey[i].join('_') , val = rval[i].join(' ');
            r[key] = val;
        }

        return r;
    },
    // return {gcaid : { val11:title11 ,val12:title12 ,val3:title13 ,val4:title14] , gcaid2:{val21:title21, val22:title: ,val23 ]}
    getSpecs:function() {
        var s = {};
        $('#category_spec div.control_group').each(function(idx,td){
            //log(td);
            var thisGcaid = 'a_'+td.id.split('_')[1], vals = {};
            $(td).find('label').each (function(idx2,lb){
                var $lb = $(lb);
                if ($lb.find('input:checked').length < 1) {
                    return ; // continue if not checked
                }
                var k = $lb.find('input').val(), val = $lb.find('span').first().text();
                vals[ k ] = val;
            });
            if ($.isEmptyObject(vals)) {
                return ; // continue if none checked
            }

            s[thisGcaid] = vals;
        });

        return s;
    }

}
// end of           规格选择

// start of 图片选择
var GoodsImg = {
    picArr : [], // modify if add or remove
    init:function(domChecker) {
        var self = this;
        $(domChecker+' input').each (function(idx , input){
            if (input.value != '') {
                self.picArr.push(input.value);
            }
        });

        $(domChecker+' span.remove').on('click', function(){
            self.remove(this);
        });

        $(domChecker+' span.moveLeft').on('click', function(){
            self.moveLeft(this);
        });

        $(domChecker+' span.moveRight').on('click', function(){
            self.moveRight(this);
        });

        $('.choosePic').hover(function(){ // on handlerIn
            $(this).find('.choosePicOpt').show();
        },function(){ // on handlerOut
            $(this).find('.choosePicOpt').hide();
        });

    } ,
    insertImage:function(domChecker ,src, img ) {
        var self = this ,$d = $(domChecker);
        if ($d.find('input').length <= 0 ) {
            popup({text:'已经满了',delayclose:1000});
            return false;
        }

        var picArr = $d.data('picArr');

        if (!picArr) {
            picArr = [];
        }

        // if ($.inArray( src , self.picArr ) >-1) {
        //     popup({text:'同一张图片不能多次添加',delayclose:1000});
        //     return false;
        // }

        var $f = $d.find('input:last');
        var $div = $f.next('div');
        log($div);
        $div.append("<img src='"+img+"' />");

        picArr.push(src);
        self.picArr.push(src);
        $d.data('picArr', picArr);
        $f.val(src);
        $(domChecker).find('li:last-child').find('span.remove').on('click', function(){
            self.remove(this);
        });

        $(domChecker).find('li:last-child').find('span.moveLeft').on('click', function(){
            self.moveLeft(this);
        });

        $(domChecker).find('li:last-child').find('span.moveRight').on('click', function(){
            self.moveRight(this);
        });
        $('.choosePic').hover(function(){ // on handlerIn
            $(this).find('.choosePicOpt').show();
        },function(){ // on handlerOut
            $(this).find('.choosePicOpt').hide();
        });
        return true;

    },
    moveLeft:function(span) {
        var $td     = $(span.parentNode.parentNode.parentNode),
            $leftTd = $td.prev('li') ,
            thisSrc = $td.find('input').val(),
            thisImg = $td.find('img'),
            leftSrc = $leftTd.find('input').val(),
            leftImg = $leftTd.find('img');

        if ($leftTd.length == 0) {
            return false;
        }

        if (leftImg.length > 0 ) {
            thisImg.attr('src' , DOMAIN.SITE_CONTENTDOMAIN+leftSrc);
            $td.find('input').val(leftSrc);
            leftImg.attr('src' ,DOMAIN.SITE_CONTENTDOMAIN+thisSrc);
        } else {
            thisImg.remove();
            $td.find('input').val('');
            $leftTd.find('div').append('<img src="'+DOMAIN.SITE_CONTENTDOMAIN+thisSrc+'" />');
        }


        $leftTd.find('input').val(thisSrc);
        return false;
    },
    moveRight:function(span) {
        var $td     = $(span.parentNode.parentNode.parentNode),
            $rightTd= $td.next('li') ,
            thisSrc = $td.find('input').val(),
            thisImg = $td.find('img'),
            leftSrc = $rightTd.find('input').val(),
            rightImg= $rightTd.find('img');

        if ($rightTd.length == 0 ) {
            return false;
        }

        if (!thisSrc || thisSrc == '') {
            return false;
        }

        if (rightImg.length > 0 ) {
            thisImg.attr('src' , DOMAIN.SITE_CONTENTDOMAIN+leftSrc);
            $td.find('input').val(leftSrc);
            rightImg.attr('src' ,DOMAIN.SITE_CONTENTDOMAIN+thisSrc);
        } else {
            thisImg.remove();
            $td.find('input').val('');
            $rightTd.find('div').append('<img src="'+DOMAIN.SITE_CONTENTDOMAIN+thisSrc+'" />');
        }


        $rightTd.find('input').val(thisSrc);
        return false;
    }
    ,remove:function(span) {
        var self = this,
            $div = $( span.parentNode.parentNode ) ,
            $input = $div.prev('input')
            src = $input.val();

        if (src != '') {
            $( span.parentNode.parentNode.parentNode).remove();
            //$div.find('img').remove();
            //$input.val('');
            self.picArr.splice($.inArray(src , self.picArr),1 );
        }
        return false;
    }
};
// end of 图片选择


