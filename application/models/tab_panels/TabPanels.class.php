<?php

  /**
  * TabPanels
  *
  * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
  */
  class TabPanels extends BaseTabPanels {
	
  	static function getEnabled() {
  		return self::instance()->findAll(array("conditions" => "`enabled` = 1"));
  	}

  	/**
  	 * Counts the enabled tabs that list members of a dimension: tabs of the member controller whose
  	 * url_params carry that dim_id, restricted to active plugins like the tab listing itself.
  	 * Permissions are deliberately ignored so every user sees the same tab titles.
  	 *
  	 * @param integer $dimension_id
  	 * @return integer
  	 */
  	static function countEnabledMemberTabsByDimension($dimension_id) {
  		static $counts = null;
  		if (is_null($counts)) {
  			$counts = array();
  			$rows = DB::executeAll("SELECT url_params FROM " . self::instance()->getTableName(true) . "
  				WHERE enabled = 1 AND default_controller = 'member'
  				AND (plugin_id IS NULL OR plugin_id = 0 OR plugin_id IN (SELECT id FROM " . TABLE_PREFIX . "plugins WHERE is_installed = 1 AND is_activated = 1))");
  			foreach ((array) $rows as $row) {
  				$url_params = trim($row['url_params']) == '' ? array() : json_decode($row['url_params'], true);
  				$dim_id = array_var($url_params, 'dim_id');
  				if ($dim_id != '') {
  					$counts[$dim_id] = array_var($counts, $dim_id, 0) + 1;
  				}
  			}
  		}
  		return array_var($counts, $dimension_id, 0);
  	}

  	/**
  	 * Returns the title of a tab that lists the members of an object type in a dimension.
  	 *
  	 * A custom plural name configured for the type in that dimension always wins. Otherwise the dimension
  	 * name is used only when this is the only member tab of the dimension (e.g. a Workspaces dimension the
  	 * admin renamed); when several member types of the same dimension have their own tab, each one is
  	 * named by its type so they can be told apart.
  	 *
  	 * @param integer $dimension_id
  	 * @param integer $object_type_id
  	 * @return string
  	 */
  	static function getMemberTabTitle($dimension_id, $object_type_id) {
  		$default_name = null;
  		$dim = Dimensions::getDimensionById($dimension_id);
  		if ($dim instanceof Dimension && self::countEnabledMemberTabsByDimension($dimension_id) <= 1) {
  			$default_name = $dim->getName();
  		}
  		return Members::getTypeNameToShowByObjectType($dimension_id, $object_type_id, $default_name, true);
  	}
  } // TabPanels 

?>
