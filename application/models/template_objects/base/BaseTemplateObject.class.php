<?php

/**
 * BaseTemplateObject class
 *
 * @author Ignacio de Soto
 */
abstract class BaseTemplateObject extends DataObject {

	// -------------------------------------------------------
	//  Access methods
	// -------------------------------------------------------

	/**
	 * Return value of 'template_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getTemplateId() {
		return $this->getColumnValue('template_id');
	} // getTemplateId()

	/**
	 * Set value of 'template_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setTemplateId($value) {
		return $this->setColumnValue('template_id', $value);
	} // setTemplateId()
	 
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
	 * Return value of 'created_on' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getCreatedOn() {
		return $this->getColumnValue('created_on');
	} // getCreatedOn()

	/**
	 * Set value of 'created_on' field
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setCreatedOn($value) {
		return $this->setColumnValue('created_on', $value);
	} // setCreatedOn()

	/**
	 * Return value of 'created_by_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getCreatedById() {
		return $this->getColumnValue('created_by_id');
	} // getCreatedById()

	/**
	 * Set value of 'created_by_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setCreatedById($value) {
		return $this->setColumnValue('created_by_id', $value);
	} // setCreatedById()


	/**
	 * Return manager instance
	 *
	 * @access protected
	 * @param void
	 * @return TemplateObjects
	 */
	function manager() {
		if(!($this->manager instanceof TemplateObjects)) $this->manager = TemplateObjects::instance();
		return $this->manager;
	} // manager

} // BaseTemplateObject

?>