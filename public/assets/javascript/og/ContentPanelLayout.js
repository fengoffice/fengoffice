/**
 *  Just like the Ext.layout.FitLayout, except that instead of having the first
 *  element fill the container panel, the first visible element is picked.
 */
og.ContentPanelLayout = Ext.extend(Ext.layout.ContainerLayout, {
    // private
    monitorResize:true,

    // private
    onLayout : function(ct, target){
        og.ContentPanelLayout.superclass.onLayout.call(this, ct, target);
        if(!this.container.collapsed){
        	var lastTransient = null;
        	for (var i=0; ct.items.itemAt(i); i++) {
        		var item = ct.items.itemAt(i);
        		if (item.hidden || item.doNotRemove) continue;
        		if (lastTransient && lastTransient !== item) {
        			if (typeof og.destroyCkEditorsInElement == 'function' && lastTransient.el) {
        				og.destroyCkEditorsInElement(lastTransient.el);
        			}
        			lastTransient.hide();
        		}
        		lastTransient = item;
        	}
        	var size = target.getStyleSize();
        	// After hiding leftover HtmlPanels, size every remaining visible
        	// child — including doNotRemove managers (FileManager, etc.).
        	for (var j=0; ct.items.itemAt(j); j++) {
        		var vis = ct.items.itemAt(j);
        		if (!vis.hidden) {
        			this.setItemSize(vis, size);
        		}
        	}
        }
    },

    // private
    setItemSize : function(item, size){
        if(item && size.height > 0){ // display none?
            item.setSize(size);
        }
    }
});
Ext.Container.LAYOUTS['contentpanel'] = og.ContentPanelLayout;