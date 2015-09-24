<?php

class Workspaces extends BaseWorkspaces {

	private static $workspaces_by_id;
	
	function __construct() {
		parent::__construct();
		$this->object_type_name = 'workspace';
	}
	

    function getPublicColumns() {
        $cols = array(
          /*  array(
            	'col' => 'description', 
            	'type' => DATA_TYPE_STRING, 
            	'large' => true ),*/
            array(
            	'col' => 'show_description_in_overview',
            	'type' => DATA_TYPE_BOOLEAN, 
            )
        );
       
        foreach ($cols as &$col) {
            $col['col_lang'] = lang("field ". self::instance()->object_type_name ." ". $col['col']);
        }
        return $cols;
    }
    
    
    static function getWorkspaceById($id) {
    	$ws = array_var(self::$workspaces_by_id, $id);
    	if (!$ws instanceof Workspace) {
    		$ws = Workspaces::findById($id);
    		if ($ws instanceof Workspace) self::$workspaces_by_id[$id] = $ws;
    	}
    	return $ws;
    }
    
    static function getWorkspaces($limit = 10) {
    	
    	$ws_dim = Dimensions::findByCode('workspaces');
    	$ws_object_type = self::instance()->getObjectTypeId();
    	 
    	$sql = "dimension_id = " . $ws_dim->getId() . " AND object_type_id = $ws_object_type";
    	 
    	$allowed_members = array();
    	$add_ctx_members = true;
    	$context = active_context();
    	foreach ($context as $selection) {
    		if ($selection instanceof Dimension && $selection->getCode() == 'workspaces') {
    			$add_ctx_members = false;
    		} else if ($selection instanceof Member && $selection->getObjectTypeId() == $ws_object_type) {
    			$allowed_members[] = $selection->getId();
    		}
    	}
    	 
    	if ($add_ctx_members && count($allowed_members) > 0) {
    		$sql .= " AND parent_member_id IN (". implode(",", $allowed_members) .")";
    	} else {
    		$sql .= " AND parent_member_id = 0";
    	}
    	 
    	$members = Members::findAll(array('conditions' => $sql, 'order' => 'name'));
    	$res = array();
    	foreach ($members as $mem) {
    		// FIXME: check permissions
    	}
    	return $members;
    }
    
    
    private static $color_cache = null;
    
    static function getWorkspaceColor($ws_id) {
    	
    	if (self::$color_cache == null) {
    		$res = DB::execute("SELECT object_id, color FROM ".TABLE_PREFIX. "workspaces");
    		$rows = $res->fetchAll();
    		if (is_array($rows)) {
    			self::$color_cache = array();
    			foreach ($rows as $row) self::$color_cache[$row['object_id']] = $row['color'];
    		}
    	}
    	
    	return array_var(self::$color_cache, $ws_id);
    }

} 