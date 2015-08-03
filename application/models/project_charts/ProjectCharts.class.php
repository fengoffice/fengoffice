<?php
/* ProjectCharts
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */
class ProjectCharts extends BaseProjectCharts {
	public function __construct() {
		parent::__construct ();
		$this->object_type_name = 'chart';
	}
	
	public static function getWorkspaceString($ids = '?') {
		return " `id` IN (SELECT `object_id` FROM `" . TABLE_PREFIX . "workspace_objects` WHERE `object_manager` = 'ProjectCharts' AND `workspace_id` IN ($ids)) ";
	}
	
	/**
	 * Return charts that belong to specific project
	 *
	 * @param Project $project
	 * @return array
	 */
	static function getProjectCharts($project) {
		$conditions = array(self::getWorkspaceString(), $project->getId());

		return self::findAll(array(
			'conditions' => $conditions,
			'order' => '`created_on` DESC',
		)); // findAll
	} // getProjectCharts
	
	static function getChartsAtProject($project = null, $tag = null, $order = '`updated_on` DESC', $limit = 5) {
		if ($project instanceof Project) {
			$ws = ProjectCharts::getWorkspaceString($project->getAllSubWorkspacesQuery()) . ' AND `show_in_parents` = 1 OR ';
			$ws .= ProjectCharts::getWorkspaceString($project->getId()) . ' AND `show_in_project` = 1';
		} else {
			$ws = "`show_in_parents` = 1";
		}
		if ($tag) {
			$tagstr = " AND `id` IN (SELECT `rel_object_id` FROM `" . TABLE_PREFIX . "tags` `t` WHERE `tag` = " . DB::escape($tag)." AND `t`.`rel_object_manager` = 'ProjectCharts')";
		} else {
			$tagstr = "";
		}
		return self::findAll(array(
			'conditions' => "$ws $tagstr" ,
			'order' => $order,
			'limit' => $limit));
	}
	 
} // ProjectCharts
?>