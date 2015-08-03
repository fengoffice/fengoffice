og.addObjectSubtype = function(genid, id, name, manager, readonly) {
	var table = document.getElementById(genid + manager + '_table');
	table.style.display = '';
	var idx = table.rows.length;
	var row = table.insertRow(idx);
	idx--;
	var rcls = (idx % 2 == 0 ? '' : 'altRow');
	row.className = rcls;
	var readonly_str = readonly ? 'readonly="readonly" style="background-color:transparent;border:0px none;"' : '';
	var c0 = row.insertCell(0);
	c0.innerHTML = '<input name="subtypes['+manager+']['+idx+'][id]" type="hidden" value="'+id+'"/>'+
			'<input name="subtypes['+manager+']['+idx+'][deleted]" type="hidden" value="0" id="'+genid + manager + '['+idx+']deleted"/>'+
			'<input name="subtypes['+manager+']['+idx+'][name]" type="text" value="'+name+'" id="'+genid + manager + '['+idx+']name" '+readonly_str+'/>';
	var c1 = row.insertCell(1);
	c1.innerHTML = '<div id="'+genid + manager + '['+idx+']options">' +
			'<a href="#" class="link-ico ico-edit" onclick="og.editObjectSubtypeName(\''+genid+'\', '+idx+', \''+manager +'\');" title="'+lang('edit')+'"></a>' +
			'<a href="#" class="link-ico ico-delete" onclick="og.deleteObjectSubtype(\''+genid+'\', '+idx+', \''+manager +'\');" title="'+lang('delete')+'"></a></div>';
	var name_field = document.getElementById(genid + manager + '['+idx+'][name]')
	if (name_field) name_field.focus();
}

og.editObjectSubtypeName = function(genid, idx, manager) {
	var input = document.getElementById(genid + manager + '['+idx+']name');
	if (input) {
		input.style.backgroundColor = 'white';
		input.style.border = '1px solid #CCC';
		input.readOnly = '';
		input.focus();
	}
}

og.deleteObjectSubtype = function(genid, idx, manager) {
	Ext.Msg.confirm(lang('delete object subtype'), lang('delete object subtype warning'), function(btn){
		if (btn == 'yes'){
			var options_div = document.getElementById(genid + manager + '['+idx+']options');
			options_div.style.display = 'none';
			options_div.parentNode.style.backgroundColor = '#FFDEAD';
			document.getElementById(genid + manager + '['+idx+']deleted').value = 1;
			var del_div = document.getElementById(genid + manager + '['+idx+']deleted_msg');
			if (!del_div)
				options_div.parentNode.innerHTML += '<div id="'+genid + manager + '['+idx+']deleted_msg">'+lang('object subtype deleted', '<a href="#" onclick="og.undoDeleteObjectSubtype(\''+genid+'\', '+idx+', \''+manager +'\')">('+lang('undo')+')</a>')+'</div>';
			else del_div.style.display = '';
		}
	});
}

og.undoDeleteObjectSubtype = function(genid, idx, manager) {
	var options_div = document.getElementById(genid + manager + '['+idx+']options');
	options_div.style.display = '';
	options_div.parentNode.style.backgroundColor = idx % 2 == 0 ? 'white' : '#F4F8F9';
	document.getElementById(genid + manager + '['+idx+']deleted').value = 0;
	var del_div = document.getElementById(genid + manager + '['+idx+']deleted_msg');
	if (del_div) del_div.style.display = 'none';
}
