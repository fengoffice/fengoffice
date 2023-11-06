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
  			INSERT INTO ".TABLE_PREFIX."dimension_associations_config (association_id, config_name, value, type)
				SELECT id, 'autoclassify_in_property_member', '1', 'boolean'
				FROM ".TABLE_PREFIX."dimension_member_associations WHERE associated_dimension_id NOT IN (SELECT id FROM ".TABLE_PREFIX."dimensions WHERE code='feng_persons')
			ON DUPLICATE KEY UPDATE value=value;
  		");
  		DB::execute("
			INSERT INTO ".TABLE_PREFIX."dimension_associations_config (association_id, config_name, value, type)
				SELECT id, 'allow_remove_from_property_member', '1', 'boolean'
				FROM ".TABLE_PREFIX."dimension_member_associations WHERE associated_dimension_id NOT IN (SELECT id FROM ".TABLE_PREFIX."dimensions WHERE code='feng_persons')
			ON DUPLICATE KEY UPDATE value=value;
  		");
  		DB::execute("
			INSERT INTO ".TABLE_PREFIX."dimension_associations_config (association_id, config_name, value, type)
				SELECT id, 'custom_association_name', '', 'text'
				FROM ".TABLE_PREFIX."dimension_member_associations WHERE associated_dimension_id NOT IN (SELECT id FROM ".TABLE_PREFIX."dimensions WHERE code='feng_persons')
			ON DUPLICATE KEY UPDATE value=value;
  		");
  		
  		// ensure that all associations have their code
  		DB::execute("
			update ".TABLE_PREFIX."dimension_member_associations dma
			set dma.code = coalesce(concat((select name from ".TABLE_PREFIX."object_types where id=dma.object_type_id),'_',(select name from ".TABLE_PREFIX."object_types where id=dma.associated_object_type_id)),'')
			where dma.code='';
		");
  	}
  	
  	static function getConfigValue($association_id, $config_name) {
  		
  		$config = self::instance()->findById(array('association_id' => $association_id, 'config_name' => $config_name));
  		if ($config instanceof DimensionAssociationsConfig) {
  			return $config->getValue();
  		}
  		
  		return "";
  	}
  	
  	static function getAssociationsWithConfigValue($config_name, $value) {
  		$assoc_ids = array();
  		
  		$matching_configs = self::instance()->findAll(array('conditions' => array("config_name=? AND value=?", $config_name, $value)));
  		foreach ($matching_configs as $mc) {
  			$assoc_ids[] = $mc->getAssociationId();
  		}
  		
  		return $assoc_ids;
  	}
  	
  }

?>