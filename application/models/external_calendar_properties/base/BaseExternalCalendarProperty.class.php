<?php

abstract class BaseExternalCalendarProperty extends DataObject {

	// -------------------------------------------------------
	//  Access methods
	// -------------------------------------------------------
    
        /**
	 * Return value of 'external_calendar_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getExternalCalendarId() {
		return $this->getColumnValue('external_calendar_id');
	} // getExternalCalendarId()

	/**
	 * Set value of 'external_calendar_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return integer
	 */
	function setExternalCalendarId($value) {
		return $this->setColumnValue('external_calendar_id', $value);
	} // setExternalCalendarId()
        
	/**
	 * Return value of 'key' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getKey() {
		return $this->getColumnValue('key');
	} // getKey()

	/**
	 * Set value of 'key' field
	 *
	 * @access public
	 * @param string $value
	 * @return string
	 */
	function setKey($value) {
		return $this->setColumnValue('key', $value);
	} // setKey()
	 
	/**
	 * Return value of 'value' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getValue() {
		return $this->getColumnValue('value');
	} // getValue()
	
	/**
	 * Set value of 'value' field
	 *
	 * @access public
	 * @param string $value
	 * @return string
	 */
	function setValue($value) {
		return $this->setColumnValue('value', $value);
	} // setValue()
        
        /**
        * Return manager instance
        *
        * @access protected
        * @param void
        * @return ExternalCalendarProperties 
        */
        function manager() {
          if(!($this->manager instanceof ExternalCalendarProperties)) $this->manager = ExternalCalendarProperties::instance();
          return $this->manager;
        } // manager
} 
?>
