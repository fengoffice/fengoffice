

og.removeCPValueGeneral = function(id, genid, index) {
	var base_id = genid + "listValues" + id;
	$("#" + base_id + " #value" + index).remove();
}


og.removeCPMemoValue = function(id, genid, index) {
	return og.removeCPValueGeneral(id, genid, index);
}

og.addCPMemoValue = function(id, genid, is_member_cp) {
	var field_name = is_member_cp ? 'member_custom_properties' : 'object_custom_properties';

	var base_id = genid + "listValues" + id;
	
	var count = $("#" + base_id + " .cp-val").length;

	$("#" + base_id + " .ico-add").remove();
	$("#" + base_id + " #value" + (count - 1)).append('<a href="#" class="link-ico ico-delete" onclick="og.removeCPMemoValue('+id+', \''+genid+'\','+(count-1)+',0)" ></a>');

	var html = '<div id="value' + count +'" class="cp-val">';
	html += '<textarea name="'+field_name+'[' + id + ']['+count+']" id="object_custom_properties[' + id + '][]" rows="5" cols="40"></textarea>';
	html += '<a href="#" class="link-ico ico-add" onclick="og.addCPMemoValue('+id+', \''+genid+'\', '+(is_member_cp ? '1':'0')+')">' + lang('add value') + '</a>';
	html += '</div>';

	$("#" + base_id).append(html);	
}


og.removeCPNumericValue = function(id, genid, index) {
	return og.removeCPValueGeneral(id, genid, index);
}

og.addCPNumericValue = function(id, genid, is_member_cp) {
	var field_name = is_member_cp ? 'member_custom_properties' : 'object_custom_properties';

	var base_id = genid + "listValues" + id;
	
	var count = $("#" + base_id + " .cp-val").length;

	$("#" + base_id + " .ico-add").remove();
	$("#" + base_id + " #value" + (count - 1)).append('<a href="#" class="link-ico ico-delete" onclick="og.removeCPNumericValue('+id+', \''+genid+'\','+(count-1)+',0)" ></a>');

	var html = '<div id="value' + count +'" class="cp-val">';
	html += '<input type="text" name="'+field_name+'[' + id + ']['+count+']" id="object_custom_properties[' + id + '][]" />';
	html += '<a href="#" class="link-ico ico-add" onclick="og.addCPNumericValue('+id+', \''+genid+'\', '+(is_member_cp ? '1':'0')+')">' + lang('add value') + '</a>';
	html += '</div>';

	$("#" + base_id).append(html);	
}


og.removeCPTextValue = function(id, genid, index) {
	return og.removeCPValueGeneral(id, genid, index);
}

og.addCPTextValue = function(id, genid, is_member_cp) {
	var field_name = is_member_cp ? 'member_custom_properties' : 'object_custom_properties';

	var base_id = genid + "listValues" + id;
	
	var count = $("#" + base_id + " .cp-val").length;

	$("#" + base_id + " .ico-add").remove();
	$("#" + base_id + " #value" + (count - 1)).append('<a href="#" class="link-ico ico-delete" onclick="og.removeCPTextValue('+id+', \''+genid+'\','+(count-1)+',0)" ></a>');

	var html = '<div id="value' + count +'" class="cp-val">';
	html += '<input type="text" name="'+field_name+'[' + id + ']['+count+']" id="object_custom_properties[' + id + '][]" />';
	html += '<a href="#" class="link-ico ico-add" onclick="og.addCPTextValue('+id+', \''+genid+'\', '+(is_member_cp ? '1':'0')+')">' + lang('add value') + '</a>';
	html += '</div>';

	$("#" + base_id).append(html);	
}

og.addCPValue = function(id, memo, is_member_cp){	
	var listDiv = document.getElementById('listValues' + id);
	var newValue = document.createElement('div');
	var count = listDiv.getElementsByTagName('div').length;
	newValue.id = 'value' + count;
	
	var field_name = is_member_cp ? 'member_custom_properties' : 'object_custom_properties';
	
	if(memo){
		newValue.innerHTML = '<textarea cols="40" rows="10" name="'+field_name+'[' + id + '][]" id="object_custom_properties[' + id + '][]"></textarea>' +
			'&nbsp;<a href="#" class="link-ico ico-add" onclick="og.addCPValue(' + id + ', true, '+(is_member_cp ? '1':'0')+')">' + lang('add value') + '</a><br/>';
	}else{
		newValue.innerHTML = '<input type="text" name="'+field_name+'[' + id + '][]" id="object_custom_properties[' + id + '][]" />' +
			'&nbsp;<a href="#" class="link-ico ico-add" onclick="og.addCPValue(' + id + ', false, '+(is_member_cp ? '1':'0')+')">' + lang('add value') + '</a><br/>';
	}
	
	listDiv.appendChild(newValue);
	var item = listDiv.childNodes.item(count - 1);
	var value = item.firstChild.value;
	if(memo){
		item.innerHTML = '<textarea cols="40" rows="10" name="'+field_name+'[' + id + '][]" id="object_custom_properties[' + id + '][]">' + value + '</textarea>' +
		'&nbsp;<a href="#" class="link-ico ico-delete" onclick="og.removeCPValue(' + id + ',' + (count - 1) + ', true, '+(is_member_cp ? '1':'0')+')" ></a>';
	}else{
		item.innerHTML = '<input type="text" name="'+field_name+'[' + id + '][]" id="object_custom_properties[' + id + '][]" value="' + value + '" />' +
			'&nbsp;<a href="#" class="link-ico ico-delete" onclick="og.removeCPValue(' + id + ',' + (count - 1) + ', false, '+(is_member_cp ? '1':'0')+')" ></a>';
	}
};
 
og.removeCPValue = function(id, pos, memo, is_member_cp){
	
	var field_name = is_member_cp ? 'member_custom_properties' : 'object_custom_properties';
	
	var listDiv = document.getElementById('listValues' + id);
	var item = listDiv.childNodes.item(pos);
	listDiv.removeChild(item);
	var value = '';
	var count = listDiv.getElementsByTagName('div').length;
	if(count == 1){
		item = listDiv.childNodes.item(0);
		value = item.firstChild.value;
		if(memo){
			item.innerHTML = '<textarea cols="40" rows="10" name="'+field_name+'[' + id + '][]" id="object_custom_properties[' + id + '][]">' + value + '</textarea>' +
				'&nbsp;<a href="#" class="link-ico ico-add" onclick="og.addCPValue(' + id + ', true, '+(is_member_cp ? '1':'0')+')">' + lang('add value') + '</a><br/>';
		}else{
			item.innerHTML = '<input type="text" name="'+field_name+'[' + id + '][]" id="object_custom_properties[' + id + '][]" value="' + value + '" />' +
				'&nbsp;<a href="#" class="link-ico ico-add" onclick="og.addCPValue(' + id + ', false, '+(is_member_cp ? '1':'0')+')">' + lang('add value') + '</a><br/>';
		}
	}else{
		for(i=0; i < listDiv.childNodes.length; i++){
			item = listDiv.childNodes.item(i);
			item.id = 'value' + i;
			value = item.firstChild.value;
			if(i < listDiv.childNodes.length - 1){
				if(memo){
					item.innerHTML = '<textarea cols="40" rows="10" name="'+field_name+'[' + id + '][]" id="object_custom_properties[' + id + '][]">' + value + '</textarea>' +
					'&nbsp;<a href="#" class="link-ico ico-delete" onclick="og.removeCPValue(' + id + ',' + i + ', true, '+(is_member_cp ? '1':'0')+')" ></a>';
				}else{
					item.innerHTML = '<input type="text" name="'+field_name+'[' + id + '][]" id="object_custom_properties[' + id + '][]" value="' + value + '" />' +
						'&nbsp;<a href="#" class="link-ico ico-delete" onclick="og.removeCPValue(' + id + ',' + i + ', false, '+(is_member_cp ? '1':'0')+')" ></a>';
				}
			}
			
		}
	}
};

og.addCPDateValue = function(genid, id, is_member_cp, use_time){
	var dateTable = document.getElementById('table' + genid + id);
	var tBody = dateTable.getElementsByTagName('tbody')[0];
	var dateCount = tBody.childNodes.length;
	if (typeof(og.tmp_date_cps_amount) != 'undefined') {
		dateCount = og.tmp_date_cps_amount[id];
		og.tmp_date_cps_amount[id]++;
	}
	
	var newTR = document.createElement('tr');
	var dateTD = document.createElement('td');
	var name = (is_member_cp ? 'member_custom_properties' : 'object_custom_properties') + '[' + id + '][]';
	dateTD.id = 'td' + genid + id + dateCount;
	var dateCond = new og.DateField({
		renderTo: dateTD,
		name: name,
		id: genid + name + dateCount
	});
	if (use_time) {
		var time_name = (is_member_cp ? 'member_custom_properties' : 'object_custom_properties') + '[time][' + id + ']['+dateCount+']';
		var dateCond = new Ext.form.TimeField({
			renderTo: dateTD,
			name: time_name,
			width: 60,
			id: genid + time_name + dateCount
		});
	}
	var deleteTD = document.createElement('td');
	deleteTD.innerHTML = '<a href="#" class="link-ico ico-delete" onclick="og.removeCPDateValue(\'' + genid + '\',' + id + ',' + dateCount + ', '+(is_member_cp ? '1':'0')+')"></a>';
	newTR.appendChild(dateTD);
	newTR.appendChild(deleteTD);
	tBody.appendChild(newTR);
	
};

og.removeCPDateValue = function(genid, id, pos, is_member_cp){
	var dateTable = document.getElementById('table' + genid + id);
	var tBody = dateTable.getElementsByTagName('tbody')[0];
	var item = tBody.childNodes.item(pos);
	tBody.removeChild(item);
	var newTBody = document.createElement('tbody');
	var name = (is_member_cp ? 'member_custom_properties' : 'object_custom_properties') + '[' + id + '][]';
	for(var i=0; i < tBody.childNodes.length; i++){
		dateTR = tBody.childNodes.item(i);
		var value = dateTR.firstChild.getElementsByTagName('input')[0].value;
		var newTR = document.createElement('tr');
		var dateTD = document.createElement('td');
		dateTD.id = 'td' + genid + id + i;
		dateTD.style.width = '150px'; 
		var dateCond = new og.DateField({
			renderTo: dateTD,
			name: name,
			id: genid + name + i,
			value: value
		});
		var deleteTD = document.createElement('td');
		deleteTD.innerHTML = '<a href="#" class="link-ico ico-delete" onclick="og.removeCPDateValue(\'' + genid + '\',' + id + ',' + i + ', '+(is_member_cp ? '1':'0')+')"></a>';
		newTR.appendChild(dateTD);
		newTR.appendChild(deleteTD);
		newTBody.appendChild(newTR);
	}
	dateTable.replaceChild(newTBody, tBody);
};


og.removeTableCustomPropertyRow = function(tr) {
	var parent = tr.parentNode;
	parent.removeChild(tr);
}


og.cp_list_remove = function(remove_link, genid, cp_id) {
	document.getElementById(genid+'_remove_cp_'+cp_id).value = 1;

	var inputs = remove_link.parentNode.getElementsByTagName('input');
	var input = inputs[0];
	var value = input.value;
	
	var tmp = [];
	for (var i=0; i<og.cp_list_selected_values[cp_id][genid].length; i++) {
		if (og.cp_list_selected_values[cp_id][genid][i] != value) {
			tmp.push(og.cp_list_selected_values[cp_id][genid][i]);
		}
	}
	og.cp_list_selected_values[cp_id][genid] = tmp;
	
	og.eventManager.fireEvent('after cp list change', [{
		cp_id: cp_id,
		genid: genid,
		values: og.cp_list_selected_values[cp_id][genid]
	}]);
	remove_link.parentNode.parentNode.removeChild(remove_link.parentNode);
	
	return false;
}

og.cp_list_selected = function(combo, genid, name, cp_id, is_multiple) {

	var rem = document.getElementById(genid+'_remove_cp_'+cp_id);
	if (rem) rem.value = 0;
	var i = og.cp_list_selected_index[cp_id][genid];
	var div = document.getElementById(genid + 'cp_list_selected' + cp_id);
	
	if (!og.cp_list_selected_values) og.cp_list_selected_values = [];
	if (!og.cp_list_selected_values[cp_id]) og.cp_list_selected_values[cp_id] = [];
	if (!og.cp_list_selected_values[cp_id][genid]) og.cp_list_selected_values[cp_id][genid] = [];
	
	var val = combo.options[combo.selectedIndex].value;
	if (val == '' || og.cp_list_selected_values[cp_id][genid].indexOf(val) >= 0) return;
	
	var html = '';
	if (is_multiple) {
		html = '<div>'+ og.clean(val) + '&nbsp;<a href=\"#\" onclick=\"og.cp_list_remove(this, \''+genid+'\', '+cp_id+');\" class=\"db-ico coViewAction ico-delete\">&nbsp;</a>';
	}
	html += '<input type=\"hidden\" name=\"'+name+'['+i+']\" value=\"'+val+'\" />';
	if (is_multiple) {
		html += '</div>';
	}
	
	if (is_multiple) {
		div.innerHTML += html;
		og.cp_list_selected_index[cp_id][genid] = i + 1;
		og.cp_list_selected_values[cp_id][genid].push(val);
	} else {
		div.innerHTML = html;
		og.cp_list_selected_values[cp_id][genid] = [val];
	}
	
	og.eventManager.fireEvent('after cp list change', [{
		cp_id: cp_id,
		genid: genid,
		values: og.cp_list_selected_values[cp_id][genid]
	}]);

}

og.check_if_valid_cp_num = function(inp) {
	if (isNaN(inp.value)) {
		inp.value='';
	}
}