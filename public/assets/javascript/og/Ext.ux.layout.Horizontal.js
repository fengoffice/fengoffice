Ext.ns('Ext.ux.layout');

Ext.ux.layout.HorizontalLayout = Ext.extend(Ext.layout.ContainerLayout, {
	
	
	
    // private
    monitorResize:true,
    
	/**
	 *  @cfg {Integer} maxActive
	 *  Max number of active panels 
	 *  Default = 1
	 */
	//maxActiveItems: 1 ,
	
	/**
	 * 
	 */
	//activeItemsCount: 0,
    
    // private
    extraCls: 'x-column',

    scrollOffset : 0,

    // private
    isValidParent : function(c, target){
        return c.getEl().dom.parentNode == this.innerCt.dom;
    },
    
    
    // private
    renderItem : function(c, position, target){
        if(c && !c.rendered){
            c.render(target, position);
            if(this.extraCls){
            	var t = c.getPositionEl ? c.getPositionEl() : c;
            	t.addClass(this.extraCls);
            }
            if (this.renderHidden && c != this.activeItem) {
                c.hide();
            }
        }else if(c && !this.isValidParent(c, target)){
            if(this.extraCls){
                c.addClass(this.extraCls);
            }
            if(typeof position == 'number'){
                position = target.dom.childNodes[position];
            }
            target.dom.insertBefore(c.getEl().dom, position || null);
            if (this.renderHidden && c != this.activeItem) {
                c.hide();
            }
        }
        c.header.addClass('horizontal-hd');
        c.on('beforeexpand', this.beforeExpand, this);
        c.on('beforecollapse', this.beforeCollapse, this);
    },
    
    // private
    renderRange : function(ct, target, start, limit){
        var items = ct.items.items;
        for(var i = start, len = Math.min( items.length, limit + start) ; i < len; i++) {
            var c = items[i];
            if(c && (!c.rendered || !this.isValidParent(c, target))){
                this.renderItem(c, i, target);
            }
        }
    },
    
    
    beforeCollapse :  function(p, anim){
    	p.addClass("horizontal-collapsed") ;
    	p.afterCollapse(); //doy vuelta el boton
    },
    
    
    beforeExpand : function(p, anim){
    	p.removeClass("horizontal-collapsed") ;
    },

    // private
    onLayout : function(ct, target){
        var cs = ct.items.items, len = cs.length, c, i;

        if(!this.innerCt){
            target.addClass('x-column-layout-ct');

            // the innerCt prevents wrapping and shuffling while
            // the container is resizing
            this.innerCt = target.createChild({cls:'x-column-inner'});
            this.innerCt.createChild({cls:'x-clear'});
        }
        
        this.renderAll(ct, this.innerCt);
        //this.renderRange(ct, this.innerCt, 0 , this.maxActiveItems );
        
        
        var size = Ext.isIE && target.dom != Ext.getBody().dom ? target.getStyleSize() : target.getViewSize();

        if(size.width < 1 && size.height < 1){ // display none?
            return;
        }

        var w = size.width - target.getPadding('lr') - this.scrollOffset,
            h = size.height - target.getPadding('tb'),
            pw = w;

        this.innerCt.setWidth(w);
        
        // some columns can be percentages while others are fixed
        // so we need to make 2 passes

        for(i = 0; i < len; i++){
            c = cs[i];
            if(!c.columnWidth){
                pw -= (c.getSize().width + c.getEl().getMargins('lr'));
            }
        }

        pw = pw < 0 ? 0 : pw;

        for(i = 0; i < len; i++){
            c = cs[i];
            if(c.columnWidth){
                c.setSize(Math.floor(c.columnWidth*pw) - c.getEl().getMargins('lr'));
            }
        }
    }
    

});

Ext.Container.LAYOUTS['horizontal'] = Ext.ux.layout.HorizontalLayout;