<?php

/**
 * Role-based default list-column configuration.
 *
 * Newly created users inherit a default set of list columns (order/visibility/width) for
 * every module list, so they don't start with the generic JavaScript defaults.
 *
 * Two storage backends are involved, mirroring how the application persists list columns:
 *   - ExtJS lists  -> `guistate` table (one opaque ExtJS-encoded blob per grid).
 *   - Tasks list   -> `task panel columns config` user config option (JSON).
 *
 * The defaults are kept per role in `role_default_list_config` (role_id = 0 is the wildcard
 * fallback applied to every role). Values stored there are already resolved to THIS install's
 * numeric ids; resolution from the portable {{...}} token form happens once, when the store is
 * seeded (see role_default_list_config_seed()).
 *
 * Install-specific ids (custom properties, dimensions, object types) are NOT portable between
 * databases, so the shipped defaults are expressed symbolically and resolved at run time:
 *   {{CP~~<object_type_name>~~C~~<code>}}  custom property by code
 *   {{CP~~<object_type_name>~~N~~<name>}}  custom property by name (for empty-code properties)
 *   {{DIM~~<dimension_code>}}              dimension by code
 *   {{OT~~<object_type_name>}}             object type by name (used inside member-manager grid ids)
 */

/**
 * Resolve {{...}} tokens inside a stored value blob to this install's numeric ids.
 * Unresolved custom property / dimension tokens collapse to id 0 (a harmless non-matching
 * column id that ExtJS ignores when applying the saved state).
 *
 * @param string $str
 * @return string
 */
function role_default_list_config_resolve_value($str) {
	$str = preg_replace_callback('/\{\{CP~~(.*?)~~([CN])~~(.*?)\}\}/', function ($m) {
		$ot_id = role_default_list_config_object_type_id($m[1]);
		if (!$ot_id) return '0';
		$cp_id = ($m[2] === 'C')
			? role_default_list_config_cp_id_by_code($ot_id, $m[3])
			: role_default_list_config_cp_id_by_name($ot_id, $m[3]);
		return $cp_id ? (string) $cp_id : '0';
	}, $str);

	$str = preg_replace_callback('/\{\{DIM~~(.*?)\}\}/', function ($m) {
		$dim_id = role_default_list_config_dimension_id($m[1]);
		return $dim_id ? (string) $dim_id : '0';
	}, $str);

	// {{OT~~name}} -> object type id (or 0). Used e.g. by tasksGroupBy (dimmembertypeid_<dim>_<ot>).
	$str = preg_replace_callback('/\{\{OT~~(.*?)\}\}/', function ($m) {
		$ot_id = role_default_list_config_object_type_id($m[1]);
		return $ot_id ? (string) $ot_id : '0';
	}, $str);

	return $str;
} // role_default_list_config_resolve_value

/**
 * Resolve {{...}} tokens inside a grid name (the guistate `name`). Member-manager grid ids
 * embed a dimension id and an object type id. If either is missing in this install the grid
 * is irrelevant here, so we return false to signal "skip this row".
 *
 * @param string $name
 * @return string|false
 */
function role_default_list_config_resolve_name($name) {
	$ok = true;
	$name = preg_replace_callback('/\{\{DIM~~(.*?)\}\}/', function ($m) use (&$ok) {
		$dim_id = role_default_list_config_dimension_id($m[1]);
		if (!$dim_id) { $ok = false; return '0'; }
		return (string) $dim_id;
	}, $name);
	$name = preg_replace_callback('/\{\{OT~~(.*?)\}\}/', function ($m) use (&$ok) {
		$ot_id = role_default_list_config_object_type_id($m[1]);
		if (!$ot_id) { $ok = false; return '0'; }
		return (string) $ot_id;
	}, $name);
	return $ok ? $name : false;
} // role_default_list_config_resolve_name

/**
 * Populate `role_default_list_config` for a role from a set of tokenized seed rows.
 * Re-runnable: clears the role's existing rows first. Resolution uses this install's ids.
 *
 * @param array $seed_rows  array of ['storage'=>..., 'name'=>..., 'value'=>...]
 * @param int   $role_id    0 = wildcard fallback for every role
 * @return int  number of rows written
 */
function role_default_list_config_seed($seed_rows, $role_id = 0) {
	if (!is_array($seed_rows)) return 0;

	DB::execute("DELETE FROM " . TABLE_PREFIX . "role_default_list_config WHERE role_id = ?", $role_id);

	$insert_values = array();
	foreach ($seed_rows as $row) {
		$storage = array_var($row, 'storage');
		$name    = array_var($row, 'name');
		$value   = array_var($row, 'value');

		if ($storage == 'guistate') {
			$resolved_name = role_default_list_config_resolve_name($name);
			if ($resolved_name === false) continue; // grid not present in this install
			$resolved_value = role_default_list_config_resolve_value($value);
			$insert_values[] = "(" . DB::escape($role_id) . "," . DB::escape('guistate') . "," . DB::escape($resolved_name) . "," . DB::escape($resolved_value) . ")";
		} else if ($storage == 'config_option') {
			$resolved_value = role_default_list_config_resolve_value($value);
			$insert_values[] = "(" . DB::escape($role_id) . "," . DB::escape('config_option') . "," . DB::escape($name) . "," . DB::escape($resolved_value) . ")";
		}
	}

	if (count($insert_values) > 0) {
		DB::execute("INSERT INTO " . TABLE_PREFIX . "role_default_list_config (role_id, storage, name, value) VALUES " . implode(",", $insert_values));
	}
	return count($insert_values);
} // role_default_list_config_seed

/**
 * Apply the default list-column configuration to a newly created user. Copies the role's rows
 * (or the role_id = 0 fallback) verbatim into the user's guistate and task-columns config.
 * Idempotent: does nothing if the user already has any saved guistate.
 *
 * @param Contact $user
 * @return void
 */
function apply_role_default_list_config($user) {
	if (!($user instanceof Contact)) return;
	$user_id = $user->getId();

	$existing = DB::executeOne("SELECT COUNT(*) AS c FROM " . TABLE_PREFIX . "guistate WHERE contact_id = ?", $user_id);
	if ($existing && (int) array_var($existing, 'c') > 0) return;

	$role_id = $user->getUserType();
	$rows = DB::executeAll("SELECT storage, name, value FROM " . TABLE_PREFIX . "role_default_list_config WHERE role_id = ?", $role_id);
	if (!is_array($rows) || count($rows) == 0) {
		$rows = DB::executeAll("SELECT storage, name, value FROM " . TABLE_PREFIX . "role_default_list_config WHERE role_id = 0");
	}
	if (!is_array($rows) || count($rows) == 0) return;

	$guistate_values = array();
	foreach ($rows as $row) {
		if ($row['storage'] == 'guistate') {
			$value = role_default_list_config_mark_state_as_default($row['value']);
			$guistate_values[] = "(" . DB::escape($user_id) . "," . DB::escape($row['name']) . "," . DB::escape($value) . ")";
		} else if ($row['storage'] == 'config_option') {
			// {{USER_ID}} resolves per user (e.g. the tasks "assigned to" filter defaults to the
			// new user's own tasks).
			$value = str_replace('{{USER_ID}}', $user_id, $row['value']);
			if ($row['name'] == 'task panel columns config') {
				ensure_task_columns_config_option();
			}
			set_user_config_option($row['name'], $value, $user_id);
		}
	}

	if (count($guistate_values) > 0) {
		DB::execute("INSERT INTO " . TABLE_PREFIX . "guistate (contact_id, name, value) VALUES " . implode(",", $guistate_values));
	}
} // apply_role_default_list_config

/**
 * Flag a guistate blob as a role default, i.e. as the COMPLETE column layout for that grid.
 *
 * The shipped defaults only name the columns that existed in the reference install. Columns
 * that exist solely in this install (dimension columns enabled through
 * lp_dim_<code>_show_as_column, custom properties with show_in_lists = 1, member type columns)
 * are not mentioned by the state, and ExtJS' applyState() leaves every unmentioned column
 * untouched: they are built visible and pile up at the end of the list. The flag tells the
 * client to read "not in the state" as "hidden" (see ColumnManager.js applyState override).
 *
 * ExtJS encodes each nested value with escape(), so an extra member of the top level state
 * object reads `^ogRoleDefaults=` + escape('b:1'), and the whole object is escaped once more
 * when stored. The flag only lives in the copy handed to a new user: getState() rebuilds the
 * blob from scratch, so it is gone the first time the user rearranges the grid himself.
 *
 * @param string $value  guistate value as stored in role_default_list_config
 * @return string
 */
function role_default_list_config_mark_state_as_default($value) {
	$flag = '%5EogRoleDefaults%3Db%253A1';

	// Only ExtJS encoded objects ("o:...") can carry extra members.
	if (!is_string($value) || strpos($value, 'o%3A') !== 0) return $value;
	if (strpos($value, $flag) !== false) return $value;

	return $value . $flag;
} // role_default_list_config_mark_state_as_default

/**
 * Ensure the 'task panel columns config' user config option definition exists. The tasks list
 * creates it lazily on first save; we need it available when seeding a new user's value too.
 *
 * @return bool
 */
function ensure_task_columns_config_option() {
	$option_name = 'task panel columns config';
	$opt = ContactConfigOptions::getByName($option_name);
	if ($opt instanceof ContactConfigOption) return true;

	try {
		DB::beginWork();
		$opt = new ContactConfigOption();
		$opt->setName($option_name);
		$opt->setCategoryName('task panel');
		$opt->setIsSystem(true);
		$opt->setDefaultValue('');
		$opt->setConfigHandlerClass('StringConfigHandler');
		$opt->save();
		DB::commit();
	} catch (Exception $e) {
		DB::rollback();
		return false;
	}
	return true;
} // ensure_task_columns_config_option

// ---------------------------------------------------------------------------
// Internal id lookups (per-request cached, direct SQL so they work in console/
// upgrade context where there is no logged user).
// ---------------------------------------------------------------------------

function role_default_list_config_object_type_id($name) {
	static $cache = array();
	if (array_key_exists($name, $cache)) return $cache[$name];
	$row = DB::executeOne("SELECT id FROM " . TABLE_PREFIX . "object_types WHERE name = ? LIMIT 1", $name);
	return $cache[$name] = ($row ? (int) $row['id'] : 0);
}

function role_default_list_config_dimension_id($code) {
	static $cache = array();
	if (array_key_exists($code, $cache)) return $cache[$code];
	$row = DB::executeOne("SELECT id FROM " . TABLE_PREFIX . "dimensions WHERE code = ? LIMIT 1", $code);
	return $cache[$code] = ($row ? (int) $row['id'] : 0);
}

function role_default_list_config_cp_id_by_code($ot_id, $code) {
	static $cache = array();
	$key = $ot_id . '|' . $code;
	if (array_key_exists($key, $cache)) return $cache[$key];
	$row = DB::executeOne("SELECT id FROM " . TABLE_PREFIX . "custom_properties WHERE object_type_id = ? AND code = ? LIMIT 1", $ot_id, $code);
	return $cache[$key] = ($row ? (int) $row['id'] : 0);
}

function role_default_list_config_cp_id_by_name($ot_id, $name) {
	static $cache = array();
	$key = $ot_id . '|' . $name;
	if (array_key_exists($key, $cache)) return $cache[$key];
	// Name tokens are only emitted for empty-code custom properties, and a single object type
	// can hold two properties with the same name (one coded, one not). Prefer the empty-code
	// one; fall back to any match only if this install has since given it a code.
	$row = DB::executeOne("SELECT id FROM " . TABLE_PREFIX . "custom_properties WHERE object_type_id = ? AND name = ? AND (code IS NULL OR code = '') LIMIT 1", $ot_id, $name);
	if (!$row) {
		$row = DB::executeOne("SELECT id FROM " . TABLE_PREFIX . "custom_properties WHERE object_type_id = ? AND name = ? LIMIT 1", $ot_id, $name);
	}
	return $cache[$key] = ($row ? (int) $row['id'] : 0);
}
