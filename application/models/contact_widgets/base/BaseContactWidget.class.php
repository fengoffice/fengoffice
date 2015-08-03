<?php
class BaseContactWidget extends DataObject {
	
	/**
	 * Return manager instance
	 *
	 * @access protected
	 * @param void
	 * @return ContactWebpages 
	 */
	function manager() {
		if (! ($this->manager instanceof ContactWidgets))
			$this->manager = ContactWidgets::instance ();
		return $this->manager;
	}
	
	function getContactId() {
		return $this->getColumnValue('contact_id');
	} 
	
	function getWidgetName() {
		return $this->getColumnValue('widget_name');
	}
	
	function getSection() {
		return $this->getColumnValue('section');
	}
	
	function getOrder() {
		return $this->getColumnValue('order');
	}
	
	
	
	function setContactId($value) {
		return $this->setColumnValue('contact_id', $value);
	}
	
	function setWidgetName($value) {
		return $this->setColumnValue('widget_name', $value);
	}
	
	function setSection($value) {
		return $this->setColumnValue('section', $value);
	}
	
	function setOrder($value) {
		return $this->setColumnValue('order', $value);
	}
	
	
}