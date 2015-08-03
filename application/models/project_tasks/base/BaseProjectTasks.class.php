<?php


/**
 * ProjectTasks class
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
abstract class BaseProjectTasks extends ContentDataObjects {

	/**
	 * Column name => Column type map
	 *
	 * @static
	 */
	static private $columns = array(
    	'object_id' => DATA_TYPE_INTEGER,
    	'parent_id' => DATA_TYPE_INTEGER, 
		'parents_path' => DATA_TYPE_STRING,
		'depth' => DATA_TYPE_INTEGER,
        'text' => DATA_TYPE_STRING,
        'assigned_to_contact_id' => DATA_TYPE_INTEGER,
        'completed_on' => DATA_TYPE_DATETIME, 
        'due_date' => DATA_TYPE_DATETIME,
        'start_date' => DATA_TYPE_DATETIME,
        'completed_by_id' => DATA_TYPE_INTEGER, 
        'order' => DATA_TYPE_INTEGER,
        'milestone_id' => DATA_TYPE_INTEGER,
        'started_on' => DATA_TYPE_DATETIME,
        'priority' => DATA_TYPE_INTEGER, 
        'state' => DATA_TYPE_INTEGER,
        'started_by_id' => DATA_TYPE_INTEGER,
    	'assigned_on' => DATA_TYPE_DATETIME,
        'assigned_by_id' => DATA_TYPE_INTEGER,
        'time_estimate' => DATA_TYPE_INTEGER,
        'is_template' => DATA_TYPE_BOOLEAN,
        'from_template_id' => DATA_TYPE_INTEGER,
		'from_template_object_id' => DATA_TYPE_INTEGER,
        'repeat_forever'=>DATA_TYPE_BOOLEAN,
    	'repeat_end' => DATA_TYPE_DATETIME,
    	'repeat_num' => DATA_TYPE_INTEGER,
    	'repeat_d' => DATA_TYPE_INTEGER,
    	'repeat_m' => DATA_TYPE_INTEGER,
    	'repeat_y' => DATA_TYPE_INTEGER,
        'repeat_by' => DATA_TYPE_STRING,
        'object_subtype' => DATA_TYPE_INTEGER,
        'percent_completed' => DATA_TYPE_INTEGER,
        'use_due_time' => DATA_TYPE_BOOLEAN,
        'use_start_time' => DATA_TYPE_BOOLEAN,
        'original_task_id' => DATA_TYPE_INTEGER,
        'instantiation_id' => DATA_TYPE_INTEGER,
        'type_content' => DATA_TYPE_STRING
	);

	/**
	 * Construct
	 *
	 * @return BaseProjectTasks
	 */
	function __construct() {
		Hook::fire('object_definition', 'ProjectTask', self::$columns);
		parent::__construct('ProjectTask', 'project_tasks', true);
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
      		'object_subtype', 'parent_id', 'parents_path',	'depth', 'assigned_to_contact_id', 'completed_by_id', 'milestone_id', 'state', 'started_by_id', 
                'from_template_id', 'from_template_object_id', 'use_due_time', 'use_start_time', 'original_task_id', 'multi_assignment', 'instantiation_id')
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
      return array_merge(parent::getExternalColumns(), array('object_subtype', 'assigned_to_contact_id', 'completed_by_id', 'assigned_by_id', 'milestone_id'));
    } // getExternalColumns
	
	/**
    * Return report object title columns
    *
    * @access public
    * @param void
    * @return array
    */
    function getReportObjectTitleColumns() {
      return array('name');
    } // getReportObjectTitleColumns
    
    /**
    * Return report object title
    *
    * @access public
    * @param void
    * @return string
    */
    function getReportObjectTitle($values) {
    	$title = isset($values['name']) ? $values['name'] : ''; 
    	return $title;
    } // getReportObjectTitle
    
    /**
    * Return template object properties
    *
    * @access public
    * @param void
    * @return string
    */
    function getTemplateObjectProperties() {
    	return array(
    		array('id' => 'name', 'type' => self::getColumnType('name')),
    		array('id' => 'text', 'type' => self::getColumnType('text')),
    		array('id' => 'start_date', 'type' => self::getColumnType('start_date')),
    		array('id' => 'due_date', 'type' => self::getColumnType('due_date')),
    		array('id' => 'assigned_to_contact_id', 'type' => 'USER')
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
	 * @return one or ProjectTasks objects
	 * @throws DBQueryError
	 */
	function find($arguments = null) {
		if(isset($this) && instance_of($this, 'ProjectTasks')) {
			return parent::find($arguments);
		} else {
			return ProjectTasks::instance()->find($arguments);
		} // if
	} // find

	/**
	 * Find all records
	 *
	 * @access public
	 * @param array $arguments
	 * @return one or ProjectTasks objects
	 */
	function findAll($arguments = null) {
		if(isset($this) && instance_of($this, 'ProjectTasks')) {
			return parent::findAll($arguments);
		} else {
			return ProjectTasks::instance()->findAll($arguments);
		} // if
	} // findAll

	/**
	 * Find one specific record
	 *
	 * @access public
	 * @param array $arguments
	 * @return ProjectTask
	 */
	function findOne($arguments = null) {
		if(isset($this) && instance_of($this, 'ProjectTasks')) {
			return parent::findOne($arguments);
		} else {
			return ProjectTasks::instance()->findOne($arguments);
		} // if
	} // findOne

	/**
	 * Return object by its PK value
	 *
	 * @access public
	 * @param mixed $id
	 * @param boolean $force_reload If true cache will be skipped and data will be loaded from database
	 * @return ProjectTask
	 */
	function findById($id, $force_reload = false) {
		if(isset($this) && instance_of($this, 'ProjectTasks')) {
			return parent::findById($id, $force_reload);
		} else {
			return ProjectTasks::instance()->findById($id, $force_reload);
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
		if(isset($this) && instance_of($this, 'ProjectTasks')) {
			return parent::count($condition);
		} else {
			return ProjectTasks::instance()->count($condition);
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
		if(isset($this) && instance_of($this, 'ProjectTasks')) {
			return parent::delete($condition);
		} else {
			return ProjectTasks::instance()->delete($condition);
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
		if(isset($this) && instance_of($this, 'ProjectTasks')) {
			return parent::paginate($arguments, $items_per_page, $current_page);
		} else {
			return ProjectTasks::instance()->paginate($arguments, $items_per_page, $current_page);
		} // if
	} // paginate

	/**
	 * Return manager instance
	 *
	 * @return ProjectTasks
	 */
	function instance() {
		static $instance;
		if(!instance_of($instance, 'ProjectTasks')) {
			$instance = new ProjectTasks();
		} // if
		return $instance;
	} // instance

} // ProjectTasks

?>