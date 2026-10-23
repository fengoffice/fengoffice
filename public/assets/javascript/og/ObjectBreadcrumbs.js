og.emptyBreadcrumbsToRefresh = new Array();

// RAF with setTimeout fallback for environments that lack requestAnimationFrame (e.g. IE9).
var _raf = window.requestAnimationFrame || function(fn) { return setTimeout(fn, 16); };

// Persistent off-screen element for text-width measurement in insertBreadcrumb.
// Created lazily on first use; never removed, so no append/remove reflows per segment.
og._breadcrumbMeasureEl = null;
og._breadcrumbDebounceTimer = null;

// Maps member_id → array of span IDs registered by getEmptyCrumbHtml.
// Enables O(1) lookup instead of O(DOM) class scan in replaceAllEmptyBreadcrumbForThisMember.
og._emptyBreadcrumbById = {};
og._emptyBreadcrumbIdSeq = 0;

// Shadow object for O(1) dedup of emptyBreadcrumbsToRefresh (keeps the array for callers).
og._emptyBreadcrumbSet = {};

// Cache compiled Handlebars template for breadcrumb buttons.
og._breadcrumbPopoverTemplate = null;

// Track if a deferred initBreadcrumbsBtns call is pending.
og._breadcrumbBtnInitPending = false;

og._getBreadcrumbMeasureEl = function() {
	if (!og._breadcrumbMeasureEl || !og._breadcrumbMeasureEl.length) {
		og._breadcrumbMeasureEl = $('<span>').css({
			position: 'absolute',
			top: '-9999px',
			left: '-9999px',
			visibility: 'hidden',
			whiteSpace: 'nowrap'
		}).appendTo('body');
	}
	return og._breadcrumbMeasureEl;
};

// Schedule a single deferred initBreadcrumbsBtns call to batch rapid-fire arrivals.
og._scheduleInitBreadcrumbsBtns = function() {
	if (og._breadcrumbBtnInitPending) return;
	og._breadcrumbBtnInitPending = true;
	_raf(function() {
		og._breadcrumbBtnInitPending = false;
		og.initBreadcrumbsBtns($('.breadcrumbBtn.btnPopoverNotInitialized').toArray());
	});
};

og.eventManager.addListener('replace all empty breadcrumb', function() {
	if (og._breadcrumbDebounceTimer) {
		clearTimeout(og._breadcrumbDebounceTimer);
	}
	og._breadcrumbDebounceTimer = setTimeout(function() {
		og._breadcrumbDebounceTimer = null;
		var copy = og.emptyBreadcrumbsToRefresh.slice(0);
		og.emptyBreadcrumbsToRefresh.length = 0;
		og._emptyBreadcrumbSet = {};
		if (copy.length === 0) return;
		var callback = function(emptyBreadcrumbs) {
			og.replaceAllEmptyBreadcrumbForThisMemberInterval(emptyBreadcrumbs);
		};
		og.getMembersFromServer(copy, callback, copy);
	}, 50);
});

og.replaceAllEmptyBreadcrumbForThisMemberInterval = function(emptyBreadcrumbs) {
	var curIndex = 0;
	var FRAME_BUDGET_MS = 10; // leave ~6ms for browser rendering at 60fps

	// Init buttons already in DOM (cached members rendered by getEmptyCrumbHtml).
	og._scheduleInitBreadcrumbsBtns();

	function processFrame() {
		var frameStart = Date.now();

		while (curIndex < emptyBreadcrumbs.length) {
			var id = emptyBreadcrumbs[curIndex++];
			var members = og.getMemberFromOgDimensions(id, true, function(dimension_id, member) {
				og.replaceAllEmptyBreadcrumbForThisMember(0, member);
				og._scheduleInitBreadcrumbsBtns();
			});
			if (members.length > 0) {
				og.replaceAllEmptyBreadcrumbForThisMember(0, members[0]);
			}

			// Yield to browser when frame budget is exhausted — continue next frame.
			if (Date.now() - frameStart >= FRAME_BUDGET_MS) {
				_raf(processFrame);
				return;
			}
		}

		// Natural exit: all members processed — init any remaining buttons.
		og._scheduleInitBreadcrumbsBtns();
	}

	_raf(processFrame);
};


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

og.replaceAllEmptyBreadcrumbForThisMember = function(dimension_id, member, extra_params) {
	var all_targets;
	var registeredIds = og._emptyBreadcrumbById[member.id];
	if (registeredIds && registeredIds.length > 0) {
		all_targets = [];
		for (var _ri = 0; _ri < registeredIds.length; _ri++) {
			var _el = document.getElementById(registeredIds[_ri]);
			if (_el) all_targets.push(_el);
		}
		delete og._emptyBreadcrumbById[member.id];
	} else {
		// Fallback for callers that bypass getEmptyCrumbHtml (e.g. getCrumbHtml path).
		all_targets = $('.empty-bread-crumb.bread-crumb-' + member.id).toArray();
	}

	if (all_targets.length === 0) return;

	// generate breadcrumb html for the first one
	var j= 0;
	
	var new_target_id = 'bread-crumb-'+ Ext.id() + member.id;
	var container_to_fill = $(all_targets[j]).data("container-to-fill");
	var show_link = $(all_targets[j]).data("show-link");
	var exclude_parents_path = $(all_targets[j]).data("exclude-parents-path");
	var epp = exclude_parents_path ? '1' : '0';
	
	$(all_targets[j]).parent().html('<span id="'+new_target_id+'" class="bread-crumb-'+ member.id +' member-path real-breadcrumb og-wsname-color-'+ member.color +
			'" data-container-to-fill="'+container_to_fill+'" data-show-link="'+show_link+'" data-exclude-parents-path="'+epp+'"></span>');
	
	// this function creates the html and fills the container with it
	og.insertBreadcrumb(member.id,new_target_id,false,null,exclude_parents_path);
	
	// get the generated html
	var target_0_html = $("#"+new_target_id).html();
	
	// for each target copy the already generated html
	// Cache widths per container_to_fill selector so rows in the same container type
	// share one measurement, while rows in different containers get their own width.
	var container_width_cache = {};
	for (var j = 1; j < all_targets.length; j++) {
		var copy_target_id = 'bread-crumb-'+ Ext.id() + member.id;
		var container_to_fill = $(all_targets[j]).data("container-to-fill");
		var show_link = $(all_targets[j]).data("show-link");
		var exclude_parents_path = $(all_targets[j]).data("exclude-parents-path");
		var epp = exclude_parents_path ? '1' : '0';

		$(all_targets[j]).parent().html('<span id="'+copy_target_id+'" class="bread-crumb-'+ member.id +' member-path real-breadcrumb og-wsname-color-'+ member.color +
				'" data-container-to-fill="'+container_to_fill+'" data-show-link="'+show_link+'" data-exclude-parents-path="'+epp+'"></span>');

		$('#'+copy_target_id).append(target_0_html);

		// set object-type so checkMultiMemberBreadcrumb can build the collapsed button correctly
		$('#'+copy_target_id).parent().data('object-type', member.object_type_id);

		// make breadcrumbs groups when container is shorter than brs length
		if (typeof container_width_cache[container_to_fill] === 'undefined') {
			container_width_cache[container_to_fill] = $('#'+copy_target_id).closest(container_to_fill).width();
		}
		og.checkMultiMemberBreadcrumb($('#'+copy_target_id), container_width_cache[container_to_fill]);
	}
	
}

/* @container_to_fill is the class or the id of the container  example .container or #container
 * this function return empty spams for each breadcrumb, so later we can update them with the correct width.
 * after the returned html is inserted on the dom you have to fire the event 'replace all empty breadcrumb'
 * */
og.getEmptyCrumbHtml = function(dims,container_to_fill,skipped_dimensions,show_link,exclude_parents_path, allow_associated_dimensions) {
	var all_bread_crumbs = "";
	if (typeof show_link == "undefined" || show_link == null ) {
		var show_link = true;
	}
	var epp = exclude_parents_path ? '1' : '0';
	
	//all_bread_crumbs += '<span class="obj-breadcrumb-container">';
	for (x in dims) {
		if (isNaN(x)) continue;
		if (typeof skipped_dimensions != "undefined" && skipped_dimensions != null ) {
			if (skipped_dimensions.indexOf(x) != -1) continue;
		}
		var dim = {};
		var empty_bread_crumbs = "";
		var members_by_ot = dims[x];
		
		// don't show associated dimensions in content objects general breadcrumb
		if (!allow_associated_dimensions && dims[x] && dims[x].is_assoc_dim) continue;
		
		for (ot_id in members_by_ot) {
			if (isNaN(ot_id)) continue;
			
			var members = members_by_ot[ot_id];
			if (!members) continue;
			for (idx=0; idx<members.length; idx++) {
				id = members[idx];
				if (isNaN(id)) continue;

				// Member not cached: assign a unique span ID and register it so
				// replaceAllEmptyBreadcrumbForThisMember can find it in O(1).
				var _spanId = 'ebc-' + id + '-' + (++og._emptyBreadcrumbIdSeq);
				empty_bread_crumbs += '<span class="member-path"><span id="' + _spanId + '" class="bread-crumb-'+ id +' empty-bread-crumb member-path" '+
					'data-container-to-fill="'+container_to_fill+'" data-show-link="'+show_link+'" data-exclude-parents-path="'+epp+'"></span></span>';
				if (!og._emptyBreadcrumbById[id]) og._emptyBreadcrumbById[id] = [];
				og._emptyBreadcrumbById[id].push(_spanId);
				if (!og._emptyBreadcrumbSet[id]) {
					og._emptyBreadcrumbSet[id] = true;
					og.emptyBreadcrumbsToRefresh.push(id);
				}
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
og.insertBreadcrumb = function(member_id, target, from_callback) {
	target = '#' + target;
	var $target = $(target);
	if (!$target.length) return;

	var container_to_fill    = $target.data('container-to-fill');
	var show_link            = $target.data('show-link');
	var exclude_parents_path = $target.data('exclude-parents-path');

	var members = og.getMemberTextsFromOgDimensions(member_id, !exclude_parents_path);
	if (!members.length) return;

	// members is [leaf, parent, grandparent...] — reverse to get root→leaf for display.
	var leafMember = members[0]; // leaf before reversing
	members.reverse();           // now root → leaf

	// Build tooltip in root→leaf order.
	var title = '';
	for (var ti = 0; ti < members.length; ti++) {
		title += members[ti].text;
		if (members[ti].id !== member_id) title += ' • ';
	}
	$target.attr('title', title);
	$target.parent().data('object-type', leafMember ? leafMember.ot : '');

	var $container = $target.closest(container_to_fill);
	var container_width = $container.width();
	if ($container.css('max-width') !== 'none') {
		container_width = parseFloat($container.css('max-width'));
	}

	// If container has no width (hidden group, collapsed tab), defer one time so that
	// any expand animation or layout pass can finish before we measure. On the retry we
	// proceed regardless to avoid an infinite loop (from_callback === 'deferred').
	if (container_width <= 0 && from_callback !== 'deferred') {
		var _def_member = member_id;
		var _def_target = target.replace('#', '');
		setTimeout(function() { og.insertBreadcrumb(_def_member, _def_target, 'deferred'); }, 150);
		return;
	}

	var real_container_width = container_width;

	// Subtract width already taken by sibling member-paths.
	var $siblings = $target.parent().siblings();
	var siblings_width = 0;
	for (var si = 0; si < $siblings.length; si++) {
		siblings_width += $($siblings[si]).outerWidth(true);
	}
	var available_width = container_width - siblings_width;

	// Build member spans HTML for each member (root→leaf order, i.e. members[0] is root).
	// Use the off-screen measurement element — no DOM clone needed.
	var measureEl = og._getBreadcrumbMeasureEl();
	var sep_html  = '<span class="more-members-separator">...<span class="bullet-separator"></span> </span>';
	var sep_width = measureEl.html(sep_html).outerWidth(true);

	// Pre-compute HTML + width for each member so we can fill from both ends.
	var memberSpans = [];
	for (var ms = 0; ms < members.length; ms++) {
		var mm = members[ms];
		var mm_name = mm.text;
		if (show_link) {
			var mm_onclick = 'return false;';
			if (og.additional_on_dimension_object_click[mm.ot]) {
				mm_onclick = og.additional_on_dimension_object_click[mm.ot].replace('<parameters>', mm.id);
			} else if (mm.dim) {
				var mm_tree = Ext.getCmp('dimension-panel-' + mm.dim);
				if (mm_tree) {
					mm_onclick = "og.memberTreeExternalClick('" + mm_tree.dimensionCode + "', " + mm.id + ");";
				}
			}
			mm_name = '<a onclick="' + mm_onclick + ';" href="#">' + mm.text + '</a>';
		}
		var mm_span = (mm.id == member_id)
			? '<span>' + mm_name + '</span>'
			: '<span>' + mm_name + ' <span class="bullet-separator"></span> </span>';
		memberSpans.push({ html: mm_span, width: measureEl.html(mm_span).outerWidth(true) + 30 });
	}

	// Fill from leaf end (back) and root end (front) alternately, keeping path order in output.
	// prefix holds members taken from the root side; suffix holds members from the leaf side.
	var prefix = [], suffix = [];
	var prefix_w = 0, suffix_w = 0;
	var front = 0, back = memberSpans.length - 1;
	var take_back = true; // prioritise showing the leaf member first

	// Ensure at least the leaf member is shown even if it doesn't fit.
	if (memberSpans.length > 0) {
		suffix.unshift(memberSpans[back].html);
		suffix_w += memberSpans[back].width;
		back--;
		take_back = false; // next take from front
	}

	while (front <= back) {
		var candidate = take_back ? memberSpans[back] : memberSpans[front];
		var new_total = prefix_w + suffix_w + candidate.width + (front < back ? sep_width : 0);
		if (new_total < available_width) {
			if (take_back) {
				suffix.unshift(candidate.html);
				suffix_w += candidate.width;
				back--;
			} else {
				prefix.push(candidate.html);
				prefix_w += candidate.width;
				front++;
			}
			take_back = !take_back;
		} else {
			break;
		}
	}

	var has_gap = front <= back; // show "..." whenever ancestors are hidden, even if prefix is empty
	var final_html = prefix.join('') + (has_gap ? sep_html : '') + suffix.join('');
	$target.html(final_html);

	// Check if multiple member-paths overflow their shared container.
	og.checkMultiMemberBreadcrumb($target, real_container_width);
};

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
			
			var plural = total > 1;
			var name_to_show = plural ? og.objectTypes[prop].c_name_plural : og.objectTypes[prop].c_name;
			
			object_types_totals_text += "<span class='ctmBadge'>"+total+"</span> ";
			if (og.objectTypes[prop] && og.objectTypes[prop].name) {
				object_types_totals_text += name_to_show +" ";						
			}
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
	// Compile Handlebars template once per page, not once per button.
	if (!og._breadcrumbPopoverTemplate) {
		var _tmplSource = $('#breadcrumb-popover-template').html();
		og._breadcrumbPopoverTemplate = Handlebars.compile(_tmplSource);
	}
	var template = og._breadcrumbPopoverTemplate;

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
	   		if (!og.objectTypes[ot]) continue;
	   		var ot_name = og.objectTypes[ot].c_name;

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




