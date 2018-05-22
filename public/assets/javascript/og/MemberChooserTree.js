og.MemberChooserTree = function(config) {
	if (!config.allowedMemberTypes) 
		config.allowedMemberTypes = '';
	if (config.filterContentType) {
		var objectTypeQuery = '&object_type_id='+config.objectTypeId;
	}else{
		var objectTypeQuery = '';
	}
	Ext.applyIf(config, {
		isMultiple: false,
		collapsible: true,
		collapsed: false,
		titleCollapse: true,  
		allowedMemberTypes: '', //Array of dimension member types to be rendered as checkbox
		reloadDimensions: [],
		loader: new og.MemberChooserTreeLoader({
			dataUrl: config.loadUrl ? config.loadUrl : 'index.php?c=dimension&a=initial_list_dimension_members_tree&ajax=true'+
				'&dimension_id='+config.dimensionId+objectTypeQuery+
				(config.checkBoxes ? '&checkboxes=true' : '')+
				(config.all_members ? '&all_members=true' : '')+
				(og.config.member_selector_page_size ? '&limit='+og.config.member_selector_page_size : '')+
				'&allowedMemberTypes='+Ext.encode(config.allowedMemberTypes)+
				'&avoid_session=1',
			ownerTree: this 
		}),
		checkBoxes: true,
		autoScroll: true,
		animCollapse: false,
		animExpand: false,
		animate: false,
		rootVisible: true,
		lines: false,
		root: {
        	text: lang('all'),
        	expanded: false
    	},
		cls: 'member-chooser',
		tbar: [{
			xtype: 'textfield',
			
			id: 'textfilter-'+config.dimensionId,
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
		}]
    	
	});
	
	
	
	og.MemberChooserTree.superclass.constructor.call(this, config);
	if ( Ext.isIE7 ) {
		this.width= 230;
		this.height= 280 ;
	}
	this.filterOnChange = true ;
	this.totalFilterTrees = 0 ;
	this.filteredTrees = 0 ;
	var self = this ; // To change scope inside callbacks	
	
	// ********** TREE EVENTS *********** //
	
	this.on ({
		'checkchange' : function (node, checked) {
			// Avoid multiple check
			if (!this.isMultiple) {
				if (checked) {
					var oldChecked = this.getChecked();
					for (var i = 0 ; i < oldChecked.length ; i++) {
						if ( oldChecked[i] && oldChecked[i].id != node.id ) {
							this.suspendEvents();
							oldChecked[i].checked = false ; 
							oldChecked[i].getUI().toggleCheck(false) ;						
							this.resumeEvents();
						}
					}
				}
			}
			
			// Filter other trees 
			if ( this.filterOnChange ) {
				var trees = this.ownerCt.items;
				if (trees){
					this.suspendEvents();
					this.totalFilterTrees = 0 ;
					this.filteredTrees = 0 ;
					
					var selected_members = [];
					trees.each(function (item, index, length){
						if (typeof(item.getSelectionModel().getSelectedNode) == 'function') {
							var sel = item.getSelectionModel().getSelectedNode();
							if (sel && !isNaN(sel.attributes.id)) selected_members.push(sel.attributes.id);
						} else if (typeof(item.getChecked) == 'function') {
							var sels = item.getChecked();
							for (x=0; x<sels.length; x++) {
								var sel = sels[x];
								if (sel && !isNaN(sel.id)) selected_members.push(sel.id);
							}
						}
					});
					
					trees.each(function (item, index, length){

						if ( self.id != item.id && self.reloadDimensions.indexOf(item.dimensionId) != -1  ) {
							self.totalFilterTrees++;
							
							var nid = (checked) ? node.id : 0 ;
							if (checked) {
								var nid = node.id ; 
							}else{
								if (!this.isMultiple){
									var nid = 0 ;
								}else {
									var nid = self.getLastChecked() ;
								}
							}
							
							item.filterByMember(selected_members ,function(){
								self.filteredTrees ++ ;
								if (self.filteredTrees == self.totalFilterTrees) {
									self.resumeEvents() ;
									self.fireEvent('all trees updated');
								}
							}) ;
							
						}
					});
					if (this.totalFilterTrees == 0 ) {
						this.resumeEvents();
						self.fireEvent('all trees updated');
					}
				}
			}
			
			
		},
		'scope':this
	});
};


Ext.extend(og.MemberChooserTree, Ext.tree.TreePanel, {
	
	suspendEvents: function() {
		og.MemberChooserTree.superclass.suspendEvents.call(this);
		var htmlInputs = Ext.query("#"+this.getId() + " input" ) ;
		for (var i in htmlInputs ) {
			if ( i != "remove" ) {
				htmlInputs[i].disabled = "disabled";	
			}
		}
	},

	resumeEvents: function() {
		og.MemberChooserTree.superclass.resumeEvents.call(this);
		var htmlInputs = Ext.query("#"+this.getId() + " input" ) ;
		for (var i = 0 ; i <htmlInputs.length; i++ ) {
			if ( htmlInputs[i] ) {
				htmlInputs[i].disabled = "";

			}
		}
		
	},

	
	filterByMember: function(selected_members, callback) {
		var checked = this.getChecked("id");
		//this.collapseAll() ;
		this.loader =  new og.MemberChooserTreeLoader({	
			dataUrl:	
				'index.php?c=dimension&a=initial_list_dimension_members_tree&ajax=true&checkboxes=true'
				+'&dimension_id='+this.dimensionId
				+'&selected_ids='+Ext.util.JSON.encode(selected_members)
				+'&object_type_id='+this.objectTypeId 
				+'&avoid_session=1',
			ownerTree: this 			 
		}) ;
		var self = this;// Scope for callback
		this.loader.load(this.getRootNode(), function() {
			self.expandAll(function(){
				self.checkNodes(checked);
		        if( typeof callback == "function"){
		        	callback();
		        }
			});			
		});
	},
	
	checkNodes: function (nids) {
		if (!nids) return ;
		for (var i = 0 ; i < nids.length ; i++ ) {
			if ( nids[i] != "undefined" ) {
				if ( nids[i] != 0 ) {
					var node = this.getNodeById(nids[i]) ;
				}else{
					var node = this.getRootNode();
				}
				if (node) {
					this.suspendEvents();
					node.checked= true;
					node.getUI().toggleCheck(true);
					this.resumeEvents();
				}
			} 
		}
	},
	
	/**
	 * Select nodes given as array of int
	 */
	selectNodes: function (nids) {
		if (nids.length){
			for (var i = 0 ; i < nids.length ; i++ ){
				this.getSelectionModel().select(this.getNodeById(nids[i]));
				if (!this.multipleSelect) {
					return true;
				}
			}
		} else {
			if (!this.checkBoxes) this.getRootNode().select();
		}
		return true;
	},
	
    afterRender : function(){
		var tree = this ;
    	var collapsed = this.collapsed ;
    	this.collapsed = false ;
        og.MemberChooserTree.superclass.afterRender.call(this);
        if(collapsed){
        	if(this.checkBoxes){
        		tree.expandAll(function(){tree.collapse(false);tree.checkNodes(tree.ownerCt.selectedMembers); tree.fireEvent('tree rendered', tree);} );
        	}else{
        		tree.expandAll(function(){tree.collapse(false);tree.selectNodes(tree.ownerCt.selectedMembers); tree.fireEvent('tree rendered', tree);} );
        	}
    	}else{
    		if (this.checkBoxes){
    			tree.expandAll(function(){tree.checkNodes(tree.ownerCt.selectedMembers); tree.fireEvent('tree rendered', tree);} );
    		}else{
    			tree.expandAll(function(){tree.selectNodes(tree.ownerCt.selectedMembers); tree.fireEvent('tree rendered', tree);} );
    		}
    	}   
        
        
    },	 
	
	getLastChecked : function() {
		var checkedNodes = this.getChecked("id");
		if (checkedNodes.length  && checkedNodes[0] ) {
			return checkedNodes[0];
		}else{
			return 0 ; // Return 'All' (root node)
		}
	},
	
	filterTree: function(text) {
		if (this.getTopToolbar().items.length) {
			var searchBox = this.getTopToolbar().items.get('textfilter-'+this.dimensionId) ;
			if (searchBox) { 
				if (text == searchBox.emptyText) {
					text = "";
				}
				if (text.trim() == '') {
					this.clearFilter();
				} else {
					var re = new RegExp(Ext.escapeRe(text.toLowerCase()), 'i');
					this.filterNode(this.getRootNode(), re);
					this.expandAll();
			}
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
		if (n.previousState == "e") {
			n.expand(false, false);
		} else if (n.previousState == "c") {
			n.collapse(false, false);
		}
		n.previousState = null;
	}
	
    
});

Ext.reg('member-chooser-tree', og.MemberChooserTree);