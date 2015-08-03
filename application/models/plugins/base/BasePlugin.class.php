<?php

/**
 * BasePlugin class
 *
 * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
abstract class BasePlugin extends DataObject {
	
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
	function getId() {
		return $this->getColumnValue ( 'id' );
	} // getId()
	

	/**
	 * Set value of 'id' field
	 *
	 * @access public   
	 * @param integer $value
	 * @return boolean
	 */
	function setId($value) {
		return $this->setColumnValue ( 'id', $value );
	} // setId() 
	

	/**
	 * Return value of 'name' field
	 *
	 * @access public
	 * @param void
	 * @return string 
	 */
	function getName() {
		return $this->getColumnValue ( 'name' );
	} // getName()
	

	/**
	 * Set value of 'name' field
	 *
	 * @access public   
	 * @param string $value
	 * @return boolean
	 */
	function setName($value) {
		return $this->setColumnValue ( 'name', $value );
	} // setName() 
	

	function getVersion() {
		return $this->getColumnValue ( 'version' );
	}
	

	function setVersion($value) {
		return $this->setColumnValue ( 'version', $value );
	}
	
	/**
	 * Return value of 'is_installed' field
	 *
	 * @access public
	 * @param void
	 * @return string 
	 */
	function getIsInstalled() {
		return $this->getColumnValue ( 'is_installed' );
	} // getIsInstalled()
	

	/**
	 * Set value of 'is_installed' field
	 *
	 * @access public   
	 * @param string $value
	 * @return boolean
	 */
	function setIsInstalled($value) {
		return $this->setColumnValue ( 'is_installed', $value );
	} // setIsInstalled() 
	

	/**
	 * Return value of 'is_activated' field
	 *
	 * @access public
	 * @param void
	 * @return string 
	 */
	function getIsActivated() {
		return $this->getColumnValue ( 'is_activated' );
	} // getIsActivated()
	

	/**
	 * Set value of 'is_activated' field
	 *
	 * @access public   
	 * @param string $value
	 * @return boolean
	 */
	function setIsActivated($value) {
		return $this->setColumnValue ( 'is_activated', $value );
	} // setIsActivated() 
	

	/**
	 * Return value of 'priority' field
	 *
	 * @access public
	 * @param void
	 * @return integer 
	 */
	function getPriority() {
		return $this->getColumnValue ( 'priority' );
	} // getPriority()
	

	/**
	 * Set value of 'priority' field
	 *
	 * @access public   
	 * @param integer $value
	 * @return boolean
	 */
	function setPriority($value) {
		return $this->setColumnValue ( 'priority', $value );
	} // setPriority() 
	

	/**
	 * Return value of 'activated_on' field
	 *
	 * @access public
	 * @param void
	 * @return string 
	 */
	function getActivatedOn() {
		return $this->getColumnValue ( 'activated_on' );
	} // getActivatedOn()
	

	/**
	 * Set value of 'activated_on' field
	 *
	 * @access public   
	 * @param string $value
	 * @return boolean
	 */
	function setActivatedOn($value) {
		return $this->setColumnValue ( 'activated_on', $value );
	} // setActivatedOn() 
	

	/**
	 * Return value of 'activated_by_id' field
	 *
	 * @access public
	 * @param void
	 * @return string 
	 */
	function getActivatedById() {
		return $this->getColumnValue ( 'activated_by_id' );
	} // getActivatedById()
	

	/**
	 * Set value of 'activated_by_id' field
	 *
	 * @access public   
	 * @param string $value
	 * @return boolean
	 */
	function setActivatedById($value) {
		return $this->setColumnValue ( 'activated_by_id', $value );
	} // setDefinesPermissions() 
	

	/**
	 * Return manager instance
	 *
	 * @access protected
	 * @param void
	 * @return Plugins 
	 */
	function manager() {
		if (! ($this->manager instanceof Plugins))
			$this->manager = Plugins::instance ();
		return $this->manager;
	} // manager


} // BasePlugin 


?>