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
      
      $options[] = option_tag("", "");
      foreach ($possible_values->option as $option) {
          
	  $option_attributes = $this->getValue() == $option->value ? array('selected' => 'selected') : null;
	  $options[] = option_tag(lang($option->text), $option->value, $option_attributes);
      }
      
      $attributes = array('id' => 'list_' . $this->getConfigOption()->getName());
      
      return select_box($control_name, $options, $attributes);
    } // render
    
    /**
     * 
     * @param type $configOption
     * @param type $value
     * @return string
     */
    public static function getConfigOptionText($configOption,$value) {
        
        $option = ContactConfigOptions::findOne(array('conditions'=>"name = '".$configOption."'"));
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
