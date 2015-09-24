var cpModified = false;
var selectedObjTypeIndex = -1;
og.isMemberCustomProperties = 0;

og.loadCustomPropertyFlags = function(is_member_cps){
	cpModified = false;
	selectedObjTypeIndex = -1;
	og.isMemberCustomProperties = is_member_cps ? 1 : 0;
};

og.objectTypeChanged = function(genid){
	var objectTypeSel = document.getElementById('objectTypeSel');
	if(cpModified){
		if(!confirm(lang('confirm discard changes'))){
			objectTypeSel.selectedIndex = selectedObjTypeIndex;
			return;
		}
	}
	cpModified = false;
	selectedObjTypeIndex = objectTypeSel.selectedIndex; 
	if(selectedObjTypeIndex != -1){
		var cpDiv = Ext.getDom(genid);
		while(cpDiv.firstChild){
			cpDiv.removeChild(cpDiv.firstChild);
		}
		var type = objectTypeSel[selectedObjTypeIndex].value;
		if(type != ''){
			og.openLink(og.getUrl('object', 'get_co_types', {object_type: type}), {
				callback: function(success, data) {
					if (success) {
						og.coTypes = data.co_types;
						
						var controller = og.isMemberCustomProperties ? 'member_custom_properties' : 'property';
						og.openLink(og.getUrl(controller, 'get_custom_properties', {object_type: type}), {
							callback: function(success, data) {
								if (success) {
									for(var i=0; i < data.custom_properties.length; i++){
										var property = data.custom_properties[i];
										og.addCustomProperty(genid, property);
									}							
								}
							},
							scope: this
						});
					}
				},
				scope: this
			});
			Ext.getDom('CPactions' + genid).style.display='';
			document.getElementById('objectType').value = type;	
		}else{
			Ext.getDom('CPactions' + genid).style.display='none';
		}
	}
}

og.addCustomProperty = function(genid, property){  	  	
	var cpDiv = Ext.getDom(genid);
	var count = cpDiv.getElementsByTagName('table').length;
	var prop_is_new = (property==null);
	if (count % 2 == 0) {
		var classname = "";
	} else {
		var classname = "odd";
	}
	
	// custom properties type selector
	var cp_types = ['text', 'numeric', 'boolean', 'contact', 'user', 'date', 'list', 'memo', 'address'];
	
	var cp_types_html = '<select id="custom_properties[' + count + '][type]" name="custom_properties[' + count + '][type]" onchange="og.fieldTypeChanged(' + count + ')">';
	for (var i=0; i<cp_types.length; i++) {
		var t = cp_types[i];
		cp_types_html += '<option value="'+t+'"' + (!prop_is_new && property.type == t ? 'selected' : '') + '>' + lang(t) + '</option>';
	}
	cp_types_html += '</select>';
	
	
	var style = 'style="width:auto;padding-right:10px;"';
	var styleHidden = 'style="width:100px;padding-right:10px;display:none;"';
	
	var prop_order = count;
	if (!prop_is_new){
		prop_order = property.order;
	}
	
	var table = '<table style="min-width:950px"><tr>';
	
	if (property && property.is_special) {
		
		table += '<td style="display:none;">'+
		'<input id="custom_properties[' + count + '][id]" name="custom_properties[' + count + '][id]" type="hidden" value="{0}"/>' +
		'<input id="custom_properties[' + count + '][name]" name="custom_properties[' + count + '][name]" type="hidden" value="{1}"/>' +
		'<input id="custom_properties[' + count + '][deleted]" name="custom_properties[' + count + '][deleted]" type="hidden" value="0"/>' +
		'<input id="custom_properties[' + count + '][is_disabled]" name="custom_properties[' + count + '][is_disabled]" type="hidden" value="' + (property.is_disabled ? '1' : '0') + '"/>' +
		'<input id="custom_properties[' + count + '][is_special]" name="custom_properties[' + count + '][is_special]" type="hidden" value="' + (property.is_special ? '1' : '0') + '"/>' +
		'<input id="custom_properties[' + count + '][type]" name="custom_properties[' + count + '][type]" type="hidden" value="' + property.type + '"/>' +
		'<input id="custom_properties[' + count + '][description]" name="custom_properties[' + count + '][description]" type="hidden" value="' + property.description + '"/>' +
		'<input id="custom_properties[' + count + '][values]" name="custom_properties[' + count + '][values]" type="hidden" value="' + property.values + '"/>' +
		'<input id="custom_properties[' + count + '][default_value]" name="custom_properties[' + count + '][default_value]" type="hidden" value="' + property.default_value + '"/>' +
		'<input id="custom_properties[' + count + '][multiple_values]" name="custom_properties[' + count + '][multiple_values]" type="hidden" value="' + (property.multiple_values ? '1' : '0') + '"/>' +
		'<input id="custom_properties[' + count + '][required]" name="custom_properties[' + count + '][required]" type="hidden" value="' + (property.required ? '1' : '0') + '"/>' +
		'<input id="custom_properties[' + count + '][visible_by_default]" name="custom_properties[' + count + '][visible_by_default]" type="hidden" value="' + (property.visible_by_default ? '1' : '0') + '"/>' +
		'</td>' +
		
		'<td style="width:182px;padding-right:10px;"><b>' + lang('name') + '</b>:<br/>' + property.name + '</td>' +
		'<td style="min-width:107px;"><b>' + lang('type') + '</b>:<br/>' + lang(property.type) + '</td>' +
		
		'<td colspan="3" style="width:600px"></td>' +
		
		'<td><div id="up' + count + '" class="clico ico-up" onclick="og.swapVisual(' + count + ',\'1\')" style="' + (property.is_disabled ? 'display:none;' : '') + '"></div></td>' +
		'<td><div id="down' + count + '" class="clico ico-down" onclick="og.swapVisual(' + count + ',\'0\')" style="' + (property.is_disabled ? 'display:none;' : '') + '"></div></td>' +
		'<td><div id="delete' + count + '" class="clico ico-delete" onclick="og.disableSpecialCustomProperty(' + count + ',\'' + genid + '\')" style="' + (property.is_disabled ? 'display:none;' : '') + '"></div></td>' +
		'<td style="display:none;"><input id="CP' + count + '_order" name="custom_properties[' + count + '][order]" type="hidden" value="'+prop_order+'"/>' +
		'<tr id="trDelete' + count + '" style="' + (property.is_disabled ? '' : 'display:none;') + '"><td colspan="6"><b>' + lang('custom property is disabled') +
		'</b><a class="internalLink" href="javascript:og.undoDisableSpecialCustomProperty(' + count + ',\'' + genid + '\')">&nbsp;(' + lang('enable') + ')</a></td></tr>' +
  		'</tr></table>';
		 
	} else {
		
		table += '<td style="display:none;"><input id="custom_properties[' + count + '][id]" name="custom_properties[' + count + '][id]" type="hidden" value="{0}"/>' +
		'<input id="custom_properties[' + count + '][deleted]" name="custom_properties[' + count + '][deleted]" type="hidden" value="0"/></td>' +
  		'<td ' + style + '><b>' + lang('name') + '</b>:<br/><input type="text" id="custom_properties[' + count + '][name]" name="custom_properties[' + count + '][name]" value="{1}"/></td>' +
		'<td ' + style + '><b>' + lang('type') + '</b>:<br/>' + cp_types_html + '</td>' +
		'<td ' + (!prop_is_new && (property.type == 'list' || property.type == 'table') && property.values != null ? style : styleHidden) + ' id="tdValues' + count + '">' + 
		
		'<b><span id="tdValues' + count + '_label">' + (!prop_is_new && property.type == 'table' ? lang('columns comma separated') : lang('values comma separated')) + '</span>' + 
		'</b>:<br/><input type="text" onchange="javascript:og.fieldValueChanged()" id="custom_properties[' + count + '][values]" name="custom_properties[' + count + '][values]" value="{2}"/></td>' +
		'<td ' + style + '><b>' + lang('description') + 
		'</b>:<br/><input type="text" onchange="javascript:og.fieldValueChanged()" id="custom_properties[' + count + '][description]" name="custom_properties[' + count + '][description]" value="{3}"/></td>' +
		'</tr>' +
		
		'<tr><td ' + style + ' id="tdDefaultValueText' + count + '"><b>' + lang('default value') + 
		'</b>:<br/><input type="text" onchange="javascript:og.fieldValueChanged()" id="custom_properties[' + count + '][default_value]" name="custom_properties[' + count + '][default_value]" value="{4}"/></td>' +
		'<td ' + styleHidden + ' id="tdDefaultValueCheck' + count + '"><b>' + lang('default value') + 
		'</b>:<br/><input type="checkbox" class="checkbox" onchange="javascript:og.fieldValueChanged()" id="custom_properties[' + count + '][default_value_boolean]" name="custom_properties[' + count + '][default_value_boolean]" {4}/>&nbsp;' + lang('checked') + '</td>' +
		'<td ' + style + '><b>' + lang('required') + 
		'</b>:<br/><input class="checkbox" onchange="javascript:og.fieldValueChanged()" type="checkbox" id="custom_properties[' + count + '][required]" name="custom_properties[' + count + '][required]" {5}/></td>' +
		
		'<td ' + style + ' id="tdMultipleValues' + count + '"><b>' + lang('multiple values') + 
		'</b>:<br/><input class="checkbox" onchange="javascript:og.fieldValueChanged();og.checkMValChecked(' + count + ');" type="checkbox" id="custom_properties[' + count + '][multiple_values]" name="custom_properties[' + count + '][multiple_values]" {6}/></td>' +
		
		'<td ' + style + ' id="tdVisibleByDefault' + count + '"><b>' + lang('visible by default') + 
  		'</b>:<br/><input class="checkbox" onchange="javascript:og.fieldValueChanged()" type="checkbox" id="custom_properties[' + count + '][visible_by_default]" name="custom_properties[' + count + '][visible_by_default]" {7}/></td>' +
  			
		'<td style="width:250px;"></td>' +
  		'<td><div id="up' + count + '" class="clico ico-up" onclick="og.swapVisual(' + count + ',\'1\')"></div></td>' +
		'<td><div id="down' + count + '" class="clico ico-down" onclick="og.swapVisual(' + count + ',\'0\')"></div></td>' +
		'<td><div id="delete' + count + '" class="clico ico-delete" onclick="og.deleteCustomProperty(' + count + ',\'' + genid + '\')"></div></td>' +
		'<td style="display:none;"><input id="CP' + count + '_order" name="custom_properties[' + count + '][order]" type="hidden" value="'+prop_order+'"/>' +
		'<tr id="trDelete' + count + '" style="display:none;"><td colspan="6"><b>' + lang('custom property deleted') +
		'</b><a class="internalLink" href="javascript:og.undoDeleteCustomProperty(' + count + ',\'' + genid + '\')">&nbsp;(' + lang('undo') + ')</a></td></tr>' +
  		'</tr></table>';
		
	}
  	
  	// CO Types
  	if (og.coTypes && og.coTypes.length > 0) {
	  	table += '<div style="margin:4px 0 3px"><b>' + lang('applies to') + '</b>&nbsp;';
	  	var coTypeNames = '';
	  	for (i=0; i<og.coTypes.length; i++) {
	  		var value = 'false';
	  		if(!prop_is_new && property.co_types != '') {
	  			var splitted = property.co_types.split(',');
	  			for (k=0; k<splitted.length; k++) {
	  				if (splitted[k] == og.coTypes[i].id) {
	  					value = true;
	  					coTypeNames += (coTypeNames == '' ? '' : ', ') + og.coTypes[i].name;
	  					break;
	  				}
	  			}
	  		} else if (prop_is_new) { //by default all types are selected
	  			value = true;
	  			coTypeNames += (coTypeNames == '' ? '' : ', ') + og.coTypes[i].name;
	  		}
	  		var t = og.coTypes[i];
	  		table = table + '<input type="hidden" value="'+value+'" name="custom_properties[' + count + '][applyto]['+t.id+']" id="custom_properties[' + count + '][applyto]['+t.id+']">';
	  	}
	  	if (coTypeNames == '') coTypeNames = lang('none');
	  	table += '<span class="desc" id="custom_properties[' + count + '][applyto_names]">' + coTypeNames + '</span>';
	  	table += '<a class="ico-edit" style="padding:5px 0 0 16px;margin-left:20px;" href="#" onclick="og.showCoTypeSelector('+count+')">'+lang('edit')+'</a>';
	  	table += '</div>';
  	}
  	
  	if(!prop_is_new){
  	  	var defaultValue = (property.type != 'boolean' ? property.default_value : (property.default_value ? 'checked' : ''));
  		table = String.format(table, property.id, property.name, (property.values ? og.clean(property.values) : ''), property.description, defaultValue, property.required == true ? 'checked="checked"' : '', property.multiple_values == true ? 'checked="checked"' : '', property.visible_by_default == true ? 'checked="checked"' : '');
  	}else{
  		table = String.format(table, '', '', '', '', '', '', '');
  	}
	var cp = document.createElement('div');
	cp.id = "CP" + count;
	cp.style.padding = "5px";
	cp.className = classname;
	cp.innerHTML = table;
	cpDiv.appendChild(cp);
	if(prop_is_new) { 
  		cpModified = true;
	} else {
		if (property.is_disabled) {
			cp.style.background = "#F5E3C8";
		}
  		if(property.type == 'boolean'){
  			document.getElementById('tdDefaultValueCheck' + count).style.display = '';
  			document.getElementById('tdDefaultValueText' + count).style.display = 'none';
			document.getElementById('tdMultipleValues' + count).style.display = 'none';
  		} else if(property.type == 'table'){
  			var multipleValues = document.getElementById('custom_properties[' + count + '][multiple_values]');
  			multipleValues.checked = true;
  		}
	}
};

og.showCoTypeSelector = function(id) {
	var oldValues = new Array();
	for (i=0; i<og.coTypes.length; i++) {
		var el = document.getElementById('custom_properties[' + id + '][applyto][' + og.coTypes[i].id + ']');
		if (el) {
			oldValues.push({id:og.coTypes[i].id, val:el.value});
		}
	}
	
	var applyAction = function() {
		var str = '';
		for (i=0; i<og.coTypes.length; i++) {
			var el = document.getElementById('custom_properties[' + id + '][applyto][' + og.coTypes[i].id + ']');
			if (el && el.value == 'true') str += (str == '' ? '' : ', ') + og.coTypes[i].name;
		}
		var el = document.getElementById('custom_properties[' + id + '][applyto_names]');
		if (str == '') str = lang('none');
		if (el) el.innerHTML = str;
		og.ExtendedDialog.dialog.destroy();
	};
	
	var cancelAction = function() {
		for (i=0; i<og.coTypes.length; i++) {
			for (j=0; j<oldValues.length; j++) {
				if (og.coTypes[i].id == oldValues[j].id) {
					var el = document.getElementById('custom_properties[' + id + '][applyto][' + oldValues[j].id + ']');
					if (el) el.value = oldValues[j].val;
					break;
				}
			}
		}
		og.ExtendedDialog.dialog.destroy();
	};
	
	var allChecked = true;
	for (i=0; i<og.coTypes.length && allChecked; i++) {
		allChecked = document.getElementById('custom_properties[' + id + '][applyto][' + og.coTypes[i].id + ']').value == 'true';
	}
	var dlgItems = [{
		xtype :'checkbox',
		name :'co_type_all',
		id : 'all',
		boxLabel: lang('all'),
		hideLabel: true,
		checked: allChecked,
		handler: function(checkbox, checked) {
			for (i=0; i<og.coTypes.length; i++) {
				var cotype = og.coTypes[i];
				var el = Ext.getCmp('co_type_' + cotype.id);
				if (el) {
					el.setValue(checked);
					el.setDisabled(checked);
				}
				var domel = document.getElementById('custom_properties[' + id + '][applyto][' + cotype.id + ']');
				if (domel) domel.value = checked;
			}
		}
	}];
	for (i=0; i<og.coTypes.length; i++) {
		var cotype = og.coTypes[i];
		var item = {
			xtype :'checkbox',
			name : cotype.id,
			id : 'co_type_' + cotype.id,
			boxLabel: cotype.name,
			hideLabel: true,
			disabled: allChecked,
			checked: document.getElementById('custom_properties[' + id + '][applyto][' + cotype.id + ']').value == 'true',
			handler: function(checkbox, checked) {
				var el = document.getElementById('custom_properties[' + id + '][applyto][' + checkbox.getName() + ']');
				if (el) el.value = checked;
			}
		};
		dlgItems.push(item);
	}
	
	var config = {
		title: lang('select co types to apply'),
		y :50,
		id :'co_type_selector',
		modal :true,
		height : 110 + dlgItems.length * 26,
		width : 250,
		resizable :false,
		closeAction :'hide',
		closable: false,
		iconCls :'op-ico',
		border :false,
		buttons : [ {
			text :lang('ok'),
			handler :applyAction,
			id :'yes_button',
			scope :this
		}, {
			text :lang('cancel'),
			handler :cancelAction,
			id :'no_button',
			scope :this
		} ],
		dialogItems : dlgItems
	};
	og.ExtendedDialog.show(config);
}

og.checkMValChecked = function(id) {
	var fieldTypeSel = document.getElementById('custom_properties[' + id + '][type]');
	if(fieldTypeSel.selectedIndex != -1){
		var type = fieldTypeSel[fieldTypeSel.selectedIndex].value;
		if (type == 'table') {
			var multipleValues = document.getElementById('custom_properties[' + id + '][multiple_values]');
			multipleValues.checked = true;
		}
	}
}

og.fieldTypeChanged = function(id){
	var fieldTypeSel = document.getElementById('custom_properties[' + id + '][type]');
	if(fieldTypeSel.selectedIndex != -1){
		var type = fieldTypeSel[fieldTypeSel.selectedIndex].value;
		var valuesField = document.getElementById('tdValues' + id);
		var valuesLabel = document.getElementById('tdValues' + id + '_label');
		var defaultValueCheck = document.getElementById('tdDefaultValueCheck' + id);
		var defaultValueText = document.getElementById('tdDefaultValueText' + id);
		var tdMultipleValues = document.getElementById('tdMultipleValues' + id);
		var multipleValues = document.getElementById('custom_properties[' + id + '][multiple_values]');
		if(type == 'list' || type == 'table'){
			valuesField.style.display = '';
		}else{
			valuesField.style.display = 'none';
		}
		if(type == 'boolean'){
			defaultValueCheck.style.display = '';
			defaultValueText.style.display = 'none';
			tdMultipleValues.style.display = 'none';
			multipleValues.checked = false;
			
		}else{
			defaultValueCheck.style.display = 'none';
			defaultValueText.style.display = '';
			tdMultipleValues.style.display = '';
			if (type == 'table') {
				multipleValues.checked = true;
				valuesLabel.innerHTML = lang('columns comma separated');
			} else {
				valuesLabel.innerHTML = lang('values comma separated');
			}
		}
	}
	cpModified = true;
}

og.fieldValueChanged = function(){
	cpModified = true;
};

og.swapVisual = function(id, dir){
	var node = $("#CP"+id);
	var swap_node = node.next();
	dir_bool = parseInt(dir);
	if(dir_bool){		
		swap_node = node.prev();
		swap_node.insertAfter("#CP"+id);
	}else{
		swap_node.insertBefore("#CP"+id);
	}
	
	if (swap_node.length > 0){

		//update order
		$('#CP' + id + '_order').val(node.index());
		$('#' + swap_node.attr("id") + '_order').val(swap_node.index());
	
		//update styles
		node.toggleClass('odd ');
		swap_node.toggleClass('odd');
	}
};

og.disableSpecialCustomProperty = function(id, genid) {
	var del_attr = 'is_disabled'; 
	var cp = document.getElementById('CP' + id);
	cp.style.background = '#F5E3C8';
	document.getElementById('trDelete' + id).style.display = '';
	document.getElementById('custom_properties[' + id + ']['+ del_attr +']').value = 1;
	document.getElementById('down' + id).style.display = 'none';
	document.getElementById('up' + id).style.display = 'none';
	document.getElementById('delete' + id).style.display = 'none';
	cpModified = true;
}

og.deleteCustomProperty = function(id, genid, del_attr){
  	if(confirm(lang('delete custom property confirmation'))){
  		if (!del_attr) del_attr = 'deleted'; 
		var cp = document.getElementById('CP' + id);
		cp.style.background = '#FFDEAD';
		document.getElementById('trDelete' + id).style.display = '';
		document.getElementById('custom_properties[' + id + ']['+ del_attr +']').value = 1;
		document.getElementById('down' + id).style.display = 'none';
		document.getElementById('up' + id).style.display = 'none';
		document.getElementById('delete' + id).style.display = 'none';
		cpModified = true;
  	}
};

og.undoDisableSpecialCustomProperty = function(id, genid) {
	og.undoDeleteCustomProperty(id, genid, 'is_disabled');
}

og.undoDeleteCustomProperty = function(id, genid, del_attr){
	if (!del_attr) del_attr = 'deleted';
	document.getElementById('trDelete' + id).style.display = 'none';
	document.getElementById('custom_properties[' + id + ']['+ del_attr +']').value = 0;
	document.getElementById('down' + id).style.display = '';
	document.getElementById('up' + id).style.display = '';
	document.getElementById('delete' + id).style.display = '';
	var cpDiv = Ext.getDom(genid);
	for(var i=0; i < cpDiv.childNodes.length; i++){
		var nextCp = cpDiv.childNodes.item(i);
		if(nextCp.id == ('CP' + id)){
			nextCp.style.background = '';
			if (i % 2 == 0) {
	  			nextCp.className = "";
	  		} else {
	  			nextCp.className = "odd";
	  		}
	  		return;
		}
	}
};

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