<?php

/**
 * BaseCronEvent class
 *
 * @author Ignacio de Soto <ignacio.desoto@fengoffice.com>
 */
abstract class BaseCronEvent extends DataObject {

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
		return $this->getColumnValue('id');
	} // getId()

	/**
	 * Set value of 'id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setId($value) {
		return $this->setColumnValue('id', $value);
	} // setId()

	/**
	 * Return value of 'name' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getName() {
		return $this->getColumnValue('name');
	} // getName()

	/**
	 * Set value of 'name' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setName($value) {
		return $this->setColumnValue('name', $value);
	} // setName()

	/**
	 * Return value of 'recursive' field
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getRecursive() {
		return $this->getColumnValue('recursive');
	} // getRecursive()

	/**
	 * Set value of 'recursive' field
	 *
	 * @access public
	 * @param boolean $value
	 * @return boolean
	 */
	function setRecursive($value) {
		return $this->setColumnValue('recursive', $value);
	} // setRecursive()

	/**
	 * Return value of 'delay' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getDelay() {
		return $this->getColumnValue('delay');
	} // getDelay()

	/**
	 * Set value of 'delay' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setDelay($value) {
		return $this->setColumnValue('delay', $value);
	} // setDelay()

	/**
	 * Return value of 'is_system' field
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getIsSystem() {
		return $this->getColumnValue('is_system');
	} // getIsSystem()

	/**
	 * Set value of 'is_system' field
	 *
	 * @access public
	 * @param boolean $value
	 * @return boolean
	 */
	function setIsSystem($value) {
		return $this->setColumnValue('is_system', $value);
	} // setIsSystem()

	/**
	 * Return value of 'enabled' field
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getEnabled() {
		return $this->getColumnValue('enabled');
	} // getEnabled()

	/**
	 * Set value of 'enabled' field
	 *
	 * @access public
	 * @param boolean $value
	 * @return boolean
	 */
	function setEnabled($value) {
		return $this->setColumnValue('enabled', $value);
	} // setEnabled()

		/**
	 * Return value of 'date' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getDate() {
		return $this->getColumnValue('date');
	} // getDate()

	/**
	 * Set value of 'date' field
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setDate($value) {
		return $this->setColumnValue('date', $value);
	} // setDate()
	

	/**
	 * Return manager instance
	 *
	 * @access protected
	 * @param void
	 * @return CronEvents
	 */
	function manager() {
		if(!($this->manager instanceof CronEvents)) $this->manager = CronEvents::instance();
		return $this->manager;
	} // manager

} // BaseCronEvent

?>