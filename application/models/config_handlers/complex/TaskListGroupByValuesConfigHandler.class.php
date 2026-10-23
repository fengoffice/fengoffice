<?php

  class TaskListGroupByValuesConfigHandler extends TaskListPropertiesSelectorConfigHandler {

	protected $uses_custom_properties = true;
	protected $uses_dimensions = true;

	protected function getPossibleFixedValues() {
		return [
			['milestone', lang('milestone')]
			,['priority',lang('priority')]
			,['assigned_to', lang('assigned to')]
			,['due_date', lang('due date')]
			,['start_date', lang('start date')]
			,['created_on', lang('created on')]
			,['created_by', lang('created by')]
			,['completed_on', lang('completed on')]
			,['completed_by', lang('completed by')]
			,['status', lang('status')]
		];
	}
  
  } // TaskListGroupByValuesConfigHandler

