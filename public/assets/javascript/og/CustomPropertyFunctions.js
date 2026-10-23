var cpModified = false;
var selectedObjTypeIndex = -1;
og.isMemberCustomProperties = 0;

og.typesWithDisabledMultiple = (og.typesWithDisabledMultiple || []).concat(['date', 'datetime', 'address']);

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


/**
 * Applies property configuration to an already-created row.
 * Works on both live and off-DOM elements.
 *
 * @param {Object|null} property   Property data object, or null for a blank new row.
 * @param {jQuery}      $container jQuery wrapper for the row's <tbody> element.
 * @param {number}      cp_count   Zero-based row index (used for the order label).
 */
og._applyCustomPropertyConfig = function(property, $container, cp_count) {
	// Derive pre_id once; used only for backwards-compatible hook calls.
	var pre_id = "#" + $container.attr('id');

	$container.find("#order").html(cp_count + 1);
	$container.find("#deleted_message").html(lang('custom property deleted'));

	if (property) {

		$container.find("#id").attr('value', property.id);
		$container.find("#name").attr('value', property.name);
		$container.find("#description").attr('value', property.description);
		$container.find("#values").attr('value', property.values);
		$container.find("#default_value").attr('value', property.default_value);
		$container.find("#is_special").attr('value', property.is_special);
		$container.find("#is_disabled").attr('value', property.is_disabled);

		$container.find("#default_value_bool").val(property.default_value);
		if (property.boolean_allow_not_specified === 0 || property.boolean_allow_not_specified === '0' || property.boolean_allow_not_specified === false) {
			$container.find("#boolean_allow_not_specified").removeAttr('checked');
		} else {
			$container.find("#boolean_allow_not_specified").attr('checked', 'checked');
		}

		if (property.is_required) {
			$container.find("#is_required").attr('checked', 'checked');
		}
		if (property.is_multiple_values) {
			$container.find("#is_multiple_values").attr('checked', 'checked');
		}
		if (property.visible_by_default) {
			$container.find("#visible_by_default").attr('checked', 'checked');
		}
		if (property.show_in_lists) {
			$container.find("#show_in_lists").attr('checked', 'checked');
		}
		if (property.information_type) {
			$container.find("#information_type").val(property.information_type);
		}
		if (property.is_import_id) {
			$container.find("#is_import_id").attr('checked', 'checked');
		}
		if (property.is_inheritable) {
			$container.find("#is_inheritable").attr('checked', 'checked');
		}
		if (property.position) {
			$container.find("#position").val(property.position);
		}
		if (property.contact_type) {
			$container.find("#contact_type").val(property.contact_type);
		}
		if (property.type == 'contact') {
			og.setCpContactTypeFilter($container, property.filter_values_by);
			$container.find("#linked_to_pivot").val(property.linked_to);
		}
		if (property.type == 'object_link') {
			$container.find("#filter_values_object_link_cp").val(property.filter_values_by);
			$container.find("#linked_to_pivot").val(property.linked_to);
		}

		$container.find('#type option[value="' + property.type + '"]').prop('selected', true);

		if (property.type == 'list' || property.type == 'table') {
			$container.find("#values").show();
			$container.find("#values_hint").hide();
		} else if (property.type == 'boolean') {
			$container.find("#boolean_default_config").show();
			$container.find("#default_value").hide();
		} else if (property.type == 'numeric' || property.type == 'amount') {
			$container.find("#numeric_options").show();
		} else if (property.type == 'contact') {
			$container.find("#filter_values_contact_cp").show();
		} else if (property.type == 'object_link') {
			$container.find("#filter_values_object_link_cp").show();
		} else if (property.type == 'display_member_property') {
			$container.find("#external_property_id").show();
			$container.find("#external_property_editable").show();
		}

		if (property.original_name && property.original_name != property.name) {
			$container.find("#original_name").html(lang('original name') + ': ' + property.original_name).show();
		} else {
			$container.find("#original_name").hide();
		}

		// if it is a fixed property or is the 'located_under'
		// don't show inputs that don't apply
		if (isNaN(property.id)) {
			if (!og.advanced_core) {
				$container.find("#description").css('visibility', 'hidden');// use 'visibility=hidden' in this field so we keep the same row height
			}
			$container.find("#default_value").hide();
			$container.find("#values").hide();
			$container.find("#show_in_lists").hide();
			$container.find("#visible_by_default").hide();
			// for dimension member associations let the user define 'is_multiple' and 'is_required', else hide them
			if (property.id.indexOf('assoc_') != 0) {
				$container.find("#is_required").hide();
				$container.find("#is_multiple_values").hide();
			} else {
				// for dimension member associations show the code
				if (property.code) {
					$container.find("#values").parent().append('<span class="desc">' + lang('code') + ': ' + property.code + '</span>');
				}
			}
		}

		if (property.is_special) {

			$container.find("#delete_action").hide();
			$container.find("#undo_delete_action").hide();

			if (!og.advanced_core) {
				$container.find("#name").attr('disabled', 'disabled');
			}

			$container.find("#type").attr('disabled', 'disabled');
			if (isNaN(property.id)) {
				let type_text = '';
				let prop_id_str = property.id ? property.id.toString() : '';
				if (prop_id_str.indexOf('assoc_') == 0 || prop_id_str.indexOf('classification_') == 0) {
					type_text = lang('dimension');
				} else if (prop_id_str.indexOf('fixedprop_') == 0) {
					type_text = lang('predefined property');
				}
				$container.find("#type").hide();
				$container.find("#type").closest('td').append('<span class="desc">' + type_text + '</span>');
			}

			$container.find("#values").attr('disabled', 'disabled').addClass('disabled');
			$container.find("#values_hint").hide();

			if(!property.override_is_required) {
				$container.find("#is_required").hide();
			}

			if(!property.override_is_multiple_values) {
				$container.find("#is_multiple_values").hide();
			}

			$container.find("#is_special_hint").show();

		}

		if (property.is_disabled) {
			$container.find("#disabled_message").show();
			$container.find("#enable_action").show();
			$container.find("#disable_action").hide();
			$container.find("#delete_action").hide();
			$container.addClass("disabled");
		} else {
			if (property.is_special) {
				$container.find("#enable_action").hide();
				$container.find("#disable_action").show();
			}
		}

		// If it is a property of parent or sibling subtype
		// dont' show delete button, only show the disable button
		if (!property.is_special && !property.is_disabled
			&& typeof property.is_from_current_subtype != 'undefined') {

				if (property.is_from_current_subtype) {
					$container.find("#disable_action").hide();
					$container.find("#delete_action").show();
				} else {
					$container.find("#disable_action").show();
					$container.find("#delete_action").hide();
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
		$container.find("#visible_by_default").attr('checked', 'checked');
		$container.find("#show_in_lists").attr('checked', 'checked');
		$container.find("#name").focus();
	}

	$container.find("#boolean_allow_not_specified").off('change.cpboolns').on('change.cpboolns', function() {
		og.onBooleanAllowNotSpecifiedToggle(this);
	});
	if ($container.find("#type").val() == 'boolean') {
		var chk = $container.find("#boolean_allow_not_specified")[0];
		if (chk) og.onBooleanAllowNotSpecifiedToggle(chk);
	}
};

og.onBooleanAllowNotSpecifiedToggle = function(chk) {
	var container = $(chk).closest('.cp-container');
	var pre_id = '#' + container.attr('id');
	var allow = $(chk).is(':checked');
	var $sel = $(pre_id + ' #default_value_bool');
	$(pre_id + ' .cp-boolean-default-not-specified-option').toggle(allow);
	if (!allow && $sel.val() == '0') {
		$sel.val('1');
	}
}

og.addCustomPropertyRow = function(genid, property, id_suffix, animate) {

	var template = $('<tbody></tbody>');

	if (!og.admin_cp_count) og.admin_cp_count = {};
	if (!og.admin_cp_count[genid]) og.admin_cp_count[genid] = 0;

	if (!og.custom_props_table_genids) og.custom_props_table_genids = [];
	if (og.custom_props_table_genids.indexOf(genid) == -1) {
		og.custom_props_table_genids.push(genid);
	}

	var cp_count = og.admin_cp_count[genid];
	if (!id_suffix) id_suffix = '';

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

	if (animate) {
		$(template).css('background-color', '#A9E05D').animate({backgroundColor: ''}, {
			duration: 'slow',
			complete: function() {
				$(this).removeAttr('style');
			}
		});
	}

	og._applyCustomPropertyConfig(property, $(template), cp_count);

	og.admin_cp_count[genid] = cp_count + 1;
}

/**
 * Batch version of og.addCustomPropertyRow.
 *
 * Builds all row HTML at once, configures every row off-DOM (no layout
 * recalculations during mutations), then commits everything to the live table
 * in a single appendChild — dramatically faster than calling
 * og.addCustomPropertyRow in a loop.
 *
 * A loading indicator is shown while the work runs. Because JS is
 * single-threaded the indicator is painted via a double requestAnimationFrame
 * before the heavy work begins. Pass an onComplete callback to run code
 * (e.g. sortable init) after the rows are fully inserted.
 *
 * @param {string}        genid       Table identifier.
 * @param {Array|null}    properties  Property data objects. Pass null/[] for one blank row.
 * @param {string}        [id_suffix] Optional suffix appended to container IDs.
 * @param {Function}      [onComplete] Called once all rows are in the DOM.
 */
og.addCustomPropertyRows = function(genid, properties, id_suffix, onComplete) {

	if (!properties || properties.length === 0) {
		og.addCustomPropertyRow(genid, null, id_suffix);
		if (onComplete) onComplete();
		return;
	}

	if (!og.admin_cp_count) og.admin_cp_count = {};
	if (!og.admin_cp_count[genid]) og.admin_cp_count[genid] = 0;

	if (!og.custom_props_table_genids) og.custom_props_table_genids = [];
	if (og.custom_props_table_genids.indexOf(genid) === -1) {
		og.custom_props_table_genids.push(genid);
	}

	if (!id_suffix) id_suffix = '';

	var template_html_source = $("#"+genid+"-cp-container-template").html();
	var start_count = og.admin_cp_count[genid];
	var tableEl = document.getElementById(genid + "custom-properties-table");

	// Show a loading indicator adjacent to the table.
	// Insert it before the table's parent container so it is visible above the rows.
	var $loadingEl = $(og.getIndependentLoading());
	$loadingEl.addClass('cp-batch-loading');
	$(tableEl).closest('table').before($loadingEl);

	// Double rAF: first frame queues a paint, second fires after that paint is
	// committed — ensuring the loading indicator is visible before the JS work
	// blocks the thread.
	requestAnimationFrame(function() {
		requestAnimationFrame(function() {

			// --- Build all row HTML as one string ---
			var allHtml = '';
			for (var i = 0; i < properties.length; i++) {
				var cp_count = start_count + i;
				var container_id = "cp-container-" + cp_count + id_suffix;
				var row_html = template_html_source.replace(/{number}/g, cp_count);
				allHtml += '<tbody id="' + container_id + '" class="cp-container ' + genid;
				if (cp_count % 2 !== 0) allHtml += ' alt';
				allHtml += '">' + row_html + '</tbody>';
			}

			// --- Parse into an off-DOM table, capture references before insertion ---
			var tempTable = document.createElement('table');
			tempTable.innerHTML = allHtml;
			var tbodyRefs = Array.prototype.slice.call(tempTable.children);

			// --- Single live-DOM insertion via DocumentFragment ---
			var frag = document.createDocumentFragment();
			while (tempTable.firstChild) {
				frag.appendChild(tempTable.firstChild);
			}
			tableEl.appendChild(frag);

			// --- Configure rows after DOM insertion so plugin hooks (after_add_custom_property_row)
			//     can find elements via document selectors (property_group, position, etc.) ---
			for (var i = 0; i < properties.length; i++) {
				og._applyCustomPropertyConfig(properties[i], $(tbodyRefs[i]), start_count + i);
			}

			og.admin_cp_count[genid] = start_count + properties.length;

			$loadingEl.remove();
			if (onComplete) onComplete();
		});
	});
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
				cp_number: $(pre_id + " #cp_number").val(),
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
				boolean_allow_not_specified: $(pre_id + " #boolean_allow_not_specified").is(':checked') ? 1 : 0,
				contact_type:  $(pre_id + " #contact_type").attr('value'),
				position: $(pre_id + " #position").val()
		}
		if (prop.type == 'contact') {
			prop.filter_values_by = og.getCpContactTypeFilterValue($(cont));
		}
		if (prop.type == 'object_link') {
			prop.filter_values_by = $(pre_id + " #filter_values_object_link_cp").val();
		}
		if (prop.type == 'contact' || prop.type == 'object_link') {
			prop.linked_to = $(pre_id + " #linked_to").val();
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

	let params = {
		ot_id: ot,
		custom_properties: Ext.util.JSON.encode(custom_props)
	};

	// additional parameters of the form, can be added by any plugin
	// exclude the ones starting with "custom_properties", they are already added in the code above
	$(".custom-properties-admin.object-type input").not('[name^="custom_properties"]').each(function() {
		let val = $(this).attr('value');
		if ($(this).attr('type') == 'checkbox') {
			val = $(this).attr('checked') == 'checked' ? 1 : 0;
		}
		params[$(this).attr('name')] = val;
	});

	og.openLink(save_url, {
		post: params
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
		$("#"+$(container).attr('id')+" #boolean_default_config").show();
		$("#"+$(container).attr('id')+" #default_value").hide();
		var chk = $("#"+$(container).attr('id')+" #boolean_allow_not_specified")[0];
		if (chk) og.onBooleanAllowNotSpecifiedToggle(chk);
	} else {
		$("#"+$(container).attr('id')+" #boolean_default_config").hide();
		$("#"+$(container).attr('id')+" #default_value").show();
	}

	if ($(combo).val() == 'numeric' || $(combo).val() == 'amount') {
		$("#"+$(container).attr('id')+" #numeric_options").show();
	} else {
		$("#"+$(container).attr('id')+" #numeric_options").hide();
	}

	if ($(combo).val() == 'contact' || $(combo).val() == 'object_link') {
		if ($(combo).val() == 'contact') {
			$("#"+$(container).attr('id')+" #filter_values_contact_cp").show();
			$("#"+$(container).attr('id')+" #filter_values_object_link_cp").hide();
		} else if ($(combo).val() == 'object_link') {
			$("#"+$(container).attr('id')+" #filter_values_contact_cp").hide();
			$("#"+$(container).attr('id')+" #filter_values_object_link_cp").show();
		}
		$("#"+$(container).attr('id')+" #linked_to").show();
		if (og.dimension_associations) {
			og.dimension_associations.fill_linked_to_select_options(genid);
		}
	} else {
		$("#"+$(container).attr('id')+" #filter_values_contact_cp").hide();
		$("#"+$(container).attr('id')+" #filter_values_object_link_cp").hide();
		$("#"+$(container).attr('id')+" #linked_to").hide();
	}

	og.eventManager.fireEvent('on custom property type changed', {combo: combo, container: container});
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

// === Disable "Multiple values" checkbox for Date, DateTime and Address types ===
og.disableMultipleForTypes = function() {
	$('select[name$="[type]"], select#type').each(function() {
		var $typeSelect = $(this);
		var $container = $typeSelect.closest('tr, div, .coInputMainBlock, .custom-property-row');

		// Find the "multiple values" checkbox in the same row
		var $multipleCheckbox = $container.find('input#is_multiple_values, input[name$="[is_multiple]"]');

		if ($multipleCheckbox.length === 0) return;

		// Function that enables/disables based on current selection
		function updateMultipleCheckbox() {
			var selectedType = $typeSelect.val();

			if (og.typesWithDisabledMultiple.indexOf(selectedType) !== -1) {
				$multipleCheckbox
					.prop({
						checked: false,
						disabled: true
					});
			} else {
				$multipleCheckbox
					.prop('disabled', false);
			}
		}

		$typeSelect.off('change.disableMultipleDate').on('change.disableMultipleDate', updateMultipleCheckbox);

		updateMultipleCheckbox();
	});
};

$(document).ready(function() {
	setInterval(og.disableMultipleForTypes, 3000);
});


/**
 * Returns the canonical filter_values_by value of a custom property row, built from its
 * contact type checkboxes. Every group checked, or none, means "do not filter" and is
 * stored as an empty string.
 */
og.getCpContactTypeFilterValue = function($container) {
	var $checks = $container.find(".cp-type-filter .cp-type-filter-check");
	if ($checks.length == 0) return '';

	var selected = [];
	$checks.each(function() {
		if (this.checked) selected.push($(this).val());
	});

	if (selected.length == 0 || selected.length == $checks.length) return '';
	return selected.join(',');
}

/**
 * Rebuilds the summary shown on the widget button from the checked groups.
 */
og.updateCpContactTypeFilterSummary = function($container) {
	var $widget = $container.find(".cp-type-filter");
	if ($widget.length == 0) return;

	var $checks = $widget.find(".cp-type-filter-check");
	var labels = [];
	$checks.each(function() {
		if (this.checked) {
			labels.push($(this).closest(".cp-type-filter-option").find(".cp-type-filter-label").text());
		}
	});

	var summary = (labels.length == 0 || labels.length == $checks.length) ? lang('all contact types') : labels.join(', ');
	$widget.find(".cp-type-filter-summary").text(summary);
}

/**
 * Applies a stored filter_values_by value to the widget of a custom property row. An empty
 * value, or one naming every group, leaves all the boxes checked.
 */
og.setCpContactTypeFilter = function($container, filter_values_by) {
	var $widget = $container.find(".cp-type-filter");
	if ($widget.length == 0) return;

	var selected = (filter_values_by || '').split(',');
	var $checks = $widget.find(".cp-type-filter-check");
	var checked_count = 0;

	$checks.each(function() {
		var is_checked = $.inArray($(this).val(), selected) >= 0;
		this.checked = is_checked;
		if (is_checked) checked_count++;
	});

	// an empty or unknown value means "do not filter", shown as every group checked
	if (checked_count == 0) {
		$checks.each(function() { this.checked = true; });
	}

	og.updateCpContactTypeFilterSummary($container);
}

/**
 * Handles a click on one of the contact type checkboxes.
 */
og.cpContactTypeFilterChanged = function(checkbox) {
	og.updateCpContactTypeFilterSummary($(checkbox).closest(".cp-container"));
}

/**
 * Closes every open contact type filter panel.
 */
og.closeCpContactTypeFilterPanels = function() {
	$(".cp-type-filter-panel:visible").hide();
	$(".cp-table-container").off("scroll.cpTypeFilter");
	og.cp_contact_type_filter_panel_open = false;
}

/**
 * Binds the handlers that dismiss an open panel. Bound once for the whole page.
 */
og.initCpContactTypeFilterHandlers = function() {
	if (og.cp_contact_type_filter_handlers_ready) return;
	og.cp_contact_type_filter_handlers_ready = true;

	$(document).on("mousedown.cpTypeFilter", function(ev) {
		if (!og.cp_contact_type_filter_panel_open) return;
		if ($(ev.target).closest(".cp-type-filter").length > 0) return;
		og.closeCpContactTypeFilterPanels();
	});

	$(document).on("keydown.cpTypeFilter", function(ev) {
		if (ev.keyCode == 27 && og.cp_contact_type_filter_panel_open) {
			og.closeCpContactTypeFilterPanels();
		}
	});

	$(window).on("resize.cpTypeFilter", function() {
		if (og.cp_contact_type_filter_panel_open) og.closeCpContactTypeFilterPanels();
	});
}

/**
 * Places an open panel right under its button.
 *
 * getBoundingClientRect() is already viewport relative, which is what a fixed panel needs.
 * offset() must not be used here: it is document relative and the table scrolls inside its
 * own container, so no window scroll offset can correct it.
 */
og.positionCpContactTypeFilterPanel = function(button, $panel) {
	$panel.show();

	var rect = button.getBoundingClientRect();
	var panel_height = $panel.outerHeight();
	var top = rect.bottom;

	// flip above the button when the panel would fall outside the viewport
	if (top + panel_height > $(window).height() && rect.top - panel_height > 0) {
		top = rect.top - panel_height;
	}

	$panel.css({
		top: top + "px",
		left: rect.left + "px",
		"min-width": rect.width + "px"
	});
}

/**
 * Opens or closes the contact type filter panel of a row. The panel is positioned against
 * the viewport because the custom properties table clips its own content.
 */
og.toggleCpContactTypeFilterPanel = function(button) {
	og.initCpContactTypeFilterHandlers();

	var $button = $(button);
	var $panel = $button.closest(".cp-type-filter").find(".cp-type-filter-panel");
	var was_visible = $panel.is(":visible");

	og.closeCpContactTypeFilterPanels();
	if (was_visible) return;

	og.positionCpContactTypeFilterPanel(button, $panel);
	og.cp_contact_type_filter_panel_open = true;

	// The table scrolls independently of the page, so a fixed panel would drift away from
	// its button. Follow the button instead of closing: the browser scrolls the button into
	// view on click, and closing on that scroll would dismiss the panel immediately.
	$(".cp-table-container").on("scroll.cpTypeFilter", function() {
		var container_rect = this.getBoundingClientRect();
		var button_rect = button.getBoundingClientRect();

		// nothing left to point at once the button leaves the visible part of the table
		if (button_rect.bottom < container_rect.top || button_rect.top > container_rect.bottom) {
			og.closeCpContactTypeFilterPanels();
			return;
		}

		og.positionCpContactTypeFilterPanel(button, $panel);
	});
}
