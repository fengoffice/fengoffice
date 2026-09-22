
// ***** tree  ***** //
og.MemberTreeAjax = function(config) {
	var tbar = [{
		xtype: 'textfield',
		id: config.id + '-textfilter',
		cls: "dimension-panel-textfilter ico-search-m search-filter" ,
		emptyText:'',
		listeners:{
			render: {
				fn: function(f){
					f.el.on('keyup', function(e) {
						
						// ignore shift+tab
						if (this.lastKeyInfo.wasTab && this.lastKeyInfo.wasShift) {
							return;
						}

						var from_server = true;

						//check history date
						if(this.tbar.history != undefined){
							var now = new Date();

							// Calculate the difference in milliseconds
							var timeDiff = now.getTime() - this.tbar.history.date.getTime();
							//convert to hours
							timeDiff = timeDiff/(1000*60*60);
							
							//refresh history after 24 hours
							if(timeDiff > 24){
								this.tbar.history = undefined;
							}						
						}

						//create history search for the searchs that we get from the server
						if(this.tbar.history == undefined){
							this.tbar.history = {prevTextFilters: [], date: new Date()};
						}

						// Only skip the server when refining a previous successful search (longer query).
						if(this.tbar.history.prevTextFilters.length > 0){
							for (var i = 0 ; i < this.tbar.history.prevTextFilters.length ; i++) {
								var prevTextFilter = this.tbar.history.prevTextFilters[i] ;

								if (e.target.value.length > prevTextFilter.length
										&& e.target.value.indexOf(prevTextFilter) === 0) {
									from_server = false;
									break;
								}
							}							
						}

						// Open dropdown if it's not visible when search started
						if(!this.body.isVisible() && e.getKey() != 27 && e.getKey() != 9) { // Ignore ESC and TAB
							this.body.show();
							this.positionDropdown();
							this.adjustDropDownListHeight();
						}

						this._ensureInitializedAndFilter(e.target.value, from_server);
					},
					this, {buffer: 350});
				},
				scope: this
			}
		}
	}];
		
	var expandM = 'root';
	if(config.hidden) expandM = 'none';
	
	var isrootVisible = false;
	if(!config.isMultiple) isrootVisible = true;
	Ext.applyIf(config, {
		region: 'center',
		id: config.id,
		autoScroll: true,
		//rootVisible: false,
		root: {
        	text: lang('view all'),
        	id:0,
        	href: "#",
        	iconCls : 'root',
        	cls: 'root'
    	},
    	enableDrop: true,
    	lines: false,
    	rootVisible: isrootVisible,
    	ddGroup: 'MemberDD',
		collapseFirst: false,
		collapsible: true,
    	selModel: (config.multipleSelection)? new Ext.tree.MultiSelectionModel() : new Ext.tree.DefaultSelectionModel(),
    	dimensionId: config.dimensionId,
    	selectFunction: config.selectFunction,
    	genid: config.genid,
    	dimensionCode: config.dimensionCode, 
    	cls: config.dimensionCode+" single-tree single-tree-right",
    	reloadHidden: false, //To force tree reload when is hidden 
    	height: 210,
    	width: 265,
    	animate: false,    		
    	hideCollapseTool: true ,
    	expandMode: expandM, //all root,
    	tbar: tbar 
	});
	
	
	if (!config.listeners) config.listeners = {};
	
	og.MemberTreeAjax.superclass.constructor.call(this, config);

	this.lastKeyInfo = { wasTab: false, wasShift: false };

	var self = this ; // To change scope inside callbacks

	// Capture the last keys before focus, store if it was tab or shift in lastKeyInfo object
	Ext.getDoc().on('keydown', function(e) {
		if (e.getKey() === e.TAB) {
			self.lastKeyInfo.wasTab = true;
			self.lastKeyInfo.wasShift = e.shiftKey;
		} else {
			self.lastKeyInfo.wasTab = false;
			self.lastKeyInfo.wasShift = false;
		}
	});

	// ********** TREE EVENTS *********** //
	this.on({
		expandnode: function(node){
			//get childs from server
	        if(node.childNodes.length < node.attributes.realTotalChilds && node.attributes.expandable){
	        	node.ownerTree.innerCt.mask();
	        	//var tree_id = node.ownerTree.id;
	        	node.attributes.gettingChildsFromServer = true;
	        	
	        	if (!node.last_childs_offset) {
	        		node.last_childs_offset = 0;
	        	} else {
	        		node.last_childs_offset = node.last_childs_offset + og.config.member_selector_page_size;
	        	}
				var limit = og.config.member_selector_page_size;
				
				var parameters = {
					member: node.id,
					limit: limit,
					ignore_context_filters: true,
					offset: node.last_childs_offset,
					tree_id: node.ownerTree.id
				};
				
				if (member_selector[this.genid] && member_selector[this.genid].sel_context) {
					var current_selected_member_ids = [];
					for (c_dim_id in member_selector[this.genid].sel_context) {
						var c_mems = member_selector[this.genid].sel_context[c_dim_id];
						for (var i=0; i<c_mems.length; i++) {
							current_selected_member_ids.push(c_mems[i]);
						}
					}
				}
				
				if (node.ownerTree.initialConfig.get_childs_params) {
					for (p_name in node.ownerTree.initialConfig.get_childs_params) {
						if (typeof(node.ownerTree.initialConfig.get_childs_params[p_name]) == 'function') continue;
						parameters[p_name] = node.ownerTree.initialConfig.get_childs_params[p_name];
					}
				}
				
	        	og.openLink(og.getUrl('dimension', 'get_member_childs', parameters), {
	    			hideLoading:true, 
	    			hideErrors:true,
	    			callback: function(success, data){
	    				
	    				var dimension_tree = Ext.getCmp(data.tree_id);
	    					    		
	    				dimension_tree.suspendEvents();			
	    				dimension_tree.addMembersToTree(data.members,data.dimension_id);  
	    				dimension_tree.resumeEvents();	

	    				for (var i = 0 ; i < node.childNodes.length ; i++ ) {
	    					node.childNodes[i].getUI().show();					
	    				}
	    				
	    				if (data.more_nodes_left) {
	    					og.addViewMoreNode(node, data.tree_id, og.ajaxMemberTreeViewMoreCallback);
	    				} else {
	    					var old_view_more_node = dimension_tree.getNodeById('view_more_' + node.id);
	    					if (old_view_more_node) old_view_more_node.remove();
	    				}
	    				
	    				dimension_tree.innerCt.unmask();
	    				
	    				var current_node = dimension_tree.getNodeById(data.member_id);
	    				if (current_node) current_node.attributes.gettingChildsFromServer = false;
	    					    				
	    			}
	    		});
	        }else{
	        	for (var i = 0 ; i < node.childNodes.length ; i++ ) {
					node.childNodes[i].getUI().show();					
				}
	        }
			
		},
		click: function(node, e){
			//clear search filter
			this.clearFilter();
			$("#" + this.id + '-textfilter').val("");
					
			if (node.getDepth() > 0) { 
				//set focus on the selected node
				node.ensureVisible();
				node.select();
								
				var params = '"' + this.genid +'",'+ node.attributes.dimension_id +','+ node.attributes.id + ',"' + member_selector[this.genid].hiddenFieldName +'"';
				if (this.dont_reload_other_trees) params += ',true';
				eval(this.selectFunction + '(' + params + ')');
				if(this.selectFunction == ""){
					member_selector.add_relation(node.attributes.dimension_id, this.genid, node.attributes.id);
				}
				if (node.getOwnerTree()) {
					$("#"+ node.getOwnerTree().id +"-current-selected .empty-text").hide();
				}
			}else{ 
				//root
				var params = '"' + this.genid +'",'+ this.dimensionId +','+ 0 + ',"' + member_selector[this.genid].hiddenFieldName +'"';
				if (this.dont_reload_other_trees) params += ',true';
				eval(this.selectFunction + '(' + params + ')');
				if(!this.isMultiple && node.getOwnerTree()){
					$("#"+ node.getOwnerTree().id +"-current-selected .empty-text").show();
				}
				if(!this.isMultiple) {
					member_selector.remove_all_dimension_selections(this.genid, this.dimensionId);
				}
			}
			
			
			node.ownerTree.body.removeClass("have-focus");
			node.ownerTree.body.hide();

			if (!this.isMultiple) {
				// only hide the input when in single selection mode
				$("#" + this.id + '-textfilter').hide();
				$("#" + this.id + '-current-selected').show();
				this.ignoreNextFocus = true;
				$("#" + this.id + '-current-selected').focus();
			} else {
				// place the focus in the input and show the dropdown
				$("#" + this.id + '-textfilter').focus();

			}
		},
		render: function(tree){
			this.body.setVisibilityMode(Ext.Element.DISPLAY);
			this.body.toggle();
			this.body.addClass("collapsible-body");

			var tree = this;
			$("#" + tree.id + '-textfilter').attr("placeholder", tree.initialConfig.search_placeholder);
			
			
			if(!this.isMultiple){
				
				if (!og.tree_focus) og.tree_focus = {};
				
				$("#" + tree.id + '-textfilter').hide();				
				$("#" + tree.id + '-textfilter').closest('.x-panel-tbar').attr("tabindex", -1);
				$("#" + tree.id + '-textfilter').parent().append( "<div id='"+ tree.id +"-current-selected' class='single_current_selected ico-search-m' tabindex='0'><div class='empty-text'>"+tree.getRootNode().text+"</div></div>" );

				$("#" + tree.id + '-textfilter').closest('.x-panel-tbar').focusin(function(e) {
					if (tree.ignoreNextFocus) {
						tree.ignoreNextFocus = false;
						return;
					}

					// check extra filters, if there is any filter depending on another selector then apply it
					tree.checkExtraFiltersAndFilterIfNeeded();
					
					// flag class to check if the input has the focus
					$("#" + tree.id + '-textfilter').addClass("filter-has-focus");
					
					// hide the selection, show the filter and set the focus to the textfilter input 
					$("#" + tree.id + '-textfilter').show();
					$("#" + tree.id + '-textfilter').select();
					$("#" + tree.id + '-current-selected').hide();

					// DO NOT automatically open dropdown on focus - only on specific interactions
				});

				// Event handlers for the single selector display element
				var currentSelected = $("#" + tree.id + '-current-selected');

				/* Use mousedown to prevent focusin from hiding the element */
				currentSelected.mousedown(function(e) {
					tree.ignoreNextFocus = true;
				});

				/* Handle Click to open the dropdown */
				currentSelected.click(function(e) {
					e.preventDefault();

					// Apply logic similar to focusin: check filters, set focus class
					tree.checkExtraFiltersAndFilterIfNeeded();
					$("#" + tree.id + '-textfilter').addClass("filter-has-focus");
					
					// Switch visibility: hide display div, show input
					$("#" + tree.id + '-textfilter').show();
					$("#" + tree.id + '-textfilter').select();
					$("#" + tree.id + '-current-selected').hide();

					// Open the tree dropdown
					tree.showDropdown();
				});

				/* Handle Keyboard on the INPUT (since textfilter receives focus) */
				$("#" + tree.id + '-textfilter').keydown(function(e) {
					// Open dropdown only on specific interaction keys: Space (32), or ArrowDown (40)
					if (e.which === 32 || e.which === 40) {
						tree.showDropdown();
					}
				});
				$("#" + tree.id + '-textfilter').mousedown(function(e) {
					// On input click, open dropdown
					tree.showDropdown();
				});
				
				$("#" + tree.id + '-textfilter').closest('.x-panel-tbar').focusout(function(e) {
					
					// only show selection and hide filter if filter input has lost its focus
					if(!$("#" + tree.id + '-textfilter').hasClass("filter-has-focus")){
						$("#" + tree.id + '-textfilter').hide();
			 			$("#" + tree.id + '-current-selected').show();
				 	}
					
				 	setTimeout(function(){
				 		// hide tree if the tree has lost its focus (selecting a node or clicking outside)
				 		if(!tree.body.hasClass( "have-focus" )){
				 			tree.body.hide();
				 			tree.clearFilter();
				 		}
				 		// after losing focus of the tree remove the flag of the filter so it can be hidden and the current selection can be shown
				 		$("#" + tree.id + '-textfilter').val("");
				 		$("#" + tree.id + '-textfilter').removeClass("filter-has-focus");
				 		
				 	}, 100);
				});
				
				
			}else{
				$("#"+this.tbar.id).focusin(function() {

					// check extra filters, if there is any filter depending on another selector then apply it
					tree.checkExtraFiltersAndFilterIfNeeded();
				});

				var showTreeFn = function() {
					// dont display tree if og.dont_show_tree = tree.dimensionId, sometimes we want to focus in text input and not display tree
					if (!og.dont_show_tree || og.dont_show_tree != tree.dimensionId) {
						// display tree if it's not visible
						tree.showDropdown();
					}
				};

				// Open tree with click
                $("#"+this.tbar.id).click(function() {
                    showTreeFn();
                });
				
				$("#"+this.tbar.id).keydown(function(e) {
					// Open dropdown only on specific interaction keys: Space, or ArrowDown
					if (e.which === 32 || e.which === 40) {
						if ($("#"+tree.body.id).css('display') == 'none') {
							e.preventDefault();
							$(this).focus();
	                        showTreeFn();
						}
					}
				});
				
				$("#"+this.tbar.id).focusout(function() {
				 	setTimeout(function(){
				 		if(!tree.body.hasClass( "have-focus" )){
				 			tree.body.hide();
				 			tree.clearFilter();
				 			
				 			$("#" + tree.id + '-textfilter').val("");
				 		}
				 	}, 300);			 	
				});
				
			}
			
			// same events for both kind of trees, mark with "have-focus" class when the tree is displayed and remove this flag class when the focus is lost
			var cont = $("#"+this.body.id);
			cont.attr("tabindex", -1);
			cont.focusin(function() {
				cont.addClass("have-focus");
			});
			cont.focusout(function() {
				cont.removeClass("have-focus");
				setTimeout(function(){
					tree.body.hide();
					tree.clearFilter();
					$("#" + tree.id + '-textfilter').val("");
				}, 200);
			});
		}
	});
	
	this.getSelectionModel().on({		
		selectionchange : function(sm, selection) {
			
		},
		scope:this // Con esto this referencia al TreeNode. Sino al SelModel
	});
			
	// **************** TREE INIT **************** //
	this.initialized = false;
	var root_lang = lang('none');
	if (config.root_lang) {
		root_lang = config.root_lang;
	}
	var root = new Ext.tree.TreeNode({
		text: root_lang,
		expandable: true,
		hidden: true,
    	id:0,
    	href: "#",
    	iconCls : 'ico-folder',
    	cls: 'root'
	});

	this.setRootNode(root);
};

Ext.extend(og.MemberTreeAjax, Ext.tree.TreePanel, {

	// ******* ATTRIBUTES ******** //
	
	filterOnChange: true,

	/**
	 * Shows the dropdown list of the tree.
	 */
	showDropdown: function(){
		var tree = this;

		if ($("#"+tree.body.id).css('display') == 'none') {
			setTimeout(function() {
				tree.body.show();

				tree.positionDropdown();

				tree.adjustDropDownListHeight();

				if(!tree.initialized || tree.totalNodes == 0){
					if (!tree._initPending) {
						tree._initPending = true;
						tree.init();
					}
				}
			}, 300);
		}
	},

	/**
	 * Loads the tree on first use, then applies the pending filter.
	 * Avoids searching before nodes are available (first keystroke showed nothing).
	 */
	_ensureInitializedAndFilter: function(text, from_server) {
		if (!this.initialized || this.totalNodes == 0) {
			this._pendingFilter = { text: text, from_server: from_server };
			if (!this._initPending) {
				this._initPending = true;
				this.init();
			}
			return;
		}
		this.filterTree(text, from_server);
	},

	_applyPendingFilterIfAny: function() {
		if (!this._pendingFilter) {
			return;
		}
		var pending = this._pendingFilter;
		this._pendingFilter = null;
		this.filterTree(pending.text, pending.from_server);
	},

	onInitialLoadComplete: function() {
		if (!this._initPending) {
			return;
		}
		this._initPending = false;
		this._applyPendingFilterIfAny();
	},

	_hasVisibleFilterMatches: function() {
		var found = false;
		var walk = function(n) {
			if (found || !n) {
				return;
			}
			if (n.getDepth() > 0) {
				var el = n.getUI().getEl();
				if (el && el.style.display !== 'none') {
					found = true;
					return;
				}
			}
			var c = n.firstChild;
			while (c) {
				walk(c);
				c = c.nextSibling;
			}
		};
		walk(this.getRootNode());
		return found;
	},
	
	filterTree: function(text, from_server) {
		if(from_server == undefined){
			var from_server = true;
		}

		if (text == this.getTopToolbar().items.get(this.id + '-textfilter').emptyText) {
			text = "";
		}
		if (text.trim() == '') {
			this.clearFilter();
		} else {
			var re = new RegExp(Ext.escapeRe(text.toLowerCase()), 'i');

			if(from_server){
				//search on server
				this.innerCt.mask();
				var tree_id = this.id;
				var searched_text = text;
				var search_time = new Date().getTime();
				this.tbar.last_search_time = search_time;

				var options = {
					dimension_id:this.dimensionId,
					query:Ext.escapeRe(text.toLowerCase()),
					ignore_context_filters: true,
					tree_id: tree_id,
					time: search_time
				};
				if (this.initialConfig.filter_by_ids) {
					options.filter_by_ids = this.initialConfig.filter_by_ids;
				}

				let extra_filters = og.getMemberTreeExtraFilters(this);
				if (extra_filters) {
					options.extra_filters = extra_filters;
				}

				og.openLink(og.getUrl('dimension', 'search_dimension_members_tree', options), {
	    			hideLoading:true,
	    			hideErrors:true,
	    			callback: function(success, data){

	    				var dimension_tree = Ext.getCmp(tree_id);
	    				if (!dimension_tree) {
	    					return;
	    				}

	    				dimension_tree.innerCt.unmask();

	    				if (!success || !data || dimension_tree.tbar.last_search_time != data.time) {
	    					return;
	    				}

	    				//add nodes to tree
	    				if (data.members) {
	    					dimension_tree.addMembersToTree(data.members, data.dimension_id);
	    				}

					// Only record the searched text in history after a confirmed server response.
					if (searched_text.trim() != '' && dimension_tree.tbar.history) {
						dimension_tree.tbar.history.prevTextFilters.push(searched_text);
					}

	    				//get the text from the filter
		    			var search_text = dimension_tree.getTopToolbar().items.get(dimension_tree.id + '-textfilter').el.getValue();
		    			var re_search_text = new RegExp(Ext.escapeRe(search_text.toLowerCase()), 'i');

	    				//filter the tree
	    				dimension_tree.filterNode(dimension_tree.getRootNode(), re_search_text);
	    				dimension_tree.suspendEvents();
	    				dimension_tree.expandAll();
	    				dimension_tree.resumeEvents();
	    			}
	    		});
			}else{
	    		//filter the tree
	    		this.filterNode(this.getRootNode(), re);
	    		if (!this._hasVisibleFilterMatches()) {
	    			this.filterTree(text, true);
	    			return;
	    		}
	    		this.suspendEvents();
	    		this.expandAll();
	    		this.resumeEvents();
	    	}	
		}
	},
	
	filterByMember: function(memberIds, nodeClicked, callback, options) {

		// Invalidate history: all nodes are being removed, so any previously
		// cached server results are gone and must be re-fetched.
		this.tbar.history = undefined;

		// remove all nodes
		while (n = this.getRootNode().childNodes[0]) {
			this.getRootNode().removeChild(n);
		}
		
		if (!options) options = {};
		
		options.dimension_id = this.dimensionId;
		options.selected_ids = Ext.util.JSON.encode(memberIds);
		options.avoid_session = 1;
		options.node_clicked = nodeClicked ? (isNaN(nodeClicked) ? nodeClicked.id : nodeClicked) : null;
		
		if (this.initialConfig.filter_by_ids) {
			options.filter_by_ids = this.initialConfig.filter_by_ids;
		}

		let extra_filters = og.getMemberTreeExtraFilters(this);
		if (extra_filters) {
			options.extra_filters = extra_filters;
		}
		
		// load filtered tree
		og.initialMemberTreeAjaxLoad(this, 500, 0, options);
		
	},
	
	filterNode: function(n, re) {
		
		var f = false;
		var c = n.firstChild;
		while (c) {
			f = this.filterNode(c, re) || f;
			c = c.nextSibling;
		}
		f = re.test(Ext.util.Format.htmlDecode(n.text.toLowerCase())) || f;
		if (!n.previousState) {
			// save the state before filtering
			n.previousState = n.expanded ? "e" :"c";
		}
		if (f) {
			n.getUI().show();
		} else {
			n.getUI().hide();
		}
		return f;
	},
	
	clearFilter: function(n) {
		if (!n) n = this.getRootNode();
		if (!n.previousState) return;
		var c = n.firstChild;
		while (c) {
			this.clearFilter(c);
			c = c.nextSibling;
		}
		n.getUI().show();
		if (n.previousState == "e") {
			n.expand(false, false);
		} else if (n.previousState == "c") {
			n.collapse(false, false);
		}
		n.previousState = null;
	},
	
	
	
	getExpandedNodes: function () {
		nodes = [];
		nodes = nodes.concat( this.root.expandedNodes() );
		return nodes ;
	},
	
	init: function ( callback  ) {
		// ensure the correct member sort by name by using the accent replace function before the comparison
		new Ext.tree.TreeSorter(this, {
		    dir: "asc",
		    property: "text",
		    sortType: function(node) {
		    	// if node is the "view more" node, it must be the last one 
		    	if (isNaN(node.id) && node.id.indexOf('view_more_') == 0) {
		    		var last_char = String.fromCharCode(126);
		    		// let text start with last char in order to set this node as the last one
		    		return last_char + last_char + last_char + node.text;
		    	}
		    	if (node.attributes && node.attributes.sort_key) {
		    		return og.replaceStringAccents(node.attributes.sort_key).toLowerCase();
		    	} else if (node.sort_key) {
		    		return og.replaceStringAccents(node.sort_key).toLowerCase();
		    	} else {
		    		return og.replaceStringAccents(node.text).toLowerCase();
		    	}
		    }
		});
		
		switch (this.expandMode) {
			case "all":
				this.expandAll(callback);
				break;
			case "root":
				this.root.expand(0,0,callback) ;
				break;
			case "none": default : // Not expand ?
				break;
		}
				
		this.root.enable();
		this.totalNodes = 1;
		
		var filtering_by_ids = this.initialConfig.filter_by_ids;
		
		let use_cache = ogMemberCache.areDimRootMembersLoaded(this.dimensionId.toString()) && !filtering_by_ids;

		let extra_filters = og.getMemberTreeExtraFilters(this);

		if (extra_filters) {
			use_cache = false;
		}

		if(use_cache){
			var dim = og.dimensions[this.dimensionId];
			if(typeof dim != "undefined"){
				for (m in dim) {
					mem = dim[m];
					
					if(typeof this.allowedMemberTypes != "undefined"){
						if(this.allowedMemberTypes.indexOf(mem.object_type_id) == -1){
							continue;
						}
					}

				    var new_node = this.createNode(mem);
				    		    
				    var node_parent = this.getNodeById(mem.parent);
				    if(mem.parent == 0){
				    	node_parent = this.getRootNode();
				    }
				    var node_exist = this.getNodeById(mem.id);
					if(!node_exist){
						if (node_parent) node_parent.appendChild(new_node);
					}
				    
				}
			}
			
			this.initialized = true;
			this.onInitialLoadComplete();
			og.eventManager.fireEvent('end_callback member_tree loaded', {});
		}else{
			
			var options = {};
			if (filtering_by_ids) {
				options.filter_by_ids = filtering_by_ids;
			}
			var limit = 500;
			if (og.config.member_selector_page_size) {
				limit = og.config.member_selector_page_size;
			}
			if (extra_filters) {
				options.extra_filters = extra_filters;
			}
			og.initialMemberTreeAjaxLoad(this, limit, 0, options);
		}
	} ,

	// ******* METHODS ******** //
	
	isMultiple: function() {
		return ( this.getSelectionModel().constructor.toString().indexOf("Array") != -1 );
	},
	
	selectRoot: function() {
		selModel = this.getSelectionModel() ;
		selModel.suspendEvents();
		var node = this.getRootNode() ;
		selModel.select(node) ;
		
		selModel.resumeEvents();

	},
	
	hide: function() {
		og.MemberTreeAjax.superclass.hide.call(this);		
	},
	show: function() {
		og.MemberTreeAjax.superclass.show.call(this);		
		this.selectRoot();
	}, 

	selectNodes: function(nids) {
		for (var i = 0 ; i < nids.length ; i++ ) {
			if ( nids[i] != "undefined" ) {
				if ( nids[i] != 0 ) {
					var node = this.getNodeById(nids[i]) ;
				}else{
					var node = this.getRootNode();
				}
				if (node) {
					selModel = this.getSelectionModel() ;
					selModel.suspendEvents();
					selModel.select(node) ;
					selModel.resumeEvents();
				}
			} 
		}
	
	},
	
	expandNodes: function (nids, callback) {
		
		for (var i = 0 ; i < nids.length ; i++ ) {
			if ( nids[i] != "undefined" ) {
				if ( nids[i] != 0 ) {
					var node = this.getNodeById(nids[i]) ;
					
				}else{
					var node = this.getRootNode();
				}
				if (node) {
					node.expand();
				}
			} 
		}
		
		
	},
	
	hideRoot: function () {
		this.addClass("root-hidden");
	},
	
	showRoot: function () {
		this.removeClass("root-hidden");
	},
	
	addMembersToTree: function(members,dimension_id) {
    	var all_parent_nodes_involved = [];
		var dimension_tree = this;
		for (var prop in members) {  
			var mem = members[prop];
			if (typeof mem == 'function') continue;

			if(typeof dimension_tree.allowedMemberTypes != "undefined"){
				if(dimension_tree.allowedMemberTypes.indexOf(mem.object_type_id) == -1){
					continue;
				}
			}
			
		    var new_node = dimension_tree.loader.createNode(mem);
			
		    var node_parent = dimension_tree.getNodeById(mem.parent);
		    if(mem.parent == 0){
		    	node_parent = dimension_tree.getRootNode();
		    }
		    var node_exist = dimension_tree.getNodeById(mem.id);

			if (node_parent) {
				dimension_tree.suspendEvents();
				if (node_exist) {
					// remove old node if already there
					node_parent.removeChild(node_exist);
				}
				// add the new node
				node_parent.appendChild(new_node);
				dimension_tree.resumeEvents();

				// add the parent to the array of parents to sort
				all_parent_nodes_involved[node_parent.id] = node_parent;
			}

			//add member to og.dimensions
			og.addMemberToOgDimensions(dimension_id,mem);						
		}
        
        // sort child nodes alphabetically
        for (var i in all_parent_nodes_involved) {
        	var pn = all_parent_nodes_involved[i];
        	if (pn && typeof(pn) == 'object') {
        		pn.sort(og.sortNodesFn);
        	}
        }
	},
	
	createNode: function (attr) {
		if (  Ext.type(this ) ){	
			if (this.totalNodes) {
				this.totalNodes++ ;
			}else{
				this.totalNodes = 1;
			}
		}
		
		var node = attr.leaf ?
	          new Ext.tree.TreeNode(attr) :
	            new Ext.tree.AsyncTreeNode(attr);
                       
       
		node.object_id = attr.object_id ;
		node.options = attr.options ;
		node.object_controller = attr.object_controller ;
		node.object_type_id = attr.object_type_id ;
		node.allow_childs = attr.allow_childs ;
        
		if (attr.actions){
			node.actions = attr.actions ;
		}
        
        return node ;            
        
	},


	// places the dropdown list below the search input, or above it if it does not fit below
	positionDropdown: function() {
		var tbar_el = $("#" + this.tbar.id);
		var list_el = $("#" + this.body.id);

		var tbar_top = tbar_el.offset().top;
		var top = tbar_top + tbar_el.height();

		// inside modal forms the list is resized by adjustDropDownListHeight, don't move it up
		if ($(".simplemodal-overlay").length == 0) {
			var list_height = list_el.outerHeight();

			// not enough room below and enough room above => render the list upwards
			if (top + list_height > $(window).height() && tbar_top > list_height) {
				top = tbar_top - list_height;
			}
		}

		list_el.css({top: top + 'px'});
	},

	// if we are in a modal form then ensure the whole dropdown list is visible
	adjustDropDownListHeight: function() {
		
		if ($(".simplemodal-overlay").length > 0) {
			let winh = $(".simplemodal-overlay").height();
			let list = this.body;
			
			// if a part of the list is below the end of the screen then change the height and the top if necessary
			if (list.getTop() + list.getHeight() > winh) {

				let max_height = winh - list.getTop() - 5;
				let minimum_heigth = 200;
				let new_top = 0;

				// set a min height so the list remains usable, if still hidden then move the list up
				if (max_height < minimum_heigth) {
					new_top = list.getTop() + max_height - minimum_heigth
					max_height = minimum_heigth;
				}

				// make the list smaller
				$("#"+list.id).css({'max-height': max_height+'px'});

				// if the list is still hidden then move it up
				if (new_top) {
					$("#"+list.id).css({'top': new_top+'px', 'border-top': '1px solid #cccccc'});
				}
			}
		}
	},

	/**
	 * Check if there are any extra filters and if so then filter the tree by them.
	 * If the extra filters have changed since last time, then filter the tree.
	 */
	checkExtraFiltersAndFilterIfNeeded: function() {
		
		let extra_filters = og.getMemberTreeExtraFilters(this);
		if (extra_filters && extra_filters != "") {
			let has_child_of_filter = false;
			let has_chained_association_filter = false;
			let efilters = JSON.parse(extra_filters);

			// check if this selector has a "child_of" filter
			if (typeof efilters == 'object') {
				for (var i = 0; i < efilters.length; i++) {
					let f = efilters[i];
					if (f.property_id == 'child_of') {
						has_child_of_filter = true;
					} else if (f.property_id == 'chained_association') {
						has_chained_association_filter = true;
					}
				}
			}

			// if this selector has a "child_of" filter then filter the tree by it
			if (has_child_of_filter || has_chained_association_filter) {
				// only reload the tree if the extra filters have changed
				if (!this.last_extra_filters || this.last_extra_filters != extra_filters) {
					this.last_extra_filters = extra_filters;
					this.filterByMember([]);
				}
			}

		}
	},
	
	
});


// ***** EXTJS REGISTER COMPONENT ******* //
Ext.reg('member-tree-ajax', og.MemberTreeAjax);




