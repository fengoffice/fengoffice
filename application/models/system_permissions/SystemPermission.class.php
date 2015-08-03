<?php

/**
 * SystemPermission class
 *
 * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
class SystemPermission extends BaseSystemPermission {
		
	function setAllPermissions($value){
		$columns = $this->manager()->getColumns();
		foreach ($columns as $col) {
			if (in_array($col, array('permission_group_id'))) continue;
			$this->setColumnValue($col, $value);
		}
		$columns = null;
	}
	
	function setPermission($value){
		$this->setColumnValue($value, 1);
	}
	
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