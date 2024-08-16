og.reloadCompanies = function ( context, genid ){
	Ext.Ajax.request({
		url: og.getUrl('contact', 'list_companies', { 'ajax':true, 'context': Ext.util.JSON.encode(context) }),
		success:  function(result, request) {
			var jsonData = Ext.util.JSON.decode(result.responseText);
			var companies = jsonData.companies ;
			
			var combo = document.getElementById(genid+"profileFormCompany");
			firstOption = combo.options[0];
			combo.innerHTML = '';
			
			combo.appendChild(firstOption);
			for (var i = 0 ; i < companies.length ; i++ ) {
				var option = document.createElement('option') ;
				option.innerHTML = companies[i].name ;
				option.value = companies[i].value ;
				combo.appendChild(option);
			}
			
			
		}
	});
}


og.addNewCompany = function(genid){

	var name_el = document.getElementById(genid + 'profileFormNewCompanyName');
	if (name_el) name_el.value = '';
	
	var el0 = document.getElementById(genid + 'new_company');
	var show = el0 && el0.style.display == 'none';
	
	var el1 = document.getElementById(genid + 'new_company');
	if (el1) el1.style.display = show ? 'block':'none';
	
	var el2 = document.getElementById(genid + 'existing_company');
	if (el2) el2.style.display = show ? 'none': 'block';
	
	var el3 = document.getElementById(genid + 'hfIsNewCompany');
	if (el3) el3.value = show;
	
	var el4 = document.getElementById(genid + 'duplicateCompanyName');
	if (el4) el4.style.display = 'none';
	
	if (name_el && show) name_el.focus();
	
	var el5 = Ext.get(genid + 'submit1');
	if (el5) el5.dom.disabled = false;
	
	var el6 = Ext.get(genid + 'submit2');
	if (el6) el6.dom.disabled = false;

};

og.checkNewCompanyName = function(genid) {
	var fff = document.getElementById(genid + 'profileFormNewCompanyName');
	var name = fff.value.toUpperCase();
	document.getElementById(genid + 'duplicateCompanyName').style.display = 'none';
	document.getElementById(genid + 'duplicateCompanyName').innerHTML = '';
	
	var select = document.getElementById(genid + 'profileFormCompany');
	for (var i = 1; i < select.options.length; i++){
		if (select.options[i].text.toUpperCase() == name){
			document.getElementById(genid + 'duplicateCompanyName').innerHTML = lang('duplicate company name', select.options[i].text, genid, i);
			document.getElementById(genid + 'companyInfo').style.display="none";
			document.getElementById(genid + 'duplicateCompanyName').style.display = 'block';
			Ext.get(genid + 'submit1').dom.disabled = true;
			Ext.get(genid + 'submit2').dom.disabled = true;
			document.getElementById(genid + 'duplicateCompanyName').focus();
			return;
		}
	}		
	Ext.get(genid + 'submit1').dom.disabled = false;
	Ext.get(genid + 'submit2').dom.disabled = false;
	document.getElementById(genid + 'companyInfo').style.display="block";
		
};

og.selectCompany = function(genid, index) {
	var select = document.getElementById(genid + 'profileFormCompany');
	select.selectedIndex = index;
	og.addNewCompany(genid);
	og.companySelectedIndexChanged(genid);
};

og.companySelectedIndexChanged = function(genid,data_js){
	select = document.getElementById(genid + 'profileFormCompany');
	Ext.get(genid + 'submit1').dom.disabled = true;
	Ext.get(genid + 'submit2').dom.disabled = true;
	
    og.openLink(og.getUrl('contact','get_company_data', {id: select.options[select.selectedIndex].value}), {
    	caller:this,
    	callback: function(success, data) {
    		if (success) {
				Ext.get(genid + 'submit1').dom.disabled = false;
				Ext.get(genid + 'submit2').dom.disabled = false;
				/*
				@TODO: fill work phone, work addres, etc with company data
    			if (data.id > 0){
	    			document.getElementById(genid + 'profileFormWAddress').value = data_js['adress'] ? data_js['adress'] :  data.address;
	    			document.getElementById(genid + 'profileFormWCity').value = data_js['city'] ? data_js['city'] : data.city;
	    			document.getElementById(genid + 'profileFormWState').value = data_js['state'] ? data_js['state'] : data.state;
					var list = document.getElementById(genid + 'profileFormWCountry');
					for (var i = 0; i < list.options.length; i++)
						if (list.options[i].value == data.country){
							list.selectedIndex = i;
							break;
						}
	    			document.getElementById(genid + 'profileFormWZipcode').value = data_js['zipCode'] ? data_js['zipCode'] : data.zipcode;
	    			document.getElementById(genid + 'profileFormWWebPage').value = data_js['web'] ? data_js['web'] : data.webpage;
	    			document.getElementById(genid + 'profileFormWPhoneNumber').value = data_js['phone'] ? data_js['phone'] : data.phoneNumber;
	    			document.getElementById(genid + 'profileFormWFaxNumber').value = data_js['fax'] ? data_js['fax'] : data.faxNumber;
	    			
	    		}else{
	    			var text = "";
	    			document.getElementById(genid + 'profileFormWAddress').value = data_js['adress'] ? data_js['adress'] :  text;
	    			document.getElementById(genid + 'profileFormWCity').value = data_js['city'] ? data_js['city'] : text;
	    			document.getElementById(genid + 'profileFormWState').value = data_js['state'] ? data_js['state'] : text;
	    			document.getElementById(genid + 'profileFormWZipcode').value = data_js['zipCode'] ? data_js['zipCode'] : text;
	    			document.getElementById(genid + 'profileFormWWebPage').value = data_js['web'] ? data_js['web'] : text;
	    			document.getElementById(genid + 'profileFormWPhoneNumber').value = data_js['phone'] ? data_js['phone'] : text;
	    			document.getElementById(genid + 'profileFormWFaxNumber').value = data_js['fax'] ? data_js['fax'] : text;
	    		}
	    		*/
    		}
    	}
    });
}

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
	
	var select = $('<select name="'+name+'" id="'+id+'" style="min-width:60px;max-width:100px;"></select>');
	for (var i=0; i<og.telephone_types.length; i++) {
		var type = og.telephone_types[i];
		var option = $('<option></option>');
		option.attr('value', type.id);
		if (selected_value == type.id) option.attr('selected', 'selected');
		option.text(type.name);
		select.append(option);
	}
	$('#'+container_id).empty().append(select);
}

og.renderTelephoneInput = function(id, name, container_id, sel_type, sel_number, sel_name, sel_id) {
	if (!sel_number) sel_number = '';
	if (!sel_name) sel_name = '';
	if (!sel_id) sel_id = 0;

	$('#'+container_id).append('<input type="hidden" name="'+name+'[id]" id="'+id+'_id" value="'+sel_id+'" />');
	$('#'+container_id).append('<input type="hidden" name="'+name+'[deleted]" id="'+id+'_deleted" value="0" />');
	
	$('#'+container_id).append('<span id="'+id+'_type"></span>');
	og.renderTelephoneTypeSelector(id+'_type', name+'[type]', id+'_type', sel_type);

	var number_input = $('<input name="'+name+'[number]" id="'+id+'_number" value="'+sel_number+'" placeholder="'+lang('phone number')+'" style="width:140px;" />');
	$('#'+container_id).append(number_input);

	var name_input = $('<input name="'+name+'[name]" id="'+id+'_name" value="'+sel_name+'" placeholder="'+lang('name')+'"/>');
	$('#'+container_id).append(name_input);

	var delete_or_undo = $(`<div class="removeUndo">
		<a href="#" onclick="og.markAsDeleted(this, \'${container_id}\', \'${id}\');" class="coViewAction ico-delete delete-link" title="${lang('delete')}"></a>
		<a href="#" onclick="og.undoMarkAsDeleted(this, \'${container_id}\', \'${id}\');" class="coViewAction ico-undo undo-delete" style="display:none;" title="${lang('undo')}"></a>
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
	if (!ignore_pre_id) {
		var name = pre_name + '[address][' + tcount + ']';
	} else {
		var name = pre_name;
	}

	$('#'+container_id).append('<div id="'+ container_id + tcount +'" class="address-input-container"></div>');
	
	og.renderAddressInput(id, name, container_id + tcount, sel_type, sel_data);

	// DEFAULT BILLING ADDRESS VERIFICATION ::
	let dataBillingAttr = $('#' + container_id).parent().siblings(".addNewLineButton").children('a').attr('data-defaultBilling');
	if (dataBillingAttr != undefined && Number(dataBillingAttr) == 1) {
		og.income.onAppendDefaultBilling(id, name, container_id + tcount, 'address', sel_data, false);
	};

	$(".address-input-container").css('max-width', ($('#'+container_id).width()-270)+'px');
	
	og.addressCount[container_id] = og.addressCount[container_id] + 1;
}


og.renderWebpageTypeSelector = function(id, name, container_id, selected_value) {
	
	var select = $('<select name="'+name+'" id="'+id+'" style="min-width:60px;max-width:100px;"></select>');
	for (var i=0; i<og.webpage_types.length; i++) {
		var type = og.webpage_types[i];
		var option = $('<option></option>');
		option.attr('value', type.id);
		if (selected_value == type.id) option.attr('selected', 'selected');
		option.text(type.name);
		select.append(option);
	}
	$('#'+container_id).empty().append(select);
}

og.renderWebpageInput = function(id, name, container_id, sel_type, sel_url, sel_id) {
	if (!sel_url) sel_url = '';
	if (!sel_id) sel_id = 0;

	$('#'+container_id).append('<input type="hidden" name="'+name+'[id]" id="'+id+'_id" value="'+sel_id+'" />');
	$('#'+container_id).append('<input type="hidden" name="'+name+'[deleted]" id="'+id+'_deleted" value="0" />');
	
	$('#' + container_id).append('<td><span id="' + id +'_type"></span></td>');
	og.renderWebpageTypeSelector(id+'_type', name+'[type]', id+'_type', sel_type);

	var webpage_input = $('<td><input name="'+name+'[url]" id="'+id+'_url" value="'+sel_url+'" placeholder="'+lang('webpage')+'"/></td>');
	$('#'+container_id).append(webpage_input);

	var delete_or_undo = $(`<td class="removeUndo">
		<a href="#" onclick="og.markAsDeleted(this, \'${container_id}\', \'${id}\');" class="coViewAction ico-delete delete-link" title="${lang('delete')}"></a>
		<a href="#" onclick="og.undoMarkAsDeleted(this, \'${container_id}\', \'${id}\');" class="coViewAction ico-undo undo-delete" style="display:none;" title="${lang('undo')}"></a>
	</td>`);
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
	
	var select = $('<select name="'+name+'" id="'+id+'" style="min-width:60px;max-width:100px;"></select>');
	for (var i=0; i<og.email_types.length; i++) {
		var type = og.email_types[i];
		var option = $('<option></option>');
		option.attr('value', type.id);
		if (selected_value == type.id) option.attr('selected', 'selected');
		option.text(type.name);
		select.append(option);
	}
	$('#'+container_id).empty().append(select);
}

og.renderEmailInput = function(id, name, container_id, sel_type, sel_address, sel_id, defaultEmail=0) {

	if (!sel_address) sel_address = '';
	if (!sel_id) sel_id = 0;

	$('#'+container_id).append('<input type="hidden" name="'+name+'[id]" id="'+id+'_id" value="'+sel_id+'" />');
	$('#'+container_id).append('<input type="hidden" name="'+name+'[deleted]" id="'+id+'_deleted" value="0" />');
	
	$('#' + container_id).append('<span id="' + id +'_type"></span>');
	og.renderEmailTypeSelector(id+'_type', name+'[type]', id+'_type', sel_type);

	var email_input = $('<input name="' + name + '[email_address]" id="' + id + '_email_address" value="' + sel_address + '" class="moreEmailInputs" placeholder="' + lang('email address') +'"/>');
	$('#'+container_id).append(email_input);
	
	var undo_or_remove = $(`<div class="removeUndo">
		<a href="#" onclick="og.markAsDeleted(this, \'${container_id}\', \'${id}\');" class="coViewAction ico-delete delete-link" title="${lang('delete')}"></a>
		<a href="#" onclick="og.undoMarkAsDeleted(this, \'${container_id}\', \'${id}\');" class="coViewAction ico-undo undo-delete" style="display:none;" title="${lang('undo')}"></a>
	</div>`);
	$('#' + container_id).append(undo_or_remove);

}

og.addNewEmailInput = function(container_id, pre_id, sel_type, sel_address, sel_id, default_email_value=0) {

	if (!pre_id) pre_id = 'contact';
	if (!og.emailCount) og.emailCount = {};
	if (!og.emailCount[container_id]) og.emailCount[container_id] = 0;
	var tcount = og.emailCount[container_id];
	
	var id = pre_id+'Email_' + tcount;
	var name = pre_id + '[emails][' + tcount + ']';

	$('#'+container_id).append('<div id="'+ container_id + tcount +'" class="email-input-container"></div>');
	
	og.renderEmailInput(id, name, container_id + tcount, sel_type, sel_address, sel_id, default_email_value);
	
	// DEFAULT BILLING EMAIL VERIFICATION ::
	let dataBillingAttr = $('#' + container_id).parent().siblings(".addNewLineButton").children('a').attr('data-defaultBilling');
	if (dataBillingAttr != undefined && Number(dataBillingAttr) == 1) {
		og.income.onAppendDefaultBilling(id, name, container_id + tcount, 'email', default_email_value, false);
	};

	og.emailCount[container_id] = og.emailCount[container_id] + 1;

	let othersEmail = document.querySelectorAll('.moreEmailInputs');
	othersEmail.forEach((element) => {
		og.checkEmailAddress('#' + element.getAttribute('id'), '', '', 'contact');
	});
}