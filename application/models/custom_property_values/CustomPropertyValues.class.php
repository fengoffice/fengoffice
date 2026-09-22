<?php

/**
 *   CustomPropertyValues class
 */
class CustomPropertyValues extends BaseCustomPropertyValues {

	/**
	 * Per-request cache for getCustomPropertyValues results.
	 * Keyed by "$object_id|$custom_property_id" => array of CustomPropertyValue objects.
	 */
	private static $_cp_values_cache = array();

	/**
	 * Tracks which object_ids have been fully pre-fetched so that a cache miss
	 * for a specific (object_id, cp_id) pair can safely return [] without querying.
	 */
	private static $_cp_prefetched_ids = array();

	/**
	 * Batch pre-warms the getCustomPropertyValues cache for multiple objects
	 * at once, replacing N*M individual queries (N objects x M custom properties)
	 * with a single IN(...) query.
	 * Callers that process a batch of objects should invoke this before the loop.
	 *
	 * @param array $object_ids
	 */
	static function prefetchForObjects(array $object_ids) {
		if (empty($object_ids)) return;

		$to_fetch = array();
		foreach ($object_ids as $id) {
			$id = (int) $id;
			if (!isset(self::$_cp_prefetched_ids[$id])) {
				$to_fetch[]                          = $id;
				self::$_cp_prefetched_ids[$id]   = true;
			}
		}
		if (empty($to_fetch)) return;

		$ids_sql = implode(',', $to_fetch);
		$rows    = self::instance()->findAll(array(
			'conditions' => "`object_id` IN ($ids_sql)"
		));
		if ($rows) {
			foreach ($rows as $cpv) {
				$key = (int) $cpv->getObjectId() . '|' . (int) $cpv->getCustomPropertyId();
				if (!isset(self::$_cp_values_cache[$key])) {
					self::$_cp_values_cache[$key] = array();
				}
				self::$_cp_values_cache[$key][] = $cpv;
			}
		}
	}


	/**
	 * Return custom property value for the object
	 *
	 * @param $object_id
	 * @param $custom_property_id
	 * @return CustomPropertyValue
	 */
	static function getCustomPropertyValue($object_id, $custom_property_id) {
		return self::instance()->findOne(array(
			'conditions' => array("`object_id` = ? AND `custom_property_id` = ?", $object_id, $custom_property_id)
		)); // findOne
	} //  getCustomPropertyValue
	

	static function setCustomPropertyValue($object_id, $custom_property_id, $value) {
		$cpv = self::getCustomPropertyValue($object_id, $custom_property_id);
		if (!$cpv) {
			$cpv = new CustomPropertyValue();
			$cpv->setObjectId($object_id);
			$cpv->setCustomPropertyId($custom_property_id);
		}
		if ($value instanceof DateTimeValue) {
    		$value = $value->toMySQL(); 
		}
		$cpv->setValue($value);
		$cpv->save();
	}



	/**
	 * Return custom property values for the object
	 *
	 * @param $object_id
	 * @param $custom_property_id
	 * @return array
	 */
	static function getCustomPropertyValues($object_id, $custom_property_id) {
		$cache_key = (int) $object_id . '|' . (int) $custom_property_id;

		if (isset(self::$_cp_values_cache[$cache_key])) {
			return self::$_cp_values_cache[$cache_key];
		}
		// If this object was fully pre-fetched, a missing cache entry means no values exist
		if (isset(self::$_cp_prefetched_ids[(int) $object_id])) {
			return array();
		}

		// Cache miss — fall back to individual DB query (original behavior)
		$result = self::instance()->findAll(array(
			'conditions' => array("`object_id` = ? AND `custom_property_id` = ?", $object_id, $custom_property_id)
		)); // findAll

		// Populate cache for subsequent calls within the same request
		self::$_cp_values_cache[$cache_key] = $result ? $result : array();
		return self::$_cp_values_cache[$cache_key];
	} //  getCustomPropertyValue
	
	/**
	 * Return all custom property values for the object
	 *
	 * @param $object_id
	 * @return array
	 */
	static function getAllCustomPropertyValuesForObject($object_id) {
		return self::instance()->findAll(array(
			'conditions' => array("`object_id` = ?", $object_id)
		)); // findAll
	} //  getAllCustomPropertyValuesForObject
	
	/**
	 * Delete custom property values for the object
	 *
	 * @param $object_id
	 * @param $custom_property_id
	 * 
	 */
	static function deleteCustomPropertyValues($object_id, $custom_property_id) {
		return self::instance()->delete(array("`object_id` = ? AND `custom_property_id` = ?", $object_id, $custom_property_id)); 
	} //  deleteCustomPropertyValues
	
	/**
	 * Return custom property value count for the object
	 *
	 * @param $object_id
	 * @return array
	 */
	static function getCustomPropertyValueCount($object, $visibility='all') {
		$visibility_cond = "";
		if ($visibility != 'all') {
			if ($visibility == 'visible_by_default') $visibility_cond = " AND visible_by_default=1";
			else $visibility_cond = " AND visible_by_default=0";
		}
		return count(self::instance()->findAll(array(
			'conditions' => array("`object_id` = ? AND `custom_property_id` in (SELECT `id` FROM " . 
				CustomProperties::instance()->getTableName(true) . " where `object_type_id` = ? $visibility_cond)"  , $object->getObjectId(), $object->getObjectTypeId())
		))); // findAll
	} //  getCustomPropertyValue

	
} // CustomProperties

?>
