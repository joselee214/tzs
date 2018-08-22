if (typeof __$coverObject === "undefined"){
	if (typeof window !== "undefined") window.__$coverObject = {};
	else if (typeof global !== "undefined") global.__$coverObject = {};
	else throw new Error("cannot find the global scope");
}
var __$coverInit = function(name, code){
	if (!__$coverObject[name]) __$coverObject[name] = {__code: code};
};
var __$coverInitRange = function(name, range){
	if (!__$coverObject[name][range]) __$coverObject[name][range] = 0;
};
var __$coverCall = function(name, range){
	__$coverObject[name][range]++;
};
__$coverInit("Plupload", "/**\n * Plupload.js\n *\n * Copyright 2013, Moxiecode Systems AB\n * Released under GPL License.\n *\n * License: http://www.plupload.com/license\n * Contributing: http://www.plupload.com/contributing\n */\n\n/*global mOxie:true */\n\n;(function(window, o, undef) {\n\nvar delay = window.setTimeout;\n\n// convert plupload features to caps acceptable by mOxie\nfunction normalizeCaps(settings) {\t\t\n\tvar features = settings.required_features, caps = {};\n\n\tfunction resolve(feature, value, strict) {\n\t\t// Feature notation is deprecated, use caps (this thing here is required for backward compatibility)\n\t\tvar map = { \n\t\t\tchunks: 'slice_blob',\n\t\t\tresize: 'send_binary_string',\n\t\t\tjpgresize: 'send_binary_string',\n\t\t\tpngresize: 'send_binary_string',\n\t\t\tprogress: 'report_upload_progress',\n\t\t\tmulti_selection: 'select_multiple',\n\t\t\tmax_file_size: 'access_binary',\n\t\t\tdragdrop: 'drag_and_drop',\n\t\t\tdrop_element: 'drag_and_drop',\n\t\t\theaders: 'send_custom_headers',\n\t\t\tcanSendBinary: 'send_binary',\n\t\t\ttriggerDialog: 'summon_file_dialog'\n\t\t};\n\n\t\tif (map[feature]) {\n\t\t\tcaps[map[feature]] = value;\n\t\t} else if (!strict) {\n\t\t\tcaps[feature] = value;\n\t\t}\n\t}\n\n\tif (typeof(features) === 'string') {\n\t\tplupload.each(features.split(/\\s*,\\s*/), function(feature) {\n\t\t\tresolve(feature, true);\n\t\t});\n\t} else if (typeof(features) === 'object') {\n\t\tplupload.each(features, function(value, feature) {\n\t\t\tresolve(feature, value);\n\t\t});\n\t} else if (features === true) {\n\t\t// check settings for required features\n\t\tif (!settings.multipart) { // special care for multipart: false\n\t\t\tcaps.send_binary_string = true;\n\t\t}\n\n\t\tif (settings.chunk_size > 0) {\n\t\t\tcaps.slice_blob = true;\n\t\t}\n\t\t\n\t\tplupload.each(settings, function(value, feature) {\n\t\t\tresolve(feature, !!value, true); // strict check\n\t\t});\n\t}\n\t\n\treturn caps;\n}\n\n/** \n * @module plupload\t\n * @static\n */\nvar plupload = {\n\t/**\n\t * Plupload version will be replaced on build.\n\t *\n\t * @property VERSION\n\t * @for Plupload\n\t * @static\n\t * @final\n\t */\n\tVERSION : '@@version@@',\n\n\t/**\n\t * Inital state of the queue and also the state ones it's finished all it's uploads.\n\t *\n\t * @property STOPPED\n\t * @static\n\t * @final\n\t */\n\tSTOPPED : 1,\n\n\t/**\n\t * Upload process is running\n\t *\n\t * @property STARTED\n\t * @static\n\t * @final\n\t */\n\tSTARTED : 2,\n\n\t/**\n\t * File is queued for upload\n\t *\n\t * @property QUEUED\n\t * @static\n\t * @final\n\t */\n\tQUEUED : 1,\n\n\t/**\n\t * File is being uploaded\n\t *\n\t * @property UPLOADING\n\t * @static\n\t * @final\n\t */\n\tUPLOADING : 2,\n\n\t/**\n\t * File has failed to be uploaded\n\t *\n\t * @property FAILED\n\t * @static\n\t * @final\n\t */\n\tFAILED : 4,\n\n\t/**\n\t * File has been uploaded successfully\n\t *\n\t * @property DONE\n\t * @static\n\t * @final\n\t */\n\tDONE : 5,\n\n\t// Error constants used by the Error event\n\n\t/**\n\t * Generic error for example if an exception is thrown inside Silverlight.\n\t *\n\t * @property GENERIC_ERROR\n\t * @static\n\t * @final\n\t */\n\tGENERIC_ERROR : -100,\n\n\t/**\n\t * HTTP transport error. For example if the server produces a HTTP status other than 200.\n\t *\n\t * @property HTTP_ERROR\n\t * @static\n\t * @final\n\t */\n\tHTTP_ERROR : -200,\n\n\t/**\n\t * Generic I/O error. For exampe if it wasn't possible to open the file stream on local machine.\n\t *\n\t * @property IO_ERROR\n\t * @static\n\t * @final\n\t */\n\tIO_ERROR : -300,\n\n\t/**\n\t * Generic I/O error. For exampe if it wasn't possible to open the file stream on local machine.\n\t *\n\t * @property SECURITY_ERROR\n\t * @static\n\t * @final\n\t */\n\tSECURITY_ERROR : -400,\n\n\t/**\n\t * Initialization error. Will be triggered if no runtime was initialized.\n\t *\n\t * @property INIT_ERROR\n\t * @static\n\t * @final\n\t */\n\tINIT_ERROR : -500,\n\n\t/**\n\t * File size error. If the user selects a file that is too large it will be blocked and an error of this type will be triggered.\n\t *\n\t * @property FILE_SIZE_ERROR\n\t * @static\n\t * @final\n\t */\n\tFILE_SIZE_ERROR : -600,\n\n\t/**\n\t * File extension error. If the user selects a file that isn't valid according to the filters setting.\n\t *\n\t * @property FILE_EXTENSION_ERROR\n\t * @static\n\t * @final\n\t */\n\tFILE_EXTENSION_ERROR : -601,\n\n\t/**\n\t * Duplicate file error. If prevent_duplicates is set to true and user selects the same file again.\n\t *\n\t * @property FILE_DUPLICATE_ERROR\n\t * @static\n\t * @final\n\t */\n\tFILE_DUPLICATE_ERROR : -602,\n\n\t/**\n\t * Runtime will try to detect if image is proper one. Otherwise will throw this error.\n\t *\n\t * @property IMAGE_FORMAT_ERROR\n\t * @static\n\t * @final\n\t */\n\tIMAGE_FORMAT_ERROR : -700,\n\n\t/**\n\t * While working on the image runtime will try to detect if the operation may potentially run out of memeory and will throw this error.\n\t *\n\t * @property IMAGE_MEMORY_ERROR\n\t * @static\n\t * @final\n\t */\n\tIMAGE_MEMORY_ERROR : -701,\n\n\t/**\n\t * Each runtime has an upper limit on a dimension of the image it can handle. If bigger, will throw this error.\n\t *\n\t * @property IMAGE_DIMENSIONS_ERROR\n\t * @static\n\t * @final\n\t */\n\tIMAGE_DIMENSIONS_ERROR : -702,\n\n\t/**\n\t * Mime type lookup table.\n\t *\n\t * @property mimeTypes\n\t * @type Object\n\t * @final\n\t */\n\tmimeTypes : o.mimes,\n\n\t/**\n\t * In some cases sniffing is the only way around :(\n\t */\n\tua: o.ua,\n\n\t/**\n\t * Gets the true type of the built-in object (better version of typeof).\n\t * @credits Angus Croll (http://javascriptweblog.wordpress.com/)\n\t *\n\t * @method typeOf\n\t * @static\n\t * @param {Object} o Object to check.\n\t * @return {String} Object [[Class]]\n\t */\n\ttypeOf: o.typeOf,\n\n\t/**\n\t * Extends the specified object with another object.\n\t *\n\t * @method extend\n\t * @static\n\t * @param {Object} target Object to extend.\n\t * @param {Object..} obj Multiple objects to extend with.\n\t * @return {Object} Same as target, the extended object.\n\t */\n\textend : o.extend,\n\n\t/**\n\t * Generates an unique ID. This is 99.99% unique since it takes the current time and 5 random numbers.\n\t * The only way a user would be able to get the same ID is if the two persons at the same exact milisecond manages\n\t * to get 5 the same random numbers between 0-65535 it also uses a counter so each call will be guaranteed to be page unique.\n\t * It's more probable for the earth to be hit with an ansteriod. You can also if you want to be 100% sure set the plupload.guidPrefix property\n\t * to an user unique key.\n\t *\n\t * @method guid\n\t * @static\n\t * @return {String} Virtually unique id.\n\t */\n\tguid : o.guid,\n\n\t/**\n\t * Executes the callback function for each item in array/object. If you return false in the\n\t * callback it will break the loop.\n\t *\n\t * @method each\n\t * @static\n\t * @param {Object} obj Object to iterate.\n\t * @param {function} callback Callback function to execute for each item.\n\t */\n\teach : o.each,\n\n\t/**\n\t * Returns the absolute x, y position of an Element. The position will be returned in a object with x, y fields.\n\t *\n\t * @method getPos\n\t * @static\n\t * @param {Element} node HTML element or element id to get x, y position from.\n\t * @param {Element} root Optional root element to stop calculations at.\n\t * @return {object} Absolute position of the specified element object with x, y fields.\n\t */\n\tgetPos : o.getPos,\n\n\t/**\n\t * Returns the size of the specified node in pixels.\n\t *\n\t * @method getSize\n\t * @static\n\t * @param {Node} node Node to get the size of.\n\t * @return {Object} Object with a w and h property.\n\t */\n\tgetSize : o.getSize,\n\n\t/**\n\t * Encodes the specified string.\n\t *\n\t * @method xmlEncode\n\t * @static\n\t * @param {String} s String to encode.\n\t * @return {String} Encoded string.\n\t */\n\txmlEncode : function(str) {\n\t\tvar xmlEncodeChars = {'<' : 'lt', '>' : 'gt', '&' : 'amp', '\"' : 'quot', '\\'' : '#39'}, xmlEncodeRegExp = /[<>&\\\"\\']/g;\n\n\t\treturn str ? ('' + str).replace(xmlEncodeRegExp, function(chr) {\n\t\t\treturn xmlEncodeChars[chr] ? '&' + xmlEncodeChars[chr] + ';' : chr;\n\t\t}) : str;\n\t},\n\n\t/**\n\t * Forces anything into an array.\n\t *\n\t * @method toArray\n\t * @static\n\t * @param {Object} obj Object with length field.\n\t * @return {Array} Array object containing all items.\n\t */\n\ttoArray : o.toArray,\n\n\t/**\n\t * Find an element in array and return it's index if present, otherwise return -1.\n\t *\n\t * @method inArray\n\t * @static\n\t * @param {mixed} needle Element to find\n\t * @param {Array} array\n\t * @return {Int} Index of the element, or -1 if not found\n\t */\n\tinArray : o.inArray,\n\n\t/**\n\t * Extends the language pack object with new items.\n\t *\n\t * @method addI18n\n\t * @static\n\t * @param {Object} pack Language pack items to add.\n\t * @return {Object} Extended language pack object.\n\t */\n\taddI18n : o.addI18n,\n\n\t/**\n\t * Translates the specified string by checking for the english string in the language pack lookup.\n\t *\n\t * @method translate\n\t * @static\n\t * @param {String} str String to look for.\n\t * @return {String} Translated string or the input string if it wasn't found.\n\t */\n\ttranslate : o.translate,\n\n\t/**\n\t * Checks if object is empty.\n\t *\n\t * @method isEmptyObj\n\t * @static\n\t * @param {Object} obj Object to check.\n\t * @return {Boolean}\n\t */\n\tisEmptyObj : o.isEmptyObj,\n\n\t/**\n\t * Checks if specified DOM element has specified class.\n\t *\n\t * @method hasClass\n\t * @static\n\t * @param {Object} obj DOM element like object to add handler to.\n\t * @param {String} name Class name\n\t */\n\thasClass : o.hasClass,\n\n\t/**\n\t * Adds specified className to specified DOM element.\n\t *\n\t * @method addClass\n\t * @static\n\t * @param {Object} obj DOM element like object to add handler to.\n\t * @param {String} name Class name\n\t */\n\taddClass : o.addClass,\n\n\t/**\n\t * Removes specified className from specified DOM element.\n\t *\n\t * @method removeClass\n\t * @static\n\t * @param {Object} obj DOM element like object to add handler to.\n\t * @param {String} name Class name\n\t */\n\tremoveClass : o.removeClass,\n\n\t/**\n\t * Returns a given computed style of a DOM element.\n\t *\n\t * @method getStyle\n\t * @static\n\t * @param {Object} obj DOM element like object.\n\t * @param {String} name Style you want to get from the DOM element\n\t */\n\tgetStyle : o.getStyle,\n\n\t/**\n\t * Adds an event handler to the specified object and store reference to the handler\n\t * in objects internal Plupload registry (@see removeEvent).\n\t *\n\t * @method addEvent\n\t * @static\n\t * @param {Object} obj DOM element like object to add handler to.\n\t * @param {String} name Name to add event listener to.\n\t * @param {Function} callback Function to call when event occurs.\n\t * @param {String} (optional) key that might be used to add specifity to the event record.\n\t */\n\taddEvent : o.addEvent,\n\n\t/**\n\t * Remove event handler from the specified object. If third argument (callback)\n\t * is not specified remove all events with the specified name.\n\t *\n\t * @method removeEvent\n\t * @static\n\t * @param {Object} obj DOM element to remove event listener(s) from.\n\t * @param {String} name Name of event listener to remove.\n\t * @param {Function|String} (optional) might be a callback or unique key to match.\n\t */\n\tremoveEvent: o.removeEvent,\n\n\t/**\n\t * Remove all kind of events from the specified object\n\t *\n\t * @method removeAllEvents\n\t * @static\n\t * @param {Object} obj DOM element to remove event listeners from.\n\t * @param {String} (optional) unique key to match, when removing events.\n\t */\n\tremoveAllEvents: o.removeAllEvents,\n\n\t/**\n\t * Cleans the specified name from national characters (diacritics). The result will be a name with only a-z, 0-9 and _.\n\t *\n\t * @method cleanName\n\t * @static\n\t * @param {String} s String to clean up.\n\t * @return {String} Cleaned string.\n\t */\n\tcleanName : function(name) {\n\t\tvar i, lookup;\n\n\t\t// Replace diacritics\n\t\tlookup = [\n\t\t\t/[\\300-\\306]/g, 'A', /[\\340-\\346]/g, 'a',\n\t\t\t/\\307/g, 'C', /\\347/g, 'c',\n\t\t\t/[\\310-\\313]/g, 'E', /[\\350-\\353]/g, 'e',\n\t\t\t/[\\314-\\317]/g, 'I', /[\\354-\\357]/g, 'i',\n\t\t\t/\\321/g, 'N', /\\361/g, 'n',\n\t\t\t/[\\322-\\330]/g, 'O', /[\\362-\\370]/g, 'o',\n\t\t\t/[\\331-\\334]/g, 'U', /[\\371-\\374]/g, 'u'\n\t\t];\n\n\t\tfor (i = 0; i < lookup.length; i += 2) {\n\t\t\tname = name.replace(lookup[i], lookup[i + 1]);\n\t\t}\n\n\t\t// Replace whitespace\n\t\tname = name.replace(/\\s+/g, '_');\n\n\t\t// Remove anything else\n\t\tname = name.replace(/[^a-z0-9_\\-\\.]+/gi, '');\n\n\t\treturn name;\n\t},\n\n\t/**\n\t * Builds a full url out of a base URL and an object with items to append as query string items.\n\t *\n\t * @method buildUrl\n\t * @static\n\t * @param {String} url Base URL to append query string items to.\n\t * @param {Object} items Name/value object to serialize as a querystring.\n\t * @return {String} String with url + serialized query string items.\n\t */\n\tbuildUrl : function(url, items) {\n\t\tvar query = '';\n\n\t\tplupload.each(items, function(value, name) {\n\t\t\tquery += (query ? '&' : '') + encodeURIComponent(name) + '=' + encodeURIComponent(value);\n\t\t});\n\n\t\tif (query) {\n\t\t\turl += (url.indexOf('?') > 0 ? '&' : '?') + query;\n\t\t}\n\n\t\treturn url;\n\t},\n\n\t/**\n\t * Formats the specified number as a size string for example 1024 becomes 1 KB.\n\t *\n\t * @method formatSize\n\t * @static\n\t * @param {Number} size Size to format as string.\n\t * @return {String} Formatted size string.\n\t */\n\tformatSize : function(size) {\n\t\tif (size === undef || /\\D/.test(size)) {\n\t\t\treturn plupload.translate('N/A');\n\t\t}\n\n\t\t// TB\n\t\tif (size > 1099511627776) {\n\t\t\treturn Math.round(size / 1099511627776, 1) + \" \" + plupload.translate('tb');\n\t\t}\n\n\t\t// GB\n\t\tif (size > 1073741824) {\n\t\t\treturn Math.round(size / 1073741824, 1) + \" \" + plupload.translate('gb');\n\t\t}\n\n\t\t// MB\n\t\tif (size > 1048576) {\n\t\t\treturn Math.round(size / 1048576, 1) + \" \" + plupload.translate('mb');\n\t\t}\n\n\t\t// KB\n\t\tif (size > 1024) {\n\t\t\treturn Math.round(size / 1024, 1) + \" \" + plupload.translate('kb');\n\t\t}\n\n\t\treturn size + \" \" + plupload.translate('b');\n\t},\n\n\n\t/**\n\t * Parses the specified size string into a byte value. For example 10kb becomes 10240.\n\t *\n\t * @method parseSize\n\t * @static\n\t * @param {String|Number} size String to parse or number to just pass through.\n\t * @return {Number} Size in bytes.\n\t */\n\tparseSize : o.parseSizeStr,\n\n\n\t/**\n\t * A way to predict what runtime will be choosen in the current environment with the\n\t * specified settings.\n\t *\n\t * @method predictRuntime\n\t * @static\n\t * @param {Object|String} config Plupload settings to check\n\t * @param {String} [runtimes] Comma-separated list of runtimes to check against\n\t * @return {String} Type of compatible runtime\n\t */\n\tpredictRuntime : function(config, runtimes) {\n\t\tvar up, runtime; \n\t\tif (runtimes) {\n\t\t\tconfig.runtimes = runtimes;\n\t\t}\n\t\tup = new plupload.Uploader(config);\n\t\truntime = up.runtime;\n\t\tup.destroy();\n\t\treturn runtime;\n\t}\n};\n\n\n/**\n@class Uploader\n@constructor\n\n@param {Object} settings For detailed information about each option check documentation.\n\t@param {String|DOMElement} settings.browse_button id of the DOM element or DOM element itself to use as file dialog trigger.\n\t@param {String} settings.url URL of the server-side upload handler.\n\t@param {Number|String} [settings.chunk_size=0] Chunk size in bytes to slice the file into. Shorcuts with b, kb, mb, gb, tb suffixes also supported. `e.g. 204800 or \"204800b\" or \"200kb\"`. By default - disabled.\n\t@param {String} [settings.container] id of the DOM element to use as a container for uploader structures. Defaults to document.body.\n\t@param {String|DOMElement} [settings.drop_element] id of the DOM element or DOM element itself to use as a drop zone for Drag-n-Drop.\n\t@param {String} [settings.file_data_name=\"file\"] Name for the file field in Multipart formated message.\n\t@param {Array} [settings.filters=[]] Set of file type filters, each one defined by hash of title and extensions. `e.g. {title : \"Image files\", extensions : \"jpg,jpeg,gif,png\"}`. Dispatches `plupload.FILE_EXTENSION_ERROR`\n\t@param {String} [settings.flash_swf_url] URL of the Flash swf.\n\t@param {Object} [settings.headers] Custom headers to send with the upload. Hash of name/value pairs.\n\t@param {Number|String} [settings.max_file_size] Maximum file size that the user can pick, in bytes. Optionally supports b, kb, mb, gb, tb suffixes. `e.g. \"10mb\" or \"1gb\"`. By default - not set. Dispatches `plupload.FILE_SIZE_ERROR`.\n\t@param {Number} [settings.max_retries=0] How many times to retry the chunk or file, before triggering Error event.\n\t@param {Boolean} [settings.multipart=true] Whether to send file and additional parameters as Multipart formated message.\n\t@param {Object} [settings.multipart_params] Hash of key/value pairs to send with every file upload.\n\t@param {Boolean} [settings.multi_selection=true] Enable ability to select multiple files at once in file dialog.\n\t@param {Boolean} [settings.prevent_duplicates=false] Do not let duplicates into the queue. Dispatches `plupload.FILE_DUPLICATE_ERROR`.\n\t@param {String|Object} [settings.required_features] Either comma-separated list or hash of required features that chosen runtime should absolutely possess.\n\t@param {Object} [settings.resize] Enable resizng of images on client-side. Applies to `image/jpeg` and `image/png` only. `e.g. {width : 200, height : 200, quality : 90, crop: true}`\n\t\t@param {Number} [settings.resize.width] If image is bigger, it will be resized.\n\t\t@param {Number} [settings.resize.height] If image is bigger, it will be resized.\n\t\t@param {Number} [settings.resize.quality=90] Compression quality for jpegs (1-100).\n\t\t@param {Boolean} [settings.resize.crop=false] Whether to crop images to exact dimensions. By default they will be resized proportionally.\n\t@param {String} [settings.runtimes=\"html5,flash,silverlight,html4\"] Comma separated list of runtimes, that Plupload will try in turn, moving to the next if previous fails.\n\t@param {String} [settings.silverlight_xap_url] URL of the Silverlight xap.\n\t@param {Boolean} [settings.unique_names=false] If true will generate unique filenames for uploaded files.\n*/\nplupload.Uploader = function(settings) {\n\t/**\n\t * Fires when the current RunTime has been initialized.\n\t *\n\t * @event Init\n\t * @param {plupload.Uploader} uploader Uploader instance sending the event.\n\t */\n\n\t/**\n\t * Fires after the init event incase you need to perform actions there.\n\t *\n\t * @event PostInit\n\t * @param {plupload.Uploader} uploader Uploader instance sending the event.\n\t */\n\n\t/**\n\t * Fires when the silverlight/flash or other shim needs to move.\n\t *\n\t * @event Refresh\n\t * @param {plupload.Uploader} uploader Uploader instance sending the event.\n\t */\n\n\t/**\n\t * Fires when the overall state is being changed for the upload queue.\n\t *\n\t * @event StateChanged\n\t * @param {plupload.Uploader} uploader Uploader instance sending the event.\n\t */\n\n\t/**\n\t * Fires when a file is to be uploaded by the runtime.\n\t *\n\t * @event UploadFile\n\t * @param {plupload.Uploader} uploader Uploader instance sending the event.\n\t * @param {plupload.File} file File to be uploaded.\n\t */\n\n\t/**\n\t * Fires when just before a file is uploaded. This event enables you to override settings\n\t * on the uploader instance before the file is uploaded.\n\t *\n\t * @event BeforeUpload\n\t * @param {plupload.Uploader} uploader Uploader instance sending the event.\n\t * @param {plupload.File} file File to be uploaded.\n\t */\n\n\t/**\n\t * Fires when the file queue is changed. In other words when files are added/removed to the files array of the uploader instance.\n\t *\n\t * @event QueueChanged\n\t * @param {plupload.Uploader} uploader Uploader instance sending the event.\n\t */\n\n\t/**\n\t * Fires while a file is being uploaded. Use this event to update the current file upload progress.\n\t *\n\t * @event UploadProgress\n\t * @param {plupload.Uploader} uploader Uploader instance sending the event.\n\t * @param {plupload.File} file File that is currently being uploaded.\n\t */\n\n\t/**\n\t * Fires while a file was removed from queue.\n\t *\n\t * @event FilesRemoved\n\t * @param {plupload.Uploader} uploader Uploader instance sending the event.\n\t * @param {Array} files Array of files that got removed.\n\t */\n\n\t/**\n\t * Fires while when the user selects files to upload.\n\t *\n\t * @event FilesAdded\n\t * @param {plupload.Uploader} uploader Uploader instance sending the event.\n\t * @param {Array} files Array of file objects that was added to queue/selected by the user.\n\t */\n\n\t/**\n\t * Fires when a file is successfully uploaded.\n\t *\n\t * @event FileUploaded\n\t * @param {plupload.Uploader} uploader Uploader instance sending the event.\n\t * @param {plupload.File} file File that was uploaded.\n\t * @param {Object} response Object with response properties.\n\t */\n\n\t/**\n\t * Fires when file chunk is uploaded.\n\t *\n\t * @event ChunkUploaded\n\t * @param {plupload.Uploader} uploader Uploader instance sending the event.\n\t * @param {plupload.File} file File that the chunk was uploaded for.\n\t * @param {Object} response Object with response properties.\n\t */\n\n\t/**\n\t * Fires when all files in a queue are uploaded.\n\t *\n\t * @event UploadComplete\n\t * @param {plupload.Uploader} uploader Uploader instance sending the event.\n\t * @param {Array} files Array of file objects that was added to queue/selected by the user.\n\t */\n\n\t/**\n\t * Fires when a error occurs.\n\t *\n\t * @event Error\n\t * @param {plupload.Uploader} uploader Uploader instance sending the event.\n\t * @param {Object} error Contains code, message and sometimes file and other details.\n\t */\n\n\t/**\n\t * Fires when destroy method is called.\n\t *\n\t * @event Destroy\n\t * @param {plupload.Uploader} uploader Uploader instance sending the event.\n\t */\n\tvar files = [], events = {}, required_caps = {},\n\t\tstartTime, total, disabled = false,\n\t\tfileInput, fileDrop, xhr;\n\n\n\t// Private methods\n\tfunction uploadNext() {\n\t\tvar file, count = 0, i;\n\n\t\tif (this.state == plupload.STARTED) {\n\t\t\t// Find first QUEUED file\n\t\t\tfor (i = 0; i < files.length; i++) {\n\t\t\t\tif (!file && files[i].status == plupload.QUEUED) {\n\t\t\t\t\tfile = files[i];\n\t\t\t\t\tif (this.trigger(\"BeforeUpload\", file)) {\n\t\t\t\t\t\tfile.status = plupload.UPLOADING;\n\t\t\t\t\t\tthis.trigger(\"UploadFile\", file);\n\t\t\t\t\t}\n\t\t\t\t} else {\n\t\t\t\t\tcount++;\n\t\t\t\t}\n\t\t\t}\n\n\t\t\t// All files are DONE or FAILED\n\t\t\tif (count == files.length) {\n\t\t\t\tif (this.state !== plupload.STOPPED) {\n\t\t\t\t\tthis.state = plupload.STOPPED;\n\t\t\t\t\tthis.trigger(\"StateChanged\");\n\t\t\t\t}\n\t\t\t\tthis.trigger(\"UploadComplete\", files);\n\t\t\t}\n\t\t}\n\t}\n\n\tfunction calcFile(file) {\n\t\tfile.percent = file.size > 0 ? Math.ceil(file.loaded / file.size * 100) : 100;\n\t\tcalc();\n\t}\n\n\tfunction calc() {\n\t\tvar i, file;\n\n\t\t// Reset stats\n\t\ttotal.reset();\n\n\t\t// Check status, size, loaded etc on all files\n\t\tfor (i = 0; i < files.length; i++) {\n\t\t\tfile = files[i];\n\n\t\t\tif (file.size !== undef) {\n\t\t\t\t// We calculate totals based on original file size\n\t\t\t\ttotal.size += file.origSize;\n\n\t\t\t\t// Since we cannot predict file size after resize, we do opposite and\n\t\t\t\t// interpolate loaded amount to match magnitude of total\n\t\t\t\ttotal.loaded += file.loaded * file.origSize / file.size;\n\t\t\t} else {\n\t\t\t\ttotal.size = undef;\n\t\t\t}\n\n\t\t\tif (file.status == plupload.DONE) {\n\t\t\t\ttotal.uploaded++;\n\t\t\t} else if (file.status == plupload.FAILED) {\n\t\t\t\ttotal.failed++;\n\t\t\t} else {\n\t\t\t\ttotal.queued++;\n\t\t\t}\n\t\t}\n\n\t\t// If we couldn't calculate a total file size then use the number of files to calc percent\n\t\tif (total.size === undef) {\n\t\t\ttotal.percent = files.length > 0 ? Math.ceil(total.uploaded / files.length * 100) : 0;\n\t\t} else {\n\t\t\ttotal.bytesPerSec = Math.ceil(total.loaded / ((+new Date() - startTime || 1) / 1000.0));\n\t\t\ttotal.percent = total.size > 0 ? Math.ceil(total.loaded / total.size * 100) : 0;\n\t\t}\n\t}\n\n\tfunction initControls() {\n\t\tvar self = this, initialized = 0;\n\n\t\t// common settings\n\t\tvar options = {\n\t\t\taccept: settings.filters,\n\t\t\truntime_order: settings.runtimes,\n\t\t\trequired_caps: required_caps,\n\t\t\tswf_url: settings.flash_swf_url,\n\t\t\txap_url: settings.silverlight_xap_url\n\t\t};\n\n\t\t// add runtime specific options if any\n\t\tplupload.each(settings.runtimes.split(/\\s*,\\s*/), function(runtime) {\n\t\t\tif (settings[runtime]) {\n\t\t\t\toptions[runtime] = settings[runtime];\n\t\t\t}\n\t\t});\n\n\t\to.inSeries([\n\t\t\tfunction(cb) {\n\t\t\t\t// Initialize file dialog trigger\n\t\t\t\tif (settings.browse_button) {\n\t\t\t\t\tfileInput = new o.FileInput(plupload.extend({}, options, {\n\t\t\t\t\t\tname: settings.file_data_name,\n\t\t\t\t\t\tmultiple: settings.multi_selection,\n\t\t\t\t\t\tcontainer: settings.container,\n\t\t\t\t\t\tbrowse_button: settings.browse_button\n\t\t\t\t\t}));\n\n\t\t\t\t\tfileInput.onready = function() {\n\t\t\t\t\t\tvar info = o.Runtime.getInfo(this.ruid);\n\n\t\t\t\t\t\t// for backward compatibility\n\t\t\t\t\t\to.extend(self.features, {\n\t\t\t\t\t\t\tchunks: info.can('slice_blob'),\n\t\t\t\t\t\t\tmultipart: info.can('send_multipart'),\n\t\t\t\t\t\t\tmulti_selection: info.can('select_multiple')\n\t\t\t\t\t\t});\n\n\t\t\t\t\t\tinitialized++;\n\t\t\t\t\t\tcb();\n\t\t\t\t\t};\n\n\t\t\t\t\tfileInput.onchange = function() {\n\t\t\t\t\t\tself.addFile(this.files);\n\t\t\t\t\t};\n\n\t\t\t\t\tfileInput.bind('mouseenter mouseleave mousedown mouseup', function(e) {\n\t\t\t\t\t\tif (!disabled) {\n\t\t\t\t\t\t\tvar bButton = o.get(settings.browse_button);\n\t\t\t\t\t\t\tif (bButton) {\n\t\t\t\t\t\t\t\tif (settings.browse_button_hover) {\n\t\t\t\t\t\t\t\t\tif ('mouseenter' === e.type) {\n\t\t\t\t\t\t\t\t\t\to.addClass(bButton, settings.browse_button_hover);\n\t\t\t\t\t\t\t\t\t} else if ('mouseleave' === e.type) {\n\t\t\t\t\t\t\t\t\t\to.removeClass(bButton, settings.browse_button_hover);\n\t\t\t\t\t\t\t\t\t}\n\t\t\t\t\t\t\t\t}\n\n\t\t\t\t\t\t\t\tif (settings.browse_button_active) {\n\t\t\t\t\t\t\t\t\tif ('mousedown' === e.type) {\n\t\t\t\t\t\t\t\t\t\to.addClass(bButton, settings.browse_button_active);\n\t\t\t\t\t\t\t\t\t} else if ('mouseup' === e.type) {\n\t\t\t\t\t\t\t\t\t\to.removeClass(bButton, settings.browse_button_active);\n\t\t\t\t\t\t\t\t\t}\n\t\t\t\t\t\t\t\t}\n\t\t\t\t\t\t\t\tbButton = null;\n\t\t\t\t\t\t\t}\n\t\t\t\t\t\t}\n\t\t\t\t\t});\n\n\t\t\t\t\tfileInput.bind('error runtimeerror', function() {\n\t\t\t\t\t\tfileInput = null;\n\t\t\t\t\t\tcb();\n\t\t\t\t\t});\n\n\t\t\t\t\tfileInput.init();\n\t\t\t\t} else {\n\t\t\t\t\tcb();\n\t\t\t\t}\n\t\t\t},\n\n\t\t\tfunction(cb) {\n\t\t\t\t// Initialize drag/drop interface if requested\n\t\t\t\tif (settings.drop_element) {\n\t\t\t\t\tfileDrop = new o.FileDrop(plupload.extend({}, options, {\n\t\t\t\t\t\tdrop_zone: settings.drop_element\n\t\t\t\t\t}));\n\n\t\t\t\t\tfileDrop.onready = function() {\n\t\t\t\t\t\tvar info = o.Runtime.getInfo(this.ruid);\n\n\t\t\t\t\t\tself.features.dragdrop = info.can('drag_and_drop');\n\n\t\t\t\t\t\tinitialized++;\n\t\t\t\t\t\tcb();\n\t\t\t\t\t};\n\n\t\t\t\t\tfileDrop.ondrop = function() {\n\t\t\t\t\t\tself.addFile(this.files);\n\t\t\t\t\t};\n\n\t\t\t\t\tfileDrop.bind('error runtimeerror', function() {\n\t\t\t\t\t\tfileDrop = null;\n\t\t\t\t\t\tcb();\n\t\t\t\t\t});\n\n\t\t\t\t\tfileDrop.init();\n\t\t\t\t} else {\n\t\t\t\t\tcb();\n\t\t\t\t}\n\t\t\t}\n\t\t],\n\t\tfunction() {\n\t\t\tif (typeof(settings.init) == \"function\") {\n\t\t\t\tsettings.init(self);\n\t\t\t} else {\n\t\t\t\tplupload.each(settings.init, function(func, name) {\n\t\t\t\t\tself.bind(name, func);\n\t\t\t\t});\n\t\t\t}\n\n\t\t\tif (initialized) {\n\t\t\t\tself.trigger('PostInit');\n\t\t\t} else {\n\t\t\t\tself.trigger('Error', {\n\t\t\t\t\tcode : plupload.INIT_ERROR,\n\t\t\t\t\tmessage : plupload.translate('Init error.')\n\t\t\t\t});\n\t\t\t}\n\t\t});\n\t}\n\n\tfunction runtimeCan(file, cap) {\n\t\tif (file.ruid) {\n\t\t\tvar info = o.Runtime.getInfo(file.ruid);\n\t\t\tif (info) {\n\t\t\t\treturn info.can(cap);\n\t\t\t}\n\t\t}\n\t\treturn false;\n\t}\n\n\tfunction resizeImage(blob, params, cb) {\n\t\tvar img = new o.Image();\n\n\t\ttry {\n\t\t\timg.onload = function() {\n\t\t\t\timg.downsize(params.width, params.height, params.crop, params.preserve_headers);\n\t\t\t};\n\n\t\t\timg.onresize = function() {\n\t\t\t\tcb(this.getAsBlob(blob.type, params.quality));\n\t\t\t\tthis.destroy();\n\t\t\t};\n\n\t\t\timg.onerror = function() {\n\t\t\t\tcb(blob);\n\t\t\t};\n\n\t\t\timg.load(blob);\n\t\t} catch(ex) {\n\t\t\tcb(blob);\n\t\t}\n\t}\n\n\n\t// Inital total state\n\ttotal = new plupload.QueueProgress();\n\n\t// Default settings\n\tsettings = plupload.extend({\n\t\truntimes: o.Runtime.order,\n\t\tmax_retries: 0,\n\t\tmultipart : true,\n\t\tmulti_selection : true,\n\t\tfile_data_name : 'file',\n\t\tflash_swf_url : 'js/Moxie.swf',\n\t\tsilverlight_xap_url : 'js/Moxie.xap',\n\t\tfilters : [],\n\t\tprevent_duplicates: false,\n\t\tsend_chunk_number: true // whether to send chunks and chunk numbers, or total and offset bytes\n\t}, settings);\n\n\t// Resize defaults\n\tif (settings.resize) {\n\t\tsettings.resize = plupload.extend({\n\t\t\tpreserve_headers: true,\n\t\t\tcrop: false\n\t\t}, settings.resize);\n\t}\n\n\t// Convert settings\n\tsettings.chunk_size = plupload.parseSize(settings.chunk_size) || 0;\n\tsettings.max_file_size = plupload.parseSize(settings.max_file_size) || 0;\n\t\n\tsettings.required_features = required_caps = normalizeCaps(plupload.extend({}, settings));\n\n\n\t// Add public methods\n\tplupload.extend(this, {\n\n\t\t/**\n\t\t * Unique id for the Uploader instance.\n\t\t *\n\t\t * @property id\n\t\t * @type String\n\t\t */\n\t\tid : plupload.guid(),\n\n\t\t/**\n\t\t * Current state of the total uploading progress. This one can either be plupload.STARTED or plupload.STOPPED.\n\t\t * These states are controlled by the stop/start methods. The default value is STOPPED.\n\t\t *\n\t\t * @property state\n\t\t * @type Number\n\t\t */\n\t\tstate : plupload.STOPPED,\n\n\t\t/**\n\t\t * Map of features that are available for the uploader runtime. Features will be filled\n\t\t * before the init event is called, these features can then be used to alter the UI for the end user.\n\t\t * Some of the current features that might be in this map is: dragdrop, chunks, jpgresize, pngresize.\n\t\t *\n\t\t * @property features\n\t\t * @type Object\n\t\t */\n\t\tfeatures : {},\n\n\t\t/**\n\t\t * Current runtime name.\n\t\t *\n\t\t * @property runtime\n\t\t * @type String\n\t\t */\n\t\truntime : o.Runtime.thatCan(required_caps, settings.runtimes), // predict runtime\n\n\t\t/**\n\t\t * Current upload queue, an array of File instances.\n\t\t *\n\t\t * @property files\n\t\t * @type Array\n\t\t * @see plupload.File\n\t\t */\n\t\tfiles : files,\n\n\t\t/**\n\t\t * Object with name/value settings.\n\t\t *\n\t\t * @property settings\n\t\t * @type Object\n\t\t */\n\t\tsettings : settings,\n\n\t\t/**\n\t\t * Total progess information. How many files has been uploaded, total percent etc.\n\t\t *\n\t\t * @property total\n\t\t * @type plupload.QueueProgress\n\t\t */\n\t\ttotal : total,\n\n\n\t\t/**\n\t\t * Initializes the Uploader instance and adds internal event listeners.\n\t\t *\n\t\t * @method init\n\t\t */\n\t\tinit : function() {\n\t\t\tvar self = this;\n\n\t\t\tsettings.browse_button = o.get(settings.browse_button);\n\t\t\t\n\t\t\t// Check if drop zone requested\n\t\t\tsettings.drop_element = o.get(settings.drop_element);\n\n\n\t\t\tif (typeof(settings.preinit) == \"function\") {\n\t\t\t\tsettings.preinit(self);\n\t\t\t} else {\n\t\t\t\tplupload.each(settings.preinit, function(func, name) {\n\t\t\t\t\tself.bind(name, func);\n\t\t\t\t});\n\t\t\t}\n\n\n\t\t\t// Check for required options\n\t\t\tif (!settings.browse_button || !settings.url) {\n\t\t\t\tthis.trigger('Error', {\n\t\t\t\t\tcode : plupload.INIT_ERROR,\n\t\t\t\t\tmessage : plupload.translate('Init error.')\n\t\t\t\t});\n\t\t\t\treturn;\n\t\t\t}\n\n\t\t\t// Add files to queue\n\t\t\tself.bind('FilesAdded', function(up, selected_files) {\n\t\t\t\tvar i, ii, file, count = 0, extensionsRegExp, filters = settings.filters;\n\n\t\t\t\t// Convert extensions to regexp\n\t\t\t\tif (filters && filters.length) {\n\t\t\t\t\textensionsRegExp = [];\n\n\t\t\t\t\tplupload.each(filters, function(filter) {\n\t\t\t\t\t\tplupload.each(filter.extensions.split(/,/), function(ext) {\n\t\t\t\t\t\t\tif (/^\\s*\\*\\s*$/.test(ext)) {\n\t\t\t\t\t\t\t\textensionsRegExp.push('\\\\.*');\n\t\t\t\t\t\t\t} else {\n\t\t\t\t\t\t\t\textensionsRegExp.push('\\\\.' + ext.replace(new RegExp('[' + ('/^$.*+?|()[]{}\\\\'.replace(/./g, '\\\\$&')) + ']', 'g'), '\\\\$&'));\n\t\t\t\t\t\t\t}\n\t\t\t\t\t\t});\n\t\t\t\t\t});\n\n\t\t\t\t\textensionsRegExp = new RegExp('(' + extensionsRegExp.join('|') + ')$', 'i');\n\t\t\t\t}\n\n\t\t\t\tnext_file:\n\t\t\t\tfor (i = 0; i < selected_files.length; i++) {\n\t\t\t\t\tfile = selected_files[i];\n\t\t\t\t\tfile.loaded = 0;\n\t\t\t\t\tfile.percent = 0;\n\t\t\t\t\tfile.status = plupload.QUEUED;\n\n\t\t\t\t\t// Invalid file extension\n\t\t\t\t\tif (extensionsRegExp && !extensionsRegExp.test(file.name)) {\n\t\t\t\t\t\tup.trigger('Error', {\n\t\t\t\t\t\t\tcode : plupload.FILE_EXTENSION_ERROR,\n\t\t\t\t\t\t\tmessage : plupload.translate('File extension error.'),\n\t\t\t\t\t\t\tfile : file\n\t\t\t\t\t\t});\n\n\t\t\t\t\t\tcontinue;\n\t\t\t\t\t}\n\n\t\t\t\t\t// Invalid file size\n\t\t\t\t\tif (file.size !== undef && settings.max_file_size && file.size > settings.max_file_size) {\n\t\t\t\t\t\tup.trigger('Error', {\n\t\t\t\t\t\t\tcode : plupload.FILE_SIZE_ERROR,\n\t\t\t\t\t\t\tmessage : plupload.translate('File size error.'),\n\t\t\t\t\t\t\tfile : file\n\t\t\t\t\t\t});\n\t\t\t\t\t\tcontinue;\n\t\t\t\t\t}\n\n\t\t\t\t\t// Bypass duplicates\n\t\t\t\t\tif (settings.prevent_duplicates) {\n\t\t\t\t\t\tii = up.files.length;\n\t\t\t\t\t\twhile (ii--) {\n\t\t\t\t\t\t\t// Compare by name and size (size might be 0 or undefined, but still equivalent for both)\n\t\t\t\t\t\t\tif (file.name === up.files[ii].name && file.size === up.files[ii].size) {\n\t\t\t\t\t\t\t\tup.trigger('Error', {\n\t\t\t\t\t\t\t\t\tcode : plupload.FILE_DUPLICATE_ERROR,\n\t\t\t\t\t\t\t\t\tmessage : plupload.translate('Duplicate file error.'),\n\t\t\t\t\t\t\t\t\tfile : file\n\t\t\t\t\t\t\t\t});\n\t\t\t\t\t\t\t\tcontinue next_file;\n\t\t\t\t\t\t\t}\n\t\t\t\t\t\t}\n\t\t\t\t\t}\n\n\t\t\t\t\t// Add valid file to list\n\t\t\t\t\tfiles.push(file);\n\t\t\t\t\tcount++;\n\t\t\t\t}\n\n\t\t\t\t// Only trigger QueueChanged event if any files where added\n\t\t\t\tif (count) {\n\t\t\t\t\tdelay(function() {\n\t\t\t\t\t\tself.trigger(\"QueueChanged\");\n\t\t\t\t\t\tself.refresh();\n\t\t\t\t\t}, 1);\n\t\t\t\t} else {\n\t\t\t\t\treturn false; // Stop the FilesAdded event from immediate propagation\n\t\t\t\t}\n\t\t\t});\n\n\t\t\tself.bind(\"CancelUpload\", function() {\n\t\t\t\tif (xhr) {\n\t\t\t\t\txhr.abort();\n\t\t\t\t}\n\t\t\t});\n\n\t\t\t// Generate unique target filenames\n\t\t\tif (settings.unique_names) {\n\t\t\t\tself.bind(\"BeforeUpload\", function(up, file) {\n\t\t\t\t\tvar matches = file.name.match(/\\.([^.]+)$/), ext = \"part\";\n\t\t\t\t\tif (matches) {\n\t\t\t\t\t\text = matches[1];\n\t\t\t\t\t}\n\t\t\t\t\tfile.target_name = file.id + '.' + ext;\n\t\t\t\t});\n\t\t\t}\n\n\t\t\tself.bind(\"UploadFile\", function(up, file) {\n\t\t\t\tvar url = up.settings.url, features = up.features, chunkSize = settings.chunk_size,\n\t\t\t\t\tretries = settings.max_retries,\n\t\t\t\t\tblob, offset = 0;\n\n\t\t\t\t// make sure we start at a predictable offset\n\t\t\t\tif (file.loaded) {\n\t\t\t\t\toffset = file.loaded = chunkSize * Math.floor(file.loaded / chunkSize);\n\t\t\t\t}\n\n\t\t\t\tfunction handleError() {\n\t\t\t\t\tif (retries-- > 0) {\n\t\t\t\t\t\tdelay(uploadNextChunk, 1);\n\t\t\t\t\t} else {\n\t\t\t\t\t\tfile.loaded = offset; // reset all progress\n\n\t\t\t\t\t\tup.trigger('Error', {\n\t\t\t\t\t\t\tcode : plupload.HTTP_ERROR,\n\t\t\t\t\t\t\tmessage : plupload.translate('HTTP Error.'),\n\t\t\t\t\t\t\tfile : file,\n\t\t\t\t\t\t\tresponse : xhr.responseText,\n\t\t\t\t\t\t\tstatus : xhr.status,\n\t\t\t\t\t\t\tresponseHeaders: xhr.getAllResponseHeaders()\n\t\t\t\t\t\t});\n\t\t\t\t\t}\n\t\t\t\t}\n\n\t\t\t\tfunction uploadNextChunk() {\n\t\t\t\t\tvar chunkBlob, formData, args, curChunkSize;\n\n\t\t\t\t\t// File upload finished\n\t\t\t\t\tif (file.status == plupload.DONE || file.status == plupload.FAILED || up.state == plupload.STOPPED) {\n\t\t\t\t\t\treturn;\n\t\t\t\t\t}\n\n\t\t\t\t\t// Standard arguments\n\t\t\t\t\targs = {name : file.target_name || file.name};\n\n\t\t\t\t\t// Only add chunking args if needed\n\t\t\t\t\tif (chunkSize && features.chunks && blob.size > chunkSize) { // blob will be of type string if it was loaded in memory \n\t\t\t\t\t\tcurChunkSize = Math.min(chunkSize, blob.size - offset);\n\n\t\t\t\t\t\tchunkBlob = blob.slice(offset, offset + curChunkSize);\n\n\t\t\t\t\t\t// Setup query string arguments\n\t\t\t\t\t\tif (settings.send_chunk_number) {\n\t\t\t\t\t\t\targs.chunk = Math.ceil(offset / chunkSize);\n\t\t\t\t\t\t\targs.chunks = Math.ceil(blob.size / chunkSize);\n\t\t\t\t\t\t} else { // keep support for experimental chunk format, just in case\n\t\t\t\t\t\t\targs.offset = offset;\n\t\t\t\t\t\t\targs.total = blob.size;\n\t\t\t\t\t\t}\n\t\t\t\t\t} else {\n\t\t\t\t\t\tcurChunkSize = blob.size;\n\t\t\t\t\t\tchunkBlob = blob;\n\t\t\t\t\t}\n\n\t\t\t\t\txhr = new o.XMLHttpRequest();\n\n\t\t\t\t\t// Do we have upload progress support\n\t\t\t\t\tif (xhr.upload) {\n\t\t\t\t\t\txhr.upload.onprogress = function(e) {\n\t\t\t\t\t\t\tfile.loaded = Math.min(file.size, offset + e.loaded);\n\t\t\t\t\t\t\tup.trigger('UploadProgress', file);\n\t\t\t\t\t\t};\n\t\t\t\t\t}\n\n\t\t\t\t\txhr.onload = function() {\n\t\t\t\t\t\t// check if upload made itself through\n\t\t\t\t\t\tif (xhr.status >= 400) {\n\t\t\t\t\t\t\thandleError();\n\t\t\t\t\t\t\treturn;\n\t\t\t\t\t\t}\n\n\t\t\t\t\t\t// Handle chunk response\n\t\t\t\t\t\tif (curChunkSize < blob.size) {\n\t\t\t\t\t\t\tchunkBlob.destroy();\n\n\t\t\t\t\t\t\toffset += curChunkSize;\n\t\t\t\t\t\t\tfile.loaded = Math.min(offset, blob.size);\n\n\t\t\t\t\t\t\tup.trigger('ChunkUploaded', file, {\n\t\t\t\t\t\t\t\toffset : file.loaded,\n\t\t\t\t\t\t\t\ttotal : blob.size,\n\t\t\t\t\t\t\t\tresponse : xhr.responseText,\n\t\t\t\t\t\t\t\tstatus : xhr.status,\n\t\t\t\t\t\t\t\tresponseHeaders: xhr.getAllResponseHeaders()\n\t\t\t\t\t\t\t});\n\t\t\t\t\t\t} else {\n\t\t\t\t\t\t\tfile.loaded = file.size;\n\t\t\t\t\t\t}\n\n\t\t\t\t\t\tchunkBlob = formData = null; // Free memory\n\n\t\t\t\t\t\t// Check if file is uploaded\n\t\t\t\t\t\tif (!offset || offset >= blob.size) {\n\t\t\t\t\t\t\t// If file was modified, destory the copy\n\t\t\t\t\t\t\tif (file.size != file.origSize) {\n\t\t\t\t\t\t\t\tblob.destroy();\n\t\t\t\t\t\t\t\tblob = null;\n\t\t\t\t\t\t\t}\n\n\t\t\t\t\t\t\tup.trigger('UploadProgress', file);\n\n\t\t\t\t\t\t\tfile.status = plupload.DONE;\n\n\t\t\t\t\t\t\tup.trigger('FileUploaded', file, {\n\t\t\t\t\t\t\t\tresponse : xhr.responseText,\n\t\t\t\t\t\t\t\tstatus : xhr.status,\n\t\t\t\t\t\t\t\tresponseHeaders: xhr.getAllResponseHeaders()\n\t\t\t\t\t\t\t});\n\t\t\t\t\t\t} else {\n\t\t\t\t\t\t\t// Still chunks left\n\t\t\t\t\t\t\tdelay(uploadNextChunk, 1); // run detached, otherwise event handlers interfere\n\t\t\t\t\t\t}\n\t\t\t\t\t};\n\n\t\t\t\t\txhr.onerror = function() {\n\t\t\t\t\t\thandleError();\n\t\t\t\t\t};\n\n\t\t\t\t\txhr.onloadend = function() {\n\t\t\t\t\t\tthis.destroy();\n\t\t\t\t\t\txhr = null;\n\t\t\t\t\t};\n\n\t\t\t\t\t// Build multipart request\n\t\t\t\t\tif (up.settings.multipart && features.multipart) {\n\n\t\t\t\t\t\targs.name = file.target_name || file.name;\n\n\t\t\t\t\t\txhr.open(\"post\", url, true);\n\n\t\t\t\t\t\t// Set custom headers\n\t\t\t\t\t\tplupload.each(up.settings.headers, function(value, name) {\n\t\t\t\t\t\t\txhr.setRequestHeader(name, value);\n\t\t\t\t\t\t});\n\n\t\t\t\t\t\tformData = new o.FormData();\n\n\t\t\t\t\t\t// Add multipart params\n\t\t\t\t\t\tplupload.each(plupload.extend(args, up.settings.multipart_params), function(value, name) {\n\t\t\t\t\t\t\tformData.append(name, value);\n\t\t\t\t\t\t});\n\n\t\t\t\t\t\t// Add file and send it\n\t\t\t\t\t\tformData.append(up.settings.file_data_name, chunkBlob);\n\t\t\t\t\t\txhr.send(formData, {\n\t\t\t\t\t\t\truntime_order: up.settings.runtimes,\n\t\t\t\t\t\t\trequired_caps: required_caps,\n\t\t\t\t\t\t\tswf_url: up.settings.flash_swf_url,\n\t\t\t\t\t\t\txap_url: up.settings.silverlight_xap_url\n\t\t\t\t\t\t});\n\t\t\t\t\t} else {\n\t\t\t\t\t\t// if no multipart, send as binary stream\n\t\t\t\t\t\turl = plupload.buildUrl(up.settings.url, plupload.extend(args, up.settings.multipart_params));\n\n\t\t\t\t\t\txhr.open(\"post\", url, true);\n\n\t\t\t\t\t\txhr.setRequestHeader('Content-Type', 'application/octet-stream'); // Binary stream header\n\n\t\t\t\t\t\t// Set custom headers\n\t\t\t\t\t\tplupload.each(up.settings.headers, function(value, name) {\n\t\t\t\t\t\t\txhr.setRequestHeader(name, value);\n\t\t\t\t\t\t});\n\n\t\t\t\t\t\txhr.send(chunkBlob, {\n\t\t\t\t\t\t\truntime_order: up.settings.runtimes,\n\t\t\t\t\t\t\trequired_caps: required_caps,\n\t\t\t\t\t\t\tswf_url: up.settings.flash_swf_url,\n\t\t\t\t\t\t\txap_url: up.settings.silverlight_xap_url\n\t\t\t\t\t\t});\n\t\t\t\t\t}\n\t\t\t\t}\n\n\t\t\t\tblob = file.getSource();\n\n\t\t\t\t// Start uploading chunks\n\t\t\t\tif (!o.isEmptyObj(up.settings.resize) && runtimeCan(blob, 'send_binary_string') && !!~o.inArray(blob.type, ['image/jpeg', 'image/png'])) {\n\t\t\t\t\t// Resize if required\n\t\t\t\t\tresizeImage.call(this, blob, up.settings.resize, function(resizedBlob) {\n\t\t\t\t\t\tblob = resizedBlob;\n\t\t\t\t\t\tfile.size = resizedBlob.size;\n\t\t\t\t\t\tuploadNextChunk();\n\t\t\t\t\t});\n\t\t\t\t} else {\n\t\t\t\t\tuploadNextChunk();\n\t\t\t\t}\n\t\t\t});\n\n\t\t\tself.bind('UploadProgress', function(up, file) {\n\t\t\t\tcalcFile(file);\n\t\t\t});\n\n\t\t\tself.bind('StateChanged', function(up) {\n\t\t\t\tif (up.state == plupload.STARTED) {\n\t\t\t\t\t// Get start time to calculate bps\n\t\t\t\t\tstartTime = (+new Date());\n\t\t\t\t} else if (up.state == plupload.STOPPED) {\n\t\t\t\t\t// Reset currently uploading files\n\t\t\t\t\tfor (var i = up.files.length - 1; i >= 0; i--) {\n\t\t\t\t\t\tif (up.files[i].status == plupload.UPLOADING) {\n\t\t\t\t\t\t\tup.files[i].status = plupload.QUEUED;\n\t\t\t\t\t\t\tcalc();\n\t\t\t\t\t\t}\n\t\t\t\t\t}\n\t\t\t\t}\n\t\t\t});\n\n\t\t\tself.bind('QueueChanged', calc);\n\n\t\t\tself.bind(\"Error\", function(up, err) {\n\t\t\t\t// Set failed status if an error occured on a file\n\t\t\t\tif (err.file) {\n\t\t\t\t\terr.file.status = plupload.FAILED;\n\n\t\t\t\t\tcalcFile(err.file);\n\n\t\t\t\t\t// Upload next file but detach it from the error event\n\t\t\t\t\t// since other custom listeners might want to stop the queue\n\t\t\t\t\tif (up.state == plupload.STARTED) {\n\t\t\t\t\t\tdelay(function() {\n\t\t\t\t\t\t\tuploadNext.call(self);\n\t\t\t\t\t\t}, 1);\n\t\t\t\t\t}\n\t\t\t\t}\n\t\t\t});\n\n\t\t\tself.bind(\"FileUploaded\", function() {\n\t\t\t\tcalc();\n\n\t\t\t\t// Upload next file but detach it from the error event\n\t\t\t\t// since other custom listeners might want to stop the queue\n\t\t\t\tdelay(function() {\n\t\t\t\t\tuploadNext.call(self);\n\t\t\t\t}, 1);\n\t\t\t});\n\n\t\t\t// some dependent scripts hook onto Init to alter configuration options, raw UI, etc (like Queue Widget),\n\t\t\t// therefore we got to fire this one, before we dive into the actual initializaion\n\t\t\tself.trigger('Init', { runtime: this.runtime });\n\n\t\t\tinitControls.call(this);\n\t\t},\n\n\t\t/**\n\t\t * Refreshes the upload instance by dispatching out a refresh event to all runtimes.\n\t\t * This would for example reposition flash/silverlight shims on the page.\n\t\t *\n\t\t * @method refresh\n\t\t */\n\t\trefresh : function() {\n\t\t\tif (fileInput) {\n\t\t\t\tfileInput.trigger(\"Refresh\");\n\t\t\t}\n\t\t\tthis.trigger(\"Refresh\");\n\t\t},\n\n\t\t/**\n\t\t * Starts uploading the queued files.\n\t\t *\n\t\t * @method start\n\t\t */\n\t\tstart : function() {\n\t\t\tif (this.state != plupload.STARTED) {\n\t\t\t\tthis.state = plupload.STARTED;\n\t\t\t\tthis.trigger(\"StateChanged\");\n\n\t\t\t\tuploadNext.call(this);\n\t\t\t}\n\t\t},\n\n\t\t/**\n\t\t * Stops the upload of the queued files.\n\t\t *\n\t\t * @method stop\n\t\t */\n\t\tstop : function() {\n\t\t\tif (this.state != plupload.STOPPED) {\n\t\t\t\tthis.state = plupload.STOPPED;\n\t\t\t\tthis.trigger(\"StateChanged\");\n\t\t\t\tthis.trigger(\"CancelUpload\");\n\t\t\t}\n\t\t},\n\n\n\t\t/**\n\t\t * Disables/enables browse button on request.\n\t\t *\n\t\t * @method disableBrowse\n\t\t * @param {Boolean} disable Whether to disable or enable (default: true)\n\t\t */\n\t\tdisableBrowse : function() {\n\t\t\tdisabled = arguments[0] !== undef ? arguments[0] : true;\n\n\t\t\tif (fileInput) {\n\t\t\t\tfileInput.disable(disabled);\n\t\t\t}\n\n\t\t\tthis.trigger(\"DisableBrowse\", disabled);\n\t\t},\n\n\t\t/**\n\t\t * Returns the specified file object by id.\n\t\t *\n\t\t * @method getFile\n\t\t * @param {String} id File id to look for.\n\t\t * @return {plupload.File} File object or undefined if it wasn't found;\n\t\t */\n\t\tgetFile : function(id) {\n\t\t\tvar i;\n\t\t\tfor (i = files.length - 1; i >= 0; i--) {\n\t\t\t\tif (files[i].id === id) {\n\t\t\t\t\treturn files[i];\n\t\t\t\t}\n\t\t\t}\n\t\t},\n\n\t\t/**\n\t\t * Adds file to the queue programmatically. Can be native file, instance of Plupload.File,\n\t\t * instance of mOxie.File, input[type=\"file\"] element, or array of these. Fires FilesAdded, \n\t\t * if any files were added to the queue. Otherwise nothing happens.\n\t\t *\n\t\t * @method addFile\n\t\t * @param {plupload.File|mOxie.File|File|Node|Array} file File or files to add to the queue.\n\t\t * @param {String} [fileName] If specified, will be used as a name for the file\n\t\t */\n\t\taddFile : function(file, fileName) {\n\t\t\tvar files = []\n\t\t\t, ruid\n\t\t\t;\n\n\t\t\tfunction getRUID() {\n\t\t\t\tvar ctrl = fileDrop || fileInput;\n\t\t\t\tif (ctrl) {\n\t\t\t\t\treturn ctrl.getRuntime().uid;\n\t\t\t\t}\n\t\t\t\treturn false;\n\t\t\t}\n\n\t\t\tfunction resolveFile(file) {\n\t\t\t\tvar type = o.typeOf(file);\n\n\t\t\t\tif (file instanceof o.File) { \n\t\t\t\t\tif (!file.ruid) {\n\t\t\t\t\t\tif (!ruid) { // weird case\n\t\t\t\t\t\t\treturn false;\n\t\t\t\t\t\t}\n\t\t\t\t\t\tfile.ruid = ruid;\n\t\t\t\t\t\tfile.connectRuntime(ruid);\n\t\t\t\t\t}\n\t\t\t\t\tresolveFile(new plupload.File(file));\n\t\t\t\t} else if (file instanceof o.Blob) {\n\t\t\t\t\tresolveFile(file.getSource());\n\t\t\t\t\tfile.destroy();\n\t\t\t\t} else if (file instanceof plupload.File) { // final step for other branches\n\t\t\t\t\tif (fileName) {\n\t\t\t\t\t\tfile.name = fileName;\n\t\t\t\t\t}\n\t\t\t\t\tfiles.push(file);\n\t\t\t\t} else if (o.inArray(type, ['file', 'blob']) !== -1) {\n\t\t\t\t\tresolveFile(new o.File(null, file));\n\t\t\t\t} else if (type === 'node' && o.typeOf(file.files) === 'filelist') {\n\t\t\t\t\t// if we are dealing with input[type=\"file\"]\n\t\t\t\t\to.each(file.files, resolveFile);\n\t\t\t\t} else if (type === 'array') {\n\t\t\t\t\t// mixed array\n\t\t\t\t\tfileName = null; // should never happen, but unset anyway to avoid funny situations\n\t\t\t\t\to.each(file, resolveFile);\n\t\t\t\t}\n\t\t\t}\n\n\t\t\truid = getRUID();\n\n\t\t\tresolveFile(file);\n\t\t\t// Trigger FilesAdded event if we added any\n\t\t\tif (files.length) {\n\t\t\t\tthis.trigger(\"FilesAdded\", files);\n\t\t\t}\n\t\t},\n\n\t\t/**\n\t\t * Removes a specific file.\n\t\t *\n\t\t * @method removeFile\n\t\t * @param {plupload.File|String} file File to remove from queue.\n\t\t */\n\t\tremoveFile : function(file) {\n\t\t\tvar id = typeof(file) === 'string' ? file : file.id;\n\n\t\t\tfor (var i = files.length - 1; i >= 0; i--) {\n\t\t\t\tif (files[i].id === id) {\n\t\t\t\t\treturn this.splice(i, 1)[0];\n\t\t\t\t}\n\t\t\t}\n\t\t},\n\n\t\t/**\n\t\t * Removes part of the queue and returns the files removed. This will also trigger the FilesRemoved and QueueChanged events.\n\t\t *\n\t\t * @method splice\n\t\t * @param {Number} start (Optional) Start index to remove from.\n\t\t * @param {Number} length (Optional) Lengh of items to remove.\n\t\t * @return {Array} Array of files that was removed.\n\t\t */\n\t\tsplice : function(start, length) {\n\t\t\t// Splice and trigger events\n\t\t\tvar removed = files.splice(start === undef ? 0 : start, length === undef ? files.length : length);\n\n\t\t\tthis.trigger(\"FilesRemoved\", removed);\n\t\t\tthis.trigger(\"QueueChanged\");\n\n\t\t\t// Dispose any resources allocated by those files\n\t\t\tplupload.each(removed, function(file) {\n\t\t\t\tfile.destroy();\n\t\t\t});\n\n\t\t\treturn removed;\n\t\t},\n\n\t\t/**\n\t\t * Dispatches the specified event name and it's arguments to all listeners.\n\t\t *\n\t\t *\n\t\t * @method trigger\n\t\t * @param {String} name Event name to fire.\n\t\t * @param {Object..} Multiple arguments to pass along to the listener functions.\n\t\t */\n\t\ttrigger : function(name) {\n\t\t\tvar list = events[name.toLowerCase()], i, args;\n\n\t\t\t// console.log(name, arguments);\n\n\t\t\tif (list) {\n\t\t\t\t// Replace name with sender in args\n\t\t\t\targs = Array.prototype.slice.call(arguments);\n\t\t\t\targs[0] = this;\n\n\t\t\t\t// Dispatch event to all listeners\n\t\t\t\tfor (i = 0; i < list.length; i++) {\n\t\t\t\t\t// Fire event, break chain if false is returned\n\t\t\t\t\tif (list[i].func.apply(list[i].scope, args) === false) {\n\t\t\t\t\t\treturn false;\n\t\t\t\t\t}\n\t\t\t\t}\n\t\t\t}\n\n\t\t\treturn true;\n\t\t},\n\n\t\t/**\n\t\t * Check whether uploader has any listeners to the specified event.\n\t\t *\n\t\t * @method hasEventListener\n\t\t * @param {String} name Event name to check for.\n\t\t */\n\t\thasEventListener : function(name) {\n\t\t\treturn !!events[name.toLowerCase()];\n\t\t},\n\n\t\t/**\n\t\t * Adds an event listener by name.\n\t\t *\n\t\t * @method bind\n\t\t * @param {String} name Event name to listen for.\n\t\t * @param {function} func Function to call ones the event gets fired.\n\t\t * @param {Object} scope Optional scope to execute the specified function in.\n\t\t */\n\t\tbind : function(name, func, scope) {\n\t\t\tvar list;\n\n\t\t\tname = name.toLowerCase();\n\t\t\tlist = events[name] || [];\n\t\t\tlist.push({func : func, scope : scope || this});\n\t\t\tevents[name] = list;\n\t\t},\n\n\t\t/**\n\t\t * Removes the specified event listener.\n\t\t *\n\t\t * @method unbind\n\t\t * @param {String} name Name of event to remove.\n\t\t * @param {function} func Function to remove from listener.\n\t\t */\n\t\tunbind : function(name) {\n\t\t\tname = name.toLowerCase();\n\n\t\t\tvar list = events[name], i, func = arguments[1];\n\n\t\t\tif (list) {\n\t\t\t\tif (func !== undef) {\n\t\t\t\t\tfor (i = list.length - 1; i >= 0; i--) {\n\t\t\t\t\t\tif (list[i].func === func) {\n\t\t\t\t\t\t\tlist.splice(i, 1);\n\t\t\t\t\t\t\t\tbreak;\n\t\t\t\t\t\t}\n\t\t\t\t\t}\n\t\t\t\t} else {\n\t\t\t\t\tlist = [];\n\t\t\t\t}\n\n\t\t\t\t// delete event list if it has become empty\n\t\t\t\tif (!list.length) {\n\t\t\t\t\tdelete events[name];\n\t\t\t\t}\n\t\t\t}\n\t\t},\n\n\t\t/**\n\t\t * Removes all event listeners.\n\t\t *\n\t\t * @method unbindAll\n\t\t */\n\t\tunbindAll : function() {\n\t\t\tvar self = this;\n\n\t\t\tplupload.each(events, function(list, name) {\n\t\t\t\tself.unbind(name);\n\t\t\t});\n\t\t},\n\n\t\t/**\n\t\t * Destroys Plupload instance and cleans after itself.\n\t\t *\n\t\t * @method destroy\n\t\t */\n\t\tdestroy : function() {\n\t\t\tthis.stop();\n\n\t\t\t// Purge the queue\n\t\t\tplupload.each(files, function(file) {\n\t\t\t\tfile.destroy();\n\t\t\t});\n\t\t\tfiles = [];\n\n\t\t\tif (fileInput) {\n\t\t\t\tfileInput.destroy();\n\t\t\t\tfileInput = null;\n\t\t\t}\n\n\t\t\tif (fileDrop) {\n\t\t\t\tfileDrop.destroy();\n\t\t\t\tfileDrop = null;\n\t\t\t}\n\n\t\t\trequired_caps = {};\n\t\t\tstartTime = total = disabled = xhr = null;\n\n\t\t\tthis.trigger('Destroy');\n\n\t\t\t// Clean-up after uploader itself\n\t\t\tthis.unbindAll();\n\t\t\tevents = {};\n\t\t}\n\t});\n};\n\n/**\n * Constructs a new file instance.\n *\n * @class File\n * @constructor\n * \n * @param {Object} file Object containing file properties\n * @param {String} file.name Name of the file.\n * @param {Number} file.size File size.\n */\nplupload.File = (function() {\n\tvar filepool = {};\n\n\tfunction PluploadFile(file) {\n\n\t\tplupload.extend(this, {\n\n\t\t\t/**\n\t\t\t * File id this is a globally unique id for the specific file.\n\t\t\t *\n\t\t\t * @property id\n\t\t\t * @type String\n\t\t\t */\n\t\t\tid: plupload.guid(),\n\n\t\t\t/**\n\t\t\t * File name for example \"myfile.gif\".\n\t\t\t *\n\t\t\t * @property name\n\t\t\t * @type String\n\t\t\t */\n\t\t\tname: file.name || file.fileName,\n\n\t\t\t/**\n\t\t\t * File type, `e.g image/jpeg`\n\t\t\t *\n\t\t\t * @property type\n\t\t\t * @type String\n\t\t\t */\n\t\t\ttype: file.type || '',\n\n\t\t\t/**\n\t\t\t * File size in bytes (may change after client-side manupilation).\n\t\t\t *\n\t\t\t * @property size\n\t\t\t * @type Number\n\t\t\t */\n\t\t\tsize: file.size || file.fileSize,\n\n\t\t\t/**\n\t\t\t * Original file size in bytes.\n\t\t\t *\n\t\t\t * @property origSize\n\t\t\t * @type Number\n\t\t\t */\n\t\t\torigSize: file.size || file.fileSize,\n\n\t\t\t/**\n\t\t\t * Number of bytes uploaded of the files total size.\n\t\t\t *\n\t\t\t * @property loaded\n\t\t\t * @type Number\n\t\t\t */\n\t\t\tloaded: 0,\n\n\t\t\t/**\n\t\t\t * Number of percentage uploaded of the file.\n\t\t\t *\n\t\t\t * @property percent\n\t\t\t * @type Number\n\t\t\t */\n\t\t\tpercent: 0,\n\n\t\t\t/**\n\t\t\t * Status constant matching the plupload states QUEUED, UPLOADING, FAILED, DONE.\n\t\t\t *\n\t\t\t * @property status\n\t\t\t * @type Number\n\t\t\t * @see plupload\n\t\t\t */\n\t\t\tstatus: 0,\n\n\t\t\t/**\n\t\t\t * Returns native window.File object, when it's available.\n\t\t\t *\n\t\t\t * @method getNative\n\t\t\t * @return {window.File} or null, if plupload.File is of different origin\n\t\t\t */\n\t\t\tgetNative: function() {\n\t\t\t\tvar file = this.getSource().getSource();\n\t\t\t\treturn o.inArray(o.typeOf(file), ['blob', 'file']) !== -1 ? file : null;\n\t\t\t},\n\n\t\t\t/**\n\t\t\t * Returns mOxie.File - unified wrapper object that can be used across runtimes.\n\t\t\t *\n\t\t\t * @method getSource\n\t\t\t * @return {mOxie.File} or null\n\t\t\t */\n\t\t\tgetSource: function() {\n\t\t\t\tif (!filepool[this.id]) {\n\t\t\t\t\treturn null;\n\t\t\t\t}\n\t\t\t\treturn filepool[this.id];\n\t\t\t},\n\n\t\t\t/**\n\t\t\t * Destroys plupload.File object.\n\t\t\t *\n\t\t\t * @method destroy\n\t\t\t */\n\t\t\tdestroy: function() {\n\t\t\t\tvar src = this.getSource();\n\t\t\t\tif (src) {\n\t\t\t\t\tsrc.destroy();\n\t\t\t\t\tdelete filepool[this.id];\n\t\t\t\t}\n\t\t\t}\n\t\t});\n\n\t\tfilepool[this.id] = file;\n\t}\n\n\treturn PluploadFile;\n}());\n\n\n/**\n * Constructs a queue progress.\n *\n * @class QueueProgress\n * @constructor\n */\n plupload.QueueProgress = function() {\n\tvar self = this; // Setup alias for self to reduce code size when it's compressed\n\n\t/**\n\t * Total queue file size.\n\t *\n\t * @property size\n\t * @type Number\n\t */\n\tself.size = 0;\n\n\t/**\n\t * Total bytes uploaded.\n\t *\n\t * @property loaded\n\t * @type Number\n\t */\n\tself.loaded = 0;\n\n\t/**\n\t * Number of files uploaded.\n\t *\n\t * @property uploaded\n\t * @type Number\n\t */\n\tself.uploaded = 0;\n\n\t/**\n\t * Number of files failed to upload.\n\t *\n\t * @property failed\n\t * @type Number\n\t */\n\tself.failed = 0;\n\n\t/**\n\t * Number of files yet to be uploaded.\n\t *\n\t * @property queued\n\t * @type Number\n\t */\n\tself.queued = 0;\n\n\t/**\n\t * Total percent of the uploaded bytes.\n\t *\n\t * @property percent\n\t * @type Number\n\t */\n\tself.percent = 0;\n\n\t/**\n\t * Bytes uploaded per second.\n\t *\n\t * @property bytesPerSec\n\t * @type Number\n\t */\n\tself.bytesPerSec = 0;\n\n\t/**\n\t * Resets the progress to it's initial values.\n\t *\n\t * @method reset\n\t */\n\tself.reset = function() {\n\t\tself.size = self.loaded = self.uploaded = self.failed = self.queued = self.percent = self.bytesPerSec = 0;\n\t};\n};\n\nwindow.plupload = plupload;\n\n}(window, mOxie));\n");
__$coverInitRange("Plupload", "223:223");
__$coverInitRange("Plupload", "224:50591");
__$coverInitRange("Plupload", "255:284");
__$coverInitRange("Plupload", "344:1774");
__$coverInitRange("Plupload", "1818:14251");
__$coverInitRange("Plupload", "17459:46971");
__$coverInitRange("Plupload", "47200:49369");
__$coverInitRange("Plupload", "49457:50542");
__$coverInitRange("Plupload", "50545:50571");
__$coverInitRange("Plupload", "382:434");
__$coverInitRange("Plupload", "438:1127");
__$coverInitRange("Plupload", "1131:1756");
__$coverInitRange("Plupload", "1761:1772");
__$coverInitRange("Plupload", "586:1016");
__$coverInitRange("Plupload", "1021:1124");
__$coverInitRange("Plupload", "1044:1070");
__$coverInitRange("Plupload", "1099:1120");
__$coverInitRange("Plupload", "1170:1262");
__$coverInitRange("Plupload", "1234:1256");
__$coverInitRange("Plupload", "1311:1394");
__$coverInitRange("Plupload", "1365:1388");
__$coverInitRange("Plupload", "1473:1574");
__$coverInitRange("Plupload", "1579:1639");
__$coverInitRange("Plupload", "1646:1753");
__$coverInitRange("Plupload", "1540:1570");
__$coverInitRange("Plupload", "1613:1635");
__$coverInitRange("Plupload", "1700:1731");
__$coverInitRange("Plupload", "7378:7496");
__$coverInitRange("Plupload", "7501:7647");
__$coverInitRange("Plupload", "7569:7635");
__$coverInitRange("Plupload", "11296:11309");
__$coverInitRange("Plupload", "11338:11638");
__$coverInitRange("Plupload", "11643:11736");
__$coverInitRange("Plupload", "11765:11797");
__$coverInitRange("Plupload", "11828:11872");
__$coverInitRange("Plupload", "11877:11888");
__$coverInitRange("Plupload", "11687:11732");
__$coverInitRange("Plupload", "12288:12302");
__$coverInitRange("Plupload", "12307:12449");
__$coverInitRange("Plupload", "12454:12523");
__$coverInitRange("Plupload", "12528:12538");
__$coverInitRange("Plupload", "12355:12443");
__$coverInitRange("Plupload", "12470:12519");
__$coverInitRange("Plupload", "12803:12883");
__$coverInitRange("Plupload", "12896:13006");
__$coverInitRange("Plupload", "13019:13123");
__$coverInitRange("Plupload", "13136:13234");
__$coverInitRange("Plupload", "13247:13339");
__$coverInitRange("Plupload", "13344:13387");
__$coverInitRange("Plupload", "12847:12879");
__$coverInitRange("Plupload", "12927:13002");
__$coverInitRange("Plupload", "13047:13119");
__$coverInitRange("Plupload", "13161:13230");
__$coverInitRange("Plupload", "13269:13335");
__$coverInitRange("Plupload", "14080:14095");
__$coverInitRange("Plupload", "14100:14149");
__$coverInitRange("Plupload", "14153:14187");
__$coverInitRange("Plupload", "14191:14211");
__$coverInitRange("Plupload", "14215:14227");
__$coverInitRange("Plupload", "14231:14245");
__$coverInitRange("Plupload", "14119:14145");
__$coverInitRange("Plupload", "20986:21099");
__$coverInitRange("Plupload", "21124:21774");
__$coverInitRange("Plupload", "21778:21896");
__$coverInitRange("Plupload", "21900:23011");
__$coverInitRange("Plupload", "23015:26226");
__$coverInitRange("Plupload", "26230:26393");
__$coverInitRange("Plupload", "26397:26808");
__$coverInitRange("Plupload", "26836:26872");
__$coverInitRange("Plupload", "26897:27275");
__$coverInitRange("Plupload", "27299:27426");
__$coverInitRange("Plupload", "27451:27517");
__$coverInitRange("Plupload", "27520:27592");
__$coverInitRange("Plupload", "27597:27686");
__$coverInitRange("Plupload", "27714:46968");
__$coverInitRange("Plupload", "21150:21172");
__$coverInitRange("Plupload", "21177:21771");
__$coverInitRange("Plupload", "21247:21531");
__$coverInitRange("Plupload", "21572:21767");
__$coverInitRange("Plupload", "21288:21526");
__$coverInitRange("Plupload", "21344:21359");
__$coverInitRange("Plupload", "21366:21493");
__$coverInitRange("Plupload", "21414:21446");
__$coverInitRange("Plupload", "21454:21486");
__$coverInitRange("Plupload", "21513:21520");
__$coverInitRange("Plupload", "21605:21719");
__$coverInitRange("Plupload", "21725:21762");
__$coverInitRange("Plupload", "21649:21678");
__$coverInitRange("Plupload", "21685:21713");
__$coverInitRange("Plupload", "21806:21883");
__$coverInitRange("Plupload", "21887:21893");
__$coverInitRange("Plupload", "21920:21931");
__$coverInitRange("Plupload", "21953:21966");
__$coverInitRange("Plupload", "22020:22603");
__$coverInitRange("Plupload", "22701:23008");
__$coverInitRange("Plupload", "22060:22075");
__$coverInitRange("Plupload", "22081:22432");
__$coverInitRange("Plupload", "22438:22599");
__$coverInitRange("Plupload", "22167:22194");
__$coverInitRange("Plupload", "22336:22391");
__$coverInitRange("Plupload", "22409:22427");
__$coverInitRange("Plupload", "22478:22494");
__$coverInitRange("Plupload", "22548:22562");
__$coverInitRange("Plupload", "22580:22594");
__$coverInitRange("Plupload", "22732:22817");
__$coverInitRange("Plupload", "22833:22920");
__$coverInitRange("Plupload", "22925:23004");
__$coverInitRange("Plupload", "23043:23075");
__$coverInitRange("Plupload", "23101:23296");
__$coverInitRange("Plupload", "23342:23491");
__$coverInitRange("Plupload", "23496:26223");
__$coverInitRange("Plupload", "23415:23485");
__$coverInitRange("Plupload", "23444:23480");
__$coverInitRange("Plupload", "23569:25189");
__$coverInitRange("Plupload", "23604:23831");
__$coverInitRange("Plupload", "23839:24175");
__$coverInitRange("Plupload", "24183:24255");
__$coverInitRange("Plupload", "24263:25034");
__$coverInitRange("Plupload", "25042:25135");
__$coverInitRange("Plupload", "25143:25159");
__$coverInitRange("Plupload", "23878:23917");
__$coverInitRange("Plupload", "23962:24133");
__$coverInitRange("Plupload", "24142:24155");
__$coverInitRange("Plupload", "24163:24167");
__$coverInitRange("Plupload", "24223:24247");
__$coverInitRange("Plupload", "24341:25025");
__$coverInitRange("Plupload", "24365:24408");
__$coverInitRange("Plupload", "24417:25017");
__$coverInitRange("Plupload", "24440:24707");
__$coverInitRange("Plupload", "24718:24984");
__$coverInitRange("Plupload", "24994:25008");
__$coverInitRange("Plupload", "24485:24697");
__$coverInitRange("Plupload", "24526:24575");
__$coverInitRange("Plupload", "24634:24686");
__$coverInitRange("Plupload", "24764:24974");
__$coverInitRange("Plupload", "24804:24854");
__$coverInitRange("Plupload", "24910:24963");
__$coverInitRange("Plupload", "25098:25114");
__$coverInitRange("Plupload", "25122:25126");
__$coverInitRange("Plupload", "25179:25183");
__$coverInitRange("Plupload", "25271:25824");
__$coverInitRange("Plupload", "25305:25409");
__$coverInitRange("Plupload", "25417:25595");
__$coverInitRange("Plupload", "25603:25672");
__$coverInitRange("Plupload", "25680:25771");
__$coverInitRange("Plupload", "25779:25794");
__$coverInitRange("Plupload", "25455:25494");
__$coverInitRange("Plupload", "25503:25553");
__$coverInitRange("Plupload", "25562:25575");
__$coverInitRange("Plupload", "25583:25587");
__$coverInitRange("Plupload", "25640:25664");
__$coverInitRange("Plupload", "25735:25750");
__$coverInitRange("Plupload", "25758:25762");
__$coverInitRange("Plupload", "25814:25818");
__$coverInitRange("Plupload", "25854:26029");
__$coverInitRange("Plupload", "26035:26217");
__$coverInitRange("Plupload", "25901:25920");
__$coverInitRange("Plupload", "25938:26024");
__$coverInitRange("Plupload", "25995:26016");
__$coverInitRange("Plupload", "26058:26082");
__$coverInitRange("Plupload", "26100:26212");
__$coverInitRange("Plupload", "26265:26374");
__$coverInitRange("Plupload", "26378:26390");
__$coverInitRange("Plupload", "26285:26324");
__$coverInitRange("Plupload", "26329:26370");
__$coverInitRange("Plupload", "26345:26365");
__$coverInitRange("Plupload", "26440:26463");
__$coverInitRange("Plupload", "26468:26805");
__$coverInitRange("Plupload", "26477:26592");
__$coverInitRange("Plupload", "26598:26701");
__$coverInitRange("Plupload", "26707:26752");
__$coverInitRange("Plupload", "26758:26772");
__$coverInitRange("Plupload", "26507:26586");
__$coverInitRange("Plupload", "26630:26675");
__$coverInitRange("Plupload", "26681:26695");
__$coverInitRange("Plupload", "26738:26746");
__$coverInitRange("Plupload", "26793:26801");
__$coverInitRange("Plupload", "27324:27423");
__$coverInitRange("Plupload", "29275:29290");
__$coverInitRange("Plupload", "29296:29350");
__$coverInitRange("Plupload", "29394:29446");
__$coverInitRange("Plupload", "29453:29637");
__$coverInitRange("Plupload", "29677:29858");
__$coverInitRange("Plupload", "29889:32214");
__$coverInitRange("Plupload", "32220:32303");
__$coverInitRange("Plupload", "32348:32599");
__$coverInitRange("Plupload", "32605:38059");
__$coverInitRange("Plupload", "38065:38139");
__$coverInitRange("Plupload", "38145:38579");
__$coverInitRange("Plupload", "38585:38616");
__$coverInitRange("Plupload", "38622:39056");
__$coverInitRange("Plupload", "39062:39305");
__$coverInitRange("Plupload", "39506:39553");
__$coverInitRange("Plupload", "39559:39582");
__$coverInitRange("Plupload", "29503:29525");
__$coverInitRange("Plupload", "29543:29632");
__$coverInitRange("Plupload", "29603:29624");
__$coverInitRange("Plupload", "29729:29841");
__$coverInitRange("Plupload", "29847:29853");
__$coverInitRange("Plupload", "29948:30020");
__$coverInitRange("Plupload", "30063:30578");
__$coverInitRange("Plupload", "30585:31937");
__$coverInitRange("Plupload", "32008:32207");
__$coverInitRange("Plupload", "30101:30122");
__$coverInitRange("Plupload", "30130:30489");
__$coverInitRange("Plupload", "30497:30572");
__$coverInitRange("Plupload", "30178:30480");
__$coverInitRange("Plupload", "30245:30470");
__$coverInitRange("Plupload", "30283:30312");
__$coverInitRange("Plupload", "30338:30461");
__$coverInitRange("Plupload", "30651:30675");
__$coverInitRange("Plupload", "30682:30697");
__$coverInitRange("Plupload", "30704:30720");
__$coverInitRange("Plupload", "30727:30756");
__$coverInitRange("Plupload", "30795:31042");
__$coverInitRange("Plupload", "31076:31342");
__$coverInitRange("Plupload", "31376:31862");
__$coverInitRange("Plupload", "31901:31917");
__$coverInitRange("Plupload", "31924:31931");
__$coverInitRange("Plupload", "30862:31018");
__$coverInitRange("Plupload", "31027:31035");
__$coverInitRange("Plupload", "31173:31319");
__$coverInitRange("Plupload", "31327:31335");
__$coverInitRange("Plupload", "31417:31437");
__$coverInitRange("Plupload", "31445:31855");
__$coverInitRange("Plupload", "31564:31847");
__$coverInitRange("Plupload", "31646:31810");
__$coverInitRange("Plupload", "31820:31838");
__$coverInitRange("Plupload", "32026:32113");
__$coverInitRange("Plupload", "32051:32079");
__$coverInitRange("Plupload", "32087:32101");
__$coverInitRange("Plupload", "32133:32145");
__$coverInitRange("Plupload", "32263:32296");
__$coverInitRange("Plupload", "32279:32290");
__$coverInitRange("Plupload", "32381:32594");
__$coverInitRange("Plupload", "32433:32490");
__$coverInitRange("Plupload", "32497:32541");
__$coverInitRange("Plupload", "32548:32586");
__$coverInitRange("Plupload", "32518:32534");
__$coverInitRange("Plupload", "32654:32796");
__$coverInitRange("Plupload", "32853:32953");
__$coverInitRange("Plupload", "32960:33381");
__$coverInitRange("Plupload", "33388:37604");
__$coverInitRange("Plupload", "37611:37634");
__$coverInitRange("Plupload", "37671:38052");
__$coverInitRange("Plupload", "32877:32947");
__$coverInitRange("Plupload", "32990:33375");
__$coverInitRange("Plupload", "33017:33042");
__$coverInitRange("Plupload", "33064:33084");
__$coverInitRange("Plupload", "33115:33368");
__$coverInitRange("Plupload", "33422:33465");
__$coverInitRange("Plupload", "33502:33623");
__$coverInitRange("Plupload", "33658:33703");
__$coverInitRange("Plupload", "33752:34399");
__$coverInitRange("Plupload", "34407:34435");
__$coverInitRange("Plupload", "34486:34666");
__$coverInitRange("Plupload", "34674:35959");
__$coverInitRange("Plupload", "35967:36021");
__$coverInitRange("Plupload", "36029:36104");
__$coverInitRange("Plupload", "36144:37598");
__$coverInitRange("Plupload", "33610:33616");
__$coverInitRange("Plupload", "33878:33932");
__$coverInitRange("Plupload", "33941:33994");
__$coverInitRange("Plupload", "34041:34322");
__$coverInitRange("Plupload", "34082:34124");
__$coverInitRange("Plupload", "34133:34179");
__$coverInitRange("Plupload", "34263:34283");
__$coverInitRange("Plupload", "34292:34314");
__$coverInitRange("Plupload", "34344:34368");
__$coverInitRange("Plupload", "34376:34392");
__$coverInitRange("Plupload", "34510:34659");
__$coverInitRange("Plupload", "34555:34607");
__$coverInitRange("Plupload", "34616:34650");
__$coverInitRange("Plupload", "34751:34819");
__$coverInitRange("Plupload", "34859:35285");
__$coverInitRange("Plupload", "35294:35321");
__$coverInitRange("Plupload", "35380:35951");
__$coverInitRange("Plupload", "34783:34796");
__$coverInitRange("Plupload", "34805:34811");
__$coverInitRange("Plupload", "34898:34917");
__$coverInitRange("Plupload", "34927:34949");
__$coverInitRange("Plupload", "34958:34999");
__$coverInitRange("Plupload", "35009:35230");
__$coverInitRange("Plupload", "35254:35277");
__$coverInitRange("Plupload", "35474:35560");
__$coverInitRange("Plupload", "35570:35604");
__$coverInitRange("Plupload", "35614:35641");
__$coverInitRange("Plupload", "35651:35814");
__$coverInitRange("Plupload", "35516:35530");
__$coverInitRange("Plupload", "35540:35551");
__$coverInitRange("Plupload", "35866:35891");
__$coverInitRange("Plupload", "36000:36013");
__$coverInitRange("Plupload", "36064:36078");
__$coverInitRange("Plupload", "36086:36096");
__$coverInitRange("Plupload", "36202:36243");
__$coverInitRange("Plupload", "36252:36279");
__$coverInitRange("Plupload", "36316:36425");
__$coverInitRange("Plupload", "36434:36461");
__$coverInitRange("Plupload", "36500:36636");
__$coverInitRange("Plupload", "36675:36729");
__$coverInitRange("Plupload", "36737:36938");
__$coverInitRange("Plupload", "36382:36415");
__$coverInitRange("Plupload", "36598:36626");
__$coverInitRange("Plupload", "37008:37101");
__$coverInitRange("Plupload", "37110:37137");
__$coverInitRange("Plupload", "37146:37210");
__$coverInitRange("Plupload", "37271:37380");
__$coverInitRange("Plupload", "37389:37591");
__$coverInitRange("Plupload", "37337:37370");
__$coverInitRange("Plupload", "37842:38009");
__$coverInitRange("Plupload", "37921:37939");
__$coverInitRange("Plupload", "37947:37975");
__$coverInitRange("Plupload", "37983:38000");
__$coverInitRange("Plupload", "38029:38046");
__$coverInitRange("Plupload", "38118:38132");
__$coverInitRange("Plupload", "38190:38572");
__$coverInitRange("Plupload", "38271:38296");
__$coverInitRange("Plupload", "38390:38566");
__$coverInitRange("Plupload", "38445:38559");
__$coverInitRange("Plupload", "38500:38536");
__$coverInitRange("Plupload", "38545:38551");
__$coverInitRange("Plupload", "38720:39049");
__$coverInitRange("Plupload", "38741:38774");
__$coverInitRange("Plupload", "38782:38800");
__$coverInitRange("Plupload", "38934:39043");
__$coverInitRange("Plupload", "38976:39036");
__$coverInitRange("Plupload", "39002:39023");
__$coverInitRange("Plupload", "39105:39111");
__$coverInitRange("Plupload", "39242:39298");
__$coverInitRange("Plupload", "39266:39287");
__$coverInitRange("Plupload", "39819:39873");
__$coverInitRange("Plupload", "39878:39901");
__$coverInitRange("Plupload", "39840:39868");
__$coverInitRange("Plupload", "40011:40149");
__$coverInitRange("Plupload", "40053:40082");
__$coverInitRange("Plupload", "40088:40116");
__$coverInitRange("Plupload", "40123:40144");
__$coverInitRange("Plupload", "40260:40404");
__$coverInitRange("Plupload", "40302:40331");
__$coverInitRange("Plupload", "40337:40365");
__$coverInitRange("Plupload", "40371:40399");
__$coverInitRange("Plupload", "40614:40669");
__$coverInitRange("Plupload", "40675:40728");
__$coverInitRange("Plupload", "40734:40773");
__$coverInitRange("Plupload", "40696:40723");
__$coverInitRange("Plupload", "41014:41019");
__$coverInitRange("Plupload", "41024:41127");
__$coverInitRange("Plupload", "41070:41122");
__$coverInitRange("Plupload", "41101:41116");
__$coverInitRange("Plupload", "41650:41677");
__$coverInitRange("Plupload", "41678:41678");
__$coverInitRange("Plupload", "41684:41821");
__$coverInitRange("Plupload", "41827:42820");
__$coverInitRange("Plupload", "42826:42842");
__$coverInitRange("Plupload", "42848:42865");
__$coverInitRange("Plupload", "42917:42979");
__$coverInitRange("Plupload", "41709:41741");
__$coverInitRange("Plupload", "41747:41798");
__$coverInitRange("Plupload", "41804:41816");
__$coverInitRange("Plupload", "41764:41792");
__$coverInitRange("Plupload", "41860:41885");
__$coverInitRange("Plupload", "41892:42815");
__$coverInitRange("Plupload", "41928:42070");
__$coverInitRange("Plupload", "42077:42113");
__$coverInitRange("Plupload", "41952:42006");
__$coverInitRange("Plupload", "42014:42030");
__$coverInitRange("Plupload", "42038:42063");
__$coverInitRange("Plupload", "41986:41998");
__$coverInitRange("Plupload", "42161:42190");
__$coverInitRange("Plupload", "42197:42211");
__$coverInitRange("Plupload", "42299:42348");
__$coverInitRange("Plupload", "42355:42371");
__$coverInitRange("Plupload", "42321:42341");
__$coverInitRange("Plupload", "42437:42472");
__$coverInitRange("Plupload", "42602:42633");
__$coverInitRange("Plupload", "42695:42710");
__$coverInitRange("Plupload", "42784:42809");
__$coverInitRange("Plupload", "42941:42974");
__$coverInitRange("Plupload", "43160:43211");
__$coverInitRange("Plupload", "43217:43336");
__$coverInitRange("Plupload", "43267:43331");
__$coverInitRange("Plupload", "43298:43325");
__$coverInitRange("Plupload", "43765:43862");
__$coverInitRange("Plupload", "43868:43905");
__$coverInitRange("Plupload", "43910:43938");
__$coverInitRange("Plupload", "43997:44062");
__$coverInitRange("Plupload", "44068:44082");
__$coverInitRange("Plupload", "44041:44055");
__$coverInitRange("Plupload", "44372:44418");
__$coverInitRange("Plupload", "44461:44814");
__$coverInitRange("Plupload", "44820:44831");
__$coverInitRange("Plupload", "44517:44561");
__$coverInitRange("Plupload", "44567:44581");
__$coverInitRange("Plupload", "44627:44809");
__$coverInitRange("Plupload", "44721:44803");
__$coverInitRange("Plupload", "44784:44796");
__$coverInitRange("Plupload", "45048:45083");
__$coverInitRange("Plupload", "45409:45417");
__$coverInitRange("Plupload", "45423:45448");
__$coverInitRange("Plupload", "45453:45478");
__$coverInitRange("Plupload", "45483:45530");
__$coverInitRange("Plupload", "45535:45554");
__$coverInitRange("Plupload", "45786:45811");
__$coverInitRange("Plupload", "45817:45864");
__$coverInitRange("Plupload", "45870:46188");
__$coverInitRange("Plupload", "45886:46078");
__$coverInitRange("Plupload", "46133:46183");
__$coverInitRange("Plupload", "45913:46043");
__$coverInitRange("Plupload", "45960:46036");
__$coverInitRange("Plupload", "45996:46013");
__$coverInitRange("Plupload", "46023:46028");
__$coverInitRange("Plupload", "46063:46072");
__$coverInitRange("Plupload", "46158:46177");
__$coverInitRange("Plupload", "46300:46315");
__$coverInitRange("Plupload", "46321:46394");
__$coverInitRange("Plupload", "46370:46387");
__$coverInitRange("Plupload", "46525:46536");
__$coverInitRange("Plupload", "46564:46627");
__$coverInitRange("Plupload", "46632:46642");
__$coverInitRange("Plupload", "46648:46715");
__$coverInitRange("Plupload", "46721:46785");
__$coverInitRange("Plupload", "46791:46809");
__$coverInitRange("Plupload", "46814:46855");
__$coverInitRange("Plupload", "46861:46884");
__$coverInitRange("Plupload", "46927:46943");
__$coverInitRange("Plupload", "46948:46959");
__$coverInitRange("Plupload", "46606:46620");
__$coverInitRange("Plupload", "46669:46688");
__$coverInitRange("Plupload", "46694:46710");
__$coverInitRange("Plupload", "46741:46759");
__$coverInitRange("Plupload", "46765:46780");
__$coverInitRange("Plupload", "47231:47248");
__$coverInitRange("Plupload", "47252:49340");
__$coverInitRange("Plupload", "49344:49363");
__$coverInitRange("Plupload", "47285:49308");
__$coverInitRange("Plupload", "49313:49337");
__$coverInitRange("Plupload", "48685:48724");
__$coverInitRange("Plupload", "48730:48801");
__$coverInitRange("Plupload", "49004:49052");
__$coverInitRange("Plupload", "49058:49082");
__$coverInitRange("Plupload", "49035:49046");
__$coverInitRange("Plupload", "49199:49225");
__$coverInitRange("Plupload", "49231:49297");
__$coverInitRange("Plupload", "49247:49260");
__$coverInitRange("Plupload", "49267:49291");
__$coverInitRange("Plupload", "49496:49511");
__$coverInitRange("Plupload", "49657:49670");
__$coverInitRange("Plupload", "49752:49767");
__$coverInitRange("Plupload", "49855:49872");
__$coverInitRange("Plupload", "49966:49981");
__$coverInitRange("Plupload", "50077:50092");
__$coverInitRange("Plupload", "50190:50206");
__$coverInitRange("Plupload", "50298:50318");
__$coverInitRange("Plupload", "50402:50539");
__$coverInitRange("Plupload", "50430:50535");
__$coverCall('Plupload', '223:223');
;
__$coverCall('Plupload', '224:50591');
(function (window, o, undef) {
    __$coverCall('Plupload', '255:284');
    var delay = window.setTimeout;
    __$coverCall('Plupload', '344:1774');
    function normalizeCaps(settings) {
        __$coverCall('Plupload', '382:434');
        var features = settings.required_features, caps = {};
        __$coverCall('Plupload', '438:1127');
        function resolve(feature, value, strict) {
            __$coverCall('Plupload', '586:1016');
            var map = {
                    chunks: 'slice_blob',
                    resize: 'send_binary_string',
                    jpgresize: 'send_binary_string',
                    pngresize: 'send_binary_string',
                    progress: 'report_upload_progress',
                    multi_selection: 'select_multiple',
                    max_file_size: 'access_binary',
                    dragdrop: 'drag_and_drop',
                    drop_element: 'drag_and_drop',
                    headers: 'send_custom_headers',
                    canSendBinary: 'send_binary',
                    triggerDialog: 'summon_file_dialog'
                };
            __$coverCall('Plupload', '1021:1124');
            if (map[feature]) {
                __$coverCall('Plupload', '1044:1070');
                caps[map[feature]] = value;
            } else if (!strict) {
                __$coverCall('Plupload', '1099:1120');
                caps[feature] = value;
            }
        }
        __$coverCall('Plupload', '1131:1756');
        if (typeof features === 'string') {
            __$coverCall('Plupload', '1170:1262');
            plupload.each(features.split(/\s*,\s*/), function (feature) {
                __$coverCall('Plupload', '1234:1256');
                resolve(feature, true);
            });
        } else if (typeof features === 'object') {
            __$coverCall('Plupload', '1311:1394');
            plupload.each(features, function (value, feature) {
                __$coverCall('Plupload', '1365:1388');
                resolve(feature, value);
            });
        } else if (features === true) {
            __$coverCall('Plupload', '1473:1574');
            if (!settings.multipart) {
                __$coverCall('Plupload', '1540:1570');
                caps.send_binary_string = true;
            }
            __$coverCall('Plupload', '1579:1639');
            if (settings.chunk_size > 0) {
                __$coverCall('Plupload', '1613:1635');
                caps.slice_blob = true;
            }
            __$coverCall('Plupload', '1646:1753');
            plupload.each(settings, function (value, feature) {
                __$coverCall('Plupload', '1700:1731');
                resolve(feature, !!value, true);
            });
        }
        __$coverCall('Plupload', '1761:1772');
        return caps;
    }
    __$coverCall('Plupload', '1818:14251');
    var plupload = {
            VERSION: '@@version@@',
            STOPPED: 1,
            STARTED: 2,
            QUEUED: 1,
            UPLOADING: 2,
            FAILED: 4,
            DONE: 5,
            GENERIC_ERROR: -100,
            HTTP_ERROR: -200,
            IO_ERROR: -300,
            SECURITY_ERROR: -400,
            INIT_ERROR: -500,
            FILE_SIZE_ERROR: -600,
            FILE_EXTENSION_ERROR: -601,
            FILE_DUPLICATE_ERROR: -602,
            IMAGE_FORMAT_ERROR: -700,
            IMAGE_MEMORY_ERROR: -701,
            IMAGE_DIMENSIONS_ERROR: -702,
            mimeTypes: o.mimes,
            ua: o.ua,
            typeOf: o.typeOf,
            extend: o.extend,
            guid: o.guid,
            each: o.each,
            getPos: o.getPos,
            getSize: o.getSize,
            xmlEncode: function (str) {
                __$coverCall('Plupload', '7378:7496');
                var xmlEncodeChars = {
                        '<': 'lt',
                        '>': 'gt',
                        '&': 'amp',
                        '"': 'quot',
                        '\'': '#39'
                    }, xmlEncodeRegExp = /[<>&\"\']/g;
                __$coverCall('Plupload', '7501:7647');
                return str ? ('' + str).replace(xmlEncodeRegExp, function (chr) {
                    __$coverCall('Plupload', '7569:7635');
                    return xmlEncodeChars[chr] ? '&' + xmlEncodeChars[chr] + ';' : chr;
                }) : str;
            },
            toArray: o.toArray,
            inArray: o.inArray,
            addI18n: o.addI18n,
            translate: o.translate,
            isEmptyObj: o.isEmptyObj,
            hasClass: o.hasClass,
            addClass: o.addClass,
            removeClass: o.removeClass,
            getStyle: o.getStyle,
            addEvent: o.addEvent,
            removeEvent: o.removeEvent,
            removeAllEvents: o.removeAllEvents,
            cleanName: function (name) {
                __$coverCall('Plupload', '11296:11309');
                var i, lookup;
                __$coverCall('Plupload', '11338:11638');
                lookup = [
                    /[\300-\306]/g,
                    'A',
                    /[\340-\346]/g,
                    'a',
                    /\307/g,
                    'C',
                    /\347/g,
                    'c',
                    /[\310-\313]/g,
                    'E',
                    /[\350-\353]/g,
                    'e',
                    /[\314-\317]/g,
                    'I',
                    /[\354-\357]/g,
                    'i',
                    /\321/g,
                    'N',
                    /\361/g,
                    'n',
                    /[\322-\330]/g,
                    'O',
                    /[\362-\370]/g,
                    'o',
                    /[\331-\334]/g,
                    'U',
                    /[\371-\374]/g,
                    'u'
                ];
                __$coverCall('Plupload', '11643:11736');
                for (i = 0; i < lookup.length; i += 2) {
                    __$coverCall('Plupload', '11687:11732');
                    name = name.replace(lookup[i], lookup[i + 1]);
                }
                __$coverCall('Plupload', '11765:11797');
                name = name.replace(/\s+/g, '_');
                __$coverCall('Plupload', '11828:11872');
                name = name.replace(/[^a-z0-9_\-\.]+/gi, '');
                __$coverCall('Plupload', '11877:11888');
                return name;
            },
            buildUrl: function (url, items) {
                __$coverCall('Plupload', '12288:12302');
                var query = '';
                __$coverCall('Plupload', '12307:12449');
                plupload.each(items, function (value, name) {
                    __$coverCall('Plupload', '12355:12443');
                    query += (query ? '&' : '') + encodeURIComponent(name) + '=' + encodeURIComponent(value);
                });
                __$coverCall('Plupload', '12454:12523');
                if (query) {
                    __$coverCall('Plupload', '12470:12519');
                    url += (url.indexOf('?') > 0 ? '&' : '?') + query;
                }
                __$coverCall('Plupload', '12528:12538');
                return url;
            },
            formatSize: function (size) {
                __$coverCall('Plupload', '12803:12883');
                if (size === undef || /\D/.test(size)) {
                    __$coverCall('Plupload', '12847:12879');
                    return plupload.translate('N/A');
                }
                __$coverCall('Plupload', '12896:13006');
                if (size > 1099511627776) {
                    __$coverCall('Plupload', '12927:13002');
                    return Math.round(size / 1099511627776, 1) + ' ' + plupload.translate('tb');
                }
                __$coverCall('Plupload', '13019:13123');
                if (size > 1073741824) {
                    __$coverCall('Plupload', '13047:13119');
                    return Math.round(size / 1073741824, 1) + ' ' + plupload.translate('gb');
                }
                __$coverCall('Plupload', '13136:13234');
                if (size > 1048576) {
                    __$coverCall('Plupload', '13161:13230');
                    return Math.round(size / 1048576, 1) + ' ' + plupload.translate('mb');
                }
                __$coverCall('Plupload', '13247:13339');
                if (size > 1024) {
                    __$coverCall('Plupload', '13269:13335');
                    return Math.round(size / 1024, 1) + ' ' + plupload.translate('kb');
                }
                __$coverCall('Plupload', '13344:13387');
                return size + ' ' + plupload.translate('b');
            },
            parseSize: o.parseSizeStr,
            predictRuntime: function (config, runtimes) {
                __$coverCall('Plupload', '14080:14095');
                var up, runtime;
                __$coverCall('Plupload', '14100:14149');
                if (runtimes) {
                    __$coverCall('Plupload', '14119:14145');
                    config.runtimes = runtimes;
                }
                __$coverCall('Plupload', '14153:14187');
                up = new plupload.Uploader(config);
                __$coverCall('Plupload', '14191:14211');
                runtime = up.runtime;
                __$coverCall('Plupload', '14215:14227');
                up.destroy();
                __$coverCall('Plupload', '14231:14245');
                return runtime;
            }
        };
    __$coverCall('Plupload', '17459:46971');
    plupload.Uploader = function (settings) {
        __$coverCall('Plupload', '20986:21099');
        var files = [], events = {}, required_caps = {}, startTime, total, disabled = false, fileInput, fileDrop, xhr;
        __$coverCall('Plupload', '21124:21774');
        function uploadNext() {
            __$coverCall('Plupload', '21150:21172');
            var file, count = 0, i;
            __$coverCall('Plupload', '21177:21771');
            if (this.state == plupload.STARTED) {
                __$coverCall('Plupload', '21247:21531');
                for (i = 0; i < files.length; i++) {
                    __$coverCall('Plupload', '21288:21526');
                    if (!file && files[i].status == plupload.QUEUED) {
                        __$coverCall('Plupload', '21344:21359');
                        file = files[i];
                        __$coverCall('Plupload', '21366:21493');
                        if (this.trigger('BeforeUpload', file)) {
                            __$coverCall('Plupload', '21414:21446');
                            file.status = plupload.UPLOADING;
                            __$coverCall('Plupload', '21454:21486');
                            this.trigger('UploadFile', file);
                        }
                    } else {
                        __$coverCall('Plupload', '21513:21520');
                        count++;
                    }
                }
                __$coverCall('Plupload', '21572:21767');
                if (count == files.length) {
                    __$coverCall('Plupload', '21605:21719');
                    if (this.state !== plupload.STOPPED) {
                        __$coverCall('Plupload', '21649:21678');
                        this.state = plupload.STOPPED;
                        __$coverCall('Plupload', '21685:21713');
                        this.trigger('StateChanged');
                    }
                    __$coverCall('Plupload', '21725:21762');
                    this.trigger('UploadComplete', files);
                }
            }
        }
        __$coverCall('Plupload', '21778:21896');
        function calcFile(file) {
            __$coverCall('Plupload', '21806:21883');
            file.percent = file.size > 0 ? Math.ceil(file.loaded / file.size * 100) : 100;
            __$coverCall('Plupload', '21887:21893');
            calc();
        }
        __$coverCall('Plupload', '21900:23011');
        function calc() {
            __$coverCall('Plupload', '21920:21931');
            var i, file;
            __$coverCall('Plupload', '21953:21966');
            total.reset();
            __$coverCall('Plupload', '22020:22603');
            for (i = 0; i < files.length; i++) {
                __$coverCall('Plupload', '22060:22075');
                file = files[i];
                __$coverCall('Plupload', '22081:22432');
                if (file.size !== undef) {
                    __$coverCall('Plupload', '22167:22194');
                    total.size += file.origSize;
                    __$coverCall('Plupload', '22336:22391');
                    total.loaded += file.loaded * file.origSize / file.size;
                } else {
                    __$coverCall('Plupload', '22409:22427');
                    total.size = undef;
                }
                __$coverCall('Plupload', '22438:22599');
                if (file.status == plupload.DONE) {
                    __$coverCall('Plupload', '22478:22494');
                    total.uploaded++;
                } else if (file.status == plupload.FAILED) {
                    __$coverCall('Plupload', '22548:22562');
                    total.failed++;
                } else {
                    __$coverCall('Plupload', '22580:22594');
                    total.queued++;
                }
            }
            __$coverCall('Plupload', '22701:23008');
            if (total.size === undef) {
                __$coverCall('Plupload', '22732:22817');
                total.percent = files.length > 0 ? Math.ceil(total.uploaded / files.length * 100) : 0;
            } else {
                __$coverCall('Plupload', '22833:22920');
                total.bytesPerSec = Math.ceil(total.loaded / ((+new Date() - startTime || 1) / 1000));
                __$coverCall('Plupload', '22925:23004');
                total.percent = total.size > 0 ? Math.ceil(total.loaded / total.size * 100) : 0;
            }
        }
        __$coverCall('Plupload', '23015:26226');
        function initControls() {
            __$coverCall('Plupload', '23043:23075');
            var self = this, initialized = 0;
            __$coverCall('Plupload', '23101:23296');
            var options = {
                    accept: settings.filters,
                    runtime_order: settings.runtimes,
                    required_caps: required_caps,
                    swf_url: settings.flash_swf_url,
                    xap_url: settings.silverlight_xap_url
                };
            __$coverCall('Plupload', '23342:23491');
            plupload.each(settings.runtimes.split(/\s*,\s*/), function (runtime) {
                __$coverCall('Plupload', '23415:23485');
                if (settings[runtime]) {
                    __$coverCall('Plupload', '23444:23480');
                    options[runtime] = settings[runtime];
                }
            });
            __$coverCall('Plupload', '23496:26223');
            o.inSeries([
                function (cb) {
                    __$coverCall('Plupload', '23569:25189');
                    if (settings.browse_button) {
                        __$coverCall('Plupload', '23604:23831');
                        fileInput = new o.FileInput(plupload.extend({}, options, {
                            name: settings.file_data_name,
                            multiple: settings.multi_selection,
                            container: settings.container,
                            browse_button: settings.browse_button
                        }));
                        __$coverCall('Plupload', '23839:24175');
                        fileInput.onready = function () {
                            __$coverCall('Plupload', '23878:23917');
                            var info = o.Runtime.getInfo(this.ruid);
                            __$coverCall('Plupload', '23962:24133');
                            o.extend(self.features, {
                                chunks: info.can('slice_blob'),
                                multipart: info.can('send_multipart'),
                                multi_selection: info.can('select_multiple')
                            });
                            __$coverCall('Plupload', '24142:24155');
                            initialized++;
                            __$coverCall('Plupload', '24163:24167');
                            cb();
                        };
                        __$coverCall('Plupload', '24183:24255');
                        fileInput.onchange = function () {
                            __$coverCall('Plupload', '24223:24247');
                            self.addFile(this.files);
                        };
                        __$coverCall('Plupload', '24263:25034');
                        fileInput.bind('mouseenter mouseleave mousedown mouseup', function (e) {
                            __$coverCall('Plupload', '24341:25025');
                            if (!disabled) {
                                __$coverCall('Plupload', '24365:24408');
                                var bButton = o.get(settings.browse_button);
                                __$coverCall('Plupload', '24417:25017');
                                if (bButton) {
                                    __$coverCall('Plupload', '24440:24707');
                                    if (settings.browse_button_hover) {
                                        __$coverCall('Plupload', '24485:24697');
                                        if ('mouseenter' === e.type) {
                                            __$coverCall('Plupload', '24526:24575');
                                            o.addClass(bButton, settings.browse_button_hover);
                                        } else if ('mouseleave' === e.type) {
                                            __$coverCall('Plupload', '24634:24686');
                                            o.removeClass(bButton, settings.browse_button_hover);
                                        }
                                    }
                                    __$coverCall('Plupload', '24718:24984');
                                    if (settings.browse_button_active) {
                                        __$coverCall('Plupload', '24764:24974');
                                        if ('mousedown' === e.type) {
                                            __$coverCall('Plupload', '24804:24854');
                                            o.addClass(bButton, settings.browse_button_active);
                                        } else if ('mouseup' === e.type) {
                                            __$coverCall('Plupload', '24910:24963');
                                            o.removeClass(bButton, settings.browse_button_active);
                                        }
                                    }
                                    __$coverCall('Plupload', '24994:25008');
                                    bButton = null;
                                }
                            }
                        });
                        __$coverCall('Plupload', '25042:25135');
                        fileInput.bind('error runtimeerror', function () {
                            __$coverCall('Plupload', '25098:25114');
                            fileInput = null;
                            __$coverCall('Plupload', '25122:25126');
                            cb();
                        });
                        __$coverCall('Plupload', '25143:25159');
                        fileInput.init();
                    } else {
                        __$coverCall('Plupload', '25179:25183');
                        cb();
                    }
                },
                function (cb) {
                    __$coverCall('Plupload', '25271:25824');
                    if (settings.drop_element) {
                        __$coverCall('Plupload', '25305:25409');
                        fileDrop = new o.FileDrop(plupload.extend({}, options, { drop_zone: settings.drop_element }));
                        __$coverCall('Plupload', '25417:25595');
                        fileDrop.onready = function () {
                            __$coverCall('Plupload', '25455:25494');
                            var info = o.Runtime.getInfo(this.ruid);
                            __$coverCall('Plupload', '25503:25553');
                            self.features.dragdrop = info.can('drag_and_drop');
                            __$coverCall('Plupload', '25562:25575');
                            initialized++;
                            __$coverCall('Plupload', '25583:25587');
                            cb();
                        };
                        __$coverCall('Plupload', '25603:25672');
                        fileDrop.ondrop = function () {
                            __$coverCall('Plupload', '25640:25664');
                            self.addFile(this.files);
                        };
                        __$coverCall('Plupload', '25680:25771');
                        fileDrop.bind('error runtimeerror', function () {
                            __$coverCall('Plupload', '25735:25750');
                            fileDrop = null;
                            __$coverCall('Plupload', '25758:25762');
                            cb();
                        });
                        __$coverCall('Plupload', '25779:25794');
                        fileDrop.init();
                    } else {
                        __$coverCall('Plupload', '25814:25818');
                        cb();
                    }
                }
            ], function () {
                __$coverCall('Plupload', '25854:26029');
                if (typeof settings.init == 'function') {
                    __$coverCall('Plupload', '25901:25920');
                    settings.init(self);
                } else {
                    __$coverCall('Plupload', '25938:26024');
                    plupload.each(settings.init, function (func, name) {
                        __$coverCall('Plupload', '25995:26016');
                        self.bind(name, func);
                    });
                }
                __$coverCall('Plupload', '26035:26217');
                if (initialized) {
                    __$coverCall('Plupload', '26058:26082');
                    self.trigger('PostInit');
                } else {
                    __$coverCall('Plupload', '26100:26212');
                    self.trigger('Error', {
                        code: plupload.INIT_ERROR,
                        message: plupload.translate('Init error.')
                    });
                }
            });
        }
        __$coverCall('Plupload', '26230:26393');
        function runtimeCan(file, cap) {
            __$coverCall('Plupload', '26265:26374');
            if (file.ruid) {
                __$coverCall('Plupload', '26285:26324');
                var info = o.Runtime.getInfo(file.ruid);
                __$coverCall('Plupload', '26329:26370');
                if (info) {
                    __$coverCall('Plupload', '26345:26365');
                    return info.can(cap);
                }
            }
            __$coverCall('Plupload', '26378:26390');
            return false;
        }
        __$coverCall('Plupload', '26397:26808');
        function resizeImage(blob, params, cb) {
            __$coverCall('Plupload', '26440:26463');
            var img = new o.Image();
            __$coverCall('Plupload', '26468:26805');
            try {
                __$coverCall('Plupload', '26477:26592');
                img.onload = function () {
                    __$coverCall('Plupload', '26507:26586');
                    img.downsize(params.width, params.height, params.crop, params.preserve_headers);
                };
                __$coverCall('Plupload', '26598:26701');
                img.onresize = function () {
                    __$coverCall('Plupload', '26630:26675');
                    cb(this.getAsBlob(blob.type, params.quality));
                    __$coverCall('Plupload', '26681:26695');
                    this.destroy();
                };
                __$coverCall('Plupload', '26707:26752');
                img.onerror = function () {
                    __$coverCall('Plupload', '26738:26746');
                    cb(blob);
                };
                __$coverCall('Plupload', '26758:26772');
                img.load(blob);
            } catch (ex) {
                __$coverCall('Plupload', '26793:26801');
                cb(blob);
            }
        }
        __$coverCall('Plupload', '26836:26872');
        total = new plupload.QueueProgress();
        __$coverCall('Plupload', '26897:27275');
        settings = plupload.extend({
            runtimes: o.Runtime.order,
            max_retries: 0,
            multipart: true,
            multi_selection: true,
            file_data_name: 'file',
            flash_swf_url: 'js/Moxie.swf',
            silverlight_xap_url: 'js/Moxie.xap',
            filters: [],
            prevent_duplicates: false,
            send_chunk_number: true
        }, settings);
        __$coverCall('Plupload', '27299:27426');
        if (settings.resize) {
            __$coverCall('Plupload', '27324:27423');
            settings.resize = plupload.extend({
                preserve_headers: true,
                crop: false
            }, settings.resize);
        }
        __$coverCall('Plupload', '27451:27517');
        settings.chunk_size = plupload.parseSize(settings.chunk_size) || 0;
        __$coverCall('Plupload', '27520:27592');
        settings.max_file_size = plupload.parseSize(settings.max_file_size) || 0;
        __$coverCall('Plupload', '27597:27686');
        settings.required_features = required_caps = normalizeCaps(plupload.extend({}, settings));
        __$coverCall('Plupload', '27714:46968');
        plupload.extend(this, {
            id: plupload.guid(),
            state: plupload.STOPPED,
            features: {},
            runtime: o.Runtime.thatCan(required_caps, settings.runtimes),
            files: files,
            settings: settings,
            total: total,
            init: function () {
                __$coverCall('Plupload', '29275:29290');
                var self = this;
                __$coverCall('Plupload', '29296:29350');
                settings.browse_button = o.get(settings.browse_button);
                __$coverCall('Plupload', '29394:29446');
                settings.drop_element = o.get(settings.drop_element);
                __$coverCall('Plupload', '29453:29637');
                if (typeof settings.preinit == 'function') {
                    __$coverCall('Plupload', '29503:29525');
                    settings.preinit(self);
                } else {
                    __$coverCall('Plupload', '29543:29632');
                    plupload.each(settings.preinit, function (func, name) {
                        __$coverCall('Plupload', '29603:29624');
                        self.bind(name, func);
                    });
                }
                __$coverCall('Plupload', '29677:29858');
                if (!settings.browse_button || !settings.url) {
                    __$coverCall('Plupload', '29729:29841');
                    this.trigger('Error', {
                        code: plupload.INIT_ERROR,
                        message: plupload.translate('Init error.')
                    });
                    __$coverCall('Plupload', '29847:29853');
                    return;
                }
                __$coverCall('Plupload', '29889:32214');
                self.bind('FilesAdded', function (up, selected_files) {
                    __$coverCall('Plupload', '29948:30020');
                    var i, ii, file, count = 0, extensionsRegExp, filters = settings.filters;
                    __$coverCall('Plupload', '30063:30578');
                    if (filters && filters.length) {
                        __$coverCall('Plupload', '30101:30122');
                        extensionsRegExp = [];
                        __$coverCall('Plupload', '30130:30489');
                        plupload.each(filters, function (filter) {
                            __$coverCall('Plupload', '30178:30480');
                            plupload.each(filter.extensions.split(/,/), function (ext) {
                                __$coverCall('Plupload', '30245:30470');
                                if (/^\s*\*\s*$/.test(ext)) {
                                    __$coverCall('Plupload', '30283:30312');
                                    extensionsRegExp.push('\\.*');
                                } else {
                                    __$coverCall('Plupload', '30338:30461');
                                    extensionsRegExp.push('\\.' + ext.replace(new RegExp('[' + '/^$.*+?|()[]{}\\'.replace(/./g, '\\$&') + ']', 'g'), '\\$&'));
                                }
                            });
                        });
                        __$coverCall('Plupload', '30497:30572');
                        extensionsRegExp = new RegExp('(' + extensionsRegExp.join('|') + ')$', 'i');
                    }
                    __$coverCall('Plupload', '30585:31937');
                    next_file:
                        for (i = 0; i < selected_files.length; i++) {
                            __$coverCall('Plupload', '30651:30675');
                            file = selected_files[i];
                            __$coverCall('Plupload', '30682:30697');
                            file.loaded = 0;
                            __$coverCall('Plupload', '30704:30720');
                            file.percent = 0;
                            __$coverCall('Plupload', '30727:30756');
                            file.status = plupload.QUEUED;
                            __$coverCall('Plupload', '30795:31042');
                            if (extensionsRegExp && !extensionsRegExp.test(file.name)) {
                                __$coverCall('Plupload', '30862:31018');
                                up.trigger('Error', {
                                    code: plupload.FILE_EXTENSION_ERROR,
                                    message: plupload.translate('File extension error.'),
                                    file: file
                                });
                                __$coverCall('Plupload', '31027:31035');
                                continue;
                            }
                            __$coverCall('Plupload', '31076:31342');
                            if (file.size !== undef && settings.max_file_size && file.size > settings.max_file_size) {
                                __$coverCall('Plupload', '31173:31319');
                                up.trigger('Error', {
                                    code: plupload.FILE_SIZE_ERROR,
                                    message: plupload.translate('File size error.'),
                                    file: file
                                });
                                __$coverCall('Plupload', '31327:31335');
                                continue;
                            }
                            __$coverCall('Plupload', '31376:31862');
                            if (settings.prevent_duplicates) {
                                __$coverCall('Plupload', '31417:31437');
                                ii = up.files.length;
                                __$coverCall('Plupload', '31445:31855');
                                while (ii--) {
                                    __$coverCall('Plupload', '31564:31847');
                                    if (file.name === up.files[ii].name && file.size === up.files[ii].size) {
                                        __$coverCall('Plupload', '31646:31810');
                                        up.trigger('Error', {
                                            code: plupload.FILE_DUPLICATE_ERROR,
                                            message: plupload.translate('Duplicate file error.'),
                                            file: file
                                        });
                                        __$coverCall('Plupload', '31820:31838');
                                        continue next_file;
                                    }
                                }
                            }
                            __$coverCall('Plupload', '31901:31917');
                            files.push(file);
                            __$coverCall('Plupload', '31924:31931');
                            count++;
                        }
                    __$coverCall('Plupload', '32008:32207');
                    if (count) {
                        __$coverCall('Plupload', '32026:32113');
                        delay(function () {
                            __$coverCall('Plupload', '32051:32079');
                            self.trigger('QueueChanged');
                            __$coverCall('Plupload', '32087:32101');
                            self.refresh();
                        }, 1);
                    } else {
                        __$coverCall('Plupload', '32133:32145');
                        return false;
                    }
                });
                __$coverCall('Plupload', '32220:32303');
                self.bind('CancelUpload', function () {
                    __$coverCall('Plupload', '32263:32296');
                    if (xhr) {
                        __$coverCall('Plupload', '32279:32290');
                        xhr.abort();
                    }
                });
                __$coverCall('Plupload', '32348:32599');
                if (settings.unique_names) {
                    __$coverCall('Plupload', '32381:32594');
                    self.bind('BeforeUpload', function (up, file) {
                        __$coverCall('Plupload', '32433:32490');
                        var matches = file.name.match(/\.([^.]+)$/), ext = 'part';
                        __$coverCall('Plupload', '32497:32541');
                        if (matches) {
                            __$coverCall('Plupload', '32518:32534');
                            ext = matches[1];
                        }
                        __$coverCall('Plupload', '32548:32586');
                        file.target_name = file.id + '.' + ext;
                    });
                }
                __$coverCall('Plupload', '32605:38059');
                self.bind('UploadFile', function (up, file) {
                    __$coverCall('Plupload', '32654:32796');
                    var url = up.settings.url, features = up.features, chunkSize = settings.chunk_size, retries = settings.max_retries, blob, offset = 0;
                    __$coverCall('Plupload', '32853:32953');
                    if (file.loaded) {
                        __$coverCall('Plupload', '32877:32947');
                        offset = file.loaded = chunkSize * Math.floor(file.loaded / chunkSize);
                    }
                    __$coverCall('Plupload', '32960:33381');
                    function handleError() {
                        __$coverCall('Plupload', '32990:33375');
                        if (retries-- > 0) {
                            __$coverCall('Plupload', '33017:33042');
                            delay(uploadNextChunk, 1);
                        } else {
                            __$coverCall('Plupload', '33064:33084');
                            file.loaded = offset;
                            __$coverCall('Plupload', '33115:33368');
                            up.trigger('Error', {
                                code: plupload.HTTP_ERROR,
                                message: plupload.translate('HTTP Error.'),
                                file: file,
                                response: xhr.responseText,
                                status: xhr.status,
                                responseHeaders: xhr.getAllResponseHeaders()
                            });
                        }
                    }
                    __$coverCall('Plupload', '33388:37604');
                    function uploadNextChunk() {
                        __$coverCall('Plupload', '33422:33465');
                        var chunkBlob, formData, args, curChunkSize;
                        __$coverCall('Plupload', '33502:33623');
                        if (file.status == plupload.DONE || file.status == plupload.FAILED || up.state == plupload.STOPPED) {
                            __$coverCall('Plupload', '33610:33616');
                            return;
                        }
                        __$coverCall('Plupload', '33658:33703');
                        args = { name: file.target_name || file.name };
                        __$coverCall('Plupload', '33752:34399');
                        if (chunkSize && features.chunks && blob.size > chunkSize) {
                            __$coverCall('Plupload', '33878:33932');
                            curChunkSize = Math.min(chunkSize, blob.size - offset);
                            __$coverCall('Plupload', '33941:33994');
                            chunkBlob = blob.slice(offset, offset + curChunkSize);
                            __$coverCall('Plupload', '34041:34322');
                            if (settings.send_chunk_number) {
                                __$coverCall('Plupload', '34082:34124');
                                args.chunk = Math.ceil(offset / chunkSize);
                                __$coverCall('Plupload', '34133:34179');
                                args.chunks = Math.ceil(blob.size / chunkSize);
                            } else {
                                __$coverCall('Plupload', '34263:34283');
                                args.offset = offset;
                                __$coverCall('Plupload', '34292:34314');
                                args.total = blob.size;
                            }
                        } else {
                            __$coverCall('Plupload', '34344:34368');
                            curChunkSize = blob.size;
                            __$coverCall('Plupload', '34376:34392');
                            chunkBlob = blob;
                        }
                        __$coverCall('Plupload', '34407:34435');
                        xhr = new o.XMLHttpRequest();
                        __$coverCall('Plupload', '34486:34666');
                        if (xhr.upload) {
                            __$coverCall('Plupload', '34510:34659');
                            xhr.upload.onprogress = function (e) {
                                __$coverCall('Plupload', '34555:34607');
                                file.loaded = Math.min(file.size, offset + e.loaded);
                                __$coverCall('Plupload', '34616:34650');
                                up.trigger('UploadProgress', file);
                            };
                        }
                        __$coverCall('Plupload', '34674:35959');
                        xhr.onload = function () {
                            __$coverCall('Plupload', '34751:34819');
                            if (xhr.status >= 400) {
                                __$coverCall('Plupload', '34783:34796');
                                handleError();
                                __$coverCall('Plupload', '34805:34811');
                                return;
                            }
                            __$coverCall('Plupload', '34859:35285');
                            if (curChunkSize < blob.size) {
                                __$coverCall('Plupload', '34898:34917');
                                chunkBlob.destroy();
                                __$coverCall('Plupload', '34927:34949');
                                offset += curChunkSize;
                                __$coverCall('Plupload', '34958:34999');
                                file.loaded = Math.min(offset, blob.size);
                                __$coverCall('Plupload', '35009:35230');
                                up.trigger('ChunkUploaded', file, {
                                    offset: file.loaded,
                                    total: blob.size,
                                    response: xhr.responseText,
                                    status: xhr.status,
                                    responseHeaders: xhr.getAllResponseHeaders()
                                });
                            } else {
                                __$coverCall('Plupload', '35254:35277');
                                file.loaded = file.size;
                            }
                            __$coverCall('Plupload', '35294:35321');
                            chunkBlob = formData = null;
                            __$coverCall('Plupload', '35380:35951');
                            if (!offset || offset >= blob.size) {
                                __$coverCall('Plupload', '35474:35560');
                                if (file.size != file.origSize) {
                                    __$coverCall('Plupload', '35516:35530');
                                    blob.destroy();
                                    __$coverCall('Plupload', '35540:35551');
                                    blob = null;
                                }
                                __$coverCall('Plupload', '35570:35604');
                                up.trigger('UploadProgress', file);
                                __$coverCall('Plupload', '35614:35641');
                                file.status = plupload.DONE;
                                __$coverCall('Plupload', '35651:35814');
                                up.trigger('FileUploaded', file, {
                                    response: xhr.responseText,
                                    status: xhr.status,
                                    responseHeaders: xhr.getAllResponseHeaders()
                                });
                            } else {
                                __$coverCall('Plupload', '35866:35891');
                                delay(uploadNextChunk, 1);
                            }
                        };
                        __$coverCall('Plupload', '35967:36021');
                        xhr.onerror = function () {
                            __$coverCall('Plupload', '36000:36013');
                            handleError();
                        };
                        __$coverCall('Plupload', '36029:36104');
                        xhr.onloadend = function () {
                            __$coverCall('Plupload', '36064:36078');
                            this.destroy();
                            __$coverCall('Plupload', '36086:36096');
                            xhr = null;
                        };
                        __$coverCall('Plupload', '36144:37598');
                        if (up.settings.multipart && features.multipart) {
                            __$coverCall('Plupload', '36202:36243');
                            args.name = file.target_name || file.name;
                            __$coverCall('Plupload', '36252:36279');
                            xhr.open('post', url, true);
                            __$coverCall('Plupload', '36316:36425');
                            plupload.each(up.settings.headers, function (value, name) {
                                __$coverCall('Plupload', '36382:36415');
                                xhr.setRequestHeader(name, value);
                            });
                            __$coverCall('Plupload', '36434:36461');
                            formData = new o.FormData();
                            __$coverCall('Plupload', '36500:36636');
                            plupload.each(plupload.extend(args, up.settings.multipart_params), function (value, name) {
                                __$coverCall('Plupload', '36598:36626');
                                formData.append(name, value);
                            });
                            __$coverCall('Plupload', '36675:36729');
                            formData.append(up.settings.file_data_name, chunkBlob);
                            __$coverCall('Plupload', '36737:36938');
                            xhr.send(formData, {
                                runtime_order: up.settings.runtimes,
                                required_caps: required_caps,
                                swf_url: up.settings.flash_swf_url,
                                xap_url: up.settings.silverlight_xap_url
                            });
                        } else {
                            __$coverCall('Plupload', '37008:37101');
                            url = plupload.buildUrl(up.settings.url, plupload.extend(args, up.settings.multipart_params));
                            __$coverCall('Plupload', '37110:37137');
                            xhr.open('post', url, true);
                            __$coverCall('Plupload', '37146:37210');
                            xhr.setRequestHeader('Content-Type', 'application/octet-stream');
                            __$coverCall('Plupload', '37271:37380');
                            plupload.each(up.settings.headers, function (value, name) {
                                __$coverCall('Plupload', '37337:37370');
                                xhr.setRequestHeader(name, value);
                            });
                            __$coverCall('Plupload', '37389:37591');
                            xhr.send(chunkBlob, {
                                runtime_order: up.settings.runtimes,
                                required_caps: required_caps,
                                swf_url: up.settings.flash_swf_url,
                                xap_url: up.settings.silverlight_xap_url
                            });
                        }
                    }
                    __$coverCall('Plupload', '37611:37634');
                    blob = file.getSource();
                    __$coverCall('Plupload', '37671:38052');
                    if (!o.isEmptyObj(up.settings.resize) && runtimeCan(blob, 'send_binary_string') && !!~o.inArray(blob.type, [
                            'image/jpeg',
                            'image/png'
                        ])) {
                        __$coverCall('Plupload', '37842:38009');
                        resizeImage.call(this, blob, up.settings.resize, function (resizedBlob) {
                            __$coverCall('Plupload', '37921:37939');
                            blob = resizedBlob;
                            __$coverCall('Plupload', '37947:37975');
                            file.size = resizedBlob.size;
                            __$coverCall('Plupload', '37983:38000');
                            uploadNextChunk();
                        });
                    } else {
                        __$coverCall('Plupload', '38029:38046');
                        uploadNextChunk();
                    }
                });
                __$coverCall('Plupload', '38065:38139');
                self.bind('UploadProgress', function (up, file) {
                    __$coverCall('Plupload', '38118:38132');
                    calcFile(file);
                });
                __$coverCall('Plupload', '38145:38579');
                self.bind('StateChanged', function (up) {
                    __$coverCall('Plupload', '38190:38572');
                    if (up.state == plupload.STARTED) {
                        __$coverCall('Plupload', '38271:38296');
                        startTime = +new Date();
                    } else if (up.state == plupload.STOPPED) {
                        __$coverCall('Plupload', '38390:38566');
                        for (var i = up.files.length - 1; i >= 0; i--) {
                            __$coverCall('Plupload', '38445:38559');
                            if (up.files[i].status == plupload.UPLOADING) {
                                __$coverCall('Plupload', '38500:38536');
                                up.files[i].status = plupload.QUEUED;
                                __$coverCall('Plupload', '38545:38551');
                                calc();
                            }
                        }
                    }
                });
                __$coverCall('Plupload', '38585:38616');
                self.bind('QueueChanged', calc);
                __$coverCall('Plupload', '38622:39056');
                self.bind('Error', function (up, err) {
                    __$coverCall('Plupload', '38720:39049');
                    if (err.file) {
                        __$coverCall('Plupload', '38741:38774');
                        err.file.status = plupload.FAILED;
                        __$coverCall('Plupload', '38782:38800');
                        calcFile(err.file);
                        __$coverCall('Plupload', '38934:39043');
                        if (up.state == plupload.STARTED) {
                            __$coverCall('Plupload', '38976:39036');
                            delay(function () {
                                __$coverCall('Plupload', '39002:39023');
                                uploadNext.call(self);
                            }, 1);
                        }
                    }
                });
                __$coverCall('Plupload', '39062:39305');
                self.bind('FileUploaded', function () {
                    __$coverCall('Plupload', '39105:39111');
                    calc();
                    __$coverCall('Plupload', '39242:39298');
                    delay(function () {
                        __$coverCall('Plupload', '39266:39287');
                        uploadNext.call(self);
                    }, 1);
                });
                __$coverCall('Plupload', '39506:39553');
                self.trigger('Init', { runtime: this.runtime });
                __$coverCall('Plupload', '39559:39582');
                initControls.call(this);
            },
            refresh: function () {
                __$coverCall('Plupload', '39819:39873');
                if (fileInput) {
                    __$coverCall('Plupload', '39840:39868');
                    fileInput.trigger('Refresh');
                }
                __$coverCall('Plupload', '39878:39901');
                this.trigger('Refresh');
            },
            start: function () {
                __$coverCall('Plupload', '40011:40149');
                if (this.state != plupload.STARTED) {
                    __$coverCall('Plupload', '40053:40082');
                    this.state = plupload.STARTED;
                    __$coverCall('Plupload', '40088:40116');
                    this.trigger('StateChanged');
                    __$coverCall('Plupload', '40123:40144');
                    uploadNext.call(this);
                }
            },
            stop: function () {
                __$coverCall('Plupload', '40260:40404');
                if (this.state != plupload.STOPPED) {
                    __$coverCall('Plupload', '40302:40331');
                    this.state = plupload.STOPPED;
                    __$coverCall('Plupload', '40337:40365');
                    this.trigger('StateChanged');
                    __$coverCall('Plupload', '40371:40399');
                    this.trigger('CancelUpload');
                }
            },
            disableBrowse: function () {
                __$coverCall('Plupload', '40614:40669');
                disabled = arguments[0] !== undef ? arguments[0] : true;
                __$coverCall('Plupload', '40675:40728');
                if (fileInput) {
                    __$coverCall('Plupload', '40696:40723');
                    fileInput.disable(disabled);
                }
                __$coverCall('Plupload', '40734:40773');
                this.trigger('DisableBrowse', disabled);
            },
            getFile: function (id) {
                __$coverCall('Plupload', '41014:41019');
                var i;
                __$coverCall('Plupload', '41024:41127');
                for (i = files.length - 1; i >= 0; i--) {
                    __$coverCall('Plupload', '41070:41122');
                    if (files[i].id === id) {
                        __$coverCall('Plupload', '41101:41116');
                        return files[i];
                    }
                }
            },
            addFile: function (file, fileName) {
                __$coverCall('Plupload', '41650:41677');
                var files = [], ruid;
                __$coverCall('Plupload', '41678:41678');
                ;
                __$coverCall('Plupload', '41684:41821');
                function getRUID() {
                    __$coverCall('Plupload', '41709:41741');
                    var ctrl = fileDrop || fileInput;
                    __$coverCall('Plupload', '41747:41798');
                    if (ctrl) {
                        __$coverCall('Plupload', '41764:41792');
                        return ctrl.getRuntime().uid;
                    }
                    __$coverCall('Plupload', '41804:41816');
                    return false;
                }
                __$coverCall('Plupload', '41827:42820');
                function resolveFile(file) {
                    __$coverCall('Plupload', '41860:41885');
                    var type = o.typeOf(file);
                    __$coverCall('Plupload', '41892:42815');
                    if (file instanceof o.File) {
                        __$coverCall('Plupload', '41928:42070');
                        if (!file.ruid) {
                            __$coverCall('Plupload', '41952:42006');
                            if (!ruid) {
                                __$coverCall('Plupload', '41986:41998');
                                return false;
                            }
                            __$coverCall('Plupload', '42014:42030');
                            file.ruid = ruid;
                            __$coverCall('Plupload', '42038:42063');
                            file.connectRuntime(ruid);
                        }
                        __$coverCall('Plupload', '42077:42113');
                        resolveFile(new plupload.File(file));
                    } else if (file instanceof o.Blob) {
                        __$coverCall('Plupload', '42161:42190');
                        resolveFile(file.getSource());
                        __$coverCall('Plupload', '42197:42211');
                        file.destroy();
                    } else if (file instanceof plupload.File) {
                        __$coverCall('Plupload', '42299:42348');
                        if (fileName) {
                            __$coverCall('Plupload', '42321:42341');
                            file.name = fileName;
                        }
                        __$coverCall('Plupload', '42355:42371');
                        files.push(file);
                    } else if (o.inArray(type, [
                            'file',
                            'blob'
                        ]) !== -1) {
                        __$coverCall('Plupload', '42437:42472');
                        resolveFile(new o.File(null, file));
                    } else if (type === 'node' && o.typeOf(file.files) === 'filelist') {
                        __$coverCall('Plupload', '42602:42633');
                        o.each(file.files, resolveFile);
                    } else if (type === 'array') {
                        __$coverCall('Plupload', '42695:42710');
                        fileName = null;
                        __$coverCall('Plupload', '42784:42809');
                        o.each(file, resolveFile);
                    }
                }
                __$coverCall('Plupload', '42826:42842');
                ruid = getRUID();
                __$coverCall('Plupload', '42848:42865');
                resolveFile(file);
                __$coverCall('Plupload', '42917:42979');
                if (files.length) {
                    __$coverCall('Plupload', '42941:42974');
                    this.trigger('FilesAdded', files);
                }
            },
            removeFile: function (file) {
                __$coverCall('Plupload', '43160:43211');
                var id = typeof file === 'string' ? file : file.id;
                __$coverCall('Plupload', '43217:43336');
                for (var i = files.length - 1; i >= 0; i--) {
                    __$coverCall('Plupload', '43267:43331');
                    if (files[i].id === id) {
                        __$coverCall('Plupload', '43298:43325');
                        return this.splice(i, 1)[0];
                    }
                }
            },
            splice: function (start, length) {
                __$coverCall('Plupload', '43765:43862');
                var removed = files.splice(start === undef ? 0 : start, length === undef ? files.length : length);
                __$coverCall('Plupload', '43868:43905');
                this.trigger('FilesRemoved', removed);
                __$coverCall('Plupload', '43910:43938');
                this.trigger('QueueChanged');
                __$coverCall('Plupload', '43997:44062');
                plupload.each(removed, function (file) {
                    __$coverCall('Plupload', '44041:44055');
                    file.destroy();
                });
                __$coverCall('Plupload', '44068:44082');
                return removed;
            },
            trigger: function (name) {
                __$coverCall('Plupload', '44372:44418');
                var list = events[name.toLowerCase()], i, args;
                __$coverCall('Plupload', '44461:44814');
                if (list) {
                    __$coverCall('Plupload', '44517:44561');
                    args = Array.prototype.slice.call(arguments);
                    __$coverCall('Plupload', '44567:44581');
                    args[0] = this;
                    __$coverCall('Plupload', '44627:44809');
                    for (i = 0; i < list.length; i++) {
                        __$coverCall('Plupload', '44721:44803');
                        if (list[i].func.apply(list[i].scope, args) === false) {
                            __$coverCall('Plupload', '44784:44796');
                            return false;
                        }
                    }
                }
                __$coverCall('Plupload', '44820:44831');
                return true;
            },
            hasEventListener: function (name) {
                __$coverCall('Plupload', '45048:45083');
                return !!events[name.toLowerCase()];
            },
            bind: function (name, func, scope) {
                __$coverCall('Plupload', '45409:45417');
                var list;
                __$coverCall('Plupload', '45423:45448');
                name = name.toLowerCase();
                __$coverCall('Plupload', '45453:45478');
                list = events[name] || [];
                __$coverCall('Plupload', '45483:45530');
                list.push({
                    func: func,
                    scope: scope || this
                });
                __$coverCall('Plupload', '45535:45554');
                events[name] = list;
            },
            unbind: function (name) {
                __$coverCall('Plupload', '45786:45811');
                name = name.toLowerCase();
                __$coverCall('Plupload', '45817:45864');
                var list = events[name], i, func = arguments[1];
                __$coverCall('Plupload', '45870:46188');
                if (list) {
                    __$coverCall('Plupload', '45886:46078');
                    if (func !== undef) {
                        __$coverCall('Plupload', '45913:46043');
                        for (i = list.length - 1; i >= 0; i--) {
                            __$coverCall('Plupload', '45960:46036');
                            if (list[i].func === func) {
                                __$coverCall('Plupload', '45996:46013');
                                list.splice(i, 1);
                                __$coverCall('Plupload', '46023:46028');
                                break;
                            }
                        }
                    } else {
                        __$coverCall('Plupload', '46063:46072');
                        list = [];
                    }
                    __$coverCall('Plupload', '46133:46183');
                    if (!list.length) {
                        __$coverCall('Plupload', '46158:46177');
                        delete events[name];
                    }
                }
            },
            unbindAll: function () {
                __$coverCall('Plupload', '46300:46315');
                var self = this;
                __$coverCall('Plupload', '46321:46394');
                plupload.each(events, function (list, name) {
                    __$coverCall('Plupload', '46370:46387');
                    self.unbind(name);
                });
            },
            destroy: function () {
                __$coverCall('Plupload', '46525:46536');
                this.stop();
                __$coverCall('Plupload', '46564:46627');
                plupload.each(files, function (file) {
                    __$coverCall('Plupload', '46606:46620');
                    file.destroy();
                });
                __$coverCall('Plupload', '46632:46642');
                files = [];
                __$coverCall('Plupload', '46648:46715');
                if (fileInput) {
                    __$coverCall('Plupload', '46669:46688');
                    fileInput.destroy();
                    __$coverCall('Plupload', '46694:46710');
                    fileInput = null;
                }
                __$coverCall('Plupload', '46721:46785');
                if (fileDrop) {
                    __$coverCall('Plupload', '46741:46759');
                    fileDrop.destroy();
                    __$coverCall('Plupload', '46765:46780');
                    fileDrop = null;
                }
                __$coverCall('Plupload', '46791:46809');
                required_caps = {};
                __$coverCall('Plupload', '46814:46855');
                startTime = total = disabled = xhr = null;
                __$coverCall('Plupload', '46861:46884');
                this.trigger('Destroy');
                __$coverCall('Plupload', '46927:46943');
                this.unbindAll();
                __$coverCall('Plupload', '46948:46959');
                events = {};
            }
        });
    };
    __$coverCall('Plupload', '47200:49369');
    plupload.File = function () {
        __$coverCall('Plupload', '47231:47248');
        var filepool = {};
        __$coverCall('Plupload', '47252:49340');
        function PluploadFile(file) {
            __$coverCall('Plupload', '47285:49308');
            plupload.extend(this, {
                id: plupload.guid(),
                name: file.name || file.fileName,
                type: file.type || '',
                size: file.size || file.fileSize,
                origSize: file.size || file.fileSize,
                loaded: 0,
                percent: 0,
                status: 0,
                getNative: function () {
                    __$coverCall('Plupload', '48685:48724');
                    var file = this.getSource().getSource();
                    __$coverCall('Plupload', '48730:48801');
                    return o.inArray(o.typeOf(file), [
                        'blob',
                        'file'
                    ]) !== -1 ? file : null;
                },
                getSource: function () {
                    __$coverCall('Plupload', '49004:49052');
                    if (!filepool[this.id]) {
                        __$coverCall('Plupload', '49035:49046');
                        return null;
                    }
                    __$coverCall('Plupload', '49058:49082');
                    return filepool[this.id];
                },
                destroy: function () {
                    __$coverCall('Plupload', '49199:49225');
                    var src = this.getSource();
                    __$coverCall('Plupload', '49231:49297');
                    if (src) {
                        __$coverCall('Plupload', '49247:49260');
                        src.destroy();
                        __$coverCall('Plupload', '49267:49291');
                        delete filepool[this.id];
                    }
                }
            });
            __$coverCall('Plupload', '49313:49337');
            filepool[this.id] = file;
        }
        __$coverCall('Plupload', '49344:49363');
        return PluploadFile;
    }();
    __$coverCall('Plupload', '49457:50542');
    plupload.QueueProgress = function () {
        __$coverCall('Plupload', '49496:49511');
        var self = this;
        __$coverCall('Plupload', '49657:49670');
        self.size = 0;
        __$coverCall('Plupload', '49752:49767');
        self.loaded = 0;
        __$coverCall('Plupload', '49855:49872');
        self.uploaded = 0;
        __$coverCall('Plupload', '49966:49981');
        self.failed = 0;
        __$coverCall('Plupload', '50077:50092');
        self.queued = 0;
        __$coverCall('Plupload', '50190:50206');
        self.percent = 0;
        __$coverCall('Plupload', '50298:50318');
        self.bytesPerSec = 0;
        __$coverCall('Plupload', '50402:50539');
        self.reset = function () {
            __$coverCall('Plupload', '50430:50535');
            self.size = self.loaded = self.uploaded = self.failed = self.queued = self.percent = self.bytesPerSec = 0;
        };
    };
    __$coverCall('Plupload', '50545:50571');
    window.plupload = plupload;
}(window, mOxie));