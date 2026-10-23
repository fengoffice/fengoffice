<?php

abstract class BaseTaskTemplateCategory extends ApplicationDataObject {

	function getId() {
		return $this->getColumnValue('id');
	}

	function setId($value) {
		return $this->setColumnValue('id', $value);
	}

	function getName() {
		return $this->getColumnValue('name');
	}

	function setName($value) {
		return $this->setColumnValue('name', $value);
	}

	function getSortOrder() {
		return $this->getColumnValue('sort_order');
	}

	function setSortOrder($value) {
		return $this->setColumnValue('sort_order', $value);
	}

	function manager() {
		if (!($this->manager instanceof TaskTemplateCategories)) {
			$this->manager = TaskTemplateCategories::instance();
		}
		return $this->manager;
	}
}
