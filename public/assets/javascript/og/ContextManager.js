og.contextManager  = new function() {
	
	// Public Attributes
	
	this.currentDimension = 0 ;

	/**
	 * Member Chooser selected members gruped by dimension.
	 * Used to Store the Selected Context - ( Right sidebar trees )
	 * @author Pepe
	 */
	this.lastCheckedMembers = {} ;
	
	/**
	 * Left Panel selected members gruped by dimension.
	 * Used to Store the Global Context - ( Left sidebar trees )
	 * @author Pepe
	 */
    this.dimensionMembers = {} ;
    
    /**
     * Contains info about each dimension abailable for the user. (hidden or not).
     * Array map of {dimension ID : diememsion INFO}
	 * @author Pepe
     */
    this.contextDimensions = {} ;
    
    /**
     * Member choosers
     * Dimension trees (ids) rendered by content type form.
     * (When editing/creating any content object)  
	 * @author Pepe
     */
    this.formDimensionTrees = [];
    
    /**
     * Initialize structure
     * @author Pepe
     */
    this.construct = function () {
    	for (var i = 0 ; i < og.dimensionPanels.length ; i++ ) {
    		if (d = og.dimensionPanels[i] ) {
	    		this.contextDimensions[d.id] = {
	    				id: d.id,
	    				dimensionId: d.dimensionId,
	    				title: d.title,
	    				checked: (d.isRoot) ? true : false,
   						visible: (d.isRoot) ? true : false
	    		} ;    		
    		}
    		
    	}
    }
    
    /**
     * Returns a list of items to be rendered in the dimension Menu (at the top of the dimension panel) 
     */
    this.getDimensionMenu = function () {
    	var menu = [] ;
    	for ( var i in this.contextDimensions ) {
    		if ( i != "remove" ) {
    			var did = this.contextDimensions[i].dimensionId;
    			menu.push({
    				id: "dimension-selector-"+did,
    				text: this.contextDimensions[i].title,
    				checked: this.contextDimensions[i].checked, 
    				dimensionId: this.contextDimensions[i].id,
    				hideOnClick: false,
    				
    				checkHandler: function (item, status){
    					var panel = Ext.getCmp(this.dimensionId) ;
    					if (!status) {
    						panel.removeFromContext();
    						
    					}else{
    						panel.show();
    						panel.expand();
    					}
    					
    					og.contextManager.setDimensionVisibility(item.dimensionId, status);
    					
    					var dim_ids = og.contextManager.getVisibleDimensions();
    					og.openLink(og.getUrl('account', 'update_user_preference', {name:'root_dimensions', value:dim_ids.join(',')}), {hideLoading:true});
    				}
    				
    			});
    		}
    	}
    	return menu ;    	
    }
    
    this.getVisibleDimensions = function() {
    	var dim_ids = [];
		for ( var idx in og.contextManager.contextDimensions ) {
			if ( idx != "remove" && og.contextManager.contextDimensions[idx].visible) {
    			dim_ids.push(og.contextManager.contextDimensions[idx].dimensionId);
    		}
		}
		return dim_ids;
    }
    
    this.setDimensionVisibility = function(dimension_id, visibility) {
    	this.contextDimensions[dimension_id].visible = visibility ? true : false;
    }
    
    // Public Methods 
    
    /**
     * Add a new member chooser tree id for certain dimension
     */
    this.addFormDimensionTree = function (id, objectType, dimensionId) {
    	if (!this.formDimensionTrees[objectType]) {
    		this.formDimensionTrees[objectType] = [];
    	}  
    	if ( this.formDimensionTrees.indexOf(id)  == -1 ) {
    		this.formDimensionTrees[objectType][dimensionId] = id;
    	}    	
    }
    
    /**
     * Returns a List of MemberChooser tree for 1 dimension
     */
    this.getFormDimensionTrees = function (objectType) {
    	if ( this.formDimensionTrees[objectType] ) {
    		return this.formDimensionTrees[objectType] ;
    	}
    	return null
    }
    
    /**
     * Clean the collection of trees ids 
     */
    this.cleanFormDimensionTrees = function (objectType, dimensionId) {
    	if (this.formDimensionTrees[objectType]) {
    		delete this.formDimensionTrees[objectType][dimensionId] ;
    	} 
    }
    
    
    // ACTIVE MEMBERS
    
    /**
     * Removes all the dimension members from the context structure for certain dimension Id
     */
    this.cleanActiveMembers = function (dimensionId) {
    	if ( dimensionId != undefined ) {
    		delete this.dimensionMembers[dimensionId] ;
    		this.dimensionMembers[dimensionId] = [0] ;
    	}
    	
    }
    
    /**
     * Adds a new member to the context 
     * @param member - integer: memeber id to add
     * @param dimension - integer: dimension id 
     *  
     */
    this.addActiveMember = function ( member , dimension, node ) {   	
    	if (dimension) {
    		if (!this.dimensionMembers[dimension]) 
    			this.dimensionMembers[dimension] = [] ;
    		if ( this.dimensionMembers[dimension].indexOf(member) == -1)
    			this.dimensionMembers[dimension].push(member) ;
    	}
    	og.eventManager.fireEvent("after add active member", node);
    }
    
    this.removeActiveMember = function ( member , dimensionId ) {
    	
		if ( this.dimensionMembers[dimensionId] ) {
    	  	var index = this.dimensionMembers[i].indexOf(member) ;
        	if ( index != -1) 
        		delete this.dimensionMembers[i][index]; //TODO break
    	}
    }
    
    this.getDimensionName = function (dimId){
    	return this.contextDimensions["dimension-panel-"+dimId].title;
    }
    
    
    this.getTreeNode = function (dimId, memberId){ 
    	var tree =  Ext.getCmp("dimension-panel-"+dimId);
    	return  tree.getNodeById(memberId);
    	
    }
    

    this.getMemberName = function (dimId, memberId){
    	var node = this.getTreeNode(dimId, memberId) ;
    	if (node) {
    		return node.text ;
    	}else{
    		return "" ;
    	}
    }
    
    this.getMemberPath = function (dimId, memberId, return_array, separator, direction) {
    	separator = separator || "/";
    	direction = direction || "ltr";
    	
    	var path = return_array ? [] : "";
    	var node = this.getTreeNode(dimId, memberId);
    	if (node) {
	    	var depth = node.getDepth();
	    	if (depth >= 2 ) {
	    		
	    		while ( depth > 1 ){
	    			node  = node.parentNode;
	    			if (node) {
	    				if (!return_array) {
		    				if (!path) {
		    					path = node.text;
		    				}else{
		    					if (direction == "ltr") {
		    						path = node.text + separator + path;
		    					}else{
		    						path =  path + separator + node.text;
		    					}
		    				}
	    				} else {
	    					path.push(node);
	    				}
		    			depth--;
	    			}
	    		}
	    	}
	    	if (!return_array && path) {
	    		if (direction == "ltr") {
	    			path = path + separator;
	    		}else {
	    			path = separator + path;
	    		}
	    	}
    	}
    	
    	if (return_array && direction == "ltr") {
    		path = path.reverse();
    	}
    	return path;
    	
    }
    
    
    /**
     * Returns an array of member ids for certain diemension that are in the context 
     */
    this.getDimensionMembers = function (dimension) {
    	if ( dimension && this.dimensionMembers[dimension] ) return this.dimensionMembers[dimension] ;
    	else if (dimension) return [] ;
    	else return null ;
    }
    
    this.plainActiveMembers = function () {
    	return "["+this.activeMembers.join(",")+"]";
    }

    /**
     * Returns the context on a plain format to be send to the servers. 
     * Format example: {"3":[6,7], "4":[7,8]}
     * Values: 
     * - 0: if 'All' is selected
     * - integer > 0: member id selected
     */
    this.plainContext = function () {
    	return Ext.util.JSON.encode( this.dimensionMembers) ;
    }
    
    /**
     * Returns this list of checked members Grouped by dimension, in JSON FORMAT
     * For certain objectTypeId
     * @params objecteTypeId
     * 
     */
    this.plainCheckedMembers = function (objectTypeId) {
    	return Ext.util.JSON.encode( this.lastCheckedMembers[objectTypeId]) ;
    }
    
    /**
     * Returns this list of checked members Grouped by dimension 
     * For certain objectTypeId
     * @params objecteTypeId
     * 
     */
    this.getCheckedMembers = function (objectTypeId) {
    	return this.lastCheckedMembers[objectTypeId];
    }
    
    this.hasCheckedMembers = function (objectTypeId) {
    	var checked = this.getCheckedMembers(objectTypeId);   	
    	for ( var i  in checked ){
    		if (checked[i].length) return true ;
    	}  
    	return false  ;
    }
    
    /**
     * Reset the list of checked members for one content type
     */
    this.cleanCheckedMembers = function (objectTypeId){
    	this.lastCheckedMembers[objectTypeId] = {};
    }
    
    /**
     * 
     */
    this.addCheckedMember = function (objectTypeId, member, dimension) {
    	if (!this.lastCheckedMembers[objectTypeId]) {
    		this.lastCheckedMembers[objectTypeId] = {} ;
    	}
    	if (!this.lastCheckedMembers[objectTypeId][dimension]) {
    		this.lastCheckedMembers[objectTypeId][dimension] = [];
    	}  
    	if ( this.lastCheckedMembers[objectTypeId][dimension].indexOf(member)  == -1 ) {
    		this.lastCheckedMembers[objectTypeId][dimension].push(member)   ;
    	}    
    }
    
    
    this.getActiveContextNames = function() {
    	var context_names = [];
		context_ids = Ext.util.JSON.decode(this.plainContext());
		for (dim_id in context_ids) {
			var mids = context_ids[dim_id];
			for (i=0; i<mids.length; i++) {
				if (mids[i] > 0) context_names.push(this.getMemberName(dim_id, mids[i]));
			}
		}
		
		return context_names;
    }
    
    this.getSelectedMemberObjectTypeId = function(dimension_id) {
    	var sel_member_type = 0;
    	context_ids = Ext.util.JSON.decode(this.plainContext());
		for (dim_id in context_ids) {
			if (dim_id == dimension_id) {
				var mids = context_ids[dim_id];
				for (i=0; i<mids.length; i++) {
					if (mids[i] > 0) {
						var tree = Ext.getCmp('dimension-panel-'+dimension_id);
						if (tree) {
							var node = tree.getNodeById(mids[i]);
							if (node) sel_member_type = node.object_type_id; 
						}
					}
				}
				break;
			}
		}
		return sel_member_type;
    }
}



