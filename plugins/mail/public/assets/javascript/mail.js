
og.eventManager.addListener('remove from email list', function (data) {
	if (data && data.ids && data.ids.length > 0) {
		if (data.remove_later) {
			og.mail.markMailsToRemove(data.ids);
		} else {
			og.mail.removeMailsFromList(data.ids);
		}
	}
});
og.eventManager.addListener('update email list', function (data) {
	if (data && data.ids && data.ids.length > 0) {
		og.mail.updateMailsList();
	}
});

og.mail = {};

og.mail.signature_div_attributes = 'class="fengoffice_signature" contenteditable="false"';

og.mail.removeMailsFromList = function(ids) {
	var man = Ext.getCmp("mails-manager");
	var processed = 0;
	var rows_to_remove = [];
	
	if (typeof(ids)=='object' && ids.length>0 && man && man.store && man.store.data) {
		for (var i=0; i<man.store.data.items.length; i++) {
			var row = man.store.data.items[i];
			if (ids.indexOf(row.id) != -1 || ids.indexOf(row.id+"") != -1) {
				rows_to_remove.push(row);
				processed++;
				if (processed >= ids.length) {
					break;
				}
			}
		}
	}
	for (var k=0; k<rows_to_remove.length; k++) {
		var r = rows_to_remove[k];
		man.store.remove(r);
	}
}

og.mail.updateMailsList = function() {
	var man = Ext.getCmp("mails-manager");
	if (man) {
		if (og.viewing_mail) og.viewing_mail = false;
		man.load();
	}
}

og.mail.markMailsToRemove = function(ids) {
	if (!og.mail.mails_to_remove_from_list) og.mail.mails_to_remove_from_list = [];
	for (var i=0; i<ids.length; i++) {
		og.mail.mails_to_remove_from_list.push(ids[i]);
	}
}

og.mail.removePendingMailsFromList = function() {
	if (og.mail.mails_to_remove_from_list) {
		og.mail.removeMailsFromList(og.mail.mails_to_remove_from_list);
	}
}
