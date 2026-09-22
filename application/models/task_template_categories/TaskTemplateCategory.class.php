<?php

class TaskTemplateCategory extends BaseTaskTemplateCategory {

	function validate($errors) {
		if (!$this->validatePresenceOf('name')) {
			$errors[] = lang('task template category name required');
		}
	}

	function getObjectTypeName() {
		return 'TaskTemplateCategory';
	}

	function getObjectUrl() {
		return '';
	}

	function getObjectName() {
		return $this->getName();
	}
}
