

Ext.override(Ext.tree.TreeNodeUI, {
	
    renderElements : function(n, a, targetNode, bulkRender){
    	nodeIconHide = "";
    	extraHTML = '';
    	if (n.actions && n.actions.length) {
    		
    		/**
    		 * node.actions = [
    		 * 		'actionName' : {
    		 * 			url: 'exampleurl',
    		 * 			text: 'test to show ',
    		 * 			iconCls: 'test-icon'
    		 * 		}	
    		 * ]
    		 */
    		extraHTML += "<ul class= 'feng-node-actions'>";
    		for (var i in n.actions) {
    			if (i == "remove") continue ;
    			action = n.actions[i];
    			if (action.url) {
	    			url = og.makeAjaxUrl(action.url);
	    			var onClick = 'og.disableEventPropagation(event);og.openLink("'+url+'")'; 
		    		extraHTML += "<li>";
		    		extraHTML += "<a class='"+action.iconCls+"' href='#' onClick = '"+onClick+"' title='"+lang('edit')+"' >";
		    		extraHTML += action.text;
		    		extraHTML += "</a>";
		    		extraHTML += "</li>";
    			}
    		}
    		extraHTML += "</ul>" ;
    		
    		nodeIconHide = "tree-node-icon-hide-over";
    	}    	
    	
        this.indentMarkup = n.parentNode ? n.parentNode.ui.getChildIndent() : '';
        var cb = typeof a.checked == 'boolean';
        var href = a.href ? a.href : Ext.isGecko ? "" : "#";
        var buf = ['<li class="x-tree-node"><div ext:tree-node-id="',n.id,'" class="x-tree-node-el x-tree-node-leaf x-unselectable ', a.cls,'" unselectable="on">',
            '<span class="x-tree-node-indent">',this.indentMarkup,"</span>",
            '<img src="', this.emptyIcon, '" class="x-tree-ec-icon x-tree-elbow" />',
            '<img src="', a.icon || this.emptyIcon, '" class="x-tree-node-icon '+nodeIconHide+' ',(a.icon ? " x-tree-node-inline-icon" : ""),(a.iconCls ? " "+a.iconCls : ""),'" unselectable="on" />',
            cb ? ('<input class="x-tree-node-cb" type="checkbox" ' + (a.checked ? 'checked="checked" />' : '/>')) : '',
            '<div style="display: inline-block;">'+extraHTML+'<a style="display: table-cell;" hidefocus="on" class="x-tree-node-anchor" href="',href,'" tabIndex="1" ',
             a.hrefTarget ? ' target="'+a.hrefTarget+'"' : "", '><span unselectable="on">',n.text,"</span></a></div></div>",
            '<ul class="x-tree-node-ct" style="display:none;"></ul>',
            // Extra HTML (feng override)

            "</li>"].join('');
        
        

        var nel;
        if(bulkRender !== true && n.nextSibling && (nel = n.nextSibling.ui.getEl())){
            this.wrap = Ext.DomHelper.insertHtml("beforeBegin", nel, buf);
        }else{
            this.wrap = Ext.DomHelper.insertHtml("beforeEnd", targetNode, buf);
        }
        
        
        
        
        this.elNode = this.wrap.childNodes[0];
        this.ctNode = this.wrap.childNodes[1];
        var cs = this.elNode.childNodes;
        this.indentNode = cs[0];
        this.ecNode = cs[1];
        this.iconNode = cs[2];
        var index = 3;
        if(cb){
            this.checkbox = cs[3];
			
			this.checkbox.defaultChecked = this.checkbox.checked;			
            index++;
        }
      
        this.anchor = cs[index].lastChild;
        this.textNode = this.anchor.firstChild;
    }
    
    
    
    /*onSelectedChange : function(state){
        if(state){
            this.focus();
            this.addClass("x-tree-selected");
        }else{
            this.removeClass("x-tree-selected");
        }
    },*/
	
});



Ext.override(Ext.tree.TreeNode,{
	
	/*actions: [
	    {
			'url' : 'http://www.google.com',
			'text' : '',
			'iconCls' : 'ico-edit'
		}
	],*/

	   /**
     * Expand this node.
     * @param {Boolean} deep (optional) True to expand all children as well
     * @param {Boolean} anim (optional) false to cancel the default animation
     * @param {Function} callback (optional) A callback to be called when
     * expanding this node completes (does not wait for deep expand to complete).
     * Called with 1 parameter, this node.
     */
    expand : function(deep, anim, callback){
    	
        if(!this.expanded){
            if(this.fireEvent("beforeexpand", this, deep, anim) === false){
                return;
            }
            if(!this.childrenRendered){
                this.renderChildren();
            }
            this.expanded = true;
            if(!this.isHiddenRoot() && (anim !== false && this.getOwnerTree() && this.getOwnerTree().animate) || anim){
                this.ui.animExpand(function(){
                    this.fireEvent("expand", this);
                    if(typeof callback == "function"){
                        callback(this);
                    }
                    if(deep === true){
                        this.expandChildNodes(true, callback);
                    }
                }.createDelegate(this));
                return;
            }else{
                this.ui.expand();
                this.fireEvent("expand", this);
                if(typeof callback == "function"){
                    callback(this);
                }
            }
        }else{
           if(typeof callback == "function"){
               callback(this);
           }
        }
    	
    	if((!this.attributes.gettingChildsFromServer) && this.expanded && this.childNodes.length < this.attributes.realTotalChilds && this.attributes.expandable){
    		this.expanded = false;  
        	this.getUI().expand();        	
    	}
    	
        if(deep === true){
            this.expandChildNodes(true, callback);
        }
    },

    expandedNodes : function () {
    	var expanded = [] ;
    	if (this.isExpanded()) {
    		if (this.getDepth()){
    			expanded.push(this.id) ;
    		}else{
    			// Root node
    			expanded.push(0) ;
    		}
    	}
    	if ( !this.leaf ) { 
	    	this.eachChild(function(n){
	    		expanded = expanded.concat(n.expandedNodes());
	    	});
    	}
    	return expanded ;
    	
    },

    /**
     * Expand all child nodes
     * @param {Boolean} deep (optional) true if the child nodes should also expand their child nodes
     */
    expandChildNodes : function(deep, callback){
        var cs = this.childNodes;
        for(var i = 0, len = cs.length; i < len; i++) {
        	if (cs[i] && typeof(cs[i]) != 'function') {
        		cs[i].expand(deep, false, callback);
        	}
        }
    },
    
    ensureVisible : function(callback){
        var tree = this.getOwnerTree();
        if (!tree || !this.parentNode) return;
        tree.expandPath(this.parentNode.getPath(), false, function(){
            var node = tree.getNodeById(this.id);
            if (node && node.ui) {
            	tree.getTreeEl().scrollChildIntoView(node.ui.anchor);
            }
            Ext.callback(callback);
        }.createDelegate(this));
    },
    
    remove : function(){
    	if (this.parentNode && this.parentNode.ownerTree) {
    		this.parentNode.removeChild(this);
    	}
        return this;
    }

});
