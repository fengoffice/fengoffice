<?php

/**
 * ProjectTaskDependencies class
 * 
 * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
 */
class  ProjectTaskDependencies extends BaseProjectTaskDependencies {

	static function getDependenciesForTask($task_id) {
		return self::findAll(array('conditions' => '`task_id` = ' . $task_id . " AND 0 = (SELECT `trashed_by_id` FROM `".TABLE_PREFIX."objects` WHERE `id`=`previous_task_id`)"));
	}
	
	static function getDependenciesForTaskOnlyPendingIds($task_id) {
		$ids = array();
		// Build Main SQL
		$sql = "
			SELECT `task_id` FROM `".TABLE_PREFIX."project_task_dependencies` AS ptd
			LEFT JOIN `".TABLE_PREFIX."project_tasks` AS e ON ptd.`task_id` = e.`object_id`
			WHERE `previous_task_id` = ".$task_id." AND `e`.`completed_on` = ".DB::escape(EMPTY_DATETIME)."  
					AND 0 = (SELECT `trashed_by_id` FROM `".TABLE_PREFIX."objects` WHERE `id`=`previous_task_id`)							
	    	
		";
			
		// Execute query and build the resultset
		$rows = DB::executeAll($sql);
		
		if(count($rows)){
			foreach ($rows as $row){
				$ids[] = $row['task_id'];
			}
		}	
		return $ids;
	}
	
	//this function not return temporal dependencies from temporal template tasks
	static function getDependenciesForTemplateTask($task_id) {
		return self::findAll(array('conditions' => '`task_id` = ' . $task_id . " AND 0 = (SELECT `trashed_by_id` FROM `".TABLE_PREFIX."objects` WHERE `id`=`previous_task_id`) AND EXISTS(SELECT `template_id` FROM `".TABLE_PREFIX."template_objects` tem WHERE tem.`object_id`=`previous_task_id`)"));
	}
	
	static function countPendingPreviousTasks($task_id) {
		
		$ids = array();
		// Build Main SQL
		$sql = "
		SELECT count(`previous_task_id`) AS row_count FROM `".TABLE_PREFIX."project_task_dependencies` AS ptd
		LEFT JOIN `".TABLE_PREFIX."project_tasks` AS e ON ptd.`previous_task_id` = e.`object_id`
		WHERE `task_id` = ".$task_id." AND `e`.`completed_on` = ".DB::escape(EMPTY_DATETIME)."
		AND 0 = (SELECT `trashed_by_id` FROM `".TABLE_PREFIX."objects` WHERE `id`=`previous_task_id`)
		
		";
			
		// Execute query and build the resultset
		$row = DB::executeOne($sql);		
		
		return (integer) array_var($row, 'row_count', 0);		
	}
	
	static function getDependantsForTask($task_id) {
		return self::findAll(array('conditions' => '`previous_task_id` = ' . $task_id . " AND 0 = (SELECT `trashed_by_id` FROM `".TABLE_PREFIX."objects` WHERE `id`=`task_id`)"));
	}	
		
	static function getDependantTasksAssignedUsers($task_id) {
		$users = array();
		$deps = self::getDependantsForTask($task_id);
		foreach ($deps as $dep) {
			/* @var $dep ProjectTaskDependency */
			$task = ProjectTasks::findById($dep->getTaskId());
			if ($task instanceof ProjectTask) {
				$u = $task->getAssignedTo();
				if ($u instanceof Contact) {
					$users[] = $u;
				}
			}
		}
		return $users;
	}
	
	static function getDependantTasks($task_id) {
		$dependant_tasks = array();
		$deps = self::getDependantsForTask($task_id);
		foreach ($deps as $dep) {
			/* @var $dep ProjectTaskDependency */
			$task = ProjectTasks::findById($dep->getTaskId());
			if ($task instanceof ProjectTask) {
				$dependant_tasks[] = $task;
			}
		}
		return $dependant_tasks;
	}
	
	static function getPreviousTasks($task_id) {
		$previous_tasks = array();
		$deps = self::getDependenciesForTask($task_id);
		foreach ($deps as $dep) {
			/* @var $dep ProjectTaskDependency */
			$task = ProjectTasks::findById($dep->getPreviousTaskId());
			if ($task instanceof ProjectTask) {
				$previous_tasks[] = $task;
			}
		}
		return $previous_tasks;
	}

} // ProjectTaskDependencies

?>