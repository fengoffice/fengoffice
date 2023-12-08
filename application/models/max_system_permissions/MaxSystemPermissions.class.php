<?php


  /**
  * MaxSystemPermissions
  *
  * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
  */
  class MaxSystemPermissions extends BaseMaxSystemPermissions {
    
  	static function getAllMaxRolesPermissions(){
  		$result = array();
  		$all_max_perm = self::instance()->findAll();
  		$cols = self::instance()->getColumns();// get_table_columns(self::instance()->getTableName());
  		
  		foreach ($all_max_perm as $perm) {
  			$result[$perm->getPermissionGroupId()] = array();
  			foreach ($cols as $col) {
  				if ($perm->getColumnValue($col)) {
  					$result[$perm->getPermissionGroupId()][] = $col;
  				}
  			}
  		}
  		
  		return $result;
  	}
  	
  } // MaxSystemPermissions 
