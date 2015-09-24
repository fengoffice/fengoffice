
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

						//save the text on the histroy only if we search on the server
						if(from_server && e.target.value.trim() != ''){
							this.tbar.history.prevTextFilters.push(e.target.value);
						}

						this.filterTree(e.target.value, from_server);
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
	
	var self = this ; // To change scope inside callbacks	

	// ********** TREE EVENTS *********** //
	this.on({
		expandnode: function(node){
			//get childs from server
	        if(node.childNodes.length < node.attributes.realTotalChilds && node.attributes.expandable){
	        	node.ownerTree.innerCt.mask();
	        	var tree_id = node.ownerTree.id;
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
					offset: node.last_childs_offset
				};
				
	        	og.openLink(og.getUrl('dimension', 'get_member_childs', parameters), {
	    			hideLoading:true, 
	    			hideErrors:true,
	    			callback: function(success, data){
	    				
	    				var dimension_tree = Ext.getCmp(tree_id);
	    					    		
	    				dimension_tree.suspendEvents();			
	    				dimension_tree.addMembersToTree(data.members,data.dimension_id);  
	    				dimension_tree.resumeEvents();	

	    				for (var i = 0 ; i < node.childNodes.length ; i++ ) {
	    					node.childNodes[i].getUI().show();					
	    				}
	    				
	    				if (data.more_nodes_left) {
	    					og.addViewMoreNode(node, tree_id, og.ajaxMemberTreeViewMoreCallback);
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
								
				var params = '"' + this.genid +'",'+ node.attributes.dimension_id +','+ node.attributes.id;
				eval(this.selectFunction + '(' + params + ')');
				if(this.selectFunction == ""){
					member_selector.add_relation(node.attributes.dimension_id, this.genid, node.attributes.id);
				}
				if (node.getOwnerTree()) {
					$("#"+ node.getOwnerTree().id +"-current-selected .empty-text").hide();
				}
			}else{ 
				//root
				var params = '"' + this.genid +'",'+ this.dimensionId +','+ 0;
				eval(this.selectFunction + '(' + params + ')');
				if(!this.isMultiple && node.getOwnerTree()){
					$("#"+ node.getOwnerTree().id +"-current-selected .empty-text").show();
				}
			}
			
			
			node.ownerTree.body.removeClass("have-focus");
			node.ownerTree.body.hide();
		},
		render: function(tree){			
			this.body.setVisibilityMode(Ext.Element.DISPLAY);
			this.body.toggle();
			this.body.addClass("collapsible-body");
			
			var tree = this;
			$("#" + tree.id + '-textfilter').attr("placeholder", tree.initialConfig.search_placeholder);
			
			
			if(!this.isMultiple){
				$("#" + tree.id + '-textfilter').hide();				
				$("#" + tree.id + '-textfilter').closest('.x-panel-tbar').attr("tabindex", -1);
				$("#" + tree.id + '-textfilter').parent().append( "<div id='"+ tree.id +"-current-selected' class='single_current_selected ico-search-m'><div class='empty-text'>"+tree.getRootNode().text+"</div></div>" );
								

				$("#" + tree.id + '-textfilter').closest('.x-panel-tbar').focusin(function(e) {
					$("#" + tree.id + '-textfilter').show();
					$("#" + tree.id + '-textfilter').select();
					$("#" + tree.id + '-current-selected').hide();

					setTimeout(function(){					
						tree.body.show();
						var top = $("#"+tree.tbar.id).offset().top + $("#"+tree.tbar.id).height();
						$("#"+tree.body.id).css({top: top+'px'});
						if(!tree.initialized){
							tree.init();
						}
				 	}, 300);
				});
				
				$("#" + tree.id + '-textfilter').closest('.x-panel-tbar').focusout(function(e) {
					if(!tree.body.hasClass( "have-focus" )){				 			
				 			$("#" + tree.id + '-textfilter').hide();
				 			$("#" + tree.id + '-current-selected').show();
				 	}
				 	setTimeout(function(){
				 		if(!tree.body.hasClass( "have-focus" )){
				 			tree.body.hide();
				 			tree.clearFilter();
				 					 			
				 			$("#" + tree.id + '-textfilter').val("");
				 			
				 		}
				 	}, 300);			 	
				});
				
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
				 		$("#" + tree.id + '-textfilter').hide();
			 			$("#" + tree.id + '-current-selected').show();
				 	}, 200);			 	
				});	
			}else{
				$("#"+this.tbar.id).focusin(function() {
					setTimeout(function(){
						tree.body.show();
						var top = $("#"+tree.tbar.id).offset().top + $("#"+tree.tbar.id).height();
						$("#"+tree.body.id).css({top: top+'px'});
						if(!tree.initialized){
							tree.init();
						}
				 	}, 300);
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
				og.openLink(og.getUrl('dimension', 'search_dimension_members_tree', {dimension_id:this.dimensionId,query:Ext.escapeRe(text.toLowerCase())}), {
	    			hideLoading:true, 
	    			hideErrors:true,
	    			callback: function(success, data){
	    				
	    				var dimension_tree = Ext.getCmp(tree_id);
	    					
	    				//add nodes to tree
	    				dimension_tree.addMembersToTree(data.members,data.dimension_id);    				
	    			   				
	    				dimension_tree.innerCt.unmask();
	    				
	    				//get the text from the filter
		    			var search_text = dimension_tree.getTopToolbar().items.get(dimension_tree.id + '-textfilter').el.getValue();
		    			re_search_text = new RegExp(Ext.escapeRe(search_text.toLowerCase()), 'i');

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
		    	return node.text;
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
				
		if(ogMemberCache.areDimRootMembersLoaded(this.dimensionId.toString())){
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
		}else{
			og.initialMemberTreeAjaxLoad(this);
			
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
					node_parent.removeChild(node_exist);

					dimension_tree.suspendEvents();
					node_parent.appendChild(new_node);
					dimension_tree.resumeEvents();
				}							
			}
			
			//add member to og.dimensions
			og.addMemberToOgDimensions(dimension_id,mem);						
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
        
	}
	
	
});


// ***** EXTJS REGISTER COMPONENT ******* //
Ext.reg('member-tree-ajax', og.MemberTreeAjax);




