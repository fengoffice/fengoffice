Ext.ns('Ext.ux.layout');

/**
 * @class Ext.ux.layout.MultiAccordion
 * @extends Ext.layout.FitLayout
 * <p>Like Accordion Layout with confugurable max active items:</p>
 **/
Ext.ux.layout.MultiAccordion = Ext.extend(Ext.layout.ContainerLayout, {
	

	
    monitorResize:true,

	/**
	 *  @cfg {Integer} maxActive
	 *  Max number of active panels 
	 *  Default = 1
	 */
	maxActiveItems: 1 ,
	
	/**
	 * 
	 */
	activeItemsCount: 0,
	
	/**
	 * 
	 */
	activeItems: [],	
    /**
     * @cfg {Boolean} fill
     * True to adjust the active item's height to fill the available space in the container, false to use the
     * item's current height, or auto height if not explicitly set (defaults to true).
     */
    fill : true,
    /**
     * @cfg {Boolean} autoWidth
     * True to set each contained item's width to 'auto', false to use the item's current width (defaults to true).
     */
    autoWidth : true,
    /**
     * @cfg {Boolean} titleCollapse
     * True to allow expand/collapse of each contained panel by clicking anywhere on the title bar, false to allow
     * expand/collapse only when the toggle tool button is clicked (defaults to true).  When set to false,
     * {@link #hideCollapseTool} should be false also.
     */
    titleCollapse : true,
    /**
     * @cfg {Boolean} hideCollapseTool
     * True to hide the contained panels' collapse/expand toggle buttons, false to display them (defaults to false).
     * When set to true, {@link #titleCollapse} should be true also.
     */
    hideCollapseTool : false,
    /**
     * @cfg {Boolean} collapseFirst
     * True to make sure the collapse/expand toggle button always renders first (to the left of) any other tools
     * in the contained panels' title bars, false to render it last (defaults to false).
     */
    collapseFirst : false,
    /**
     * @cfg {Boolean} animate
     * True to slide the contained panels open and closed during expand/collapse using animation, false to open and
     * close directly with no animation (defaults to false).  Note: to defer to the specific config setting of each
     * contained panel for this property, set this to undefined at the layout level.
     */
    animate : false,
    /**
     * @cfg {Boolean} sequence
     * <b>Experimental</b>. If animate is set to true, this will result in each animation running in sequence.
     */
    sequence : false,
    /**
     * @cfg {Boolean} activeOnTop
     * True to swap the position of each panel as it is expanded so that it becomes the first item in the container,
     * false to keep the panels in the rendered order. <b>This is NOT compatible with "animate:true"</b> (defaults to false).
     */
    activeOnTop : false,

    /**
     * 
     */
    addActiveItem : function (item){
	    	this.activeItems.push(item) ;
	    	this.activeItemsCount++ ;
    },
    

    
    renderItem : function(c){
	        if(this.animate === false){
	            c.animCollapse = false;
	        }
	        c.collapsible = true;
	        if(this.autoWidth){
	            c.autoWidth = true;
	        }
	        if(this.titleCollapse){
	            c.titleCollapse = true;
	        }
	        if(this.hideCollapseTool){
	            c.hideCollapseTool = true;
	        }
	        if(this.collapseFirst !== undefined){
	            c.collapseFirst = this.collapseFirst;
	        }
	        if( this.activeItems.length < this.maxActiveItems && !c.collapsed && !c.hidden ){
        		this.addActiveItem(c) ;
	        }else {
	            c.collapsed = true;
	        }
	        Ext.ux.layout.MultiAccordion.superclass.renderItem.apply(this, arguments);
	        c.header.addClass('x-accordion-hd');
	        c.on('beforeexpand', this.beforeExpand, this);
	        c.on('beforecollapse', this.beforeCollapse, this);
	        c.on('beforehide', this.beforeHide, this);
    },

    beforeHide : function (p) {
    	p.hidden = true ;
    	this.layout();
    },
    
    beforeCollapse : function (p, anim) {
    	// find this item on active items collection and delete it  //TODO performance make a map
    	var ais = this.activeItems ;
    	this.activeItemsCount-- ;
    	var deleted = false ;
    	for (var i = 0  ; i < ais.length ; i++ ) {
    		if ( ais[i] && ais[i].id ==  p.id ) {
    			var deleted = true ;
    			delete this.activeItems[i];
    			break;
    		}
    	}

        p.willExpand = false ;
        p.willCollapse = true ;   
        this.layout();
        
    },
    
    
    // private
    beforeExpand : function(p, anim){
        var ais = this.activeItems;
        var max = this.maxActiveItems;
		if(this.activeItemsCount >= max) {
			for (var i = 0  ; i<this.activeItems.length ; i++) {
				if ( this.activeItems[i] ) {
					this.activeItems[i];
					this.activeItems[i].collapse(false);
					break;
				}
			}
		}
        this.addActiveItem(p);
        if(this.activeOnTop){
            p.el.dom.parentNode.insertBefore(p.el.dom, p.el.dom.parentNode.firstChild);
        }
        p.willExpand = true ;
        p.willCollapse = false ;        
        this.layout();
        

    },

    
    // private
    onLayout : function(ct, target){
        Ext.ux.layout.MultiAccordion.superclass.onLayout.call(this, ct, target);
        if(!this.container.collapsed){
            this.setActiveItemsSize(target.getStyleSize());
        }
    },
    
    /*
     * size: Size of the container (widh,height)
     */
    setActiveItemsSize : function(size){
    	var margins = 7 ;
    	var delta = 30 ;// Change this value when you change item heigt/margins via css
        if(this.fill){

            var items = this.container.items.items; // Each Tree
            var hh = 0 ; // Collapsed items height
            
            for(var i = 0, len = items.length; i < len; i++){
                var p = items[i];
                if ( p.willCollapse && !p.hidden || ( p.collapsed && !p.hidden && !p.willExpand )  ) {
                	var delta = (p.getSize().height - p.bwrap.getHeight()) ;
                	delta += margins ;
                    hh += delta ;

               		//var margins = $("#"+p.id).outerHeight(true) - $("#"+p.id).outerHeight(false) ;
                   // hh += 25 ;
                    //this.debug("hh delta="+ (p.getSize().height - p.bwrap.getHeight()));
                	/*var htmlEl = document.getElementById(p.id);
               		var margins = $("#"+p.id).outerHeight(true) - $("#"+p.id).outerHeight(false) ;
                	hh += (p.getSize().height - p.bwrap.getHeight()) + margins ;*/
                }	
            }
            
            //this.debug("("+size.height + " - " + hh/delta +"*23 ) /  "+ this.activeItemsCount + " = "+ (  size.height - hh ) / this.activeItemsCount  ) ;
            // Set each active item the new calc size
            size.height = ( size.height - hh ) / this.activeItemsCount - margins ;
            
           // this.debug("height="+  size.height + "hh="+hh + "delta = "+ delta  + "tree size = " +  size.height);
            var dbg = '' ;
            for (var i = 0 ; i < this.activeItems.length ; i++) {
            	if ( this.activeItems[i] ) {
            		
            		//dbg += "\n * " + this.activeItems[i].title + "\n" ; 
            		this.activeItems[i].setSize(size);
            		this.activeItems[i].doLayout();
            	}
            }
            
            //og.msg("",dbg,5);
        }
    },
    
    
    debug : function (title) {
    	var out = '' ;
    	out += title+'\n';
    	out += '-------------------\n'; 
    	out += '* ACTIVE ITEMS COUNT: ' + this.activeItemsCount + '\n';
    	out += '* ACTIVE ITEMS : \n';
    	for (var i = 0 ; i < this.activeItems.length ; i++) {
    		if (item = this.activeItems[i]){
    			out += '      *'+item.title + '\n' ; 
    		}
    	}
    	og.msg("",out, 15);
    }
    
});
Ext.Container.LAYOUTS['multi-accordion'] = Ext.ux.layout.MultiAccordion;