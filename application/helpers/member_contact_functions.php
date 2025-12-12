<?php

/**
 * Saves associated contact data for a member.
 *
 * This function processes contact or company information for a given member,
 * based on the provided parameters. It either updates existing contact data 
 * or creates new contact or company records. It handles email, address, 
 * and other contact details, and associates the contact with the member.
 *
 * @param array $params An associative array containing:
 *                      - 'contact_data_type': The type of contact data (contact or company).
 *                      - 'member': The member object.
 *                      - 'object': The customer/organization object.
 *                      - 'data': Additional data related to the contact.
 *                      - 'is_new': Boolean indicating if the member is new.
 *                      - 'is_api': Boolean indicating if the call is from an API.
 */
function save_member_associated_contact_data($params) {

	$member = array_var($params, 'member');
	if (!$member instanceof Member) {
		return;
	}
	/** @var $object Customer|Organization|Supplier */
	$object = array_var($params, 'object');
	if (!$object instanceof ContentDataObject) {
		return;
	}
	$data = array_var($params, 'data', []);
	$is_api = array_var($params, 'is_api', false);
	$isNew = array_var($params, 'is_new', $member->isNew());
	
	$name = $member->getName();

	$contact_data_type = array_var($data, 'contact_data_type');

    // Create a contact or company for this client/organzation/etc.
    if ($contact_data_type) {
        $objectController = new ObjectController();
        $typeObject = ObjectTypes::instance()->findById($contact_data_type);

        if (isset($_REQUEST['dim_obj-associated-contact_id'])) {
            $newContactData = $_REQUEST['dim_obj-associated-contact_id'];
            $emails = &$newContactData['emails'];

            if (count($emails) > 0) {
                $firstEmail = &$emails[0];
                if (!$firstEmail['deleted']) {
                    $newContactData['email'] = $firstEmail['email_address'];
                    $newContactData['email_type'] = $firstEmail['type'];
                    unset($emails[0]);
                } elseif (!isset($firstEmail['id']) || $firstEmail['id'] == 0) {
                    unset($emails[0]);
                }
            }

            $associatedData = $_REQUEST['dim_obj']['associated']['contact_id'];
            $newContactData['comments'] = $associatedData['comments'];
            $newContactData['timezone_id'] = $associatedData['timezone_id'];
            $newContactData['company_id'] = $associatedData['company_id'];
            $newContactData['department'] = $associatedData['department'];
            $newContactData['birthday'] = $associatedData['birthday'];

            if ($typeObject->getName() == 'contact') {
                $_POST['contact'] = $newContactData;
            } elseif ($typeObject->getName() == 'company') {
                $_POST['company'] = $newContactData;
            }
        }

        switch ($typeObject->getName()) {
            case "contact":
                $contactData = array_var($_POST, 'contact', false);
                $contactEmail = $contactData['email'];
                $existingContact = null;

                if (!$isNew && $object->getContactId() > 0) {
                    $existingContact = Contacts::instance()->findById($object->getContactId());
                }

                if (!$existingContact && trim($contactEmail) != "") {
                    if (config_option('allow_duplicated_contact_emails')) {
                        $existingContact = Contacts::getByFirstnameAndSurname(
                            trim(array_var($contactData, 'first_name')),
                            trim(array_var($contactData, 'surname')),
                            $contactEmail
                        );
                    } else {
                        $contactEmails = ContactEmails::instance()->findAll(array(
                            'conditions' => array("`email_address` = ?", $contactEmail)
                        ));
                        foreach ($contactEmails as $ce) {
                            if (can_read_sharing_table(logged_user(), $ce->getContactId())) {
                                $existingContact = Contacts::instance()->findById($ce->getContactId());
                                break;
                            }
                        }
                    }
                }

                if ($existingContact instanceof Contact && !$existingContact->getIsCompany()) {
                    $object->setContactId($existingContact->getId());
                    $contact = $existingContact;
                } elseif ($existingContact instanceof Contact) {
                    $object->setContactId($existingContact->getId());
                    $contact = $existingContact;
                    $contact->setIsCompany(false);
                } else {
                    $contact = new Contact();

                    $compData = array_var($_POST, 'company');
                    if (trim(array_var($compData, 'first_name', '')) != '') {
                        $comp = new Contact();
                        if ($compData) {
                            $comp->setFromAttributes($compData);
                        }
                        $comp->setIsCompany(true);
                        $comp->setObjectName();
                        $comp->save();
                        if (trim(array_var($compData, 'email', '')) != "") {
                            $comp->addEmail($compData['email'], $compData['email_type'], true);
                        }

                        $contactController = new ContactController();
                        $contactController->save_phones_addresses_webpages($compData, $comp);

                        $contactData['company_id'] = $comp->getId();
                    }
                }

                $contactData['birthday'] = getDateValue($contactData["birthday"]);
                if ($contactData) {
                    $contact->setFromAttributes($contactData);
                }
                $nameExp = explode(" ", $name, 2);

                if (count($nameExp) >= 2) {
                    $contact->setFirstName($nameExp[0]);
                    $contact->setSurname($nameExp[1]);
                } else {
                    $contact->setFirstName($name);
                    $contact->setSurname("");
                }

                $contact->setObjectName();
                $contact->add_skip_validation('email');
                $contact->save();

                $contactData['email'] = $contactEmail;
                $typeEmail = 'personal';

                if (array_var($contactData, 'email') != "") {
                    $cEmail = ContactEmails::instance()->findOne(array(
                        'conditions' => array(
                            "`contact_id` = ? AND `email_type_id` = ? AND TRIM(email_address) <> '' ",
                            $contact->getId(), $contactData['email_type']
                        )
                    ));

                    if ($cEmail instanceof ContactEmail && $cEmail->getEmailAddress() != $contactData['email']) {
                        $cEmail->setEmailAddress($contactData['email']);
                        $cEmail->setEmailTypeId($typeEmail);
                        $cEmail->save();
                    } elseif (!$cEmail) {
                        $emailTypes = EmailTypes::getAllEmailTypesInfo();
                        foreach ($emailTypes as $email) {
                            if ($email['id'] == $contactData['email_type']) {
                                $typeEmail = $email['code'];
                                break;
                            }
                        }
                        $contact->addEmail($contactData['email'], $typeEmail, true);
                    }
                } else {
                    if (array_var($contactData, 'email') == "") {
                        ContactEmails::instance()->delete("`is_main`=1 AND `contact_id`=" . $contact->getId() . " AND
                            `email_type_id`='" . array_var($contactData, 'email_type') . "' AND TRIM(email_address) <> '' ");
                    } else {
                        if (!is_valid_email($contactData['email'])) {
                            throw new Exception(lang('invalid email address'));
                        }
                    }
                }

                if ($contactData) {
                    $contactController = new ContactController();
                    $contactController->save_phones_addresses_webpages($contactData, $contact);
                    $contactController->save_non_main_emails($contactData, $contact);
                }

                $imTypes = ImTypes::instance()->findAll(array('order' => '`id`'));
                $contact->clearImValues();
                foreach ($imTypes as $imType) {
                    $value = trim(array_var($contactData, 'im_' . $imType->getId()));
                    if ($value <> '') {
                        $contactImValue = new ContactImValue();
                        $contactImValue->setContactId($contact->getId());
                        $contactImValue->setImTypeId($imType->getId());
                        $contactImValue->setValue($value);
                        $contactImValue->setIsMain(array_var($contactData, 'default_im') == $imType->getId());
                        $contactImValue->save();
                    }
                }

                if ($is_api) {
                    $objCustomProperties = array_var($data, 'object_custom_properties');
                    $objectController->add_custom_properties($contact, $objCustomProperties);
                } else {
                    $objectController->add_custom_properties($contact, array_var($_POST, 'object_custom_properties'));
                }

                ObjectMembers::addObjectToMembers($contact->getId(), array($member));
                $contact->addToRelatedMembers(array($member), false, true);
                $contact->addToSharingTable();
                $object->setContactId($contact->getId());
                $object->setContactObjectTypeId($contact_data_type);
                $object->save();
                break;

            case "company":
                $companyData = array_var($_POST, 'company', false);
                $existingContact = null;

                if (!$isNew && $object->getContactId() > 0) {
                    $existingContact = Contacts::instance()->findById($object->getContactId());
                }

                if (!$existingContact && isset($contactEmail) && trim($contactEmail) != "") {
                    $contactEmails = ContactEmails::instance()->findAll(array(
                        'conditions' => array("`email_address` = ?", $contactEmail)
                    ));
                    foreach ($contactEmails as $ce) {
                        if (can_read_sharing_table(logged_user(), $ce->getContactId())) {
                            $existingContact = Contacts::instance()->findById($ce->getContactId());
                            break;
                        }
                    }
                }

                if ($existingContact instanceof Contact && $existingContact->getIsCompany() && can_read_sharing_table(logged_user(), $existingContact->getId())) {
                    $object->setContactId($existingContact->getId());
                    $company = $existingContact;
                } elseif ($existingContact instanceof Contact && can_read_sharing_table(logged_user(), $existingContact->getId())) {
                    $object->setContactId($existingContact->getId());
                    $company = $existingContact;
                    $company->setIsCompany(true);
                } else {
                    $company = new Contact();
                }

                if ($companyData) {
                    $company->setFromAttributes($companyData);
                }
                $company->setFirstName($name);
                $company->setSurname("");
                $company->setObjectName();
                $company->setIsCompany(true);

                if (isset($_SESSION['new_contact_picture']) && $_SESSION['new_contact_picture']) {
                    $company->setPictureFile($_SESSION['new_contact_picture']);
                    $company->setPictureFileMedium($_SESSION['new_contact_picture_medium'], 'image/png');
                    $company->setPictureFileSmall($_SESSION['new_contact_picture_small'], 'image/png');
                    $_SESSION['new_contact_picture'] = null;
                    $_SESSION['new_contact_picture_medium'] = null;
                    $_SESSION['new_contact_picture_small'] = null;
                }

                $company->add_skip_validation('email');
                $company->save();
                $typeEmail = 'work';

                if (array_var($companyData, 'email') != "") {
                    $cEmail = ContactEmails::instance()->findOne(array(
                        'conditions' => array(
                            "`contact_id` = ? AND `email_type_id` = ? AND TRIM(email_address) <> '' ",
                            $company->getId(), $companyData['email_type']
                        )
                    ));

                    if ($cEmail instanceof ContactEmail && $cEmail->getEmailAddress() != $companyData['email']) {
                        $cEmail->setEmailAddress($companyData['email']);
                        $cEmail->setEmailTypeId($companyData['email_type']);
                        $cEmail->save();
                    } elseif (!$cEmail) {
                        $emailTypes = EmailTypes::getAllEmailTypesInfo();
                        foreach ($emailTypes as $email) {
                            if ($email['id'] == $companyData['email_type']) {
                                $typeEmail = $email['code'];
                                break;
                            }
                        }
                        $company->addEmail($companyData['email'], $typeEmail, true);
                    }
                } else {
                    if (array_var($companyData, 'email') == "") {
                        ContactEmails::instance()->delete("`is_main`=1 AND `contact_id`=" . $company->getId() . " AND 
                            `email_type_id`='" . array_var($companyData, 'email_type') . "' AND TRIM(email_address) <> '' ");
                    } else {
                        if (!is_valid_email($companyData['email'])) {
                            throw new Exception(lang('invalid email address'));
                        }
                    }
                }

                if ($companyData) {
                    $contactController = new ContactController();
                    $contactController->save_phones_addresses_webpages($companyData, $company);
                    $contactController->save_non_main_emails($companyData, $company);
                }

                if ($is_api) {
                    $objCustomProperties = array_var($data, 'object_custom_properties');
                    $objectController->add_custom_properties($company, $objCustomProperties);
                } else {
                    $objectController->add_custom_properties($company, array_var($_POST, 'object_custom_properties'));
                }

                ObjectMembers::addObjectToMembers($company->getId(), array($member));
                $company->addToRelatedMembers(array($member), false, true);
                $company->addToSharingTable();
                $object->setContactId($company->getId());
                $object->setContactObjectTypeId($contact_data_type);
                $object->save();
                break;
        }
    } else {
        if (array_var($data, 'existing_contact_id') > 0) {
            $existingContact = Contacts::instance()->findById(array_var($data, 'existing_contact_id'));
            if ($existingContact instanceof Contact) {
                $existingContact->addToMembers(array($member), true);
                $object->setContactId($existingContact->getId());
                $contactTypeId = $existingContact->getIsCompany() ? ObjectTypes::findByName('company')->getId() : $existingContact->getObjectTypeId();
                $object->setContactObjectTypeId($contactTypeId);
                $object->save();
            }
        } else {
            $object->setContactId(0);
            $object->setContactObjectTypeId(0);
            $object->save();
        }
    }
}
