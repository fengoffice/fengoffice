og.emptyBreadcrumbsToRefresh = new Array();

og.eventManager.addListener('replace all empty breadcrumb',function(){
	//before insert breadcrumbs we need to have all members that we need for breadcrumbs in og.dimensions
	//CALLBACK
	var callback = function(emptyBreadcrumbs){		
		og.replaceAllEmptyBreadcrumbForThisMemberInterval(emptyBreadcrumbs);		
	}
	
	var copy = og.emptyBreadcrumbsToRefresh.slice(0);
	var members = og.getMembersFromServer(og.emptyBreadcrumbsToRefresh,callback,copy);

	//empty the array after refresh
	og.emptyBreadcrumbsToRefresh.length = 0;
});

og.replaceAllEmptyBreadcrumbForThisMemberInterval = function(emptyBreadcrumbs){
	var curNewsIndex = -1;

	function advanceNewsItem() {
	    ++curNewsIndex;
	    if (curNewsIndex >= emptyBreadcrumbs.length) {
	    	 clearInterval(intervalID);
	    }
	   	  
	    var id = emptyBreadcrumbs[curNewsIndex];
		var members = og.getMemberFromOgDimensions(id, true);
		
		if (members.length > 0){
			var member = members[0];			
			og.replaceAllEmptyBreadcrumbForThisMember (0,member);
		}
		
		//init popover btns that are not initialized yet
		var btns = $(".breadcrumbBtn.btnPopoverNotInitialized").toArray();
		og.initBreadcrumbsBtns(btns);
		
	}

	var intervalID = setInterval(advanceNewsItem, 100);	
}


/*
 * dims array all dimensions with members for this breadcrumb
 * draw_all_members bool if true draw all members (breadcrumb_member_count preference ignored)
 * skipped_dimensions array with all dimensions to skip in this breadcrumb
 * show_archived 
 * fixed_mem_len int this int indicate the max length for the name for each member
 * 
 * return html 
 * */
og.getRealCrumbHtml = function(dims, draw_all_members, skipped_dimensions, show_archived, fixed_mem_len, show_link) {
	var html = '';
	var dim_index = 0;
	var max_members_per_dim = og.preferences['breadcrumb_member_count'];
	for (x in dims) {
		if (isNaN(x)) continue;
		
		var skip_this_dimension = false;
		if (skipped_dimensions) {
			for (sd in skipped_dimensions) {
				if (skipped_dimensions[sd] == x) {
					skip_this_dimension = true;
					break;
				}
			}
		}
		if (skip_this_dimension) continue;
		
		var members = dims[x];
		var inner_html = "";
		var title = "";
		var total_texts = 0;
		var all_texts = [];
		
		for (id in members) {
			id = parseInt(id);
			if (isNaN(id)) continue;
			
			var m = members[id];
			
			var texts = og.getMemberTextsFromOgDimensions(id, true);
			
			if (texts.length == 0){				
				texts.push({id:id, text:m.name, ot:m.ot, c:m.c});
			}
			total_texts += texts.length;
				
			all_texts[id] = texts;			
		}
		
		if (fixed_mem_len && !isNaN(fixed_mem_len)) {
			max_len = fixed_mem_len;
		} else {
			if (total_texts == 1) max_len = 13
			else if (total_texts < 3) max_len = 9;
			else if (total_texts < 5) max_len = 5;
			else max_len = 4;
		}
		
		breadcrumb_count = 0;
		for (id in members) {
			if (isNaN(id)) continue;
			texts = all_texts[id];
			
			if (texts.length > 0) {
				breadcrumb_count++;
			}
			if (!draw_all_members && breadcrumb_count > max_members_per_dim) break;
			
			if (title != "" && breadcrumb_count <= max_members_per_dim) title += '- ';
			var color = members[id]['c'];
			var member_path_span = '<span class="bread-crumb-'+ id +' member-path real-breadcrumb og-wsname-color-'+ color +'">';
			var member_path_content = "";
			
			for (i=texts.length-1; i>=0; i--) {
				var text = texts[i].text;
				text = text.replace("&amp;","&");
				if (i>0) {
					str = text.length > max_len ? text.substring(0, max_len-3) + ".." : text;
				} else {
					str = text.length > 12 ? text.substring(0, 10) + ".." : text;
				}
				if (breadcrumb_count <= max_members_per_dim) {
					title += texts[i].text + (i>0 ? "/" : " ");
				}
				
				var onclick = "return false;";
				if (og.additional_on_dimension_object_click[texts[i].ot]) {
					onclick = og.additional_on_dimension_object_click[texts[i].ot].replace('<parameters>', texts[i].id);
				}   
				
				if(show_link){
					var link = '<a href="#" onclick="' + onclick + '">' + str + '</a>';
				}else{
					var link = str;
				}
				
				
				member_path_content += link;
				if (i>0) member_path_content += " > ";
			}
			member_path_span += member_path_content + '</span>';
			
			if (member_path_content != '') inner_html += member_path_span;
		}
		
		if (members['total'] > max_members_per_dim) {
			title += lang('and number more', (members['total'] - max_members_per_dim));
		}
		
		if (inner_html != "") html += '<span class="member-path" title="'+title+'">' + inner_html + '</span>';
		dim_index++;
	}
		
	return html;
}

/*
 * This function return all the breadcrumbs for a set of members
 * @dims array all dimensions with members for this breadcrumb
 * @draw_all_members bool if true draw all members (breadcrumb_member_count preference ignored)
 * @skipped_dimensions array with all dimensions to skip in this breadcrumb
 * @show_archived 
 * @fixed_mem_len int this int indicate the max length for the name for each member
 * 
 * return html 
 * */
og.getCrumbHtml = function(dims, draw_all_members, skipped_dimensions, show_archived, fixed_mem_len, show_link) {
	var all_bread_crumbs = "";
	
	if (typeof show_link == "undefined") {
		show_link = true;
	}
	
	for (x in dims) {
		if (isNaN(x)) continue;
		var dim = {};
		var empty_bread_crumbs = "";
		var members = dims[x];
		
		for (id in members) {
			if (isNaN(id)) continue;
			
			var members = og.getMemberFromOgDimensions(id, false);
			
			if (members.length > 0){
				var member = members[0];
				
				if (typeof dim[member.dimension_id] == "undefined") {
					dim[member.dimension_id] = {};
				}
				
				member_info ={
				 			"id":member.id,
				 			"ot":member.object_type_id,
				 			"c":member.color,
				 			"name":member.name
				};
				dim[member.dimension_id][member.id] = member_info;
			}else{
				//return a target to reload on the callback after get the member from the server
				empty_bread_crumbs += '<span class="member-path"><span class="bread-crumb-'+ id +' member-path"></span></span>';
			}
		}
		all_bread_crumbs += og.getRealCrumbHtml(dim, draw_all_members, skipped_dimensions, show_archived, fixed_mem_len, show_link);
		all_bread_crumbs += empty_bread_crumbs;
	}
	
	return all_bread_crumbs;
}

og.getCrumbHtmlWithoutLinksMemPath = function(dims, draw_all_members, skipped_dimensions, show_archived, fixed_mem_len , total_length, genid) {
	var html = '';
	var dim_index = 0;
	var max_members_per_dim = og.preferences['breadcrumb_member_count'];
	for (x in dims) {
		if (isNaN(x)) continue;
		
		var skip_this_dimension = false;
		if (skipped_dimensions) {
			for (sd in skipped_dimensions) {
				if (skipped_dimensions[sd] == x) {
					skip_this_dimension = true;
					break;
				}
			}
		}
		if (skip_this_dimension) continue;
		
		var members = dims[x];
		var inner_html = "";
		var title = "";
		var total_texts = 0;
		var all_texts = [];
		var total_text_length = 0;
		var total_texts_in_Crumb = 0;
		var important_member_name = "";
		
		for (id in members) {
			id = parseInt(id);
			if (isNaN(id)) continue;
			var m = members[id];
			if (!m.archived) {
				var callback_extra_params = {genid:genid}; 
				var texts = og.getMemberTextsFromOgDimensions(id, true, og.replaceCrumbHtmlWithoutLinks, callback_extra_params);				
			} else {
				var texts = [];
				texts.push({id:m.id, text:m.name, ot:m.ot, c:m.c});
			}
			if (texts.length == 0 && show_archived){
				texts.push({id:id, text:m.name, ot:m.ot, c:m.c});
			}
			total_texts += texts.length;
			
			all_texts[id] = texts;
			
			if(total_length && !isNaN(total_length)){
				for (x in texts) {
					total_text_length += texts[x].length;
				    total_texts_in_Crumb++;
				}
			}
		}
		
		if (fixed_mem_len && !isNaN(fixed_mem_len)) {
			max_len = fixed_mem_len;
		} else {
			if (total_texts == 1) max_len = 13
			else if (total_texts < 3) max_len = 9;
			else if (total_texts < 5) max_len = 5;
			else max_len = 4;
			
			if(total_length && !isNaN(total_length)){
				max_len = Math.floor(total_length/total_texts_in_Crumb);
			}
		}
		
		
		breadcrumb_count = 0;
		for (id in members) {
			if (isNaN(id)) continue;
			texts = all_texts[id];
			
			if (texts.length > 0) {
				breadcrumb_count++;
			}
			if (!draw_all_members && breadcrumb_count > max_members_per_dim) break;
			
			if (title != "" && breadcrumb_count <= max_members_per_dim) title += '- ';
			var color = members[id]['c'];
			var member_path_span = '<span class="member-path og-wsname-color-'+ color +'">';
			var member_path_content = "";
			
			for (i=texts.length-1; i>=0; i--) {
				var text = texts[i].text;
				text = text.replace("&amp;","&");
				if (i>0) {
					str = text.length > max_len ? text.substring(0, max_len-3) + ".." : text;
				} else {
					min_len = max_len < 12 ?  10 : max_len-3;					
					str = (text.length > 12 && text.length > max_len) ? text.substring(0, min_len) + ".." : text;
					important_member_name = text.substring(0, total_length);
				}
				if (breadcrumb_count <= max_members_per_dim) {
					title += texts[i].text + (i>0 ? "/" : " ");
				}
				
				var onclick = "return false;";
				if (og.additional_on_dimension_object_click[texts[i].ot]) {
					onclick = og.additional_on_dimension_object_click[texts[i].ot].replace('<parameters>', texts[i].id);
				}                                
				
				member_path_content += str;
				
				if (i>0) member_path_content += "/";
			}
						
			if(member_path_content.length > total_length && max_len <= 3){
				member_path_content = ".../"+important_member_name;
			}
			member_path_span += member_path_content + '</span>';
			
			if (member_path_content != '') inner_html += member_path_span;
		}
		
		if (members['total'] > max_members_per_dim) {
			title += lang('and number more', (members['total'] - max_members_per_dim));
		}
		
		if (inner_html != "") html += '<span class="member-path" title="'+title+'">' + inner_html + '</span>';
		dim_index++;
	}
	
	return html;
}

//return the member bredcrumb without links. Length of bredcrumb is calculate from completePath contenedor
og.getCrumbHtmlWithoutLinks = function (member_id, dimension_id, genid) {
	member_id = parseInt(member_id);
	if (isNaN(member_id)) return false;
	dimension_id = parseInt(dimension_id);
	if (isNaN(dimension_id)) return false;
	
	//calculate bredcrumb width
	width = $("#"+genid+"selected-member"+member_id+" .completePath").width();
	if(width == null || width == 0){
		width = 240;
	}
	
	var callback_extra_params = {genid:genid}; 
	var texts = og.getMemberTextsFromOgDimensions(member_id, false, og.replaceCrumbHtmlWithoutLinks, callback_extra_params);
	
	bredcrumb_total_length = width / 7;
	
	if(texts.length > 0){
		var member = {};
		member[member_id] = texts[0];
		var member_path = {};
		member_path[dimension_id] = member;
		mem_path = og.getCrumbHtmlWithoutLinksMemPath(member_path, false, null,false,null,bredcrumb_total_length,genid);
		
		return mem_path;
	}else{
		return false;
	}
}

//this function is used as a callback if a member is not in og.dimensions
og.replaceCrumbHtmlWithoutLinks = function(dimension_id ,member, extra_params) {
	//replace breadcrumb for this member
	var html = og.getCrumbHtmlWithoutLinks(member.id,dimension_id,extra_params.genid);	
	$("#"+ extra_params.genid +"selected-member"+ member.id +" > .completePath").replaceWith(html);
}

og.replaceAllEmptyBreadcrumbForThisMember = function(dimension_id ,member, extra_params) {
	//replace all breadcrumb for this member
	var targets = ".empty-bread-crumb.bread-crumb-"+member.id;
	var all_targets = $(targets);
	
	for (var j = 0; j < all_targets.length; j++) {
		var new_target_id = 'bread-crumb-'+ Ext.id() + member.id;
		var container_to_fill = $(all_targets[j]).data("container-to-fill");
		var show_link = $(all_targets[j]).data("show-link");
		$(all_targets[j]).parent().html('<span id="'+new_target_id+'" class="bread-crumb-'+ member.id +' member-path real-breadcrumb og-wsname-color-'+ member.color +'" data-container-to-fill="'+container_to_fill+'" data-show-link="'+show_link+'"></span>');
		
		og.insertBreadcrumb(member.id,new_target_id,false);
	}	
}

/* @container_to_fill is the class or the id of the container  example .container or #container
 * this function return empty spams for each breadcrumb, so later we can update them with the correct width.
 * after the returned html is inserted on the dom you have to fire the event 'replace all empty breadcrumb'
 * */
og.getEmptyCrumbHtml = function(dims,container_to_fill,skipped_dimensions,show_link) {
	var all_bread_crumbs = "";
	if (typeof show_link == "undefined" || show_link == null ) {
		var show_link = true;
	}
	
	//all_bread_crumbs += '<span class="obj-breadcrumb-container">';
	for (x in dims) {
		if (isNaN(x)) continue;
		if (typeof skipped_dimensions != "undefined" && skipped_dimensions != null ) {
			if (skipped_dimensions.indexOf(x) != -1) continue;
		}
		var dim = {};
		var empty_bread_crumbs = "";
		var members = dims[x];
		
		for (id in members) {
			if (isNaN(id)) continue;
									
			//return a target to reload on the callback after get the member from the server if is necesary
			empty_bread_crumbs += '<span class="member-path"><span class="bread-crumb-'+ id +' empty-bread-crumb member-path" data-container-to-fill="'+container_to_fill+'" data-show-link="'+show_link+'"></span></span>';
			if(og.emptyBreadcrumbsToRefresh.indexOf(id) == -1){  
				og.emptyBreadcrumbsToRefresh.push(id);
			}
		}
		
		all_bread_crumbs += empty_bread_crumbs;
	}
	
	//all_bread_crumbs += '</span>';
	return all_bread_crumbs;
}

/*
 * member_id the member id
 * target the class or id of the target to insert the breadcrumb
 * container_to_fill the class or id of the breadcrumb container to be fill
 * */
og.insertBreadcrumb = function(member_id,target,from_callback) {
	target = "#"+target;
	var container_to_fill = $(target).data("container-to-fill");
	var show_link = $(target).data("show-link");
	
	/*SINGLE BREADCRUMB SECTION*/
	var extra_params = {										
			};	
	var members = og.getMemberTextsFromOgDimensions(member_id, true);
	
	//title must have all parents members names
	var title = '';
	members.reverse();
	for (var i=0; i<members.length; i++) {
		var m = members[i];
		title += m.text;
		if(m.id != member_id){
			title += " • ";
		}
		member = m;
	}
	$(target).attr('title', title);	
		
	$(target).parent().data('object-type', member.ot);	
		
	//calculate the container width and check if thers more elements in the same container
	var container_width = $(target).closest(container_to_fill).width();//.parent().parent() .closest(container_to_fill)
	var real_container_width = container_width;
	var container_current_childs = $(target).parent().siblings();
	var container_current_childs_width = 0;
	for (var j = 0; j < container_current_childs.length; j++) {
		container_current_childs_width += $(container_current_childs[j]).outerWidth(true);
	}
	container_width = container_width - container_current_childs_width;
		
	//we clone the element because the target or a parent can be not displayed and this can generate some problems to calculate child's widths
	var clone = $(target).closest(container_to_fill).clone(true);
	$('body').append(clone);
	
	var original_container = $(target).closest(container_to_fill);
	original_container.html('');
	
	//reorder members in path (one from the end, one from the start) work1*work2*work3 ->  work1*...*work3
	var last = true;
	var ordained_members = new Array();
	var length = members.length;
	for (var i=1; i<= length; i++) {
		if(last){
			ordained_members.push(members.pop());			
			last = false;
		}else{
			ordained_members.push(members.shift());			
			last = true;
		}
	}
	
	//add all members
	last = true;
	var more_members = '<span class="more-members-separator">...<span class="bullet-separator"></span> </span>';
	$(target).prepend(more_members);
	for (var i=0; i<ordained_members.length; i++) {		
		var m = ordained_members[i];	
		
		var member_name = m.text;
		if(show_link){
			var onclick = "return false;";
			if (og.additional_on_dimension_object_click[m.ot]) {
				onclick = og.additional_on_dimension_object_click[m.ot].replace('<parameters>', m.id);
			}  
			
			member_name = '<a onclick="'+onclick+';" href="#">'+ m.text +'</a>';
		}
		
		
		var member_text = '<span>'+member_name+' <span class="bullet-separator"></span> </span>';
		if(m.id == member_id){
			member_text = '<span>'+member_name+'</span>';
		}
			
		var childs = $(target).children();
		
		var childs_width = 0;
		for (var j = 0; j < childs.length; j++) {
			childs_width += $(childs[j]).outerWidth(true);
		}
			
		//estimate the width of member text
		var calc = '<span id="test_width" style="display:none">' + member_text + '</span>';
		 $('body').append(calc);
		 var member_text_width = $('#test_width').outerWidth(true) + 30;
		 $('#test_width').remove();		 	
		
		//only add if there's free space
		if(childs_width + member_text_width < container_width ){
			if(i == ordained_members.length-1){
				more_members = "";
			}
			if(last){
				$(target).children( ".more-members-separator" ).replaceWith(more_members + member_text);	
				last = false;
			}else{
				$(target).children( ".more-members-separator" ).replaceWith(member_text + more_members);	
				last = true;
			}			
		}else{
			if(i == 0){				
				if(ordained_members.length > 1){
					$(target).children( ".more-members-separator" ).replaceWith(more_members + member_text);
				}else{
					$(target).children( ".more-members-separator" ).replaceWith(member_text);
				}
			}
			break;
		}		
	}
		
	//Multiple Breadcrumb	
	og.checkMultiMemberBreadcrumb(target,real_container_width);
	
	//remove the clone
	var final_container = clone.clone(true);
	original_container.replaceWith(final_container);
	clone.remove();
}

//check if there are more member paths in the same breadcrumb container and if is necesary colapse them to objet types totals
og.checkMultiMemberBreadcrumb = function(target, container_width) {	
	var member_paths_in_container= $(target).parent().siblings(".member-path");
	member_paths_in_container.push($(target).parent());
	
	var member_paths_in_container_width = 0;
	var object_types_totals = {};
	
	//get the total width for the members in the container and count how many members there are for each dimension
	for (var i = 0, length = member_paths_in_container.length; i < length; i++) {
		member_paths_in_container_width += $(member_paths_in_container[i]).outerWidth(true);
		var object_type = $(member_paths_in_container[i]).data("object-type");
		
		if (typeof(object_type) !== 'undefined'){
			if (typeof(object_types_totals[object_type]) !== 'undefined'){
				object_types_totals[object_type] = object_types_totals[object_type] + 1;
			}else{
				object_types_totals[object_type] = 1;
			}	
		}
	}
		
	var total_paths = member_paths_in_container.length;
	
	//if thers overflow colapse all breadcrumbs to objet types totals
	if(member_paths_in_container_width > container_width && total_paths > 1){
		var object_types_totals_text = "";
		
		object_types_totals_text += "<button class='members-total-colapsed breadcrumbBtn btnPopoverNotInitialized'>";
		for (var prop in object_types_totals) {
			var total = object_types_totals[prop];
			var plural = "";
			if(total > 1){
				plural = "s";
			}			
			object_types_totals_text += "<span class='ctmBadge'>"+total+"</span> ";
			object_types_totals_text += lang(og.objectTypes[prop].name+plural)+" ";						
 	    }
		object_types_totals_text += "</button>";
		
		//remove old btns
		var old_colapsed_btns = $(target).parent().parent().children(".members-total-colapsed");
		if(old_colapsed_btns.length > 0){
			for (var i = 0, length = old_colapsed_btns.length; i < length; i++) {
				 $(old_colapsed_btns[i]).remove();
			}				
		}
		
		$(target).parent().parent().prepend(object_types_totals_text);
		
		for (var i = 0, length = member_paths_in_container.length; i < length; i++) {
			$(member_paths_in_container[i]).hide();
		}
		
		og.checkObjectTypesTotalsOverflow(target, container_width);
	}
}

//if thers overflow colapse all object types totals to "view classification" btn
og.checkObjectTypesTotalsOverflow = function(target, container_width) {	
	var object_types_in_container= $(target).parent().siblings(".members-total-colapsed");
		
	var object_types_in_container_width = 0;
		
	for (var i = 0, length = object_types_in_container.length; i < length; i++) {
		object_types_in_container_width += $(object_types_in_container[i]).outerWidth(true);		
	}
		
	//if thers overflow colapse all object types totals to "view classification" btn
	if(object_types_in_container_width > container_width){
		for (var i = 0, length = object_types_in_container.length; i < length; i++) {
			$(object_types_in_container[i]).remove();
		}
						
		if($(target).parent().parent().children(".breadcrumbAllBtn").length == 0){
			var view_classification_btn = '<button class="breadcrumbAllBtn breadcrumbBtn btnPopoverNotInitialized">'+lang("view classification")+'</button>';
			$(target).parent().parent().prepend(view_classification_btn);
		}
	}
}

og.initBreadcrumbsBtns = function(btns){
	for (var i = 0; i < btns.length; i++) {
	    var btn = $(btns[i]);
	    
	    var member_paths = btn.siblings(".member-path");
	    var breadcrumbs_html = new Array();
	    var max_width = 0;
	    
	    var tmp_ot = {};
	   	for (var j = 0; j < member_paths.length; j++) {
	   		
	   		var ot = $(member_paths[j]).data("object-type");
	   		if(typeof ot == "undefined"){
	   			continue;
	   		}	   		
	   		var ot_name = og.objectTypes[ot].name+"s";
	   		
	   		if(typeof tmp_ot[ot_name] == "undefined"){
	   			tmp_ot[ot_name] = new Array();
	   		}
	   		
	   		if($(member_paths[j]).outerWidth(true) > max_width){
	   			max_width = $(member_paths[j]).outerWidth(true);
	   		}  
	   		
	   		var tmp_member = {};
			tmp_member["html"] = $(member_paths[j]).html();
			
			tmp_ot[ot_name].push(tmp_member);
			
		}
	   	 	
	   	var btn_id = Ext.id();
	   	
	  	//POPOVER	
	   	//get template
		var source = $("#breadcrumb-popover-template").html(); 
		//compile the template
		var template = Handlebars.compile(source);
		
		//template data
		var data = {
				breadcrumbs: tmp_ot,
				btn_id: btn_id,
				max_width: max_width
		}
		
		//instantiate the template
		var html = template(data);
				
		btn.attr("id",btn_id);		
		btn.data( "visible", 0 );
		btn.popover('destroy');
	    btn.popover({ content: "example", 
	    	delay: { 
	    	       show: "100", 
	    	       hide: "100"
	    	    },
	    	html: true,
	        container: 'body',
	    	template : html,
	    	placement: 'auto left',
	    	trigger: 'hover'
        });	
	    	    
	    btn.removeClass("btnPopoverNotInitialized");
	    
	    btn.on('hide.bs.popover', function (event) {
	    	if($(this).data( "visible")){
	    		event.preventDefault();
	    	}	    	  
	    });
		
	}	
}

og.showBreadcrumbsPopover = function(btn_id){
	$('#'+btn_id).data( "visible", 1 );	
}

og.hideBreadcrumbsPopover = function(btn_id,pop_id){
	$('#'+btn_id).data( "visible", 0 );
	$('#'+btn_id).popover('hide');
	$('#'+pop_id).remove();	
}




