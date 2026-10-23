<?php

  class TaskListPropertiesSelectorConfigHandler extends ConfigHandler {

	/**
	 * @var bool determines if the config option will list dimensions as properties
	 */
    protected $uses_dimensions = true;

	/**
	 * @var bool determines if the config option will list custom properties as properties
	 */
	protected $uses_custom_properties = true;

	/**
	 * Returns an array of the possible fixed properties for tasks.
	 *
	 * This method must be overriden by subclasses
	 *
	 * @return array
	 */
	protected function getPossibleFixedValues() {
		return [];
	}

	/**
	 * Returns an array of available custom properties for tasks for the config option
	 * 
	 * @return array
	 */
	protected function getAvailableCustomProperties() {
		$task_ot = ObjectTypes::findByName('task');
		return CustomProperties::instance()->getAllCustomPropertiesByObjectType($task_ot->getId());
	}
	
	/**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
	function render($control_name){
		$genid = gen_id();

		$task_ot = ObjectTypes::findByName('task');

		$possible_values = $this->getPossibleFixedValues();
		
		Hook::fire('modify_fixed_props_labels', ['object_type_id' => $task_ot->getId()], $possible_values);

		if ($this->uses_custom_properties) {
			$task_cps = $this->getAvailableCustomProperties();
			foreach ($task_cps as $cp) {
				$possible_values[] = ['cp_'.$cp->getId(), $cp->getName()];
			}
		}

		if ($this->uses_dimensions) {
			$task_member_types = ProjectTasks::instance()->getTaskMemberTypesForListOptions(false);
			foreach ($task_member_types as $task_member_type) {
				$possible_values[] = ['dim_'.$task_member_type['dim_id'].'_'.$task_member_type['mem_type_id'], $task_member_type['mem_type_name']];
			}
		}
	  
		$current_values = $this->getValue();

		usort($possible_values, function($a, $b) {
			return strcmp(strtolower($a[1]), strtolower($b[1]));
		});
      
      
		$out = '<div class="checkbox-config-options visible">';
		foreach ($possible_values as $k => $value) {
		
			if (is_array($value)) {
				$option_id = $value[0];
				$option_text = $value[1];
			} else {
				$option_id = $value;
				$option_text = lang($value);
			}
			
			$checked = is_array($current_values) && array_search($option_id, $current_values) !== false;
			$out .= '<div class="checkbox-config-option">';
			$out .= checkbox_field($control_name . '[' . $option_id . ']', $checked, array('id' => $genid.'_'.$control_name.'_'.$option_id));
			$out .= label_tag($option_text, $genid.'_'.$control_name.'_'.$option_id, false, array('style' => 'cursor:pointer;'), '');
			$out .= '</div >';

			if ($k == 49) {
				$out .= '</div>';
				$show_more_click = '$(this).parent().find(\'.checkbox-config-options.not-visible\').show(); $(this).hide();';
				$out .= '<a href="javascript:void(0);" onclick="'.$show_more_click.'" class="show-more link-ico ico-search-m btn btn-primary-50 btn-sm">'.lang('show more').'</a>';
				$out .= '<div class="checkbox-config-options not-visible" style="display:none;">';
			}
		
		}
		$out .= '</div >';
      
		// dummy input to ensure that always is sent something to the server
		$out .= '<input type="hidden" value="0" name="'.$control_name.'[0]">';
		
		return $out;
	} // render
    
    
    function rawToPhp($value) {
    	return explode(",", $value);
    }
    
    function phpToRaw($value) {
    	if (is_array($value) && count($value)) {
    		unset($value[0]);
    		return implode(',', array_keys($value));
    	}else{
    		return $value;
    	}
    }
  
  } // TaskListPropertiesSelectorConfigHandler

