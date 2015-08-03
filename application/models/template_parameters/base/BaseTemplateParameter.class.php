<?php

/**
 * BaseTemplateParameter class
 *
 * @author Pablo Kamil
 */
abstract class BaseTemplateParameter extends DataObject {

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
	 * Return value of 'type' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getType() {
		return $this->getColumnValue('type');
	} // getType()

	/**
	 * Set value of 'type' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setType($value) {
		return $this->setColumnValue('type', $value);
	} // setType()
	
	
	function getDefaultValue() {
		return $this->getColumnValue('default_value');
	}
	
	function setDefaultValue($value) {
		return $this->setColumnValue('default_value', $value);
	}

	/**
	 * Return manager instance
	 *
	 * @access protected
	 * @param void
	 * @return TemplateParameters
	 */
	function manager() {
		if(!($this->manager instanceof TemplateParameters)) $this->manager = TemplateParameters::instance();
		return $this->manager;
	} // manager

} // BaseTemplateParameter

?>