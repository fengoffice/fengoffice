
og.addContactTypeChanged = function(type, genid){
	if(type == 0){
		//document.getElementById(genid + 'hfType').value = 0;
		document.getElementById(genid + 'non-registered-person-form').style.display = '';
		document.getElementById(genid + 'registered-person-form').style.display = 'none';
		//, 'onclick' => "$('.non-registered-add-person-form').slideToggle();$('#non-registered-add-person-form-show').show();"
	}else{
		//document.getElementById(genid + 'hfType').value = 1;
		document.getElementById(genid + 'non-registered-person-form').style.display = 'none';
		document.getElementById(genid + 'registered-person-form').style.display = '';
	}
}



og.markAsDeleted = function(del_el, container_id, input_id) {
	$('#'+input_id+'_deleted').val(1);
	$('#'+container_id).css('background-color', '#ECC');
	del_el.style.display = 'none';

	$('#'+container_id+' textarea').attr('disabled', 'disabled');
	$('#'+container_id+' input').attr('disabled', 'disabled');
	$('#'+container_id+' select').attr('disabled', 'disabled');

	$('#'+input_id+'_deleted').removeAttr('disabled');
	$('#'+input_id+'_id').removeAttr('disabled');

	$('#'+container_id+' .undo-delete').css('display', '');

	// when marking email field as deleted remove error msg and enable form if there was an error before
	$('#'+input_id+'_email_address').removeClass('field-error');
	$('#'+container_id+' .field-error-msg').html('');
	$('.submit').attr('disabled', false);
	$('.submit').removeClass('disabled');
}
og.undoMarkAsDeleted = function(undo_el, container_id, input_id) {
	$('#'+input_id+'_deleted').val(0);
	$('#'+container_id).css('background-color', '#fff');
	undo_el.style.display = 'none';

	$('#'+container_id+' textarea').removeAttr('disabled');
	$('#'+container_id+' input').removeAttr('disabled');
	$('#'+container_id+' select').removeAttr('disabled');

	$('#'+container_id+' .delete-link').css('display', '');
}

og.renderTelephoneTypeSelector = function(id, name, container_id, selected_value) {
	
	if (og.config.show_type_sel_on_phone_field) {
		var select = $('<select name="'+name+'" id="'+id+'" style="min-width:85px;max-width:100px;"></select>');
		for (var i=0; i<og.telephone_types.length; i++) {
			var type = og.telephone_types[i];
			var option = $('<option></option>');
			option.attr('value', type.id);
			if (selected_value == type.id) option.attr('selected', 'selected');
			option.text(type.name);
			select.append(option);
		}
		$('#'+container_id).empty().append(select);
	} else {
		$('#'+container_id).empty().append('<input type="hidden" name="'+name+'" id="'+id+'" value="'+selected_value+'" />');
	}
}

og.renderTelephoneInput = function(id, name, container_id, sel_type, sel_number, sel_name, sel_id) {
	if (!sel_number) sel_number = '';
	if (!sel_name) sel_name = '';
	if (!sel_id) sel_id = 0;

	$('#'+container_id).append('<input type="hidden" name="'+name+'[id]" id="'+id+'_id" value="'+sel_id+'" />');
	$('#'+container_id).append('<input type="hidden" name="'+name+'[deleted]" id="'+id+'_deleted" value="0" />');
	
	$('#'+container_id).append('<span id="'+id+'_type"></span>');
	og.renderTelephoneTypeSelector(id+'_type', name+'[type]', id+'_type', sel_type);

	if (!og.config.show_type_sel_on_phone_field) {
		$('#'+container_id).addClass('no-type-selector');
	}

	var number_input = $('<input name="'+name+'[number]" id="'+id+'_number" value="'+sel_number+'" placeholder="'+lang('phone number')+'" class="phone-number" />');
	$('#'+container_id).append(number_input);

	var name_input = $('<input name="'+name+'[name]" id="'+id+'_name" value="'+sel_name+'" placeholder="'+lang('name')+'" class="phone-name"/>');
	$('#'+container_id).append(name_input);

	var delete_or_undo = $(`<div class="removeUndo">
		<a href="#" tabindex="-1" onclick="og.markAsDeleted(this, \'${container_id}\', \'${id}\');" class="coViewAction ico-delete delete-link" title="${lang('delete')}"></a>
		<a href="#" tabindex="-1" onclick="og.undoMarkAsDeleted(this, \'${container_id}\', \'${id}\');" class="coViewAction ico-undo undo-delete" style="display:none;" title="${lang('undo')}"></a>
	</div>`);
	$('#' + container_id).append(delete_or_undo);
}

og.addNewTelephoneInput = function(container_id, pre_id, sel_type, sel_number, sel_name, sel_id) {
	if (!pre_id) pre_id = 'contact';
	if (!og.telephoneCount) og.telephoneCount = {};
	if (!og.telephoneCount[container_id]) og.telephoneCount[container_id] = 0;
	var tcount = og.telephoneCount[container_id];
	
	var id = pre_id+'Phone_' + tcount;
	var name = pre_id+'[phone][' + tcount + ']';

	$('#'+container_id).append('<div id="'+ container_id + tcount +'" class="phone-input-container"></div>');
	
	og.renderTelephoneInput(id, name, container_id + tcount, sel_type, sel_number, sel_name, sel_id);

	og.telephoneCount[container_id] = og.telephoneCount[container_id] + 1;
}


og.addNewAddressInput = function(container_id, pre_id, sel_type, sel_data, ignore_pre_id) {
	// use the default address type defined in the config option if we don't have any in the parameter
	if (typeof sel_type == 'undefined' || sel_type == '') {
		sel_type = og.config.default_type_address;
	}
	
	if (!pre_id) pre_id = 'contact';
	if (!og.addressCount) og.addressCount = {};
	if (!og.addressCount[container_id]) og.addressCount[container_id] = 0;
	var tcount = og.addressCount[container_id];
	
	// remove special characters from id
	var pre_name = pre_id;
	pre_id = pre_id.replace("[","_").replace("]","_");
	
	var id = pre_id + 'Address_' + tcount;
	var name = pre_name + '[address][' + tcount + ']';
	if (ignore_pre_id) {
		name = pre_name;
	}

	// the row is appended and rendered below, inside the visible form container when there is one,
	// so nothing is added here (an early append left an empty row on top of the first address)

	let visibleContainers = $('.contact_form_container').filter(function () {
		return $(this).css('display') === 'block';
	});

	if (visibleContainers.length > 0) {
		visibleContainers.each(function () {
			let childContainer = $(this).find('#' + container_id);
			if (childContainer.length === 0) {
				return;
			}

			tcount = og.addressCount[container_id] = og.addressCount[container_id] + 1;
			var id = pre_id + 'Address_' + tcount;
			if (!ignore_pre_id) {
				name = pre_name + '[address][' + tcount + ']';
			}
			childContainer.append('<div id="' + container_id + tcount + '" class="address-input-container"></div>');
			og.renderAddressInput(id, name, container_id + tcount, sel_type, sel_data);

			if (og.income) {
				let dataBillingAttr = $(childContainer).parent().siblings(".addNewLineButton").children('a').attr('data-defaultBilling');
				if (dataBillingAttr != undefined && Number(dataBillingAttr) == 1) {
					og.income.onAppendDefaultBilling(id, name, container_id + tcount, 'address', sel_data, false);
				}
			}
		});
	} else {

		$('#' + container_id).append('<div id="' + container_id + tcount + '" class="address-input-container"></div>');
		og.renderAddressInput(id, name, container_id + tcount, sel_type, sel_data);

		if (og.income) {
			let dataBillingAttr = $('#' + container_id).parent().siblings(".addNewLineButton").children('a').attr('data-defaultBilling');
			if (dataBillingAttr != undefined && Number(dataBillingAttr) == 1) {
				og.income.onAppendDefaultBilling(id, name, container_id + tcount, 'address', sel_data, false);
			}
		}

		og.addressCount[container_id] = og.addressCount[container_id] + 1;
	}

	let othersAddress = document.querySelectorAll('.moreAddressInputs');
	othersAddress.forEach((element) => {
		og.checkAddress('#' + element.getAttribute('id'), '', '', 'contact');
	});
	
	//$(".address-input-container").css('max-width', ($('#'+container_id).width()-270)+'px');
}



og.renderWebpageTypeSelector = function(id, name, container_id, selected_value) {
	
	if (og.config.show_type_sel_on_website_field) {
		var select = $('<select name="'+name+'" id="'+id+'" style="min-width:85px;max-width:100px;"></select>');
		for (var i=0; i<og.webpage_types.length; i++) {
			var type = og.webpage_types[i];
			var option = $('<option></option>');
			option.attr('value', type.id);
			if (selected_value == type.id) option.attr('selected', 'selected');
			option.text(type.name);
			select.append(option);
		}
		$('#'+container_id).empty().append(select);
	} else {
		$('#'+container_id).empty().append('<input type="hidden" name="'+name+'" id="'+id+'" value="'+selected_value+'" />');
	}
}

og.renderWebpageInput = function(id, name, container_id, sel_type, sel_url, sel_id) {
	if (!sel_url) sel_url = '';
	if (!sel_id) sel_id = 0;

	$('#'+container_id).append('<input type="hidden" name="'+name+'[id]" id="'+id+'_id" value="'+sel_id+'" />');
	$('#'+container_id).append('<input type="hidden" name="'+name+'[deleted]" id="'+id+'_deleted" value="0" />');
	
	$('#' + container_id).append('<div class="webpage-type"><span id="' + id +'_type"></span></div>');
	og.renderWebpageTypeSelector(id+'_type', name+'[type]', id+'_type', sel_type);

	if (!og.config.show_type_sel_on_website_field) {
		$('#'+container_id).addClass('no-type-selector');
	}

	var webpage_input = $('<input name="'+name+'[url]" id="'+id+'_url" value="'+sel_url+'" placeholder="'+lang('webpage')+'"/>');
	$('#'+container_id).append(webpage_input);

	var delete_or_undo = $(`<div class="removeUndo">
		<a href="#" tabindex="-1" onclick="og.markAsDeleted(this, \'${container_id}\', \'${id}\');" class="coViewAction ico-delete delete-link" title="${lang('delete')}"></a>
		<a href="#" tabindex="-1" onclick="og.undoMarkAsDeleted(this, \'${container_id}\', \'${id}\');" class="coViewAction ico-undo undo-delete" style="display:none;" title="${lang('undo')}"></a>
	</div>`);
	$('#' + container_id).append(delete_or_undo);

}

og.addNewWebpageInput = function(container_id, pre_id, sel_type, sel_url, sel_id) {
	if (!pre_id) pre_id = 'contact';
	if (!og.webpageCount) og.webpageCount = {};
	if (!og.webpageCount[container_id]) og.webpageCount[container_id] = 0;
	var tcount = og.webpageCount[container_id];
	
	var id = pre_id+'Webpage_' + tcount;
	var name = pre_id + '[webpage][' + tcount + ']';

	$('#'+container_id).append('<div id="'+ container_id + tcount +'" class="webpage-input-container"></div>');
	
	og.renderWebpageInput(id, name, container_id + tcount, sel_type, sel_url, sel_id);

	og.webpageCount[container_id] = og.webpageCount[container_id] + 1;
}



og.renderEmailTypeSelector = function(id, name, container_id, selected_value) {
	
	if (og.config.show_type_sel_on_email_field) {
		var select = $('<select name="'+name+'" id="'+id+'" style="min-width:85px;max-width:100px;"></select>');
		for (var i=0; i<og.email_types.length; i++) {
			var type = og.email_types[i];
			var option = $('<option></option>');
			option.attr('value', type.id);
			if (selected_value == type.id) option.attr('selected', 'selected');
			option.text(type.name);
			select.append(option);
		}
		$('#'+container_id).empty().append(select);
	} else {
		$('#'+container_id).empty().append('<input type="hidden" name="'+name+'" id="'+id+'" value="'+selected_value+'" />').hide();
	}
}

og.renderEmailInput = function(id, name, container_id, sel_type, sel_address, sel_id, defaultEmail=0) {

	if (!sel_address) sel_address = '';
	if (!sel_id) sel_id = 0;

	$('#'+container_id).append('<input type="hidden" name="'+name+'[id]" id="'+id+'_id" value="'+sel_id+'" />');
	$('#'+container_id).append('<input type="hidden" name="'+name+'[deleted]" id="'+id+'_deleted" value="0" />');
	
	$('#' + container_id).append('<span id="' + id +'_type"></span>');
	og.renderEmailTypeSelector(id+'_type', name+'[type]', id+'_type', sel_type);

	if (!og.config.show_type_sel_on_email_field) {
		$('#'+container_id).addClass('no-type-selector');
	}

	var email_input = $('<input name="' + name + '[email_address]" id="' + id + '_email_address" value="' + sel_address + '" class="moreEmailInputs" placeholder="' + lang('email address') +'"/>');
	$('#'+container_id).append(email_input);
	
	var undo_or_remove = $(`<div class="removeUndo">
		<a href="#" tabindex="-1" onclick="og.markAsDeleted(this, \'${container_id}\', \'${id}\');" class="coViewAction ico-delete delete-link" title="${lang('delete')}"></a>
		<a href="#" tabindex="-1" onclick="og.undoMarkAsDeleted(this, \'${container_id}\', \'${id}\');" class="coViewAction ico-undo undo-delete" style="display:none;" title="${lang('undo')}"></a>
	</div>`);
	$('#' + container_id).append(undo_or_remove);

}

og.addNewEmailInput = function (container_id, pre_id, sel_type, sel_address, sel_id, default_email_value = 0) {
	if (!pre_id) pre_id = 'contact';
	if (!og.emailCount) og.emailCount = {};
	if (!og.emailCount[container_id]) og.emailCount[container_id] = 0;
	var tcount = og.emailCount[container_id];

	let visibleContainers = $('.contact_form_container').filter(function () {
		return $(this).css('display') === 'block';
	});

	if (visibleContainers.length > 0) {
		visibleContainers.each(function () {
			let childContainer = $(this).find('#' + container_id);
			if (childContainer.length === 0) {
				return;
			}

			tcount = og.emailCount[container_id] = og.emailCount[container_id] + 1;
			var id = pre_id + 'Email_' + tcount;
			var name = pre_id + '[emails][' + tcount + ']';

			childContainer.append('<div id="' + container_id + tcount + '" class="email-input-container"></div>');
			og.renderEmailInput(id, name, container_id + tcount, sel_type, sel_address, sel_id, default_email_value);

			if (og.income) {
				let dataBillingAttr = $(childContainer).parent().siblings(".addNewLineButton").children('a').attr('data-defaultBilling');
				if (dataBillingAttr != undefined && Number(dataBillingAttr) == 1) {
					og.income.onAppendDefaultBilling(id, name, container_id + tcount, 'email', default_email_value, false);
				}
			}
		});
	}else{
		var id = pre_id + 'Email_' + tcount;
		var name = pre_id + '[emails][' + tcount + ']';

		$('#' + container_id).append('<div id="' + container_id + tcount + '" class="email-input-container"></div>');
		og.renderEmailInput(id, name, container_id + tcount, sel_type, sel_address, sel_id, default_email_value);
		
		if (og.income) {
			let dataBillingAttr = $('#' + container_id).parent().siblings(".addNewLineButton").children('a').attr('data-defaultBilling');
			if (dataBillingAttr != undefined && Number(dataBillingAttr) == 1) {
				og.income.onAppendDefaultBilling(id, name, container_id + tcount, 'email', default_email_value, false);
			};
		}

		og.emailCount[container_id] = og.emailCount[container_id] + 1;
	}

	let contact_type = pre_id;
	let othersEmail = document.querySelectorAll('.moreEmailInputs');
	othersEmail.forEach((element) => {
		og.checkEmailAddress('#' + element.getAttribute('id'), '', '', contact_type);
	});
}