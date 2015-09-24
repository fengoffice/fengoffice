<?php

  /**
  * DimensionObjectTypes
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class DimensionObjectTypes extends BaseDimensionObjectTypes {
    
  	static function getChildObjectTypes($member) {
  		if ($member instanceof Member) {
  			$member_id = $member->getId();
  			$m = $member;
  		} else {
  			$member_id = $member;
	  		$m = Members::instance()->findById($member_id);
	  		if (!$m instanceof Member) return array();
  		}
  		
  		$d = $m->getDimensionId() ;
  		$parent_object_type_id = $m->getObjectTypeId() ;
  		$sql = "
  			SELECT distinct(child_object_type_id) FROM ".TABLE_PREFIX."dimension_object_type_hierarchies 
  			WHERE 
  		 		dimension_id = $d AND enabled=1 AND
  		 		parent_object_type_id = $parent_object_type_id ";
  		return  self::findAll(array("conditions"=>"object_type_id IN ($sql) AND dimension_id = $d")); 
  	}
  	
  	static function getObjectTypeIdsByDimension($dimension_id){
  		
  		$dimension_object_types = self::findAll(array('conditions' => 'enabled=1 AND `dimension_id` = ' . $dimension_id));
  		$object_type_ids = array();
  		foreach ($dimension_object_types as $obj_type){
  			$object_type_ids [] = $obj_type->getObjectTypeId();
  		}
  		
  		return $object_type_ids;
  	}
  }