<?php
class Timezones {
	
	private static $id_cache = array ();
	private static $name_cache = array ();
	
	static function getTimezoneById($zone_id) {
		$zone = null;
		
		if (! isset(self::$id_cache[$zone_id])) {
			$zone = DB::executeOne("SELECT * FROM " . TABLE_PREFIX . "timezones WHERE id=" . DB::escape($zone_id));
			if ($zone) {
				self::$id_cache[$zone['id']] = $zone;
			}
		} else {
			$zone = self::$id_cache[$zone_id];
		}
		
		return $zone;
	}
	
	
	static function getTimezoneFromName($zone_name) {
		$zone = null;
		
		if (! isset(self::$name_cache[$zone_name])) {
			$zone_name = DB::escape($zone_name);
			$zone = DB::executeOne("SELECT * FROM " . TABLE_PREFIX . "timezones WHERE name=$zone_name");
			if ($zone) {
				self::$name_cache[$zone['name']] = $zone;
			}
		} else {
			$zone = self::$name_cache[$zone_name];
		}
		
		return $zone;
	}
	
	
	static function getTimezoneName($zone_id) {
		$name = "";
		$zone = self::getTimezoneById($zone_id);
		if (is_array($zone)) {
			$name = array_var($zone, 'name');
		}
		
		return $name;
	}
	
	
	static function getTimezoneOffset($zone_id) {
		$zone_offset = 0;
		
		$zone = self::getTimezoneById($zone_id);
		if (is_array($zone)) {
			if ($zone['has_dst'] && $zone['using_dst']) {
				$zone_offset = $zone['gmt_dst_offset'];
			} else {
				$zone_offset = $zone['gmt_offset'];
			}
		}
		
		return $zone_offset;
	}
	
	
	static function getTimezoneOffsetToApply($object, $user = null) {
		if (! $object instanceof ContentDataObject) {
			if (is_array($object) && isset($object['id'])) {
				$object = Objects::findObject($object['id']);
			}
			if (! $object instanceof ContentDataObject) return;
		}

		$object_arr = array();
		$object_arr['timezone_id'] = $object->getTimezoneId();
		$object_arr['timezone_value'] = $object->getTimezoneValue();

		$tz_value = self::getTimezoneOffsetToApplyFromArray($object_arr, $user);
		
		return $tz_value;
	}

	static function getTimezoneOffsetToApplyFromArray($object_arr, $user = null) {
		$tz_value = 0;

		if (! $user instanceof Contact) {
			$user = logged_user();
			if (! $user instanceof Contact) {
				return;
			}
		}

		if(!array_key_exists('timezone_id', $object_arr) || !array_key_exists('timezone_value', $object_arr) ){
			return;
		}

		if ($object_arr['timezone_id'] == $user->getUserTimezoneId()) {
			//$tz_value = $object_arr['timezone_value'];
			$tz_value = $user->getUserTimezoneValue();
		} else {
			$tz_value = self::getTimezoneOffset($user->getUserTimezoneId());

			//check if object gmt is different from the object timezone
			$z = self::getTimezoneById($object_arr['timezone_id']);
			$dst_diff = 0;
			if ($z && $z['has_dst']) {
				if($z['using_dst']){
					if($z['gmt_dst_offset'] != $object_arr['timezone_value']){
						$dst_diff = $object_arr['timezone_value'] - $z['gmt_dst_offset'];
					}
				}else{
					if($z['gmt_offset'] != $object_arr['timezone_value']) {
						$dst_diff = $object_arr['timezone_value'] - $z['gmt_offset'];
					}
				}
				$tz_value = $tz_value + $dst_diff;
			}
		}

		return $tz_value;
	}
	
	
	static function updateUsingDst($zone_id, $using_dst) {
		$z = self::getTimezoneById($zone_id);
		if ($z && $z['has_dst']) {
			DB::execute("UPDATE ".TABLE_PREFIX."timezones SET using_dst=".DB::escape($using_dst)." WHERE id=".$z['id']);
		}
	}
	
	
	
	static function getTimezonesByCountryCode($country_code) {
		$escaped = DB::escape($country_code);
		$zones = DB::executeAll("SELECT * FROM ".TABLE_PREFIX."timezones WHERE country_code=$escaped");
		
		return $zones;
	}
	
	
	static function getAllTimezonesGroupedByCountry() {
		$grouped_zones = array();
		
		$zones = DB::executeAll("SELECT * FROM ".TABLE_PREFIX."timezones");
		foreach ($zones as $z) {
			if (!isset($grouped_zones[$z['country_code']])) $grouped_zones[$z['country_code']] = array();
			$grouped_zones[$z['country_code']][] = $z;
		}
		
		return $grouped_zones;
	}
	
	static function getFormattedOffset($zone_offset_s) {
		
		$zone_offset = $zone_offset_s / 3600;
		
		$hs = floor(abs($zone_offset));
		$mins = (abs($zone_offset) - $hs)*60;
		$zone_offset_formatted = str_pad($hs, 2, "0", STR_PAD_LEFT) . ":" . str_pad($mins, 2, "0", STR_PAD_LEFT);
		
		return "GMT ". ($zone_offset<0 ?"-":"+") . $zone_offset_formatted;
	}
	
	static function getFormattedDescription($zone, $return_array = false) {
		if (is_numeric($zone)) {
			$zone = self::getTimezoneById($zone);
		}
		
		$zone_offset_s = $zone['has_dst'] && $zone['using_dst'] ? $zone['gmt_dst_offset'] : $zone['gmt_offset'];
		$zone_offset_formatted = self::getFormattedOffset($zone_offset_s);
		
		$zone_name = str_replace('_', ' ', $zone['name']);
		$zone_name = str_replace('/', ' / ', $zone_name);
		
		if ($return_array) {
			
			$zone_description = array(
					'name' => $zone_name,
					'offset' => self::getFormattedOffset($zone['gmt_offset']),
					'dst_offset' => self::getFormattedOffset($zone['gmt_dst_offset']),
			);
			
		} else {
			
			$zone_description = $zone_name . " (" . $zone_offset_formatted .")";
			
		}
		
		return $zone_description;
	}
	
}
