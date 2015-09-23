if (!member_selector) var member_selector = {};

member_selector.init = function(genid) {

	member_selector[genid].sel_context = {};
	var json_sel_ids = document.getElementById(genid + member_selector[genid].hiddenFieldName).value;
	var selected_member_ids = json_sel_ids == "" ? [] : Ext.util.JSON.decode(json_sel_ids);
	
	var dimension_to_get = new Array();
	if (selected_member_ids) {
	  for (i=0; i<selected_member_ids.length; i++) {
		var mid = selected_member_ids[i];
		if (member_selector[genid].members_dimension[mid] > 0) {
			var dim = member_selector[genid].members_dimension[mid];
			if (!member_selector[genid].sel_context[dim]) {
				member_selector[genid].sel_context[dim] = [];
			}
			member_selector[genid].sel_context[dim].push(mid);
			
			if (selected_member_ids.length == i) {
				var idshf = document.getElementById(genid+'subscribers_ids_hidden');
				if (idshf) og.reload_subscribers(genid, member_selector[genid].otid, idshf.value);
			}			
		} else {
			dimension_to_get.push(mid);
		}
	  }
	}
	
	// fill store with preloaded members
	member_selector.preload_members(genid);
	
	og.openLink(og.getUrl('member', 'get_dimension_id', {member_id: Ext.util.JSON.encode(dimension_to_get)}), {callback: function(success, data){
		
		if (!data.dim_ids) {
			og.eventManager.fireEvent('after member_selector init', null);
			return;
		}
		
		for (var i=0;i<data.dim_ids.length;i++){
			if (!member_selector[genid].sel_context[data.dim_ids[i].dim_id]) {
				member_selector[genid].sel_context[data.dim_ids[i].dim_id] = [];
			}
			
			member_selector[genid].sel_context[data.dim_ids[i].dim_id].push(data.dim_ids[i].member_id);
			member_selector[genid].members_dimension[data.dim_ids[i].member_id] = data.dim_ids[i].dim_id;
		}
		
		//RENDER
		if (selected_member_ids.length == i) {
			var idshf = document.getElementById(genid+'subscribers_ids_hidden');
			if (idshf) og.reload_subscribers(genid, member_selector[genid].otid, idshf.value);
		}
		
		//render Invited people in event
		if ($("#"+genid+"add_event_invitation_div").length > 0) {
			og.redrawPeopleList(genid);
		}
		
		for (dim in member_selector[genid].sel_context) {
			if (!dim) continue;
			if (member_selector[genid].sel_context[dim].length > 0) {
				member_selector.reload_dependant_selectors(dim, genid);				
			}
		}
		
		og.eventManager.fireEvent('after member_selector init', null);	
		og.eventManager.fireEvent('replace all empty breadcrumb', null);
	}});
	
	if (selected_member_ids.length == 0) {
		var idshf = document.getElementById(genid+'subscribers_ids_hidden');
		if (idshf) og.reload_subscribers(genid, member_selector[genid].otid, idshf.value);
	}	
}

member_selector.autocomplete_select = function(dimension_id, genid, combo, record, preload) {
	combo.setValue(record.data.name);
	combo.selected_member = record.data;
	member_selector.add_relation(dimension_id, genid);
	
	// fill store with preloaded members
	if(preload){
		member_selector.preload_members(genid, dimension_id);
	}
}

member_selector.add_relation = function(dimension_id, genid, member_id, show_actions) {
	if (typeof member_id == "undefined") {
		var combo = Ext.getCmp(genid + 'add-member-input-dim' + dimension_id);
		var member = combo.selected_member;
		if (member == null) return;
	}else{
		var member = {};
		member.id= member_id;
	}
		
	if (typeof show_actions == "undefined") {
		var show_actions = true;
	}
	
	var json_sel_ids = $.parseJSON(document.getElementById(genid + member_selector[genid].hiddenFieldName).value);
	var selected_member_ids = json_sel_ids ? json_sel_ids : [];
	
	//check if is selected
	var ind = selected_member_ids.indexOf(member_id);
	if(ind >= 0) return;
	
	var i = 0;
	while (selected_member_ids[i] != member.id && i < selected_member_ids.length) i++;
	
	if (!member_selector[genid].sel_context[dimension_id]) member_selector[genid].sel_context[dimension_id] = [];
	member_selector[genid].sel_context[dimension_id].push(member.id);
	
	var sel_members_div = Ext.get(genid + 'selected-members-dim' + dimension_id);
	var already_selected = sel_members_div.select('div.selected-member-div').elements;
	var last = already_selected.length > 0 ? Ext.fly(already_selected[already_selected.length - 1]) : null;
	var alt_cls = last==null || last.hasClass('alt-row') ? "" : " alt-row";
	
	var html = '<div class="selected-member-div'+alt_cls+'" id="'+genid+'selected-member'+member.id+'">';
	html += '<div class="completePath">';
	
	html += '</div>';
	
	if(show_actions){
		html += '<div class="selected-member-actions"' + (Ext.isIE ? 'style="display:inline;margin-left:40px;float:none;"' : '') + '>';
		html += '<a class="coViewAction ico-delete" onclick="member_selector.remove_relation('+dimension_id+',\''+genid+'\', '+member.id+')" href="#"></a></div>';
	}
	
	html += '</div><div class="separator"></div>';

	var sep = sel_members_div.select('div.separator').elements;
	for (x in sep) Ext.fly(sep[x]).remove();
	sel_members_div.insertHtml('beforeEnd', html);
	
	//add mem_path after insert completePath div to calculate the correct width
	
	var tmp_member = {};
	tmp_member[member.id] = member.id;
	var tmp_dim = {};
	tmp_dim[dimension_id] = tmp_member;
	mem_path = og.getEmptyCrumbHtml(tmp_dim,".completePath",null,false);
	$("#"+genid+"selected-member"+member.id+" .completePath").append(mem_path);
	og.eventManager.fireEvent('replace all empty breadcrumb', null);
	
	if (!member_selector[genid].properties[dimension_id].isMultiple) {
		var form = Ext.get(genid + 'add-member-form-dim' + dimension_id);
		if (form) {
			f = Ext.fly(form);
			f.enableDisplayMode();
			f.hide();
		}
	}

	// refresh member_ids input
	var member_ids_input = Ext.fly(Ext.get(genid + member_selector[genid].hiddenFieldName));
	var json_mem_ids = $.parseJSON(member_ids_input.getValue());
	var member_ids = json_mem_ids ? json_mem_ids : [];
	member_ids.push(member.id);
	member_ids_input.dom.value = Ext.util.JSON.encode(member_ids);

	// on selection change listener
	if (member_selector[genid].properties[dimension_id].listeners.on_selection_change) {
		eval(member_selector[genid].properties[dimension_id].listeners.on_selection_change);
	}
}

member_selector.remove_relation = function(dimension_id, genid, member_id, dont_reload) {
	
	var div = Ext.get(genid+'selected-member'+member_id);
	if (div) {
		div = Ext.fly(div);
		var next = div;
		while (next = next.next('div.selected-member-div')) {
			if (next.hasClass('alt-row')) next.removeClass('alt-row');
			else next.addClass('alt-row');
		}
		div.remove();
	}

	var sel_members_div = Ext.get(genid + 'selected-members-dim' + dimension_id);
	var already_selected = sel_members_div.select('div.selected-member-div').elements;
	if (already_selected.length == 0) {
		var sep = sel_members_div.select('div.separator').elements;
		for (x in sep) Ext.fly(sep[x]).remove();
	}

	// refresh member_ids input
	var member_ids_input = Ext.fly(Ext.get(genid + member_selector[genid].hiddenFieldName));
	var member_ids = Ext.util.JSON.decode(member_ids_input.getValue());
	for (index in member_ids) {
		if (member_ids[index] == member_id) member_ids.splice(index, 1);
	}
	member_ids_input.dom.value = Ext.util.JSON.encode(member_ids);
	
	if (member_selector[genid].sel_context[dimension_id]) {
		for (var i=0;i<member_selector[genid].sel_context[dimension_id].length;i++){
			if (member_selector[genid].sel_context[dimension_id][i] == member_id) {
				member_selector[genid].sel_context[dimension_id].splice(i, 1);
			}
		}
	}

	if (member_selector[genid].properties[dimension_id].isMultiple || !member_selector[genid].sel_context[dimension_id] || member_selector[genid].sel_context[dimension_id].length == 0) {
		var form = Ext.get(genid + 'add-member-form-dim' + dimension_id);
		if (form) {
			f = Ext.fly(form);
			f.enableDisplayMode();
			f.show();
		}
	}

	if (!dont_reload) {
		// reload dependant selectors
		member_selector.reload_dependant_selectors(dimension_id, genid);
	
		// on selection change listener
		if (member_selector[genid].properties[dimension_id].listeners.on_selection_change) {
			eval(member_selector[genid].properties[dimension_id].listeners.on_selection_change);
		}
	}
	
	if (member_selector[genid].properties[dimension_id].listeners.on_remove_relation) {
		eval(member_selector[genid].properties[dimension_id].listeners.on_remove_relation);
	}
}

member_selector.reload_dependant_selectors = function(dimension_id, genid) {
	if (typeof member_selector[genid].properties[dimension_id] == 'undefined') return;
	var dimensions_to_reload_object = member_selector[genid].properties[dimension_id].reloadDimensions;
	
	var dimensions_to_reload = [];
	for (ot in dimensions_to_reload_object) {
		for (var i=0; i<dimensions_to_reload_object[ot].length; i++) {
			dimensions_to_reload.push(dimensions_to_reload_object[ot][i]);
		}
	}

	for (i=0; i<dimensions_to_reload.length; i++) {
		var dim_id = dimensions_to_reload[i];
		if (member_selector[genid].properties[dim_id]) {
		
			var member_ids_input = Ext.fly(Ext.get(genid + member_selector[genid].hiddenFieldName));
			var selected_members = member_ids_input.getValue();
			
			$.ajax({
				data: {
					dimension_id: dim_id,
					object_type_id: member_selector[genid].properties[dim_id].objectTypeId,
					onlyname: 1,
					selected_ids: selected_members
				},	
				url: og.makeAjaxUrl(og.getUrl('dimension', 'initial_list_dimension_members_tree')),
				dataType: "json",
				type: "POST",
				success: function(data){
					var combo = Ext.getCmp(genid + 'add-member-input-dim' + data.dimension_id);
					if (combo) {
						combo.disable();
						var records = [];
						for (x=0; x<data.dimension_members.length; x++) {
							dm = data.dimension_members[x];
							
							var to_show = dm.path == '' ? dm.name : dm.name + " ("+dm.path+")";
							var record = new Ext.data.Record(
								{'id':dm.id, 'name':dm.name, 'path':dm.path, 'to_show':to_show, 'ico':dm.ico, 'dim':dim_id},
								dm.id
							);
							records.push(record);

							if(!member_selector[genid].members_dimension[dm.id]) {
								member_selector[genid].members_dimension[dm.id] = dm.dim;
							}
						}
						combo.reset();
						combo.store.removeAll();
						combo.store.add(records);
						combo.enable();
					}
            	}
            });
			
		}
	}
}


member_selector.remove_all_selections = function(genid) {
	for (dim_id in member_selector[genid].properties) {
		member_selector[genid].properties[dim_id];
			
		if (member_selector[genid].sel_context[dim_id]) {
			var length = member_selector[genid].sel_context[dim_id].length;
			for (var i=0;i<length;i++){
				var member_id = member_selector[genid].sel_context[dim_id][0];
				member_selector.remove_relation(dim_id, genid, member_id, true);
			}
			member_selector.reload_dependant_selectors(dim_id, genid);
		}
		
	}
}

member_selector.set_selected = function(genid, sel_member_ids, preload) {
	for (var idx=0; idx<sel_member_ids.length; idx++) {
		var sel_id = Number(sel_member_ids[idx]);
		var members = og.getMemberFromOgDimensions(sel_id);
		
		if (members.length > 0){
			var member = members[0];
			if(member_selector[genid].properties[member.dimension_id]){
				member_selector.add_relation(member.dimension_id, genid, member.id, true);	
			}			
		}				
	}	
}

member_selector.preload_members = function(genid, d) {
	/*for (dim_id in member_selector[genid].properties) {
		if (typeof d != 'undefined' && d != dim_id) continue;
		
		var combo = Ext.getCmp(genid + 'add-member-input-dim' + dim_id);
		var dim_members = og.dimensions[dim_id];
		var records = [];
		
		for (var k in dim_members) {
			var m = dim_members[k];
			if (typeof m == 'function') continue;
			// ["id", "name", "path", "to_show", "ico", "dim"]
			
			// check permissions
			//if (!m.archived && og.member_permissions[dim_id] && og.member_permissions[dim_id][m.id] && og.member_permissions[dim_id][m.id][member_selector[genid].otid]) {
				
				var to_show = m.path == '' ? m.name : m.name + " ("+m.path+")";
				var record = new Ext.data.Record(
					{'id':m.id, 'name':m.name, 'path':m.path, 'to_show':to_show, 'ico':m.ico, 'dim':dim_id},
					m.id
				);
				records.push(record);
			//}
		}
	
		if (records.length > 0) {
			combo.disable();
			combo.store.removeAll();
			combo.store.add(records);
			combo.reset();
			combo.enable();
		}
	
	}*/
}