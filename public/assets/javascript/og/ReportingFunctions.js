var modified = false;
var selectedObjTypeIndex = -1;
var fieldValues = {};

og.loadReportingFlags = function(){
	modified = false;
	selectedObjTypeIndex = -1;
	fieldValues = {};
	og.last_report_group_id = 0;
};

og.reportObjectTypeChanged = function(genid, order_by, order_by_asc, cols, execute_callbacks){
	var objectTypeSel = document.getElementById('objectTypeSel');
	if(modified){
		if(!confirm(lang('confirm discard changes'))){
			objectTypeSel.selectedIndex = selectedObjTypeIndex;
			return;
		}
	}
	modified = false;
	selectedObjTypeIndex = objectTypeSel.selectedIndex;
	if(selectedObjTypeIndex != -1){
		var conditionsDiv = Ext.getDom(genid);
		while(conditionsDiv.firstChild){
			conditionsDiv.removeChild(conditionsDiv.firstChild);
		}
		var type = objectTypeSel[selectedObjTypeIndex].value;
		if(type == ''){
			document.getElementById(genid + 'MainDiv').style.display = 'none';
			return;
		}
		
		var type_el = document.getElementById(genid + 'report[report_object_type_id]');
		if (type_el) type_el.value = type;
		
		Ext.get('columnListContainer').load({
			url: og.getUrl('reporting', 'get_object_column_list', {object_type: type, columns:cols, orderby:order_by, orderbyasc:order_by_asc, genid:genid}),
			scripts: true
		});
		
		if (typeof(execute_callbacks) == 'undefined') execute_callbacks = true;
		
		if (execute_callbacks && og.after_report_object_type_change_functions) {
			for (var i=0; i<og.after_report_object_type_change_functions.length; i++) {
				var fn = og.after_report_object_type_change_functions[i];
				if (typeof(fn) == "function") {
					fn.call(null, type, genid);
				}
			}
		}

		document.getElementById(genid + 'MainDiv').style.display = '';
	}
};

og.reportTask = function(genid, order_by, order_by_asc, cols){
	type = "ProjectTasks";
	var column_list_container = Ext.get(genid+'columnListContainer');
	if (column_list_container) {
		column_list_container.load({
			url: og.getUrl('reporting', 'get_object_column_list_task', {object_type: type, columns:cols, orderby:order_by, orderbyasc:order_by_asc, genid:genid}),
			scripts: true
		});
	}
};

og.addCondition = function(genid, id, cpId, fieldName, condition, value, is_parametrizable, is_for_time_report, only_cps, hide_param_field, group_id){ //param is_for_time_report only used for time reporting
	var time_report = is_for_time_report;	
	var type_el = document.getElementById(genid + 'report[report_object_type_id]');
	
	if(!time_report){
		var get_object_fields = only_cps ? 'get_object_fields_custom_properties' : 'get_object_fields';
		if (!type_el) {
			alert(lang('object type not selected'));
	  		return;
		}
		var type = type_el.value;
		if(type == ""){
	  		alert(lang('object type not selected'));
	  		return;
		}
	}else{
		var get_object_fields = 'get_object_fields_custom_properties';
		var type = "ProjectTasks";
	}
	
	var condDiv = Ext.getDom(genid);
	
	var use_condition_groups = true;
	if (typeof(group_id) == 'undefined') {
		use_condition_groups = false;
	}
	
	if (group_id=='0') {
		og.last_report_group_id++;
		group_id = og.last_report_group_id;
	} else {
		if (og.last_report_group_id < group_id) {
			og.last_report_group_id = group_id;
		}
	}

	var condGroup = document.getElementById(genid + '_group_' + group_id);
	var addGroupWrapper = true;
	if (condGroup) addGroupWrapper = false;
	
	var count = $("#"+genid+" .condition-div").length;
	var classname = "condition-div";
	
	var table = '<div class="report-condition-body">' +
	'<input id="conditions[' + count + '][id]" name="conditions[' + count + '][id]" type="hidden" value="{0}"/>' +
	(use_condition_groups ? '<input type="hidden" name="conditions[' + count + '][group_id]" value="' + group_id + '" />' : '') +
	'<input id="conditions[' + count + '][deleted]" name="conditions[' + count + '][deleted]" type="hidden" value="0"/>' +
	'<div class="report-condition-fields">' +
	'<div class="report-condition-col report-condition-col-field" id="tdFields' + count + '"></div>' +
	'<div class="report-condition-col report-condition-col-operator" id="tdConditions' + count + '"></div>' +
	'<div class="report-condition-col report-condition-col-value" id="tdValue' + count + '"></div>';	
	
	if (!time_report && !hide_param_field) {
		table += '<div class="report-condition-col report-condition-col-param" id="tdIsParametrizable' + count + '">' +
		'<label for="conditions[' + count + '][is_parametrizable]">' + lang('parametrizable') + '</label>' +
		'<input type="checkbox" class="checkbox" onclick="og.changeParametrizable(' + count + ')" id="conditions[' + count + '][is_parametrizable]" name="conditions[' + count + '][is_parametrizable]" {1}">' +
		'</div>';
	}

	table += '</div>' +
	'<a href="#" id="delete' + count + '" class="report-condition-remove ico-delete" title="' + lang('remove') + '" onclick="og.deleteCondition(' + count + ', \'' + genid + '\'); return false;"></a>' +
	'</div>' +
	'<div id="tdDelete' + count + '" class="report-condition-deleted-msg" style="display:none;">' +
	'<span class="report-condition-deleted-text">' + lang('condition deleted') + '</span> ' +
	'<a class="internalLink" href="javascript:og.undoDeleteCondition(' + count + ',\'' + genid + '\')">(' + lang('undo') + ')</a>' +
	'</div>';
	
	table = String.format(table, id, (is_parametrizable == 1 ? "checked" : ""));
	
	if (use_condition_groups && addGroupWrapper) {
		condGroup = document.createElement('div');
		condGroup.id = genid + '_group_' + group_id;
		condGroup.innerHTML = '';
		condGroup.className = 'group-of-conditions';
		if (condDiv.innerHTML != '') $(condDiv).append('<div class="report-conditions-logic report-conditions-logic-and">'+lang('and').toUpperCase()+'</div>');
		condDiv.appendChild(condGroup);
	}

	var newCondition = document.createElement('div');
	newCondition.id = "Condition" + count;
	newCondition.className = classname + ' report-condition-row';
	newCondition.innerHTML = table;
	if (use_condition_groups && condGroup) {
		if (condGroup.innerHTML != '') $(condGroup).append('<div class="report-conditions-logic report-conditions-logic-or">'+lang('or').toUpperCase()+'</div>');
		condGroup.appendChild(newCondition);
		$('#'+genid+'_glink_'+group_id).remove();
		var or_link_onclick = "og.addCondition('"+genid+"', 0, 0, '', '', '', false, null, null, null, '"+group_id+"');this.remove();";
		var orLinkDiv = document.createElement('div');
		orLinkDiv.className = 'report-condition-add-or';
		orLinkDiv.innerHTML = '<a href="#" id="'+genid+'_glink_'+group_id+'" class="link-ico ico-add" onclick="'+or_link_onclick+'">'+ lang('add or condition') +'</a>';
		condGroup.appendChild(orLinkDiv);
	} else {
		condDiv.appendChild(newCondition);
	}
	
	
	og.openLink(og.getUrl('reporting', get_object_fields, {object_type: type, noaddcol:1}), {
		callback: function(success, data) {
			if (success) {
				var disabled = ((cpId > 0 || fieldName != '') ? 'disabled' : '');
				var fields = '<label for="conditions[' + count + '][custom_property_id]">' + lang('field') + '</label>' + 
					'<select class="reportConditionDD" onchange="og.fieldChanged(' + count + ', \'\', \'\', \''+genid+'\', \''+type+'\')" id="conditions[' + count + '][custom_property_id]" name="conditions[' + count + '][custom_property_id]" ' + disabled + ' >';					
				
				for(var i=0; i < data.fields.length; i++){
					var field = data.fields[i];
					if(id > 0 && (field.id != cpId && fieldName != field.id)) continue;
					fields += '<option value="' + field.id + '" class="' + field.type + '">' + og.clean(field.name) + '</option>';
					if(field.values){
						if(!fieldValues[count]){
							fieldValues[count] = {};
						}
						fieldValues[count][field.id] = field.values;
					}						
				}
				fields += '</select>';	
				if(cpId > 0){
					fields += '<input type="hidden" name="conditions[' + count + '][custom_property_id]" value="' + cpId + '">';
				}
				
				$("#" + genid + " #tdFields" + count).html(fields);
				var selectEl = $("#" + genid + " [name='conditions[" + count + "][custom_property_id]']")[0];
				if (selectEl) {
					var targetFieldId = cpId > 0 ? String(cpId) : (fieldName ? String(fieldName) : '');
					if (targetFieldId) {
						for (var j = 0; j < selectEl.options.length; j++) {
							if (String(selectEl.options[j].value) == targetFieldId) {
								selectEl.selectedIndex = j;
								break;
							}
						}
					}
				}
				og.fieldChanged(count, (condition != "" ? condition : ""), (value != "" ? value : ""), genid, type);
			}
		},
		scope: this
	});
	if(id == 0){
		modified = true;
	}
};

og.deleteCondition = function(id, genid){
	$("#" + genid + " #Condition" + id).addClass('is-deleted');
	$("#" + genid + " #tdDelete" + id).show();
	$("#" + genid + " [name='conditions[" + id + "][deleted]']").val(1);
	$("#" + genid + " #delete" + id).hide();
	modified = true;
};

og.undoDeleteCondition = function(id, genid){
	$("#" + genid + " #Condition" + id).removeClass('is-deleted');
	$("#" + genid + " #tdDelete" + id).hide();
	$("#" + genid + " [name='conditions[" + id + "][deleted]']").val(0);
	$("#" + genid + " #delete" + id).show();
	/*var conditionDiv = Ext.getDom(genid);
	for(var i=0; i < conditionDiv.childNodes.length; i++){
		var nextCond = conditionDiv.childNodes.item(i);
		if(nextCond.id == ('Condition' + id)){
			nextCond.style.background = '';
			if (i % 2 == 0) {
				nextCond.className = "";
	  		} else {
	  			nextCond.className = "odd";
	  		}
	  		return;
		}
	}*/
};

og.onReportCondOperatorChange = function(select, id, genid){
	let value = $(select).val();
	if (value == 'empty') {
		$("#" + genid + " #tdValue" + id).hide();
		$("#" + genid + " #tdIsParametrizable" + id).hide();
	} else {
		$("#" + genid + " #tdValue" + id).show();
		$("#" + genid + " #tdIsParametrizable" + id).show();
	}
}

og.fieldChanged = function(id, condition, value, genid, object_type_id){
	var ot = og.objectTypes[object_type_id];
	var fields = $("#" + genid + " [name='conditions[" + id + "][custom_property_id]']");
	fields = fields[0];
	var selField = fields.selectedIndex;
	if(selField != -1){
		var fieldType = fields[selField].className;
		var onOperatorChange = 'og.onReportCondOperatorChange(this, ' + id + ', \''+genid+'\');';
		var type_and_name = '<input type="hidden" name="conditions[' + id + '][field_name]" value="' + fields[selField].value + '"/>' +
			'<input type="hidden" name="conditions[' + id + '][field_type]" value="' + fieldType + '"/>'; 
		var conditions = '<label for="conditions[' + id + '][condition]">' + lang('condition') + '</label><select class="reportConditionDD" id="conditions[' + id + '][condition]" name="conditions[' + id + '][condition]" onchange="' + onOperatorChange + '">';
		var dateContainerId = genid + 'condition_value_' + id;
		var textValueField = '<label for="conditions[' + id + '][value]">' + lang('value') + '</label>' +
			'<div class="report-condition-value-input"><input type="text" id="conditions[' + id + '][value]" name="conditions[' + id + '][value]" value="' + value + '"/></div>' + type_and_name;
		var dateValueField = '<label for="' + dateContainerId + '">' + lang('value') + '</label>' +
			'<div class="report-condition-value-input"><div id="' + dateContainerId + '"></div></div>' + type_and_name; 
		
		if(fieldType == "text" || fieldType == "memo"){
			$("#" + genid + " #tdValue" + id).html(textValueField);
			conditions += '<option value="like">' + lang('like') + '</option>';
			conditions += '<option value="not like">' + lang('not like') + '</option>';
			conditions += '<option value="=">' + lang('equals') + '</option>';
			conditions += '<option value="<>">' + lang('not equals') + '</option>';
			conditions += '<option value="%">' + lang('ends with') + '</option>';
			conditions += '</select>';
			$("#" + genid + " #tdConditions" + id).html(conditions);
			
		}else if(fieldType == "numeric"){
			$("#" + genid + " #tdValue" + id).html(textValueField);
			conditions += '<option value=">">&gt;</option>';
			conditions += '<option value=">=">&ge;</option>';
			conditions += '<option value="<">&lt;</option>';
			conditions += '<option value="<=">&le;</option>';
			conditions += '<option value="=">=</option>';
			conditions += '<option value="<>"><></option>';
			conditions += '<option value="%">' + lang('ends with') + '</option>';
			conditions += '</select>';
			$("#" + genid + " #tdConditions" + id).html(conditions);
			
		}else if(fieldType == "boolean"){
			var values = '<b>' + lang('value') + '</b>:<br/><select class="reportConditionDD" id="conditions[' + id + '][value]" name="conditions[' + id + '][value]">';
			values += '<option value="0"' + (value == false ? "selected" : "") + '></option>';
			values += '<option value="1" ' + (value != "" && value == true ? "selected" : "") + '>' + lang('true') + '</option>';
			values += '<option value="-1"' + (value == -1 ? "selected" : "") + '>' + lang('false') + '</option>';
			values += '</select>' + type_and_name;
			$("#" + genid + " #tdValue" + id).html(values);
			conditions += '<option value="=">' + lang('equals') + '</option>';
			conditions += '</select>';
			$("#" + genid + " #tdConditions" + id).html(conditions);
			
		} else if(fieldType == "date" || fieldType == "datetime"){
			$("#" + genid + " #tdValue" + id).html(dateValueField);
			conditions += '<option value=">">&gt;</option>';
			conditions += '<option value=">=">&ge;</option>';
			conditions += '<option value="<">&lt;</option>';
			conditions += '<option value="<=">&le;</option>';
			conditions += '<option value="=">=</option>';
			conditions += '<option value="<>"><></option>';
			conditions += '<option value="empty">'+lang('empty')+'</option>';
			conditions += '</select>';
			$("#" + genid + " #tdConditions" + id).html(conditions);
			
			var dateCond = new og.DateField({
				renderTo: dateContainerId,
				name: 'conditions[' + id + '][value]',
				id: 'conditions[' + id + '][value]',
				value: Ext.util.Format.date(value, og.preferences['date_format'])
			});
			
		}else if(fieldType == "list"){
			var valuesList = fieldValues[id][fields[selField].value].split(',');
			var listValueField = '<label for="conditions[' + id + '][value]">' + lang('value') + '</label>' +
				'<div class="report-condition-value-input"><select class="reportConditionDD" id="conditions[' + id + '][value]" name="conditions[' + id + '][value]">';
			listValueField += '<option value="">-- ' + lang('none') + ' --</option>';
			for(var i=0; i < valuesList.length; i++){
				let option_lang = lang(valuesList[i]);
				if (option_lang.indexOf('Missing lang') > -1) option_lang = valuesList[i];
				listValueField += '<option ' + (valuesList[i] == value ? "selected" : "") + '>' + option_lang + '</option>';
			}
			listValueField += '</select></div>' + type_and_name;
			$("#" + genid + " #tdValue" + id).html(listValueField);
			conditions += '<option value="=">=</option>';
			conditions += '<option value="<>"><></option>';
			conditions += '</select>';
			$("#" + genid + " #tdConditions" + id).html(conditions);
			
		}else if(fieldType == "external" || fieldType == "contact" || fieldType == "user"){
			
			var objectTypeSel = document.getElementById('objectTypeSel');
			og.openLink(og.getUrl('reporting', 'get_external_field_values', {external_field: fields[selField].value, report_type: objectTypeSel[objectTypeSel.selectedIndex].value}), {
				callback: function(success, data) {
					if (success) {
						var externalValueField = '<label for="conditions[' + id + '][value]">' + lang('value') + '</label>';
						var external_fields_values = [];
						for(var j=0; j < data.values.length; j++){
							var extValue = data.values[j];							
							external_fields_values.push([extValue.id,extValue.name]);
						}
						
						externalValueField += type_and_name;

					    var external_fields_store = new Ext.data.SimpleStore({
	    		        	fields: ["id", "name"],
	    		        	data: external_fields_values
	    				});	 
					    
					    $('#tdValue'+id).html('<label for="containerConditions[' + id + '][value]">' + lang('value') + '</label><br/>' + '<span id="'+genid+'containerConditions[' + id + '][value]"></span>' + type_and_name);
						
					    var tsContactCombo = new Ext.form.ComboBox({
				    		renderTo:'tdValue'+id,					    		
				    		name: genid+"conditions["+id+"][value]",
				    		id: genid+"conditions["+id+"][value]",
				    		value: value,
				    		store: external_fields_store,
				    		mode: 'local',
				            cls: 'assigned-to-combo',
				            triggerAction: 'all',
				            selectOnFocus:true,
				            listClass: 'assigned-to-combo-list',
				            displayField    : 'name',
				            valueField        : 'id',
				            hiddenName : "conditions["+id+"][value]",				            
				            emptyText: '',
				            valueNotFoundText: ''
					    });	
						
						if(condition != ""){
							var parametrizable = $("#" + genid + " [name='conditions[" + id + "][is_parametrizable]']").attr('checked') == 'checked';
							if (parametrizable) {
								$("#" + genid + " [name='conditions[" + id + "][value]']").attr('disabled', 'disabled');								
							}
						}
					}
				}
			});
			
			conditions += '<option value="=">=</option>';
			conditions += '<option value="<>"><></option>';
			conditions += '</select>';
			$("#" + genid + " #tdConditions" + id).html(conditions);
			
		} else if(ot && ot.name == 'task' && fieldType == "calculated" && fields[selField].value == "status"){
			var values = '<b>' + lang('value') + '</b>:<br/><select class="reportConditionDD" id="conditions[' + id + '][value]" name="conditions[' + id + '][value]">';
			values += '<option value="0"' + (value == false ? "selected" : "") + '> '+ lang('pending') +'</option>';
			values += '<option value="1" ' + (value != "" && value == true ? "selected" : "") + '>' + lang('complete') + '</option>';			
			values += '</select>' + type_and_name;
			$("#" + genid + " #tdValue" + id).html(values);
			conditions += '<option value="=">' + lang('equals') + '</option>';
			conditions += '</select>';
			$("#" + genid + " #tdConditions" + id).html(conditions);			
		}
		
		if (og.additional_report_condition_renderers && og.additional_report_condition_renderers.length > 0) {
			
			for (var i=0; i<og.additional_report_condition_renderers.length; i++) {
				var fn = og.additional_report_condition_renderers[i];
				if (typeof(fn) == 'function') {
					var render_params = {
						id: id,
						ot: ot,
						type_and_name: type_and_name,
						fieldType: fieldType,
						selected: fields[selField].value,
						conditions: conditions,
						value: value,
						div_id: genid
					}
					fn.call(null, render_params);
				}
			}
		}
		
		$("#" + genid + " [name='conditions[" + id + "][condition]']").val(condition);
		setTimeout(function(){
			$("#" + genid + " [name='conditions[" + id + "][condition]']").change();
		}, 100);
		
		var selector = "#" + genid + " [name='conditions[" + id + "][condition]']";
		og.advanced_reports.hiddenInputType(selector);
		if(condition == "") {
			$("#" + genid + " [name='conditions[" + id + "][is_parametrizable]']").removeAttr('checked');
			modified = true;
		}else{
			var parametrizable = $("#" + genid + " [name='conditions[" + id + "][is_parametrizable]']").attr('checked') == 'checked';
			if (parametrizable) {
				$("#" + genid + " [name='conditions[" + id + "][value]']").attr('disabled', 'disabled');
			}
		}
	}
};

og.changeParametrizable = function(id){
	var isparam_el = document.getElementById('conditions[' + id + '][is_parametrizable]');
	var parametrizable = isparam_el && isparam_el.checked;
	var valueField = document.getElementById('conditions[' + id + '][value]');
	if(valueField){
		valueField.disabled = parametrizable;
	}
	modified = true;
};

og.validateReport = function(genid){
	var conditions = $("#"+genid+" .condition-div");
	for(var i=0; i < conditions.length; i++){
		var is_del_el = document.getElementById('conditions[' + i + '][deleted]');
		var deleted = is_del_el ? is_del_el.value : '0';
		var isparam_el = document.getElementById('conditions[' + i + '][is_parametrizable]');
		var parametrizable = isparam_el && isparam_el.checked;
		if(deleted == "0" && !parametrizable){
			var fields = document.getElementById('conditions[' + i + '][custom_property_id]');
			var fieldName = fields[fields.selectedIndex].text;
			var field_db = fields[fields.selectedIndex].value;
			if(field_db == 'workspace') continue;
			
			var fieldType = fields[fields.selectedIndex].className;
			
			var val_el = document.getElementById('conditions[' + i + '][value]');
			var value = val_el ? val_el.value : '';
			
			if (fieldType == 'numeric') {
				var cond_el = document.getElementById('conditions[' + i + '][condition]');
				if (cond_el) {
					var condition = cond_el.value;
					if(condition != '%' && !og.isReportFieldNumeric(value)){
						alert(lang('condition value not numeric', fieldName));
						return false;
					}
				}
			}
		}
	}

	var columns = document.getElementsByTagName('input');
	var colSelected = false;
	for(var j=0; j < columns.length; j++){
		var item = columns[j];
		if (item.type == 'hidden' && item.name.indexOf('columns') == 0) {
			var item = columns[j];
			if(item.value > 0){
				colSelected = true;
				break;
			}
		}
	}
	if(!colSelected){
		alert(lang('report cols not selected'));
		return false;
	}
	return true;
};

og.toggleColumnSelection = function(){
	var columns = document.getElementsByName('columns[]');
	var checked = document.getElementById('columns[]').checked;
	var columnFields = document.getElementById('tdFields');
	var columnCPs = document.getElementById('tdCPs');
	for(var i=0; i < columns.length; i++){
		columns[i].checked = (checked ? '' : 'checked');
	}
};

og.isReportFieldNumeric = function(sText){
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
};

og.deleteReport = function(id){
	if(confirm(lang('delete report confirmation'))){
		og.openLink(og.getUrl('reporting', 'delete_custom_report', {id: id}));
	}
};

og.tttReportGbSelected = function(select, genid){
	var show = select.options[select.selectedIndex].value != 0;
	
	if (select.name == 'report[group_by_1]'){
		var html = '';
		
		if (show){
			if (document.getElementById(genid + 'gbspan2').innerHTML == ''){
				html = document.getElementById(genid + 'gbspan1').innerHTML;
				html = html.replace(/group_by_1/g, 'group_by_2');
				document.getElementById(genid + 'gbspan2').innerHTML = html;
			}
		} else {
			var gb3 = document.getElementById(genid + 'group_by_3');
			if (gb3){
				document.getElementById(genid + 'gbspan1').innerHTML = document.getElementById(genid + 'gbspan2').innerHTML;
				document.getElementById(genid + 'gbspan2').innerHTML = document.getElementById(genid + 'gbspan3').innerHTML;
				document.getElementById(genid + 'gbspan3').innerHTML = '';
			}
		}
	}
	
	if (select.name == 'report[group_by_2]'){
		var html = '';
		
		if (show){
			if (document.getElementById(genid + 'gbspan3').innerHTML == ''){
				html = document.getElementById(genid + 'gbspan2').innerHTML;
				html = html.replace(/group_by_2/g, 'group_by_3');
				document.getElementById(genid + 'gbspan3').innerHTML = html;
			}
		} else {
			var gb3 = document.getElementById(genid + 'group_by_3');
			if (gb3){
				document.getElementById(genid + 'gbspan2').innerHTML = document.getElementById(genid + 'gbspan3').innerHTML;
				document.getElementById(genid + 'gbspan3').innerHTML = '';
			}
		}
	}				
};

og.showPDFOptions = function(){
	document.getElementById('pdfOptions').style.display = '';
};

og.openReportAction = function(url, reportId) {
	$('.report-actions-btn').popover('hide');
	if (reportId) {
		og.deleteReport(reportId);
	} else if (url) {
		og.openLink(url);
	}
};

og.initReportActionMenus = function() {
	$('.report-actions-btn').each(function() {
		var btn = $(this);
		if (btn.data('popover-initialized')) return;
		btn.data('popover-initialized', true);
		var popover_options = {
			content: "example",
			delay: {show: "100", hide: "200"},
			template: $("#" + btn.data("templateid")).html()
		};
		if ($.browser.mozilla) {
			popover_options.trigger = 'focus';
			btn.on('click', function() { $(this).focus(); });
		}
		btn.popover(popover_options);

		btn.on('click', function() {
			$('.report-actions-btn').not(this).popover('hide');
		});
	});

	if (!og.reportActionMenusDocumentBound) {
		og.reportActionMenusDocumentBound = true;

		$(document).on('click.reportActions', '.report-action-link', function(e) {
			e.preventDefault();
			e.stopPropagation();
			var reportId = $(this).attr('data-report-id');
			var url = $(this).attr('data-report-url');
			if (reportId) {
				og.openReportAction(null, parseInt(reportId, 10));
			} else if (url) {
				og.openReportAction(url);
			}
			return false;
		});

		$(document).on('click.reportActionsClose', function(e) {
			if (!$(e.target).closest('.report-actions-btn, .popover, .report-actions-menu').length) {
				$('.report-actions-btn').popover('hide');
			}
		});
	}
};
