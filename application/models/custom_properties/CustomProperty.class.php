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
			default: $type = DATA_TYPE_STRING;
				break;
		}
		return $type;
	}
	
	function delete() {
		ReportColumns::delete('`custom_property_id` = ' . $this->getId());
		ReportConditions::delete('`custom_property_id` = ' . $this->getId());
		return parent::delete();
	}
    
    
  } // ObjectProperty

?>