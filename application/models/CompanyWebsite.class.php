<?php

/**
 * Company website class
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
final class CompanyWebsite {

	/** Name of the cookie / session var where we save session_id **/
	const USER_SESSION_ID_VAR = 'user_session_id';

	/**
	 * Owner company
	 *
	 * @var Contact
	 */
	private $company;

	/**
	 * Logged user
	 *
	 * @var Contact
	 */
	private $logged_user;

	/**
	 * Enter description here ...
	 * @var unknown_type
	 */
	private $context ;
	
	
	
	/**
	 * Init company website environment
	 *
	 * @access public
	 * @param void
	 * @return null
	 * @throws Error
	 */
	function init() {
		if(isset($this) && ($this instanceof CompanyWebsite)) {
			$this->initCompany();
			$this->initLoggedUser();
			$this->initContext();
		} else {
			CompanyWebsite::instance()->init();
		} // if
	} // init

	/**
	 * Init company based on subdomain
	 *
	 * @access public
	 * @param string
	 * @return null
	 * @throws Error
	 */
	private function initCompany() {
		$company = Contacts::getOwnerCompany();
		if(!($company instanceof Contact)) {
			throw new OwnerCompanyDnxError();
		}

		$owner = null;
		if (GlobalCache::isAvailable()) {
			$owner = GlobalCache::get('owner_company_creator', $success);
		}
		if (!($owner instanceof Contact)) {
			$owner = $company->getCreatedBy();
			// Update cache if available
			if ($owner instanceof Contact && GlobalCache::isAvailable()) {
				GlobalCache::update('owner_company_creator', $owner);
			}
		}
		
		if(!($owner instanceof Contact)) {
			throw new AdministratorDnxError();
		}

		$this->setCompany($company);
	} // initCompany

	private function initContext() {
		$context_plain = array_var($_GET, 'context');
		$context = build_context_array($context_plain);
		$this->setContext($context) ;
	} // initContext
	
	/**
	 * This function will use session ID from session or cookie and if presend log user
	 * with that ID. If not it will simply break.
	 *
	 * When this function uses session ID from cookie the whole process will be treated
	 * as new login and users last login time will be set to current time.
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	private function initLoggedUser() {
        
        //Hack for API Auth & Magic login!
        if((isset($_REQUEST['auth']) && !empty($_REQUEST['auth'])) || array_var($_REQUEST, 'm') == "login")
        {
            if(array_var($_REQUEST, 'm') != "login"){
                $contact = Contacts::findAll(array("conditions" => "`token` = '".$_REQUEST['auth']. "'"));
                $contact = $contact[0];
            }else{
                $username = urldecode($_REQUEST['username']);
                $password = $_REQUEST['password'];
                if (preg_match(EMAIL_FORMAT, $username)) {
                        $contact = Contacts::getByEmail($username);
                } else {
                        $contact = Contacts::getByUsername($username);
                }
                if($contact)
                {
                    if(!$contact->isValidPassword($password))
                        die('API Response: Invalid password.');
                }else{
                    die('API Response: Invalid username.');
                }
            }
            
            if($contact instanceof Contact)
            {
                $this->logUserIn($contact, false);
                if(array_var($_REQUEST, 'm') == "login")
                {
                    $temp = array(
                    	'token' => $contact->getToken(), 
                    	'username' => $contact->getUsername(),
                    	'user_id' =>  $contact->getId(),
                    	'company' => owner_company()->getName()
                    );
                    echo json_encode($temp);
                    exit;
                }                    
            }
            else
                die('API Response: Invalid authorization code.');
        }	
        
        $user_id       = Cookie::getValue('id');
		$twisted_token = Cookie::getValue('token');
		$remember      = (boolean) Cookie::getValue('remember', false);
		
		//check if thers a croos domain cookie
		if(empty($user_id) || empty($twisted_token)){
			$user_id       = Cookie::getValue('idCross');
			$twisted_token = Cookie::getValue('tokenCross');
		}
		

		if(empty($user_id) || empty($twisted_token)) {
			return false; // we don't have a user
		} // if

		$user = Contacts::findById($user_id);
		if(!($user instanceof Contact)) {
			return false; // failed to find user
		} // if
		if(!$user->isValidToken($twisted_token)) {
			return false; // failed to validate token
		} // if

		$last_act = $user->getLastActivity();
		if ($last_act instanceof DateTimeValue) {
			$session_expires = $last_act->advance(SESSION_LIFETIME, false);
		}
		if(!$last_act instanceof DateTimeValue || $session_expires!=null && DateTimeValueLib::now()->getTimestamp() < $session_expires->getTimestamp()) {
			$this->setLoggedUser($user, $remember, true);
		} else {
			$this->logUserIn($user, $remember);
		} // if
		 
		
	} // initLoggedUser

	// ---------------------------------------------------
	//  Utils
	// ---------------------------------------------------

	/**
	 * Log user in
	 *
	 * @access public
	 * @param Contact $user
	 * @param boolean $remember
	 * @return null
	 */
	function logUserIn(Contact $user, $remember = false) {
		$this->setLoggedUser($user, $remember, true);
	} // logUserIn

	/**
	 * Log out user
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function logUserOut() {
		$this->logged_user = null;
		Cookie::unsetValue('id');
		Cookie::unsetValue('token');
		Cookie::unsetValue('remember');
		
		//check if thers a cross domain cookie
		$user_id       = Cookie::getValue('idCross');
		$twisted_token = Cookie::getValue('tokenCross');
		if(!empty($user_id) || !empty($twisted_token)){
			$local_domain = parse_url(ROOT_URL, PHP_URL_HOST);
			
			if(($pos = strpos($local_domain, '.')) !== false){
				$local_domain = substr($local_domain, $pos);
			}
						
			$domain = defined('COOKIE_CROSS_DOMAIN') ? COOKIE_CROSS_DOMAIN : $local_domain;
			
			//croos
			Cookie::setValue('idCross',false,null,$domain);
			Cookie::setValue('tokenCross',false,null,$domain);
			//local
			Cookie::unsetValue('idCross');
			Cookie::unsetValue('tokenCross');
		}
		
		if(session_id() != "") {
			@session_destroy();
		}
	} // logUserOut

	// ---------------------------------------------------
	//  Getters and setters
	// ---------------------------------------------------

	/**
	 * Get company
	 *
	 * @access public
	 * @param null
	 * @return Company
	 */
	function getCompany() {
		return $this->company;
	} // getCompany

	/**
	 * Set company value
	 *
	 * @access public
	 * @param Company $value
	 * @return null
	 */
	function setCompany(Contact $value) {
		$this->company = $value;
	} // setCompany

	/**
	 * Get logged_user
	 *
	 * @access public
	 * @param null
	 * @return User
	 */
	function getLoggedUser() {
		return $this->logged_user;
	} // getLoggedUser

	/**
	 * Set logged_user value
	 *
	 * @access public
	 * @param Contact $value
	 * @param boolean $remember Remember this user for 2 weeks (configurable)
	 * @param DateTimeValue $set_last_activity_time Set last activity time. This property is turned off in case of feed
	 *   login for instance
	 * @return null
	 * @throws DBQueryError
	 */
	function setLoggedUser(Contact $user, $remember = false, $set_last_activity_time = true, $set_cookies = true) {
		if($set_last_activity_time) {
			$last_activity_mod_timestamp = array_var($_SESSION, 'last_activity_mod_timestamp', null);
			if (!isset($_SESSION['last_activity_updating']) && (!$last_activity_mod_timestamp || $last_activity_mod_timestamp < time() - 60 * 10)) {
				$_SESSION['last_activity_updating'] = true;
				
				$now = DateTimeValueLib::now() ;
				if(is_null($user->getLastActivity())) {
					$last_visit = $now;
				} else {
					$last_visit = $user->getLastActivity();
				}
				
				$sql = "UPDATE ".TABLE_PREFIX."contacts SET last_activity = '".$now->toMySQL()."',
				 		last_visit = '".($last_visit instanceof DateTimeValue ? $last_visit->toMySQL() : EMPTY_DATETIME)."', last_login='".$now->toMySQL()."'
				 		WHERE object_id = ".$user->getId();
				DB::execute($sql);
				
				$_SESSION['last_activity_mod_timestamp'] = time();
				unset($_SESSION['last_activity_updating']);
			}
		}

		if ($set_cookies) {
			$expiration = $remember ? REMEMBER_LOGIN_LIFETIME : SESSION_LIFETIME;
	
			Cookie::setValue('id', $user->getId(), $expiration);
			Cookie::setValue('token', $user->getTwistedToken(), $expiration);
	
			if($remember) {
				Cookie::setValue('remember', 1, $expiration);
			} else {
				Cookie::unsetValue('remember');
			} // if
		}

		$this->logged_user = $user;
	} // setLoggedUser


	/**
	 * Return single CompanyWebsite instance
	 *
	 * @access public
	 * @param void
	 * @return CompanyWebsite
	 */
	static function instance() {
		static $instance;
		if(!($instance instanceof CompanyWebsite)) {
			$instance = new CompanyWebsite();
		} // if
		return $instance;
	} // instance
	
	
	function getContext() {
		return $this->context ;		
	}
	
	function setContext($context) {
		$this->context = $context ;
	}

} // CompanyWebsite
