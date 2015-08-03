<?php

  /**
  * DimensionObjectTypeContents
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class DimensionObjectTypeContents extends BaseDimensionObjectTypeContents {
    

  	static private $content_ot_cache = array();
  	static function getContentObjectTypeIds($dimension_id, $dimension_object_type = null) {
  		$type_ids = array();
  		$cond = "";
  		
  		$key = $dimension_id."_".$dimension_object_type;
  		if (isset(self::$content_ot_cache[$key])) {
  			return self::$content_ot_cache[$key];
  		} else {
  		
	  		if ($dimension_object_type != null) {
	  			$cond = ' AND `dimension_object_type_id` = '.$dimension_object_type;
	  		}
  		
  			$types = DB::executeAll("SELECT content_object_type_id FROM ".TABLE_PREFIX."dimension_object_type_contents WHERE `dimension_id` = ".$dimension_id.$cond);
  			foreach ($types as $type) {
  				$type_ids[] = $type['content_object_type_id'];
  			}
  			self::$content_ot_cache[$key] = array_unique($type_ids);
  			return self::$content_ot_cache[$key];
  		}
  	}
    
  	
  	static function getDimensionObjectTypesforObject($object_type_id){
  		return self::findAll(array('conditions' => "`content_object_type_id` = '$object_type_id'"));
  	}
  	
  	
  	static function getRequiredDimensions($object_type_id){
  		$sql = "SELECT DISTINCT `dimension_id` FROM `".TABLE_PREFIX."dimension_object_type_contents` WHERE 
  			   `content_object_type_id` = '$object_type_id' AND `is_required` = 1
  			   OR `dimension_id` IN (SELECT `d`.`id` FROM `".TABLE_PREFIX."dimensions` `d` WHERE `d`.`is_required`=1)";
  		
  		$result = DB::execute($sql);
    	$rows = $result->fetchAll();
    	$dimension_ids = array();
    	if ($rows){
	    	foreach ($rows as $row){
	    		$dimension_ids[] = (int)$row['dimension_id'];
	    	}
    	}
    	return $dimension_ids;
  	}
  } // DimensionObjectTypeContents 

