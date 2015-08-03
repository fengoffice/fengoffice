var ogSearchSelector = {};

//get item template copy
ogSearchSelector.getItemTemplateCopy = function (container_id , template_id, data){
	var copy = $("#"+container_id+" > #searchSelectorItemTemplate"+template_id).clone();
		
	//load data to the copy
	ogSearchSelector.loadDataToItemTemplateCopy(copy, data);
	
	copy.removeClass("item-template");
		
	return copy;	
}

//get show more item
ogSearchSelector.getShowMoreItem = function (container_id , template_id){
	var copy = $("#"+container_id+" > #showMoreTemplate"+template_id).clone();
	
	copy.removeClass("item-template");
		
	return copy;	
}

//load data to item template copy
ogSearchSelector.loadDataToItemTemplateCopy = function (copy , data){	
	//for each param in data search by id(the id is the param name) on the copy and add the param value as a class
	for (var param in data) {
		switch (param) {
			case "style":
		    	for (var style in data[param]) {
		    		if(copy.attr('id') == style){
		    			copy.addClass(data[param][style]);
		    			copy.removeAttr("id");
		    		}else{
		    			copy.find("#"+style).addClass(data[param][style]);	
		    			copy.find("#"+style).removeAttr("id");
		    		}
				}		    	
		        break;
		    default:
		    	//default append content 
		    	copy.find("."+param).append(data[param]);
		} 
	}
		
}

ogSearchSelector.resetLimit = function (container_id, limit){
	if(typeof limit=="undefined"){
		var first_limit = $("#"+container_id+"-input-first-limit").val();
		$("#"+container_id+"-input-limit").val(first_limit);
	}else{
		$("#"+container_id+"-input-limit").val(limit);
	}	
}

//Jquery autocomplete
ogSearchSelector.init = function (genid, container_id, extra_param, search_func, select_function, search_minLength, search_delay){
	$("#"+container_id+"-input").autocomplete({
			position: { my : "right top", at: "right bottom" },			
			search: function(){
								$(this).autocomplete("widget").show();
								$(this).autocomplete("widget").empty();
								$(this).autocomplete("widget").prepend( "<li id='ui_menu_item_loading_indicator' class='ui-menu-item loading-indicator'></li>" );								
							},
			response: function(){
									$(this).autocomplete("widget").remove( "#ui_menu_item_loading_indicator" );
								},
			delay: search_delay,
	   		minLength: search_minLength,
	   		source: function(request, callback){
	            var searchParam  = request.term;
	            search_func(genid, container_id, extra_param, searchParam, callback);
	        },
	   		focus: function( event, ui ) {
	   			if(typeof(ui.item)!= "undefined"){
	   				if(ui.item.label != "advanced"){
	   					$("#"+container_id+"-input").val( ui.item.label );
	   				}
	   			}	   			
	   			return false;
	   		},
	   		select: function( event, ui ) {
	   			$("#"+container_id+"-input").val( ui.item.label ); 
	   			select_function(genid, container_id, extra_param, ui.item);
	   			return false;
	   		}
	})
	.autocomplete( "instance" )._renderItem = function( ul, item ) {
			return $( "<li>" )
	   		.append( item.desc )
	   		.appendTo( ul );
	};
	
	$("#"+container_id+"-input").on( "click", function() {
		//fire the search
		$( this ).keydown();
	});
	
	$("#"+container_id+"-ico-search-m").on( "click", function() {
		//fire the search		
		$( this ).siblings( ".search-input" ).keydown();
	});
}

/*
 * Custom functions
 * define here your own search function for the search selector
 * define here your own onclick function for each item on the result list on the search selector
 **/

//Member search
ogSearchSelector.searchMember = function (genid, container_id, dimension_id, search_text, callback){
	var template_id = 1;
	var result_limit = parseInt($("#"+container_id+"-input-limit").val());	
	var callback_func = function (data){
		if (typeof data.members != "undefined") {
			var items = [];
			for (var prop in data.members) {  
				var mem = data.members[prop];
				var dim_id = data.dimension_id;
								
				var searchResultInfo = mem.name;
				
				//add breadcrumb
				if(mem.parent > 0){
					var mpath_aux = {};
					mpath_aux[dim_id] = {};
					mpath_aux[dim_id][mem.id] = mem;
					text = og.getEmptyCrumbHtml(mpath_aux,".search-selector-result",null,false);//searchResultInfo
					
					searchResultInfo += "<br>" + text;
				}
				
				//item row height
				rowHeight = data.row_class;
								
				//build the item
				var result = {
								searchResultInfo: searchResultInfo
							};
				
				
				result["style"] = {
								searchSelectorItemTemplate1 : rowHeight,
								searchResultImgTemplate1 : mem.iconCls								
							};			
				
				var item_desc = ogSearchSelector.getItemTemplateCopy(container_id, template_id, result);
				
				var item =  {
					 value: mem.id,
					 label: mem.name,
					 desc: item_desc	
					 };	
				items.push(item);				
			}
			
			//show more
			if(data.show_more){
				var last_item =  {
						 value: 'more',
						 label: search_text,
						 limit: items.length + 5,
						 desc: ogSearchSelector.getShowMoreItem(container_id, template_id)	
						 };	
				
				items.push(last_item);
			}
			
			callback(items);
			og.eventManager.fireEvent('replace all empty breadcrumb', null);
		}else{
			callback();
		}		
	}

	var search_params = {};	
	search_params.text = search_text;
	search_params.start = 0;
	search_params.limit = result_limit;
	search_params.order = 'name';
	search_params.parents = 0;
	
	if(search_text.length == 0){
		search_params.random = 1;
	}
	
	if (member_selector[genid].properties[dimension_id].allowedMemberTypes) {
		search_params.allowed_member_types = member_selector[genid].properties[dimension_id].allowedMemberTypes
	}
		
	og.searchMemberOnServer(dimension_id, search_params, callback_func);
	
	ogSearchSelector.resetLimit(container_id);
}
//On member select
ogSearchSelector.onItemMemberSelect = function (genid, container_id, dimension_id, item){
	var member_id = item.value;
	if(member_id != "more"){
		member_selector.add_relation(dimension_id, genid, member_id);		
	}else if (member_id == "more"){
		$("#"+container_id+"-input").val(item.label);
		
		//increase the limit
		ogSearchSelector.resetLimit(container_id, item.limit);
		
		//fire the search
		$("#"+container_id+"-input").keydown();
	}	
}
//END Member




// Permission group search (users and groups)
ogSearchSelector.searchPermissionGroup = function (genid, container_id, ignored, search_text, callback){
	var template_id = 1;
	var result_limit = parseInt($("#"+container_id+"-input-limit").val());	
	var callback_func = function (data){
		
		if (typeof data.permission_groups != "undefined") {
			var items = [];
			for (var prop in data.permission_groups) {  
				var pg = data.permission_groups[prop];
				
				if (typeof pg == 'function') continue;
				
				var img_link = '';
				if (pg.picture_url && pg.type == 'user') {
					img_link = '<div class="selector-user-img-container"><img src="'+ pg.picture_url +'" /></div>';
				} else if (pg.type == 'group') {
					img_link = '<div class="selector-user-img-container ico-large-group" style="width:48px;height:48px;">&nbsp;</div>';
				}
				
				name_html = '<div class="selector-user-name-container">' + pg.name;
				if(pg.company_name){
					name_html += "<br><span class='desc'>" + pg.company_name + "</span>";
				}
				name_html += '</div><div class="clear"></div>';
				
				var searchResultInfo = img_link + name_html;
				
				
				
				//item row height
				rowHeight = data.row_class;
								
				//build the item
				var result = {
					searchResultInfo: searchResultInfo
				};
				
				result["style"] = {
					searchSelectorItemTemplate1 : rowHeight,
					searchResultImgTemplate1 : pg.iconCls								
				};			
				
				var item_desc = ogSearchSelector.getItemTemplateCopy(container_id, template_id, result);
				
				var item = {
					value: pg.id,
					label: pg.name,
					desc: item_desc
				};
				for (x in pg) {
					item[x] = pg[x];
				}
				items.push(item);				
			}
			
			//show more
			if(data.show_more){
				var last_item =  {
					value: 'more',
					label: search_text,
					limit: items.length + 5,
					desc: ogSearchSelector.getShowMoreItem(container_id, template_id)	
				};	
				
				items.push(last_item);
			}
			
			callback(items);
		}		
	}

	var search_params = {};	
	search_params.text = search_text;
	search_params.start = 0;
	search_params.limit = result_limit;
	search_params.order = 'name';
	search_params.parents = 0;
		
	og.searchPermissionGroupOnServer(search_params, callback_func);
	
	ogSearchSelector.resetLimit(container_id);
}

//On user or group select (member permissions component)
ogSearchSelector.onItemPermissionGroupSelect = function (genid, container_id, ignored, item){
	
	if(item.value != "more"){
		// draw list item in permissions component
		og.userPermissions.drawUserListItem(genid, item);
		
		// reset input value
		$("#"+container_id+"-input").val('');
		
	} else if (item.value == "more") {
		if(item.label == "more"){
			item.label = "";
		}
		$("#"+container_id+"-input").val(item.label);
		
		//increase the limit
		ogSearchSelector.resetLimit(container_id, item.limit);
		
		//fire the search
		$("#"+container_id+"-input").keydown();
	}	
}

/*
 * This function search permission groups (users and groups) on the server. 
 * @param search_params objetc (text, start, limit) only text is required
 * @param func_callback function to execute after search on the server (recive 1 param the data from the server)
 */
og.searchPermissionGroupOnServer = function(search_params, func_callback){
	var params = {};
	params.query = Ext.escapeRe(search_params.text.toLowerCase());
	
	if(typeof search_params.limit != 'undefined'){
		params.limit = search_params.limit;		
	}
	
	if(typeof search_params.order != 'undefined'){
		params.order = search_params.order;		
	}
	
	if(typeof search_params.start != 'undefined'){
		params.start = search_params.start;
	}
	
	if(typeof search_params.parents != 'undefined'){
		params.parents = search_params.parents;
	}
	
	og.openLink(og.getUrl('group', 'search_permission_group', params), {
		hideLoading:true, 
		hideErrors:true,
		callback: function(success, data){
			if (typeof func_callback != "undefined") {
				func_callback(data);
			}
		}
	});
}
// END Permission groups

//General search (header)
ogSearchSelector.generalSearch = function (genid, container_id, dimension_id, search_text, callback){
	var template_id = 2;
	var result_limit = parseInt($("#"+container_id+"-input-limit").val());	
	var callback_func = function (data){
		var items = [];
		if (typeof data.search_results != "undefined") {			
			for (var prop in data.search_results) {  
				if(isNaN(parseInt(prop))){					
					continue;
				}				
				var obj = data.search_results[prop];
				var dim_id = data.dimension_id;
								
				var searchResultInfo = obj.text_match;
				if(obj.name == obj.text_match){
					searchResultInfo = "";
				}
				
				//item row height
				rowHeight = data.row_class;
								
				//build the item
				var result = {
								searchResultName: obj.name,
								searchResultInfo: searchResultInfo
							};
								
				result["style"] = {
								searchSelectorItemTemplate2 : rowHeight,
								searchResultImgTemplate2 : obj.iconCls								
							};			
				
				var item_desc = ogSearchSelector.getItemTemplateCopy(container_id, template_id, result);
				
				var item =  {
					 value: obj.id,
					 label: search_text,
					 desc: item_desc	
					 };	
				items.push(item);				
			}
			
			//show more
			if(data.show_more){
				var last_item =  {
						 value: 'more',
						 label: search_text,
						 limit: items.length + 5,
						 desc: ogSearchSelector.getShowMoreItem(container_id, template_id)	
						 };	
				
				items.push(last_item);
			}
			
			
		}
		
		//advanced search
		var last_item =  {
				 value: 'advanced',
				 label: "",
				 desc: ogSearchSelector.getAdvancedSearchItem(container_id, template_id)
				 };	
			
		items.push(last_item);
		
		
		callback(items);
	}

	var search_params = {};	
	search_params.query = search_text;
	search_params.start = 0;
	search_params.limit = result_limit;
	search_params.order = 'name';
			
	og.openLink(og.getUrl('search', 'general_search', search_params), {
		hideLoading:true, 
		hideErrors:true,
		callback: function(success, data){
			if (typeof callback_func != "undefined") {
				callback_func(data);
			}
		}
	});
	
	ogSearchSelector.resetLimit(container_id);
}

//get advanced serch item
ogSearchSelector.getAdvancedSearchItem = function (container_id , template_id){
	var copy = $("#"+container_id+" > #advancedSearchTemplate"+template_id).clone();
	
	copy.removeClass("item-template");
		
	return copy;	
}

ogSearchSelector.onGeneralSearchResultSelect = function (genid, container_id, ignored, item){
	
	switch (item.value) {
    case "more":
    	//$("#"+container_id+"-input").val(item.label);
    	$("#"+container_id+"-input").val('');
		//increase the limit
		//ogSearchSelector.resetLimit(container_id, item.limit);
		
		//fire the search
		//$("#"+container_id+"-input").keydown();
    	var search_params = {};
    	search_params.search_for = item.label;    	
    	search_params.current = 'search';
    	og.openLink(og.getUrl('search', 'search', search_params));
        break; 
    case "advanced":
    	$("#"+container_id+"-input").val('');
    	var search_params = {};	
    	search_params.advanced = true;
    	search_params.current = 'search';		
    	og.openLink(og.getUrl('search', 'search', search_params), {
    		hideLoading:true, 
    		hideErrors:true		
    	});	
        break; 
    default: 
    	$("#"+container_id+"-input").val('');
		// redirect to object
		og.openLink(og.getUrl('object', 'view', {id:item.value}));
}
}

//END General search 
