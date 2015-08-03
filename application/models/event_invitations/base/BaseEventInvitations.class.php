<?php 

  
  /**
  * BaseEventInvitations class
  *
  * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
  */
  abstract class BaseEventInvitations extends DataManager {
  
    /**
    * Column name => Column type map
    *
    * @var array
    * @static
    */
    static private $columns = array('event_id' => DATA_TYPE_INTEGER, 'contact_id' => DATA_TYPE_INTEGER, 'invitation_state' => DATA_TYPE_INTEGER, 'synced' => DATA_TYPE_INTEGER,'special_id' => DATA_TYPE_STRING);
  
    /**
    * Construct
    *
    * @return BaseEventInvitations 
    */
    function __construct() {
    	Hook::fire('object_definition', 'EventInvitation', self::$columns);
      parent::__construct('EventInvitation', 'event_invitations', true);
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
    	return array('event_id', 'contact_id');
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
    * @return one or EventInvitations objects
    * @throws DBQueryError
    */
    function find($arguments = null) {
      if(isset($this) && instance_of($this, 'EventInvitations')) {
        return parent::find($arguments);
      } else {
        return EventInvitations::instance()->find($arguments);
        //$instance =& EventInvitations::instance();
        //return $instance->find($arguments);
      } // if
    } // find
    
    /**
    * Find all records
    *
    * @access public
    * @param array $arguments
    * @return one or EventInvitations objects
    */
    function findAll($arguments = null) {
      if(isset($this) && instance_of($this, 'EventInvitations')) {
        return parent::findAll($arguments);
      } else {
        return EventInvitations::instance()->findAll($arguments);
        //$instance =& EventInvitations::instance();
        //return $instance->findAll($arguments);
      } // if
    } // findAll
    
    /**
    * Find one specific record
    *
    * @access public
    * @param array $arguments
    * @return EventInvitation 
    */
    function findOne($arguments = null) {
      if(isset($this) && instance_of($this, 'EventInvitations')) {
        return parent::findOne($arguments);
      } else {
        return EventInvitations::instance()->findOne($arguments);
        //$instance =& EventInvitations::instance();
        //return $instance->findOne($arguments);
      } // if
    } // findOne
    
    /**
    * Return object by its PK value
    *
    * @access public
    * @param mixed $id
    * @param boolean $force_reload If true cache will be skipped and data will be loaded from database
    * @return EventInvitation 
    */
    function findById($id, $force_reload = false) {
      if(isset($this) && instance_of($this, 'EventInvitations')) {
        return parent::findById($id, $force_reload);
      } else {
        return EventInvitations::instance()->findById($id, $force_reload);
        //$instance =& EventInvitations::instance();
        //return $instance->findById($id, $force_reload);
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
      if(isset($this) && instance_of($this, 'EventInvitations')) {
        return parent::count($condition);
      } else {
        return EventInvitations::instance()->count($condition);
        //$instance =& EventInvitations::instance();
        //return $instance->count($condition);
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
      if(isset($this) && instance_of($this, 'EventInvitations')) {
        return parent::delete($condition);
      } else {
        return EventInvitations::instance()->delete($condition);
        //$instance =& EventInvitations::instance();
        //return $instance->delete($condition);
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
      if(isset($this) && instance_of($this, 'EventInvitations')) {
        return parent::paginate($arguments, $items_per_page, $current_page);
      } else {
        return EventInvitations::instance()->paginate($arguments, $items_per_page, $current_page);
        //$instance =& EventInvitations::instance();
        //return $instance->paginate($arguments, $items_per_page, $current_page);
      } // if
    } // paginate
    
    /**
    * Return manager instance
    *
    * @return EventInvitations 
    */
    function instance() {
      static $instance;
      if(!instance_of($instance, 'EventInvitations')) {
        $instance = new EventInvitations();
      } // if
      return $instance;
    } // instance
  
  } // EventInvitations 

?>