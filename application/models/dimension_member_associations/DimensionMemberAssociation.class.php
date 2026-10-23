<?php

/**
 * DimensionMemberAssociation class
 *
 * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
class DimensionMemberAssociation extends BaseDimensionMemberAssociation {
	
	
	function getConfig() {
		$config = array();
		
		$config_objects = DimensionAssociationsConfigs::instance()->findAll(array('conditions' => "association_id=".$this->getId()));
		foreach ($config_objects as $c) {
			$config[$c->getConfigName()] = $c->getValue();
		}
		
		return $config;
	}
	
	function getConfigTypes() {
		$config = array();
		
		$config_objects = DimensionAssociationsConfigs::instance()->findAll(array('conditions' => "association_id=".$this->getId()));
		foreach ($config_objects as $c) {
			$config[$c->getConfigName()] = $c->getType();
		}
		
		return $config;
	}
	
	
	function getArrayInfo($dim_reference = null) {
		if (!$dim_reference) {
			$dim_reference = Dimensions::getDimensionById($this->getDimensionId());
		}
		if (!$dim_reference) {
			return;
		}
		$assoc = $this;
		$enabled_dimensions = config_option('enabled_dimensions');
		$assoc_description = "";
		
		if ($assoc->getDimensionId() == $dim_reference->getId()) {
			if (!in_array($assoc->getAssociatedDimensionMemberAssociationId(), $enabled_dimensions)) return;
		
			$object_type_id = $assoc->getObjectTypeId();
			$assoc_dimension_id = $assoc->getAssociatedDimensionMemberAssociationId();
			$assoc_dim = Dimensions::getDimensionById($assoc->getAssociatedDimensionMemberAssociationId());
			$assoc_dimension_name = $assoc_dim->getName();
			$assoc_dimension_code = $assoc_dim->getCode();
			$assoc_object_type_id = $assoc->getAssociatedObjectType();

			$custom_assoc_name = DimensionAssociationsConfigs::getConfigValue($assoc->getId(), 'custom_association_name');
			if ($custom_assoc_name) {
				$assoc_dimension_name = $custom_assoc_name;
			}
			$assoc_description = DimensionAssociationsConfigs::getConfigValue($assoc->getId(), 'custom_association_description');
			
		} else {
			if (!in_array($assoc->getDimensionId(), $enabled_dimensions)) return;
		
			$object_type_id = $assoc->getAssociatedObjectType();
			$assoc_dimension_id = $assoc->getDimensionId();
			$assoc_dim = Dimensions::getDimensionById($assoc->getDimensionId());
			$assoc_dimension_name = $assoc_dim->getName();
			$assoc_dimension_code = $assoc_dim->getCode();
			$assoc_object_type_id = $assoc->getObjectTypeId();
		}
			
		$info = array(
				'id' => $assoc->getId(),
				'name' => $assoc_dimension_name,
				'code' => $assoc_dimension_code,
				'assoc_code' => $assoc->getCode(),
				'description' => $assoc_description,
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


	/**
	 * Can delete the dimension member association
	 *
	 * @param string $error_message a variable to store the error message
	 * @return boolean true if the association can be deleted, false otherwise
	 */
	function canDelete(&$error_message) {
		// Check if there are associated records
		$main_ot = ObjectTypes::instance()->findById($this->getObjectTypeId());
		$assoc_ot = ObjectTypes::instance()->findById($this->getAssociatedObjectType());
		
		$associated_records = MemberPropertyMembers::instance()->findAll(array('conditions' => "association_id=".$this->getId()));
		if (count($associated_records)) {
			// If there are associated records, it cannot be deleted
			$error_message = lang('cannot delete dimension member association, it has associated records', $main_ot->getPluralObjectTypeName(), $assoc_ot->getPluralObjectTypeName());
			return false;
		}

		// Special associations cannot be deleted
		$is_special_association = in_array($this->getCode(), array('customer_business_company', 'project_billing_client'));
		if ($is_special_association) {
			// If it is a special association, it cannot be deleted
			$error_message = lang('cannot delete dimension member association', $main_ot->getPluralObjectTypeName(), $assoc_ot->getPluralObjectTypeName());
			return false;
		}

		// If all conditions are met, it can be deleted
		return true;
	}



	/**
	 * Delete the dimension member association
	 *
	 * @return boolean true if the association was deleted, false otherwise
	 * @throws Exception if the association cannot be deleted
	 */
	function delete() {

		$error_message = null;
		if (!$this->canDelete($error_message)) {
			throw new Exception($error_message);
		}

		// Delete the configs for the association
		DimensionAssociationsConfigs::instance()->delete(array("`association_id` = ?", $this->getId()));

		// Fire the hook to allow plugins to act on the deletion
		$ignored = null;
		Hook::fire('dimension_member_association_delete', array('dim_association' => $this), $ignored);

		// Call the parent delete
		return parent::delete();
	}


	/**
	 * Gets similar associations
	 *
	 * Similar associations are ones that share the same dimension, object type, associated dimension, and associated object type.
	 *
	 * @return array of DimensionMemberAssociation
	 */
	function getSimilarAssociations() {
		$similar_associations = DimensionMemberAssociations::instance()->findAll(array(
			'conditions' => 
			'`dimension_id` = ' . $this->getDimensionId() . 
			' AND `object_type_id` = ' . $this->getObjectTypeId() . 
			' AND `associated_dimension_id` = ' . $this->getAssociatedDimensionMemberAssociationId() . 
			' AND `associated_object_type_id` = ' . $this->getAssociatedObjectType() . 
			' AND `id` != '.$this->getId()
		));

		if (!$similar_associations) {
			return array();
		}

		return $similar_associations;
	}

	/**
	 * Gets the information of similar associations
	 *
	 * Similar associations are ones that share the same dimension, object type, associated dimension, and associated object type.
	 *
	 * @return array An array of associative arrays containing the information of the similar associations.
	 */
	function getSimilarAssociationsInfo() {
		$associations = $this->getSimilarAssociations();
		$associations_info = array();
		foreach ($associations as $association) {
			// Get the associative array containing the information of the association
			$associations_info[] = $association->getArrayInfo();
		}
		return $associations_info;
	}

	/**
	 * Gets all the associations that can be chained to this association with the given 
	 * associated dimension id and associated object type id.
	 *
	 * An association can be chained if the associated dimension of the association 
	 * is the same as the dimension of another association, and the associated object type
	 * of the association is the same as the object type of that other association.
	 *
	 * @param int $assoc_dim_id The associated dimension id of the association.
	 * @param int $assoc_ot_id The associated object type id of the association.
	 * @return array of DimensionMemberAssociation The chained associations.
	 */
	function getChainableAssociations($assoc_dim_id, $assoc_ot_id) {
		$assoc_type_associations = DimensionMemberAssociations::instance()->findAll(array(
			'conditions' => 
			'`dimension_id` = ' . $assoc_dim_id . 
			' AND `object_type_id` = ' . $assoc_ot_id
		));
		if (!$assoc_type_associations) return array();
		

		$main_type_associations = DimensionMemberAssociations::instance()->findAll(array(
			'conditions' => 
			'`dimension_id` = ' . $this->getDimensionId() . 
			' AND `object_type_id` = ' . $this->getObjectTypeId()
		));
		if (!$main_type_associations) return array();

		$chainable_associations = array();
		foreach ($assoc_type_associations as $association) {
			foreach ($main_type_associations as $main_association) {
				if ($association->getAssociatedDimensionMemberAssociationId() == $main_association->getAssociatedDimensionMemberAssociationId()
					&& $association->getAssociatedObjectType() == $main_association->getAssociatedObjectType()) {
					
					$chainable_associations[] = $main_association;
				}
			}
		}

		return $chainable_associations;
	}

	function getChainableAssociationsInfo($assoc_dim_id, $assoc_ot_id) {
		$associations = $this->getChainableAssociations($assoc_dim_id, $assoc_ot_id);
		$associations_info = array();
		foreach ($associations as $association) {
			// Get the associative array containing the information of the association
			$associations_info[] = $association->getArrayInfo();
		}
		return $associations_info;
	}

} // DimensionMemberAssociation

?>