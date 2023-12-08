<?php

  /**
  * ObjectTypeHierarchies
  */
  class ObjectTypeHierarchies extends BaseObjectTypeHierarchies {
  	
  	static function hasParentObjectType($ot_id) {
  		
  		$h = ObjectTypeHierarchies::instance()->findOne(array('conditions' => array("child_object_type_id=?", $ot_id)));
  		
  		return $h instanceof ObjectTypeHierarchy;
  	}
  	
  	
  	
  	static function hasChildObjectType($ot_id) {
  		
  		$h = ObjectTypeHierarchies::instance()->findOne(array('conditions' => array("parent_object_type_id=?", $ot_id)));
  		
  		return $h instanceof ObjectTypeHierarchy;
  	}
    
  	
  	
  	static function getByObjectTypes($parent_ot_id, $child_ot_id) {
  		$h = ObjectTypeHierarchies::instance()->findOne(array('conditions' => array("parent_object_type_id=? AND child_object_type_id=?", $parent_ot_id, $child_ot_id)));
  		return $h;
  	}
  	
  	
  	
  	static function getHierarchyOptionValue($parent_ot_id, $child_ot_id, $dimension_id, $member_type_id, $config_name) {
  		
  		$h = self::getByObjectTypes($parent_ot_id, $child_ot_id);
  		
  		if ($h instanceof ObjectTypeHierarchy) {
  			$hierarchy_id = $h->getId();
  			
	  		$option_val = ObjectTypeHierarchyOptions::instance()->findById(array('hierarchy_id' => $hierarchy_id, 'dimension_id' => $dimension_id, 'member_type_id' => $member_type_id, 'option' => $config_name));
	  		
	  		if ($option_val instanceof ObjectTypeHierarchyOption) {
	  			return $option_val->getValue();
	  		}
  		}
  	
  		return "";
  	}
  	
  	
  	static function getAllHierarchyOptionValues($parent_ot_id, $child_ot_id) {
  	
  		$h = self::getByObjectTypes($parent_ot_id, $child_ot_id);
  		 
  		if ($h instanceof ObjectTypeHierarchy) {
  			return ObjectTypeHierarchyOptions::instance()->findAll(array('conditions' => "hierarchy_id=".$h->getId()));
  		}
  		
  		return null;
  	}
  	
  	
  }

?>