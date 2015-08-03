<?php


/**
 * BaseWorkspaces class
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
abstract class BaseWorkspaces extends ContentDataObjects {

	/**
	 * Column name => Column type map
	 *
	 * @var array
	 * @static
	 */
	static private $columns = array(
    	'object_id' => DATA_TYPE_INTEGER,
    	'description' => DATA_TYPE_STRING,
		'show_description_in_overview' => DATA_TYPE_BOOLEAN,
		'color' => DATA_TYPE_INTEGER
	);

	/**
	 * Construct
	 *
	 * @return BaseWorkspaces
	 */
	function __construct() {
		Hook::fire('object_definition', 'Workspace', self::$columns);
		parent::__construct('Workspace', 'workspaces', true);
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
		return parent::getCOColumnType($column_name, self::$columns);
	}
	/**
	 * Return array of PK columns. If only one column is PK returns its name as string
	 *
	 * @access public
	 * @param void
	 * @return array or string
	 */
	function getPkColumns() {
		return 'object_id';
	} // getPkColumns

	/**
	 * Return name of first auto_incremenent column if it exists
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getAutoIncrementColumn() {
		return 'object_id';
	} // getAutoIncrementColumn

	/**
	 * Return system columns
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getSystemColumns() {
		return array_merge(array('show_description_in_overview', 'color'), parent::getSystemColumns());
	} // getSystemColumns
	
	/**
    * Return external columns
    *
    * @access public
    * @param void
    * @return array
    */
    function getExternalColumns() {
      return parent::getExternalColumns();
    } // getExternalColumns
	
	
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
	 * @return one or Workspaces objects
	 * @throws DBQueryError
	 */
	function find($arguments = null) {
		if(isset($this) && instance_of($this, 'Workspaces')) {
			return parent::find($arguments);
		} else {
			return Workspaces::instance()->find($arguments);
		} // if
	} // find

	/**
	 * Find all records
	 *
	 * @access public
	 * @param array $arguments
	 * @return one or Workspaces objects
	 */
	function findAll($arguments = null) {
		if(isset($this) && instance_of($this, 'Workspaces')) {
			return parent::findAll($arguments);
		} else {
			return Workspaces::instance()->findAll($arguments);
		} // if
	} // findAll

	/**
	 * Find one specific record
	 *
	 * @access public
	 * @param array $arguments
	 * @return Workspace
	 */
	function findOne($arguments = null) {
		if(isset($this) && instance_of($this, 'Workspaces')) {
			return parent::findOne($arguments);
		} else {
			return Workspaces::instance()->findOne($arguments);
		} // if
	} // findOne

	/**
	 * Return object by its PK value
	 *
	 * @access public
	 * @param mixed $id
	 * @param boolean $force_reload If true cache will be skipped and data will be loaded from database
	 * @return Workspace
	 */
	function findById($id, $force_reload = false) {
		if(isset($this) && instance_of($this, 'Workspaces')) {
			return parent::findById($id, $force_reload);
		} else {
			return Workspaces::instance()->findById($id, $force_reload);
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
		if(isset($this) && instance_of($this, 'Workspaces')) {
			return parent::count($condition);
		} else {
			return Workspaces::instance()->count($condition);
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
		if(isset($this) && instance_of($this, 'Workspaces')) {
			return parent::delete($condition);
		} else {
			return Workspaces::instance()->delete($condition);
		} // if
	} // delete


	/**
	 * Return manager instance
	 *
	 * @return Workspaces
	 */
	function instance() {
		static $instance;
		if(!instance_of($instance, 'Workspaces')) {
			$instance = new Workspaces();
		} 
		return $instance;
	}

}