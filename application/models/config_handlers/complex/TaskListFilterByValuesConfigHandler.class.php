<?php

  class TaskListFilterByValuesConfigHandler extends TaskListPropertiesSelectorConfigHandler {

  	protected $uses_custom_properties = true;

	/** 
	 * Don't use dimensions in this handler
	 */
	protected $uses_dimensions = false;

	protected function getPossibleFixedValues() {
		$values = [
			['created_by',lang('created by')]
			,['completed_by', lang('completed by')]
			,['assigned_to', lang('assigned to')]
			,['assigned_by', lang('assigned by')]
			,['milestone', lang('milestone')]
			,['priority', lang('priority')]
			,['subscribed_to', lang('subscribed to')]
			,['start_date', lang('start date')]
			,['due_date', lang('due date')]
			,['invoicing_status', lang('invoicing status')]
		];
		if (Plugins::instance()->isActivePlugin('object_subtypes')) {
			$values[] = ['object_subtype', lang('task type')];
		}
		return $values;
	}

	/**
	 * Returns an array of CustomProperties that are applicable to the Task object type
	 * and are of type user, date, or datetime.
	 *
	 * @return array
	 */
	protected function getAvailableCustomProperties() {
		$task_ot = ObjectTypes::findByName('task');
		$cps = CustomProperties::instance()->getAllCustomPropertiesByObjectType($task_ot->getId());
		$result = [];
		/*
		 * Loop through all available custom properties and only add the ones that are applicable to the Task object type
		 * and are of type user, date, or datetime
		 */
		foreach ($cps as $cp) {
			if (in_array($cp->getType(), ['user', 'date', 'datetime'])) {
				$result[] = $cp;
			}
		}
		return $result;
	}
  
  } // TaskListFilterByValuesConfigHandler

