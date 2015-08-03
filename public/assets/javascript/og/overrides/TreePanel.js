Ext.override(Ext.tree.TreePanel,{
	
	totalNodes: 0,
	
	expandedNodes: 0,
	
	expandAll: function(callback) {
        if( typeof callback == "function"){
        	this.expandedNodes = 0 ;
    		this.root.expand(true, this.animate, function (node) {
    			var tree  = node.getOwnerTree() ;
    			tree.expandedNodes++ ;    			
    			if (tree.expandedNodes >= tree.totalNodes ) {
    				//tree.expandedNodes  = tree.totalNodes ;  
    				callback(this, node);
    				//alert(tree.title + "Expanded: "+tree.expandedNodes + " de "+ tree.totalNodes) ;
    			}
    		});
        }else{
    		this.root.expand(true);
        }
	}

});