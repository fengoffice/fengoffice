<?php

  /**
  * DimensionMemberAssociations
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class DimensionMemberAssociations extends BaseDimensionMemberAssociations {
  	
  	
  	static function getAssociationByCode($code) {
  		return self::instance()->findOne(array("conditions" => array("`code`=?", $code)));
  	}
  	
    
    static function getAssociatedDimensions($associated_dimension_id) {

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
  	
  	
    static function getDimensionsToReload($dimension_id) {

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
	static function getDimensionsToReloadByObjectType($dimension_id) {
		
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
  	
  	
    static function getAllAssociationIds($dimension_id, $associated_dimension_id, $obj_type_id=null) {
    	
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
  	
  	
    static function getAllAssociations($dimension_id, $associated_dimension_id) {

  		$associations =  self::instance()->findAll(array('conditions' => '`dimension_id` = ' . 
								$dimension_id.' AND `associated_dimension_id` = ' . $associated_dimension_id));
		return $associations;
  	}
  	
  	static function getAssociatations($dimension_id, $object_type_id) {
  		return self::instance()->findAll(array("conditions" => array("`dimension_id` = ? AND `object_type_id` = ?", $dimension_id, $object_type_id)));
  	}

	static function getReverseAssociatations($dimension_id, $object_type_id) {
  		return self::instance()->findAll(array("conditions" => array("`associated_dimension_id` = ? AND `associated_object_type_id` = ?", $dimension_id, $object_type_id)));
  	}
  	
  	static function getAllAssociatationsForObjectType($dimension_id, $object_type_id) {
  		return self::instance()->findAll(array("conditions" => array("`dimension_id` = ? AND `object_type_id` = ? OR `associated_dimension_id` = ? AND `associated_object_type_id` = ?",
  				$dimension_id, $object_type_id, $dimension_id, $object_type_id)));
  	}
  	
	static function getRequiredAssociatations($dimension_id, $object_type_id, $only_ids = false) {
		$enabled_dimensions = config_option('enabled_dimensions');
		$enabled_dims_cond = " AND associated_dimension_id IN (".implode(',', $enabled_dimensions).") ";
  		return self::instance()->findAll(array(
  			"conditions" => array("`dimension_id` = ? AND `object_type_id` = ? AND is_required = 1 $enabled_dims_cond", $dimension_id, $object_type_id),
  			"id" => $only_ids,
  		));
  	}
  
  	
    static function existsAssociationBetweenDimensions($dimension_id, $associated_dimension_id){
  		$associations =  self::instance()->findOne(array('conditions' => '`dimension_id` = ' . 
								$dimension_id.' AND `associated_dimension_id` = ' . $associated_dimension_id));
			
		if (is_null($associations)) return false;
		else return true;						
  	}



	/**
	 * Retrieves all dimension associations and returns them in an array.
	 * 
	 * The array is indexed by the association ID and each value is an array 
	 * containing the association info.
	 * 
	 * @return array The array with all dimension associations.
	 */
	static function getAllAssociationsInfoById() {

		$all_associations = self::instance()->findAll();
		$result = array();
		foreach ($all_associations as $association) {
			// Store the association info in an array indexed by the association ID
			$info = $association->getArrayInfo();
			if ($info) {
				$result[$association->getId()] = $info;
			}
		}
		return $result;
	}
  	
  	
  	
  	static function getAllAssociationsInfo($dim_ids = null) {
  		$enabled_dimensions = config_option('enabled_dimensions');
  		if (is_null($dim_ids)) {
  			$dim_ids = $enabled_dimensions;
  		}
  		$dimensions = Dimensions::instance()->findAll(array('conditions' => 'id IN ('.implode(',', $dim_ids).')'));
  		
		$dims_info = array();
		
		foreach ($dimensions as $dim) {
			$associations = self::instance()->findAll(array('conditions' => '`dimension_id` = ' .$dim->getId().' OR `associated_dimension_id` = ' . $dim->getId()));
			
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
  	
  	
  	static function getAssociationObject($dimension_id, $object_type_id, $assoc_dimension_id, $assoc_object_type_id) {
  		return self::instance()->findOne(array('conditions' => "dimension_id=$dimension_id AND object_type_id=$object_type_id
  				AND associated_dimension_id=$assoc_dimension_id AND associated_object_type_id=$assoc_object_type_id"));
  	}
  	
  	
	/**
	 * Gets the data for the association in the custom properties list.
	 *
	 * @param DimensionMemberAssociation $dim_association The association.
	 * @param int $dimension_id The id of the dimension.
	 * @param ObjectType $object_type The object type the association belongs to.
	 * @return array The data for the association in the custom properties list.
	 */
	static function getAssociationDataForCustomPropertiesList($dim_association, $dimension_id, $object_type) {
		$enabled_dimension_ids = config_option('enabled_dimensions');
		if (is_numeric($object_type)) {
			$object_type = ObjectTypes::instance()->findById($object_type);
		}
		$assoc_dimension = Dimensions::getDimensionById($dim_association->getAssociatedDimensionMemberAssociationId());
		$ot = ObjectTypes::instance()->findById($dim_association->getAssociatedObjectType());
		
		// If the dimension is not enabled, don't show the association.
		if (!in_array($assoc_dimension->getId(), $enabled_dimension_ids)) return;
		
		$custom_assoc_name = null;
		// If the dimension is the same as the association, use the custom name if exists.
		if ($dimension_id == $dim_association->getDimensionId()) {
			$custom_assoc_name = DimensionAssociationsConfigs::getConfigValue($dim_association->getId(), 'custom_association_name');
		}
		
		// If there is a custom name, use it, otherwise use the name of the dimension.
		if ($custom_assoc_name) {
			$label = $custom_assoc_name;
		} else {
			$custom_name = $assoc_dimension->getOptionValue('custom_dimension_name');
			// If there is a custom name for the dimension, use it.
			if ($custom_name && trim($custom_name) != "") {
				$label = $custom_name;
			} else {
				// Otherwise, try to get the translation for the object type.
				$label = Localization::instance()->lang(str_replace('_',' ', $ot->getName()) . ($dim_association->getIsMultiple() ? 's' : ''));
				// If there is no translation, use the name of the dimension.
				if (is_null($label)) {
					$label = $assoc_dimension->getName();
				}
			}
		}
		
		// Get the custom description for the association.
		$assoc_description = DimensionAssociationsConfigs::getConfigValue($dim_association->getId(), 'custom_association_description');
		
		// Generate the property id for the association.
		$prop_id = "assoc_".$dim_association->getId();
		$is_required = $dim_association->getIsRequired();
		$is_multiple = $dim_association->getIsMultiple();
		$assoc_code = $dim_association->getCode();
		
		// Get the order and inheritability of the property in the group.
		if (class_exists('PropertyGroups')) {
			$order = PropertyGroups::getPropertyOrderInGroup($object_type, $prop_id);
			$is_inheritable = PropertyGroups::getPropertyIsInheritable($object_type, $prop_id);
		} else {
			$order = 0;
			$is_inheritable = 0;
		}
		
		// Create the data array for the association in the custom properties list.
		$cp_data = array(
			'id' => $prop_id,
			'code' => $assoc_code,
			'name' => $label,
			'description' => $assoc_description,
			'is_special' => true,
			'no_options' => true,
			'property_order' => $order,
			'is_inheritable' => $is_inheritable,
			'is_required' => $is_required,
			'is_multiple_values' => $is_multiple,
			'override_is_required' => true,
			'override_is_multiple_values' => true
		);
		
		return $cp_data;
	}
	
	
  }

?>