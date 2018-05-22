<?php

  /**
  * Data object class
  *
  * This class enables easy implementation of any object that is based
  * on single database row. It enables reading, updating, inserting and 
  * deleting that row without writing any SQL. Also, it can chack if 
  * specific row exists in database.
  * 
  * This class supports PKs over multiple fields
  *
  * @package System
  * @version 1.0.1
  * @author Ilija Studen <ilija.studen@gmail.com>
  * @copyright 2005 by Ilija Studen
  */
  abstract class DataObject {
    
  	/**
  	 * Indicates if the 'create' and 'update' timestamps will be set on the save() method.
  	 *
  	 * @var unknown_type
  	 */
  	private $mark_timestamps = true;
  	
  	/**
  	* Indicates if this is new object (not saved)
  	*
  	* @var boolean
  	*/
  	private $is_new = true;
  	
  	/**
  	* Indicates if this object have been deleted from database
  	*
  	* @var boolean
  	*/
  	private $is_deleted = false;
  	
  	/**
  	* Object is loaded
  	*
  	* @var boolean
  	*/
  	private $is_loaded = false;
  	
  	/**
  	* Cached column values
  	*
  	* @var array
  	*/
  	private $column_values = array();
  	
  	/**
  	* Array of modified columns (if any)
  	*
  	* @var array
  	*/
  	private $modified_columns = array();
  	
  	/**
  	* Array of updated primary key columns with cached old values (used in WHERE clausule on update or delete)
  	*
  	* @var array
  	*/
  	private $updated_pks = array();
  	
  	/**
  	* Manager object instance
  	*
  	* @var DataManager
  	*/
  	protected $manager;
  	
  	/**
  	* Array of protected attributes that can not be set through mass-assignment functions (like setFromAttributes)
  	* 
  	* One of the great ActiveRecord tricks
  	*
  	* @var array
  	*/
  	protected $attr_protected = array('id', 'created_on', 'created_by_id', 'updated_on', 'updated_by_id');
  	
  	/**
  	* Array of acceptable attributes (fields) that can be set through mass-assignment function (setFromAttributes)
  	* 
  	* One of the great ActiveRecord tricks
  	*
  	* @var array
  	*/
  	protected $attr_acceptable = null;
  	
  	/**
  	 * Set to true to append ON DUPLICATE KEY UPDATE to insert query
  	 * 
  	 * @var boolean
  	 */
  	protected $use_on_duplicate_key_when_insert = false;
  	
  	/**
  	 * Array of fields with errors after validation
  	 * 
  	 * @var array
  	 */
  	protected $fields_with_errors_after_validation = null;
  	
  	function setUseOnDuplicateKeyWhenInsert($value) {
  		$this->use_on_duplicate_key_when_insert = $value;
  	}
  	function getUseOnDuplicateKeyWhenInsert() {
  		return $this->use_on_duplicate_key_when_insert;
  	}
  	
  	/**
  	* Constructor
  	*
  	* @param void
  	* @return null
  	*/
  	function __construct() {
  	  // Empty...
  	} // __construct
  	
  	// ----------------------------------------------------------
  	//  Abstract function
  	// ----------------------------------------------------------
  	
  	/**
  	* Return object manager
  	*
  	* @access public
  	* @param void
  	* @return DataManager
  	*/
  	abstract function manager();

  	
  	/**
  	* Validate input data (usualy collected from from). This method is called
  	* before the item is saved and can be used to fetch errors in data before
  	* we really save it database. $errors array is populated with errors
  	*
  	* @access public
  	* @param array $errors
  	* @return boolean
  	* @throws ModelValidationError
  	*/
  	function validate($errors) {
  	  return true;
  	} // validate
  	
  	/**
  	* Set object attributes / properties. This function will take hash and set 
  	* value of all fields that she finds in the hash
  	*
  	* @access public
  	* @param array $attributes
  	* @return null
  	*/
  	function setFromAttributes($attributes) {
  	  if(is_array($attributes)) {
  	    foreach($attributes as $k => &$v) {
  	      if(is_array($this->attr_protected) && in_array($k, $this->attr_protected)) {
  	      	continue; // protected attribute
  	      } // if
  	      if(is_array($this->attr_acceptable) && !in_array($k, $this->attr_acceptable)) {
  	      	continue; // not acceptable
  	      } // if
  	      
  	      if($this->columnExists($k)) {
  	      	$this->setColumnValue($k, $attributes[$k]); // column exists, set
  	      } // if
  	    } // foreach
  	    
  	  } // if
  	} // setFromAttributes
  	
  	/**
  	* Return table name
  	*
  	* @access public
  	* @param boolean $escape Escape table name
  	* @return boolean
  	*/
  	function getTableName($escape = false) {
  	  return $this->manager()->getTableName($escape);
  	} // getTableName
  	
  	/**
  	* Return array of columns
  	*
  	* @access public
  	* @param void
  	* @return array
  	*/
  	function getColumns() {
  	  return $this->manager()->getColumns();
  	} // getColumns
  	
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
  	
  	/**
  	* Return type of specific column
  	*
  	* @access public
  	* @param string $column_name
  	* @return string
  	*/
  	function getColumnType($column_name) {
  	  return $this->manager()->getColumnType($column_name);
  	} // getColumnType
  	
  	/**
  	* Return name of Primary key column (or array of columns)
  	*
  	* @access public
  	* @param void
  	* @return string or array
  	*/
  	function getPkColumns() {
  	  return $this->manager()->getPkColumns();
  	} // getPkColumns
  	
  	/**
  	* Check if specific column is part of the primary key
  	*
  	* @access public
  	* @param string $column Column that need to be checked
  	* @return boolean
  	*/
  	function isPrimaryKeyColumn($column) {
  	  
  	  // Get primary key column name or array of column names that
  	  // make PK here
  	  $pks = $this->getPkColumns();
  	  
  	  // Check...
  	  if(is_array($pks)) {
  	    return in_array($column, $pks);
  	  } else {
  	    return $column == $pks;
  	  } // if
  	  
  	} // isPrimaryKeyColumn
  	
  	/**
  	* Check if this column is PK and if it is modified
  	*
  	* @access public
  	* @param string $column
  	* @return boolean
  	*/
  	function isModifiedPrimaryKeyColumn($column) {
  	  
  	  // Check if we have modified column...
  	  if($this->isPrimaryKeyColumn($column)) {
  	    return isset($this->modified_columns[$column]);
  	  } // if
  	  
  	  // Selected column is not PK column
  	  return false;
  	  
  	} // isModifiedPrimaryKeyColumn
  	
  	/**
  	* Return value of PK colum(s) that was initaly loaded (it will 
  	* load old values of PK columns that was modified)
  	*
  	* @access public
  	* @param void
  	* @return array or mixed
  	*/
  	function getInitialPkValue() {
  	  
  	  // Get primary key column, name...
  		$pks = $this->getPkColumns();
  		
  		// If we have multiple PKs get values and return as array
  		// else, return as scalar
  		if(is_array($pks)) {
  		
  			// Prepare result
  			$ret = array();
  			
  			// Loop primary keys and get values...
  			foreach ($pks as $column) {
  			  $ret[$column] = $this->isModifiedPrimaryKeyColumn($column) ?
  			    $this->modified_columns[$column] :
  			    $this->getColumnValue($column);
  			} // foreach
  			
  			// Return result
  			return $ret;
  			
  		} else {
  		  return $this->isModifiedPrimaryKeyColumn($pks) ?
  			  $this->modified_columns[$pks] :
  			  $this->getColumnValue($pks);
  		} // if
  	  
  	} // getInitialPkValue
  	
  	/**
  	* Return auto increment column if exists
  	*
  	* @access public
  	* @param void
  	* @return string
  	*/
  	function getAutoIncrementColumn() {
  	  return $this->manager()->getAutoIncrementColumn();
  	} // getAutoIncrementColumn
  	
  	/**
  	* Return auto increment column
  	*
  	* @access public
  	* @param string $column
  	* @return boolean
  	*/
  	function isAutoIncrementColumn($column) {
  	  return $this->getAutoIncrementColumn() == $column;
  	} // isAutoIncrementColumn
  	
  	/**
  	* Return lazy load columns if there are lazy load columns
  	*
  	* @access public
  	* @param void
  	* @return array
  	*/
  	function getLazyLoadColumns() {
  	  return $this->manager()->getLazyLoadColumns();
  	} // getLazyLoadColumns
  	
  	/**
  	* Check if specific column is lazy load
  	*
  	* @access public
  	* @param string $column
  	* @return boolean
  	*/
  	function isLazyLoadColumn($column) {
  	  $lazy_load = $this->getLazyLoadColumns();
  	  if(is_array($lazy_load)) return in_array($column, $lazy_load);
  	  return false;
  	} // isLazyLoadColumn
  	
  	/**
  	* Return value of specific column
  	*
  	* @access public
  	* @param string $column_name
  	* @param mixed $default
  	* @return mixed
  	*/
  	function getColumnValue($column_name, $default = null) {
  	  
  	  // Do we have it cached?
  	  if(isset($this->column_values[$column_name])) return $this->column_values[$column_name];
  	  
  	  // We don't have it cached. Exists?
  	  if(!$this->columnExists($column_name) && $this->isLazyLoadColumn($column_name)) {
    	  return $this->loadLazyLoadColumnValue($column_name, $default);
  	  } // if
  	  
  	  // Failed to load column or column DNX
  	  return $default;
  	  
  	} // getColumnValue
  	
  	/**
  	* Set specific field value
  	*
  	* @access public
  	* @param string $field Field name
  	* @param mixed $value New field value
  	* @return boolean
  	*/
  	function setColumnValue($column, $value) {
  		
  		// Field defined
  		if(!$this->columnExists($column)) return false;
  		
  		// Get type...
  		$coverted_value = $this->rawToPHP($value, $this->getColumnType($column));
  		$old_value = $this->getColumnValue($column);
  		
  		// Do we have modified value?
  		if($this->isNew() || ($old_value <> $coverted_value)) {
  		  
  		  // Set the value and report modification
  		  $this->column_values[$column] = $coverted_value;
  		  $this->addModifiedColumn($column);
  		  
  		  // Save primary key value. Also make sure that only the first PK value is
  			// saved as old. Not to save second value on third modification ;)
  		  if($this->isPrimaryKeyColumn($column) && !isset($this->updated_pks[$column])) {
  		    $this->updated_pks[$column] = $old_value;
  		  } // if
  		  
  		} // if
  		
  		// Set!
  		return true;
  		
  	} // setColumnValue
  	
  	
  	function getFieldsWithErrorsAfterValidation() {
  		return $this->fields_with_errors_after_validation;
  	}
  	// -------------------------------------------------------------
  	//  Top level manipulation methods
  	// -------------------------------------------------------------
  	
  	/**
  	* Save object into database (insert or update)
  	*
  	* @access public
  	* @param void
  	* @return boolean
  	* @throws DBQueryError
  	* @throws DAOValidationError
  	*/
  	function save() {
  	  $errors = $this->doValidate();
  	  
  	  if(is_array($errors)) {
  	    throw new DAOValidationError($this, $errors);
  	  } // if
  	  
  	  Hook::fire('before_object_save', $this, $ret);
  	  $saved = $this->doSave();
  	  Hook::fire('after_object_save', $this, $ret);
  	  return $saved;
  	} // save
  	
  	/**
  	* Delete specific object (and related objects if neccecery)
  	*
  	* @access public
  	* @param void
  	* @return boolean
  	* @throws DBQueryError
  	*/
  	function delete() {
  		if($this->isNew() || $this->isDeleted()) {
  		  return false;
  		} // if
  		
  		if($this->doDelete()) {
  		  $this->setDeleted(true);
  		  $this->setLoaded(false);
  		  
  		  return true;
  		} else {
  		  return false;
  		} // if
  	} // delete
  	
  	// -------------------------------------------------------------
  	//  Loader methods
  	// -------------------------------------------------------------
  	
  	/**
  	* Load data from database row
  	*
  	* @access public
  	* @param array $row Database row
  	* @return boolean
  	*/
  	function loadFromRow($row) {
  	  
  	  // Check input array...
  	  if(is_array($row)) {
  	  
  	    // Loop fiedls...
  	    foreach ($row as $k => $v) {
  	      
  	      // If key exists set value
  	      if($this->columnExists($k)) {
  	        $this->setColumnValue($k, $v);
  	      } // if
  	      
  	    } // foreach
  	    
  	    // Prepare stamps...
  	    $this->setLoaded(true);
  	    $this->notModified();
  	    
  	    // Job well done...
  	    return true;
  	    
  	  } // if
  	  
  	  // Error...
  	  return false;
  	  
  	} // loadFromRow
  	
  	/**
  	* Load lazy load column value
  	*
  	* @access private
  	* @param string $column_name
  	* @param mixed $default
  	* @return mixed
  	*/
  	private function loadLazyLoadColumnValue($column_name, $default = null) {
  	  return $default;
  	} // loadLazyLoadColumnValue
  	
  	/**
  	* Check if specific row exists in database
  	*
  	* @access public
  	* @param mixed $value Primay key value that need to be checked
  	* @return boolean
  	*/
  	private function rowExists($value) {
  	  // Don't do COUNT(*) if we have one PK column
      $escaped_pk = is_array($pk_columns = $this->getPkColumns()) ? '*' : DB::escapeField($pk_columns);
  		
  	  $sql = "SELECT count($escaped_pk) AS 'row_count' FROM " . $this->getTableName(true) . " WHERE " . $this->manager()->getConditionsById($value);
  	  $row = DB::executeOne($sql);
  	  return (boolean) array_var($row, 'row_count', false);
  	} // rowExists
  	
  	/**
  	* This function will call validate() method and handle errors
  	*
  	* @access public
  	* @param void
  	* @return array or NULL if there are no errors
  	*/
  	private function doValidate() {
  	  
  	  // Prepare errors array and call validate() method
  	  $errors = array();
  	  $fields = $this->validate($errors);
  	  $this->fields_with_errors_after_validation = $fields;
  	  
  	  Hook::fire('object_validate', $this, $errors);
  	  
  	  // If we have errors return them as array, else return NULL
  	  return count($errors) ? $errors : null;
  	  
  	} // doValidate
  	
  	/**
  	* Save data into database
  	*
  	* @access public
  	* @param void
  	* @return integer or false
  	*/
  	private function doSave() {
  		
  	  // Do we need to insert data or we need to save it...
  		if($this->isNew()) {
  		  
  		  // Lets check if we have created_on and updated_on columns and they are empty
  		  if($this->mark_timestamps && $this->columnExists('created_on') && !$this->isColumnModified('created_on')) {
  		    $this->setColumnValue('created_on', DateTimeValueLib::now());
  		  } // if
  		  if($this->mark_timestamps && $this->columnExists('updated_on') && !$this->isColumnModified('updated_on')) {
  		    $this->setColumnValue('updated_on', DateTimeValueLib::now());
  		  } // if
  		  
  		  if(function_exists('logged_user') && (logged_user() instanceof Contact)) {
    		  if($this->mark_timestamps && $this->columnExists('created_by_id') && !$this->isColumnModified('created_by_id') && (logged_user() instanceof Contact)) {
    		    $this->setColumnValue('created_by_id', logged_user()->getId());
    		  } // if
    		  if($this->mark_timestamps && $this->columnExists('updated_by_id') && !$this->isColumnModified('updated_by_id') && (logged_user() instanceof Contact)) {
    		    $this->setColumnValue('updated_by_id', logged_user()->getId());
    		  } // if
    		  
    		  // set object timezone attributes from the creator's timezone attributes
    		  if($this->columnExists('timezone_id') && !$this->isColumnModified('timezone_id')) {
    		  	$this->setColumnValue('timezone_id', logged_user()->getUserTimezoneId());
    		  }
    		  if($this->columnExists('timezone_value') && !$this->isColumnModified('timezone_value')) {
    		  	$this->setColumnValue('timezone_value', logged_user()->getUserTimezoneValue());
    		  }
    		  
  		  } // if
  		  
  		  // Get auto increment column name
  		  $autoincrement_column = $this->getAutoIncrementColumn();
  		  $autoincrement_column_modified = $this->columnExists($autoincrement_column) && $this->isColumnModified($autoincrement_column);
  			
  		  // Get SQL
  		  $sql = $this->getInsertQuery();
  		  if(!DB::execute($this->getInsertQuery())) return false;
  		  
				// If we have autoincrement field load it...
				if(!$autoincrement_column_modified && $this->columnExists($autoincrement_column)) {
				  $this->setColumnValue($autoincrement_column, DB::lastInsertId());
				} // if
				
				// Loaded...
				$this->setLoaded(true);
				
				// Done...
			  return true;
  		
  	  // Update...	
  		} else {
  		  
  		  // Set value of updated_on column...
  		  if($this->mark_timestamps && $this->columnExists('updated_on') && !$this->isColumnModified('updated_on')) {
  		    $this->setColumnValue('updated_on', DateTimeValueLib::now());
  		  } // if
  		  
  		  if(function_exists('logged_user') && (logged_user() instanceof Contact)) {
    		  if($this->mark_timestamps && $this->columnExists('updated_by_id') && !$this->isColumnModified('updated_by_id')) {
    		    $this->setColumnValue('updated_by_id', logged_user()->getId());
    		  } // if
  		  } // if
  		  
  		  // Get update SQL
  		  $sql = $this->getUpdateQuery();
  		  
  		  // Nothing to update...
  		  if(is_null($sql)) return true;
  		  
  		  // Save...
  		  if(!DB::execute($sql)) return false;
		    $this->setLoaded(true);
		    
		    // Done!
		    return true;
  			
  		} // if
  		
  	} // doSave
  	
  	/**
  	* Delete object row from database
  	*
  	* @access public
  	* @param void
  	* @return boolean
  	* @throws DBQueryError
  	*/
  	private function doDelete() {
  	  return DB::execute("DELETE FROM " . $this->getTableName(true) . " WHERE " . $this->manager()->getConditionsById( $this->getInitialPkValue() ));
  	} // doDelete
  	
  	/**
  	* Prepare insert query
  	*
  	* @access private
  	* @param void
  	* @return string
  	*/
  	private function getInsertQuery() {
  	
  		// Prepare data
  		$columns = array();
  		$values = array();
  		
  		$this_columns = $this->getColumns();
  		
  		// Loop fields
  		foreach ($this_columns as $column) {
  		  
  		  // If this field autoincrement?
  		  $auto_increment = $this->isAutoIncrementColumn($column);
  		  
  		  // If not add it...
  		  if(!$auto_increment || $this->isColumnModified($column)) {
  		    
  		    // Add field...
				  $columns[] = DB::escapeField($column);
				  $values[] = DB::escape($this->phpToRaw($this->getColumnValue($column), $this->getColumnType($column)));
				  
				  // Switch type...
				  //switch($this->getColumnType($column)) {
				  //  case DATA_TYPE_BOOLEAN:
				  //    $key_value = $this->getColumnValue($column) ? 1 : 0;
				  //    break;
				  //  default:
				  //    $key_value = $this->getColumnValue($column);
				  //} // switch

					// Add value...
					//$values[] = DB::escape($key_value);

		
  		  } // if
  			
  		} // foreach
  		
  		$on_duplicate_key_string = $this->use_on_duplicate_key_when_insert ? "ON DUPLICATE KEY UPDATE ".$this_columns[0]."=".$this_columns[0] : "";
  		$this_columns = null;
  		
  		// And put it all together
  		return sprintf("INSERT INTO %s (%s) VALUES (%s) %s", 
  		  $this->getTableName(true), 
  		  implode(', ', $columns), 
  		  implode(', ', $values),
  		  $on_duplicate_key_string
  		); // sprintf
  		
  	} // getInsertQuery
  	
  	/**
  	* Prepare update query
  	*
  	* @access private
  	* @param void
  	* @return string
  	*/
  	private function getUpdateQuery() {
  	
  		// Prepare data...
  		$columns = array();
  		
  		// Check number of modified fields
  		if(!$this->isObjectModified()) return null;
  		
  		$this_columns = $this->getColumns();
  		// Loop fields
  		foreach ($this_columns as $column) {
  			  
  			// Is this field modified?
  			if($this->isColumnModified($column)) {
  			  $columns[] = sprintf('%s = %s', DB::escapeField($column), DB::escape($this->phpToRaw($this->getColumnValue($column), $this->getColumnType($column))));
  			} // if
  		  
  		} // foreach
  		$this_columns = null;
  		
  		// Prepare update SQL
  		return sprintf("UPDATE %s SET %s WHERE %s", $this->getTableName(true), implode(', ', $columns), $this->manager()->getConditionsById( $this->getInitialPkValue() ));
  		
  	} // getUpdateQuery
  	
  	/**
  	* Return field type value
  	*
  	* @access private
  	* @param string $field Field name
  	* @return string
  	*/
  	function getColumnValueType($field) {
  	  return isset($this->__fields[$field]['type']) ? $this->__fields[$field]['type'] : DATA_TYPE_NONE;
  	} // getColumnValueType
  	
  	/**
  	* Convert raw value from database to PHP value
  	*
  	* @access public
  	* @param mixed $value
  	* @param string $type
  	* @return mixed
  	*/
  	function rawToPHP($value, $type = DATA_TYPE_STRING) {
  	  
  	  // NULL!
  	  if(is_null($value)) return null;
  	  
  	  // Switch type...
  	  switch($type) {
  	    
  	    // String
  	    case DATA_TYPE_STRING:
  	      return strval($value);
  	    
  	    // Integer
  	    case DATA_TYPE_INTEGER:
  	      return intval($value);
  	      
  	    // Float
  	    case DATA_TYPE_FLOAT:
  	      return floatval($value);
  	      
  	    // Boolean
  	    case DATA_TYPE_BOOLEAN:
  	      return (boolean) $value;
  	      
  	    // Date and time
  	    case DATA_TYPE_DATETIME:
  	    case DATA_TYPE_DATE:
  	    case DATA_TYPE_TIME:
  	      if($value instanceof DateTimeValue) {
  	        return $value;
  	      } else {
  	        if ($type == DATA_TYPE_DATETIME && ($value == EMPTY_DATETIME || $value == EMPTY_DATE)
  	        	|| $type == DATA_TYPE_DATE && $value == EMPTY_DATE
  	        	|| $type == DATA_TYPE_TIME && $value == EMPTY_TIME) {
  	        		return null;
  	        	}
  	        return DateTimeValueLib::makeFromString($value);
  	      } // if
  	      
  	  } // switch
  	  
  	} // rawToPHP
  	
  	/**
  	* Convert PHP value to value for database
  	*
  	* @access public
  	* @param mixed $value
  	* @param string $type
  	* @return string
  	*/
  	function phpToRaw($value, $type = DATA_TYPE_STRING) {
  	  
  	  // Switch type...
  	  switch($type) {
  	    
  	    // String
  	    case DATA_TYPE_STRING:
  	      return strval($value);
  	      
  	    // Integer
  	    case DATA_TYPE_INTEGER:
  	      return intval($value);
  	    
  	    // Float
  	    case DATA_TYPE_FLOAT:
  	      return floatval($value);
  	      
  	    // Boolean
  	    case DATA_TYPE_BOOLEAN:
  	      return (boolean) $value ? 1 : 0;
  	    
  	    // Date and time
  	    case DATA_TYPE_DATETIME:
  	    case DATA_TYPE_DATE:
  	    case DATA_TYPE_TIME:
  	      if(empty($value)) return EMPTY_DATETIME;
  	      if($value instanceof DateTimeValue) {
  	        return $value->toMySQL();
  	      } elseif(is_numeric($value)) {
  	        return date(DATE_MYSQL, $value);
  	      } else {
  	        return EMPTY_DATETIME;
  	      } // if
  	      
  	  } // switch
  	  
  	} // phpToRaw
  	
  	// ---------------------------------------------------------------
  	//  Flags
  	// ---------------------------------------------------------------
  	
  	/**
  	* Return value of $is_new variable
  	*
  	* @access public
  	* @param void
  	* @return boolean
  	*/
  	function isNew() {
  	  return (boolean) $this->is_new;
  	} // isNew
  	
  	/**
  	* Set new stamp value
  	*
  	* @access public
  	* @param boolean $value New value
  	* @return void
  	*/
  	function setNew($value) {
  	  $this->is_new = (boolean) $value;
  	} // setNew
  	
  	/**
  	* Returns true if this object has modified columns
  	*
  	* @access public
  	* @param void
  	* @return boolean
  	*/
  	function isModified() {
  	  return is_array($this->modified_columns) && (boolean) count($this->modified_columns);
  	} // isModified
  	
  	/**
  	* Return value of $is_deleted variable
  	*
  	* @access public
  	* @param void
  	* @return boolean
  	*/
  	function isDeleted() {
  	  return (boolean) $this->is_deleted;
  	} // isDeleted
  	
  	/**
  	* Set deleted stamp value
  	*
  	* @access public
  	* @param boolean $value New value
  	* @return void
  	*/
  	function setDeleted($value) {
  	  $this->is_deleted = (boolean) $value;
  	} // setDeleted
  	
  	/**
  	* Return value of $is_loaded variable
  	*
  	* @access public
  	* @param void
  	* @return boolean
  	*/
  	function isLoaded() {
  	  return (boolean) $this->is_loaded;
  	} // isLoaded
  	
  	/**
  	* Set loaded stamp value
  	*
  	* @access public
  	* @param boolean $value New value
  	* @return void
  	*/
  	function setLoaded($value) {
  	  $this->is_loaded = (boolean) $value;
  	  $this->setNew(!$this->is_loaded);
  	  //$this->is_new = !$this->is_loaded;
  	  //if($this->is_loaded) $this->setNew(false);
  	} // setLoaded
  	
  	/**
  	* Check if this object is modified (one or more column value are modified)
  	*
  	* @access public
  	* @param void
  	* @return boolean
  	*/
  	function isObjectModified() {
  	  return (boolean) count($this->modified_columns);
  	} // isObjectModified
  	
  	/**
  	* Check if specific column is modified
  	*
  	* @access public
  	* @param string $column_name Column name
  	* @return boolean
  	*/
  	function isColumnModified($column_name) {
  	  return in_array($column_name, $this->modified_columns);
  	} // isColumnModified
  	
  	/**
  	* Report modified column
  	*
  	* @access public
  	* @param string $column_name
  	* @return null
  	*/
  	protected function addModifiedColumn($column_name) {
  	  if(!in_array($column_name, $this->modified_columns)) $this->modified_columns[] = $column_name;
  	} // addModifiedColumn
  	
  	/**
  	* Returns true if PK column value is updated
  	*
  	* @access public
  	* @param void
  	* @return boolean
  	*/
  	function isPkUpdated() {
  	  return count($this->updated_pks);
  	} // isPkUpdated
  	
  	/**
  	* Reset modification idicators. Usefull when you use setXXX functions
  	* but you don't want to modify anything (just loading data from database
  	* in fresh object using setColumnValue function)
  	*
  	* @access public
  	* @param void
  	* @return void
  	*/
  	function notModified() {
  	  $this->modified_columns = array();
  	  $this->updated_pks = array();
  	} // notModified
  	
  	/**
  	* Returns an array of protected attributes
  	*
  	* @param void
  	* @return array
  	*/
  	function getProtectedAttributes() {
  	  return $this->attr_protected;
  	} // getProtectedAttributes
  	
  	/**
  	* Add one or multiple protected attributes
  	*
  	* @param void
  	* @return null
  	*/
  	function addProtectedAttribute() {
  	  $args = func_get_args();
  	  if(is_array($args)) {
  	    foreach($args as $arg) {
  	      if(!in_array($arg, $this->attr_protected)) {
  	        if($this->columnExists($arg)) $this->attr_protected[] = $arg;
  	      } // if
  	    } // foreach
  	  } // if
  	} // addProtectedAttribute
  	
  	/**
  	* Return an array of acceptable attributes
  	*
  	* @param void
  	* @return array
  	*/
  	function getAcceptableAttributes() {
  	  return $this->attr_acceptable;
  	} // getAcceptAttributes
  	
  	/**
  	* Add one or many acceptable attributes
  	*
  	* @param void
  	* @return null
  	*/
  	function addAcceptableAttribute() {
  	  $args = func_get_args();
  	  if(is_array($args)) {
  	    foreach($args as $arg) {
  	      if(!in_array($arg, $this->attr_acceptable)) {
  	        if($this->columnExists($arg)) $this->attr_acceptable[] = $arg;
  	      } // if
  	    } // foreach
  	  } // if
  	} // addAcceptableAttribute
  	
  	/**
  	 * Sets if the 'create' and 'update' timestamps will be set on the next save() method
  	 *
  	 * @param boolean $value
  	 */
  	function setMarkTimestamps($value = true){
  		$this->mark_timestamps = $value;
  	} //setMarkTimestamp
  	
  	function getMarkTimestamps(){
  		return (boolean) $this->mark_timestamps;
  	}
  	
  	// ---------------------------------------------------------------
  	//  Validators
  	// ---------------------------------------------------------------
  	
  	/**
  	* Validates presence of specific field. Presence of value is determined 
  	* by the empty function
  	*
  	* @access public
  	* @param string $field Field name
  	* @param boolean $trim_string If value is string trim it before checks to avoid
  	*   returning true for strings like ' '.
  	* @return boolean
  	*/
  	function validatePresenceOf($field, $trim_string = true) {
  	  $value = $this->getColumnValue($field);
  	  if(is_string($value) && $trim_string) $value = trim($value);
  	  return !empty($value);
  	} // validatePresenceOf
  	
  	/**
  	* This validator will return true if $value is unique (there is no row with such value in that field)
  	*
  	* @access public
  	* @param string $field Filed name
  	* @param mixed $value Value that need to be checked
  	* @return boolean
  	*/
  	function validateUniquenessOf() {
  	  // Don't do COUNT(*) if we have one PK column
      $escaped_pk = is_array($pk_columns = $this->getPkColumns()) ? '*' : DB::escapeField($pk_columns);
  	  
  	  // Get columns
  	  $columns = func_get_args();
  	  if(!is_array($columns) || count($columns) < 1) return true;
  	  
  	  // Check if we have existsing columns
  	  foreach($columns as $column) {
  	    if(!$this->columnExists($column)) return false;
  	  } // foreach
  	  
  	  // Get where parets
  	  $where_parts = array();
  	  foreach($columns as $column) {
  	    $where_parts[] = DB::escapeField($column) . ' = ' . DB::escape($this->getColumnValue($column));
  	  } // if
  	  
  	  // If we have new object we need to test if there is any other object
  	  // with this value. Else we need to check if there is any other EXCEPT
  	  // this one with that value
  	  if($this->isNew()) {
  	    $sql = sprintf("SELECT COUNT($escaped_pk) AS 'row_count' FROM %s WHERE %s", $this->getTableName(true), implode(' AND ', $where_parts));
  	  } else {
  	    
  	    // Prepare PKs part...
  	    $pks = $this->getPkColumns();
  	    $pk_values = array();
  	    if(is_array($pks)) {
  	      foreach($pks as $pk) {
  	        $pk_values[] = sprintf('%s <> %s', DB::escapeField($pk), DB::escape($this->getColumnValue($pk)));
  	      } // foreach
  	    } else {
  	      $pk_values[] = sprintf('%s <> %s', DB::escapeField($pks), DB::escape($this->getColumnValue($pks)));
  	    } // if

  	    // Prepare SQL
  	    $sql = sprintf("SELECT COUNT($escaped_pk) AS 'row_count' FROM %s WHERE (%s) AND (%s)", $this->getTableName(true), implode(' AND ', $where_parts), implode(' AND ', $pk_values));
  	    
  	  } // if
  	  
  	  $row = DB::executeOne($sql);
  	  return array_var($row, 'row_count', 0) < 1;
  	} // validateUniquenessOf
  	
  	/**
  	* Validate max value of specific field. If that field is string time 
  	* max lenght will be validated
  	*
  	* @access public
  	* @param string $column
  	* @param integer $max Maximal value
  	* @return null
  	*/
  	function validateMaxValueOf($column, $max) {
  	  
  	  // Field does not exists
  	  if(!$this->columnExists($column)) return false;
  	  
  	  // Get value...
  	  $value = $this->getColumnValue($column);
  	  
  	  // Integer and float...
  	  if(is_int($value) || is_float($value)) {
  	    return $value <= $max;
  	    
  	  // String...
  	  } elseif(is_string($value)) {
  	    return strlen($value) <= $max;
  	    
  	  // Any other value...
  	  } else {
  	    return $value <= $max;
  	  } // if
  	  
  	} // validateMaxValueOf
  	
  	/**
  	* Valicate minimal value of specific field. If string minimal lenght is checked
  	*
  	* @access public
  	* @param string $column
  	* @param integer $min Minimal value
  	* @return boolean
  	*/
  	function validateMinValueOf($column, $min) {
  	  
  	  // Field does not exists
  	  if(!$this->columnExists($column)) return false;
  	  
  	  // Get value...
  	  $value = $this->getColumnValue($column);
  	  
  	  // Integer and float...
  	  if(is_int($value) || is_float($value)) {
  	    return $value >= $min;
  	    
  	  // String...
  	  } elseif(is_string($value)) {
  	    return strlen($value) >= $min;
  	    
  	  // Any other value...
  	  } else {
  	    return $value >= $min;
  	  } // if
  	  
  	} // validateMinValueOf
  	
  	/**
  	* This function will validate format of specified columns value
  	*
  	* @access public
  	* @param string $column Column name
  	* @param string $pattern
  	* @return boolean
  	*/
  	function validateFormatOf($column, $pattern) {
  	  if(!$this->columnExists($column)) return false;
  	  $value = $this->getColumnValue($column);
  	  return preg_match($pattern, $value);
  	} // validateFormatOf
  	
  } // end class DataObject

?>