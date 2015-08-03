<?php


/**
 * ProjectWebpages class
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */
abstract class BaseProjectWebpages extends ContentDataObjects {

	/**
	 * Column name => Column type map
	 *
	 * @var array
	 * @static
	 */
	static private $columns = array(
    	'object_id' => DATA_TYPE_INTEGER,  
    	'url' => DATA_TYPE_STRING, 
    	'description' => DATA_TYPE_STRING,
    );

	/**
	 * Construct
	 *
	 * @return BaseProjectWebpages
	 */
	function __construct() {
		Hook::fire('object_definition', 'ProjectWebpage', self::$columns);
		parent::__construct('ProjectWebpage', 'project_webpages', true);
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
		return null;
	} // getAutoIncrementColumn

	/**
	 * Return system columns
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getSystemColumns() {
		return array_merge(parent::getSystemColumns());
	} // getSystemColumns
	
	/**
    * Return external columns
    *
    * @access public
    * @param void
    * @return array
    */
    function getExternalColumns() {
      return array_merge(parent::getExternalColumns(), array());
    } // getExternalColumns
	
	/**
    * Return report object title columns
    *
    * @access public
    * @param void
    * @return array
    */
    function getReportObjectTitleColumns() {
      return array('title');
    } // getReportObjectTitleColumns
    
    /**
    * Return report object title
    *
    * @access public
    * @param void
    * @return string
    */
    function getReportObjectTitle($values) {
    	$title = isset($values['title']) ? $values['title'] : ''; 
    	return $title;
    } // getReportObjectTitle

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
	 * @return one or ProjectWebpages objects
	 * @throws DBQueryError
	 */
	function find($arguments = null) {
		if(isset($this) && instance_of($this, 'ProjectWebpages')) {
			return parent::find($arguments);
		} else {
			return ProjectWebpages::instance()->find($arguments);
		} // if
	} // find

	/**
	 * Find all records
	 *
	 * @access public
	 * @param array $arguments
	 * @return one or ProjectWebpages objects
	 */
	function findAll($arguments = null) {
		if(isset($this) && instance_of($this, 'ProjectWebpages')) {
			return parent::findAll($arguments);
		} else {
			return ProjectWebpages::instance()->findAll($arguments);
		} // if
	} // findAll

	/**
	 * Find one specific record
	 *
	 * @access public
	 * @param array $arguments
	 * @return ProjectWebpage
	 */
	function findOne($arguments = null) {
		if(isset($this) && instance_of($this, 'ProjectWebpages')) {
			return parent::findOne($arguments);
		} else {
			return ProjectWebpages::instance()->findOne($arguments);
		} // if
	} // findOne

	/**
	 * Return object by its PK value
	 *
	 * @access public
	 * @param mixed $id
	 * @param boolean $force_reload If true cache will be skipped and data will be loaded from database
	 * @return ProjectWebpage
	 */
	function findById($id, $force_reload = false) {
		if(isset($this) && instance_of($this, 'ProjectWebpages')) {
			return parent::findById($id, $force_reload);
		} else {
			return ProjectWebpages::instance()->findById($id, $force_reload);
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
		if(isset($this) && instance_of($this, 'ProjectWebpages')) {
			return parent::count($condition);
		} else {
			return ProjectWebpages::instance()->count($condition);
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
		if(isset($this) && instance_of($this, 'ProjectWebpages')) {
			return parent::delete($condition);
		} else {
			return ProjectWebpages::instance()->delete($condition);
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
		if(isset($this) && instance_of($this, 'ProjectWebpages')) {
			return parent::paginate($arguments, $items_per_page, $current_page);
		} else {
			return ProjectWebpages::instance()->paginate($arguments, $items_per_page, $current_page);
		} // if
	} // paginate

	/**
	 * Return manager instance
	 *
	 * @return ProjectWebpages
	 */
	function instance() {
		static $instance;
		if(!instance_of($instance, 'ProjectWebpages')) {
			$instance = new ProjectWebpages();
		} // if
		return $instance;
	} // instance

} // ProjectWebpages

?>