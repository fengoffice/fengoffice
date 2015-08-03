<?php
  
  /**
  * Data manager class
  *
  * This class implements methods for managing data objects, database rows etc. One 
  * of its features is automatinc caching of loaded data.
  *
  * @package System
  * @version 1.0
  * @author Ilija Studen <ilija.studen@gmail.com>
  * @copyright 2005 by Ilija Studen
  */
  abstract class DataManager {
  
    /**
    * Database table where items are saved
    *
    * @var string
    */
    private $table_name;
    
    /**
    * Item cache array
    *
    * @var array
    */
    private $cache = array();
    
    /**
    * Class of items that this manager is handling
    *
    * @var string
    */
    private $item_class = '';
    
    /**
    * Cache items
    *
    * @var boolean
    */
    private $caching = true;
    
    /**
    * Construct and set item class
    *
    * @access public
    * @param string $item_class Value of class of items that this manager is handling
    * @param string $table Table where data is stored
    * @param boolean $caching Caching stamp value
    * @return DataManager
    */
    function __construct($item_class, $table_name, $caching = true) {
      $this->setItemClass($item_class);
      $this->setTableName($table_name);
      $this->setCaching($caching);
    } // end func __construct
    
    // ---------------------------------------------------
    //  Definition methods
    // ---------------------------------------------------
    
    /**
    * Return array of object columns
    *
    * @access public
    * @param void
    * @return array
    */
    abstract function getColumns();
    
    /**
    * Return column type
    *
    * @access public
    * @param string $column_name
    * @return string
    */
    abstract function getColumnType($column_name);
    
    /**
    * Return array of PK columns. If only one column is PK returns its name as string
    *
    * @access public
    * @param void
    * @return array or string
    */
    abstract function getPkColumns();
    
    /**
    * Return name of first auto_incremenent column if it exists
    *
    * @access public
    * @param void
    * @return string
    */
    abstract function getAutoIncrementColumn();
    
    /**
    * Return system columns
    *
    * @access public
    * @param void
    * @return array
    */
    function getSystemColumns() {
      return array('object_id', 'object_type_id', 'created_by_id', 'updated_by_id', 'trashed_on', 'trashed_by_id', 'archived_by_id');
    } // getSystemColumns
    
    /**
    * Return external columns
    *
    * @access public
    * @param void
    * @return array
    */
    function getExternalColumns() {
      return array('created_by_id', 'updated_by_id');
    } // getExternalColumns
    
    /**
    * Return report object title columns
    *
    * @access public
    * @param void
    * @return array
    */
    function getReportObjectTitleColumns() {
      return array('id');
    } // getReportObjectTitleColumns
    
    /**
    * Return report object title
    *
    * @access public
    * @param void
    * @return string
    */
    function getReportObjectTitle($values) {
    	foreach(self::getReportObjectTitleColumns() as $title){
      		if(isset($values[$title])){
      			return $title;
      		}
    	}
    	return '';
    } // getReportObjectTitle
    
    /**
    * Return template object properties
    *
    * @access public
    * @param void
    * @return string
    */
    function getTemplateObjectProperties() {
    	return array();
    } // getTemplateObjectProperties
    
    /**
    * Return array of lazy load columns
    *
    * @access public
    * @param void
    * @return array
    */
    function getLazyLoadColumns() {
      return array();
    } // getLazyLoadColumnss
    
    /**
    * Check if specific column is lazy load column
    *
    * @access public
    * @param string $column_name
    * @return boolean
    */
    function isLazyLoadColumn($column_name) {
      return in_array($column_name, $this->getLazyLoadColumns());
    } // isLazyLoadColumn
    
    /**
    * Return all columns that are not martked as lazy load
    *
    * @access public
    * @param boolean $escape_column_names
    * @return array
    */
    function getLoadColumns($escape_column_names = false) {
      
      // Prepare
      $load_columns = array();
      
      // Loop...
      $columns = $this->getColumns();
      foreach($columns as $column) {
      //  if(!$this->isLazyLoadColumn($column)) {
          $load_columns[] = $escape_column_names ? DB::escapeField($column) : $column;
      //  } // if
      } // foreach
      
      $columns = null;
      
      // Done...
      return $load_columns;
      
    } // getLoadColumns
    
    /**
  	* Check if specific column exists in this object
  	*
  	* @access public
  	* @param string $column_name
  	* @return boolean
  	*/
  	function columnExists($column_name) {
  	  $columns = $this->getColumns();
  	  $res = in_array($column_name, $columns);
  	  $columns = null;
  	  
  	  return $res;
  	} // columnExists
    
    // ---------------------------------------------------
    //  Finders
    // ---------------------------------------------------
    
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
    * @return one or many objects
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
      $columns    = array_var($arguments, 'columns', null);
      
      // limit = 1 when findOne is invoked
      if ($one) {
      	$limit = 1;
      }
      
      // Prepare query parts
      $where_string = trim($conditions) == '' ? '' : "WHERE " . preg_replace("/\s+in\s*\(\s*\)/i", " = -1", $conditions);
      $order_by_string = trim($order_by) == '' ? '' : "ORDER BY $order_by";
      $limit_string = $limit > 0 ? "LIMIT $offset, $limit" : '';
      $distinct = $distinct ? "DISTINCT " : "";
      
      if ($columns && is_array($columns) && count($columns) > 0) {
      	$columns_string = "`" . implode('`, `', $columns) . "`";
      } else {
      	$columns_string = ($id ? '`id`' : '*');
      }
      // Prepare SQL
      $sql = "SELECT $distinct" . $columns_string . " FROM " . $this->getTableName(true) . " $where_string $order_by_string $limit_string";

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
      	$rows = null;
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
    * Find all records
    *
    * @access public
    * @param array $arguments
    * @return array
    */
    function findAll($arguments = null) {
      if(!is_array($arguments)) $arguments = array();
      $arguments['one'] = false;
      $ret = $this->find($arguments);
      if (is_array($ret)) {
      	return $ret;
      } else {
      	return array();
      }
    } // findAll
    
    /**
    * Find one specific record
    *
    * @access public
    * @param array $arguments
    * @return array
    */
    function findOne($arguments = null) {
      if(!is_array($arguments)) $arguments = array();
      $arguments['one'] = true;
      return $this->find($arguments);
    } // findOne
    
    /**
    * Return object by its PK value
    *
    * @access public
    * @param mixed $id
    * @param boolean $force_reload If value of this variable is true cached value
    *   will be skipped and new data will be loaded from database
    * @return object
    */
    function findById($id, $force_reload = false) {
      return $this->load($id, $force_reload);
    } // findById
    
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
      $row = DB::executeOne("SELECT COUNT($escaped_pk) AS 'row_count' FROM " . $this->getTableName(true) . " $where_string");
      return (integer) array_var($row, 'row_count', 0);
    } // count
    
    /**
    * Delete rows from this table that match specific conditions
    *
    * @access public
    * @param string $conditions Query conditions
    * @return boolean
    */
    function delete($conditions = null) {
      $conditions = $this->prepareConditions($conditions);
      $where_string = trim($conditions) == '' ? '' : "WHERE $conditions";
      return DB::execute("DELETE FROM " . $this->getTableName(true) . " $where_string");
    } // delete
    
    /**
    * This function will return paginated result. Result is array where first element is 
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
    function paginate($arguments = null, $items_per_page = 10, $current_page = 1, $count = null) {
      if(!is_array($arguments)) $arguments = array();
      $conditions = array_var($arguments, 'conditions');
      if (defined('INFINITE_PAGING') && INFINITE_PAGING) $count = 10000000;
      $pagination = new DataPagination($count ? $count : $this->count($conditions), $items_per_page, $current_page);
      
      $arguments['offset'] = $pagination->getLimitStart();
      $arguments['limit'] = $pagination->getItemsPerPage();
      
      $items = $this->findAll($arguments);
      return array($items, $pagination);
    } // paginate
    
    /**
    * Get conditions as argument and return them in the string (if array walk through and escape values)
    *
    * @param mixed $conditions
    * @return string
    */
    function prepareConditions($conditions) {
      if(is_array($conditions)) {
        $conditions_sql = array_shift($conditions);
        $conditions_arguments = count($conditions) ? $conditions : null;
        return DB::prepareString($conditions_sql, $conditions_arguments);
      } // if
      return $conditions;
    } // prepareConditions
    
    /**
    * Load specific item. If we can't load data return NULL, else return item object
    *
    * @access public
    * @param mixed $id Item ID
    * @param boolean $force_reload If this value is true cached value (if set) will be skipped
    *   and object data will be loaded from database
    * @return DataObject
    */
    function load($id, $force_reload = false) {
    
      // Is manager ready to do the job?
      if(!$this->isReady()) return null;
      
      // If caching and we dont need to reload check the cache...
      if(!$force_reload && $this->getCaching()) {
        $item = $this->getCachedItem($id);
        if(instance_of($item, $this->getItemClass())) return $item;
      } // if
      
      // Get object from row...
      $object = $this->loadFromRow($this->loadRow($id));
      
      // Check item...
      if(!instance_of($object, $this->getItemClass())) return null;
      
      // If loaded cache and return...
      if($object->isLoaded()) {
        if($this->getCaching()) $this->cacheItem($object);
        return $object;
      } // if
      
      // Item not loaded...
      return null;
      
    } // end func load
    
    /**
    * Load row from database based on ID
    *
    * @access public
    * @param mixed $id
    * @return array
    */
    function loadRow($id) {
      $cols = $this->getLoadColumns(true);
      $imploded = implode(', ', $cols);
      $sql = sprintf("SELECT %s FROM %s WHERE %s", 
        $imploded, 
        $this->getTableName(true), 
        $this->getConditionsById($id)
      ); // sprintf
      
      $cols = null;
      $imploded = null;
      
      return DB::executeOne($sql);
    } // loadRow
    
    /**
    * Load item from database row
    *
    * @access public
    * @param array $row Row from witch we need to load data...
    * @return DataObject
    */
    function loadFromRow($row) {
    
      // Is manager ready?
      if(!$this->isReady()) return null;
      
      // OK, get class and construct item...
      $class = $this->getItemClass();
      $item = new $class();

      // If not valid item break
      if(!instance_of($item, 'DataObject')) return null;

      // Load item...
      if($item->loadFromRow($row) && $item->isLoaded()) {
        if($this->getCaching()) $this->cacheItem($item);
        return $item;
      } // if
      
      // Item not loaded, from some reason
      return null;
      
    } // end func loadFromRow
    
    /**
    * Return condition part of query by value(s) of PK column(s)
    *
    * @access public
    * @param array or string $id
    * @return string
    */
    function getConditionsById($id) {
      
      // Prepare data...
  	  $pks = $this->getPkColumns();
  	  
  	  // Multiple PKs?
  	  if(is_array($pks)) {
  	  	
  	  	// Ok, prepare it...
  	  	$where = array();
  	  	
  	  	// Loop PKs
  	  	foreach($pks as $column) {
  	  	  if(isset($id[$column])) {
  	  	    $where[] = sprintf('%s = %s', DB::escapeField($column), DB::escape($id[$column]));
  	  	  } // if
  	  	} // foreach
  	  	
  	  	// Join...
  	  	if(is_array($where) && count($where)) {
  	  	  return count($where) > 1 ? implode(' AND ', $where) : $where[0];
  	  	} else {
  	  	  return '';
  	  	} // if
  	  	
  	  } else {
  	    return sprintf('%s = %s', DB::escapeField($pks), DB::escape($id));
  	  } // if
  	  
    } // getConditionsById
    
    // ----------------------------------------------------
    //  Caching
    // ----------------------------------------------------
    
    /**
    * Get specific item from cache
    *
    * @access public
    * @param mixed $id Item ID
    * @return DataObject
    */
    function getCachedItem($id) {
    
      // Multicolumn PK
      if(is_array($id)) {
        
        // Lock first cache level
        $array = $this->cache;
        
        // Loop IDs until we reach the end
        foreach($id as $id_field) {
          if(is_array($array) && isset($array[$id_field])) {
            $array = $array[$id_field];
          } // if
        } // if
        
        // If we have valid instance return it
        if(instance_of($array, 'DataObject')) return $array;
        
      } else {
      
        // If we have it in cache return it...
        if(isset($this->cache[$id]) && instance_of($this->cache[$id], $this->getItemClass())) {
          return $this->cache[$id];
        } // if
        
      } // if
      
      // Item not cache...
      return null;
      
    } // end func getCacheItem
    
    /**
    * Add this item to cache
    *
    * @access public
    * @param DataObject $item Item that need to be cached
    * @return boolean
    */
    function cacheItem($item) {
      
      // Check item instance...
      if(!instance_of($item, 'DataObject') || !$item->isLoaded()) return false;
      
      // Get PK column(s)
      $id = $item->getPkColumns();
      
      // If array them we have item with multiple items...
      if(is_array($id)) {
        
        // First level is cahce
        $array = $this->cache;
        
        // Set counter
        $iteration = 0;
        
        // Loop fields
        foreach($id as $id_field) {
          
          // Value of this field...
          $field_value = $item->getColumnValue($id_field);
          
          // Increment counter
          $iteration++;
          
          // Last field? Cache object here
          if($iteration == count($id)) {
            $array[$field_value] = $item;
          
          // Prepare for next iteration and continue...
          } else {
            if(!isset($array[$field_value]) || !is_array($array[$field_value])) $array[$field_value] = array();
            $array =& $array[$field_value];
          } // if
          
        } // foreach
        
      } else {
        $this->cache[$item->getColumnValue($id)] = $item;
      } // if
      
      // Done...
      return true;
      
    } // end func setCacheItem
    
    /**
    * Clear the item cache
    *
    * @access public
    * @param void
    * @return void
    */
    function clearCache() {
      $this->cache = array();
    } // end func clearCache
    
    // ---------------------------------------------------
    //  Getters and setters
    // ---------------------------------------------------
    
    /**
    * Get the value of item class
    *
    * @access public
    * @param void
    * @return string
    */
    function getItemClass() {
      return $this->item_class;
    } // end func getItemClass
    
    /**
    * Set value of item class. This function will set the value only when item class is
    * defined, else it will return FALSE.
    *
    * @access public
    * @param string $value New item class value
    * @return null
    */
    function setItemClass($value) {
      $this->item_class = trim($value);
    } // end func setItemClass
    
    /**
    * Return table name. Options include adding table prefix in front of table name (true by 
    * default) and escaping resulting name, usefull for using in queries (false by default)
    *
    * @access public
    * @param boolean $escape Return escaped table name
    * @param boolean $with_prefix Include table prefix. This functionality is added when
    *   installer was built so user can set custom table prefix, not default 'pm_'
    * @return string
    */
    function getTableName($escape = false, $with_prefix = true) {
      $table_prefix = $with_prefix ? TABLE_PREFIX : "";
      if (defined('FORCED_TABLE_PREFIX') && FORCED_TABLE_PREFIX) $table_prefix = FORCED_TABLE_PREFIX;
      $table_name = $table_prefix . $this->table_name;
      return $escape ? DB::escapeField($table_name) : $table_name;
    } // end func getTableName
    
    /**
    * Set items table
    *
    * @access public
    * @param string $value Table name
    * @return void
    */
    function setTableName($value) {
      $this->table_name = trim($value);
    } // end func setTableName
    
    /**
    * Return value of caching stamp
    *
    * @access public
    * @param void
    * @return boolean
    */
    function getCaching() {
      return (boolean) $this->caching;
    } // end func getCaching
    
    /**
    * Set value of caching property
    *
    * @access public
    * @param boolean $value New caching value
    * @return void
    */
    function setCaching($value) {
      $this->caching = (boolean) $value;
    } // end func setCaching
    
    /**
    * Check if manager is ready to do the job
    *
    * @access private
    * @param void
    * @return boolean
    */
    function isReady() {
      return class_exists($this->item_class);
    } // end func isReady
    
    // ---------------------------------------------------
    //  Checkers
    // ---------------------------------------------------
    
    function is_valid_csv_ids($csv) {
    	$exploded = explode(",", $csv);
    	foreach ($exploded as $value) {
    		if (!is_numeric(trim($value))) return false;
    	}
    	return true;
    }
  } // end func DataManager

?>