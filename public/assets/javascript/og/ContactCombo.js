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
	var is_multiple = config.is_multiple;
    var custom_selected_class = config.custom_selected_class;
    var no_style_in_selected = config.no_style_in_selected
	var selected = config.selected;
	var selected_name = config.selected_name;
	var onchange_fn = config.onchange_fn;

	var tabindex = config.tabindex | 0;

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
	var list_align = (config.listAlign ? config.listAlign : 'tl-bl');

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
        listClass: list_class,
        listAlign: list_align,
        cls: config.cls ? config.cls : 'assigned-to-combo',
        shadow: config.shadow != 'undefined' ? config.shadow : true,
        triggerAction: 'all',
        selectOnFocus: true,
        valueField: 'id',
        tabIndex: tabindex,
        emptyText: config.empty_text ? config.empty_text : (lang('select contact') + '...'),
        valueNotFoundText: '',
        inline_selector: config.inline_selector
	});
	contactsCombo.doQuery('', true);

	contactsCombo.on('focus', function(combo) {
		if (combo) combo.expand();
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
			og.selectContactFromCombo(record.data.id, record.data.name, combo, genid+render_to, genid+id, onchange_fn, is_multiple,custom_selected_class,no_style_in_selected);
		}

	});

	if(is_multiple){
        var html_labels = '<div id="'+genid+id+'_labels" class="multiple-cp-contact-labels-container"></div>';
        document.getElementById(genid + render_to).insertAdjacentHTML( 'beforeend', html_labels);
    }

	var input = document.createElement("input");
	input.setAttribute("type", "hidden");
	input.setAttribute("id", genid + id);
	if (is_multiple){
		input.setAttribute("name", 'pivot_'+name);
	}else{
		input.setAttribute("name", name);
	}

	input.setAttribute("data-name", name);
	input.setAttribute("value", "");
	var container = document.getElementById(genid + render_to);
	if (container) {
		container.appendChild(input);
	}

	if (!is_multiple){
		if (!isNaN(selected) && selected > 0) {
			og.selectContactFromCombo(selected, selected_name, contactsCombo, genid+render_to, genid+id, onchange_fn, is_multiple);
		}
	}else{
		var array_selected_ids = selected != '' ? selected.split(",") : [];
		var array_selected_names = selected_name != '' ? selected_name.split(",") : [];
		if(array_selected_ids.length > 0 && array_selected_names.length > 0){
			for(var i=0; i < array_selected_ids.length; i++){
				og.selectContactFromCombo(array_selected_ids[i], array_selected_names[i], contactsCombo, genid+render_to, genid+id, onchange_fn, is_multiple);
			}
		}

	}
}

og.selectContactFromCombo = function(contact_id, contact_name, combo, container_id, hf_id, onchange_fn, is_multiple,custom_selected_class,no_style_in_selected) {

	if(no_style_in_selected==null){
        no_style_in_selected=false;
	}
	// set hidden field values
	if (is_multiple){

		//check if all contact was deleted from combo, change the name for doing the pivot
		if(document.getElementById(hf_id).name == document.getElementById(hf_id).getAttribute('data-name')+"[0]"){
			document.getElementById(hf_id).name = 'pivot_'+document.getElementById(hf_id).getAttribute('data-name');
		}

		if (document.getElementById(hf_id).value == 0) document.getElementById(hf_id).value = "";

		var name_hidden = document.getElementById(hf_id).getAttribute("data-name");

		if (document.getElementById(hf_id).value != lang('select user') ){
			var array_contact_ids = JSON.parse("[" + document.getElementById(hf_id).value + "]");
		}else{
			var array_contact_ids = [];
		}


		if (!array_contact_ids.includes(contact_id)){
			array_contact_ids.push(contact_id);

			document.getElementById(hf_id).value =  array_contact_ids.join(", ");

			var i = array_contact_ids.length-1;
			var item = array_contact_ids[i];

			html_hiddens ='<input id=hidden_'+name_hidden+'['+i+'] class='+hf_id+' type="hidden" name='+name_hidden+'['+i+'] value='+item+' contact-id='+contact_id+'>';

			Ext.get(container_id).insertHtml('beforeEnd', html_hiddens);

		}else{
			return;
		}
	}else{
		document.getElementById(hf_id).value = contact_id;
	}


	// draw contact div and hide combo
	if (!is_multiple) combo.hide();
	var style = "min-width:300px; width:300px;";
	var remove_text = lang('remove');
	var rem_float_dir = 'right';

	if (combo.initialConfig.inline_selector) {
		style = "display:inline-flex; width:"+ combo.initialConfig.width +"px;";
		remove_text = "";
		rem_float_dir = 'left';
	}
	if(no_style_in_selected==true){
        style="";
    }

	if (!is_multiple){
		var html = '<div class="contact-sel-name-cont '+((custom_selected_class!=null)?custom_selected_class:"")+'" style="'+style+'"><div style="float:left;margin-right:5px;">'+ contact_name + '</div>' +
		'<a href="#" onclick="document.getElementById(\''+hf_id+'\').value=0;og.showContactCombo(\''+combo.getId()+'\'); Ext.get(this).parent().remove();" style="padding-left:18px;" class="link-ico ico-delete">'+remove_text+'</a></div>';
	}else{

		var html = '<div class="contact-sel-name-cont '+((custom_selected_class!=null)?custom_selected_class:"")+'" style="white-space: nowrap;'+style+'"><div class="multiple-cp-contact-div-name">'+ contact_name + '</div>' +
		'<a href="#" onclick="og.reCalculateValue('+contact_id+',\''+hf_id+'\');og.showContactCombo(\''+combo.getId()+'\'); Ext.get(this).parent().remove();" class="link-ico ico-delete multiple-cp-contact-a-remove">'+remove_text+'</a></div>';
	}

	//fill div with names contact selected and option to remove it.
	var div_id = hf_id+'_labels';

	if (document.getElementById(div_id)){
		document.getElementById(div_id).insertAdjacentHTML( 'beforeend', html);
	}else{
		Ext.get(container_id).insertHtml('beforeEnd', html);
	}

	if (typeof(onchange_fn) == 'function') {
		onchange_fn(contact_id);
	}
}

//remove id from array of contact_ids and remove label
og.reCalculateValue = function(contact_id, hf_id) {
	var array_ids = JSON.parse("[" + document.getElementById(hf_id).value + "]");

	var index = array_ids.indexOf(contact_id);
	if (index > -1) {
		array_ids.splice(index, 1);
	}

	if (array_ids.length > 0){
		var new_value = array_ids.join(", ");
	}else{
		//if all contact was deleted send original input name and empty value for delete all in DB CPV
		var new_value = "";
		document.getElementById(hf_id).name = document.getElementById(hf_id).getAttribute('data-name')+"[0]";

	}

	document.getElementById(hf_id).value = new_value;

	var elms = document.getElementsByClassName(hf_id);
	for (var i = 0; i < elms.length; i++) {
	  if (parseInt(elms[i].getAttribute("contact-id")) === contact_id){
	   var id_to_delete = elms[i].id;
	   var element_to_delete = document.getElementById(id_to_delete);
		element_to_delete.parentNode.removeChild(element_to_delete);
	  }
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
