<?php

  /**
  * Let user select where he wants to store uploaded files
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class ListConfigHandler extends ConfigHandler {
  
    /**
    * Render form control
    *
     * JSON FORMAT
     * {
	"option": [{
			"value": "1",
			"text": "value one"
		},
		{
			"value": "2",
			"text": "value two"
		},
		{
			"value": "3",
			"text": "value tree"
		}
	]
     * }
     * 
     * 
     * 
     * 
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
      $options = array();
      
      $data = $this->getConfigOption()->getOptions();
      $possible_values = json_decode($data);
      
      if (!isset($possible_values->no_empty_value) || !$possible_values->no_empty_value) {
      	$options[] = option_tag("", "");
      }
      
      $all_options = $possible_values->option;
      
      if (isset($possible_values->dynamic_options)) {
      	$table_name = TABLE_PREFIX . $possible_values->dynamic_options;
      	
      	if (checkTableExists($table_name)) {
      		$rows = DB::executeAll("SELECT `id`, `name` FROM `$table_name`");
      		if (is_array($rows)) {
      			foreach ($rows as $row) {
      				$op = new stdClass();
      				$op->value = $row['id'];
      				$op->text = $row['name'];
      				$all_options[] = $op;
      			}
      		}
      	}
      }
      
      foreach ($all_options as $option) {
          
		$option_attributes = $this->getValue() == $option->value ? array('selected' => 'selected') : null;
		
		$option_text = Localization::instance()->lang($option->text);
		if (!$option_text) $option_text = $option->text;
		
		$options[] = option_tag($option_text, $option->value, $option_attributes);
      }
      
      $attributes = array('id' => 'list_' . $this->getConfigOption()->getName());
      
      return select_box($control_name, $options, $attributes);
    } // render
    
    /**
     * 
     * @param string $configOption
     * @param string $value
     * @return string
     */
    public static function getConfigOptionText($configOption,$value) {
        
        $option = ContactConfigOptions::instance()->findOne(array('conditions'=>"name = '".$configOption."'"));
        if (!$option) $option = ConfigOptions::instance()->findOne(array('conditions'=>"name = '".$configOption."'"));
        $data = $option->getColumnValue('options');
        $possible_values = json_decode($data);
        foreach ($possible_values->option as $option) {
            if ($value == $option->value) {
                return $option->text;
            }
        }
        return '-';
    }
  
  } // ListConfigHandler

?>
