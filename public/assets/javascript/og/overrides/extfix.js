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

Ext.form.DateField.override({
	parseDate : function(value){
        if(!value || Ext.isDate(value)){
            return value;
        }
        var v = Date.parseDate(value, this.format);
        // if current format couldn't be used, try with the same format without zeros
		if (!v) {
        	var no_zeros_format = this.format.replace('d','j').replace('m','n');
        	v = Date.parseDate(value, no_zeros_format);
        	if (!v) {
        		var no_zeros_format_y = no_zeros_format.replace('Y','y')
        		v = Date.parseDate(value, no_zeros_format_y);
        	}
        }
        if(!v && this.altFormats){
            if(!this.altFormatsArray){
                this.altFormatsArray = this.altFormats.split("|");
            }
            for(var i = 0, len = this.altFormatsArray.length; i < len && !v; i++){
                v = Date.parseDate(value, this.altFormatsArray[i]);
            }
        }
        return v;
    }
});

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

Ext.grid.GridView.override({
	// override the insertRows function to avoid focus when we insert a row 
	insertRows : function(dm, firstRow, lastRow, isUpdate){
		if(!isUpdate && firstRow === 0 && lastRow >= dm.getCount()-1){
			this.refresh();
		}else{
			if(!isUpdate){
				this.fireEvent("beforerowsinserted", this, firstRow, lastRow);
			}
			var html = this.renderRows(firstRow, lastRow);
			var before = this.getRow(firstRow);
			if(before){
				Ext.DomHelper.insertHtml('beforeBegin', before, html);
			}else{
				Ext.DomHelper.insertHtml('beforeEnd', this.mainBody.dom, html);
			}
			if(!isUpdate){
				this.fireEvent("rowsinserted", this, firstRow, lastRow);
				this.processRows(firstRow);
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
				
				man.updateGridPagingToolbar(data);
			  }});
		
			params.only_result = 0;
			params.count_results = 0;
		}
	},
	updateGridPagingToolbar: function (data) {
		var man = this;
		
		man.store.proxy.totalLength = data.totalCount;
		man.store.totalLength = data.totalCount;
				
		var bba = man.getBottomToolbar();
		bba.updateInfo();
		
		var total_pag =  data.totalCount < bba.pageSize ? 1 : Math.ceil(data.totalCount/bba.pageSize);
		if (bba.afterTextEl) {
			bba.afterTextEl.el.textContent = String.format(bba.afterPageText, total_pag);
		}
		
		// enable toolbar
		bba.enable();
		
		var current_page = $("#"+man.id+" .x-tbar-page-number").val();
		
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
		
		if (bba.loading) bba.loading.enable();
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
	},
	updateColumnModelHiddenColumns: function() {
		var hidden_columns = this.hiddenColumnIds;
		if (hidden_columns && hidden_columns.length > 0) {
			var cm = this.getColumnModel();
			for (var i=0; i<cm.config.length; i++) {
				if (hidden_columns.indexOf(cm.config[i].id) !== -1) {
					cm.config[i].hidden = true;
				}
			}
			cm.fireEvent('configchange');
		}
	},
	
	afterColumnShowHide: function(col_model, col_index, is_hidden) {
		var col = col_model.config[col_index];
		if (col.id && col.id.indexOf('cp_') == 0 && this.hiddenColumnIds) {
			var h_index = this.hiddenColumnIds.indexOf(col.id);
			if (is_hidden && h_index == -1) {
				this.hiddenColumnIds.push(col.id);
			} else if (!is_hidden && h_index >= 0) {
				this.hiddenColumnIds.splice(h_index, 1);
			}
		}
    },
    
	addCustomPropertyColumns: function(cps, cm_info, grid_id) {
		this.hiddenColumnIds = [];
		
		var last_state = Ext.state.Manager.getProvider().state;
		var last_grid_state = last_state ? last_state[grid_id] : null;
		
		for (i=0; i<cps.length; i++) {
			// check last option saved in the gui state
			var state_col = null;
			if (last_grid_state && last_grid_state.columns) {
				for (var j=0; j<last_grid_state.columns.length; j++) {
					if (last_grid_state.columns[j].id == 'cp_' + cps[i].id) {
						state_col = last_grid_state.columns[j];
						break;
					}
				}
			}
			
			// if no state is present for this column then use the show in lists field of the cp
			var is_hidden = (state_col == null) ? parseInt(cps[i].show_in_lists) == 0 : state_col.hidden;
			if (is_hidden) {
				this.hiddenColumnIds.push('cp_' + cps[i].id);
			}
			cm_info.push({
				id: 'cp_' + cps[i].id,
				hidden: is_hidden,
				header: cps[i].name,
				align: cps[i].cp_type=='numeric' ? 'right' : 'left',
				dataIndex: 'cp_' + cps[i].id,
				sortable: true,
				renderer: 'string' //cps[i].cp_type=='image' ? 'string' : og.clean
			});
		}
	}
});

Date.getShortMonthName = function(month) {
	var short_lang = lang("month "+(month+1)+" short");
	if (short_lang && short_lang.indexOf("Missing lang") == -1) return short_lang;
    return Date.monthNames[month].substring(0, 3);
}


/**/
