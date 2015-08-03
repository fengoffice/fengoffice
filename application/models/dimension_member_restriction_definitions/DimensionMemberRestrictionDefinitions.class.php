<?php

  /**
  * DimensionMemberRestrictionDefinitions
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class DimensionMemberRestrictionDefinitions extends BaseDimensionMemberRestrictionDefinitions {
    

  	function getRestrictedObjectTypeIds($dimension_id, $object_type_id, $restricted_dimension_id, $is_required){
  		if ($is_required) $is_required_condition = "1";
  		else $is_required_condition = "0";
  		  		
  		$member_restrictions =  self::findAll(array('conditions' => '`dimension_id` = ' . 
								$dimension_id.' AND `object_type_id` = ' . $object_type_id. 
								' AND `restricted_dimension_id` = ' . $restricted_dimension_id. 
								' AND `is_required` = ' . $is_required_condition));

		$restricted_obj_type_ids = array();					
		foreach ($member_restrictions as $mr){
			$restricted_obj_type_ids[] = $mr->getRestrictedObjectTypeId();
		}

		return $restricted_obj_type_ids;
  	}
  	
  	
  	function existsRestrictionBetweenDimensions($dimension_id, $restricted_dimension_id){
  		$restrictions =  self::findOne(array('conditions' => '`dimension_id` = ' . 
								$dimension_id.' AND `restricted_dimension_id` = ' . $restricted_dimension_id));
			
		if (is_null($restrictions)) return false;
		else return true;						
  	}
  	
 } 