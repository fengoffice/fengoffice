<?php

/**
 * MaxSystemPermission class
 *
 * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
 */
class MaxSystemPermission extends BaseMaxSystemPermission {
		
	function getColumnValue($column_name, $default = null) {
		if ($this->manager()->columnExists($column_name)) {
			return parent::getColumnValue($column_name, $default);
		}
		$column_exists = false;
		$rows = DB::executeAll("DESCRIBE ".$this->manager()->getTableName());
		foreach ($rows as $row) {
			if ($row['Field'] == $column_name) $column_exists = true;
		}
		if ($column_exists) {
			$res = DB::executeAll("SELECT $column_name FROM ".$this->manager()->getTableName()." WHERE permission_group_id=".$this->getPermissionGroupId());
			if (count($res) > 0) return $res[0][$column_name];
		}
		return false;
	}

}