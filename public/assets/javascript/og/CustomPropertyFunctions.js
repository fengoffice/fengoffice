var cpModified = false;
var selectedObjTypeIndex = -1;
og.isMemberCustomProperties = 0;

og.validateCustomProperties = function(genid){
	var cpDiv = Ext.getDom(genid);
	var cpNames = new Array();
	for(var i=0; i < cpDiv.childNodes.length; i++){
		var deleted = document.getElementById('custom_properties[' + i + '][deleted]').value;
		if(deleted == "0"){
			var name = document.getElementById('custom_properties[' + i + '][name]').value;
			if(name == ''){
				alert(lang('custom property name empty'));
				return false;
			}
			var type = document.getElementById('custom_properties[' + i + '][type]').value;
			var defaultValue = document.getElementById('custom_properties[' + i + '][default_value]').value;
			if(type == 'list'){
				var values = document.getElementById('custom_properties[' + i + '][values]').value;
				if(values == ''){
					alert(lang('custom property values empty', name));
					return false;
				}
				var valuesArray = values.split(',');
				var defaultValueOK = false;
				for(var j=0; j < valuesArray.length; j++){
					valuesArray[j] = valuesArray[j].trim();
					if(valuesArray[j] == defaultValue){
						defaultValueOK = true;
					}
				}
				if(defaultValue != '' && !defaultValueOK){
					alert(lang('custom property wrong default value', name));
					return false;
				}
				
			}else if(type == 'numeric'){
				if(!og.isNumeric(defaultValue)){
					alert(lang('custom property invalid numeric value', name));
					return false;
				}
			}
			for(var k=0; k < cpNames.length; k++){
				if(cpNames[k] == name){
					alert(lang('custom property duplicate name', name));
					return false;
				}
			}
			cpNames.push(name);
		}
	}
	return true;
};

og.isNumeric = function(sText){
   var ValidChars = "0123456789.";
   var IsNumber=true;
   var Char;
 
   for (i = 0; i < sText.length && IsNumber == true; i++){ 
      Char = sText.charAt(i); 
      if (ValidChars.indexOf(Char) == -1){
         IsNumber = false;
      }
   }
   return IsNumber;  	   
 }


og.addCustomPropertyRow = function(genid, property, id_suffix) {

	var template = $('<tbody></tbody>');
	
	if (!og.admin_cp_count) og.admin_cp_count = {};
	if (!og.admin_cp_count[genid]) og.admin_cp_count[genid] = 0;
	
	if (!og.custom_props_table_genids) og.custom_props_table_genids = [];
	if (og.custom_props_table_genids.indexOf(genid) == -1) {
		og.custom_props_table_genids.push(genid);
	}
	
	var cp_count = og.admin_cp_count[genid];
	if (!id_suffix) id_suffix = id_suffix;

	var container_id = "cp-container-" + cp_count + id_suffix;

	// get html and replace {number} with new cp index
	var template_html = $("#"+genid+"-cp-container-template").html();
	template_html = template_html.replace(/{number}/g, cp_count);
	template.html(template_html);
	
	$(template).attr('id', container_id);
	$(template).addClass("cp-container").addClass(genid);
	if (cp_count % 2 != 0) {
		$(template).addClass("alt");
	}

	$("#"+genid+"custom-properties-table").append(template);
	
	var pre_id = "#" + container_id;
	
	$(pre_id + " #order").html(cp_count + 1);
	$(pre_id + " #deleted_message").html(lang('custom property deleted'));
	
	if (property) {

		$(pre_id + " #id").attr('value', property.id);
		$(pre_id + " #name").attr('value', property.name);
		$(pre_id + " #description").attr('value', property.description);
		$(pre_id + " #values").attr('value', property.values);
		$(pre_id + " #default_value").attr('value', property.default_value);
		$(pre_id + " #is_special").attr('value', property.is_special);
		$(pre_id + " #is_disabled").attr('value', property.is_disabled);
		
		$(pre_id + " #default_value_bool").val(property.default_value);
		
		if (property.is_required) {
			$(pre_id + " #is_required").attr('checked', 'checked');
		}
		if (property.is_multiple_values) {
			$(pre_id + " #is_multiple_values").attr('checked', 'checked');
		}
		if (property.visible_by_default) {
			$(pre_id + " #visible_by_default").attr('checked', 'checked');
		}
		if (property.show_in_lists) {
			$(pre_id + " #show_in_lists").attr('checked', 'checked');
		}
		if (property.information_type) {
			$(pre_id + " #information_type").val(property.information_type);
		}
		if (property.is_import_id) {
			$(pre_id + " #is_import_id").attr('checked', 'checked');
		}
		if (property.is_inheritable) {
			$(pre_id + " #is_inheritable").attr('checked', 'checked');
		}
		if (property.contact_type) {
			$(pre_id + " #contact_type").val(property.contact_type);
		}

		$(pre_id + ' #type option[value="' + property.type + '"]').prop('selected', true);
		
		if (property.type == 'list' || property.type == 'table') {
			$(pre_id + " #values").show();
			$(pre_id + " #values_hint").hide();
		} else if (property.type == 'boolean') {
			$(pre_id + " #default_value_bool").show();
			$(pre_id + " #default_value").hide();
		} else if (property.type == 'numeric' || property.type == 'amount') {
			$(pre_id + " #numeric_options").show();
		}

		// if it is a fixed property or is the 'located_under'
		// don't show desription and other inputs that don't apply
		if (isNaN(property.id)) {
			$(pre_id + " #description").css('visibility', 'hidden');// use 'visibility=hidden' in this field so we keep the same row height
			$(pre_id + " #default_value").hide();
			$(pre_id + " #values").hide();
			$(pre_id + " #show_in_lists").hide();
			$(pre_id + " #visible_by_default").hide();
			// for dimension member associations let the user define 'is_multiple' and 'is_required'
			if (property.id.indexOf('assoc_') != 0) {
				$(pre_id + " #is_required").hide();
				$(pre_id + " #is_multiple_values").hide();
			}
		}

		if (property.is_special) {
			
			$(pre_id + " #delete_action").hide();
			$(pre_id + " #undo_delete_action").hide();

			$(pre_id + " #name").attr('disabled', 'disabled');
			$(pre_id + " #type").attr('disabled', 'disabled');
			$(pre_id + " #values").attr('disabled', 'disabled').addClass('disabled');
			$(pre_id + " #values_hint").hide();
		
			if(!property.override_is_required) {
				$(pre_id + " #is_required").hide();	
			}
			
			if(!property.override_is_multiple_values) {
				$(pre_id + " #is_multiple_values").hide();
			}
			
			$(pre_id + " #is_special_hint").show();

			if (property.is_disabled) {
				$(pre_id + " #disabled_message").show();
				$(pre_id + " #enable_action").show();
				$(pre_id + " #disable_action").hide();
				$(template).addClass("disabled");
			} else {
				$(pre_id + " #enable_action").hide();
				$(pre_id + " #disable_action").show();
			}
			
		}
		
		if (og.after_add_custom_property_row && og.after_add_custom_property_row.length > 0) {
			for (var i=0; i<og.after_add_custom_property_row.length; i++) {
				var fn = og.after_add_custom_property_row[i];
				
				if (typeof(fn) == "function") {
					fn.call(null, property, pre_id);
				}
			}
		}
	} else {
		// when adding new custom property set visible_by_default and show_in_lists checked
		$(pre_id + " #visible_by_default").attr('checked', 'checked');
		$(pre_id + " #show_in_lists").attr('checked', 'checked');
	}

	og.admin_cp_count[genid] = cp_count + 1;
}



og.saveObjectTypeCustomProperties = function(genid, save_url) {
	var ot = $("#"+genid+"_ot_id").val();

	var containers = [];
	for (var i=0; i<og.custom_props_table_genids.length; i++) {
		var gid = og.custom_props_table_genids[i];
		var tmp_cont = $(".cp-container."+gid);
		if (tmp_cont && tmp_cont.length > 0) {
			for (var k=0; k<tmp_cont.length; k++) {
				containers.push(tmp_cont[k]);
			}
		}
	}
	
	var custom_props = [];
	
	for (var i=0; i<containers.length; i++) {
		var cont = containers[i];

		var pre_id = "#" + cont.id;
		
		var del = $(pre_id + " #deleted").attr('value')
		var name = $(pre_id + " #name").attr('value');
		
		if (!del && name == '') {
			og.err(lang('custom property name empty'));
			return;
		} 
		
		var prop = {
				id: $(pre_id + " #id").attr('value'),
				deleted: del,
				name: name,
				type: $(pre_id + " #type").val(),
				description: $(pre_id + " #description").attr('value'),
				default_value: $(pre_id + " #default_value").attr('value'),
				default_value_bool: $(pre_id + " #default_value_bool").val(),
				values: $(pre_id + " #values").attr('value'),
				is_special: $(pre_id + " #is_special").attr('value'),
				is_disabled: $(pre_id + " #is_disabled").attr('value'),
				is_required: $(pre_id + " #is_required").attr('checked') == 'checked',
				is_multiple_values: $(pre_id + " #is_multiple_values").attr('checked') == 'checked',
				visible_by_default: $(pre_id + " #visible_by_default").attr('checked') == 'checked',
				show_in_lists: $(pre_id + " #show_in_lists").attr('checked') == 'checked',
				contact_type:  $(pre_id + " #contact_type").attr('value')
		}

		// set additional parameters foreach cp
		if (og.additional_on_cp_submit_fn && og.additional_on_cp_submit_fn.length > 0) {
			for (var k=0; k<og.additional_on_cp_submit_fn.length; k++) {
				var add_func = og.additional_on_cp_submit_fn[k];
				if (typeof(add_func) == 'function') {
					prop = add_func.call(null, prop, pre_id);
				}
			}
		}
		
		custom_props.push(prop);
	}
	
	if (!save_url) {
		save_url = og.getUrl('administration', 'save_custom_properties_for_type');
	}

	og.openLink(save_url, {
		post: {
			ot_id: ot,
			custom_properties: Ext.util.JSON.encode(custom_props)
		}
	});
}



og.customPropTypeChanged = function(combo) {
	var container = $(combo).closest(".cp-container");
	if ($(combo).val() == 'list' || $(combo).val() == 'table') {
		$("#"+$(container).attr('id')+" #values").show();
		$("#"+$(container).attr('id')+" #values_hint").hide();
	} else {
		$("#"+$(container).attr('id')+" #values").hide();
		$("#"+$(container).attr('id')+" #values_hint").show();
	}
	
	if ($(combo).val() == 'boolean') {
		$("#"+$(container).attr('id')+" #default_value_bool").show();
		$("#"+$(container).attr('id')+" #default_value").hide();
	} else {
		$("#"+$(container).attr('id')+" #default_value_bool").hide();
		$("#"+$(container).attr('id')+" #default_value").show();
	}
	
	if ($(combo).val() == 'numeric') {
		$("#"+$(container).attr('id')+" #numeric_options").show();
	} else {
		$("#"+$(container).attr('id')+" #numeric_options").hide();
	}
}



og.disableSpecialCustomProperty = function(link) {
	var container = $(link).closest(".cp-container");

	$("#"+$(container).attr('id')+" #is_disabled").val(1);
	
	$("#"+$(container).attr('id')+" #enable_action").show();
	$("#"+$(container).attr('id')+" #disable_action").hide();

	$("#"+$(container).attr('id')+" #disabled_message").show();
	$(container).addClass('disabled');
}

og.deleteCustomProperty = function(link){
  	if(confirm(lang('delete custom property confirmation'))){

  		var container = $(link).closest(".cp-container");

		$("#"+$(container).attr('id')+" #deleted").val(1);
		
		$("#"+$(container).attr('id')+" #undo_delete_action").show();
		$("#"+$(container).attr('id')+" #delete_action").hide();

		$("#"+$(container).attr('id')+" #deleted_message").show();
		$(container).addClass('disabled');
		
  	}
};

og.undoDisableSpecialCustomProperty = function(link) {

	var container = $(link).closest(".cp-container");

	$("#"+$(container).attr('id')+" #is_disabled").val(0);
	
	$("#"+$(container).attr('id')+" #enable_action").hide();
	$("#"+$(container).attr('id')+" #disable_action").show();

	$("#"+$(container).attr('id')+" #disabled_message").hide();
	$(container).removeClass('disabled');
}

og.undoDeleteCustomProperty = function(link){
	
	var container = $(link).closest(".cp-container");

	$("#"+$(container).attr('id')+" #deleted").val(0);
	
	$("#"+$(container).attr('id')+" #undo_delete_action").hide();
	$("#"+$(container).attr('id')+" #delete_action").show();

	$("#"+$(container).attr('id')+" #deleted_message").hide();
	$(container).removeClass('disabled');
};

og.refreshTableRowsOrder = function(genid) {
	var containers = $(".cp-container."+genid);
	for (var i=0; i<containers.length; i++) {
		var cont = containers[i];

		if (i % 2 != 0) {
			$(cont).addClass("alt");
		} else {
			$(cont).removeClass("alt");
		}

		$("#" + cont.id + " #order").html(i+1);
		
	}
}



