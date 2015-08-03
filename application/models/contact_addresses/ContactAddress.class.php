<?php

  /**
  * ContactAddress class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class ContactAddress extends BaseContactAddress {
  
    /**
    * Return Address type
    *
    * @access public
    * @param void
    * @return AddressType
    */
    function getAddressType() {
      return AddressTypes::findById($this->getAddressTypeId());
    } // getAddressType
    
    /**
    * Return contact
    *
    * @access public
    * @param void
    * @return Contact
    */
    function getContact() {
      return Contacts::findById($this->getContactId());
    } // getContact
    
        /**
    * Edit address
    *
    * @access public
    * @param string $URL
    * @return void
    */
    function edit($street, $city, $state, $country, $zipCode, $address_type, $isMain = false) {

    	$this->setAddressTypeId($address_type);
    	$this->setStreet($street);
    	$this->setCity($city);
    	$this->setState($state);
    	$this->setCountry($country);
    	$this->setZipCode($zipCode);
    	$this->setIsMain($isMain);
    	$this->save();
    } // edit
    
    
    /**
	 * Return name of country
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getCountryName() {
		if ($this->getCountry())
		return lang('country ' . $this->getCountry());
		return '';
	} // getCountryName
	
	
	
	/**
	 * Validate before save
	 *
	 * @param array $errors
	 * @return null
	 */
	function validate(&$errors) {
		$mandatory_fields = config_option('mandatory_address_fields');
		if (is_array($mandatory_fields)) {
			foreach ($mandatory_fields as $mfield) {
				if (!$this->validatePresenceOf($mfield)) {
					$errors[] = lang('address field is required', lang('address'), lang($mfield));
				}
			}
		}
	} // validate
    
  } // ContactAddress 

?>