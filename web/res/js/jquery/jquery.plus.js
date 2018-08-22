(function($) {

    $.postJSON = function(url,data,fun)
    {
//        var opt = {
//            url     :url,
//            data    :data,
//            complete:fun,
//            type    :'POST',
//            dataType: 'json'
//        };
//
//        return jQuery.ajax(opt);
        return jQuery.post(url,data,fun,'json');
    }

    $.isArray = function(o) {
        return Object.prototype.toString.call(o) === '[object Array]';
    }


//    $.format = function( source, params ) {
//        if ( arguments.length === 1 ) {
//            return function() {
//                var args = $.makeArray(arguments);
//                args.unshift(source);
//                return $.validator.format.apply( this, args );
//            };
//        }
//        if ( arguments.length > 2 && params.constructor !== Array  ) {
//            params = $.makeArray(arguments).slice(1);
//        }
//        if ( params.constructor !== Array ) {
//            params = [ params ];
//        }
//        $.each(params, function( i, n ) {
//            source = source.replace( new RegExp("\\{" + i + "\\}", "g"), function() {
//                return n;
//            });
//        });
//        return source;
//    };
})(jQuery);

//$(document).ready(function(){
//    $(document).ajaxSuccess(function(evt ,req, opt){
//        log('ajaxComplete :');
//        log(evt);
//        var text = req.responseText;
//        if (/^[\],:{}\s]*$/
//            .test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, '@')
//            .replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']')
//            .replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {
//            var arr = $.parseJSON(text);
//            if (typeof arr['error'] != 'undefined' && arr['error'] == 'login_required') {
//                alert("login_required, redirecting");
//                window.location.href = site.userfront + '?url='+encodeURIComponent(window.location.href);
//                return false;
//            }
//        }
//        log("after");
//    });
//});
/*
jQuery cookie 插件
 */
(function($) {
    $.cookie = function(key, value, options) {

        // key and at least value given, set cookie...
        if (arguments.length > 1 && (!/Object/.test(Object.prototype.toString.call(value)) || value === null || value === undefined)) {
            options = $.extend({}, options);

            if (value === null || value === undefined) {
                options.expires = -1;
            }

            if (typeof options.expires === 'number') {
                var days = options.expires, t = options.expires = new Date();
                t.setDate(t.getDate() + days);
            }

            value = String(value);

            return (document.cookie = [
                encodeURIComponent(key), '=', options.raw ? value : encodeURIComponent(value),
                options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
                options.path    ? '; path=' + options.path : '',
                options.domain  ? '; domain=' + options.domain : '',
                options.secure  ? '; secure' : ''
            ].join(''));
        }

        // key and possibly options given, get cookie...
        options = value || {};
        var decode = options.raw ? function(s) { return s; } : decodeURIComponent;

        var pairs = document.cookie.split('; ');
        for (var i = 0, pair; pair = pairs[i] && pairs[i].split('='); i++) {
            if (decode(pair[0]) === key) return decode(pair[1] || ''); // IE saves cookies with empty string as "c; ", e.g. without "=" as opposed to EOMB, thus pair[1] may be undefined
        }
        return null;
    };
})(jQuery);

/*
jQuery.History
*/
(function($) {
    var locationWrapper = {
        put: function(hash, win) {
            (win || window).location.hash = this.encoder(hash);
        },
        get: function(win) {
            var hash = ((win || window).location.hash).replace(/^#/, '');
            try {
                return $.browser.mozilla ? hash : decodeURIComponent(hash);
            }
            catch (error) {
                return hash;
            }
        },
        encoder: encodeURIComponent
    };

    var iframeWrapper = {
        id: "__jQuery_history",
        init: function() {
            var html = '<iframe id="'+ this.id +'" style="display:none" src="javascript:false;" />';
            $("body").prepend(html);
            return this;
        },
        _document: function() {
            return $("#"+ this.id)[0].contentWindow.document;
        },
        put: function(hash) {
            var doc = this._document();
            doc.open();
            doc.close();
            locationWrapper.put(hash, doc);
        },
        get: function() {
            return locationWrapper.get(this._document());
        }
    };

    function initObjects(options) {
        options = $.extend({
                unescape: false
            }, options || {});

        locationWrapper.encoder = encoder(options.unescape);

        function encoder(unescape_) {
            if(unescape_ === true) {
                return function(hash){ return hash; };
            }
            if(typeof unescape_ == "string" &&
               (unescape_ = partialDecoder(unescape_.split("")))
               || typeof unescape_ == "function") {
                return function(hash) { return unescape_(encodeURIComponent(hash)); };
            }
            return encodeURIComponent;
        }

        function partialDecoder(chars) {
            var re = new RegExp($.map(chars, encodeURIComponent).join("|"), "ig");
            return function(enc) { return enc.replace(re, decodeURIComponent); };
        }
    }

    var implementations = {};

    implementations.base = {
        callback: undefined,
        type: undefined,

        check: function() {},
        load:  function(hash) {},
        init:  function(callback, options) {
            initObjects(options);
            self.callback = callback;
            self._options = options;
            self._init();
        },

        _init: function() {},
        _options: {}
    };

    implementations.timer = {
        _appState: undefined,
        _init: function() {
            var current_hash = locationWrapper.get();
            self._appState = current_hash;
            self.callback(current_hash);
            setInterval(self.check, 100);
        },
        check: function() {
            var current_hash = locationWrapper.get();
            if(current_hash != self._appState) {
                self._appState = current_hash;
                self.callback(current_hash);
            }
        },
        load: function(hash) {
            if(hash != self._appState) {
                locationWrapper.put(hash);
                self._appState = hash;
                self.callback(hash);
            }
        }
    };

    implementations.iframeTimer = {
        _appState: undefined,
        _init: function() {
            var current_hash = locationWrapper.get();
            self._appState = current_hash;
            iframeWrapper.init().put(current_hash);
            self.callback(current_hash);
            setInterval(self.check, 100);
        },
        check: function() {
            var iframe_hash = iframeWrapper.get(),
                location_hash = locationWrapper.get();

            if (location_hash != iframe_hash) {
                if (location_hash == self._appState) {    // user used Back or Forward button
                    self._appState = iframe_hash;
                    locationWrapper.put(iframe_hash);
                    self.callback(iframe_hash);
                } else {                              // user loaded new bookmark
                    self._appState = location_hash;
                    iframeWrapper.put(location_hash);
                    self.callback(location_hash);
                }
            }
        },
        load: function(hash) {
            if(hash != self._appState) {
                locationWrapper.put(hash);
                iframeWrapper.put(hash);
                self._appState = hash;
                self.callback(hash);
            }
        }
    };

    implementations.hashchangeEvent = {
        _init: function() {
            self.callback(locationWrapper.get());
            $(window).bind('hashchange', self.check);
        },
        check: function() {
            self.callback(locationWrapper.get());
        },
        load: function(hash) {
            locationWrapper.put(hash);
        }
    };

    var self = $.extend({}, implementations.base);

    if(!$.support.leadingWhitespace) {  //if($.browser.msie && ($.browser.version < 8 || document.documentMode < 8)) {
        self.type = 'iframeTimer';
    } else if("onhashchange" in window) {
        self.type = 'hashchangeEvent';
    } else {
        self.type = 'timer';
    }

    $.extend(self, implementations[self.type]);
    $.history = self;
})(jQuery);

/**
 * Timeago
 */

(function($) {
  $.timeago = function(timestamp) {
    if (timestamp instanceof Date) {
      return inWords(timestamp);
    } else if (typeof timestamp === "string") {
      return inWords($.timeago.parse(timestamp));
    } else if (typeof timestamp === "number") {
      return inWords(new Date(timestamp));
    } else {
      return inWords($.timeago.datetime(timestamp));
    }
  };
  var $t = $.timeago;

  $.extend($.timeago, {
    settings: {
      refreshMillis: 60000,
      allowFuture: false,
      strings: {
        prefixAgo: null,
        prefixFromNow: null,
        suffixAgo: "前", // "ago",
        suffixFromNow: "..", // "from now",
        seconds: "1分钟内", // "less than a minute",
        minute: "1分钟", // "about a minute",
        minutes: "%d分钟", // "%d minutes",
        hour:"1小时", //"about an hour",
        hours: "%d小时",//"about %d hours",
        day:"一天", //"a day",
        days: "%d天", //"%d days",
        month: "1月", //"about a month",
        months:"%d月", // "%d months",
        year: "一年", //"about a year",
        years:"%d年", // "%d years",
        wordSeparator: "",
        numbers: []
      }
    },
    inWords: function(distanceMillis) {
      var $l = this.settings.strings;
      var prefix = $l.prefixAgo;
      var suffix = $l.suffixAgo;
      if (this.settings.allowFuture) {
        if (distanceMillis < 0) {
          prefix = $l.prefixFromNow;
          suffix = $l.suffixFromNow;
        }
      }

      var seconds = Math.abs(distanceMillis) / 1000;
      var minutes = seconds / 60;
      var hours = minutes / 60;
      var days = hours / 24;
      var years = days / 365;

      function substitute(stringOrFunction, number) {
        var string = $.isFunction(stringOrFunction) ? stringOrFunction(number, distanceMillis) : stringOrFunction;
        var value = ($l.numbers && $l.numbers[number]) || number;
        return string.replace(/%d/i, value);
      }

      var words = seconds < 45 && substitute($l.seconds, Math.round(seconds)) ||
        seconds < 90 && substitute($l.minute, 1) ||
        minutes < 45 && substitute($l.minutes, Math.round(minutes)) ||
        minutes < 90 && substitute($l.hour, 1) ||
        hours < 24 && substitute($l.hours, Math.round(hours)) ||
        hours < 42 && substitute($l.day, 1) ||
        days < 30 && substitute($l.days, Math.round(days)) ||
        days < 45 && substitute($l.month, 1) ||
        days < 365 && substitute($l.months, Math.round(days / 30)) ||
        years < 1.5 && substitute($l.year, 1) ||
        substitute($l.years, Math.round(years));

      var separator = $l.wordSeparator === undefined ?  " " : $l.wordSeparator;
      return $.trim([prefix, words, suffix].join(separator));
    },
    parse: function(iso8601) {
      var s = $.trim(iso8601);
      s = s.replace(/\.\d\d\d+/,""); // remove milliseconds
      s = s.replace(/-/,"/").replace(/-/,"/");
      s = s.replace(/T/," ").replace(/Z/," UTC");
      s = s.replace(/([\+\-]\d\d)\:?(\d\d)/," $1$2"); // -04:00 -> -0400
      return new Date(s);
    },
    datetime: function(elem) {
      var iso8601 = $t.isTime(elem) ? $(elem).attr("datetime") : $(elem).attr("title");
      return $t.parse(iso8601);
    },
    isTime: function(elem) {
      // jQuery's `is()` doesn't play well with HTML5 in IE
      return $(elem).get(0).tagName.toLowerCase() === "time"; // $(elem).is("time");
    }
  });

  $.fn.timeago = function() {
    var self = this;
    self.each(refresh);

    var $s = $t.settings;
    if ($s.refreshMillis > 0) {
      setInterval(function() { self.each(refresh); }, $s.refreshMillis);
    }
    return self;
  };

  function refresh() {
    var data = prepareData(this);
    if (!isNaN(data.datetime)) {
      $(this).text(inWords(data.datetime));
    }
    return this;
  }

  function prepareData(element) {
    element = $(element);
    if (!element.data("timeago")) {
      element.data("timeago", { datetime: $t.datetime(element) });
      var text = $.trim(element.text());
      if (text.length > 0 && !($t.isTime(element) && element.attr("title"))) {
        element.attr("title", text);
      }
    }
    return element.data("timeago");
  }

  function inWords(date) {
    return $t.inWords(distance(date));
  }

  function distance(date) {
    return (new Date().getTime() - date.getTime());
  }

  // fix for IE6 suckage
  document.createElement("abbr");
  document.createElement("time");
}(jQuery));


/**
 * 为当前整篇文档应用CSS文件样式
 * @example JQuery.getCSS('CSSFilePath');
 * @example JQuery.getCSS('CSSFilePath', function (css) {
 *              //do something after all style applied
 *          });
 * @example JQuery.getCSS('CSSFilePath', {
 *              beforeApp           : function (css) {
 *                  //do something before all style apply
 *              }
 *              beforeSelectorApp   : function (selector, cssObject) {
 *                  //do something before a style apply
 *              }
 *              afterSelectorApp    : function (selector, cssObject) {
 *                  //do something after a style applied
 *              }
 *              afterApp            : function (css) {
 *                  //do something after all style applied
 *              }
 *          });
 */
$.extend({
    getCSS  : function (url, option) {
        $.get(url, function (css) {
            var clip    = css.match(/[^{]+\{[^}]+?\}/g);

            if ('object' == typeof option && 'function' == typeof option.beforeApp) {
                if (false === option.beforeApp(css)) {
                    return  null;
                }
            }
            for(var i = 0;i < clip.length;i ++) {
                //删除注释
                var styleCode   = clip[i].replace(/\/\*(.|\s)*?\*\//g, '');
                //获取选择器
                var selector    = styleCode.replace(/^([^{]+)(.|\s)+$/g, "$1").replace(/(^[^;]*;\s*|^\s+|\s+$)/g, '');
                //获取样式定义
                var attrList    = styleCode.replace(/^[^{]+\{([^}]+)\}[^}]*$/g, "$1").split(';');
                var cssObject   = {};
                for (var j = 0;j < attrList.length;j ++) {
                    var attr    = attrList[j].split(':');
                    if (2 == attr.length) {
                        var tmpVarName  = attr[0].replace(/(^\s+|\s+$)/g, '');
                        var varName     = '';
                        for (var k = 0;k < tmpVarName.length;k ++) {
                            if ('-' == tmpVarName.charAt(k)) {
                                ++ k;
                                varName += tmpVarName.charAt(k).toUpperCase();
                            } else {
                                varName += tmpVarName.charAt(k);
                            }
                        }
                        cssObject[varName]  = attr[1].replace(/(^\s+|\s+$)/g, '');
                    }
                }
                if ('object' == typeof option && 'function' == typeof option.beforeSelectorApp) {
                    if (false === option.beforeSelectorApp(selector, cssObject)) {
                        continue;
                    }
                }
                $(selector).css(cssObject);
                if ('object' == typeof option && 'function' == typeof option.afterSelectorApp) {
                    option.afterSelectorApp(selector, cssObject);
                }
            }
            if ('function' == typeof option) {
                option(css);
            }
            if ('object' == typeof option && 'function' == typeof option.afterApp) {
                option.afterApp(css);
            }
        });
    },
    loadCss: function(file)
    {
        var files = typeof file == "string" ? [file] : file;
        for (var i = 0; i < files.length; i++)
        {
            var name = files[i].replace(/^\s|\s$/g, "");
            var tag = "link";
            var attr = " type='text/css' rel='stylesheet' ";
            var link = "href" + "='" + name + "'";
            if ($(tag + "[" + link + "]").length == 0) $('html').append("<" + tag + attr + link + "></" + tag + ">");
        }
    }
});

$.extend({
    sleep : function(n) //强制全局阻塞
    {
        var start = new Date().getTime();
        while(true)
        {
            if( new Date().getTime()-start > n)
            {
                console.log('sleep success...');
                break;
            }
        }
    },
    loadJsDict : {},
    loadJsLoaded : [],
    loadJsCheck : function(timestamp){
        var callback = $.loadJsDict[timestamp][1];
        var urllist = $.loadJsDict[timestamp][0];
        var checkret = true;
        $.each(urllist,function(k,v){
            if( $.inArray(v,$.loadJsLoaded)==-1 ){ checkret = false; }
        });
        if( checkret )
        {
            if(callback && (callback  instanceof Function)){
                callback();//回调
            }
        }
        else
        {
            setTimeout('$.loadJsCheck('+timestamp+')',100);
        }
    },
    loadJs : function (urllist,callback,tp) {
        var timestamp = (new Date()).valueOf();
        if(tp==undefined){tp='ajax';}
        $.loadJsDict[timestamp] = [urllist,callback,tp];
        $.each(urllist,function(k,v){
            if($.inArray(v,$.loadJsLoaded)==-1 )
            {
                if( tp=='getScript' )
                {
                    $.getScript(v,function(){$.loadJsLoaded.push(v);});
                }
                else
                {
                    $.ajax({url:v,dataType: "script",cache: "true",success:function(){$.loadJsLoaded.push(v);}});
                }
            }
        });
        $.loadJsCheck(timestamp);
    }
});

/*
 jQuery j7 插件
 ==================================
 依赖 j7actionpath Action目录
 ==================================
 jQuery.j7.action('init',1,2,3);
 调用文件  j7actionpath + j7_Action_init(1,2,3)
 调用Action方法,无回调,附加参数执行
 */
(function(jQuery) {
    jQuery.j7 = {
        Actions:{scriptfiles:[]},   //存放备份方法
        Action:function()
        {
            // ' base_init.gamemain ' // 以/分隔文件夹，~调用该文件下命令
            var arr = Array.prototype.slice.call(arguments, 0);
            if( arr.length<=0 )
            {
                console.log('error in jQuery.j7.callAction');
                return;
            }
            var methodname = arr.shift();
            var methodstr = 'jQuery.j7.Actions["'+methodname.replace('.','"]["')+'"]';
            var checkmethod = methodname.indexOf('.')>0?methodname.substr(0,methodname.indexOf('.')):methodname;
            var extmethod = methodname.indexOf('.')>0?methodname.substr(methodname.indexOf('.')+1):'execute';
            var methodstr = methodname.indexOf('.')>0?methodstr:methodstr+'["execute"]';

            if( typeof(jQuery.j7.Actions[checkmethod]) == 'function' || typeof(jQuery.j7.Actions[checkmethod]) == 'object'  )
            {
                if( typeof(eval(methodstr))=='function' )
                    return eval(methodstr).apply(jQuery.j7.Actions[checkmethod],arr);
                else
                    alert('function not exist'+methodstr);
            }
            else
            {
                if( $.inArray(checkmethod,j7.Actions['scriptfiles'] )>-1 )
                {
                    arr.unshift(methodname);
                    setTimeout(function(){j7.Action.apply(this,arr)},200);
                }
                else
                {
                    j7.Actions['scriptfiles'].unshift(checkmethod);
                    var sfile = j7actionpath+checkmethod+'.js';
                    jQuery.getScript(sfile,function(){
                        if( typeof(jQuery.j7.Actions[checkmethod][extmethod]) == 'function')
                            return eval(methodstr).apply(jQuery.j7.Actions[checkmethod],arr);
                        else
                            console.log('@!@#'+methodname);
                    });
                }
            }
        }
    };
})(jQuery);


var j7 = jQuery.j7;
if(j7actionpath==undefined){var j7actionpath = '/js/j7actions/';}
if (typeof JSON == 'undefined') {jQuery.getScript('./json2.js');}
function log(s)
{
    if (arguments.length > 1 ) {
        s = arguments[1]+' : '+s;
    }
    if( $('#logshow').length<=0 )
    {
        console.log(s);
    }
    else
    {
        if( typeof s == 'object')
            s = JSON.stringify(s);
        var adddiv = $('<div></div>');
        adddiv.text(s);
        $('#logshow').prepend(adddiv);
    }
}


//jquery.dom.form_params.js

(function( $ ) {
    var radioCheck = /radio|checkbox/i,
        keyBreaker = /[^\[\]]+/g,
        numberMatcher = /^[\-+]?[0-9]*\.?[0-9]+([eE][\-+]?[0-9]+)?$/;

    var isNumber = function( value ) {
        if ( typeof value == 'number' ) {
            return true;
        }

        if ( typeof value != 'string' ) {
            return false;
        }

        return value.match(numberMatcher);
    };

    $.fn.extend({
        /**
         * @parent dom
         * @download http://jmvcsite.heroku.com/pluginify?plugins[]=jquery/dom/form_params/form_params.js
         * Example code:
         *
         *     $('form').formParams() //-> { foo:{bar:'2', ced: '4'} }
         *
         */
        formParams: function( params, convert ) {

            // Quick way to determine if something is a boolean
            if ( !! params === params ) {
                convert = params;
                params = null;
            }

            if ( params ) {
                return this.setParams( params );
            } else if ( this[0].nodeName.toLowerCase() == 'form' && this[0].elements ) {
                return jQuery(jQuery.makeArray(this[0].elements)).getParams(convert);
            }
            return jQuery("input[name], textarea[name], select[name]", this[0]).getParams(convert);
        },
        setParams: function( params ) {

            // Find all the inputs
            this.find("[name]").each(function() {

                var value = params[ $(this).attr("name") ],
                    $this;

                // Don't do all this work if there's no value
                if ( value !== undefined ) {
                    $this = $(this);

                    // Nested these if statements for performance
                    if ( $this.is(":radio") ) {
                        if ( $this.val() == value ) {
                            $this.attr("checked", true);
                        }
                    } else if ( $this.is(":checkbox") ) {
                        // Convert single value to an array to reduce
                        // complexity
                        value = $.isArray( value ) ? value : [value];
                        if ( $.inArray( $this.val(), value ) > -1) {
                            $this.attr("checked", true);
                        }
                    } else {
                        $this.val( value );
                    }
                }
            });
        },
        getParams: function( convert ) {
            var data = {},
                current;

            convert = convert === undefined ? false : convert;

            this.each(function() {
                var el = this,
                    type = el.type && el.type.toLowerCase();
                //if we are submit, ignore
                if ((type == 'submit') || !el.name ) {
                    return;
                }

                var key = el.name,
                    value = $.data(el, "value") || $.fn.val.call([el]),
                    isRadioCheck = radioCheck.test(el.type),
                    parts = key.match(keyBreaker),
                    write = !isRadioCheck || !! el.checked,
                //make an array of values
                    lastPart;

                if ( convert ) {
                    if ( isNumber(value) ) {
                        value = parseFloat(value);
                    } else if ( value === 'true') {
                        value = true;
                    } else if ( value === 'false' ) {
                        value = false;
                    }
                    if(value === '') {
                        value = undefined;
                    }
                }

                // go through and create nested objects
                current = data;
                for ( var i = 0; i < parts.length - 1; i++ ) {
                    if (!current[parts[i]] ) {
                        current[parts[i]] = {};
                    }
                    current = current[parts[i]];
                }
                lastPart = parts[parts.length - 1];

                //now we are on the last part, set the value
                if (current[lastPart]) {
                    if (!$.isArray(current[lastPart]) ) {
                        current[lastPart] = current[lastPart] === undefined ? [] : [current[lastPart]];
                    }
                    if ( write ) {
                        current[lastPart].push(value);
                    }
                } else if ( write || !current[lastPart] ) {

                    current[lastPart] = write ? value : undefined;
                }

            });
            return data;
        }
    });

})(jQuery);
// end //jquery.dom.form_params.js


/*================================================================================
 * @name: bPopup - if you can't get it up, use bPopup
 * @author: (c)Bjoern Klinggaard (twitter@bklinggaard)
 * @demo: http://dinbror.dk/bpopup
 * @version: 0.9.3.min
 ================================================================================*/
(function(b){b.fn.bPopup=function(u,C){function v(){a.modal&&b('<div class="b-modal '+e+'"></div>').css({backgroundColor:a.modalColor,position:"fixed",top:0,right:0,bottom:0,left:0,opacity:0,zIndex:a.zIndex+l}).appendTo(a.appendTo).fadeTo(a.speed,a.opacity);z();c.data("bPopup",a).data("id",e).css({left:"slideIn"===a.transition?-1*(m+h):n(!(!a.follow[0]&&p||g)),position:a.positionStyle||"absolute",top:"slideDown"===a.transition?-1*(q+h):r(!(!a.follow[1]&&s||g)),"z-index":a.zIndex+l+1}).each(function(){a.appending&&b(this).appendTo(a.appendTo)});D(!0)}function t(){a.modal&&b(".b-modal."+c.data("id")).fadeTo(a.speed,0,function(){b(this).remove()});a.scrollBar||b("html").css("overflow","auto");b(".b-modal."+e).unbind("click");j.unbind("keydown."+e);d.unbind("."+e).data("bPopup",0<d.data("bPopup")-1?d.data("bPopup")-1:null);c.undelegate(".bClose, ."+a.closeClass,"click."+e,t).data("bPopup",null);D();return!1}function E(f){var b=f.width(),e=f.height(),d={};a.contentContainer.css({height:e,width:b});e>=c.height()&&(d.height=c.height());b>=c.width()&&(d.width=c.width());w=c.outerHeight(!0);h=c.outerWidth(!0);z();a.contentContainer.css({height:"auto",width:"auto"});d.left=n(!(!a.follow[0]&&p||g));d.top=r(!(!a.follow[1]&&s||g));c.animate(d,250,function(){f.show();x=A()})}function D(f){switch(a.transition){case "slideIn":c.css({display:"block",opacity:1}).animate({left:f?n(!(!a.follow[0]&&p||g)):j.scrollLeft()-(h||c.outerWidth(!0))-200},a.speed,a.easing,function(){B(f)});break;case "slideDown":c.css({display:"block",opacity:1}).animate({top:f?r(!(!a.follow[1]&&s||g)):j.scrollTop()-(w||c.outerHeight(!0))-200},a.speed,a.easing,function(){B(f)});break;default:c.stop().fadeTo(a.speed,f?1:0,function(){B(f)})}}function B(f){f?(d.data("bPopup",l),c.delegate(".bClose, ."+a.closeClass,"click."+e,t),a.modalClose&&b(".b-modal."+e).css("cursor","pointer").bind("click",t),!G&&(a.follow[0]||a.follow[1])&&d.bind("scroll."+e,function(){x&&c.dequeue().animate({left:a.follow[0]?n(!g):"auto",top:a.follow[1]?r(!g):"auto"},a.followSpeed,a.followEasing)}).bind("resize."+e,function(){if(x=A())clearTimeout(F),F=setTimeout(function(){z();c.dequeue().each(function(){g?b(this).css({left:m,top:q}):b(this).animate({left:a.follow[0]?n(!0):"auto",top:a.follow[1]?r(!0):"auto"},a.followSpeed,a.followEasing)})},50)}),a.escClose&&j.bind("keydown."+e,function(a){27==a.which&&t()}),k(C)):(c.hide(),k(a.onClose),a.loadUrl&&(a.contentContainer.empty(),c.css({height:"auto",width:"auto"})))}function n(a){return a?m+j.scrollLeft():m}function r(a){return a?q+j.scrollTop():q}function k(a){b.isFunction(a)&&a.call(c)}function z(){var b;s?b=a.position[1]:(b=((window.innerHeight||d.height())-c.outerHeight(!0))/2-a.amsl,b=b<y?y:b);q=b;m=p?a.position[0]:((window.innerWidth||d.width())-c.outerWidth(!0))/2;x=A()}function A(){return(window.innerHeight||d.height())>c.outerHeight(!0)+y&&(window.innerWidth||d.width())>c.outerWidth(!0)+y}b.isFunction(u)&&(C=u,u=null);var a=b.extend({},b.fn.bPopup.defaults,u);a.scrollBar||b("html").css("overflow","hidden");var c=this,j=b(document),d=b(window),G=/OS 6(_\d)+/i.test(navigator.userAgent),y=20,l=0,e,x,s,p,g,q,m,w,h,F;c.close=function(){a=this.data("bPopup");e="__b-popup"+d.data("bPopup")+"__";t()};return c.each(function(){if(!b(this).data("bPopup"))if(k(a.onOpen),l=(d.data("bPopup")||0)+1,e="__b-popup"+l+"__",s="auto"!==a.position[1],p="auto"!==a.position[0],g="fixed"===a.positionStyle,w=c.outerHeight(!0),h=c.outerWidth(!0),a.loadUrl)switch(a.contentContainer=b(a.contentContainer||c),a.content){case "iframe":var f=b('<iframe class="b-iframe" scrolling="no" frameborder="0"></iframe>');f.appendTo(a.contentContainer);w=c.outerHeight(!0);h=c.outerWidth(!0);v();f.attr("src",a.loadUrl);k(a.loadCallback);break;case "image":v();b("<img />").load(function(){k(a.loadCallback);E(b(this))}).attr("src",a.loadUrl).hide().appendTo(a.contentContainer);break;default:v(),b('<div class="b-ajax-wrapper"></div>').load(a.loadUrl,a.loadData,function(){k(a.loadCallback);E(b(this))}).hide().appendTo(a.contentContainer)}else v()})};b.fn.bPopup.defaults={amsl:50,appending:!0,appendTo:"body",closeClass:"b-close",content:"ajax",contentContainer:!1,easing:"swing",escClose:!0,follow:[!0,!0],followEasing:"swing",followSpeed:500,loadCallback:!1,loadData:!1,loadUrl:!1,modal:!0,modalClose:!0,modalColor:"#000",onClose:!1,onOpen:!1,opacity:0.7,position:["auto","auto"],positionStyle:"absolute",scrollBar:!0,speed:250,transition:"fadeIn",zIndex:9997}})(jQuery);






/*
 * 具体方法参考 http://dinbror.dk/blog/bPopup/
 * 添加的几个属性:
 * hideclose 不显示 close 按钮
 * text 仅文字内容
 */
function popup(config)
{
    if( config.close === true )
    {
        if( $('#_popupdiv').is(":visible") )
            $('#_popupdiv').bPopup().close();
    }
    else
    {
        if( config.delayclose !== undefined ){ setTimeout("popup({'close':true})",config.delayclose); }
        if( config.hideclose === true ){ $('#_popupdiv_close').hide(); }else{ $('#_popupdiv_close').show(); }
        if( config.text === undefined ){ $('#_popupcontent').html(''); }else{ $('#_popupcontent').html(config.text); }
        $('#_popupdiv').bPopup(config);
    }
}

//全站错误弹出
function error(str,tp)
{
    alert(str);
}

/**数组根据数组对象中的某个属性值进行排序的方法
 * 使用例子：newArray.sort(sortBy('number',false)) //表示根据number属性降序排列;若第二个参数不传递，默认表示升序排序
 * @param attr 排序的属性 如number属性
 * @param rev true表示升序排列，false降序排序
 * */
function sortBy(attr,rev){
  //第二个参数没有传递 默认升序排列
  if(rev ==  undefined){
    rev = 1;
  }else{
    rev = (rev) ? 1 : -1;
  }

  return function(a,b){
    a = a[attr];
    b = b[attr];
    if(a < b){
      return rev * -1;
    }
    if(a > b){
      return rev * 1;
    }
    return 0;
  }
}

/**
 * 时间格式化
 * http://phpjs.org/functions/date/
 * @param format
 * @param timestamp
 * @return {*}
 */
function date (format, timestamp) {
    // *     example 1: date('H:m:s \\m \\i\\s \\m\\o\\n\\t\\h', 1062402400);
    // *     returns 1: '09:09:40 m is month'
    // *     example 2: date('F j, Y, g:i a', 1062462400);
    // *     returns 2: 'September 2, 2003, 2:26 am'
    // *     example 3: date('Y W o', 1062462400);
    // *     returns 3: '2003 36 2003'
    // *     example 4: x = date('Y m d', (new Date()).getTime()/1000);
    // *     example 4: (x+'').length == 10 // 2009 01 09
    // *     returns 4: true
    // *     example 5: date('W', 1104534000);
    // *     returns 5: '53'
    // *     example 6: date('B t', 1104534000);
    // *     returns 6: '999 31'
    // *     example 7: date('W U', 1293750000.82); // 2010-12-31
    // *     returns 7: '52 1293750000'
    // *     example 8: date('W', 1293836400); // 2011-01-01
    // *     returns 8: '52'
    // *     example 9: date('W Y-m-d', 1293974054); // 2011-01-02
    // *     returns 9: '52 2011-01-02'
    var that = this,
        jsdate,
        f,
        formatChr = /\\?([a-z])/gi,
        formatChrCb,
    // Keep this here (works, but for code commented-out
    // below for file size reasons)
    //, tal= [],
        _pad = function (n, c) {
            n = n.toString();
            return n.length < c ? _pad('0' + n, c, '0') : n;
        },
        txt_words = ["Sun", "Mon", "Tues", "Wednes", "Thurs", "Fri", "Satur", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    formatChrCb = function (t, s) {
        return f[t] ? f[t]() : s;
    };
    f = {
        // Day
        d: function () { // Day of month w/leading 0; 01..31
            return _pad(f.j(), 2);
        },
        D: function () { // Shorthand day name; Mon...Sun
            return f.l().slice(0, 3);
        },
        j: function () { // Day of month; 1..31
            return jsdate.getDate();
        },
        l: function () { // Full day name; Monday...Sunday
            return txt_words[f.w()] + 'day';
        },
        N: function () { // ISO-8601 day of week; 1[Mon]..7[Sun]
            return f.w() || 7;
        },
        S: function(){ // Ordinal suffix for day of month; st, nd, rd, th
            var j = f.j()
            i = j%10;
            if (i <= 3 && parseInt((j%100)/10) == 1) i = 0;
            return ['st', 'nd', 'rd'][i - 1] || 'th';
        },
        w: function () { // Day of week; 0[Sun]..6[Sat]
            return jsdate.getDay();
        },
        z: function () { // Day of year; 0..365
            var a = new Date(f.Y(), f.n() - 1, f.j()),
                b = new Date(f.Y(), 0, 1);
            return Math.round((a - b) / 864e5);
        },

        // Week
        W: function () { // ISO-8601 week number
            var a = new Date(f.Y(), f.n() - 1, f.j() - f.N() + 3),
                b = new Date(a.getFullYear(), 0, 4);
            return _pad(1 + Math.round((a - b) / 864e5 / 7), 2);
        },

        // Month
        F: function () { // Full month name; January...December
            return txt_words[6 + f.n()];
        },
        m: function () { // Month w/leading 0; 01...12
            return _pad(f.n(), 2);
        },
        M: function () { // Shorthand month name; Jan...Dec
            return f.F().slice(0, 3);
        },
        n: function () { // Month; 1...12
            return jsdate.getMonth() + 1;
        },
        t: function () { // Days in month; 28...31
            return (new Date(f.Y(), f.n(), 0)).getDate();
        },

        // Year
        L: function () { // Is leap year?; 0 or 1
            var j = f.Y();
            return j % 4 === 0 & j % 100 !== 0 | j % 400 === 0;
        },
        o: function () { // ISO-8601 year
            var n = f.n(),
                W = f.W(),
                Y = f.Y();
            return Y + (n === 12 && W < 9 ? 1 : n === 1 && W > 9 ? -1 : 0);
        },
        Y: function () { // Full year; e.g. 1980...2010
            return jsdate.getFullYear();
        },
        y: function () { // Last two digits of year; 00...99
            return f.Y().toString().slice(-2);
        },

        // Time
        a: function () { // am or pm
            return jsdate.getHours() > 11 ? "pm" : "am";
        },
        A: function () { // AM or PM
            return f.a().toUpperCase();
        },
        B: function () { // Swatch Internet time; 000..999
            var H = jsdate.getUTCHours() * 36e2,
            // Hours
                i = jsdate.getUTCMinutes() * 60,
            // Minutes
                s = jsdate.getUTCSeconds(); // Seconds
            return _pad(Math.floor((H + i + s + 36e2) / 86.4) % 1e3, 3);
        },
        g: function () { // 12-Hours; 1..12
            return f.G() % 12 || 12;
        },
        G: function () { // 24-Hours; 0..23
            return jsdate.getHours();
        },
        h: function () { // 12-Hours w/leading 0; 01..12
            return _pad(f.g(), 2);
        },
        H: function () { // 24-Hours w/leading 0; 00..23
            return _pad(f.G(), 2);
        },
        i: function () { // Minutes w/leading 0; 00..59
            return _pad(jsdate.getMinutes(), 2);
        },
        s: function () { // Seconds w/leading 0; 00..59
            return _pad(jsdate.getSeconds(), 2);
        },
        u: function () { // Microseconds; 000000-999000
            return _pad(jsdate.getMilliseconds() * 1000, 6);
        },

        // Timezone
        e: function () { // Timezone identifier; e.g. Atlantic/Azores, ...
            // The following works, but requires inclusion of the very large
            // timezone_abbreviations_list() function.
            /*              return that.date_default_timezone_get();
             */
            throw 'Not supported (see source code of date() for timezone on how to add support)';
        },
        I: function () { // DST observed?; 0 or 1
            // Compares Jan 1 minus Jan 1 UTC to Jul 1 minus Jul 1 UTC.
            // If they are not equal, then DST is observed.
            var a = new Date(f.Y(), 0),
            // Jan 1
                c = Date.UTC(f.Y(), 0),
            // Jan 1 UTC
                b = new Date(f.Y(), 6),
            // Jul 1
                d = Date.UTC(f.Y(), 6); // Jul 1 UTC
            return ((a - c) !== (b - d)) ? 1 : 0;
        },
        O: function () { // Difference to GMT in hour format; e.g. +0200
            var tzo = jsdate.getTimezoneOffset(),
                a = Math.abs(tzo);
            return (tzo > 0 ? "-" : "+") + _pad(Math.floor(a / 60) * 100 + a % 60, 4);
        },
        P: function () { // Difference to GMT w/colon; e.g. +02:00
            var O = f.O();
            return (O.substr(0, 3) + ":" + O.substr(3, 2));
        },
        T: function () { // Timezone abbreviation; e.g. EST, MDT, ...
            // The following works, but requires inclusion of the very
            // large timezone_abbreviations_list() function.
            /*              var abbr = '', i = 0, os = 0, default = 0;
             if (!tal.length) {
             tal = that.timezone_abbreviations_list();
             }
             if (that.php_js && that.php_js.default_timezone) {
             default = that.php_js.default_timezone;
             for (abbr in tal) {
             for (i=0; i < tal[abbr].length; i++) {
             if (tal[abbr][i].timezone_id === default) {
             return abbr.toUpperCase();
             }
             }
             }
             }
             for (abbr in tal) {
             for (i = 0; i < tal[abbr].length; i++) {
             os = -jsdate.getTimezoneOffset() * 60;
             if (tal[abbr][i].offset === os) {
             return abbr.toUpperCase();
             }
             }
             }
             */
            return 'UTC';
        },
        Z: function () { // Timezone offset in seconds (-43200...50400)
            return -jsdate.getTimezoneOffset() * 60;
        },

        // Full Date/Time
        c: function () { // ISO-8601 date.
            return 'Y-m-d\\TH:i:sP'.replace(formatChr, formatChrCb);
        },
        r: function () { // RFC 2822
            return 'D, d M Y H:i:s O'.replace(formatChr, formatChrCb);
        },
        U: function () { // Seconds since UNIX epoch
            return jsdate / 1000 | 0;
        }
    };
    this.date = function (format, timestamp) {
        that = this;
        jsdate = (timestamp === undefined ? new Date() : // Not provided
            (timestamp instanceof Date) ? new Date(timestamp) : // JS Date()
                new Date(timestamp * 1000) // UNIX timestamp (auto-convert to int)
            );
        return format.replace(formatChr, formatChrCb);
    };
    return this.date(format, timestamp);
}


/**
 * jQuery 扩展方法
 *
 * $.Object.count( p )
 * 获取一个对象的长度，需要指定上下文，通过 call/apply 调用
 * 示例: $.Object.count.call( obj, true );
 * @param {p} 是否跳过 null / undefined / 空值
 *
 */
$.extend({
    // 获取对象的长度，需要指定上下文 this
    Object: {
        count: function( p ) {
            p = p || false;
            return $.map( this, function(o) {
                if( !p ) return o;

                return true;
            } ).length;
        }
    },
    browser:{
        msie:function () {
            return /msie/.test(navigator.userAgent.toLowerCase());
        },
        opera:function () {
            return /opera/.test(navigator.userAgent.toLowerCase());
        },
        webkit:function () {
            return /webkit/.test(navigator.userAgent.toLowerCase());
        },
        mozilla:function () {
            return /firefox/.test(navigator.userAgent.toLowerCase());
        },
        safari:function () {
            return /safari/.test(navigator.userAgent.toLowerCase());
        }
    }
});