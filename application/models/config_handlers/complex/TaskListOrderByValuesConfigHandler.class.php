<?php

  class TaskListOrderByValuesConfigHandler extends TaskListPropertiesSelectorConfigHandler {

	protected $uses_custom_properties = true;
	protected $uses_dimensions = true;

	protected function getPossibleFixedValues() {
		return [
			['priority',lang('priority')]
			,['name', lang('task name')]
			,['due_date', lang('due date')]
			,['created_on', lang('created on')]
			,['completed_on', lang('completed on')]
			,['assigned_to', lang('assigned to')]
			,['start_date', lang('start date')]
			,['percent_completed', lang('progress')]
		];
	}
  
  } // TaskListOrderByValuesConfigHandler

