<?php

  /**
  * CustomProperty class
  */
  class CustomProperty extends BaseCustomProperty {
      
    /**
    * Construct the object
    *
    * @param void
    * @return null
    */
    function __construct() {
      parent::__construct();
    } // __construct
    
  
	function getOgType(){
		switch ($this->getType()) {
			case 'list':
			case 'text': $type = DATA_TYPE_STRING;
				break;
			case 'numeric': $type = DATA_TYPE_INTEGER;
				break;
			case 'date': $type = DATA_TYPE_DATE;
				break;
			case 'boolean': $type = DATA_TYPE_BOOLEAN;
				break;
			case 'user': 
			case 'contact': $type = DATA_TYPE_OBJECT;
				break;
			default: $type = DATA_TYPE_STRING;
				break;
		}
		return $type;
	}
	
	function delete() {
		ReportColumns::delete('`custom_property_id` = ' . $this->getId());
		ReportConditions::delete('`custom_property_id` = ' . $this->getId());
		CustomPropertyValues::delete('`custom_property_id` = ' . $this->getId());
		return parent::delete();
	}
	
	
	function getName() {
		$cp_name = parent::getName();
		if ($this->getIsSpecial()) {
			$label_code = str_replace("_special", "", $this->getCode());
			$label_value = Localization::instance()->lang($label_code);
			if (is_null($label_value)) {
				$label_value = Localization::instance()->lang(str_replace('_', ' ', $label_code));
			}
			if (!is_null($label_value)) $cp_name = $label_value;
		}
	
		return $cp_name;
	}
	
	
	function getValues() {
		$list_values_str = parent::getValues();
		
		if ($this->getType() == 'list') {
			Hook::fire('override_list_custom_property_values', array('cp' => $this), $list_values_str);
		}
		
		return $list_values_str;
	}
	
	
	function getArrayInfo() {
		$columns = $this->getColumns();
		$info = array();
		foreach ($columns as $col) {
			$info[$col] = $this->getColumnValue($col);
		}
		return $info;
	}
    
    
  } // ObjectProperty

?>