<?php
/**
 * 	
 * Enter description here ...
 * @author Pepe
 *
 */
abstract class ContentDataObjects extends DataManager {
	
	protected $object_type_name = null;
	
	private $object_type_id = null;
	
	private $foundRows = null ;
	
	function getFoundRows() {
		return $this->foundRows ;
	}
	
	function getObjectTypeId() {
		if (!$this instanceof ContentDataObjects || is_null($this->object_type_name)) {
			return null;
		}		
		if (is_null($this->object_type_id)) {
			$ot = ObjectTypes::findByName($this->object_type_name);
			if ($ot instanceof ObjectType) {
				$this->object_type_id = $ot->getId();
			}
		}
		
		return $this->object_type_id;
	}
	
	
	/**
	 * Returns the fields visible to the user, e.g. for add/edit forms
	 * Must be overriden by the specific object classes
	 */
	function getPublicColumns() {
		$public_columns = array();
		Hook::fire('modify_content_object_public_columns', $this, $public_columns);
		return $public_columns;
	}
	
	
	/**
	 * Returns the numeric fields that store a time value
	 * Must be overriden by the specific object classes
	 */
	function getTimeColumns() {
		return array();
	}

	/**
	 * Return system columns
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getSystemColumns() {
		$system_columns = parent::getSystemColumns();
		Hook::fire('additional_system_columns', $this, $system_columns);
		
		return $system_columns;
	}
	
	/**
	 * Return system columns
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getExternalColumns() {
		$external_columns = parent::getExternalColumns();
		Hook::fire('additional_external_columns', $this, $external_columns);
		
		return $external_columns;
	}
	
	/**
	 * Return column type
	 *
	 * @access public
	 * @param string $column_name
	 * @return string
	 */
	function getCOColumnType($column_name, $columns) {
		if(isset($columns[$column_name])) {
			return $columns[$column_name];
		} else {
			return Objects::instance()->getColumnType($column_name);
		}
	}
	
	/**
	 * Retuns a new instance of a concrete object managed by this class
	 * This method is required to be overriden by classes that manage 'dimension_objects'
	 */
	function newDimensionObject() {
		$ot = ObjectTypes::findById($this->getObjectTypeId());
		eval('$manager = '.$ot->getHandlerClass().'::instance();');
		eval('$object = new '.$manager->getItemClass().'();');
		if (isset($object) && $object) {
			$object->setObjectTypeId($this->getObjectTypeId());
		}
		return $object;
	}
	
	
	function sqlFields($all = true) {
		$common_fields = array() ;
		foreach ( Objects::getColumns() as $col ) {
			$common_fields[] = "o.$col AS `$col`";
		}
		if (!$all) {
			return $common_fields ;
		}else{
			$extra_fields = array();
			$columns = $this->getColumns();
			foreach ( $columns as $col ) {
				if ($col != "id") 
					$extra_fields[] = "m.$col AS `$col`";
			}
			$columns = null;
			$fields = array_merge($common_fields,$extra_fields);
			return $fields ;
		}
		
	}
	
	/**
	 * 
	 * FAST PAGINATE ! DONT DELETE.. CAN BE USED IN THE FUTURE
	 * @author Pepe
	 */
	/*
	function paginate($arguments = null, $items_per_page = 10, $current_page = 1, $count = null) {
	
		
		if (isset ( $this ) && instance_of ( $this, 'ContentDataObjects' )) {
			if (! is_array ( $arguments ))	$arguments = array ();
			$conditions = $this->prepareConditions( array_var ( $arguments, 'conditions' ) );
			$object_table = $this->getTableName();
			
			if (defined ( 'INFINITE_PAGING' ) && INFINITE_PAGING)
				$count = 10000000;
			$fields = $this->sqlFields() ;
			
			$sql = "SELECT SQL_CALC_FOUND_ROWS 
						".implode(", ",$fields)."
					FROM 
						".TABLE_PREFIX."objects o INNER JOIN $object_table  m ON o.id  = m.object_id						
					WHERE 
						$conditions
					LIMIT ".($current_page - 1) * $items_per_page . ", $items_per_page
			";
						
			
			$res = DB::execute($sql);
			$items = $res->fetchAll();			
			$className = $this->getItemClass();
			$messages = array() ;
			foreach ($items as $item) {
				$object = new $className;
				$object->loadFromRow($item);
				$objects[] = $object ;
			}
			
			$num_rows = DB::executeOne("SELECT FOUND_ROWS() AS total")  ;
			$total = $num_rows["total"];
			$pagination = new DataPagination ( $total , $items_per_page, $current_page );
			return array ($objects, $pagination );
		} else {
			return ContentDataObjects::instance()->paginate ( $arguments, $items_per_page, $current_page );
		} // if
    } // paginate

    */
    
	/**
    * Do a SELECT query over database with specified arguments
    *
    * @access public
    * @param array $arguments Array of query arguments. Fields:
    * 
    *  - one - select first row
    *  - conditions - additional conditions
    *  - order - order by string
    *  - offset - limit offset, valid only if limit is present
    *  - limit
    * 
    * @return array
    * @throws DBQueryError
    */
    function find($arguments = null) {
      
      if (isset($arguments['conditions'])) {
      	$conditions = $arguments['conditions'];
      } else if (isset($arguments['condition'])) {
      	$conditions = $arguments['condition'];
      } else {
      	$conditions = '';
      }
    	
      // Collect attributes...
      $one        = (boolean) array_var($arguments, 'one', false);
      $id         = (boolean) array_var($arguments, 'id', false);
      $distinct   = (boolean) array_var($arguments, 'distinct', false);
      $conditions = $this->prepareConditions( $conditions );
      $order_by   = array_var($arguments, 'order', '');
      $offset     = (integer) array_var($arguments, 'offset', 0);
      $limit      = (integer) array_var($arguments, 'limit', 0);
      $join		  = array_var($arguments, 'join');
      
	  // limit = 1 when findOne is invoked
      if ($one) {
      	$limit = 1;
      }
      
      
      $table_prefix = defined('FORCED_TABLE_PREFIX') && FORCED_TABLE_PREFIX ? FORCED_TABLE_PREFIX : TABLE_PREFIX;

      // Prepare query parts
      //$where_string = trim($conditions) == '' ? '' : "WHERE " . preg_replace("/\s+in\s*\(\s*\)/i", " = -1", $conditions);
      $where_string = trim($conditions) == '' ? '' : "WHERE " . trim($conditions);
      $order_by_string = trim($order_by) == '' ? '' : "ORDER BY $order_by";
      $limit_string = $limit > 0 ? "LIMIT $offset, $limit" : '';
      $distinct = $distinct ? "DISTINCT " : "";
      $join_string = "";
      if (is_array($join) && array_var($join, 'table') && array_var($join, 'jt_field') && (array_var($join, 'e_field') || array_var($join, 'j_sub_q'))) {
      	if (array_var($join, 'e_field')) {
      		$join_cond = "e." . array_var($join, 'e_field');
      	} else {
      		$join_cond = "(" . array_var($join, 'j_sub_q') . ")";
      	}
      	if ( isset($join['join_type']) && in_array(strtoupper(trim($join['join_type'])) , array("INNER", "LEFT")) ){
      		$join_type = trim(strtoupper($join['join_type']));
      	}else{
      		$join_type = "INNER";
      	}
      	$join_string = "$join_type JOIN " . array_var($join, 'table') . " jt ON jt." . array_var($join, 'jt_field') . " = " . $join_cond;
      }
      
      // Prepare SQL
      $sql = "
      	SELECT $distinct" . ($id ? '`id`' : 'e.*, o.* ') . " 
      	FROM " . $this->getTableName(true) . " e
      	INNER JOIN ".$table_prefix."objects o ON o.id = e.object_id 
        $join_string $where_string $order_by_string $limit_string";

      // Run!
      $rows = DB::executeAll($sql);

      // Empty?
      if(!is_array($rows) || (count($rows) < 1)) return null;
      
      // return only ids?
      if ($id) {
      	$ids = array();
      	foreach ($rows as $row) {
      		$ids[] = $row['id'];
      	}
      	return $ids;
      }
      
      // If we have one load it, else loop and load many
      if($one) {
        return $this->loadFromRow($rows[0]);
      } else {
        $objects = array();
        foreach($rows as $row) {
          $object = $this->loadFromRow($row);
          if(instance_of($object, $this->getItemClass())) $objects[] = $object;
        } // foreach
        return count($objects) ? $objects : null;
      } // if
    } // find

    
    
    
    
		
	/**
    * Return number of rows in this table
    *
    * @access public
    * @param string $conditions Query conditions
    * @return integer
    */
    function count($conditions = null) {
      // Don't do COUNT(*) if we have one PK column
      $escaped_pk = is_array($pk_columns = $this->getPkColumns()) ? '*' : DB::escapeField($pk_columns);
      
      $conditions = $this->prepareConditions($conditions);
      $where_string = trim($conditions) == '' ? '' : "WHERE $conditions";
      $row = DB::executeOne("
      	SELECT COUNT($escaped_pk) AS 'row_count' 
      	FROM " . $this->getTableName(true) . " e
      	INNER JOIN ".TABLE_PREFIX."objects o ON o.id = e.object_id 
        $where_string ");
      return (integer) array_var($row, 'row_count', 0);
    } // count

    

	


	
	
	/**
    * Load row from database based on ID
    *
    * @access public
    * @param mixed $id
    * @return array
    */
    function loadRow($id) {
        $ocols = Objects::getColumns();
        $tcols = $this->getColumns();
    	$columns = array_map('db_escape_field', array_merge ($ocols, $tcols ) );
    	$table_prefix = defined('FORCED_TABLE_PREFIX') && FORCED_TABLE_PREFIX ? FORCED_TABLE_PREFIX : TABLE_PREFIX;
    	
      	$sql = sprintf("
      		SELECT %s 
      		FROM %s	INNER JOIN `".$table_prefix."objects` o
      		ON o.id = %s.object_id 
      		WHERE %s", 
      	
        	implode(', ', array_merge( $columns )), 
        	$this->getTableName(true), 
        	$this->getTableName(true),         	
        	$this->getConditionsById($id)
		); // sprintf
		
		$ocols = null;
		$tcols = null;
		$columns = null;
		
		return DB::executeOne($sql);
    } 
	
	
	private function check_include_trashed(& $arguments = null) {
		if (!array_var($arguments, 'include_trashed', false)) {
			$columns = $this->getColumns();
			if (array_search("trashed_on", $columns) != false) {
				$conditions = array_var($arguments, 'conditions', '');
				if (is_array($conditions)) {
					$conditions[0] = "`trashed_on` = " . DB::escape(EMPTY_DATETIME). " AND (".$conditions[0].")";
				} else if ($conditions != '') {
					$conditions = "`trashed_on` = " . DB::escape(EMPTY_DATETIME). " AND ($conditions)";
				} else {
					$conditions = "`trashed_on` = " . DB::escape(EMPTY_DATETIME);
				}
				$arguments['conditions'] = $conditions;
			}
			$columns = null;
		}
	}


	function findById($id, $force_reload = false) {
		$co = parent::findById($id, $force_reload);
		if (!is_null($co)) {
			$co->setObject(Objects::findById($id, $force_reload));
		}
		return $co;
	}
	
	/**
	 * Permormance FIX: getContentObjects replacement
	 * @param array $args 
	 *		order = null  -  may be performance killer depending on the order criteria  
	 * 		order_dir = null 
	 * 		extra_conditions = null : extra sql 'inyection' - may be performance killer depending on the injected query  
	 * 		join_params = null : extra join table
	 * 		trashed = false 
	 *	 	archived = false
	 * 		start = 0 
	 * 		limit = null	
	 * 		ignore_context
	 *		include_deleted 
	 *		count_results : if true calc found rows else show 'many'	 
	 *      extra_member_ids : Search also objects in this slist of members 
	 *      member_ids : force to search objects in this list of members (strinct)
	 *  	 
	 */
	public function listing($args = array()) {

	    //Initialization of variables
	    $result = new stdClass;
		$result->objects = array();
		$result->total = array();
		$type_id = self::getObjectTypeId();
		$members = array ();
		$SQL_BASE_JOIN = '';
		$SQL_SEARCHABLE_OBJ_JOIN = '';
		$SQL_EXTRA_JOINS = '';
		$SQL_TYPE_CONDITION = 'true';
		$SQL_FOUND_ROWS = '';
        $SQL_BEFORE_COLUMNS = '';
        $SQL_GROUP_BY = '';

        if (isset($args['sql_before_columns'])) {
            $SQL_BEFORE_COLUMNS = $args['sql_before_columns'];
        }

		if (isset($args['count_results'])) {
			$count_results = $args['count_results'];
		} else {
			$count_results = defined('INFINITE_PAGING') ? !INFINITE_PAGING : false;
		}
		
		//get only the number of results without limit not data
		if (isset($args['only_count_results'])){
			$only_count_results = $args['only_count_results'];
		}else{
			$only_count_results = false;
		}
		
		
		$return_raw_data = array_var($args,'raw_data');
		$start = array_var($args,'start');
		$limit = array_var($args,'limit');
		$member_ids = array_var($args, 'member_ids');
		$extra_member_ids =  array_var($args,'extra_member_ids');
		$ignore_context = array_var($args,'ignore_context');
		$include_deleted = (bool) array_var($args,'include_deleted');
		$join_with_searchable_objects = array_var($args, 'join_with_searchable_objects');
		$select_columns = array_var($args, 'select_columns');
		$fire_additional_data_hook = array_var($args, 'fire_additional_data_hook', true);
		
		//text filter param
		$text_filter = DB::cleanStringToFullSearch(array_var($_GET, 'text_filter'));
		
		$controller = array_var($_GET, 'c');
		$text_filter_extra_conditions = ''; 
		
		if (trim($text_filter) != '') {
		    
		    $join_with_searchable_objects = true;
		    
		    $text_filter = str_replace("'", "\'", trim($text_filter));
		    
		    //if text_filter starts or ends with special characters, remove it for do the query.
		    $text_filter = str_replace( array( '-', '+', '~' , '(', ')','<','>','*','"' ), ' ', $text_filter);
		    
		    if(str_word_count($text_filter, 0) > 1){
		        $text_filter_extra_conditions .= "
								AND MATCH (so.content) AGAINST ('\"$text_filter\"' IN BOOLEAN MODE)
						    ";
		    }else{
		        $text_filter_extra_conditions  .= "
								AND MATCH (so.content) AGAINST ('$text_filter*' IN BOOLEAN MODE)
						    ";
		    }
		}
		
		if (empty($select_columns)) {
		    if ($join_with_searchable_objects) {
		        $select_columns = array('DISTINCT(o.id),o.*,e.*');
		    } else {
		        $select_columns = array('*');
		    }
		}else{
		    if ($join_with_searchable_objects) {
		        $select_columns = array('DISTINCT(o.id),o.*,e.*');		        		        
		    }
		}
		
		
		$only_return_query_string = array_var($args, 'only_return_query_string');
		
		//template objects
		$template_objects = (bool) array_var($args,'template_objects',false);
				
		$handler_class = "Objects";
	
		if ($type_id){
			// If isset type, is a concrete instance linsting. Otherwise is a generic listing of objects
			$type = ObjectTypes::findById($type_id); /* @var $object_type ObjectType */
			$handler_class = $type->getHandlerClass();
			$table_name = self::getTableName();
			
			if (!isset($args['join_ts_with_task'])) $args['join_ts_with_task'] = false;
			$join_ts_with_task = $args['join_ts_with_task'];
			
	    	// Extra Join statements
	    	if ($this instanceof ContentDataObjects && $this->object_type_name == 'timeslot' && $join_ts_with_task) {
	    		// if object is a timeslot and is related to a content object => check for members of the related content object.
	    		$SQL_BASE_JOIN = " INNER JOIN  $table_name e ON IF(e.rel_object_id > 0, e.rel_object_id, e.object_id) = o.id ";
	    		$SQL_TYPE_CONDITION = "o.object_type_id = IF(e.rel_object_id > 0, (SELECT z.object_type_id FROM ".TABLE_PREFIX."objects z WHERE z.id = e.rel_object_id), $type_id)";
	    	} else {
	    		$SQL_BASE_JOIN = " INNER JOIN  $table_name e ON e.object_id = o.id ";
	    		$SQL_TYPE_CONDITION = "o.object_type_id = $type_id";
	    	}
			$SQL_EXTRA_JOINS = self::prepareJoinConditions(array_var($args,'join_params'));
			
		}
		
		if ($join_with_searchable_objects) {
			$SQL_SEARCHABLE_OBJ_JOIN = " INNER JOIN ".TABLE_PREFIX."searchable_objects so ON so.rel_object_id=o.id ";
		}
		
		if (!$ignore_context && !$member_ids) {
			$members = active_context_members(false); // Context Members Ids
		} elseif ( count($member_ids) ) {
			$members = $member_ids;
		}
		
		if  (is_array($extra_member_ids)) {
			if (isset($members)) {
				$members = array_merge($members, $extra_member_ids);
			} else {
				$members = $extra_member_ids;
			}
		}
		
		// Specific order statements for dimension orders
		if (is_numeric(array_var($args,'dim_order')) && array_var($args,'dim_order') > 0) {
			
			$SQL_BASE_JOIN .= "
					LEFT JOIN ".TABLE_PREFIX."object_members obj_mems ON obj_mems.object_id=o.id AND obj_mems.is_optimization=0
			";
			$SQL_BASE_JOIN .= "
					LEFT JOIN ".TABLE_PREFIX."members memb ON obj_mems.member_id=memb.id AND memb.dimension_id=".array_var($args,'dim_order')."
			";
			
			$select_columns = array("DISTINCT o.*", "e.*, GROUP_CONCAT(memb.name) as memb_name");
			
			$args['order'] = 'memb_name';
			$args['group_by'] = "o.id";
			
		} else if (is_numeric(array_var($args,'cp_order')) && array_var($args,'cp_order') > 0) {
			// Specific order statements for custom property orders
			
			$SQL_BASE_JOIN .= " LEFT JOIN ".TABLE_PREFIX."custom_property_values cpropval ON cpropval.object_id=o.id 
					AND (cpropval.custom_property_id=".array_var($args,'cp_order')." OR cpropval.custom_property_id IS NULL) ";
			
			$cp = CustomProperties::findById(array_var($args,'cp_order'));
			if ($cp instanceof CustomProperty && ($cp->getType() == 'contact' || $cp->getType() == 'user')) {
				
				$SQL_BASE_JOIN .= " LEFT JOIN ".TABLE_PREFIX."objects cpobj ON cpobj.id=cpropval.value";
				
				$select_columns = array("DISTINCT o.*", "e.*, GROUP_CONCAT(cpobj.name) as cpobj_name");
				$args['order'] = 'cpobj_name';
				$args['group_by'] = "o.id";
				
			} else {
				
			    if ($cp instanceof CustomProperty && $cp->getType() == 'numeric'){			        
			        $cp_concat_string = "CONVERT(cpropval.value,SIGNED INTEGER)";
			    }else{
			        $cp_concat_string = "cpropval.value";
			    }
			    $select_columns = array("DISTINCT o.*", "e.*, $cp_concat_string as cpropval_value");
				$args['order'] = 'cpropval_value';
				$args['group_by'] = "o.id";
			}
		}
		// Order statement
    	$SQL_ORDER = self::prepareOrderConditions(array_var($args,'order'), array_var($args,'order_dir'));
		
		// Prepare Limit SQL 
		if (is_numeric(array_var($args,'limit')) && array_var($args,'limit')>0){
			$SQL_LIMIT = "LIMIT ".array_var($args,'start',0)." , ".array_var($args,'limit');
		}else{
			$SQL_LIMIT = '' ;
		}
		
		$SQL_CONTEXT_CONDITION = " true ";
		//show only objects that are on this members by classification not by hierarchy
		$show_only_member_objects = array_var($args,'show_only_member_objects',false);
		$exclusive_in_member = '';
		if($show_only_member_objects){
			$exclusive_in_member = " AND om.`is_optimization` = 0";
		}
		if (!empty($members) && count($members)) {
			
			$SQL_BASE_JOIN .= " LEFT JOIN ".TABLE_PREFIX."object_members om ON om.object_id=o.id ";
			
			$SQL_CONTEXT_CONDITION = "om.member_id IN (" . implode ( ',', $members ) . ") $exclusive_in_member";
			
			//Fixing: Undefined index
			if (!isset($args['group_by'])) {
				$args['group_by'] = "";
			}
		    if (trim($args['group_by']) != "") {
		        $args['group_by'] .= ", ";
		    }
		    
		    $args['group_by'] .= "om.object_id HAVING COUNT(DISTINCT(om.member_id)) = ".count($members);

		}else{
			//show only objects that are on root
			if($show_only_member_objects){				
				if (is_array(active_context())) {
					$active_dims_ids = array();
					foreach (active_context() as $ctx) {
						if($ctx instanceof Dimension ) {							
							$active_dims_ids[] = $ctx->getId();
						}
					}
					if(count($active_dims_ids) > 0){
						$SQL_CONTEXT_CONDITION = "(NOT EXISTS (SELECT om.object_id
							FROM  ".TABLE_PREFIX."object_members om
							INNER JOIN  ".TABLE_PREFIX."members mem ON mem.id = om.member_id AND mem.dimension_id IN (" . implode(",", $active_dims_ids) . ")
							WHERE	o.id = om.object_id
							))";
					}
				}				
			}
		}


		// Prepare Group By SQL $group_by = array_var($args,'group_by');
		if (array_var($args,'group_by')){
			$SQL_GROUP_BY = "GROUP BY ".array_var($args,'group_by');
		}else{
			$SQL_GROUP_BY = '' ;
		}
		
		// Trash && Archived CONDITIONS
    	$trashed_archived_conditions = self::prepareTrashandArchivedConditions(array_var($args,'trashed'), array_var($args,'archived'));
    	$SQL_TRASHED_CONDITION = ($include_deleted) ? ' TRUE '  : $trashed_archived_conditions[0];
    	$SQL_ARCHIVED_CONDITION =($include_deleted) ? ' AND TRUE ' :  $trashed_archived_conditions[1];
    	
		// Extra CONDITIONS
		if (array_var($args,'extra_conditions')) {
			$SQL_EXTRA_CONDITIONS = array_var($args,'extra_conditions') ;	
		}else{
			$SQL_EXTRA_CONDITIONS = '';
		}
		
		$SQL_EXTRA_CONDITIONS .= $text_filter_extra_conditions;
		
		$SQL_COLUMNS = implode(',', $select_columns);

		//column to check permissions
		if (isset($args['check_permissions_col'])){
			$check_permissions_col = $args['check_permissions_col'];
		}else{
			$check_permissions_col = "o.id";
		}
		
		if (logged_user() instanceof Contact) {
			$uid = logged_user()->getId();
			// Build Main SQL
			$logged_user_pgs = implode(',', logged_user()->getPermissionGroupIds());
			
			$permissions_condition = " true ";
			if (!logged_user()->isAdministrator() || $this instanceof MailContents) {
				if ($this instanceof MailContents) {
					$permissions_condition = "(
						$check_permissions_col IN (
							SELECT sh.object_id FROM ".TABLE_PREFIX."sharing_table sh
							WHERE ".$check_permissions_col." = sh.object_id
								AND sh.group_id  IN ($logged_user_pgs)
							)
						OR
						e.account_id IN (
							SELECT macc.account_id FROM ".TABLE_PREFIX."mail_account_contacts macc
							WHERE macc.contact_id=$uid
						)
					) ";
				} else {
					$permissions_condition = $check_permissions_col." IN (
						SELECT sh.object_id FROM ".TABLE_PREFIX."sharing_table sh
						WHERE ".$check_permissions_col." = sh.object_id
						AND sh.group_id  IN ($logged_user_pgs)
					) ";
				}
			}
			
			
			/*
			 * Check that the objects to list does not belong only to a non-manageable dimension that defines permissions
			 * Object can be shown if:
			 * 		1 - It belongs to at least a member in a dimension that defines permissions and is manageable
			 * 		2 - Or it belongs to at least a member in a dimension that does not defines permissions
			 * 		3 - Or user has permissions to read objects without classification 
			 */
		  if (!$type instanceof ObjectType || !$type->getName()=='mail') {
			$without_perm_dim_ids = Dimensions::findAll(array('id' => true, 'conditions' => "defines_permissions=0"));
			$no_perm_dims_cond = "";
			
			if (count($without_perm_dim_ids) > 0) {
				$no_perm_dims_cond = " OR EXISTS (
					select * from ".TABLE_PREFIX."object_members omems
					  inner join ".TABLE_PREFIX."members mems on mems.id = omems.member_id
					  WHERE omems.object_id=o.id AND mems.dimension_id IN (".implode(',', $without_perm_dim_ids).")
				)";
			}
			
			$permissions_condition .= " AND IF (o.object_type_id=".MailContents::instance()->getObjectTypeId().", true, (
				EXISTS (
					SELECT cmp.permission_group_id FROM ".TABLE_PREFIX."contact_member_permissions cmp 
					WHERE cmp.member_id=0 AND cmp.permission_group_id=".logged_user()->getPermissionGroupId()." AND cmp.object_type_id = o.object_type_id
				)
				OR
				EXISTS (
					select * from ".TABLE_PREFIX."object_members omems
						inner join ".TABLE_PREFIX."members mems on mems.id = omems.member_id
						inner join ".TABLE_PREFIX."dimensions dims on dims.id = mems.dimension_id
					WHERE omems.object_id=o.id and dims.defines_permissions=1 and dims.is_manageable=1
				) $no_perm_dims_cond
			))";
		  }
			/********************************************************************************************************/
			
			if (!$this instanceof MailContents && logged_user()->isAdministrator() || 
					($this instanceof Contacts && $this->object_type_name == 'contact' && can_manage_contacts(logged_user()))) {
				$permissions_condition = "true";
			}
			/*
			if ($this instanceof ProjectFiles && !logged_user()->isAdministrator() && Plugins::instance()->isActivePlugin('mail')) {
				$permissions_condition .= ($permissions_condition=="" ? "" : " AND ") . "IF(e.mail_id > 0,
					  o.id IN (
										SELECT sh.object_id FROM ".TABLE_PREFIX."sharing_table sh
										WHERE o.id = sh.object_id
										AND sh.group_id  IN ($logged_user_pgs)
					  ),
					  true
					)";
			}*/
			
			if($template_objects){
				$permissions_condition = "true";
				$SQL_BASE_JOIN .= " INNER JOIN  ".TABLE_PREFIX."template_tasks temob ON temob.object_id = o.id ";
			}
			$sql = "
				SELECT $SQL_FOUND_ROWS $SQL_BEFORE_COLUMNS $SQL_COLUMNS FROM ".TABLE_PREFIX."objects o
				$SQL_BASE_JOIN
				$SQL_SEARCHABLE_OBJ_JOIN
				$SQL_EXTRA_JOINS
				WHERE
					$permissions_condition
					AND	$SQL_CONTEXT_CONDITION
					AND $SQL_TYPE_CONDITION
					AND $SQL_TRASHED_CONDITION $SQL_ARCHIVED_CONDITION $SQL_EXTRA_CONDITIONS
				$SQL_GROUP_BY
				$SQL_ORDER
				$SQL_LIMIT";
			
			if (isset($args['query_wraper_start'])){
				$query_wraper_start = $args['query_wraper_start'];
				$query_wraper_end = $args['query_wraper_end'];
				$sql = $query_wraper_start.$sql.$query_wraper_end;
			}
			
			$sql_total = "
				SELECT count(DISTINCT(o.id)) as total FROM ".TABLE_PREFIX."objects o
				$SQL_BASE_JOIN
				$SQL_SEARCHABLE_OBJ_JOIN
				$SQL_EXTRA_JOINS
				WHERE
					$permissions_condition
					AND	$SQL_CONTEXT_CONDITION
					AND $SQL_TYPE_CONDITION
					AND $SQL_TRASHED_CONDITION $SQL_ARCHIVED_CONDITION $SQL_EXTRA_CONDITIONS	
					$SQL_GROUP_BY
			";

			if($SQL_GROUP_BY != ''){
                $sql_total = "
                  SELECT count(*) as total FROM (
                  $sql_total
                  ) as temptotal
                ";
            }
			
            //For debugging purposes:
            //Logger::log_r("At ContentDataObject listing() function: listing. SQL is: \n". $sql . "\n");
			
			if ($only_return_query_string) {
				return $sql;
			}

			
		    if(!$only_count_results){
				// Execute query and build the resultset
				
		    	$rows = DB::executeAll($sql);
		    	
		    	if ($return_raw_data) {
		    		$result->objects = $rows;
		    	} else {
		    		if($rows && is_array($rows)) {
		    			foreach ($rows as $row) {
		    				if ($handler_class) {
		    					$phpCode = '$co = '.$handler_class.'::instance()->loadFromRow($row);';
		    					eval($phpCode);
		    				}
		    				if ( $co ) {
		    					$result->objects[] = $co;
		    				}
		    			}
		    		}
		    	}
				if ($count_results) {
					$total = DB::executeOne($sql_total);
					$result->total = $total['total'];	
				}else{
					if  ( count($result->objects) >= $limit ) {
						$result->total = 10000000;
					}else{
						$result->total = $start + count($result->objects);
					}
				}
				
				// additional data over result
				$from_sql = "FROM ".TABLE_PREFIX."objects o";
				$joins_sql = "$SQL_BASE_JOIN $SQL_SEARCHABLE_OBJ_JOIN $SQL_EXTRA_JOINS";
				$all_conditions_sql = "
					$permissions_condition
					AND $SQL_CONTEXT_CONDITION
					AND $SQL_TYPE_CONDITION
					AND $SQL_TRASHED_CONDITION $SQL_ARCHIVED_CONDITION $SQL_EXTRA_CONDITIONS
				";
				$params = array('type_id' => $type_id, 'from_sql' => $from_sql, 'joins_sql' => $joins_sql, 'conditions_sql' => $all_conditions_sql,
						'group_by' => $SQL_GROUP_BY, 'order' => array_var($args,'order'), 'order_dir' => array_var($args,'order_dir'),
						'start' => $start, 'limit' => $limit, 'totalCount' => $result->total, 'member_ids' => $members);
				
				$more_data = null;
				if ($fire_additional_data_hook) {
					Hook::fire('object_listing_additional_data', $params, $more_data);
				}
				if ($more_data) {
					foreach ($more_data as $key => $value) {
						$result->$key = $value;
					}
				}
				// ---------------------------
				
		    }else{
		    	$total = DB::executeOne($sql_total);
		    	$result->total = $total['total'];		    	
		    }
		    
		} else {
			$result->objects = array();
			$result->total = 0;
		}
		
		return $result;
	}
	
	/**
	 * @deprecated by listing(args) 
	 * param unknown_type $context
	 * param unknown_type $object_type
	 * param unknown_type $order
	 * param unknown_type $order_dir
	 * param unknown_type $extra_conditions
	 * param unknown_type $join_params
	 * param unknown_type $trashed
	 * param unknown_type $archived
	 * param unknown_type $start
	 * param unknown_type $limit
	 */
	static function getContentObjects($context, $object_type, $order=null, $order_dir=null, $extra_conditions=null, $join_params=null, $trashed=false, $archived=false, $start = 0 , $limit=null){
		$table_name = $object_type->getTableName();
		$object_type_id = $object_type->getId();
		
		//Join conditions
		$join_conditions = self::prepareJoinConditions($join_params);
		
    	//Trash && Archived conditions
    	$conditions = self::prepareTrashandArchivedConditions($trashed, $archived);
    	$trashed_cond = $conditions[0];
    	$archived_cond = $conditions[1];
    	
    	//Order conditions
    	$order_conditions = self::prepareOrderConditions($order, $order_dir);
    	
    	//Extra conditions
		if (!$extra_conditions) $extra_conditions = "";
		
		//Dimension conditions
    	$member_conditions = self::prepareDimensionConditions($context, $object_type_id);
    	if ($member_conditions == "") $member_conditions = "true";
    	
    	$limit_query = "";
    	if ($limit !== null) {
    		$limit_query = "LIMIT $start , $limit " ;
    	} 
    	
    	$sql_count = "SELECT COUNT( DISTINCT `om`.`object_id` ) AS total FROM `".TABLE_PREFIX."object_members` `om` 
    		INNER JOIN `".TABLE_PREFIX."objects` `o` ON `o`.`id` = `om`.`object_id`
    		INNER JOIN `".TABLE_PREFIX."$table_name` `e` ON `e`.`object_id` = `o`.`id`
    		$join_conditions WHERE $trashed_cond $archived_cond AND ($member_conditions) $extra_conditions";
    	$total = array_var(DB::executeOne($sql_count), "total");	
    		
    	$sql = "SELECT DISTINCT `om`.`object_id` FROM `".TABLE_PREFIX."object_members` `om` 
    		INNER JOIN `".TABLE_PREFIX."objects` `o` ON `o`.`id` = `om`.`object_id`
    		INNER JOIN `".TABLE_PREFIX."$table_name` `e` ON `e`.`object_id` = `o`.`id`
    		$join_conditions WHERE $trashed_cond $archived_cond AND ($member_conditions) $extra_conditions $order_conditions
    		$limit_query
    		";
		
	    $result = DB::execute($sql);
    	$rows = $result->fetchAll();
    	$objects = array();
    	$handler_class = $object_type->getHandlerClass();
    	if (!is_null($rows)) {
    		$ids = array();
	    	foreach ($rows as $row) {
	    		$ids[] = array_var($row, 'object_id');
	    	}
	    	if (count($ids) > 0) {
	    		$join_str = "";
	    		if ($join_params) {
	    			$join_str = ', "join" => array(';
	    			
		    		if (isset($join_params['join_type'])) $join_str .= '"join_type" => "'. $join_params['join_type']  .'",';
		    		if (isset($join_params['table'])) $join_str .= '"table" => "' . $join_params['table'] .'",';
		    		if (isset($join_params['jt_field'])) $join_str .= '"jt_field" => "' . $join_params['jt_field'] .'",';
		    		if (isset($join_params['e_field'])) $join_str .= '"e_field" => "' . $join_params['e_field'] .'",';
		    		if (isset($join_params['j_sub_q'])) $join_str .= '"j_sub_q" => "' . $join_params['j_sub_q'] .'",';
		    		if (str_ends_with($join_str, ",")) $join_str = substr($join_str, 0, strlen($join_str)-1);
		    		$join_str .= ')';
	    		}
	    		$phpCode = '$objects = '.$handler_class.'::findAll(array("conditions" => "`e`.`object_id` IN ('.implode(',', $ids).')", "order" => "'.str_replace("ORDER BY ", "", $order_conditions).'"'.$join_str.'));';
	    		eval($phpCode);
	    	}
    	}
    	

    	$result = new stdClass();
    	$result->objects = $objects ;
    	$result->total = $total ;
    	
    	return $result ;
	}
	
	static function prepareJoinConditions($join_params){
        if (!$join_params) {
    		$join_conditions = "";
        } else {
    		if (isset($join_params['join_type'])){
    			$join_type = strtoupper($join_params['join_type']);
    		}else{
    			$join_type = "INNER";
    		}
	    	
    		if (array_var($join_params, 'e_field')) {
	      		$on_cond = "`e`.`".$join_params['e_field']."` = `jt`.`".$join_params['jt_field']."`";
	      		if (array_var($join_params, 'on_extra')) {
	      			$on_cond = "`e`.`".$join_params['e_field']."` = `jt`.`".$join_params['jt_field']."` ".$join_params['on_extra'];
	      		}
	      	} else if (array_var($join_params, 'j_sub_q')) {
	      		$on_cond = "`jt`.`".$join_params['jt_field']."` = (" . array_var($join_params, 'j_sub_q') . ")";
	      	}
    		$join_conditions = $join_type." JOIN `".$join_params['table']."` `jt` ON " . $on_cond;
    	}
    	return $join_conditions;
    }
    
    
    static function prepareTrashAndArchivedConditions($trashed, $archived){
        $trashed_cond = "`o`.`trashed_by_id` " .($trashed ? ">" : "="). " 0";
    	if ($trashed) {
    		$archived_cond = "";
    	} else {
    		if ($archived == 'all') {
    			$archived_cond = "";
    		} else {
    			if (!$archived || $archived == 'unarchived') {
    				$archived_cond = "AND `o`.`archived_by_id` = 0";
    			} else {
    				$archived_cond = "AND `o`.`archived_by_id` > 0";
    			}
    		}
    	}
    	if ($trashed && Plugins::instance()->isActivePlugin('mail')) {
    		$mail_accounts = MailAccounts::getMailAccountsByUser(logged_user());
    		$mail_account_ids = array();
    		foreach($mail_accounts as $account){
    			$mail_account_ids[] =  $account->getId();
    		}
    		$mcot_id = MailContents::instance()->getObjectTypeId();
    		if(empty($mail_account_ids)){
    			$trashed_cond .= " AND IF(o.object_type_id=$mcot_id, 0, 1)";
    		}else{
    			$trashed_cond .= " AND IF(o.object_type_id=$mcot_id, NOT (SELECT mcx.is_deleted FROM ".TABLE_PREFIX."mail_contents mcx WHERE mcx.object_id=o.id) AND EXISTS (SELECT mct.object_id FROM ".TABLE_PREFIX."mail_contents mct WHERE mct.object_id=o.id AND mct.account_id IN(".implode(',',$mail_account_ids).")), 1)";
    		}
    	}
    	return array($trashed_cond, $archived_cond);
    }
    
    
    static function prepareOrderConditions($order, $order_dir){
    	$order_conditions = "";
    	if (is_null($order_dir)) $order_dir = "DESC";
    	if ($order && $order_dir){
    		if (!is_array($order)){
                $order_conditions = "ORDER BY $order $order_dir";
                $order_conditions .= ", o.name $order_dir";
            } else {
    			$i = 0;
    			foreach($order as $o){
    				switch ($o) {
    					case 'dateDeleted': $o = 'trashed_on'; break;
    					case 'dateUpdated': $o = 'updated_on'; break;
    					case 'dateCreated': $o = 'created_on'; break;
    					case 'dateArchived': $o = 'archived_on'; break;
    					default: break;
    				}
    				if ($i==0)$order_conditions.= "ORDER BY $o $order_dir";
    				else $order_conditions.= ", $o $order_dir";

    				if($i == count($order)){
                        $order_conditions .= ", o.name ".$order_dir;
                    }

                    $i++;
    			}
    		}
    	}else{
            $order_conditions = "ORDER BY o.name ASC";
    	}

        return $order_conditions;
    }
    
    static function prepareDimensionConditions($context, $object_type_id){
  	
    	//get contact's permission groups ids
    	$pg_ids = ContactPermissionGroups::getPermissionGroupIdsByContactCSV(logged_user()->getId(), false);    	

    	$all_dim_in_all_conditions = "";
    	$dm_conditions = "";
    	
    	$context_dimensions = array ();
    	$selection_members = array();// - stores the ids of all members selected in context
    	$selected_dimensions = array();// - stores the ids of all dimensions selected in context
    	$properties = array(); //- stores associations between dimensions
    	$redefined_context = array();// - if there are dimensions that are associated to another dimension in the context, we may need to redefine the context

    	foreach ($context as $selection) {
    		if ($selection instanceof Member){
    			$selection_members[]=$selection;
    		}
    	}
    	
    	$member_count = 0;
    	foreach ($context as $selection) {
    		if ($selection instanceof Member){
    			// condiciones para filtrar por el miembro seleccionado
    			$member_count++;
    			$dimension = $selection->getDimension();
    			$dimension_id = $dimension->getId();
    			$selected_dimensions[] = $dimension;
    			$context_dimensions[$dimension_id]['allowed_members'] = array(); // - stores the ids of the members where we must search for objects
    			
    			
    		   	$context_dimensions[$dimension_id]['allowed_members'][] =  $selection->getId();
    			
    			$children = $selection->getAllChildrenInHierarchy();
    			foreach($children as $child) {
    				$context_dimensions[$dimension_id]['allowed_members'][] = $child->getId();
    			}
    			
    			if ($dimension->canContainObjects()){
    				$allowed_members = $context_dimensions[$dimension_id]['allowed_members'];
    				$dm_conditions .= self::prepareQuery($dm_conditions, $dimension, $allowed_members, $object_type_id, $pg_ids, 'AND', $selection_members);
    				$redefined_context[] = $dimension_id;
    			}
    			else{ 
	    		    //let's check if this dimension is property of another	
	    			$associated_dimensions_ids = $dimension->getAssociatedDimensions();
	    			if (count($associated_dimensions_ids)>0){
	    				foreach ($associated_dimensions_ids as $aid){
	    					$properties[$dimension_id][] = $aid;
	    				}
	    			}
    			}
    		}
    		else{
    			// condiciones para cuando se selecciona "all" en todas las dimensiones visibles
    			$all_members = $selection->getAllMembers();
    			foreach($all_members as $member) {
    				$context_dimensions[$selection->getId()]['allowed_members'][] = $member->getId();
    			}
    			//get all the content object type ids that can hang in the dimension
    			if ($selection->canContainObjects()){
    				if (!isset($context_dimensions[$selection->getId()])) $context_dimensions[$selection->getId()] = array();
	    			$allowed_members = array_var($context_dimensions[$selection->getId()], 'allowed_members', array());
	    			$all_dim_in_all_conditions .= self::prepareQuery($all_dim_in_all_conditions, $selection, $allowed_members, $object_type_id, $pg_ids, 'OR', $selection_members, true);
    			}
    		}
    	}
    	
    	// Si esta parado en 'all' de todas las dimensiones visibles aplico la condicion de que el objeto pertenezca a algun miembro de las dimensiones al cual yo tenga permisos
    	if ($member_count == 0) $dm_conditions .= $all_dim_in_all_conditions;
    	
    	if(count($properties)>0){
    		foreach ($properties as $property=>$values){
    			foreach ($values as $dim_id){
    				if (!in_array($dim_id, $redefined_context)){
    					$redefined_context[] = $dim_id;
    				}
    			}
    		}
    		return self::prepareAssociationConditions($redefined_context, $context_dimensions, $properties, $object_type_id, $pg_ids, $selection_members);
	    }
    	
    	$dimensions = Dimensions::findAll();
    	foreach ($dimensions as $dimension){
    		if ($dimension->canContainObjects() && !in_array($dimension, $context) && !in_array($dimension, $selected_dimensions)){
    			$member_ids = array();
    			$all_members = $dimension->getAllMembers();
    			foreach($all_members as $member) {
    				$member_ids[] = $member->getId();
    			}
    			$dm_conditions .= self::prepareQuery($dm_conditions, $dimension, $member_ids, $object_type_id, $pg_ids, 'OR', $selection_members, true);
    		}
	    }
    	
    	
    	return $dm_conditions;
    }
    
    /**
     * @deprecated
     * Enter description here ...
     * param unknown_type $dm_conditions
     * param unknown_type $dimension
     * param unknown_type $member_ids
     * param unknown_type $object_type_id
     * param unknown_type $pg_ids
     * param unknown_type $operator
     * param unknown_type $selection_members
     * param unknown_type $all
     */
    static function prepareQuery($dm_conditions, $dimension, $member_ids, $object_type_id, $pg_ids, $operator, $selection_members, $all = false){
    	$permission_conditions ="";
    	$member_ids_csv = count($member_ids) > 0 ? implode(",", $member_ids) : '0';
    	$check = $dimension->getDefinesPermissions() && !$dimension->hasAllowAllForContact($pg_ids);
    	if ($check){
    		
    	    // context permissions
    	    $context_conditions = "";
    	    $context_permission_member_ids = array();
    		$context_permission_member_ids = ContactMemberPermissions::getActiveContextPermissions(logged_user(),$object_type_id, $selection_members, $member_ids);
    		if (count($context_permission_member_ids)!= 0) {
		    	$context_conditions .= "OR EXISTS (SELECT `om2`.`object_id` FROM `".TABLE_PREFIX."object_members` `om2` WHERE
	    							`om2`.`object_id` = `om`.`object_id` AND `o`.`object_type_id` = $object_type_id 
	    							AND `om2`.`member_id` IN (" .implode(",", $context_permission_member_ids)."))";
		    }
	    	
    		$permission_conditions = "AND EXISTS (SELECT `cmp`.`member_id` FROM `".TABLE_PREFIX."contact_member_permissions` 
    						`cmp` WHERE `om2`.`member_id` = `cmp`.`member_id` AND `cmp`.`permission_group_id` IN ($pg_ids) AND 
    						`o`.`object_type_id` = `cmp`.`object_type_id`) $context_conditions";
    		
    	}
    	$not_exists = "OR NOT EXISTS (SELECT `om2`.`object_id` FROM `".TABLE_PREFIX."object_members` `om2` WHERE
    						`om2`.`object_id` = `om`.`object_id` AND `om2`.`member_id` IN (".$member_ids_csv.")
    						AND `om2`.`is_optimization` = 0)";
    	
    	$dm_condition = "EXISTS (SELECT `om2`.`object_id` FROM `".TABLE_PREFIX."object_members` `om2` WHERE
    						`om2`.`object_id` = `om`.`object_id` AND `om2`.`member_id` IN (".$member_ids_csv.")
    						AND `om2`.`is_optimization` = 0 $permission_conditions)";
    	
    	if ($all){
    		$condition = "($dm_condition $not_exists)";
    		$operator = "AND";
    	} 
    	else $condition = $dm_condition;
    	$dm_conditions = $dm_conditions != "" ? " $operator $condition" : " $condition";
    	
    	return $dm_conditions;
    }
    
    
    static function prepareAssociationConditions($redefined_context, $dimensions, $properties, $object_type_id, $pg_ids, $selection_members){
    	
    	$is_property = array();
    	foreach ($properties as $p=>$value){
	    		//obtener miembros de la dimension asociada que tienen como propiedad los miembros seleccionados de esta dimension
	    		foreach ($value as $v){
	    			$associations = DimensionMemberAssociations::getAllAssociations($v, $p);
			    		if (!is_null($associations)){
			    			foreach ($associations as $association){
			    				$is_property[$v] = true;
			    				$v_ids_csv = is_array($dimensions[$v]['allowed_members']) && count($dimensions[$v]['allowed_members']) > 0 ? implode(",",$dimensions[$v]['allowed_members']) : '0';
			    				$p_ids_csv = is_array($dimensions[$p]['allowed_members']) && count($dimensions[$p]['allowed_members']) > 0 ? implode(",",$dimensions[$p]['allowed_members']): '0';
			    				$prop_members = MemberPropertyMembers::getAssociatedMembers($association->getId(),$v_ids_csv, $p_ids_csv);
			    				if (count($prop_members)>0)
			    					$property_members[] = $prop_members;
			    			}
			    		}
	    		}
	    }
    		
    	// intersect the allowed members for each property
    	$member_intersection = array_var($property_members, 0, array());
    	if (count($property_members) > 1) {
    		$k = 1;
    		while ($k < count($property_members)) {
    			$member_intersection = array_intersect($member_intersection, $property_members[$k++]);
    		}
    	}

    	$association_conditions = "";
    	foreach ($redefined_context as $key=>$value){
	    		$dimension = Dimensions::getDimensionById($value);
	    		if (!isset($is_property[$value])) $member_ids = $dimensions[$value]['allowed_members'];
	    		else $member_ids = $member_intersection;
	    		$association_conditions.= self::prepareQuery($association_conditions, $dimension, $member_ids,$object_type_id, $pg_ids, 'AND', $selection_members);
    	}
    	$dims = Dimensions::findAll();
    	foreach ($dims as $dim){
    		if (!in_array($dim->getId(), $redefined_context) && !isset($properties[$dim->getId()]) && $dim->canContainObjects()){
    			$member_ids = array();
    			$all_members = $dim->getAllMembers();
    			foreach($all_members as $member) {
    				$member_ids[] = $member->getId();
    			}
		    	$association_conditions.= self::prepareQuery($association_conditions, $dim, $member_ids, $object_type_id, $pg_ids, 'OR', $selection_members, true);
	    			
    		}
    	}
    	
    	return $association_conditions;
    }
    
    
    
    
    function getExternalColumnValue($field, $id) {
    	return "";
    }
    
    
    
	function populateTimeslots($objects_list){
		if (is_array($objects_list) && count($objects_list) > 0 && $objects_list[0]->allowsTimeslots() && $objects_list[0] instanceof ContentDataObject){
			$ids = array();
			$objects = array();
			for ($i = 0; $i < count($objects_list); $i++){
				$ids[] = $objects_list[$i]->getId();
				$objects[$objects_list[$i]->getId()] = $objects_list[$i];
				$objects_list[$i]->timeslots = array();
				$objects_list[$i]->timeslots_count = 0;
			}
			if (count($ids > 0)){
				$result = Timeslots::instance()->listing(array(
					"extra_conditions" => ' AND `e`.`object_id` in (' . implode(',', $ids) . ')'
				));
				$timeslots = $result->objects;
				for ($i = 0; $i < count($timeslots); $i++){
					$object = $objects[$timeslots[$i]->getRelObjectId()];
					$object->timeslots[] = $timeslots[$i];
					$object->timeslots_count = count($object->timeslots);
				}
			}
		}
	}
	
	
	private $member_info_cache = array();
	function getCachedMembersInfo($member_ids) {
		if ($member_ids == null) $member_ids = array();
		
		$res = array();
		$not_found = array();
		foreach ($member_ids as $mid) {
			if (isset($this->member_info_cache[$mid])) {
				$res[] = $this->member_info_cache[$mid];
			} else {
				$not_found[] = $mid;
			}
		}
		
		if (count($not_found) > 0) {
			$db_res = DB::execute("SELECT id, name, dimension_id, object_type_id FROM ".TABLE_PREFIX."members WHERE id IN (".implode(",",$not_found).")");
			$members = $db_res->fetchAll();
			if (is_array($members)) {
				foreach ($members as $m) {
					$this->member_info_cache[$m['id']] = $m;
					$res[] = $m;
				}
			}
		}
		
		return $res;
	}
	
	static function addObjToSharingTable($oid) {
		SharingTables::instance()->fill_sharing_table_by_object($oid);
	}

	function getColumnsToAggregateInTotals() {
		$columns = array();
		Hook::fire('more_columns_to_aggregate_in_totals', $this, $columns);
		return $columns;
	}
	
	
	function getCalculatedColumns() {
		$columns = array();
		Hook::fire('more_object_calculated_columns', $this, $columns);
		return $columns;
	}
	
	
	function getDefinition($ot_id = null) {
	    $columns = $this->getColumns();
	    $object_columns = Objects::instance()->getColumns();
	    $columns = array_merge($columns,$object_columns);
	    
	    if (is_null($ot_id)) {
	    	$ot_id = $this->getObjectTypeId();
	    }
	    
	    $cps = CustomProperties::getAllCustomPropertiesByObjectType($ot_id);
	    
	    $array_cp_info = array();
	    foreach($cps as $cp){
	    	$cp_info = array(
	        		'id' => $cp->getId(),
	        		'type' => $cp->getType(),
	        		'label' => $cp->getName(),
	        		'code' => $cp->getCode(),
	        );
	    	if ($cp->getType() == 'list') {
	    		$list_values = explode(',', $cp->getValues());
	    		
	    		$list_value_labels = array();
	    		foreach ($list_values as $list_value) {
	    			$lang_value = Localization::instance()->lang($list_value);
	    			if (is_null($lang_value)) {
	    				$exp = explode('@', $cp_list_value);
	    				if (count($exp) == 2) {
	    					$lang_value = Localization::instance()->lang($exp[1]);
	    					if (is_null($lang_value)) {
	    						$lang_value = $exp[1];
	    					}
	    				} else {
	    					$lang_value = $list_value;
	    				}
	    			}
	    			$list_value_labels[$list_value] = $lang_value;
	    		}

	    		$cp_info['list_values'] = $list_value_labels;
	    	}
	        $array_cp_info["cp_".$cp->getId()] = $cp_info;
	    }

	    $result = array();
	    $system_columns = $this->getSystemColumns();
	    
	    foreach ($columns as $c){
	        if (!in_array($c, $system_columns)){
	            $result[$c]= array( 'id'=>$c, 'type' => $this->getColumnType($c), 'label'=>lang( 'field '.get_class($this).' '.$c ));
	        }	        
	    }
	    
	    $associated_obj_columns = $this->getAssociatedObjectsFixedColumns();
	    foreach ($associated_obj_columns as $key_col => $col_infos) {
		    foreach ($col_infos as $col) {
		    	$result[$col['col']]= array('id'=>$col['col'], 'type'=>$col['type'], 'label'=>$col['label']);
		    }
	    }
	    
	    $result = array_merge($result,$array_cp_info);
	    return $result;
	}
	
	
	function getAdditionalCustomProperties() {
		return array();
	}
	
	function getAssociatedObjectsFixedColumns() {
		return array();
	}
	
	function getAssociatedObjectManagers() {
		return array();
	}
	
	
	static function generateOldContentObjectData(ContentDataObject $object) {

		$old_content_object = $object->manager()->findById($object->getId(), true);
		$old_content_object->member_ids = $object->getMemberIds();
		$old_content_object->linked_object_ids = $object->getAllLinkedObjectIds();
		$old_content_object->subscriber_ids = $object->getSubscriberIds();
		
		$cps = CustomProperties::getAllCustomPropertiesByObjectType($object->getObjectTypeId());
		
		$cp_values = array();
		foreach ($cps as $cp) {
			$cpval = CustomPropertyValues::instance()->getCustomPropertyValue($object->getId(), $cp->getId());
			$cp_values[$cp->getId()] = $cpval instanceof CustomPropertyValue ? $cpval->getValue() : '';
		}
		$old_content_object->custom_properties = $cp_values;
		
		return $old_content_object;
	}
	
}