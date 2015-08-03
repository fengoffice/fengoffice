<?php

/**
 * BaseTemplateObjectProperty class
 *
 * @author Pablo Kamil
 */
abstract class BaseTemplateObjectProperty extends DataObject {

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
	 * Return value of 'property' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getProperty() {
		return $this->getColumnValue('property');
	} // getProperty()

	/**
	 * Set value of 'property' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setProperty($value) {
		return $this->setColumnValue('property', $value);
	} // setProperty()

	/**
	 * Return value of 'value' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getValue() {
		return $this->getColumnValue('value');
	} // getType()

	/**
	 * Set value of 'value' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setValue($value) {
		return $this->setColumnValue('value', $value);
	} // setType()

	/**
	 * Return manager instance
	 *
	 * @access protected
	 * @param void
	 * @return TemplateParameters
	 */
	function manager() {
		if(!($this->manager instanceof TemplateObjectProperties)) $this->manager = TemplateObjectProperties::instance();
		return $this->manager;
	} // manager

} // BaseTemplateObjectProperty

?>