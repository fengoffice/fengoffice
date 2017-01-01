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

} // DimensionMemberAssociation

?>