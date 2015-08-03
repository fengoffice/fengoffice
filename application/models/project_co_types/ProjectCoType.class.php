<?php

class ProjectCoType extends BaseProjectCoType {

    function getArrayInfo() {
    	$result = array(
			'id' => $this->getId(),
			'name' => $this->getName()
		);
		
		return $result;
    }
    
    
} // ProjectCoType

?>