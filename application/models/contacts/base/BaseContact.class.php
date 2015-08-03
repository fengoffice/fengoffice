<?php

  /**
  * BaseContact class
  *
  * @author Carlos Palma <chonwil@gmail.com>
  */
    abstract class BaseContact extends ContentDataObject {
  	
  	 	
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------
  
	
    /**
    * Return value of 'id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getObjectId() {
      return $this->getColumnValue('object_id');
    } // getObjectId()
    
    
    /**
    * Set value of 'id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setObjectId($value) {
      return $this->setColumnValue('object_id', $value);
    } // setObjectId() 

    /**
    * Return value of 'first_name' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getFirstName() {
      return $this->getColumnValue('first_name');
    } // getFirstName()
    
    /**
    * Set value of 'first_name' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setFirstName($value) {
      return $this->setColumnValue('first_name', $value);
    } // setFirstName() 

    /**
    * Return value of 'surname' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getSurname() {
      return $this->getColumnValue('surname');
    } // getSurname()
    
    /**
    * Set value of 'surname' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setSurname($value) {
      return $this->setColumnValue('surname', $value);
    } // setSurname() 

    /**
    * Return value of 'company_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getCompanyId() {
      return $this->getColumnValue('company_id');
    } // getCompanyId()
    
    /**
    * Set value of 'company_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setCompanyId($value) {
      return $this->setColumnValue('company_id', $value);
    } // setCompanyId() 
    
    
    /**
    * Return value of 'is_company' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getIsCompany() {
      return $this->getColumnValue('is_company');
    } // getIsCompany()
    
    
    /**
    * Set value of 'is_company' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setIsCompany($value) {
      return $this->setColumnValue('is_company', $value);
    } // setIsCompany() 
    
    /**
    * Return value of 'user_type' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getUserType() {
      return $this->getColumnValue('user_type');
    } // getUserType()
    
    
    /**
    * Set value of 'user_type' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setUserType($value) {
      return $this->setColumnValue('user_type', $value);
    } // setUserType() 
    
    
    /**
    * Return value of 'birthday' field
    *
    * @access public
    * @param void
    * @return datetimevalue 
    */
    function getBirthday() {
      return $this->getColumnValue('birthday');
    } // getBirthday()
    
    /**
    * Set value of 'birthday' field
    *
    * @access public   
    * @param datetimevalue $value
    * @return boolean
    */
    function setBirthday($value) {
      return $this->setColumnValue('birthday', $value);
    } // setBirthday() 
    
/**
    * Return value of 'department' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getDepartment() {
      return $this->getColumnValue('department');
    } // getDepartment()
    
    /**
    * Set value of 'department' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setDepartment($value) {
      return $this->setColumnValue('department', $value);
    } // setDepartment() 

    /**
    * Return value of 'job_title' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getJobTitle() {
      return $this->getColumnValue('job_title');
    } // getJobTitle()
    
    /**
    * Set value of 'job_title' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setJobTitle($value) {
      return $this->setColumnValue('job_title', $value);
    } // setJobTitle() 
    
    /**
    * Return value of 'timezone' field
    *
    * @access public
    * @param void
    * @return float 
    */
    function getTimezone() {
      return $this->getColumnValue('timezone');
    } // getTimezone()
    
    /**
    * Set value of 'timezone' field
    *
    * @access public   
    * @param float $value
    * @return boolean
    */
    function setTimezone($value) {
      return $this->setColumnValue('timezone', $value);
    } // setTimezone() 
    
    /**
    * Return value of 'is_active_user' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getIsActiveUser() {
      return $this->getColumnValue('is_active_user');
    } // getIsActiveUser()
    
    /**
    * Set value of 'is_active_user' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setIsActiveUser($value) {
      return $this->setColumnValue('is_active_user', $value);
    } // setIsActiveUser() 
    
    	/**
	 * Return value of 'token' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getToken() {
		return $this->getColumnValue('token');
	} // getToken()

	/**
	 * Set value of 'token' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setToken($value) {
		return $this->setColumnValue('token', $value);
	} // setToken()

	/**
	 * Return value of 'salt' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getSalt() {
		return $this->getColumnValue('salt');
	} // getSalt()

	/**
	 * Set value of 'salt' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setSalt($value) {
		return $this->setColumnValue('salt', $value);
	} // setSalt()

	/**
	 * Return value of 'twister' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getTwister() {
		return $this->getColumnValue('twister');
	} // getTwister()

	/**
	 * Set value of 'twister' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setTwister($value) {
		return $this->setColumnValue('twister', $value);
	} // setTwister()
    
	
	/**
    * Return value of 'display_name' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getDisplayName() {
      return $this->getColumnValue('display_name');
    } // getDisplayName()
    
    
    /**
    * Set value of 'display_name' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setDisplayName($value) {
      return $this->setColumnValue('display_name', $value);
    } // setDisplayName() 
    
    
    /**
    * Return value of 'permission_group_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getPermissionGroupId() {
      return $this->getColumnValue('permission_group_id');
    } // getPermissionGroupId()
    
    /**
    * Set value of 'permission_group_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setPermissionGroupId($value) {
      return $this->setColumnValue('permission_group_id', $value);
    } // setPermissionGroupId() 
    
    /**
    * Return value of 'username' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getUsername() {
      return $this->getColumnValue('username');
    } // getUsername()
    
    /**
    * Set value of 'username' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setUsername($value) {
      return $this->setColumnValue('username', $value);
    } // setUsername() 
    
    /**
    * Return value of 'contact_passwords_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getContactPasswordsId() {
      return $this->getColumnValue('contact_passwords_id');
    } // getContactPasswordsId()
    
    /**
    * Set value of 'contact_passwords_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setContactPasswordsId($value) {
      return $this->setColumnValue('contact_passwords_id', $value);
    } // setContactPasswordsId() 
    
    /**
    * Return value of 'comments' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getCommentsField() {
      return $this->getColumnValue('comments');
    } // getCommentsField()
    
    /**
    * Set value of 'comments' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setCommentsField($value) {
      return $this->setColumnValue('comments', $value);
    } // setCommentsField() 
    
    
    /**
    * Return value of 'picture_file' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getPictureFile() {
      return $this->getColumnValue('picture_file');
    } // getPictureFile()
    
    
    /**
    * Set value of 'picture_file' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setPictureFile($value) {
      return $this->setColumnValue('picture_file', $value);
    } // setPictureFile()

    
    function getPictureFileSmall() {
    	return $this->getColumnValue('picture_file_small');
    }
    
    function setPictureFileSmall($value) {
    	return $this->setColumnValue('picture_file_small', $value);
    }
    
    function getPictureFileMedium() {
    	return $this->getColumnValue('picture_file_medium');
    }
    
    function setPictureFileMedium($value) {
    	return $this->setColumnValue('picture_file_medium', $value);
    }
    
    
    /**
    * Return value of 'avatar_file' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getAvatarFile() {
      return $this->getColumnValue('avatar_file');
    } // getAvatarFile()
    
    
    /**
    * Set value of 'avatar_file' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setAvatarFile($value) {
      return $this->setColumnValue('avatar_file', $value);
    } // setAvatarFile() 
    
    
   	/**
	 * Return value of 'last_login' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getLastLogin() {
		return $this->getColumnValue('last_login');
	} // getLastLogin()

	/**
	 * Set value of 'last_login' field
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setLastLogin(DateTimeValue $value) {
		return $this->setColumnValue('last_login', $value);
	} // setLastLogin()
    
    	/**
	 * Return value of 'last_visit' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getLastVisit() {
		return $this->getColumnValue('last_visit');
	} // getLastVisit()

	/**
	 * Set value of 'last_visit' field
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setLastVisit($value) {
		return $this->setColumnValue('last_visit', $value);
	} // setLastVisit()

	/**
	 * Return value of 'last_activity' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getLastActivity() {
		return $this->getColumnValue('last_activity');
	} // getLastActivity()

	/**
	 * Set value of 'last_activity' field
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setLastActivity($value) {
		return $this->setColumnValue('last_activity', $value);
	} // setLastActivity()

	
	/**
    * Return value of 'personal_member_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getPersonalMemberId() {
      return $this->getColumnValue('personal_member_id');
    } // getPersonalMemberId()
    
    
    /**
    * Set value of 'personal_member_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setPersonalMemberId($value) {
      return $this->setColumnValue('personal_member_id', $value);
    } // setPersonalMemberId()


	/**
    * Return value of 'disabled' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getDisabled() {
      return $this->getColumnValue('disabled');
    } // getDisabled()
    
    
    /**
    * Set value of 'disabled' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setDisabled($value) {
      return $this->setColumnValue('disabled', $value);
    } // setDisabled()
    
    /**
    * Return value of 'default_billing_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getDefaultBillingId() {
      return $this->getColumnValue('default_billing_id');
    } // getDefaultBillingId()
    
    
    /**
    * Set value of 'default_billing_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setDefaultBillingId($value) {
      return $this->setColumnValue('default_billing_id', $value);
    } // setDefaultBillingId()
    
	
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return Contacts 
    */
    function manager() {
      if(!($this->manager instanceof Contacts)) $this->manager = Contacts::instance();
      return $this->manager;
    } // manager
  
  } // BaseContact 

?>