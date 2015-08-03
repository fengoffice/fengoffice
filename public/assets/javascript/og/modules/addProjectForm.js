App.modules.addProjectForm = {
  formActionClick: function() {
    Ext.getDom('projectFormActionSelectMessage').disabled = !Ext.getDom('projectFormActionAddComment').checked;
    Ext.getDom('projectFormActionSelectTaskList').disabled = !Ext.getDom('projectFormActionAddTask').checked;
  }
};

og.sme = [];

og.sme.addTosme = function(genid, contact_id){
	if (Ext.getDom(genid + 'contacts' + contact_id)){
		alert(lang('contact is already assigned'));
		return;
	}

	Ext.Msg.prompt(lang('role'), lang('role for this contact') + ':',
		function(btn, text) {
			if (btn == 'ok') {
				var table = Ext.getDom(genid + 'contactsTable');
				var position = table.rows.length;

				var row = table.insertRow(position);
				row.id = genid + 'contacts' + contact_id;
				
				if (position % 2 == 1)
					row.className = 'altrow';
				
				var cell = row.insertCell(0);
				cell.className = 'contact_name';
				cell.id = genid + 'contacts_name_cell' + contact_id;
				cell.innerHTML = Ext.getDom(genid + 'name_cell' + contact_id).innerHTML;
				
				cell = row.insertCell(1);
				cell.innerHTML = Ext.getDom(genid + 'info_cell' + contact_id).innerHTML;

				cell = row.insertCell(2);
				cell.innerHTML = text;
				cell.innerHTML = '<input type="hidden" name="project[contacts][' + contact_id + '][role]" value="' + text + '"/>' + text;
				
				cell = row.insertCell(3);
				cell.className = 'actions';
				cell.innerHTML = '<input type="hidden" name="project[contacts][' + contact_id + '][contact_id]" value="' + contact_id + '"/><a class="internalLink coViewAction ico-delete" href="#" onclick="og.sme.removeFromsme(\'' + genid + '\', \'' + contact_id + '\');return false;" style="line-height:18px" title="' + lang('remove') + '">' + lang('remove') + '</a>';

				Ext.getDom(genid + 'noContacts').style.display = 'none';
			}
		}, null, false, ''
	);
}

og.sme.removeFromsme = function(genid, contact_id){
	var table = Ext.getDom(genid + 'contactsTable');
	var row = Ext.getDom(genid + 'contacts' + contact_id);
	if (table && row && confirm(lang('confirm remove contact',Ext.getDom(genid + 'contacts_name_cell' + contact_id).innerHTML)))  
		table.deleteRow(row.sectionRowIndex);
}

og.sme.search = function(){
	var genid = og.sme.genid;
	var controller = og.sme.controller;
	var action = og.sme.action;
	var sf = Ext.getDom(genid + 'searchField');
	if (sf){
		if (sf.value && sf.value.length > 1){
			Ext.getDom(genid + 'searching').style.display = 'block';
			var post = [];
			post['search_for'] = '*' + sf.value + '*';
			og.openLink(og.getUrl(controller, action), {
				method: 'POST',
				post: post,
				callback: function(success, data) {
					Ext.getDom(genid + 'searching').style.display = 'none';
					if (success && ! data.errorCode) {
						if (data.results){
							Ext.getDom(genid + 'body').innerHTML = '<table id="' + genid + 'resultsTable" class="results"><tr><td colspan=3></td></tr></table>';
							for (var i = 0; i < data.results.length; i++){
								og.sme.drawLine(genid,data.results[i]);
							}
						} else {
							Ext.getDom(genid + 'body').innerHTML = lang('no results found');
						}
					} else {
						Ext.getDom(genid + 'body').innerHTML = lang('error', data.errorMessage);
					}
				},
				scope: this
			});
			
		} else
			Ext.getDom(genid + 'body').innerHTML = '';
	}
}



og.sme.drawLine = function(genid, data){
	var table = Ext.getDom(genid + 'resultsTable');
	var position = table.rows.length;
	
	var row = table.insertRow(position);
	row.id = genid + 'searchRow' + data.id;
	row.height = '20px';
	
	if (position % 2 == 1)
		row.style.backgroundColor = '#F0F6FF';
	
	var cell = row.insertCell(0);
	cell.id = genid + 'name_cell' + data.id;
	cell.className = 'contact_name';
	var textNode = document.createTextNode(data.name);
	cell.appendChild(textNode);
	
	cell = row.insertCell(1);
	cell.id = genid + 'info_cell' + data.id;
	var text = data.jobtitle;
	if (text != '')
		text += ', ';
	text += data.company.name;
	textNode = document.createTextNode(text);
	cell.appendChild(textNode);
	
	cell = row.insertCell(2);
	cell.className = 'actions';
	cell.innerHTML = '<a class="internalLink coViewAction ico-add" href="#" onclick="javascript:og.sme.addTosme(\'' + genid + '\', \'' + data.id + '\');return false;" style="line-height:18px" title="' + lang('add') + '">' + lang('add') + '</a>';
}

og.sme.searchTO = function(e, genid, controller, action){
	if (og.sme.timeout) clearTimeout(og.sme.timeout);
	if (!e) var e = window.event;
	
	og.sme.genid = genid;
	og.sme.controller = controller;
	og.sme.action = action;

	var val = (e.which) ? e.which : e.keyCode;
	if (val == 13) {
		e.cancelBubble = true;
        e.returnValue = false;
        e.cancel = true;
        if (e.stopPropagation) e.stopPropagation();
        if (e.preventDefault) e.preventDefault();
		og.sme.search();
		return false;
	} else {
		og.sme.timeout = setTimeout(og.sme.search,400);
		return true;
	}
}