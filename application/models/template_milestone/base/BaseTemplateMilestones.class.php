<?php


/**
 * TemplateMilestones class
 *
 * 
 */
abstract class BaseTemplateMilestones extends ContentDataObjects {

	/**
	 * Column name => Column type map
	 *
	 * @var array
	 * @static
	 */
	static private $columns = array(
		'template_id' => DATA_TYPE_INTEGER,
		'session_id' => DATA_TYPE_INTEGER,
    	'object_id' => DATA_TYPE_INTEGER,
    	'description' => DATA_TYPE_STRING,
    	'due_date' => DATA_TYPE_DATETIME,
    	'is_urgent' => DATA_TYPE_BOOLEAN,
    	'completed_on' => DATA_TYPE_DATETIME,
    	'completed_by_id' => DATA_TYPE_INTEGER,
    	'is_template' => DATA_TYPE_BOOLEAN,
		'from_template_id' => DATA_TYPE_INTEGER,
		'from_template_object_id' => DATA_TYPE_INTEGER
	);

	/**
	 * Construct
	 *
	 * @return BaseTemplateMilestones
	 */
	function __construct() {
		Hook::fire('object_definition', 'TemplateMilestone', self::$columns);
		parent::__construct('TemplateMilestone', 'template_milestones', true);
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
		return array_merge(parent::getSystemColumns(), array(
      		'assigned_to_contact_id', 'completed_by_id', 'from_template_id', 'from_template_object_id')
		);
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
	
	/**
    * Return report object title columns
    *
    * @access public
    * @param void
    * @return array
    */
    /*function getReportObjectTitleColumns() {
      return array('name');
    } // getReportObjectTitleColumns*/
    
    /**
    * Return report object title
    *
    * @access public
    * @param void
    * @return string
    */
    /*function getReportObjectTitle($values) {
    	$name = isset($values['name']) ? $values['name'] : ''; 
    	return $name;
    } // getReportObjectTitle*/
    
    /**
    * Return template object properties
    *
    * @access public
    * @param void
    * @return string
    */
    function getTemplateObjectProperties() {
    	return array(
    		array('id' => 'name', 'type' => self::instance()->getColumnType('name')),
    		array('id' => 'description', 'type' => self::instance()->getColumnType('description')),
    		array('id' => 'due_date', 'type' => self::instance()->getColumnType('due_date')),
    	);
    } // getTemplateObjectProperties

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
	 * @return one or ProjectMilestones objects
	 * @throws DBQueryError
	 */
	function find($arguments = null) {
		if(isset($this) && instance_of($this, 'TemplateMilestones')) {
			return parent::find($arguments);
		} else {
			return TemplateMilestones::instance()->find($arguments);
			//$instance =& TemplateMilestones::instance();
			//return $instance->find($arguments);
		} // if
	} // find

	/**
	 * Find all records
	 *
	 * @access public
	 * @param array $arguments
	 * @return one or TemplateMilestones objects
	 */
	function findAll($arguments = null) {
		if(isset($this) && instance_of($this, 'TemplateMilestones')) {
			return parent::findAll($arguments);
		} else {
			return TemplateMilestones::instance()->findAll($arguments);
			//$instance =& TemplateMilestones::instance();
			//return $instance->findAll($arguments);
		} // if
	} // findAll

	/**
	 * Find one specific record
	 *
	 * @access public
	 * @param array $arguments
	 * @return ProjectMilestone
	 */
	function findOne($arguments = null) {
		if(isset($this) && instance_of($this, 'TemplateMilestones')) {
			return parent::findOne($arguments);
		} else {
			return TemplateMilestones::instance()->findOne($arguments);
			//$instance =& TemplateMilestones::instance();
			//return $instance->findOne($arguments);
		} // if
	} // findOne

	/**
	 * Return object by its PK value
	 *
	 * @access public
	 * @param mixed $id
	 * @param boolean $force_reload If true cache will be skipped and data will be loaded from database
	 * @return ProjectMilestone
	 */
	function findById($id, $force_reload = false) {
		if(isset($this) && instance_of($this, 'TemplateMilestones')) {
			return parent::findById($id, $force_reload);
		} else {
			return TemplateMilestones::instance()->findById($id, $force_reload);
			//$instance =& TemplateMilestones::instance();
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
		if(isset($this) && instance_of($this, 'TemplateMilestones')) {
			return parent::count($condition);
		} else {
			return TemplateMilestones::instance()->count($condition);
			//$instance =& TemplateMilestones::instance();
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
		if(isset($this) && instance_of($this, 'TemplateMilestones')) {
			return parent::delete($condition);
		} else {
			return TemplateMilestones::instance()->delete($condition);
			//$instance =& TemplateMilestones::instance();
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
	function paginate($arguments = null, $items_per_page = 10, $current_page = 1, $count = null) {
		if(isset($this) && instance_of($this, 'TemplateMilestones')) {
			return parent::paginate($arguments, $items_per_page, $current_page);
		} else {
			return TemplateMilestones::instance()->paginate($arguments, $items_per_page, $current_page);
			//$instance =& TemplateMilestones::instance();
			//return $instance->paginate($arguments, $items_per_page, $current_page);
		} // if
	} // paginate

	/**
	 * Return manager instance
	 *
	 * @return TemplateMilestones
	 */
	static function instance() {
		static $instance;
		if(!instance_of($instance, 'TemplateMilestones')) {
			$instance = new TemplateMilestones();
		} // if
		return $instance;
	} // instance

} // TemplateMilestones

?>