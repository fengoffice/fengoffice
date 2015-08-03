<?php

/**
 * ContactPasswords class
 *
 * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
class ContactPasswords extends BaseContactPasswords {

	/**
	 * Return last 10 contact passwords
	 *
	 * @access public
	 * @param integer $contact_id
	 * @return array
	 */
	static function getLastTenContactPasswords($contact_id) {
		return ContactPasswords::findAll(array(
        'conditions' => array('`contact_id` = ?', $contact_id),
        'order' => 'password_date desc',
		'limit' => '10',
		)); // findAll
	} // getLastTenContactPasswords

	/**
	 * Return oldest contact password
	 *
	 * @access public
	 * @param integer $contact_id
	 * @return array
	 */
	static function getOldestContactPassword($contact_id) {
		return ContactPasswords::findAll(array(
        'conditions' => array('`contact_id` = ?', $contact_id),
        'order' => 'password_date',
		'limit' => '1',
		)); // findAll
	} // getOldestContactPassword

	/**
	 * Return newest contact password
	 *
	 * @access public
	 * @param integer $contact_id
	 * @return ContactPassword
	 */
	static function getNewestContactPassword($contact_id) {
		return ContactPasswords::findOne(array(
        'conditions' => array('`contact_id` = ?', $contact_id),
        'order' => 'password_date desc',
		'limit' => '1',
		)); // findAll
	} // getNewestContactPassword

	/**
	 * Return newest contact passwords for all contacts
	 *
	 * @access public
	 * @return array
	 */
	static function getNewestContactPasswords() {
		return ContactPasswords::findAll(array(
        'order' => 'password_date desc',
		'group by' => 'contact_id',
		)); // findAll
	} // getNewestContactPasswords

	// ---------------------------------------------------
	//  Validation functions
	// ---------------------------------------------------

	/**
	 * Check if password has valid min length
	 *
	 * @access public
	 * @param string $password
	 * @return boolean
	 */
	static function validateMinLength($password){
		$min_pass_length = config_option('min_password_length', 0);
		if(strlen($password) < $min_pass_length){
			return false;
		}
		return true;
	}

	/**
	 * Check if password has valid amount of numerical characters
	 *
	 * @access public
	 * @param string $password
	 * @return boolean
	 */
	static function validateNumbers($password){
		$pass_numbers = config_option('password_numbers', 0);
		$numerical_chars = array();
		preg_match_all('/[0-9]/', $password, $numerical_chars);
		$numerical_chars_count = count($numerical_chars[0]);
		if($numerical_chars_count < $pass_numbers){
			return false;
		}
		return true;
	}

	/**
	 * Check if password has valid amount of uppercase characters
	 *
	 * @access public
	 * @param string $password
	 * @return boolean
	 */
	static function validateUppercaseCharacters($password){
		$pass_uppercase = config_option('password_uppercase_characters', 0);
		$uppercase_chars = array();
		preg_match_all('/[A-Z]/', $password, $uppercase_chars);
		$uppercase_chars_count = count($uppercase_chars[0]);
		if($uppercase_chars_count < $pass_uppercase){
			return false;
		}
		return true;
	}

	/**
	 * Check if password has valid amount of metacharacters
	 *
	 * @access public
	 * @param string $password
	 * @return boolean
	 */
	static function validateMetacharacters($password){
		$pass_metacharacters = config_option('password_metacharacters', 0);
		$metachars = array();
		preg_match_all('/[\\[|\\]|\\&|\\#|\\^|\\$|\\\|\\%|\\@|\\/|\\(|\\)|\\?|\\+|\\{|\\<|\\>|\\-|\\}|,|\\.|\\=|\\!|\\<|\\>|\\:|\\;|\\*]/', $password, $metachars);
		$metachars_count = count($metachars[0]);
		if($metachars_count < $pass_metacharacters){
			return false;
		}
		return true;
	}

	/**
	 * Check if password was used on last ten passwords
	 *
	 * @access public
	 * @param integer $contact_id
	 * @param string $password
	 * @return boolean
	 */
	static function validateAgainstPasswordHistory($contact_id, $password){
		if(config_option('validate_password_history', 0) == 1){
			$passwords = self::getLastTenContactPasswords($contact_id);
			foreach($passwords as $contact_pass){
				if(cp_decrypt($contact_pass->getPassword(), $contact_pass->getPasswordDate()->getTimestamp()) == $password){
					return false;
				}
			}
		}
		return true;
	}
	
	 /**
	 * Check if password has more than 3 differences with last 10 passwords
	 *
	 * @access public
	 * @param integer $contact_id
	 * @param string $password
	 * @return boolean
	 */
	static function validateCharDifferences($contact_id, $password){		
		if(config_option('new_password_char_difference', 0) == 1){
			$passwords = self::getLastTenContactPasswords($contact_id);
			foreach($passwords as $contact_pass){
				$storedPass = cp_decrypt($contact_pass->getPassword(), $contact_pass->getPasswordDate()->getTimestamp());
				$differences = abs(strlen($storedPass) - strlen($password));
				$minLength = min(array(strlen($storedPass), strlen($password)));
				
				for($i=0; $i<$minLength; $i++){
					if(substr($password, $i, 1) != substr($storedPass, $i, 1)){
						$differences++;
					}
				}
				if($differences < 3){
					return false;
				}
			}
		}		
		return true;
	}

	/**
	 * Check if password is fulfills password options
	 *
	 * @access public
	 * @param string $password
	 * @return boolean
	 */
	static function validatePassword($password){
		if(self::validateMinLength($password) &&
		self::validateNumbers($password) &&
		self::validateUppercaseCharacters($password) &&
		self::validateMetacharacters($password)){
			return true;
		}
		return false;
	}

	/**
	 * Check if current contact password has expired
	 *
	 * @access public
	 * @param integer $contact_id
	 * @return boolean
	 */
	static function isContactPasswordExpired($contact_id){
		$contact = Contacts::findById($contact_id);
		if($contact instanceof Contact){
			$current_password = self::getNewestContactPassword($contact_id);
			$password_expiration_days = config_option('password_expiration', 0);
			if ($password_expiration_days > 0){
				$diff_days = self::getContactPasswordDays($current_password);
				if($diff_days >= $password_expiration_days){
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Return number of days of current contact password
	 *
	 * @access public
	 * @param ContactPassword $contact_password
	 * @return integer
	 */
	static function getContactPasswordDays($contact_password){
		$uts['now'] = strtotime(DateTimeValueLib::now()->toMySQL());
		$uts['passDate'] = strtotime($contact_password->getPasswordDate()->toMySQL());

		if( $uts['now']!==-1 && $uts['passDate']!==-1 ){
			if( $uts['now'] >= $uts['passDate'] ){
				$diff = $uts['now'] - $uts['passDate'];
				if($days=intval((floor($diff/86400)))){
					return $days;
				}
			}
		}		
		return 0;
	}

	/**
	 * Return a random password following password rules defined by administrator
	 *
	 * @access public
	 * @return string
	 */
	static function generateRandomPassword(){
		$min_pass_length = max(config_option('min_password_length',0), 13);
		$pass_numbers = config_option('password_numbers',0);
		$pass_uppercase = config_option('password_uppercase_characters',0);
		$pass_metacharacters = config_option('password_metacharacters',0);

		$password = "";

		// define possible characters
		$number_chars = "0123456789";
		$uppercase_chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$metacharacters = "[]^$\\\/()?+{}|.=!<>:*";
		$characters = "abcdefghijklmnopqrstuvwxyz";

		for($i = 0; $i < $pass_numbers; $i++){
			$char = substr($number_chars, mt_rand(0, strlen($number_chars)-1), 1);
			$password .= $char;
		}

		for($i = 0; $i < $pass_uppercase; $i++){
			$char = substr($uppercase_chars, mt_rand(0, strlen($uppercase_chars)-1), 1);
			$password .= $char;
		}

		for($i = 0; $i < $pass_metacharacters; $i++){
			$char = substr($metacharacters, mt_rand(0, strlen($metacharacters)-1), 1);
			$password .= $char;
		}

		if(strlen($password) < $min_pass_length || strlen($password) == 0){
			do{
				$password .= substr($characters, mt_rand(0, strlen($characters)-1), 1);
			} while(strlen($password) < $min_pass_length);
		}

		return str_shuffle($password);
	}


	/**
	 * Send password expiration reminders to contacts
	 *
	 * @access public
	 * @return int
	 */
	static function sendPasswordExpirationReminders(){
		$sent = 0;
		$password_expiration_days = config_option('password_expiration', 0);
		$password_expiration_notification = config_option('password_expiration_notification', 0);
		$contact_passwords = ContactPasswords::getNewestContactPasswords();
		foreach($contact_passwords as $password){
			$diff_days = self::getContactPasswordDays($password);
			if($diff_days == ($password_expiration_days - $password_expiration_notification)){
				$contact = Contacts::findById($password->getContactId());
				if($contact instanceof Contact){
					if(Notifier::passwordExpiration($contact, $password_expiration_notification)){
						$sent++;
					}
				}
			}
		}
		return $sent;
	}
	
	static function clearByContact($contact) {
		return self::delete('`contact_id` = ' . $contact->getId());
	}

}

?>