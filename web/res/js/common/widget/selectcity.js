(typeof(widget) !=='object' ) && (widget = {});
/*选择城市*/
widget.selectcity = function( dom , level , proviceid , cityid , areaid , callback ){
	if( undefined === $ )
	{
		window.alert('请加载jquery');
		return false;
	}
	if( typeof(DOMAIN) !== 'object' || undefined === DOMAIN.FRONT_DOMAIN )
	{
		window.alert('请先定义DOMAIN.FRONT_DOMAIN');
		return false;
	}
	var div = dom;
	proviceid = proviceid || dom.attr('data-pid');
	cityid = cityid || dom.attr('data-cid');
	areaid = areaid || dom.attr('data-did');

	var prename = ((undefined === dom.attr('name')) ? 'region' : dom.attr('name'));

	$.getJSON( '/__all__/widget/selectcity?callback=?' , function(data){
		var dom = [];
		var currentVal = { p: 0 , c: 0 , a : 0 };
		for(var l = 1 ; l <= level ; l++)
		{
			dom[l] = $('<select class="select_'+l+'" name="'+prename+'['+l+']"><option value="">-请选择-</option></select>');
		}

		for(var i in dom )
		{
			div.before(dom[i]);
		}
		div.remove();

		var html = '<option value="">-请选择-</option>';
		var dataarr = [];
		for (var i in data)
		{
      dataarr.push(data[i]);
		}
    dataarr.sort(sortBy('sort',false));
		for(var i in dataarr )
		{
			var data1 = dataarr[i];
			html += '<option value="'+data1['region_id']+'">'+data1['region_name']+'</option>';
		}
		dom[1].html(html);

		dom[1].change(function(){
			currentVal.c = currentVal.a = 0;
			var val = $(this).val();
			var html1 = html = '<option value="">-请选择-</option>';
			if( data[val] != undefined && data[val]['child'] != undefined )
			{
				if( $.Object.count.call(data[val]['child'])==1 )
				{
					html = '';
				}
				for(var i in data[val]['child'] )
				{
					var data1 = data[val]['child'][i];
					html += '<option value="'+data1['region_id']+'" data-parent="'+val+'">'+data1['region_name']+'</option>';
				}
			}
			dom[2] && dom[2].html(html);
			dom[3] && dom[3].html(html1);

			if($.isFunction(jQuery.fn.selectBox)) {
				dom[2].selectBox('refresh');
				dom[3].selectBox('refresh');
			}
			currentVal.p = val;
			if (val == 0){
				currentVal.c = currentVal.a = 0;
			}
			if( $.Object.count.call(data[val]['child'])==1 && dom[2] )
			{
				dom[2].trigger('change');
			}
			else
			{
				if(data[val]['child']==undefined)
					callback && callback( currentVal.p , currentVal.c , currentVal.a , true );
				else
					callback && callback( currentVal.p , currentVal.c , currentVal.a , false );
			}
		});
		if( dom[2] )
		{
			dom[2].change(function(){
				currentVal.a = 0;
				var val = $(this).val();
				var parentval = $(this).find('option').filter(':selected').attr('data-parent');
				var html = '<option value="">-请选择-</option>';
				if( parentval && data[parentval]['child'][val] != undefined && data[parentval]['child'][val]['child'] != undefined )
				{
					if( $.Object.count.call(data[parentval]['child'][val]['child'])==1 )
					{
						html = '';
					}
					for(var i in data[parentval]['child'][val]['child'] )
					{
						var data1 = data[parentval]['child'][val]['child'][i];
						html += '<option value="'+data1['region_id']+'">'+data1['region_name']+'</option>';
					}
				}
				dom[3] && dom[3].html(html);
				if($.isFunction(jQuery.fn.selectBox)) {
					dom[3].selectBox('refresh');
				}
				currentVal.c = val;
				if (val == 0){
					currentVal.a = 0;
				}
				if( parentval && data[parentval]['child'][val] != undefined && $.Object.count.call(data[parentval]['child'][val]['child'])==1 && dom[3] )
				{
					dom[3].trigger('change');
				}
				else
				{
					if( parentval && data[parentval]['child'][val] != undefined && data[parentval]['child'][val]['child'] == undefined )
						callback && callback( currentVal.p , currentVal.c , currentVal.a , true );
					else
						callback && callback( currentVal.p , currentVal.c , currentVal.a , false );
				}
			});
		}
		if( dom[3] )
		{
			dom[3].on('change' , function(){
				var val = $(this).val();
				currentVal.a = val;
				callback && callback( currentVal.p , currentVal.c , currentVal.a ,true );
			});
		}
		//初始化时候 		//默认的赋值时候不激活起change
		var callback_bak = callback;
		callback = undefined;
		if( proviceid && dom[1] )
		{
			dom[1].val(proviceid);
			dom[1].trigger('change')
		}
		if( cityid && dom[2] )
		{
			dom[2].val(cityid);
			dom[2].trigger('change')
		}
		if( areaid && dom[3] )
		{
			dom[3].val(areaid);
			dom[3].trigger('change');
		}
		callback = callback_bak;
		if($.isFunction(jQuery.fn.selectBox)) {
			dom[1].selectBox();
			dom[1].selectBox().change(function() {
				proviceid = $(this).val();
				dom[1].val(proviceid);
			});
			dom[2].selectBox();
			dom[2].selectBox().change(function() {
				cityid = $(this).val();
				dom[2].val(cityid);
			});
			dom[3].selectBox();
			dom[3].selectBox().change(function() {
				areaid = $(this).val();
				dom[3].val(areaid);
			});
		}
	});

};