<?php

/**
 * Contact class
 *
 * @author Carlos Palma <chonwil@gmail.com>, Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
class Contact extends BaseContact {
	
	protected $searchable_columns = array('name', 'first_name', 'surname', 'display_name');
	
	protected $is_read_markable = false;
	
	public $notify_myself;
	public $ignore_permissions_for_notifications = false;
	
	/**
	 * If contact is a company, cache the company users for subsequent calls
	 *
	 * @var array
	 */
	private $company_users = null;
	
	/**
	 * Cached is_account_owner value. Value is retrived on first requests
	 *
	 * @var boolean
	 */
	private $is_account_owner = null;
	
	private $company;
	
	/**
	 * Construct contact object
	 *
	 * @param void
	 * @return User
	 */
	function __construct() {
		parent::__construct ();
	} // __construct
	
	function getObjectTypeName() {
		if ($this->getIsCompany()) return 'company';
		else return 'contact';
	}
	
	
	
	function getObjectName() {
		//$name = parent::getObjectName();
        if(user_config_option("listingContactsBy")){
            $name = $this->getDisplayName();
        } else {
            $name = $this->getReverseDisplayName();
        }
		
		Hook::fire('override_contact_name', array('contact' => $this), $name);
		
		return $name;
	}

	
	
	/**
	 * Array of email accounts
	 *
	 * @var array
	 */
	protected $mail_accounts;
	function save(){
	   		parent::save();
	   		$sql = "DELETE FROM ".TABLE_PREFIX."searchable_objects
					WHERE rel_object_id = '".$this->getId()."' AND (column_name LIKE 'phone_number%'  OR column_name LIKE 'email_addres%' OR column_name LIKE 'web_url%' OR column_name LIKE 'im_value%' OR column_name LIKE 'address%')";
	   		DB::execute($sql);
	   		//save telephones on searchable_objects
	   		$telephones = $this->getAllPhones();
	   		$lengthTel = count($telephones);
	   		for ($i = 0; $i < $lengthTel; $i++) {
	   			$j=strval($i);
	   			$telephone = array_var($telephones, $j);
	   			if ($telephone instanceof ContactTelephone){
	   				$telephone =$telephone->getNumber();
	   			}else{
	   				continue;
	   			}
	   			$searchable_object = new SearchableObject();
	   			$searchable_object->setRelObjectId($this->getId());
	   			$searchable_object->setColumnName("phone_number".$j);
	   			$searchable_object->setContent($telephone);
	   			$searchable_object->save();	   			
	   		}
	   		//save emails on searchable_objects
	   		$emails = $this->getAllEmails();	   			   		
	   		$lengthEm = count($emails);
	   		for ($i = 0; $i < $lengthEm; $i++) {
	   			$j=strval($i);
	   			$email = array_var($emails, $j);
	   			if ($email instanceof ContactEmail){
	   				$email =$email->getEmailAddress();
	   			}else{
	   				continue;
	   			}
	   			if($email != ''){
		   			$searchable_object = new SearchableObject();
		   			$searchable_object->setRelObjectId($this->getId());
		   			$searchable_object->setColumnName("email_addres".$j);
		   			$searchable_object->setContent($email);
		   			$searchable_object->save();		   			
	   			}
	   		}
	   		//save web_pages on searchable_objects
	   		$web_pages = $this->getAllWebpages();
	   		$lengthWeb = count($web_pages);	   		
	   		for ($i = 0; $i < $lengthWeb; $i++) {
	   			$j=strval($i);
	   			$web_page = array_var($web_pages, $j);
	   			if ($web_page instanceof ContactWebpage){
	   				$web_page =$web_page->getUrl();
	   			}else{
	   				continue;
	   			}
	   			$searchable_object = new SearchableObject();
	   			$searchable_object->setRelObjectId($this->getId());
	   			$searchable_object->setColumnName("web_url".$j);
	   			$searchable_object->setContent($web_page);
	   			$searchable_object->save();	   			
	   		}  		
	   		//save im_values on searchable_objects
	   		$im_values = $this->getImValues();
	   		$lengthIm = count($im_values);
	   		for ($i = 0; $i < $lengthIm; $i++) {
	   			$j=strval($i);
	   			$im_value = array_var($im_values, $j);
	   			if ($im_value instanceof ContactImValue){
	   				$im_value =$im_value->getValue();
	   			}else{
	   				continue;
	   			}
	   			$searchable_object = new SearchableObject();
	   			$searchable_object->setRelObjectId($this->getId());
	   			$searchable_object->setColumnName("im_value".$j);
	   			$searchable_object->setContent($im_value);
	   			$searchable_object->save();	   			   			
	   		}
	   		//save addresses on searchable_objects
			$addresses = $this->getAllAddresses();
			$lengthAd = count($addresses);
			for ($i = 0; $i < $lengthAd; $i++) {
	   			$j=strval($i);
	   			$address = array_var($addresses, $j);
	   			if (!$address instanceof ContactAddress){
	   				continue;
	   			}
	   			$address = strval(array_var($addresses, $j)->getStreet())." ";
	   			$address .= strval(array_var($addresses, $j)->getCity())." ";
	   			$address .= strval(array_var($addresses, $j)->getState())." ";
	   			$address .= strval(array_var($addresses, $j)->getCountry())." ";
	   			$address .= strval(array_var($addresses, $j)->getZipCode());
	   			$searchable_object = new SearchableObject();
	   			$searchable_object->setRelObjectId($this->getId());
	   			$searchable_object->setColumnName("address".$j);
	   			$searchable_object->setContent($address);
	   			$searchable_object->save();
	   		}
	   		return true;
	}
	
	function hasMailAccounts(){
		if (Plugins::instance()->isActivePlugin('mail')) {
			if(is_null($this->mail_accounts))
				$this->mail_accounts = MailAccounts::getMailAccountsByUser(logged_user());
			return is_array($this->mail_accounts) && count($this->mail_accounts) > 0;
		}
		return false;
	}
	
	function hasReferences() {
		$id = $this->getId();
		
		// Check form linked objects
		$linked_obj_references_count = LinkedObjects::instance()->count("`created_by_id` = $id");
		if ($linked_obj_references_count > 0){
			return true;
		}
			
		// Check direct references
		$references = DB::executeAll("SELECT id FROM ".TABLE_PREFIX."objects WHERE `created_by_id` = $id OR `updated_by_id` = $id OR `trashed_by_id` = $id OR `archived_by_id` = $id limit 1");
		if (isset($references) && count($references) > 0){
			return true;
		}
		
		return false;
	}
	
	
	/**
	 * @abstract Sets the user disabled, if it has no references in the system it is physically deleted
	 * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
	 */
	function disable($deleteInactive = true) {
		if (!$this->canDelete(logged_user())) {
			return false;	
		}
		
		if (parent::getUserType() != 0 && !$this->getDisabled()) {
			if (!$deleteInactive || $this->hasReferences() ) {
				$this->setDisabled(true);
				$this->setTokenDisabled($this->getToken());
				$this->setToken("");
				$this->save();
			} else {
				$this->do_delete();
			}
			return true;
		}
	}
	
	
	function do_delete() {
		$id = $this->getId();
		
		ContactAddresses::instance()->delete("`contact_id` = $id");
		ContactImValues::instance()->delete("`contact_id` = $id");
		ContactEmails::instance()->delete("`contact_id` = $id");
		ContactTelephones::instance()->delete("`contact_id` = $id");
		ContactWebpages::instance()->delete("`contact_id` = $id");
		ContactConfigOptionValues::instance()->delete("`contact_id` = $id");
		ContactPasswords::instance()->delete("`contact_id` = $id");
		
		ObjectSubscriptions::instance()->delete("`contact_id` = $id");
		ObjectReminders::instance()->delete("`contact_id` = $id");
		
		ContactPermissionGroups::instance()->delete("`contact_id` = $id");
		ContactMemberPermissions::instance()->delete("`permission_group_id` = " . $this->getPermissionGroupId());
		ContactDimensionPermissions::instance()->delete("`permission_group_id` = " . $this->getPermissionGroupId());
		SystemPermissions::instance()->delete("`permission_group_id` = " . $this->getPermissionGroupId());
		TabPanelPermissions::instance()->delete("`permission_group_id` = " . $this->getPermissionGroupId());
		
		$this->delete();
		$ret = null;
		Hook::fire("after_user_deleted", $this, $ret);
	}
	
	
	
	
	function modifyMemberValidations($member) {
		if ($member instanceof Member) {
			$member->add_skip_validation('uniqueness of parent - name');
		} else {
			if ($this->getId() > 0 && Plugins::instance()->isActivePlugin('core_dimensions')) {
				$dim = Dimensions::findByCode('feng_persons');
				if ($dim instanceof Dimension) {
					$m = Members::findByObjectId($this->getId(), $dim->getId());
					if ($m instanceof Member) {
						$m->add_skip_validation('uniqueness of parent - name');
					}
				}
			}
		}
	}
	
	
	
	
	// ---------------------------------------------------
	//  IMs
	// ---------------------------------------------------
	

	/**
	 * Return true if this contact have at least one IM address
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function hasImValue() {
		return ContactImValues::instance()->count('`contact_id` = ' . DB::escape ($this->getId()));
	} // hasImValue
	

	/**
	 * Return all IM values
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getImValues() {
		return ContactImValues::getByContact($this);
	} // getImValues
	

	/**
	 * Return value of specific IM. This function will return null if IM is not found
	 *
	 * @access public
	 * @param ImType $im_type
	 * @return string
	 */
	function getImValue(ImType $im_type) {
		$im_value = ContactImValues::instance()->findOne(array("conditions" => "`contact_id` = ".$this->getId()." AND `im_type_id` = ".$im_type->getId()));
		return $im_value instanceof ContactImValue && (trim($im_value->getValue()) != '') ? $im_value->getValue() : null;
	} // getImValue
	

	/**
	 * Return main IM value. If value was not found NULL is returned
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getMainImValue() {
		$main_im_type = $this->getMainImType();
		return $this->getImValue($main_im_type);
	} // getMainImValue
	

	/**
	 * Return main contact IM type. If there is no contact main IM type NULL is returned
	 *
	 * @access public
	 * @param void
	 * @return ImType
	 */
	function getMainImType() {
		return ContactImValues::getContactMainImType($this);
		
	} // getMainImType
	

	/**
	 * Clear all IM values
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function clearImValues() {
		return ContactImValues::instance()->clearByContact($this);
	} // clearImValues
	

	// ---------------------------------------------------
	//  Retrieve
	// ---------------------------------------------------
	

	/**
	 * Return display name for this account. If there is no display name set username will be used
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDisplayName() {
		return $this->getFirstName()." ".$this->getSurname();
	} // getDisplayName
	

	function getInitials() {
		$initials = '';
		$names = explode(' ', $this->getDisplayName());
		foreach ($names as $name) {
			$initials .= strtoupper(substr($name, 0, 1));
		}
		return $initials; 
	}

	/**
	 * Return display name with last name first for this contact
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getReverseDisplayName() {
		if (parent::getSurname() != "")
			$display = parent::getSurname() . ", " . parent::getFirstName();
		else
			$display = parent::getFirstName();
		return trim ($display);
	} // getReverseDisplayName
	
	
	/**
	 * Returns the contact's company
	 *
	 * @access public
	 * @return Contact
	 */
	function getCompany() {
		if(is_null($this->company)) {
			$this->company = Contacts::instance()->findById($this->getCompanyId());
		}
		return $this->company;
	} // getCompany
	
	
	/**
	 * Returns true if contact is a user
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function isUser() {
		$type =  parent::getUserType();
		return $type != 0;
	} // isUser
	
	
	/**
	 * Returns true if is Owner company
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function isOwnerCompany() {
		return $this->getObjectId() == owner_company()->getId();
	} // isOwnerCompany

	
	/**
	 * Returns true if contact is a company
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function isCompany() {
		return parent::getIsCompany();
	} // isCompany
	

	/**
	 * Returns true if contact is an active user
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function isActiveUser() {
		return parent::getIsActiveUser();
	} // isActiveUser
	
	
	/**
	 * 
	 *
	 * @access public
	 * @param void
	 * @return string
	 * @deprecated
	 */
	 function getEmail($type=null) {
		if (is_null($type)){
			if ($this->getIsCompany()) {
				$type = 'work';
			} else {
				$type = $this->getUserType() > 0 ? 'user' : 'personal';
			}
		}
		$email_type_id = EmailTypes::getEmailTypeId($type);
		return ContactEmails::getContactMainEmail($this, $email_type_id);
	 } // getEmail
	 
	 
	 
	/**
	 * Return mail address for the contact.
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	 function getEmailAddress($type=null) {
	 	$contact_id = $this->getId();
	 	$type_condition =  ($type) ? "AND t.name = '$type'" : "";
	 	
	 	$sql = "SELECT * FROM ".TABLE_PREFIX."contact_emails ce
				LEFT JOIN ".TABLE_PREFIX."email_types t
				ON ce.email_type_id = t.id
				WHERE TRIM(email_address) <> ''
				AND email_address IS NOT NULL
				AND contact_id = $contact_id
				$type_condition order by is_main desc LIMIT 1";
	 	
	 	$email_address = null;
		if ($row = DB::executeOne($sql)) {
			$email_address = $row['email_address'];
		}
		Hook::fire('override_contact_email', array('contact' => $this), $email_address);
		return $email_address;
	 } 
	
	 	 
	 function getContactEmails($type){
	 	$email_type_id = EmailTypes::getEmailTypeId($type);
		return ContactEmails::getContactEmails($this, $email_type_id);
	 }
	 
	 
	 function getNonMainEmails() {
	 	return ContactEmails::instance()->findAll(array('conditions' => 'is_main=0 AND contact_id = '.$this->getId()));
	 }
	 
	 function getMainEmails() {
	 	return ContactEmails::instance()->findAll(array('conditions' => 'is_main=1 AND contact_id = '.$this->getId()));
	 }

	/**
	 * Retrieve all email addresses of the contact as a CSV string.
	 *
	 * @return string A comma-separated string of all email addresses.
	 */
	function getAllEmailsCSV() {
		$all_email_addresses = [];
		$all_emails = $this->getAllEmails(); // Fetch all email objects associated with the contact.
		
		// Iterate through each email object and extract the email address.
		foreach ($all_emails as $email) {
			$all_email_addresses[] = $email->getEmailAddress();
		}
		
		// Concatenate all email addresses into a single CSV string.
		$all_emails_string = implode(",", $all_email_addresses);
		return $all_emails_string;
	}
	 

	/**
	 * Return  Address for this contact.
	 *
	 * @access public
	 * @param $typeId
	 * @param $main
	 * @return ContactAddress
     * @author Seba
	 */
	function getAddress($type) {
		$address_type_id = AddressTypes::getAddressTypeId($type);
		return ContactAddresses::instance()->findOne(array('conditions' => array("`contact_id` = ? AND `address_type_id` = ?", $this->getId(), $address_type_id)));
	} // getMainPhone

	/**
	 * Return
	 *
	 * @access public
	 * @param $typeId
	 * @param $main
	 * @return string with formated address.
	 */
	function getStringAddress($type) {
		$address_type_id = AddressTypes::getAddressTypeId($type);
		$address = ContactAddresses::instance()->findOne(array('conditions' => array("`contact_id` = ? AND `address_type_id` = ?", $this->getId(), $address_type_id)));

		if (!$address instanceof ContactAddress) return "";

		$out = $address->getStreet();
		if($address->getCity() != '') {
			$out .= ' - ' . $address->getCity();
		}
		if($address->getZipCode() != '') {
			$out .= ' - ' . $address->getZipCode();
		}
		if($address->getState() != '') {
			$out .= ' - ' . $address->getState();
		}
		if($address->getCountry() != '') {
			$out .= ' - ' . $address->getCountryName();
		}
		return $out;
	} // getMainPhone
	
	
	/**
	 * Return personal fax phone for this contact.
	 *
	 * @access public
	 * @param void
	 * @return ContactTelephone
     * @author Seba
	 */
	function getPhone($type, $is_main = false, $check_is_main = true) {
		if ($check_is_main) {
			$is_main_cond = "`is_main` = ".($is_main ? 1 : 0);
		} else {
			$is_main_cond = "true";
		}
		$telephone_type_id = TelephoneTypes::getTelephoneTypeId($type);
		return ContactTelephones::instance()->findOne(array('conditions' => array("$is_main_cond AND `contact_id` = ? AND 
		`telephone_type_id` = ?", $this->getId(), $telephone_type_id)));
	} // getFaxPhone	
	
	function getAllPhones($type = '') {
		$type_cond = '';
		if ($type != '') {
			$type_cond = " AND telephone_type_id = (SELECT id FROM ".TABLE_PREFIX."telephone_types WHERE name='$type')";
		}
		return ContactTelephones::instance()->findAll(array('conditions' => array("`contact_id` = ? $type_cond" ,$this->getId())));
		
	} // getAllPhones
	
	function getAllEmails() {
		return ContactEmails::instance()->findAll(array('conditions' => array("`contact_id` = ?" ,$this->getId())));
	
	} // getAllEmails
	
	function getAllWebpages() {		
		return ContactWebpages::instance()->findAll(array('conditions' => array("`contact_id` = ?",	$this->getId())));
	} // getAllWebpages
	
	function getAllAddresses() {
		return ContactAddresses::instance()->findAll(array('conditions' => array("`contact_id` = ?", $this->getId())));
	} // getAllAddress
	/**
	 * Return personal fax phone for this contact.
	 *
	 * @access public
	 * @param void
	 * @return string
     * @author Seba
	 */
	function getPhoneNumber($type, $is_main = false, $check_is_main = true) {
		$telephone = $this->getPhone($type, $is_main, $check_is_main);
		$number = is_null($telephone)? '' : $telephone->getNumber();
		return $number;
	} // getPhoneNumber

	/**
	 * Get all phone numbers of a contact in a single CSV string.
	 *
	 * This function will return a comma separated list of phone numbers associated with the contact.
	 * The list will include all phone numbers, regardless of their type.
	 *
	 * @param string $type (optional) If provided, only phone numbers of the specified type will be returned.
	 *
	 * @return string A comma separated list of phone numbers.
	 */
	function getAllPhoneNumbersCSV($type = '') {
		$all_phone_numbers = []; // Initialize an empty array to store phone numbers.
		$all_phones = $this->getAllPhones($type); // Fetch all phone objects associated with the contact.
		
		// Loop through all phone objects and add the phone number to the array.
		foreach ($all_phones as $phone) {
			$all_phone_numbers[] = $phone->getNumber();
		}
		
		// Join the array of phone numbers into a single string using a comma as the separator.
		return implode(',', $all_phone_numbers);
	}
	
	function getPhoneName($type, $is_main = false, $check_is_main = true) {
		$telephone = $this->getPhone($type, $is_main, $check_is_main);
		$name = is_null($telephone)? '' : $telephone->getName();
		return $name;
	} // getPhoneNumber

	function getAllImValues() {
		$rows = DB::executeAll("SELECT i.value, t.name FROM ".TABLE_PREFIX."contact_im_values i INNER JOIN ".TABLE_PREFIX."im_types t ON i.im_type_id=t.id WHERE i.contact_id=".$this->getId());
		$res = array();
		foreach ($rows as $row) {
			$res[$row['name']] = $row['value'];
		}
		return $res;
	}
	
	/**
	 * Return first webpage for this contact.
	 *
	 * @access public
	 * @param void
	 * @return ContactWebpage
     * @author Seba
	 */
	function getWebpage($type) {
		$webpage_type_id = WebpageTypes::getWebpageTypeId($type);
		return ContactWebpages::instance()->findOne(array('conditions' => array("`contact_id` = ? AND `web_type_id` = ?", 
    		   $this->getId(), $webpage_type_id)));
	} // getWebpage	
	
	 
	/**
	 * Return first webpage URL for this contact.
	 *
	 * @access public
	 * @param void
	 * @return string
     * @author Seba
	 */
	function getWebpageUrl($type) {
		$webpage = $this->getWebpage($type);
		$address = is_null($webpage) ? '' : $webpage->getUrl();
		return $address;
	} // getWebpageURL

	/**
	 * Return all webpage URLs associated with this contact as a comma-separated string.
	 *
	 * If the $type parameter is provided, only webpage URLs of the specified type will be returned.
	 *
	 * @param string $type (optional) Webpage type to filter by.
	 * @return string A comma-separated list of webpage URLs associated with the contact.
	 */
	function getAllWebpageUrlsCSV($type = '') {
		$all_webpage_urls = []; // Initialize an empty array to store webpage URLs.
		$all_webpages = $this->getAllWebpages($type); // Fetch all webpage objects associated with the contact.
		
		// Loop through all webpage objects and add the webpage URL to the array.
		foreach ($all_webpages as $webpage) {
			$all_webpage_urls[] = $webpage->getUrl();
		}
		
		// Join the array of webpage URLs into a single string using a comma as the separator.
		return implode(',', $all_webpage_urls);
	}
	

	
	// ---------------------------------------------------
	//  Utils
	// ---------------------------------------------------
	

	/**
	 * This function will generate new user password, set it and return it
	 *
	 * @param boolean $save Save object after the update
	 * @return string
	 */
	function resetPassword($save = true) {
		$new_password = substr ( sha1 ( uniqid ( rand (), true ) ), rand ( 0, 25 ), 13 );
		$this->setPassword ( $new_password );
		if ($save) {
			$this->save ();
		} // if
		return $new_password;
	} // resetPassword
	

	/**
	 * Set password value
	 *
	 * @param string $value
	 * @return boolean
	 */
	function setPassword($value) {
		do {
			$salt = substr ( sha1 ( uniqid ( rand (), true ) ), rand ( 0, 25 ), 13 );
			$token = sha1 ( $salt . $value );
		} while ( Contacts::tokenExists ( $token ) );
		
		$this->setToken ( $token );
		$this->setSalt ( $salt );
		$this->setTwister ( StringTwister::getTwister () );
	} // setPassword
	

	/**
	 * Return twisted token
	 *
	 * @param void
	 * @return string
	 */
	function getTwistedToken() {
		return StringTwister::twistHash ( $this->getToken (), $this->getTwister () );
	} // getTwistedToken
	

	/**
	 * Check if $check_password is valid user password
	 *
	 * @param string $check_password
	 * @return boolean
	 */
	function isValidPassword($check_password) {
		return sha1 ( $this->getSalt () . $check_password ) == $this->getToken ();
	} // isValidPassword
    
	/**
	 * Check if $user and $password are related to a valid user and password
	 *
	 * @param string $check_password
	 * @return boolean
	 */	
    static function isValidPasswordLdap($user, $password, $config) {

                // Connecting using the configuration:
                require_once "Net/LDAP2.php";

                $ldap = Net_LDAP2::connect($config);

                // Testing for connection error
                if (PEAR::isError($ldap)) {       
                        return false;
                }
                
                $filter = Net_LDAP2_Filter::create($config['uid'], 'equals', $user);
                $search = $ldap->search(null, $filter, null);

                if (Net_LDAP2::isError($search)) {
                        return false;
                }

                if ($search->count() != 1) {
                        return false;
                }

                // User exists so we may rebind to authenticate the password
                $entries = $search->entries();
                $bind_result = $ldap->bind($entries[0]->dn(), $password);

                if (PEAR::isError($bind_result)) {
                        return false;
                }
                return true;
    } // isValidPasswordLdap
	
    /**
    *
    *@param api hash code
    @return boolean have access to api.
    */
    /*private function  getApiAccess()
    {
        return $this->getToken() == $api_token;
    }
	*/

	/**
	 * Check if $twisted_token is valid for this user account
	 *
	 * @param string $twisted_token
	 * @return boolean
	 */
	function isValidToken($twisted_token) {
		return StringTwister::untwistHash ( $twisted_token, $this->getTwister () ) == $this->getToken ();
	} // isValidToken
	
	
	/* Return array of all company contacts
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getContactsByCompany() {
		return Contacts::instance()->findAll(array(
			'conditions' => '`company_id` = ' . $this->getId(). ' AND `user_type` = 0 AND `disabled` = 0 AND trashed_by_id=0', 
			'order' => '`first_name` ASC, `surname` ASC'
		)); // findAll
	} // getContactsByCompany
	
	
	/* Return array of all company users
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getUsersByCompany() {
		if ($this->company_users == null) {
			$this->company_users = Contacts::instance()->findAll(array('conditions' => '`user_type` <> 0 AND `company_id` = ' . $this->getId(), 'order' => '`first_name` ASC, `surname` ASC'));
		}
		return $this->company_users;
	} // getContactsByCompany

	
	// ---------------------------------------------------
	//  URLs
	// ---------------------------------------------------
	

	/**
	 * Return view URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getViewUrl() {
		$action = $this->isCompany()? 'company_card': 'card';
		return get_url('contact', $action, $this->getId());
	} // getAccountUrl
	
	
	/**
	 * Return view contact URL of this contact
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getCompanyViewUrl() {
		return get_url ( 'contact', 'company_card', $this->getId () );
	} // getCompanyViewUrl

	/**
	 * Return URL that will be used to create a user based on the info of this contact
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getCreateUserUrl() {
		return get_url ( 'contact', 'add', array("id" => $this->getId(), 'is_user' => 1, 'user_type' => 4, "create_user_from_contact" => true) );
	} //  getCreateUserUrl
	

	/**
	 * Show contact card page
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function getCardUrl() {
		$action = "card" ;
		if ($this->isCompany()) {
			$action = 'company_card';
		}else{
			$action = 'card';
		}
		return get_url ( 'contact', $action , $this->getId () );
	} 
	
	
	/**
	 * Show user card page
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function getCardUserUrl() {
		return get_url ( 'contact', 'card', $this->getId () );
	} // getCardUrl

	/**
	 * Return edit contact URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function get() {
		return get_url ( 'contact', 'edit', $this->getId () );
	} // get
	

	/**
	 * Return add contact URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	 function getAddUrl() {
		return get_url ( 'contact', 'add' );
	} // getAddUrl
	

	/**
	 * Return add contact URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getAddContactUrl() {
		return get_url('contact', 'add', array('company_id' => $this->getId()));
	} //  getAddContactUrl
	

	/**
	 * Return update picture URL
	 *
	 * @param string
	 * @return string
	 */
	function getUpdatePictureUrl($redirect_to = null) {
		$attributes = array('id' => $this->getId());
		if(trim($redirect_to) <> '') {
			$attributes['redirect_to'] = str_replace('&amp;', '&', trim($redirect_to));
		} // if

		if ($this->isCompany()) {
			$attributes['is_company'] = 1;
		}
		
		return get_url('contact', 'edit_picture', $attributes);
	}// getUpdatePictureUrl
	

	/**
	 * Return delete picture URL
	 *
	 * @param void
	 * @return string
	 */
	function getDeletePictureUrl($redirect_to = null) {
		$attributes = array('id' => $this->getId());
		if(trim($redirect_to) <> '') {
			$attributes['redirect_to'] = str_replace('&amp;', '&', trim($redirect_to));
		} // if

		return get_url('contact', 'delete_picture', $attributes);
	} 
	// getDeletePictureUrl
	

	// ---------------------------------------------------
	//  System functions
	// ---------------------------------------------------

	private $skip_validations = array();
	
	function add_skip_validation($validation) {
		if (!array_key_exists($validation, $this->skip_validations)) {
			$this->skip_validations[$validation] = true;
		}
	}

	/**
	 * Validate data before save
	 *
	 * @access public
	 * @param array $errors
	 * @return void
	 */
	function validate($errors) {
		
		if ($this->getIsCompany()){
			
			if($this->validatePresenceOf('email')) {
				if(!is_valid_email(trim($this->getEmailAddress()))) {
					$errors[] = lang('invalid email address');
				} 
			}
			
			if(!$this->validatePresenceOf('first_name')) {
				$errors[] = lang('company name required');
			} 

		}
		else{
			$fields = array();
			
			// Only for users: Validate if username is present
			if ($this->getUserType() > 0 && !$this->validatePresenceOf('username')) {
				$errors[] = lang('username value required');
				$fields[] = 'username';
			}
			// Only for users: Validate uniqueness of username
			if ($this->getUserType() > 0 && !$this->validateUniquenessOf('username')) {
				$errors[] = lang('username must be unique');
				$fields[] = 'username';
			}
			
			// check existance of firstname or surname
			if(!$this->validatePresenceOf('surname') && !$this->validatePresenceOf('first_name')) {
				$errors[] = lang('contact identifier required');
				$fields[] = 'first_name';
				$fields[] = 'surname';
			}

	
			if (!in_array('email', $this->skip_validations)) {
				//if email address is entered, it must be unique
				$contact_data = array_var($_POST, 'contact');
				$user = array_var($contact_data,'user');
				$main_email = trim(array_var($contact_data,'email'));
				if($main_email || array_var($user, 'create-user')) {
					if(!preg_match(EMAIL_FORMAT, $main_email)) {
						$errors[] = lang('invalid email address');
						$fields[] = 'email';
					}
					
					$do_validate_unique_mail = true;
					Hook::fire('validate_contact_unique_mail', $this, $do_validate_unique_mail);
					
					if ($do_validate_unique_mail) {
						$conditions = "email_address=".DB::escape($main_email);
						$type_condition = "";
						if (!config_option('check_unique_mail_contact_comp')) {
							$type_condition = " AND (SELECT c.is_company FROM ".TABLE_PREFIX."contacts c WHERE c.object_id=contact_id)=0";
						}
						if (!$this->isNew()) {
							$conditions .= " AND contact_id <> ".$this->getId();
						}
						$conditions .= $type_condition;
						
						$em = ContactEmails::instance()->findOne(array('conditions' => $conditions));
						if($em instanceof ContactEmail) {
							$errors[] = lang('email address must be unique');
							$fields[] = 'email';
						}
					}
				}
			}
			
			return $fields;
		}
	} // validate*/
	
	
	/**
	 * Delete this object
	 *
	 * @param void
	 * @return boolean
	 */
	function delete() {
		// dont delete owner company and account owner
		if ($this->isOwnerCompany() || $this->isAccountOwner()) {
			return false;
		}
		
		if($this->isUser() && logged_user() instanceof Contact && !can_manage_security(logged_user())) {
			return false;
		}
		$this->deletePicture();
		
		ContactEmails::clearByContact($this);	
		ContactAddresses::clearByContact($this);
		ContactTelephones::clearByContact($this);
		ContactWebpages::clearByContact($this);
		ContactImValues::clearByContact($this);
		
		return parent::delete();
	} // delete


	// ---------------------------------------------------
	//  ApplicationDataObject implementation
	// ---------------------------------------------------
	

	/**
	 * Set object name
	 */
	function setObjectName($name = null) {
		if ($name) {
			parent::setObjectName($name);
		}else {
			$display = trim (parent::getFirstName()." ".parent::getSurname());
			parent::setObjectName($display);
		}	
	} 
	

	/**
	 * Return object URl
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectUrl() {
		return $this->getCardUrl ();
	} // getObjectUrl
	

	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------
	

	/**
	 * Returns true if $user can access this contact
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canView(Contact $user) {
		
		$return_false = false;
		Hook::fire('contact_can_view', $this, $return_false);
		if ($return_false) return false;
		
		if ( $this->isOwnerCompany()) return true;
		if (logged_user() instanceof Contact && $this->getId() == logged_user()->getId() ) return true ;
		if ($this->isUser()) {
			// a contact that has a user assigned to it can be modified by anybody that can manage security (this is: users and permissions) or the user himself.
			if($this->getCompanyId() ==  $user->getCompanyId()){
				return true;
			}
			return ($this->getUserType() > $user->getUserType() || $user->isAdministrator());
		}
		 
		return can_read($user, $this->getMembers(), $this->getObjectTypeId());
	} // canView
	
	
	static function canAdd(Contact $user, $context, &$notAllowedMember = ''){
		return can_manage_contacts($user) || can_add($user, $context, Contacts::instance()->getObjectTypeId(), $notAllowedMember);
	}

	/**
	 * Check if specific user can add users
	 *
	 * @access public
	 * @param Contact $user
	 * @return boolean
	 */
	static function canAddUser(Contact $user) {
		return can_manage_security($user);
	}
	
	
	/**
	 * Returns true if this user can see $user
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canSeeUser(Contact $user) {
		if($this->isMemberOfOwnerCompany()) {
			return true; // see all
		} // if
		if($user->getCompanyId() == $this->getCompanyId()) {
			return true; // see members of your own company
		} // if
		if($user->isMemberOfOwnerCompany()) {
			return true; // see members of owner company
		} // if
		return false;
	} // canSeeUser

	/**
	 * Check if specific user can edit this contact
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canEdit(Contact $user) {
		if ($this->isUser()) {
		
			$return_false = false;
			Hook::fire('contact_can_edit', $this, $return_false);
			if ($return_false) return false;
			// a contact that has a user assigned to it can be modified by anybody that can manage security (this is: users and permissions) or the user himself. admin can edit admin
			return can_manage_security($user) && ($this->getUserType() > $user->getUserType() || $user->isAdministrator() || $this->isAdminGroup() && $user->isAdminGroup() && $this->getUserType() >= $user->getUserType() ) || $this->getObjectId() == $user->getObjectId();
		} 
		if ($this->isOwnerCompany()) return can_manage_configuration($user);
		return can_manage_contacts($user) || can_write ($user, $this->getMembers(), $this->getObjectTypeId());
	} // canEdit
	

	/**
	 * Check if specific user can delete this contact
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canDelete(Contact $user) {
		// dont delete account owner
		if ($this->isAccountOwner() || $this->isOwnerCompany()) {
			return false;
		}
		if ($this->getUserType() != 0) {
			return can_manage_security($user) && $this->getUserType() > $user->getUserType();
		} else {
			return can_manage_contacts($user) || can_delete($user, $this->getMembers(), $this->getObjectTypeId());
		}
	} // canDelete
	

	function canLinkObject(Contact $user) {
		return can_read($user, $this->getMembers(), $this->getObjectTypeId());
	}
	
	
	// ---------------------------------------------------
	//  Addresses
	// ---------------------------------------------------
	

	/**
	 * Returns the full address
	 *
	 * @return string
	 */
	function getFullAddress(ContactAddress $address) {
		if($address){
		$line1 = $address->getStreet();
		
		$line2 = '';
		if ($address->getCity() != '')
			$line2 = $address->getCity();
		
		if ($address->getState() != '') {
			if ($line2 != '')
				$line2 .= ', ';
			$line2 .= $address->getState();
		}
		
		if ($address->getZipcode() != '') {
			if ($line2 != '')
				$line2 .= ', ';
			$line2 .= $address->getZipcode();
		}
		
		$line3 = '';
		if ($address->getCountryName() != '')
			$line3 = $address->getCountryName();
		
		$result = $line1;
		if ($line2 != '')
			$result .= "\n" . $line2;
		if ($line3 != '')
			$result .= "\n" . $line3;
		
		return $result;
		}
		return "";
	}
	
	function getDashboardObject() {
		//FIXME
		$wsIds = $this->getWorkspacesIdsCSV ( logged_user ()->getWorkspacesQuery () );
		
		if ($this->getUpdatedById () > 0 && $this->getUpdatedBy () instanceof Contact) {
			$updated_by_id = $this->getUpdatedBy ()->getObjectId ();
			$updated_by_name = $this->getUpdatedByDisplayName ();
			$updated_on = $this->getObjectUpdateTime () instanceof DateTimeValue ? ($this->getObjectUpdateTime ()->isToday () ? format_time ( $this->getObjectUpdateTime () ) : format_datetime ( $this->getObjectUpdateTime () )) : lang ( 'n/a' );
		} else {
			if ($this->getCreatedById () > 0 && $this->getCreatedBy () instanceof Contact)
				$updated_by_id = $this->getCreatedBy ()->getId ();
			else
				$updated_by_id = lang ( 'n/a' );
			$updated_by_name = $this->getCreatedByDisplayName ();
			$updated_on = $this->getObjectCreationTime () instanceof DateTimeValue ? ($this->getObjectCreationTime ()->isToday () ? format_time ( $this->getObjectCreationTime () ) : format_datetime ( $this->getObjectCreationTime () )) : lang ( 'n/a' );
		}
		
		$deletedOn = $this->getTrashedOn () instanceof DateTimeValue ? ($this->getTrashedOn ()->isToday () ? format_time ( $this->getTrashedOn () ) : format_datetime ( $this->getTrashedOn (), 'M j' )) : lang ( 'n/a' );
		if ($this->getTrashedById () > 0)
			$deletedBy = Contacts::instance()->findById( $this->getTrashedById () );
		if (isset ( $deletedBy ) && $deletedBy instanceof Contact) {
			$deletedBy = $deletedBy->getObjectName ();
		} else {
			$deletedBy = lang ( "n/a" );
		}
		
		$archivedOn = $this->getArchivedOn () instanceof DateTimeValue ? ($this->getArchivedOn ()->isToday () ? format_time ( $this->getArchivedOn () ) : format_datetime ( $this->getArchivedOn (), 'M j' )) : lang ( 'n/a' );
		if ($this->getArchivedById () > 0)
			$archivedBy = Contacts::instance()->findById( $this->getArchivedById () );
		if (isset ( $archivedBy ) && $archivedBy instanceof Contact) {
			$archivedBy = $archivedBy->getObjectName ();
		} else {
			$archivedBy = lang ( "n/a" );
		}
		return array ("id" => $this->getObjectTypeName () . $this->getId (), "object_id" => $this->getId (), "ot_id" => $this->getObjectTypeId (), "name" => $this->getObjectName (), "type" => $this->getObjectTypeName (), "tags" => project_object_tags ( $this ), "createdBy" => $this->getCreatedByDisplayName (), // Users::instance()->findById($this->getCreatedBy())->getUsername(),
"createdById" => $this->getCreatedById (), "dateCreated" => $this->getObjectCreationTime () instanceof DateTimeValue ? ($this->getObjectCreationTime ()->isToday () ? format_time ( $this->getObjectCreationTime () ) : format_datetime ( $this->getObjectCreationTime () )) : lang ( 'n/a' ), "updatedBy" => $updated_by_name, "updatedById" => $updated_by_id, "dateUpdated" => $updated_on, "wsIds" => $wsIds, "url" => $this->getObjectUrl (), "manager" => get_class ( $this->manager () ), "deletedById" => $this->getTrashedById (), "deletedBy" => $deletedBy, "dateDeleted" => $deletedOn, "archivedById" => $this->getArchivedById (), "archivedBy" => $archivedBy, "dateArchived" => $archivedOn );
	}
	
	/**
	 * This function will return content of specific searchable column. It uses inherited
	 * behaviour for all columns except for `firstname`, which is used as a column representing
	 * the first and last name of the contact, and all of the addresses, which are saved in full
	 * form.
	 *
	 * @param string $column_name Column name
	 * @return string
	 */
	function getSearchableColumnContent($column_name) {
		if ($column_name == 'firstname') {
			return trim ( $this->getFirstname () . ' ' . $this->getSurname () );
		} else if ($column_name == 'w_address') {
			return strip_tags ( trim ( $this->getFullWorkAddress () ) );
		} else if ($column_name == 'h_address') {
			return strip_tags ( trim ( $this->getFullHomeAddress () ) );
		} else if ($column_name == 'o_address') {
			return strip_tags ( trim ( $this->getFullOtherAddress () ) );
		}
		
		return parent::getSearchableColumnContent ( $column_name );
	} // getSearchableColumnContent
	
	
	
	/**
     * 
     * Add email address to the contact
     * @param string $value
     * @param boolean $isMain
     * @author pepe
     */
    function addEmail($value, $email_type, $isMain = false, $isMainbilling = false) {

    	$value=trim($value);
    	$email = new ContactEmail() ;
    	$email->setEmailTypeId(EmailTypes::getEmailTypeId($email_type));
    	$email->setEmailAddress($value);
    	$email->setContactId($this->getId());
    	$email->setIsMain($isMain);
		$email->setDefaultEmail($isMainbilling);
    	$email->save();
    }
    
    function hasEmail($value, $email_type, $isMain = false) {
    	$type_id = EmailTypes::getEmailTypeId($email_type);
    	$obj = ContactEmails::instance()->findOne(array('conditions' => array(
    			'contact_id=? AND email_type_id=? AND email_address=?',
    			$this->getId(), $type_id, $value
    	)));
    	 
    	return $obj instanceof ContactEmail;
    }
    
    
	/**
     * 
     * Add address to the contact
     * @param string $street
     * @param string $city
     * @param string $state
     * @param string $country
     * @param string $zipCode
     * @param int $email_type
     * @param boolean $isMain
     * @author Seba
     */
    function addAddress($street, $city, $state, $country, $zipCode, $address_type, $isMain = false) {
    	$address = new ContactAddress();
    	$address->setAddressTypeId(AddressTypes::getAddressTypeId($address_type));
    	$address->setStreet($street);
    	$address->setCity($city);
    	$address->setState($state);
    	$address->setCountry($country);
    	$address->setZipCode($zipCode);
    	$address->setContactId($this->getId());
    	$address->setIsMain($isMain);
    	$address->save();
    }
    
    function hasAddress($street, $city, $state, $country, $zipCode, $address_type, $isMain = false) {
    	$type_id = AddressTypes::getAddressTypeId($address_type);
    	$obj = ContactAddresses::instance()->findOne(array('conditions' => array(
    			'contact_id=? AND address_type_id=? AND street=? AND city=? AND state=? AND zip_code=? AND country=?',
    			$this->getId(), $type_id, $street, $city, $state, $zipCode, $country
    	)));
    	
    	return $obj instanceof ContactAddress;
    }
    
    
	/**
     * 
     * Add phone to the contact
     * @param string $number
     * @param int $phone_type
     * @param boolean $isMain
     * @author Seba
     */
    function addPhone($number, $phone_type, $isMain = false, $name = "") {
    	$phone = new ContactTelephone() ;
    	$phone->setNumber($number);
    	$phone->setTelephoneTypeId(TelephoneTypes::getTelephoneTypeId($phone_type));
    	$phone->setContactId($this->getId());
    	$phone->setIsMain($isMain);
    	$phone->setName($name);
    	$phone->save();
    }
    
    function hasPhone($number, $phone_type, $isMain = false, $name = "") {
    	$type_id = TelephoneTypes::getTelephoneTypeId($phone_type);
    	$obj = ContactTelephones::instance()->findOne(array('conditions' => array(
    			'contact_id=? AND telephone_type_id=? AND number=?',
    			$this->getId(), $type_id, $number
    	)));
    	 
    	return $obj instanceof ContactTelephone;
    }
    
    
	/**
     * 
     * Add webpage to the contact
     * @param string $url
     * @param int $web_type
     * @author Seba
     */
    function addWebpage($url, $web_type) {
    	$web = new ContactWebpage() ;
    	$web->setUrl($url);
    	$web->setWebTypeId(WebpageTypes::getWebpageTypeId($web_type));
    	$web->setContactId($this->getId());
    	$web->save();
    }
    
    function hasWebpage($url, $web_type) {
    	$type_id = WebpageTypes::getWebpageTypeId($web_type);
    	$obj = ContactWebpages::instance()->findOne(array('conditions' => array(
    			'contact_id=? AND web_type_id=? AND url=?',
    			$this->getId(), $type_id, $url
    	)));
    	 
    	return $obj instanceof ContactWebpage;
    }
    
    
    private static $pg_cache = array();
    
    /**
     * @author pepe
     * Returns true when user is super administrator
     */
    function isAdministrator() {
    	$type = parent::getUserType();
    	if (!$type) return false;
    	if (!array_var(self::$pg_cache, $type)) {
    		$pg = PermissionGroups::instance()->findById($type);
    		self::$pg_cache[$type] = $pg;
    	} else {
    		$pg = array_var(self::$pg_cache, $type);
    	}
    	$name = $pg->getName();
		return $name == 'Super Administrator';
    }
    
    function isModerator() {
    	$type = $this->getUserType();
    	if (!$type) return false;
    	if (!array_var(self::$pg_cache, $type)) {
    		$pg = PermissionGroups::instance()->findById($type);
    		self::$pg_cache[$type] = $pg;
    	} else {
    		$pg = array_var(self::$pg_cache, $type);
    	}
    	$name = $pg->getName();
		return $name == 'Administrator';
    }
    
    function isExecutive(){
    	$type = $this->getUserType();
    	if (!$type) return false;
    	if (!array_var(self::$pg_cache, $type)) {
    		$pg = PermissionGroups::instance()->findById($type);
    		self::$pg_cache[$type] = $pg;
    	} else {
    		$pg = array_var(self::$pg_cache, $type);
    	}
    	$name = $pg->getName();
		return $name == 'Executive';
    }
    
    function isManager(){
    	$type = $this->getUserType();
    	if (!$type) return false;
    	if (!array_var(self::$pg_cache, $type)) {
    		$pg = PermissionGroups::instance()->findById($type);
    		self::$pg_cache[$type] = $pg;
    	} else {
    		$pg = array_var(self::$pg_cache, $type);
    	}
    	$name = $pg->getName();
		return $name == 'Manager';
    }
    
    function isExecutiveGroup(){
    	return $this->isAdministrator()||$this->isManager()||$this->isModerator()||$this->isExecutive();
    }
    
    function isAdminGroup(){
    	return $this->isModerator()||$this->isAdministrator();
    }
    
    /**
     * @author mati
     * Enter description here ...
     */
    function getUserTypeName(){
    	$type = $this->getUserType();
    	if (!$type) return null;
    	if (!array_var(self::$pg_cache, $type)) {
    		$pg = PermissionGroups::instance()->findById($type);
    		self::$pg_cache[$type] = $pg;
    	} else {
    		$pg = array_var(self::$pg_cache, $type);
    	}
    	return $pg->getName();
    }
    

    function isGuest() {
    	return in_array($this->getUserTypeName(), array('Guest', 'Guest Customer', 'Non-Exec Director'));
    }
    
    
    function hasEmailAccounts() {
    	$mail_plugin_enabled = Plugins::instance()->isActivePlugin('mail');
    	if ($mail_plugin_enabled) {
	    	$accounts = MailAccountContacts::instance()->find(array('conditions' => '`contact_id` = '.$this->getId()));
	    	return is_array($accounts) && count($accounts) > 0;
    	}
    }    
    

    function isMemberOfOwnerCompany(){
    	return $this->getCompanyId() == owner_company()->getId(); 
    }
    
    
    function getArrayInfo() {
        $name = $this->getObjectName();
        if(user_config_option("listingContactsBy")){
            $name = $this->getDisplayName();
        } else {
            $name = $this->getReverseDisplayName();
        }
        $info = array(
            'id' => $this->getId(),
            'name' => $name,
            'cid' => $this->getCompanyId(),
            'img_url' => $this->getPictureUrl(),
            'role' => $this->getUserType(),
            'address'=>array_map(function($value){return $value->getArrayInfo();},$this->getAllAddresses()),
            'email'=>array_map(function($value){return $value->getArrayInfo();},$this->getAllEmails()),
            'phone'=>array_map(function($value){return $value->getArrayInfo();},$this->getAllPhones())
        );
        if ($this->getId() == logged_user()->getId()) $info['isCurrent'] = 1;
        return $info;
    }
    
    
    /**
     * Return path to the picture file. This function just generates the path, does not check if file really exists
     *
     * @access public
     * @param void
     * @return string
     */
    function getPicturePath($size = 'small') {
    	switch ($size) {
    		case 'small':
    			if (FileRepository::isInRepository($this->getPictureFileSmall())) {
    				return PublicFiles::getFilePath($this->getPictureFileSmall());
    			}
    		case 'medium':
    			if (FileRepository::isInRepository($this->getPictureFileMedium())) {
    				return PublicFiles::getFilePath($this->getPictureFileMedium());
    			}
    		case 'large':
    			if (FileRepository::isInRepository($this->getPictureFile())) {
    				return PublicFiles::getFilePath($this->getPictureFile());
    			}
    	}
    }
    
    
    /**
     * Return path to the picture file. This function just generates the path, does not check if file really exists
     *
     * @access public
     * @param void
     * @return string
     */
    function getPictureFileContent($size = 'small') {
    	switch ($size) {
    		case 'small':
    			if (FileRepository::isInRepository($this->getPictureFileSmall())) {
    				return FileRepository::getFileContent($this->getPictureFileSmall());
    			}
    		case 'medium':
    			if (FileRepository::isInRepository($this->getPictureFileMedium())) {
    				return FileRepository::getFileContent($this->getPictureFileMedium());
    			}
    		case 'large':
    			if (FileRepository::isInRepository($this->getPictureFile())) {
    				return FileRepository::getFileContent($this->getPictureFile());
    			}
    	}
    }// getPicturePath
    
	
    function getPictureUrl($size = 'small') {

		$default_img_file = null; Hook::fire('default_image_file', $this, $default_img_file);
    	
    	$url = null; Hook::fire('override_contact_picture_url', $this, $url);
    	if ($url != null) return $url;
    	
		if($default_img_file==null) $default_img_file = "default-avatar.png";

    	switch ($size) {
    		case 'small':
    			return ($this->getPictureFileSmall() != '' ? get_url('files', 'get_public_file', array('id' => $this->getPictureFileSmall())): get_image_url($default_img_file));
    		case 'medium':
    			return ($this->getPictureFileMedium() != '' ? get_url('files', 'get_public_file', array('id' => $this->getPictureFileMedium())): get_image_url($default_img_file));
    		case 'large':
    			return ($this->getPictureFile() != '' ? get_url('files', 'get_public_file', array('id' => $this->getPictureFile())): get_image_url($default_img_file));
    	}
	} // getPictureUrl
	
	
	/**
	 * Set contact picture from $source file
	 *
	 * @param string $source Source file
	 * @param integer $max_width Max picture widht
	 * @param integer $max_height Max picture height
	 * @param boolean $save Save user object when done
	 * @return string
	 */
	function setPicture($source, $fileType, $max_width = 50, $max_height = 50, $save = true) {
		if (!is_readable($source)) return false;

		do {
			$temp_file = CACHE_DIR . '/' . sha1(uniqid(rand(), true));
		} while(is_file($temp_file));

		Env::useLibrary('simplegd');

		$image = new SimpleGdImage($source);
		if ($image->getImageType() != IMAGETYPE_PNG) {
			
			$image->convertType(IMAGETYPE_PNG);
			$image->saveAs($temp_file, IMAGETYPE_PNG);
			$public_fileId = FileRepository::addFile($temp_file, array('type' => 'image/png', 'public' => true));
			
		} else {
			$public_fileId = FileRepository::addFile($source, array('type' => 'image/png', 'public' => true));
		}
		
		$this->generateAllSizePictures($public_fileId, $save);
		
		$result = true;

		// Cleanup
		if(!$result && $public_fileId) {
			FileRepository::deleteFile($public_fileId);
		}
		if (file_exists($temp_file)) {
			@unlink($temp_file);
		}

		return $public_fileId;
	} // setPicture
	
	
	function generateAllSizePictures($repository_id, $save = true) {
		try {
			if (!FileRepository::isInRepository($repository_id)) {
				return;
			}
			
			$result = array();
			
			$temp_file_name = CACHE_DIR . "/contact-" . $this->getId() . "_" . gen_id() . ".png";
			
			$content = FileRepository::getFileContent($repository_id);
			file_put_contents($temp_file_name, $content);
			
			// generate large image
			$rep_id = $this->generatePictureFile($temp_file_name, 600, str_replace('.png', '-large.png', $temp_file_name));
			if (is_null($rep_id)) {
				$rep_id = $repository_id;
			}
			$result['large'] = $rep_id;
			
			// generate medium image
			$rep_id = $this->generatePictureFile($temp_file_name, 200, str_replace('.png', '-medium.png', $temp_file_name));
			if (is_null($rep_id)) {
				$rep_id = $repository_id;
			}
			$result['medium'] = $rep_id;
			
			// generate small image
			$rep_id = $this->generatePictureFile($temp_file_name, 60, str_replace('.png', '-small.png', $temp_file_name));
			if (is_null($rep_id)) {
				$rep_id = $repository_id;
			}
			$result['small'] = $rep_id;
			
			$this->setPictureFile($result['large']);
			$this->setPictureFileMedium($result['medium']);
			$this->setPictureFileSmall($result['small']);
			if ($save) {
				$this->save();
			}
			
			@unlink($temp_file_name);
			
			return $result;
			
		} catch (Exception $e) {
			Logger::log_r("Error in Contact::generateAllSizePictures('$repository_id'): ".$e->getMessage());
			Logger::log_r($e->getTraceAsString());
		}
	}
	
	private function generatePictureFile($source_file, $max_size, $tmp_filename = "") {
		if (!$tmp_filename) {
			$tmp_filename = CACHE_DIR . "/" . gen_id() . ".png";
		}
		if (!is_file($source_file)) {
			return null;
		}
		if (!$max_size) {
			$max_size = 600;
		}
		Env::useLibrary('simplegd');
		$image = new SimpleGdImage($source_file);
		
		if ($image->getWidth() > $max_size || $image->getHeight() > $max_size) {
				
			if ($image->getWidth() > $image->getHeight()) {
				$w = $max_size;
				$ratio = $image->getHeight() / $image->getWidth();
				$h = $ratio * $w;
			} else {
				$h = $max_size;
				$ratio = $image->getWidth() / $image->getHeight();
				$w = $ratio * $h;
			}
			
			$data = file_get_contents($source_file);
			$vImg = imagecreatefromstring($data);
			$dstImg = imagecreatetruecolor($w, $h);
			// save transparency
			imagealphablending($dstImg, FALSE);
			imagesavealpha($dstImg, TRUE);
			
			imagecopyresampled($dstImg, $vImg, 0, 0, 0, 0, $w, $h, $image->getWidth(), $image->getHeight());
			imagepng($dstImg, $tmp_filename);
			imagedestroy($dstImg);
			
			$repo_id = FileRepository::addFile($tmp_filename, array('type' => 'image/png', 'public' => true));
			
			@unlink($tmp_filename);
			
			return $repo_id;
				
		} else {
			return null;
		}
	}
	
	
	/**
	 * Delete picture
	 *
	 * @param void
	 * @return null
	 */
	function deletePicture() {
		if($this->hasPicture()) {
			FileRepository::deleteFile($this->getPictureFile());
			FileRepository::deleteFile($this->getPictureFileMedium());
			FileRepository::deleteFile($this->getPictureFileSmall());
			$this->setPictureFile('');
			$this->setPictureFileMedium('');
			$this->setPictureFileSmall('');
		} // if
	} // deletePicture
	
	
	/**
	 * Return add user URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getAddUserUrl() {
		return get_url('contact', 'add', array('company_id' => $this->getId(), 'is_user' => 1, 'user_type' => 4));
	} // getAddUserUrl
	
	
	/**
	 * Return add group URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getAddGroupUrl() {
		return get_url('group', 'add', array('company_id' => $this->getId()));
	} // getAddUserUrl
	

	
	/**
	 * Check if specific user can update this profile
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	function canUpdateProfile(Contact $user) {
		if($this->getId() == $user->getId()) {
			return true;
		} // if
		if(can_manage_security(logged_user())) {
			return true;
		} // if
		return false;
	} // canUpdateProfile
	
	
	/**
	 * Check if specific $user can change $this user's password
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	function canChangePassword(Contact $user) {
		if($this->getId() == $user->getId()) {
			return true;
		}
		if(can_manage_security($user)) {
			// Only managers, admins and super admins can change lower roles passwords, Super admins can change all passwords
			if ($user->isAdminGroup() || $user->isManager()) {
				return $user->isAdministrator() || $this->getUserType() > $user->getUserType();
			}
		}
		return false;
	}
	
	
	/**
	 * Check if specific $user can change $this user's external tokens
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	function canChangeExternalToken(Contact $user) {
	    if(can_manage_security($user)) {
	        // Only managers, admins and super admins can change lower roles passwords, Super admins can change all passwords
	        if ($user->isAdminGroup() || $user->isManager()) {
	            return $user->isAdministrator() || $this->getUserType() > $user->getUserType();
	        }
	    }
	    return false;
	}
	
	
	/**
	 * Check if this user can update this users permissions
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	function canUpdatePermissions(Contact $user) {
		if (!$this->isUser()) return false;
		$actual_user_type = array_var(self::$pg_cache, $user->getUserType());
		if (!$actual_user_type)
			$actual_user_type = PermissionGroups::instance()->findOne(array("conditions" => "id = ".$user->getUserType()));
		
		$this_user_type = array_var(self::$pg_cache, $this->getUserType());
		if (!$this_user_type)
			$this_user_type = PermissionGroups::instance()->findOne(array("conditions" => "id = ".$this->getUserType()));

		//if current user type < user type OR current user is admin and user is admin OR current user is superadmin
		$can_change_type = $actual_user_type->getId() < $this_user_type->getId() || $user->isAdminGroup() && $this->isAdminGroup() && $actual_user_type->getId() <= $this_user_type->getId()  || $user->isAdministrator();
		
		return can_manage_security($user) && $can_change_type;
	} // canUpdatePermissions

	
	/**
	 * Return edit profile URL
	 *
	 * @param string $redirect_to URL where we need to redirect user when he updates profile
	 * @return string
	 */
	function getEditProfileUrl($redirect_to = null) {
		$attributes = array('id' => $this->getId());
		if(trim($redirect_to) <> '') {
			$attributes['redirect_to'] = str_replace('&amp;', '&', trim($redirect_to));
		} // if

		return get_url('account', 'edit_profile', $attributes);
	} // getEditProfileUrl

	
	/**
	 * Edit users password
	 *
	 * @param string $redirect_to URL where we need to redirect user when he updates password
	 * @return null
	 */
	function getEditPasswordUrl($redirect_to = null) {
		$attributes = array('id' => $this->getId());
		if(trim($redirect_to) <> '') {
			$attributes['redirect_to'] = str_replace('&amp;', '&', trim($redirect_to));
		} // if

		return get_url('account', 'edit_password', $attributes);
	} // getEditPasswordUrl
	
	
	/**
	 * Return edit preferences URL of this user
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getEditPreferencesUrl() {
		return get_url('contact', 'list_user_categories');
	} // getEditPreferencesUrl
	
	/**
	 * Return update user permissions page URL
	 *
	 * @param string $redirect_to
	 * @return string
	 */
	function getUpdatePermissionsUrl($redirect_to = null) {
		return get_url('contact', 'edit', array('id' => $this->getId(), 'active_tab' => 'permissions'));
		/*
		$attributes = array('id' => $this->getId());
		if(trim($redirect_to) <> '') {
			$attributes['redirect_to'] = str_replace('&amp;', '&', trim($redirect_to));
		} // if

		return get_url('account', 'update_permissions', $attributes);*/
	} // getUpdatePermissionsUrl

	
	function getEditExternalTokensUrl($redirect_to = null) {
	    $attributes = array('user_id' => $this->getId());
	    if(trim($redirect_to) != "") {
	        $attributes['redirect_to'] = str_replace('&amp;', '&', trim($redirect_to));
	    } // if
	    return get_url('account', 'edit_external_tokens', $attributes);
	} // getEditExternalTokensUrl
	
	
	function getDeleteExternalTokensUrl($redirect_to = null) {
	    $attributes = array('id' => $this->getId());
	    if(trim($redirect_to) != "") {
	        $attributes['redirect_to'] = str_replace('&amp;', '&', trim($redirect_to));
	    } // if
	    return get_url('account', 'delete_external_token', $attributes);
	} // getDeleteExternalTokensUrl
	
	
	function setUserType($type){
		parent::setUserType($type);
	}
	
	
	function getUserType(){
		$user_type = parent::getUserType();
		return $user_type;
	}
	
	
	/**
	 * Check if this user is company administration (used to check many other permissions). User must
	 * be part of the company and have is_admin stamp set to true
	 *
	 * @access public
	 * @param Contact $company
	 * @return boolean
	 */
	function isCompanyAdmin(Contact $company) {
		return ($this->getCompanyId() == $company->getId()) && $this->isAdminGroup();
	} // isCompanyAdmin
	
	
	/**
	 * Return all client companies
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	static function getClientCompanies() {
		return Contacts::instance()->findAll(array('conditions' => '`object_id` <> 1 AND `is_company` = 1'));
	} // getClientCompanies
	

	/**
	 * Returns true if specific user can add client company
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canAddClient(Contact $user) {
		return $user->isAccountOwner() || $user->isAdministrator($this);
	} // canAddClient
	
	
	/**
	 * Return number of company users
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function countUsers() {
		return Contacts::instance()->count('`company_id` = ' . DB::escape($this->getId()));
	} // countUsers
	
	/**
	 * Account owner is user account that was created when company website is created
	 *
	 * @param void
	 * @return boolean
	 */
	function isAccountOwner() {
		if(is_null($this->is_account_owner)) {
			$this->is_account_owner = $this->isMemberOfOwnerCompany() && (owner_company()->getCreatedById() == $this->getId());
		} // if
		return $this->is_account_owner;
	} // isAccountOwner
	
	
	/**
	 * Return delete URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDeleteUrl() {
		if ( $this->isUser()) {
			return get_url('account', 'delete_user', $this->getId());
		}else{
			return get_url('contact', 'delete', $this->getId());
		}
	} // getDeleteUrl
	
	
	/**
	 * Return edit URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getEditUrl() {
		$action = $this->isCompany()? 'edit_company': 'edit';
		return get_url('contact', $action, $this->getId());
	} // getEditUrl
	
	
	/**
	 * Return update avatar URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getEditPictureUrl() {
		return get_url('contact', 'edit_picture', $this->getId());
	} // getEditPictureUrl
	
	
	
	/**
	 * Check if this user has uploaded picture
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function hasPicture() {
		return (trim($this->getPictureFile()) <> '') && FileRepository::isInRepository($this->getPictureFile());
	} // hasPicture

	
	function getLocale() {
		$locale = user_config_option("localization", null, $this->getObjectId());
		return $locale ? $locale : DEFAULT_LOCALIZATION;
	}
	
	function getAccountUrl() {
		return get_url('contact', 'card', array('id'=>$this->getId()));
	}
	
	
	function getIconClass($large = false) {
		$class = 'ico-' . ($large ? "large-" : "") . ($this->getIsCompany() ? "company" : "contact");
		if ($this->getObject()->getTrashedById() > 0) $class .= "-trashed";
		else if ($this->getObject()->getArchivedById() > 0) $class .= "-archived";
		
		return $class;
	}
	
	function getDisableUrl() {
		return get_url('account', 'disable', array("id"=>$this->getId()));
	}

	
	private $pg_ids_cache = null;
	function getPermissionGroupIds() {
		if (is_null($this->pg_ids_cache)) {
			$this->pg_ids_cache = array_flat(DB::executeAll("SELECT permission_group_id FROM ".TABLE_PREFIX."contact_permission_groups WHERE contact_id = '".$this->getId()."'"));
		}
		return $this->pg_ids_cache;
	}
	
	
	
	
	// override job title attribute getter and setter
	function getJobTitle() {
		$cp = CustomProperties::instance()->findOne(array('conditions' => "code='job_title' AND object_type_id=".$this->manager()->getObjectTypeId()));
		if ($cp instanceof CustomProperty) {
			if ($cp->getIsDisabled()) {
				return "";
			}
			$cp_val = CustomPropertyValues::getCustomPropertyValue($this->getId(), $cp->getId());
			if ($cp_val instanceof CustomPropertyValue) {
				return $cp_val->getValue();
			} else {
				return "";
			}
		} else {
			return $this->getColumnValue('job_title');
		}
	}
	
	function setJobTitle($value) {
		$cp = CustomProperties::instance()->findOne(array('conditions' => "code='job_title' AND object_type_id=".$this->manager()->getObjectTypeId()));
		if ($cp instanceof CustomProperty) {
			$cp_val = CustomPropertyValues::getCustomPropertyValue($this->getId(), $cp->getId());
			if (!$cp_val instanceof CustomPropertyValue) {
				$cp_val = new CustomPropertyValue();
				$cp_val->setObjectId($this->getId());
				$cp_val->setCustomPropertyId($cp->getId());
			}
			$cp_val->setValue($value);
			$cp_val->save();
			return true;
			
		} else {
			return $this->setColumnValue('job_title', $value);
		}
	}
	



	function getUserTimezoneValue() {
		return Timezones::getTimezoneOffset($this->getUserTimezoneId());
	}
	
	function getUserTimezoneHoursOffset() {
		$offset_seconds = Timezones::getTimezoneOffset($this->getUserTimezoneId());
		$offset_hours = $offset_seconds / 3600;
	
		return $offset_hours;
	}
	
	/**
	 * Method overriden from BaseContact to calculate the timezone using 
	 * the timezones table and not reading the attribute "timezone" of the contact
	 */
	function getTimezone() {
		$offset_hours = $this->getUserTimezoneHoursOffset();
		return $offset_hours;
	}
	
	
	
	function getFixedColumnValue($column_name, $raw_data=false) {
		$value = null;
		switch ($column_name) {
			case 'email':
				$value = ContactEmails::instance()->findAll(array("conditions" => array("contact_id=?",$this->getId())));
				break;
			case 'phone':
				$value = ContactTelephones::instance()->findAll(array("conditions" => array("contact_id=?",$this->getId())));
				break;
			case 'address':
				$value = ContactAddresses::instance()->findAll(array("conditions" => array("contact_id=?",$this->getId())));
				break;
			case 'webpage':
				$value = ContactWebpages::instance()->findAll(array("conditions" => array("contact_id=?",$this->getId())));
				break;
			case 'company_id':
				if ($raw_data) {
					$value = $this->getCompanyId();
				} else {
					if ($this->getCompanyId() > 0) {
						$comp = $this->getCompany();
						if ($comp instanceof Contact) $value = $comp->getObjectName();
					}
				}
				break;
			case 'picture_file':
				if ($this->getPictureFile() != '') {
					$value = $this->getPictureUrl();
				}
				break;
			default:
				$value = $this->getColumnValue($column_name);
		}
		return $value;
	}

	function getBillingEmail() {
		$value = ContactEmails::instance()->findOne(array("conditions" => "contact_id = ".$this->getId()." AND is_main = 1"));
		if(empty($value)) return false;
		return ($value->getColumnValue('default_billing_email') != '') ? $value->getColumnValue('default_billing_email') : 0;
	}

	

	function getAllPhonesString() {
		$all_phones = $this->getAllPhones();
		$phone_numbers = array();
		foreach ($all_phones as $phone) {
			$phone_numbers[] = $phone->getNumber();
		}
		return implode(" - ", $phone_numbers);
	}

	function getAllAddressesString() {
		$addresses = array();
		$all_addresses = $this->getAllAddresses();
		foreach ($all_addresses as $address) {
			$addresses[] = $address->toString();
		}
		return implode("\n", $addresses);
	}

	function getAllEmailsString() {
		$emails = array();
		$all_emails = $this->getAllEmails();
		foreach ($all_emails as $email) {
			$emails[] = $email->getEmailAddress();
		}
		return implode(" - ", $emails);
	}
}
