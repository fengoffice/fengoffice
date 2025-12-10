
og.Breadcrumbs = {
		
	resetSelection: function () {
		// delete any pending request to select a node
		if (og.try_to_select_member_intervals) {
			for (x in og.try_to_select_member_intervals) {
				clearInterval(og.try_to_select_member_intervals[x]);
			}
			og.try_to_select_member_intervals = {};
		}

		// remove everything from the context
		for (dimId in og.contextManager.dimensionMembers) {
			og.contextManager.cleanActiveMembers(dimId);
		}

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
	
			// Rezize the header breadcrumb container
			var left_w = $('.header-content-left').width();
			if (left_w < 60) left_w = 60;
			$('#left-header-cell').css('min-width', left_w + 'px');
			$('#left-header-cell').css('width', left_w + 'px');
	
			// Calculate the width of the user box
			var user_link_w = $('.header-content-right #userboxWrapper #userLink').width();
			$('.header-content-right #userboxWrapper').css('width', (user_link_w + 65) + 'px');
			var right_w = $('.header-content-right').outerWidth();
			$('#right-header-cell').css('min-width', (right_w + 30) + 'px');
			$('#right-header-cell').css('width', (right_w + 30) + 'px');
	
			// Calculate the width of the breadcrumb container
			var center_w = $("#headerContent").outerWidth() - left_w - right_w - 100;
			$('#center-header-cell').css('width', center_w + 'px');
	
			// Calculate the width of the breadcrumb container
			$('.header-breadcrumb-container').css('max-width', center_w + 'px');
			$('.breadcrumb-members').css('width', (center_w - $('.header-breadcrumb.home').width() - 15) + 'px');
			$('.primary-breadcrumb').css('width', $('.breadcrumb-members').width());
			$('.secondary-breadcrumb').css('width', $('.breadcrumb-members').width());
	
			// Rezize the primary breadcrumb container
			var prim_bcs = $('.primary-breadcrumb .header-breadcrumb');
			var primaryBreadcrumbWidth = $('.primary-breadcrumb').width();
			var totalBreadcrumbWidth = 0;
	
			prim_bcs.each(function() {
				totalBreadcrumbWidth += $(this).outerWidth(true);
			});
	

			if (totalBreadcrumbWidth > primaryBreadcrumbWidth || prim_bcs.length > 1) {
				og.Breadcrumbs.resizeHeaderBreadcrumbLine(prim_bcs, 22);
			}
	
			// Rezize the secondary breadcrumb container
			var sec_bcs = $('.secondary-breadcrumb .header-breadcrumb');
			if (sec_bcs.length > 1) {
				og.Breadcrumbs.resizeHeaderBreadcrumbLine(sec_bcs, 15);
			}
	
		}, 500);
	},
	
    
	resizeHeaderBreadcrumbLine: function(br_array, max_font_size) {
		var min_font_size = 14;
		var next_font_size = max_font_size - 1;
		var containerWidth = $('.breadcrumb-members').width();
		var sumWidth = 0;
		
		// Aplicar font-size inicial y resetear max-width
		br_array.css({'max-width':'1000px', 'font-size': max_font_size + 'px'});
		
		// Calcular ancho total actual de los breadcrumbs
		br_array.each(function() {
			 sumWidth += $(this).outerWidth(true);
		});
		
		// Mientras el ancho total supere el ancho del contenedor y podamos reducir la fuente...
		while (sumWidth > containerWidth && next_font_size >= min_font_size) {
			// Reducir el font-size de todos los elementos
			br_array.css('font-size', next_font_size + 'px');
			
			// Recalcular el ancho total
			sumWidth = 0;
			br_array.each(function() {
				sumWidth += $(this).outerWidth(true);
			});
			
			next_font_size--;
		}
		
		// Si aún no cabe, recortar los elementos (por ejemplo, acortar el max-width de los primeros)
		if (sumWidth > containerWidth) {
			var $last = br_array.last();
			var lastWidth = $last.outerWidth(true);
			var remainingWidth = containerWidth - lastWidth;
			var singleWidth = Math.floor(remainingWidth / (br_array.length - 1)) - 15;
			if (singleWidth < 40) singleWidth = 40;
			
			// Asignar max-width a todos menos el último
			var actualWidth = 0;
			br_array.slice(0, br_array.length - 1).css('max-width', singleWidth + 'px');
			br_array.slice(0, br_array.length - 1).each(function() {
				actualWidth += $(this).outerWidth(true);
			});
			
			// Si aún sobra overflow, ajustar el último
			if (containerWidth - actualWidth < lastWidth) {
				$last.css('max-width', (containerWidth - actualWidth - 15) + 'px');
			}
		}
	}
	
}