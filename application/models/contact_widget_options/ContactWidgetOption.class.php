<?php 

class ContactWidgetOption extends  BaseContactWidgetOption {
	
	function getArrayInfo() {
		return array(
			'widget' => $this->getWidgetName(),
			'contact_id' => $this->getContactId(),
			'option' => $this->getOption(),
			'handler' => $this->getConfigHandlerClass(),
			'value' => $this->getValue(),
			'is_system' => $this->getIsSystem(),
			'member_type_id' => $this->getMemberTypeId(),
		);
	}
}