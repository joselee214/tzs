/*
 * a copycat inputzoom effect plugin for jquery, original from alipay
 * Ver: 0.1
 *
 * Usege: $(this).inpurZoom();
 * Target element must have "inputZoom" (example: "### #### ####") for display pattern or not be bind their events
 *
 * Author: CST <cst4you@hotmail.com>
*/

(function($) {
	$.fn.inputZoom = function() {
		var options = [];

		init();

		return this.each(function() {  
            bind($(this));  
        });  


		/* 创建用于放大的元素 */
		function init($target) {
			options.element = $('<div class="ui-inputzoom"><div class="ui-inputzoom-ctn">49264390234707243092347027349</div></div>');
			options.element.appendTo("body");
		}

		/* 绑定事件 */
		function bind($target) {
			if($target.attr('inputZoom')) {
				$target.bind("focus", function(){
					change($target);
					show($target);
				}).bind("blur", function(){
					change($target);
					hide($target);
				}).bind("keyup", function(){
					change($target);
				});
			}
		}

		/* 定位并且显示放大的元素 */
		function show($target) {
			var offset 		= $target.offset();
			var input_value	= $target.val();
			if (input_value.length >=1) {
				options.element.css("top", offset.top- 33).css("left", offset.left);
				options.element.show();
			}
		}

		/* 隐藏放大的元素 */
		function hide($target) {
			options.element.hide();
		}

		/* 数据处理 */
		function change($target) {
			var input_value		= $target.val();
			var input_pattern	= $target.attr('inputZoom');
			var input_format	= '';
			var input_charat	= 0;

			if (input_value.length >=1) {
				for (var i = 0; i < input_pattern.length; i++) {
					if (input_pattern.charAt(i) == "#") {
						input_format = input_format + input_value.charAt(input_charat);
						input_charat++;
					} else if (input_pattern.charAt(i) == " "){
						input_format = input_format + " ";
					}
				}

				show($target);
			} else {
				hide($target);
			}

			options.element.find($('.ui-inputzoom-ctn')).html(input_format);
		}
	}
})(jQuery);