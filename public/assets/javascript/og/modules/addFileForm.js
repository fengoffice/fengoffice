App.modules.addFileForm = {

	/**
	 * Change state on the upload file click
	 * 
	 * @param void
	 * @return null
	 */
	updateFileClick: function(genid) {
		if (Ext.getDom(genid + 'fileFormUpdateFile').checked) {
			Ext.getDom(genid + 'updateFileDescription').style.display = 'none';
			Ext.getDom(genid + 'updateFileForm').style.display = 'block';
		} else {
			Ext.getDom(genid + 'updateFileDescription').style.display = 'block';
			Ext.getDom(genid + 'updateFileForm').style.display = 'none';
		} // if
	},

	/**
	 * Change state on file change checkbox click
	 * 
	 * @param void
	 * @return null
	 */
	versionFileChangeClick: function(genid) {
		if (Ext.getDom(genid + 'fileFormVersionChange').checked) {
			var display_value = 'block';
		} else {
			var display_value = 'none';
		} // if
		Ext.getDom(genid + 'fileFormRevisionCommentBlock').style.display = display_value;
	}
};

og.fileValidateAttempt = false;
og.checkFileNameResult = 0;

og.fileCheckSubmit = function(genid) {
	if (og.fileValidateAttempt) {
		og.fileCheckInterval = setInterval(function() {
			if (og.checkFileNameResult != 0) {
				clearInterval(og.fileCheckInterval);
				if (og.checkFileNameResult == 2) {
					og.fileSubmitMe(genid);
				}
			}
		}, 100);
		return false;
	} else {
		return og.fileSubmitMe(genid);
	}
}

og.fileSubmitMe = function(genid) {
	var form = document.getElementById(genid + 'addfile');
	if (form.submitted) return true;
	var type = document.getElementById(genid + 'hfType').value;
	var newRevision = (!Ext.get(genid + "fileFormUpdateFile") || Ext.getDom(genid + "fileFormUpdateFile").checked);
	if (newRevision){
		var comment = document.getElementById(genid + 'fileFormRevisionComment').value;
		comment = comment.replace(/^\s*/, "").replace(/\s*$/, ""); //Trims the input string
		var commentRequired = document.getElementById(genid + 'RevisionCommentsRequired').value;
		if (comment == '' && commentRequired == "1") {
			og.err(lang('file revision comments required'));
			return false;
		}
	}
	if (type == '1') {
		form.submitted = true;
		form.onsubmit();
	} else {
		og.doFileUpload(genid, {
			callback: function() {
				var form = document.getElementById(genid + 'addfile');
				form.submitted = true;
				form.onsubmit();
				form.submitted = false;
			}
		});
	}
	return false;
}

og.doFileUpload = function(genid, config) {
	var fileInput = document.getElementById(genid + 'fileFormFile');
	var fileParent = fileInput.parentNode;
	fileParent.removeChild(fileInput);
	fileParent.innerHTML = '&nbsp;';
	var form = document.createElement('form');
	form.method = 'post';
	form.enctype = 'multipart/form-data';
	form.encoding = 'multipart/form-data';
	form.action = og.getUrl('files', 'temp_file_upload', {'id': genid});
	form.style.display = 'none';
	form.appendChild(fileInput);
	document.body.appendChild(form);

	og.submit(form, {
		callback: function() {
			form.removeChild(fileInput);
			fileParent.innerHTML = '';
			fileParent.appendChild(fileInput);
			document.body.removeChild(form);
			if (typeof config.callback == 'function') {
				config.callback.call(config.scope);
			}
		}
	});
}

//*************************************************
//   Filename Checking
//*************************************************

og.checkFileName = function(genid) {
	og.fileValidateAttempt = true;
	og.checkFileNameResult = 0;
	setTimeout(function(){
		var name_el = document.getElementById(genid + 'fileFormFilename');
		var name = "";
		if (name_el) name = name_el.value;
		
		var btn = Ext.get(genid + 'add_file_submit2');
		Ext.get(genid + "addFileFilenameCheck").setDisplayed(true);
		Ext.get(genid + "addFileFilenameExists").setDisplayed(false);
	    
		var eid = 0;
		var hfIsNew = Ext.get(genid + "hfFileIsNew");
		var fileIsNew = true;
		if (hfIsNew) fileIsNew = hfIsNew.getValue();
		
	  	if (!fileIsNew){
	  		var hfFileId = Ext.get(genid + 'hfFileId');
	 		if (hfFileId) eid = hfFileId.getValue();
	  	}
		
		var members_el = member_selector[genid] ? Ext.fly(Ext.get(genid + member_selector[genid].hiddenFieldName)) : null;

		og.openLink(og.getUrl('files', 'check_filename', {
			id: eid
		}), {
			post: {
				members: (members_el ? members_el.getValue() : ""),
				filename: name
			},
			caller: this,
			callback: function(success, data) {
				og.checkFileNameCallback(success, data, genid);
				//og.resize_modal_form();
			}
		});
	}, 100);
}

og.checkFileNameCallback = function(success, data, genid){
	if (success) {
  		Ext.get(genid + "addFileFilenameCheck").setDisplayed(false);
		Ext.get(genid + "addFileFilename").setDisplayed('inline');

		var isNew = Ext.get(genid + "hfFileIsNew").dom.value;
  		og.fileValidateAttempt = false;
		if (data.files && isNew){
			og.checkFileNameResult = 1;
			og.showFileExists(genid, data);
		} else {
			og.checkFileNameResult = 2;
		}
  	} else {
  		og.fileValidateAttempt = false;
  		og.checkFileNameResult = 1;
  	}
}
  
og.showFileExists = function(genid, fileInfo){
 	Ext.get(genid + "addFileFilenameExists").setDisplayed(true);
 	var table = document.getElementById(genid + 'upload-table');
 	while(table.rows.length>0) 
    	table.deleteRow(table.rows.length-1);
 	
 	for (var i = 0; i < fileInfo.files.length; i++)
 		og.addFileOption(table, fileInfo.files[i], genid);
}

og.addFileOption = function(table, file, genid){
	var row = table.insertRow(table.rows.length);
	var cell = row.insertCell(0);
	cell.style.paddingRight='4px';

	if (file.can_edit && (!file.is_checked_out || file.can_check_in)){
	
		if (Ext.isIE) {
			var el = document.createElement('<input type="radio" name="file[upload_option]">');
		} else {
			var el = document.createElement('input');
			el.type = "radio";
			el.name = 'file[upload_option]';
		}
		el.id = file.id + "chk" + genid;
		el.className = "checkbox";
		el.value = file.id;
		el.enabled = file.can_edit && (!file.is_checked_out || (file.is_checked_out && file.can_check_in));
		cell.appendChild(el);
	}
	
	var cell = row.insertCell(1);
	cell.style.height = '20px';
	var div = document.createElement('div');
	div.className = 'ico-link ico-' + file.type;
	
	var addMessage = lang('add as new revision to') + ":&nbsp;";
	if(file.is_checked_out){
		if (file.can_check_in)
			addMessage = lang('check in') + ":&nbsp;";
		else
			addMessage = lang('cannot check in') + "&nbsp;";
	}
		
	var classes = "db-ico ico-unknown ico-" + file.type;
	if (file.type) {
		var path = file.type.replace(/\//ig, "-").split("-");
		var acc = "";
		for (var i=0; i < path.length; i++) {
			acc += path[i];
			classes += " ico-" + acc;
			acc += "-";
		}
	}
	var fileLink = "<a style='padding-left:18px;line-height:16px' class=\""+ classes + "\" href=\"" + og.getUrl('files','download_file',{id : file.id}) + "\" title=\"" + lang('download') + "\">" + og.clean(file.name) + "</a>";
	var workspaces = '';
	
	
	div.innerHTML = addMessage + fileLink + workspaces;
	cell.appendChild(div);
	
	var cell = row.insertCell(2);
	cell.style.paddingLeft = '10px';
	var div = document.createElement('div');
	var dateToShow = '';
	var newDate = new Date(file.created_on * 1000);
	var currDate = new Date();
	if (newDate.getFullYear() != currDate.getFullYear()) {
		dateToShow = newDate.format("j M Y");
	} else {
		dateToShow = newDate.format("j M");
	}
	cell.innerHTML = lang("created by on", file.created_by_name, dateToShow);
	
	var cell = row.insertCell(3);
	cell.style.paddingLeft = '10px';
	if (file.is_checked_out){
		cell.innerHTML = lang('checked out by', file.checked_out_by_name); 
	}
}


og.updateFileName = function(genid, name) {
	var new_rev_el = document.getElementById(genid + 'new_rev_file_id');
	if (new_rev_el && new_rev_el.value > 0) {
		// if is new revision of an existing document return
		return;
	}
	
	var start = Math.max(0, Math.max(name.lastIndexOf('/'), name.lastIndexOf('\\')) + 1);
	name = name.substring(start);
	document.getElementById(genid + 'fileFormFilename').value = name;
	if (document.getElementById(genid + 'fileRadio').checked)
		og.checkFileName(genid);
	else
		document.getElementById(genid + 'addFileFilename').style.display = 'inline';
	
}


//*************************************************
// Add document Filename Checking
//*************************************************

og.showAddDocumentDialog = function(genid){
	var form = Ext.getDom(genid + 'form');
	var commentsRequired = (Ext.getDom(genid + "commentsRequired").value == 1) && (!form.autosave || form.autosave.value == 0);
	var config = {};
	
	config.ok_fn = function(){
		var editor = og.getCkEditorInstance(genid + 'ckeditor');
		if (editor) form['fileContent'].value = editor.getData();
		else form['fileContent'].value = "";
		
		//form['fileContent'].value = document.getElementById('cke_'+genid+'ckeditor').getData();
		if (Ext.getCmp(genid + 'title')){
			var filename = Ext.getCmp(genid + 'title').getValue();
			if (filename.length < 5 || filename.substring(filename.length - 5) != '.html')
				filename += '.html';
			form['file[name]'].value = filename;
		}
		if (Ext.getCmp(genid + 'comment'))
			form['file[comment]'].value = Ext.getCmp(genid + 'comment').getValue();
		if (Ext.getCmp(genid + 'new_file')){
			if (Ext.getCmp(genid + 'new_file').getValue())
				form['file[id]'].value = '';
		}
		if (form['file[name]'].value != '' && (!commentsRequired || form['file[comment]'].value != '')){
			og.ExtendedDialog.hide();
			form.ready = true;
			form.onsubmit();
			form.new_revision_document.value = "";
		}
		if (commentsRequired && form['file[comment]'].value == '') {
			Ext.Msg.show({
			   	title: lang('error'),
			   	msg: lang('file revision comments required'),
	   			icon: Ext.MessageBox.ERROR 
	   		});
		}
	};
	
	if (!commentsRequired && !form.rename && form['file[id]'].value) {
		// if comments are not required and not renaming doc and not a new doc, just save
		config.ok_fn();
		return;
	}
	
	config.genid = genid;
	config.title = lang('save');
	config.height = 180;

	config.dialogItems = [];
	if (form.rename || !form['file[id]'].value){
		config.dialogItems.push({xtype: 'textfield',
			listeners:{
			specialkey:function(elem,evnt){
				if(evnt.getKey()== 13){
					config.ok_fn();
				}
			},
			scope: this
		},
		name: 'title', value: form['file[name]'].value, id: genid + 'title', fieldLabel: lang('choose a filename'), allowBlank:false, blankText: lang('this field is required')});
		config.height += 40;
	}
	config.dialogItems.push({xtype: 'textarea', height:80, width:250, name: 'comment', id: genid + 'comment', fieldLabel: lang('comment'), allowBlank: (commentsRequired?'false':'true'), blankText: lang('this field is required')});
	if (form['file[id]'].value && form.rename){
		config.height += 40;
		config.dialogItems.push({
			xtype: 'checkbox',
			checked: false,
			name: 'new_file', 
			id: genid + 'new_file', 
			fieldLabel: lang('save as a new document')
		});
	}

	og.ExtendedDialog.show(config);
	setTimeout(function() {
		btn = Ext.getCmp(genid + 'title');
		if (btn != null) btn.focus();
	}, 100);
}

og.addDocumentSubmit = function(genid){
	var form = Ext.getDom(genid + 'form');
	if (form.ready){
		form.ready = false;
		return true;
	}
	
	var commentsRequired = (Ext.getDom(genid + "commentsRequired").value == 1) && (!form.autosave || form.autosave.value == 0);
	if (commentsRequired) {
		og.showAddDocumentDialog(genid);
		return false;
	} else {
		
		var editor = og.getCkEditorInstance(genid + 'ckeditor');
		if (editor) form['fileContent'].value = editor.getData();
		else form['fileContent'].value = "";
		
		return true;
	}
}


og.addDocumentTypeChanged = function(type, genid){
	if(type == 0){
		document.getElementById(genid + 'hfType').value = 0;
		document.getElementById(genid + 'fileUploadDiv').style.display = '';
		document.getElementById(genid + 'weblinkDiv').style.display = 'none';
		var comments_box = document.getElementById(genid + 'addFileRevisionComments');
		if (comments_box) comments_box.style.display = '';
	}else{
		document.getElementById(genid + 'hfType').value = 1;
		document.getElementById(genid + 'fileUploadDiv').style.display = 'none';
		document.getElementById(genid + 'weblinkDiv').style.display = '';
		var comments_box = document.getElementById(genid + 'addFileRevisionComments');
		if (comments_box) comments_box.style.display = 'none';
	}
}
