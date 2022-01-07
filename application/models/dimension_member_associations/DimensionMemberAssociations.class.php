<?php

  /**
  * DimensionMemberAssociations
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class DimensionMemberAssociations extends BaseDimensionMemberAssociations {
  	
  	
  	static function getAssociationByCode($code) {
  		return self::findOne(array("conditions" => array("`code`=?", $code)));
  	}
  	
    
    function getAssociatedDimensions($associated_dimension_id) {

  		$sql = "SELECT DISTINCT (`dimension_id`) FROM `".TABLE_PREFIX."dimension_member_associations` WHERE `associated_dimension_id` = $associated_dimension_id";
  		
  		$result = DB::execute($sql);
    	$rows = $result->fetchAll();
    	$dimension_ids = array();
    	if ($rows){
	    	foreach ($rows as $row){
	    		$dimension_ids[] = (int)$row['dimension_id'];
	    	}
    	}

    	return $dimension_ids ;	
  	}
  	
  	
    function getDimensionsToReload($dimension_id) {

  		$sql = "SELECT DISTINCT(`associated_dimension_id`) FROM `".TABLE_PREFIX."dimension_member_associations` WHERE `dimension_id` = $dimension_id
				UNION SELECT DISTINCT(`dimension_id`) FROM `".TABLE_PREFIX."dimension_member_associations` WHERE `associated_dimension_id` = $dimension_id";
  		
  		$result = DB::execute($sql);
    	$rows = $result->fetchAll();
    	$dIds = array();
    	if ($rows){
	    	foreach ($rows as $row){
	    		$dIds[] = (int)$row['associated_dimension_id'];
	    	}
    	}

    	return $dIds;
  	}
  	
  	
  	/** 
  	 * Returns an array with the dimensions to reload foreach member type that belongs to this dimension
  	 * Use only the reverse associations (associated dim -> main dim), so only secondary dimensions filter main dimensions
  	 */
	function getDimensionsToReloadByObjectType($dimension_id) {
		
		$sql = "SELECT `dimension_id` as dim_id, `associated_object_type_id` as ot_id  
				FROM `".TABLE_PREFIX."dimension_member_associations` 
				WHERE `associated_dimension_id` = $dimension_id";
		
		$rows = DB::executeAll($sql);
		
		$result = array();
		if (is_array($rows)) {
			foreach ($rows as $row) {
				if (!isset($result[$row['ot_id']])) $result[$row['ot_id']] = array();
				$result[$row['ot_id']][] = $row['dim_id'];
			}
		}

		Hook::fire('add_dimensions_to_reload_by_object_type', array('dimension_id' => $dimension_id), $result);
		
		/*
		$sql = "SELECT `associated_dimension_id` as dim_id, `object_type_id` as ot_id
				FROM `".TABLE_PREFIX."dimension_member_associations`
				WHERE `dimension_id` = $dimension_id";
		
		$rows = DB::executeAll($sql);
		
		if (is_array($rows)) {
			foreach ($rows as $row) {
				if (!isset($result[$row['ot_id']])) $result[$row['ot_id']] = array();
				$result[$row['ot_id']][] = $row['dim_id'];
			}
		}
		*/
		return $result;
	}
  	
  	
    function getAllAssociationIds($dimension_id, $associated_dimension_id, $obj_type_id=null) {
    	
    	$ot_cond = "";
    	$associated_ot_cond = "";
    	if (is_numeric($obj_type_id)) {
    		$ot_cond = " AND object_type_id=$obj_type_id";
    		$associated_ot_cond = " AND associated_object_type_id=$obj_type_id";
    	}
		
    	$sql = "SELECT `id` FROM `".TABLE_PREFIX."dimension_member_associations` WHERE `dimension_id` = $dimension_id $ot_cond AND `associated_dimension_id` = $associated_dimension_id
		  UNION SELECT `id` FROM `".TABLE_PREFIX."dimension_member_associations` WHERE `dimension_id` = $associated_dimension_id $associated_ot_cond AND `associated_dimension_id` = $dimension_id";
  		
    	$result = DB::execute($sql);
    	$rows = $result->fetchAll();
    	$association_ids = array();
    	if ($rows){
	    	foreach ($rows as $row){
	    		$association_ids[] = (int)$row['id'];
	    	}
    	}

    	return $association_ids;	
  	}
  	
  	
    function getAllAssociations($dimension_id, $associated_dimension_id) {

  		$associations =  self::findAll(array('conditions' => '`dimension_id` = ' . 
								$dimension_id.' AND `associated_dimension_id` = ' . $associated_dimension_id));
		return $associations;
  	}
  	
  	static function getAssociatations($dimension_id, $object_type_id) {
  		return self::findAll(array("conditions" => array("`dimension_id` = ? AND `object_type_id` = ?", $dimension_id, $object_type_id)));
  	}
  	
  	static function getAllAssociatationsForObjectType($dimension_id, $object_type_id) {
  		return self::findAll(array("conditions" => array("`dimension_id` = ? AND `object_type_id` = ? OR `associated_dimension_id` = ? AND `associated_object_type_id` = ?",
  				$dimension_id, $object_type_id, $dimension_id, $object_type_id)));
  	}
  	
	static function getRequiredAssociatations($dimension_id, $object_type_id, $only_ids = false) {
		$enabled_dimensions = config_option('enabled_dimensions');
		$enabled_dims_cond = " AND associated_dimension_id IN (".implode(',', $enabled_dimensions).") ";
  		return self::findAll(array(
  			"conditions" => array("`dimension_id` = ? AND `object_type_id` = ? AND is_required = 1 $enabled_dims_cond", $dimension_id, $object_type_id),
  			"id" => $only_ids,
  		));
  	}
  
  	
    function existsAssociationBetweenDimensions($dimension_id, $associated_dimension_id){
  		$associations =  self::findOne(array('conditions' => '`dimension_id` = ' . 
								$dimension_id.' AND `associated_dimension_id` = ' . $associated_dimension_id));
			
		if (is_null($associations)) return false;
		else return true;						
  	}
  	
  	
  	
  	function getAllAssociationsInfo($dim_ids = null) {
  		$enabled_dimensions = config_option('enabled_dimensions');
  		if (is_null($dim_ids)) {
  			$dim_ids = $enabled_dimensions;
  		}
  		$dimensions = Dimensions::findAll(array('conditions' => 'id IN ('.implode(',', $dim_ids).')'));
  		
		$dims_info = array();
		
		foreach ($dimensions as $dim) {
			$associations = self::findAll(array('conditions' => '`dimension_id` = ' .$dim->getId().' OR `associated_dimension_id` = ' . $dim->getId()));
			
			if (is_array($associations) && count($associations)) {
				$this_dim_info = array();
				foreach ($associations as $assoc) {
					/* @var $assoc DimensionMemberAssociation */
					
					$info = $assoc->getArrayInfo($dim);
					if (!is_array($info)) continue;
					
					if ($assoc->getDimensionId() == $dim->getId()) {
						$object_type_id = $assoc->getObjectTypeId();
					} else {
						$object_type_id = $assoc->getAssociatedObjectType();
					}
					
					if (!isset($this_dim_info[$object_type_id])) {
						$this_dim_info[$object_type_id] = array();
					}
					$this_dim_info[$object_type_id][] = $info;
				}
				
				$dims_info[$dim->getId()] = $this_dim_info;
			}
		}
		
		return $dims_info;
  	}
  	
  	
  	function getAssociationObject($dimension_id, $object_type_id, $assoc_dimension_id, $assoc_object_type_id) {
  		return self::findOne(array('conditions' => "dimension_id=$dimension_id AND object_type_id=$object_type_id
  				AND associated_dimension_id=$assoc_dimension_id AND associated_object_type_id=$assoc_object_type_id"));
  	}
  	
  	
  }

?>