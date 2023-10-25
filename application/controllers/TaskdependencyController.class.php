<?php

class TaskdependencyController extends ApplicationController {
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	}
	
	private function checkCanEditBothTasks($t, $pt) {
		$error = null;
		
		$task = ProjectTasks::instance()->findById($t);
		if (!$task instanceof ProjectTask) {
			$error = lang('task list dnx');
		} else if (!$task->canEdit(logged_user())) {
			$error = lang('no access permissions');
		}
		if (!$error) {
			$prev_task = ProjectTasks::instance()->findById($pt);
			if (!$prev_task instanceof ProjectTask) {
				$error = lang('task list dnx');
			} else if (!$prev_task->canEdit(logged_user())) {
				$error = lang('no access permissions');
			}
		}
		
		return $error;
	}
	
	function remove() {
		$pt = DB::escape(array_var($_GET, 'pt'));
		$t = DB::escape(array_var($_GET, 't'));
		
		$error = $this->checkCanEditBothTasks(array_var($_GET, 't'), array_var($_GET, 'pt'));
		if ($error) {
			flash_error($error);
			ajx_current("empty");
			return;
		}
		
		$dep = ProjectTaskDependencies::instance()->findOne(array('conditions' => "`previous_task_id` = $pt AND `task_id` = $t"));
		if ($dep instanceof ProjectTaskDependency) {
			$dep->delete();
			flash_success(lang('success remove task dependency'));
		} else {
			flash_error(lang('task dependency dnx'));
		}
		
		$reload = array_var($_GET, 'reload', true);
		if($reload){
			ajx_current("reload");
		}else{
			ajx_current("empty");
		}		
	}
	
	function add() {
		$pt = DB::escape(array_var($_GET, 'pt'));
		$t = DB::escape(array_var($_GET, 't'));
		// check that the task and its previous are not the same 
		if ($pt == $t) {
			ajx_current("empty");
			return;
		}
		
		$error = $this->checkCanEditBothTasks(array_var($_GET, 't'), array_var($_GET, 'pt'));
		if ($error) {
			flash_error($error);
			ajx_current("empty");
			return;
		}
		
		$dep = ProjectTaskDependencies::instance()->findOne(array('conditions' => "`previous_task_id` = $pt AND `task_id` = $t"));
		if (!$dep instanceof ProjectTaskDependency) {
			try {
				DB::beginWork();
				$dep = new ProjectTaskDependency();
				$dep->setPreviousTaskId(array_var($_GET, 'pt'));
				$dep->setTaskId(array_var($_GET, 't'));
				$dep->save();
				DB::commit();
			} catch (Exception $e) {
				flash_error($e->getMessage());
				DB::rollback();
			}
		}
		flash_success(lang('success add task dependency'));
		
		$reload = array_var($_GET, 'reload', true);
		if($reload){
			ajx_current("reload");
		}else{
			ajx_current("empty");
		}	
	}
	
}
?>