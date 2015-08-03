<?php 

  
  /**
  * MailAccounts class
  *
  * @author Carlos Palma <chonwil@gmail.com>
  */
  abstract class BaseMailAccounts extends DataManager {
  
    /**
    * Column name => Column type map
    *
    * @var array
    * @static
    */
    static private $columns = array(
    'id' => DATA_TYPE_INTEGER,
    'contact_id' => DATA_TYPE_INTEGER,  
    'name' => DATA_TYPE_STRING, 
    'email' => DATA_TYPE_STRING, 
    'email_addr' => DATA_TYPE_STRING, 
    'password' => DATA_TYPE_STRING, 
    'server' => DATA_TYPE_STRING, 
    'smtp_server' => DATA_TYPE_STRING, 
    'is_imap' => DATA_TYPE_BOOLEAN, 
    'incoming_ssl' => DATA_TYPE_BOOLEAN, 
    'incoming_ssl_port' => DATA_TYPE_INTEGER, 
    'smtp_port' => DATA_TYPE_INTEGER,  
    'smtp_use_auth' => DATA_TYPE_INTEGER,  
    'smtp_username' => DATA_TYPE_STRING, 
    'smtp_password' => DATA_TYPE_STRING,
    'del_from_server' => DATA_TYPE_INTEGER,
    'mark_read_on_server' => DATA_TYPE_INTEGER,
    'outgoing_transport_type' => DATA_TYPE_STRING,
    'last_checked' => DATA_TYPE_DATETIME,
	'is_default' => DATA_TYPE_BOOLEAN,
    'signature' => DATA_TYPE_STRING,
    'sender_name' => DATA_TYPE_STRING,
    'last_error_date' => DATA_TYPE_DATETIME,
    'last_error_msg' => DATA_TYPE_STRING,
    'sync_addr' => DATA_TYPE_STRING,
    'sync_server' => DATA_TYPE_STRING,
    'sync_pass' => DATA_TYPE_STRING,
    'sync_ssl' => DATA_TYPE_BOOLEAN, 
    'sync_ssl_port' => DATA_TYPE_INTEGER, 
    'sync_folder' => DATA_TYPE_STRING,
    'member_id' => DATA_TYPE_STRING
);
  
    /**
    * Construct
    *
    * @return BaseMailAccounts 
    */
    function __construct() {
    	Hook::fire('object_definition', 'MailAccount', self::$columns);
      parent::__construct('MailAccount', 'mail_accounts', true);
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
    * @return one or MailAccounts objects
    * @throws DBQueryError
    */
    function find($arguments = null) {
      if(isset($this) && instance_of($this, 'MailAccounts')) {
        return parent::find($arguments);
      } else {
        return MailAccounts::instance()->find($arguments);
        //$instance =& MailAccounts::instance();
        //return $instance->find($arguments);
      } // if
    } // find
    
    /**
    * Find all records
    *
    * @access public
    * @param array $arguments
    * @return one or MailAccounts objects
    */
    function findAll($arguments = null) {
      if(isset($this) && instance_of($this, 'MailAccounts')) {
        return parent::findAll($arguments);
      } else {
        return MailAccounts::instance()->findAll($arguments);
        //$instance =& MailAccounts::instance();
        //return $instance->findAll($arguments);
      } // if
    } // findAll
    
    /**
    * Find one specific record
    *
    * @access public
    * @param array $arguments
    * @return MailAccount 
    */
    function findOne($arguments = null) {
      if(isset($this) && instance_of($this, 'MailAccounts')) {
        return parent::findOne($arguments);
      } else {
        return MailAccounts::instance()->findOne($arguments);
        //$instance =& MailAccounts::instance();
        //return $instance->findOne($arguments);
      } // if
    } // findOne
    
    /**
    * Return object by its PK value
    *
    * @access public
    * @param mixed $id
    * @param boolean $force_reload If true cache will be skipped and data will be loaded from database
    * @return MailAccount 
    */
    function findById($id, $force_reload = false) {
      if(isset($this) && instance_of($this, 'MailAccounts')) {
        return parent::findById($id, $force_reload);
      } else {
        return MailAccounts::instance()->findById($id, $force_reload);
        //$instance =& MailAccounts::instance();
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
      if(isset($this) && instance_of($this, 'MailAccounts')) {
        return parent::count($condition);
      } else {
        return MailAccounts::instance()->count($condition);
        //$instance =& MailAccounts::instance();
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
      if(isset($this) && instance_of($this, 'MailAccounts')) {
        return parent::delete($condition);
      } else {
        return MailAccounts::instance()->delete($condition);
        //$instance =& MailAccounts::instance();
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
      if(isset($this) && instance_of($this, 'MailAccounts')) {
        return parent::paginate($arguments, $items_per_page, $current_page);
      } else {
        return MailAccounts::instance()->paginate($arguments, $items_per_page, $current_page);
        //$instance =& MailAccounts::instance();
        //return $instance->paginate($arguments, $items_per_page, $current_page);
      } // if
    } // paginate
    
    /**
    * Return manager instance
    *
    * @return MailAccounts 
    */
    function instance() {
      static $instance;
      if(!instance_of($instance, 'MailAccounts')) {
        $instance = new MailAccounts();
      } // if
      return $instance;
    } // instance
  
  } // MailAccounts 

?>