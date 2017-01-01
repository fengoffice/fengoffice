Ext.BLANK_IMAGE_URL = "s.gif";
// fix node background problem
Ext.override(Ext.tree.TreeEventModel, {
	initEvents : function(){
		var el = this.tree.getTreeEl();
		el.on('click', this.delegateClick, this);
		if(this.tree.trackMouseOver !== false){
			var innerCt = Ext.fly(el.dom.firstChild);
			innerCt.on('mouseover', this.delegateOver, this);
			innerCt.on('mouseout', this.delegateOut, this);
		}
		el.on('dblclick', this.delegateDblClick, this);
		el.on('contextmenu', this.delegateContextMenu, this);
	}
});
// disable keyboard navigation on tree panels
Ext.tree.DefaultSelectionModel.override({
   onKeyDown: Ext.emptyFn
});
/**/
// Uncomment this to support drag and drop in grids
// Also uncomment enableDrag on grids, enableDrop on workspacepanel and ddGroup on both
/*Ext.grid.CheckboxSelectionModel.override({
    handleMouseDown: Ext.emptyFn
});*/

Ext.grid.CheckboxSelectionModel.override({
	handleMouseDown: function(grid, rowIndex, e) {
		var t = e.getTarget();
		var checkbox_clicked = (t.className == 'x-grid3-row-checker');
		
		if(e.button === 0){
			e.stopEvent();
			var row = e.getTarget('.x-grid3-row');
			if(row){
				var index = row.rowIndex;
				if(this.isSelected(index)){
					if (!checkbox_clicked) this.deselectRow(index);
				}else{
					if (e.shiftKey) {
						// range selection
	            		var sels = this.getSelections();
	            		if (sels.length > 0) {
	            			// get first selected index
	            			var first_idx = 0;
	            			var all_items = sels[0].store.data.items;
	            			for (var k=0; k<all_items.length; k++) {
	            				if (sels[0].data.object_id == all_items[k].data.object_id) break;
	            				first_idx++;
	            			}
	            			
	            			if (checkbox_clicked) {
	            				// avoid selecting the clicked checkbox row, let its handler to select it.
	            				if (first_idx < index) {
	            					index = index-1;
	            				} else {
	            					index = index+1;
	            				}
	            			}
	            			// make the selection
	            			this.selectRange(first_idx, index);
	            		}
	            	} else {
	            		if (checkbox_clicked) {
	            			// let the checkbox handler to handle the selection
	            			return;
	            		}
	            		// single selection, if ctrlKey then keep previous selection
	            		this.selectRow(index, e.ctrlKey === true);
	            	}
				}
			}
		}
	}
});

Ext.grid.GridView.override({
	focusCell : function(row, col, hscroll){
		this.syncFocusEl(this.ensureVisible(row, col, hscroll));
		this.focusEl.focus.defer(1, this.focusEl);
	},

    syncFocusEl : function(row, col, hscroll){
        var xy = row;
        if(!Ext.isArray(xy)){
            row = Math.min(row, Math.max(0, this.getRows().length-1));
            //xy = this.getResolvedXY(this.resolveCell(row, col, hscroll));
        }
        this.focusEl.setXY(xy||this.scroller.getXY());
    }
});
Ext.grid.RowSelectionModel.override({
    initEvents : function() {
        if (!this.grid.enableDragDrop && !this.grid.enableDrag) {
        	this.grid.on("rowmousedown", this.handleMouseDown, this);
        } else { // allow click to work like normal
        	this.grid.on("rowclick", function(grid, rowIndex, e) {
        		
            }, this);
        }

        this.rowNav = new Ext.KeyNav(this.grid.getGridEl(), {
            "up" : function(e){
                if(!e.shiftKey){
                    this.selectPrevious(e.shiftKey);
                }else if(this.last !== false && this.lastActive !== false){
                    var last = this.last;
                    this.selectRange(this.last,  this.lastActive-1);
                    this.grid.getView().focusRow(this.lastActive);
                    if(last !== false){
                        this.last = last;
                    }
                }else{
                    this.selectFirstRow();
                }
            },
            "down" : function(e){
                if(!e.shiftKey){
                    this.selectNext(e.shiftKey);
                }else if(this.last !== false && this.lastActive !== false){
                    var last = this.last;
                    this.selectRange(this.last,  this.lastActive+1);
                    this.grid.getView().focusRow(this.lastActive);
                    if(last !== false){
                        this.last = last;
                    }
                }else{
                    this.selectFirstRow();
                }
            },
            scope: this
        });

        var view = this.grid.view;
        view.on("refresh", this.onRefresh, this);
        view.on("rowupdated", this.onRowUpdated, this);
        view.on("rowremoved", this.onRemove, this);        
    }
});



Ext.override(Ext.Element, {
	getAttributeNS : function(ns, name){
		
		if (Ext.isIE) {
			var ieVer = navigator.userAgent.match(/msie (\d+)/i);
			ieVer = ieVer ? parseInt(ieVer[1], 10) : 0;
		}
		
		if (!Ext.isIE || ieVer >= 9) {
			var d = this.dom;
		    return d.getAttributeNS(ns, name) || d.getAttribute(ns+":"+name) || d.getAttribute(name) || d[name];
		} else {
			var d = this.dom;
		    var type = typeof d[ns+":"+name];
		    if(type != 'undefined' && type != 'unknown'){
		        return d[ns+":"+name];
		    }
		    return d[name];
		}
	}
});


// IE 9 does not implement function createContextualFragment for range objects
if (typeof Range != "undefined") {
	if (typeof Range.prototype.createContextualFragment == "undefined") {
	    Range.prototype.createContextualFragment = function (html) {
	        var doc = window.document;
	        var container = doc.createElement("div");
	        container.innerHTML = html;
	        var frag = doc.createDocumentFragment(), n;
	        while ((n = container.firstChild)) {
	            frag.appendChild(n);
	        }
	        return frag;
	    };
	}
}

Ext.form.ComboBox.override({
	doQuery: function(q, forceAll) {
		if (q === undefined || q === null) {
			q = '';
		}
		var qe = {
			query: q,
			forceAll: forceAll,
			combo: this,
			cancel:false
		};
		if (this.fireEvent('beforequery', qe)===false || qe.cancel) {
			return false;
		}
		q = qe.query;
		forceAll = qe.forceAll;
		if (forceAll === true || (q.length >= this.minChars)) {
			if (this.lastQuery !== q) {
				this.lastQuery = q;
				if (this.mode == 'local') {
					this.selectedIndex = -1;
					if (forceAll) {
						this.store.clearFilter();
					} else {
						this.store.filter(this.displayField, q, true);
					}
					this.onLoad();
				} else {
					this.store.baseParams[this.queryParam] = q;
					this.store.load({
						params: this.getParams(q)
					});
					this.expand();
				}
			} else {
				this.selectedIndex = -1;
				this.onLoad();
			}
		}
	}
});

Ext.grid.GridPanel.override({
	reloadGridPagingToolbar: function (  controller , func , manager) {
		var bba_before = Ext.getCmp(manager).getBottomToolbar();
		if (bba_before.loading){
			bba_before.loading.disable();
		}
		var params = Ext.getCmp(manager).store.lastOptions.params;
		
		if(typeof params != 'undefined'){
			delete params.action;
			params.only_result = 1;
			Ext.getCmp(manager).getBottomToolbar().disable();
			og.openLink(og.getUrl(controller, func, params), {
			  hideLoading: true,
			  callback: function(success, data) {
				var man = Ext.getCmp(manager);
				if (!man) return;
				
				man.store.proxy.totalLength = data.totalCount;
				man.store.totalLength = data.totalCount;
						
				var bba = man.getBottomToolbar();
				bba.updateInfo();
				
				var total_pag =  data.totalCount < bba.pageSize ? 1 : Math.ceil(data.totalCount/bba.pageSize)
				bba.afterTextEl.el.textContent = String.format(bba.afterPageText, total_pag);
				
				// enable toolbar
				bba.enable();
				
				var current_page = $("#"+manager+" .x-tbar-page-number").val();
				
				// disable prev,first buttons if first page
				if(parseInt(current_page) == 1) {					
					bba.first.disable();
					bba.prev.disable();
				}
				if(parseInt(current_page) == 1 && total_pag > 1){
					bba.last.enable();
					bba.next.enable();
				}
				
				//if((parseInt(data.totalCount) - parseInt(data.start))<= parseInt(bba.pageSize)){
				if(Math.ceil(parseInt(data.totalCount) / parseInt(bba.pageSize)) == current_page){
					bba.last.disable();
					bba.next.disable();
				}
				
				bba.loading.enable();
			  }});
		
			params.only_result = 0;
			params.count_results = 0;
		}
	},
	columnModelHasDimensionAssociations: function() {
		var man = this;
		var has_associations = false;
		var cm = man.getColumnModel();
		for (var i=0; i<cm.config.length; i++) {
			if (cm.config[i].id.indexOf('dimassoc_') == 0) {
				has_associations = true;
				break;
			}
		}
		return has_associations;
	}
});

Date.getShortMonthName = function(month) {
	var short_lang = lang("month "+(month+1)+" short");
	if (short_lang && short_lang.indexOf("Missing lang") == -1) return short_lang;
    return Date.monthNames[month].substring(0, 3);
}


/**/
