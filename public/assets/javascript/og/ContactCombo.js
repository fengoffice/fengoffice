og.ContactCombo = Ext.extend(Ext.form.ComboBox, {

	onLoad: function() {
		if (!this.hasFocus) {
			return;
		}
                og.ContactCombo.addQuickContactButton(this);
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
    var no_style_in_selected = config.no_style_in_selected;
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
        url_params['object_id'] = Ext.util.JSON.encode(config.id);
	}

	if (config.plugin_filters) {
		if (!url_params) url_params = {};
		url_params['plugin_filters'] = Ext.util.JSON.encode(config.plugin_filters);
        url_params['object_id'] = Ext.util.JSON.encode(config.id);
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

	if(config.is_bootstrap){
        config.width = '100%';
	}else{
        config.width = !isNaN(config.width) ? config.width : 300;
	}
	var contactsCombo = new og.ContactCombo({
		renderTo: genid + render_to,
		name: name + 'combo',
		id: genid + id + 'combo',
		value: selected,
		minChars: 0,
		store: store,
		displayField: 'name',
        mode: 'remote',
        width: config.width,
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
        inline_selector: config.inline_selector,
        disabled:config.disabled,
        hidden:config.disabled || config.hidden,
        cp_type:config.cp_type,
		is_bootstrap:config.is_bootstrap
	});
	contactsCombo.doQuery('', true);
	
	contactsCombo.config_parameters = config;

	contactsCombo.on('focus', function(combo) {
            og.ContactCombo.addQuickContactButton(this);
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
            } else if(record.data.id == -3){
                    // focus on text input
                    setTimeout(function(){
                        combo.emptyText = '';
						combo.clearValue();
						combo.focus();
                    }, 50);
                    var quickConfig = {
                        combo:combo,
                        genid_r:genid+render_to,
                        gendid:genid+id,
                        onchange:onchange_fn,
                        multiple:is_multiple,
                        class:custom_selected_class,
                        style:no_style_in_selected
                    }
                    og.renderModalQuickContact(config.memberId,combo.id,genid+render_to,genid+id,is_multiple);
                    return true;
            }else {
		        og.selectContactFromCombo(record.data.id, record.data.name, combo, genid+render_to, genid+id, onchange_fn, is_multiple,custom_selected_class,no_style_in_selected,record.json.unclassified);
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
		//var array_selected_names = selected_name != '' ? selected_name.split(",") : [];
		if(array_selected_ids.length > 0){
			for(var i=0; i < array_selected_ids.length; i++){
				og.selectContactFromCombo(array_selected_ids[i], null, contactsCombo, genid+render_to, genid+id, onchange_fn, is_multiple);
			}
		}

	}
	
	if (config.disabled) {
		$("#"+genid + render_to+" a.link-ico.ico-delete").remove();
	}
}


og.selectContactFromCombo = function(contact_id, contact_name, combo, container_id, hf_id, onchange_fn, is_multiple,custom_selected_class,no_style_in_selected,unclassified) {
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

	let onchange_fn_str = "";
	if (typeof(onchange_fn) == 'function') {
		onchange_fn(contact_id);
		onchange_fn_str = onchange_fn.name + "("+contact_id+");";
	}

	// draw contact div and hide combo
	if (!is_multiple) combo.hide();

	// after selection show only the contact name or the full card
	let show_only_name = combo.config_parameters.show_only_name ? '1' : '0';

	og.openLink(og.getUrl('contact', 'contact_selector_contact_card'), {
		hideLoading: true,
		preventPanelLoad: true,
		post: {
			id: contact_id,
			combo_id: combo.getId(),
			hf_id: hf_id,
			is_multiple: is_multiple ? '1' : '0',
			container_id: container_id,
			onchange_fn_str: onchange_fn_str,
			show_only_name: show_only_name,
		},
		callback: function (success, data) {
			if (success && data && data.html) {
				$('#' + data.post_vars.container_id).append(data.html);
			}
			og.hideLoading();
		}
	});

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

/**
 * Renders the modal form for adding a quick contact.
 * 
 * @param {number} member_id - The ID of the member to associate with the contact.
 * @param {string} combo_id - The ID of the combo box to update.
 * @param {string} render_to - The target element for rendering.
 * @param {string} genid - The generated ID for the form.
 * @param {boolean} multiple - Indicates if multiple contacts can be selected.
 */
og.renderModalQuickContact = function (member_id, combo_id, render_to, genid, multiple) {

	// Open the link to the quick add form page
	og.openLink(og.getUrl('contact','quick_add_form'),{
		// Pass the member ID, combo box ID, generated ID, render target,
		// and multiple flag as parameters
		post: {
			member_id: member_id,
			combo_id: combo_id,
			genid: genid,
			render_to: render_to,
			multiple: multiple
		},
		// Hide the loading indicator
		hideLoading: true,
		// Don't load the form in a panel
		preventPanelLoad: true,
		// Callback to execute when the form is loaded
		callback: function(success, data) {
			if (success) {
				// Extract the HTML from the response and show it in the modal
				html = og.extractScripts(data.current.data);
				og.ExtModal.show({
					html: html
				});
			}
		}
	});
	
}

/**
 * Returns an array of client member IDs that are selected in the form.
 * It goes through all the associations of the object type 'client' and
 * checks if the associated member field is set. If it is, it parses the
 * value as a JSON array and concatenates it with the current member IDs.
 */
og.getFormSelectedClientMemberIds = function() {

	let mem_ids = [];
	if (og.customers) {
		// iterate over all dimension member associations
		for (x in og.dimension_member_associations_by_id) {
			let assoc = og.dimension_member_associations_by_id[x];
			if (typeof assoc == 'function') continue;

			// when the association is with the object type 'client' get the value
			if (assoc.assoc_object_type_id == og.customers.object_type_id) {
				let val = $('[name="associated_members\['+ assoc.id +'\]"]').val();
				if (val) {
					let cli_mem_ids = JSON.parse(val);
					if (cli_mem_ids && cli_mem_ids.length > 0) {
						mem_ids = mem_ids.concat(cli_mem_ids);
					}
				}
			}
		}
	}
	
	return mem_ids;
}

/**
 * Adds a quick contact from a modal form.
 * 
 * @param {number} member_id - The ID of the member to associate with the contact.
 * @param {string} combo_id - The ID of the combo box to update.
 * @param {string} render_to - The target element for rendering.
 * @param {string} genid - The generated ID for the form.
 * @param {boolean} multiple - Indicates if multiple contacts can be selected.
 */
og.addQuickContactFromModal = function (member_id, combo_id, render_to, genid, multiple){
    
    // Construct the form ID
    let form_id = genid + "submit-edit-form";

	// build the classification of the new contact, the current member + the selected client members if any
	let member_ids = [];
	member_ids.push(member_id);
	let client_member_ids = og.getFormSelectedClientMemberIds();
	if (client_member_ids.length > 0) {
		member_ids = member_ids.concat(client_member_ids);
	}
	let member_ids_str = member_ids.join(',');
    
    // Initialize the post object with default values
    og.quickadd_contact_post_object = {
        from_quick_add: 1,
        members: '[' + member_ids_str + ']',
    };
    
    // Collect form input values and populate the post object
    $('.contact-quick-add #' + form_id + ' input, .contact-quick-add #' + form_id + ' textarea, .contact-quick-add #' + form_id + ' select').each(function(index) {
        og.quickadd_contact_post_object[$(this).attr('name')] = $(this).val();
    });

    // Send an AJAX request to add the contact
    og.openLink(og.getUrl('contact', 'add'), {
        hideLoading: true,
        preventPanelLoad: true,
        post: og.quickadd_contact_post_object,
        callback: function(success, data) {
            if (success) {
                // If successful, update the combo box with the new contact
                var combo = Ext.getCmp(combo_id);
                if (combo) {
                    og.selectContactFromCombo(data.contact_id, data.contact_name, combo, render_to, genid, '', multiple);
                }
                // Hide the modal dialog
                og.ExtModal.hide();
            }
        }
    });
}

og.ContactCombo.addQuickContactButton = function (object){
    if (object.cp_type == "contact"){
        var flag = true;
        var data = {'id':-3, 'name':'<a href="#" class="db-ico ico-expand ico-task" style="color:blue;text-decoration:underline;padding-left:20px;">'+lang('add contact')+'</a>'};
        if(object.store.getCount()>0){
            object.store.each(function(D){
                if(D.data.id == data.id){
                    flag = false;
                }
            })
        };
        if(flag){
            var r = new Ext.data.Record(data, 0);
            object.store.insert(0,r);
        }
    }
}

og.contentModalAddUnclassified = function (container_id,div_id){

    var method = "og.addUnclassifiedContactFromModal('"+container_id+"','"+div_id+"')";
    var button_content = lang("accept");
    return '<div id="modalQuickContact" class="coInputHeader">'
        +'<div class="coInputName">'
        +'<p class="coInputName" style="max-width: 510px;text-align:  justify;font-size: 17px;">'+lang('unclassified message')+'</p>'
        +'</div>'
        +'<div class="coInputButtons" style="float:  none;width: 100%;">'
        +'<button style="margin-top:0px;margin-left:10px;float: right;" id="submit" class="submit " type="submit" accesskey="s" onclick="'+method+'">'+button_content+'</button></div>'
        +'<div class="clear"></div>'
        +'<div class="clear"></div>'
        +'</div>';
};

og.addUnclassifiedContactFromModal = function (container_id, div_id) {
    if (document.getElementById(div_id)){
        document.getElementById(div_id).insertAdjacentHTML( 'beforeend', og.elementToAddUnclassified);
    }else{
        Ext.get(container_id).insertHtml('beforeEnd', og.elementToAddUnclassified);
    }
    og.ExtModal.hide();
}