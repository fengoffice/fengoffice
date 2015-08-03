<?php
abstract class BaseWidgets extends DataManager {
	
	/**
	 * Column name => Column type map
	 *
	 * @var array
	 * @static
	 */
	private static $columns = array (
		'name' => DATA_TYPE_STRING,
		'title' => DATA_TYPE_STRING,
		'plugin_id' => DATA_TYPE_INTEGER,
		'default_section' => DATA_TYPE_STRING,
		'default_order' => DATA_TYPE_INTEGER,
		'default_options' => DATA_TYPE_STRING,
		'icon_cls' => DATA_TYPE_STRING,
	);
	
	function __construct() {
		Hook::fire ( 'object_definition', 'Widget', self::$columns );
		parent::__construct ( 'Widget', 'widgets', true );
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
		return array_keys ( self::$columns );
	} // getColumns
	

	/**
	 * Return column type
	 *
	 * @access public
	 * @param string $column_name
	 * @return string
	 */
	function getColumnType($column_name) {
		if (isset ( self::$columns [$column_name] )) {
			return self::$columns [$column_name];
		} else {
			return DATA_TYPE_STRING;
		}
	}
	
	
	function getPkColumns() {
		return 'name';
	} 
	
	function getAutoIncrementColumn() {
		return null;
	} 

	function getSystemColumns() {
		return parent::getSystemColumns ();
	}
	
	function getExternalColumns() {
		return parent::getExternalColumns ();
	}

	function find($arguments = null) {
		if (isset ( $this ) && instance_of ( $this, 'Widgets' )) {
			return parent::find ( $arguments );
		} else {
			return Widgets::instance ()->find ( $arguments );
		}
	}
	
	function findAll($arguments = null) {
		if (isset ( $this ) && instance_of ( $this, 'Widgets' )) {
			return parent::findAll ( $arguments );
		} else {
			return Widgets::instance ()->findAll ( $arguments );
		}
	}
	
	function findOne($arguments = null) {
		if (isset ( $this ) && instance_of ( $this, 'Widgets' )) {
			return parent::findOne ( $arguments );
		} else {
			return Widgets::instance ()->findOne ( $arguments );
		}
	}
	
	function findById($id, $force_reload = false) {
		if (isset ( $this ) && instance_of ( $this, 'Widgets' )) {
			return parent::findById ( $id, $force_reload );
		} else {
			return Widgets::instance ()->findById ( $id, $force_reload );
		}
	}
	
	function count($condition = null) {
		if (isset ( $this ) && instance_of ( $this, 'Widgets' )) {
			return parent::count ( $condition );
		} else {
			return Widgets::instance ()->count ( $condition );
		}
	}
	
	function delete($condition = null) {
		if (isset ( $this ) && instance_of ( $this, 'Widgets' )) {
			return parent::delete ( $condition );
		} else {
			return Widgets::instance ()->delete ( $condition );
		}
	}
	function paginate($arguments = null, $items_per_page = 10, $current_page = 1) {
		if (isset ( $this ) && instance_of ( $this, 'Widgets' )) {
			return parent::paginate ( $arguments, $items_per_page, $current_page );
		} else {
			return Widgets::instance ()->paginate ( $arguments, $items_per_page, $current_page );
		}
	}
	
	function instance() {
		static $instance;
		if (! instance_of ( $instance, 'Widgets' )) {
			$instance = new Widgets ();
		}
		return $instance;
	}

}