<?php

/**
 * BaseDimensionObjectType class
 *
 * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
abstract class BaseDimensionObjectType extends DataObject {
	
	// -------------------------------------------------------
	//  Access methods
	// -------------------------------------------------------
	

	/**
	 * Return value of 'dimension_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer 
	 */
	function getDimensionId() {
		return $this->getColumnValue ( 'dimension_id' );
	} // getDimensionId()
	

	/**
	 * Set value of 'dimension_id' field
	 *
	 * @access public   
	 * @param integer $value
	 * @return boolean
	 */
	function setDimensionId($value) {
		return $this->setColumnValue ( 'dimension_id', $value );
	} // setDimensionId() 
	

	/**
	 * Return value of 'object_type_id' field
	 *
	 * @access public
	 * @param void
	 * @return string 
	 */
	function getObjectTypeId() {
		return $this->getColumnValue ( 'object_type_id' );
	} // getObjectTypeId()
	

	/**
	 * Set value of 'object_type_id' field
	 *
	 * @access public   
	 * @param string $value
	 * @return boolean
	 */
	function setObjectTypeId($value) {
		return $this->setColumnValue ( 'object_type_id', $value );
	} // setObjectTypeId()
	

	/**
	 * Return value of 'is_root' field
	 *
	 * @access public
	 * @param void
	 * @return string 
	 */
	function getIsRoot() {
		return $this->getColumnValue ( 'is_root' );
	} // getIsRoot()
	

	/**
	 * Set value of 'is_root' field
	 *
	 * @access public   
	 * @param string $value
	 * @return boolean
	 */
	function setIsRoot($value) {
		return $this->setColumnValue ( 'is_root', $value );
	}
	
	
	function getOptions() {
		return $this->getColumnValue ( 'options' );
	}
	
	function setOptions($options) {
		return $this->setColumnValue ( 'options', $options );
	}
	
	function getEnabled() {
		return $this->getColumnValue ( 'enabled' );
	}
	
	function setEnabled($value) {
		return $this->setColumnValue ( 'enabled', $value );
	}
	
	/**
	 * Return manager instance
	 *
	 * @access protected
	 * @param void
	 * @return DimensionObjectTypes 
	 */
	function manager() {
		if (! ($this->manager instanceof DimensionObjectTypes))
			$this->manager = DimensionObjectTypes::instance ();
		return $this->manager;
	} // manager


} 

