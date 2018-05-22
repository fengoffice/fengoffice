<?php

  /**
  * PermissionGroups
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class PermissionGroups extends BasePermissionGroups {
    
  	function getUserTypeGroups($order = '`name` ASC') {
  		return self::findAll(array("conditions" => "`contact_id` = 0 AND `parent_id` = 0 AND `type`='roles'", "order" => $order));
  	}
  	
    function getNonPersonalPermissionGroups($order = '`name` ASC') {
    	return self::findAll(array("conditions" => "`contact_id` = 0 AND `parent_id` != 0 AND `type`='roles'", "order" => $order));
    }
    function getNonPersonalSameLevelPermissionsGroups($order = '`name` ASC') {
    	return self::findAll(array("conditions" => "`contact_id` = 0 AND `parent_id` != 0 AND `type`='roles' AND `id` >= ".logged_user()->getUserType(), "order" => $order));
    }
    function getParentId($group_id){
    	return self::findById($group_id)->getParentId();
    }
    
    function getGuestPermissionGroups() {
    	return self::findAll(array("conditions" => "parent_id IN (SELECT p.id FROM ".TABLE_PREFIX."permission_groups p WHERE p.name='GuestGroup')"));
    }
    
    function getCollaboratorPermissionGroups() {
    	return self::findAll(array("conditions" => "parent_id IN (SELECT p.id FROM ".TABLE_PREFIX."permission_groups p WHERE p.name='CollaboratorGroup')"));
    }
    
    function getExecutivePermissionGroups() {
    	return self::findAll(array("conditions" => "parent_id IN (SELECT p.id FROM ".TABLE_PREFIX."permission_groups p WHERE p.name='ExecutiveGroup')"));
    }
    
    static function getNonRolePermissionGroups() {
		$order = '`name` ASC';
        return self::findAll(array("conditions" => "`type` = 'user_groups'",  "order" => $order));
    }
    
    function getDefaultRolesByType() {
    	$result = array();
    	
    	$exe_group = PermissionGroups::findOne(array('id' => true, 'conditions' => "name='ExecutiveGroup' AND type='roles'"));
    	$col_group = PermissionGroups::findOne(array('id' => true, 'conditions' => "name='CollaboratorGroup' AND type='roles'"));
    	$gue_group = PermissionGroups::findOne(array('id' => true, 'conditions' => "name='GuestGroup' AND type='roles'"));
    	
    	$exe = PermissionGroups::findOne(array('id' => true, 'conditions' => "name='Executive' AND type='roles'"));
    	$col = PermissionGroups::findOne(array('id' => true, 'conditions' => "name='Internal Collaborator' AND type='roles'"));
    	$gue = PermissionGroups::findOne(array('id' => true, 'conditions' => "name='Guest' AND type='roles'"));
    	
    	$result[$exe_group[0]] = $exe[0];
    	$result[$col_group[0]] = $col[0];
    	$result[$gue_group[0]] = $gue[0];
    	
    	return $result;
    }
    
    function getUserGroupsInfo($extra_conditions = "", $order = "name", $escape=true) {
    	$result = array();
    	$extra_cond = "type = 'user_groups'";
    	$extra_cond .= $extra_conditions ? $extra_conditions : "";
    	$pgs = self::findAll(array('conditions' => $extra_cond, 'order' => $order));
    	foreach ($pgs as $pg) {
    		$result[$pg->getId()] = array('id' => $pg->getId());
    		if ($escape) {
    			$result[$pg->getId()]['name'] = escape_character($pg->getName());
    		} else {
    			$result[$pg->getId()]['name'] = str_replace("'", "&apos;", $pg->getName());
    		}
    	}
    	return $result;
    }

    /**
     * Get all groups with root permissions for an object type
     *
     * @param integer $object_type_id  Object type id
     * @return array containing the groups ids
     */
    static function get_groups_with_root_permissions_by_object_type($object_type_id){

        //Exclude non manageable dimensions
        $main_select_sql = "
		  SELECT mp.permission_group_id
		  FROM ".TABLE_PREFIX."contact_member_permissions mp
		  WHERE mp.member_id=0
		  AND mp.object_type_id = $object_type_id
	    ";

        $rows = DB::executeAll($main_select_sql);

        return array_filter(array_flat($rows));
    }

    /**
     * Get all groups with permissions on a classified object
     *
     * @param integer $object_id  Object id
     * @return array containing the groups ids
     */
    static function get_groups_with_permissions_on_a_classified_object($object_id){
        // Check mandatory dimensions, if an objects belongs to a member in a mandatory dimension then the permission group must have permissions in the member,
        // if user doesn't have permissions there, then the user cannot read the object, no matter what other permissions are active
        $mdim_conds = array();
        $enabled_dimensions_sql = "";
        $enabled_dimensions_ids = implode(',', config_option('enabled_dimensions'));
        if ($enabled_dimensions_ids != "") {
            $enabled_dimensions_sql = " AND id IN ($enabled_dimensions_ids) ";
        }

        $mandatory_dims_sql = "";
        $mandatory_dim_ids = Dimensions::findAll(array(
            'id' => true,
            'conditions' => "`defines_permissions`=1 $enabled_dimensions_sql AND `permission_query_method`='".DIMENSION_PERMISSION_QUERY_METHOD_MANDATORY."'"
        ));
        if (count($mandatory_dim_ids) > 0) {
            foreach ($mandatory_dim_ids as $md_id) {
                $mdim_conds[] = "
			AND IF (
				(SELECT count(om1.object_id) FROM ".TABLE_PREFIX."object_members om1 
					INNER JOIN ".TABLE_PREFIX."members m1 ON m1.id=om1.member_id
					WHERE om1.object_id=o.id AND om1.is_optimization=0 AND m1.dimension_id=$md_id)=0,
				true,
				EXISTS (
					SELECT cmp.permission_group_id FROM ".TABLE_PREFIX."contact_member_permissions cmp
					INNER JOIN ".TABLE_PREFIX."object_members om2 ON om2.member_id = cmp.member_id
					INNER JOIN ".TABLE_PREFIX."members m2 ON m2.id = om2.member_id
					WHERE
					m2.dimension_id=$md_id
					AND cmp.object_type_id = o.object_type_id
					AND om2.object_id=o.id 
					AND om2.is_optimization=0 
					AND cmp.permission_group_id=mp.permission_group_id
				)
			)
			";
            }
        }

        //Intersect the mandatory dimensions in the main sql
        foreach ($mdim_conds as $mdim_cond) {
            $mandatory_dims_sql .= $mdim_cond;
        }

        //Exclude non manageable dimensions and archived members
        $main_select_sql = "
		SELECT mp.permission_group_id
		FROM ".TABLE_PREFIX."object_members om
		INNER JOIN ".TABLE_PREFIX."objects o ON o.id = om.object_id AND o.id = $object_id
		INNER JOIN ".TABLE_PREFIX."contact_member_permissions mp ON mp.member_id = om.member_id AND mp.object_type_id = o.object_type_id
		INNER JOIN ".TABLE_PREFIX."members m ON m.id=om.member_id
		INNER JOIN ".TABLE_PREFIX."dimensions d ON d.id=m.dimension_id
		WHERE d.is_manageable = 1
		AND d.defines_permissions = 1
		AND m.archived_on = 0
		AND mp.member_id != 0
		AND om.is_optimization = 0
		$mandatory_dims_sql
	    ";

        $rows = DB::executeAll($main_select_sql);

        return array_unique(array_filter(array_flat($rows)));
    }
    /**
     * Get all object ids by permissions group (only classified objects)
     *
     * @param integer $permission_group_id  Permission Group id
     * @param array $object_type_ids  Object type ids
     * @param array $members_ids  Members ids
     * @return array containing the object ids
     */
    static function get_classified_objects_ids_by_permission_group($permission_group_id, $object_type_ids = null, $members_ids = null){

        // Check mandatory dimensions, if an objects belongs to a member in a mandatory dimension then the permission group must have permissions in the member,
        // if user doesn't have permissions there, then the user cannot read the object, no matter what other permissions are active
        $mdim_conds = array();
        $enabled_dimensions_sql = "";
        $enabled_dimensions_ids = implode(',', config_option('enabled_dimensions'));
        if ($enabled_dimensions_ids != "") {
            $enabled_dimensions_sql = " AND id IN ($enabled_dimensions_ids) ";
        }
        $mandatory_dims_sql = "";
        $mandatory_dim_ids = Dimensions::findAll(array(
            'id' => true,
            'conditions' => "`defines_permissions`=1 $enabled_dimensions_sql AND `permission_query_method`='".DIMENSION_PERMISSION_QUERY_METHOD_MANDATORY."'"
        ));
        if (count($mandatory_dim_ids) > 0) {
            foreach ($mandatory_dim_ids as $md_id) {
                $mdim_conds[] = "
			    AND IF (
				    (SELECT count(om1.object_id) FROM ".TABLE_PREFIX."object_members om1 
					    INNER JOIN ".TABLE_PREFIX."members m1 ON m1.id=om1.member_id
					    WHERE om1.object_id=o.id AND om1.is_optimization=0 AND m1.dimension_id=$md_id)=0,
				    true,
				    EXISTS (
					SELECT cmp.permission_group_id FROM ".TABLE_PREFIX."contact_member_permissions cmp
					INNER JOIN ".TABLE_PREFIX."object_members om2 ON om2.member_id = cmp.member_id
					INNER JOIN ".TABLE_PREFIX."members m2 ON m2.id = om2.member_id
					WHERE
					m2.dimension_id=$md_id
					AND cmp.object_type_id = o.object_type_id
					AND om2.object_id=o.id 
					AND om2.is_optimization=0 
					AND cmp.permission_group_id= $permission_group_id
				    )
			    )";
            }
        }

        //Intersect the mandatory dimensions in the main sql
        foreach ($mdim_conds as $mdim_cond) {
            $mandatory_dims_sql .= $mdim_cond;
        }

        $object_type_sql = "";
        if(!is_null($object_type_ids)){
            $object_type_sql = " AND mp.object_type_id IN (".implode(',',$object_type_ids).") ";
        }

        $members_ids_sql = "";
        if(!is_null($members_ids)){
            $members_ids_sql = " AND mp.member_id IN (".implode(',',$members_ids).") ";
        }

        //Exclude non manageable dimensions and archived members
        $main_select_sql = "
		SELECT o.id
		FROM ".TABLE_PREFIX."object_members om
		INNER JOIN ".TABLE_PREFIX."objects o ON o.id = om.object_id
		INNER JOIN ".TABLE_PREFIX."contact_member_permissions mp ON mp.member_id = om.member_id AND mp.object_type_id = o.object_type_id
		INNER JOIN ".TABLE_PREFIX."members m ON m.id=om.member_id
		INNER JOIN ".TABLE_PREFIX."dimensions d ON d.id=m.dimension_id
		WHERE d.is_manageable = 1
		AND mp.permission_group_id = $permission_group_id
		AND d.defines_permissions = 1
		AND m.archived_on = 0
		AND mp.member_id != 0
		AND om.is_optimization = 0
		$object_type_sql
		$members_ids_sql
		$mandatory_dims_sql
	    ";

        $rows = DB::executeAll($main_select_sql);

        $return_array = array_filter(array_flat($rows));

        Hook::fire("get_classified_objects_ids_by_permission_group_modify_object_ids", array("permission_group_id" => $permission_group_id, "object_type_ids" => $object_type_ids, "members_ids" => $members_ids), $return_array);


        return $return_array;
    }

  } // PermissionGroups 

?>