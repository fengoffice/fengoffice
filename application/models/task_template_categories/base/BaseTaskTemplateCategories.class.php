<?php

abstract class BaseTaskTemplateCategories extends DataManager {

	static private $columns = array(
		'id' => DATA_TYPE_INTEGER,
		'name' => DATA_TYPE_STRING,
		'sort_order' => DATA_TYPE_INTEGER,
	);

	function __construct() {
		parent::__construct('TaskTemplateCategory', 'task_template_categories', true);
	}

	function getColumns() {
		return array_keys(self::$columns);
	}

	function getColumnType($column_name) {
		if (isset(self::$columns[$column_name])) {
			return self::$columns[$column_name];
		}
		return DATA_TYPE_STRING;
	}

	function getPkColumns() {
		return 'id';
	}

	function getAutoIncrementColumn() {
		return 'id';
	}

	function find($arguments = null) {
		if (isset($this) && instance_of($this, 'TaskTemplateCategories')) {
			return parent::find($arguments);
		}
		return TaskTemplateCategories::instance()->find($arguments);
	}

	function findAll($arguments = null) {
		if (isset($this) && instance_of($this, 'TaskTemplateCategories')) {
			return parent::findAll($arguments);
		}
		return TaskTemplateCategories::instance()->findAll($arguments);
	}

	function findOne($arguments = null) {
		if (isset($this) && instance_of($this, 'TaskTemplateCategories')) {
			return parent::findOne($arguments);
		}
		return TaskTemplateCategories::instance()->findOne($arguments);
	}

	function findById($id, $force_reload = false) {
		if (isset($this) && instance_of($this, 'TaskTemplateCategories')) {
			return parent::findById($id, $force_reload);
		}
		return TaskTemplateCategories::instance()->findById($id, $force_reload);
	}

	function count($condition = null) {
		if (isset($this) && instance_of($this, 'TaskTemplateCategories')) {
			return parent::count($condition);
		}
		return TaskTemplateCategories::instance()->count($condition);
	}

	function delete($condition = null) {
		if (isset($this) && instance_of($this, 'TaskTemplateCategories')) {
			return parent::delete($condition);
		}
		return TaskTemplateCategories::instance()->delete($condition);
	}

	function paginate($arguments = null, $items_per_page = 10, $current_page = 1, $count = null) {
		if (isset($this) && instance_of($this, 'TaskTemplateCategories')) {
			return parent::paginate($arguments, $items_per_page, $current_page);
		}
		return TaskTemplateCategories::instance()->paginate($arguments, $items_per_page, $current_page);
	}

	static function instance() {
		static $instance;
		if (!instance_of($instance, 'TaskTemplateCategories')) {
			$instance = new TaskTemplateCategories();
		}
		return $instance;
	}
}
