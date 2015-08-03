<?php

/**
 * Contacts class
 *
 * @author Carlos Palma <chonwil@gmail.com>, Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
class Contacts extends BaseContacts {
	
	public function __construct() {
		parent::__construct ();
		$this->object_type_name = 'contact';
	}

	
	/**
	 * Returns an array containing only the contacts that logged_user can read.
	 *
	 * @return array
	 */
	function getAllowedContacts($extra_conds = null) {
		$result = array();
		
		$can_manage_contacts = can_manage_contacts(logged_user());
		
		$conditions = $extra_conds ? $extra_conds : "";
		if (!$can_manage_contacts) {
			$conditions .= ($extra_conds ? " AND " : "") . " e.object_id IN (
				SELECT st.object_id FROM ".TABLE_PREFIX."sharing_table st WHERE st.group_id IN (
					SELECT pg.permission_group_id FROM ".TABLE_PREFIX."contact_permission_groups pg WHERE pg.contact_id = ".logged_user()->getId()."
				)
			)";
		}
		
		$contacts = Contacts::instance()->findAll(array('conditions' => $conditions));
		return $contacts;
	}
	
	function countAllowedContacts() {
		$conditions = "";
		if (!can_manage_contacts(logged_user())) {
			$conditions .= "e.user_type>0 OR e.object_id IN (
				SELECT st.object_id FROM ".TABLE_PREFIX."sharing_table st WHERE st.group_id IN (
					SELECT pg.id FROM ".TABLE_PREFIX."permission_groups pg WHERE pg.type='permission_groups' AND pg.contact_id = ".logged_user()->getId()."
				)
			)";
		}
		return Contacts::instance()->count($conditions);
	}
	
	/**
	 * @param string $extra_conditions: Extra conditions for users query (default '')
	 * @param boolean $include_disabled: Include or not the disabled users (default false)
	 * @param string $order_by: Query order (default "first_name, surname, username")
	 * @return array of Contact
	 */
	static function getAllUsers($extra_conditions = "", $include_disabled = false, $order_by = "") {
		if ($order_by == "") $order_by = "first_name, surname, username";
		if (!$include_disabled) $extra_conditions .= " AND `disabled` = 0";
		return self::findAll(array("conditions" => "`user_type` <> 0 $extra_conditions", "order" => $order_by));
	}
	
	
	/**
	 * Return Contact object by email
	 *
	 * @param string $email
	 * @return Contact
	 */
	static function getByEmail($email, $id_contact = 0) {
		$contact_email = ContactEmails::findOne(array('conditions' => array("`email_address` = ? AND `contact_id` <> ? AND (SELECT c.is_company FROM ".TABLE_PREFIX."contacts c WHERE c.object_id=contact_id)=0", $email, $id_contact)));
		if (!is_null($contact_email))
			return self::findById($contact_email->getContactId());
		return null;
	} // getByEmail
        
        /**
	 * Return Contact object by email
	 *
	 * @param string $email
	 * @return Contact
	 */
	static function getByEmailCheck($email, $id_contact = 0, $contact_type = '') {
		if (is_null($email) || $email == '') return null;
		
		$contact_type_str = "";
		if ($contact_type == 'contact') {
			$contact_type_str = " AND is_company=0";
		} else if ($contact_type == 'company') {
			$contact_type_str = " AND is_company=1";
		}
		
		$contact_email = Contacts::findOne(array(
			'conditions' => array("`email_address` = ? AND `contact_id` <> ? $contact_type_str", $email, $id_contact),
			'join' => array(
				'table' => ContactEmails::instance()->getTableName(),
				'jt_field' => 'contact_id',
				'e_field' => 'object_id',
			)
		));
		if (!is_null($contact_email))
			return self::findById($contact_email->getObjectId());
		return null;
	} // getByEmail
	
	
	/**
	 * Return user by username
	 *
	 * @access public
	 * @param string $username
	 * @return Contact
	 */
	static function getByUsername($username) {
		return self::findOne(array(
        	'conditions' => array('`username` = ?', $username)
		)); // array
	} // getByUsername
	

	/**
	 * Return all companies that have system users
	 *
	 * @param void
	 * @return array
	 */
	static function getCompaniesWithUsers() {
		$companies = self::findAll(array('conditions' => array("`is_company` = 1 AND EXISTS (SELECT c.object_id FROM ".TABLE_PREFIX."contacts c WHERE c.company_id=o.id AND c.user_type > 0)")));
		return is_array($companies) ? $companies : array();
	} // getCompaniesWithUsers
	
	
	/**
	 * Return contacts grouped by company
	 *
	 * @param void
	 * @return array
	 */
	static function getGroupedByCompany($include_disabled = true) {
		$companies = self::findAll(array("conditions" => "is_company = 1 AND object_id IN (SELECT company_id FROM ".TABLE_PREFIX."contacts WHERE user_type>0 ".($include_disabled ? "" :"AND disabled=0").")"));
		if(!is_array($companies) || !count($companies)) {
			//return null;
		}

		$result = array();
		$comp_ids = array(0);
		foreach ($companies as $company) {
			$comp_ids[] = $company->getId();
			$result[$company->getId()] = array(
            	'details' => $company,
            	'users' => array(),
			);
		}
		
		$company_users = Contacts::findAll(array('order' => 'company_id, first_name, surname', 'conditions' => 'user_type<>0 AND company_id IN ('.implode(',', $comp_ids).')' . ($include_disabled ? "" : " AND disabled=0")));
		foreach ($company_users as $user) {
			$result[$user->getCompanyId()]['users'][] = $user;
		}

		$res = array();
		foreach ($result as $comp_info) {
			if (array_var($comp_info, 'details') instanceof Contact) {
				$res[$comp_info['details']->getObjectName()] = $comp_info;
			}
		}
		$result = $res;

		
		$no_company_users = Contacts::getAllUsers("AND `company_id` = 0", $include_disabled);
		if (count($no_company_users) > 0) {
			$result[lang('without company')] = array('details' => null, 'users' => $no_company_users);
		}

		return count($result) ? $result : null;
	} // getGroupedByCompany

	
	static function getVisibleCompanies(Contact $user, $additional_conditions = null){
		$conditions = $additional_conditions ? "`is_company` = 1 AND $additional_conditions" : "`is_company` = 1";
		//FIXME 
		return self::findAll(array('conditions' => $conditions));
	}
	
	function getRangeContactsByBirthday($from, $to, $member_ids=null) {
		if (!$from instanceof DateTimeValue || !$to instanceof DateTimeValue || $from->getTimestamp() > $to->getTimestamp()) {
			return array();
		}
		
		$from = new DateTimeValue($from->getTimestamp());
		$from->beginningOfDay();
		$to = new DateTimeValue($to->getTimestamp());
		$to->endOfDay();
		$year1 = $from->getYear();
		$year2 = $to->getYear();
		if ($year1 == $year2) {
			$condition = 'DAYOFYEAR(`birthday`) >= DAYOFYEAR(' . DB::escape($from) . ')' . ' AND DAYOFYEAR(`birthday`) <= DAYOFYEAR(' . DB::escape($to) . ')';
		} else if ($year2 - $year1 == 1) {
			$condition = '(DAYOFYEAR(`birthday`) >= DAYOFYEAR(' . DB::escape($from) . ')' . ' OR DAYOFYEAR(`birthday`) <= DAYOFYEAR(' . DB::escape($to) . '))';
		} else {
			$condition = "`birthday` <> '0000-00-00 00:00:00'";
		}
		
		if (!is_null($member_ids) && count($member_ids) > 0) {
			$condition .= " AND object_id IN (SELECT om.object_id FROM ".TABLE_PREFIX."object_members om WHERE om.member_id IN (".implode(',', $member_ids)."))";
		}
		
		return $this->getAllowedContacts($condition);
	}

	static function getContactFieldNames() {
		return array('contact[first_name]' => lang('first name'),
			'contact[surname]' => lang('surname'), 
			'contact[email]' => lang('email address'),
			'contact[company_id]' => lang('company'),

			'contact[w_web_page]' => lang('website'), 
			'contact[w_address]' => lang('address'),
			'contact[w_city]' => lang('city'),
			'contact[w_state]' => lang('state'),
			'contact[w_zipcode]' => lang('zipcode'),
			'contact[w_country]' => lang('country'),
			'contact[w_phone_number]' => lang('phone'),
			'contact[w_phone_number2]' => lang('phone 2'),
			'contact[w_fax_number]' => lang('fax'),
			'contact[w_assistant_name]' => lang('assistant'),
			'contact[w_assistant_number]' => lang('assistant number'),
			'contact[w_callback_number]' => lang('callback'),
			
			'contact[h_web_page]' => lang('website'),
			'contact[h_address]' => lang('address'),
			'contact[h_city]' => lang('city'),
			'contact[h_state]' => lang('state'),
			'contact[h_zipcode]' => lang('zipcode'),
			'contact[h_country]' => lang('country'),
			'contact[h_phone_number]' => lang('phone'),
			'contact[h_phone_number2]' => lang('phone 2'),
			'contact[h_fax_number]' => lang('fax'),
			'contact[h_mobile_number]' => lang('mobile'),
			'contact[h_pager_number]' => lang('pager'),
			
			'contact[o_web_page]' => lang('website'),
			'contact[o_address]' => lang('address'),
			'contact[o_city]' => lang('city'),
			'contact[o_state]' => lang('state'),
			'contact[o_zipcode]' => lang('zipcode'),
			'contact[o_country]' => lang('country'),
			'contact[o_phone_number]' => lang('phone'),
			'contact[o_phone_number2]' => lang('phone 2'),
			'contact[o_birthday]' => lang('birthday'),
			'contact[email2]' => lang('email address 2'),
			'contact[email3]' => lang('email address 3'),
			'contact[job_title]' => lang('job title'),
			'contact[department]' => lang('department')
		);
	}
	
	
	static function getCompanyFieldNames() {
		return array('company[first_name]' => lang('name'),
			'company[address]' => lang('address'),
			'company[city]' => lang('city'),
			'company[state]' => lang('state'),
			'company[zipcode]' => lang('zipcode'),
			'company[country]' => lang('country'),
			'company[phone_number]' => lang('phone'),
			'company[fax_number]' => lang('fax'),
			'company[email]' => lang('email address'),
			'company[homepage]' => lang('homepage'),
		);
	}
	
	
	/**
	 * Return owner company
	 *
	 * @access public
	 * @param void
	 * @return Company
	 */
	static function getOwnerCompany() {
		
		$owner_company = null;
		if (GlobalCache::isAvailable()) {
			$owner_company = GlobalCache::get('owner_company', $success);
			if ($success && $owner_company instanceof Contact) {
				return $owner_company;
			}
		}

		$owner_company = Contacts::findOne(array(
			"conditions" => " is_company > 0",
			"limit" => 1,
			"order" => "object_id ASC"
		));
		
		if (GlobalCache::isAvailable()) {
			GlobalCache::update('owner_company', $owner_company);
		}
		
		return $owner_company;
	} // getOwnerCompany
	
	
	/**
	 * Check if specific token already exists in database
	 *
	 * @param string $token
	 * @return boolean
	 */
	static function tokenExists($token) {
		return self::count(array('`token` = ?', $token)) > 0;
	} // tokenExists
	

	/**
	 * Validate unique email.
	 * Accepets id param when editing contact (and not chaging email )
	 * @param unknown_type $email
	 * @param unknown_type $id
	 */
	static function validateUniqueEmail ($email, $id = null, $contact_type = "") {
		$email = trim($email);
		if ($id) {
			$id_cond = " AND o.id <> $id ";
		}else{
			$id_cond = "" ;
		} 	
		
		if (config_option('check_unique_mail_contact_comp')) {
			$contact_type = "";
		}
		$contact_type_cond = "";
		if ($contact_type == 'contact') {
			$contact_type_cond = " AND c.is_company=0";
		} else if ($contact_type == 'company') {
			$contact_type_cond = " AND c.is_company=1";
		}
		
		$sql = "
			SELECT DISTINCT(contact_id) FROM ".TABLE_PREFIX."contact_emails ce 
			INNER JOIN ".TABLE_PREFIX."objects o ON  ce.contact_id = o.id
			INNER JOIN ".TABLE_PREFIX."contacts c ON  c.object_id = o.id
			WHERE 
				o.archived_by_id = 0 AND 
				o.trashed_by_id = 0 AND 
				ce.email_address = '$email'
				$id_cond
				$contact_type_cond
				LIMIT 1 ";
		
		$res  = DB::execute($sql);
		return !(bool)$res->numRows();
	}
	
	
	

	/**
	 * Validate unique email.
	 * Accepets id param when editing contact (and not chaging email )
	 * @param unknown_type $email
	 * @param unknown_type $id
	 */
	static function validateUniqueUsername ($username, $id = null) {
		if ($id) {
			$id_cond = " AND o.id <> $id ";
		}else{
			$id_cond = "" ;
		} 	
		
		$sql = "
			SELECT distinct(object_id)
			FROM ".TABLE_PREFIX."contacts c 
			INNER JOIN ".TABLE_PREFIX."objects o ON o.id = c.object_id
			WHERE
			  o.archived_by_id = 0 AND		
			  o.trashed_by_id = 0 AND
			  username = '$username' 
			  $id_cond
			  LIMIT 1 ";
	
		
		$res  = DB::execute($sql);
		return !(bool)$res->numRows();
	}
	
	/**
	 * Do a first validation directly from parameters (before the object is loading)
	 * 
	 * @param array $attributes
	 */
	static function validate($attributes, $id = null) {
		$errors = array() ;
		//contact form 

/* URL validations removed		
		if (trim($attributes['w_web_page']) && !preg_match(URL_FORMAT, $attributes['w_web_page'])){
			$errors[] = lang("invalid webpage");			
		}
		
		//company form
		if (trim($attributes['homepage']) && !preg_match(URL_FORMAT, $attributes['homepage'])){
			$errors[] = lang("invalid webpage");			
		}
*/		
		if (trim($attributes['email']) && !self::validateUniqueEmail(trim($attributes['email']), $id, array_var($attributes, 'contact_type'))){
			$errors[] = lang("email address must be unique");
		}
		
		if (trim($attributes['email']) &&  !preg_match(EMAIL_FORMAT, trim($attributes['email']))) {
			$errors[] = lang("invalid email");
		}
		if(is_array($errors) && count($errors)) {
			throw new DAOValidationError(self::instance(), $errors);
		} 
	}
	
	/**
	 * Do a first validation directly from parameters (before the object is loading)
	 * 
	 * @param array $attributes
	 */
	static function validateUser($attributes, $id = null) {
		$errors = array() ;

		if (trim($attributes['email']) && !self::validateUniqueEmail($attributes['email'], $id, 'contact')){
			$errors[] = lang("email address must be unique");
		}
		
		if (trim($attributes['email']) &&  !preg_match(EMAIL_FORMAT, trim($attributes['email']))) {
			$errors[] = lang("invalid email");
		}

		if (trim($attributes['username']) && !self::validateUniqueUsername($attributes['username'], $id) ) {
			$errors[] = lang("username must be unique");
		}
		
		if(is_array($errors) && count($errors)) {
			throw new DAOValidationError(self::instance(), $errors);
		} 
	}
        
        function getUserDisplayName($user_id) {
		$user = Contacts::findById($user_id);
		if ($user) {
			return $user->getDisplayName();
		} else {
			$log = ApplicationLogs::findOne(array('conditions' => "`rel_object_id` = '$user_id' AND `action` = 'add'"));
			if ($log) return $log->getObjectName();
			else return lang('n/a');
		}
	}
	
} // Contacts

