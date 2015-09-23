
// ***** tree loader ***** //
og.MemberChooserTreeLoader = function(config) {
	og.MemberChooserTreeLoader.superclass.constructor.call(this, config);
	if (this.ownerTree) {		
		this.ownerTree.totalNodes = 0 ;
	}
};

Ext.extend(og.MemberChooserTreeLoader , Ext.tree.TreeLoader, {
		
	ownerTree: null ,
	
	createNode: function (attr) {
		
		if (  Ext.type(this.ownerTree ) ){	
			if (this.ownerTree.totalNodes) {
				this.ownerTree.totalNodes++ ;
			}else{
				this.ownerTree.totalNodes = 1;
			}
		}else{
			alert("MemberChooserTreeLoader.js - TREE NOT DEFINED  ! ! ! "+ attr.text) ;
		}
		
        // apply baseAttrs, nice idea Corey!
        if(this.baseAttrs){
            Ext.applyIf(attr, this.baseAttrs);
        }
        if(this.applyLoader !== false){
        	if (!attr) attr = {};
            attr.loader = this;
        }
        if(typeof attr.uiProvider == 'string'){
           attr.uiProvider = this.uiProviders[attr.uiProvider] || eval(attr.uiProvider);
        }
        if(attr.nodeType){

            var node =  Ext.tree.TreePanel.nodeTypes[attr.nodeType](attr);
        }else{
        	
            var node = attr.leaf ?
	            new Ext.tree.TreeNode(attr) :
	            new Ext.tree.AsyncTreeNode(attr);
                       
        }
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
	
	processResponse:function(response, node, callback) {
		if (  Ext.type(this.ownerTree ) ){
			this.ownerTree.totalNodes = 1 ;
		}
		var json = response.responseText;
		try {
			var json_obj = eval("("+json+")");

			var dimension_id = this.ownerTree.dimensionId;
			if (!og.tmp_members_to_add) og.tmp_members_to_add = [];
			
			//add members to og.dimensions
			for (i=0; i<json_obj.dimension_members.length; i++) {
				og.addMemberToOgDimensions(dimension_id,json_obj.dimension_members[i]);
			}	
			
			if(typeof(json_obj.dimensions_root_members) != "undefined" && !json_obj.more_nodes_left){
				ogMemberCache.addDimToDimRootMembers(json_obj.dimension_id);
			}
			
			// build tmp member arrays
			var tmp_member_array = [];
			var count = 0;
			while (json_obj.dimension_members.length > 0) {
				tmp_member_array[count] = json_obj.dimension_members.splice(0, 100);
				count++;
			}
			tmp_member_array.reverse();
			
			var tree_id = this.ownerTree.id;
			og.tmp_members_to_add[tree_id] = tmp_member_array;
			
			if (!og.tmp_node) og.tmp_node = [];
			og.tmp_node[tree_id] = node;
			
			// mask
			var old_text = this.ownerTree.getRootNode().text;
			this.ownerTree.getRootNode().setText(lang('loading'));
			this.ownerTree.innerCt.mask();
		
			// add nodes
			for (x=0; x<count; x++) {
				setTimeout('og.addNodesToTree("'+tree_id+'", '+(json_obj.more_nodes_left ? '1':'0')+');', 1000 * x);
			}
			
			// unmask
			var t = this.ownerTree;
			setTimeout(function(){
				t.innerCt.unmask();
				t.getRootNode().setText(old_text);
				//t.getRootNode().expand(true);
				//t.getRootNode().collapse(true);
				//t.getRootNode().expand(false);
			}, 1000 * count);
			
			node.endUpdate();
			if(typeof callback == "function"){
				callback(this, node);
			}
			this.ownerTree.expanded_once = false;
			
		}catch(e){
			this.handleFailure(response);
		}
	}
});