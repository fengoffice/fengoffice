<?php

  /**
  * DimensionAssociationsConfigs
  */
  class DimensionAssociationsConfigs extends BaseDimensionAssociationsConfigs {
    
  	
  	
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