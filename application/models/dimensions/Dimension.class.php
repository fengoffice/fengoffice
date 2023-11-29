<?php

/**
 * Dimension class
 *
 * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
class Dimension extends BaseDimension {
	
	private $options_cache = array();
	
	function getAllMembers($only_ids = false, $order = null, $filter_deleted_objects = false, $extra_conditions = "", $limit = null, $order_dir = null) {
		$contactsType = ObjectTypes::instance()->findByName('person');
		if ($contactsType) {
			$contactsTypeId = $contactsType->getId();
		}
		
		$parameters = array(
			'conditions' => '`dimension_id` = ' . $this->getId(), 'id' => $only_ids
		);
		if (!is_null($order)) { 
			$parameters['order'] = $order;
		}
		if (!is_null($order_dir)) { 
			$parameters['order_dir'] = $order_dir;
		}
		
		if (!is_null($limit)) {
			if (is_array($limit)) {
				if (isset($limit['offset'])) {
					$parameters['offset'] = $limit['offset'];
				}
				if (isset($limit['limit'])) {
					$parameters['limit'] = $limit['limit'];
				}
			} else if (is_numeric($limit)) {
				$parameters['limit'] = $limit;
			}
		}
		
		if ($filter_deleted_objects){
			$parameters['conditions'].= " AND ( object_id = 0 OR EXISTS ( SELECT id FROM ".TABLE_PREFIX."objects WHERE id = object_id AND archived_on = '0000-00-00 00:00:00' AND trashed_on = '0000-00-00 00:00:00' ))" ;
			if (!empty($contactsTypeId)) {	
				$parameters['conditions'].= " AND ( object_type_id <> $contactsTypeId OR EXISTS ( SELECT object_id FROM  ".TABLE_PREFIX."contacts c WHERE c.object_id = `".TABLE_PREFIX."members`.object_id AND c.disabled = 0 ))" ;
			}
		}
		
		if ($extra_conditions != "") {
			$parameters['conditions'].= " $extra_conditions";
		}
		
		$members = Members::instance()->findAll($parameters);
  		return $members;
  	}
  	
  	
	function getRootMembers() {
		$members = Members::instance()->findAll(array('conditions' => '`parent_member_id`=0 AND `dimension_id` = ' . $this->getId()));
  		return $members;
  	}
  	
  	function isDefault() {
  		return (bool) parent::getIsDefault();
  	}
  	
  	function deniesAllForContact($permission_group_ids){
  		$res = DB::execute("SELECT permission_group_id FROM ".TABLE_PREFIX."contact_dimension_permissions WHERE `dimension_id` = " . $this->getId(). " AND `permission_type` <> ". DB::escape('deny all') ." AND `permission_group_id` in ($permission_group_ids) limit 1");
		return $res->numRows() == 0;
  	}
  	

	function hasAllowAllForContact($permission_group_ids){
		$res = DB::execute("SELECT permission_group_id FROM ".TABLE_PREFIX."contact_dimension_permissions WHERE `dimension_id` = " . $this->getId(). " AND `permission_type` = ". DB::escape('allow all') ." AND `permission_group_id` in ($permission_group_ids) limit 1");
		return $res->numRows() > 0;
  	}
  	
  	
	function hasCheckForContact($permission_group_ids){
		$res = DB::execute("SELECT permission_group_id FROM ".TABLE_PREFIX."contact_dimension_permissions WHERE `dimension_id` = " . $this->getId(). " AND `permission_type` = ". DB::escape('check') ." AND `permission_group_id` in ($permission_group_ids) limit 1");
		return $res->numRows() > 0;
  	}
  	
	function getPermissionGroupsAllowAll($permission_group_ids = null){
		return $this->getPermissionGroupsByPermissionType('allow all', $permission_group_ids);
  	}
	
  	function getPermissionGroupsCheck($permission_group_ids = null){
		return $this->getPermissionGroupsByPermissionType('check', $permission_group_ids);
  	}
  	
	function getPermissionGroupsDenyAll($permission_group_ids = null){
		return $this->getPermissionGroupsByPermissionType('deny all', $permission_group_ids);
  	}
  	
	function getPermissionGroupsByPermissionType($permission_type, $permission_group_ids = null){
		if (!is_null($permission_group_ids) && is_array($permission_group_ids)) {
			$permission_group_ids = implode(",", $permission_group_ids);
		}
		$permission_group_ids_cond = "";
		if (!is_null($permission_group_ids)) {
			$permission_group_ids_cond = " AND `permission_group_id` in ($permission_group_ids)";
		}
		$rows = DB::executeAll("SELECT permission_group_id FROM ".TABLE_PREFIX."contact_dimension_permissions WHERE `dimension_id` = " . $this->getId(). " AND `permission_type` = ". DB::escape($permission_type) . $permission_group_ids_cond);
		$res = array();
		if ($rows && is_array($rows)) {
			foreach ($rows as $row) $res[] = $row['permission_group_id'];
		}
		return $res;
  	}
  	
  	
  	function setContactDimensionPermission($permission_group_id, $value) {
  		if (!in_array($value, array('allow all','deny all','check'))) return;
  		
  		$dim_permission = ContactDimensionPermissions::instance()->findById(array('dimension_id' => $this->getId(), 'permission_group_id' => $permission_group_id));
  		if (!$dim_permission instanceof ContactDimensionPermission) {
  			$dim_permission = new ContactDimensionPermission();
  			$dim_permission->setPermissionGroupId($permission_group_id);
  			$dim_permission->setContactDimensionId($this->getId());
  		}
  		$dim_permission->setPermissionType($value);
  		$dim_permission->save();
  	}
  	

  	function getObjectTypeContent($object_type_id){
  		return DimensionObjectTypeContents::instance()->findAll(array('conditions' => array("`dimension_id` = ? AND `content_object_type_id` = ?", $this->getId(), $object_type_id)));
  	}
  	
  	
	function getAllowedObjectTypeContents(){
		return DimensionObjectTypeContents::instance()->findAll(array(
		'conditions' => array("`dimension_id` = ?
			AND (`content_object_type_id` IN (SELECT `id` FROM ".ObjectTypes::instance()->getTableName(true)." WHERE `type` = 'located' AND `name` <> 'template')
			OR ( 
				`content_object_type_id` NOT IN (SELECT `object_type_id` FROM ".TabPanels::instance()->getTableName(true)." WHERE `enabled` = 0) 
	  			AND `content_object_type_id` IN (
	  				SELECT `id` FROM ".ObjectTypes::instance()->getTableName(true)." WHERE `type` = 'content_object' AND `name` <> 'file revision' AND `name` <> 'template' AND name <> 'template_task' AND name <> 'template_milestone' 
	  					AND IF(plugin_id is NULL OR plugin_id = 0, TRUE, plugin_id IN (SELECT id FROM ".TABLE_PREFIX."plugins WHERE is_activated > 0 AND is_installed > 0))
	  			)
  			))", $this->getId()), 
  		'distinct' => true));
  	}
  	
  	
  	//returns the ids of the dimensions from which $this is property
  	function getAssociatedDimensions(){
  		return DimensionMemberAssociations::getAssociatedDimensions($this->getId());
  	}
  	
  	
  	function canContainObjects(){
  		$res = DB::execute("SELECT is_required FROM ".TABLE_PREFIX."dimension_object_type_contents WHERE `dimension_id` = ".$this->getId()." limit 1");
		return $res->numRows() > 0;
  	}
  	
  	
	function canContainObject($object_type_id){
		$res = DB::execute("SELECT is_required FROM ".TABLE_PREFIX."dimension_object_type_contents WHERE `dimension_id` = ".$this->getId()." AND `content_object_type_id` = '$object_type_id' limit 1");
		return $res->numRows() > 0;
	}
	
	
	function isRequired($object_type_id){
		$res = DB::execute("SELECT is_required FROM ".TABLE_PREFIX."dimension_object_type_contents WHERE `dimension_id` = ".$this->getId()." AND `content_object_type_id` = '$object_type_id' AND `is_required` = 1 limit 1");
		return $res->numRows() > 0;
	}
	
	function getRequiredObjectTypes() {
		$types = array();
		$res = DB::execute("SELECT content_object_type_id FROM ".TABLE_PREFIX."dimension_object_type_contents WHERE `dimension_id` = ".$this->getId()." AND `is_required` = 1");
		$rows = $res->fetchAll();
		if (is_array($rows) && count($rows) > 0) {
			foreach ($rows as $row) {
				$types[] = $row['content_object_type_id'];
			}
		}
		return $types;
	}
	
	/**
	 * @deprecated Use Dimension::getOptionValue($name) instead.
	 * @param bool True to get JSON decoded. false to get plain text
	 */
	function getOptions($decoded = false ) {
		$js = $this->getColumnValue("options") ;
		if ( $decoded ) { 
			return json_decode ( $js );
		}else{
			return $js ;
		}
	}
	
	function canHaveHierarchies() {
		$sql =  "SELECT 1
					FROM `".TABLE_PREFIX."dimension_object_type_hierarchies`
					WHERE dimension_id = ".$this->getId();	
					
		$result = DB::executeOne($sql);
		
		if($result){
			return true;
		}
		return false;
	}
	
	function useLangs() {
		return intval($this->getOptionValue('useLangs'));
	}
	
	/**
	 * @see BaseDimension::getName()
	 */
	function getName() {
		
		$custom_name = $this->getOptionValue('custom_dimension_name');
		if ($custom_name && trim($custom_name) != "") {
			
			$name = $custom_name;
			
		} else {
			if ($this->useLangs()) {
				$name = lang($this->getCode());
			} else {
				$name = parent::getName();
			}
			Hook::fire("edit_dimension_name", array('dimension' => $this), $name);
		}
		
		return $name;
	}
	
	
	function getOptionValue($name) {
		
		if (!isset($this->options_cache[$name])) {
			$value = DimensionOptions::getOptionValue($this->getId(), $name);
			$this->options_cache[$name] = $value;
		}
		return $this->options_cache[$name];
	}
	
	function setOptionValue($name, $value) {
		DimensionOptions::setOptionValue($this->getId(), $name, $value);
		$this->options_cache[$name] = $value;
	}

}
