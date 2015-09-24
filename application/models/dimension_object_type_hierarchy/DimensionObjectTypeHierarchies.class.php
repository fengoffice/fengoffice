<?php

  /**
  * DimensionObjectTypeHierarchies
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class DimensionObjectTypeHierarchies extends BaseDimensionObjectTypeHierarchies {
    
  	static function getAllChildrenObjectTypeIds($dimension_id, $parent_object_type_id, $recursive = true){
  		  		
  		$res = DB::execute("SELECT child_object_type_id FROM ".TABLE_PREFIX."dimension_object_type_hierarchies WHERE `dimension_id` = $dimension_id AND `parent_object_type_id` = $parent_object_type_id");
  		$dimension_obj_type_hierarchy = $res->fetchAll();
		
  		$children = array();
  		if ($recursive && is_array($dimension_obj_type_hierarchy)) {
	  		foreach ($dimension_obj_type_hierarchy as $obj_type_hierarchy) {
	  			$child = $obj_type_hierarchy['child_object_type_id'];
	  			$children [] = $child;
  				// prevent infinite loop
	  			if ($child != $parent_object_type_id) {
	  				$children = array_unique(array_merge($children, self::getAllChildrenObjectTypeIds($dimension_id, $child, $recursive)));
	  			}
	  		}
		}
		
		return $children;
	}//getAllChildrenObjectTypeIds
	
	
	static function getAllParentObjectTypeIds($dimension_id, $child_object_type_id, $recursive = true){
  		
		$res = DB::execute("SELECT parent_object_type_id FROM ".TABLE_PREFIX."dimension_object_type_hierarchies WHERE `dimension_id` = $dimension_id AND `child_object_type_id` = $child_object_type_id");
  		$dimension_obj_type_hierarchy = $res->fetchAll();
  		
  		$parents = array();
  		if ($recursive) {
	  		foreach ($dimension_obj_type_hierarchy as $obj_type_hierarchy) {
	  			$child = $obj_type_hierarchy['parent_object_type_id'];
	  			$parents [] = $parent;
	  			$parents = array_unique(array_merge($parents, self::getAllParentObjectTypeIds($dimension_id, $parent, $recursive)));
	  		}
		}else{
			foreach ($dimension_obj_type_hierarchy as $obj_type_hierarchy) {
				$parent = $obj_type_hierarchy['parent_object_type_id'];
				$parents [] = $parent;
			}
		}
		
		return $parents;
	}//getAllParentObjectTypeIds
	
	
	private static $allow_childs_cache = array();
	
	static function typeAllowChilds($dimension_id, $parent_object_type_id) {
		
		if (isset(self::$allow_childs_cache[$dimension_id."-".$parent_object_type_id])) {
			return self::$allow_childs_cache[$dimension_id."-".$parent_object_type_id];
		}
		
		$sql = "SELECT count(*) as total FROM ".TABLE_PREFIX."dimension_object_type_hierarchies 
  			WHERE dimension_id = $dimension_id AND parent_object_type_id = $parent_object_type_id ";
		
  		$res =  DB::executeOne($sql) ;
  		$allow = (bool) array_var($res,'total');
  		
  		self::$allow_childs_cache[$dimension_id."-".$parent_object_type_id] = $allow;
  		
  		return $allow;
	}
  		
  } // DimensionObjectTypeHierarchies 

?>