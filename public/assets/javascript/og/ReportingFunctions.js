var modified = false;
var selectedObjTypeIndex = -1;
var fieldValues = {};

og.loadReportingFlags = function(){
	modified = false;
	selectedObjTypeIndex = -1;
	fieldValues = {};
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

og.reportObjectTypeChanged = function(genid, order_by, order_by_asc, cols){
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
		
		document.getElementById('report[report_object_type_id]').value = type;
		
		Ext.get('columnListContainer').load({
			url: og.getUrl('reporting', 'get_object_column_list', {object_type: type, columns:cols, orderby:order_by, orderbyasc:order_by_asc, genid:genid}),
			scripts: true
		});

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

og.addCondition = function(genid, id, cpId, fieldName, condition, value, is_parametrizable, is_for_time_report){ //param is_for_time_report only used for time reporting
	var time_report = false;	
	if (is_for_time_report!=null)
		time_report = true;
	if(!time_report){		
		var get_object_fields = 'get_object_fields';
		var type = document.getElementById('report[report_object_type_id]').value;
		if(type == ""){
	  		alert(lang('object type not selected'));
	  		return;
		}
	}else{
		var get_object_fields = 'get_object_fields_custom_properties';
		var type = "ProjectTasks";
	}

	var condDiv = Ext.getDom(genid);
	var count = condDiv.childNodes.length;
	var classname = "";
	if (count % 2 != 0) {
		classname = "odd";
	}
	var style = 'style="width:130px;padding-right:10px;"';
	var table = '<table onmouseover="og.enterCondition(' + count + ')" onmouseout="og.leaveCondition(' + count + ')"><tr>' +
	'<td><input id="conditions[' + count + '][id]" name="conditions[' + count + '][id]" type="hidden" value="{0}"/></td>' +
	'<td><input id="conditions[' + count + '][deleted]" name="conditions[' + count + '][deleted]" type="hidden" value="0"/></td>' +
	'<td ' + style + ' id="tdFields' + count + '"></td>' +
	'<td ' + style + ' id="tdConditions' + count + '"></td>' +
	'<td ' + style + ' id="tdValue' + count + '"><b>' + lang('value') + '</b>:<br/>' +
	'<input type="text" style="width:100px;" id="conditions[' + count + '][value]" name="conditions[' + count + '][value]" name="conditions[' + count + '][value]" value="{1}" ></td>';	
	
	if (!time_report) {
		table = table + '<td ' + style + '><label for="conditions[' + count + '][is_parametrizable]">' + lang('parametrizable') + '</label>' + 
		'<input type="checkbox" class="checkbox" onclick="og.changeParametrizable(' + count + ')" id="conditions[' + count + '][is_parametrizable]" name="conditions[' + count + '][is_parametrizable]" {2}></td>';	
	}
	
	table = table +'<td style="padding-left:20px;"><div style="opacity:0.6;" id="delete' + count + '" class="clico ico-delete" onclick="og.deleteCondition(' + count + ',\'' + genid + '\')"></div></td>' +
	'<td id="tdDelete' + count + '" style="display:none;"><b>' + lang('condition deleted') +
	'</b><a class="internalLink" href="javascript:og.undoDeleteCondition(' + count + ',\'' + genid + '\')">&nbsp;(' + lang('undo') + ')</a></td>' +
  	'</tr></table>';

	table = String.format(table, id, value, (is_parametrizable == 1 ? "checked" : ""));

	var newCondition = document.createElement('div');
	newCondition.id = "Condition" + count;
	newCondition.style.padding = "5px";
	newCondition.className = classname;
	newCondition.innerHTML = table;
	condDiv.appendChild(newCondition);
	og.openLink(og.getUrl('reporting', get_object_fields, {object_type: type, noaddcol:1}), {
		callback: function(success, data) {
			if (success) {
				var disabled = ((cpId > 0 || fieldName != '') ? 'disabled' : '');
				var fields = '<label for="conditions[' + count + '][custom_property_id]">' + lang('field') + '</label>' + 
				'<select class="reportConditionDD" onchange="og.fieldChanged(' + count + ', \'\', \'\')" id="conditions[' + count + '][custom_property_id]" name="conditions[' + count + '][custom_property_id]" ' + disabled + ' >';					
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
				document.getElementById('tdFields' + count).innerHTML = fields;
				og.fieldChanged(count, (condition != "" ? condition : ""), (value != "" ? value : ""));				
			}
		},
		scope: this
	});
	if(id == 0){
		modified = true;
	}
};

og.deleteCondition = function(id, genid){
	var conditionDiv = document.getElementById('Condition' + id);
	conditionDiv.style.background = '#FFDEAD';
	document.getElementById('tdDelete' + id).style.display = '';
	document.getElementById('conditions[' + id + '][deleted]').value = 1;
	$("#delete"+id).hide();
	modified = true;
};

og.undoDeleteCondition = function(id, genid){
	document.getElementById('tdDelete' + id).style.display = 'none';
	document.getElementById('conditions[' + id + '][deleted]').value = 0;
	$("#delete"+id).show();
	var conditionDiv = Ext.getDom(genid);
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
	}
};

og.fieldChanged = function(id, condition, value){
	var fields = document.getElementById('conditions[' + id + '][custom_property_id]');
	var selField = fields.selectedIndex;
	if(selField != -1){
		var fieldType = fields[selField].className;
		var type_and_name = '<input type="hidden" name="conditions[' + id + '][field_name]" value="' + fields[selField].value + '"/>' +
		'<input type="hidden" name="conditions[' + id + '][field_type]" value="' + fieldType + '"/>'; 
		var conditions = '<label for="conditions[' + id + '][condition]">' + lang('condition') + '</label><select class="reportConditionDD" id="conditions[' + id + '][condition]" name="conditions[' + id + '][condition]">';
		var textValueField = '<label for="conditions[' + id + '][value]">' + lang('value') + '</label><input type="text" style="width:100px;" id="conditions[' + id + '][value]" name="conditions[' + id + '][value]" value="' + value + '"/>' + type_and_name;
		var dateValueField = '<label for="containerConditions[' + id + '][value]">' + lang('value') + '</label>' + '<span id="containerConditions[' + id + '][value]"></span>' + type_and_name; 
		
		if(fieldType == "text" || fieldType == "memo"){
			document.getElementById('tdValue' + id).innerHTML = textValueField;
			conditions += '<option value="like">' + lang('like') + '</option>';
			conditions += '<option value="not like">' + lang('not like') + '</option>';
			conditions += '<option value="=">' + lang('equals') + '</option>';
			conditions += '<option value="<>">' + lang('not equals') + '</option>';
			conditions += '<option value="%">' + lang('ends with') + '</option>';
			conditions += '</select>';
			document.getElementById('tdConditions' + id).innerHTML = conditions;
		}else if(fieldType == "numeric"){
			document.getElementById('tdValue' + id).innerHTML = textValueField;
			conditions += '<option value=">">&gt;</option>';
			conditions += '<option value=">=">&ge;</option>';
			conditions += '<option value="<">&lt;</option>';
			conditions += '<option value="<=">&le;</option>';
			conditions += '<option value="=">=</option>';
			conditions += '<option value="<>"><></option>';
			conditions += '<option value="%">' + lang('ends with') + '</option>';
			conditions += '</select>';
			document.getElementById('tdConditions' + id).innerHTML = conditions;
		}else if(fieldType == "boolean"){
			var values = '<b>' + lang('value') + '</b>:<br/><select class="reportConditionDD" id="conditions[' + id + '][value]" name="conditions[' + id + '][value]">';
			values += '<option value="1" ' + (value != "" && value == true ? "selected" : "") + '>' + lang('true') + '</option>';
			values += '<option value="0"' + (value == false ? "selected" : "") + '>' + lang('false') + '</option>';
			values += '</select>' + type_and_name;
			document.getElementById('tdValue' + id).innerHTML = values;
			conditions += '<option value="=">' + lang('equals') + '</option>';
			conditions += '</select>';
			document.getElementById('tdConditions' + id).innerHTML = conditions;
		} else if(fieldType == "date"){
			document.getElementById('tdValue' + id).innerHTML = dateValueField;
			conditions += '<option value=">">&gt;</option>';
			conditions += '<option value=">=">&ge;</option>';
			conditions += '<option value="<">&lt;</option>';
			conditions += '<option value="<=">&le;</option>';
			conditions += '<option value="=">=</option>';
			conditions += '<option value="<>"><></option>';
			conditions += '</select>';
			document.getElementById('tdConditions' + id).innerHTML = conditions;
			
			var dateCond = new og.DateField({
				renderTo:'containerConditions[' + id + '][value]',
				name: 'conditions[' + id + '][value]',
				id: 'conditions[' + id + '][value]',
				value: Ext.util.Format.date(value, og.preferences['date_format'])
			});
		}else if(fieldType == "list"){
			var valuesList = fieldValues[id][selField].split(',');
			var listValueField = '<label for="conditions[' + id + '][value]">' + lang('value') + '</label><select class="reportConditionDD" id="conditions[' + id + '][value]" name="conditions[' + id + '][value]">';
			for(var i=0; i < valuesList.length; i++){
				listValueField += '<option ' + (valuesList[i] == value ? "selected" : "") + '>' + valuesList[i] + '</option>';
			}
			listValueField += '</select>' + type_and_name; 
			document.getElementById('tdValue' + id).innerHTML = listValueField;
			conditions += '<option value="=">=</option>';
			conditions += '<option value="<>"><></option>';
			conditions += '</select>';
			document.getElementById('tdConditions' + id).innerHTML = conditions;
		}else if(fieldType == "external"){
			
			var objectTypeSel = document.getElementById('objectTypeSel');
			og.openLink(og.getUrl('reporting', 'get_external_field_values', {external_field: fields[selField].value, report_type: objectTypeSel[objectTypeSel.selectedIndex].value}), {
				callback: function(success, data) {
					if (success) {
						var externalValueField = '<label for="conditions[' + id + '][value]">' + lang('value') + '</label><select class="reportConditionDD" id="conditions[' + id + '][value]" name="conditions[' + id + '][value]">';
						for(var j=0; j < data.values.length; j++){
							var extValue = data.values[j];
							externalValueField += '<option value="' + extValue.id + '" ' + (extValue.id == value ? "selected" : "") + '>' + extValue.name + '</option>';
						}
						externalValueField += '</select>' + type_and_name; 
						document.getElementById('tdValue' + id).innerHTML = externalValueField;
						
						if(condition != ""){
							var isparam_el = document.getElementById('conditions[' + id + '][is_parametrizable]');
							var parametrizable = isparam_el && isparam_el.checked;
							var valueField = document.getElementById('conditions[' + id + '][value]');
							valueField.disabled = parametrizable;
						}
					}
				}
			});
			
			conditions += '<option value="=">=</option>';
			conditions += '<option value="<>"><></option>';
			conditions += '</select>';
			document.getElementById('tdConditions' + id).innerHTML = conditions;
		}
		
		var conditionSel = document.getElementById('conditions[' + id + '][condition]');
		for(var j=0; j < conditionSel.options.length; j++){ 
			if(conditionSel.options[j].value == condition){
				conditionSel.selectedIndex = j;
			}
		}
		if(condition == "") {
			var isparam_el = document.getElementById('conditions[' + id + '][is_parametrizable]');
			if (isparam_el) isparam_el.checked = false;
			modified = true;
		}else{
			var isparam_el = document.getElementById('conditions[' + id + '][is_parametrizable]');
			var parametrizable = isparam_el && isparam_el.checked;
			var valueField = document.getElementById('conditions[' + id + '][value]');
			if(valueField){
				valueField.disabled = parametrizable;
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
	var cpConditions = Ext.getDom(genid);
	for(var i=0; i < cpConditions.childNodes.length; i++){
		var deleted = document.getElementById('conditions[' + i + '][deleted]').value;
		var isparam_el = document.getElementById('conditions[' + i + '][is_parametrizable]');
		var parametrizable = isparam_el && isparam_el.checked;
		if(deleted == "0" && !parametrizable){
			var fields = document.getElementById('conditions[' + i + '][custom_property_id]');
			var fieldName = fields[fields.selectedIndex].text;
			var field_db = fields[fields.selectedIndex].value;
			if(field_db == 'workspace') continue;
			var value = document.getElementById('conditions[' + i + '][value]').value;
			if(value == ""){
				alert(lang('condition value empty', fieldName));
				return false;
			}
			var fieldType = fields[fields.selectedIndex].className;
			var condition = document.getElementById('conditions[' + i + '][condition]').value;
			if(fieldType == 'numeric' && condition != '%' && !og.isReportFieldNumeric(value)){
				alert(lang('condition value not numeric', fieldName));
				return false;
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
