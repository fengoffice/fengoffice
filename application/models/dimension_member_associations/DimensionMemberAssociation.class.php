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

} // DimensionMemberAssociation

?>