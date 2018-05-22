<?php

/**
 *  TemplateParameter class
 *
 * @author Pablo Kamil
 */
class TemplateParameter extends BaseTemplateParameter {

	/**
    * Construct the object
    *
    * @param void
    * @return null
    */
    function __construct() {
      parent::__construct();
    } // __construct
    
    
    
    function getArrayInfo() {
    	$cols = $this->manager()->getColumns();
    	$pdata = array();
    	foreach ($cols as $col) {
    		$pdata[$col] = $this->getColumnValue($col);
    	}
    	
    	return $pdata;
    }

} // TemplateParameter

?>