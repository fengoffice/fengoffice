
og.Breadcrumbs = {
		
	resetSelection: function () {

		// clear dimension filter inputs + tree filters (UI + actual tree state)
		try {
			var $filters = $('.dimension-panel-textfilter');
			$filters.each(function() {
				var el = this;
				if (!el || !el.id) return;
				
				// clear Ext field value when possible (keeps ExtJS internal state consistent)
				var cmp = Ext.getCmp(el.id);
				if (cmp && typeof cmp.setValue === 'function') {
					cmp.setValue('');
				} else {
					$(el).val('');
				}
				
				// also clear tree filter state if this input belongs to a tree
				var id = el.id;
				var suffix = '-textfilter';
				var prefix = 'textfilter-';
				
				// Convention A: MemberTree/MemberTreeAjax input is config.id + '-textfilter'
				if (id.indexOf(suffix) === id.length - suffix.length) {
					var treeId = id.substring(0, id.length - suffix.length);
					var tree = Ext.getCmp(treeId);
					if (tree) {
						// reset search cache used by MemberTree/MemberTreeAjax
						if (tree.getTopToolbar && tree.getTopToolbar() && tree.getTopToolbar().container) {
							tree.getTopToolbar().container.history = undefined;
						} else if (tree.tbar) {
							tree.tbar.history = undefined;
						}
						
						if (typeof tree.clearFilter === 'function') {
							tree.clearFilter();
						}
					}
				} else if (id.indexOf(prefix) === 0) {
					// Convention B: MemberChooserTree input is 'textfilter-' + config.dimensionId
					var dimensionId = id.substring(prefix.length);
					
					// First, try a couple of common ids (depending on how the tree panel is mounted)
					var candidateIds = [
						'dimension-panel-' + dimensionId,
						'dimension-panel-' + dimensionId + '-tree'
					];
					for (var ci = 0; ci < candidateIds.length; ci++) {
						var candTree = Ext.getCmp(candidateIds[ci]);
						if (candTree && typeof candTree.clearFilter === 'function') {
							candTree.clearFilter();
							return;
						}
					}
					
					// Then, clear all MemberChooserTree instances matching this dimensionId
					try {
						var chooserTrees = [];
						if (Ext.ComponentQuery && typeof Ext.ComponentQuery.query === 'function') {
							chooserTrees = Ext.ComponentQuery.query('member-chooser-tree');
						} else if (Ext.ComponentMgr && Ext.ComponentMgr.all) {
							// Fallback for older ExtJS: scan all components.
							chooserTrees = [];
							try {
								var all = Ext.ComponentMgr.all;
								if (typeof all.each === 'function') {
									all.each(function(comp) {
										chooserTrees.push(comp);
									});
								} else if (Array.isArray(all)) {
									chooserTrees = all;
								} else {
									for (var key in all) {
										var comp = all[key];
										if (comp) chooserTrees.push(comp);
									}
								}
							} catch (e) {
								// ignore
							}
						}
						
						for (var t = 0; t < chooserTrees.length; t++) {
							var ct = chooserTrees[t];
							if (ct && String(ct.dimensionId) === String(dimensionId) && typeof ct.clearFilter === 'function') {
								ct.clearFilter();
							}
						}
					} catch (e) {
						// ignore: best-effort reset
					}
				}
			});
		} catch (e) {
			// ignore: resetSelection must not fail
		}

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

		// Remove all dimension filters from trees
		var treeElements = $("#menu-panel .x-tree");
		treeElements.each(function() {
			var treeId = $(this).attr('id');
			var tree = Ext.getCmp(treeId);
			if (tree) {
				var root = tree.getRootNode();
				if (root) {
					tree.filterByMember([], root);
				}
			}
		});
		// refresh header breadcrumbs
		this.refresh();
		// reload current panel
		var currentPanel = Ext.getCmp('tabs-panel').getActiveTab();
		if (currentPanel) {
			currentPanel.reset();
		}
	},
	
	refresh: function (node) {
		var mainDimensionId = node ? node.ownerTree.dimensionId : null;
		if (!mainDimensionId) {
			mainDimensionId = Object.keys(og.contextManager.dimensionMembers)[0];
		}
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
	
			// The user box (#userboxWrapper) sizes itself via CSS (flex + padding), no js resize needed
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
		var containerWidth = $('.breadcrumb-members').width() - 18;
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