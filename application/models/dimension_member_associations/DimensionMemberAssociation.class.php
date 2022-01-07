<?php

/**
 * DimensionMemberAssociation class
 *
 * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
class DimensionMemberAssociation extends BaseDimensionMemberAssociation {
	
	
	function getConfig() {
		$config = array();
		
		$config_objects = DimensionAssociationsConfigs::findAll(array('conditions' => "association_id=".$this->getId()));
		foreach ($config_objects as $c) {
			$config[$c->getConfigName()] = $c->getValue();
		}
		
		return $config;
	}
	
	function getConfigTypes() {
		$config = array();
		
		$config_objects = DimensionAssociationsConfigs::findAll(array('conditions' => "association_id=".$this->getId()));
		foreach ($config_objects as $c) {
			$config[$c->getConfigName()] = $c->getType();
		}
		
		return $config;
	}
	
	
	function getArrayInfo(Dimension $dim_reference) {
		$assoc = $this;
		$enabled_dimensions = config_option('enabled_dimensions');
		
		if ($assoc->getDimensionId() == $dim_reference->getId()) {
			if (!in_array($assoc->getAssociatedDimensionMemberAssociationId(), $enabled_dimensions)) return;
		
			$object_type_id = $assoc->getObjectTypeId();
			$assoc_dimension_id = $assoc->getAssociatedDimensionMemberAssociationId();
			$assoc_dim = Dimensions::getDimensionById($assoc->getAssociatedDimensionMemberAssociationId());
			$assoc_dimension_name = $assoc_dim->getName();
			$assoc_dimension_code = $assoc_dim->getCode();
			$assoc_object_type_id = $assoc->getAssociatedObjectType();
		} else {
			if (!in_array($assoc->getDimensionId(), $enabled_dimensions)) return;
		
			$object_type_id = $assoc->getAssociatedObjectType();
			$assoc_dimension_id = $assoc->getDimensionId();
			$assoc_dim = Dimensions::getDimensionById($assoc->getDimensionId());
			$assoc_dimension_name = $assoc_dim->getName();
			$assoc_dimension_code = $assoc_dim->getCode();
			$assoc_object_type_id = $assoc->getObjectTypeId();
		}
		
		$custom_assoc_name = DimensionAssociationsConfigs::getConfigValue($assoc->getId(), 'custom_association_name');
		if ($custom_assoc_name) {
			$assoc_dimension_name = $custom_assoc_name;
		}
			
		$info = array(
				'id' => $assoc->getId(),
				'name' => $assoc_dimension_name,
				'code' => $assoc_dimension_code,
				'assoc_dimension_id' => $assoc_dimension_id,
				'assoc_object_type_id' => $assoc_object_type_id,
				'is_required' => $assoc->getIsRequired(),
				'is_multiple' => $assoc->getIsMultiple(),
				'keeps_record' => $assoc->getKeepsRecord(),
				'allows_default_selection' => $assoc->getAllowsDefaultSelection(),
				'is_reverse' => $dim_reference->getId() != $assoc->getDimensionId(),
				// load the configs only in one direction
				'config' => $dim_reference->getId() == $assoc->getDimensionId() ? $assoc->getConfig() : array(),
		);
		
		return $info;
	}

} // DimensionMemberAssociation

?>