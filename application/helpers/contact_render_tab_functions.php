<?php

function get_contact_data_tab_html($genid, $contact, $renderContext, $contact_data, $renderAddCompany = true){
	ob_start();
	render_contact_data_tab($genid, $contact, $renderContext, $contact_data, $renderAddCompany);
	return ob_get_clean();
}

function get_company_data_tab_html($genid, $company, $renderContext, $company_data){
	ob_start();
	render_company_data_tab($genid, $company, $renderContext, $company_data);
	return ob_get_clean();
}

function render_contact_data_tab($genid, $contact, $renderContext, $contact_data, $renderAddCompany = true){
	$object = $contact;
	
	if ($contact instanceof Contact && !$contact->isNew()) {
		$contact_data = array(
			'first_name' => $contact->getFirstName(),
			'surname' => $contact->getSurname(),
			'username' => $contact->getUsername(),
			'department' => $contact->getDepartment(),
			'job_title' => $contact->getJobTitle(),
			'email' => $contact->getEmailAddress(),
			'birthday'=> $contact->getBirthday(),
			'comments' => $contact->getCommentsField(),
			'picture_file' => $contact->getPictureFile(),
			'timezone' => $contact->getTimezone(),
			'company_id' => $contact->getCompanyId(),
		); 
		 
		$all_phones = ContactTelephones::findAll(array('conditions' => 'contact_id = '.$contact->getId()));
		$contact_data['all_phones'] = $all_phones;
		$all_addresses = ContactAddresses::findAll(array('conditions' => 'contact_id = '.$contact->getId()));
		$contact_data['all_addresses'] = $all_addresses;
		$all_webpages = ContactWebpages::findAll(array('conditions' => 'contact_id = '.$contact->getId()));
		$contact_data['all_webpages'] = $all_webpages;
		$all_emails = $contact->getNonMainEmails();
		$contact_data['all_emails'] = $all_emails;
	}
	
	// telephone types
	$all_telephone_types = TelephoneTypes::getAllTelephoneTypesInfo();
	tpl_assign('all_telephone_types', $all_telephone_types);
	include get_template_path("tabs/contact_data","contact");
}

function render_company_data_tab($genid, $company, $renderContext, $company_data){	
	$object = $company;
	
	if ($company instanceof Contact && !$company->isNew()) {
		$address = $company->getAddress('work');
		$street = "";
		$city = "";
		$state = "";
		$zipcode = "";
		if($address){
			$street = $address->getStreet();
			$city = $address->getCity();
			$state = $address->getState();
			$zipcode = $address->getZipCode();
			$country = $address->getCountry();
		}
			
		$company_data = array(
			'first_name' => $company->getFirstName(),
			'timezone' => $company->getTimezone(),
			'email' => $company->getEmailAddress(),
			'comments' => $company->getCommentsField(),
		); // array
			
		// telephone types
		$all_telephone_types = TelephoneTypes::getAllTelephoneTypesInfo();
		tpl_assign('all_telephone_types', $all_telephone_types);
		// address types
		$all_address_types = AddressTypes::getAllAddressTypesInfo();
		tpl_assign('all_address_types', $all_address_types);
		// webpage types
		$all_webpage_types = WebpageTypes::getAllWebpageTypesInfo();
		tpl_assign('all_webpage_types', $all_webpage_types);
		// email types
		$all_email_types = EmailTypes::getAllEmailTypesInfo();
		tpl_assign('all_email_types', $all_email_types);
			
		$all_phones = ContactTelephones::findAll(array('conditions' => 'contact_id = '.$company->getId()));
		$company_data['all_phones'] = $all_phones;
		$all_addresses = ContactAddresses::findAll(array('conditions' => 'contact_id = '.$company->getId()));
		$company_data['all_addresses'] = $all_addresses;
		$all_webpages = ContactWebpages::findAll(array('conditions' => 'contact_id = '.$company->getId()));
		$company_data['all_webpages'] = $all_webpages;
		$all_emails = $company->getNonMainEmails();
		$company_data['all_emails'] = $all_emails;
	}
	
	// telephone types
	$all_telephone_types = TelephoneTypes::getAllTelephoneTypesInfo();
	
	// address types
	$all_address_types = AddressTypes::getAllAddressTypesInfo();
	
	// webpage types
	$all_webpage_types = WebpageTypes::getAllWebpageTypesInfo();
	
	// email types
	$all_email_types = EmailTypes::getAllEmailTypesInfo();
		
	include get_template_path("tabs/company_data","contact");
}