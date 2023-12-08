<?php 

  
  /**
  * BaseContactExternalTokens class
  */
abstract class BaseContactExternalTokens extends DataManager {
  
    /**
    * Column name => Column type map
    *
    * @var array
    * @static
    */
    static private $columns = array(
    	'id' => DATA_TYPE_INTEGER, 
    	'contact_id' => DATA_TYPE_INTEGER, 
    	'token' => DATA_TYPE_STRING,
        'type' => DATA_TYPE_STRING,
        'external_key' => DATA_TYPE_STRING,
        'external_name' => DATA_TYPE_STRING,
        'created_date' => DATA_TYPE_DATETIME,
    	'expired_date' => DATA_TYPE_DATETIME,
    );
  
    /**
    * Construct
    *
    * @return BaseContactExternalTokens 
    */
    function __construct() {
      parent::__construct('ContactExternalToken', 'contact_external_tokens', true);
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
      return 'id';
    } // getPkColumns
    
    /**
    * Return name of first auto_incremenent column if it exists
    *
    * @access public
    * @param void
    * @return string
    */
    function getAutoIncrementColumn() {
      return 'id';
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
    * @return one or ContactExternalTokens objects
    * @throws DBQueryError
    */
    function find($arguments = null) {
      if(isset($this) && instance_of($this, 'ContactExternalTokens')) {
        return parent::find($arguments);
      } else {
          return ContactExternalTokens::instance()->find($arguments);
      } // if
    } // find
    
    /**
    * Find all records
    *
    * @access public
    * @param array $arguments
    * @return one or ContactExternalToken objects
    */
    function findAll($arguments = null) {
      if(isset($this) && instance_of($this, 'ContactExternalTokens')) {
        return parent::findAll($arguments);
      } else {
          return ContactExternalTokens::instance()->findAll($arguments);
      } // if
    } // findAll
    
    /**
    * Find one specific record
    *
    * @access public
    * @param array $arguments
    * @return ContactExternalToken 
    */
    function findOne($arguments = null) {
      if(isset($this) && instance_of($this, 'ContactExternalTokens')) {
        return parent::findOne($arguments);
      } else {
          return ContactExternalTokens::instance()->findOne($arguments);
      } // if
    } // findOne
    
    /**
    * Return object by its PK value
    *
    * @access public
    * @param mixed $id
    * @param boolean $force_reload If true cache will be skipped and data will be loaded from database
    * @return Contact 
    */
    function findById($id, $force_reload = false) {
      if(isset($this) && instance_of($this, 'ContactExternalTokens')) {
        return parent::findById($id, $force_reload);
      } else {
          return ContactExternalTokens::instance()->findById($id, $force_reload);
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
      if(isset($this) && instance_of($this, 'ContactExternalTokens')) {
        return parent::count($condition);
      } else {
          return ContactExternalTokens::instance()->count($condition);
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
      if(isset($this) && instance_of($this, 'ContactExternalTokens')) {
        return parent::delete($condition);
      } else {
          return ContactExternalTokens::instance()->delete($condition);
      } // if
    } // delete
   
    
    /**
    * Return manager instance
    *
    * @return ContactExternalTokens 
    */
    static function instance() {
      static $instance;
      if(!instance_of($instance, 'ContactExternalTokens')) {
          $instance = new ContactExternalTokens();
      } // if
      return $instance;
    } // instance
  
} // ContactExternalTokens

?>