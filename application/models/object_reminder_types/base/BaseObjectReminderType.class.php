<?php

/**
 * BaseObjectReminderType class
 *
 * @author Ignacio de Soto <ignacio.desoto@fengoffice.com>
 */
abstract class BaseObjectReminderType extends DataObject {

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
	} // setId

	 
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
	 * Return manager instance
	 *
	 * @access protected
	 * @param void
	 * @return ObjectReminderTypes
	 */
	function manager() {
		if(!($this->manager instanceof ObjectReminderTypes)) $this->manager = ObjectReminderTypes::instance();
		return $this->manager;
	} // manager

} // BaseObjectReminderType

?>