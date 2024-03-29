<?php


/**
 * COTemplates class
 *
 * @author FengOffice
 */
abstract class BaseCOTemplates extends ContentDataObjects {

	/**
	 * Column name => Column type map
	 *
	 * @var array
	 * @static
	 */
	static private $columns = array(
    	'object_id' => DATA_TYPE_INTEGER,
    	'description' => DATA_TYPE_STRING
	);

	/**
	 * Construct
	 *
	 * @return BaseCOTemplates
	 */
	function __construct() {
		Hook::fire('object_definition', 'COTemplate', self::$columns);
		parent::__construct('COTemplate', 'templates', true);
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
		return 'null';
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
	 * @return one or COTemplates objects
	 * @throws DBQueryError
	 */
	function find($arguments = null) {
		if(isset($this) && instance_of($this, 'COTemplates')) {
			return parent::find($arguments);
		} else {
			return COTemplates::instance()->find($arguments);
		} // if
	} // find

	/**
	 * Find all records
	 *
	 * @access public
	 * @param array $arguments
	 * @return one or COTemplates objects
	 */
	function findAll($arguments = null) {
		if(isset($this) && instance_of($this, 'COTemplates')) {
			return parent::findAll($arguments);
		} else {
			return COTemplates::instance()->findAll($arguments);
		} // if
	} // findAll

	/**
	 * Find one specific record
	 *
	 * @access public
	 * @param array $arguments
	 * @return COTemplate
	 */
	function findOne($arguments = null) {
		if(isset($this) && instance_of($this, 'COTemplates')) {
			return parent::findOne($arguments);
		} else {
			return COTemplates::instance()->findOne($arguments);
		} // if
	} // findOne

	/**
	 * Return object by its PK value
	 *
	 * @access public
	 * @param mixed $id
	 * @param boolean $force_reload If true cache will be skipped and data will be loaded from database
	 * @return COTemplate
	 */
	function findById($id, $force_reload = false) {
		if(isset($this) && instance_of($this, 'COTemplates')) {
			return parent::findById($id, $force_reload);
		} else {
			return COTemplates::instance()->findById($id, $force_reload);
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
		if(isset($this) && instance_of($this, 'COTemplates')) {
			return parent::count($condition);
		} else {
			return COTemplates::instance()->count($condition);
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
		if(isset($this) && instance_of($this, 'COTemplates')) {
			return parent::delete($condition);
		} else {
			return COTemplates::instance()->delete($condition);
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
	function paginate($arguments = null, $items_per_page = 10, $current_page = 1, $count = null) {
		if(isset($this) && instance_of($this, 'COTemplates')) {
			return parent::paginate($arguments, $items_per_page, $current_page);
		} else {
			return COTemplates::instance()->paginate($arguments, $items_per_page, $current_page);
		} // if
	} // paginate

	/**
	 * Return manager instance
	 *
	 * @return COTemplates
	 */
	static function instance() {
		static $instance;
		if(!instance_of($instance, 'COTemplates')) {
			$instance = new COTemplates();
		} // if
		return $instance;
	} // instance

} // COTemplates

?>