og.eventManager.addListener('new email in conversation',
	function(mail) {

		function sendAnyway() {
			og.ExtendedDialog.dialog.destroy();
			var form = document.getElementById(mail.genid + 'form');
			form["mail[last_mail_in_conversation]"].value = mail.id;
			if (form) form.onsubmit();
		}

		function viewNewEmail() {
			og.ExtendedDialog.dialog.destroy();
			var form = document.getElementById(mail.genid + 'form');
			form["mail[last_mail_in_conversation]"].value = mail.id;
			og.openLink(og.getUrl('mail', 'view', {id: mail.id}), {caller: 'new'});
		}
	
		og.ExtendedDialog.show({
			title: lang('warning'),
			id: 'newEmailInConverstaion',
			modal: true,
			height: 150,
			width: 350,
			resizable: false,
			buttons: [{
				text: lang('send anyway'),
				handler: sendAnyway
			}, {
				text: lang('view new email'),
				handler: viewNewEmail
			}],
			dialogItems: [{
				xtype: 'label',
				hideLabel: true,
				style: 'font-size:100%;',
				text: lang('new email in conversation text')
			}]
		});
	}
);

og.mailSetBody = function(genid) {
	var form = Ext.getDom(genid + 'form');
	if (form.preventDoubleSubmit) return false;
	if (form['mail[autosave]'].value == 'false') {
		form.preventDoubleSubmit = true;
	}
	
	setTimeout(function() {
		form.preventDoubleSubmit = false;
	}, 2000);
	if (Ext.getDom(genid + 'format_html').checked){
		var editor = og.getCkEditorInstance(genid + 'ckeditor');
		form['mail[body]'].value = editor.getData();
	} else {
		form['mail[body]'].value = Ext.getDom(genid + 'mailBody').value;
	}
	return true;
};

og.mailAlertFormat = function(genid, opt) {
	var oEditor = og.getCkEditorInstance(genid + 'ckeditor');
	if (opt == 'plain') {
		Ext.MessageBox.confirm(lang('warning'), lang('switch format warn'), function(btn) {
			if (btn == 'yes') {
				var mailBody = Ext.getDom(genid + 'mailBody')
				mailBody.style.display = 'block';				
				Ext.getDom(genid + 'ck_editor').style.display= 'none';

				var sig = Ext.getDom(genid + 'signatures');

				var iText = oEditor.getData();
				 // remove line breaks
				iText = iText.replace(/[\n\r]/ig, "");
				// replace signature
				iText = iText.replace(/<div class="fengoffice_signature">.*?<\/div>/i, sig.actualTextSignature.replace(/\n/g, "<br />"));
				// convert html to text
				iText = og.htmlToText(iText);
				mailBody.value = iText;
				mailBody.oldMailBody = mailBody.value;
			} else {
				Ext.getDom(genid + 'format_html').checked = true;
				Ext.getDom(genid + 'format_plain').checked = false;
				Ext.getDom(genid + 'mailBody').style.display= 'none';
				Ext.getDom(genid + 'ck_editor').style.display= 'block';	
			}
		});	
	} else {
		var sig = Ext.getDom(genid + 'signatures');
		var mailBody = Ext.getDom(genid + 'mailBody');
		mailBody.style.display= 'none';
		Ext.getDom(genid + 'ck_editor').style.display = 'block';
		var html = mailBody.value;
		html = og.clean(html);
		html = html.replace('--\n' + og.htmlToText(sig.actualTextSignature.replace(/\n/g, "<br />")), '--<br />' + sig.actualHtmlSignature);
		html = html.replace(/\r\n/g, "<br />");
		html = html.replace(/\r|\n/g, "<br />");
		oEditor.setData(html);
		mailBody.oldMailBody = html;
	}
};

og.setHfValue = function(genid, id, val) {
	var hf = Ext.getDom(genid + id);
	if (hf) {
		old = hf.value;
		hf.value = val;
		return old;
	}
	return 'true';
};

og.setDiscard = function(genid, val){
	var the_id = Ext.getDom(genid + 'id').value;
	document.frmMail.action = og.getUrl('mail', 'discard', {id: the_id, ajax:'true'});
};

og.addContactsToAdd = function(genid) {
	var mail_contacts = Ext.get(genid + 'hf_mail_contacts').getValue();
	var addresses_str = Ext.get(genid + 'mailTo').getValue() + ',' + Ext.get(genid + 'mailCC').getValue() + ',' + Ext.get(genid + 'mailBCC').getValue();
	var addresses = addresses_str.split(',');
	var fieldset = Ext.get(genid + 'fieldset_add_contacts');
	var container = Ext.get(genid + 'add_contacts_container');
	container.remove();
	
	var label_empty = document.getElementById(genid + 'label_no_contacts');
	var old_style_display = label_empty.style.display;
	label_empty.style.display = 'none';
	
	fieldset.insertHtml('beforeEnd', '<div id="' + genid + 'add_contacts_container"></div>');
	var container = Ext.get(genid + 'add_contacts_container');
	
	var cant = 0;
	for (i=0; i < addresses.length; i++) {
		addr = addresses[i].trim();
		if (addr != '' && mail_contacts.indexOf(addr) == -1) {
			var url = og.getUrl('contact', 'add', {ce: addr, div_id: genid + 'new_contact_' + i, hf_contacts: genid + 'hf_mail_contacts'});
			container.insertHtml('beforeEnd', '<div id="' + genid + 'new_contact_' + i + '">' + addr + '&nbsp;<a class="coViewAction ico-add" href="javascript:og.openLink(\'' + url + '\', {caller:\'contact\'})" ></a></div>');
			cant++;
		}
	} 
	if (cant == 0) {
		label_empty.style.display = 'block';
	}
};

og.changeSignature = function(genid, acc_id) {
	setting_autoreply = genid.indexOf("autoreply")? true:false;
	if (setting_autoreply){
		genid = genid.replace(/autoreply/g,'');  
	}	
	var sig = Ext.getDom(genid + 'signatures');
	for (i=0; i < sig.accountSignatures.length; i++) {
		if (sig.accountSignatures[i].acc == acc_id) {
			var new_htmlsig = sig.accountSignatures[i].htmlsig;
			var new_textsig = sig.accountSignatures[i].textsig;
			break;
		}
	}
	if (setting_autoreply || Ext.getDom(genid + 'format_html').checked) {
		var iname = genid + 'ckeditor';
		var editor = og.getCkEditorInstance(iname);
		var html = editor.getData();
		
		var original_content = "";
		var pos = html.indexOf('<div id="original_mail">');
		if (pos > -1) {
			original_content = html.substring(pos);
		}
		
		html = html.replace(/\n/g, '');
		html = html.replace(/<div class="fengoffice_signature">.*<\/div>/i, new_htmlsig) + original_content;
		editor.setData(html);
		
	} else {
		if (Ext.getDom('mailBody').value.indexOf('--\n' + sig.actualTextSignature) != -1) {
			Ext.getDom('mailBody').value = Ext.getDom('mailBody').value.replace('--\n' + sig.actualTextSignature, '--\n' + new_textsig);
		} else {
			Ext.getDom('mailBody').value += '\n\n--\n' + new_textsig;
		}
	}
	sig.actualTextSignature = new_textsig;
	sig.actualHtmlSignature = new_htmlsig;
};


// <attachments>
og.addMailAttachment = function(container, obj) {
 	var objid = obj.manager + ":" + obj.object_id;
 	var count = container.getElementsByTagName('span').length; // there is one <span> per attachment
 	if (obj.name.length > 40) {
 		var objname = obj.name.substring(0, 40) + '&hellip;';
 	} else {
 		var objname = obj.name;
 	}
 	if (obj.manager == 'FwdMailAttach') {
 	 	var name = objname;
 	} else {
 		var name = '<a class="viewLink" href="javascript:og.openLink(og.getUrl(\'object\', \'view\', {id:' + obj.object_id + ', manager:\'' + obj.manager + '\'}), {caller: \'new tab\'})">' + objname + '</a>';
 	}
 	if (obj.manager == 'ProjectFiles') {
		name += '&nbsp;<a target="_download" class="link-ico ico-download" href="' + og.getUrl('files', 'download_file', {'id': obj.object_id, 'download': 'true'}) + '">&nbsp;</a>';
 	}
 	if (obj.manager == 'ProjectFiles' || obj.manager == 'MailContents') {
 	 	var id = Ext.id();
 		name += "<input id=\"check" + id + "\" type=\"checkbox\" checked=\"checked\"  style=\"margin-left: 30px; position: relative; top: 3px; width: 16px;\" name=\"attach_contents[" + count + "]\" />" +
		"<label for=\"check" + id + "\" style=\"display: inline; margin-left: 5px;\">" + lang("attach contents") + "</label>";
 	} else if (obj.manager == 'FwdMailAttach') {
 	 	name += "<input type=\"checkbox\" checked=\"checked\" style=\"margin-left: 30px; position: relative; top: 3px; width: 16px;\" disabled=\"disabled\" />" +
		"<label style=\"display: inline; margin-left: 5px;\">" + lang("attach contents") + "</label>";
 	}
	var html = 
		"<input type=\"hidden\" value=\"" + objid + "\" name=\"linked_objects[" + count + "]\"/>" +
		"<span class=\"name\">" +
		name +
		"</span>" +
		"<a class=\"removeDiv\" onclick=\"og.removeMailAttachment(this.parentNode)\" href=\"#\">" + lang('remove') + "</a>";
	var div = document.createElement('div');
	var icocls = obj.icocls ? obj.icocls : 'ico-file ' + (obj.mimeType ? "ico-" + obj.mimeType.replace(/\//g, "-").replace(/\./g, "_") : '');
	div.className = 'og-add-template-object ' + icocls;
	div.innerHTML = html;
	container.appendChild(div);
	container.style.borderBottom = '1px solid #ccc';
	if (container.offsetHeight >= 61) container.style.overflowY = 'scroll';
};

og.removeMailAttachment = function(attachment) {
 	var container = attachment.parentNode;
 	container.removeChild(attachment);
 	var div = container.firstChild;
 	var count = 0;
 	while (div) {
 	 	if (div.tagName == 'DIV') {
 	 		var inputs = div.getElementsByTagName('input');
 			for (var i=0; i < inputs.length; i++) {
 				if (inputs[i].name.substring(0, 14) == 'linked_objects') {
 					inputs[i].name = 'linked_objects[' + count + ']';
 				} else if (inputs[i].name.substring(0, 15) == 'attach_contents') {
 					inputs[i].name = 'attach_contents[' + count + ']';
 				}
 			}
 	 		count++;
 	 	}
 	 	div = div.nextSibling;
 	}
 	if (container.offsetHeight < 61) container.style.overflowY = 'hidden';
};

og.attachFromFileSystem = function(genid, account_member_id) {
 	var quickId = Ext.id();
 	og.openLink(og.getUrl('files', 'quick_add_files', { genid: quickId, composing_mail:1}), {
		preventPanelLoad: true,
		onSuccess: function(data) {
			og.ExtendedDialog.show({
        		html: data.current.data,
        		height: 300,
        		width: 600,
        		title: lang('upload file'),
        		ok_fn: function() {
    				og.doFileUpload(quickId, {
    					callback: function() {
    						var form = document.getElementById(quickId + 'quickaddfile');
    						
    						var mem_input = document.getElementById(quickId + 'member_ids');
    						mem_input.setAttribute("value", account_member_id);
    						
    						var input = document.getElementById(quickId + 'no_msg');
							input.setAttribute("value", "1");
    						og.ajaxSubmit(form, {
    							callback: function(success, data) {
    								if (success) {
    									var container = document.getElementById(genid + "attachments");
    									//if multiple suport
    									if (typeof data.files_data != "undefined") {
    										data.files_data.forEach(function(entry) {
	    										var obj = {object_id: entry.file_id, manager: 'ProjectFiles', name: entry.file_name, icocls: entry.icocls};
	    	    								og.addMailAttachment(container, obj);
	    									});
    									}else{ 
    										var obj = {object_id: data.file_id, manager: 'ProjectFiles', name: data.file_name, icocls: data.icocls};
    	    								og.addMailAttachment(container, obj);
    									}
    								}
    							}
    						});
    					}
					});
            		og.ExtendedDialog.hide();
    			}
        	});
        	return;
		}
	});
};

og.attachFromWorkspace = function(genid) {
	og.ObjectPicker.show(function (objs) {
		if (objs) {
			var container = document.getElementById(genid + 'attachments');
			for (var i=0; i < objs.length; i++) {
				var o = objs[i].data;
				var obj = {object_id: o.object_id, manager: 'ProjectFiles', name: o.name, icocls: o.ico, mimeType:o.mimeType};
				og.addMailAttachment(container, obj);
			}
		}
	}, this, 
	{
		selected_type:'file',
		types: ['file']
	});
};
og.getMailBodyFromUI = function(genid) {
	var format_html = Ext.getDom(genid + 'format_html');
	if (format_html && format_html.checked) {
		var editor = og.getCkEditorInstance(genid + 'ckeditor');
		return editor.getData();		
	} else {
		return Ext.getDom(genid + 'mailBody').value;
	}
};

og.checkMailBodyChanges = function(genid) {
	var mb = Ext.getDom(genid + 'mailBody');
	var new_body = og.getMailBodyFromUI(genid);	
	mb.thisDraftHasChanges = mb.oldMailBody != new_body;
	mb.oldMailBody = new_body;
};

og.autoSaveDraft = function(genid) {
	var mb = Ext.getDom(genid + 'mailBody');
	if(mb == null) return;
	
	var old_val = og.setHfValue(genid, 'isDraft', true);
	og.setHfValue(genid, 'autosave', true);

	if (mb.oldMailBody == null) mb.oldMailBody = og.getMailBodyFromUI(genid);
	// if html -> always check for changes, if plain -> only check when key is pressed

	var format_html = Ext.getDom(genid + 'format_html');
	if (format_html && format_html.checked) og.checkMailBodyChanges(genid);
		
	if (mb.thisDraftHasChanges) {
		mb.thisDraftHasChanges = false;
		var form = document.getElementById(genid + 'form');
		if (form) form.onsubmit();
	}
	og.setHfValue(genid, 'isDraft', old_val);
	og.setHfValue(genid, 'autosave', false);
	og.stopAutosave(genid);
	mb.autoSaveTOut = setTimeout(function() {
		og.autoSaveDraft(genid);
	}, og.preferences['draft_autosave_timeout'] * 1000);
};

og.stopAutosave = function(genid) {
	var mb = Ext.getDom(genid + 'mailBody');
	if (mb.autoSaveTOut) clearTimeout(mb.autoSaveTOut);
}

og.resetClassButton = function(genid) {
	elem = Ext.DomQuery.select("button.mail");
	for (var i = 0; i < elem.length; i++) {
		var t = Ext.get(elem[i].parentNode.parentNode.parentNode.parentNode.parentNode);
		t.removeClass("x-btn");
		t.removeClass("x-btn-text-icon");	
		t.addClass("custom-btn-wrapper");
	}
}
// </autosave>