<?php

/**
 * BaseCOTemplate class
 *
 * @author Ignacio de Soto <ignacio.desoto@gmail.com>
 */
abstract class BaseCOTemplate extends ContentDataObject {

	// -------------------------------------------------------
	//  Access methods
	// -------------------------------------------------------

	/**
	 * Return value of 'object_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getObjectId() {
		return $this->getColumnValue('object_id');
	} // getObjectId()

	/**
	 * Set value of 'object_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setObjectId($value) {
		return $this->setColumnValue('object_id', $value);
	} // setObjectId()

	/**
	 * Return value of 'description' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDescription() {
		return $this->getColumnValue('description');
	} // getDescription()

	/**
	 * Set value of 'description' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setDescription($value) {
		return $this->setColumnValue('description', $value);
	} // setDescription()


	/**
	 * Return manager instance
	 *
	 * @access protected
	 * @param void
	 * @return Templates
	 */
	function manager() {
		if(!($this->manager instanceof COTemplates)) $this->manager = COTemplates::instance();
		return $this->manager;
	} // manager

} // BaseCOTemplate

?>