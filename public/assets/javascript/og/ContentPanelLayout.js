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
        	// get first visible panel
        	for (var i=0; ct.items.itemAt(i); i++) {
        		if (!ct.items.itemAt(i).hidden) {
            		this.setItemSize(this.activeItem || ct.items.itemAt(i), target.getStyleSize());
            	}
            }
            this.setItemSize(this.activeItem || ct.items.itemAt(0), target.getStyleSize());
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