<?php

function _tz_upgrade_db_execute($sql, $database_connection) {
	$rows = array();
	
	$result = mysqli_query($database_connection, $sql);
	if ($result) {
		if (gettype($result) == 'boolean') return;
		
		while($r = mysqli_fetch_assoc($result)) {
			$rows[] = $r;
		}
	}
	
	return $rows;
}

function _tz_upgrade_get_contact_country($table_prefix, $database_connection, $contact_id) {
	$rows = _tz_upgrade_db_execute("SELECT country FROM ".$table_prefix."contact_addresses 
			WHERE contact_id='$contact_id' AND country<>''
			LIMIT 1", $database_connection);
	if (count($rows) > 0) {
		return $rows[0]['country'];
	}
	return '';
}

function _tz_upgrade_get_owner_company_zone($table_prefix, $database_connection) {
	$z = null;
	
	$owner_comp_rows = _tz_upgrade_db_execute("SELECT * FROM ".$table_prefix."contacts WHERE is_company>0 ORDER BY object_id LIMIT 1", $database_connection);
	$owner_comp = count($owner_comp_rows)>0 ? $owner_comp_rows[0] : null;
	
	if (is_array($owner_comp)) {
		$zones = _tz_upgrade_db_execute("SELECT * FROM ".$table_prefix."timezones WHERE id='".$owner_comp['user_timezone_id']."' LIMIT 1", $database_connection);
		if (count($zones)>0) $z = $zones[0];
	}
	
	return $z;
}

/**
 * update user and company time zones 
 */
function _tz_upgrade_user_and_company_timezones($table_prefix, $database_connection) {
	
	$users_and_companies = _tz_upgrade_db_execute("SELECT * FROM ".$table_prefix."contacts WHERE user_type>0 OR is_company>0", $database_connection);
	foreach ($users_and_companies as $contact) {
		// get country from user address
		$ccode = _tz_upgrade_get_contact_country($table_prefix, $database_connection, $contact['object_id']);
		
		if (!$ccode && $contact['company_id'] > 0) {
			// get country from user company address
			$ccode = _tz_upgrade_get_contact_country($table_prefix, $database_connection, $contact['company_id']);
		}
		
		$offset = $contact['timezone'] * 3600;
		
		$offset_column = defined('UPGRADE_TIMEZONE_DST') && UPGRADE_TIMEZONE_DST ? 'gmt_dst_offset' : 'gmt_offset';
		$second_offset_column = defined('UPGRADE_TIMEZONE_DST') && UPGRADE_TIMEZONE_DST ? 'gmt_offset' : 'gmt_dst_offset';
		
		$zones = null;
		if ($ccode) {
			// get the first time zone in the contact country with the same offset
			$ccode = strtoupper($ccode);
			$zones = _tz_upgrade_db_execute("SELECT * FROM ".$table_prefix."timezones WHERE country_code='$ccode' AND $offset_column ='$offset' LIMIT 1", $database_connection);
			
			// if not found check by the other gmt column in the same country
			if (!is_array($zones) || count($zones) == 0) {
				$zones = _tz_upgrade_db_execute("SELECT * FROM ".$table_prefix."timezones WHERE country_code='$ccode' AND $second_offset_column ='$offset' LIMIT 1", $database_connection);
			}

		}
		
		if (!is_array($zones) || count($zones) == 0) {
			// if not found, check if owner company has the same offset, in that case use it.
			$owner_comp_z = _tz_upgrade_get_owner_company_zone($table_prefix, $database_connection);
			if ($owner_comp_z && ($owner_comp_z[$offset_column] == $offset || $owner_comp_z[$second_offset_column] == $offset )) {
				$zones = array($owner_comp_z);
			}
				
			// if not found check only by offset
			if (!is_array($zones) || count($zones) == 0) {
				$zones = _tz_upgrade_db_execute("SELECT * FROM ".$table_prefix."timezones WHERE $offset_column ='$offset' LIMIT 1", $database_connection);
			}
		}
		
		$zone_id = (count($zones) > 0 ? $zones[0]['id'] : 0);
		
		// use config default zone
		if (defined('UPGRADE_TIMEZONE_ID')) {
			$def_zones = _tz_upgrade_db_execute("SELECT * FROM ".$table_prefix."timezones WHERE id='".UPGRADE_TIMEZONE_ID."' LIMIT 1", $database_connection);
			$def_zone = count($def_zones) > 0 ? $def_zones[0] : array();
			
			if ($def_zone) {
				$prev_offset = $contact['timezone'] * 3600;
				if ($prev_offset == $def_zone[$offset_column] || $def_zone[$second_offset_column] == $prev_offset) {
					$zone_id = UPGRADE_TIMEZONE_ID;
				}
			}
		}
		
		if ($zone_id) {
			_tz_upgrade_db_execute("UPDATE ".$table_prefix."contacts SET user_timezone_id='$zone_id' WHERE object_id='".$contact['object_id']."'", $database_connection);
		}
	}
}


/**
 * Update config option with the default timezone
 * @param string $table_prefix
 * @param db_connection object $database_connection
 */
function _tz_upgrade_default_system_timezone($table_prefix, $database_connection) {
	$zone_id = null;
	
	if (defined('UPGRADE_TIMEZONE_ID')) {
		
		$zone_id = UPGRADE_TIMEZONE_ID;
		
	} else {
	
		$rows = _tz_upgrade_db_execute("SELECT * FROM ".$table_prefix."contacts WHERE is_company>0 ORDER BY object_id LIMIT 1", $database_connection);
		
		// use owner company time zone
		if (is_array($rows) && count($rows) > 0) {
			$owner_company = $rows[0];
			$zone_id = $owner_company['user_timezone_id'];
			
			if (!$zone_id) {
				// if owner company does not have time zone use the superadmin time zone
				$rows = _tz_upgrade_db_execute("SELECT * FROM ".$table_prefix."contacts WHERE is_company>0 ORDER BY object_id LIMIT 1", $database_connection);
				if (is_array($rows) && count($rows) > 0) {
					$administrator = $rows[0];
					$zone_id = $owner_company['user_timezone_id'];
				}
			}
			
		}
	}
	
	if ($zone_id) {
		_tz_upgrade_db_execute("UPDATE ".$table_prefix."config_options SET value='$zone_id' WHERE name='default_timezone'", $database_connection);
	}
}


/**
 * Update object timezone id and offset with the timezone of its creator
 * @param string $table_prefix
 * @param object db_connection $database_connection
 */
function _tz_upgrade_objects_timezone($table_prefix, $database_connection) {
	
	$user_rows = _tz_upgrade_db_execute("
			SELECT c.object_id as user_id, t.*, t.id as tz_id 
			FROM ".$table_prefix."contacts c 
			LEFT JOIN ".$table_prefix."timezones t ON t.id=c.user_timezone_id
			WHERE user_type>0;
	", $database_connection);
	
	foreach ($user_rows as $user) {
		
		if (defined('UPGRADE_TIMEZONE_DST')) {
			$offset = UPGRADE_TIMEZONE_DST ? $user['gmt_dst_offset'] : $user['gmt_offset'];
		} else {
			$offset = $user['using_dst'] ? $user['gmt_dst_offset'] : $user['gmt_offset'];
		}
		$zone_id = $user['tz_id'];
		
		$sql = "
				UPDATE ".$table_prefix."objects
				SET timezone_id='$zone_id', timezone_value='$offset'
				WHERE created_by_id='".$user['user_id']."'
		";
		_tz_upgrade_db_execute($sql, $database_connection);
	}
}


