var modified = false;
var selectedObjTypeIndex = -1;
var fieldValues = {};

og.loadReportingFlags = function(){
	modified = false;
	selectedObjTypeIndex = -1;
	fieldValues = {};
	og.last_report_group_id = 0;
};

og.enterCondition = function(id) {
  	var deleted = document.getElementById('conditions[' + id + '][deleted]');
  	if(deleted && deleted.value == "0"){
  		$("#delete"+id).css('opacity', '1.0');
 	}
};

og.leaveCondition = function(id) {
	$("#delete"+id).css('opacity', '0.6');
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
	
	var style = 'style="width:130px;padding-right:10px;"';
	var table = '<table onmouseover="og.enterCondition(' + count + ')" onmouseout="og.leaveCondition(' + count + ')"><tr>' +
	'<td><input id="conditions[' + count + '][id]" name="conditions[' + count + '][id]" type="hidden" value="{0}"/>' +
	(use_condition_groups ? '<input type="hidden" name="conditions[' + count + '][group_id]" value="' + group_id + '" />' : '') +
	'</td>' +
	'<td><input id="conditions[' + count + '][deleted]" name="conditions[' + count + '][deleted]" type="hidden" value="0"/></td>' +
	'<td ' + style + ' id="tdFields' + count + '"></td>' +
	'<td ' + style + ' id="tdConditions' + count + '"></td>' +
	'<td ' + style + ' id="tdValue' + count + '"><b>' + lang('value') + '</b>:<br/>' +
	'<input type="text" style="width:100px;" id="conditions[' + count + '][value]" name="conditions[' + count + '][value]" name="conditions[' + count + '][value]" value="{1}" ></td>';	
	
	if (!time_report && !hide_param_field) {
		table = table + '<td ' + style + '><label for="conditions[' + count + '][is_parametrizable]">' + lang('parametrizable') + '</label>' + 
		'<input type="checkbox" class="checkbox" onclick="og.changeParametrizable(' + count + ')" id="conditions[' + count + '][is_parametrizable]" name="conditions[' + count + '][is_parametrizable]" {2}></td>';	
	}
	
	table = table +'<td style="padding-left:20px;"><div style="opacity:0.6;" id="delete' + count + '" class="clico ico-delete" onclick="og.deleteCondition(' + count + ',\'' + genid + '\')"></div></td>' +
	'<td id="tdDelete' + count + '" style="display:none;"><b>' + lang('condition deleted') +
	'</b><a class="internalLink" href="javascript:og.undoDeleteCondition(' + count + ',\'' + genid + '\')">&nbsp;(' + lang('undo') + ')</a></td>' +
  	'</tr></table>';
	
	// add "OR" condition link
	if (use_condition_groups) {
		$('#'+genid+'_glink_'+group_id).remove();
		var or_link_onclick = "og.addCondition('"+genid+"', 0, 0, '', '', '', false, null, null, null, '"+group_id+"');this.remove();";
		table += '<div style="margin-top:5px">'+
			'<a href="#" id="'+genid+'_glink_'+group_id+'" class="link-ico ico-add" onclick="'+or_link_onclick+'">'+ lang('add or condition') +'</a></div>';
	}

	table = String.format(table, id, value, (is_parametrizable == 1 ? "checked" : ""));
	
	if (use_condition_groups && addGroupWrapper) {
		var condGroup = document.createElement('div');
		condGroup.id = genid + '_group_' + group_id;
		condGroup.innerHTML = '';
		condGroup.className = 'group-of-conditions';
		if (condDiv.innerHTML != '') $(condDiv).append('<div class="bold">'+lang('and').toUpperCase()+'</div>');
		condDiv.appendChild(condGroup);
	}

	var newCondition = document.createElement('div');
	newCondition.id = "Condition" + count;
	newCondition.style.padding = "5px";
	newCondition.className = classname;
	newCondition.innerHTML = table;
	if (use_condition_groups && condGroup) {
		if (condGroup.innerHTML != '') $(condGroup).append('<div class="bold" style="padding-left: 5px;">'+lang('or').toUpperCase()+'</div>');
		condGroup.appendChild(newCondition);
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
						if(id == 0){
							fieldValues[count][i] = field.values;
						}else{
							fieldValues[count][0] = field.values;
						}
					}						
				}
				fields += '</select>';	
				if(cpId > 0){
					fields += '<input type="hidden" name="conditions[' + count + '][custom_property_id]" value="' + cpId + '">';
				}
				
				$("#" + genid + " #tdFields" + count).html(fields);
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
	$("#" + genid + " #Condition" + id).css('background', '#FFDEAD');
	$("#" + genid + " #tdDelete" + id).css('display', '');
	$("#" + genid + " [name='conditions[" + id + "][deleted]']").val(1);
	$("#" + genid + " #delete" + id).hide();
	modified = true;
};

og.undoDeleteCondition = function(id, genid){
	$("#" + genid + " #tdDelete" + id).css('display', 'none');
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

og.fieldChanged = function(id, condition, value, genid, object_type_id){
	var ot = og.objectTypes[object_type_id];
	var fields = $("#" + genid + " [name='conditions[" + id + "][custom_property_id]']");
	fields = fields[0];
	var selField = fields.selectedIndex;
	if(selField != -1){
		var fieldType = fields[selField].className;
		var type_and_name = '<input type="hidden" name="conditions[' + id + '][field_name]" value="' + fields[selField].value + '"/>' +
			'<input type="hidden" name="conditions[' + id + '][field_type]" value="' + fieldType + '"/>'; 
		var conditions = '<label for="conditions[' + id + '][condition]">' + lang('condition') + '</label><select class="reportConditionDD" id="conditions[' + id + '][condition]" name="conditions[' + id + '][condition]">';
		var textValueField = '<label for="conditions[' + id + '][value]">' + lang('value') + '</label><input type="text" style="width:100px;" id="conditions[' + id + '][value]" name="conditions[' + id + '][value]" value="' + value + '"/>' + type_and_name;
		var dateValueField = '<label for="containerConditions[' + id + '][value]">' + lang('value') + '</label>' + '<span id="'+genid+'containerConditions[' + id + '][value]"></span>' + type_and_name; 
		
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
			conditions += '</select>';
			$("#" + genid + " #tdConditions" + id).html(conditions);
			
			var dateCond = new og.DateField({
				renderTo: genid + 'containerConditions[' + id + '][value]',
				name: 'conditions[' + id + '][value]',
				id: 'conditions[' + id + '][value]',
				value: Ext.util.Format.date(value, og.preferences['date_format'])
			});
			
		}else if(fieldType == "list"){
			var valuesList = fieldValues[id][selField].split(',');
			var listValueField = '<label for="conditions[' + id + '][value]">' + lang('value') + '</label><select class="reportConditionDD" id="conditions[' + id + '][value]" name="conditions[' + id + '][value]">';
			listValueField += '<option value="">-- ' + lang('none') + ' --</option>';
			for(var i=0; i < valuesList.length; i++){
				listValueField += '<option ' + (valuesList[i] == value ? "selected" : "") + '>' + valuesList[i] + '</option>';
			}
			listValueField += '</select>' + type_and_name; 
			$("#" + genid + " #tdValue" + id).html(listValueField);
			conditions += '<option value="=">=</option>';
			conditions += '<option value="<>"><></option>';
			conditions += '</select>';
			$("#" + genid + " #tdConditions" + id).html(conditions);
			
		}else if(fieldType == "external"){
			
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
					    
					    $('#tdValue'+id).html('<label for="containerConditions[' + id + '][value]">' + lang('value') + '</label>' + '<span id="'+genid+'containerConditions[' + id + '][value]"></span>' + type_and_name);
						
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
				            width: 100,
				            listWidth: 100,
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
			if(item.value == 1){
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
