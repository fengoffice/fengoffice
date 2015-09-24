og.ContactCombo = Ext.extend(Ext.form.ComboBox, {

	onLoad: function() {
		if (!this.hasFocus) {
			return;
		}
		if (this.store.getCount() > 0) {
			this.expand();
			this.restrictHeight();
			if (this.lastQuery == this.allQuery) {
				if (this.editable) {
					//this.el.dom.select();
				}
				if (!this.selectByValue(this.value, true)) {
					this.select(0, true);
				}
			} else {
				this.selectNext();
				if (this.typeAhead && this.lastKey != Ext.EventObject.BACKSPACE && this.lastKey != Ext.EventObject.DELETE) {
					this.taTask.delay(this.typeAheadDelay);
				}
			}
		} else {
			this.onEmptyResults();
		}
		this.el.on('change', function() {
			this.setValue(this.getRawValue());
		}, this);
	},
	
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
						rexp = new RegExp(Ext.escapeRe(q), 'i');
						this.store.filter(this.searchField, rexp);
					}
					this.onLoad();
				} else {
					if (q.length >= this.minChars) {
						this.store.baseParams[this.queryParam] = q;
						this.store.load({
							params: this.getParams(q)
						});
					}
					this.expand();
				}
			} else {
				this.selectedIndex = -1;
				this.onLoad();
			}
		}
	}
    
});
Ext.reg('contactcombo', og.ContactCombo);


og.renderContactSelector = function(config) {
	
	var genid = config.genid;
	var id = config.id;
	var name = config.name;
	var render_to = config.render_to;
	var selected = config.selected;
	var selected_name = config.selected_name;
	var onchange_fn = config.onchange_fn;
	var is_multiple = config.is_multiple;
	
	var url_params = null;
	if (!isNaN(selected) && selected > 0){
		//url_params = {'sel' : selected};
	}
	
	if (config.filters) {
		if (!url_params) url_params = {};
		url_params['filters'] = Ext.util.JSON.encode(config.filters);
	}
	
	if (config.plugin_filters) {
		if (!url_params) url_params = {};
		url_params['plugin_filters'] = Ext.util.JSON.encode(config.plugin_filters);
	}
	
	var selector_filters = config.filters;
	
	var store = new Ext.data.Store({
		proxy: new Ext.data.HttpProxy({
			method: "GET",
			url: og.makeAjaxUrl(og.getUrl('contact', 'get_contacts_for_selector', url_params))
		}),
		reader: new Ext.data.JsonReader({
			root: "contacts",
			fields: [{name: "id"},{name: "name"}]
		})
	});
	
	var list_class = (config.listClass ? config.listClass : '') + ' ' + genid;
	
	var contactsCombo = new og.ContactCombo({
		renderTo: genid + render_to,
		name: name + 'combo',
		id: genid + id + 'combo',
		value: selected,
		minChars: 0,
		store: store,
		displayField: 'name',
        mode: 'remote',
        width: !isNaN(config.width) ? config.width : 300,
		listWidth: config.listWidth ? config.listWidth : 'auto',
        cls: config.cls ? config.cls : 'assigned-to-combo',
        listClass: list_class,
        shadow: config.shadow != 'undefined' ? config.shadow : true,
        triggerAction: 'all',
        selectOnFocus: true,
        valueField: 'id',
        emptyText: config.empty_text ? config.empty_text : (lang('select contact') + '...'),
        valueNotFoundText: ''
	});
	contactsCombo.doQuery('', true);

	// ensure that dropdown list is aligned with the text input
	contactsCombo.on('expand', function(combo) {
		var ddlist = $("."+genid);
		ddlist = ddlist[0];
		
		combo.try_count = 0;
		var interval_ddlist = setInterval(function(){
			var left = $('#' + genid + id + 'combo').offset().left;
			if (left == $(ddlist).offset().left || combo.try_count >= 50) {
				clearInterval(interval_ddlist);
			} else {
				$(ddlist).css('left', left + 'px');
				$(ddlist).css('min-width', ($('#' + genid + id + 'combo').outerWidth()-2)+'px');
			}
			combo.try_count = combo.try_count + 1;
		}, 10);
	});
	
	contactsCombo.on('beforeselect', function(combo, record, index){
		
		if (record.data.id == -1) {
			// focus on text input
			setTimeout(function(){
				combo.emptyText = '';
				combo.clearValue();
				combo.focus();
			}, 100);
			
		} else if (record.data.id == -2) {
			// clear text input
			setTimeout(function(){
				combo.clearValue();
			}, 100);
			
			//show object picker
			
			og.ObjectPicker.show(function (objs) {
				if (objs && objs.length > 0) {
    				var obj = objs[0].data;
    				if (obj.type != 'contact') {
    					og.msg(lang("error"), lang("object type not supported"), 4, "err");
    				} else {
    					// build store with only the selected record
						records = [];
    					var r = new Ext.data.Record({'id':obj.object_id, 'name':obj.name}, obj.object_id);
    					records.push(r);
    					// add record to combo
    					this.store.removeAll();
    					this.store.add(records);
    					this.reset();

    					// select new record
    					this.setValue(r.data[this.valueField || this.displayField]);
    		            this.fireEvent('select', combo, r, 0);
    		            
    		            // set selected value
    		            og.selectContactFromCombo(obj.object_id, obj.name, combo, genid+render_to, genid+id, onchange_fn, is_multiple);
    				}
    			}
    		}, combo, {
    			ignore_context: true,
        		hideFilters: true,
        		sort: 'name',
        		dir: 'ASC',
        		extra_list_params: selector_filters,
    			types: ['contact'],
    			selected_type: 'contact'
    		});
		} else {
			og.selectContactFromCombo(record.data.id, record.data.name, combo, genid+render_to, genid+id, onchange_fn, is_multiple);
		}
		
	});

	var input = document.createElement("input");
	input.setAttribute("type", "hidden");
	input.setAttribute("id", genid + id);
	input.setAttribute("name", name);
	input.setAttribute("value", selected);
	var container = document.getElementById(genid + render_to);
	if (container) {
		container.appendChild(input);
	}
	
	if (!isNaN(selected) && selected > 0) {
		og.selectContactFromCombo(selected, selected_name, contactsCombo, genid+render_to, genid+id, onchange_fn, is_multiple);
	}
}

og.selectContactFromCombo = function(contact_id, contact_name, combo, container_id, hf_id, onchange_fn, is_multiple) {
	// set hidden field value
	document.getElementById(hf_id).value = contact_id;
	// draw contact div and hide combo
	if (!is_multiple) combo.hide();
	var html = '<div class="" style="min-width:500px; width:500px;">'+contact_name+
		'<a href="#" onclick="document.getElementById(\''+hf_id+'\').value=0;og.showContactCombo(\''+combo.getId()+'\'); Ext.get(this).parent().remove();" style="float:right;padding-left:18px;" class="link-ico ico-delete">'+lang('remove')+'</a></div>';
	Ext.get(container_id).insertHtml('beforeEnd', html);
	
	if (typeof(onchange_fn) == 'function') {
		onchange_fn(contact_id);
	}
}

og.showContactCombo = function(id) {
	combo = Ext.getCmp(id);
	if (combo) {
		combo.clearValue();
		combo.show();
		combo.doQuery(' ', true);
	}
}