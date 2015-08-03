<?php 

  
  /**
  * BaseSystemPermissions class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  abstract class BaseSystemPermissions extends DataManager {
  
    /**
    * Column name => Column type map
    *
    * @var array
    * @static
    */
    static private $columns = array(
    	'permission_group_id' => DATA_TYPE_INTEGER,
    	'can_manage_security' => DATA_TYPE_BOOLEAN, 
    	'can_manage_configuration' => DATA_TYPE_BOOLEAN,
    	'can_manage_templates' => DATA_TYPE_BOOLEAN,  
    	'can_manage_time' => DATA_TYPE_BOOLEAN,
    	'can_add_mail_accounts' => DATA_TYPE_BOOLEAN,
    	'can_manage_dimensions' => DATA_TYPE_BOOLEAN,
	    'can_manage_dimension_members' => DATA_TYPE_BOOLEAN,
	    'can_manage_tasks' => DATA_TYPE_BOOLEAN,
	    'can_task_assignee' => DATA_TYPE_BOOLEAN,
	    'can_manage_billing' => DATA_TYPE_BOOLEAN,
	    'can_view_billing' => DATA_TYPE_BOOLEAN,
	    'can_see_assigned_to_other_tasks' => DATA_TYPE_BOOLEAN,
	    'can_update_other_users_invitations' => DATA_TYPE_BOOLEAN,
    	'can_link_objects' => DATA_TYPE_BOOLEAN,
    );
  
    /**
    * Construct
    *
    * @return BaseSystemPermissions 
    */
    function __construct() {
      Hook::fire('object_definition', 'SystemPermission', self::$columns);
      parent::__construct('SystemPermission', 'system_permissions', true);
    } // __construct
    
    // -------------------------------------------------------
    //  Description methods
    // -------------------------------------------------------
    
    /**
    * Return array of object columns
    *
    * @access public
    * @param void
    * @return array
    */
    function getColumns() {
      return array_keys(self::$columns);
    } // getColumns
    
    /**
    * Return column type
    *
    * @access public
    * @param string $column_name
    * @return string
    */
    function getColumnType($column_name) {
      if(isset(self::$columns[$column_name])) {
        return self::$columns[$column_name];
      } else {
        return DATA_TYPE_STRING;
      } // if
    } // getColumnType
    
    /**
    * Return array of PK columns. If only one column is PK returns its name as string
    *
    * @access public
    * @param void
    * @return array or string
    */
    function getPkColumns() {
      return 'permission_group_id';
    } // getPkColumns
    
    /**
    * Return name of first auto_incremenent column if it exists
    *
    * @access public
    * @param void
    * @return string
    */
    function getAutoIncrementColumn() {
      return null;
    } // getAutoIncrementColumn
    
    // -------------------------------------------------------
    //  Finders
    // -------------------------------------------------------
    
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
    * @return one or SystemPermissions objects
    * @throws DBQueryError
    */
    function find($arguments = null) {
      if(isset($this) && instance_of($this, 'SystemPermissions')) {
        return parent::find($arguments);
      } else {
        return SystemPermissions::instance()->find($arguments);
      } // if
    } // find
    
    /**
    * Find all records
    *
    * @access public
    * @param array $arguments
    * @return one or SystemPermissions objects
    */
    function findAll($arguments = null) {
      if(isset($this) && instance_of($this, 'SystemPermissions')) {
        return parent::findAll($arguments);
      } else {
        return SystemPermissions::instance()->findAll($arguments);
      } // if
    } // findAll
    
    /**
    * Find one specific record
    *
    * @access public
    * @param array $arguments
    * @return SystemPermission 
    */
    function findOne($arguments = null) {
      if(isset($this) && instance_of($this, 'SystemPermissions')) {
        return parent::findOne($arguments);
      } else {
        return SystemPermissions::instance()->findOne($arguments);
      } // if
    } // findOne
    
    /**
    * Return object by its PK value
    *
    * @access public
    * @param mixed $id
    * @param boolean $force_reload If true cache will be skipped and data will be loaded from database
    * @return SystemPermission 
    */
    function findById($id, $force_reload = false) {
      if(isset($this) && instance_of($this, 'SystemPermissions')) {
        return parent::findById($id, $force_reload);
      } else {
        return SystemPermissions::instance()->findById($id, $force_reload);
      } // if
    } // findById
    
    /**
    * Return number of rows in this table
    *
    * @access public
    * @param string $conditions Query conditions
    * @return integer
    */
    function count($condition = null) {
      if(isset($this) && instance_of($this, 'SystemPermissions')) {
        return parent::count($condition);
      } else {
        return SystemPermissions::instance()->count($condition);
      } // if
    } // count
    
    /**
    * Delete rows that match specific conditions. If $conditions is NULL all rows from table will be deleted
    *
    * @access public
    * @param string $conditions Query conditions
    * @return boolean
    */
    function delete($condition = null) {
      if(isset($this) && instance_of($this, 'SystemPermissions')) {
        return parent::delete($condition);
      } else {
        return SystemPermissions::instance()->delete($condition);
      } // if
    } // delete
    
    /**
    * This function will return paginated result. Result is an array where first element is 
    * array of returned object and second populated pagination object that can be used for 
    * obtaining and rendering pagination data using various helpers.
    * 
    * Items and pagination array vars are indexed with 0 for items and 1 for pagination
    * because you can't use associative indexing with list() construct
    *
    * @access public
    * @param array $arguments Query argumens (@see find()) Limit and offset are ignored!
    * @param integer $items_per_page Number of items per page
    * @param integer $current_page Current page number
    * @return array
    */
    function paginate($arguments = null, $items_per_page = 10, $current_page = 1) {
      if(isset($this) && instance_of($this, 'SystemPermissions')) {
        return parent::paginate($arguments, $items_per_page, $current_page);
      } else {
        return SystemPermissions::instance()->paginate($arguments, $items_per_page, $current_page);
      } // if
    } // paginate
    
    /**
    * Return manager instance
    *
    * @return SystemPermissions 
    */
    function instance() {
      static $instance;
      if(!instance_of($instance, 'SystemPermissions')) {
        $instance = new SystemPermissions();
      } // if
      return $instance;
    } // instance
  
  } // BaseSystemPermissions 

?>