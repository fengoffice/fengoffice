
og.Breadcrumbs = {
		
	resetSelection: function () {
		// click root node in first tree, then call the next
		for (dimId in og.contextManager.dimensionMembers) {
			og.clickRootNodeAndCallNext(dimId, 0);
			break;
		}
	},
	
	refresh: function (node) {
		var mainDimensionId = node.ownerTree.dimensionId;
		var secondary_htmls = [];
		var primary_html = '';
		
		$('#headerBreadcrumb div.primary-breadcrumb').html('');
		$('#headerBreadcrumb div.secondary-breadcrumb').html('');
		
		for (dimId in og.contextManager.dimensionMembers) {
			
			var members = og.contextManager.dimensionMembers[dimId];
			if (members.length > 0) {
				for(var j in members) {
					var member = members[j];
					if (member > 0 ) {
						
						memberTitle = og.contextManager.getMemberName(dimId, member);
						if (memberTitle) {
							var parent_html = '';
							var first = true;
							
							var path_array = og.contextManager.getMemberPath(dimId, member, true);
							for (var p in path_array) {
								if (typeof path_array[p] == 'function') continue;
								
								var dcode = path_array[p].ownerTree.dimensionCode;
								var onclick = 'og.memberTreeExternalClick(\''+dcode+'\','+path_array[p].id+');';
								
								var ico_html = '<div class="member-icon '+path_array[p].attributes.iconCls+'" style="padding-left:10px; margin-right:5px; float:left; height:18px;">&nbsp;</div>';
								
								parent_html += '<div class="header-breadcrumb" onclick="'+onclick+'" title="'+path_array[p].text+'">'+ ico_html + path_array[p].text + '</div>';
								first = false;
							}
							
							var n = og.contextManager.getTreeNode(dimId, member);
							var ico_html = '<div class="member-icon '+n.attributes.iconCls+'" style="padding-left:10px; margin-right:5px; float:left; height:18px;">&nbsp;</div>';
							
							var dcode = n.ownerTree.dimensionCode;
							var onclick = 'og.memberTreeExternalClick(\''+dcode+'\','+n.id+');';
							
							var on_close_click = n.parentNode ? 'og.memberTreeExternalClick(\''+dcode+'\',\''+n.parentNode.id+'\');' : 'return true;';
							var close_html = '<div class="header-breadcrumb-close" onclick="'+on_close_click+'"></div>';
							
							var bhtml = parent_html + '<div class="header-breadcrumb" title="'+memberTitle+'" onclick="'+onclick+'">'+ ico_html + memberTitle +'</div>'+ close_html;;
							
							if (dimId == mainDimensionId) {
								primary_html += bhtml;
							} else {
								secondary_htmls.push(bhtml);
							}
						}
					}
				}
			}
			
		}
		
		if (primary_html == '') {
			primary_html = secondary_htmls.shift();
		}
		secondary_html = secondary_htmls.join('<div class="separator">|</div>');
		$('#headerBreadcrumb div.primary-breadcrumb').html(primary_html);
		$('#headerBreadcrumb div.secondary-breadcrumb').html(secondary_html);
		
		if (secondary_htmls.length == 0) {
			$('#headerBreadcrumb .header-breadcrumb.home').css('line-height', '35px');
			$('#headerBreadcrumb div.primary-breadcrumb').css({'height': '45px'});
			$('#headerBreadcrumb div.primary-breadcrumb .header-breadcrumb').css({'height': '45px', 'line-height':'35px'});
			$('#headerBreadcrumb div.primary-breadcrumb .member-icon').css({'margin-top': '8px'});
			$('#headerBreadcrumb div.primary-breadcrumb .header-breadcrumb-close').css({'margin-top': '7px'});
			$('.header-content-left #logodiv h1').css({'height': '45px', 'line-height':'35px'});
		} else {
			$('#headerBreadcrumb div.primary-breadcrumb').css({'height': '21px'});
			$('#headerBreadcrumb .header-breadcrumb.home').css({'line-height': '20px'});
			$('.header-content-left #logodiv h1').css({'height': '21px', 'line-height':'21px'});
		}
		
		og.Breadcrumbs.resizeHeaderBreadcrumbs();
	},
	
	resizeHeaderBreadcrumbs: function() {
		setTimeout(function() {
			$(".header-breadcrumb.home").show();
			// left td
			var left_w = $('.header-content-left').width();
			if (left_w < 60) left_w=60;
			$('#left-header-cell').css('min-width', (left_w)+'px');
			$('#left-header-cell').css('width', (left_w)+'px');
			// right td
			var user_link_w = $('.header-content-right #userboxWrapper #userLink').width();
			$('.header-content-right #userboxWrapper').css('width', (user_link_w + 65)+'px');
			var right_w = $('.header-content-right').width();
			if ($.browser.msie) {
				if (right_w > 600) right_w = 600;
				else if (right_w < 370) right_w = 370;
			}
			$('#right-header-cell').css('min-width', (right_w + 30)+'px');
			$('#right-header-cell').css('width', (right_w + 30)+'px');
			
			var center_w = $("#headerContent").outerWidth() - left_w - right_w - 100;
			$('#center-header-cell').css('width', center_w + 'px');
			
			// breadcrumbs
	    	$('.header-breadcrumb-container').css('max-width', (center_w)+'px');
	    	$('.breadcrumb-members').css('width', (center_w - $('.header-breadcrumb.home').width() - 15)+'px');
	    	$('.primary-breadcrumb').css('width', $('.breadcrumb-members').width());
	    	$('.secondary-breadcrumb').css('width', $('.breadcrumb-members').width());

	    	// resize primary breadcrumb
	    	var prim_bcs = $('.primary-breadcrumb .header-breadcrumb');
	    	if (prim_bcs.length > 1) {
	    		og.Breadcrumbs.resizeHeaderBreadcrumbLine(prim_bcs, 22);
	    	}
	    	
	    	// resize secondary breadcrumb
	    	var sec_bcs = $('.secondary-breadcrumb .header-breadcrumb');
	    	if (sec_bcs.length > 1) {
	    		og.Breadcrumbs.resizeHeaderBreadcrumbLine(sec_bcs, 15);
	    	}
		}, 500);
		
    },
    
    resizeHeaderBreadcrumbLine: function(br_array, max_font_size) {
    	
    	var min_font_size = 14;
    	var next_font_size = max_font_size - 1;
    	var total_w = $('.breadcrumb-members').width();
    	
    	var sum_w = 0;
		for (var i=0; i < br_array.length; i++) {
			// reset font-size and max-width
			$(br_array[i]).css('max-width','1000px').css('font-size', max_font_size+'px');
			// sum total width
			sum_w += $(br_array[i]).width();
		}
		
		// while total width > container widtg => decrease font-size
		while (sum_w > total_w && next_font_size >= min_font_size) {
			sum_w = 0;
			for (var i=0; i < br_array.length; i++) {
				$(br_array[i]).css('font-size', next_font_size + 'px');
				sum_w += $(br_array[i]).width() + 10;
			}
			next_font_size = next_font_size - 1 ;
		}
		
		// if still not all breadcrumbs are visible, cut the first ones
		if (sum_w > total_w) {
			
			var last_w = $(br_array[br_array.length - 1]).width();
			var tw = total_w - last_w;
			
			var single_w = Math.floor(tw / (br_array.length - 1)) - 15;
			if (single_w < 40) single_w = 40;
			
			var actual_w = 0;
			for (var i=0; i < (br_array.length - 1); i++) {
	    		$(br_array[i]).css('max-width', single_w + 'px');
	    		actual_w += single_w + 10;
	    	}
			
			// if still not visible, cut the last one
			if (total_w - actual_w < last_w) {
				$(br_array[br_array.length - 1]).css('max-width', (total_w - actual_w - 15)+'px');
	    	}
		}
    }
}