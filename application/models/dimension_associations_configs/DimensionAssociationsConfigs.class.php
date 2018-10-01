<?php

  /**
  * DimensionAssociationsConfigs
  */
  class DimensionAssociationsConfigs extends BaseDimensionAssociationsConfigs {
    
  	/**
  	 * Adds the minimum set of config options that all dimension associations must have
  	 */
  	static function ensureAllAssociationsHaveConfigOptions() {
  		DB::execute("
  			INSERT INTO ".TABLE_PREFIX."dimension_associations_config (association_id, config_name, value)
				SELECT id, 'autoclassify_in_property_member', '1'
				FROM ".TABLE_PREFIX."dimension_member_associations WHERE associated_dimension_id NOT IN (SELECT id FROM ".TABLE_PREFIX."dimensions WHERE code='feng_persons')
			ON DUPLICATE KEY UPDATE value=value;
  		");
		DB::execute("
			INSERT INTO ".TABLE_PREFIX."dimension_associations_config (association_id, config_name, value)
				SELECT id, 'allow_remove_from_property_member', '1'
				FROM ".TABLE_PREFIX."dimension_member_associations WHERE associated_dimension_id NOT IN (SELECT id FROM ".TABLE_PREFIX."dimensions WHERE code='feng_persons')
			ON DUPLICATE KEY UPDATE value=value;
  		");
  	}
  	
  	static function getConfigValue($association_id, $config_name) {
  		
  		$config = self::findById(array('association_id' => $association_id, 'config_name' => $config_name));
  		if ($config instanceof DimensionAssociationsConfig) {
  			return $config->getValue();
  		}
  		
  		return "";
  	}
  	
  	static function getAssociationsWithConfigValue($config_name, $value) {
  		$assoc_ids = array();
  		
  		$matching_configs = self::findAll(array('conditions' => array("config_name=? AND value=?", $config_name, $value)));
  		foreach ($matching_configs as $mc) {
  			$assoc_ids[] = $mc->getAssociationId();
  		}
  		
  		return $assoc_ids;
  	}
  	
  }

?>