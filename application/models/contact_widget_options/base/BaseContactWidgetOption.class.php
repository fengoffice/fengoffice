<?php
class BaseContactWidgetOption extends DataObject {
	
	/**
	 * Return manager instance
	 *
	 * @access protected
	 * @param void
	 * @return ContactWebpages 
	 */
	function manager() {
		if (! ($this->manager instanceof ContactWidgetOptions))
			$this->manager = ContactWidgetOptions::instance ();
		return $this->manager;
	}
	
	function getContactId() {
		return $this->getColumnValue('contact_id');
	} 
	
	function setContactId($value) {
		return $this->setColumnValue('contact_id', $value);
	}
	
	
	function getWidgetName() {
		return $this->getColumnValue('widget_name');
	}
	
	function setWidgetName($value) {
		return $this->setColumnValue('widget_name', $value);
	}
	
	
	function getMemberTypeId() {
		return $this->getColumnValue('member_type_id');
	} 
	
	function setMemberTypeId($value) {
		return $this->setColumnValue('member_type_id', $value);
	} 
	
	
	function getOption() {
		return $this->getColumnValue('option');
	} 
	
	function setOption($value) {
		return $this->setColumnValue('option', $value);
	} 
	
	
	function getValue() {
		return $this->getColumnValue('value');
	} 
	
	function setValue($value) {
		return $this->setColumnValue('value', $value);
	}
	
	
	function getConfigHandlerClass() {
		return $this->getColumnValue('config_handler_class');
	} 
	
	function setConfigHandlerClass($value) {
		return $this->setColumnValue('config_handler_class', $value);
	}
	
	
	function getIsSystem() {
		return $this->getColumnValue('is_system');
	} 
	
	function setIsSystem($value) {
		return $this->setColumnValue('is_system', $value);
	}
	
}