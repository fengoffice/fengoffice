
// ***** tree  ***** //
og.MemberTree = function(config) {

	var tbar = [{
		xtype: 'textfield',
		id: config.id + '-textfilter',
		cls: "dimension-panel-textfilter" ,
		emptyText:lang('filter members'),
		listeners:{
			render: {
				fn: function(f){
					f.el.on('keyup', function(e) {
						var from_server = true;
						
						//check history date
						if(this.tbar.history != undefined){
							var now = new Date();

							// Calculate the difference in milliseconds
							var timeDiff = now.getTime() - this.tbar.history.date.getTime();
							
							// convert to minutes
							timeDiff = timeDiff/(1000*60);
							
							// refresh history after 10 minutes
							if(timeDiff > 10){
								this.tbar.history = undefined;
							}						
						}

						//create history search for the searchs that we get from the server
						if(this.tbar.history == undefined){
							this.tbar.history = {prevTextFilters: [], date: new Date()};
						}

						//search on the server only if the current text is not on the history
						//or if we already search a text with the same start
						if(this.tbar.history.prevTextFilters.length > 0){
							for (var i = 0 ; i < this.tbar.history.prevTextFilters.length ; i++) {
								var prevTextFilter = this.tbar.history.prevTextFilters[i] ;

								//the text is on the history?
								if(e.target.value.indexOf(prevTextFilter) == 0){
									from_server = false;
								}
							}							
						}


						this.filterTree(e.target.value, from_server);
					},
					this, {buffer: 350});
				},
				scope: this
			}
		}
	}];
	
	//show only active Object Type c_names to over new button
	var name_to_over = "";
	for (key in og.dimension_object_type_descendants[config.dimensionId]){
		if (parseInt(key) > 0 && og.objectTypes[key]){
			if ( ['project_folder','customer_folder'].indexOf(og.objectTypes[key].name) == -1 ){
				name_to_over += og.objectTypes[key].c_name + ', ';
			}
		}			
	}

	name_to_over = name_to_over.slice(0, -2);
	var show_over_button = name_to_over != '' ? lang('add a new custom member in', name_to_over) : '';
	
	var load_url = 'index.php?c=dimension&a=initial_list_dimension_members_tree_root&ajax=true&dimension_id='+config.dimensionId+'&avoid_session=1';
	if (config.loadUrl) {
		load_url = config.loadUrl;
	}
	load_url += (og.config.member_selector_page_size ? '&limit='+og.config.member_selector_page_size : '');

	var expandM = 'root';
	if(config.hidden) expandM = 'none';
	Ext.applyIf(config, {
		region: 'center',
		id: config.id,
		loader: new og.MemberChooserTreeLoader({
    		dataUrl: load_url,
    		ownerTree: this  
    	}),
		autoScroll: true,
		//rootVisible: false,
		root: {
        	text: config.root_node_text ? config.root_node_text : lang('view all'),
        	id:0,
        	href: "#",
        	iconCls : 'root',
        	cls: 'root'
    	},
    	enableDrop: true,
    	ddGroup: 'MemberDD',
		collapseFirst: false,
		collapsible: true,
    	selModel: (config.multipleSelection)? new Ext.tree.MultiSelectionModel() : new Ext.tree.DefaultSelectionModel(),
    	dimensionId: config.dimensionId,
    	dimensionCode: config.dimensionCode, 
    	cls: config.dimensionCode,
    	reloadHidden: false, //To force tree reload when is hidden 
    	height: 210,
    	animate: false,
    	tools: [
	       {
	    	   id: 'toggle',
	    	   handler : function(e,t,p){
	    		   p.toggleCollapse();
	    	   },
	    	   hidden: !og.preferences['can_modify_navigation_panel']
	       }, 
    	   {
    		   id: 'options',
    		   qtip: show_over_button,
    		   handler: function(e,t,p){
    			   og.quickForm({ dimensionId: p.dimensionId,type: 'member', treeId: p.dimensionId, elId: t.id});	    	   		
	       		}
    	    }

    	], 
    	toolTemplate: new Ext.XTemplate(
        '<tpl if="id==\'options\'">',
			'<buton type="" class="btn btn-xs btn-primary x-tool x-tool-{id}"><div class="x-tool x-tool-{id}-ico">&#160;</div>'+lang("new")+'</buton>',
        '</tpl>',
        '<tpl if="id!=\'options\'">',
            '<div class="x-tool x-tool-{id}">&#160;</div>',
        '</tpl>'
    	), 	
    	hideCollapseTool: true ,
    	expandMode: expandM, //all root,
    	tbar: tbar 
	});
	
	config.initialLoader = config.loader;
	if (!config.listeners) config.listeners = {};
	Ext.apply(config.listeners, {
		beforenodedrop: function(e) {
			if (this.disable_default_events) return;
			
			if (!isNaN(e.target.id) && e.data.grid) {
				
				var has_relations = false;
				var ids = [];
				for (var i=0; i<e.data.selections.length; i++) {
					if (isNaN(e.data.selections[i].data.object_id)) continue;
					ids.push(e.data.selections[i].data.object_id);
					if (!has_relations) {
						var mpath = Ext.util.JSON.decode(e.data.selections[i].data.memPath);
						if (mpath && mpath[config.dimensionId]) has_relations = true;
					}
				}

				function selectionHasAttachments() {
					if (Ext.getCmp('mails-manager') != undefined) {
						var sm = Ext.getCmp('mails-manager').getSelectionModel();
						var selections = sm.getSelections();
						if (selections.length <= 0) {
							return false;
						} else {
							for ( var i = 0; i < selections.length; i++) {
								if (selections[i].data.hasAttachment || selections[i].data.conv_hasatt) {
									return true;
								}
							}
							sm.clearSelections();	
							return false;
						}
					} else {
						return false;
					}

				}
				this.selectionHasAttachments = selectionHasAttachments;
				
				var first_selected_row = null;
				for (var j=0; j<e.data.selections.length; j++) {
					var sel_row = e.data.selections[j];
					if (!sel_row) continue;
					if (sel_row.id != 'quick_add_row' && sel_row.id != '#__total_row__' && sel_row.data.ot_id) {
						first_selected_row = sel_row;
						break;
					}
				}

				if (first_selected_row && first_selected_row.data && e.target && 
						og.dimension_object_type_contents[config.dimensionId] &&
						og.dimension_object_type_contents[config.dimensionId][e.target.object_type_id] &&
						og.dimension_object_type_contents[config.dimensionId][e.target.object_type_id][first_selected_row.data.ot_id] &&
						og.dimension_object_type_contents[config.dimensionId][e.target.object_type_id][first_selected_row.data.ot_id].multiple) {


					if (this.selectionHasAttachments() && e.target.id) {
						if (og.preferences['mail_drag_drop_prompt'] == 'prompt') {
							var attachment = confirm(lang('do you want to classify the unclassified emails attachments', config.title)) ? "1" : "0";
						} else if (og.preferences['mail_drag_drop_prompt'] == 'classify') {
							var attachment = 1;
						} else if (og.preferences['mail_drag_drop_prompt'] == 'dont') {
							var attachment = 0;
						}
					}

					
					// Show modal form if preferences is prompt
					if (og.preferences['drag_drop_prompt'] == 'prompt') {
						
						var div = document.createElement('div');
						var question = lang('do you want to mantain the current associations of this obj with members of', config.title);
						div.style = "border-radius: 5px; background-color: #fff; padding: 10px; width: 500px;";
						var genid = Ext.id();
						div.innerHTML = '<div><label class="coInputTitle">'+lang('classification')+'</label></div>'+
							'<div id="'+genid+'_question">'+ question+'</div>'+
							'<div id="'+genid+'_buttons">'+
							'<button class="replace submit blue">'+lang('replace with the new one')+'</button><button class="keep submit blue">'+lang('keep both current and new')+'</button>'+
							'</div><div class="clear"></div>';

						var modal_params = {
							'escClose': false,
							'overlayClose': false,
							'closeHTML': '<a id="'+genid+'_close_link" class="modal-close" title="'+lang('close')+'"></a>',
							'onShow': function (dialog) {
								$("#"+genid+"_close_link").addClass("modal-close-img");
								$("#"+genid+"_buttons").css('text-align', 'right').css('margin', '10px 0');
								$("#"+genid+"_question").css('margin', '10px 0');
								$("#"+genid+"_buttons button.replace").css('margin-right', '10px').click(function(){

									og.call_add_objects_to_member(e, ids, e.target.id, attachment, true, true);
									$('.modal-close').click();
								});
								$("#"+genid+"_buttons button.keep").css('margin-right', '10px').click(function(){
									
									og.call_add_objects_to_member(e, ids, e.target.id, attachment, true, false);
									$('.modal-close').click();
								});
						    }
						};
						setTimeout(function() {
							$.modal(div, modal_params);
						}, 100);
						
					} else {
						// Here don't show any modal form, just call the reclassify function
						
						if (og.preferences['drag_drop_prompt'] == 'move') {
							var rm_prev = 1 ;
						} else if (og.preferences['drag_drop_prompt'] == 'keep') {
							var rm_prev = 0 ;
						}
						og.call_add_objects_to_member(e, ids, e.target.id, attachment, true, rm_prev);
					}
					// -------------
					
					
				} else {
					
					if (this.selectionHasAttachments() && e.target.id) {
						if (og.preferences['mail_drag_drop_prompt'] == 'prompt') {
							var attachment = confirm(lang('do you want to classify the unclassified emails attachments', config.title)) ? "1" : "0";
						} else if (og.preferences['mail_drag_drop_prompt'] == 'classify') {
							var attachment = 1;
						} else if (og.preferences['mail_drag_drop_prompt'] == 'dont') {
							var attachment = 0;
						}
					}
					
					var dim_id = e.target.attributes.dimension_id;
					
					// Ask if user also wants to reclassify the object in the associated dimension members
					if (og.dimension_member_associations[dim_id] && og.dimension_member_associations[dim_id][e.target.object_type_id] && og.dimension_member_associations[dim_id][e.target.object_type_id].length > 0) {

						var obj_type_name = 'object';
						if (e.data && e.data.grid && e.data.grid.type_name) {
							obj_type_name = lang(e.data.grid.type_name);
						}
						var mem_type_name = og.objectTypes[e.target.object_type_id].c_name;
						var assoc_dim_names = '';
						for (var i=0; i<og.dimension_member_associations[dim_id][e.target.object_type_id].length; i++) {
							var assoc = og.dimension_member_associations[dim_id][e.target.object_type_id][i];
							assoc_dim_names += (assoc_dim_names == '' ? '' : ', ') + assoc.name;
						}
						
						var div = document.createElement('div');
						var question = lang('do you want to reclassify in memtype associated dimensions', obj_type_name, mem_type_name, assoc_dim_names);
						div.style = "border-radius: 5px; background-color: #fff; padding: 10px; width: 400px;";
						var genid = Ext.id();
						div.innerHTML = '<div><label class="coInputTitle">'+lang('classification in associated members')+'</label></div>'+
							'<div id="'+genid+'_question">'+ question+'</div>'+
							'<div id="'+genid+'_buttons">'+
							'<button class="yes submit blue">'+lang('yes')+'</button><button class="no submit blue">'+lang('no')+'</button>'+
							'</div><div class="clear"></div>';

						var modal_params = {
							'escClose': false,
							'overlayClose': false,
							'closeHTML': '<a id="'+genid+'_close_link" class="modal-close" title="'+lang('close')+'"></a>',
							'onShow': function (dialog) {
								$("#"+genid+"_close_link").addClass("modal-close-img");
								$("#"+genid+"_buttons").css('text-align', 'right').css('margin', '10px 0');
								$("#"+genid+"_question").css('margin', '10px 0');
								$("#"+genid+"_buttons button.yes").css('margin-right', '10px').click(function(){

									og.call_add_objects_to_member(e, ids, e.target.id, attachment, true);
									$('.modal-close').click();
								});
								$("#"+genid+"_buttons button.no").css('margin-right', '10px').click(function(){
									
									og.call_add_objects_to_member(e, ids, e.target.id, attachment);
									$('.modal-close').click();
								});
						    }
						};
						setTimeout(function() {
							$.modal(div, modal_params);
						}, 100);
						
					} else {
						// if member has no associations then directly call to the reclassify function
						og.call_add_objects_to_member(e, ids, e.target.id, attachment);
					}
					
				}
			} else {
				// if is root node => unclassify
				if (e.target.getDepth() == 0) {
					var has_relations = false;
					var ids = [];
					for (var i=0; i<e.data.selections.length; i++) {
						ids.push(e.data.selections[i].data.object_id);
						if (!has_relations) {
							var mpath = Ext.util.JSON.decode(e.data.selections[i].data.memPath);
							if (mpath && mpath[config.dimensionId]) has_relations = true;
						}
					}

					og.openLink(og.getUrl('member', 'add_objects_to_member'),{
						method: 'POST',
						post: {objects: Ext.util.JSON.encode(ids), dimension: e.target.getOwnerTree().dimensionId},
						callback: function(){
							e.data.grid.load();
						}
					});
				}
			}
			return false;
		}
    });

	og.MemberTree.superclass.constructor.call(this, config);
	
	var self = this ; // To change scope inside callbacks	

	// ********** TREE EVENTS *********** //
	this.on({
		expandnode: function(node){
			if (node && isNaN(node.id) && node.id.indexOf('view_more_') >= 0) {
				return;
			}
			//get childs from server
	        if(node.childNodes.length < node.attributes.realTotalChilds && node.attributes.expandable && !node.attributes.gettingChildsFromServer){
	        	node.ownerTree.innerCt.mask();
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
					tree_id: this.id,
					offset: node.last_childs_offset,
					ignore_context_filters: !this.filterOnChange,
					context: og.contextManager.plainContext()
				};
				
	        	og.openLink(og.getUrl('dimension', 'get_member_childs', parameters), {
	    			hideLoading:true, 
	    			hideErrors:true,
	    			callback: function(success, data){
	    				//var dimension_tree = Ext.getCmp('dimension-panel-'+data.dimension);
	    				var dimension_tree = Ext.getCmp(data.tree_id);
	    				if (dimension_tree) {
		    				dimension_tree.addMembersToTree(data.members, data.dimension);
		    				
		    				if (data.more_nodes_left) {
		    					og.addViewMoreNode(node, node.ownerTree.id, og.ajaxMemberTreeViewMoreCallback);
		    				} else {
		    					var old_view_more_node = dimension_tree.getNodeById('view_more_' + node.id);
		    					if (old_view_more_node) old_view_more_node.remove();
		    				}
		    				 				
		    				dimension_tree.innerCt.unmask();
		    				
		    				var current_node = dimension_tree.getNodeById(data.member_id);
		    				if (current_node) current_node.attributes.gettingChildsFromServer = false;
	    				}
	    			}
	    		});
	        }else{
	        	//ensure show childs
	        	for (var i = 0 ; i < node.childNodes.length ; i++) {
						var child = node.childNodes[i];
						child.getUI().show();
				}
	        }
			
		},
		click: function(node, e){
			if (this.disable_default_events) return;
			
			if (node && isNaN(node.id) && node.id.indexOf('view_more_') >= 0) {
				return;
			}
			
			og.contextManager.currentDimension = self.dimensionId ;
			og.eventManager.fireEvent("member tree node click", node);
			var treeConf = node.attributes.loader.ownerTree.initialConfig ;
			if  (node.getDepth() == 0 ){
				
				// clean context for this dimension
				og.contextManager.cleanActiveMembers(this.dimensionId);
				
				// Manage dashboard
				if ( treeConf.dimensionOptions.defaultAjax ){
					var controller =  treeConf.dimensionOptions.defaultAjax.controller ;
					var action =  treeConf.dimensionOptions.defaultAjax.action ;
					if ( controller && action ) {
						og.customDashboard(controller, action, {}, true);
					}
				}
				
				// Fire 'all' selection for related trees
				var trees = this.ownerCt.items;
				if (trees){
					trees.each(function (item, index, length){
						var must_reload = false;
						if (self.reloadDimensions) {
							for (ot_id in self.reloadDimensions) {
								if (self.reloadDimensions[ot_id] && typeof(self.reloadDimensions[ot_id].indexOf) == 'function'
									&& self.reloadDimensions[ot_id].indexOf(item.dimensionId) != -1) {
										must_reload = true;
								}
							}
						}
						
						if ( self.id != item.id  && (!item.hidden ||item.reloadHidden) && must_reload ) {
							
							item.getRootNode().suspendEvents();
							item.getRootNode().select();
							item.getRootNode().resumeEvents();
							
						}
					});
				}
			}else{
				// Member selection (not root)
				if ( node.options && node.options.defaultAjax && node.options.defaultAjax.controller && node.options.defaultAjax.action) {
					var reload = ( this.getSelectionModel() && this.getSelectionModel().getSelectedNode() && this.getSelectionModel().getSelectedNode().id  ==  node.id );

					if (og.contextManager.getDimensionMembers(this.dimensionId).indexOf(node.id) == -1) {
						og.customDashboard( node.options.defaultAjax.controller, node.options.defaultAjax.action, {id: node.object_id}, reload);
					}
				  
				} else {
					og.resetDashboard();
				}
			
			}
			
			if (node.getDepth() > 0) { 	
				//set focus on the selected node
				node.ownerTree.suspendEvents();		        
				node.ensureVisible();
				
				node.select();
				node.expand();
				node.ownerTree.resumeEvents();
								
				//get childs from server
		        if(node.childNodes.length < node.attributes.realTotalChilds && node.attributes.expandable && !node.attributes.gettingChildsFromServer){
		        	node.ownerTree.innerCt.mask();
		        	node.attributes.gettingChildsFromServer = true;
		        	og.openLink(og.getUrl('dimension', 'get_member_childs', {member:node.id, ignore_context_filters: !this.filterOnChange, tree_id:this.id}), {
		    			hideLoading:true, 
		    			hideErrors:true,
		    			callback: function(success, data){
		    				
		    				//var dimension_tree = Ext.getCmp('dimension-panel-'+data.dimension);
		    				var dimension_tree = Ext.getCmp(data.tree_id);
		    				
		    				dimension_tree.addMembersToTree(data.members, data.dimension);
		    				 
		    				var current_node = dimension_tree.getNodeById(data.member_id);
		    				current_node.attributes.gettingChildsFromServer = false;
		    				
		    				dimension_tree.innerCt.unmask();    						    				
		    			}
		    		});
		        }else{
		        	//ensure show childs
		        	for (var i = 0 ; i < node.childNodes.length ; i++) {
							var child = node.childNodes[i];
							child.getUI().show();
					}
		        }		        		       				
			}
		},
		dblclick: function(node, e){
			if (this.disable_default_events) return;
			
			og.contextManager.currentDimension = self.dimensionId;
			og.eventManager.fireEvent("member tree node dblclick", node);
			var treeConf = node.attributes.loader.ownerTree.initialConfig;
			if  (node.getDepth() > 0 && node.actions && node.actions.length > 0){
				// Member clicked (not root)
				for (var i=0; i<node.actions.length; i++) {
					var action = node.actions[i];
					if (action['class'] == 'action-edit' && action.url) {
						og.render_modal_form('', {url:action.url});
						return;
					}
				}
			
			}
		}
	});
	
	this.getSelectionModel().on({
		
		selectionchange : function(sm, selection) {
			if (this.disable_default_events) return;
			
			if (selection && isNaN(selection.id) && selection.id.indexOf('view_more_') >= 0) {
				return;
			}
			if (selection && !this.pauseEvents) {
				var selection_changed = og.contextManager.getDimensionMembers(this.dimensionId).indexOf(selection.id) == -1;
				og.contextManager.cleanActiveMembers(this.dimensionId) ;
				if ( ! this.isMultiple() ){
					// Single Selection
					var node = selection ; 
					if (node.getDepth()) {
						var member = node.attributes.id ;
						if(node.attributes.allow_childs) {
							$('#'+this.id + " .member-quick-form-link").show();
						}else{
							$('#'+this.id + " .member-quick-form-link").hide();
						}
					}else{
						$('#'+this.id + " .member-quick-form-link").show();
						var member = 0 ; 
					}
					
					if (!this.hidden) {
						og.contextManager.addActiveMember(member, this.dimensionId, node );
					}
					if ( this.filterOnChange ) {
						var trees = this.ownerCt.items;
						if (trees){
							this.suspendEvents();
							this.totalFilterTrees = 0 ;
							this.filteredTrees = 0;
							
							var selected_members = [];
							if (!og.resettingAllTrees) {
								trees.each(function (item, index, length){
									var sel = item.getSelectionModel().getSelectedNode();
									if (sel && !isNaN(sel.attributes.id)) selected_members.push(sel.attributes.id);
								});
							}
							
							trees.each(function (item, index, length){
								var must_reload = false;
								if (self.reloadDimensions && self.reloadDimensions[node.object_type_id]) {
									for (var k=0; k<self.reloadDimensions[node.object_type_id].length; k++) {
										var reload_dim_id = parseInt(self.reloadDimensions[node.object_type_id][k]);
										if (reload_dim_id == parseInt(item.dimensionId)) {
											must_reload = true;
											break;
										}
									}
								}
								
								if ( self.id != item.id  && (!item.hidden ||item.reloadHidden) && (must_reload || item.is_filtered_by)) {
									// Filter other Member Trees
									self.totalFilterTrees++;
									
									if (item.disableReloadOtherDimensions) {
										item.disableReloadOtherDimensions = false;
									} else {
										var n = og.resettingAllTrees ? item.getRootNode() : node;
										
										item.filterByMember(selected_members, n, function(){
											self.filteredTrees++;
											if (self.filteredTrees == self.totalFilterTrees) {
												self.resumeEvents();
												og.eventManager.fireEvent('member trees updated', n);
											}
										});
										
									}
									
									// register that this tree has been filtered, so if any other node is selected this has to be reloaded despite of having no associations with selected member.  
									item.is_filtered_by = must_reload;
								}
							});
							
							if (this.totalFilterTrees == 0 ) {
								this.resumeEvents();
								og.eventManager.fireEvent('member trees updated',node);
								
							}
						}
					}
					
					var type =  node.attributes.object_type_id;
					og.contextManager.lastSelectedNode = node ;
					og.contextManager.lastSelectedDimension = this.dimensionId ;
					og.contextManager.lastSelectedMemberType = type; 

					if (selection_changed && !og.resettingAllTrees) {
						og.eventManager.fireEvent('member changed', node);
					}
					
				}else { 
					// Multiple Selection: (UNDER DEVELOPENT) 
					// Add to context
					for (var i = 0 ; i < selection.length ; i++) {
						var node = selection[i] ;
						if (node.getDepth()) {
							var member = node.attributes.id ;
						} else {
							var member = 0;
						}
						og.contextManager.addActiveMember(member, this.dimensionId, node );
					}
				}
			}
		},
		scope:this // Con esto this referencia al TreeNode. Sino al SelModel
	});
	
	this.init(function(){
		self.selectRoot([0]);
		setTimeout(function() {
			if (self.totalNodes > 1000) {
				self.collapseAll();
				self.root.expand();
			}
		}, 100);
	}) ;
	
	// **************** TREE INIT **************** //	
};

Ext.extend(og.MemberTree, Ext.tree.TreePanel, {

	// ******* ATTRIBUTES ******** //
	
	filterOnChange: false,
	
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
				// if there is an active search request it must be cancelled
				/*if (og.last_search_request_id && Ext.Ajax.isLoading(og.last_search_request_id)) {
					Ext.Ajax.abort(og.last_search_request_id);
				}*/
				
				var d = new Date();
				this.tbar.last_search_time = d.getTime();
				
				var dimension_id = this.dimensionId ? this.dimensionId : this.id.replace("dimension-panel-", "");
				
				og.last_search_request_id = og.openLink(og.getUrl('dimension', 'search_dimension_members_tree', {
					dimension_id: dimension_id,
					tree_id: this.id,
					query: Ext.escapeRe(text.toLowerCase()),
					time: d.getTime()
				}), {
	    			hideLoading:true, 
	    			hideErrors:true,
	    			callback: function(success, data){
	    				if(success){
		    				//var dimension_tree = Ext.getCmp('dimension-panel-'+data.dimension_id);
	    					var dimension_tree = Ext.getCmp(data.tree_id);
		    				// don't process response if it isn't the last one
		    				if (dimension_tree.tbar.last_search_time != data.time) {
		    					dimension_tree.innerCt.unmask();
		    					return;
		    				}
		    				//add nodes to tree
		    				dimension_tree.addMembersToTree(data.members, data.dimension_id);
		    								
		    				dimension_tree.innerCt.unmask();
		    				
		    				//get the text from the filter
		    				var search_text = dimension_tree.getTopToolbar().items.get(dimension_tree.id + '-textfilter').el.getValue();
		    				re_search_text = new RegExp(Ext.escapeRe(search_text.toLowerCase()), 'i');
		    				
		    				//add the last search criteria to the search history
		    				if(data.query && data.query.trim() != ''){
		    					if(dimension_tree.tbar.history == undefined){
		    						dimension_tree.tbar.history = {prevTextFilters: [], date: new Date()};
								}
		    					dimension_tree.tbar.history.prevTextFilters.push(data.query);
							}

		    				//filter the tree
		    				dimension_tree.filterNode(dimension_tree.getRootNode(), re_search_text);
		    				dimension_tree.suspendEvents();
		    				dimension_tree.expandAll();
		    				dimension_tree.resumeEvents();
	    				}				
	    			}
	    		});
	    	}else{
	    		//filter the tree
	    		this.filterNode(this.getRootNode(), re);
	    		this.suspendEvents();
	    		this.expandAll();
	    		this.resumeEvents();
	    	}			
		}
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
		this.collapseAll();
		if (n.previousState == "e") {
			n.expand(false, false);
		} else if (n.previousState == "c") {
			n.collapse(false, false);
		}
		n.previousState = null;
	},
	
	
	
	expandedNodes: function () {
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
		if (!this.hidden) og.contextManager.addActiveMember(0, this.dimensionId, node );
		selModel.resumeEvents();

	},
	
	hide: function() {
		og.MemberTree.superclass.hide.call(this);
		og.contextManager.cleanActiveMembers(this.dimensionId);
	},
	show: function() {
		og.MemberTree.superclass.show.call(this);
		og.contextManager.cleanActiveMembers(this.dimensionId);
		this.selectRoot();
	}, 

	selectNodes: function(nids) {
		if (og.resettingAllTrees) {
			// if all trees are being reset then don't select any other node
			nids = [];
		}
		
		var mem_count = 0;
		for (var i = 0 ; i < nids.length ; i++ ) {
			if ( nids[i] != "undefined" ) {
				if ( nids[i] != 0 ) {
					var node = this.getNodeById(nids[i]);
					
					// expand selected member hierarchy
					var member_obj = og.dimensions[this.dimensionId][nids[i]];
					if (member_obj) {
						
						var pnode = member_obj ? og.dimensions[this.dimensionId][member_obj.parent] : null;
						while (pnode != null && pnode.id != this.getRootNode().id) {
							if (pnode.id > 0) {
								og.eventManager.fireEvent('try to expand member', {id:pnode.id, dimension_id:this.dimensionId});
							}
							pnode = pnode.parent > 0 ? og.dimensions[this.dimensionId][pnode.parent] : null;
						}
					}
					// select member
					og.eventManager.fireEvent('try to select member', {id:nids[i], dimension_id:this.dimensionId});
					
				} else if (nids.length == 1) {
					var node = this.getRootNode();
				} else {
					continue;
				}
				
				if (nids[i] > 0) {
					var dimensions_to_reload = this.reloadDimensions;
				
					var trees = this.ownerCt.items;
					if (trees) {
						trees.each(function (item, index, length){
							for (ot in dimensions_to_reload) {
								var dims_array = dimensions_to_reload[ot];
								
								for (var k=0; k<dims_array.length; k++) {
									var reload_dim_id = parseInt(dims_array[k]);
									if (reload_dim_id == parseInt(item.dimensionId)) {
										item.disableReloadOtherDimensions = true;
										break;
									}
								}
							}
						});
					}
					
					mem_count++;
				}
			}
		}
		if (mem_count == 0) {
			og.contextManager.cleanActiveMembers(this.dimensionId);
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
	
	filterByMember: function(memberIds, nodeClicked, callback) {
		var tree = this ; //scope
		var expandedNodes = tree.expandedNodes() ;
		
		// if resetting all trees don't select any node
		var selectedMembers = og.resettingAllTrees ? [] : og.contextManager.getDimensionMembers(this.dimensionId);

		tree.expandMode = "root";
		
		// reset search cache
		tree.getTopToolbar().container.history = undefined;
		
		//this.collapseAll() ;
		
		this.loader =  new og.MemberChooserTreeLoader({
			dataUrl: 'index.php?c=dimension&a=initial_list_dimension_members_tree_root&ajax=true&dimension_id='+this.dimensionId+'&selected_ids='+ Ext.util.JSON.encode(memberIds) +'&avoid_session=1',	
			ownerTree: this
		});
		this.loader.load(this.getRootNode(), function(loader, node, response_object) {
			tree.init(
				function() {
					var was_filtered = true;
					if (response_object && response_object.list_was_filtered_by) {
						if (response_object.list_was_filtered_by.length==0) {
							was_filtered = false;
						}
					}
					// dont expand if the list was filtered by an associated dimension
					var expand = response_object && !(response_object.list_was_filtered_by && response_object.list_was_filtered_by.length > 0);
					
					// expand filtered nodes
					if (nodeClicked.getDepth() > 0 && expand) {
						og.expandAllChildNodes(tree.getRootNode());
					}
					
					if (tree.expandMode != "all"){
						// If not all nodes are exapnded, expand only needed
						tree.expandNodes(expandedNodes);
					}
					tree.selectNodes(selectedMembers); 
			        if( typeof callback == "function"){
			        	callback();
			        }
				} 
			);
		});
			
	},
	
	removeFromContext: function() {
		this.hide();
		this.collapse();
		this.getSelectionModel().select(this.getRootNode());
		var did = this.dimensionId;
		Ext.getCmp("dimension-selector-"+did).suspendEvents();
		Ext.getCmp("dimension-selector-"+did).setChecked(false);
		Ext.getCmp("dimension-selector-"+did).resumeEvents();
	},

    addMembersToTree: function(members,dimension_id) {
    	var all_parent_nodes_involved = [];
        var dimension_tree = this;
        for (var prop in members) {
            var mem = members[prop];

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
            if(!node_exist){
                dimension_tree.suspendEvents();
                if (node_parent) node_parent.appendChild(new_node);
                dimension_tree.resumeEvents();
            }else{
                if (node_parent){
                    // dont remove old and insert the new, only update the name and the attributes.
                    node_exist.attributes = mem;
                    node_exist.setText(mem.text);
                }
            }
            if (node_parent) {
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

	onMemberExternalClick: function (member_id) {
		//og.expandCollapseDimensionTree(item);
		var n = this.getNodeById(member_id);
		if (n) {
			if (n.parentNode) this.expandPath(n.parentNode.getPath(), false);
			if (n.getOwnerTree()) n.select();
			og.eventManager.fireEvent('member tree node click', n);
		}else {
			this.innerCt.mask();
			og.openLink(og.getUrl('dimension', 'get_member_parents', {member:member_id}), {
				hideLoading:true, 
				hideErrors:true,
				callback: function(success, data){
					
					var dimension_tree = Ext.getCmp('dimension-panel-'+data.dimension_id);
					if (dimension_tree) {
						if (dimension_tree.hidden) {
							// if tree is hidden then show and expand it
							dimension_tree.show();
							dimension_tree.expand();
							dimension_tree.getRootNode().expand();
							dimension_tree.innerCt.unmask();
							
							// mark dimension as checked in the dimension panel selector but don't fire the event to modify the user preference.
							Ext.getCmp("dimension-selector-" + data.dimension_id).setChecked(true, true);
							
							// expand parents path
							if (data.members && data.members.length > 0) {
								for (var i=0; i<data.members.length; i++) {
									var mem = data.members[i];
									if (mem.id == data.member_id) {
										break;
									} else {
										og.eventManager.fireEvent('try to expand member', {id: mem.id, dimension_id: data.dimension_id});
									}
								}
							}
							
							// select the node
							og.eventManager.fireEvent('try to select member', {id: data.member_id, dimension_id: data.dimension_id});

                            // ensure that first level nodes are ordered after the insertion
                            dimension_tree.root.sort(og.sortNodesFn);
						} else {

							dimension_tree.addMembersToTree(data.members, data.dimension_id);
							dimension_tree.innerCt.unmask();
							
							var n = dimension_tree.getNodeById(data.member_id);
							if(n){
								dimension_tree.suspendEvents();
								if (n.parentNode){
                                    dimension_tree.expandPath(n.parentNode.getPath(), false);
                                    n.parentNode.sort(og.sortNodesFn);
								}else{
                                    // ensure that first level nodes are ordered after the insertion
                                    dimension_tree.root.sort(og.sortNodesFn);
								}
								dimension_tree.resumeEvents();
								if (n.getOwnerTree()) n.select();
								og.eventManager.fireEvent('member tree node click', n);
							}
							
						}
					}
				}
			});
		}
	}
});


// ***** EXTJS REGISTER COMPONENT ******* //
Ext.reg('member-tree', og.MemberTree);


og.updateDimensionTreeNode = function(dimension_id, member, extra_params) {
	var dimension_tree = Ext.getCmp('dimension-panel-'+dimension_id);
	var node_parent = dimension_tree.getNodeById(member.parent);
	if(member.parent == 0){
		node_parent = dimension_tree.root;
	}
	
	member.leaf = !member.expandable;
	
	member.text = member.name;
	var new_node = dimension_tree.loader.createNode(member);
		    												
	var node_exist = dimension_tree.getNodeById(member.id);			

	if(!node_exist){		
		if (node_parent) node_parent.appendChild(new_node);
	}else{	
		//check if the parent have changed
		if(node_exist.parentNode.attributes.id == member.parent || (node_exist.parentNode.isRoot && member.parent == 0)){
			node_parent.removeChild(node_exist);
		}else{
			node_exist.parentNode.removeChild(node_exist);			
		}
		node_parent.appendChild(new_node);
		
		if(!node_parent.isExpanded()){
			dimension_tree.suspendEvents();
			node_parent.expand();
			dimension_tree.resumeEvents();
		}
		
			
	}
	
	new_node.ensureVisible();
	if (extra_params.select_node) {
		dimension_tree.suspendEvents();
		//dimension_tree.selectNodes([new_node.id]);
		new_node.select();
		dimension_tree.resumeEvents();
		og.eventManager.fireEvent('member tree node click', new_node);
	}
	if (new_node.attributes.expandable)	new_node.expand();
	
	// ensure that first level nodes are ordered after the insertion
	dimension_tree.root.sort(og.sortNodesFn);
}

og.sortNodesFn = function(node1, node2) {
	if (node1 && node2) {
		if (node1.attributes && node1.attributes.sort_key && node2.attributes && node2.attributes.sort_key) {
			return node1.attributes.sort_key.toLowerCase().localeCompare(node2.attributes.sort_key.toLowerCase());
		} else if (node1.sort_key && node2.sort_key) {
			return node1.sort_key.toLowerCase().localeCompare(node2.sort_key.toLowerCase());
		} else {
			return node1.text.toLowerCase().localeCompare(node2.text.toLowerCase());
		}
	}
}
