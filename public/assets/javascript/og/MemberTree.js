
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
						this.filterTree(e.target.value);
					},
					this, {buffer: 350});
				},
				scope: this
			}
		}
	}];
		
	var expandM = 'root';
	if(config.hidden) expandM = 'none';
	Ext.applyIf(config, {
		region: 'center',
		id: config.id,
		loader: new og.MemberChooserTreeLoader({
    		dataUrl: 'index.php?c=dimension&a=initial_list_dimension_members_tree_root&ajax=true&dimension_id='+config.dimensionId+'&avoid_session=1',
    		ownerTree: this  
    	}),
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
    		   qtip: lang('add a new member in ' + config.dimensionCode),
    		   handler: function(e,t,p){
    			   og.quickForm({ dimensionId: p.dimensionId,type: 'member', treeId: p.dimensionId, elId: t.id});	    	   		
	       		}
    	    }

    	],  	
    	hideCollapseTool: true ,
    	expandMode: expandM, //all root,
    	tbar: tbar 
	});
	
	config.initialLoader = config.loader;
	if (!config.listeners) config.listeners = {};
	Ext.apply(config.listeners, {
		beforenodedrop: function(e) {
			if (!isNaN(e.target.id) && e.data.grid) {
				
				var has_relations = false;
				var ids = [];
				for (var i=0; i<e.data.selections.length; i++) {
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

				if (e.data.selections[0] && og.dimension_object_type_contents[config.dimensionId][e.target.object_type_id][e.data.selections[0].data.ot_id] &&
						og.dimension_object_type_contents[config.dimensionId][e.target.object_type_id][e.data.selections[0].data.ot_id].multiple) {
					
					if (og.preferences['drag_drop_prompt'] == 'prompt') {
						var rm_prev = has_relations ? (confirm(lang('do you want to mantain the current associations of this obj with members of', config.title)) ? "0" : "1") : "1";
					}else if (og.preferences['drag_drop_prompt'] == 'move') {
						var rm_prev = 1 ;
					}else if (og.preferences['drag_drop_prompt'] == 'keep') {
						var rm_prev = 0 ;
					}

					if (this.selectionHasAttachments() && e.target.id) {
						if (og.preferences['mail_drag_drop_prompt'] == 'prompt') {
							var attachment = confirm(lang('do you want to classify the unclassified emails attachments', config.title)) ? "1" : "0";
						} else if (og.preferences['mail_drag_drop_prompt'] == 'classify') {
							var attachment = 1;
						} else if (og.preferences['mail_drag_drop_prompt'] == 'dont') {
							var attachment = 0;
						}
					}

					og.openLink(og.getUrl('member', 'add_objects_to_member'),{
						method: 'POST',
						post: {objects: Ext.util.JSON.encode(ids), member: e.target.id, remove_prev:rm_prev, attachment:attachment},
						callback: function(){
							e.data.grid.load();
						}
					});                                        
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

					og.openLink(og.getUrl('member', 'add_objects_to_member'),{
						method: 'POST',
						post: {objects: Ext.util.JSON.encode(ids), member: e.target.id, attachment:attachment},
						callback: function(){
							e.data.grid.load();
						}
					});
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
			//get childs from server
	        if(node.childNodes.length < node.attributes.realTotalChilds && node.attributes.expandable && !node.attributes.gettingChildsFromServer){
	        	node.ownerTree.innerCt.mask();
	        	node.attributes.gettingChildsFromServer = true;
	        	og.openLink(og.getUrl('dimension', 'get_member_childs', {member:node.id}), {
	    			hideLoading:true, 
	    			hideErrors:true,
	    			callback: function(success, data){
	    				
	    				var dimension_tree = Ext.getCmp('dimension-panel-'+data.dimension);
	    				if (dimension_tree) {
		    				dimension_tree.addMembersToTree(data.members, data.dimension);
		    				 				
		    				dimension_tree.innerCt.unmask();
		    				
		    				var current_node = dimension_tree.getNodeById(data.member_id);
		    				if (current_node) current_node.attributes.gettingChildsFromServer = false;
	    				}
	    			}
	    		});
	        }
			
		},
		click: function(node, e){
			//clear search filter
			this.clearFilter();
			$("#" + this.id + '-textfilter').val("");
						
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
						if ( self.id != item.id  && (!item.hidden ||item.reloadHidden) && self.reloadDimensions.indexOf(item.dimensionId) != -1  ) {
							
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
		        	og.openLink(og.getUrl('dimension', 'get_member_childs', {member:node.id}), {
		    			hideLoading:true, 
		    			hideErrors:true,
		    			callback: function(success, data){
		    				
		    				var dimension_tree = Ext.getCmp('dimension-panel-'+data.dimension);
		    				
		    				dimension_tree.addMembersToTree(data.members, data.dimension);
		    				 
		    				var current_node = dimension_tree.getNodeById(data.member_id);
		    				current_node.attributes.gettingChildsFromServer = false;
		    				
		    				dimension_tree.innerCt.unmask();    						    				
		    			}
		    		});
		        }		        		       				
			}
		},
		dblclick: function(node, e){
			og.contextManager.currentDimension = self.dimensionId;
			og.eventManager.fireEvent("member tree node dblclick", node);
			var treeConf = node.attributes.loader.ownerTree.initialConfig;
			if  (node.getDepth() > 0 && node.actions && node.actions.length > 0){
				// Member clicked (not root)
				for (var i=0; i<node.actions.length; i++) {
					var action = node.actions[i];
					if (action['class'] == 'action-edit' && action.url) {
						og.openLink(action.url);
						return;
					}
				}
			
			}
		}
	});
	
	this.getSelectionModel().on({
		
		selectionchange : function(sm, selection) {
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
								if ( self.id != item.id  && (!item.hidden ||item.reloadHidden) && self.reloadDimensions.indexOf(item.dimensionId) != -1 ) {
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
	
	filterOnChange: true,
	
	filterTree: function(text) {

		if (text == this.getTopToolbar().items.get(this.id + '-textfilter').emptyText) {
			text = "";
		}
		if (text.trim() == '') {
			this.clearFilter();
		} else {
			var re = new RegExp(Ext.escapeRe(text.toLowerCase()), 'i');
			//search on server
			this.innerCt.mask();
			og.openLink(og.getUrl('dimension', 'search_dimension_members_tree', {dimension_id:this.id.replace("dimension-panel-", ""),query:Ext.escapeRe(text.toLowerCase())}), {
    			hideLoading:true, 
    			hideErrors:true,
    			callback: function(success, data){
    				
    				var dimension_tree = Ext.getCmp('dimension-panel-'+data.dimension_id);
    				    	
    				//add nodes to tree
    				dimension_tree.addMembersToTree(data.members, data.dimension_id);
    								
    				dimension_tree.innerCt.unmask();
    				
    				//filter the tree
    				dimension_tree.filterNode(dimension_tree.getRootNode(), re);
    				dimension_tree.suspendEvents();
    				dimension_tree.expandAll();
    				dimension_tree.resumeEvents();
    			}
    		});			
		}
	},
	
	filterNode: function(n, re) {
		
		var f = false;
		var c = n.firstChild;
		while (c) {
			f = this.filterNode(c, re) || f;
			c = c.nextSibling;
		}
		f = re.test(n.text.toLowerCase()) || f;
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
		new Ext.tree.TreeSorter(this, {
		    dir: "asc",
		    property: "text"		   
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
							if (dimensions_to_reload.indexOf(item.dimensionId) != -1) {
								item.disableReloadOtherDimensions = true;
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
		
		//this.collapseAll() ;
		
		this.loader =  new og.MemberChooserTreeLoader({
			dataUrl: 'index.php?c=dimension&a=initial_list_dimension_members_tree_root&ajax=true&dimension_id='+this.dimensionId+'&selected_ids='+ Ext.util.JSON.encode(memberIds) +'&avoid_session=1',	
			ownerTree: this
		});
		this.loader.load(this.getRootNode(), function() {
			tree.init(
				function() {
					
					// expand filtered nodes
					if (nodeClicked.getDepth() > 0) {
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
		var dimension_tree = this;
		
		for (var prop in members) {  
			var mem = members[prop];
			og.addMemberToOgDimensions(dimension_id,mem);
			
			var node_parent = dimension_tree.getNodeById(mem.parent);
			if(mem.parent == 0){
				node_parent = dimension_tree.root;
			}
			
			mem.leaf = true;
			mem.text = mem.name;
			var new_node = dimension_tree.loader.createNode(mem);
			
			var node_exist = dimension_tree.getNodeById(mem.id);			
			
			if(!node_exist){
				if (node_parent) node_parent.appendChild(new_node);
			}else{				
				if (node_parent){
					node_exist.setText(new_node.text);
				/*	node_parent.removeChild(node_exist);
					node_parent.appendChild(new_node);*/								
				}							
			}
		}
	},

	onMemberExternalClick: function (member_id) {
		//og.expandCollapseDimensionTree(item);
		var n = this.getNodeById(member_id);
		if (n) {
			if (n.parentNode) this.expandPath(n.parentNode.getPath(), false);
			n.select();
			og.eventManager.fireEvent('member tree node click', n);
		}else {
			this.innerCt.mask();
			og.openLink(og.getUrl('dimension', 'get_member_parents', {member:member_id}), {
				hideLoading:true, 
				hideErrors:true,
				callback: function(success, data){
					
					var dimension_tree = Ext.getCmp('dimension-panel-'+data.dimension_id);
					if (dimension_tree) {
						
						dimension_tree.addMembersToTree(data.members, data.dimension_id);
						dimension_tree.innerCt.unmask();
						
						var n = dimension_tree.getNodeById(data.member_id);
						if(n){
							dimension_tree.suspendEvents();
							if (n.parentNode) dimension_tree.expandPath(n.parentNode.getPath(), false);
							dimension_tree.resumeEvents();
							n.select();
							og.eventManager.fireEvent('member tree node click', n);
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
		
	member.leaf = true;
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
	dimension_tree.suspendEvents();
	//dimension_tree.selectNodes([new_node.id]);
	new_node.select();
	dimension_tree.resumeEvents();
	og.eventManager.fireEvent('member tree node click', new_node);
	new_node.expand();
}

