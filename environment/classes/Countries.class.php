<?php

  class Countries {
  
    /**
    * Countries array
    *
    * @var array
    */
    static $countries = null;
    
    /**
    * Return array of countries
    *
    * @access public
    * @param void
    * @return array
    */
    static function getAll() {
    	if (is_null(self::$countries)) {
    		self::$countries = array();
    		
    		$rows = DB::executeAll("SELECT * FROM ".TABLE_PREFIX."countries");
    		foreach ($rows as $r) {
    			self::$countries[$r['code']] = $r['name'];
    		}
    	}
    	return self::$countries;
    } // getAll
    
    /**
    * Find specific country by country code
    *
    * @access public
    * @param string $code
    * @return string
    */
    static function getCountryNameByCode($code) {
    	$all_countries = self::getAll();
    	return array_var($all_countries, $code);
    } // getCountryNameByCode
  
    
    
  	static function getCountryCodeByName($countryName) {
  		$all_countries = self::getAll();
		$country_codes = array_keys($all_countries);
		if (in_array($countryName, $country_codes)) { //name is a code
			return $countryName;
		} else {
			foreach ($country_codes as $code) {
				if (strtolower(lang("country $code")) == strtolower($countryName)) return $code;
			}
		}
		return '';
	}
  }

