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
	
	if (dimension_to_get.length > 0) {
	  og.openLink(og.getUrl('member', 'get_dimension_id', {member_id: Ext.util.JSON.encode(dimension_to_get)}), {callback: function(success, data){
		
		if (!data.dim_ids) {
			og.eventManager.fireEvent('after member_selector init', {genid:genid});
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
		
		og.eventManager.fireEvent('after member_selector init', {genid:genid});	
		og.eventManager.fireEvent('replace all empty breadcrumb', null);
	  }});	
	}
	
	if (!selected_member_ids || selected_member_ids.length == 0) {
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

member_selector.add_relation = function(dimension_id, genid, member_id, show_actions, dont_reload_dep_selectors, dont_select_assoc_members) {
	if (typeof member_id == "undefined") {
		var combo = Ext.getCmp(genid + 'add-member-input-dim' + dimension_id);
		var member = combo.selected_member;
		if (member == null) return;
	}else{
		var member = {};
		member.id= member_id;
	}
	
	if (!member_selector[genid].properties[dimension_id]) {
		member_selector[genid].properties[dimension_id] = {
				listeners: {},
				isMultiple: false
		};
	}
		
	if (typeof show_actions == "undefined") {
		var show_actions = member_selector[genid].properties[dimension_id].isMultiple;
	}
	
	//var json_sel_ids = $.parseJSON(document.getElementById(genid + member_selector[genid].hiddenFieldName).value);
	var hf_input = document.getElementById(genid + member_selector[genid].hiddenFieldName);
	if (hf_input.value == "") hf_input = "[]";
	var json_sel_ids = Ext.util.JSON.decode(hf_input.value);
	var selected_member_ids = json_sel_ids ? json_sel_ids : [];
	
	//check if is selected
	var ind = selected_member_ids.indexOf(member_id);
	if(ind >= 0) return;
	
	var i = 0;
	while (selected_member_ids[i] != member.id && i < selected_member_ids.length) i++;
	
	if (!member_selector[genid].sel_context[dimension_id]) member_selector[genid].sel_context[dimension_id] = [];
	if (member_selector[genid].properties[dimension_id].isMultiple) {
		member_selector[genid].sel_context[dimension_id].push(member.id);
	} else {
		member_selector[genid].sel_context[dimension_id] = [member.id];
	}
	
	
	var sel_members_div = Ext.get(genid + 'selected-members-dim' + dimension_id);
	if (sel_members_div) {
		var already_selected = sel_members_div.select('div.selected-member-div').elements;
	}
	var last = already_selected && already_selected.length > 0 ? Ext.fly(already_selected[already_selected.length - 1]) : null;
	var alt_cls = last==null || last.hasClass('alt-row') ? "" : " alt-row";
	
	var checkbox_class = "";
	if (member_selector[genid].defaultSelectionCheckboxes) {
		checkbox_class = "with-checkbox";
	}
	
	var html = '<div class="selected-member-div'+alt_cls+'" id="'+genid+'selected-member'+member.id+'">';
	html += '<div class="completePath '+checkbox_class+'">';
	
	html += '</div>';
	
	if(show_actions){
		html += '<div class="selected-member-actions '+checkbox_class+'"' + (Ext.isIE ? 'style="display:inline;margin-left:40px;float:none;"' : '') + '>';
		if (member_selector[genid].defaultSelectionCheckboxes) {
			html += '<input type="checkbox" class="checkbox" name="member[default_selection]['+member_id+']" title="'+lang('select by default')+'"/>&nbsp;';
		}
		if (member_selector[genid].properties[dimension_id].isMultiple) {
			html += '<a class="coViewAction ico-delete" onclick="member_selector.remove_relation('+dimension_id+',\''+genid+'\', '+member.id+')" href="#"></a>';
		}
		html += '</div>';
	}
	
	html += '</div><div class="separator"></div>';

	if (sel_members_div) {
		var sep = sel_members_div.select('div.separator').elements;
		for (x in sep) Ext.fly(sep[x]).remove();
		if (member_selector[genid].properties[dimension_id].isMultiple) {
			sel_members_div.insertHtml('beforeEnd', html);
		} else {
			$("#"+sel_members_div.id).html(html);
		}
	}
	
	if (og.fn_sel_member_additional_html && og.fn_sel_member_additional_html.length > 0) {
		for (var x=0; x<og.fn_sel_member_additional_html.length; x++) {
			var fn = og.fn_sel_member_additional_html[x];
			if (typeof(fn) == 'function') fn.call(null, genid, member);
		}
	}
	
	//add mem_path after insert completePath div to calculate the correct width
	
	var minfo = null;
	if (og.dimensions[dimension_id]) {
		minfo = og.dimensions[dimension_id][member.id];
	}
	
	if (!minfo) {
		var tree = Ext.getCmp(genid + '-member-chooser-panel-' + dimension_id + '-tree');
		if (tree) {
			var node = tree.getNodeById(member.id);
			if (node) minfo = node.attributes;
		}
	}
	if (minfo) {
		var tmp_dim = {};
		tmp_dim[dimension_id] = {};
		tmp_dim[dimension_id][minfo.object_type_id] = [member.id];
		
		mem_path = og.getEmptyCrumbHtml(tmp_dim,".completePath",null,false);
		$("#"+genid+"selected-member"+member.id+" .completePath").append(mem_path);
		og.eventManager.fireEvent('replace all empty breadcrumb', null);
	}
	
	if (!member_selector[genid].properties[dimension_id].isMultiple) {
		var form = Ext.get(genid + 'add-member-form-dim' + dimension_id);
		if (form) {
			f = Ext.fly(form);
			f.enableDisplayMode();
			f.hide();
		}
	}

	// refresh member_ids input
	var sel_members_str = "";
	for (dim_id in member_selector[genid].sel_context) {
		if (member_selector[genid].sel_context[dim_id] && member_selector[genid].sel_context[dim_id].length > 0) {
			sel_members_str += (sel_members_str=="" ? "" : ",") + member_selector[genid].sel_context[dim_id].join(',');
		}
	}
	sel_members_str = "["+ sel_members_str +"]";
	
	var member_ids_input = Ext.fly(Ext.get(genid + member_selector[genid].hiddenFieldName));
	member_ids_input.dom.value = sel_members_str;

	// on selection change listener
	if (member_selector[genid].properties[dimension_id].listeners.on_selection_change) {
		eval(member_selector[genid].properties[dimension_id].listeners.on_selection_change);
	}
	
	// reload dependant selectors
	if (!dont_reload_dep_selectors) {
		member_selector.reload_dependant_selectors(dimension_id, genid);
	}
	
	// automatic selection of associated members of related dimensions
	if (!dont_select_assoc_members && !member_selector[genid].properties[dimension_id].dontSelectAssociatedMembers) {
		og.selectDefaultAssociatedMembers(genid, dimension_id, member.id, true);
	}
	

	if (!member_selector[genid].properties[dimension_id].isMultiple) {
		$("#"+genid+"-member-chooser-panel-"+dimension_id+"-tree-current-selected .empty-text").hide();
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
	if (sel_members_div) {
		var already_selected = sel_members_div.select('div.selected-member-div').elements;
		if (already_selected.length == 0) {
			var sep = sel_members_div.select('div.separator').elements;
			for (x in sep) Ext.fly(sep[x]).remove();
		}
	}
	
	// refresh member_ids input
	var member_ids_input = Ext.fly(Ext.get(genid + member_selector[genid].hiddenFieldName));
	var member_ids_input_val = member_ids_input.getValue();
	var member_ids = [];
	if (member_ids_input_val) {
		member_ids = Ext.util.JSON.decode(member_ids_input.getValue());
	}
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

	if (member_selector[genid].properties[dimension_id] && member_selector[genid].properties[dimension_id].isMultiple 
			|| !member_selector[genid].sel_context[dimension_id] || member_selector[genid].sel_context[dimension_id].length == 0) {
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
		if (member_selector[genid].properties[dimension_id] && member_selector[genid].properties[dimension_id].listeners.on_selection_change) {
			eval(member_selector[genid].properties[dimension_id].listeners.on_selection_change);
		}
	}
	
	if (member_selector[genid].properties[dimension_id] && member_selector[genid].properties[dimension_id].listeners.on_remove_relation) {
		eval(member_selector[genid].properties[dimension_id].listeners.on_remove_relation);
	}
}

member_selector.reload_dependant_selectors = function(dimension_id, genid) {
		
	if (typeof member_selector[genid].properties[dimension_id] == 'undefined') return;
	var dimensions_to_reload_object = member_selector[genid].properties[dimension_id].reloadDimensions;
	
	var dimensions_to_reload_ots = [];
	var dimensions_to_reload = [];
	for (ot in dimensions_to_reload_object) {
		for (var i=0; i<dimensions_to_reload_object[ot].length; i++) {
			dimensions_to_reload.push(dimensions_to_reload_object[ot][i]);
			dimensions_to_reload_ots.push(ot);
		}
	}

	var hf = Ext.get(genid + member_selector[genid].hiddenFieldName);
	var form_id = hf && hf.dom && hf.dom.form ? hf.dom.form.id : null;
	
	if (typeof(form_id) != 'string') return;

	var member_ids_input = Ext.fly(Ext.get(genid + member_selector[genid].hiddenFieldName));
	var selected_members = eval(member_ids_input.getValue());

	var main_tree = Ext.getCmp(genid + '-member-chooser-panel-' + dimension_id + '-tree');

	if (main_tree && main_tree.filterOnChange) {
	  for (i=0; i<dimensions_to_reload.length; i++) {
		var dim_id = dimensions_to_reload[i];

		var dep_genid = "";
		var selector_inputs = form_id ? $("#" + form_id + ' .dimension-panel-textfilter') : [];
		for (var x=0; x<selector_inputs.length; x++) {
			var sel_id = selector_inputs[x].id;
			var key = "-member-chooser-panel-"+ dim_id +"-tree-textfilter";
			if (sel_id.indexOf(key) >= 0) {
				dep_genid = selector_inputs[x].id.substring(0, selector_inputs[x].id.indexOf("-"));
				break;
			}
		}
		
		var selector_object = member_selector[dep_genid];
		
		if (selector_object && selector_object.properties[dim_id] && !selector_object.dontFilterThisSelector) {
			
			if (selected_members) {
				// get the selected node
				var selected_node = null;
				var k = 0;
				while (!selected_node && k<selected_members.length) {
					selected_node = main_tree.getNodeById(selected_members[k]);
					k++;
				}
				
				var tree = Ext.getCmp(dep_genid + '-member-chooser-panel-' + dim_id + '-tree');
				
				// build tree filter options
				var filter_options = {};
				if (og.reload_selectors_modify_filter_options_functions) {
					fn_params = {dimension_id:dimension_id, dimensions_to_reload_ots:dimensions_to_reload_ots, tree:tree, genid:genid};
					
					for (var x=0; x<og.reload_selectors_modify_filter_options_functions.length; x++) {
						var fn = og.reload_selectors_modify_filter_options_functions[x];
						if (typeof(fn) == 'function') {
							fn.call(null, fn_params, filter_options);
						}
					}
				}
				
				// filter the dependant tree
				tree.filterByMember(selected_members, selected_node, function(){
					self.filteredTrees++;
					if (self.filteredTrees == self.totalFilterTrees) {
						self.resumeEvents();
						og.eventManager.fireEvent('member trees updated', selected_node);
					}
				}, filter_options);
			}

		}
	  }
	}
}

member_selector.remove_all_dimension_selections = function(genid, dim_id) {
	
	if (!member_selector[genid]) return;
		
	if (member_selector[genid].sel_context[dim_id]) {
		var length = member_selector[genid].sel_context[dim_id].length;
		for (var i=0;i<length;i++){
			var member_id = member_selector[genid].sel_context[dim_id][0];
			member_selector.remove_relation(dim_id, genid, member_id, true);
		}
		member_selector.reload_dependant_selectors(dim_id, genid);
	}	
}

member_selector.remove_all_selections = function(genid, excluded_dim_ids) {
	if (typeof(excluded_dim_ids) == 'undefined') excluded_dim_ids = [];

	for (dim_id in member_selector[genid].properties) {

		if (excluded_dim_ids.indexOf(parseInt(dim_id)) == -1) {
			member_selector.remove_all_dimension_selections(genid, dim_id);
			
			if (!member_selector[genid].properties[dim_id].isMultiple) {
				$("#"+genid+"-member-chooser-panel-"+dim_id+"-tree-current-selected .empty-text").show();
			}
		}
	}
}

member_selector.reset_all_selections = function(genid) {

	member_selector.remove_all_selections(genid);
	
	var added_dim_ids = [];
	var dimensionMembers = og.contextManager.dimensionMembers;
	for(dim_id in dimensionMembers){
		var mids = dimensionMembers[dim_id];
		for (i=0; i<mids.length; i++) {
			if (mids[i] > 0) {
				member_selector.add_relation(dim_id, genid, mids[i], true, true, false);
				added_dim_ids.push(dim_id);
			}
		}
	}
	
	for (dim_id in og.dimensions_info) {
		var dim_info = og.dimensions_info[dim_id];
		if (added_dim_ids.indexOf(dim_id) == -1 && dim_info.default_member) {
			member_selector.add_relation(dim_id, genid, dim_info.default_member, true, true, false);
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